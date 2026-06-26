<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KelayakanSeminar extends Model
{
    protected $fillable = [
        'pengajuan_id', 'mahasiswa_id', 'dosen_id', 'pembimbing_lapangan_id',
        'laporan_hasil_magang', 'output_magang', 'produk_magang', 'draft_jurnal', 'catatan_mahasiswa', 'status',
        'submitted_at', 'status_persetujuan_dosen', 'catatan_dosen', 'tanggal_persetujuan_dosen',
        'status_persetujuan_pembimbing', 'catatan_pembimbing', 'tanggal_persetujuan_pembimbing',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'tanggal_persetujuan_dosen' => 'datetime',
        'tanggal_persetujuan_pembimbing' => 'datetime',
    ];

    public function pengajuan() { return $this->belongsTo(PengajuanMagang::class, 'pengajuan_id'); }
    public function mahasiswa() { return $this->belongsTo(MahasiswaProfile::class, 'mahasiswa_id'); }
    public function dosen() { return $this->belongsTo(Dosen::class); }
    public function pembimbingLapangan() { return $this->belongsTo(PembimbingLapangan::class, 'pembimbing_lapangan_id'); }

    public function isApproved(): bool
    {
        return filled($this->laporan_hasil_magang)
            && filled($this->output_magang)
            && filled($this->produk_magang)
            && filled($this->draft_jurnal)
            && $this->status_persetujuan_dosen === 'disetujui'
            && $this->status_persetujuan_pembimbing === 'disetujui';
    }

    public function deadlineApproval()
    {
        return $this->mahasiswa?->deadlineLaporanMagang();
    }

    public function deadlineApprovalLabel(): ?string
    {
        return $this->mahasiswa?->deadlineLaporanMagangLabel();
    }

    public function melewatiDeadline(): bool
    {
        $deadline = $this->deadlineApproval();
        if (!$deadline) return false;

        $submittedAt = $this->updated_at ?: $this->created_at;
        return $submittedAt ? $submittedAt->gt($deadline) : now()->gt($deadline);
    }
}
