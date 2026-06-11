<?php

namespace App\Http\Controllers\Admin;

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

class SkKolektifController extends Controller
{
    public function index()
    {
        $mahasiswas = MahasiswaProfile::with(['user', 'prodi'])->orderBy('nama_lengkap')->get();
        $periodes = Periode::orderByDesc('created_at')->get();

        return view('admin.sk-kolektif.index', compact('mahasiswas', 'periodes'));
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
            ? MahasiswaProfile::pluck('id')->all()
            : array_filter($request->input('mahasiswa_ids', []));

        if (empty($mahasiswaIds)) {
            return back()->withInput()->with('error', 'Pilih minimal satu mahasiswa atau gunakan pilihan semua mahasiswa.');
        }

        $path = $request->file('file_sk')->store('documents/sk-kolektif', 'public');
        $created = 0;

        foreach (MahasiswaProfile::whereIn('id', $mahasiswaIds)->get() as $mahasiswa) {
            $pengajuan = $this->ensurePengajuanBerjalan($mahasiswa, $request->periode_id, $request->tanggal_mulai, $request->tanggal_selesai);

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

        return back()->with('success', 'SK Magang kolektif berhasil diterbitkan untuk ' . $created . ' mahasiswa.');
    }

    public function importPenugasan(Request $request)
    {
        $request->validate([
            'file_penugasan' => 'required|file|mimes:csv,txt|max:5120',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'periode_id' => 'nullable|exists:periodes,id',
        ]);

        $handle = fopen($request->file('file_penugasan')->getRealPath(), 'r');
        $header = fgetcsv($handle);
        if (!$header) {
            return back()->with('error', 'File CSV tidak memiliki header.');
        }

        $map = $this->headerMap($header);
        $updated = 0;
        $notes = [];

        while (($row = fgetcsv($handle)) !== false) {
            $data = $this->rowData($map, $row);
            $nim = trim($data['nim'] ?? '');
            if ($nim === '') {
                $notes[] = 'Baris dilewati karena NIM kosong.';
                continue;
            }

            $mahasiswa = MahasiswaProfile::where('nim', $nim)->first();
            if (!$mahasiswa) {
                $notes[] = 'NIM ' . $nim . ' tidak ditemukan di Master Data Mahasiswa.';
                continue;
            }

            $mitra = null;
            if (!empty($data['nama_mitra'])) {
                $mitra = Mitra::firstOrCreate(
                    ['nama_instansi' => trim($data['nama_mitra'])],
                    [
                        'kota' => $data['kota_mitra'] ?? null,
                        'jenis_mitra' => 'non_mou',
                        'status_mitra' => 'terdaftar',
                        'status_mitra_detail' => 'menunggu_verifikasi',
                        'status_mou' => 'tidak',
                    ]
                );
            }

            $pengajuan = $this->ensurePengajuanBerjalan($mahasiswa, $request->periode_id, $request->tanggal_mulai, $request->tanggal_selesai);
            if ($mitra) {
                $pengajuan->update(['mitra_id' => $mitra->id]);
            }

            $dosen = $this->findDosen($data);
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
                $notes[] = 'Dosen untuk NIM ' . $nim . ' belum ditemukan/terhubung.';
            }

            $pembimbing = $this->findOrCreatePembimbing($data, $mitra);
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
        fclose($handle);

        $message = 'Import penugasan selesai. Data diproses: ' . $updated . '.';
        if ($notes) {
            $message .= ' Catatan: ' . implode(' ', array_slice($notes, 0, 8));
            if (count($notes) > 8) $message .= ' Dan ' . (count($notes) - 8) . ' catatan lain.';
        }

        return back()->with('success', $message);
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
        if (!empty($data['email_dosen'])) {
            $dosen = Dosen::where('email_dosen', trim($data['email_dosen']))->first();
            if ($dosen) return $dosen;
        }
        if (!empty($data['nidn_nip_dosen'])) {
            return Dosen::where('nidn', trim($data['nidn_nip_dosen']))->first();
        }
        return null;
    }

    private function findOrCreatePembimbing(array $data, ?Mitra $mitra): ?PembimbingLapangan
    {
        $email = trim($data['email_pembimbing_lapangan'] ?? '');
        $nama = trim($data['nama_pembimbing_lapangan'] ?? '');
        if ($email === '' && $nama === '') return null;

        $user = null;
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = User::create([
                    'name' => $nama ?: $email,
                    'email' => $email,
                    'password' => Hash::make('12345678'),
                    'role' => 'pembimbing_lapangan',
                    'status' => 'aktif',
                ]);
            } elseif ($user->role !== 'pembimbing_lapangan') {
                return null;
            }
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
                'instansi' => $mitra?->nama_instansi ?? ($data['nama_mitra'] ?? null),
                'status' => 'aktif',
            ]
        );
    }

    private function headerMap(array $header): array
    {
        $aliases = [
            'nim' => ['nim'],
            'email_dosen' => ['emaildosen'],
            'nidn_nip_dosen' => ['nidnnipdosen', 'nidn', 'nip', 'nidnnip'],
            'nama_pembimbing_lapangan' => ['namapembimbinglapangan', 'pembimbinglapangan'],
            'email_pembimbing_lapangan' => ['emailpembimbinglapangan'],
            'no_hp_pembimbing_lapangan' => ['nohppembimbinglapangan', 'nohppembimbing', 'telppembimbing'],
            'jabatan_pembimbing_lapangan' => ['jabatanpembimbinglapangan'],
            'nama_mitra' => ['namamitra', 'mitra', 'namainstansi'],
            'kota_mitra' => ['kotamitra', 'kota'],
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