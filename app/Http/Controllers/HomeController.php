<?php

namespace App\Http\Controllers;

use App\Models\Period;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    //
    public function index()
    {
        $active_periode_lapus = Period::where('type', 'Lapangan Usaha')
            ->where('status', 'Aktif')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->select(['type', 'description'])
            ->get();
        $active_periode_peng = Period::where('type', 'Pengeluaran')
            ->where('status', 'Aktif')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->select(['type', 'description'])
            ->get();
        return Inertia::render('Dashboard', [
            'lapus' => $active_periode_lapus,
            'peng' => $active_periode_peng,
        ]);
    }
}
