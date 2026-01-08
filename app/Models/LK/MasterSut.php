<?php

namespace App\Models\LK;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterSut extends Model
{
    //
    use HasFactory;
    public $table = 'master_sut_irio';
    protected $fillable = ['label'];
    public $timestamps = false;
}
