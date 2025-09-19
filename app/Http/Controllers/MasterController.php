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
            $status_set = StatusSekunder::where('sekunder_id', $s->id)->get();
            $status_id = $status_set->first();
            $status_tahun = $status_set->pluck('tahun');
            if ($status_id) {
                $check_data = Datacontent::where('status_id', $status_id->id)->get();
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

    public function SekunderUpdate(String $id)
    {
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
        $sekunder_row = Datacontent::whereIn('status_id', $status_id)->pluck('row_id')->unique();
        return Inertia::render('Sekunder/Update', [
            'produsen' => $produsen,
            'rows' => $rows,
            'sekunder' => $sekunder,
            'tahun' => $status_tahun,
            'sekunder_row' => $sekunder_row,
        ]);
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
