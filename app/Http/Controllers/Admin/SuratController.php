<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengajuanMagang;
use App\Models\Dokumen;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use PDF;
use Storage;
use App\Models\User;

class SuratController extends Controller
{
    public function generateSkIndividual($id)
    {
        // Generate Surat Keterangan Magang (individual) — called when surat_diterima is validated
        $pengajuan = PengajuanMagang::with(['mahasiswa.prodi', 'mahasiswa.fakultas', 'mitra', 'periode'])->findOrFail($id);
        $html = view('surat.surat_keterangan', compact('pengajuan'))->render();
        $pdf = PDF::loadHTML($html)->setPaper('a4', 'portrait');

        $path = 'surat/surat_keterangan_' . $pengajuan->id . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        // store as dokumen record with jenis 'surat_keterangan_magang'
        Dokumen::updateOrCreate(
            ['pengajuan_id' => $pengajuan->id, 'jenis_dokumen' => 'surat_keterangan_magang'],
            ['file_path' => $path, 'tanggal_upload' => now(), 'status_verifikasi' => 'valid']
        );

        // notify mahasiswa
        if ($pengajuan->mahasiswa && $pengajuan->mahasiswa->user) {
            Notifikasi::create([
                'user_id' => $pengajuan->mahasiswa->user->id,
                'judul' => 'Surat Keterangan Magang Terbit',
                'pesan' => 'Surat keterangan magang Anda telah dibuat.',
                'status' => 'belum',
                'target_url' => route('mahasiswa.dokumen.index', $pengajuan->id),
            ]);
        }

        return response()->download(storage_path('app/public/' . $path));
    }

    public function generateSkKolektif(Request $request)
    {
        $request->validate(['periode_id' => 'required|exists:periodes,id']);
        $pengajuans = PengajuanMagang::with(['mahasiswa', 'mitra', 'dokumens'])
            ->where('periode_id', $request->periode_id)
            ->where(function ($query) {
                $query->where('status_surat_keterangan', 'valid')
                    ->orWhereHas('dokumens', fn($q) => $q->where('jenis_dokumen', 'surat_diterima')->where('status_verifikasi', 'valid'));
            })
            ->get();

        $html = view('surat.sk_kolektif', compact('pengajuans'))->render();
        $pdf = PDF::loadHTML($html)->setPaper('a4', 'portrait');

        $path = 'surat/sk_kolektif_periode_' . $request->periode_id . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        // create dokumen entries for each mahasiswa and notify
        foreach ($pengajuans as $p) {
            Dokumen::updateOrCreate(
                ['pengajuan_id' => $p->id, 'jenis_dokumen' => 'sk_magang'],
                ['file_path' => $path, 'tanggal_upload' => now(), 'status_verifikasi' => 'valid']
            );
            if ($p->mahasiswa && $p->mahasiswa->user) {
                Notifikasi::create([
                    'user_id' => $p->mahasiswa->user->id,
                    'judul' => 'SK Magang Terbit',
                    'pesan' => 'SK Magang kolektif untuk periode telah dibuat dan tersedia di menu Dokumen.',
                    'status' => 'belum',
                    'target_url' => route('mahasiswa.dokumen.index', $p->id),
                ]);
            }
        }

        return response()->download(storage_path('app/public/' . $path));
    }

    public function generateSkSeminar($id)
    {
        $pengajuan = PengajuanMagang::with(['mahasiswa', 'mitra'])->findOrFail($id);
        $html = view('surat.sk_seminar', compact('pengajuan'))->render();
        $pdf = PDF::loadHTML($html)->setPaper('a4', 'portrait');

        $path = 'surat/sk_seminar_' . $pengajuan->id . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        Dokumen::updateOrCreate(
            ['pengajuan_id' => $pengajuan->id, 'jenis_dokumen' => 'surat_seminar'],
            ['file_path' => $path, 'tanggal_upload' => now(), 'status_verifikasi' => 'valid']
        );

        // notify mahasiswa
        if ($pengajuan->mahasiswa && $pengajuan->mahasiswa->user) {
            Notifikasi::create([
                'user_id' => $pengajuan->mahasiswa->user->id,
                'judul' => 'Surat Seminar Terbit',
                'pesan' => 'Surat seminar Anda telah dibuat dan tersedia di menu Dokumen.',
                'status' => 'belum',
                'target_url' => route('mahasiswa.dokumen.index', $pengajuan->id),
            ]);
        }

        return response()->download(storage_path('app/public/' . $path));
    }
}
