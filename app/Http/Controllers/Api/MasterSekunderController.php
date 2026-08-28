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

class MasterSekunderController extends Controller
{
    /**
     * List master sekunder data with filtering, sorting, and pagination.
     * GET /api/master/sekunder/index
     */
    public function index(Request $request): JsonResponse
    {
        $paginated = $request->paginated ? (int) $request->paginated : 10;
        $currentPage = $request->currentPage ? (int) $request->currentPage : 1;
        $number = ($currentPage - 1) * $paginated + 1;

        $query = Sekunder::query()
            ->join('produsen as p', 'p.id', '=', 'sekunder.produsen_id')
            ->join('users as u', 'u.id', '=', 'sekunder.created_by')
            ->select([
                'sekunder.id as id',
                'sekunder.label as label_data',
                'p.nama as nama_dinas',
                'u.name as username',
                'sekunder.created_at as created_time',
            ]);

        // Sorting
        $order = $request->orderAttribute;
        if (is_string($order)) {
            $order = json_decode($order, true);
        }

        if (is_array($order) && !empty($order['label']) && !empty($order['value'])) {
            $query->orderBy($order['label'], $order['value']);
        } else {
            $query->orderBy('sekunder.created_at', 'desc')->orderBy('p.nama', 'asc');
        }

        // Filters
        $filter = $request->ArrayFilter;
        if (is_string($filter)) {
            $filter = json_decode($filter, true);
        }

        if (is_array($filter)) {
            if (!empty($filter['label_data'])) {
                $query->where('sekunder.label', 'like', '%' . $filter['label_data'] . '%');
            }
            if (!empty($filter['nama_dinas'])) {
                $query->where('p.nama', 'like', '%' . $filter['nama_dinas'] . '%');
            }
            if (!empty($filter['tahun'])) {
                $target = StatusSekunder::where('tahun', 'like', '%' . $filter['tahun'] . '%')
                    ->pluck('sekunder_id')->unique();
                $query->whereIn('sekunder.id', $target);
            }
            if (!empty($filter['row_label'])) {
                if (trim(strtolower($filter['row_label'])) == 'tidak ada data') {
                    $statusIds = StatusSekunder::pluck('sekunder_id')->unique()->toArray();
                    $allIds = Sekunder::pluck('id')->toArray();
                    $noDataIds = array_values(array_diff($allIds, $statusIds));
                    $query->whereIn('sekunder.id', $noDataIds);
                } else {
                    $statusTarget = Datacontent::join('rows as r', 'r.id', '=', 'datacontent.row_id')
                        ->where('r.label', 'like', '%' . $filter['row_label'] . '%')
                        ->pluck('datacontent.status_id')->unique();
                    $target = StatusSekunder::whereIn('id', $statusTarget)
                        ->pluck('sekunder_id')->unique();
                    $query->whereIn('sekunder.id', $target);
                }
            }
        }

        $countData = (clone $query)->count();
        $sekunder = $query->paginate($paginated, ['*'], 'page', $currentPage);

        // Build enriched response with rows and tahun
        $sekunderObject = [];
        foreach ($sekunder as $s) {
            $s->number = $number;
            $number++;

            $statusSet = StatusSekunder::where('sekunder_id', $s->id)
                ->orderBy('tahun', 'desc')->get();
            $statusIds = $statusSet->pluck('id')->toArray();
            $statusTahun = $statusSet->pluck('tahun');

            if (count($statusIds) > 0) {
                $checkData = Datacontent::whereIn('status_id', $statusIds)->get();
                if (count($checkData) > 0) {
                    $rowIds = $checkData->pluck('row_id')->unique();
                    $rows = Row::whereIn('id', $rowIds)->get();
                } else {
                    $rows = [['id' => 'Tidak ada data', 'label' => 'Tidak ada data']];
                }
            } else {
                $rows = [['id' => 'Tidak ada data', 'label' => 'Tidak ada data']];
            }

            $sekunderObject[] = [
                'number' => $s->number,
                'id' => $s->id,
                'label_data' => $s->label_data,
                'nama_dinas' => $s->nama_dinas,
                'username' => $s->username,
                'created_time' => $s->created_time,
                'rows' => $rows,
                'tahun' => $statusTahun,
            ];
        }

        return response()->json([
            'sekunder' => $sekunderObject,
            'countData' => $countData,
        ]);
    }

