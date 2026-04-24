<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AnalyticModel;
use App\Models\ViolationModel;

class AdminController extends BaseController
{
    public function index()
    {
        $analyticModel = new AnalyticModel();
        $violationModel = new ViolationModel();
        
        $stats = $analyticModel->getDashboardStats();
        $data = array_merge($stats, [
            'recent_violations' => $violationModel->orderBy('violation_date', 'DESC')->limit(5)->find(),
        ]);
        
        return view('admin/dashboard', $data);
    }

    public function analytics()
    {
        $analyticModel = new AnalyticModel();
        
        $data = [
            'status_counts' => $analyticModel->getStatusCounts(),
            'type_counts'   => $analyticModel->getTypeCounts(),
        ];
        
        return view('admin/analytics', $data);
    }

    public function about()
    {
        return view('admin/about');
    }
}
