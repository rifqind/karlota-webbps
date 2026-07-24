<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subsector extends Model
{
    use HasFactory;

    const IMPOR_ID = 69;

    protected $guarded = ['id'];
    public $timestamps = false; // Disable timestamps
    public function Sector()
    {
        return $this->belongsTo(Sector::class);
    }

    public function pdrb()
    {
        return $this->hasMany(Pdrb::class);
    }
}
