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
            $this->setupCategory($dataset_before, $dataset);
        } else if ($setup == 'sector') {
            $this->setupSector($dataset_before, $dataset);
        } else if ($setup == 'subsector') {
            $this->setupSubsector($dataset_before, $dataset);
        } else {
            $this->setupTotal($dataset_before, $dataset);
        }

        // $summary_per_regions = SummaryPdrb::orderBy('region_id', 'asc')
        //     ->whereNot('subsector_id', null)
        //     ->groupBy('region_id', 'quarter')
        //     ->selectRaw('quarter, SUM(adhb) as total_adhb, region_id')
        //     ->get();

        // foreach ($summary_per_regions as $keyReg => $region) {
        //     # code...
        //     $summaries = SummaryPdrb::pluck('id');
        //     foreach ($summaries as $key => $value) {
        //         # code...
        //         $this_pdrb = SummaryPdrb::where('id', $value)->value('adhb');
        //         $dist = SummaryPdrb::where('id', $value)
        //             ->where('quarter', $region->quarter)
        //             ->where('region_id', $region->region_id)
        //             ->update([
        //                 'dist' => ($this_pdrb != 0 && $region->total_adhb != 0) ? ($this_pdrb / $region->total_adhb) * 100 : 0
        //             ]);
        //     }
        // }
        return response()->json([
            'message' => $setup . ' done',
            'lapus_period' => $lapus_period,
            'peng_period' => $peng_period
        ]);
    }

    private function setupCategory($dataset_before, $dataset)
    {
        $category = Category::pluck('id');
        foreach ($category as $keyCat => $cat) {
            # code...
            $sectorForSearch = Sector::where('category_id', $cat)->pluck('id');
            $subsectorForSearch = Subsector::whereIn('sector_id', $sectorForSearch)->pluck('id');
            $data_before = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                ->whereIn('pdrbs.dataset_id', $dataset_before)
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
                ->map(function ($item) use ($cat) {
                    return [
                        'year' => $item->year,
                        'quarter' => $item->quarter,
                        'category_id' => $cat,
                        'adhb' => $item->adhb + ($item->adj_adhb ?? 0),
                        'adhk' => $item->adhk + ($item->adj_adhk ?? 0),
                        'region_id' => $item->region_id
                    ];
                });
            $data = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                ->whereIn('pdrbs.dataset_id', $dataset)
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
                ->map(function ($item) use ($cat) {
                    return [
                        'year' => $item->year,
                        'quarter' => $item->quarter,
                        'category_id' => $cat,
                        'adhb' => $item->adhb + ($item->adj_adhb ?? 0),
                        'adhk' => $item->adhk + ($item->adj_adhk ?? 0),
                        'region_id' => $item->region_id
                    ];
                });
            foreach ($data as $key => $value) {
                # code...
                $result = $this->buildValue($data_before, $value, $data, 'category_id', 'category');
                $updating_summary = SummaryPdrb::where('region_id', $value['region_id'])
                    ->where('quarter', $value['quarter'])
                    ->where('category_id', $value['category_id'])
                    ->where('sector_id', null)
                    ->where('subsector_id', null)
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
        }
    }

    private function setupSector($dataset_before, $dataset)
    {
        $sector = Sector::pluck('id');
        foreach ($sector as $keySet => $set) {
            # code...
            $subsectorForSearch = Subsector::where('sector_id', $set)->pluck('id');
            if ($set == '54') {
                $importId = ['69'];
                $total_before = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                    ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                    ->whereIn('pdrbs.dataset_id', $dataset_before)
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
                $total = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                    ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                    ->whereIn('pdrbs.dataset_id', $dataset)
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

                $import_before = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                    ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                    ->whereIn('pdrbs.dataset_id', $dataset_before)
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

                $import = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                    ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                    ->whereIn('pdrbs.dataset_id', $dataset)
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
                $data_before = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                    ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                    ->whereIn('pdrbs.dataset_id', $dataset_before)
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
                    ->map(function ($item) use ($set) {
                        return [
                            'year' => $item->year,
                            'quarter' => $item->quarter,
                            'sector_id' => $set,
                            'adhb' => $item->adhb + ($item->adj_adhb ?? 0),
                            'adhk' => $item->adhk + ($item->adj_adhk ?? 0),
                            'region_id' => $item->region_id
                        ];
                    });

                $data = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                    ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                    ->whereIn('pdrbs.dataset_id', $dataset)
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
                    ->map(function ($item) use ($set) {
                        return [
                            'year' => $item->year,
                            'quarter' => $item->quarter,
                            'sector_id' => $set,
                            'adhb' => $item->adhb + ($item->adj_adhb ?? 0),
                            'adhk' => $item->adhk + ($item->adj_adhk ?? 0),
                            'region_id' => $item->region_id
                        ];
                    });
            }
            foreach ($data as $key => $value) {
                # code...
                $result = $this->buildValue($data_before, $value, $data, 'sector_id', 'sector');
                $updating_summary = SummaryPdrb::where('region_id', $value['region_id'])
                    ->where('quarter', $value['quarter'])
                    ->where('sector_id', $value['sector_id'])
                    ->where('subsector_id', null)
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
        }
    }

    private function setupSubsector($dataset_before, $dataset)
    {
        $subsector = Subsector::pluck('id');
        foreach ($subsector as $keySub => $sub) {
            # code...

            $data_before = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                ->whereIn('pdrbs.dataset_id', $dataset_before)
                ->where('pdrbs.subsector_id', $sub)
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

            $data = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
                ->whereIn('pdrbs.dataset_id', $dataset)
                ->where('pdrbs.subsector_id', $sub)
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
            foreach ($data as $key => $value) {
                # code...
                $result = $this->buildValue($data_before, $value, $data, 'subsector_id', 'subsector');
                $updating_summary = SummaryPdrb::where('region_id', $value['region_id'])
                    ->where('quarter', $value['quarter'])
                    ->where('subsector_id', $value['subsector_id'])
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
        }
    }

    private function setupTotal($dataset_before, $dataset)
    {
        $subsector = Subsector::pluck('id');
        $data_before = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
            ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
            ->whereIn('pdrbs.dataset_id', $dataset_before)
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

        $group_before = $data_before->groupBy(function ($item) {
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
        $data = Pdrb::leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
            ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id')
            ->whereIn('pdrbs.dataset_id', $dataset)
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

        $group_now = $data->groupBy(function ($item) {
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
        
        $super_result = [];
        foreach ($group_now as $key => $value) {
            # code...
            $result = $this->buildTotal($group_before, $value, $group_now);  
            array_push($super_result,$result );      
            // $updating_summary = SummaryPdrb::where('region_id', $value['region_id'])
            //     ->where('quarter', $value['quarter'])
            //     ->where('category_id', null)
            //     ->where('sector_id', null)
            //     ->where('subsector_id', null)
            //     ->update(
            //         [
            //             'adhb' => $value['adhb'],
            //             'adhk' => $value['adhk'],
            //             'qtoq' => $result['qtoq'],
            //             'yony' => $result['yony'],
            //             'ctoc' => $result['ctoc'],
            //             'idx' => $result['idx'],
            //             'iqtoq' => $result['iqtoq'],
            //             'iyony' => $result['iyony']
            //         ]
            //     );
        }
        dd($super_result);
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
        if ($present['region_id'] == '2') {
            dd($adhk_before_qtoq, $present['adhk'], ($present['adhk'] / $adhk_before_qtoq) * 100 - 100);
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
