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
use App\Services\SummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SummaryController extends Controller
{
    //
    public function index(Request $request, SummaryService $summaryService)
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
        $dataset_before = Dataset::where('period_id', $lapus_period_before)
            ->orWhere('period_id', $peng_period_before)
            ->pluck('id');

        $setup = $request->setup;
        if ($setup == 'category') {
            $result_cat = $summaryService->setupCategory($dataset_before, $dataset, $request->region_id);
        } else if ($setup == 'sector') {
            $result_set = $summaryService->setupSector($dataset_before, $dataset, $request->region_id);
        } else if ($setup == 'subsector') {
            $result_sub = $summaryService->setupSubsector($dataset_before, $dataset, $request->region_id);
        } else {
            $dataset_lapus = Dataset::where('period_id', $lapus_period)
                ->pluck('id');
            $dataset_peng = Dataset::where('period_id', $peng_period)
                ->pluck('id');
            $dataset_lapus_before = Dataset::where('period_id', $lapus_period_before)
                ->pluck('id');
            $dataset_peng_before = Dataset::where('period_id', $peng_period_before)
                ->pluck('id');
            $result_tot = $summaryService->setupTotal($dataset_lapus_before, $dataset_lapus, $dataset_peng_before, $dataset_peng, $request->region_id);
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
                $dist = ($value->adhb != 0 && $lapus_total != 0) ? ($value->adhb / $lapus_total) * 100 : 0;
            } else if ($value->category_id > 17) {
                $dist = ($value->adhb != 0 && $peng_total != 0) ? ($value->adhb / $peng_total) * 100 : 0;
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

}
