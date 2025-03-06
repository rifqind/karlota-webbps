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
        $active_periode = Period::where('status', 'Aktif')->get();
        return Inertia::render('Dashboard', [
            'data' => $active_periode
        ]);
    }
}
