<?php

namespace App\Http\Controllers;

use App\Models\Pdrb;
use App\Models\Region;
use App\Models\Subsector;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PdrbController extends Controller
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
        $dataContents = Pdrb::where('dataset_id', 1)
            ->get();
        return Inertia::render('Pdrb/Entri', [
            'type' => $type,
            'subsectors' => $subsectors,
            'dataContents' => $dataContents,
            'regions' => $regions
        ]);
    }
}
