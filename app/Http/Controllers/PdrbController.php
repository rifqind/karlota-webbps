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
        $subsectors = Subsector::pluck('id');
        $validated = $request->validate([
            'type' => ['required', 'string'],
            'year' => ['required', 'integer'],
            'quarter' => ['required', 'integer'],
            'description' => ['required', 'integer'],
            'dataBefore' => ['sometimes', 'integer', 'nullable'],
            'regions' => ['required', 'integer'],
        ]);
        $period_id = $validated['description'];
        $period_before = ($request->dataBefore) ? $validated['dataBefore'] : null;
        $current_period = Period::where('id', $period_id)->first();
        if (!$period_before) {
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
        } else $previous_period = $period_before;

        $current_dataset = Dataset::where('period_id', $period_id)
            ->where('region_id', $validated['regions'])
            ->value('id');
        $previous_dataset = Dataset::where('period_id', $previous_period)
            ->where('region_id', $validated['regions'])
            ->value('id');

        if ($previous_dataset) {
            $previous_data = Pdrb::where('dataset_id', $previous_dataset)
                ->orderBy('subsector_id')
                ->get();
            //noptification
            //     $message = [
            //     'type' => 'success',
            //     'text' => 'Data periode sebelumnya berhasil diunduh, Tahun ' . $previous_period->year . ' ' . $previous_period->description
            // ];
            // array_push($notification, $message);
        } else {
            $previous_data = [];
            for ($index = 1; $index <= 4; $index++) {
                foreach ($subsectors as $subsector_id) {
                    $singleData = [
                        'subsector_id' => $subsector_id,
                        'type' => $validated['type'],
                        'year' => $validated['year'] - 1,
                        'quarter' => $index,
                        'region_id' => $validated['regions'],
                        'adhb' => null,
                        'adhk' => null,
                    ];
                    array_push($previous_data, $singleData);
                }
            }

            // $message = [
            //     'type' => 'warning',
            //     'text' => 'Data periode sebelumnya tidak ada / belum final, summary tidak dapat ditampilkan'
            // ];
            // array_push($notification, $message);
        }

        if ($current_dataset) {
            $current_data = Pdrb::where('dataset_id', $current_dataset)
                ->orderBy('subsector_id')
                ->get();
        } else {
            //create new datasets
            $current_object_dataset = Dataset::create([
                'type' => $validated['type'],
                'period_id' => $period_id,
                'region_id' => $validated['regions'],
                'year' => $validated['year'],
                'quarter' => $validated['quarter'],
                'status' => 'Entry',
            ]);

            for ($index = 1; $index <= 4; $index++) {
                if ($index <= $validated['quarter']) {

                    $inputData = [];
                    foreach ($subsectors as $subsector_id) {
                        $singleData = [
                            'subsector_id' => $subsector_id,
                            'dataset_id' => $current_object_dataset->id,
                            'year' => $validated['year'],
                            'quarter' => $index,
                        ];
                        array_push($inputData, $singleData);
                    }

                    Pdrb::insert($inputData);
                }
            }
            $current_data = Pdrb::where('dataset_id', $current_object_dataset->id)
                ->orderBy('subsector_id')
                ->get();
        }
        return  response()->json([
            'current_data' => $current_data,
            'previous_data' => $previous_data
        ]);
    }
}
