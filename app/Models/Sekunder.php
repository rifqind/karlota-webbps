<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sekunder extends Model
{
    //
    use HasFactory, HasUuids;
    protected $fillable = [
        'label',
        'produsen_id',
        'created_at',
        'updated_at',
        'created_by'
    ];
    
    public $timestamps = true;
}
