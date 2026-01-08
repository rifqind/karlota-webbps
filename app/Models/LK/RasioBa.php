<?php

namespace App\Models\LK;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RasioBa extends Model
{
    //
    use HasFactory;
    public $table = 'master_rasio_ntb';
    protected $fillable = ['komoditas_id', 'sut_id', 'rasio_ntb'];
    public $timestamps = false;
}
