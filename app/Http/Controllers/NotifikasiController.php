<?php
namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index()
    {
        $notifikasis = auth()->user()->notifikasis()->latest()->paginate(15);

        return view('notifikasi.index', compact('notifikasis'));
    }

    public function read(Notifikasi $notifikasi)
    {
        abort_unless($notifikasi->user_id === auth()->id(), 403);

        $notifikasi->update(['status' => 'dibaca']);

        return redirect()->to($notifikasi->target_url ?: route('dashboard'));
    }

    public function readAll(Request $request)
    {
        auth()->user()->notifikasis()->where('status', 'belum')->update(['status' => 'dibaca']);

        return redirect()->route('notifikasi.index')->with('success', 'Semua notifikasi ditandai dibaca.');
    }
    public function destroy(Notifikasi $notifikasi)
    {
        abort_unless($notifikasi->user_id === auth()->id(), 403);
        $notifikasi->delete();

        return redirect()->route('notifikasi.index')->with('success', 'Notifikasi berhasil dihapus.');
    }

    public function destroyAll(Request $request)
    {
        auth()->user()->notifikasis()->delete();

        return redirect()->route('notifikasi.index')->with('success', 'Semua notifikasi berhasil dihapus.');
    }
}
