<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    protected DashboardService $service;

    public function __construct(DashboardService $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        $statistics = $this->service->getStatistics();
        
        return response()->json($statistics);
    }

    public function exportMaterials()
    {
        $csv = $this->service->exportMaterialsToCSV();
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="materialy-' . now()->format('Y-m-d') . '.csv"');
    }
}
