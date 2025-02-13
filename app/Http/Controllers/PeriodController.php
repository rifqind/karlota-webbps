<?php

namespace App\Http\Controllers;

use App\Models\Period;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PeriodController extends Controller
{
    //
    public function index(Request $request)
    {
        if ($request->paginated) $paginated = $request->paginated;
        else $paginated = 10;
        if ($request->currentPage) $currentPage = $request->currentPage;
        else $currentPage = 1;

        $query = Period::query();
        $number = 1;
        $dataToCounted = $query
            ->select('*');

        if ($request->orderAttribute) {
            $order = $request->orderAttribute;
            if (sizeof($order) > 2) $query->orderBy($order['label'], $order['value']);
            else $query->orderBy('created_at', 'desc');
        } else $query->orderBy('created_at', 'desc');

        if ($request->ArrayFilter) {
            $filter = $request->ArrayFilter;
            if (!empty($filter['type'])) {
                $query->where('type', 'like', '%' . $filter['type'] . '%');
            }
            if (!empty($filter['quarter'])) {
                $query->where('quarter', 'like', '%' .  $filter['quarter'] . '%');
            }
            if (!empty($filter['year'])) {
                $query->where('year', 'like', '%' .  $filter['year'] . '%');
            }
            if (!empty($filter['description'])) {
                $query->where('description', 'like', '%' . $filter['description'] . '%');
            }
            if (!empty($filter['status'])) {
                $query->where('status', 'like', '%' . $filter['status'] . '%');
            }
            if (!empty($filter['started_at'])) {
                $query->where('started_at', 'like', '%' . $filter['started_at'] . '%');
            }
            if (!empty($filter['ended_at'])) {
                $query->where('ended_at', 'like', '%' . $filter['ended_at'] . '%');
            }
        }

        $countData = $dataToCounted->count();
        $data = $query->paginate($paginated, ['*'], 'page', $currentPage);
        foreach ($data as $key => $value) {
            # code...
            $value->number = $number;
            $number++;
        }
        if ($request->paginated) {
            return response()->json([
                'period' => $data,
                'countData' => $countData
            ]);
        }
        return Inertia::render('Period/Index', [
            'period' => $data,
            'countData' => $countData
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string'],
            'year' => ['required', 'integer'],
            'quarter' => ['required', 'integer'],
            'description' => ['required', 'string'],
            'datepicker' => ['required', 'array'],
            'datepicker.startDate' => ['required', 'date'],
            'datepicker.endDate' => ['required', 'date'],
        ]);

        try {
            //code...
            DB::beginTransaction();
            $validated['started_at'] = Carbon::parse($validated['datepicker']['startDate'])->format('Y-m-d');
            $validated['ended_at'] = Carbon::parse($validated['datepicker']['endDate'])->format('Y-m-d');

            if ($request->id) {
                $updated_data = Period::findOrFail($request->id);
                $updated_data->update($validated);
                DB::commit();
                return redirect()->route('period.index')->with('message', 'Berhasil mengedit periode putaran tersebut');
            } else
                $new_data = Period::create($validated);
            DB::commit();
            return redirect()->route('period.index')->with('message', 'Berhasil menambah periode putaran baru');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return redirect()->route('period.index')->with('error', $th->getMessage());
        }
    }

    public function fetch(String $id)
    {
        $fetched = Period::find($id);
        return response()->json(['data' => $fetched]);
    }

    public function destroy(String $id)
    {
        try {
            //code...
            DB::beginTransaction();
            $data_to_delete = Period::findOrFail($id);
            $data_to_delete->delete();
            DB::commit();
            return redirect()->route('period.index')->with('message', 'Berhasil menghapus periode putaran tersebut');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return redirect()->route('period.index')->with('error', $th->getMessage());
        }
    }

    //fetchStage
    public function fetchYear(Request $request)
    {
        $data = Period::selectRaw('DISTINCT year as value, year as label')
            ->where('type', $request->type)
            ->orderBy('year', 'DESC')
            ->get();
        return response()->json($data);
    }

    public function fetchQuarter(Request $request)
    {
        $data = Period::selectRaw('DISTINCT quarter as value, quarter as label')
            ->where('type', $request->type)
            ->where('year', $request->year)
            ->get();
        return response()->json($data);
    }

    public function fetchPeriod(Request $request)
    {
        $data = Period::where('type', $request->type)
            ->where('year', $request->year)
            ->where('quarter', $request->quarter)
            ->get(['id as value', 'description as label']);
        return response()->json($data);
    }

    public function fetchYearBefore(Request $request)
    {
        $data = Period::where('type', $request->type)
            ->where('year', $request->year - 1)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->year . ' - Triwulan ' . $item->quarter . ' - ' . $item->description . ' (' . $item->status . ')',
                    'value' => $item->id,
                ];
            });

        return response()->json($data);
    }
}
