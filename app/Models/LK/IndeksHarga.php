<?php

namespace App\Models\LK;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndeksHarga extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'komoditas_id',
        'indeks_harga',
        'tahun',
        'triwulan'
    ];
    public $table = 'indeks_harga';
    // public $timestamps = true;
}
