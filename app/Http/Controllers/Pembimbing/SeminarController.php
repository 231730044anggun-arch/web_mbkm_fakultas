<?php
namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Concerns\HandlesSecurePublicFiles;
use App\Http\Controllers\Controller;
use App\Models\KelayakanSeminar;
use App\Models\KelayakanSeminarCatatanHistory;
use App\Models\PengajuanMagang;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SeminarController extends Controller
{
    use HandlesSecurePublicFiles;
    public function index()
    {
        $pembimbingId = auth()->user()->pembimbingLapangan?->id;
        $kelayakans = KelayakanSeminar::with(['pengajuan.mahasiswa.prodi', 'pengajuan.mitra', 'pengajuan.periode'])
            ->where(function ($query) use ($pembimbingId) {
                $query->where('pembimbing_lapangan_id', $pembimbingId)
                    ->orWhereHas('pengajuan', fn($q) => $q->where('pembimbing_lapangan_id', $pembimbingId));
            })
            ->latest()
            ->paginate(15);
        $pengajuans = PengajuanMagang::with(['mahasiswa', 'mitra', 'periode', 'kelayakanSeminar'])
            ->where('pembimbing_lapangan_id', $pembimbingId)
            ->whereIn('status_seminar', ['menunggu_jadwal', 'terjadwal', 'selesai', 'ditunda'])
            ->latest()
            ->paginate(10, ['*'], 'jadwal_page');
        return view('pembimbing.seminar.index', compact('kelayakans', 'pengajuans'));
    }

    public function show(KelayakanSeminar $kelayakan)
    {
        $kelayakan->loadMissing('pengajuan');
        abort_unless($this->canAccessKelayakan($kelayakan), 403);
        $kelayakan->load(['pengajuan.mahasiswa.prodi', 'pengajuan.mitra', 'pengajuan.bimbinganFormals', 'pengajuan.bimbingans.dosen', 'catatanHistories.user']);
        return view('pembimbing.seminar.show', compact('kelayakan'));
    }

    public function validasi(Request $request, KelayakanSeminar $kelayakan)
    {
        $kelayakan->loadMissing('pengajuan');
        abort_unless($this->canAccessKelayakan($kelayakan), 403);
        $request->validate([
            'status' => 'required|in:disetujui,revisi,ditolak',
            'catatan' => 'required_if:status,revisi,ditolak|nullable|string|max:1000',
        ]);
        $statusKelayakan = $request->status === 'disetujui' && $kelayakan->status_persetujuan_dosen === 'disetujui'
            ? 'siap_dijadwalkan'
            : ($request->status === 'disetujui' ? 'menunggu_persetujuan' : $request->status);

        $kelayakan->update([
            'status' => $statusKelayakan,
            'status_persetujuan_pembimbing' => $request->status,
            'catatan_pembimbing' => $request->catatan,
            'tanggal_persetujuan_pembimbing' => $request->status === 'disetujui' ? now() : null,
        ]);
        KelayakanSeminarCatatanHistory::create([
            'kelayakan_seminar_id' => $kelayakan->id,
            'user_id' => auth()->id(),
            'role_pemberi' => 'Pembimbing Lapangan',
            'nama_pemberi' => auth()->user()->pembimbingLapangan?->nama_pembimbing ?? auth()->user()->name ?? auth()->user()->email,
            'status_tindakan' => $request->status,
            'catatan' => $request->catatan,
        ]);
        if ($kelayakan->mahasiswa?->user) {
            Notifikasi::create(['user_id' => $kelayakan->mahasiswa->user->id, 'judul' => 'Status Kelayakan Seminar dari Pembimbing Lapangan', 'pesan' => 'Pembimbing lapangan memberi status ' . $request->status . ' pada bahan kelayakan seminar Anda.', 'status' => 'belum', 'target_url' => route('mahasiswa.seminar.index')]);
        }

        $fresh = $kelayakan->fresh(['pengajuan.mahasiswa']);
        if ($fresh->isApproved()) {
            $this->markReadyForSchedule($fresh);
            if ($fresh->mahasiswa?->user) {
                Notifikasi::create(['user_id' => $fresh->mahasiswa->user->id, 'judul' => 'Seminar Sudah Bisa Diajukan', 'pesan' => 'Bahan kelayakan seminar sudah disetujui dosen dan pembimbing lapangan.', 'status' => 'belum', 'target_url' => route('mahasiswa.seminar.index')]);
            }
        }
        return redirect()->back()->with('success', 'Status kelayakan seminar berhasil disimpan.');
    }

    public function file(KelayakanSeminar $kelayakan, string $type)
    {
        $kelayakan->loadMissing('pengajuan');
        abort_unless($this->canAccessKelayakan($kelayakan), 403);
        $path = match ($type) {
            'produk' => $kelayakan->produk_magang,
            'jurnal' => $kelayakan->draft_jurnal,
            default => $kelayakan->laporan_hasil_magang,
        };
        return $this->publicInlineResponse($path, basename($this->normalizePublicPath($path) ?: $path));
    }

    private function canAccessKelayakan(KelayakanSeminar $kelayakan): bool
    {
        $pembimbingId = auth()->user()->pembimbingLapangan?->id;
        if (!$pembimbingId) {
            return false;
        }

        return $this->idsMatch($kelayakan->pembimbing_lapangan_id, $pembimbingId)
            || $this->idsMatch($kelayakan->pengajuan?->pembimbing_lapangan_id, $pembimbingId);
    }

    private function markReadyForSchedule(KelayakanSeminar $kelayakan): void
    {
        $pengajuan = $kelayakan->pengajuan;
        if (!$pengajuan || in_array($pengajuan->status_seminar, ['menunggu_jadwal', 'terjadwal', 'selesai', 'ditunda', 'dibatalkan'], true)) {
            return;
        }

        $pengajuan->update(['status_seminar' => 'menunggu_jadwal']);
        $nama = $pengajuan->mahasiswa->nama_lengkap ?? 'Mahasiswa';

        User::whereIn('role', ['admin', 'superadmin'])->each(function (User $user) use ($nama) {
            Notifikasi::create([
                'user_id' => $user->id,
                'judul' => 'Seminar Siap Dijadwalkan',
                'pesan' => 'Kelayakan seminar mahasiswa ' . $nama . ' sudah disetujui dosen pembimbing dan pembimbing lapangan. Seminar siap dijadwalkan.',
                'status' => 'belum',
                'target_url' => route('admin.seminar.index'),
            ]);
        });
    }
}
