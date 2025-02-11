<?php

namespace App\Http\Controllers;

use App\Models\Period;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PeriodController extends Controller
{
    //
    public function index(Request $request)
    {
        if ($request->paginated) $paginated = $request->paginated;
        else $paginated = 10;
        if ($request->currentPage) $currentPage = $request->currentPage;
        else $currentPage = 1;

        $query = Period::query();
        $number = 1;
        $dataToCounted = $query
            ->orderBy('created_at', 'desc')
            ->select('*');
        $countData = $dataToCounted->count();
        $data = $query->paginate($paginated, ['*'], 'page', $currentPage);
        foreach ($data as $key => $value) {
            # code...
            $value->number = $number;
            $number++;
        }
        return Inertia::render('Period/Index', [
            'data' => $data,
            'countData' => $countData
        ]);
    }
}
