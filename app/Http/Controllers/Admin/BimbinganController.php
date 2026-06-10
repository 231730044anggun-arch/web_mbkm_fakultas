<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;

class BimbinganController extends Controller
{
    public function index()
    {
        $bimbingans = Bimbingan::with(['pengajuan.mahasiswa', 'dosen'])->latest()->paginate(15);
        return view('admin.bimbingan.index', compact('bimbingans'));
    }
}