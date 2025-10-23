<?php

namespace App\Http\Controllers\LK;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Komoditas;
use App\Models\Subsector;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KomoditasController extends Controller
{
    //
    public function index(Request $request)
    {
        if ($request->paginated) $paginated = $request->paginated;
        else $paginated = 10;
        if ($request->currentPage) $currentPage = $request->currentPage;
        else $currentPage = 1;
        $number = 1;

        $query = Komoditas::query();
        $dataToCounted = $query->join('subsectors as s', 's.id', '=' . 'master_komoditas.subsector_id')
            ->join('users as u', 'u.id', '=', 'master_komoditas.edited_by')
            ->select([
                'komoditas.*',
                's.label as subsector_label',
                'u.name as username',
                'master_komoditas.updated_at as updated_time'
            ]);
        if ($request->orderAttribute) {
            $order = $request->orderAttribute;
            if (sizeof($order) > 2) $query->orderBy($order['label'], $order['value']);
            else {
                $query->orderBy('category_id', 'asc')
                    ->orderBy('sector_id', 'asc')
                    ->orderBy('subsector_id', 'asc');
            }
        } else {
            $query->orderBy('category_id', 'asc')
                ->orderBy('sector_id', 'asc')
                ->orderBy('subsector_id', 'asc');
        }
        if ($request->ArrayFilter) {
            $filter = $request->ArrayFilter;
            if (!empty($filter['label'])) {
                $query->where('label', 'like', '%' .  $filter['label'] . '%');
            }
            if (!empty($filter['code'])) {
                $query->where('code', 'like', '%' . $filter['code'] . '%');
            }
            if (!empty($filter['type'])) {
                $query->where('type', 'like', '%' . $filter['type'] . '%');
            }
            if (!empty($filter['satuan'])) {
                $query->where('satuan', 'like', '%' . $filter['satuan'] . '%');
            }
        }

        $countData = $dataToCounted->count();
        $komoditas = $query->paginate($paginated, ['*'], 'page', $currentPage);
        foreach ($komoditas as $key => $value) {
            # code...
            $value->number = $number;
            $number++;
        }
        if ($request->paginated) {
            return response()->json([
                'sekunder' => $komoditas,
                'countData' => $countData
            ]);
        }
        $subsector = Subsector::select(['id', 'name'])->get();

        return Inertia::render('LK/Komoditas/Index', [
            'komoditas' => $komoditas,
            'countData' => $countData,
            'subsector' => $subsector
        ]);
    }
}
