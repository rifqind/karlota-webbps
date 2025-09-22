<?php

namespace App\Http\Controllers;

use App\Models\Datacontent;
use App\Models\Produsen;
use App\Models\Row;
use App\Models\RowOrder;
use App\Models\Sekunder;
use App\Models\StatusSekunder;
use App\Models\Variabel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MasterController extends Controller
{
    //
    public function RowIndex(Request $request)
    {
        if ($request->paginated) $paginated = $request->paginated;
        else $paginated = 10;
        if ($request->currentPage) $currentPage = $request->currentPage;
        else $currentPage = 1;

        $query = Row::query();
        $number = 1;
        $dataToCounted = $query->select(['rows.*']);
        if ($request->orderAttribute) {
            $order = $request->orderAttribute;
            if (sizeof($order) > 2) $query->orderBy($order['label'], $order['value']);
            else $query->orderBy('label');
        } else $query->orderBy('label');
        if ($request->ArrayFilter) {
            $filter = $request->ArrayFilter;
            if (!empty($filter['label'])) {
                $query->where('label', 'like', '%' .  $filter['label'] . '%');
            }
        }
        $countData = $dataToCounted->count();
        $rows = $query->paginate($paginated, ['*'], 'page', $currentPage);
        foreach ($rows as $din) {
            $din->number = $number;
            $number++;
        }
        if ($request->paginated) {
            return response()->json([
                'row' => $rows,
                'countData' => $countData,
            ]);
        }
        return Inertia::render('Master/Row', [
            'row' => $rows,
            'countData' => $countData,
        ]);
    }

    public function RowStore(Request $request)
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:100'],
        ]);
        $notification = [];
        try {
            //code...
            DB::beginTransaction();
            if ($request->id) {
                $request->validate(['label' => [Rule::unique('rows', 'label')->ignore($request->id)]]);
                $updated_rows = Row::findOrFail($request->id);
                $updated_rows->update($validated);
                $message = ['type' => 'success', 'message' => 'Berhasil mengedit Row ini'];
            } else {
                $request->validate(['label' => [Rule::unique('rows', 'label')]]);
                $new_rows = Row::create([
                    'label' => $validated['label'],
                ]);
                $message = ['type' => 'success', 'message' => 'Berhasil menambahkan Row baru'];
            }
            array_push($notification, $message);
            DB::commit();
            return redirect()->route('master.rows.index')->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Ada kesalahan ketika melakukan perubahan di Row',
                'error' => $th->getMessage()
            ];
            array_push($notification, $message);
            return redirect()->route('master.rows.index')->with('notification', $notification);
        }
    }

    public function RowFetch(String $id)
    {
        $data = Row::find($id);
        return response()->json(['data' => $data]);
    }

    public function RowDestroy(String $id)
    {
        $notification = [];
        try {
            //code...
            DB::beginTransaction();
            $data = Row::findOrFail($id);
            $data->delete();
            $message = [
                'type' => 'message',
                'message' => 'Berhasil menghapus Row tersebut'
            ];
            array_push($notification, $message);
            DB::commit();
            return redirect()->route('master.rows.index')->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Ada kesalahan ketika menghapus Row tersebut',
                'error' => $th->getMessage()
            ];
            return redirect()->route('master.rows.index')->with('notification', $notification);
        }
    }

    public function SekunderIndex(Request $request)
    {
        if ($request->paginated) $paginated = $request->paginated;
        else $paginated = 10;
        if ($request->currentPage) $currentPage = $request->currentPage;
        else $currentPage = 1;
        $number = 1;
        $query = Sekunder::query();
        $dataToCounted = $query
            ->join('produsen as p', 'p.id', '=', 'sekunder.produsen_id')
            // ->join('status_sekunder as ss', 'ss.status_id', '=', 'sekunder.id')
            ->join('users as u', 'u.id', '=', 'sekunder.created_by')
            ->select([
                'sekunder.id as id',
                'sekunder.label as label_data',
                'p.nama as nama_dinas',
                'u.name as username',
                'sekunder.created_at as created_time'
            ]);

        if ($request->orderAttribute) {
            $order = $request->orderAttribute;
            if (sizeof($order) > 2) $query->orderBy($order['label'], $order['value']);
            else $query->orderBy('sekunder.created_at', 'desc')->orderBy('p.nama', 'asc');
        } else $query->orderBy('sekunder.created_at', 'desc')->orderBy('p.nama', 'asc');
        if ($request->ArrayFilter) {
            $filter = $request->ArrayFilter;
            if (!empty($filter['label_data'])) {
                $query->where('sekunder.label', 'like', '%' .  $filter['label_data'] . '%');
            }
            if (!empty($filter['nama_dinas'])) {
                $query->where('p.nama', 'like', '%' . $filter['nama_dinas'] . '%');
            }
            if (!empty($filter['created_at'])) {
                $query->where(DB::raw("CONCAT(users.name, ' - ', sekunder.created_at)"), 'like', '%' . $filter['updated_at'] . '%');
            }
            if (!empty($filter['tahun'])) {
                $target = StatusSekunder::where('tahun', 'like', '%' . $filter['tahun'] . '%')->pluck('sekunder_id')->unique();
                $query->whereIn('sekunder.id', $target);
            }
            //row_label
            if (!empty($filter['row_label'])) {
                $status_target = Datacontent::join('rows as r', 'r.id', '=', 'datacontent.row_id')
                    ->where('r.label', 'like', '%' . $filter['row_label'] . '%')->pluck('datacontent.status_id')->unique();
                $target = StatusSekunder::whereIn('sekunder_id', $status_target)->pluck('sekunder.id')->unique();
                $query->whereIn('sekunder.id', $target);
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
            $status_set = StatusSekunder::where('sekunder_id', $s->id)->orderBy('tahun', 'desc')->get();
            $status_id = $status_set->pluck('id')->toArray();
            $status_tahun = $status_set->pluck('tahun');
            if (sizeof($status_id) > 0) {
                $check_data = Datacontent::whereIn('status_id', $status_id)->get();
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
                'id' => $s->id,
                'label_data' => $s->label_data,
                'nama_dinas' => $s->nama_dinas,
                'username' => $s->username,
                'created_time' => $s->created_time,
                'rows' => $rows,
                'tahun' => $status_tahun
            ]);
        }
        if ($request->paginated) {
            return response()->json([
                'sekunder' => $sekunder_object,
                'countData' => $countData
            ]);
        }
        return Inertia::render('Master/Sekunder', [
            'sekunder' => $sekunder_object,
            'countData' => $countData
        ]);
    }

    public function SekunderUpdate(Request $request, String $id = null)
    {
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'datas.id' => ['required'],
                'datas.label' => ['required', 'string', 'max:255'],
                'datas.tahun.*' => ['required', 'date_format:Y'],
                'datas.produsen_id' => ['required', 'integer'],
                'rows.selected.*' => ['required', 'integer'],
                'order.*' => ['sometimes', 'nullable']
            ]);
            $notification = [];
            try {
                //code...
                DB::beginTransaction();
                $status_list = StatusSekunder::where('sekunder_id', $validated['datas']['id'])
                    ->whereIn('tahun', $validated['datas']['tahun'])
                    ->pluck('id');
                $current_row = Datacontent::whereIn('status_id', $status_list)
                    ->pluck('row_id')
                    ->unique()
                    ->toArray();
                if (!$request->force) {
                    $diff_row = array_diff($current_row, $validated['rows']['selected']);
                    if (!empty($diff_row)) {
                        DB::rollBack();
                        return back()->withErrors([
                            'rows.selected' => 'Ada baris yang berbeda dengan data yang sudah ada.'
                        ]);
                    }
                }
                $update_data = Sekunder::where('id', $validated['datas']['id'])
                    ->update([
                        'label' => $validated['datas']['label'],
                        'produsen_id' => $validated['datas']['produsen_id'],
                        'created_by' => Auth::user()->id
                    ]);
                foreach ($validated['datas']['tahun'] as $key => $t) {
                    $new_status = StatusSekunder::updateOrCreate([
                        'sekunder_id' => $validated['datas']['id'],
                        'tahun' => $t,
                    ], [
                        'status' => 1,
                        'updated_by' => Auth::user()->id,
                    ]);
                    $new_status->touch();
                    foreach ([1, 2, 3, 4] as $tw) {
                        foreach ($validated['rows']['selected'] as $row) {
                            Datacontent::updateOrCreate(
                                [
                                    'status_id' => $new_status->id,
                                    'row_id'    => $row,
                                    'triwulan'  => $tw,
                                ],
                                []
                            );
                        }
                    }
                }
                $data_not_this = Datacontent::whereIn('status_id', $status_list)
                    ->whereNotIn('row_id', $validated['rows']['selected'])
                    ->delete();
                $message = ['type' => 'success', 'message' => 'Berhasil update data'];
                array_push($notification, $message);
                DB::commit();
                return redirect()->route('master.sekunder.update', ['id' => $validated['datas']['id']])
                    ->with('notification', $notification);
            } catch (\Throwable $th) {
                //throw $th;
                DB::rollBack();
                $message = [
                    'type' => 'error',
                    'message' => 'Ada kesalahan ketika update data',
                    'error' => $th->getMessage()
                ];
                array_push($notification, $message);
                return redirect()->route('master.sekunder.update', ['id' => $validated['datas']['id']])
                    ->with('notification', $notification);
            }
        }
        $produsen = Produsen::orderBy('nama', 'asc')
            ->select(['id as value', 'nama as label'])
            ->get();
        $rows = Row::orderBy('label', 'asc')
            ->select(['id as value', 'label'])
            ->get();
        $sekunder = Sekunder::find($id);
        $status = StatusSekunder::where('sekunder_id', $id)->get();
        $status_tahun = $status->pluck('tahun');
        $status_id = $status->pluck('id');
        $sekunder_row = Datacontent::whereIn('status_id', $status_id)->pluck('row_id')->unique()->toArray();
        $sekunder_row = array_values($sekunder_row);
        $orders = RowOrder::where('sekunder_id', $id)->first('orders');
        $exploded_orders = explode(',', $orders->orders);
        return Inertia::render('Sekunder/Update', [
            'produsen' => $produsen,
            'rows' => $rows,
            'sekunder' => $sekunder,
            'tahun' => $status_tahun,
            'sekunder_row' => $sekunder_row,
            'order' => $exploded_orders
        ]);
    }

    public function SekunderAddYear(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required'],
            'tahun.*' => ['required', 'date_format:Y', Rule::unique('status_sekunder', 'tahun')]
        ], ['tahun.*.unique' => 'Tahun tersebut sudah ada']);
        $notification = [];
        try {
            //code...
            DB::beginTransaction();
            $latest_status = StatusSekunder::where('sekunder_id', $request->id)
                ->orderBy('updated_at', 'desc')
                ->first();
            $rows = Datacontent::where('status_id', $latest_status->id)->pluck('row_id')->unique()->toArray();
            foreach ($validated['tahun'] as $key => $value) {
                # code...
                $new_data = StatusSekunder::firstOrCreate([
                    'sekunder_id' => $validated['id'],
                    'tahun' => $value
                ], ['status' => 1, 'updated_by' => Auth::user()->id]);
                foreach ([1, 2, 3, 4] as $key => $tw) {
                    # code...
                    foreach ($rows as $key => $r) {
                        # code...
                        $new_data_content = Datacontent::updateOrCreate([
                            'status_id' => $new_data->id,
                            'row_id' => $r,
                            'triwulan' => $tw
                        ], []);
                    }
                }
            }
            $message = ['type' => 'success', 'message' => 'Berhasil menambahkan tahun baru'];
            array_push($notification, $message);
            DB::commit();
            return redirect()->route('sekunder.index')->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Ada kesalahan ketika menambah tahun',
                'error' => $th->getMessage()
            ];
            array_push($notification, $message);
            return redirect()->route('sekunder.index')->with('notification', $notification);
        }
    }

    public function SekunderDestroy(String $id)
    {
        $notification = [];
        try {
            //code...
            DB::beginTransaction();
            $status_sekunder = StatusSekunder::where('sekunder_id', $id)->get();
            if (sizeof($status_sekunder) > 0) {
                $message = ['type' => 'error', 'message' => 'Masih ada data tahunan yang belum dihapus'];
                array_push($notification, $message);
                // return redirect()->route('master.sekunder.index')->with('notification', $notification);
            } else {
                $deleted_order = RowOrder::where('sekunder_id', $id)->delete();
                $deleted = Sekunder::where('id', $id)->delete();
                $message = ['type' => 'success', 'message' => 'Berhasil hapus data'];
                array_push($notification, $message);
            }
            DB::commit();
            return redirect()->route('master.sekunder.index')->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Ada kesalahan ketika hapus data',
                'error' => $th->getMessage()
            ];
            array_push($notification, $message);
            return redirect()->route('master.sekunder.index')->with('notification', $notification);
        }
    }

    // public function VariabelIndex(Request $request)
    // {
    //     if ($request->paginated) $paginated = $request->paginated;
    //     else $paginated = 10;
    //     if ($request->currentPage) $currentPage = $request->currentPage;
    //     else $currentPage = 1;

    //     $query = Variabel::query();
    //     $number = 1;
    //     $dataToCounted = $query->select(['variabel.*']);
    //     if ($request->orderAttribute) {
    //         $order = $request->orderAttribute;
    //         if (sizeof($order) > 2) $query->orderBy($order['label'], $order['value']);
    //         else $query->orderBy('label');
    //     } else $query->orderBy('label');
    //     if ($request->ArrayFilter) {
    //         $filter = $request->ArrayFilter;
    //         if (!empty($filter['label'])) {
    //             $query->where('label', 'like', '%' .  $filter['label'] . '%');
    //         }
    //     }
    //     $countData = $dataToCounted->count();
    //     $rows = $query->paginate($paginated, ['*'], 'page', $currentPage);
    //     foreach ($rows as $din) {
    //         $din->number = $number;
    //         $number++;
    //     }
    //     if ($request->paginated) {
    //         return response()->json([
    //             'row' => $rows,
    //             'countData' => $countData,
    //         ]);
    //     }
    //     return Inertia::render('Master/Row', [
    //         'row' => $rows,
    //         'countData' => $countData,
    //     ]);
    // }

    // public function RowStore(Request $request) {
    //     $validated = $request->validate([
    //         'label' => ['required', 'string', 'max:100'],
    //     ]);
    //     $notification = [];
    //     try {
    //         //code...
    //         DB::beginTransaction();
    //         if ($request->id) {
    //             $request->validate(['label' => [Rule::unique('rows', 'label')->ignore($request->id)]]);
    //             $updated_rows = Row::findOrFail($request->id);
    //             $updated_rows->update($validated);
    //             $message = ['type' => 'success', 'message' => 'Berhasil mengedit Row ini'];
    //         } else {
    //             $request->validate(['label' => [Rule::unique('rows', 'label')]]);
    //             $new_rows = Row::create([
    //                 'label' => $validated['label'],
    //             ]);
    //             $message = ['type' => 'success', 'message' => 'Berhasil menambahkan Row baru'];
    //         }
    //         array_push($notification, $message);
    //         DB::commit();
    //         return redirect()->route('master.rows.index')->with('notification', $notification);
    //     } catch (\Throwable $th) {
    //         //throw $th;
    //         DB::rollBack();
    //         $message = [
    //             'type' => 'error',
    //             'message' => 'Ada kesalahan ketika melakukan perubahan di Row',
    //             'error' => $th->getMessage()
    //         ];
    //         array_push($notification, $message);
    //         return redirect()->route('master.rows.index')->with('notification', $notification);
    //     }
    // }

    // public function RowFetch(String $id)
    // {
    //     $data = Row::find($id);
    //     return response()->json(['data' => $data]);
    // }

    // public function RowDestroy(String $id) {
    //     $notification = [];
    //     try {
    //         //code...
    //         DB::beginTransaction();
    //         $data = Row::findOrFail($id);
    //         $data->delete();
    //         $message = [
    //             'type' => 'message',
    //             'message' => 'Berhasil menghapus Row tersebut'
    //         ];
    //         array_push($notification, $message);
    //         DB::commit();
    //         return redirect()->route('master.rows.index')->with('notification', $notification);
    //     } catch (\Throwable $th) {
    //         //throw $th;
    //         DB::rollBack();
    //         $message = [
    //             'type' => 'error',
    //             'message' => 'Ada kesalahan ketika menghapus Row tersebut',
    //             'error' => $th->getMessage()
    //         ];
    //         return redirect()->route('master.rows.index')->with('notification', $notification);
    //     }
    // }
}
