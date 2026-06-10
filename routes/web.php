<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\MasterMahasiswaController;
use App\Http\Controllers\Admin\MasterDosenController;
use App\Http\Controllers\Admin\MasterPembimbingLapanganController;
use App\Http\Controllers\Admin\PengajuanController as AdminPengajuan;
use App\Http\Controllers\Admin\MitraController;
use App\Http\Controllers\Admin\BimbinganController as AdminBimbingan;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\PedomanController;
use App\Http\Controllers\Admin\PeriodeController;
use App\Http\Controllers\Admin\SeminarController as AdminSeminar;
use App\Http\Controllers\Mahasiswa\DashboardController as MahasiswaDashboard;
use App\Http\Controllers\Mahasiswa\PengajuanController as MahasiswaPengajuan;
use App\Http\Controllers\Mahasiswa\BimbinganController as MahasiswaBimbingan;
use App\Http\Controllers\Mahasiswa\LogbookController as MahasiswaLogbook;
use App\Http\Controllers\Mahasiswa\DokumenController;
use App\Http\Controllers\Mahasiswa\PenilaianController as MahasiswaPenilaian;
use App\Http\Controllers\Mahasiswa\SeminarController as MahasiswaSeminar;
use App\Http\Controllers\Mahasiswa\AbsensiController as MahasiswaAbsensi;
use App\Http\Controllers\Mahasiswa\MitraController as MahasiswaMitra;
use App\Http\Controllers\Dosen\DashboardController as DosenDashboard;
use App\Http\Controllers\Dosen\BimbinganController as DosenBimbingan;
use App\Http\Controllers\Dosen\LogbookController as DosenLogbook;
use App\Http\Controllers\Dosen\PenilaianController as DosenPenilaian;
use App\Http\Controllers\Dosen\SeminarController as DosenSeminar;
use App\Http\Controllers\Mitra\DashboardController as MitraDashboard;
use App\Http\Controllers\Mitra\PengajuanController as MitraPengajuan;
use App\Http\Controllers\Mitra\PenilaianController as MitraPenilaian;
use App\Http\Controllers\Mitra\LogbookController as MitraLogbook;
use App\Http\Controllers\Mitra\AbsensiController as MitraAbsensi;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotifikasiController;

// ============ AUTH ============
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ============ DASHBOARD REDIRECT ============
Route::get('/dashboard', function () {
    $role = auth()->user()->role;
    return match($role) {
        'superadmin', 'admin' => redirect()->route('admin.dashboard'),
        'mahasiswa'           => redirect()->route('mahasiswa.dashboard'),
        'dosen'               => redirect()->route('dosen.dashboard'),
        'mitra'               => redirect()->route('mitra.dashboard'),
        'pembimbing_lapangan'=> redirect()->route('profile.show'),
        default               => redirect('/'),
    };
})->middleware('auth')->name('dashboard');

