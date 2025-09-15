<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Datacontent extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'data',
        'status_id',
        'row_id',
        // 'variabel_id',
        'triwulan'
    ];
    public $timestamps = false;
}
