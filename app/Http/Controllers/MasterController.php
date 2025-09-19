<?php

namespace App\Http\Controllers;

use App\Models\Row;
use App\Models\Sekunder;
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

    public function DataIndex(Request $request)
    {
        if ($request->paginated) $paginated = $request->paginated;
        else $paginated = 10;
        if ($request->currentPage) $currentPage = $request->currentPage;
        else $currentPage = 1;
        $number = 1;
        $query = Sekunder::query();
        $dataToCounted = $query
            ->join('produsen as p', 'p.id', '=', 'sekunder.produsen_id')
            ->join('status_sekunder as ss', 'ss.status_id', '=', 'sekunder.id')
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
            // if (!empty($filter['tahun'])) {
            //     $query->where('status_sekunder.tahun', 'like', '%' . $filter['tahun'] . '%');
            // }
            if (!empty($filter['created_at'])) {
                $query->where(DB::raw("CONCAT(users.name, ' - ', sekunder.created_at)"), 'like', '%' . $filter['updated_at'] . '%');
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
        return Inertia::render('Master/Sekunder', [
            'sekunder' => $sekunder,
            'countData' => $countData
        ]);
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
