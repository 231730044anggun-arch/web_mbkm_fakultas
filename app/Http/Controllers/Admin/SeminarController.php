<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesSecurePublicFiles;
use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Dokumen;
use App\Models\KelayakanSeminar;
use App\Models\Notifikasi;
use App\Models\Penilaian;
use App\Models\PengajuanMagang;
use App\Models\StatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SeminarController extends Controller
{
    use HandlesSecurePublicFiles;
    public function index()
    {
        $pengajuans = PengajuanMagang::with(['mahasiswa.user', 'mitra', 'periode', 'bimbingans.dosen.user', 'pembimbingLapangan.user', 'kelayakanSeminar', 'penilaian'])
            ->where(function ($query) {
                $query->whereIn('status_seminar', ['menunggu_jadwal', 'terjadwal', 'selesai', 'ditunda', 'dibatalkan'])
                    ->orWhereHas('kelayakanSeminar', fn($kelayakan) => $kelayakan
                        ->where('status_persetujuan_dosen', 'disetujui')
                        ->where('status_persetujuan_pembimbing', 'disetujui'));
            })
            ->latest()
            ->paginate(15);
        $dosens = Dosen::orderBy('nama_dosen')->get();

        return view('admin.seminar.index', compact('pengajuans', 'dosens'));
    }

    public function schedule(Request $request, $id)
    {
        $request->validate([
            'judul_laporan' => 'required|string|max:255',
            'seminar_tanggal' => 'required|date',
            'seminar_jam' => 'required|string|max:20',
            'seminar_ruangan' => 'required|string|max:120',
            'status_seminar' => 'required|in:terjadwal,selesai,ditunda,dibatalkan',
            'catatan' => 'nullable|string|max:500',
        ]);

        $pengajuan = PengajuanMagang::with(['mahasiswa.user', 'bimbingans.dosen.user', 'pembimbingLapangan.user', 'kelayakanSeminar'])->findOrFail($id);
        if (!$pengajuan->kelayakanSeminar?->isApproved()) {
            return redirect()->back()->with('error', 'Seminar belum dapat dijadwalkan karena belum disetujui oleh dosen pembimbing dan pembimbing lapangan.');
        }

        $pengajuan->update([
            'judul_laporan' => $request->judul_laporan,
            'seminar_tanggal' => $request->seminar_tanggal,
            'seminar_jam' => $request->seminar_jam,
            'seminar_ruangan' => $request->seminar_ruangan,
            'status_seminar' => $request->status_seminar,
            'catatan_admin' => $request->catatan ?: $pengajuan->catatan_admin,
        ]);

        StatusHistory::create([
            'pengajuan_id' => $pengajuan->id,
            'status' => 'seminar_' . $request->status_seminar,
            'keterangan' => $request->catatan ?: 'Jadwal seminar diperbarui.',
            'updated_by' => auth()->id(),
        ]);

        if ($pengajuan->mahasiswa?->user) {
            Notifikasi::create([
                'user_id' => $pengajuan->mahasiswa->user->id,
                'judul' => 'Jadwal Seminar Magang',
                'pesan' => 'Seminar magang Anda berstatus ' . $request->status_seminar . '.',
                'status' => 'belum',
                'target_url' => route('mahasiswa.seminar.index'),
            ]);
        }
        foreach ($pengajuan->bimbingans as $bimbingan) {
            if ($bimbingan->dosen?->user) {
                Notifikasi::create([
                    'user_id' => $bimbingan->dosen->user->id,
                    'judul' => 'Jadwal Seminar Mahasiswa Bimbingan',
                    'pesan' => 'Seminar mahasiswa ' . ($pengajuan->mahasiswa->nama_lengkap ?? '-') . ' berstatus ' . $request->status_seminar . '.',
                    'status' => 'belum',
                    'target_url' => route('dosen.seminar.index'),
                ]);
            }
        }
        if ($pengajuan->pembimbingLapangan?->user) {
            Notifikasi::create([
                'user_id' => $pengajuan->pembimbingLapangan->user->id,
                'judul' => 'Jadwal Seminar Mahasiswa Bimbingan',
                'pesan' => 'Seminar mahasiswa ' . ($pengajuan->mahasiswa->nama_lengkap ?? '-') . ' berstatus ' . $request->status_seminar . '.',
                'status' => 'belum',
                'target_url' => route('pembimbing.seminar.index'),
            ]);
        }

        if (in_array($request->status_seminar, ['terjadwal', 'selesai'], true)) {
            $this->generateSuratSeminar($pengajuan);
        }

        return redirect()->back()->with('success', 'Jadwal seminar berhasil diperbarui.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'status_seminar' => 'required|in:ditunda,dibatalkan',
            'catatan' => 'required|string|max:500',
        ]);

        $pengajuan = PengajuanMagang::with(['mahasiswa.user', 'bimbingans.dosen.user', 'pembimbingLapangan.user', 'kelayakanSeminar'])->findOrFail($id);
        $pengajuan->update([
            'status_seminar' => $request->status_seminar,
            'catatan_admin' => $request->catatan,
        ]);

        StatusHistory::create([
            'pengajuan_id' => $pengajuan->id,
            'status' => 'seminar_' . $request->status_seminar,
            'keterangan' => $request->catatan,
            'updated_by' => auth()->id(),
        ]);

        if ($pengajuan->mahasiswa?->user) {
            Notifikasi::create([
                'user_id' => $pengajuan->mahasiswa->user->id,
                'judul' => 'Status Seminar Magang',
                'pesan' => 'Pengajuan seminar Anda berstatus ' . $request->status_seminar . '. Catatan: ' . $request->catatan,
                'status' => 'belum',
                'target_url' => route('mahasiswa.seminar.index'),
            ]);
        }

        return redirect()->back()->with('success', 'Status seminar berhasil diperbarui.');
    }

    public function storeSeminarScore(Request $request, $id)
    {
        return redirect()->back()->with('error', 'Nilai seminar hasil magang diinput oleh Dosen Pembimbing dan Pembimbing Lapangan melalui menu Penilaian setelah status seminar selesai.');
    }

    public function cancel(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'required|string|max:500',
        ]);

        $pengajuan = PengajuanMagang::with(['mahasiswa.user', 'bimbingans.dosen.user', 'pembimbingLapangan.user', 'kelayakanSeminar'])->findOrFail($id);
        if ($pengajuan->status_seminar === 'selesai' || $pengajuan->penilaian()->exists()) {
            return redirect()->back()->with('error', 'Seminar tidak dapat dibatalkan karena sudah selesai atau nilai sudah masuk.');
        }

        $pengajuan->update([
            'status_seminar' => 'dibatalkan',
            'seminar_tanggal' => null,
            'seminar_jam' => null,
            'seminar_ruangan' => null,
            'catatan_admin' => $request->catatan,
        ]);

        StatusHistory::create([
            'pengajuan_id' => $pengajuan->id,
            'status' => 'seminar_dibatalkan_admin',
            'keterangan' => $request->catatan,
            'updated_by' => auth()->id(),
        ]);

        if ($pengajuan->mahasiswa?->user) {
            Notifikasi::create([
                'user_id' => $pengajuan->mahasiswa->user->id,
                'judul' => 'Seminar Magang Dibatalkan',
                'pesan' => 'Seminar magang Anda dibatalkan admin. Catatan: ' . $request->catatan,
                'status' => 'belum',
                'target_url' => route('mahasiswa.seminar.index'),
            ]);
        }

        return redirect()->back()->with('success', 'Seminar berhasil dibatalkan.');
    }

    public function file(KelayakanSeminar $kelayakan, string $type)
    {
        $path = match ($type) {
            'produk' => $kelayakan->produk_magang,
            'jurnal' => $kelayakan->draft_jurnal,
            default => $kelayakan->laporan_hasil_magang,
        };
        return $this->publicInlineResponse($path, basename($this->normalizePublicPath($path) ?: $path));
    }

    private function generateSuratSeminar(PengajuanMagang $pengajuan): void
    {
        try {
            $pengajuan->loadMissing(['mahasiswa', 'mitra']);
            $html = view('surat.sk_seminar', compact('pengajuan'))->render();
            $pdf = \PDF::loadHTML($html)->setPaper('a4', 'portrait');
            $path = 'surat/sk_seminar_' . $pengajuan->id . '.pdf';
            \Storage::disk('public')->put($path, $pdf->output());

            Dokumen::updateOrCreate(
                ['pengajuan_id' => $pengajuan->id, 'jenis_dokumen' => 'surat_seminar'],
                ['file_path' => $path, 'tanggal_upload' => now(), 'status_verifikasi' => 'valid']
            );
        } catch (\Exception $e) {
            \Log::error('Surat seminar generation failed: ' . $e->getMessage());
        }
    }
}
