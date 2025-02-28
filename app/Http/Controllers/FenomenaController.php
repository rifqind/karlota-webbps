<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Fenomena;
use App\Models\FenomenaSet;
use App\Models\Region;
use App\Models\Sector;
use App\Models\Subsector;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FenomenaController extends Controller
{
    //
    public function entri()
    {
        $prefix = request()->route()->getPrefix();
        if ($prefix == 'lapus') $type = 'Lapangan Usaha';
        else if ($prefix == 'peng') $type = 'Pengeluaran';
        $regions = Region::getMyRegion();
        $subsectors = Subsector::where('type', $type)
            ->with(['sector.category'])
            ->get();
        $categoryId = Category::pluck('id');
        $subsectorId = Subsector::pluck('id');
        $sectorId = Sector::pluck('id');
        return Inertia::render('Fenomena/Entri', [
            'type' => $type,
            'subsectors' => $subsectors,
            'category_id' => $categoryId,
            'sector_id' => $sectorId,
            'subsector_id' => $subsectorId,
            'regions' => $regions
        ]);
    }

    public function show(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string'],
            'year' => ['required', 'integer'],
            'quarter' => ['required', 'integer'],
            'regions' => ['required', 'integer'],
        ]);
        $notification = [];
        $fenomena_set = FenomenaSet::where('type', $validated['type'])
            ->where('region_id', $validated['regions'])
            ->where('year', $validated['year'])
            ->where('quarter', $validated['quarter'])
            ->first();
        if ($fenomena_set) {
            $data = Fenomena::where('fenomena_sets', $fenomena_set->id)->get();
            $message = [
                'type' => 'success',
                'message' => 'Mengambil Data Fenomena Periode Ini'
            ];
            array_push($notification, $message);
        } else {
            $fenomena_set = FenomenaSet::create([
                'type' => $validated['type'],
                'region_id' => $validated['regions'],
                'year' => $validated['year'],
                'quarter' => $validated['quarter'],
                'status' => 'Entry',
            ]);
            $data = collect();
            $message = [
                'type' => 'success',
                'message' => 'Fenomena set baru dibuat'
            ];
            array_push($notification, $message);
        }
        return response()->json([
            'data' => $data,
            'fenomena_set' => $fenomena_set,
            'notification' => $notification
        ]);
    }

    public function monitoring() {}
}
