<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PedomanSop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class PedomanController extends Controller
{
    public function index()
    {
        $pedomans = PedomanSop::latest()->paginate(15);
        return view('admin.pedoman.index', compact('pedomans'));
    }

    public function show(PedomanSop $pedoman)
    {
        return view('admin.pedoman.show', compact('pedoman'));
    }

    public function download(PedomanSop $pedoman)
    {
        abort_if(!$pedoman->file_path || !Storage::disk('public')->exists($pedoman->file_path), 404);

        return Storage::disk('public')->download($pedoman->file_path, $this->downloadName($pedoman));
    }

    public function preview(PedomanSop $pedoman)
    {
        abort_if(!$pedoman->file_path || !Storage::disk('public')->exists($pedoman->file_path), 404);

        return $this->inlineFile($pedoman->file_path, $this->downloadName($pedoman));
    }

    public function create()
    {
        return view('admin.pedoman.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul'    => 'required|string',
            'kategori' => 'required|in:Pedoman,SOP,Template',
        ]);
        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('documents/pedoman', 'public');
        }
        PedomanSop::create([
            'judul'     => $request->judul,
            'kategori'  => $request->kategori,
            'file_path' => $filePath,
            'tahun'     => $request->tahun,
        ]);
        return redirect()->route('admin.pedoman.index')->with('success', 'Pedoman berhasil ditambahkan.');
    }

    public function edit(PedomanSop $pedoman)
    {
        return view('admin.pedoman.edit', compact('pedoman'));
    }

    public function update(Request $request, PedomanSop $pedoman)
    {
        $request->validate([
            'judul'    => 'required|string',
            'kategori' => 'required|in:Pedoman,SOP,Template',
        ]);

        $filePath = $pedoman->file_path;
        if ($request->hasFile('file')) {
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = $request->file('file')->store('documents/pedoman', 'public');
        }

        $pedoman->update([
            'judul' => $request->judul,
            'kategori' => $request->kategori,
            'file_path' => $filePath,
            'tahun' => $request->tahun,
        ]);

        return redirect()->route('admin.pedoman.index')->with('success', 'Pedoman berhasil diperbarui.');
    }

    public function destroy(PedomanSop $pedoman)
    {
        if ($pedoman->file_path) {
            Storage::disk('public')->delete($pedoman->file_path);
        }
        $pedoman->delete();
        return redirect()->route('admin.pedoman.index')->with('success', 'Pedoman berhasil dihapus.');
    }

    private function downloadName(PedomanSop $pedoman): string
    {
        $extension = pathinfo($pedoman->file_path, PATHINFO_EXTENSION) ?: 'pdf';
        return $this->safeFilename($pedoman->judul ?: 'Pedoman SOP') . '.' . $extension;
    }

    private function safeFilename(string $name): string
    {
        $name = preg_replace('/[\\\\\/:*?"<>|]+/', ' ', $name);
        $name = preg_replace('/\s+/', ' ', trim($name));
        return $name ?: 'Pedoman SOP';
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
}
