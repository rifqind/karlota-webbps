<?php

namespace App\Models;

use App\HasJobLogs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    use HasFactory, HasJobLogs;

    protected $guarded = ['id'];

    protected $load = ['dataset'];

    protected $fillable = [
        'type',
        'year',
        'status',
        'quarter',
        'description',
        'started_at',
        'ended_at',
    ];
    public function dataset()
    {
        return $this->hasMany(Dataset::class);
    }
}
