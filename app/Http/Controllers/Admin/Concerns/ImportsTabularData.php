<?php
namespace App\Http\Controllers\Admin\Concerns;

use PhpOffice\PhpSpreadsheet\IOFactory;

trait ImportsTabularData
{
    private function readRows(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rawRows = $sheet->toArray(null, true, true, true);
        if (count($rawRows) < 2) return [];

        $headers = array_shift($rawRows);
        $headers = array_map(fn($value) => $this->normalizeHeader((string) $value), $headers);

        $rows = [];
        foreach ($rawRows as $rawRow) {
            $row = [];
            $hasValue = false;
            foreach ($headers as $key => $header) {
                if (!$header) continue;
                $value = $rawRow[$key] ?? null;
                if ($value !== null && trim((string) $value) !== '') $hasValue = true;
                $row[$header] = is_string($value) ? trim($value) : $value;
            }
            if ($hasValue) $rows[] = $row;
        }

        return $rows;
    }

    private function normalizeHeader(string $header): string
    {
        $header = trim(preg_replace('/\x{FEFF}/u', '', $header));
        $compact = strtolower(preg_replace('/[^a-z0-9]+/i', '', $header));
        $aliases = [
            'nama' => 'nama_lengkap',
            'namalengkap' => 'nama_lengkap',
            'nim' => 'nim',
            'email' => 'email',
            'kelas' => 'kelas',
            'jeniskelamin' => 'jenis_kelamin',
            'alamat' => 'alamat',
            'alamatlengkap' => 'alamat_lengkap',
            'nohp' => 'no_hp',
            'nohandphone' => 'no_hp',
            'nomorhp' => 'no_hp',
            'nomorhandphone' => 'no_hp',
            'telepon' => 'no_hp',
            'telp' => 'no_hp',
            'tempatlahir' => 'tempat_lahir',
            'tanggallahir' => 'tanggal_lahir',
            'angkatan' => 'angkatan',
            'fakultas' => 'fakultas',
            'programstudi' => 'program_studi',
            'prodi' => 'program_studi',
            'semester' => 'semester',
            'skslulus' => 'sks_lulus',
            'ipk' => 'ipk',
            'statusmahasiswa' => 'status_mahasiswa',
            'nidnnip' => 'nidn_nip',
            'nidn' => 'nidn_nip',
            'nip' => 'nidn_nip',
            'namadosen' => 'nama_dosen',
            'statusdosen' => 'status_dosen',
        ];

        if (isset($aliases[$compact])) return $aliases[$compact];

        return preg_replace('/_+/', '_', strtolower(trim(preg_replace('/[^a-z0-9]+/i', '_', $header), '_')));
    }

    private function csvResponse(string $filename, array $headers, iterable $rows)
    {
        $callback = function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($rows as $row) fputcsv($handle, $row);
            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}