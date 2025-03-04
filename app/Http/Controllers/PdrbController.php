<?php

namespace App\Http\Controllers;

use App\Models\Adjustment;
use App\Models\Dataset;
use App\Models\Pdrb;
use App\Models\Period;
use App\Models\Region;
use App\Models\Sector;
use App\Models\Subsector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        return Inertia::render('Pdrb/Entri', [
            'type' => $type,
            'subsectors' => $subsectors,
            'regions' => $regions
        ]);
    }

    public function show(Request $request)
    {
        $subsectors = Subsector::where('type', $request->type)->pluck('id');
        $validated = $request->validate([
            'type' => ['required', 'string'],
            'year' => ['required', 'integer'],
            'quarter' => ['required', 'integer'],
            'description' => ['required', 'integer'],
            'dataBefore' => ['sometimes', 'integer', 'nullable'],
            'regions' => ['required', 'integer'],
        ]);
        $notification = [];
        try {
            //code...
            DB::beginTransaction();
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

            $periode_before_year = Period::find($previous_period);
            $current_dataset = Dataset::where('period_id', $period_id)
                ->where('region_id', $validated['regions'])
                ->where('type', $validated['type'])
                ->value('id');
            $previous_dataset = Dataset::where('period_id', $previous_period)
                ->where('region_id', $validated['regions'])
                ->where('type', $validated['type'])
                ->value('id');

            if ($previous_dataset) {
                $previous_data = Pdrb::where('dataset_id', $previous_dataset)
                    ->orderBy('subsector_id')
                    ->get();
                $message = [
                    'type' => 'success',
                    'message' => 'Data periode sebelumnya berhasil diunduh, Tahun ' . $periode_before_year->year . ' ' . $periode_before_year->description
                ];
                array_push($notification, $message);
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

                $message = [
                    'type' => 'error',
                    'message' => 'Data periode sebelumnya tidak ada / belum final, summary tidak dapat ditampilkan'
                ];
                array_push($notification, $message);
            }

            if ($current_dataset) {
                $current_data = Pdrb::where('dataset_id', $current_dataset)
                    ->orderBy('subsector_id')
                    ->get();
                $message = [
                    'type' => 'success',
                    'message' => 'Mengambil Data PDRB Periode Ini'
                ];
                array_push($notification, $message);
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
                $current_dataset = $current_object_dataset->id;
                $message = [
                    'type' => 'success',
                    'message' => 'Inisiasi Data PDRB Periode Ini'
                ];
                array_push($notification, $message);
            }
            $dataset = Dataset::find($current_dataset);
            DB::commit();
            return  response()->json([
                'current_data' => $current_data,
                'previous_data' => $previous_data,
                'notification' => $notification,
                'dataset' => $dataset
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Ada kesalahan ketika mengambil data dari database'
            ];
            array_push($notification, $message);
            return response()->json(['notification' => $notification], 500);
        }
    }

    public function saveEntri(Request $request)
    {
        $notification = [];
        $type = $request->type;
        if ($type == 'Lapangan Usaha') $route = 'lapus.';
        else if ($type == 'Pengeluaran') $route = 'peng.';
        try {
            //code...
            DB::beginTransaction();
            $data = $request->validate([
                'dataContents' => ['required', 'array'],
                'dataContents.*.id' => ['required', 'integer', 'exists:pdrbs,id'],
                'dataContents.*.dataset_id' => ['required', 'integer'],
                'dataContents.*.year' => ['required', 'integer'],
                'dataContents.*.quarter' => ['required', 'integer'],
                'dataContents.*.subsector_id' => ['required', 'integer'],
                'dataContents.*.adhb' => ['sometimes', 'numeric', 'nullable'],
                'dataContents.*.adhk' => ['sometimes', 'numeric', 'nullable'],
            ]);
            foreach ($data['dataContents'] as $key => $value) {
                # code...
                Pdrb::where('id', $value['id'])
                    ->update([
                        'dataset_id' => $value['dataset_id'],
                        'year' => $value['year'],
                        'quarter' => $value['quarter'],
                        'subsector_id' => $value['subsector_id'],
                        'adhb' => $value['adhb'],
                        'adhk' => $value['adhk'],
                    ]);
            }
            $message = [
                'type' => 'success',
                'message' => 'Berhasil Simpan Data PDRB'
            ];
            array_push($notification, $message);
            DB::commit();
            return redirect()->route($route . 'entri')->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Ada Kesalahan dalam Data yang disimpan',
                'errors' => $th->getMessage()
            ];
            array_push($notification, $message);
            return redirect()->route($route . 'entri')->with('notification', $notification);
        }
    }

    public function submitEntri(Request $request)
    {
        $notification = [];
        $type = $request->type;
        if ($type == 'Lapangan Usaha') $route = 'lapus.';
        else if ($type == 'Pengeluaran') $route = 'peng.';
        try {
            //code...
            DB::beginTransaction();
            if ($type == 'Lapangan Usaha') $route = 'lapus.';
            else if ($type == 'Pengeluaran') $route = 'peng.';
            $request->validate([
                'id' => ['required', 'integer'],
                'dataContents' => ['required', 'array'],
                'dataContents.*.id' => ['required', 'integer', 'exists:pdrbs,id'],
                'dataContents.*.dataset_id' => ['required', 'integer'],
                'dataContents.*.year' => ['required', 'integer'],
                'dataContents.*.quarter' => ['required', 'integer'],
                'dataContents.*.subsector_id' => ['required', 'integer'],
                'dataContents.*.adhb' => ['sometimes', 'numeric', 'nullable'],
                'dataContents.*.adhk' => ['sometimes', 'numeric', 'nullable'],
            ]);
            $dataset = Dataset::where('id', $request->id)
                ->update(['status' => 'Submitted']);
            foreach ($request->dataContents as $key => $value) {
                # code...
                Pdrb::where('id', $value['id'])
                    ->update([
                        'dataset_id' => $value['dataset_id'],
                        'year' => $value['year'],
                        'quarter' => $value['quarter'],
                        'subsector_id' => $value['subsector_id'],
                        'adhb' => $value['adhb'],
                        'adhk' => $value['adhk'],
                    ]);
            }
            $message = [
                'type' => 'success',
                'message' => 'Data sudah disubmit dan disimpan'
            ];
            array_push($notification, $message);
            DB::commit();
            return redirect()->route($route . 'entri')->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Ada Kesalahan ketika submit',
                'errors' => $th->getMessage()
            ];
            array_push($notification, $message);
            return redirect()->route($route . 'entri')->with('notification', $notification);
        }
    }

    public function unsubmitEntri(Request $request)
    {
        $notification = [];
        $type = $request->type;
        if ($type == 'Lapangan Usaha') $route = 'lapus.';
        else if ($type == 'Pengeluaran') $route = 'peng.';
        try {
            //code...
            DB::beginTransaction();
            if ($type == 'Lapangan Usaha') $route = 'lapus.';
            else if ($type == 'Pengeluaran') $route = 'peng.';
            $request->validate([
                'id' => ['required', 'integer']
            ]);
            $dataset = Dataset::where('id', $request->id)
                ->update(['status' => 'Entry']);
            $message = [
                'type' => 'success',
                'message' => 'Data kembali ke status Entry'
            ];
            array_push($notification, $message);
            DB::commit();
            return redirect()->route($route . 'entri')->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Ada Kesalahan ketika unsubmit',
                'errors' => $th->getMessage()
            ];
            array_push($notification, $message);
            return redirect()->route($route . 'entri')->with('notification', $notification);
        }
    }

    public function copyEntri(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string'],
            'year' => ['required', 'integer'],
            'quarter' => ['required', 'integer'],
            'description' => ['required', 'integer'],
            'regions' => ['required', 'integer'],
        ]);
        try {
            //code...
            DB::beginTransaction();
            $notification = [];
            $period_id = $validated['description'];
            $current_dataset = Dataset::where('period_id', $period_id)
                ->where('region_id', $validated['regions'])
                ->where('type', $validated['type'])
                ->value('id');
            if ($current_dataset) {
                $current_data = Pdrb::where('dataset_id', $current_dataset)
                    ->orderBy('subsector_id')
                    ->get();
                $message = [
                    'type' => 'success',
                    'message' => 'Mengambil Data PDRB Periode Ini'
                ];
                array_push($notification, $message);
            } else {
                $message = [
                    'type' => 'error',
                    'message' => 'Data yang ingin di-salin tidak ada'
                ];
                array_push($notification, $message);
                return response()->json(['notification' => $notification], 500);
            }
            DB::commit();
            return  response()->json([
                'current_data' => $current_data,
                'notification' => $notification,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Ada kesalahan server ketika menyalin data'
            ];
            array_push($notification, $message);
            return response()->json(['notification' => $notification], 500);
        }
    }

    public function adjustment()
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

    public function getAdjustment(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string'],
            'year' => ['required', 'integer'],
            'quarter' => ['required', 'integer'],
            'description' => ['required', 'integer'],
            'dataBefore' => ['sometimes', 'integer', 'nullable'],
            'subsectors' => ['required', 'string'],
        ]);
        $regions = Region::select('id as value', 'name as label')->get();
        $type = $validated['type'];
        $period_id = $validated['description'];
        $period_before = ($request->dataBefore) ? $validated['dataBefore'] : null;
        $notification = [];
        $current_period = Period::where('id', $validated['description'])->first();
        if (!$period_before) {
            if ($current_period->status == 'Aktif') {
                $previous_period = Period::where('type', $type)
                    ->where('year', $validated['year'] - 1)
                    ->where('quarter', 4)
                    ->latest('id')
                    ->value('id');
            } else {
                $previous_period = Period::where('type', $type)
                    ->where('year', $validated['year'] - 1)
                    ->where('quarter', 4)
                    ->where('status', '<>', 'Aktif')
                    ->latest('id')
                    ->value('id');
            }
        } else $previous_period = $period_before;

        $exploded_subsectors = explode('-', $validated['subsectors']);
        $typeOfSubsector = $exploded_subsectors[0];
        $categoryId = $exploded_subsectors[1];
        $sectorId = $exploded_subsectors[2];
        $subsectorId = $exploded_subsectors[3];

        $periode_before_year = Period::find($previous_period);
        $current_dataset = Dataset::where('period_id', $period_id)
            ->where('type', $validated['type'])
            ->pluck('id');
        $previous_dataset = Dataset::where('period_id', $previous_period)
            ->where('type', $validated['type'])
            ->pluck('id');

        $list_region = [];
        foreach ($regions as $key => $reg) {
            # code...
            $cek_previous = Dataset::where('period_id', $previous_period)
                ->where('region_id', $reg->value)
                ->where('type', $type)
                ->first();
            if (!$cek_previous) {
                $message = [
                    'type' => 'error',
                    'message' => 'Data ' . $reg->label . ' periode sebelumnya tidak ada',
                ];
                array_push($notification, $message);
            } else {
                $message = [
                    'type' => 'success',
                    'message' => 'Data ' . $reg->label . ' periode sebelumnya berhasil diambil',
                ];
                array_push($notification, $message);
            }
            $cek_current = Dataset::where('period_id', $period_id)
                ->where('region_id', $reg->value)
                ->where('type', $type)
                ->first();
            if (!$cek_current) {
                $message = [
                    'type' => 'error',
                    'message' => 'Data ' . $reg->label . ' periode ini tidak ada',
                ];
                array_push($notification, $message);
            } else {
                $message = [
                    'type' => 'success',
                    'message' => 'Data ' . $reg->label . ' periode ini berhasil diambil',
                ];
                array_push($notification, $message);
                array_push($list_region, $reg->value);
            }
        }
        $previous_data = collect(); // Ensure $previous_data is always initialized
        $current_data = collect();

        if ($typeOfSubsector == 'subsector') {
            if ($previous_dataset->isNotEmpty()) {
                $previous_for = Pdrb::whereIn('dataset_id', $previous_dataset)
                    ->where('subsector_id', $subsectorId)
                    ->pluck('id');
                $adjustment = Adjustment::whereIn('pdrb_id', $previous_for)
                    ->pluck('id');
                $previous_data = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                    ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                    ->whereIn('pdrbs.dataset_id', $previous_dataset)
                    ->where('pdrbs.subsector_id', $subsectorId)
                    ->orderBy('d.region_id', 'asc')
                    ->select(
                        'pdrbs.id',
                        'pdrbs.dataset_id',
                        'pdrbs.year',
                        'pdrbs.quarter',
                        'pdrbs.subsector_id',
                        'pdrbs.adhb',
                        'pdrbs.adhk',
                        'adj.adhb as adj_adhb',
                        'adj.adhk as adj_adhk',
                        'd.region_id as region_id'
                    )
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'dataset_id' => $item->dataset_id,
                            'year' => $item->year,
                            'quarter' => $item->quarter,
                            'subsector_id' => $item->subsector_id,
                            'adhb' => $item->adhb,
                            'adhk' => $item->adhk,
                            'adj_adhb' => $item->adj_adhb ?? null,
                            'adj_adhk' => $item->adj_adhk ?? null,
                            'region_id' => $item->region_id
                        ];
                    });
            }

            if ($current_dataset->isNotEmpty()) {
                $current_for = Pdrb::whereIn('dataset_id', $current_dataset)
                    ->where('subsector_id', $subsectorId)
                    ->pluck('id');
                $adjustment = Adjustment::whereIn('pdrb_id', $current_for)
                    ->pluck('id');
                $current_data = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                    ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                    ->whereIn('pdrbs.dataset_id', $current_dataset)
                    ->where('pdrbs.subsector_id', $subsectorId)
                    ->orderBy('d.region_id', 'asc')
                    ->select(
                        'pdrbs.id',
                        'pdrbs.dataset_id',
                        'pdrbs.year',
                        'pdrbs.quarter',
                        'pdrbs.subsector_id',
                        'pdrbs.adhb',
                        'pdrbs.adhk',
                        'adj.adhb as adj_adhb',
                        'adj.adhk as adj_adhk',
                        'd.region_id as region_id'
                    )
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'dataset_id' => $item->dataset_id,
                            'year' => $item->year,
                            'quarter' => $item->quarter,
                            'subsector_id' => $item->subsector_id,
                            'adhb' => $item->adhb,
                            'adhk' => $item->adhk,
                            'adj_adhb' => $item->adj_adhb ?? null,
                            'adj_adhk' => $item->adj_adhk ?? null,
                            'region_id' => $item->region_id
                        ];
                    });
            }
        } else if ($typeOfSubsector == 'sector') {
            if ($sectorId == '54') {
                $importId = ['69'];
                if ($previous_dataset->isNotEmpty()) {
                    $subsectorForSearch = Subsector::where('sector_id', $sectorId)->pluck('id');
                    $total = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                        ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                        ->whereIn('pdrbs.dataset_id', $previous_dataset)
                        ->whereIn('pdrbs.subsector_id', $subsectorForSearch)
                        ->whereNotIn('pdrbs.subsector_id', $importId)
                        ->orderBy('d.region_id', 'asc')
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectRaw(
                            'pdrbs.year,
                            pdrbs.quarter,
                            SUM(pdrbs.adhb) as adhb,
                            SUM(pdrbs.adhk) as adhk,
                            SUM(adj.adhb) as adj_adhb,
                            SUM(adj.adhk) as adj_adhk,
                            d.region_id as region_id'
                        )
                        ->get()
                        ->keyBy(fn($item) => "{$item->region_id}_{$item->year}_{$item->quarter}");

                    $import = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                        ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                        ->whereIn('pdrbs.dataset_id', $previous_dataset)
                        ->whereIn('subsector_id', $importId)
                        ->orderBy('d.region_id', 'asc')
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectRaw(
                            'pdrbs.year,
                            pdrbs.quarter,
                            SUM(pdrbs.adhb) as adhb,
                            SUM(pdrbs.adhk) as adhk,
                            SUM(adj.adhb) as adj_adhb,
                            SUM(adj.adhk) as adj_adhk,
                            d.region_id as region_id'
                        )
                        ->get()
                        ->keyBy(fn($item) => "{$item->region_id}_{$item->year}_{$item->quarter}");

                    $previous_data = collect($total)->map(function ($prev) use ($import, $sectorId) {
                        $key = "{$prev->region_id}_{$prev->year}_{$prev->quarter}";
                        $imp = $import[$key] ?? (object) ['adhb' => 0, 'adhk' => 0, 'adj_adhb' => 0, 'adj_adhk' => 0];

                        return [
                            'year' => $prev->year,
                            'quarter' => $prev->quarter,
                            'sector_id' => $sectorId,
                            'adhb' => $prev->adhb - $imp->adhb,
                            'adhk' => $prev->adhk - $imp->adhk,
                            'adj_adhb' => ($prev->adj_adhb ?? 0) - ($imp->adj_adhb ?? 0),
                            'adj_adhk' => ($prev->adj_adhk ?? 0) - ($imp->adj_adhk ?? 0),
                            'region_id' => $prev->region_id
                        ];
                    })->values();
                }
                if ($current_dataset->isNotEmpty()) {
                    $subsectorForSearch = Subsector::where('sector_id', $sectorId)->pluck('id');
                    $total = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                        ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                        ->whereIn('pdrbs.dataset_id', $current_dataset)
                        ->whereIn('pdrbs.subsector_id', $subsectorForSearch)
                        ->whereNotIn('pdrbs.subsector_id', $importId)
                        ->orderBy('d.region_id', 'asc')
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectRaw(
                            'pdrbs.year,
                            pdrbs.quarter,
                            SUM(pdrbs.adhb) as adhb,
                            SUM(pdrbs.adhk) as adhk,
                            SUM(adj.adhb) as adj_adhb,
                            SUM(adj.adhk) as adj_adhk,
                            d.region_id as region_id'
                        )
                        ->get()
                        ->keyBy(fn($item) => "{$item->region_id}_{$item->year}_{$item->quarter}");

                    $import = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                        ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                        ->whereIn('pdrbs.dataset_id', $current_dataset)
                        ->whereIn('subsector_id', $importId)
                        ->orderBy('d.region_id', 'asc')
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectRaw(
                            'pdrbs.year,
                            pdrbs.quarter,
                            SUM(pdrbs.adhb) as adhb,
                            SUM(pdrbs.adhk) as adhk,
                            SUM(adj.adhb) as adj_adhb,
                            SUM(adj.adhk) as adj_adhk,
                            d.region_id as region_id'
                        )
                        ->get()
                        ->keyBy(fn($item) => "{$item->region_id}_{$item->year}_{$item->quarter}");

                    $current_data = collect($total)->map(function ($prev) use ($import, $sectorId) {
                        $key = "{$prev->region_id}_{$prev->year}_{$prev->quarter}";
                        $imp = $import[$key] ?? (object) ['adhb' => 0, 'adhk' => 0, 'adj_adhb' => 0, 'adj_adhk' => 0];

                        return [
                            'year' => $prev->year,
                            'quarter' => $prev->quarter,
                            'sector_id' => $sectorId,
                            'adhb' => $prev->adhb - $imp->adhb,
                            'adhk' => $prev->adhk - $imp->adhk,
                            'adj_adhb' => ($prev->adj_adhb ?? 0) - ($imp->adj_adhb ?? 0),
                            'adj_adhk' => ($prev->adj_adhk ?? 0) - ($imp->adj_adhk ?? 0),
                            'region_id' => $prev->region_id
                        ];
                    })->values();
                }
            } else {
                if ($previous_dataset->isNotEmpty()) {
                    $subsectorForSearch = Subsector::where('sector_id', $sectorId)->pluck('id');
                    $previous_for = Pdrb::whereIn('dataset_id', $previous_dataset)
                        ->where('subsector_id', $subsectorId)
                        ->pluck('id');
                    $previous_data = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                        ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                        ->whereIn('pdrbs.dataset_id', $previous_dataset)
                        ->whereIn('pdrbs.subsector_id', $subsectorForSearch)
                        ->orderBy('d.region_id', 'asc')
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectRaw(
                            'pdrbs.year,
                            pdrbs.quarter,
                            SUM(pdrbs.adhb) as adhb,
                            SUM(pdrbs.adhk) as adhk,
                            SUM(adj.adhb) as adj_adhb,
                            SUM(adj.adhk) as adj_adhk,
                            d.region_id as region_id'
                        )
                        ->get()
                        ->map(function ($item) use ($sectorId) {
                            return [
                                'year' => $item->year,
                                'quarter' => $item->quarter,
                                'sector_id' => $sectorId,
                                'adhb' => $item->adhb,
                                'adhk' => $item->adhk,
                                'adj_adhb' => $item->adj_adhb ?? null,
                                'adj_adhk' => $item->adj_adhk ?? null,
                                'region_id' => $item->region_id
                            ];
                        });
                }
                if ($current_dataset->isNotEmpty()) {
                    $subsectorForSearch = Subsector::where('sector_id', $sectorId)->pluck('id');
                    $current_for = Pdrb::whereIn('dataset_id', $current_dataset)
                        ->where('subsector_id', $subsectorId)
                        ->pluck('id');
                    $current_data = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                        ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                        ->whereIn('pdrbs.dataset_id', $current_dataset)
                        ->whereIn('pdrbs.subsector_id', $subsectorForSearch)
                        ->orderBy('d.region_id', 'asc')
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectRaw(
                            'pdrbs.year,
                            pdrbs.quarter,
                            SUM(pdrbs.adhb) as adhb,
                            SUM(pdrbs.adhk) as adhk,
                            SUM(adj.adhb) as adj_adhb,
                            SUM(adj.adhk) as adj_adhk,
                            d.region_id as region_id'
                        )
                        ->get()
                        ->map(function ($item) use ($sectorId) {
                            return [
                                'year' => $item->year,
                                'quarter' => $item->quarter,
                                'sector_id' => $sectorId,
                                'adhb' => $item->adhb,
                                'adhk' => $item->adhk,
                                'adj_adhb' => $item->adj_adhb ?? null,
                                'adj_adhk' => $item->adj_adhk ?? null,
                                'region_id' => $item->region_id
                            ];
                        });
                }
            }
        } else if ($typeOfSubsector == 'category') {
            if ($previous_dataset->isNotEmpty()) {
                $sectorForSearch = Sector::where('category_id', $categoryId)->pluck('id');
                $subsectorForSearch = Subsector::whereIn('sector_id', $sectorForSearch)->pluck('id');
                $previous_for = Pdrb::whereIn('dataset_id', $previous_dataset)
                    ->where('subsector_id', $subsectorId)
                    ->pluck('id');
                $adjustment = Adjustment::whereIn('pdrb_id', $previous_for)
                    ->pluck('id');
                $previous_data = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                    ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                    ->whereIn('pdrbs.dataset_id', $previous_dataset)
                    ->whereIn('pdrbs.subsector_id', $subsectorForSearch)
                    ->orderBy('d.region_id', 'asc')
                    ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                    ->selectRaw(
                        'pdrbs.year,
                        pdrbs.quarter,
                        SUM(pdrbs.adhb) as adhb,
                        SUM(pdrbs.adhk) as adhk,
                        SUM(adj.adhb) as adj_adhb,
                        SUM(adj.adhk) as adj_adhk,
                        d.region_id as region_id'
                    )
                    ->get()
                    ->map(function ($item) use ($sectorId) {
                        return [
                            'year' => $item->year,
                            'quarter' => $item->quarter,
                            'sector_id' => $sectorId,
                            'adhb' => $item->adhb,
                            'adhk' => $item->adhk,
                            'adj_adhb' => $item->adj_adhb ?? null,
                            'adj_adhk' => $item->adj_adhk ?? null,
                            'region_id' => $item->region_id
                        ];
                    });
            }
            if ($current_dataset->isNotEmpty()) {
                $sectorForSearch = Sector::where('category_id', $categoryId)->pluck('id');
                $subsectorForSearch = Subsector::whereIn('sector_id', $sectorForSearch)->pluck('id');
                $current_for = Pdrb::whereIn('dataset_id', $current_dataset)
                    ->where('subsector_id', $subsectorId)
                    ->pluck('id');
                $adjustment = Adjustment::whereIn('pdrb_id', $current_for)
                    ->pluck('id');
                $current_data = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                    ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                    ->whereIn('pdrbs.dataset_id', $current_dataset)
                    ->whereIn('pdrbs.subsector_id', $subsectorForSearch)
                    ->orderBy('d.region_id', 'asc')
                    ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                    ->selectRaw(
                        'pdrbs.year,
                        pdrbs.quarter,
                        SUM(pdrbs.adhb) as adhb,
                        SUM(pdrbs.adhk) as adhk,
                        SUM(adj.adhb) as adj_adhb,
                        SUM(adj.adhk) as adj_adhk,
                        d.region_id as region_id'
                    )
                    ->get()
                    ->map(function ($item) use ($sectorId) {
                        return [
                            'year' => $item->year,
                            'quarter' => $item->quarter,
                            'sector_id' => $sectorId,
                            'adhb' => $item->adhb,
                            'adhk' => $item->adhk,
                            'adj_adhb' => $item->adj_adhb ?? null,
                            'adj_adhk' => $item->adj_adhk ?? null,
                            'region_id' => $item->region_id
                        ];
                    });
            }
        } else if ($typeOfSubsector == 'total') {
            if ($type == 'Lapangan Usaha') {
                if ($previous_dataset->isNotEmpty()) {
                    $previous_data = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                        ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                        ->whereIn('pdrbs.dataset_id', $previous_dataset)
                        ->orderBy('d.region_id', 'asc')
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectRaw(
                            'pdrbs.year,
                            pdrbs.quarter,
                            SUM(pdrbs.adhb) as adhb,
                            SUM(pdrbs.adhk) as adhk,
                            SUM(adj.adhb) as adj_adhb,
                            SUM(adj.adhk) as adj_adhk,
                            d.region_id as region_id'
                        )
                        ->get()
                        ->map(function ($item) use ($sectorId) {
                            return [
                                'year' => $item->year,
                                'quarter' => $item->quarter,
                                'sector_id' => $sectorId,
                                'adhb' => $item->adhb,
                                'adhk' => $item->adhk,
                                'adj_adhb' => $item->adj_adhb ?? null,
                                'adj_adhk' => $item->adj_adhk ?? null,
                                'region_id' => $item->region_id
                            ];
                        });
                }
                if ($current_dataset->isNotEmpty()) {
                    $current_data = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                        ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                        ->whereIn('pdrbs.dataset_id', $current_dataset)
                        ->orderBy('d.region_id', 'asc')
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectRaw(
                            'pdrbs.year,
                            pdrbs.quarter,
                            SUM(pdrbs.adhb) as adhb,
                            SUM(pdrbs.adhk) as adhk,
                            SUM(adj.adhb) as adj_adhb,
                            SUM(adj.adhk) as adj_adhk,
                            d.region_id as region_id'
                        )
                        ->get()
                        ->map(function ($item) use ($sectorId) {
                            return [
                                'year' => $item->year,
                                'quarter' => $item->quarter,
                                'sector_id' => $sectorId,
                                'adhb' => $item->adhb,
                                'adhk' => $item->adhk,
                                'adj_adhb' => $item->adj_adhb ?? null,
                                'adj_adhk' => $item->adj_adhk ?? null,
                                'region_id' => $item->region_id
                            ];
                        });
                }
            } else if ($type == 'Pengeluaran') {
                $importId = ['69'];
                if ($previous_dataset->isNotEmpty()) {
                    $total = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                        ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                        ->whereIn('pdrbs.dataset_id', $previous_dataset)
                        ->whereNotIn('subsector_id', $importId)
                        ->orderBy('d.region_id', 'asc')
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectRaw(
                            'pdrbs.year,
                            pdrbs.quarter,
                            SUM(pdrbs.adhb) as adhb,
                            SUM(pdrbs.adhk) as adhk,
                            SUM(adj.adhb) as adj_adhb,
                            SUM(adj.adhk) as adj_adhk,
                            d.region_id as region_id'
                        )
                        ->get()
                        ->keyBy(fn($item) => "{$item->region_id}_{$item->year}_{$item->quarter}");

                    $import = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                        ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                        ->whereIn('pdrbs.dataset_id', $previous_dataset)
                        ->whereIn('subsector_id', $importId)
                        ->orderBy('d.region_id', 'asc')
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectRaw(
                            'pdrbs.year,
                            pdrbs.quarter,
                            SUM(pdrbs.adhb) as adhb,
                            SUM(pdrbs.adhk) as adhk,
                            SUM(adj.adhb) as adj_adhb,
                            SUM(adj.adhk) as adj_adhk,
                            d.region_id as region_id'
                        )
                        ->get()
                        ->keyBy(fn($item) => "{$item->region_id}_{$item->year}_{$item->quarter}");

                    $previous_data = collect($total)->map(function ($prev) use ($import, $sectorId) {
                        $key = "{$prev->region_id}_{$prev->year}_{$prev->quarter}";
                        $imp = $import[$key] ?? (object) ['adhb' => 0, 'adhk' => 0, 'adj_adhb' => 0, 'adj_adhk' => 0];

                        return [
                            'year' => $prev->year,
                            'quarter' => $prev->quarter,
                            'sector_id' => $sectorId,
                            'adhb' => $prev->adhb - $imp->adhb,
                            'adhk' => $prev->adhk - $imp->adhk,
                            'adj_adhb' => ($prev->adj_adhb ?? 0) - ($imp->adj_adhb ?? 0),
                            'adj_adhk' => ($prev->adj_adhk ?? 0) - ($imp->adj_adhk ?? 0),
                            'region_id' => $prev->region_id
                        ];
                    })->values();
                }
                if ($current_dataset->isNotEmpty()) {
                    $total = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                        ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                        ->whereIn('pdrbs.dataset_id', $current_dataset)
                        ->whereNotIn('subsector_id', $importId)
                        ->orderBy('d.region_id', 'asc')
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectRaw(
                            'pdrbs.year,
                            pdrbs.quarter,
                            SUM(pdrbs.adhb) as adhb,
                            SUM(pdrbs.adhk) as adhk,
                            SUM(adj.adhb) as adj_adhb,
                            SUM(adj.adhk) as adj_adhk,
                            d.region_id as region_id'
                        )
                        ->get()
                        ->keyBy(fn($item) => "{$item->region_id}_{$item->year}_{$item->quarter}");

                    $import = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                        ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                        ->whereIn('pdrbs.dataset_id', $current_dataset)
                        ->whereIn('subsector_id', $importId)
                        ->orderBy('d.region_id', 'asc')
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectRaw(
                            'pdrbs.year,
                            pdrbs.quarter,
                            SUM(pdrbs.adhb) as adhb,
                            SUM(pdrbs.adhk) as adhk,
                            SUM(adj.adhb) as adj_adhb,
                            SUM(adj.adhk) as adj_adhk,
                            d.region_id as region_id'
                        )
                        ->get()
                        ->keyBy(fn($item) => "{$item->region_id}_{$item->year}_{$item->quarter}");

                    $current_data = collect($total)->map(function ($prev) use ($import, $sectorId) {
                        $key = "{$prev->region_id}_{$prev->year}_{$prev->quarter}";
                        $imp = $import[$key] ?? (object) ['adhb' => 0, 'adhk' => 0, 'adj_adhb' => 0, 'adj_adhk' => 0];

                        return [
                            'year' => $prev->year,
                            'quarter' => $prev->quarter,
                            'sector_id' => $sectorId,
                            'adhb' => $prev->adhb - $imp->adhb,
                            'adhk' => $prev->adhk - $imp->adhk,
                            'adj_adhb' => ($prev->adj_adhb ?? 0) - ($imp->adj_adhb ?? 0),
                            'adj_adhk' => ($prev->adj_adhk ?? 0) - ($imp->adj_adhk ?? 0),
                            'region_id' => $prev->region_id
                        ];
                    })->values();
                }
            }
        }
        $message = [
            'type' => 'success',
            'message' => 'Data Adjustment sudah berhasil diambil',
        ];
        array_push($notification, $message);
        return response()->json([
            'previous_data' => $previous_data,
            'current_data' => $current_data,
            'list_region' => $list_region,
            'notification' => $notification
        ]);
    }

    public function saveAdjustment(Request $request)
    {
        $notification = [];
        $type = $request->type;
        if ($type == 'Lapangan Usaha') $route = 'lapus.';
        else if ($type == 'Pengeluaran') $route = 'peng.';
        try {
            //code...
            DB::beginTransaction();
            foreach ($request->dataContents as $key => $value) {
                if ($value['adj_adhb'] || $value['adj_adhk']) {
                    Adjustment::updateOrCreate(
                        ['pdrb_id' => $value['id']], // Condition to check
                        [
                            'adhb' => $value['adj_adhb'] ?? 0,
                            'adhk' => $value['adj_adhk'] ?? 0
                        ]
                    );
                }
            }
            $message = [
                'type' => 'success',
                'message' => 'Data sudah disimpan'
            ];
            array_push($notification, $message);
            DB::commit();
            return redirect()->route($route . 'adjustment')->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Ada Kesalahan ketika simpan',
                'errors' => $th->getMessage()
            ];
            array_push($notification, $message);
            return redirect()->route($route . 'adjustment')->with('notification', $notification);
        }
    }

    public function monitoring()
    {
        $regions = Region::getMyRegion();
        return Inertia::render('Pdrb/Monitoring', [
            'regions' => $regions
        ]);
    }

    public function getMonitoring(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string'],
            'year' => ['required', 'integer'],
            'quarter' => ['required', 'integer'],
            'description' => ['required', 'integer'],
        ]);
        $period_id = $validated['description'];
        $regions = Region::getMyRegion();
        $datasetStatuses = Dataset::where('period_id', $period_id)
            ->whereIn('region_id', $regions->pluck('value'))
            ->pluck('status', 'region_id');

        $monitoring = $regions->mapWithKeys(function ($reg) use ($datasetStatuses) {
            return [$reg->value => ['status' => $datasetStatuses[$reg->value] ?? 'Belum']];
        });
        return response()->json(['monitoring' => $monitoring]);
    }

    public function hasil()
    {
        $prefix = request()->route()->getPrefix();
        if ($prefix == 'lapus') $type = 'Lapangan Usaha';
        else if ($prefix == 'peng') $type = 'Pengeluaran';
        $regions = Region::getMyRegion();
        $subsectors = Subsector::where('type', $type)
            ->with(['sector.category'])
            ->get();
        return Inertia::render('Pdrb/Hasil', [
            'type' => $type,
            'subsectors' => $subsectors,
            'regions' => $regions
        ]);
    }

    public function getHasil(Request $request)
    {
        $result = $this->fetchHasil($request);
        if ($result['status'] == 500) {
            return response()->json(['notification' => $result['notification']], 500);
        } else {
            return  response()->json([
                'current_data' => $result['current_data'],
                'previous_data' => $result['previous_data'],
                'current_summary_set' => $result['current_summary_set'],
                'previous_summary_set' => $result['previous_summary_set'],
                'notification' => $result['notification']
            ]);
        }
    }

    public function diskrepansi()
    {
        $prefix = request()->route()->getPrefix();
        if ($prefix == 'lapus') $type = 'Lapangan Usaha';
        else if ($prefix == 'peng') $type = 'Pengeluaran';
        $regions = Region::select('id as value', 'name as label')->get();
        $subsectors = Subsector::where('type', $type)
            ->with(['sector.category'])
            ->get();
        return Inertia::render('Pdrb/Diskrepansi', [
            'type' => $type,
            'subsectors' => $subsectors,
            'regions' => $regions
        ]);
    }

    public function getDiskrepansi(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string'],
            'year' => ['required', 'integer'],
            'quarter' => ['required', 'integer'],
            'description' => ['required', 'integer'],
            'dataBefore' => ['sometimes', 'integer', 'nullable'],
        ]);
        $notification = [];
        $type = $validated['type'];
        $previous_data = collect(); // Ensure $previous_data is always initialized
        $current_data = collect();
        try {
            //code...
            DB::beginTransaction();
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

            $regions = Region::select('id as value', 'name as label')->get();
            $current_dataset = Dataset::where('period_id', $period_id)
                ->where('type', $validated['type'])
                ->pluck('id');
            $previous_dataset = Dataset::where('period_id', $previous_period)
                ->where('type', $validated['type'])
                ->pluck('id');
            foreach ($regions as $key => $reg) {
                # code...
                $cek_previous = Dataset::where('period_id', $previous_period)
                    ->where('region_id', $reg->value)
                    ->where('type', $type)
                    ->first();
                if (!$cek_previous) {
                    $message = [
                        'type' => 'error',
                        'message' => 'Data ' . $reg->label . ' periode sebelumnya tidak ada',
                    ];
                    array_push($notification, $message);
                } else {
                    $message = [
                        'type' => 'success',
                        'message' => 'Data ' . $reg->label . ' periode sebelumnya berhasil diambil',
                    ];
                    array_push($notification, $message);
                }
                $cek_current = Dataset::where('period_id', $period_id)
                    ->where('region_id', $reg->value)
                    ->where('type', $type)
                    ->first();
                if (!$cek_current) {
                    $message = [
                        'type' => 'error',
                        'message' => 'Data ' . $reg->label . ' periode ini tidak ada',
                    ];
                    array_push($notification, $message);
                } else {
                    $message = [
                        'type' => 'success',
                        'message' => 'Data ' . $reg->label . ' periode ini berhasil diambil',
                    ];
                    array_push($notification, $message);
                }
            }
            if ($previous_dataset->isNotEmpty()) {
                $previous_data = Pdrb::join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                    ->whereIn('pdrbs.dataset_id', $previous_dataset)
                    ->orderBy('d.region_id', 'asc')
                    ->select('pdrbs.*', 'd.region_id as region_id')
                    ->get();
            }
            if ($current_dataset->isNotEmpty()) {
                $current_data = Pdrb::join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                    ->whereIn('pdrbs.dataset_id', $current_dataset)
                    ->orderBy('d.region_id', 'asc')
                    ->select('pdrbs.*', 'd.region_id as region_id')
                    ->get();
            }
            $message = [
                'type' => 'success',
                'message' => 'Data sudah berhasil diambil',
            ];
            array_push($notification, $message);
            return response()->json([
                'previous_data' => $previous_data,
                'current_data' => $current_data,
                'notification' => $notification
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            $message = [
                'type' => 'error',
                'message' => 'Ada Error',
                'error' => $th->getMessage()
            ];
            array_push($notification, $message);
            return response()->json([
                'previous_data' => $previous_data,
                'current_data' => $current_data,
                'notification' => $notification
            ]);
        }
    }

    private function fetchHasil(Request $request)
    {
        $subsectors = Subsector::where('type', $request->type)->pluck('id');
        $validated = $request->validate([
            'type' => ['required', 'string'],
            'year' => ['required', 'integer'],
            'quarter' => ['required', 'integer'],
            'description' => ['required', 'integer'],
            'dataBefore' => ['sometimes', 'integer', 'nullable'],
            'regions' => ['required', 'integer'],
        ]);
        $notification = [];
        $type = $validated['type'];
        try {
            //code...
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

            $periode_before_year = Period::find($previous_period);
            $current_dataset = Dataset::where('period_id', $period_id)
                ->where('region_id', $validated['regions'])
                ->where('type', $validated['type'])
                ->value('id');
            $previous_dataset = Dataset::where('period_id', $previous_period)
                ->where('region_id', $validated['regions'])
                ->where('type', $validated['type'])
                ->value('id');

            if ($previous_dataset) {
                $previous_data = Pdrb::where('dataset_id', $previous_dataset)
                    ->orderBy('subsector_id')
                    ->get();
                if ($type == 'Lapangan Usaha') {
                    $previous_summary_set = Pdrb::where('dataset_id', $previous_dataset)
                        ->groupBy('dataset_id', 'year', 'quarter', 'setdata')
                        ->selectRaw("
                            dataset_id,
                            year,
                            quarter,
                            SUM(pdrbs.adhb) as adhb,
                            SUM(pdrbs.adhk) as adhk,
                            CASE 
                                WHEN subsector_id BETWEEN 1 AND 30 THEN 'primer'
                                WHEN subsector_id BETWEEN 31 AND 34 THEN 'sekunder'
                                WHEN subsector_id BETWEEN 35 AND 55 THEN 'tersier'
                            END as setdata
                            ")
                        ->get();
                } else if ($type == 'Pengeluaran') {
                    $previous_summary_set = Pdrb::where('dataset_id', $previous_dataset)
                        ->groupBy('dataset_id', 'year', 'quarter', 'setdata')
                        ->selectRaw("
                            dataset_id,
                            year,
                            quarter,
                            SUM(pdrbs.adhb) as adhb,
                            SUM(pdrbs.adhk) as adhk,
                            CASE 
                                WHEN subsector_id BETWEEN 56 AND 63 THEN 'kanp'
                                WHEN subsector_id = 64 THEN 'kap'
                                WHEN subsector_id BETWEEN 65 AND 67 THEN 'pai'
                                WHEN subsector_id = 68 THEN 'export'
                                WHEN subsector_id = 69 THEN 'import'
                            END as setdata
                            ")
                        ->get();
                    $grouped = $previous_summary_set->groupBy(fn($item) => "{$item->dataset_id}_{$item->year}_{$item->quarter}");
                    $previous_summary_set = $grouped->flatMap(function ($group) {
                        $export = $group->where('setdata', 'export')->first();
                        $import = $group->where('setdata', 'import')->first();

                        $netExportImport = (object) [
                            'dataset_id' => $export->dataset_id ?? $import->dataset_id,
                            'year' => $export->year ?? $import->year,
                            'quarter' => $export->quarter ?? $import->quarter,
                            'adhb' => ($export->adhb ?? 0) - ($import->adhb ?? 0),
                            'adhk' => ($export->adhk ?? 0) - ($import->adhk ?? 0),
                            'setdata' => 'net_export_import',
                        ];
                        return $group->push($netExportImport);
                    });
                }
                $message = [
                    'type' => 'success',
                    'message' => 'Data periode sebelumnya berhasil diunduh, Tahun ' . $periode_before_year->year . ' ' . $periode_before_year->description
                ];
                array_push($notification, $message);
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

                $message = [
                    'type' => 'error',
                    'message' => 'Data periode sebelumnya tidak ada / belum final, summary tidak dapat ditampilkan'
                ];
                array_push($notification, $message);
            }

            if ($current_dataset) {
                $current_data = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                    ->where('dataset_id', $current_dataset)
                    ->orderBy('subsector_id')
                    ->select(['pdrbs.*', 'adj.adhb as adj_adhb', 'adj.adhk as adj_adhk'])
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'dataset_id' => $item->dataset_id,
                            'year' => $item->year,
                            'quarter' => $item->quarter,
                            'subsector_id' => $item->subsector_id,
                            'adhb' => ($item->adhb ?? 0) + ($item->adj_adhb ?? 0),
                            'adhk' => ($item->adhk ?? 0) + ($item->adj_adhk ?? 0),
                        ];
                    });
                if ($type == 'Lapangan Usaha') {
                    $current_summary_set = Pdrb::where('dataset_id', $current_dataset)
                        ->groupBy('dataset_id', 'year', 'quarter', 'setdata')
                        ->selectRaw("
                            dataset_id,
                            year,
                            quarter,
                            SUM(pdrbs.adhb) as adhb,
                            SUM(pdrbs.adhk) as adhk,
                            CASE 
                                WHEN subsector_id BETWEEN 1 AND 30 THEN 'primer'
                                WHEN subsector_id BETWEEN 31 AND 34 THEN 'sekunder'
                                WHEN subsector_id BETWEEN 35 AND 55 THEN 'tersier'
                            END as setdata
                                ")
                        ->get();
                } else if ($type == 'Pengeluaran') {
                    $current_summary_set = Pdrb::where('dataset_id', $current_dataset)
                        ->groupBy('dataset_id', 'year', 'quarter', 'setdata')
                        ->selectRaw("
                            dataset_id,
                            year,
                            quarter,
                            SUM(pdrbs.adhb) as adhb,
                            SUM(pdrbs.adhk) as adhk,
                            CASE 
                                WHEN subsector_id BETWEEN 56 AND 63 THEN 'kanp'
                                WHEN subsector_id = 64 THEN 'kap'
                                WHEN subsector_id BETWEEN 65 AND 67 THEN 'pai'
                                WHEN subsector_id = 68 THEN 'export'
                                WHEN subsector_id = 69 THEN 'import'
                            END as setdata
                                ")
                        ->get();
                    $grouped = $current_summary_set->groupBy(fn($item) => "{$item->dataset_id}_{$item->year}_{$item->quarter}");
                    $current_summary_set = $grouped->flatMap(function ($group) {
                        $export = $group->where('setdata', 'export')->first();
                        $import = $group->where('setdata', 'import')->first();

                        $netExportImport = (object) [
                            'dataset_id' => $export->dataset_id ?? $import->dataset_id,
                            'year' => $export->year ?? $import->year,
                            'quarter' => $export->quarter ?? $import->quarter,
                            'adhb' => ($export->adhb ?? 0) - ($import->adhb ?? 0),
                            'adhk' => ($export->adhk ?? 0) - ($import->adhk ?? 0),
                            'setdata' => 'net_export_import',
                        ];
                        return $group->push($netExportImport);
                    });
                }
                $message = [
                    'type' => 'success',
                    'message' => 'Mengambil Data PDRB Periode Ini'
                ];
                array_push($notification, $message);
            } else {
                $current_data = [];
                $message = [
                    'type' => 'error',
                    'message' => 'Data PDRB Periode ini tidak ada'
                ];
                array_push($notification, $message);
            }
            $result = [
                'current_data' => $current_data,
                'previous_data' => $previous_data,
                'current_summary_set' => $current_summary_set,
                'previous_summary_set' => $previous_summary_set,
                'notification' => $notification,
                'status' => 400
            ];
            return  $result;
        } catch (\Throwable $th) {
            //throw $th;
            $message = [
                'type' => 'error',
                'message' => 'Ada kesalahan ketika mengambil data dari database'
            ];
            array_push($notification, $message);
            $result = [
                'notification' => $notification,
                'status' => 500
            ];
            return $result;
        }
    }

    public function copyHasil(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string'],
            'description' => ['required', 'integer'],
            'regions' => ['required', 'integer'],
        ]);
        $notification = [];
        $period_id = $validated['description'];
        $current_dataset = Dataset::where('period_id', $period_id)
            ->where('region_id', $validated['regions'])
            ->where('type', $validated['type'])
            ->value('id');
        if ($current_dataset) {
            $result = $this->fetchHasil($request);
            if ($result['status'] == 500) {
                return response()->json(['notification' => $result['notification']], 500);
            } else {
                return response()->json([
                    'current_data' => $result['current_data'],
                    'notification' => $result['notification']
                ]);
            }
        } else {
            $message = [
                'type' => 'error',
                'message' => 'Data yang ingin di-salin tidak ada'
            ];
            array_push($notification, $message);
            return response()->json(['notification' => $notification], 500);
        }
    }
}
