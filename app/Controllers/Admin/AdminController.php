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

        $data = [
            'title' => 'Analytics',
            'monthly_trend' => $monthlyTrend,
            'violation_types' => $violationTypesSummary,
            'status_counts' => [
                'pending' => $this->violationRecord->getPendingCount(),
                'paid' => $this->violationRecord->getPaidCount(),
                'cancelled' => $this->violationRecord->getCancelledCount(),
            ],
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
