<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ImportsTabularData;
use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\Dokumen;
use App\Models\Dosen;
use App\Models\MahasiswaProfile;
use App\Models\Mitra;
use App\Models\Notifikasi;
use App\Models\PembimbingLapangan;
use App\Models\PengajuanMagang;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SkKolektifController extends Controller
{
    use ImportsTabularData;

    public function index()
    {
        $mahasiswas = MahasiswaProfile::with(['user', 'prodi', 'angkatanMaster'])->orderBy('nama_lengkap')->get();
        $periodes = Periode::orderByDesc('created_at')->get();

        return view('admin.sk-kolektif.index', compact('mahasiswas', 'periodes'));
    }

    public function penempatan()
    {
        $periodes = Periode::orderByDesc('created_at')->get();

        return view('admin.sk-kolektif.penempatan', compact('periodes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file_sk' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'mahasiswa_ids' => 'nullable|array',
            'mahasiswa_ids.*' => 'exists:mahasiswa_profiles,id',
            'select_all' => 'nullable|boolean',
            'periode_id' => 'nullable|exists:periodes,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $mahasiswaIds = $request->boolean('select_all')
            ? MahasiswaProfile::with('angkatanMaster')->get()->filter->isAngkatanKhususSkKolektif()->pluck('id')->all()
            : array_filter($request->input('mahasiswa_ids', []));

        if (empty($mahasiswaIds)) {
            return back()->withInput()->with('error', 'Pilih minimal satu mahasiswa atau gunakan pilihan semua mahasiswa.');
        }

        $path = $request->file('file_sk')->store('documents/sk-kolektif', 'public');
        $created = 0;
        $skipped = [];

        foreach (MahasiswaProfile::with('angkatanMaster')->whereIn('id', $mahasiswaIds)->get() as $mahasiswa) {
            $pengajuan = $mahasiswa->isAngkatanKhususSkKolektif()
                ? $this->ensurePengajuanBerjalan($mahasiswa, $request->periode_id, $request->tanggal_mulai, $request->tanggal_selesai)
                : $mahasiswa->pengajuans()
                    ->where('jenis_pengajuan', 'surat_keterangan')
                    ->whereIn('status_pengajuan', ['disetujui', 'berjalan', 'selesai'])
                    ->latest('updated_at')
                    ->first();

            if (!$pengajuan) {
                $skipped[] = ($mahasiswa->nim ?? '-') . ' dilewati karena bukan angkatan khusus dan belum punya pengajuan SK Magang aktif.';
                continue;
            }

            Dokumen::updateOrCreate(
                [
                    'pengajuan_id' => $pengajuan->id,
                    'jenis_dokumen' => 'sk_magang',
                ],
                [
                    'file_path' => $path,
                    'tanggal_upload' => now()->toDateString(),
                    'status_verifikasi' => 'valid',
                    'catatan' => 'SK Magang Kolektif',
                ]
            );

            if ($mahasiswa->user) {
                Notifikasi::create([
                    'user_id' => $mahasiswa->user->id,
                    'judul' => 'SK Magang Diterbitkan',
                    'pesan' => 'SK Magang Anda telah diterbitkan secara kolektif.',
                    'status' => 'belum',
                    'target_url' => route('mahasiswa.dokumen.index', $pengajuan->id),
                ]);
            }

            $created++;
        }

        $message = 'SK Magang berhasil diterbitkan untuk ' . $created . ' mahasiswa.';
        if ($skipped) {
            $message .= ' Catatan: ' . implode(' ', array_slice($skipped, 0, 5));
            if (count($skipped) > 5) $message .= ' Dan ' . (count($skipped) - 5) . ' catatan lain.';
        }

        return back()->with('success', $message);
    }

    public function templatePenugasan()
    {
        return $this->csvResponse('template_penempatan_magang.csv', $this->penugasanHeaders(), []);
    }

    public function templatePenugasanXlsx()
    {
        return $this->xlsxResponse('template_penempatan_magang.xlsx', $this->penugasanHeaders(), []);
    }

    public function importPenugasan(Request $request)
    {
        $request->validate([
            'file_penugasan' => 'required|file|mimes:csv,txt,xlsx|max:10240',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'periode_id' => 'nullable|exists:periodes,id',
        ]);

        $rows = $this->readRows($request->file('file_penugasan')->getRealPath());
        if (empty($rows)) {
            return back()->with('error', 'File import tidak memiliki data atau header tidak terbaca.');
        }

        $updated = 0;
        $notes = [];
        $newDosenAccounts = [];
        $newPembimbingAccounts = [];

        foreach ($rows as $index => $data) {
            $line = $index + 2;
            $nim = trim($data['nim'] ?? '');
            if ($nim === '') {
                $notes[] = "Baris {$line}: dilewati karena NIM kosong.";
                continue;
            }

            $mahasiswa = MahasiswaProfile::where('nim', $nim)->first();
            if (!$mahasiswa) {
                $notes[] = "Baris {$line}: NIM {$nim} tidak ditemukan di Master Data Mahasiswa.";
                continue;
            }

            $mitra = null;
            $namaMitra = trim($data['nama_instansi'] ?? $data['nama_mitra'] ?? '');
            if ($namaMitra !== '') {
                $mitra = Mitra::firstOrCreate(
                    ['nama_instansi' => $namaMitra],
                    [
                        'kota' => $data['kota_mitra'] ?? null,
                        'jenis_mitra' => 'non_mou',
                        'status_mitra' => 'terdaftar',
                        'status_mitra_detail' => 'aktif',
                        'status_mou' => 'tidak',
                    ]
                );
                if ($mitra->status_mitra_detail === 'menunggu_verifikasi') {
                    $mitra->update(['status_mitra' => 'terdaftar', 'status_mitra_detail' => 'aktif']);
                }
            }

            $periodeId = $request->periode_id ?: $this->resolvePeriodeId($data['periode_magang'] ?? null);
            $defaultStart = $mahasiswa->defaultTanggalMulaiMagang();
            $defaultEnd = $mahasiswa->defaultTanggalSelesaiMagang();
            $tanggalMulai = ($data['tanggal_mulai_magang'] ?? null) ?: ($request->tanggal_mulai ?: $defaultStart);
            $tanggalSelesai = ($data['tanggal_selesai_magang'] ?? null) ?: ($request->tanggal_selesai ?: $defaultEnd);
            $pengajuan = $this->ensurePengajuanBerjalan($mahasiswa, $periodeId, $tanggalMulai, $tanggalSelesai);
            if ($mitra) {
                $pengajuan->update(['mitra_id' => $mitra->id]);
            }

            $dosen = $this->findOrCreateDosenData($data, $newDosenAccounts);
            if ($dosen) {
                Bimbingan::updateOrCreate(
                    ['pengajuan_id' => $pengajuan->id, 'dosen_id' => $dosen->id],
                    ['tanggal_penugasan' => now()->toDateString(), 'status' => 'aktif']
                );
                if ($dosen->user) {
                    Notifikasi::create([
                        'user_id' => $dosen->user->id,
                        'judul' => 'Mahasiswa Bimbingan Baru',
                        'pesan' => 'Mahasiswa ' . ($mahasiswa->nama_lengkap ?? $nim) . ' telah ditugaskan kepada Anda.',
                        'status' => 'belum',
                        'target_url' => route('dosen.bimbingan.show', $pengajuan->id),
                    ]);
                }
            } else {
                $notes[] = "Baris {$line}: dosen untuk NIM {$nim} belum ditemukan/terhubung.";
            }

            $pembimbing = $this->findOrCreatePembimbing($data, $mitra, $newPembimbingAccounts);
            if ($pembimbing) {
                $pengajuan->update(['pembimbing_lapangan_id' => $pembimbing->id]);
                if ($pembimbing->user) {
                    Notifikasi::create([
                        'user_id' => $pembimbing->user->id,
                        'judul' => 'Mahasiswa Bimbingan Baru',
                        'pesan' => 'Mahasiswa ' . ($mahasiswa->nama_lengkap ?? $nim) . ' telah terhubung sebagai bimbingan lapangan Anda.',
                        'status' => 'belum',
                        'target_url' => route('pembimbing.mahasiswa.show', $pengajuan->id),
                    ]);
                }
            }

            $updated++;
        }

        $message = 'Import penugasan selesai.';
        $summary = [
            'processed' => $updated,
            'dosen' => $newDosenAccounts,
            'pembimbing' => $newPembimbingAccounts,
            'notes' => $notes,
        ];
        if ($newDosenAccounts || $newPembimbingAccounts) {
            $message .= ' Password sementara hanya ditampilkan sekali pada hasil import. User dapat mengubah password melalui menu Profile.';
        }
        if ($notes) {
            $message .= ' Catatan: ' . implode(' ', array_slice($notes, 0, 8));
            if (count($notes) > 8) $message .= ' Dan ' . (count($notes) - 8) . ' catatan lain.';
        }

        return back()->with('success', $message)->with('import_summary', $summary);
    }

    private function penugasanHeaders(): array
    {
        return [
            'nim',
            'nama_instansi',
            'nama_dosen_pembimbing',
            'email_dosen_pembimbing',
            'nama_pembimbing_lapangan',
            'email_pembimbing_lapangan',
            'tanggal_mulai_magang',
            'tanggal_selesai_magang',
        ];
    }

    private function ensurePengajuanBerjalan(MahasiswaProfile $mahasiswa, $periodeId, $tanggalMulai = null, $tanggalSelesai = null): PengajuanMagang
    {
        $pengajuan = PengajuanMagang::firstOrCreate(
            [
                'mahasiswa_id' => $mahasiswa->id,
                'jenis_pengajuan' => 'surat_keterangan',
                'periode_id' => $periodeId,
            ],
            [
                'jenis_magang' => 'mitra',
                'jenis_mitra' => 'kolektif',
                'posisi_magang' => 'Magang',
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai,
                'durasi' => ($tanggalMulai && $tanggalSelesai) ? now()->parse($tanggalMulai)->diffInDays(now()->parse($tanggalSelesai)) + 1 : null,
                'status_pengajuan' => 'berjalan',
                'status_surat_pengantar' => 'valid',
                'status_surat_keterangan' => 'valid',
                'status_laporan' => 'belum_ada',
                'status_seminar' => 'belum',
            ]
        );

        $updates = [
            'status_pengajuan' => 'berjalan',
            'status_surat_keterangan' => 'valid',
        ];
        if ($periodeId) $updates['periode_id'] = $periodeId;
        if ($tanggalMulai) $updates['tanggal_mulai'] = $tanggalMulai;
        if ($tanggalSelesai) $updates['tanggal_selesai'] = $tanggalSelesai;
        if ($tanggalMulai && $tanggalSelesai) $updates['durasi'] = now()->parse($tanggalMulai)->diffInDays(now()->parse($tanggalSelesai)) + 1;
        $pengajuan->update($updates);

        return $pengajuan;
    }

    private function findDosen(array $data): ?Dosen
    {
        $email = trim($data['email_dosen_pembimbing'] ?? $data['email_dosen'] ?? '');
        if ($email !== '') {
            $dosen = Dosen::where('email_dosen', $email)->first();
            if ($dosen) return $dosen;
        }
        if (!empty($data['nidn_nip_dosen'])) {
            $dosen = Dosen::where('nidn', trim($data['nidn_nip_dosen']))->first();
            if ($dosen) return $dosen;
        }
        $nama = trim($data['nama_dosen_pembimbing'] ?? $data['nama_dosen'] ?? '');
        if ($nama !== '') {
            return Dosen::where('nama_dosen', $nama)->first();
        }
        return null;
    }

    private function findOrCreateDosenData(array $data, array &$newAccounts = []): ?Dosen
    {
        $dosen = $this->findDosen($data);
        $email = trim($data['email_dosen_pembimbing'] ?? $data['email_dosen'] ?? '');
        $nama = trim($data['nama_dosen_pembimbing'] ?? $data['nama_dosen'] ?? '');
        if ($dosen) {
            if (!$dosen->user_id && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $user = $this->findOrCreateLoginUser($email, $nama ?: $dosen->nama_dosen, 'dosen', $newAccounts);
                if (!$user) return $dosen;
                $dosen->update([
                    'user_id' => $user->id,
                    'email_dosen' => $dosen->email_dosen ?: $email,
                    'nama_dosen' => $dosen->nama_dosen ?: ($nama ?: $user->name),
                ]);
            }
            return $dosen;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $user = $this->findOrCreateLoginUser($email, $nama ?: $email, 'dosen', $newAccounts);
        if (!$user) return null;

        return Dosen::updateOrCreate(
            ['email_dosen' => $email],
            [
                'user_id' => $user->id,
                'nama_dosen' => $nama ?: ($user->name ?? $email),
                'status_dosen' => 'aktif',
                'profile_status' => 'belum_lengkap',
            ]
        );
    }

    private function resolvePeriodeId(?string $periodeName): ?int
    {
        $periodeName = trim((string) $periodeName);
        if ($periodeName === '') return null;

        return Periode::where('nama_periode', $periodeName)
            ->orWhere('nama', $periodeName)
            ->value('id');
    }

    private function findOrCreatePembimbing(array $data, ?Mitra $mitra, array &$newAccounts = []): ?PembimbingLapangan
    {
        $email = trim($data['email_pembimbing_lapangan'] ?? '');
        $nama = trim($data['nama_pembimbing_lapangan'] ?? '');
        if ($email === '' && $nama === '') return null;

        $user = null;
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $user = $this->findOrCreateLoginUser($email, $nama ?: $email, 'pembimbing_lapangan', $newAccounts);
            if (!$user) return null;
        }

        return PembimbingLapangan::updateOrCreate(
            $email !== '' ? ['email' => $email] : ['nama' => $nama, 'mitra_id' => $mitra?->id],
            [
                'user_id' => $user?->id,
                'mitra_id' => $mitra?->id,
                'nama' => $nama ?: ($user->name ?? '-'),
                'jabatan' => $data['jabatan_pembimbing_lapangan'] ?? null,
                'email' => $email ?: null,
                'no_hp' => $data['no_hp_pembimbing_lapangan'] ?? null,
                'instansi' => $mitra?->nama_instansi ?? ($data['nama_instansi'] ?? $data['nama_mitra'] ?? null),
                'status' => 'aktif',
            ]
        );
    }

    private function findOrCreateLoginUser(string $email, string $name, string $role, array &$newAccounts): ?User
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            return $user->role === $role ? $user : null;
        }

        $temporaryPassword = Str::random(10);
        $user = User::create([
            'name' => $name ?: $email,
            'email' => $email,
            'password' => Hash::make($temporaryPassword),
            'role' => $role,
            'status' => 'aktif',
        ]);

        $newAccounts[] = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $temporaryPassword,
        ];

        return $user;
    }

    private function headerMap(array $header): array
    {
        $aliases = [
            'nim' => ['nim'],
            'email_dosen' => ['emaildosen'],
            'email_dosen_pembimbing' => ['emaildosenpembimbing', 'emaildosen'],
            'nama_dosen' => ['namadosen', 'dosen'],
            'nama_dosen_pembimbing' => ['namadosenpembimbing', 'namadosen', 'dosen'],
            'nidn_nip_dosen' => ['nidnnipdosen', 'nidn', 'nip', 'nidnnip'],
            'nama_pembimbing_lapangan' => ['namapembimbinglapangan', 'pembimbinglapangan'],
            'email_pembimbing_lapangan' => ['emailpembimbinglapangan'],
            'no_hp_pembimbing_lapangan' => ['nohppembimbinglapangan', 'nohppembimbing', 'telppembimbing'],
            'jabatan_pembimbing_lapangan' => ['jabatanpembimbinglapangan'],
            'nama_mitra' => ['namamitra', 'mitra', 'namainstansi', 'instansi', 'tempatmagang'],
            'nama_instansi' => ['namainstansi', 'instansi', 'tempatmagang'],
            'kota_mitra' => ['kotamitra', 'kota'],
            'periode_magang' => ['periodemagang', 'periode'],
            'tanggal_mulai_magang' => ['tanggalmulaimagang', 'tanggalmulai', 'mulai'],
            'tanggal_selesai_magang' => ['tanggalselesaimagang', 'tanggalselesai', 'selesai'],
        ];
        $map = [];
        foreach ($header as $index => $name) {
            $key = preg_replace('/[^a-z0-9]/', '', strtolower(trim((string) $name)));
            foreach ($aliases as $field => $items) {
                if (in_array($key, $items, true)) {
                    $map[$field] = $index;
                    break;
                }
            }
        }
        return $map;
    }

    private function rowData(array $map, array $row): array
    {
        $data = [];
        foreach ($map as $field => $index) {
            $data[$field] = isset($row[$index]) ? trim((string) $row[$index]) : null;
        }
        return $data;
    }
}
