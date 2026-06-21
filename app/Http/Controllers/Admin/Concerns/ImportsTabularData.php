<?php
namespace App\Http\Controllers\Admin\Concerns;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
            'namainstansi' => 'nama_instansi',
            'namamitra' => 'nama_instansi',
            'instansi' => 'nama_instansi',
            'tempatmagang' => 'nama_instansi',
            'kotamitra' => 'kota_mitra',
            'kota' => 'kota_mitra',
            'namadosenpembimbing' => 'nama_dosen_pembimbing',
            'emaildosenpembimbing' => 'email_dosen_pembimbing',
            'emaildosen' => 'email_dosen_pembimbing',
            'nidnnipdosen' => 'nidn_nip_dosen',
            'namapembimbinglapangan' => 'nama_pembimbing_lapangan',
            'emailpembimbinglapangan' => 'email_pembimbing_lapangan',
            'nohppembimbinglapangan' => 'no_hp_pembimbing_lapangan',
            'jabatanpembimbinglapangan' => 'jabatan_pembimbing_lapangan',
            'periodemagang' => 'periode_magang',
            'tanggalmulaimagang' => 'tanggal_mulai_magang',
            'tanggalmulai' => 'tanggal_mulai_magang',
            'tanggalselesaimagang' => 'tanggal_selesai_magang',
            'tanggalselesai' => 'tanggal_selesai_magang',
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

    private function xlsxResponse(string $filename, array $headers, iterable $rows)
    {
        $callback = function () use ($headers, $rows) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray($headers, null, 'A1');
            $rowNumber = 2;
            foreach ($rows as $row) {
                $sheet->fromArray(array_values(is_array($row) ? $row : iterator_to_array($row)), null, 'A' . $rowNumber);
                $rowNumber++;
            }
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
