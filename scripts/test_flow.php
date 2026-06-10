<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MahasiswaProfile;
use App\Models\Periode;
use App\Models\PengajuanMagang;
use App\Models\Mitra;
use App\Models\Bimbingan;
use App\Models\Logbook;
use App\Models\Dosen;
use App\Models\Penilaian;
use App\Http\Controllers\Admin\SuratController;
use App\Http\Controllers\Mahasiswa\PengajuanController as MPengController;
use Illuminate\Http\Request;
use Carbon\Carbon;

echo "Starting test_flow...\n";
$ma = MahasiswaProfile::first();
if (!$ma) { echo "NO_MAHASISWA\n"; exit(1); }
$periode = Periode::first();
if (!$periode) {
    $periode = Periode::create(['nama_periode' => 'Test Periode', 'tahun' => 2026, 'status' => 'aktif', 'tanggal_mulai' => '2026-05-01', 'tanggal_selesai' => '2026-06-30']);
    echo "Periode created: {$periode->id}\n";
}
$mitra = Mitra::first() ?? Mitra::create(['nama_instansi'=>'TEST MITRA','kota'=>'TestCity']);
$peng = PengajuanMagang::create([
    'mahasiswa_id' => $ma->id,
    'periode_id' => $periode->id,
    'jenis_magang' => 'mitra',
    'mitra_id' => $mitra->id,
    'posisi_magang' => 'Tester',
    'deskripsi_kegiatan' => 'Testing flow',
    'tanggal_mulai' => '2026-05-01',
    'tanggal_selesai' => '2026-05-31',
    'durasi' => 31,
    'status_pengajuan' => 'pending',
    'catatan_admin' => null,
]);
echo "Pengajuan created: {$peng->id}\n";

$sc = new SuratController();
try {
    $sc->generateSkIndividual($peng->id);
    echo "generateSkIndividual OK\n";
} catch (Exception $e) {
    echo "generateSkIndividual ERR: " . $e->getMessage() . "\n";
}

try {
    $req = Request::create('/', 'POST', ['periode_id' => $periode->id]);
    $sc->generateSkKolektif($req);
    echo "generateSkKolektif OK\n";
} catch (Exception $e) {
    echo "generateSkKolektif ERR: " . $e->getMessage() . "\n";
}


// Now test logbook weekly checks and seminar request
function computeMissingWeeks($peng, $logbooks=null, $toDate=null){
    $start = $peng->tanggal_mulai ? Carbon::parse($peng->tanggal_mulai)->startOfWeek() : null;
    $end = $toDate ? Carbon::parse($toDate) : Carbon::now();
    if ($peng->tanggal_selesai) $end = Carbon::parse($peng->tanggal_selesai);
    if (!$start) return [];
    $weeks = [];
    $cursor = $start->copy();
    while ($cursor->lessThanOrEqualTo($end)){
        $weeks[] = $cursor->toDateString();
        $cursor->addWeek();
    }
    $entries = $logbooks ?? $peng->logbooks()->get();
    $filled = [];
    foreach($entries as $e){ $filled[Carbon::parse($e->tanggal)->startOfWeek()->toDateString()] = true; }
    $missing = [];
    foreach($weeks as $w) if(!isset($filled[$w])) $missing[]=$w;
    return $missing;
}

// authenticate as mahasiswa user if possible
if(isset($ma->user_id)){
    try{ auth()->loginUsingId($ma->user_id); echo "Authenticated as user {$ma->user_id}\n"; }catch(Exception $e){ echo "Auth fail: " . $e->getMessage() . "\n"; }
}

$pengId = $peng->id;
// initially there are no logbooks, so missing should not be empty
$missing = computeMissingWeeks($peng);
if(count($missing)){
    echo "Missing weeks initially: " . implode(', ', $missing) . "\n";
} else {
    echo "No missing weeks initially (unexpected)\n";
}

// create one logbook entry (first week)
Logbook::create(['pengajuan_id'=>$pengId,'tanggal'=>$peng->tanggal_mulai,'kegiatan'=>'Test kegiatan','jam_mulai'=>'08:00','jam_selesai'=>'16:00','status_validasi'=>'pending']);
$missing = computeMissingWeeks($peng);
echo "Missing after one entry: " . implode(', ', $missing) . "\n";

// now fill all weeks
$start = Carbon::parse($peng->tanggal_mulai)->startOfWeek();
$end = Carbon::parse($peng->tanggal_selesai);
$cursor = $start->copy();
while($cursor->lessThanOrEqualTo($end)){
    $date = $cursor->toDateString();
    // check existing
    $exists = Logbook::where('pengajuan_id',$pengId)->where('tanggal',$date)->exists();
    if(!$exists){ Logbook::create(['pengajuan_id'=>$pengId,'tanggal'=>$date,'kegiatan'=>'Auto fill','jam_mulai'=>'08:00','jam_selesai'=>'16:00','status_validasi'=>'pending']); }
    $cursor->addWeek();
}
$missing = computeMissingWeeks($peng);
if(count($missing)) echo "Still missing after fill: " . implode(', ', $missing) . "\n"; else echo "All weeks filled.\n";

// simulate student requestSeminar by calling controller method
$mpc = new MPengController();
$req = Request::create('/', 'POST', ['judul_laporan'=>'Judul Test','requested_tanggal'=>null,'requested_jam'=>null,'requested_ruangan'=>null]);
try{
    $res = $mpc->requestSeminar($req, $pengId);
    echo "requestSeminar executed; returned: ";
    if(is_object($res)) echo get_class($res) . "\n"; else echo $res . "\n";
}catch(Exception $e){ echo "requestSeminar exception: " . $e->getMessage() . "\n"; }

// assign dosen and mitra penilaian flow
$dosen = Dosen::first(); if(!$dosen){ echo "No dosen available; skipping dosen scoring.\n"; } else { Bimbingan::updateOrCreate(['pengajuan_id'=>$pengId],['dosen_id'=>$dosen->id,'tanggal_penugasan'=>now()->toDateString()]); echo "Assigned dosen {$dosen->id}\n"; }

// create penilaian entries via model
Penilaian::updateOrCreate(['pengajuan_id'=>$pengId], ['nilai_lapangan'=>80,'nilai_dosen'=>85,'nilai_seminar'=>90,'catatan'=>null]);
$pen = Penilaian::where('pengajuan_id',$pengId)->first(); if($pen){ $pen->calculateFinalScore(); echo "Final score: {$pen->nilai_akhir}\n"; }

echo "Test flow finished.\n";
