<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Datacontent;
use App\Models\Produsen;
use App\Models\Region;
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

    /**
     * Get data for Sekunder Entry page (status, sekunder info, rows, datacontent, datacontent_before).
     * GET /api/sekunder/entri/{id}
     */
    public function entri(string $id): JsonResponse
    {
        $statusSekunder = StatusSekunder::where('status_sekunder.id', $id)
            ->join('status as s', 's.id', '=', 'status_sekunder.status')
            ->select([
                'status_sekunder.*',
                'status_sekunder.updated_at as updated_time',
                's.label as status_label',
            ])
            ->first();

        if (!$statusSekunder) {
            return response()->json(['error' => 'Data status sekunder tidak ditemukan.'], 404);
        }

        $sekunder = Sekunder::find($statusSekunder->sekunder_id);
        if (!$sekunder) {
            return response()->json(['error' => 'Data sekunder tidak ditemukan.'], 404);
        }

        $datacontent = Datacontent::where('status_id', $id)->get();
        $rowIds = $datacontent->pluck('row_id')->unique()->toArray();
        $rows = Row::whereIn('id', $rowIds)->get();

        // Apply custom row order if available
        $rowOrder = RowOrder::where('sekunder_id', $sekunder->id)->first();
        if ($rowOrder && !empty($rowOrder->orders)) {
            $orderedIds = explode(',', $rowOrder->orders);
            $rows = $rows->sortBy(function ($model) use ($orderedIds) {
                $pos = array_search((string) $model->id, $orderedIds);
                return $pos !== false ? $pos : 99999;
            })->values();
        }

        // Previous year's data for Growth calculation
        $statusSekunderBefore = StatusSekunder::where('sekunder_id', $sekunder->id)
            ->where('tahun', (int) $statusSekunder->tahun - 1)
            ->first();

        $datacontentBefore = $statusSekunderBefore
            ? Datacontent::where('status_id', $statusSekunderBefore->id)->get()
            : [];

        return response()->json([
            'status_sekunder' => $statusSekunder,
            'sekunder' => $sekunder,
            'rows' => $rows,
            'datacontent' => $datacontent,
            'datacontent_before' => $datacontentBefore,
        ]);
    }

    /**
     * Save/update Sekunder Entry data.
     * POST /api/sekunder/update
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status_id' => ['required'],
            'datacontent' => ['required', 'array'],
            'datacontent.*.row_id' => ['required'],
            'datacontent.*.triwulan' => ['required'],
            'datacontent.*.data' => ['nullable'],
        ]);

        try {
            DB::beginTransaction();

            $payload = $request->attributes->get('auth_payload');
            $userId = $payload['sub'] ?? null;

            // Update status_sekunder status to 2 (Terisi/Tersedia) and update timestamp
            $statusSekunder = StatusSekunder::find($validated['status_id']);
            if (!$statusSekunder) {
                DB::rollBack();
                return response()->json(['error' => 'Data status sekunder tidak ditemukan.'], 404);
            }

            $statusSekunder->status = 2;
            $statusSekunder->updated_by = $userId;
            $statusSekunder->touch();
            $statusSekunder->save();

            foreach ($validated['datacontent'] as $item) {
                if (isset($item['id']) && !empty($item['id'])) {
                    Datacontent::where('id', $item['id'])->update([
                        'data' => $item['data'],
                    ]);
                } else {
                    Datacontent::updateOrCreate(
                        [
                            'status_id' => $validated['status_id'],
                            'row_id' => $item['row_id'],
                            'triwulan' => $item['triwulan'],
                        ],
                        [
                            'data' => $item['data'],
                        ]
                    );
                }
            }

            DB::commit();
            return response()->json(['message' => 'Berhasil menyimpan data sekunder']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => $th->getMessage(),
            ], 422);
        }
    }

    /**
     * List produsen with secondary data summary for Data by Dinas page.
     * GET /api/sekunder/data-by-dinas
     */
    public function dataByDinas(Request $request): JsonResponse
    {
        $paginated = $request->paginated ? (int) $request->paginated : 10;
        $currentPage = $request->currentPage ? (int) $request->currentPage : 1;
        $number = ($currentPage - 1) * $paginated + 1;

        $query = Produsen::query();
        $wilayah = Region::getMyRegionId();

        $dataToCounted = $query
            ->leftJoin('regions as r', 'r.id', '=', 'produsen.region_id')
            ->whereIn('r.id', $wilayah)
            ->select(['produsen.*', 'r.name as region_name']);

        $order = $request->orderAttribute;
        if (is_string($order)) {
            $order = json_decode($order, true);
        }

        if (is_array($order) && !empty($order['label']) && !empty($order['value'])) {
            $query->orderBy($order['label'], $order['value']);
        } else {
            $query->orderBy('region_id')->orderBy('nama');
        }

        $filter = $request->ArrayFilter;
        if (is_string($filter)) {
            $filter = json_decode($filter, true);
        }

        if (is_array($filter)) {
            if (!empty($filter['nama'])) {
                $query->where('nama', 'like', '%' . $filter['nama'] . '%');
            }
            if (!empty($filter['region_name'])) {
                $query->where('r.name', 'like', '%' . $filter['region_name'] . '%');
            }
            if (!empty($filter['sekunder_list'])) {
                $sekunderId = Sekunder::where('label', 'like', '%' . $filter['sekunder_list'] . '%')
                    ->pluck('produsen_id')->toArray();
                $query->whereIn('produsen.id', $sekunderId);
            }
        }

        $countData = (clone $query)->count();
        $produsen = $query->paginate($paginated, ['*'], 'page', $currentPage);
        $produsenObject = [];
        $sekunderNumber = 0;

        foreach ($produsen as $p) {
            $sekunders = Sekunder::where('produsen_id', $p->id)->pluck('label')->toArray();
            $sekunderNumber += count($sekunders);
            $produsenObject[] = [
                'number' => $number++,
                'id' => $p->id,
                'produsen_label' => $p->nama,
                'region_name' => $p->region_name,
                'sekunder_list' => $sekunders,
            ];
        }

        return response()->json([
            'produsen' => $produsenObject,
            'sekunder_number' => $sekunderNumber,
            'countData' => $countData,
        ]);
    }

    /**
     * View secondary data grouped by a specific produsen/dinas.
     * GET /api/sekunder/data-by-dinas/{id}
     */
    public function byDinasView(string $id): JsonResponse
    {
        $dinas = Produsen::find($id);
        if (!$dinas) {
            return response()->json(['error' => 'Dinas tidak ditemukan.'], 404);
        }

        $sekunderList = Sekunder::where('produsen_id', $id)
            ->select(['id', 'label'])
            ->get();

        $sekunderIds = $sekunderList->pluck('id');
        $latestYear = StatusSekunder::whereIn('sekunder_id', $sekunderIds)->max('tahun');
        $listOfYear = StatusSekunder::whereIn('sekunder_id', $sekunderIds)
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        if (!$latestYear) {
            return response()->json([
                'status' => [],
                'produsen' => $dinas,
                'sekunder' => $sekunderList,
                'data' => [],
                'data_before' => [],
                'latestYear' => null,
                'listOfYear' => [],
            ]);
        }

        $status = StatusSekunder::whereIn('sekunder_id', $sekunderIds)
            ->where('tahun', $latestYear)
            ->get()
            ->groupBy('sekunder_id');

        $dataObject = $this->buildData($sekunderList, $latestYear, $latestYear - 1);

        return response()->json([
            'status' => $status,
            'produsen' => $dinas,
            'sekunder' => $sekunderList,
            'data' => $dataObject['data'],
            'data_before' => $dataObject['data_before'],
            'latestYear' => $latestYear,
            'listOfYear' => $listOfYear,
        ]);
    }

    /**
     * Fetch secondary data when switching year on By Dinas view.
     * GET /api/sekunder/data-by-dinas-change-year
     */
    public function byDinasChangeYear(Request $request): JsonResponse
    {
        $currentYear = (int) $request->tahun;
        $beforeYear = $currentYear - 1;
        $sekunderList = Sekunder::where('produsen_id', $request->id)
            ->select(['id', 'label'])
            ->get();

        $dataObject = $this->buildData($sekunderList, $currentYear, $beforeYear);
        return response()->json([
            'data' => $dataObject['data'],
            'data_before' => $dataObject['data_before'],
        ]);
    }

    /**
     * Helper to build structured dataset for By Dinas view.
     */
    private function buildData($sekunderList, $currentYear, $beforeYear): array
    {
        $sekunderIds = $sekunderList->pluck('id');
        $status = StatusSekunder::whereIn('sekunder_id', $sekunderIds)
            ->where('tahun', $currentYear)
            ->get()
            ->groupBy('sekunder_id');

        $datas = [];
        foreach ($status as $sekunderId => $statusList) {
            $sekunder = $sekunderList->firstWhere('id', $sekunderId);
            $statusIds = $statusList->pluck('id');

            $datacontent = Datacontent::whereIn('status_id', $statusIds)->get();
            $rows = Row::whereIn('id', $datacontent->pluck('row_id'))->get();

            $rowOrder = RowOrder::where('sekunder_id', $sekunderId)->first();
            if ($rowOrder && !empty($rowOrder->orders)) {
                $orderedIds = explode(',', $rowOrder->orders);
                $rows = $rows->sortBy(function ($model) use ($orderedIds) {
                    $pos = array_search((string) $model->id, $orderedIds);
                    return $pos !== false ? $pos : 99999;
                })->values();
            }

            $datas[] = [
                'sekunder_id' => $sekunderId,
                'label' => $sekunder?->label,
                'tahun' => $currentYear,
                'status' => $statusList->first(),
                'data' => $datacontent,
                'row' => $rows,
            ];
        }

        $statusBefore = StatusSekunder::whereIn('sekunder_id', $sekunderIds)
            ->where('tahun', $beforeYear)
            ->get()
            ->groupBy('sekunder_id');

        $dataBefore = [];
        if ($statusBefore->isNotEmpty()) {
            foreach ($statusBefore as $sekunderId => $statusList) {
                $sekunder = $sekunderList->firstWhere('id', $sekunderId);
                $statusIds = $statusList->pluck('id');

                $datacontent = Datacontent::whereIn('status_id', $statusIds)->get();
                $rows = Row::whereIn('id', $datacontent->pluck('row_id'))->get();

                $rowOrder = RowOrder::where('sekunder_id', $sekunderId)->first();
                if ($rowOrder && !empty($rowOrder->orders)) {
                    $orderedIds = explode(',', $rowOrder->orders);
                    $rows = $rows->sortBy(function ($model) use ($orderedIds) {
                        $pos = array_search((string) $model->id, $orderedIds);
                        return $pos !== false ? $pos : 99999;
                    })->values();
                }

                $dataBefore[] = [
                    'sekunder_id' => $sekunderId,
                    'label' => $sekunder?->label,
                    'tahun' => $beforeYear,
                    'status' => $statusList->first(),
                    'data' => $datacontent,
                    'row' => $rows,
                ];
            }
        }

        return ['data' => $datas, 'data_before' => $dataBefore];
    }
}
