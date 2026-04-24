<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ViolationRecord;

class AdminController extends BaseController
{
    protected $violationRecord;

    public function __construct()
    {
        $this->violationRecord = new ViolationRecord();
    }

    public function index()
    {
        $stats = $this->violationRecord->getStatistics();
        $recentViolations = $this->violationRecord->getRecentViolations(5);

        $data = array_merge($stats, [
            'title' => 'Admin Dashboard',
            'recent_violations' => $recentViolations,
        ]);

        return view('admin/main/dashboard', $data);
    }

    public function analytics()
    {
        $monthlyTrend = $this->violationRecord->getMonthlyTrend();
        $violationTypesSummary = $this->violationRecord->getViolationTypesSummary();
        $statusDistribution = $this->violationRecord->getStatusDistribution();
        
        $totalCollected = $this->violationRecord->getTotalRevenue();
        $totalPending = $this->violationRecord->getOutstandingFines();
        $totalViolations = $this->violationRecord->countAll();
        
        $collectionRate = $totalViolations > 0 ? ($this->violationRecord->getPaidCount() / $totalViolations) * 100 : 0;

        $data = [
            'title' => 'Analytics Dashboard',
            'monthly_trend' => $monthlyTrend,
            'violation_types' => $violationTypesSummary,
            'status_distribution' => $statusDistribution,
            'summary' => [
                'total_collected' => $totalCollected,
                'total_pending' => $totalPending,
                'total_violations' => $totalViolations,
                'collection_rate' => round($collectionRate, 1),
                'top_violation' => $violationTypesSummary[0]['violation_type'] ?? 'None'
            ]
        ];

        return view('admin/main/analytics', $data);
    }

    public function about()
    {
        $data = [
            'title' => 'About System'
        ];
        return view('admin/main/about', $data);
    }
}
