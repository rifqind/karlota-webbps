<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produsen;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProdusenController extends Controller
{
    /**
     * List produsen (dinas) with filtering, sorting, and pagination.
     * GET /api/produsen/index
     */
    public function index(Request $request): JsonResponse
    {
        $paginated = $request->paginated ? (int) $request->paginated : 10;
        $currentPage = $request->currentPage ? (int) $request->currentPage : 1;

        $wilayah = Region::getMyRegionId();

        $query = Produsen::query()
            ->leftJoin('regions as r', 'r.id', '=', 'produsen.region_id')
            ->whereIn('r.id', $wilayah)
            ->select(['produsen.*', 'r.name as region_name']);

        // Sorting
        $order = $request->orderAttribute;
        if (is_string($order)) {
            $order = json_decode($order, true);
        }

        if (is_array($order) && !empty($order['label']) && !empty($order['value'])) {
            $query->orderBy($order['label'], $order['value']);
        } else {
            $query->orderBy('region_id')->orderBy('nama');
        }

        // Filters
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
        }

        $countData = (clone $query)->count();
        $produsen = $query->paginate($paginated, ['*'], 'page', $currentPage);

        $number = ($currentPage - 1) * $paginated + 1;
        foreach ($produsen as $din) {
            $din->number = $number;
            $number++;
        }

        return response()->json([
            'produsen' => $produsen,
            'countData' => $countData,
        ]);
    }

    /**
     * Fetch a single produsen by ID.
     * GET /api/produsen/fetch/{id}
     */
    public function fetch(string $id): JsonResponse
    {
        $data = Produsen::find($id);
        return response()->json(['data' => $data]);
    }

    /**
     * Create or update a produsen.
     * POST /api/produsen/store
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'region_id' => ['required', 'integer'],
        ]);

        try {
            DB::beginTransaction();

            if ($request->id) {
                $request->validate([
                    'nama' => [Rule::unique('produsen', 'nama')->ignore($request->id)],
                ]);
                $produsen = Produsen::findOrFail($request->id);
                $produsen->update($validated);
                $message = 'Berhasil mengedit dinas';
            } else {
                $request->validate([
                    'nama' => [Rule::unique('produsen', 'nama')],
                ]);
                Produsen::create([
                    'nama' => $validated['nama'],
                    'region_id' => $validated['region_id'],
                ]);
                $message = 'Berhasil menambahkan dinas baru';
            }

            DB::commit();
            return response()->json(['message' => $message], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => $th->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete a produsen.
     * DELETE /api/produsen/delete/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            DB::beginTransaction();
            $data = Produsen::findOrFail($id);
            $data->delete();
            DB::commit();
            return response()->json(['message' => 'Berhasil menghapus dinas']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => $th->getMessage(),
            ], 422);
        }
    }

    /**
     * Get list of wilayah (regions) for dropdown.
     * GET /api/produsen/wilayah
     */
    public function wilayah(): JsonResponse
    {
        $wilayah = Region::getMyRegionId();
        $data = Region::whereIn('id', $wilayah)
            ->select(['id as value', 'name as label'])
            ->get();
        return response()->json($data);
    }
}
