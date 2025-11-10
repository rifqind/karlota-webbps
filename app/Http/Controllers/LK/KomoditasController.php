<?php

namespace App\Http\Controllers\LK;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HargaDasar;
use App\Models\LK\Komoditas;
use App\Models\Subsector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
            ->leftJoin('master_harga as mh', 'mh.komoditas_id', '=', 'master_komoditas.id')
            ->select([
                'master_komoditas.*',
                's.name as subsector_label',
                'u.name as username',
                'mh.harga_konstan as harga_konstan',
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
                $query->where('master_komoditas.code', 'like', '%' . $filter['code'] . '%');
            }
            if (!empty($filter['satuan'])) {
                $query->where('satuan', 'like', '%' . $filter['satuan'] . '%');
            }
            if (!empty($filter['subsector_label'])) {
                $query->where('s.id', '=',  $filter['subsector_label']);
            }
            if (!empty($filter['subsector_updatedAt'])) {
                $query->where(DB::raw("CONCAT(u.name, ' - ', master_komoditas.updated_at)"), 'like', '%' . $filter['subsector_updatedAt'] . '%');
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
                'komoditas' => $komoditas,
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
                    'manual.harga_dasar' => 'nullable|sometimes|numeric',
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
                $labelWithSatuanandSubId = Komoditas::where('label', $validated['manual']['label'])
                    ->where('satuan', $validated['manual']['satuan'])
                    ->where('subsector_id', $validated['manual']['subsector_id'])
                    // ->whereNot('id', $request->id)
                    ->first();
                if ($labelWithSatuanandSubId) throw new \Exception('Komoditas sudah ada');
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
                if (!empty($validated['harga_dasar'])) {
                    $new_harga_dasar = HargaDasar::create([
                        'komoditas_id' => $new_komoditas->id,
                        'harga_konstan' => $validated['harga_dasar']
                    ]);
                }
                $message = [
                    'type' => 'success',
                    'message' => 'Komoditas berhasil ditambahkan.'
                ];
                array_push($notifications, $message);
                DB::commit();
                return back()->with('notifications', $notifications);
            } else if ($request->mode == 2) {
                // import excel
                $validated = $request->validate([
                    'rows' => 'required|array|min:1',
                    'rows.*.label' => 'required|string|max:255',
                    'rows.*.code' => 'nullable|sometimes|string|max:100|distinct|unique:master_komoditas,code',
                    'rows.*.satuan' => 'required|string|max:50',
                    'rows.*.type' => 'required|integer|in:1,2',
                    'rows.*.subsector_id' => 'required|exists:subsectors,id',
                    'rows.*.harga_dasar' => 'nullable|sometimes|numeric'
                ]);

                $rows = $validated['rows'];

                // Ambil subsector + relasi buat prefix
                $subsectorIds = collect($rows)->pluck('subsector_id')->unique()->values();
                $subsectors = Subsector::with('sector.category')
                    ->whereIn('id', $subsectorIds)->get()->keyBy('id');

                // Prefix per subsector
                $prefixMap = [];
                foreach ($subsectors as $sid => $sub) {
                    $prefixMap[$sid] =
                        (string) ($sub->sector->category->code ?? '') .
                        (string) ($sub->sector->code ?? '') .
                        (string) ($sub->code ?? '');
                }

                DB::beginTransaction();

                // Kunci subsector & siapkan next sequence per subsector
                $existingCodes = Komoditas::whereIn('subsector_id', $subsectorIds)
                    ->pluck('code', 'code')
                    ->keys()
                    ->toArray();
                $existingSet = array_flip($existingCodes);

                // foreach ($subsectorIds as $sid) {
                //     $prefix = $prefixMap[$sid] ?? '';
                //     $lastCode = Komoditas::where('subsector_id', $sid)
                //         ->when($prefix !== '', fn($q) => $q->where('code', 'like', $prefix . '%'))
                //         ->lockForUpdate()
                //         ->orderByDesc('code')
                //         ->value('code');

                //     $seq = 1;
                //     if ($lastCode && preg_match('/(\d{3})$/', (string) $lastCode, $m)) {
                //         $seq = ((int) $m[1]) + 1;
                //     }
                //     $nextSeq[$sid] = $seq;
                // }

                // Ambil semua code yang DIISI di file, untuk cek duplikat di DB (skip)
                $providedCodes = collect($rows)->pluck('code')->filter()->values();
                $existingProvided = $providedCodes->isNotEmpty()
                    ? Komoditas::whereIn('code', $providedCodes)->pluck('code')->flip()->all()
                    : [];

                $nextSeq = [];
                foreach ($subsectorIds as $sid) {
                    $prefix = $prefixMap[$sid] ?? '';

                    // Ambil semua kode dengan prefix ini
                    $codes = Komoditas::where('subsector_id', $sid)
                        ->where('code', 'like', $prefix . '%')
                        ->lockForUpdate()
                        ->pluck('code');

                    // Ambil semua suffix angka yang sudah dipakai (contoh: 001, 002, 004 → [1,2,4])
                    $suffixes = [];
                    foreach ($codes as $c) {
                        if (preg_match('/(\d{3})$/', $c, $m)) {
                            $suffixes[(int) $m[1]] = true;
                        }
                    }
                    $usedSeq[$sid] = $suffixes;
                    // Mulai dari 1 (akan dicari yang kosong di loop)
                    $nextSeq[$sid] = 1;
                }

                $now = now();
                $payloads = [];
                $codesInPayload = []; // cegah duplikat dalam batch
                $skipped = [];        // simpan info baris yang diskip

                foreach ($rows as $idx => $r) {
                    $sid = $r['subsector_id'];
                    $sub = $subsectors[$sid] ?? null;
                    if (!$sub) {
                        $skipped[] = [
                            'row' => $idx + 1,
                            'reason' => 'Subsector tidak ditemukan',
                            'label' => $r['label'] ?? null,
                            'code'  => $r['code'] ?? null,
                        ];
                        continue;
                    }

                    $code = $r['code'] ?? null;

                    // Jika code DIISI dan SUDAH ADA di DB, SKIP baris ini
                    if (!empty($code) && isset($existingProvided[$code])) {
                        $skipped[] = [
                            'row' => $idx + 1,
                            'reason' => 'Kode sudah ada di database',
                            'label' => $r['label'] ?? null,
                            'code'  => $code,
                        ];
                        continue;
                    }

                    $labelWithSatuanandSubId = Komoditas::where('label', $r['label'])
                        ->where('satuan', $r['satuan'])
                        ->where('subsector_id', $sid)
                        ->first();

                    if ($labelWithSatuanandSubId) {
                        $skipped[] = [
                            'row' => $idx + 1,
                            'reason' => 'Komoditas dengan label, satuan, dan subsector yang sama sudah ada di database',
                            'label' => $r['label'] ?? null,
                            'code'  => $code,
                        ];
                        continue;
                    }

                    // Jika code kosong -> generate
                    if (empty($code)) {
                        $prefix = $prefixMap[$sid] ?? '';
                        for ($i = $nextSeq[$sid]; $i <= 999; $i++) {
                            if (
                                empty($usedSeq[$sid][$i]) &&
                                !isset($codesInPayload[$prefix . str_pad($i, 3, '0', STR_PAD_LEFT)]) &&
                                !isset($existingSet[$prefix . str_pad($i, 3, '0', STR_PAD_LEFT)])
                            ) {
                                $code = $prefix . str_pad($i, 3, '0', STR_PAD_LEFT);
                                $usedSeq[$sid][$i] = true;
                                $nextSeq[$sid] = $i + 1; // increment setelah dipakai
                                break;
                            }
                        }
                    } else {
                        // Cegah duplikat dalam file yang sama (selain validator distinct)
                        if (isset($codesInPayload[$code])) {
                            $skipped[] = [
                                'row' => $idx + 1,
                                'reason' => 'Kode duplikat di file import',
                                'label' => $r['label'] ?? null,
                                'code'  => $code,
                            ];
                            continue;
                        }
                    }

                    $codesInPayload[$code] = true;

                    $payloads[] = [
                        'label'        => $r['label'],
                        'code'         => $code,
                        'satuan'       => $r['satuan'],
                        'type'         => $r['type'],
                        'subsector_id' => $sid,
                        'sector_id'    => $sub->sector->id,
                        'category_id'  => $sub->sector->category->id,
                        'edited_by'    => Auth::id(),
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ];
                }

                // Insert yang lolos. Pakai insertOrIgnore agar kalau ada race kecil, baris konflik di-skip oleh DB.
                // (pastikan ada UNIQUE index di kolom code)
                Komoditas::insertOrIgnore($payloads);

                $message = [
                    'type' => 'success',
                    'message' => 'Import selesai. ' .
                        'Berhasil diproses: ' . count($payloads) . ' baris. ' .
                        'Diskip: ' . count($skipped) . ' baris.'
                ];
                array_push($notifications, $message);

                // Optional: kalau mau tampilkan daftar singkat baris yang diskip
                if (!empty($skipped)) {
                    $detail = collect($skipped)->map(function ($s) {
                        return "{$s['label']}  - sudah ada di database";
                    })->take(10)->implode('; ');
                    // $message['detail'] = $detail . (count($skipped) > 10 ? ' (+ lainnya)' : '');
                    $message = [
                        'type' => 'info',
                        'message' => $detail . (count($skipped) > 10 ? ' (+ lainnya)' : '')
                    ];
                    array_push($notifications, $message);
                }
                DB::commit();
                return back()->with('notification', $notifications);
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
            return back()->withErrors(['notification' => $notifications]);
        }
    }

    public function update(Request $request, $id = null)
    {
        //
        $id = $id ?? $request->id;
        if ($request->isMethod('post')) {
            $notifications = [];
            try {
                //code...
                $validated = $request->validate([
                    'label' => ['required', 'string', 'max:255'],
                    'code'  => [
                        'nullable',
                        'sometimes',
                        'string',
                        'max:100',
                        Rule::unique('master_komoditas', 'code')->ignore($id),
                    ],
                    'satuan'       => ['required', 'string', 'max:50'],
                    'type'         => ['required', 'integer', 'in:1,2'],
                    'subsector_id' => ['required', 'exists:subsectors,id'],
                ]);
                DB::beginTransaction();
                $sub = Subsector::with('sector.category')->findOrFail($validated['subsector_id']);
                if (empty($validated['code'])) {
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

                    $validated['code'] = $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
                }
                $payload = [
                    'label'        => $validated['label'],
                    'code'         => $validated['code'],
                    'satuan'       => $validated['satuan'],
                    'type'         => $validated['type'],
                    'subsector_id' => $validated['subsector_id'],
                    'sector_id'    => $sub->sector->id,
                    'category_id'  => $sub->sector->category->id,
                    'edited_by'    => Auth::user()->id,
                ];
                $labelWithSatuanandSubId = Komoditas::where('label', $validated['label'])
                    ->where('satuan', $validated['satuan'])
                    ->where('subsector_id', $validated['subsector_id'])
                    ->whereNot('id', $request->id)
                    ->first();
                if ($labelWithSatuanandSubId) throw new \Exception('Komoditas sudah ada');
                $update_komoditas = Komoditas::where('id', $request->id)
                    ->update($payload);
                $message = [
                    'type' => 'success',
                    'message' => 'Komoditas berhasil diedit.'
                ];
                array_push($notifications, $message);
                DB::commit();
                return back()->with('notification', $notifications);
            } catch (\Throwable $th) {
                //throw $th;
                DB::rollBack();
                $message = [
                    'type' => 'error',
                    'message' => 'Ada kesalahan : ' . $th->getMessage(),
                ];
                array_push($notifications, $message);
                return back()->withErrors(['notification' => $notifications]);
            } catch (ValidationException $ex) {
            }
        }
        $this_komoditas = Komoditas::findOrFail($id);
        return response()->json([
            'this_komoditas' => $this_komoditas
        ]);
    }

    public function destroy($id)
    {
        $notifications = [];
        try {
            DB::beginTransaction();
            $komoditas = Komoditas::find($id);
            if (!$komoditas) {
                throw new \Exception('Komoditas tidak ditemukan.');
            }
            $komoditas->delete();
            $notifications[] = [
                'type' => 'success',
                'message' => 'Komoditas berhasil dihapus.',
            ];
            DB::commit();
            return back()->with('notification', $notifications);
        } catch (\Throwable $th) {
            DB::rollBack();
            $notifications[] = [
                'type' => 'error',
                'message' => 'Ada kesalahan: ' . $th->getMessage(),
            ];
            return back()->withErrors(['notification' => $notifications]);
        }
    }
}
