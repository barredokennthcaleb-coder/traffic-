<?php

namespace App\Controllers;

use App\Models\ViolationModel;
use CodeIgniter\Controller;

class AdminController extends BaseController
{
    public function index()
    {
        $violationModel = new ViolationModel();
        $data = [
            'total_violations' => $violationModel->countAll(),
            'pending_penalties' => $violationModel->where('status', 'Pending')->countAllResults(),
            'paid_penalties' => $violationModel->where('status', 'Paid')->countAllResults(),
            'total_revenue' => $violationModel->selectSum('penalty_amount')->where('status', 'Paid')->first()['penalty_amount'] ?? 0,
            'recent_violations' => $violationModel->orderBy('violation_date', 'DESC')->limit(5)->find(),
        ];
        return view('admin/dashboard', $data);
    }

    public function analytics()
    {
        $violationModel = new ViolationModel();
        
        // Data for charts
        $statusCounts = $violationModel->select('status, COUNT(*) as count')->groupBy('status')->findAll();
        $typeCounts = $violationModel->select('violation_type, COUNT(*) as count')->groupBy('violation_type')->findAll();
        
        $data = [
            'status_counts' => $statusCounts,
            'type_counts' => $typeCounts,
        ];
        
        return view('admin/analytics', $data);
    }

    public function about()
    {
        return view('admin/about');
    }
}
