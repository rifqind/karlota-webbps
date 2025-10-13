<?php

namespace App\Http\Controllers\LK;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LkController extends Controller
{
    //
    public function dashboard()
    {
        return Inertia::render('LK/Home/Dashboard');
    }
}
