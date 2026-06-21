<?php
namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Concerns\HandlesSecurePublicFiles;
use App\Http\Controllers\Controller;
use App\Models\Dokumen;
use App\Models\PengajuanMagang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DokumenController extends Controller
{
    use HandlesSecurePublicFiles;
    public function index($pengajuanId)
    {
        $pengajuan = $this->findOwnedPengajuan($pengajuanId);
        $dokumens  = Dokumen::where('pengajuan_id', $pengajuanId)->latest()->get();
        return view('mahasiswa.dokumen.index', compact('pengajuan', 'dokumens'));
    }

    public function store(Request $request, $pengajuanId)
    {
        $this->findOwnedPengajuan($pengajuanId);

        $request->validate([
            'jenis_dokumen' => 'required|in:surat_diterima,proposal_magang',
            'file'          => 'required|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $filePath = $request->file('file')->store('documents/surat', 'public');

        Dokumen::create([
            'pengajuan_id'      => $pengajuanId,
            'jenis_dokumen'     => $request->jenis_dokumen,
            'file_path'         => $filePath,
            'tanggal_upload'    => now()->toDateString(),
            'status_verifikasi' => 'pending',
        ]);

        $pengajuan = PengajuanMagang::findOrFail($pengajuanId);
        if (in_array($request->jenis_dokumen, ['surat_permohonan', 'surat_diterima'])) {
            $pengajuan->update(['status_surat_pengantar' => 'pending']);
        }

        return redirect()->back()->with('success', 'Dokumen berhasil diupload.');
    }

    public function preview($dokumenId)
    {
        $dokumen = $this->findOwnedDokumen($dokumenId);
        return $this->publicInlineResponse($dokumen->file_path, $this->downloadName($dokumen));
    }

    public function download($dokumenId)
    {
        $dokumen = $this->findOwnedDokumen($dokumenId);
        return $this->publicDownloadResponse($dokumen->file_path, $this->downloadName($dokumen));
    }

    public function update(Request $request, $dokumenId)
    {
        $dokumen = $this->findOwnedDokumen($dokumenId);
        if (!$this->canStudentModify($dokumen)) {
            return redirect()->back()->with('error', 'Dokumen tidak dapat diganti karena sudah valid atau dipakai dalam proses berikutnya.');
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        $oldPath = $dokumen->file_path;
        $newPath = $request->file('file')->store('documents/mahasiswa', 'public');
        $dokumen->update([
            'file_path' => $newPath,
            'tanggal_upload' => now()->toDateString(),
            'status_verifikasi' => 'pending',
            'catatan' => null,
        ]);

        if ($oldPath) {
            $this->deletePublicFileIfExists($oldPath);
        }

        return redirect()->back()->with('success', 'Dokumen berhasil diganti.');
    }

    public function destroy($dokumenId)
    {
        $dokumen = $this->findOwnedDokumen($dokumenId);
        if (!$this->canStudentModify($dokumen)) {
            return redirect()->back()->with('error', 'Dokumen tidak dapat dihapus karena sudah valid atau dipakai dalam proses berikutnya.');
        }

        if ($dokumen->file_path) {
            $this->deletePublicFileIfExists($dokumen->file_path);
        }

        $pengajuan = $dokumen->pengajuan;
        $jenis = $dokumen->jenis_dokumen;
        $dokumen->delete();

        if ($jenis === 'surat_diterima') {
            $pengajuan->update(['status_surat_keterangan' => 'belum_ada']);
        }

        return redirect()->back()->with('success', 'Dokumen berhasil dihapus.');
    }
    private function findOwnedPengajuan($pengajuanId): PengajuanMagang
    {
        return PengajuanMagang::where('mahasiswa_id', auth()->user()->mahasiswaProfile?->id)->findOrFail($pengajuanId);
    }

    private function findOwnedDokumen($dokumenId): Dokumen
    {
        return Dokumen::whereHas('pengajuan', fn($q) => $q->where('mahasiswa_id', auth()->user()->mahasiswaProfile?->id))
            ->with('pengajuan.mahasiswa')
            ->findOrFail($dokumenId);
    }

    private function canStudentModify(Dokumen $dokumen): bool
    {
        $studentDocs = ['surat_diterima', 'proposal_magang'];
        if (!in_array($dokumen->jenis_dokumen, $studentDocs, true)) {
            return false;
        }

        if (!in_array($dokumen->status_verifikasi, ['pending', 'revisi'], true)) {
            return false;
        }

        $pengajuan = $dokumen->pengajuan;
        if ($pengajuan && in_array($pengajuan->status_pengajuan, ['berjalan', 'selesai'], true)) {
            return false;
        }

        return true;
    }
    private function downloadName(Dokumen $dokumen): string
    {
        $labels = [
            'surat_pengantar' => 'Surat Pengantar Magang',
            'surat_keterangan_magang' => 'Surat Keterangan Magang',
            'sk_magang' => 'SK Magang',
            'proposal_magang' => 'Proposal Magang',
            'surat_diterima' => 'Surat Balasan Diterima Instansi',
            'laporan' => 'Laporan Magang',
            'laporan_hasil_magang' => 'Laporan Hasil Magang',
            'produk_magang' => 'Produk Magang',
            'laporan_kukerta' => 'Laporan Kukerta',
            'surat_seminar' => 'Surat Seminar Magang',
        ];

        $label = $labels[$dokumen->jenis_dokumen] ?? str_replace('_', ' ', $dokumen->jenis_dokumen);
        $nama = $dokumen->pengajuan?->mahasiswa?->nama_lengkap ?: auth()->user()->name;
        $extension = pathinfo($dokumen->file_path, PATHINFO_EXTENSION) ?: 'pdf';

        return $this->safeFilename($label . ' ' . $nama) . '.' . $extension;
    }

    private function safeFilename(string $name): string
    {
        $name = preg_replace('/[\\\\\/:*?"<>|]+/', ' ', $name);
        $name = preg_replace('/\s+/', ' ', trim($name));
        return $name ?: 'Dokumen Magang';
    }

    private function inlineFile(string $path, string $filename)
    {
        $disk = Storage::disk('public');
        $absolutePath = $disk->path($path);
        $mime = $disk->mimeType($path) ?: 'application/octet-stream';

        return response()->stream(function () use ($absolutePath) {
            readfile($absolutePath);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Length' => (string) filesize($absolutePath),
            'Content-Disposition' => HeaderUtils::makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename),
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
