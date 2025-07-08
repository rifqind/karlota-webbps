<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SummaryTime extends Model
{
    //
    use HasFactory;
    public $table = 'summary_time';
    protected $guarded = ['id'];
    public $timestamps = false;
    protected $fillable = [
        'type',
        'period_id',
        'id_user',
        'timestamp'
    ];
}
