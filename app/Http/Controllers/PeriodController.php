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
        $paginated = $request->paginated ? (int) $request->paginated : 10;
        $currentPage = $request->currentPage ? (int) $request->currentPage : 1;

        $query = Period::query();

        $order = $request->orderAttribute;
        if (is_string($order)) {
            $order = json_decode($order, true);
        }
        if (is_array($order) && !empty($order['label']) && !empty($order['value'])) {
            $query->orderBy($order['label'], $order['value']);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $filter = $request->ArrayFilter;
        if (is_string($filter)) {
            $filter = json_decode($filter, true);
        }
        if (is_array($filter)) {
            if (!empty($filter['type'])) {
                $query->where('type', 'like', '%' . $filter['type'] . '%');
            }
            if (!empty($filter['quarter'])) {
                $query->where('quarter', 'like', '%' . $filter['quarter'] . '%');
            }
            if (!empty($filter['year'])) {
                $query->where('year', 'like', '%' . $filter['year'] . '%');
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

        $countData = (clone $query)->count();
        $data = $query->paginate($paginated, ['*'], 'page', $currentPage);

        $number = ($currentPage - 1) * $paginated + 1;
        foreach ($data as $key => $value) {
            $value->number = $number;
            $number++;
        }

        if ($request->paginated || $request->wantsJson() || $request->is('api/*')) {
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
                $request->validate([
                    'status' => ['required', 'string'],
                ]);
                $validated['status'] = $request->status;
                $updated_data = Period::findOrFail($request->id);
                $updated_data->update($validated);
                DB::commit();
                if ($request->wantsJson() || $request->is('api/*')) {
                    return response()->json(['message' => 'Berhasil mengedit periode putaran tersebut', 'data' => $updated_data]);
                }
                return redirect()->route('period.index')->with('message', 'Berhasil mengedit periode putaran tersebut');
            } else {
                $validated['status'] = 'Aktif';
                $new_data = Period::create($validated);
            }
            DB::commit();
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Berhasil menambah periode putaran baru', 'data' => $new_data]);
            }
            return redirect()->route('period.index')->with('message', 'Berhasil menambah periode putaran baru');
        } catch (\Throwable $th) {
            DB::rollBack();
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['error' => $th->getMessage()], 422);
            }
            return redirect()->route('period.index')->with('error', $th->getMessage());
        }
    }

    public function fetch(string $id)
    {
        $fetched = Period::find($id);
        return response()->json(['data' => $fetched]);
    }

    public function destroy(Request $request, string $id)
    {
        try {
            DB::beginTransaction();
            $data_to_delete = Period::findOrFail($id);
            $data_to_delete->delete();
            DB::commit();
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Berhasil menghapus periode putaran tersebut']);
            }
            return redirect()->route('period.index')->with('message', 'Berhasil menghapus periode putaran tersebut');
        } catch (\Throwable $th) {
            DB::rollBack();
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['error' => $th->getMessage()], 422);
            }
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
            ->orderBy('quarter', 'asc')
            ->get();
        return response()->json($data);
    }

    public function fetchPeriod(Request $request)
    {
        $query = Period::query();
        if ($request->start == 'true') {
            $query->whereNot('status', 'Final');
        }
        $data = $query->where('type', $request->type)
            ->where('year', $request->year)
            ->where('quarter', $request->quarter)
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->description . ' (' . $item->status . ')',
                    'value' => $item->id
                ];
            });
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
