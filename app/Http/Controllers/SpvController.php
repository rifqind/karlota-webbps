<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Subsector;
use App\Models\SummaryPdrb;
use App\Models\SummaryTime;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SpvController extends Controller
{
    //
    public function index()
    {
        $data = SummaryPdrb::get();
        $regions = Region::select(['id', 'name'])->get();
        $prefix = request()->route()->getPrefix();
        if ($prefix == 'lapus') $type = 'Lapangan Usaha';
        else if ($prefix == 'peng') $type = 'Pengeluaran';
        $subsectors = Subsector::where('type', $type)->with(['sector.category'])->get();
        $sumTime = SummaryTime::join('periods as p', 'p.id', '=', 'summary_time.period_id')
            ->join('users as u', 'u.id', '=', 'summary_time.id_user')
            ->where('p.type', $type)
            ->select([
                'p.type',
                'p.year',
                'p.quarter',
                'p.description',
                'p.status',
                'summary_time.timestamp as waktu',
                'u.name as nama'
            ])
            ->first();
        return Inertia::render('Summary/Index', [
            'data' => $data,
            'regions' => $regions,
            'subsectors' => $subsectors,
            'type' => $type,
            'sumTime' => $sumTime
        ]);
    }
}
