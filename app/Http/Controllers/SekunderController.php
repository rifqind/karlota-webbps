<?php

namespace App\Http\Controllers;

use App\Models\Datacontent;
use App\Models\Produsen;
use App\Models\Region;
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
                'sekunder_id',
                's.label as label_data',
                'p.id as produsen_id',
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
            //row_label
            if (!empty($filter['row_label'])) {
                $target = Datacontent::join('rows as r', 'r.id', '=', 'datacontent.row_id')
                    ->where('r.label', 'like', '%' . $filter['row_label'] . '%')->pluck('datacontent.status_id')->unique();
                $query->whereIn('status_sekunder.id', $target);
            }
        }
        $countData = $dataToCounted->count();
        $sekunder = $query->paginate($paginated, ['*'], 'page', $currentPage);
        foreach ($sekunder as $key => $value) {
            # code...
            $value->number = $number;
            $number++;
        }
        $sekunder_object = [];
        foreach ($sekunder as $key => $s) {
            # code...
            $check_data = Datacontent::where('status_id', $s->id)->get();
            if (sizeof($check_data) > 0) {
                $row_id = $check_data->pluck('row_id')->unique();
                $rows = Row::whereIn('id', $row_id)->get();
            } else {
                $rows = [
                    [
                        'id' => 'Tidak ada data',
                        'label' => 'Tidak ada data'
                    ]
                ];
            }
            array_push($sekunder_object, [
                'number' => $s->number,
                'sekunder_id' => $s->sekunder_id,
                'id' => $s->id,
                'label_data' => $s->label_data,
                'nama_dinas' => $s->nama_dinas,
                'produsen_id' => $s->produsen_id,
                'tahun' => $s->tahun,
                'label_status' => $s->label_status,
                'status_id' => $s->status_id,
                'username' => $s->username,
                'updated_time' => $s->updated_time,
                'rows' => $rows
            ]);
        }

        if ($request->paginated) {
            return response()->json([
                'sekunder' => $sekunder_object,
                'countData' => $countData
            ]);
        }
        return Inertia::render('Sekunder/Index', [
            'sekunder' => $sekunder_object,
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
        //cek dengan data
        $cek_data = Sekunder::where('label', $validated['datas']['label'])
            ->where('produsen_id', $validated['datas']['produsen_id'])
            ->first();
        $notification = [];
        if ($cek_data) {
            $notification[] = [
                'type' => 'error',
                'message' => 'Data dengan label tersebut sudah ada di dinas yang dipilih',
            ];
            return redirect()->route('sekunder.create')->with('notification', $notification);
        }
        // dd($validated);
        // $orderSame = $validated['rows']['selected'] === $validated['order'];
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
        $status_sekunder_before = StatusSekunder::where('sekunder_id', $sekunder->id)
            ->where('tahun', $status_sekunder->tahun - 1)
            ->first();
        if ($status_sekunder_before) $datacontent_before = Datacontent::where('status_id', $status_sekunder_before->id)->get();
        else $datacontent_before = [];
        return Inertia::render('Sekunder/Entri', [
            'datacontent' => $datacontent,
            'rows' => $rows,
            'datacontent_before' => sizeof($datacontent_before) > 0 ? $datacontent_before : [],
            'status_sekunder' => $status_sekunder,
            'sekunder' => $sekunder,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'datacontent.*' => ['required'],
        ]);
        $notification = [];
        try {
            //code...
            DB::beginTransaction();
            $status = StatusSekunder::where('id', $request->status_id)
                ->update([
                    'status' => 2,
                    'updated_by' => Auth::user()->id
                ]);
            foreach ($validated['datacontent'] as $key => $value) {
                # code..
                $updated_datacontent = Datacontent::where('id', $value['id'])
                    ->update(['data' => $value['data']]);
            }
            $message = ['type' => 'success', 'message' => 'Berhasil tersimpan!'];
            array_push($notification, $message);
            DB::commit();
            return redirect()->route('sekunder.entri', ['id' => $request->status_id])->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Ada kesalahan ketika simpan data',
                'error' => $th->getMessage()
            ];
            array_push($notification, $message);
            return redirect()->route('sekunder.entri', ['id' => $request->status_id])->with('notification', $notification);
        }
    }

    public function dataByDinas(Request $request)
    {
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
            if (!empty($filter['sekunder_list'])) {
                $sekunder_id = Sekunder::where('label', 'like', '%' . $filter['sekunder_list'] . '%')
                    ->pluck('produsen_id')->toArray();
                $query->whereIn('id', $sekunder_id);
            }
        }
        $countData = $dataToCounted->count();
        $produsen = $query->paginate($paginated, ['*'], 'page', $currentPage);
        $produsen_object = [];
        $sekunder_number = 0;
        foreach ($produsen as $key => $p) {
            # code...
            $sekunders = Sekunder::where('produsen_id', $p->id)->pluck('label')->toArray();
            $sekunder_number = sizeof($sekunders) + $sekunder_number;
            $produsen_object[] = [
                'number'=> $number++,
                'id' => $p->id,
                'produsen_label' => $p->nama,
                'region_name' => $p->region_name,
                'sekunder_list' => $sekunders
            ];
        }
        if ($request->paginated) {
            return response()->json([
                'produsen' => $produsen_object,
                'sekunder_number' => $sekunder_number,
                'countData' => $countData
            ]);
        }
        return Inertia::render('Sekunder/ByDinas', [
            'produsen' => $produsen_object,
            'sekunder_number' => $sekunder_number,
            'countData' => $countData
        ]);
    }


    public function destroy(String $id)
    {
        $notification = [];
        try {
            //code...
            DB::beginTransaction();
            $deleted_datacontent = Datacontent::where('status_id', $id)->delete();
            $deleted_status = StatusSekunder::where('id', $id)->delete();
            $message = ['type' => 'success', 'message' => 'Berhasil hapus data'];
            array_push($notification, $message);
            DB::commit();
            return redirect()->route('sekunder.index')->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Ada kesalahan ketika hapus data',
                'error' => $th->getMessage()
            ];
            array_push($notification, $message);
            return redirect()->route('sekunder.index')->with('notification', $notification);
        }
    }
}
