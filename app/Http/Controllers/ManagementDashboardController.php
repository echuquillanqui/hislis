<?php

namespace App\Http\Controllers;

use App\Services\ManagementDashboardService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManagementDashboardController extends Controller
{
    public function index(Request $request, ManagementDashboardService $service)
    {
        $metrics = $service->metrics($request->query('from'), $request->query('to'));

        return view('admin.dashboard.management', compact('metrics'));
    }

    public function export(Request $request, ManagementDashboardService $service): Response
    {
        $metrics = $service->metrics($request->query('from'), $request->query('to'));

        return response($service->exportCsv($metrics), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="dashboard-gerencial.csv"',
        ]);
    }
}