// ============ ADMIN ============
Route::prefix('admin')->middleware(['auth', 'role:admin,superadmin'])->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);
    Route::get('/master-data', [MasterDataController::class, 'index'])->name('master.index');
    Route::get('/master-data/referensi/{type}', [MasterDataController::class, 'reference'])->name('master.reference.index');
    Route::post('/master-data/referensi/{type}', [MasterDataController::class, 'storeReference'])->name('master.reference.store');
    Route::put('/master-data/referensi/{type}/{id}', [MasterDataController::class, 'updateReference'])->name('master.reference.update');
    Route::delete('/master-data/referensi/{type}/{id}', [MasterDataController::class, 'destroyReference'])->name('master.reference.destroy');
    Route::get('/master-data/mahasiswa/template', [MasterMahasiswaController::class, 'template'])->name('master.mahasiswa.template');
    Route::get('/master-data/mahasiswa/export', [MasterMahasiswaController::class, 'export'])->name('master.mahasiswa.export');
    Route::post('/master-data/mahasiswa/import', [MasterMahasiswaController::class, 'import'])->name('master.mahasiswa.import');
    Route::resource('/master-data/mahasiswa', MasterMahasiswaController::class)->names('master.mahasiswa');
    Route::get('/master-data/dosen/template', [MasterDosenController::class, 'template'])->name('master.dosen.template');
    Route::get('/master-data/dosen/export', [MasterDosenController::class, 'export'])->name('master.dosen.export');
    Route::post('/master-data/dosen/import', [MasterDosenController::class, 'import'])->name('master.dosen.import');
    Route::resource('/master-data/dosen', MasterDosenController::class)->names('master.dosen');
    Route::resource('/master-data/pembimbing-lapangan', MasterPembimbingLapanganController::class)->parameters(['pembimbing-lapangan' => 'pembimbing'])->names('master.pembimbing');
    Route::get('/pengajuan', [AdminPengajuan::class, 'index'])->name('pengajuan.index');
    Route::get('/pengajuan/{id}', [AdminPengajuan::class, 'show'])->name('pengajuan.show');
    Route::post('/pengajuan/{id}/status', [AdminPengajuan::class, 'updateStatus'])->name('pengajuan.status');
    Route::delete('/pengajuan/{id}', [AdminPengajuan::class, 'destroy'])->name('pengajuan.destroy');
    Route::post('/pengajuan/{id}/dokumen', [AdminPengajuan::class, 'updateDokumenStatus'])->name('pengajuan.dokumen.status');
    Route::get('/dokumen/{dokumen}/preview', [AdminPengajuan::class, 'previewDokumen'])->name('dokumen.preview');
    Route::get('/dokumen/{dokumen}/download', [AdminPengajuan::class, 'downloadDokumen'])->name('dokumen.download');
    Route::delete('/dokumen/{dokumen}', [AdminPengajuan::class, 'destroyDokumen'])->name('dokumen.destroy');
    Route::post('/pengajuan/{id}/seminar', [AdminPengajuan::class, 'scheduleSeminar'])->name('pengajuan.seminar');
    Route::get('/seminar', [AdminSeminar::class, 'index'])->name('seminar.index');
    Route::post('/seminar/{id}/schedule', [AdminSeminar::class, 'schedule'])->name('seminar.schedule');
    Route::post('/seminar/{id}/reject', [AdminSeminar::class, 'reject'])->name('seminar.reject');
    Route::post('/seminar/{id}/cancel', [AdminSeminar::class, 'cancel'])->name('seminar.cancel');
    Route::post('/pengajuan/{id}/dosen', [AdminPengajuan::class, 'assignDosen'])->name('pengajuan.dosen');
    Route::post('/pengajuan/{id}/mitra', [AdminPengajuan::class, 'assignMitra'])->name('pengajuan.mitra');
    Route::get('/pengajuan/{id}/surat/keterangan', [\App\Http\Controllers\Admin\SuratController::class, 'generateSkIndividual'])->name('pengajuan.surat.keterangan');
    Route::get('/pengajuan/{id}/surat/seminar', [\App\Http\Controllers\Admin\SuratController::class, 'generateSkSeminar'])->name('pengajuan.surat.seminar');
    Route::post('/surat/sk-kolektif', [\App\Http\Controllers\Admin\SuratController::class, 'generateSkKolektif'])->name('surat.sk.kolektif');
    Route::resource('mitra', MitraController::class);
    Route::post('/periode/{periode}/activate', [PeriodeController::class, 'activate'])->name('periode.activate');
    Route::resource('periode', PeriodeController::class)->except(['show']);
    Route::resource('pedoman', PedomanController::class)->except(['show']);
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export', [LaporanController::class, 'export'])->name('laporan.export');
});

Route::middleware('auth')->group(function () {
    Route::get('/pedoman', [PedomanController::class, 'index'])->name('pedoman.index');
    Route::get('/pedoman/{pedoman}', [PedomanController::class, 'show'])->name('pedoman.show');
    Route::get('/pedoman/{pedoman}/preview', [PedomanController::class, 'preview'])->name('pedoman.preview');
    Route::get('/pedoman/{pedoman}/download', [PedomanController::class, 'download'])->name('pedoman.download');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::get('/notifikasi/{notifikasi}/read', [NotifikasiController::class, 'read'])->name('notifikasi.read');
    Route::post('/notifikasi/read-all', [NotifikasiController::class, 'readAll'])->name('notifikasi.read-all');
    Route::delete('/notifikasi/delete-all', [NotifikasiController::class, 'destroyAll'])->name('notifikasi.destroy-all');
    Route::delete('/notifikasi/{notifikasi}', [NotifikasiController::class, 'destroy'])->name('notifikasi.destroy');
});

