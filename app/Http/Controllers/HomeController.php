<?php

namespace App\Http\Controllers;

use App\Models\Period;
use App\Models\Region;
use App\Models\SummaryPdrb;
use App\Models\SummaryTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class HomeController extends Controller
{
    //
    public function index()
    {
        $active_periode_lapus = Period::where('type', 'Lapangan Usaha')
            ->where('status', 'Aktif')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->select(['type', 'description'])
            ->get();
        $active_periode_peng = Period::where('type', 'Pengeluaran')
            ->where('status', 'Aktif')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->select(['type', 'description'])
            ->get();
        $regions = Region::select(['id as value', 'name as label'])->get();
        $sumTime = SummaryTime::join('periods as p', 'p.id', '=', 'summary_time.period_id')
            ->join('users as u', 'u.id', '=', 'summary_time.id_user')
            ->select([
                'p.type',
                'p.year',
                'p.quarter',
                'p.description',
                'p.status',
                'summary_time.timestamp as waktu',
                'u.name as nama'
            ])
            ->get();
        $default_data = User::where('users.id', auth()->user()->id)
            ->join('regions as r', 'r.satker_id', '=', 'users.satker_id')
            ->select(['r.id'])->first();
        return Inertia::render('Dashboard', [
            'lapus' => $active_periode_lapus,
            'peng' => $active_periode_peng,
            'sumTime' => $sumTime,
            'regions' => $regions,
            'default' => $default_data,
        ]);
    }

    public function updateSummaryTime(Request $request)
    {
        $lapus_id = $request->lapus_id;
        $peng_id = $request->peng_id;

        $notification = [];
        try {
            DB::beginTransaction();
            $userId = auth()->check() ? auth()->id() : 1;
            if ($lapus_id) {
                SummaryTime::updateOrCreate(
                    ['type' => 'Lapangan Usaha'],
                    [
                        'type' => 'Lapangan Usaha',
                        'period_id' => $lapus_id,
                        'id_user' => $userId,
                        'timestamp' => Carbon::now(),
                    ]
                );
            }
            if ($peng_id) {
                SummaryTime::updateOrCreate(
                    ['type' => 'Pengeluaran'],
                    [
                        'type' => 'Pengeluaran',
                        'period_id' => $peng_id,
                        'id_user' => $userId,
                        'timestamp' => Carbon::now(),
                    ]
                );
            }

            $message = [
                'type' => 'success',
                'message' => 'Terima kasih telah bersabar dalam sinkronisasi data!'
            ];
            array_push($notification, $message);
            DB::commit();
            return redirect()->route('dashboard')->with('notification', $notification);
        } catch (\Throwable $th) {
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Ada kesalahan ketika update',
                'error' => $th->getMessage()
            ];
            array_push($notification, $message);
            return redirect()->route('dashboard')->with('notification', $notification);
        }
    }

    public function buildSummaries(Request $request)
    {
        set_time_limit(0);
        try {
            // Pre-step: Reset all summary records to 0 so un-uploaded regions stay 0 instead of holding stale data
            SummaryPdrb::query()->update([
                'adhb' => 0,
                'adhk' => 0,
                'qtoq' => 0,
                'yony' => 0,
                'ctoc' => 0,
                'idx' => 0,
                'iqtoq' => 0,
                'iyony' => 0,
                'dist' => 0,
            ]);

            $summaryController = new \App\Http\Controllers\SummaryController();

            $regions = Region::pluck('id')->toArray();
            if (!in_array(17, $regions)) {
                $regions[] = 17;
            }

            $setups = ['category', 'sector', 'subsector', 'total'];

            $lapus_period = Period::where('type', 'Lapangan Usaha')->where('status', 'Aktif')->orderBy('year', 'desc')->orderBy('quarter', 'desc')->orderBy('created_at', 'desc')->first()?->id
                ?? Period::where('type', 'Lapangan Usaha')->where('status', 'Final')->orderBy('year', 'desc')->orderBy('quarter', 'desc')->orderBy('created_at', 'desc')->first()?->id
                ?? Period::where('type', 'Lapangan Usaha')->orderBy('year', 'desc')->orderBy('quarter', 'desc')->orderBy('created_at', 'desc')->first()?->id;

            $peng_period = Period::where('type', 'Pengeluaran')->where('status', 'Aktif')->orderBy('year', 'desc')->orderBy('quarter', 'desc')->orderBy('created_at', 'desc')->first()?->id
                ?? Period::where('type', 'Pengeluaran')->where('status', 'Final')->orderBy('year', 'desc')->orderBy('quarter', 'desc')->orderBy('created_at', 'desc')->first()?->id
                ?? Period::where('type', 'Pengeluaran')->orderBy('year', 'desc')->orderBy('quarter', 'desc')->orderBy('created_at', 'desc')->first()?->id;

            $activeLapusPeriod = Period::find($lapus_period);
            $targetQuarter = $activeLapusPeriod ? $activeLapusPeriod->quarter : 1;

            foreach ($regions as $regId) {
                foreach ($setups as $setup) {
                    $subRequest = new Request([
                        'setup' => $setup,
                        'region_id' => $regId,
                        'quarter' => $targetQuarter,
                    ]);
                    $summaryController->index($subRequest);
                }
                $summaryController->calculateDistributions($regId, $targetQuarter);
            }

            $user = auth()->user();
            if (!$user && $request->attributes->get('auth_payload')) {
                $payload = $request->attributes->get('auth_payload');
                $user = User::find($payload['sub'] ?? null);
            }
            $userId = $user ? $user->id : (User::first()?->id);

            if ($lapus_period) {
                SummaryTime::updateOrCreate(
                    ['type' => 'Lapangan Usaha'],
                    [
                        'type' => 'Lapangan Usaha',
                        'period_id' => $lapus_period,
                        'id_user' => $userId,
                        'timestamp' => Carbon::now(),
                    ]
                );
            }
            if ($peng_period) {
                SummaryTime::updateOrCreate(
                    ['type' => 'Pengeluaran'],
                    [
                        'type' => 'Pengeluaran',
                        'period_id' => $peng_period,
                        'id_user' => $userId,
                        'timestamp' => Carbon::now(),
                    ]
                );
            }

            return response()->json([
                'message' => 'Berhasil menjalankan Build Summaries untuk seluruh wilayah!',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Gagal menjalankan Build Summaries: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function getSummary(Request $request)
    {
        $region_id = $request->region_id;
        $quarter = $request->quarter;
        $type = $request->type;

        $data = SummaryPdrb::where('quarter', $quarter)
            ->where('region_id', $region_id)
            ->where('category_id', ($type == 'Lapangan Usaha') ? 98 : 99)
            ->first();

        return response()->json([
            'data' => $data,
            'type' => $type
        ]);
    }

    public function getGraph(Request $request)
    {
        $type = $request->type;
        $catAttribute = $request->catAttribute;
        $quarter = $request->quarter;
        $regions = Region::whereNot('id', 1)->pluck('name');
        if ($catAttribute == 'distribusi') {
            $adhb = SummaryPdrb::orderBy('region_id', 'asc')
                ->whereNot('region_id', 1)
                ->where('quarter', $quarter)
                ->where('category_id', ($type == 'Lapangan Usaha') ? 98 : 99)
                ->pluck('adhb');
            $adhb_prov = SummaryPdrb::where('region_id', 1)
                ->where('quarter', $quarter)
                ->where('category_id', ($type == 'Lapangan Usaha') ? 98 : 99)
                ->value('adhb');
            $data = $adhb->map(function ($value) use ($adhb_prov) {
                return ($value != 0 && $adhb_prov != 0)
                    ? ($value / $adhb_prov) * 100
                    : 0;
            });
        } else {
            $data = SummaryPdrb::orderBy('region_id', 'asc')
                ->whereNot('region_id', 1)
                ->where('quarter', $quarter)
                ->where('category_id', ($type == 'Lapangan Usaha') ? 98 : 99)
                ->pluck($catAttribute);
        }
        return response()->json([
            'data' => $data,
            'regions' => $regions
        ]);
    }

    public function dashboardData(Request $request)
    {
        $active_periode_lapus = Period::where('type', 'Lapangan Usaha')
            ->where('status', 'Aktif')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->select(['type', 'description', 'year', 'quarter'])
            ->get();
        $active_periode_peng = Period::where('type', 'Pengeluaran')
            ->where('status', 'Aktif')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->select(['type', 'description', 'year', 'quarter'])
            ->get();
        $regions = Region::select(['id as value', 'name as label'])->get();
        $sumTime = SummaryTime::join('periods as p', 'p.id', '=', 'summary_time.period_id')
            ->join('users as u', 'u.id', '=', 'summary_time.id_user')
            ->select([
                'p.type',
                'p.year',
                'p.quarter',
                'p.description',
                'p.status',
                'summary_time.timestamp as waktu',
                'u.name as nama'
            ])
            ->get();

        $user = auth()->user();
        if (!$user && $request->attributes->get('auth_payload')) {
            $user = User::find($request->attributes->get('auth_payload')['sub'] ?? null);
        }

        $default_data = null;
        if ($user) {
            $reg = DB::table('users')
                ->join('regions as r', 'r.satker_id', '=', 'users.satker_id')
                ->where('users.id', $user->id)
                ->select(['r.id as value', 'r.name as label'])
                ->first();
            if ($reg) {
                $default_data = [
                    'id' => (int) $reg->value,
                    'value' => (int) $reg->value,
                    'label' => $reg->label,
                ];
            }
        }
        if (!$default_data && count($regions) > 0) {
            $firstRegion = $regions->first();
            $default_data = [
                'id' => (int) $firstRegion->value,
                'value' => (int) $firstRegion->value,
                'label' => $firstRegion->label,
            ];
        }
        return response()->json([
            'lapus' => $active_periode_lapus,
            'peng' => $active_periode_peng,
            'sumTime' => $sumTime,
            'regions' => $regions,
            'default' => $default_data,
        ]);
    }
}
