<?php

namespace App\Http\Controllers\LK;

use App\Http\Controllers\Controller;
use App\Models\LK\MasterSut;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BiayaAntaraController extends Controller
{
    //
    public function masterSut(Request $request)
    {
        if ($request->paginated) $paginated = $request->paginated;
        else $paginated = 10;
        if ($request->currentPage) $currentPage = $request->currentPage;
        else $currentPage = 1;

        $number = 1;
        $query = MasterSut::query();
        $dataToCounted = $query->get();
        if ($request->orderAttribute) {
            $order = $request->orderAttribute;
            if (sizeof($order) > 2) $query->orderBy($order['label'], $order['value']);
            else $query->orderBy('master_sut_irio.label', 'asc');
        } else $query->orderBy('master_sut_irio.label', 'asc');

        if ($request->ArrayFilter) {
            $filter = $request->ArrayFilter;
            if (!empty($filter[['label']])) $query->where('label', 'like', '%' . $filter['label'] . '%');
        }
        $countData = $dataToCounted->count();
        $sut = $query->paginate($paginated, ['*'], 'page', $currentPage);
        foreach ($sut as $s) {
            $s->number = $number;
            $number++;
        }
        if ($request->paginated) {
            return response()->json(['sut' => $sut, 'countData' => $countData]);
        }
        return Inertia::render('LK/RBA/MasterSut', ['sut' => $sut, 'countData' => $countData]);
    }
}
