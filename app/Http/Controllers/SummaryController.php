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
        $kbli = $request->kbli ?? 'kbli_2020';

        $lapus_PERIOD = $request->lapus_period_id
            ? Period::find($request->lapus_period_id)
            : Period::where('type', 'Lapangan Usaha')
                ->where('kbli', $kbli)
                ->where('status', 'Aktif')
                ->orderBy('year', 'desc')
                ->orderBy('quarter', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
        if (!$lapus_PERIOD) {
            $lapus_PERIOD = Period::where('type', 'Lapangan Usaha')
                ->where('kbli', $kbli)
                ->where('status', 'Final')
                ->orderBy('year', 'desc')
                ->orderBy('quarter', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
        }
        if (!$lapus_PERIOD) {
            $lapus_PERIOD = Period::where('type', 'Lapangan Usaha')
                ->where('kbli', $kbli)
                ->orderBy('year', 'desc')
                ->orderBy('quarter', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
        }
        $lapus_period = $lapus_PERIOD?->id;

        $lapus_period_before = $lapus_PERIOD
            ? Period::where('type', 'Lapangan Usaha')
                ->where('kbli', $kbli)
                ->where('year', $lapus_PERIOD->year - 1)
                ->where('quarter', 4)
                ->orderBy('created_at', 'desc')
                ->value('id')
            : null;

        $peng_PERIOD = $request->peng_period_id
            ? Period::find($request->peng_period_id)
            : Period::where('type', 'Pengeluaran')
                ->where('kbli', $kbli)
                ->where('status', 'Aktif')
                ->orderBy('year', 'desc')
                ->orderBy('quarter', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
        if (!$peng_PERIOD) {
            $peng_PERIOD = Period::where('type', 'Pengeluaran')
                ->where('kbli', $kbli)
                ->where('status', 'Final')
                ->orderBy('year', 'desc')
                ->orderBy('quarter', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
        }
        if (!$peng_PERIOD) {
            $peng_PERIOD = Period::where('type', 'Pengeluaran')
                ->where('kbli', $kbli)
                ->orderBy('year', 'desc')
                ->orderBy('quarter', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
        }
        $peng_period = $peng_PERIOD?->id;

        $peng_period_before = $peng_PERIOD
            ? Period::where('type', 'Pengeluaran')
                ->where('kbli', $kbli)
                ->where('year', $peng_PERIOD->year - 1)
                ->where('quarter', 4)
                ->orderBy('created_at', 'desc')
                ->value('id')
            : null;

        $periodIds = array_filter([$lapus_period, $peng_period]);
        $periodIdsBefore = array_filter([$lapus_period_before, $peng_period_before]);

        $dataset = !empty($periodIds)
            ? Dataset::whereIn('period_id', $periodIds)->pluck('id')
            : collect([]);
        $dataset_before = !empty($periodIdsBefore)
            ? Dataset::whereIn('period_id', $periodIdsBefore)->pluck('id')
            : collect([]);

        $setup = $request->setup;
        if ($setup == 'category') {
            $result_cat =  $this->setupCategory($dataset_before, $dataset, $request->region_id, $kbli);
        } else if ($setup == 'sector') {
            $result_set =  $this->setupSector($dataset_before, $dataset, $request->region_id, $kbli);
        } else if ($setup == 'subsector') {
            $result_sub = $this->setupSubsector($dataset_before, $dataset, $request->region_id, $kbli);
        } else {
            $dataset_lapus = $lapus_period ? Dataset::where('period_id', $lapus_period)->pluck('id') : collect([]);
            $dataset_peng = $peng_period ? Dataset::where('period_id', $peng_period)->pluck('id') : collect([]);
            $dataset_lapus_before = $lapus_period_before ? Dataset::where('period_id', $lapus_period_before)->pluck('id') : collect([]);
            $dataset_peng_before = $peng_period_before ? Dataset::where('period_id', $peng_period_before)->pluck('id') : collect([]);
            $result_tot = $this->setupTotal($dataset_lapus_before, $dataset_lapus, $dataset_peng_before, $dataset_peng, $request->region_id, $kbli);
        }


        $targetQuarter = $request->quarter ?? $lapus_PERIOD?->quarter ?? $peng_PERIOD?->quarter ?? 1;

        $this->calculateDistributions($request->region_id, $targetQuarter, $kbli);

        return response()->json([
            'message' => $setup . ' done',
            'lapus_period' => $lapus_period,
            'peng_period' => $peng_period
        ]);
    }

    public function calculateDistributions($region_id, $quarter, $kbli = 'kbli_2020')
    {
        $lapus_total = SummaryPdrb::where('kbli', $kbli)
            ->where('region_id', $region_id)
            ->where('quarter', $quarter)
            ->where('subsector_id', 98)
            ->value('adhb');

        $peng_total = SummaryPdrb::where('kbli', $kbli)
            ->where('region_id', $region_id)
            ->where('quarter', $quarter)
            ->where('subsector_id', 99)
            ->value('adhb');

        $categoryTypes = Category::where('kbli', $kbli)->pluck('type', 'id');

        $data = SummaryPdrb::where('kbli', $kbli)
            ->where('region_id', $region_id)
            ->where('quarter', $quarter)
            ->where(function ($query) {
                $query->whereNotIn('subsector_id', [98, 99])
                    ->orWhereNull('subsector_id');
            })
            ->get();

        foreach ($data as $value) {
            $dist = 0;
            $catType = $categoryTypes[$value->category_id] ?? ($value->category_id < 18 ? 'Lapangan Usaha' : 'Pengeluaran');
            if ($catType === 'Lapangan Usaha') {
                $dist = ($value->adhb != 0 && $lapus_total != 0) ? ($value->adhb / $lapus_total) * 100 : 0;
            } else if ($catType === 'Pengeluaran') {
                $dist = ($value->adhb != 0 && $peng_total != 0) ? ($value->adhb / $peng_total) * 100 : 0;
            }
            SummaryPdrb::where('id', $value->id)->update(['dist' => $dist]);
        }

        SummaryPdrb::where('kbli', $kbli)
            ->where('region_id', $region_id)
            ->where('quarter', $quarter)
            ->whereIn('subsector_id', [98, 99])
            ->update(['dist' => 100]);
    }

    private function setupCategory($dataset_before, $dataset, $region, $kbli = 'kbli_2020')
    {
        $category = Category::where('kbli', $kbli)->pluck('id');
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
                $quarter_list = $data->pluck('quarter');
                foreach ($quarter_list as $key => $qt) {
                    # code...
                    $this_data = $data->where('quarter', $qt)->first();
                    $result = $this->buildValue($data_before, $this_data, $data, 'category_id', 'category');
                    array_push($super_result, $this_data);
                    SummaryPdrb::updateOrCreate(
                        [
                            'kbli' => $kbli,
                            'region_id' => $this_data['region_id'],
                            'quarter' => $this_data['quarter'],
                            'category_id' => $this_data['category_id'],
                            'sector_id' => null,
                            'subsector_id' => null,
                        ],
                        [
                            'adhb' => $this_data['adhb'],
                            'adhk' => $this_data['adhk'],
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
        }
        return $super_result;
    }

    private function setupSector($dataset_before, $dataset, $region, $kbli = 'kbli_2020')
    {
        $sectorMap = Sector::where('kbli', $kbli)->pluck('category_id', 'id');
        $sector = $sectorMap->keys();
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
                $quarter_list = $data->pluck('quarter');
                foreach ($quarter_list as $key => $qt) {
                    $this_data = $data->where('quarter', $qt)->first();
                    $result = $this->buildValue($data_before, $this_data, $data, 'sector_id', 'sector');
                    array_push($super_result, $this_data);
                    SummaryPdrb::updateOrCreate(
                        [
                            'kbli' => $kbli,
                            'region_id' => $this_data['region_id'],
                            'quarter' => $this_data['quarter'],
                            'category_id' => $sectorMap[$this_data['sector_id']] ?? null,
                            'sector_id' => $this_data['sector_id'],
                            'subsector_id' => null,
                        ],
                        [
                            'adhb' => $this_data['adhb'],
                            'adhk' => $this_data['adhk'],
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
        }
        return $super_result;
    }

    private function setupSubsector($dataset_before, $dataset, $region, $kbli = 'kbli_2020')
    {
        $subsectorModels = Subsector::where('kbli', $kbli)->with('sector:id,category_id')->get(['id', 'sector_id'])->keyBy('id');
        $subsector = $subsectorModels->keys();
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
                $quarter_list = $data->pluck('quarter');
                foreach ($quarter_list as $key => $qt) {
                    # code...
                    $this_data = $data->where('quarter', $qt)->first();
                    $result = $this->buildValue($data_before, $this_data, $data, 'subsector_id', 'subsector');
                    array_push($super_result, $result);
                    $subModel = $subsectorModels->get($this_data['subsector_id']);
                    SummaryPdrb::updateOrCreate(
                        [
                            'kbli' => $kbli,
                            'region_id' => $this_data['region_id'],
                            'quarter' => $this_data['quarter'],
                            'category_id' => $subModel?->sector?->category_id,
                            'sector_id' => $subModel?->sector_id,
                            'subsector_id' => $this_data['subsector_id'],
                        ],
                        [
                            'adhb' => $this_data['adhb'],
                            'adhk' => $this_data['adhk'],
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
        }
        return $super_result;
    }

    private function setupTotal($lapus_before, $lapus, $peng_before, $peng, $region, $kbli = 'kbli_2020')
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
            array_push($super_result, $value);
            SummaryPdrb::updateOrCreate(
                [
                    'kbli' => $kbli,
                    'region_id' => $value['region_id'],
                    'quarter' => $value['quarter'],
                    'category_id' => 98,
                    'sector_id' => 98,
                    'subsector_id' => 98,
                ],
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
            array_push($super_result, $value);
            SummaryPdrb::updateOrCreate(
                [
                    'kbli' => $kbli,
                    'region_id' => $value['region_id'],
                    'quarter' => $value['quarter'],
                    'category_id' => 99,
                    'sector_id' => 99,
                    'subsector_id' => 99,
                ],
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
            $adhb_before_qtoq = $presentObject->where('quarter', $present['quarter'] - 1)
                ->where('region_id', $present['region_id'])
                ->where($type, ($typeAttribute == 'category') ? $present['category_id'] : (($typeAttribute == 'sector') ? $present['sector_id'] : $present['subsector_id']))
                ->value('adhb');
            $adhk_before_qtoq = $presentObject->where('quarter', $present['quarter'] - 1)
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
            $adhb_before_qtoq = $presentObject->where('quarter', $present['quarter']-1)
                ->where('region_id', $present['region_id'])
                ->value('adhb');
            $adhk_before_qtoq = $presentObject->where('quarter', $present['quarter']-1)
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
