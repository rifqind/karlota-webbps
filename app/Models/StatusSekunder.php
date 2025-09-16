<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusSekunder extends Model
{
    //
    use HasFactory, HasUuids;
    protected $fillable = [
        'sekunder_id',
        'tahun',
        'status',
        'created_by',
        'created_at',
        'updated_at'
    ];
    public $timestamps = true;
    public $table = 'status_sekunder';
}
