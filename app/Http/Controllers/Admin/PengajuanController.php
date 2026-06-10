<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengajuanMagang;
use App\Models\Dosen;
use App\Models\Mitra;
use App\Models\Bimbingan;
use App\Models\StatusHistory;
use App\Models\Dokumen;
use App\Models\Notifikasi;
use App\Models\User;
use App\Models\PembimbingLapangan;
use App\Mail\PembimbingLapanganAccountMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class PengajuanController extends Controller
{
    public function index()
    {
        $pengajuans = PengajuanMagang::with(['mahasiswa', 'periode', 'mitra'])->latest()->paginate(15);
        return view('admin.pengajuan.index', compact('pengajuans'));
    }

    public function show($id)
    {
        $pengajuan = PengajuanMagang::with([
            'mahasiswa.user', 'periode', 'mitra', 'pembimbingLapangan.user', 'dokumens', 'bimbingans.dosen',
            'pengajuanAwal.periode', 'pengajuanAwal.mitra', 'pengajuanAwal.dokumens'
        ])->findOrFail($id);
        $dosens = Dosen::with('user')->where('status_dosen', 'aktif')->get();
        $mitras = Mitra::orderBy('nama_instansi')->get();
        return view('admin.pengajuan.show', compact('pengajuan', 'dosens', 'mitras'));
    }

    public function destroy($id)
    {
        $pengajuan = PengajuanMagang::withCount(['dokumens', 'logbooks', 'bimbingans', 'bimbinganFormals'])->with('penilaian')->findOrFail($id);

        $hasSeminarHistory = !in_array($pengajuan->status_seminar, [null, 'belum'], true)
            || $pengajuan->seminar_tanggal
            || $pengajuan->seminar_jam
            || $pengajuan->seminar_ruangan;

        if ($pengajuan->dokumens_count > 0 || $pengajuan->logbooks_count > 0 || $pengajuan->bimbingans_count > 0 || $pengajuan->bimbingan_formals_count > 0 || $pengajuan->penilaian || $hasSeminarHistory) {
            return redirect()->back()->with('error', 'Pengajuan tidak dapat dihapus karena sudah memiliki riwayat dokumen/logbook/bimbingan/seminar/nilai. Gunakan status ditolak atau dibatalkan sesuai alur.');
        }

        $pengajuan->delete();

        return redirect()->route('admin.pengajuan.index')->with('success', 'Pengajuan berhasil dihapus.');
    }
    public function updateStatus(Request $request, $id)
    {
        $pengajuan = PengajuanMagang::with(['mitra', 'bimbingans.dosen'])->findOrFail($id);
        $hasStoredAdminFile = function (string $jenisDokumen) use ($pengajuan): bool {
            return Dokumen::where('pengajuan_id', $pengajuan->id)
                ->where('jenis_dokumen', $jenisDokumen)
                ->whereNotNull('file_path')
                ->get()
                ->contains(fn($dokumen) => $dokumen->file_path && Storage::disk('public')->exists($dokumen->file_path));
        };
        $needsSuratPengantarFile = $pengajuan->jenis_pengajuan === 'surat_pengantar'
            && $request->input('status') === 'selesai'
            && !$hasStoredAdminFile('surat_pengantar');
        $needsSuratKeteranganFile = $pengajuan->jenis_pengajuan === 'surat_keterangan'
            && $request->input('status') === 'berjalan'
            && !$hasStoredAdminFile('surat_keterangan_magang');
        $needsSkMagangFile = $pengajuan->jenis_pengajuan === 'surat_keterangan'
            && $request->input('status') === 'selesai'
            && !$hasStoredAdminFile('sk_magang');
        $allowedStatuses = $pengajuan->jenis_pengajuan === 'surat_pengantar'
            ? ['pending', 'disetujui', 'revisi', 'ditolak', 'selesai']
            : ['pending', 'disetujui', 'revisi', 'ditolak', 'berjalan', 'selesai'];

        $request->validate([
            'status' => ['required', Rule::in($allowedStatuses)],
            'catatan' => 'nullable|string|max:500',
            'verify_mitra_status' => 'nullable|in:aktif,nonaktif,menunggu_verifikasi',
            'mitra_id' => 'nullable|exists:mitras,id',
            'surat_pengantar_file' => [Rule::requiredIf($needsSuratPengantarFile), 'nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'surat_keterangan_file' => [Rule::requiredIf($needsSuratKeteranganFile), 'nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'sk_magang_file' => [Rule::requiredIf($needsSkMagangFile), 'nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        if ($pengajuan->jenis_pengajuan === 'surat_keterangan' && ($request->input('status') === 'berjalan' || $request->hasFile('surat_keterangan_file'))) {
            $hasDosenPembimbing = $pengajuan->bimbingans()->exists();
            if (!$hasDosenPembimbing) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Pilih dosen pembimbing terlebih dahulu sebelum menerbitkan Surat Keterangan Magang.');
            }

            if (!$pengajuan->mitra_id && !$request->filled('mitra_id')) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Pilih atau hubungkan mitra terlebih dahulu sebelum menerbitkan Surat Keterangan Magang.');
            }

            if ($request->filled('mitra_id')) {
                $pengajuan->mitra_id = $request->mitra_id;
                $pengajuan->save();
                $pengajuan->load('mitra');
            }

            $pembimbingResult = $this->ensurePembimbingLapanganAccount($pengajuan);
            if (!$pengajuan->fresh()->pembimbing_lapangan_id) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $pembimbingResult['message'] ?? 'Pembimbing Lapangan belum dapat dihubungkan. Lengkapi nama dan email pembimbing lapangan pada Pengajuan SK Magang.');
            }
        }

        if ($pengajuan->jenis_pengajuan === 'surat_keterangan' && $request->filled('mitra_id')) {
            $pengajuan->mitra_id = $request->mitra_id;
            $pengajuan->save();
            $pengajuan->load('mitra');
        }

        $statusPengajuan = $request->status;
        $pengajuan->update([
            'status_pengajuan' => $statusPengajuan,
            'catatan_admin'    => $request->catatan,
        ]);

        if ($pengajuan->jenis_pengajuan !== 'surat_pengantar' && $pengajuan->mitra && $request->filled('verify_mitra_status')) {
            $pengajuan->mitra->update(['status_mitra_detail' => $request->verify_mitra_status]);
        }

        if ($pengajuan->jenis_pengajuan === 'surat_keterangan' && $request->input('status') === 'disetujui') {
            $this->activateMitraAfterAcceptance($pengajuan);
            $pembimbingResult = $this->ensurePembimbingLapanganAccount($pengajuan);
            $accountMessage = $pembimbingResult['message'] ?? null;
        }

        if ($pengajuan->jenis_pengajuan === 'surat_pengantar' && $request->hasFile('surat_pengantar_file')) {
            $this->storeSuratPengantarUpload($pengajuan, $request);
        }

        if ($pengajuan->jenis_pengajuan === 'surat_keterangan') {
            if ($request->hasFile('surat_keterangan_file')) {
                $this->storeAdminUpload($pengajuan, $request, 'surat_keterangan_file', 'surat_keterangan_magang', 'surat/surat-keterangan');
                $pengajuan->update(['status_surat_keterangan' => 'valid']);
                $this->activateMitraAfterAcceptance($pengajuan);
                if ($statusPengajuan !== 'selesai') {
                    $statusPengajuan = 'berjalan';
                }
                $this->notifyBimbinganAndDocument($pengajuan, 'Surat Keterangan Magang diterbitkan', 'Surat Keterangan Magang telah diterbitkan. Masa magang aktif dimulai.');
            }

            if ($request->hasFile('sk_magang_file')) {
                $this->storeAdminUpload($pengajuan, $request, 'sk_magang_file', 'sk_magang', 'surat/sk-magang');
                $statusPengajuan = 'selesai';
                $this->notifyMahasiswa($pengajuan, 'SK Magang diterbitkan', 'SK Magang telah diterbitkan oleh admin.', route('mahasiswa.dokumen.index', $pengajuan->id));
            }

            if (in_array($statusPengajuan, ['berjalan', 'selesai'], true)) {
                $pengajuan->load('mitra');
                $this->activateMitraAfterAcceptance($pengajuan);
            }

            if ($statusPengajuan !== $pengajuan->status_pengajuan) {
                $pengajuan->update(['status_pengajuan' => $statusPengajuan]);
            }
        }

        StatusHistory::create([
            'pengajuan_id' => $id,
            'status'       => $statusPengajuan,
            'keterangan'   => $request->catatan,
            'updated_by'   => auth()->id(),
        ]);

        // notify mahasiswa about status change
        $pengajuan = PengajuanMagang::findOrFail($id);
        if ($pengajuan->mahasiswa && $pengajuan->mahasiswa->user) {
            Notifikasi::create([
                'user_id' => $pengajuan->mahasiswa->user->id,
                'judul' => 'Perubahan Status Pengajuan',
                'pesan' => 'Status pengajuan Anda diubah menjadi: ' . $statusPengajuan,
                'status' => 'belum',
                'target_url' => route('mahasiswa.pengajuan.show', $pengajuan->id),
            ]);
        }

        $successMessage = 'Status berhasil diperbarui.';
        if (!empty($accountMessage)) {
            $successMessage .= ' ' . $accountMessage;
        }

        return redirect()->back()->with('success', $successMessage);
    }

    public function assignDosen(Request $request, $id)
    {
        $request->validate([
            'dosen_id' => 'required|exists:dosens,id',
        ]);

        $pengajuan = PengajuanMagang::with(['mahasiswa.user'])->findOrFail($id);
        abort_unless($pengajuan->jenis_pengajuan === 'surat_keterangan', 422, 'Dosen pembimbing hanya ditugaskan pada Pengajuan SK Magang.');

        Bimbingan::updateOrCreate(
            ['pengajuan_id' => $id],
            ['dosen_id' => $request->dosen_id, 'tanggal_penugasan' => now()->toDateString(), 'status' => 'aktif']
        );

        $dosen = Dosen::with('user')->find($request->dosen_id);
        if ($dosen?->user) {
            Notifikasi::create([
                'user_id' => $dosen->user->id,
                'judul' => 'Penugasan Dosen Pembimbing',
                'pesan' => 'Anda ditugaskan membimbing ' . ($pengajuan->mahasiswa->nama_lengkap ?? 'mahasiswa') . '.',
                'status' => 'belum',
                'target_url' => route('dosen.bimbingan.index'),
            ]);
        }
        $this->notifyMahasiswa($pengajuan, 'Dosen pembimbing ditugaskan', 'Dosen pembimbing Anda sudah ditugaskan.', route('mahasiswa.bimbingan.index'));

        return redirect()->back()->with('success', 'Dosen pembimbing berhasil ditugaskan.');
    }

    public function assignMitra(Request $request, $id)
    {
        $request->validate([
            'mitra_id' => 'required|exists:mitras,id',
        ]);

        $pengajuan = PengajuanMagang::findOrFail($id);
        abort_unless($pengajuan->jenis_pengajuan === 'surat_keterangan', 422, 'Mitra hanya dihubungkan pada Pengajuan SK Magang.');

        $pengajuan->update(['mitra_id' => $request->mitra_id]);
        $pengajuan->load('mahasiswa', 'mitra.mitraUsers.user', 'pembimbingLapangan.user');

        if ($pengajuan->mitra) {
            foreach ($pengajuan->mitra->mitraUsers as $mitraUser) {
                if ($mitraUser->user) {
                    Notifikasi::create([
                        'user_id' => $mitraUser->user->id,
                        'judul' => 'Mahasiswa Magang Terhubung',
                        'pesan' => ($pengajuan->mahasiswa->nama_lengkap ?? 'Mahasiswa') . ' telah dihubungkan ke instansi Anda.',
                        'status' => 'belum',
                        'target_url' => route('mitra.pengajuan.show', $pengajuan->id),
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Mitra berhasil dihubungkan ke pengajuan SK Magang.');
    }

    public function updateDokumenStatus(Request $request, $id)
    {
        $request->validate([
            'dokumen_id' => 'required|exists:dokumens,id',
            'status_verifikasi' => 'required|in:pending,valid,revisi',
            'catatan' => 'nullable|string|max:500',
        ]);

        $dokumen = Dokumen::where('pengajuan_id', $id)->findOrFail($request->dokumen_id);
        $dokumen->update([
            'status_verifikasi' => $request->status_verifikasi,
            'catatan' => $request->catatan,
        ]);

        $pengajuan = PengajuanMagang::findOrFail($id);
        if (in_array($dokumen->jenis_dokumen, ['surat_permohonan', 'surat_pengantar'], true)) {
            $pengajuan->update(['status_surat_pengantar' => $request->status_verifikasi]);
        }

        if ($dokumen->jenis_dokumen === 'surat_diterima') {
            $pengajuan->update(['status_surat_keterangan' => $request->status_verifikasi]);

            if ($request->status_verifikasi === 'valid') {
                $this->activateMitraAfterAcceptance($pengajuan);
            }
        }

        if ($dokumen->jenis_dokumen === 'laporan') {
            $pengajuan->update(['status_laporan' => $request->status_verifikasi]);
        }

        if ($pengajuan->mahasiswa && $pengajuan->mahasiswa->user) {
            Notifikasi::create([
                'user_id' => $pengajuan->mahasiswa->user->id,
                'judul' => 'Verifikasi Dokumen',
                'pesan' => 'Dokumen ' . str_replace('_', ' ', $dokumen->jenis_dokumen) . ' telah diberi status: ' . $request->status_verifikasi,
                'status' => 'belum',
                'target_url' => route('mahasiswa.dokumen.index', $pengajuan->id),
            ]);
        }

        return redirect()->back()->with('success', 'Status dokumen berhasil diperbarui.');
    }

    public function scheduleSeminar(Request $request, $id)
    {
        $request->validate([
            'judul_laporan' => 'required|string|max:255',
            'seminar_tanggal' => 'required|date|after_or_equal:' . now()->toDateString(),
            'seminar_jam' => 'required|string|max:20',
            'seminar_ruangan' => 'required|string|max:120',
            'status_seminar' => 'required|in:belum,terjadwal,selesai',
        ]);

        $pengajuan = PengajuanMagang::findOrFail($id);
        $pengajuan->update([
            'judul_laporan' => $request->judul_laporan,
            'seminar_tanggal' => $request->seminar_tanggal,
            'seminar_jam' => $request->seminar_jam,
            'seminar_ruangan' => $request->seminar_ruangan,
            'status_seminar' => $request->status_seminar,
        ]);

        return redirect()->back()->with('success', 'Jadwal seminar berhasil disimpan.');
    }

    public function previewDokumen(Dokumen $dokumen)
    {
        abort_if(!$dokumen->file_path || !Storage::disk('public')->exists($dokumen->file_path), 404);

        return $this->inlineFile($dokumen->file_path, $this->downloadName($dokumen));
    }

    public function downloadDokumen(Dokumen $dokumen)
    {
        abort_if(!$dokumen->file_path || !Storage::disk('public')->exists($dokumen->file_path), 404);

        return Storage::disk('public')->download($dokumen->file_path, $this->downloadName($dokumen));
    }

    public function destroyDokumen(Dokumen $dokumen)
    {
        $pengajuan = $dokumen->pengajuan;
        if ($dokumen->file_path) {
            Storage::disk('public')->delete($dokumen->file_path);
        }

        $jenis = $dokumen->jenis_dokumen;
        $dokumen->delete();

        if ($pengajuan) {
            if ($jenis === 'surat_pengantar') {
                $pengajuan->update(['status_surat_pengantar' => 'belum_ada']);
            }
            if ($jenis === 'surat_keterangan_magang') {
                $pengajuan->update(['status_surat_keterangan' => 'belum_ada']);
            }
            if ($jenis === 'laporan') {
                $pengajuan->update(['status_laporan' => 'belum_ada']);
            }
        }

        return redirect()->back()->with('success', 'Dokumen berhasil dihapus.');
    }
    private function storeSuratPengantarUpload(PengajuanMagang $pengajuan, Request $request): void
    {
        $this->storeAdminUpload($pengajuan, $request, 'surat_pengantar_file', 'surat_pengantar', 'surat/surat-pengantar');
        $pengajuan->update(['status_surat_pengantar' => 'valid']);
    }

    private function storeAdminUpload(PengajuanMagang $pengajuan, Request $request, string $field, string $jenisDokumen, string $directory): void
    {
        $existing = Dokumen::where('pengajuan_id', $pengajuan->id)
            ->where('jenis_dokumen', $jenisDokumen)
            ->first();

        if ($existing?->file_path) {
            Storage::disk('public')->delete($existing->file_path);
        }

        $path = $request->file($field)->store($directory, 'public');

        Dokumen::updateOrCreate(
            ['pengajuan_id' => $pengajuan->id, 'jenis_dokumen' => $jenisDokumen],
            [
                'file_path' => $path,
                'tanggal_upload' => now(),
                'status_verifikasi' => 'valid',
                'catatan' => $request->catatan,
            ]
        );
    }

    private function ensurePembimbingLapanganAccount(PengajuanMagang $pengajuan): array
    {
        $pengajuan->loadMissing(['mitra', 'mahasiswa.prodi', 'pembimbingLapangan.user']);
        $mitra = $pengajuan->mitra;
        if (!$mitra) {
            return ['message' => 'Pembimbing Lapangan belum dibuat karena mitra/instansi belum terhubung.'];
        }

        $nama = $pengajuan->pic_nama ?: $mitra->pembimbing_lapangan_nama;
        $email = $pengajuan->pic_email ?: $mitra->pembimbing_lapangan_email;
        $noHp = $pengajuan->pic_no_hp ?: $mitra->pembimbing_lapangan_kontak;
        $jabatan = $pengajuan->pic_jabatan ?: $mitra->pembimbing_lapangan_jabatan;

        if (!filled($nama) || !filled($email)) {
            return ['message' => 'Pembimbing Lapangan belum dibuat karena nama atau email pembimbing belum lengkap.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['message' => 'Email Pembimbing Lapangan tidak valid. Periksa kembali data pembimbing pada Pengajuan SK Magang.'];
        }

        $password = '12345678';
        $user = User::where('email', $email)->first();
        if ($user && $user->role !== 'pembimbing_lapangan') {
            return ['message' => 'Email Pembimbing Lapangan sudah digunakan oleh role lain. Silakan admin cek email: ' . $email . '.'];
        }

        $createdUser = false;
        if (!$user) {
            $user = User::create([
                'name' => $nama,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'pembimbing_lapangan',
                'status' => 'aktif',
            ]);
            $createdUser = true;
        } else {
            $user->update(['name' => $nama, 'status' => 'aktif']);
        }

        $pembimbing = PembimbingLapangan::firstOrNew(['email' => $email]);
        $pembimbing->fill([
            'user_id' => $user->id,
            'mitra_id' => $mitra->id,
            'pengajuan_id' => $pembimbing->pengajuan_id ?: $pengajuan->id,
            'nama' => $nama,
            'jabatan' => $jabatan,
            'email' => $email,
            'no_hp' => $noHp,
            'instansi' => $mitra->nama_instansi,
            'status' => 'aktif',
        ]);
        $pembimbing->syncProfileStatus();
        $pembimbing->save();

        $pengajuan->update(['pembimbing_lapangan_id' => $pembimbing->id]);
        $pengajuan->absensis()->whereNull('pembimbing_lapangan_id')->update(['pembimbing_lapangan_id' => $pembimbing->id]);

        Notifikasi::create([
            'user_id' => $user->id,
            'judul' => 'Mahasiswa Bimbingan Terhubung',
            'pesan' => 'Mahasiswa ' . ($pengajuan->mahasiswa->nama_lengkap ?? '-') . ' terhubung sebagai mahasiswa bimbingan lapangan Anda.',
            'status' => 'belum',
            'target_url' => route('pembimbing.mahasiswa.show', $pengajuan->id),
        ]);

        $emailMessage = '';
        try {
            Mail::to($email)->send(new PembimbingLapanganAccountMail($pembimbing, $pengajuan, $password));
            $pembimbing->update(['email_akses_terkirim' => true, 'last_email_sent_at' => now()]);
            $emailMessage = ' Email akses berhasil dikirim.';
        } catch (\Throwable $e) {
            $emailMessage = ' Akun dibuat/terhubung, tetapi email akses gagal dikirim. Cek konfigurasi MAIL di .env.';
        }

        return [
            'pembimbing' => $pembimbing,
            'message' => ($createdUser ? 'Akun Pembimbing Lapangan berhasil dibuat: ' : 'Akun Pembimbing Lapangan berhasil dihubungkan: ') . $email . ' dengan password awal 12345678.' . $emailMessage,
        ];
    }
    private function activateMitraAfterAcceptance(PengajuanMagang $pengajuan): void
    {
        if (!$pengajuan->mitra || $pengajuan->mitra->status_mitra_detail !== 'menunggu_verifikasi') {
            return;
        }

        $pengajuan->mitra->update([
            'status_mitra' => 'terdaftar',
            'status_mitra_detail' => 'aktif',
            'jenis_mitra' => $pengajuan->mitra->jenis_mitra ?: 'non_mou',
            'status_mou' => $pengajuan->mitra->status_mou ?: 'tidak',
        ]);
    }

    private function notifyMahasiswa(PengajuanMagang $pengajuan, string $judul, string $pesan, string $targetUrl): void
    {
        if ($pengajuan->mahasiswa?->user) {
            Notifikasi::create([
                'user_id' => $pengajuan->mahasiswa->user->id,
                'judul' => $judul,
                'pesan' => $pesan,
                'status' => 'belum',
                'target_url' => $targetUrl,
            ]);
        }
    }

    private function notifyBimbinganAndDocument(PengajuanMagang $pengajuan, string $judul, string $pesan): void
    {
        $this->notifyMahasiswa($pengajuan, $judul, $pesan, route('mahasiswa.dokumen.index', $pengajuan->id));
        $pengajuan->loadMissing('bimbingans.dosen.user', 'pembimbingLapangan.user');
        foreach ($pengajuan->bimbingans as $bimbingan) {
            if ($bimbingan->dosen?->user) {
                Notifikasi::create([
                    'user_id' => $bimbingan->dosen->user->id,
                    'judul' => $judul,
                    'pesan' => $pesan . ' Mahasiswa: ' . ($pengajuan->mahasiswa->nama_lengkap ?? '-'),
                    'status' => 'belum',
                    'target_url' => route('dosen.bimbingan.index'),
                ]);
            }
        }
        if ($pengajuan->pembimbingLapangan?->user) {
            Notifikasi::create([
                'user_id' => $pengajuan->pembimbingLapangan->user->id,
                'judul' => $judul,
                'pesan' => $pesan . ' Mahasiswa: ' . ($pengajuan->mahasiswa->nama_lengkap ?? '-'),
                'status' => 'belum',
                'target_url' => route('pembimbing.mahasiswa.show', $pengajuan->id),
            ]);
        }
    }

    private function downloadName(Dokumen $dokumen): string
    {
        $dokumen->loadMissing('pengajuan.mahasiswa');
        $labels = [
            'surat_diterima' => 'Surat Balasan Diterima Instansi',
            'proposal_magang' => 'Proposal Magang',
            'surat_pengantar' => 'Surat Pengantar Magang',
            'surat_keterangan_magang' => 'Surat Keterangan Magang',
            'sk_magang' => 'SK Magang',
            'laporan' => 'Laporan Magang',
            'surat_seminar' => 'Surat Seminar Magang',
        ];

        $label = $labels[$dokumen->jenis_dokumen] ?? str_replace('_', ' ', $dokumen->jenis_dokumen);
        $nama = $dokumen->pengajuan?->mahasiswa?->nama_lengkap ?: 'Mahasiswa';
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

    private function generateSuratKeterangan(PengajuanMagang $pengajuan): void
    {
        try {
            $pengajuan->loadMissing(['mahasiswa.prodi', 'mahasiswa.fakultas', 'mitra', 'periode']);
            $html = view('surat.surat_keterangan', compact('pengajuan'))->render();
            $pdf = \PDF::loadHTML($html)->setPaper('a4', 'portrait');
            $path = 'surat/surat_keterangan_' . $pengajuan->id . '.pdf';
            \Storage::disk('public')->put($path, $pdf->output());

            Dokumen::updateOrCreate(
                ['pengajuan_id' => $pengajuan->id, 'jenis_dokumen' => 'surat_keterangan_magang'],
                ['file_path' => $path, 'tanggal_upload' => now(), 'status_verifikasi' => 'valid']
            );

            $pengajuan->update(['status_surat_keterangan' => 'valid']);
        } catch (\Exception $e) {
            \Log::error('Surat Keterangan generation failed: ' . $e->getMessage());
        }
    }
}
