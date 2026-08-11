<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    /**
     * Display the dashboard with stats and activity log data.
     */
    public function index()
    {
        $data = $this->dashboardService->getDashboardData(Auth::user());

        return Inertia::render('Dashboard', $data);
    }
}
