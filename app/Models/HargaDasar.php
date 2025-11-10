<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HargaDasar extends Model
{
    //
    use HasFactory;
    protected $fillable = ['komoditas_id', 'harga_konstan'];
    public $timestamps = false;
    public $table = 'master_harga';
}
