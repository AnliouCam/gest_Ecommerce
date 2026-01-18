<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show dashboard based on user role.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isGerant()) {
            return view('dashboard.gerant');
        }

        return view('dashboard.vendeur');
    }
}
