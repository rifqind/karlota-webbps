<?php

namespace App\Http\Controllers\LK;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Komoditas;
use App\Models\Subsector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class KomoditasController extends Controller
{
    //
    public function index(Request $request)
    {
        if ($request->paginated) $paginated = $request->paginated;
        else $paginated = 10;
        if ($request->currentPage) $currentPage = $request->currentPage;
        else $currentPage = 1;
        $number = 1;

        $query = Komoditas::query();
        $dataToCounted = $query->join('subsectors as s', 's.id', '=', 'master_komoditas.subsector_id')
            ->join('users as u', 'u.id', '=', 'master_komoditas.edited_by')
            ->select([
                'master_komoditas.*',
                's.name as subsector_label',
                'u.name as username',
                'master_komoditas.updated_at as updated_time'
            ]);
        if ($request->orderAttribute) {
            $order = $request->orderAttribute;
            if (sizeof($order) > 2) $query->orderBy($order['label'], $order['value']);
            else {
                $query->orderBy('category_id', 'asc')
                    ->orderBy('sector_id', 'asc')
                    ->orderBy('subsector_id', 'asc');
            }
        } else {
            $query->orderBy('category_id', 'asc')
                ->orderBy('sector_id', 'asc')
                ->orderBy('subsector_id', 'asc');
        }
        if ($request->ArrayFilter) {
            $filter = $request->ArrayFilter;
            if (!empty($filter['label'])) {
                $query->where('label', 'like', '%' .  $filter['label'] . '%');
            }
            if (!empty($filter['code'])) {
                $query->where('code', 'like', '%' . $filter['code'] . '%');
            }
            if (!empty($filter['type'])) {
                $query->where('type', 'like', '%' . $filter['type'] . '%');
            }
            if (!empty($filter['satuan'])) {
                $query->where('satuan', 'like', '%' . $filter['satuan'] . '%');
            }
        }

        $countData = $dataToCounted->count();
        $komoditas = $query->paginate($paginated, ['*'], 'page', $currentPage);
        foreach ($komoditas as $key => $value) {
            # code...
            $value->number = $number;
            $number++;
        }
        if ($request->paginated) {
            return response()->json([
                'sekunder' => $komoditas,
                'countData' => $countData
            ]);
        }
        $subsector = Subsector::select(['id as value', 'name as label'])->get();

        return Inertia::render('LK/Komoditas/Index', [
            'komoditas' => $komoditas,
            'countData' => $countData,
            'subsector' => $subsector
        ]);
    }

    public function store(Request $request)
    {
        $notifications = [];
        try {
            //code...
            if ($request->mode == 1) {
                //manual
                DB::beginTransaction();
                $validated = $request->validate([
                    'manual.label' => 'required|string|max:255',
                    'manual.code' => 'nullable|sometimes|string|max:100|unique:master_komoditas,code',
                    'manual.satuan' => 'required|string|max:50',
                    'manual.type' => 'required|integer|in:1,2',
                    'manual.subsector_id' => 'required|exists:subsectors,id',
                ]);
                $sub = Subsector::with('sector.category')->findOrFail($validated['manual']['subsector_id']);
                if (empty($validated['manual']['code'])) {
                    // Bangun prefix (tanpa underscore, agar sort lexicographical rapi)
                    $prefix =
                        (string) ($sub->sector->category->code ?? '') .
                        (string) ($sub->sector->code ?? '') .
                        (string) ($sub->code ?? '');

                    // Ambil terakhir berdasarkan prefix yang sama, KUNCI baris untuk update
                    $last = Komoditas::where('subsector_id', $sub->id)
                        ->where('code', 'like', $prefix . '%')
                        ->lockForUpdate()
                        ->orderByDesc('code')
                        ->first();

                    $seq = 1;
                    if ($last && preg_match('/(\d{3})$/', (string) $last->code, $m)) {
                        $seq = ((int) $m[1]) + 1;
                    }

                    $validated['manual']['code'] = $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
                }
                $payload = [
                    'label'        => $validated['manual']['label'],
                    'code'         => $validated['manual']['code'],
                    'satuan'       => $validated['manual']['satuan'],
                    'type'         => $validated['manual']['type'],
                    'subsector_id' => $validated['manual']['subsector_id'],
                    'sector_id'    => $sub->sector->id,
                    'category_id'  => $sub->sector->category->id,
                    'edited_by'    => Auth::user()->id,
                ];
                $new_komoditas = Komoditas::create($payload);
                $message = [
                    'type' => 'success',
                    'message' => 'Komoditas berhasil ditambahkan.'
                ];
                array_push($notifications, $message);
                DB::commit();
                return back()->with('notifications', $notifications);
            } else if ($request->mode == 2) {
                //import excel
                // dd($request->rows);
                $validated = $request->validate([
                    'rows' => 'required|array|min:1',
                    'rows.*.label' => 'required|string|max:255',
                    'rows.*.code' => 'nullable|sometimes|string|max:100|distinct|unique:master_komoditas,code',
                    'rows.*.satuan' => 'required|string|max:50',
                    'rows.*.type' => 'required|tinyint',
                    'rows.*.subsector_id' => 'required|exists:subsectors,id',
                ]);
                $rows = $validated['rows'];
                $subsectorIds = collect($rows)->pluck('subsector_id')->unique()->values();
                $subsectors = Subsector::with('sector.category')
                    ->whereIn('id', $subsectorIds)
                    ->get()
                    ->keyBy('id');
                $now = now();
                $payloads = collect($rows)->map(function ($r) use ($subsectors, $now) {
                    $sub = $subsectors[$r['subsector_id']];
                    return [
                        'label'        => $r['label'],
                        'code'         => $r['code'],
                        'satuan'       => $r['satuan'],
                        'type'         => $r['type'],
                        'subsector_id' => $r['subsector_id'],
                        'sector_id'    => $sub->sector->id,
                        'category_id'  => $sub->sector->category->id,
                        'edited_by'    => Auth::id(),
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ];
                })->all();
                DB::beginTransaction();
                Komoditas::insert($payloads);
                $message = [
                    'type' => 'success',
                    'message' => 'Komoditas berhasil diimport.'
                ];
                array_push($notifications, $message);
                DB::commit();
                return back()->with('notifications', $notifications);
            }
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Terjadi kesalahan saat menambahkan komoditas.',
                'error' => $th->getMessage(),
            ];
            array_push($notifications, $message);
            return back()->withErrors(['notifications' => $notifications]);
        }
    }
}
