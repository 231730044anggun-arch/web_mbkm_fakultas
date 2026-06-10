<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedomanSop extends Model
{
    protected $fillable = ['judul', 'kategori', 'file_path', 'tahun'];
}