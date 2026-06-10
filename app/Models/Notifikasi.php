<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $fillable = ['user_id', 'judul', 'pesan', 'status', 'target_url'];

    public function user() { return $this->belongsTo(User::class); }
}
