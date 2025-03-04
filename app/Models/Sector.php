<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    use HasFactory;
 
    protected $guarded = ['id'];
    public $timestamps = false; // Disable timestamps

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function subsector()
    {
        return $this->hasMany(Subsector::class);
    }
}
