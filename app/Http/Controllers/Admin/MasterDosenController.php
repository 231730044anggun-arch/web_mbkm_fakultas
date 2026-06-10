<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ImportsTabularData;
use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MasterDosenController extends Controller
{
    use ImportsTabularData;

    public function index(Request $request)
    {
        $dosens = Dosen::with(['user', 'prodi'])
            ->when($request->search, fn($q, $s) => $q->where(function ($sub) use ($s) {
                $sub->where('nidn', 'like', "%{$s}%")->orWhere('nama_dosen', 'like', "%{$s}%")->orWhere('email_dosen', 'like', "%{$s}%");
            }))
            ->latest()
            ->paginate($this->perPage($request))
            ->withQueryString();

        return view('admin.master.dosen.index', compact('dosens'));
    }

    public function create()
    {
        return view('admin.master.dosen.form', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->saveDosen($data);
        return redirect()->route('admin.master.dosen.index')->with('success', 'Data dosen berhasil disimpan.');
    }

    public function show(Dosen $dosen)
    {
        $dosen->load(['user', 'prodi']);
        return view('admin.master.dosen.show', compact('dosen'));
    }

    public function edit(Dosen $dosen)
    {
        return view('admin.master.dosen.form', array_merge($this->formData(), compact('dosen')));
    }

    public function update(Request $request, Dosen $dosen)
    {
        $data = $this->validated($request, $dosen);
        $this->saveDosen($data, $dosen);
        return redirect()->route('admin.master.dosen.index')->with('success', 'Data dosen berhasil diperbarui.');
    }

    public function destroy(Dosen $dosen)
    {
        if ($dosen->bimbingans()->exists()) {
            $dosen->update(['status_dosen' => 'nonaktif']);
            return back()->with('error', 'Dosen sudah punya riwayat bimbingan. Data tidak dihapus dan status dosen dinonaktifkan.');
        }
        $dosen->delete();
        return back()->with('success', 'Data dosen berhasil dihapus.');
    }

    public function template()
    {
        return $this->csvResponse('template_import_dosen.csv', $this->headers(), []);
    }

    public function export()
    {
        $rows = Dosen::with('prodi')->orderBy('nama_dosen')->get()->map(fn($d) => [
            $d->nidn, $d->nama_dosen, $d->email_dosen, $d->prodi?->nama_prodi, $d->no_hp, $d->status_dosen,
        ]);
        return $this->csvResponse('data_dosen.csv', $this->headers(), $rows);
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240']);
        $rows = $this->readRows($request->file('file')->getRealPath());
        $created = 0;
        $updated = 0;
        $skipped = [];

        foreach ($rows as $index => $row) {
            if (blank($row['nidn_nip'] ?? null)) {
                $skipped[] = 'Baris ' . ($index + 2) . ': NIDN/NIP kosong.';
                continue;
            }
            try {
                $existing = Dosen::where('nidn', $row['nidn_nip'])->first();
                $this->saveDosen([
                    'nidn' => $row['nidn_nip'],
                    'nama_dosen' => $row['nama_dosen'] ?? null,
                    'email_dosen' => $row['email'] ?? null,
                    'program_studi' => $row['program_studi'] ?? null,
                    'no_hp' => $row['no_hp'] ?? null,
                    'status_dosen' => $row['status_dosen'] ?? 'aktif',
                ], $existing);
                $existing ? $updated++ : $created++;
            } catch (\Throwable $e) {
                $skipped[] = 'Baris ' . ($index + 2) . ': ' . $e->getMessage();
            }
        }

        $message = "Import selesai. Baru: {$created}, diperbarui: {$updated}.";
        if ($skipped) $message .= ' Catatan: ' . implode(' ', array_slice($skipped, 0, 5));
        return back()->with($skipped ? 'error' : 'success', $message);
    }

    private function validated(Request $request, ?Dosen $dosen = null): array
    {
        return $request->validate([
            'nidn' => ['required', 'string', 'max:80', Rule::unique('dosens', 'nidn')->ignore($dosen?->id)],
            'nama_dosen' => 'required|string|max:255',
            'email_dosen' => 'nullable|email|max:150',
            'prodi_id' => 'nullable|exists:prodis,id',
            'no_hp' => 'nullable|string|max:50',
            'status_dosen' => 'nullable|in:aktif,nonaktif',
        ]);
    }

    private function saveDosen(array $data, ?Dosen $dosen = null): Dosen
    {
        $prodiId = $data['prodi_id'] ?? null;
        if (!$prodiId && filled($data['program_studi'] ?? null)) {
            $prodiId = Prodi::firstOrCreate(['nama_prodi' => $data['program_studi']])->id;
        }

        $userId = $dosen?->user_id;
        if (filled($data['email_dosen'] ?? null)) {
            $user = User::where('email', $data['email_dosen'])->first();
            if ($user && $user->role !== 'dosen') {
                throw new \RuntimeException('Email ' . $data['email_dosen'] . ' sudah digunakan oleh role lain.');
            }
            if (!$user) {
                $user = User::create([
                    'name' => $data['nama_dosen'],
                    'email' => $data['email_dosen'],
                    'password' => Hash::make('12345678'),
                    'role' => 'dosen',
                    'status' => 'aktif',
                ]);
            }
            $userId = $user->id;
        }

        $dosen = $dosen ?: Dosen::firstOrNew(['nidn' => $data['nidn']]);
        if ($dosen->exists && $dosen->user_id && $userId && (int) $dosen->user_id !== (int) $userId) {
            throw new \RuntimeException('NIDN/NIP ' . $data['nidn'] . ' sudah terhubung dengan akun lain.');
        }
        $dosen->fill([
            'user_id' => $userId,
            'nidn' => $data['nidn'],
            'nama_dosen' => $data['nama_dosen'],
            'prodi_id' => $prodiId,
            'no_hp' => $data['no_hp'] ?? null,
            'email_dosen' => $data['email_dosen'] ?? null,
            'status_dosen' => $data['status_dosen'] ?? 'aktif',
        ]);
        $dosen->syncProfileStatus();
        $dosen->save();

        return $dosen;
    }

    private function perPage(Request $request): int
    {
        $allowed = [10, 25, 50, 100];
        $perPage = (int) $request->input('per_page', 10);

        return in_array($perPage, $allowed, true) ? $perPage : 10;
    }
    private function formData(): array
    {
        return ['prodis' => Prodi::orderBy('nama_prodi')->get()];
    }

    private function headers(): array
    {
        return ['nidn_nip','nama_dosen','email','program_studi','no_hp','status_dosen'];
    }
}