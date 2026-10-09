<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
    ) {}

    /**
     * Dashboard with live KPIs and recent activity.
     */
    public function index(): View
    {
        $this->authorize('dashboard.view');

        return view('dashboard', [
            'totalStockValue' => $this->dashboardService->getTotalStockValue(),
            'todaysSales' => $this->dashboardService->getTodaysSalesTotal(),
            'lowStockCount' => $this->dashboardService->getLowStockCount(),
            'outstandingReceivables' => $this->dashboardService->getOutstandingReceivables(),
            'recentSales' => $this->dashboardService->getRecentSales(),
            'lowStockItems' => $this->dashboardService->getLowStockItems(),
        ]);
    }
}
