<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FenomenaSet extends Model
{
    //
    use HasFactory;
    protected $guarded = ['id'];
    public $timestamps = false;
    protected $fillable = [
        'type', 'region_id', 'year', 'quarter', 'status'
    ];
    protected $table = 'fenomena_sets';
    public function region() {
        return $this->belongsTo(Region::class);
    }
}
