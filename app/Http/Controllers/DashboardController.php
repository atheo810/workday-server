<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function privateDashboard()
    {
        return view('dashboard');
    }

    public function publicDashboard()
    {
        return view('public-dashboard');
    }
}
