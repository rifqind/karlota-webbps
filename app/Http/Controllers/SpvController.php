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
    public function index()
    {
        $data = SummaryPdrb::get();
        $regions = Region::select(['id', 'name'])->get();
        $prefix = basename(request()->route()?->getPrefix() ?? '');
        if ($prefix == 'lapus') $type = 'Lapangan Usaha';
        else if ($prefix == 'peng') $type = 'Pengeluaran';
        else $type = 'Lapangan Usaha';

        $subsectors = Subsector::where('type', $type)->with(['sector.category'])->get();
        $sumTime = SummaryTime::join('periods as p', 'p.id', '=', 'summary_time.period_id')
            ->leftJoin('users as u', 'u.id', '=', 'summary_time.id_user')
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

    public function getSpvData(Request $request)
    {
        $type = $request->query('type', 'Lapangan Usaha');
        $kbli = $request->query('kbli', 'kbli_2020');

        $regions = Region::select(['id', 'name'])->get();
        $subsectors = Subsector::where('type', $type)
            ->where('kbli', $kbli)
            ->with(['sector.category'])
            ->get();

        $sumTime = SummaryTime::join('periods as p', 'p.id', '=', 'summary_time.period_id')
            ->leftJoin('users as u', 'u.id', '=', 'summary_time.id_user')
            ->where('p.type', $type)
            ->where('summary_time.kbli', $kbli)
            ->select([
                'p.type',
                'p.kbli',
                'p.year',
                'p.quarter',
                'p.description',
                'p.status',
                'summary_time.timestamp as waktu',
                'u.name as nama'
            ])
            ->first();

        $dataQuery = SummaryPdrb::where('kbli', $kbli);
        if ($sumTime && $sumTime->quarter) {
            $dataQuery->where('quarter', $sumTime->quarter);
        }
        $data = $dataQuery->get();

        return response()->json([
            'data' => $data,
            'regions' => $regions,
            'subsectors' => $subsectors,
            'type' => $type,
            'kbli' => $kbli,
            'sumTime' => $sumTime,
        ]);
    }
}
