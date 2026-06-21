<?php
namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\PengajuanMagang;
use App\Models\Mitra;
use App\Models\Periode;
use App\Models\StatusHistory;
use App\Models\Notifikasi;
use App\Models\User;
use App\Models\Dokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PengajuanController extends Controller
{
    public function index()
    {
        $mahasiswa = auth()->user()->mahasiswaProfile;
        $mahasiswaId = $mahasiswa->id;
        $pengajuans  = PengajuanMagang::where('mahasiswa_id', $mahasiswaId)->with(['periode', 'mitra'])->latest()->get();
        if ($mahasiswa->isAngkatanKhususSkKolektif()) {
            $pengajuans = $pengajuans->reject(fn($pengajuan) => $pengajuan->isPenempatanKolektif());
        }
        return view('mahasiswa.pengajuan.index', compact('pengajuans'));
    }

    public function create()
    {
        $mahasiswa  = auth()->user()->mahasiswaProfile;
        $jenisPengajuan = request('jenis');
        $mitrasBerMou = Mitra::where('jenis_mitra', 'ber_mou')->where('status_mitra_detail', 'aktif')->get();
        $mitrasNonMou = Mitra::where('jenis_mitra', 'non_mou')->where('status_mitra_detail', 'aktif')->get();
        $periodes = Periode::where('status', 'aktif')->get();
        $eligibility = $this->checkEligibility($mahasiswa);
        $pengajuanDisetujui = PengajuanMagang::where('mahasiswa_id', $mahasiswa?->id)
            ->where('jenis_pengajuan', 'surat_pengantar')
            ->whereIn('status_pengajuan', ['disetujui', 'selesai'])
            ->where('status_surat_pengantar', 'valid')
            ->with(['periode', 'mitra'])
            ->latest()
            ->get();

        return view('mahasiswa.pengajuan.create', compact(
            'mitrasBerMou',
            'mitrasNonMou',
            'periodes',
            'eligibility',
            'jenisPengajuan',
            'pengajuanDisetujui'
        ));
    }

    public function store(Request $request)
    {
        if ($request->input('jenis_pengajuan') === 'surat_keterangan') {
            return $this->storeSuratKeterangan($request);
        }

        return $this->storeSuratPengantar($request);
    }

    private function storeSuratPengantar(Request $request)
    {
        $mahasiswa = auth()->user()->mahasiswaProfile;
        if ($mahasiswa?->isAngkatanKhususSkKolektif()) {
            return redirect()->route('mahasiswa.pengajuan.create')->with('error', 'Pengajuan Surat Pengantar Magang sedang dinonaktifkan untuk angkatan 2023 karena alur menggunakan SK Magang kolektif dari admin.');
        }
        $eligibility = $this->checkEligibility($mahasiswa, $request->periode_id);
        if (!$eligibility['allowed']) {
            return redirect()->back()->with('error', implode(' ', $eligibility['reasons']))->withInput();
        }

        $request->validate([
            'jenis_magang'               => 'required|in:mitra,mandiri',
            'jenis_mitra'                => 'required_if:jenis_magang,mitra|nullable|in:ber_mou,non_mou,baru',
            'mitra_id'                   => 'required_if:jenis_mitra,ber_mou,non_mou|nullable|exists:mitras,id',
            'nama_instansi_manual'       => 'required_if:jenis_mitra,baru|required_if:jenis_magang,mandiri|nullable|string|max:255',
            'alamat_instansi_manual'     => 'nullable|string',
            'kota_instansi_manual'       => 'nullable|string|max:100',
            'bidang_industri'            => 'nullable|string|max:120',
            'email_instansi'             => 'nullable|email|max:150',
            'no_telp_instansi'           => 'nullable|string|max:100',
            'kontak_instansi'            => 'nullable|string|max:100',
            'nama_pembimbing_lapangan'   => 'nullable|string|max:150',
            'jabatan_pembimbing_lapangan'=> 'nullable|string|max:150',
            'kontak_pembimbing_lapangan' => 'nullable|string|max:150',
            'email_pembimbing_lapangan' => 'nullable|email|max:150',
            'posisi_magang'              => 'required|string|max:255',
            'tanggal_mulai'              => 'required|date',
            'tanggal_selesai'            => 'required|date|after:tanggal_mulai',
            'periode_id'                 => 'required|exists:periodes,id',
        ]);

        $mitraId = null;
        $jenisMitra = null;

        if ($request->jenis_magang === 'mitra') {
            $jenisMitra = $request->jenis_mitra;
            if ($request->jenis_mitra === 'baru') {
                $mitra = Mitra::create([
                    'nama_instansi' => $request->nama_instansi_manual,
                    'alamat'        => $request->alamat_instansi_manual,
                    'kota'          => $request->kota_instansi_manual,
                    'bidang_industri' => $request->bidang_industri,
                    'email'         => $request->email_instansi,
                    'no_telp'       => $request->no_telp_instansi ?: $request->kontak_instansi,
                    'jenis_mitra'   => 'non_mou',
                    'status_mitra'  => 'terdaftar',
                    'status_mitra_detail' => 'menunggu_verifikasi',
                    'status_mou'    => 'tidak',
                    'pembimbing_lapangan_nama' => $request->nama_pembimbing_lapangan,
                    'pembimbing_lapangan_jabatan' => $request->jabatan_pembimbing_lapangan,
                    'pembimbing_lapangan_kontak' => $request->kontak_pembimbing_lapangan,
                ]);
                $mitraId = $mitra->id;

                    // notify admins that a new mitra needs verification
                    $admins = User::whereIn('role', ['admin','superadmin'])->get();
                    foreach ($admins as $a) {
                        Notifikasi::create([
                    'user_id' => $a->id,
                    'judul' => 'Mitra baru menunggu verifikasi',
                    'pesan' => 'Mitra ' . $mitra->nama_instansi . ' telah ditambahkan dan menunggu verifikasi.',
                    'status' => 'belum',
                ]);
                    }
            } else {
                $mitraId = $request->mitra_id;
            }
        } else {
            $jenisMitra = 'mandiri';
        }

        $pengajuan = PengajuanMagang::create([
            'mahasiswa_id'           => $mahasiswa->id,
            'jenis_pengajuan'         => 'surat_pengantar',
            'periode_id'             => $request->periode_id,
            'jenis_magang'           => $request->jenis_magang,
            'jenis_mitra'            => $jenisMitra,
            'mitra_id'               => $mitraId,
            'nama_instansi_manual'   => $request->nama_instansi_manual,
            'alamat_instansi_manual' => $request->alamat_instansi_manual,
            'kota_instansi_manual'   => $request->kota_instansi_manual,
            'posisi_magang'          => $request->posisi_magang,
            'deskripsi_kegiatan'     => $request->deskripsi_kegiatan,
            'tanggal_mulai'          => $request->tanggal_mulai,
            'tanggal_selesai'        => $request->tanggal_selesai,
            'durasi'                 => now()->parse($request->tanggal_mulai)->diffInDays(now()->parse($request->tanggal_selesai)) + 1,
            'status_pengajuan'       => 'pending',
            'status_surat_pengantar' => 'belum_ada',
            'status_surat_keterangan'=> 'belum_ada',
            'status_laporan'         => 'belum_ada',
            'status_seminar'         => 'belum',
        ]);

        // notify admins about new pengajuan
        $admins = User::whereIn('role', ['admin','superadmin'])->get();
        foreach ($admins as $a) {
            Notifikasi::create([
                'user_id' => $a->id,
                'judul' => 'Pengajuan Magang Baru',
                'pesan' => 'Mahasiswa ' . $mahasiswa->nama_lengkap . ' mengajukan Surat Pengantar/Rekomendasi Magang.',
                'status' => 'belum',
                'target_url' => route('admin.pengajuan.show', $pengajuan->id),
            ]);
        }

        return redirect()->route('mahasiswa.pengajuan.index')->with('success', 'Pengajuan berhasil dikirim!');
    }

    private function storeSuratKeterangan(Request $request)
    {
        $mahasiswa = auth()->user()->mahasiswaProfile;
        if ($mahasiswa?->isAngkatanKhususSkKolektif()) {
            return redirect()->route('mahasiswa.pengajuan.create')->with('error', 'Pengajuan SK Magang sedang dinonaktifkan untuk angkatan 2023 karena SK Magang diterbitkan secara kolektif oleh admin.');
        }
        $eligibility = $this->checkEligibility($mahasiswa);
        if (!$eligibility['allowed']) {
            return redirect()->back()->with('error', implode(' ', $eligibility['reasons']))->withInput();
        }

        $request->validate([
            'pengajuan_awal_id' => 'required|exists:pengajuan_magangs,id',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'proposal_magang' => 'required|file|mimes:pdf,doc,docx|max:5120',
            'nomor_surat_balasan' => 'nullable|string|max:120',
            'tanggal_surat_balasan' => 'nullable|date',
            'nama_pembimbing_lapangan' => 'nullable|string|max:150',
            'jabatan_pembimbing_lapangan' => 'nullable|string|max:150',
            'kontak_pembimbing_lapangan' => 'nullable|string|max:150',
            'email_pembimbing_lapangan' => 'nullable|email|max:150',
            'catatan_mahasiswa' => 'nullable|string|max:500',
        ]);

        $pengajuanAwal = PengajuanMagang::where('mahasiswa_id', auth()->user()->mahasiswaProfile?->id)
            ->where('jenis_pengajuan', 'surat_pengantar')
            ->whereIn('status_pengajuan', ['disetujui', 'selesai'])
            ->where('status_surat_pengantar', 'valid')
            ->findOrFail($request->pengajuan_awal_id);

        $pengajuan = PengajuanMagang::create([
            'mahasiswa_id' => $pengajuanAwal->mahasiswa_id,
            'pengajuan_awal_id' => $pengajuanAwal->id,
            'jenis_pengajuan' => 'surat_keterangan',
            'periode_id' => $pengajuanAwal->periode_id,
            'jenis_magang' => $pengajuanAwal->jenis_magang,
            'jenis_mitra' => $pengajuanAwal->jenis_mitra,
            'mitra_id' => $pengajuanAwal->mitra_id,
            'nama_instansi_manual' => $pengajuanAwal->nama_instansi_manual,
            'alamat_instansi_manual' => $pengajuanAwal->alamat_instansi_manual,
            'kota_instansi_manual' => $pengajuanAwal->kota_instansi_manual,
            'posisi_magang' => $pengajuanAwal->posisi_magang,
            'deskripsi_kegiatan' => $pengajuanAwal->deskripsi_kegiatan,
            'tanggal_mulai' => $pengajuanAwal->tanggal_mulai,
            'tanggal_selesai' => $pengajuanAwal->tanggal_selesai,
            'durasi' => $pengajuanAwal->durasi,
            'status_pengajuan' => 'pending',
            'status_surat_pengantar' => 'valid',
            'status_surat_keterangan' => 'pending',
            'status_laporan' => 'belum_ada',
            'status_seminar' => 'belum',
            'nomor_surat_balasan' => $request->nomor_surat_balasan,
            'tanggal_surat_balasan' => $request->tanggal_surat_balasan,
            'catatan_mahasiswa' => $request->catatan_mahasiswa,
            'pic_nama' => $request->nama_pembimbing_lapangan,
            'pic_jabatan' => $request->jabatan_pembimbing_lapangan,
            'pic_no_hp' => $request->kontak_pembimbing_lapangan,
                    'pic_email' => $request->email_pembimbing_lapangan,
]);

        $filePath = $request->file('file')->store('documents/surat-balasan', 'public');
        Dokumen::create([
            'pengajuan_id' => $pengajuan->id,
            'jenis_dokumen' => 'surat_diterima',
            'file_path' => $filePath,
            'tanggal_upload' => now()->toDateString(),
            'status_verifikasi' => 'pending',
            'catatan' => $request->catatan_mahasiswa,
        ]);
        Dokumen::create([
            'pengajuan_id' => $pengajuan->id,
            'jenis_dokumen' => 'proposal_magang',
            'file_path' => $request->file('proposal_magang')->store('documents/proposal', 'public'),
            'tanggal_upload' => now()->toDateString(),
            'status_verifikasi' => 'pending',
            'catatan' => $request->catatan_mahasiswa,
        ]);

        if ($pengajuan->mitra) {
            $pengajuan->mitra->update([
                'pembimbing_lapangan_nama' => $request->nama_pembimbing_lapangan ?: $pengajuan->mitra->pembimbing_lapangan_nama,
                'pembimbing_lapangan_jabatan' => $request->jabatan_pembimbing_lapangan ?: $pengajuan->mitra->pembimbing_lapangan_jabatan,
                'pembimbing_lapangan_kontak' => $request->kontak_pembimbing_lapangan ?: $pengajuan->mitra->pembimbing_lapangan_kontak,
                            'pembimbing_lapangan_email' => $request->email_pembimbing_lapangan ?: $pengajuan->mitra->pembimbing_lapangan_email,
]);
        }

        foreach (User::whereIn('role', ['admin','superadmin'])->get() as $admin) {
            Notifikasi::create([
                'user_id' => $admin->id,
                'judul' => 'Pengajuan SK Magang',
                'pesan' => 'Mahasiswa ' . ($pengajuan->mahasiswa->nama_lengkap ?? '-') . ' mengupload bukti diterima instansi.',
                'status' => 'belum',
                'target_url' => route('admin.pengajuan.show', $pengajuan->id),
            ]);
        }

        return redirect()->route('mahasiswa.pengajuan.index')->with('success', 'Pengajuan SK Magang berhasil dikirim.');
    }

    public function show($id)
    {
        $pengajuan = PengajuanMagang::with(['periode', 'mitra', 'dokumens', 'statusHistories', 'bimbingans.dosen'])
            ->where('mahasiswa_id', auth()->user()->mahasiswaProfile?->id)
            ->findOrFail($id);
        $missing = $this->findMissingWeeks($pengajuan);
        return view('mahasiswa.pengajuan.show', compact('pengajuan', 'missing'));
    }

    public function edit(PengajuanMagang $pengajuan)
    {
        $this->authorizeRevisiSuratPengantar($pengajuan);

        $mitrasBerMou = Mitra::where('jenis_mitra', 'ber_mou')->where('status_mitra_detail', 'aktif')->get();
        $mitrasNonMou = Mitra::where('jenis_mitra', 'non_mou')->where('status_mitra_detail', 'aktif')->get();
        $periodes = Periode::where('status', 'aktif')->get();

        return view('mahasiswa.pengajuan.edit', compact('pengajuan', 'mitrasBerMou', 'mitrasNonMou', 'periodes'));
    }

    public function update(Request $request, PengajuanMagang $pengajuan)
    {
        $this->authorizeRevisiSuratPengantar($pengajuan);

        $request->validate([
            'jenis_mitra'                => 'required|in:ber_mou,non_mou,baru',
            'mitra_id'                   => 'required_if:jenis_mitra,ber_mou,non_mou|nullable|exists:mitras,id',
            'nama_instansi_manual'       => 'required_if:jenis_mitra,baru|nullable|string|max:255',
            'alamat_instansi_manual'     => 'nullable|string',
            'kota_instansi_manual'       => 'nullable|string|max:100',
            'bidang_industri'            => 'nullable|string|max:120',
            'email_instansi'             => 'nullable|email|max:150',
            'no_telp_instansi'           => 'nullable|string|max:100',
            'kontak_instansi'            => 'nullable|string|max:100',
            'posisi_magang'              => 'required|string|max:255',
            'tanggal_mulai'              => 'required|date',
            'tanggal_selesai'            => 'required|date|after:tanggal_mulai',
            'periode_id'                 => 'required|exists:periodes,id',
            'deskripsi_kegiatan'         => 'nullable|string',
        ]);

        $mitraId = null;
        $jenisMitra = $request->jenis_mitra;

        if ($request->jenis_mitra === 'baru') {
            $mitra = Mitra::create([
                'nama_instansi' => $request->nama_instansi_manual,
                'alamat' => $request->alamat_instansi_manual,
                'kota' => $request->kota_instansi_manual,
                'bidang_industri' => $request->bidang_industri,
                'email' => $request->email_instansi,
                'no_telp' => $request->no_telp_instansi ?: $request->kontak_instansi,
                'jenis_mitra' => 'non_mou',
                'status_mitra' => 'terdaftar',
                'status_mitra_detail' => 'menunggu_verifikasi',
                'status_mou' => 'tidak',
            ]);
            $mitraId = $mitra->id;
        } else {
            $mitraId = $request->mitra_id;
        }

        $pengajuan->update([
            'periode_id' => $request->periode_id,
            'jenis_magang' => 'mitra',
            'jenis_mitra' => $jenisMitra,
            'mitra_id' => $mitraId,
            'nama_instansi_manual' => $request->jenis_mitra === 'baru' ? $request->nama_instansi_manual : null,
            'alamat_instansi_manual' => $request->jenis_mitra === 'baru' ? $request->alamat_instansi_manual : null,
            'kota_instansi_manual' => $request->jenis_mitra === 'baru' ? $request->kota_instansi_manual : null,
            'posisi_magang' => $request->posisi_magang,
            'deskripsi_kegiatan' => $request->deskripsi_kegiatan,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'durasi' => now()->parse($request->tanggal_mulai)->diffInDays(now()->parse($request->tanggal_selesai)) + 1,
            'status_pengajuan' => 'pending',
            'catatan_admin' => null,
        ]);

        StatusHistory::create([
            'pengajuan_id' => $pengajuan->id,
            'status' => 'revisi_dikirim',
            'keterangan' => 'Mahasiswa mengirim ulang revisi pengajuan surat pengantar.',
            'updated_by' => auth()->id(),
        ]);

        foreach (User::whereIn('role', ['admin','superadmin'])->get() as $admin) {
            Notifikasi::create([
                'user_id' => $admin->id,
                'judul' => 'Revisi Pengajuan Surat Pengantar',
                'pesan' => 'Mahasiswa ' . (auth()->user()->mahasiswaProfile->nama_lengkap ?? '-') . ' mengirim ulang revisi pengajuan.',
                'status' => 'belum',
                'target_url' => route('admin.pengajuan.show', $pengajuan->id),
            ]);
        }

        return redirect()->route('mahasiswa.pengajuan.show', $pengajuan->id)->with('success', 'Revisi pengajuan berhasil dikirim ulang.');
    }

    public function seminar()
    {
        $pengajuan = $this->activePengajuan();

        if (!$pengajuan) {
            return view('mahasiswa.info-butuh-pengajuan', ['feature' => 'Seminar Magang']);
        }

        return redirect()->route('mahasiswa.pengajuan.show', $pengajuan->id);
    }

    public function needsActivePengajuan($feature)
    {
        return view('mahasiswa.info-butuh-pengajuan', compact('feature'));
    }

    public function requestSeminar(Request $request, $id)
    {
        $pengajuan = PengajuanMagang::findOrFail($id);

        if ($pengajuan->status_laporan === 'belum_ada') {
            return redirect()->back()->with('error', 'Anda harus mengupload laporan sebelum mengajukan seminar.');
        }

        // check missing weekly logbook entries
        $missing = $this->findMissingWeeks($pengajuan);
        if (count($missing)) {
            return redirect()->back()->with('error', 'Anda belum mengisi logbook untuk minggu-minggu berikut: ' . implode(', ', $missing));
        }

        $request->validate([
            'judul_laporan' => 'required|string|max:255',
            'requested_tanggal' => 'nullable|date',
            'requested_jam' => 'nullable|string|max:20',
            'requested_ruangan' => 'nullable|string|max:120',
        ]);

        $pengajuan->update([
            'judul_laporan' => $request->judul_laporan,
            'seminar_tanggal' => $request->requested_tanggal,
            'seminar_jam' => $request->requested_jam,
            'seminar_ruangan' => $request->requested_ruangan,
            'status_seminar' => 'menunggu',
        ]);

        StatusHistory::create([
            'pengajuan_id' => $id,
            'status' => 'seminar_diajukan',
            'keterangan' => 'Mahasiswa mengajukan seminar',
            'updated_by' => auth()->id(),
        ]);

        // notify admins about seminar request
        $admins = User::whereIn('role', ['admin','superadmin'])->get();
        foreach ($admins as $a) {
            Notifikasi::create([
                'user_id' => $a->id,
                'judul' => 'Permohonan Seminar',
                'pesan' => 'Mahasiswa ' . ($pengajuan->mahasiswa->nama_lengkap ?? 'seseorang') . ' mengajukan seminar untuk pengajuan #' . $pengajuan->id,
                'status' => 'belum',
            ]);
        }

        return redirect()->back()->with('success', 'Permohonan seminar berhasil dikirim. Tunggu konfirmasi dari admin.');
    }

    public function cancel(PengajuanMagang $pengajuan)
    {
        abort_unless($this->idsMatch($pengajuan->mahasiswa_id, auth()->user()->mahasiswaProfile?->id), 403);

        if (!in_array($pengajuan->status_pengajuan, ['pending', 'revisi'], true)) {
            return redirect()->back()->with('error', 'Pengajuan tidak dapat dibatalkan karena sudah diproses admin.');
        }

        $hasImportantDocuments = $pengajuan->dokumens()
            ->whereIn('jenis_dokumen', ['surat_pengantar', 'surat_keterangan_magang', 'sk_magang', 'surat_seminar'])
            ->whereNotNull('file_path')
            ->exists();
        if ($hasImportantDocuments || $pengajuan->logbooks()->exists() || $pengajuan->bimbingans()->exists() || $pengajuan->bimbinganFormals()->exists() || $pengajuan->penilaian()->exists()) {
            return redirect()->back()->with('error', 'Pengajuan tidak dapat dibatalkan karena sudah memiliki riwayat penting.');
        }

        $pengajuan->update([
            'status_pengajuan' => 'dibatalkan',
            'catatan_mahasiswa' => trim(($pengajuan->catatan_mahasiswa ? $pengajuan->catatan_mahasiswa . "\n" : '') . 'Dibatalkan oleh mahasiswa pada ' . now()->format('d M Y H:i')),
        ]);

        StatusHistory::create([
            'pengajuan_id' => $pengajuan->id,
            'status' => 'dibatalkan',
            'keterangan' => 'Pengajuan dibatalkan oleh mahasiswa.',
            'updated_by' => auth()->id(),
        ]);

        foreach (User::whereIn('role', ['admin','superadmin'])->get() as $admin) {
            Notifikasi::create([
                'user_id' => $admin->id,
                'judul' => 'Pengajuan Dibatalkan Mahasiswa',
                'pesan' => 'Mahasiswa ' . ($pengajuan->mahasiswa->nama_lengkap ?? '-') . ' membatalkan pengajuan.',
                'status' => 'belum',
                'target_url' => route('admin.pengajuan.show', $pengajuan->id),
            ]);
        }

        return redirect()->route('mahasiswa.pengajuan.index')->with('success', 'Pengajuan berhasil dibatalkan.');
    }
    private function checkEligibility($mahasiswa, $periodeId = null)
    {
        $reasons = [];
        if (!$mahasiswa) {
            $reasons[] = 'Data mahasiswa tidak ditemukan.';
            return ['allowed' => false, 'reasons' => $reasons];
        }

        if (!$mahasiswa->profileComplete()) {
            $reasons[] = 'Profile Anda belum lengkap. Silakan lengkapi profile terlebih dahulu sebelum mengajukan magang.';
        }

        // Allow students who had a leave (cuti) to apply at semester 7
        if (!($mahasiswa->pernah_cuti && $mahasiswa->semester == 7)) {
            if ($mahasiswa->semester < 5) {
                $reasons[] = 'Mahasiswa belum berada pada semester yang diperbolehkan.';
            }
        }

        if (($mahasiswa->sks_lulus ?? 0) < 90) {
            $reasons[] = 'Anda belum dapat mengajukan magang karena SKS lulus belum memenuhi syarat minimal 90 SKS.';
        }

        $periode = $periodeId ? Periode::find($periodeId) : Periode::where('status', 'aktif')->first();

        if (!$periode || $periode->status !== 'aktif') {
            $reasons[] = 'Periode magang tidak sedang dibuka atau tidak aktif.';
        }

        return [
            'allowed' => count($reasons) === 0,
            'reasons' => $reasons,
            'periode' => $periode,
        ];
    }

    private function activePengajuan()
    {
        return auth()->user()->mahasiswaProfile?->pengajuans()
            ->whereIn('status_pengajuan', ['disetujui', 'berjalan'])
            ->latest()
            ->first();
    }

    private function authorizeRevisiSuratPengantar(PengajuanMagang $pengajuan): void
    {
        abort_unless($this->idsMatch($pengajuan->mahasiswa_id, auth()->user()->mahasiswaProfile?->id), 403);
        abort_unless($pengajuan->jenis_pengajuan === 'surat_pengantar', 404);
        abort_unless($pengajuan->status_pengajuan === 'revisi', 403);
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
            $filledWeeks[$w] = true;
        }

        $missing = [];
        foreach ($weeks as $w) {
            if (!isset($filledWeeks[$w])) $missing[] = $w;
        }
        return $missing;
    }
}
