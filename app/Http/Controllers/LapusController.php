<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class LapusController extends Controller
{
    //
    public function entri()
    {
        return Inertia::render('Lapus/Entri');
    }
}
