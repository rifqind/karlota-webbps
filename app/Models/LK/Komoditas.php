<?php

namespace App\Models\LK;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Komoditas extends Model
{
    //
    use HasFactory;
    public $table = 'master_komoditas';
    protected $fillable = [
        'label',
        'code',
        'type',
        'satuan',
        'category_id',
        'sector_id',
        'subsector_id',
        'edited_by'
    ];
}
