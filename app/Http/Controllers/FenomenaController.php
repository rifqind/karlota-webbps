<?php

namespace App\Http\Controllers;

use App\Models\Fenomena;
use App\Models\Region;
use App\Models\Subsector;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FenomenaController extends Controller
{
    //
    public function entri()
    {
        $prefix = request()->route()->getPrefix();
        if ($prefix == 'lapus') $type = 'Lapangan Usaha';
        else if ($prefix == 'peng') $type = 'Pengeluaran';
        $regions = Region::getMyRegion();
        $subsectors = Subsector::where('type', $type)
            ->with(['sector.category'])
            ->get();
        return Inertia::render('Fenomena/Entri', [
            'type' => $type,
            'subsectors' => $subsectors,
            'regions' => $regions
        ]);
    }

    public function show(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string'],
            'year' => ['required', 'integer'],
            'quarter' => ['required', 'integer'],
            'regions' => ['required', 'integer'],
        ]);
        $notification = [];
        $fenomena = Fenomena::where('type', $validated['type'])
            ->where('year', $validated['year'])
            ->where('quarter', $validated['quarter'])
            ->where('regions', $validated['regions'])
            ->get();
        if ($fenomena->isEmpty()) {
        
        }
    }

    public function monitoring() {}
}
