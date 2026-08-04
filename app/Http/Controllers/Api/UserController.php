<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * List users with filtering, sorting, and pagination.
     * GET /api/user/index
     */
    public function index(Request $request): JsonResponse
    {
        $paginated   = $request->paginated   ? (int) $request->paginated   : 10;
        $currentPage = $request->currentPage ? (int) $request->currentPage : 1;

        $query = User::query()
            ->join('regions as r', 'r.satker_id', '=', 'users.satker_id')
            ->select(['users.id', 'users.name', 'users.email', 'users.satker_id', 'users.role', 'users.nip_lama', 'r.name as satker']);

        // Sorting
        $order = $request->orderAttribute;
        if (is_string($order)) {
            $order = json_decode($order, true);
        }

        if (is_array($order) && !empty($order['label']) && !empty($order['value'])) {
            $col = $order['label'] === 'satker' ? 'r.name' : 'users.' . $order['label'];
            $query->orderBy($col, $order['value']);
        } else {
            $query->orderBy('users.satker_id', 'asc');
        }

        // Filters
        $filter = $request->ArrayFilter;
        if (is_string($filter)) {
            $filter = json_decode($filter, true);
        }

        if (is_array($filter)) {
            if (!empty($filter['name'])) {
                $query->where('users.name', 'like', '%' . $filter['name'] . '%');
            }
            if (!empty($filter['email'])) {
                $query->where('users.email', 'like', '%' . $filter['email'] . '%');
            }
            if (!empty($filter['satker'])) {
                $query->where('r.name', 'like', '%' . $filter['satker'] . '%');
            }
            if (!empty($filter['role'])) {
                $query->where('users.role', 'like', '%' . $filter['role'] . '%');
            }
        }

        $countData = (clone $query)->count();
        $data = $query->paginate($paginated, ['*'], 'page', $currentPage);

        return response()->json([
            'user'      => $data,
            'countData' => $countData,
        ]);
    }

    /**
     * Fetch a single user by ID.
     * GET /api/user/fetch/{id}
     */
    public function fetch(string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Pengguna tidak ditemukan.'], 404);
        }

        return response()->json(['data' => $user]);
    }

    /**
     * Create or update a user.
     * POST /api/user/store
     */
    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $baseRules = [
                'name'      => ['required', 'string', 'regex:/^\S*$/u'],
                'satker_id' => ['required', 'integer'],
                'nip_lama'  => ['sometimes', 'nullable', 'max:9'],
                'email'     => ['required', 'string', 'lowercase', 'email', 'max:255'],
                'role'      => ['required', 'string'],
            ];

            $validated = $request->validate($baseRules);

            if ($request->id) {
                // --- UPDATE ---
                $request->validate([
                    'name'     => [Rule::unique('users', 'name')->ignore($request->id)],
                    'nip_lama' => ['nullable', Rule::unique('users', 'nip_lama')->ignore($request->id)],
                    'email'    => [
                        'required', 'string', 'lowercase', 'email', 'max:255',
                        Rule::unique('users', 'email')->ignore($request->id),
                    ],
                ]);

                $user = User::findOrFail($request->id);

                $updateData = [
                    'name'      => $validated['name'],
                    'email'     => $validated['email'],
                    'nip_lama'  => $validated['nip_lama'] ?? null,
                    'satker_id' => $validated['satker_id'],
                    'role'      => $validated['role'],
                ];

                if ($request->password) {
                    $request->validate([
                        'password' => ['confirmed', Rules\Password::defaults()],
                    ]);
                    $updateData['password'] = Hash::make($request->password);
                }

                $user->update($updateData);
                DB::commit();

                return response()->json([
                    'message' => 'Berhasil mengedit pengguna.',
                    'data'    => $user->fresh(),
                ]);
            } else {
                // --- CREATE ---
                $request->validate([
                    'name'     => [Rule::unique('users', 'name')],
                    'email'    => [Rule::unique('users', 'email')],
                    'nip_lama' => ['nullable', Rule::unique('users', 'nip_lama')],
                    'password' => ['required', 'confirmed', Rules\Password::defaults()],
                ]);

                $user = User::create([
                    'name'      => $validated['name'],
                    'email'     => $validated['email'],
                    'nip_lama'  => $validated['nip_lama'] ?? null,
                    'password'  => Hash::make($request->password),
                    'role'      => $validated['role'],
                    'satker_id' => $validated['satker_id'],
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Berhasil menambah pengguna baru.',
                    'data'    => $user,
                ], 201);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validasi gagal.',
                'error'   => $e->errors(),
            ], 422);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Ada kesalahan di server.',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a user by ID.
     * DELETE /api/user/destroy/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            DB::beginTransaction();
            $user = User::findOrFail($id);
            $user->delete();
            DB::commit();

            return response()->json(['message' => 'Berhasil menghapus pengguna.']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Ada kesalahan ketika menghapus pengguna.',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Return list of satker / BPS (regions) for dropdown.
     * GET /api/user/satker
     */
    public function satker(): JsonResponse
    {
        // All distinct satker grouped from regions table
        $bps = Region::selectRaw('MIN(id) as id, satker_id')
            ->groupBy('satker_id')
            ->orderBy('id')
            ->pluck('id');

        $result = Region::whereIn('id', $bps)
            ->select(['satker_id as value', 'name as label'])
            ->get();

        return response()->json($result);
    }
}