// ============ MAHASISWA ============
Route::prefix('mahasiswa')->middleware(['auth', 'role:mahasiswa'])->name('mahasiswa.')->group(function () {
    Route::get('/dashboard', [MahasiswaDashboard::class, 'index'])->name('dashboard');
    Route::resource('pengajuan', MahasiswaPengajuan::class)->only(['index', 'create', 'store', 'show', 'edit', 'update']);
    Route::post('/pengajuan/{pengajuan}/cancel', [MahasiswaPengajuan::class, 'cancel'])->name('pengajuan.cancel');
    Route::get('/mitra', [MahasiswaMitra::class, 'index'])->name('mitra.index');
    Route::get('/seminar', [MahasiswaSeminar::class, 'index'])->name('seminar.index');
    Route::post('/seminar', [MahasiswaSeminar::class, 'store'])->name('seminar.store');
    Route::post('/seminar/{pengajuanId}/cancel', [MahasiswaSeminar::class, 'cancel'])->name('seminar.cancel');
    Route::get('/seminar/{pengajuanId}/surat', [MahasiswaSeminar::class, 'downloadSurat'])->name('seminar.surat');
    Route::get('/logbook', [MahasiswaPengajuan::class, 'needsActivePengajuan'])->defaults('feature', 'Logbook')->name('logbook.info');
    Route::get('/logbook/{pengajuanId}', [MahasiswaLogbook::class, 'index'])->name('logbook.index');
    Route::post('/logbook/{pengajuanId}', [MahasiswaLogbook::class, 'store'])->name('logbook.store');
    Route::get('/logbook/item/{logbook}/edit', [MahasiswaLogbook::class, 'edit'])->name('logbook.edit');
    Route::put('/logbook/item/{logbook}', [MahasiswaLogbook::class, 'update'])->name('logbook.update');
    Route::delete('/logbook/item/{logbook}', [MahasiswaLogbook::class, 'destroy'])->name('logbook.destroy');
    Route::get('/logbook/{pengajuanId}/export', [MahasiswaLogbook::class, 'export'])->name('logbook.export');
    Route::get('/logbook/foto/{logbook}/preview', [MahasiswaLogbook::class, 'previewFoto'])->name('logbook.foto.preview');
    Route::get('/absensi', [MahasiswaAbsensi::class, 'index'])->name('absensi.index');
    Route::post('/absensi', [MahasiswaAbsensi::class, 'store'])->name('absensi.store');
    Route::get('/absensi/{absensi}/preview', [MahasiswaAbsensi::class, 'preview'])->name('absensi.preview');
    Route::get('/bimbingan', [MahasiswaBimbingan::class, 'index'])->name('bimbingan.index');
    Route::get('/bimbingan/create', [MahasiswaBimbingan::class, 'create'])->name('bimbingan.create');
    Route::post('/bimbingan', [MahasiswaBimbingan::class, 'store'])->name('bimbingan.store');
    Route::get('/bimbingan/{bimbingan}', [MahasiswaBimbingan::class, 'show'])->name('bimbingan.show');
    Route::get('/bimbingan/{bimbingan}/download', [MahasiswaBimbingan::class, 'download'])->name('bimbingan.download');
    Route::delete('/bimbingan/{bimbingan}', [MahasiswaBimbingan::class, 'destroy'])->name('bimbingan.destroy');
    Route::get('/dokumen', [MahasiswaPengajuan::class, 'needsActivePengajuan'])->defaults('feature', 'Dokumen')->name('dokumen.info');
    Route::get('/dokumen/{pengajuanId}', [DokumenController::class, 'index'])->name('dokumen.index');
    Route::post('/dokumen/{pengajuanId}', [DokumenController::class, 'store'])->name('dokumen.store');
    Route::put('/dokumen/file/{dokumenId}', [DokumenController::class, 'update'])->name('dokumen.update');
    Route::delete('/dokumen/file/{dokumenId}', [DokumenController::class, 'destroy'])->name('dokumen.destroy');
    Route::get('/dokumen/file/{dokumenId}/preview', [DokumenController::class, 'preview'])->name('dokumen.preview');
    Route::get('/dokumen/file/{dokumenId}/download', [DokumenController::class, 'download'])->name('dokumen.download');
    Route::get('/surat', [MahasiswaPengajuan::class, 'needsActivePengajuan'])->defaults('feature', 'Surat/Download Surat')->name('surat.info');
    Route::get('/penilaian', [MahasiswaPengajuan::class, 'needsActivePengajuan'])->defaults('feature', 'Penilaian')->name('penilaian.info');
    Route::get('/penilaian/{pengajuanId}', [MahasiswaPenilaian::class, 'show'])->name('penilaian.show');
});

