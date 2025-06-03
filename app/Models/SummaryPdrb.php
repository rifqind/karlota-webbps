<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SummaryPdrb extends Model
{
    //
    use HasFactory;
    public $table = 'summary_pdrb';
    protected $guarded = ['id'];
    public $timestamps = false;
    protected $fillable = [
        'adhb',
        'adhk',
        'dist',
        'qtoq',
        'yony',
        'ctoc',
        'idx',
        'iqtoq',
        'iyony'
    ];
}
