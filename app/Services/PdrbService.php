<?php

namespace App\Services;

use App\Models\Adjustment;
use App\Models\Dataset;
use App\Models\Pdrb;
use App\Models\Period;
use App\Models\Region;
use App\Models\Sector;
use App\Models\Subsector;
use Illuminate\Support\Facades\DB;

class PdrbService
{
    public function getAdjustmentData(array $validated)
    {
        $regions = Region::select('id as value', 'name as label')->get();
        $type = $validated['type'];
        $period_id = $validated['description'];
        $period_before = isset($validated['dataBefore']) ? $validated['dataBefore'] : null;
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
        } else {
            $previous_period = $period_before;
        }

        $exploded_subsectors = explode('-', $validated['subsectors']);
        $typeOfSubsector = $exploded_subsectors[0];
        $categoryId = $exploded_subsectors[1];
        $sectorId = $exploded_subsectors[2];
        $subsectorId = $exploded_subsectors[3];

        $periode_before_year = Period::find($previous_period);
        $current_dataset = Dataset::where('period_id', $period_id)
            ->pluck('id');
        $previous_dataset = Dataset::where('period_id', $previous_period)
            ->pluck('id');

        // Optimasi N+1 Regions
        $previous_datasets_db = Dataset::where('period_id', $previous_period)->get()->keyBy('region_id');
        $current_datasets_db = Dataset::where('period_id', $period_id)->get()->keyBy('region_id');

        $list_region = [];
        foreach ($regions as $key => $reg) {
            $cek_previous = $previous_datasets_db->get($reg->value);
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
            $cek_current = $current_datasets_db->get($reg->value);
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

        $previous_data = collect();
        $current_data = collect();

        if ($typeOfSubsector == 'subsector') {
            if ($previous_dataset->isNotEmpty()) {
                $previous_data = Pdrb::withAdjustmentsAndDatasets()
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
                $current_data = Pdrb::withAdjustmentsAndDatasets()
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
            if ($sectorId == Sector::EKSPOR_IMPOR_ID) {
                $importId = [Subsector::IMPOR_ID];
                if ($previous_dataset->isNotEmpty()) {
                    $subsectorForSearch = Subsector::where('sector_id', $sectorId)->pluck('id');
                    $total = Pdrb::withAdjustmentsAndDatasets()
                        ->whereIn('pdrbs.dataset_id', $previous_dataset)
                        ->whereIn('pdrbs.subsector_id', $subsectorForSearch)
                        ->whereNotIn('pdrbs.subsector_id', $importId)
                        ->orderBy('d.region_id', 'asc')
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectTotalPdrb()
                        ->get()
                        ->keyBy(fn($item) => "{$item->region_id}_{$item->year}_{$item->quarter}");

                    $import = Pdrb::withAdjustmentsAndDatasets()
                        ->whereIn('pdrbs.dataset_id', $previous_dataset)
                        ->whereIn('subsector_id', $importId)
                        ->orderBy('d.region_id', 'asc')
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectTotalPdrb()
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
                    $total = Pdrb::withAdjustmentsAndDatasets()
                        ->whereIn('pdrbs.dataset_id', $current_dataset)
                        ->whereIn('pdrbs.subsector_id', $subsectorForSearch)
                        ->whereNotIn('pdrbs.subsector_id', $importId)
                        ->orderBy('d.region_id', 'asc')
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectTotalPdrb()
                        ->get()
                        ->keyBy(fn($item) => "{$item->region_id}_{$item->year}_{$item->quarter}");

                    $import = Pdrb::withAdjustmentsAndDatasets()
                        ->whereIn('pdrbs.dataset_id', $current_dataset)
                        ->whereIn('subsector_id', $importId)
                        ->orderBy('d.region_id', 'asc')
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectTotalPdrb()
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
                    $previous_data = Pdrb::withAdjustmentsAndDatasets()
                        ->whereIn('pdrbs.dataset_id', $previous_dataset)
                        ->whereIn('pdrbs.subsector_id', $subsectorForSearch)
                        ->orderBy('d.region_id', 'asc')
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectTotalPdrb()
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
                    $current_data = Pdrb::withAdjustmentsAndDatasets()
                        ->whereIn('pdrbs.dataset_id', $current_dataset)
                        ->whereIn('pdrbs.subsector_id', $subsectorForSearch)
                        ->orderBy('d.region_id', 'asc')
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectTotalPdrb()
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
                $previous_data = Pdrb::withAdjustmentsAndDatasets()
                    ->whereIn('pdrbs.dataset_id', $previous_dataset)
                    ->whereIn('pdrbs.subsector_id', $subsectorForSearch)
                    ->orderBy('d.region_id', 'asc')
                    ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                    ->selectTotalPdrb()
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
                $current_data = Pdrb::withAdjustmentsAndDatasets()
                    ->whereIn('pdrbs.dataset_id', $current_dataset)
                    ->whereIn('pdrbs.subsector_id', $subsectorForSearch)
                    ->orderBy('d.region_id', 'asc')
                    ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                    ->selectTotalPdrb()
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
                    $previous_data = Pdrb::withAdjustmentsAndDatasets()
                        ->whereIn('pdrbs.dataset_id', $previous_dataset)
                        ->orderBy('d.region_id', 'asc')
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectTotalPdrb()
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
                    $current_data = Pdrb::withAdjustmentsAndDatasets()
                        ->whereIn('pdrbs.dataset_id', $current_dataset)
                        ->orderBy('d.region_id', 'asc')
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectTotalPdrb()
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
                $importId = [Subsector::IMPOR_ID];
                if ($previous_dataset->isNotEmpty()) {
                    $total = Pdrb::withAdjustmentsAndDatasets()
                        ->whereIn('pdrbs.dataset_id', $previous_dataset)
                        ->whereNotIn('subsector_id', $importId)
                        ->orderBy('d.region_id', 'asc')
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectTotalPdrb()
                        ->get()
                        ->keyBy(fn($item) => "{$item->region_id}_{$item->year}_{$item->quarter}");

                    $import = Pdrb::withAdjustmentsAndDatasets()
                        ->whereIn('pdrbs.dataset_id', $previous_dataset)
                        ->whereIn('subsector_id', $importId)
                        ->orderBy('d.region_id', 'asc')
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectTotalPdrb()
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
                    $total = Pdrb::withAdjustmentsAndDatasets()
                        ->whereIn('pdrbs.dataset_id', $current_dataset)
                        ->whereNotIn('subsector_id', $importId)
                        ->orderBy('d.region_id', 'asc')
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectTotalPdrb()
                        ->get()
                        ->keyBy(fn($item) => "{$item->region_id}_{$item->year}_{$item->quarter}");

                    $import = Pdrb::withAdjustmentsAndDatasets()
                        ->whereIn('pdrbs.dataset_id', $current_dataset)
                        ->whereIn('subsector_id', $importId)
                        ->orderBy('d.region_id', 'asc')
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectTotalPdrb()
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

        return [
            'previous_data' => $previous_data,
            'current_data' => $current_data,
            'list_region' => $list_region,
            'notification' => $notification
        ];
    }
}