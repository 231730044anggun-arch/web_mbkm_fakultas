<?php
namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use App\Models\Notifikasi;
use App\Models\PengajuanMagang;
use PDF;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class LogbookController extends Controller
{
    public function index(Request $request, $pengajuanId)
    {
        $pengajuan = $this->findOwnedPengajuan($pengajuanId);
        if (!$this->hasPublishedSuratKeterangan($pengajuan)) {
            return view('mahasiswa.info-butuh-pengajuan', [
                'feature' => 'Logbook',
                'message' => 'Logbook belum dapat diisi karena Surat Keterangan Magang belum diterbitkan.',
            ]);
        }

        $query = Logbook::where('pengajuan_id', $pengajuanId);
        if ($request->filled('from')) $query->where('tanggal', '>=', $request->from);
        if ($request->filled('to')) $query->where('tanggal', '<=', $request->to);
        if ($request->filled('status')) $query->where('status_validasi', $request->status);
        $logbooks  = $query->orderBy('tanggal', 'desc')->paginate(10)->withQueryString();
        $missing = $this->findMissingWeeks($pengajuan);
        return view('mahasiswa.logbook.index', compact('pengajuan', 'logbooks', 'missing'));
    }

    public function store(Request $request, $pengajuanId)
    {
        $pengajuan = $this->findOwnedPengajuan($pengajuanId);
        if (!$this->hasPublishedSuratKeterangan($pengajuan)) {
            return redirect()->back()->with('error', 'Logbook belum dapat diisi karena Surat Keterangan Magang belum diterbitkan.');
        }

        $request->validate([
            
            'kegiatan'        => 'required|string',
            'output_kegiatan' => 'required|string|max:2000',
            'kendala'         => 'nullable|string|max:2000',
            'solusi'          => 'nullable|string|max:2000',
            'jam_mulai'       => 'required',
            'jam_selesai'     => 'required',
            'bukti_foto'      => 'required|image|max:5120',
        ]);

        $existingToday = Logbook::where('pengajuan_id', $pengajuan->id)->whereDate('tanggal', now()->toDateString())->first();
        if ($existingToday && !in_array($existingToday->status_validasi, ['revisi'], true)) {
            return redirect()->back()->withInput()->with('error', 'Logbook untuk hari ini sudah ada. Satu tanggal hanya boleh memiliki satu logbook.');
        }

        $fotoPath = $request->file('bukti_foto')->store('images/logbook', 'public');

        $logbook = Logbook::create([
            'pengajuan_id'     => $pengajuanId,
            'tanggal'          => now()->toDateString(),
            'kegiatan'         => $request->kegiatan,
            'output_kegiatan'  => $request->output_kegiatan,
            'kendala'          => $request->kendala,
            'solusi'           => $request->solusi,
            'jam_mulai'        => $request->jam_mulai,
            'jam_selesai'      => $request->jam_selesai,
            'bukti_foto'       => $fotoPath,
            'status_validasi'  => 'pending',
            'status_dosen'     => 'pending',
            'status_mitra'     => 'pending',
            'catatan_dosen'    => null,
            'catatan_mitra'    => null,
        ]);

        $pengajuan->loadMissing('mahasiswa', 'bimbingans.dosen.user', 'pembimbingLapangan.user', 'mitra.mitraUsers.user');
        foreach ($pengajuan->bimbingans as $bimbingan) {
            if ($bimbingan->dosen?->user) {
                Notifikasi::create([
                    'user_id' => $bimbingan->dosen->user->id,
                    'judul' => 'Logbook Baru',
                    'pesan' => 'Mahasiswa ' . ($pengajuan->mahasiswa->nama_lengkap ?? '-') . ' menginput logbook tanggal ' . $logbook->tanggal . '.',
                    'status' => 'belum',
                    'target_url' => route('dosen.logbook.show', $pengajuan->id),
                ]);
            }
        }

        if ($pengajuan->pembimbingLapangan?->user) {
            Notifikasi::create([
                'user_id' => $pengajuan->pembimbingLapangan->user->id,
                'judul' => 'Logbook Baru Mahasiswa Magang',
                'pesan' => 'Mahasiswa ' . ($pengajuan->mahasiswa->nama_lengkap ?? '-') . ' menginput logbook tanggal ' . $logbook->tanggal . '.',
                'status' => 'belum',
                'target_url' => route('pembimbing.logbook.show', $pengajuan->id),
            ]);
        } else {
            foreach (($pengajuan->mitra?->mitraUsers ?? collect()) as $mitraUser) {
                if ($mitraUser->user) {
                    Notifikasi::create([
                        'user_id' => $mitraUser->user->id,
                        'judul' => 'Logbook Baru Mahasiswa Magang',
                        'pesan' => 'Mahasiswa ' . ($pengajuan->mahasiswa->nama_lengkap ?? '-') . ' menginput logbook tanggal ' . $logbook->tanggal . '.',
                        'status' => 'belum',
                        'target_url' => route('mitra.logbook.show', $pengajuan->id),
                    ]);
                }
            }
        }

        $pengajuan = PengajuanMagang::findOrFail($pengajuanId);
        $missing = $this->findMissingWeeks($pengajuan);

        $msg = 'Logbook berhasil ditambahkan.';
        if (count($missing)) {
            $msg .= ' Peringatan: ditemukan minggu tanpa entri logbook.';
        }

        return redirect()->back()->with('success', $msg);
    }

    public function edit(Logbook $logbook)
    {
        $pengajuan = $this->findOwnedPengajuan($logbook->pengajuan_id);
        abort_unless($this->canModify($logbook), 403);

        return view('mahasiswa.logbook.edit', compact('pengajuan', 'logbook'));
    }

    public function update(Request $request, Logbook $logbook)
    {
        $pengajuan = $this->findOwnedPengajuan($logbook->pengajuan_id);
        if (!$this->canModify($logbook)) {
            return redirect()->route('mahasiswa.logbook.index', $pengajuan->id)->with('error', 'Logbook tidak dapat diubah karena sudah disetujui.');
        }

        $request->validate([
            
            'kegiatan'        => 'required|string',
            'output_kegiatan' => 'required|string|max:2000',
            'kendala'         => 'nullable|string|max:2000',
            'solusi'          => 'nullable|string|max:2000',
            'jam_mulai'       => 'required',
            'jam_selesai'     => 'required',
            'bukti_foto'      => 'nullable|image|max:5120',
        ]);

        $data = $request->only(['kegiatan', 'output_kegiatan', 'kendala', 'solusi', 'jam_mulai', 'jam_selesai']);
        $data['status_validasi'] = 'pending';
        $data['status_dosen'] = 'pending';
        $data['status_mitra'] = 'pending';
        $data['catatan_dosen'] = null;
        $data['catatan_mitra'] = null;

        if ($request->hasFile('bukti_foto')) {
            if ($logbook->bukti_foto) {
                Storage::disk('public')->delete($logbook->bukti_foto);
            }
            $data['bukti_foto'] = $request->file('bukti_foto')->store('images/logbook', 'public');
        }

        $logbook->update($data);

        return redirect()->route('mahasiswa.logbook.index', $pengajuan->id)->with('success', 'Logbook berhasil diperbarui.');
    }

    public function destroy(Logbook $logbook)
    {
        $pengajuan = $this->findOwnedPengajuan($logbook->pengajuan_id);
        if (!$this->canModify($logbook)) {
            return redirect()->back()->with('error', 'Logbook tidak dapat dihapus karena sudah disetujui.');
        }

        if ($logbook->bukti_foto) {
            Storage::disk('public')->delete($logbook->bukti_foto);
        }
        $logbook->delete();

        return redirect()->route('mahasiswa.logbook.index', $pengajuan->id)->with('success', 'Logbook berhasil dihapus.');
    }
    public function previewFoto(Logbook $logbook)
    {
        $pengajuan = $this->findOwnedPengajuan($logbook->pengajuan_id);
        abort_unless($logbook->pengajuan_id === $pengajuan->id, 403);
        abort_if(!$logbook->bukti_foto || !Storage::disk('public')->exists($logbook->bukti_foto), 404);

        $extension = pathinfo($logbook->bukti_foto, PATHINFO_EXTENSION) ?: 'jpg';
        return $this->inlineFile($logbook->bukti_foto, 'Bukti Logbook ' . ($pengajuan->mahasiswa->nama_lengkap ?? 'Mahasiswa') . '.' . $extension);
    }

    public function export(Request $request, $pengajuanId)
    {
        $this->findOwnedPengajuan($pengajuanId);
        $pengajuan = PengajuanMagang::with(['mahasiswa', 'mitra', 'bimbingans.dosen', 'periode', 'dokumens'])->findOrFail($pengajuanId);
        if (!$this->hasPublishedSuratKeterangan($pengajuan)) {
            return redirect()->back()->with('error', 'Logbook belum dapat diexport karena Surat Keterangan Magang belum diterbitkan.');
        }
        $query = Logbook::where('pengajuan_id', $pengajuanId);

        if ($request->filled('from')) $query->where('tanggal', '>=', $request->from);
        if ($request->filled('to')) $query->where('tanggal', '<=', $request->to);
        if ($request->filled('status')) $query->where('status_validasi', $request->status);

        $logbooks = $query->orderBy('tanggal')->get();
        $missing = $this->findMissingWeeks($pengajuan, $logbooks, $request->to ?: now()->toDateString());

        $html = view('logbook.pdf', compact('pengajuan', 'logbooks', 'missing'))->render();
        $pdf = PDF::loadHTML($html)->setPaper('a4', 'portrait');

        return $pdf->download('logbook_pengajuan_' . $pengajuan->id . '.pdf');
    }

    private function canModify(Logbook $logbook): bool
    {
        return in_array($logbook->status_validasi, ['pending', 'revisi'], true)
            || in_array($logbook->status_dosen, ['pending', 'revisi'], true)
            || in_array($logbook->status_mitra, ['pending', 'revisi'], true);
    }
    private function findMissingWeeks(PengajuanMagang $pengajuan, $logbooks = null, $toDate = null)
    {
        $start = $pengajuan->tanggal_mulai ? Carbon::parse($pengajuan->tanggal_mulai)->startOfWeek() : null;
        $end = $toDate ? Carbon::parse($toDate) : Carbon::now();
        if ($pengajuan->tanggal_selesai) $end = Carbon::parse($pengajuan->tanggal_selesai);
        if (!$start) return [];

        $weeks = [];
        $cursor = $start->copy();
        while ($cursor->lessThanOrEqualTo($end)) {
            $weeks[] = $cursor->toDateString();
            $cursor->addWeek();
        }

        $entries = $logbooks ?? $pengajuan->logbooks()->get();
        $filledWeeks = [];
        foreach ($entries as $e) {
            $w = Carbon::parse($e->tanggal)->startOfWeek()->toDateString();
            if (($e->status_dosen ?? 'pending') === 'disetujui' && ($e->status_mitra ?? 'pending') === 'disetujui') {
                $filledWeeks[$w] = true;
            }
        }

        $missing = [];
        foreach ($weeks as $w) {
            if (!isset($filledWeeks[$w])) $missing[] = $w;
        }
        return $missing;
    }

    private function findOwnedPengajuan($pengajuanId): PengajuanMagang
    {
        return PengajuanMagang::with(['dokumens', 'mahasiswa', 'bimbingans.dosen.user', 'mitra.mitraUsers.user'])
            ->where('mahasiswa_id', auth()->user()->mahasiswaProfile?->id)
            ->findOrFail($pengajuanId);
    }

    private function hasPublishedSuratKeterangan(PengajuanMagang $pengajuan): bool
    {
        return $pengajuan->dokumens
            ->where('jenis_dokumen', 'surat_keterangan_magang')
            ->where('status_verifikasi', 'valid')
            ->whereNotNull('file_path')
            ->isNotEmpty();
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


