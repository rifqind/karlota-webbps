<?php

namespace App\Http\Controllers;

use App\Models\Period;
use App\Models\SummaryTime;
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
        $sumTime = SummaryTime::leftJoin('periods as p', 'p.id', '=', 'summary_time.period_id')
            ->select(['p.type', 'p.year', 'p.quarter', 'p.description', 'p.status'])
            ->get();
        return Inertia::render('Dashboard', [
            'lapus' => $active_periode_lapus,
            'peng' => $active_periode_peng,
            'sumTime' => $sumTime,
        ]);
    }

    public function updateSummaryTime(Request $request)
    {
        $lapus_id = $request->lapus_id;
        $peng_id = $request->peng_id;

        $notification = [];
        try {
            //code...
            DB::beginTransaction();
            $update_lapus = SummaryTime::updateOrCreate(
                ['type' => 'Lapangan Usaha'],
                [
                    'type' => 'Lapangan Usaha',
                    'period_id' => $lapus_id,
                    'timestamp' => Carbon::now(),
                ]
            );
            $update_peng = SummaryTime::updateOrCreate(
                ['type' => 'Pengeluaran'],
                [
                    'type' => 'Pengeluaran',
                    'period_id' => $peng_id,
                    'timestamp' => Carbon::now(),
                ]
            );

            $message = [
                'type' => 'success',
                'message' => 'Terima kasih telah bersabar dalam sinkronisasi data!'
            ];
            array_push($notification, $message);
            DB::commit();
            return redirect()->route('dashboard')->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
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
}
