<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $load = ['pdrb'];
    public $timestamps = false; // Disable timestamps

    public function pdrb()
    {
        return $this->hasMany(Pdrb::class);
    }

    public static function getMyRegion()
    {
        if (auth()->user()->satker_id == 1) {
            $region = Region::select(['id as value', 'name as label'])
                ->get();
        } else {
            $region = Region::where('satker_id', auth()->user()->satker_id)
                ->select(['id as value', 'name as label'])
                ->get();
        }

        return $region;
    }

    public static function getMyBps()
    {
        if (auth()->user()->satker_id == 1) {
            $bps = Region::selectRaw('MIN(id) as id, satker_id')
                ->groupBy('satker_id')
                ->orderBy('id')
                ->pluck('id');
            $result = Region::whereIn('id', $bps)
                ->select(['satker_id as value', 'name as label'])
                ->get();
        } else {
            $bps = Region::selectRaw('MIN(id) as id, satker_id')
                ->where('satker_id', auth()->user()->satker_id)
                ->groupBy('satker_id')
                ->orderBy('id')
                ->pluck('id');
            $result = Region::whereIn('id', $bps)
                ->select(['satker_id as value', 'name as label'])
                ->get();
        }

        return $result;
    }

    public static function getMyRegionId()
    {
        if (auth()->user()->satker_id == 1) {
            $region = Region::all()->pluck('id');
        } else {
            $region = Region::where('satker_id', auth()->user()->satker_id)->pluck('id');
        }

        return $region;
    }
}
