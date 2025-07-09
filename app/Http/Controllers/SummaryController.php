<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Dataset;
use App\Models\Pdrb;
use App\Models\Period;
use App\Models\Region;
use App\Models\Sector;
use App\Models\Subsector;
use App\Models\SummaryPdrb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SummaryController extends Controller
{
    //
    public function index(Request $request)
    {
        set_time_limit(0);
        $region = Region::pluck('id');

        $lapus_PERIOD = Period::where('type', 'Lapangan Usaha')
            ->latest()
            ->first();
        $lapus_period = $lapus_PERIOD->id;
        $lapus_period_before = Period::where('type', 'Lapangan Usaha')
            ->where('year', $lapus_PERIOD->year - 1)
            ->where('quarter', 4)
            ->latest()
            ->value('id');
        $peng_PERIOD = Period::where('type', 'Pengeluaran')
            ->latest()
            ->first();
        $peng_period = $peng_PERIOD->id;
        $peng_period_before = Period::where('type', 'Pengeluaran')
            ->where('year', $peng_PERIOD->year - 1)
            ->where('quarter', 4)
            ->latest()
            ->value('id');
        $dataset = Dataset::where('period_id', $lapus_period)
            ->orWhere('period_id', $peng_period)
            ->pluck('id');
        $dataset_before =  Dataset::where('period_id', $lapus_period_before)
            ->orWhere('period_id', $peng_period_before)
            ->pluck('id');

        $setup = $request->setup;
        if ($setup == 'category') {
            $result_cat =  $this->setupCategory($dataset_before, $dataset, $request->region_id);
        } else if ($setup == 'sector') {
            $result_set =  $this->setupSector($dataset_before, $dataset, $request->region_id);
        } else if ($setup == 'subsector') {
            $result_sub = $this->setupSubsector($dataset_before, $dataset, $request->region_id);
        } else {
            $dataset_lapus = Dataset::where('period_id', $lapus_period)
                ->pluck('id');
            $dataset_peng = Dataset::where('period_id', $peng_period)
                ->pluck('id');
            $dataset_lapus_before = Dataset::where('period_id', $lapus_period_before)
                ->pluck('id');
            $dataset_peng_before = Dataset::where('period_id', $peng_period_before)
                ->pluck('id');
            $result_tot = $this->setupTotal($dataset_lapus_before, $dataset_lapus, $dataset_peng_before, $dataset_peng,  $request->region_id);
        }


        $data = SummaryPdrb::where('region_id', $request->region_id)
            ->where('quarter', $request->quarter)
            ->where(function ($query) {
                $query->whereNotIn('subsector_id', [98, 99])
                    ->orWhereNull('subsector_id'); // 👈 include NULL values
            })
            ->get();

        $total = SummaryPdrb::where('region_id', $request->region_id)
            ->where('quarter', $request->quarter)
            ->where(function ($query) {
                $query->whereIn('subsector_id', [98, 99])
                    ->orWhereNull('subsector_id'); // 👈 include NULL values
            })
            ->get();

        $lapus_total = $total->where('subsector_id', 98)->value('adhb');
        $peng_total = $total->where('subsector_id', 99)->value('adhb');

        foreach ($data as $key => $value) {
            # code...
            if ($value->category_id < 18) {
                $dist =  ($value->adhb != 0 && $lapus_total != 0) ? ($value->adhb / $lapus_total) * 100 : 0;
            } else if ($value->category_id > 17) {
                $dist =  ($value->adhb != 0 && $peng_total != 0) ? ($value->adhb / $peng_total) * 100 : 0;
            }
            SummaryPdrb::where('id', $value->id)->update(['dist' => $dist]);
        }
        SummaryPdrb::whereIn('subsector_id', [98, 99])
            ->update(['dist' => 100]);

        return response()->json([
            'message' => $setup . ' done',
            'lapus_period' => $lapus_period,
            'peng_period' => $peng_period
        ]);
    }

    private function setupCategory($dataset_before, $dataset, $region)
    {
        $category = Category::pluck('id');
        $super_result = [];
        foreach ($category as $keyCat => $cat) {
            # code...
            $sectorForSearch = Sector::where('category_id', $cat)->pluck('id');
            $subsectorForSearch = Subsector::whereIn('sector_id', $sectorForSearch)->pluck('id');
            $query_before = Pdrb::query();
            $query_before->leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                ->whereIn('pdrbs.dataset_id', $dataset_before)
                ->whereIn('pdrbs.subsector_id', $subsectorForSearch);
            if ($region != 17) {
                $data_before = $query_before
                    ->where('d.region_id', $region)
                    ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                    ->selectRaw(
                        'pdrbs.year,
                        pdrbs.quarter,
                        SUM(pdrbs.adhb) as adhb,
                        SUM(pdrbs.adhk) as adhk,
                        SUM(adj.adhb) as adj_adhb,
                        SUM(adj.adhk) as adj_adhk,
                        d.region_id as region_id'
                    )->get();
            } else {
                $data_before = $query_before
                    ->whereNot('d.region_id', 1)
                    ->groupBy('pdrbs.year', 'pdrbs.quarter')
                    ->selectRaw(
                        'pdrbs.year,
                        pdrbs.quarter,
                        SUM(pdrbs.adhb) as adhb,
                        SUM(pdrbs.adhk) as adhk,
                        SUM(adj.adhb) as adj_adhb,
                        SUM(adj.adhk) as adj_adhk'
                    )->get();
            }
            $data_before = $data_before
                ->map(function ($item) use ($cat) {
                    return [
                        'year' => $item->year,
                        'quarter' => $item->quarter,
                        'category_id' => $cat,
                        'adhb' => $item->adhb + ($item->adj_adhb ?? 0),
                        'adhk' => $item->adhk + ($item->adj_adhk ?? 0),
                        'region_id' => $item->region_id ?? 17
                    ];
                });
            $query = Pdrb::query();
            $query->leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                ->whereIn('pdrbs.dataset_id', $dataset)
                ->whereIn('pdrbs.subsector_id', $subsectorForSearch);
            if ($region != 17) {
                $data = $query
                    ->where('d.region_id', $region)
                    ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                    ->selectRaw(
                        'pdrbs.year,
                        pdrbs.quarter,
                        SUM(pdrbs.adhb) as adhb,
                        SUM(pdrbs.adhk) as adhk,
                        SUM(adj.adhb) as adj_adhb,
                        SUM(adj.adhk) as adj_adhk,
                        d.region_id as region_id'
                    )->get();
            } else {
                $data = $query
                    ->whereNot('d.region_id', 1)
                    ->groupBy('pdrbs.year', 'pdrbs.quarter')
                    ->selectRaw(
                        'pdrbs.year,
                        pdrbs.quarter,
                        SUM(pdrbs.adhb) as adhb,
                        SUM(pdrbs.adhk) as adhk,
                        SUM(adj.adhb) as adj_adhb,
                        SUM(adj.adhk) as adj_adhk'
                    )->get();
            }
            $data = $data
                ->map(function ($item) use ($cat) {
                    return [
                        'year' => $item->year,
                        'quarter' => $item->quarter,
                        'category_id' => $cat,
                        'adhb' => $item->adhb + ($item->adj_adhb ?? 0),
                        'adhk' => $item->adhk + ($item->adj_adhk ?? 0),
                        'region_id' => $item->region_id ?? 17
                    ];
                });
            if (sizeof($data) > 0) {
                $result = $this->buildValue($data_before, $data[0], $data, 'category_id', 'category');
                array_push($super_result, $data[0]);
                $updating_summary = SummaryPdrb::where('region_id', $data[0]['region_id'])
                    ->where('quarter', $data[0]['quarter'])
                    ->where('category_id', $data[0]['category_id'])
                    ->where('sector_id', null)
                    ->where('subsector_id', null);
                $updating_summary->update(
                    [
                        'adhb' => $data[0]['adhb'],
                        'adhk' => $data[0]['adhk'],
                        'qtoq' => $result['qtoq'],
                        'yony' => $result['yony'],
                        'ctoc' => $result['ctoc'],
                        'idx' => $result['idx'],
                        'iqtoq' => $result['iqtoq'],
                        'iyony' => $result['iyony']
                    ]
                );
            }
        }
        return $super_result;
    }

    private function setupSector($dataset_before, $dataset, $region)
    {
        $sector = Sector::pluck('id');
        $super_result = [];
        foreach ($sector as $keySet => $set) {
            # code...
            $subsectorForSearch = Subsector::where('sector_id', $set)->pluck('id');
            if ($set == '54') {
                $importId = ['69'];
                $query_total_before = Pdrb::query();
                $query_total_before->leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                    ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                    ->whereIn('pdrbs.dataset_id', $dataset_before)
                    ->whereIn('pdrbs.subsector_id', $subsectorForSearch)
                    ->whereNotIn('pdrbs.subsector_id', $importId);
                if ($region != 17) {
                    $total_before = $query_total_before
                        ->where('d.region_id', $region)
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
                        ->get();
                } else {
                    $total_before = $query_total_before
                        ->whereNot('d.region_id', 1)
                        ->groupBy('pdrbs.year', 'pdrbs.quarter')
                        ->selectRaw(
                            'pdrbs.year,
                            pdrbs.quarter,
                            SUM(pdrbs.adhb) as adhb,
                            SUM(pdrbs.adhk) as adhk,
                            SUM(adj.adhb) as adj_adhb,
                            SUM(adj.adhk) as adj_adhk,
                            17 as region_id'
                        )
                        ->get();
                }
                $total_before = $total_before
                    ->keyBy(fn($item) => "{$item->region_id}_{$item->year}_{$item->quarter}");

                $query_total = Pdrb::query();
                $query_total->leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                    ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                    ->whereIn('pdrbs.dataset_id', $dataset)
                    ->whereIn('pdrbs.subsector_id', $subsectorForSearch)
                    ->whereNotIn('pdrbs.subsector_id', $importId);
                if ($region != 17) {
                    $total = $query_total
                        ->where('d.region_id', $region)
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
                        ->get();
                } else {
                    $total = $query_total
                        ->whereNot('d.region_id', 1)
                        ->groupBy('pdrbs.year', 'pdrbs.quarter')
                        ->selectRaw(
                            'pdrbs.year,
                            pdrbs.quarter,
                            SUM(pdrbs.adhb) as adhb,
                            SUM(pdrbs.adhk) as adhk,
                            SUM(adj.adhb) as adj_adhb,
                            SUM(adj.adhk) as adj_adhk,
                            17 as region_id'
                        )
                        ->get();
                }
                $total = $total
                    ->keyBy(fn($item) => "{$item->region_id}_{$item->year}_{$item->quarter}");

                $query_import_before = Pdrb::query();
                $query_import_before->leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                    ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                    ->whereIn('pdrbs.dataset_id', $dataset_before)
                    ->whereIn('subsector_id', $importId);
                if ($region != 17) {
                    $import_before = $query_import_before
                        ->where('d.region_id', $region)
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
                        ->get();
                } else {
                    $import_before = $query_import_before
                        ->whereNot('d.region_id', 1)
                        ->groupBy('pdrbs.year', 'pdrbs.quarter')
                        ->selectRaw(
                            'pdrbs.year,
                            pdrbs.quarter,
                            SUM(pdrbs.adhb) as adhb,
                            SUM(pdrbs.adhk) as adhk,
                            SUM(adj.adhb) as adj_adhb,
                            SUM(adj.adhk) as adj_adhk,
                            17 as region_id'
                        )
                        ->get();
                }
                $import_before = $import_before
                    ->keyBy(fn($item) => "{$item->region_id}_{$item->year}_{$item->quarter}");

                $query_import = Pdrb::query();
                $query_import->leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                    ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                    ->whereIn('pdrbs.dataset_id', $dataset)
                    ->whereIn('subsector_id', $importId);
                if ($region != 17) {
                    $import = $query_import
                        ->where('d.region_id', $region)
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
                        ->get();
                } else {
                    $import = $query_import
                        ->whereNot('d.region_id', 1)
                        ->groupBy('pdrbs.year', 'pdrbs.quarter')
                        ->selectRaw(
                            'pdrbs.year,
                            pdrbs.quarter,
                            SUM(pdrbs.adhb) as adhb,
                            SUM(pdrbs.adhk) as adhk,
                            SUM(adj.adhb) as adj_adhb,
                            SUM(adj.adhk) as adj_adhk,
                            17 as region_id'
                        )
                        ->get();
                }
                $import = $import
                    ->keyBy(fn($item) => "{$item->region_id}_{$item->year}_{$item->quarter}");

                $data_before = collect($total_before)->map(function ($prev) use ($import_before, $set) {
                    $key = "{$prev->region_id}_{$prev->year}_{$prev->quarter}";
                    $imp = $import_before[$key] ?? (object) ['adhb' => 0, 'adhk' => 0, 'adj_adhb' => 0, 'adj_adhk' => 0];

                    return [
                        'year' => $prev->year,
                        'quarter' => $prev->quarter,
                        'sector_id' => $set,
                        'adhb' => $prev->adhb - $imp->adhb + (($prev->adj_adhb ?? 0) - ($imp->adj_adhb ?? 0)),
                        'adhk' => $prev->adhk - $imp->adhk + (($prev->adj_adhk ?? 0) - ($imp->adj_adhk ?? 0)),
                        'region_id' => $prev->region_id
                    ];
                })->values();

                $data = collect($total)->map(function ($prev) use ($import, $set) {
                    $key = "{$prev->region_id}_{$prev->year}_{$prev->quarter}";
                    $imp = $import[$key] ?? (object) ['adhb' => 0, 'adhk' => 0, 'adj_adhb' => 0, 'adj_adhk' => 0];

                    return [
                        'year' => $prev->year,
                        'quarter' => $prev->quarter,
                        'sector_id' => $set,
                        'adhb' => $prev->adhb - $imp->adhb + (($prev->adj_adhb ?? 0) - ($imp->adj_adhb ?? 0)),
                        'adhk' => $prev->adhk - $imp->adhk + (($prev->adj_adhk ?? 0) - ($imp->adj_adhk ?? 0)),
                        'region_id' => $prev->region_id
                    ];
                })->values();
            } else {
                $query_before = Pdrb::query();
                $query_before->leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                    ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                    ->whereIn('pdrbs.dataset_id', $dataset_before)
                    ->whereIn('pdrbs.subsector_id', $subsectorForSearch);
                if ($region != 17) {
                    $data_before = $query_before
                        ->where('d.region_id', $region)
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectRaw(
                            'pdrbs.year,
                        pdrbs.quarter,
                        SUM(pdrbs.adhb) as adhb,
                        SUM(pdrbs.adhk) as adhk,
                        SUM(adj.adhb) as adj_adhb,
                        SUM(adj.adhk) as adj_adhk,
                        d.region_id as region_id'
                        )->get();
                } else {
                    $data_before = $query_before
                        ->whereNot('d.region_id', 1)
                        ->groupBy('pdrbs.year', 'pdrbs.quarter')
                        ->selectRaw(
                            'pdrbs.year,
                        pdrbs.quarter,
                        SUM(pdrbs.adhb) as adhb,
                        SUM(pdrbs.adhk) as adhk,
                        SUM(adj.adhb) as adj_adhb,
                        SUM(adj.adhk) as adj_adhk'
                        )->get();
                }
                $data_before = $data_before
                    ->map(function ($item) use ($set) {
                        return [
                            'year' => $item->year,
                            'quarter' => $item->quarter,
                            'sector_id' => $set,
                            'adhb' => $item->adhb + ($item->adj_adhb ?? 0),
                            'adhk' => $item->adhk + ($item->adj_adhk ?? 0),
                            'region_id' => $item->region_id ?? 17
                        ];
                    });

                $query = Pdrb::query();
                $query->leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                    ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                    ->whereIn('pdrbs.dataset_id', $dataset)
                    ->whereIn('pdrbs.subsector_id', $subsectorForSearch);
                if ($region != 17) {
                    $data = $query
                        ->where('d.region_id', $region)
                        ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                        ->selectRaw(
                            'pdrbs.year,
                        pdrbs.quarter,
                        SUM(pdrbs.adhb) as adhb,
                        SUM(pdrbs.adhk) as adhk,
                        SUM(adj.adhb) as adj_adhb,
                        SUM(adj.adhk) as adj_adhk,
                        d.region_id as region_id'
                        )->get();
                } else {
                    $data = $query
                        ->whereNot('d.region_id', 1)
                        ->groupBy('pdrbs.year', 'pdrbs.quarter')
                        ->selectRaw(
                            'pdrbs.year,
                        pdrbs.quarter,
                        SUM(pdrbs.adhb) as adhb,
                        SUM(pdrbs.adhk) as adhk,
                        SUM(adj.adhb) as adj_adhb,
                        SUM(adj.adhk) as adj_adhk'
                        )->get();
                }
                $data = $data
                    ->map(function ($item) use ($set) {
                        return [
                            'year' => $item->year,
                            'quarter' => $item->quarter,
                            'sector_id' => $set,
                            'adhb' => $item->adhb + ($item->adj_adhb ?? 0),
                            'adhk' => $item->adhk + ($item->adj_adhk ?? 0),
                            'region_id' => $item->region_id ?? 17
                        ];
                    });
            }
            if (sizeof($data) > 0) {
                $result = $this->buildValue($data_before, $data[0], $data, 'sector_id', 'sector');
                array_push($super_result, $result);
                $updating_summary = SummaryPdrb::where('region_id', $data[0]['region_id'])
                    ->where('quarter', $data[0]['quarter'])
                    ->where('sector_id', $data[0]['sector_id'])
                    ->where('subsector_id', null)
                    ->update(
                        [
                            'adhb' => $data[0]['adhb'],
                            'adhk' => $data[0]['adhk'],
                            'qtoq' => $result['qtoq'],
                            'yony' => $result['yony'],
                            'ctoc' => $result['ctoc'],
                            'idx' => $result['idx'],
                            'iqtoq' => $result['iqtoq'],
                            'iyony' => $result['iyony']
                        ]
                    );
            }
        }
        return $super_result;
    }

    private function setupSubsector($dataset_before, $dataset, $region)
    {
        $subsector = Subsector::pluck('id');
        $super_result = [];
        foreach ($subsector as $keySub => $sub) {
            # code...
            $query_before = Pdrb::query();
            $query_before->leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                ->whereIn('pdrbs.dataset_id', $dataset_before)
                ->where('pdrbs.subsector_id', $sub);
            if ($region != 17) {
                $data_before = $query_before
                    ->where('d.region_id', $region)
                    ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                    ->selectRaw(
                        'pdrbs.year,
                        pdrbs.quarter,
                        SUM(pdrbs.adhb) as adhb,
                        SUM(pdrbs.adhk) as adhk,
                        SUM(adj.adhb) as adj_adhb,
                        SUM(adj.adhk) as adj_adhk,
                        d.region_id as region_id'
                    )->get();
            } else {
                $data_before = $query_before
                    ->whereNot('d.region_id', 1)
                    ->groupBy('pdrbs.year', 'pdrbs.quarter')
                    ->selectRaw(
                        'pdrbs.year,
                        pdrbs.quarter,
                        SUM(pdrbs.adhb) as adhb,
                        SUM(pdrbs.adhk) as adhk,
                        SUM(adj.adhb) as adj_adhb,
                        SUM(adj.adhk) as adj_adhk'
                    )->get();
            }
            $data_before = $data_before
                ->map(function ($item) use ($sub) {
                    return [
                        'year' => $item->year,
                        'quarter' => $item->quarter,
                        'subsector_id' => $sub,
                        'adhb' => $item->adhb + ($item->adj_adhb ?? 0),
                        'adhk' => $item->adhk + ($item->adj_adhk ?? 0),
                        'region_id' => $item->region_id ?? 17
                    ];
                });

            $query = Pdrb::query();
            $query->leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                ->whereIn('pdrbs.dataset_id', $dataset)
                ->where('pdrbs.subsector_id', $sub);

            if ($region != 17) {
                $data = $query
                    ->where('d.region_id', $region)
                    ->groupBy('d.region_id', 'pdrbs.year', 'pdrbs.quarter')
                    ->selectRaw(
                        'pdrbs.year,
                        pdrbs.quarter,
                        SUM(pdrbs.adhb) as adhb,
                        SUM(pdrbs.adhk) as adhk,
                        SUM(adj.adhb) as adj_adhb,
                        SUM(adj.adhk) as adj_adhk,
                        d.region_id as region_id'
                    )->get();
            } else {
                $data = $query
                    ->whereNot('d.region_id', 1)
                    ->groupBy('pdrbs.year', 'pdrbs.quarter')
                    ->selectRaw(
                        'pdrbs.year,
                        pdrbs.quarter,
                        SUM(pdrbs.adhb) as adhb,
                        SUM(pdrbs.adhk) as adhk,
                        SUM(adj.adhb) as adj_adhb,
                        SUM(adj.adhk) as adj_adhk'
                    )->get();
            }
            $data = $data
                ->map(function ($item) use ($sub) {
                    return [
                        'year' => $item->year,
                        'quarter' => $item->quarter,
                        'subsector_id' => $sub,
                        'adhb' => $item->adhb + ($item->adj_adhb ?? 0),
                        'adhk' => $item->adhk + ($item->adj_adhk ?? 0),
                        'region_id' => $item->region_id ?? 17
                    ];
                });
            if (sizeof($data) > 0) {
                # code...
                $result = $this->buildValue($data_before, $data[0], $data, 'subsector_id', 'subsector');
                array_push($super_result, $result);
                $updating_summary = SummaryPdrb::where('region_id', $data[0]['region_id'])
                    ->where('quarter', $data[0]['quarter'])
                    ->where('subsector_id', $data[0]['subsector_id'])
                    ->update(
                        [
                            'adhb' => $data[0]['adhb'],
                            'adhk' => $data[0]['adhk'],
                            'qtoq' => $result['qtoq'],
                            'yony' => $result['yony'],
                            'ctoc' => $result['ctoc'],
                            'idx' => $result['idx'],
                            'iqtoq' => $result['iqtoq'],
                            'iyony' => $result['iyony']
                        ]
                    );
            }
        }
        return $super_result;
    }

    private function setupTotal($lapus_before, $lapus, $peng_before, $peng,  $region)
    {
        // lapus
        $query_lapus_before = Pdrb::query();
        $query_lapus_before->leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
            ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
            ->whereIn('pdrbs.dataset_id', $lapus_before);
        if ($region != 17) {
            $data_lapus_before = $query_lapus_before->where('d.region_id', $region)
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
                ->get();
            $data_lapus_before = $data_lapus_before
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'year' => $item->year,
                        'quarter' => $item->quarter,
                        'subsector_id' => $item->subsector_id,
                        'adhb' => $item->adhb + ($item->adj_adhb ?? 0),
                        'adhk' => $item->adhk + ($item->adj_adhk ?? 0),
                        'region_id' => $item->region_id
                    ];
                });
        } else {
            $data_lapus_before = $query_lapus_before->whereNot('d.region_id', 1)
                ->orderBy('d.region_id', 'asc')
                ->select(
                    'pdrbs.year',
                    'pdrbs.quarter',
                    'pdrbs.subsector_id',
                    DB::raw('SUM(pdrbs.adhb + IFNULL(adj.adhb, 0)) as adhb'),
                    DB::raw('SUM(pdrbs.adhk + IFNULL(adj.adhk, 0)) as adhk')
                )
                ->groupBy('pdrbs.year', 'pdrbs.quarter', 'pdrbs.subsector_id')
                ->get();
            $data_lapus_before = $data_lapus_before
                ->map(function ($item) {
                    return [
                        'year' => $item->year,
                        'quarter' => $item->quarter,
                        'subsector_id' => $item->subsector_id,
                        'adhb' => (float) $item->adhb,
                        'adhk' => (float) $item->adhk,
                        'region_id' => 17
                    ];
                });
        }


        $group_lapus_before = $data_lapus_before->groupBy(function ($item) {
            return $item['region_id'] . '-' . $item['year'] . '-' .  $item['quarter'];
        })->map(function ($group, $key) {
            [$region_id, $year, $quarter] = explode('-', $key);
            return [
                'region_id' => (int) $region_id,
                'year' => (int) $year,
                'quarter' => (int) $quarter,
                'adhb' => $group->sum('adhb'),
                'adhk' => $group->sum('adhk')
            ];
        });

        $query_lapus = Pdrb::query();
        $query_lapus->leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
            ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
            ->whereIn('pdrbs.dataset_id', $lapus);
        if ($region != 17) {
            $data_lapus = $query_lapus->where('d.region_id', $region)
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
                ->get();
            $data_lapus = $data_lapus
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'year' => $item->year,
                        'quarter' => $item->quarter,
                        'subsector_id' => $item->subsector_id,
                        'adhb' => $item->adhb + ($item->adj_adhb ?? 0),
                        'adhk' => $item->adhk + ($item->adj_adhk ?? 0),
                        'region_id' => $item->region_id
                    ];
                });
        } else {
            $data_lapus = $query_lapus->whereNot('d.region_id', 1)
                ->orderBy('d.region_id', 'asc')
                ->select(
                    'pdrbs.year',
                    'pdrbs.quarter',
                    'pdrbs.subsector_id',
                    DB::raw('SUM(pdrbs.adhb + IFNULL(adj.adhb, 0)) as adhb'),
                    DB::raw('SUM(pdrbs.adhk + IFNULL(adj.adhk, 0)) as adhk')
                )
                ->groupBy('pdrbs.year', 'pdrbs.quarter', 'pdrbs.subsector_id')
                ->get();
            $data_lapus = $data_lapus
                ->map(function ($item) {
                    return [
                        'year' => $item->year,
                        'quarter' => $item->quarter,
                        'subsector_id' => $item->subsector_id,
                        'adhb' => (float) $item->adhb,
                        'adhk' => (float) $item->adhk,
                        'region_id' => 17
                    ];
                });
        }
        $group_lapus_now = $data_lapus->groupBy(function ($item) {
            return $item['region_id'] . '-' . $item['year'] . '-' . $item['quarter'];
        })->map(function ($group, $key) {
            [$region_id, $year, $quarter] = explode('-', $key);
            return [
                'region_id' => (int) $region_id,
                'year' => (int) $year,
                'quarter' => (int) $quarter,
                'adhb' => $group->sum('adhb'),
                'adhk' => $group->sum('adhk')
            ];
        });

        //peng
        $importId = ['69'];
        $query_peng_before_total = Pdrb::query();
        $query_peng_before_total->leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
            ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
            ->whereIn('pdrbs.dataset_id', $peng_before)
            ->whereNotIn('subsector_id', $importId);
        if ($region != 17) {
            $data_peng_before_total = $query_peng_before_total
                ->where('d.region_id', $region)
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
                ->get();
            $data_peng_before_total = $data_peng_before_total
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'year' => $item->year,
                        'quarter' => $item->quarter,
                        'subsector_id' => $item->subsector_id,
                        'adhb' => $item->adhb + ($item->adj_adhb ?? 0),
                        'adhk' => $item->adhk + ($item->adj_adhk ?? 0),
                        'region_id' => $item->region_id
                    ];
                });
        } else {
            $data_peng_before_total = $query_peng_before_total->whereNot('d.region_id', 1)
                ->orderBy('d.region_id', 'asc')
                ->select(
                    'pdrbs.year',
                    'pdrbs.quarter',
                    'pdrbs.subsector_id',
                    DB::raw('SUM(pdrbs.adhb + IFNULL(adj.adhb, 0)) as adhb'),
                    DB::raw('SUM(pdrbs.adhk + IFNULL(adj.adhk, 0)) as adhk')
                )
                ->groupBy('pdrbs.year', 'pdrbs.quarter', 'pdrbs.subsector_id')
                ->get();
            $data_peng_before_total = $data_peng_before_total
                ->map(function ($item) {
                    return [
                        'year' => $item->year,
                        'quarter' => $item->quarter,
                        'subsector_id' => $item->subsector_id,
                        'adhb' => (float) $item->adhb,
                        'adhk' => (float) $item->adhk,
                        'region_id' => 17
                    ];
                });
        }

        $query_peng_before_import = Pdrb::query();
        $query_peng_before_import->leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
            ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
            ->whereIn('pdrbs.dataset_id', $peng_before)
            ->whereNotIn('subsector_id', $importId);
        if ($region != 17) {
            $data_peng_before_import = $query_peng_before_import
                ->where('d.region_id', $region)
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
                ->get();
            $data_peng_before_import = $data_peng_before_import
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'year' => $item->year,
                        'quarter' => $item->quarter,
                        'subsector_id' => $item->subsector_id,
                        'adhb' => $item->adhb + ($item->adj_adhb ?? 0),
                        'adhk' => $item->adhk + ($item->adj_adhk ?? 0),
                        'region_id' => $item->region_id
                    ];
                });
        } else {
            $data_peng_before_import = $query_peng_before_import->whereNot('d.region_id', 1)
                ->orderBy('d.region_id', 'asc')
                ->select(
                    'pdrbs.year',
                    'pdrbs.quarter',
                    'pdrbs.subsector_id',
                    DB::raw('SUM(pdrbs.adhb + IFNULL(adj.adhb, 0)) as adhb'),
                    DB::raw('SUM(pdrbs.adhk + IFNULL(adj.adhk, 0)) as adhk')
                )
                ->groupBy('pdrbs.year', 'pdrbs.quarter', 'pdrbs.subsector_id')
                ->get();
            $data_peng_before_import = $data_peng_before_import
                ->map(function ($item) {
                    return [
                        'year' => $item->year,
                        'quarter' => $item->quarter,
                        'subsector_id' => $item->subsector_id,
                        'adhb' => (float) $item->adhb,
                        'adhk' => (float) $item->adhk,
                        'region_id' => 17
                    ];
                });
        }

        $group_peng_before_total = $data_peng_before_total->groupBy(function ($item) {
            return $item['region_id'] . '-' . $item['year'] . '-' .  $item['quarter'];
        })->map(function ($group, $key) {
            [$region_id, $year, $quarter] = explode('-', $key);
            return [
                'region_id' => (int) $region_id,
                'year' => (int) $year,
                'quarter' => (int) $quarter,
                'adhb' => $group->sum('adhb'),
                'adhk' => $group->sum('adhk')
            ];
        });
        $group_peng_before_import = $data_peng_before_import->groupBy(function ($item) {
            return $item['region_id'] . '-' . $item['year'] . '-' .  $item['quarter'];
        })->map(function ($group, $key) {
            [$region_id, $year, $quarter] = explode('-', $key);
            return [
                'region_id' => (int) $region_id,
                'year' => (int) $year,
                'quarter' => (int) $quarter,
                'adhb' => $group->sum('adhb'),
                'adhk' => $group->sum('adhk')
            ];
        });

        $group_peng_before = $group_peng_before_total->map(function ($total, $key) use ($group_peng_before_import) {
            $import = $group_peng_before_import->get($key, ['adhb' => 0, 'adhk' => 0]);
            return [
                'region_id' => $total['region_id'],
                'year' => $total['year'],
                'quarter' => $total['quarter'],
                'adhb' => $total['adhb'] - $import['adhb'],
                'adhk' => $total['adhk'] - $import['adhk'],
            ];
        });

        $query_peng_total = Pdrb::query();
        $query_peng_total->leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
            ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
            ->whereIn('pdrbs.dataset_id', $peng)
            ->whereNotIn('subsector_id', $importId);
        if ($region != 17) {
            $data_peng_total = $query_peng_total
                ->where('d.region_id', $region)
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
                ->get();
            $data_peng_total = $data_peng_total
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'year' => $item->year,
                        'quarter' => $item->quarter,
                        'subsector_id' => $item->subsector_id,
                        'adhb' => $item->adhb + ($item->adj_adhb ?? 0),
                        'adhk' => $item->adhk + ($item->adj_adhk ?? 0),
                        'region_id' => $item->region_id
                    ];
                });
        } else {
            $data_peng_total = $query_peng_total->whereNot('d.region_id', 1)
                ->orderBy('d.region_id', 'asc')
                ->select(
                    'pdrbs.year',
                    'pdrbs.quarter',
                    'pdrbs.subsector_id',
                    DB::raw('SUM(pdrbs.adhb + IFNULL(adj.adhb, 0)) as adhb'),
                    DB::raw('SUM(pdrbs.adhk + IFNULL(adj.adhk, 0)) as adhk')
                )
                ->groupBy('pdrbs.year', 'pdrbs.quarter', 'pdrbs.subsector_id')
                ->get();
            $data_peng_total = $data_peng_total
                ->map(function ($item) {
                    return [
                        'year' => $item->year,
                        'quarter' => $item->quarter,
                        'subsector_id' => $item->subsector_id,
                        'adhb' => (float) $item->adhb,
                        'adhk' => (float) $item->adhk,
                        'region_id' => 17
                    ];
                });
        }

        $data_peng_import = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
            ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
            ->whereIn('pdrbs.dataset_id', $peng)
            ->whereIn('subsector_id', $importId)
            ->where('d.region_id', $region)
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
                    'year' => $item->year,
                    'quarter' => $item->quarter,
                    'subsector_id' => $item->subsector_id,
                    'adhb' => $item->adhb + ($item->adj_adhb ?? 0),
                    'adhk' => $item->adhk + ($item->adj_adhk ?? 0),
                    'region_id' => $item->region_id
                ];
            });

        $query_peng_import = Pdrb::query();
        $query_peng_import->leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
            ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
            ->whereIn('pdrbs.dataset_id', $peng)
            ->whereNotIn('subsector_id', $importId);
        if ($region != 17) {
            $data_peng_import = $query_peng_import
                ->where('d.region_id', $region)
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
                ->get();
            $data_peng_import = $data_peng_import
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'year' => $item->year,
                        'quarter' => $item->quarter,
                        'subsector_id' => $item->subsector_id,
                        'adhb' => $item->adhb + ($item->adj_adhb ?? 0),
                        'adhk' => $item->adhk + ($item->adj_adhk ?? 0),
                        'region_id' => $item->region_id
                    ];
                });
        } else {
            $data_peng_import = $query_peng_import->whereNot('d.region_id', 1)
                ->orderBy('d.region_id', 'asc')
                ->select(
                    'pdrbs.year',
                    'pdrbs.quarter',
                    'pdrbs.subsector_id',
                    DB::raw('SUM(pdrbs.adhb + IFNULL(adj.adhb, 0)) as adhb'),
                    DB::raw('SUM(pdrbs.adhk + IFNULL(adj.adhk, 0)) as adhk')
                )
                ->groupBy('pdrbs.year', 'pdrbs.quarter', 'pdrbs.subsector_id')
                ->get();
            $data_peng_import = $data_peng_import
                ->map(function ($item) {
                    return [
                        'year' => $item->year,
                        'quarter' => $item->quarter,
                        'subsector_id' => $item->subsector_id,
                        'adhb' => (float) $item->adhb,
                        'adhk' => (float) $item->adhk,
                        'region_id' => 17
                    ];
                });
        }

        $group_peng_total = $data_peng_total->groupBy(function ($item) {
            return $item['region_id'] . '-' . $item['year'] . '-' .  $item['quarter'];
        })->map(function ($group, $key) {
            [$region_id, $year, $quarter] = explode('-', $key);
            return [
                'region_id' => (int) $region_id,
                'year' => (int) $year,
                'quarter' => (int) $quarter,
                'adhb' => $group->sum('adhb'),
                'adhk' => $group->sum('adhk')
            ];
        });
        $group_peng_import = $data_peng_import->groupBy(function ($item) {
            return $item['region_id'] . '-' . $item['year'] . '-' .  $item['quarter'];
        })->map(function ($group, $key) {
            [$region_id, $year, $quarter] = explode('-', $key);
            return [
                'region_id' => (int) $region_id,
                'year' => (int) $year,
                'quarter' => (int) $quarter,
                'adhb' => $group->sum('adhb'),
                'adhk' => $group->sum('adhk')
            ];
        });
        $group_peng_now = $group_peng_total->map(function ($total, $key) use ($group_peng_import) {
            $import = $group_peng_import->get($key, ['adhb' => 0, 'adhk' => 0]);
            return [
                'region_id' => $total['region_id'],
                'year' => $total['year'],
                'quarter' => $total['quarter'],
                'adhb' => $total['adhb'] - $import['adhb'],
                'adhk' => $total['adhk'] - $import['adhk'],
            ];
        });

        $super_result = [];
        foreach ($group_lapus_now as $key => $value) {
            # code...
            $result = $this->buildTotal($group_lapus_before, $value, $group_lapus_now);
            array_push($super_result, $result);
            $updating_summary = SummaryPdrb::where('region_id', $value['region_id'])
                ->where('quarter', $value['quarter'])
                ->where('category_id', 98)
                ->where('sector_id', 98)
                ->where('subsector_id', 98)
                ->update(
                    [
                        'adhb' => $value['adhb'],
                        'adhk' => $value['adhk'],
                        'qtoq' => $result['qtoq'],
                        'yony' => $result['yony'],
                        'ctoc' => $result['ctoc'],
                        'idx' => $result['idx'],
                        'iqtoq' => $result['iqtoq'],
                        'iyony' => $result['iyony']
                    ]
                );
        }
        foreach ($group_peng_now as $key => $value) {
            # code...
            $result = $this->buildTotal($group_peng_before, $value, $group_peng_now);
            array_push($super_result, $result);
            $updating_summary = SummaryPdrb::where('region_id', $value['region_id'])
                ->where('quarter', $value['quarter'])
                ->where('category_id', 99)
                ->where('sector_id', 99)
                ->where('subsector_id', 99)
                ->update(
                    [
                        'adhb' => $value['adhb'],
                        'adhk' => $value['adhk'],
                        'qtoq' => $result['qtoq'],
                        'yony' => $result['yony'],
                        'ctoc' => $result['ctoc'],
                        'idx' => $result['idx'],
                        'iqtoq' => $result['iqtoq'],
                        'iyony' => $result['iyony']
                    ]
                );
        }
        return $super_result;
    }

    public function getProgress()
    {
        return response()->json([
            'category' => Cache::get('category'),
            'sector' => Cache::get('sector'),
            'subsector' => Cache::get('subsector'),
            'dist' => Cache::get('dist'),
            'progress' => Cache::get('progress'),
        ]);
    }

    private function buildValue($past, $present, $presentObject, $type, $typeAttribute)
    {
        $qtoq = 0;
        $yony = 0;
        $ctoc = 0;
        $idx = ($present['adhb'] != 0) && ($present['adhk'] != 0) ? ($present['adhb'] / $present['adhk']) * 100 : 0;
        $idx_before_qtoq = 0;
        $idx_before_yony = 0;
        $iqtoq = 0;
        $iyony = 0;
        $adhb_before_yony = $past->where('quarter', $present['quarter'])
            ->where($type, ($typeAttribute == 'category') ? $present['category_id'] : (($typeAttribute == 'sector') ? $present['sector_id'] : $present['subsector_id']))
            ->where('region_id', $present['region_id'])
            ->value('adhb');
        $adhk_before_yony = $past->where('quarter', $present['quarter'])
            ->where($type, ($typeAttribute == 'category') ? $present['category_id'] : (($typeAttribute == 'sector') ? $present['sector_id'] : $present['subsector_id']))
            ->where('region_id', $present['region_id'])
            ->value('adhk');
        $idx_before_yony = ($adhb_before_yony != 0) && ($adhk_before_yony != 0) ? ($adhb_before_yony / $adhk_before_yony) * 100 : 0;
        if ($present['quarter'] == 1) {
            $adhb_before_qtoq = $past->where('quarter', 4)
                ->where($type, ($typeAttribute == 'category') ? $present['category_id'] : (($typeAttribute == 'sector') ? $present['sector_id'] : $present['subsector_id']))
                ->where('region_id', $present['region_id'])
                ->value('adhb');
            $adhk_before_qtoq = $past->where('quarter', 4)
                ->where($type, ($typeAttribute == 'category') ? $present['category_id'] : (($typeAttribute == 'sector') ? $present['sector_id'] : $present['subsector_id']))
                ->where('region_id', $present['region_id'])
                ->value('adhk');
        } else {
            $adhb_before_qtoq = $presentObject->where('quarter', $present['quarter'])
                ->where('region_id', $present['region_id'])
                ->where($type, ($typeAttribute == 'category') ? $present['category_id'] : (($typeAttribute == 'sector') ? $present['sector_id'] : $present['subsector_id']))
                ->value('adhb');
            $adhk_before_qtoq = $presentObject->where('quarter', $present['quarter'])
                ->where('region_id', $present['region_id'])
                ->where($type, ($typeAttribute == 'category') ? $present['category_id'] : (($typeAttribute == 'sector') ? $present['sector_id'] : $present['subsector_id']))
                ->value('adhk');
        }
        $idx_before_qtoq = ($adhb_before_qtoq != 0) && ($adhk_before_qtoq != 0) ? ($adhb_before_qtoq / $adhk_before_qtoq) * 100 : 0;
        $before_cumulative = 0;
        $present_cumulative = 0;
        for ($i = 1; $i <= $present['quarter']; $i++) {
            # code...
            $before_cumulative += $past->where('quarter', $i)
                ->where($type, ($typeAttribute == 'category') ? $present['category_id'] : (($typeAttribute == 'sector') ? $present['sector_id'] : $present['subsector_id']))
                ->where('region_id', $present['region_id'])
                ->value('adhk');
            $present_cumulative += $presentObject->where('quarter', $i)
                ->where($type, ($typeAttribute == 'category') ? $present['category_id'] : (($typeAttribute == 'sector') ? $present['sector_id'] : $present['subsector_id']))
                ->where('region_id', $present['region_id'])
                ->value('adhk');
        }
        $ctoc = ($before_cumulative != 0) && ($present_cumulative != 0) ? ($present_cumulative / $before_cumulative) * 100 - 100 : 0;
        $qtoq = ($adhk_before_qtoq != 0) && ($present['adhk'] != 0) ? ($present['adhk'] / $adhk_before_qtoq) * 100 - 100 : 0;
        $yony = ($adhk_before_yony != 0) && ($present['adhk'] != 0) ? ($present['adhk'] / $adhk_before_yony) * 100 - 100 : 0;
        $iqtoq = ($idx_before_qtoq != 0) && ($idx != 0) ? ($idx / $idx_before_qtoq) * 100 - 100 : 0;
        $iyony = ($idx_before_yony != 0) && ($idx != 0) ? ($idx / $idx_before_yony) * 100 - 100 : 0;

        $result = [
            'qtoq' => $qtoq,
            'yony' => $yony,
            'ctoc' => $ctoc,
            'idx' => $idx,
            'iqtoq' => $iqtoq,
            'iyony' => $iyony
        ];
        return $result;
    }

    private function buildTotal($past, $present, $presentObject)
    {
        $qtoq = 0;
        $yony = 0;
        $ctoc = 0;
        $idx = ($present['adhb'] != 0) && ($present['adhk'] != 0) ? ($present['adhb'] / $present['adhk']) * 100 : 0;
        $idx_before_qtoq = 0;
        $idx_before_yony = 0;
        $iqtoq = 0;
        $iyony = 0;
        $adhb_before_yony = $past->where('quarter', $present['quarter'])
            ->where('region_id', $present['region_id'])
            ->value('adhb');
        $adhk_before_yony = $past->where('quarter', $present['quarter'])
            ->where('region_id', $present['region_id'])
            ->value('adhk');
        $idx_before_yony = ($adhb_before_yony != 0) && ($adhk_before_yony != 0) ? ($adhb_before_yony / $adhk_before_yony) * 100 : 0;
        if ($present['quarter'] == 1) {
            $adhb_before_qtoq = $past->where('quarter', 4)
                ->where('region_id', $present['region_id'])
                ->value('adhb');
            $adhk_before_qtoq = $past->where('quarter', 4)
                ->where('region_id', $present['region_id'])
                ->value('adhk');
        } else {
            $adhb_before_qtoq = $presentObject->where('quarter', $present['quarter'])
                ->where('region_id', $present['region_id'])
                ->value('adhb');
            $adhk_before_qtoq = $presentObject->where('quarter', $present['quarter'])
                ->where('region_id', $present['region_id'])
                ->value('adhk');
        }
        $idx_before_qtoq = ($adhb_before_qtoq != 0) && ($adhk_before_qtoq != 0) ? ($adhb_before_qtoq / $adhk_before_qtoq) * 100 : 0;
        $before_cumulative = 0;
        $present_cumulative = 0;
        for ($i = 1; $i <= $present['quarter']; $i++) {
            # code...
            $before_cumulative += $past->where('quarter', $i)
                ->where('region_id', $present['region_id'])
                ->value('adhk');
            $present_cumulative += $presentObject->where('quarter', $i)
                ->where('region_id', $present['region_id'])
                ->value('adhk');
        }
        $ctoc = ($before_cumulative != 0) && ($present_cumulative != 0) ? ($present_cumulative / $before_cumulative) * 100 - 100 : 0;
        $qtoq = ($adhk_before_qtoq != 0) && ($present['adhk'] != 0) ? ($present['adhk'] / $adhk_before_qtoq) * 100 - 100 : 0;
        $yony = ($adhk_before_yony != 0) && ($present['adhk'] != 0) ? ($present['adhk'] / $adhk_before_yony) * 100 - 100 : 0;
        $iqtoq = ($idx_before_qtoq != 0) && ($idx != 0) ? ($idx / $idx_before_qtoq) * 100 - 100 : 0;
        $iyony = ($idx_before_yony != 0) && ($idx != 0) ? ($idx / $idx_before_yony) * 100 - 100 : 0;

        $result = [
            'qtoq' => $qtoq,
            'yony' => $yony,
            'ctoc' => $ctoc,
            'idx' => $idx,
            'iqtoq' => $iqtoq,
            'iyony' => $iyony
        ];
        return $result;
    }
}
