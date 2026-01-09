<?php

namespace App\Http\Controllers\LK;

use App\Http\Controllers\Controller;
use App\Models\LK\Komoditas;
use App\Models\LK\MasterSut;
use App\Models\LK\RasioBa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $dataToCounted = $query;
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

    public function storeMasterSut(Request $request)
    {
        $validated = $request->validate([
            'label' => ['required', 'unique:master_sut_irio,label']
        ]);
        $notification = [];
        try {
            //code...
            DB::beginTransaction();
            $inserted = MasterSut::create($validated);
            $notification[] = [
                'type' => 'success',
                'message' => 'Berhasil menambahkan SUT terbaru'
            ];
            DB::commit();
            return back()->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $notification[] = [
                'type' => 'error',
                'message' => 'Gagal menambahkan SUT terbaru: ' . $th->getMessage()
            ];
            return back()->withErrors(['notification' => $notification]);
        }
    }

    public function fetchSut($id)
    {
        $target = MasterSut::findOrFail($id);
        return response()->json($target);
    }

    public function updateMasterSut(Request $request)
    {
        $notification = [];
        $validated = $request->validate([
            'id' => ['required', 'exists:master_sut_irio,id'],
            'label' => ['required', 'unique:master_sut_irio,label,' . $request->id]
        ]);
        try {
            //code...
            DB::beginTransaction();
            $target = MasterSut::findOrFail($validated['id']);
            $target->update([
                'label' => $validated['label']
            ]);
            $notification[] = [
                'type' => 'success',
                'message' => 'Berhasil memperbarui data SUT'
            ];
            DB::commit();
            return back()->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $notification[] = [
                'type' => 'error',
                'message' => 'Gagal memperbarui data SUT: ' . $th->getMessage()
            ];
            return back()->withErrors(['notification' => $notification]);
        }
    }

    public function destroyMasterSut($id)
    {
        $notification = [];
        try {
            //code...
            DB::beginTransaction();
            $target = MasterSut::findOrFail($id);
            $target->delete();
            $notification[] = [
                'type' => 'success',
                'message' => 'Berhasil menghapus data SUT'
            ];
            DB::commit();
            return back()->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $notification[] = [
                'type' => 'error',
                'message' => 'Gagal menghapus data SUT: ' . $th->getMessage()
            ];
            return back()->withErrors(['notification' => $notification]);
        }
    }

    public function index(Request $request)
    {
        if ($request->paginated) $paginated = $request->paginated;
        else $paginated = 10;
        if ($request->currentPage) $currentPage = $request->currentPage;
        else $currentPage = 1;

        $number = 1;
        $query = Komoditas::query();
        $dataToCounted = $query->leftJoin('master_rasio_ntb as mrn', 'mrn.komoditas_id', '=', 'master_komoditas.id')
            ->leftJoin('master_sut_irio as msi', 'msi.id', '=', 'mrn.sut_id')
            ->select([
                'master_komoditas.id as komoditas_id',
                'master_komoditas.label as komoditas_label',
                'master_komoditas.code as komoditas_code',
                'msi.label as sut_label',
                'mrn.rasio_ntb as rasio_ntb',
                'mrn.id as mrn_id'
            ]);
        if ($request->orderAttribute) {
            $order = $request->orderAttribute;
            if (sizeof($order) > 2) $query->orderBy($order['label'], $order['value']);
            else {
                $query->orderBy('category_id', 'asc')
                    ->orderBy('sector_id', 'asc')
                    ->orderBy('subsector_id', 'asc')
                    ->orderBy('code', 'asc');
            }
        } else {
            $query->orderBy('category_id', 'asc')
                ->orderBy('sector_id', 'asc')
                ->orderBy('subsector_id', 'asc')
                ->orderBy('code', 'asc');
        }

        if ($request->ArrayFilter) {
            $filter = $request->ArrayFilter;
            if (!empty($filter[['komoditas_label']])) $query->where('master_komoditas.label', 'like', '%' . $filter['komoditas_label'] . '%');
            if (!empty($filter[['sut_label']])) $query->where('msi.label', 'like', '%' . $filter['sut_label'] . '%');
        }
        $countData = $dataToCounted->count();
        $rasioba = $query->paginate($paginated, ['*'], 'page', $currentPage);
        foreach ($rasioba as $s) {
            $s->number = $number;
            $number++;
        }
        if ($request->paginated) {
            return response()->json(['rasioba' => $rasioba, 'countData' => $countData]);
        }
        $sut = MasterSut::select(['id as value', 'label'])->get();
        return Inertia::render('LK/RBA/RasioBa', [
            'rasioba' => $rasioba,
            'countData' => $countData,
            'sut' => $sut
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'komoditas_id' => ['required', 'exists:master_komoditas,id'],
            'sut_id' => ['required', 'exists:master_sut_irio,id'],
            'rasio_ntb' => ['required', 'numeric']
        ]);
        $notification = [];
        try {
            //code...
            DB::beginTransaction();
            $inserted = RasioBa::create($validated);
            $notification[] = [
                'type' => 'success',
                'message' => 'Berhasil menambahkan data rasio biaya antara'
            ];
            DB::commit();
            dd($validated);
            return back()->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $notification[] = [
                'type' => 'error',
                'message' => 'Gagal menambahkan data : ' . $th->getMessage()
            ];
            return back()->withErrors(['notification' => $notification]);
        }
    }

    public function update(Request $request)
    {
        $notification = [];
        $validated = $request->validate([
            'id' => ['required', 'exists:master_rasio_ntb,id'],
            'rasio_ntb' => ['required', 'numeric']
        ]);
        try {
            //code...
            DB::beginTransaction();
            $target = RasioBa::findOrFail($validated['id']);
            $target->update([
                'rasio_ntb' => $validated['rasio_ntb']
            ]);
            $notification[] = [
                'type' => 'success',
                'message' => 'Berhasil memperbarui data rasio biaya antara'
            ];
            DB::commit();
            return back()->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Gagal memperbarui data rasio biaya antara: ' . $th->getMessage()
            ];
            return back()->withErrors(['notification' => $notification]);
        }
    }

    public function destroy($id)
    {
        $notification = [];
        try {
            //code...
            DB::beginTransaction();
            $target = RasioBa::findOrFail($id);
            $target->delete();
            $notification[] = [
                'type' => 'success',
                'message' => 'Berhasil menghapus data rasio biaya antara'
            ];
            DB::commit();
            return back()->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Gagal menghapus data rasio biaya antara: ' . $th->getMessage()
            ];
            return back()->withErrors(['notification' => $notification]);
        }
    }
}
