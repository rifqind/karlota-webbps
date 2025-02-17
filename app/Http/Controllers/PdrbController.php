<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use App\Models\Pdrb;
use App\Models\Period;
use App\Models\Region;
use App\Models\Subsector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PdrbController extends Controller
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
        // $dataContents = Pdrb::where('dataset_id', 1)
        //     ->get();
        return Inertia::render('Pdrb/Entri', [
            'type' => $type,
            'subsectors' => $subsectors,
            // 'dataContents' => $dataContents,
            'regions' => $regions
        ]);
    }

    public function show(Request $request)
    {
        $subsectors = Subsector::where('type', $request->type)->pluck('id');
        $validated = $request->validate([
            'type' => ['required', 'string'],
            'year' => ['required', 'integer'],
            'quarter' => ['required', 'integer'],
            'description' => ['required', 'integer'],
            'dataBefore' => ['sometimes', 'integer', 'nullable'],
            'regions' => ['required', 'integer'],
        ]);
        $period_id = $validated['description'];
        $period_before = ($request->dataBefore) ? $validated['dataBefore'] : null;
        $current_period = Period::where('id', $period_id)->first();
        $notification = [];
        if (!$period_before) {
            if ($current_period->status == 'Aktif') {
                $previous_period = Period::where('type', $validated['type'])->where('year', $validated['year'] - 1)
                    ->where('quarter', 4)
                    ->latest('id')
                    ->value('id');
            } else {
                $previous_period = Period::where('type', $validated['type'])
                    ->where('year', $validated['year'] - 1)
                    ->where('quarter', 4)
                    ->where('status', '<>', 'Aktif')
                    ->latest('id')
                    ->value('id');
            }
        } else $previous_period = $period_before;

        $periode_before_year = Period::find($previous_period);
        $current_dataset = Dataset::where('period_id', $period_id)
            ->where('region_id', $validated['regions'])
            ->where('type', $validated['type'])
            ->value('id');
        $previous_dataset = Dataset::where('period_id', $previous_period)
            ->where('region_id', $validated['regions'])
            ->where('type', $validated['type'])
            ->value('id');

        if ($previous_dataset) {
            $previous_data = Pdrb::where('dataset_id', $previous_dataset)
                ->orderBy('subsector_id')
                ->get();
            $message = [
                'type' => 'success',
                'message' => 'Data periode sebelumnya berhasil diunduh, Tahun ' . $periode_before_year->year . ' ' . $periode_before_year->description
            ];
            array_push($notification, $message);
        } else {
            $previous_data = [];
            for ($index = 1; $index <= 4; $index++) {
                foreach ($subsectors as $subsector_id) {
                    $singleData = [
                        'subsector_id' => $subsector_id,
                        'type' => $validated['type'],
                        'year' => $validated['year'] - 1,
                        'quarter' => $index,
                        'region_id' => $validated['regions'],
                        'adhb' => null,
                        'adhk' => null,
                    ];
                    array_push($previous_data, $singleData);
                }
            }

            $message = [
                'type' => 'error',
                'message' => 'Data periode sebelumnya tidak ada / belum final, summary tidak dapat ditampilkan'
            ];
            array_push($notification, $message);
        }

        if ($current_dataset) {
            $current_data = Pdrb::where('dataset_id', $current_dataset)
                ->orderBy('subsector_id')
                ->get();
            $message = [
                'type' => 'success',
                'message' => 'Mengambil Data PDRB Periode Ini'
            ];
        } else {
            //create new datasets
            $current_object_dataset = Dataset::create([
                'type' => $validated['type'],
                'period_id' => $period_id,
                'region_id' => $validated['regions'],
                'year' => $validated['year'],
                'quarter' => $validated['quarter'],
                'status' => 'Entry',
            ]);

            for ($index = 1; $index <= 4; $index++) {
                if ($index <= $validated['quarter']) {

                    $inputData = [];
                    foreach ($subsectors as $subsector_id) {
                        $singleData = [
                            'subsector_id' => $subsector_id,
                            'dataset_id' => $current_object_dataset->id,
                            'year' => $validated['year'],
                            'quarter' => $index,
                        ];
                        array_push($inputData, $singleData);
                    }

                    Pdrb::insert($inputData);
                }
            }
            $current_data = Pdrb::where('dataset_id', $current_object_dataset->id)
                ->orderBy('subsector_id')
                ->get();
            $current_dataset = $current_object_dataset->id;
            $message = [
                'type' => 'success',
                'message' => 'Inisiasi Data PDRB Periode Ini'
            ];
            array_push($notification, $message);
        }
        $dataset = Dataset::find($current_dataset);
        return  response()->json([
            'current_data' => $current_data,
            'previous_data' => $previous_data,
            'notification' => $notification,
            'dataset' => $dataset
        ]);
    }

    public function saveEntri(Request $request)
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
                'dataContents.*.id' => ['required', 'integer', 'exists:pdrbs,id'],
                'dataContents.*.dataset_id' => ['required', 'integer'],
                'dataContents.*.year' => ['required', 'integer'],
                'dataContents.*.quarter' => ['required', 'integer'],
                'dataContents.*.subsector_id' => ['required', 'integer'],
                'dataContents.*.adhb' => ['sometimes', 'numeric', 'nullable'],
                'dataContents.*.adhk' => ['sometimes', 'numeric', 'nullable'],
            ]);
            foreach ($data['dataContents'] as $key => $value) {
                # code...
                Pdrb::where('id', $value['id'])
                    ->update([
                        'dataset_id' => $value['dataset_id'],
                        'year' => $value['year'],
                        'quarter' => $value['quarter'],
                        'subsector_id' => $value['subsector_id'],
                        'adhb' => $value['adhb'],
                        'adhk' => $value['adhk'],
                    ]);
            }
            $message = [
                'type' => 'success',
                'message' => 'Berhasil Simpan Data PDRB'
            ];
            array_push($notification, $message);
            DB::commit();
            return redirect()->route($route . 'entri')->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Ada Kesalahan dalam Data yang disimpan',
                'errors' => $th->getMessage()
            ];
            array_push($notification, $message);
            return redirect()->route($route . 'entri')->with('notification', $notification);
        }
    }

    public function submitEntri(Request $request)
    {
        $notification = [];
        $type = $request->type;
        if ($type == 'Lapangan Usaha') $route = 'lapus.';
        else if ($type == 'Pengeluaran') $route = 'peng.';
        try {
            //code...
            DB::beginTransaction();
            if ($type == 'Lapangan Usaha') $route = 'lapus.';
            else if ($type == 'Pengeluaran') $route = 'peng.';
            $request->validate([
                'id' => ['required', 'integer']
            ]);
            $dataset = Dataset::where('id', $request->id)
                ->update(['status' => 'Submitted']);
            $message = [
                'type' => 'success',
                'message' => 'Data sudah di-submit'
            ];
            array_push($notification, $message);
            DB::commit();
            return redirect()->route($route . 'entri')->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Ada Kesalahan ketika submit',
                'errors' => $th->getMessage()
            ];
            array_push($notification, $message);
            return redirect()->route($route . 'entri')->with('notification', $notification);
        }
    }

    public function unsubmitEntri(Request $request)
    {
        $notification = [];
        $type = $request->type;
        if ($type == 'Lapangan Usaha') $route = 'lapus.';
        else if ($type == 'Pengeluaran') $route = 'peng.';
        try {
            //code...
            DB::beginTransaction();
            if ($type == 'Lapangan Usaha') $route = 'lapus.';
            else if ($type == 'Pengeluaran') $route = 'peng.';
            $request->validate([
                'id' => ['required', 'integer']
            ]);
            $dataset = Dataset::where('id', $request->id)
                ->update(['status' => 'Entry']);
            $message = [
                'type' => 'success',
                'message' => 'Data kembali ke status Entry'
            ];
            array_push($notification, $message);
            DB::commit();
            return redirect()->route($route . 'entri')->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Ada Kesalahan ketika unsubmit',
                'errors' => $th->getMessage()
            ];
            array_push($notification, $message);
            return redirect()->route($route . 'entri')->with('notification', $notification);
        }
    }
}
