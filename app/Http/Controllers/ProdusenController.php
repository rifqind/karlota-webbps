<?php

namespace App\Http\Controllers;

use App\Models\Produsen;
use App\Models\Region;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProdusenController extends Controller
{
    //
    public function index(Request $request)
    {
        //
        if ($request->paginated) $paginated = $request->paginated;
        else $paginated = 10;
        if ($request->currentPage) $currentPage = $request->currentPage;
        else $currentPage = 1;
        $query = Produsen::query();

        $number = 1;
        $wilayah = Region::getMyRegionId();
        $dataToCounted = $query
            ->leftJoin('regions as r', 'r.id', '=', 'produsen.region_id')
            ->whereIn(
                'r.id',
                $wilayah
            )->select(['produsen.*', 'r.name as region_name']);

        if ($request->orderAttribute) {
            $order = $request->orderAttribute;
            if (sizeof($order) > 2) $query->orderBy($order['label'], $order['value']);
            else $query->orderBy('region_id')->orderBy('nama');
        } else $query->orderBy('region_id')->orderBy('nama');
        if ($request->ArrayFilter) {
            $filter = $request->ArrayFilter;
            if (!empty($filter['nama'])) {
                $query->where('nama', 'like', '%' .  $filter['nama'] . '%');
            }
            if (!empty($filter['region_name'])) {
                $query->where('r.name', 'like', '%' . $filter['region_name'] . '%');
            }
        }
        $countData = $dataToCounted->count();
        $produsen = $query->paginate($paginated, ['*'], 'page', $currentPage);
        foreach ($produsen as $din) {
            $din->number = $number;
            $number++;
        }
        if ($request->paginated) {
            return response()->json([
                'produsen' => $produsen,
                'countData' => $countData,
            ]);
        }
        $wilayah_kerja = Region::whereIn('id', $wilayah)->select(['id as value', 'name as label'])->get();
        return Inertia::render('Produsen/Index', [
            'produsen' => $produsen,
            'wilayah' => $wilayah_kerja,
            'countData' => $countData,
        ]);
    }
}
