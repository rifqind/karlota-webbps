<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adjustment extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'pdrb_id', 'adhb', 'adhk'
    ];

    public function pdrb()
    {
        return $this->belongsTo(Pdrb::class);
    }
}