// ============ DOSEN ============
Route::prefix('dosen')->middleware(['auth', 'role:dosen'])->name('dosen.')->group(function () {
    Route::get('/dashboard', [DosenDashboard::class, 'index'])->name('dashboard');
    Route::get('/bimbingan', [DosenBimbingan::class, 'index'])->name('bimbingan.index');
    Route::get('/bimbingan/{pengajuanId}/detail', [DosenBimbingan::class, 'show'])->name('bimbingan.show');
    Route::get('/bimbingan/{pengajuanId}/formal', [DosenBimbingan::class, 'formalIndex'])->name('bimbingan.formal.index');
    Route::get('/bimbingan/formal/{bimbingan}', [DosenBimbingan::class, 'formalShow'])->name('bimbingan.formal.show');
    Route::post('/bimbingan/formal/{bimbingan}/reply', [DosenBimbingan::class, 'reply'])->name('bimbingan.formal.reply');
    Route::get('/logbook', [DosenLogbook::class, 'index'])->name('logbook.index');
    Route::get('/logbook/{pengajuanId}', [DosenLogbook::class, 'show'])->name('logbook.show');
    Route::post('/logbook/{id}/validasi', [DosenLogbook::class, 'validasi'])->name('logbook.validasi');
    Route::get('/logbook/{pengajuanId}/export', [DosenLogbook::class, 'export'])->name('logbook.export');
    Route::get('/logbook/foto/{logbook}/preview', [DosenLogbook::class, 'previewFoto'])->name('logbook.foto.preview');
    Route::get('/seminar', [DosenSeminar::class, 'index'])->name('seminar.index');
    Route::get('/penilaian', [DosenPenilaian::class, 'index'])->name('penilaian.index');
    Route::get('/penilaian/{pengajuanId}', [DosenPenilaian::class, 'create'])->name('penilaian.create');
    Route::post('/penilaian/{pengajuanId}', [DosenPenilaian::class, 'store'])->name('penilaian.store');
});

// ============ MITRA ============
Route::prefix('mitra')->middleware(['auth', 'role:mitra'])->name('mitra.')->group(function () {
    Route::get('/dashboard', [MitraDashboard::class, 'index'])->name('dashboard');
    Route::get('/pengajuan', [MitraPengajuan::class, 'index'])->name('pengajuan.index');
    Route::get('/pengajuan/{pengajuanId}', [MitraPengajuan::class, 'show'])->name('pengajuan.show');
    Route::get('/pic', [MitraPengajuan::class, 'pic'])->name('pic.index');
    Route::get('/absensi', [MitraAbsensi::class, 'index'])->name('absensi.index');
    Route::post('/absensi/{absensi}/validasi', [MitraAbsensi::class, 'validasi'])->name('absensi.validasi');
    Route::get('/absensi/{absensi}/preview', [MitraAbsensi::class, 'preview'])->name('absensi.preview');
    Route::get('/logbook', [MitraLogbook::class, 'index'])->name('logbook.index');
    Route::get('/logbook/{pengajuanId}', [MitraLogbook::class, 'show'])->name('logbook.show');
    Route::post('/logbook/{id}/validasi', [MitraLogbook::class, 'validasi'])->name('logbook.validasi');
    Route::get('/logbook/foto/{logbook}/preview', [MitraLogbook::class, 'previewFoto'])->name('logbook.foto.preview');
    Route::get('/penilaian', [MitraPenilaian::class, 'index'])->name('penilaian.index');
    Route::get('/penilaian/{pengajuanId}', [MitraPenilaian::class, 'create'])->name('penilaian.create');
    Route::post('/penilaian/{pengajuanId}', [MitraPenilaian::class, 'store'])->name('penilaian.store');
});




