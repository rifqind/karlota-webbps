<?php

namespace App\Http\Controllers;

use App\Models\Produsen;
use App\Models\Row;
use App\Models\StatusSekunder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SekunderController extends Controller
{
    //
    public function index(Request $request)
    {
        if ($request->paginated) $paginated = $request->paginated;
        else $paginated = 10;
        if ($request->currentPage) $currentPage = $request->currentPage;
        else $currentPage = 1;
        $number = 1;
        $query = StatusSekunder::query();
        $dataToCounted = $query
            ->join('sekunder as s', 's.id', '=', 'status_sekunder.sekunder_id')
            ->join('produsen as p', 'p.id', '=', 's.produsen_id')
            ->join('users as u', 'u.id', '=', 'status_sekunder.updated_by')
            ->join('status as st', 'st.id', '=', 'status_sekunder.status')
            ->select([
                's.label as label_data',
                'p.nama as nama_dinas',
                'status_sekunder.tahun',
                'st.label as label_status',
                'u.username as username',
                'status_sekunder.updated_at as updated_time'
            ]);

        if ($request->orderAttribute) {
            $order = $request->orderAttribute;
            if (sizeof($order) > 2) $query->orderBy($order['label'], $order['value']);
            else $query->orderBy('status_sekunder.updated_at', 'desc')->orderBy('status', 'asc');
        } else $query->orderBy('status_sekunder.updated_at', 'desc')->orderBy('status', 'asc');
        if ($request->ArrayFilter) {
            $filter = $request->ArrayFilter;
            if (!empty($filter['label_data'])) {
                $query->where('s.label', 'like', '%' .  $filter['label_data'] . '%');
            }
            if (!empty($filter['nama_dinas'])) {
                $query->where('p.nama', 'like', '%' . $filter['nama_dinas'] . '%');
            }
            if (!empty($filter['tahun'])) {
                $query->where('status_sekunder.tahun', 'like', '%' . $filter['tahun'] . '%');
            }
            if (!empty($filter['status'])) {
                $query->where('st.label', 'like', '%' . $filter['status'] . '%');
            }
            if (!empty($filter['updated_at'])) {
                // $query->join('users', 'statustables.edited_by', '=', 'users.id');
                $query->where(DB::raw("CONCAT(users.username, ' - ', status_sekunder.updated_at)"), 'like', '%' . $filter['updated_at'] . '%');
            }
        }
        $countData = $dataToCounted->count();
        $sekunder = $query->paginate($paginated, ['*'], 'page', $currentPage);
        foreach ($sekunder as $key => $value) {
            # code...
            $value->number = $number;
            $number++;
        }
        if ($request->paginated) {
            return response()->json([
                'sekunder' => $sekunder,
                'countData' => $countData
            ]);
        }
        return Inertia::render('Sekunder/Index', [
            'sekunder' => $sekunder,
            'countData' => $countData
        ]);
    }

    public function create()
    {
        $produsen = Produsen::orderBy('nama', 'asc')
            ->select(['id as value', 'nama as label'])
            ->get();
        $rows = Row::orderBy('label', 'asc')
            ->select(['id as value', 'label'])
            ->get();
        return Inertia::render('Sekunder/Create', [
            'produsen' => $produsen,
            'rows' => $rows,
        ]);
    }
}
