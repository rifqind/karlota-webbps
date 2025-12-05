<?php

namespace App\Http\Controllers\Lk;

use App\Http\Controllers\Controller;
use App\Models\HargaDasar;
use App\Models\LK\IndeksHarga;
use App\Models\LK\Komoditas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class IndeksHargaController extends Controller
{
    //
    public function idxdasar(Request $request)
    {
        if ($request->paginated) $paginated = $request->paginated;
        else $paginated = 10;
        if ($request->currentPage) $currentPage = $request->currentPage;
        else $currentPage = 1;
        $number = 1;

        $query = HargaDasar::query();
        $dataToCounted = $query->join('komoditas as k', 'k.id', '=', 'master_harga.komoditas_id')
            ->select(['master_harga.*', 'k.label']);
        if ($request->orderAttribute) {
            $order = $request->orderAttribute;
            if (sizeof($order) > 2) $query->orderBy($order['label'], $order['value']);
            else {
                $query->orderBy('k.category_id', 'asc')
                    ->orderBy('k.sector_id', 'asc')
                    ->orderBy('k.subsector_id', 'asc');
            }
        } else {
            $query->orderBy('k.category_id', 'asc')
                ->orderBy('k.sector_id', 'asc')
                ->orderBy('k.subsector_id', 'asc');
        }
        if ($request->ArrayFilter) {
            $filter = $request->ArrayFilter;
            if (!empty($filter['label'])) {
                $query->where('k.label', 'like', '%' .  $filter['label'] . '%');
            }
            if (!empty($filter['subsector_id'])) {
                $query->where('k.subsector_id', '=',  $filter['subsector_id']);
            }
        }
        $countData = $dataToCounted->count();
        $ihd = $query->paginate($paginated, ['*'], 'page', $currentPage);
        foreach ($ihd as $key => $value) {
            # code...
            $value->number = $number;
            $number++;
        }
        if ($request->paginated) {
            return response()->json([
                'ihd' => $ihd,
                'countData' => $countData
            ]);
        }
        $komoditas = Komoditas::select(['id as value', 'label as label']);
        return Inertia::render('LK/IH/DasarIdx', [
            'ihd' => $ihd,
            'countData' => $countData,
            'komoditas' => $komoditas,
        ]);
    }

    public function index(Request $request)
    {
        if ($request->paginated) $paginated = $request->paginated;
        else $paginated = 10;
        if ($request->currentPage) $currentPage = $request->currentPage;
        else $currentPage = 1;
        $number = 1;

        $query = Komoditas::query();
        $dataToCounted = $query->leftJoin('indeks_harga as ih', 'ih.komoditas_id', '=', 'master_komoditas.id')
            ->selectRaw("
            master_komoditas.id,
            master_komoditas.label,
            master_komoditas.category_id,
            master_komoditas.sector_id,
            master_komoditas.subsector_id,
            ih.tahun,
            MAX(CASE WHEN ih.triwulan = 1 THEN ih.indeks_harga END) AS tw1,
            MAX(CASE WHEN ih.triwulan = 2 THEN ih.indeks_harga END) AS tw2,
            MAX(CASE WHEN ih.triwulan = 3 THEN ih.indeks_harga END) AS tw3,
            MAX(CASE WHEN ih.triwulan = 4 THEN ih.indeks_harga END) AS tw4
        ")
            ->groupBy(
                'master_komoditas.id',
                'master_komoditas.label',
                'master_komoditas.category_id',
                'master_komoditas.sector_id',
                'master_komoditas.subsector_id',
                'ih.tahun'
            );
        $query->orderBy('ih.tahun', 'desc');
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
            if (!empty($filter['year'])) {
                $query->whereIn('tahun',  $filter['year']);
            }
        }
        $countData = (clone $query)->get()->count();
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
        $tahun = IndeksHarga::select('tahun')
            ->distinct()
            ->orderBy('tahun')
            ->get()
            ->map(fn($i) => [
                'value' => $i->tahun,
                'label' => $i->tahun,
            ]);
        return Inertia::render('LK/IH/Index', [
            'countData' => $countData,
            'komoditas' => $komoditas,
            'tahun' => $tahun
        ]);
    }

    public function store(Request $request)
    {
        $notifications = [];
        $validated = $request->validate([
            'fileUpload' => [
                'required',
                'array',
                'min:1',
                function ($att, $r, $f) {
                    $err = [
                        'komoditas_required' => 0,
                        'komoditas_max'      => 0,
                        'tahun_required'     => 0,
                        'tahun_format'       => 0,
                        'tw1_decimal'        => 0,
                        'tw2_decimal'        => 0,
                        'tw3_decimal'        => 0,
                        'tw4_decimal'        => 0,
                    ];
                    foreach ($r as $row) {
                        // KOMODITAS
                        $komoditas = $row['komoditas'] ?? null;
                        if ($komoditas === null || trim($komoditas) === '') {
                            $err['komoditas_required']++;
                        } elseif (mb_strlen($komoditas) > 150) {
                            $err['komoditas_max']++;
                        }

                        // TAHUN
                        $tahun = $row['tahun'] ?? null;
                        if ($tahun === null || $tahun === '') {
                            $err['tahun_required']++;
                        } else {
                            // cek angka 4 digit
                            if (!ctype_digit((string) $tahun) || strlen((string) $tahun) !== 4) {
                                $err['tahun_format']++;
                            }
                        }

                        // TW1–TW4 (boleh kosong, tapi kalau diisi harus integer)
                        foreach (['tw1', 'tw2', 'tw3', 'tw4'] as $tw) {
                            $val = $row[$tw] ?? null;

                            if ($val === null || $val === '') {
                                continue; // boleh kosong
                            }

                            // if (filter_var($val, FILTER_VALIDATE_INT) === false) {
                            //     $err[$tw . '_integer']++;
                            // }
                            if (!is_numeric($val)) {
                                $err[$tw . '_decimal']++;
                            }
                        }
                    }
                    if ($err['komoditas_required'] > 0) {
                        $f($err['komoditas_required'] . ' data error, kolom komoditas tidak boleh kosong');
                    }
                    if ($err['komoditas_max'] > 0) {
                        $f($err['komoditas_max'] . ' data error, kolom komoditas melebihi 150 karakter');
                    }
                    if ($err['tahun_required'] > 0) {
                        $f($err['tahun_required'] . ' data error, kolom tahun tidak boleh kosong');
                    }
                    if ($err['tahun_format'] > 0) {
                        $f($err['tahun_format'] . ' data error, kolom tahun harus berupa angka 4 digit');
                    }
                    if ($err['tw1_decimal'] > 0) {
                        $f($err['tw1_decimal'] . ' data error, kolom TW1 harus diisi angka bulat');
                    }
                    if ($err['tw2_decimal'] > 0) {
                        $f($err['tw2_decimal'] . ' data error, kolom TW2 harus diisi angka bulat');
                    }
                    if ($err['tw3_decimal'] > 0) {
                        $f($err['tw3_decimal'] . ' data error, kolom TW3 harus diisi angka bulat');
                    }
                    if ($err['tw4_decimal'] > 0) {
                        $f($err['tw4_decimal'] . ' data error, kolom TW4 harus diisi angka bulat');
                    }
                }
            ],
        ], [
            'fileUpload.required' => 'Belum upload file',
            'fileUpload.min' => 'File harus setidaknya terisi 1 baris',
        ]);
        try {
            //code...
            DB::beginTransaction();
            $data = $validated['fileUpload'];
            // if (!empty($data)) IndeksHarga::insertOrIgnore($data);
            $payload = [];
            $labels = [];
            foreach ($data as $key => $v) {
                # code...
                if (!empty($v['komoditas'])) $labels[] = strtolower($v['komoditas']);
            }
            $labels = array_values(array_unique($labels));
            $komoditasMap = Komoditas::select('id', DB::raw('LOWER(label) as label_lower'))
                ->whereIn(DB::raw('LOWER(label)'), $labels)
                ->pluck('id', 'label_lower');
            $counted = 0;
            foreach ($data as $key => $v) {
                $labelLower = strtolower($v['komoditas'] ?? '');
                if (!$labelLower || !isset($komoditasMap[$labelLower])) {
                    $notifications[] = [
                        'type' => 'warning',
                        'message' => 'Komoditas ' . $v['komoditas'] . ' tidak ada di database'
                    ];
                    continue;
                }
                $komoditas_id = $komoditasMap[$labelLower];
                $twMap = [
                    'tw1' => 1,
                    'tw2' => 2,
                    'tw3' => 3,
                    'tw4' => 4,
                ];
                foreach ($twMap as $twkey => $tw) {
                    # code...
                    $val = $v[$twkey] ?? null;
                    if ($val === null || $val == '') {
                        $counted++;
                        continue;
                    }
                    $payload[] = [
                        'komoditas_id' => $komoditas_id,
                        'indeks_harga' => $v[$twkey],
                        'tahun' => $v['tahun'],
                        'triwulan' => $tw,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            if ($counted > 0) {
                $notifications[] = [
                    'type' => 'warning',
                    'message' => 'Ada sebanyak ' . $counted . ' data yang kosong'
                ];
            }
            if (!empty($payload)) {
                IndeksHarga::upsert($payload, ['komoditas_id', 'tahun', 'triwulan'], ['indeks_harga', 'updated_at']);
            }
            DB::commit();
            $notifications[] = [
                'type' => 'success',
                'message' => 'Berhasil update data',
            ];
            return back()->with(['notification' => $notifications]);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $notifications[] = [
                'type' => 'error',
                'message' => 'Error : ' . $th->getMessage(),
            ];
            return back()->withErrors(['notification' => $notifications]);
        }
    }
}
