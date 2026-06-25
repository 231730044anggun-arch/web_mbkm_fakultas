<?php
namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Concerns\HandlesSecurePublicFiles;
use App\Http\Controllers\Controller;
use App\Models\KelayakanSeminar;
use App\Models\Notifikasi;
use App\Models\PengajuanMagang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SeminarController extends Controller
{
    use HandlesSecurePublicFiles;
    public function index()
    {
        $dosenId = auth()->user()->dosen?->id;
        $kelayakans = KelayakanSeminar::with(['pengajuan.mahasiswa.prodi', 'pengajuan.mitra', 'pengajuan.periode'])
            ->where('dosen_id', $dosenId)
            ->latest()
            ->paginate(15);
        $pengajuans = PengajuanMagang::with(['mahasiswa', 'mitra', 'periode', 'kelayakanSeminar'])
            ->whereHas('bimbingans', fn($q) => $q->where('dosen_id', $dosenId))
            ->whereIn('status_seminar', ['menunggu_jadwal', 'terjadwal', 'selesai', 'ditunda'])
            ->latest()
            ->paginate(10, ['*'], 'jadwal_page');
        return view('dosen.seminar.index', compact('kelayakans', 'pengajuans'));
    }

    public function show(KelayakanSeminar $kelayakan)
    {
        abort_unless($this->idsMatch($kelayakan->dosen_id, auth()->user()->dosen?->id), 403);
        $kelayakan->load(['pengajuan.mahasiswa.prodi', 'pengajuan.mitra', 'pengajuan.bimbinganFormals', 'pengajuan.pembimbingLapangan']);
        return view('dosen.seminar.show', compact('kelayakan'));
    }

    public function validasi(Request $request, KelayakanSeminar $kelayakan)
    {
        abort_unless($this->idsMatch($kelayakan->dosen_id, auth()->user()->dosen?->id), 403);
        $request->validate([
            'status' => 'required|in:disetujui,revisi,ditolak',
            'catatan' => 'required_if:status,revisi,ditolak|nullable|string|max:1000',
        ]);
        $statusKelayakan = $request->status === 'disetujui' && $kelayakan->status_persetujuan_pembimbing === 'disetujui'
            ? 'siap_dijadwalkan'
            : ($request->status === 'disetujui' ? 'menunggu_persetujuan' : $request->status);

        $kelayakan->update([
            'status' => $statusKelayakan,
            'status_persetujuan_dosen' => $request->status,
            'catatan_dosen' => $request->catatan,
            'tanggal_persetujuan_dosen' => $request->status === 'disetujui' ? now() : null,
        ]);
        $this->notifyMahasiswa($kelayakan, 'Status Kelayakan Seminar dari Dosen', 'Dosen pembimbing memberi status ' . $request->status . ' pada bahan kelayakan seminar Anda.');
        $fresh = $kelayakan->fresh(['pengajuan.mahasiswa']);
        if ($fresh->isApproved()) {
            $this->markReadyForSchedule($fresh);
            $this->notifyMahasiswa($fresh, 'Seminar Sudah Bisa Diajukan', 'Bahan kelayakan seminar sudah disetujui dosen dan pembimbing lapangan.');
        }
        return redirect()->back()->with('success', 'Status kelayakan seminar berhasil disimpan.');
    }

    public function file(KelayakanSeminar $kelayakan, string $type)
    {
        abort_unless($this->idsMatch($kelayakan->dosen_id, auth()->user()->dosen?->id), 403);
        $path = match ($type) {
            'produk' => $kelayakan->produk_magang,
            'jurnal' => $kelayakan->draft_jurnal,
            default => $kelayakan->laporan_hasil_magang,
        };
        return $this->publicInlineResponse($path, basename($this->normalizePublicPath($path) ?: $path));
    }

    private function notifyMahasiswa(KelayakanSeminar $kelayakan, string $judul, string $pesan): void
    {
        if ($kelayakan->mahasiswa?->user) {
            Notifikasi::create(['user_id' => $kelayakan->mahasiswa->user->id, 'judul' => $judul, 'pesan' => $pesan, 'status' => 'belum', 'target_url' => route('mahasiswa.seminar.index')]);
        }
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
