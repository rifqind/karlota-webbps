<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Datacontent;
use App\Models\Produsen;
use App\Models\Row;
use App\Models\RowOrder;
use App\Models\Sekunder;
use App\Models\StatusSekunder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SekunderDataController extends Controller
{
    /**
     * List status sekunder data with filtering, sorting, and pagination.
     * GET /api/sekunder/index
     */
    public function index(Request $request): JsonResponse
    {
        $paginated = $request->paginated ? (int) $request->paginated : 10;
        $currentPage = $request->currentPage ? (int) $request->currentPage : 1;
        $number = ($currentPage - 1) * $paginated + 1;

        $query = StatusSekunder::query();
        $query
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
                'status_sekunder.updated_at as updated_time',
            ]);

        // Sorting
        $order = $request->orderAttribute;
        if (is_string($order)) {
            $order = json_decode($order, true);
        }

        if (is_array($order) && !empty($order['label']) && !empty($order['value'])) {
            $query->orderBy($order['label'], $order['value']);
        } else {
            $query->orderBy('status_sekunder.updated_at', 'desc')->orderBy('status', 'asc');
        }

        // Filters
        $filter = $request->ArrayFilter;
        if (is_string($filter)) {
            $filter = json_decode($filter, true);
        }

        if (is_array($filter)) {
            if (!empty($filter['label_data'])) {
                $query->where('s.label', 'like', '%' . $filter['label_data'] . '%');
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
                $query->where(DB::raw("CONCAT(u.name, ' - ', status_sekunder.updated_at)"), 'like', '%' . $filter['updated_at'] . '%');
            }
            if (!empty($filter['row_label'])) {
                $target = Datacontent::join('rows as r', 'r.id', '=', 'datacontent.row_id')
                    ->where('r.label', 'like', '%' . $filter['row_label'] . '%')->pluck('datacontent.status_id')->unique();
                $query->whereIn('status_sekunder.id', $target);
            }
        }

        $countData = (clone $query)->count();
        $sekunder = $query->paginate($paginated, ['*'], 'page', $currentPage);

        $sekunderObject = [];
        foreach ($sekunder as $s) {
            $s->number = $number;
            $number++;

            $checkData = Datacontent::where('status_id', $s->id)->get();
            if (count($checkData) > 0) {
                $rowId = $checkData->pluck('row_id')->unique();
                $rows = Row::whereIn('id', $rowId)->get();
            } else {
                $rows = [
                    [
                        'id' => 'Tidak ada data',
                        'label' => 'Tidak ada data',
                    ],
                ];
            }

            $sekunderObject[] = [
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
                'rows' => $rows,
            ];
        }

        return response()->json([
            'sekunder' => $sekunderObject,
            'countData' => $countData,
        ]);
    }

    /**
     * Return data needed for the Create form (produsen list + rows list).
     * GET /api/sekunder/create-data
     */
    public function createData(): JsonResponse
    {
        $produsen = Produsen::orderBy('nama', 'asc')
            ->select(['id as value', 'nama as label'])
            ->get();
        $rows = Row::orderBy('label', 'asc')
            ->select(['id as value', 'label'])
            ->get();

        return response()->json([
            'produsen' => $produsen,
            'rows' => $rows,
        ]);
    }

    /**
     * Store a new Sekunder record with status per year and datacontent per row.
     * POST /api/sekunder/store
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'datas.label' => ['required', 'string', 'max:255'],
            'datas.tahun.*' => ['required', 'date_format:Y'],
            'datas.produsen_id' => ['required', 'integer'],
            'rows.selected.*' => ['required', 'integer'],
            'order.*' => ['sometimes', 'nullable'],
        ]);

        // Check for duplicate
        $existing = Sekunder::where('label', $validated['datas']['label'])
            ->where('produsen_id', $validated['datas']['produsen_id'])
            ->first();

        if ($existing) {
            return response()->json([
                'error' => 'Data dengan label tersebut sudah ada di dinas yang dipilih.',
            ], 422);
        }

        try {
            DB::beginTransaction();
            $payload = $request->attributes->get('auth_payload');
            $userId = $payload['sub'] ?? null;

            $newSekunder = Sekunder::create([
                'label' => $validated['datas']['label'],
                'produsen_id' => $validated['datas']['produsen_id'],
                'created_by' => $userId,
            ]);

            foreach ($validated['datas']['tahun'] as $t) {
                $newStatus = StatusSekunder::create([
                    'sekunder_id' => $newSekunder->id,
                    'tahun' => $t,
                    'status' => 1,
                    'updated_by' => $userId,
                ]);

                foreach ([1, 2, 3, 4] as $tw) {
                    foreach ($validated['rows']['selected'] as $rowId) {
                        Datacontent::create([
                            'status_id' => $newStatus->id,
                            'row_id' => $rowId,
                            'triwulan' => $tw,
                        ]);
                    }
                }
            }

            // Save row order
            if (!empty($validated['order'])) {
                RowOrder::create([
                    'orders' => implode(',', $validated['order']),
                    'sekunder_id' => $newSekunder->id,
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Berhasil menambahkan data baru']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['error' => $th->getMessage()], 422);
        }
    }

    /**
     * Delete status_sekunder and datacontent for a specific year.
     * DELETE /api/sekunder/delete/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            DB::beginTransaction();
            Datacontent::where('status_id', $id)->delete();
            StatusSekunder::where('id', $id)->delete();
            DB::commit();
            return response()->json(['message' => 'Berhasil menghapus data']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['error' => $th->getMessage()], 422);
        }
    }

    /**
     * Add year to an existing sekunder dataset.
     * POST /api/sekunder/add-year
     */
    public function addYear(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required'],
            'produsen_id' => ['required', 'exists:produsen,id'],
            'label' => ['required'],
            'tahun' => ['required', 'array'],
            'tahun.*' => [
                'required',
                'date_format:Y',
                Rule::unique('status_sekunder', 'tahun')
                    ->where(fn($q) => $q->whereIn('sekunder_id', function ($sub) use ($request) {
                        $sub->select('id')
                            ->from('sekunder')
                            ->where('produsen_id', $request->produsen_id)
                            ->where('label', $request->label);
                    })),
            ],
        ], ['tahun.*.unique' => 'Tahun tersebut sudah ada']);

        try {
            DB::beginTransaction();
            $payload = $request->attributes->get('auth_payload');
            $userId = $payload['sub'] ?? null;

            $latestStatus = StatusSekunder::where('sekunder_id', $request->id)
                ->orderBy('updated_at', 'desc')
                ->first();

            $rows = $latestStatus
                ? Datacontent::where('status_id', $latestStatus->id)->pluck('row_id')->unique()->toArray()
                : [];

            foreach ($validated['tahun'] as $val) {
                $newData = StatusSekunder::firstOrCreate([
                    'sekunder_id' => $validated['id'],
                    'tahun' => $val,
                ], ['status' => 1, 'updated_by' => $userId]);

                foreach ([1, 2, 3, 4] as $tw) {
                    foreach ($rows as $r) {
                        Datacontent::updateOrCreate([
                            'status_id' => $newData->id,
                            'row_id' => $r,
                            'triwulan' => $tw,
                        ], []);
                    }
                }
            }

            DB::commit();
            return response()->json(['message' => 'Berhasil menambahkan tahun baru']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['error' => $th->getMessage()], 422);
        }
    }
}
