<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\ViolationTypeModel;
use App\Models\ViolatorRecord;

class AdminController extends BaseController
{
    protected $violationRecord;
    protected $users;
    protected $violationTypes;

    public function __construct()
    {
        $this->violationRecord = new ViolatorRecord();
        $this->users = new UserModel();
        $this->violationTypes = new ViolationTypeModel();
    }

    public function index()
    {
        // Basic Statistics
        $stats = $this->violationRecord->getStatistics();
        $recentViolations = $this->violationRecord->getRecentViolations(5);

        // Analytics Data
        $monthlyTrend = $this->violationRecord->getMonthlyTrend();
        $violationTypesSummary = $this->violationRecord->getViolationTypesSummary();
        $statusDistribution = $this->violationRecord->getStatusDistribution();
        
        $totalCollected = $this->violationRecord->getTotalRevenue();
        $totalPending = $this->violationRecord->getOutstandingFines();
        $totalViolations = $this->violationRecord->countAll();
        
        $collectionRate = $totalViolations > 0 ? ($this->violationRecord->getPaidCount() / $totalViolations) * 100 : 0;

        $userRoleDistribution = $this->users
            ->select('role, COUNT(*) as count')
            ->groupBy('role')
            ->findAll();

        $userStatusDistribution = $this->users
            ->select('status, COUNT(*) as count')
            ->groupBy('status')
            ->findAll();

        $violationTypeCatalog = [
            'active' => $this->violationTypes->where('status', 'active')->countAllResults(),
            'inactive' => $this->violationTypes->where('status', 'inactive')->countAllResults(),
        ];

        $data = array_merge($stats, [
            'title' => 'Admin Dashboard',
            'recent_violations' => $recentViolations,
            'monthly_trend' => $monthlyTrend,
            'violation_types' => $violationTypesSummary,
            'status_distribution' => $statusDistribution,
            'user_role_distribution' => $userRoleDistribution,
            'user_status_distribution' => $userStatusDistribution,
            'summary' => [
                'total_collected' => $totalCollected,
                'total_pending' => $totalPending,
                'total_violations' => $totalViolations,
                'collection_rate' => round($collectionRate, 1),
                'top_violation' => $violationTypesSummary[0]['violation_type'] ?? 'None',
                'total_users' => $this->users->countAll(),
                'total_officers' => $this->users->where('role', 'enforcer')->countAllResults(),
                'violation_types_active' => $violationTypeCatalog['active'],
            ]
        ]);

        return view('admin/main/dashboard', $data);
    }
}
