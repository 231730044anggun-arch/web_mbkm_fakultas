<?php
namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Concerns\HandlesSecurePublicFiles;
use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
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
        $dosenId = auth()->user()->dosen?->id;
        abort_unless($dosenId, 403);

        $pengajuans = PengajuanMagang::with(['mahasiswa.prodi', 'mahasiswa.angkatanMaster', 'mitra', 'periode', 'penilaian', 'kelayakanSeminar', 'logbooks', 'bimbinganFormals'])
            ->where('jenis_pengajuan', 'surat_keterangan')
            ->whereIn('status_pengajuan', ['berjalan', 'selesai'])
            ->whereHas('bimbingans', fn($query) => $query->where('dosen_id', $dosenId))
            ->latest('updated_at')
            ->paginate(15);

        return view('dosen.penilaian.index', compact('pengajuans'));
    }

    public function create($pengajuanId)
    {
        $pengajuan = $this->findPengajuanForDosen($pengajuanId);
        $penilaian = Penilaian::where('pengajuan_id', $pengajuanId)->first();
        [$canInput, $lockMessage] = $this->inputStatus($pengajuan);

        return view('dosen.penilaian.create', compact('pengajuan', 'penilaian', 'canInput', 'lockMessage'));
    }

    public function store(Request $request, $pengajuanId)
    {
        $pengajuan = $this->findPengajuanForDosen($pengajuanId);

        [$canInput, $lockMessage] = $this->inputStatus($pengajuan);
        if (!$canInput) {
            return redirect()->route('dosen.penilaian.create', $pengajuanId)
                ->with('error', $lockMessage);
        }

        $request->validate($this->rules(), $this->validationMessages());
        $existing = Penilaian::where('pengajuan_id', $pengajuanId)->first();
        $filePenilaian = $existing?->file_penilaian_formal_dosen;
        if ($request->hasFile('file_penilaian_formal')) {
            if ($filePenilaian) $this->deletePublicFileIfExists($filePenilaian);
            $filePenilaian = $request->file('file_penilaian_formal')->store('documents/penilaian/dosen', 'public');
        }

        $data = [
            ...$this->stageOnePayload($request, 'dosen'),
            'catatan_dosen' => $request->catatan_dosen,
            'catatan' => $request->catatan_dosen,
            'file_penilaian_formal_dosen' => $filePenilaian,
        ];

        if ($pengajuan->status_seminar === 'selesai') {
            $data = [
                ...$data,
                ...$this->seminarPayload($request, $existing, 'dosen'),
                'nama_penguji' => $request->nama_penguji ?: $existing?->nama_penguji,
                'catatan_seminar' => $request->catatan_seminar ?: $existing?->catatan_seminar,
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
                'judul' => 'Nilai Dosen Pembimbing Tersimpan',
                'pesan' => 'Nilai dari dosen pembimbing telah diperbarui. Nilai akhir tampil setelah nilai pembimbing lapangan juga lengkap.',
                'status' => 'belum',
                'target_url' => route('mahasiswa.penilaian.show', $pengajuan->id),
            ]);
        }

        if ($filePenilaian) {
            Dokumen::updateOrCreate(
                ['pengajuan_id' => $pengajuan->id, 'jenis_dokumen' => 'file_penilaian_formal_dosen'],
                [
                    'file_path' => $filePenilaian,
                    'tanggal_upload' => now()->toDateString(),
                    'status_verifikasi' => 'valid',
                    'catatan' => 'File penilaian formal dosen pembimbing',
                ]
            );
        }

        return redirect()->route('dosen.penilaian.index')->with('success', 'Nilai dosen pembimbing berhasil disimpan.');
    }

    public function file($pengajuanId)
    {
        $pengajuan = $this->findPengajuanForDosen($pengajuanId);
        $path = $pengajuan->penilaian?->file_penilaian_formal_dosen;
        return $this->publicInlineResponse($path, basename($this->normalizePublicPath($path) ?: $path));
    }

    private function rules(): array
    {
        $rules = [
            'file_penilaian_formal' => 'nullable|file|mimes:pdf|max:10240',
            'catatan_dosen' => 'nullable|string|max:1000',
            'nama_penguji' => 'nullable|string|max:255',
            'catatan_seminar' => 'nullable|string|max:1000',
        ];

        foreach (array_keys(Penilaian::tahap1Fields('dosen')) as $field) {
            $rules[$field] = 'required|numeric|min:1|max:100';
        }

        foreach (array_keys(Penilaian::laporanRubrik('dosen') + Penilaian::presentasiRubrik('dosen')) as $field) {
            $rules[$field] = 'nullable|numeric|min:50|max:100';
        }

        return $rules;
    }

    private function validationMessages(): array
    {
        $messages = [];

        foreach (array_keys(Penilaian::tahap1Fields('dosen')) as $field) {
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

    private function findPengajuanForDosen($pengajuanId): PengajuanMagang
    {
        $dosenId = auth()->user()->dosen?->id;
        abort_unless(Bimbingan::where('dosen_id', $dosenId)
            ->where('pengajuan_id', $pengajuanId)
            ->whereHas('pengajuan', fn($q) => $q
                ->where('jenis_pengajuan', 'surat_keterangan')
                ->whereIn('status_pengajuan', ['berjalan', 'selesai']))
            ->exists(), 403);

        return PengajuanMagang::with(['mahasiswa.prodi', 'mahasiswa.angkatanMaster', 'mitra', 'periode', 'penilaian', 'kelayakanSeminar', 'logbooks', 'bimbinganFormals'])->findOrFail($pengajuanId);
    }

    private function inputStatus(PengajuanMagang $pengajuan): array
    {
        return [true, ''];
    }
}