    /**
     * Delete a sekunder record.
     * DELETE /api/master/sekunder/delete/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            DB::beginTransaction();
            $statusSekunder = StatusSekunder::where('sekunder_id', $id)->get();

            if (count($statusSekunder) > 0) {
                DB::rollBack();
                return response()->json([
                    'error' => 'Masih ada data tahunan yang belum dihapus. Hapus data tahunan terlebih dahulu.',
                ], 422);
            }

            RowOrder::where('sekunder_id', $id)->delete();
            Sekunder::where('id', $id)->delete();

            DB::commit();
            return response()->json(['message' => 'Berhasil menghapus data sekunder']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => $th->getMessage(),
            ], 422);
        }
    }

    /**
     * Fetch a single sekunder record with related data for the Update form.
     * GET /api/master/sekunder/fetch/{id}
     */
    public function fetch(string $id): JsonResponse
    {
        $produsen = Produsen::orderBy('nama', 'asc')
            ->select(['id as value', 'nama as label'])
            ->get();

        $rows = Row::orderBy('label', 'asc')
            ->select(['id as value', 'label'])
            ->get();

        $sekunder = Sekunder::find($id);

        if (!$sekunder) {
            return response()->json(['error' => 'Data sekunder tidak ditemukan.'], 404);
        }

        $status = StatusSekunder::where('sekunder_id', $id)->get();
        $statusTahun = $status->pluck('tahun');
        $statusIds = $status->pluck('id');
        $sekunderRow = Datacontent::whereIn('status_id', $statusIds)
            ->pluck('row_id')->unique()->toArray();
        $sekunderRow = array_values($sekunderRow);

        $orders = RowOrder::where('sekunder_id', $id)->first('orders');
        $explodedOrders = $orders ? explode(',', $orders->orders) : [];

        return response()->json([
            'produsen' => $produsen,
            'rows' => $rows,
            'sekunder' => $sekunder,
            'tahun' => $statusTahun,
            'sekunder_row' => $sekunderRow,
            'order' => $explodedOrders,
        ]);
    }

    /**
     * Update a sekunder record.
     * POST /api/master/sekunder/update
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'datas.id' => ['required'],
            'datas.label' => ['required', 'string', 'max:255'],
            'datas.tahun' => ['required', 'array'],
            'datas.tahun.*' => ['required', 'date_format:Y'],
            'datas.produsen_id' => ['required', 'integer'],
            'rows.selected' => ['required', 'array'],
            'rows.selected.*' => ['required', 'integer'],
            'order' => ['sometimes', 'nullable', 'array'],
            'order.*' => ['sometimes', 'nullable'],
        ]);

        try {
            DB::beginTransaction();

            $statusList = StatusSekunder::where('sekunder_id', $validated['datas']['id'])
                ->whereIn('tahun', $validated['datas']['tahun'])
                ->pluck('id');

            $currentRow = Datacontent::whereIn('status_id', $statusList)
                ->pluck('row_id')
                ->unique()
                ->toArray();

            if (!$request->force) {
                $diffRow = array_diff($currentRow, $validated['rows']['selected']);
                if (!empty($diffRow)) {
                    DB::rollBack();
                    return response()->json([
                        'error' => 'Ada baris yang berbeda dengan data yang sudah ada. Gunakan "Force Simpan" untuk melanjutkan.',
                        'requires_force' => true,
                    ], 422);
                }
            }

            // Update sekunder record
            $payload = $request->attributes->get('auth_payload');
            Sekunder::where('id', $validated['datas']['id'])
                ->update([
                    'label' => $validated['datas']['label'],
                    'produsen_id' => $validated['datas']['produsen_id'],
                    'created_by' => $payload['sub'] ?? null,
                ]);

            // Update/create status and datacontent for each tahun and row
            foreach ($validated['datas']['tahun'] as $t) {
                $newStatus = StatusSekunder::updateOrCreate([
                    'sekunder_id' => $validated['datas']['id'],
                    'tahun' => $t,
                ], [
                    'status' => 1,
                    'updated_by' => $payload['sub'] ?? null,
                ]);
                $newStatus->touch();

                foreach ([1, 2, 3, 4] as $tw) {
                    foreach ($validated['rows']['selected'] as $row) {
                        Datacontent::updateOrCreate([
                            'status_id' => $newStatus->id,
                            'row_id'    => $row,
                            'triwulan'  => $tw,
                        ], []);
                    }
                }
            }

            // Remove rows that are no longer selected
            Datacontent::whereIn('status_id', $statusList)
                ->whereNotIn('row_id', $validated['rows']['selected'])
                ->delete();

            // Save row order
            if (isset($validated['order']) && !empty($validated['order'])) {
                RowOrder::updateOrCreate(
                    ['sekunder_id' => $validated['datas']['id']],
                    ['orders' => implode(',', $validated['order'])]
                );
            }

            DB::commit();
            return response()->json(['message' => 'Berhasil update data']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => $th->getMessage(),
            ], 422);
        }
    }
}
