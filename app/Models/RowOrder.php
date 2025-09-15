<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RowOrder extends Model
{
    //
    use HasFactory;
    protected $fillable = ['orders', 'sekunder_id'];
    public $timestamps = false;
}
