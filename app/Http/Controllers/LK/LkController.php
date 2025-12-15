<?php

namespace App\Http\Controllers\LK;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\LK\Komoditas;
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
            ->get();
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
                'mapped' => $mapped
            ]
        );
    }

    public function getData(Request $request)
    {
        $cat = $request->category ?? null;
        $sec = $request->sector ?? null;
        $sub = $request->subsector ?? null;
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
