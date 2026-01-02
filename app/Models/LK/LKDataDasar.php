<?php

namespace App\Models\LK;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LKDataDasar extends Model
{
    //
    use HasFactory;
    public $table = 'lk_datadasar';
    protected $fillable = [
        'komoditas_id',
        'produksi',
        'tahun',
        'triwulan',
    ];
}
