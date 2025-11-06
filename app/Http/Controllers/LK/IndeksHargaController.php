<?php

namespace App\Http\Controllers\Lk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndeksHargaController extends Controller
{
    //
    public function idxdasar(Request $request) {
        return Inertia::render('LK/IH/DasarIdx');
    }
}
