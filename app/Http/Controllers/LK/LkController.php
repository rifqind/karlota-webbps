<?php

namespace App\Http\Controllers\LK;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\LK\Komoditas;
use App\Models\LK\LKDataDasar;
use App\Models\Sector;
use App\Models\Subsector;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LkController extends Controller
{
    //
    public function dashboard()
    {
        return Inertia::render('LK/Home/Dashboard');
    }

    public function index()
    {
        $category = Category::select(['id as value', 'name as label'])->get();
        $komoditas = Komoditas::where('subsector_id', 1)->get();
        $test_data = Komoditas::leftJoin('master_harga as mh', 'mh.komoditas_id', '=', 'master_komoditas.id')
            ->join('indeks_harga as ih', 'ih.komoditas_id', '=', 'master_komoditas.id')
            ->where('subsector_id', 1)
            ->select([
                'ih.indeks_harga',
                'ih.triwulan',
                'ih.tahun',
                'mh.harga_konstan',
                'master_komoditas.*'
            ])
            ->get()
            ->map(function ($item) {
                $item->produksi = null;
                return $item;
            });
        $mapped = $test_data->groupBy('tahun')
            ->map(function ($by) {
                return $by->groupBy('triwulan')->map(function ($item) {
                    return $item;
                });
            });
        return Inertia::render(
            'LK/Home/Index',
            [
                'category' => $category,
                'test' => $komoditas,
                // 'mapped' => $mapped
            ]
        );
    }

    public function getData(Request $request)
    {
        $cat = $request->category ?? null;
        $sec = $request->sector ?? null;
        $sub = $request->subsector ?? null;
        $data = Komoditas::leftJoin('master_harga as mh', 'mh.komoditas_id', '=', 'master_komoditas.id')
            ->join('indeks_harga as ih', 'ih.komoditas_id', '=', 'master_komoditas.id')
            ->whereIn('ih.tahun', $request->years)
            ->where(function ($q) use ($cat, $sec, $sub) {
                $q->where('category_id', $cat)
                    ->orWhere('sector_id', $sec)
                    ->orWhere('subsector_id', $sub);
            })
            ->select([
                'ih.indeks_harga',
                'ih.triwulan',
                'ih.tahun',
                'mh.harga_konstan',
                'master_komoditas.*'
            ])
            ->get()
            ->map(function ($item) {
                $komoditas = LKDataDasar::where('id', $item->id)
                    ->where('tahun', $item->tahun)
                    ->where('triwulan', $item->triwulan)
                    ->first();
                $item->produksi = $komoditas ? $komoditas->produksi : null;
                return $item;
            });
        $mapped = $data->groupBy('tahun')
            ->map(function ($by) {
                return $by->groupBy('triwulan')->map(function ($item) {
                    return $item;
                });
            });
        return response()->json($mapped);
    }

    public function fetchSector($category_id)
    {
        $target = Sector::where('category_id', $category_id)
            ->select(['id as value', 'name as label'])
            ->get();
        return response()->json($target);
    }

    public function fetchSubsector($sector_id)
    {
        $target = Subsector::where('sector_id', $sector_id)
            ->select(['id as value', 'name as label'])
            ->get();
        return response()->json($target);
    }
}
