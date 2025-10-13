<?php

namespace App\Http\Controllers;

use App\Models\Produsen;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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

    public function store(Request $request)
    {
        // Rule::unique('produsen', 'nama')
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'region_id' => ['required', 'integer']
        ]);
        $notification = [];
        try {
            //code...
            DB::beginTransaction();
            if ($request->id) {
                $request->validate(['nama' => [Rule::unique('produsen', 'nama')->ignore($request->id)]]);
                $updated_produsen = Produsen::findOrFail($request->id);
                $updated_produsen->update($validated);
                $message = ['type' => 'success', 'message' => 'Berhasil mengedit Dinas ini'];
            } else {
                $request->validate(['nama' => [Rule::unique('produsen', 'nama')]]);
                $new_produsen = Produsen::create([
                    'nama' => $validated['nama'],
                    'region_id' => $validated['region_id']
                ]);
                $message = ['type' => 'success', 'message' => 'Berhasil menambahkan Dinas baru'];
            }
            array_push($notification, $message);
            DB::commit();
            return redirect()->route('produsen.index')->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Ada kesalahan ketika melakukan perubahan di dinas',
                'error' => $th->getMessage()
            ];
            array_push($notification, $message);
            return redirect()->route('produsen.index')->with('notification', $notification);
        }
    }

    public function fetch(String $id)
    {
        $data = Produsen::find($id);
        return response()->json(['data' => $data]);
    }

    public function destroy(String $id)
    {
        $notification = [];
        try {
            //code...
            DB::beginTransaction();
            $data = Produsen::findOrFail($id);
            $data->delete();
            $message = [
                'type' => 'message',
                'message' => 'Berhasil menghapus dinas tersebut'
            ];
            array_push($notification, $message);
            DB::commit();
            return redirect()->route('produsen.index')->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Ada kesalahan ketika menghapus dinas tersebut',
                'error' => $th->getMessage()
            ];
            return redirect()->route('produsen.index')->with('notification', $notification);
        }
    }
}
