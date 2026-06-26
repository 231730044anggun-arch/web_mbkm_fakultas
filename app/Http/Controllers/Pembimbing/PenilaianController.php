<?php
namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Concerns\HandlesSecurePublicFiles;
use App\Http\Controllers\Controller;
use App\Models\Dokumen;
use App\Models\Notifikasi;
use App\Models\Penilaian;
use App\Models\PengajuanMagang;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class PenilaianController extends Controller
{
    use HandlesSecurePublicFiles;
    public function index()
    {
        $pengajuans = $this->baseQuery()
            ->with(['mahasiswa.prodi', 'mahasiswa.angkatanMaster', 'mitra', 'periode', 'penilaian', 'absensis', 'kelayakanSeminar', 'logbooks', 'bimbinganFormals'])
            ->latest('updated_at')
            ->paginate(15);

        return view('pembimbing.penilaian.index', compact('pengajuans'));
    }

    public function create($pengajuanId)
    {
        $pengajuan = $this->findPengajuan($pengajuanId);
        $penilaian = Penilaian::where('pengajuan_id', $pengajuanId)->first();
        $rekapAbsensi = $this->rekapAbsensi($pengajuan);
        [$canInput, $lockMessage] = $this->inputStatus($pengajuan);

        return view('pembimbing.penilaian.create', compact('pengajuan', 'penilaian', 'rekapAbsensi', 'canInput', 'lockMessage'));
    }

    public function store(Request $request, $pengajuanId)
    {
        $pengajuan = $this->findPengajuan($pengajuanId);

        [$canInput, $lockMessage] = $this->inputStatus($pengajuan);
        if (!$canInput) {
            return redirect()->route('pembimbing.penilaian.create', $pengajuanId)
                ->with('error', $lockMessage);
        }

        $request->validate($this->rules(), $this->validationMessages());
        $existing = Penilaian::where('pengajuan_id', $pengajuanId)->first();
        $filePenilaian = $existing?->file_penilaian_formal_pembimbing;
        if ($request->hasFile('file_penilaian_formal')) {
            if ($filePenilaian) $this->deletePublicFileIfExists($filePenilaian);
            $filePenilaian = $request->file('file_penilaian_formal')->store('documents/penilaian/pembimbing', 'public');
        }

        $data = [
            ...$this->stageOnePayload($request, 'pembimbing'),
            'catatan_mitra' => $request->catatan_mitra,
            'catatan' => $request->catatan_mitra,
            'file_penilaian_formal_pembimbing' => $filePenilaian,
        ];

        if ($pengajuan->status_seminar === 'selesai') {
            $data = [
                ...$data,
                ...$this->seminarPayload($request, $existing, 'pembimbing'),
            ];
        }

        $penilaian = Penilaian::updateOrCreate(
            ['pengajuan_id' => $pengajuanId],
            $data
        );

        $penilaian->calculateFinalScore();

        if ($penilaian->nilai_akhir !== null && $pengajuan->status_pengajuan !== 'selesai') {
            $pengajuan->update(['status_pengajuan' => 'selesai']);
        }

        if ($pengajuan->mahasiswa?->user) {
            Notifikasi::create([
                'user_id' => $pengajuan->mahasiswa->user->id,
                'judul' => 'Nilai Pembimbing Lapangan Tersimpan',
                'pesan' => 'Nilai dari pembimbing lapangan telah diperbarui. Nilai akhir tampil setelah nilai dosen pembimbing juga lengkap.',
                'status' => 'belum',
                'target_url' => route('mahasiswa.penilaian.show', $pengajuan->id),
            ]);
        }

        if ($filePenilaian) {
            Dokumen::updateOrCreate(
                ['pengajuan_id' => $pengajuan->id, 'jenis_dokumen' => 'file_penilaian_formal_pembimbing'],
                [
                    'file_path' => $filePenilaian,
                    'tanggal_upload' => now()->toDateString(),
                    'status_verifikasi' => 'valid',
                    'catatan' => 'File penilaian formal pembimbing lapangan',
                ]
            );
        }

        return redirect()->route('pembimbing.penilaian.index')->with('success', 'Nilai pembimbing lapangan berhasil disimpan.');
    }

    public function file($pengajuanId)
    {
        $pengajuan = $this->findPengajuan($pengajuanId);
        $path = $pengajuan->penilaian?->file_penilaian_formal_pembimbing;
        return $this->publicInlineResponse($path, basename($this->normalizePublicPath($path) ?: $path));
    }

    private function rules(): array
    {
        $rules = [
            'file_penilaian_formal' => 'nullable|file|mimes:pdf|max:10240',
            'catatan_mitra' => 'nullable|string|max:1000',
        ];

        foreach (array_keys(Penilaian::tahap1Fields('pembimbing')) as $field) {
            $rules[$field] = 'required|numeric|min:1|max:100';
        }

        foreach (array_keys(Penilaian::laporanRubrik('pembimbing') + Penilaian::presentasiRubrik('pembimbing')) as $field) {
            $rules[$field] = 'nullable|numeric|min:50|max:100';
        }

        return $rules;
    }

    private function validationMessages(): array
    {
        $messages = [];

        foreach (array_keys(Penilaian::tahap1Fields('pembimbing')) as $field) {
            $messages[$field . '.required'] = 'Nilai wajib diisi.';
            $messages[$field . '.numeric'] = 'Nilai harus berupa angka.';
            $messages[$field . '.min'] = 'Nilai minimal adalah 1.';
            $messages[$field . '.max'] = 'Nilai maksimal adalah 100.';
        }

        return $messages;
    }

    private function stageOnePayload(Request $request, string $role): array
    {
        $payload = [];
        foreach (array_keys(Penilaian::tahap1Fields($role)) as $field) {
            $payload[$field] = $request->input($field);
        }

        return $payload;
    }

    private function seminarPayload(Request $request, ?Penilaian $existing, string $role): array
    {
        $payload = [];
        foreach (array_keys(Penilaian::laporanRubrik($role) + Penilaian::presentasiRubrik($role)) as $field) {
            $value = $request->input($field);
            $payload[$field] = $value === null || $value === '' ? $existing?->{$field} : $value;
        }

        return $payload;
    }

    private function baseQuery()
    {
        return PengajuanMagang::query()
            ->where('pembimbing_lapangan_id', auth()->user()->pembimbingLapangan?->id)
            ->where('jenis_pengajuan', 'surat_keterangan')
            ->whereIn('status_pengajuan', ['berjalan', 'selesai']);
    }

    private function findPengajuan($pengajuanId): PengajuanMagang
    {
        return $this->baseQuery()
            ->with(['mahasiswa.prodi', 'mahasiswa.angkatanMaster', 'mitra', 'periode', 'penilaian', 'absensis', 'kelayakanSeminar', 'logbooks', 'bimbinganFormals'])
            ->findOrFail($pengajuanId);
    }

    private function inputStatus(PengajuanMagang $pengajuan): array
    {
        return [true, ''];
    }

    private function rekapAbsensi(PengajuanMagang $pengajuan): array
    {
        $total = count($this->workdays($pengajuan));
        $absensis = $pengajuan->absensis;
        $approved = $absensis->where('status', 'disetujui')->count();

        return [
            'total_hari_wajib' => $total,
            'jumlah_absensi_masuk' => $absensis->count(),
            'jumlah_disetujui' => $approved,
            'jumlah_pending' => $absensis->where('status', 'pending')->count(),
            'jumlah_revisi' => $absensis->whereIn('status', ['revisi', 'ditolak'])->count(),
            'persentase_kehadiran' => $total > 0 ? round(($approved / $total) * 100, 2) : 0,
        ];
    }

    private function workdays(PengajuanMagang $pengajuan): array
    {
        if (!$pengajuan->tanggal_mulai || !$pengajuan->tanggal_selesai) return [];
        $dates = [];
        $start = Carbon::parse($pengajuan->tanggal_mulai);
        $end = Carbon::parse($pengajuan->tanggal_selesai);
        for ($date = $start->copy(); $date->lessThanOrEqualTo($end); $date->addDay()) {
            if ($date->isWeekday()) $dates[] = $date->toDateString();
        }
        return $dates;
    }
}
