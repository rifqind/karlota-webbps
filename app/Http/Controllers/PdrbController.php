<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use App\Models\Pdrb;
use App\Models\Period;
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
        // $dataContents = Pdrb::where('dataset_id', 1)
        //     ->get();
        return Inertia::render('Pdrb/Entri', [
            'type' => $type,
            'subsectors' => $subsectors,
            // 'dataContents' => $dataContents,
            'regions' => $regions
        ]);
    }

    public function show(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string'],
            'year' => ['required', 'integer'],
            'quarter' => ['required', 'integer'],
            'description' => ['required', 'integer'],
            'dataBefore' => ['sometimes', 'integer', 'nullable'],
            'regions' => ['required', 'integer'],
        ]);
        $period_id = $validated['description'];
        $current_period = Period::where('id', $period_id)->first();
        if (!$validated['dataBefore']) {
            if ($current_period->status == 'Aktif') {
                $previous_period = Period::where('type', $validated['type'])->where('year', $validated['year'] - 1)
                    ->where('quarter', 4)
                    ->latest('id')
                    ->value('id');
            } else {
                $previous_period = Period::where('type', $validated['type'])
                    ->where('year', $validated['year'] - 1)
                    ->where('quarter', 4)
                    ->where('status', '<>', 'Aktif')
                    ->latest('id')
                    ->value('id');
            }
        } else $previous_period = $validated['dataBefore'];

        $current_dataset = Dataset::where('period_id', $period_id)
            ->where('region_id', $validated['regions'])
            ->value('id');
        $previous_dataset = Dataset::where('period_id', $previous_period)
            ->where('region_id', $validated['regions'])
            ->value('id');
            
    }
}
