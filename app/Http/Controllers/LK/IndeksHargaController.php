<?php

namespace App\Http\Controllers\Lk;

use App\Http\Controllers\Controller;
use App\Models\HargaDasar;
use App\Models\LK\Komoditas;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndeksHargaController extends Controller
{
    //
    public function idxdasar(Request $request)
    {
        if ($request->paginated) $paginated = $request->paginated;
        else $paginated = 10;
        if ($request->currentPage) $currentPage = $request->currentPage;
        else $currentPage = 1;
        $number = 1;

        $query = HargaDasar::query();
        $dataToCounted = $query->join('komoditas as k', 'k.id', '=', 'master_harga.komoditas_id')
            ->select(['master_harga.*', 'k.label']);
        if ($request->orderAttribute) {
            $order = $request->orderAttribute;
            if (sizeof($order) > 2) $query->orderBy($order['label'], $order['value']);
            else {
                $query->orderBy('k.category_id', 'asc')
                    ->orderBy('k.sector_id', 'asc')
                    ->orderBy('k.subsector_id', 'asc');
            }
        } else {
            $query->orderBy('k.category_id', 'asc')
                ->orderBy('k.sector_id', 'asc')
                ->orderBy('k.subsector_id', 'asc');
        }
        if ($request->ArrayFilter) {
            $filter = $request->ArrayFilter;
            if (!empty($filter['label'])) {
                $query->where('k.label', 'like', '%' .  $filter['label'] . '%');
            }
            if (!empty($filter['subsector_id'])) {
                $query->where('k.subsector_id', '=',  $filter['subsector_id']);
            }
        }
        $countData = $dataToCounted->count();
        $ihd = $query->paginate($paginated, ['*'], 'page', $currentPage);
        foreach ($ihd as $key => $value) {
            # code...
            $value->number = $number;
            $number++;
        }
        if ($request->paginated) {
            return response()->json([
                'ihd' => $ihd,
                'countData' => $countData
            ]);
        }
        $komoditas = Komoditas::select(['id as value', 'label as label']);
        return Inertia::render('LK/IH/DasarIdx', [
            'ihd' => $ihd,
            'countData' => $countData,
            'komoditas' => $komoditas,
        ]);
    }
}
