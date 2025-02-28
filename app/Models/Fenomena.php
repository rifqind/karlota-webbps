<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fenomena extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    public $timestamps = false;
    protected $fillable = [
        'fenomena_sets',
        'category_id',
        'sector_id',
        'subsector_id',
        'fenomena_qtoq',
        'fenomena_yony',
        'fenomena_implisit'
    ];
    public function fenomenaSet() {
        return $this->belongsTo(FenomenaSet::class, 'fenomena_sets', 'id');
    }
    public function category() {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
    public function sector(){
        return $this->belongsTo(Sector::class, 'sector_id', 'id');
    }
    public function subsector(){
        return $this->belongsTo(Subsector::class, 'subsector_id','id');
    }
}
