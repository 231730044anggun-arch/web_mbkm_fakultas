<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KelayakanSeminarCatatanHistory extends Model
{
    protected $fillable = [
        'kelayakan_seminar_id',
        'user_id',
        'role_pemberi',
        'nama_pemberi',
        'status_tindakan',
        'catatan',
    ];

    public function kelayakanSeminar()
    {
        return $this->belongsTo(KelayakanSeminar::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
