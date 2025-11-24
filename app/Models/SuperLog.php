<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperLog extends Model
{
    //
    protected $table = 'super_logs';
    protected $fillable = [
        'table_name',
        'record_id',
        'action',
        'before',
        'after',
        'user_id',
        'logged_at',
    ];

    protected $casts = [
        'before' => 'array',
        'after'  => 'array',
        'logged_at' => 'datetime',
    ];
}
