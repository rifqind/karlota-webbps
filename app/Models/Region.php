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

    private static function getAuthUser()
    {
        $user = auth()->user();
        if (!$user && request()) {
            $payload = request()->attributes->get('auth_payload');
            if ($payload && isset($payload['sub'])) {
                $user = User::find($payload['sub']);
            }
        }
        return $user;
    }

    public static function getMyRegion()
    {
        $user = self::getAuthUser();
        if (!$user || $user->satker_id == 1) {
            $region = Region::select(['id as value', 'name as label'])
                ->get();
        } else {
            $region = Region::where('satker_id', $user->satker_id)
                ->select(['id as value', 'name as label'])
                ->get();
        }

        return $region;
    }

    public static function getMyBps()
    {
        $user = self::getAuthUser();
        if (!$user || $user->satker_id == 1) {
            $bps = Region::selectRaw('MIN(id) as id, satker_id')
                ->groupBy('satker_id')
                ->orderBy('id')
                ->pluck('id');
            $result = Region::whereIn('id', $bps)
                ->select(['satker_id as value', 'name as label'])
                ->get();
        } else {
            $bps = Region::selectRaw('MIN(id) as id, satker_id')
                ->where('satker_id', $user->satker_id)
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
        $user = self::getAuthUser();
        if (!$user || $user->satker_id == 1) {
            $region = Region::all()->pluck('id');
        } else {
            $region = Region::where('satker_id', $user->satker_id)->pluck('id');
        }

        return $region;
    }
}
