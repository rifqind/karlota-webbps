<?php

namespace App\Http\Controllers;

use App\Models\Datacontent;
use App\Models\Produsen;
use App\Models\Row;
use App\Models\RowOrder;
use App\Models\Sekunder;
use App\Models\StatusSekunder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                'status_sekunder.id as id',
                's.label as label_data',
                'p.nama as nama_dinas',
                'status_sekunder.tahun',
                'st.label as label_status',
                'st.id as status_id',
                'u.name as username',
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
                $query->where(DB::raw("CONCAT(users.name, ' - ', status_sekunder.updated_at)"), 'like', '%' . $filter['updated_at'] . '%');
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'datas.label' => ['required', 'string', 'max:255'],
            'datas.tahun.*' => ['required', 'date_format:Y'],
            'datas.produsen_id' => ['required', 'integer'],
            'rows.selected.*' => ['required', 'integer'],
            'order.*' => ['sometimes', 'nullable']
        ]);
        // dd($validated);
        // $orderSame = $validated['rows']['selected'] === $validated['order'];
        $notification = [];
        try {
            //code...
            DB::beginTransaction();
            $new_data = Sekunder::create([
                'label' => $validated['datas']['label'],
                'produsen_id' => $validated['datas']['produsen_id'],
                'created_by' => Auth::user()->id
            ]);
            foreach ($validated['datas']['tahun'] as $key => $t) {
                $new_status = StatusSekunder::create([
                    'sekunder_id' => $new_data->id,
                    'tahun' => $t,
                    'status' => 1,
                    'updated_by' => Auth::user()->id,
                ]);
                $triwulan = [1, 2, 3, 4];
                foreach ($triwulan as $key => $tw) {
                    foreach ($validated['rows']['selected'] as $key => $rows) {
                        # code...
                        $new_data_content = Datacontent::create([
                            'status_id' => $new_status->id,
                            'row_id' => $rows,
                            'triwulan' => $tw
                        ]);
                    }
                }
            }
            // if (!$orderSame) {
            $imploded_order = implode(',', $validated['order']);
            $new_order = RowOrder::create([
                'orders' => $imploded_order,
                'sekunder_id' => $new_data->id,
            ]);
            // }
            $message = ['type' => 'success', 'message' => 'Berhasil menambahkan data baru'];
            array_push($notification, $message);
            DB::commit();
            return redirect()->route('sekunder.create')->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Ada kesalahan ketika menambah data',
                'error' => $th->getMessage()
            ];
            array_push($notification, $message);
            return redirect()->route('sekunder.create')->with('notification', $notification);
        }
    }

    public function entri(String $id)
    {
        $query = Datacontent::query();
        $query->where('status_id', $id);
        $datacontent = $query->get();
        $row_datacontent = $query->pluck('row_id');
        $rows = Row::whereIn('id', $row_datacontent)->distinct()->get();
        $status_sekunder = StatusSekunder::where('status_sekunder.id', $id)
            ->join('status as s', 's.id', '=', 'status_sekunder.status')
            ->select([
                'status_sekunder.*',
                'status_sekunder.updated_at as updated_time',
                's.label as status_label'
            ])->first();
        $sekunder = Sekunder::where('id', $status_sekunder->sekunder_id)
            ->first();
        return Inertia::render('Sekunder/Entri', [
            'datacontent' => $datacontent,
            'rows' => $rows,
            'status_sekunder' => $status_sekunder,
            'sekunder' => $sekunder,
        ]);
    }

    public function update(Request $request) {
        $validated = $request->validate([
            'datacontent.*.data' => ['sometimes', 'nullable', 'integer'],
            'datacontent.*.status_id' => ['string', 'required', 'min:36', 'max:36'],
            'datacontent.*.row_id' => ['required', 'integer'],
            'datacontent.*.triwulan' => ['required', 'integer'],
        ]);
        dd($validated);
    }
}
