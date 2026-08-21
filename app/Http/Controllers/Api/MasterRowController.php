<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Row;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MasterRowController extends Controller
{
    /**
     * List rows with filtering, sorting, and pagination.
     * GET /api/master/rows/index
     */
    public function index(Request $request): JsonResponse
    {
        $paginated = $request->paginated ? (int) $request->paginated : 10;
        $currentPage = $request->currentPage ? (int) $request->currentPage : 1;

        $query = Row::query()->select(['rows.*']);

        // Sorting
        $order = $request->orderAttribute;
        if (is_string($order)) {
            $order = json_decode($order, true);
        }

        if (is_array($order) && !empty($order['label']) && !empty($order['value'])) {
            $query->orderBy($order['label'], $order['value']);
        } else {
            $query->orderBy('label');
        }

        // Filters
        $filter = $request->ArrayFilter;
        if (is_string($filter)) {
            $filter = json_decode($filter, true);
        }

        if (is_array($filter)) {
            if (!empty($filter['nama'])) {
                $query->where('label', 'like', '%' . $filter['nama'] . '%');
            }
        }

        $countData = (clone $query)->count();
        $rows = $query->paginate($paginated, ['*'], 'page', $currentPage);

        $number = ($currentPage - 1) * $paginated + 1;
        foreach ($rows as $din) {
            $din->number = $number;
            $number++;
        }

        return response()->json([
            'row' => $rows,
            'countData' => $countData,
        ]);
    }

    /**
     * Fetch a single row by ID.
     * GET /api/master/rows/fetch/{id}
     */
    public function fetch(string $id): JsonResponse
    {
        $data = Row::find($id);
        return response()->json(['data' => $data]);
    }

    /**
     * Create or update a row. Also supports file upload (batch insert).
     * POST /api/master/rows/store
     */
    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            if ($request->id) {
                // Update existing row
                $validated = $request->validate([
                    'label' => [
                        'required',
                        'string',
                        'max:100',
                        Rule::unique('rows', 'label')->ignore($request->id),
                    ],
                ]);
                $row = Row::findOrFail($request->id);
                $row->update($validated);

                DB::commit();
                return response()->json(['message' => 'Berhasil mengedit Row']);
            } elseif ($request->fileUpload) {
                // Batch insert from file upload
                $fileData = $request->fileUpload;
                if (!is_array($fileData) || !isset($fileData[0][0]) || $fileData[0][0] != 'label') {
                    DB::rollBack();
                    return response()->json([
                        'error' => 'Format file tidak sesuai. Kolom header harus "label".',
                    ], 422);
                }

                $inserted = 0;
                foreach ($fileData as $key => $value) {
                    if ($key === 0) continue; // skip header
                    if (is_array($value) && count($value) > 0) {
                        $label = trim($value[0] ?? '');
                        if ($label === '') {
                            DB::rollBack();
                            return response()->json([
                                'error' => 'Label tidak boleh kosong.',
                            ], 422);
                        }
                        $exists = Row::whereRaw('LOWER(label) = ?', [strtolower($label)])->exists();
                        if ($exists) {
                            DB::rollBack();
                            return response()->json([
                                'error' => "Label '{$label}' sudah ada.",
                            ], 422);
                        }
                        Row::create(['label' => $label]);
                        $inserted++;
                    }
                }

                DB::commit();
                return response()->json([
                    'message' => "Berhasil menambahkan {$inserted} Row baru",
                ]);
            } else {
                // Create single row
                $validated = $request->validate([
                    'label' => [
                        'required',
                        'string',
                        'max:100',
                        Rule::unique('rows', 'label'),
                    ],
                ]);
                Row::create(['label' => $validated['label']]);

                DB::commit();
                return response()->json(['message' => 'Berhasil menambahkan Row baru']);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => $th->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete a row.
     * DELETE /api/master/rows/delete/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            DB::beginTransaction();
            $data = Row::findOrFail($id);
            $data->delete();
            DB::commit();
            return response()->json(['message' => 'Berhasil menghapus Row']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => $th->getMessage(),
            ], 422);
        }
    }
}
