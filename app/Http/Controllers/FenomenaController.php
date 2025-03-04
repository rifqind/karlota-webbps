<?php

namespace App\Http\Controllers;

use App\Models\Fenomena;
use App\Models\FenomenaSet;
use App\Models\Region;
use App\Models\Subsector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FenomenaController extends Controller
{
    //
    public function entri()
    {
        $prefix = request()->route()->getPrefix();
        if ($prefix == 'lapus') $type = 'Lapangan Usaha';
        else if ($prefix == 'peng') $type = 'Pengeluaran';
        $regions = Region::getMyRegion();
        $subsectors = Subsector::where('type', $type)
            ->with(['sector.category'])
            ->get();
        return Inertia::render('Fenomena/Entri', [
            'type' => $type,
            'subsectors' => $subsectors,
            'regions' => $regions
        ]);
    }

    public function show(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string'],
            'year' => ['required', 'integer'],
            'quarter' => ['required', 'integer'],
            'regions' => ['required', 'integer'],
        ]);
        $notification = [];
        $fenomena_set = FenomenaSet::where('type', $validated['type'])
            ->where('region_id', $validated['regions'])
            ->where('year', $validated['year'])
            ->where('quarter', $validated['quarter'])
            ->first();
        if ($fenomena_set) {
            $data = Fenomena::where('fenomena_sets', $fenomena_set->id)->get();
            $message = [
                'type' => 'success',
                'message' => 'Mengambil Data Fenomena Periode Ini'
            ];
            array_push($notification, $message);
        } else {
            $fenomena_set = FenomenaSet::create([
                'type' => $validated['type'],
                'region_id' => $validated['regions'],
                'year' => $validated['year'],
                'quarter' => $validated['quarter'],
                'status' => 'Entry',
            ]);
            $data = collect();
            $message = [
                'type' => 'success',
                'message' => 'Fenomena set baru dibuat'
            ];
            array_push($notification, $message);
        }
        return response()->json([
            'data' => $data,
            'fenomena_set' => $fenomena_set,
            'notification' => $notification
        ]);
    }

    public function saveFenomena(Request $request)
    {
        $notification = [];
        $type = $request->type;
        if ($type == 'Lapangan Usaha') $route = 'lapus.';
        else if ($type == 'Pengeluaran') $route = 'peng.';
        try {
            //code...
            DB::beginTransaction();
            $data = $request->validate([
                'dataContents' => ['required', 'array'],
                'dataContents.*.id' => ['sometimes', 'nullable', 'integer', 'exists:fenomenas,id'],
                'dataContents.*.fenomena_sets' => ['sometimes', 'nullable', 'integer'],
                'dataContents.*.category_id' => ['required', 'integer'],
                'dataContents.*.sector_id' => ['sometimes', 'nullable', 'integer'],
                'dataContents.*.subsector_id' => ['sometimes', 'nullable', 'integer'],
                'dataContents.*.qtoq' => ['sometimes', 'string', 'nullable'],
                'dataContents.*.yony' => ['sometimes', 'string', 'nullable'],
                'dataContents.*.implisit' => ['sometimes', 'string', 'nullable'],
            ]);
            foreach ($data['dataContents'] as $key => $value) {
                # code...
                if ($value['id']) {
                    Fenomena::where('id', $value['id'])
                        ->update([
                            'fenomena_sets' => $value['fenomena_sets'],
                            'category_id' => $value['category_id'],
                            'sector_id' => $value['sector_id'],
                            'subsector_id' => $value['subsector_id'],
                            'qtoq' => $value['qtoq'] ?? null,
                            'yony' => $value['yony'] ?? null,
                            'implisit' => $value['implisit'] ?? null,
                        ]);
                } else {
                    if ($value['qtoq'] || $value['yony'] || $value['implisit']) {
                        $this_fenomena = Fenomena::where('fenomena_sets', $request->fenomena_sets)
                            ->where('category_id', $value['category_id'])
                            ->where('sector_id', $value['sector_id'])
                            ->where('subsector_id', $value['subsector_id'])
                            ->first();
                        if ($this_fenomena) {
                            Fenomena::where('category_id', $value['category_id'])
                                ->where('sector_id', $value['sector_id'])
                                ->where('subsector_id', $value['subsector_id'])
                                ->update([
                                    'qtoq' => $value['qtoq'] ?? null,
                                    'yony' => $value['yony'] ?? null,
                                    'implisit' => $value['implisit'] ?? null,
                                ]);
                        } else {
                            Fenomena::create([
                                'fenomena_sets' => $request->fenomena_sets,
                                'category_id' => $value['category_id'],
                                'sector_id' => $value['sector_id'],
                                'subsector_id' => $value['subsector_id'],
                                'qtoq' => $value['qtoq'] ?? null,
                                'yony' => $value['yony'] ?? null,
                                'implisit' => $value['implisit'] ?? null,
                            ]);
                        }
                    }
                }
            }
            $message = [
                'type' => 'success',
                'message' => 'Berhasil Simpan Fenomena'
            ];
            array_push($notification, $message);
            DB::commit();
            return redirect()->route($route . 'entri-fenomena')->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Ada Kesalahan dalam Data yang disimpan',
                'errors' => $th->getMessage()
            ];
            array_push($notification, $message);
            return redirect()->route($route . 'entri-fenomena')->with('notification', $notification);
        }
    }

    public function submitFenomena(Request $request)
    {
        $notification = [];
        $type = $request->type;
        if ($type == 'Lapangan Usaha') $route = 'lapus.';
        else if ($type == 'Pengeluaran') $route = 'peng.';
        try {
            //code...
            DB::beginTransaction();
            $fenomenasets = FenomenaSet::where('id', $request->id)
                ->update(['status' => 'Submitted']);
            $data = $request->validate([
                'dataContents' => ['required', 'array'],
                'dataContents.*.id' => ['sometimes', 'nullable', 'integer', 'exists:fenomenas,id'],
                'dataContents.*.fenomena_sets' => ['sometimes', 'nullable', 'integer'],
                'dataContents.*.category_id' => ['required', 'integer'],
                'dataContents.*.sector_id' => ['sometimes', 'nullable', 'integer'],
                'dataContents.*.subsector_id' => ['sometimes', 'nullable', 'integer'],
                'dataContents.*.qtoq' => ['sometimes', 'string', 'nullable'],
                'dataContents.*.yony' => ['sometimes', 'string', 'nullable'],
                'dataContents.*.implisit' => ['sometimes', 'string', 'nullable'],
            ]);
            foreach ($data['dataContents'] as $key => $value) {
                # code...
                if ($value['id']) {
                    Fenomena::where('id', $value['id'])
                        ->update([
                            'fenomena_sets' => $value['fenomena_sets'],
                            'category_id' => $value['category_id'],
                            'sector_id' => $value['sector_id'],
                            'subsector_id' => $value['subsector_id'],
                            'qtoq' => $value['qtoq'] ?? null,
                            'yony' => $value['yony'] ?? null,
                            'implisit' => $value['implisit'] ?? null,
                        ]);
                } else {
                    if ($value['qtoq'] || $value['yony'] || $value['implisit']) {
                        $this_fenomena = Fenomena::where('fenomena_sets', $request->id)
                            ->where('category_id', $value['category_id'])
                            ->where('sector_id', $value['sector_id'])
                            ->where('subsector_id', $value['subsector_id'])
                            ->first();
                        if ($this_fenomena) {
                            Fenomena::where('category_id', $value['category_id'])
                                ->where('sector_id', $value['sector_id'])
                                ->where('subsector_id', $value['subsector_id'])
                                ->update([
                                    'qtoq' => $value['qtoq'] ?? null,
                                    'yony' => $value['yony'] ?? null,
                                    'implisit' => $value['implisit'] ?? null,
                                ]);
                        } else {
                            Fenomena::create([
                                'fenomena_sets' => $request->id,
                                'category_id' => $value['category_id'],
                                'sector_id' => $value['sector_id'],
                                'subsector_id' => $value['subsector_id'],
                                'qtoq' => $value['qtoq'] ?? null,
                                'yony' => $value['yony'] ?? null,
                                'implisit' => $value['implisit'] ?? null,
                            ]);
                        }
                    }
                }
            }
            $message = [
                'type' => 'success',
                'message' => 'Berhasil Submit'
            ];
            array_push($notification, $message);
            DB::commit();
            return redirect()->route($route . 'entri-fenomena')->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Ada Kesalahan ketika submit',
                'errors' => $th->getMessage()
            ];
            array_push($notification, $message);
            return redirect()->route($route . 'entri-fenomena')->with('notification', $notification);
        }
    }

    public function unsubmitFenomena(Request $request)
    {
        $notification = [];
        $type = $request->type;
        if ($type == 'Lapangan Usaha') $route = 'lapus.';
        else if ($type == 'Pengeluaran') $route = 'peng.';
        try {
            //code...
            DB::beginTransaction();
            $request->validate([
                'id' => ['required', 'integer']
            ]);
            $fenomenasets = FenomenaSet::where('id', $request->id)
                ->update(['status' => 'Entry']);
            $message = [
                'type' => 'success',
                'message' => 'Data kembali ke status Entry'
            ];
            array_push($notification, $message);
            DB::commit();
            return redirect()->route($route . 'entri-fenomena')->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Ada Kesalahan ketika unsubmit',
                'errors' => $th->getMessage()
            ];
            array_push($notification, $message);
            return redirect()->route($route . 'entri-fenomena')->with('notification', $notification);
        }
    }

    public function monitoring()
    {
        $regions = Region::getMyRegion();
        return Inertia::render('Fenomena/Monitoring', [
            'regions' => $regions
        ]);
    }

    public function getMonitoring(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string'],
            'year' => ['required', 'integer'],
            'quarter' => ['required', 'integer'],
        ]);
        $notification = [];
        $regions = Region::getMyRegion();
        try {
            //code...
            $fenomena_set = FenomenaSet::where('type', $validated['type'])
                ->where('year', $validated['year'])
                ->where('quarter', $validated['quarter'])
                ->whereIn('region_id', $regions->pluck('value'))
                ->pluck('status', 'region_id');
            $message = [
                'type' => 'success',
                'message' => 'Monitoring berhasil diambil'
            ];
            array_push($notification, $message);
            $monitoring = $regions->mapWithKeys(function ($reg) use ($fenomena_set) {
                return [$reg->value => ['status' => $fenomena_set[$reg->value] ?? 'Belum']];
            });
            return response()->json([
                'monitoring' => $monitoring,
                'notification' => $notification
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            $message = [
                'type' => 'error',
                'message' => 'Ada Kesalahan ketika unsubmit',
                'errors' => $th->getMessage()
            ];
            array_push($notification, $message);
            return response()->json(['notification' => $notification]);
        }
    }
}
