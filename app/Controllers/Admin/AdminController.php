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
        
        $totalCollected  = $this->violationRecord->getTotalRevenue();
        $totalPending    = $this->violationRecord->getOutstandingFines();
        $totalViolations = $stats['total_violations']; // distinct tickets via getStatistics()

        $paidTickets    = $this->violationRecord->getPaidCount();
        $collectionRate = $totalViolations > 0 ? ($paidTickets / $totalViolations) * 100 : 0;

        $userRoleDistribution = $this->users
            ->select('role, COUNT(*) as count')
            ->groupBy('role')
            ->findAll();

        $userStatusDistribution = $this->users
            ->select('status, COUNT(*) as count')
            ->groupBy('status')
            ->findAll();

        $year = $this->request->getGet('year') ?? date('Y');
        $weeklyTrendLast12 = $this->violationRecord->getWeeklyTrend(12);
        $monthlyBreakdown = $this->violationRecord->getMonthlyBreakdown($year);
        $natureSummary = $this->violationRecord->getViolationNatureSummary();

        $violationTypeCatalog = [
            'active' => $this->violationTypes->where('status', 'active')->countAllResults(),
            'inactive' => $this->violationTypes->where('status', 'inactive')->countAllResults(),
        ];

        $data = array_merge($stats, [
            'title' => 'Admin Dashboard',
            'year' => $year,
            'weekly_trend' => $weeklyTrendLast12,
            'monthly_breakdown' => $monthlyBreakdown,
            'nature_summary' => $natureSummary,
            'recent_violations' => $recentViolations,
            'monthly_trend' => $monthlyTrend,
            'violation_types' => $violationTypesSummary,
            'status_distribution' => $statusDistribution,
            'user_role_distribution' => $userRoleDistribution,
            'user_status_distribution' => $userStatusDistribution,
            'top_violators_list' => $this->violationRecord->getTopViolatorsList(10),
            'top_violations_list' => $this->violationRecord->getTopViolationsList(10),
            'summary' => [
                'total_collected' => $totalCollected,
                'total_pending' => $totalPending,
                'total_violations' => $totalViolations,
                'collection_rate' => round($collectionRate, 1),
                'top_violation'       => $this->violationRecord->getTopViolationType(),
                'top_violator_person' => $this->violationRecord->getTopViolatorPerson(),
                'total_users' => $this->users->countAll(),
                'total_officers' => $this->users->where('role', 'enforcer')->countAllResults(),
                'violation_types_active' => $violationTypeCatalog['active'],
            ]
        ]);

        return view('admin/main/dashboard', $data);
    }

    public function reports()
    {
        $periodType = $this->request->getGet('period_type') ?? 'weekly';
        $status     = $this->request->getGet('status');
        $typeId     = $this->request->getGet('violation_type_id');

        $startDate  = $this->request->getGet('start_date');
        $endDate    = $this->request->getGet('end_date');

        // Auto-calculate start and end date if empty
        if (empty($startDate) || empty($endDate)) {
            if ($periodType === 'monthly') {
                $month = $this->request->getGet('month') ?? date('m');
                $year  = $this->request->getGet('year') ?? date('Y');
                $startDate = date('Y-m-d', strtotime("$year-$month-01"));
                $endDate   = date('Y-m-t', strtotime($startDate));
            } else { // weekly
                $weekRef   = $this->request->getGet('start_date') ?: date('Y-m-d');
                $startDate = date('Y-m-d', strtotime('monday this week', strtotime($weekRef)));
                $endDate   = date('Y-m-d', strtotime('sunday this week', strtotime($weekRef)));
            }
        }

        $reportData = $this->violationRecord->getReportData($startDate, $endDate, $status, $typeId);
        $allTypes   = $this->violationTypes->where('status', 'active')->findAll();

        $data = array_merge($reportData, [
            'title'        => 'Generated Traffic Violation Report',
            'period_type'  => $periodType,
            'status_filter'=> $status,
            'type_filter'  => $typeId,
            'all_types'    => $allTypes,
            'month'        => date('m', strtotime($startDate)),
            'year'         => date('Y', strtotime($startDate)),
        ]);

        return view('admin/reports/index', $data);
    }

    public function exportReportCsv()
    {
        $periodType = $this->request->getGet('period_type') ?? 'weekly';
        $status     = $this->request->getGet('status');
        $typeId     = $this->request->getGet('violation_type_id');
        $startDate  = $this->request->getGet('start_date');
        $endDate    = $this->request->getGet('end_date');

        if (empty($startDate) || empty($endDate)) {
            if ($periodType === 'monthly') {
                $month = $this->request->getGet('month') ?? date('m');
                $year  = $this->request->getGet('year') ?? date('Y');
                $startDate = date('Y-m-d', strtotime("$year-$month-01"));
                $endDate   = date('Y-m-t', strtotime($startDate));
            } else {
                $startDate = date('Y-m-d', strtotime('monday this week'));
                $endDate   = date('Y-m-d', strtotime('sunday this week'));
            }
        }

        $report = $this->violationRecord->getReportData($startDate, $endDate, $status, $typeId);
        $filename = "Traffic_Report_{$periodType}_{$startDate}_to_{$endDate}.csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');

        // Executive Summary Headers
        fputcsv($output, ['REPUBLIC OF THE PHILIPPINES']);
        fputcsv($output, ['OFFICE OF THE CITY MAYOR - KABANKALAN CITY']);
        fputcsv($output, ['TRAFFIC CITATION VIOLATION REPORT']);
        fputcsv($output, ['Report Period:', $startDate . ' to ' . $endDate]);
        fputcsv($output, []);

        // KPI Summary Block
        fputcsv($output, ['SUMMARY METRICS']);
        fputcsv($output, ['Total Tickets Issued', $report['summary']['total_tickets']]);
        fputcsv($output, ['Total Violations Recorded', $report['summary']['total_violations']]);
        fputcsv($output, ['Total Assessed Penalties (PHP)', number_format($report['summary']['total_assessed'], 2)]);
        fputcsv($output, ['Total Revenue Collected (PHP)', number_format($report['summary']['total_paid'], 2)]);
        fputcsv($output, ['Total Outstanding Pending (PHP)', number_format($report['summary']['total_pending'], 2)]);
        fputcsv($output, ['Collection Rate (%)', $report['summary']['collection_rate'] . '%']);
        fputcsv($output, []);

        // Detailed Table
        fputcsv($output, ['DETAILED VIOLATION RECORDS']);
        fputcsv($output, ['Ticket ID', 'Driver Name', 'License Plate', 'DL Number', 'Violation Type', 'Amount (PHP)', 'Status', 'Apprehending Officer', 'Date & Time', 'Location']);

        foreach ($report['records'] as $r) {
            fputcsv($output, [
                $r['ticket_id'],
                $r['driver_name'],
                $r['license_plate'],
                $r['license_number'] ?? 'N/A',
                $r['violation_name'] ?: $r['violation_type'],
                number_format((float)$r['penalty_amount'], 2),
                $r['status'],
                $r['officer_name'] ?? 'System',
                date('Y-m-d H:i', strtotime($r['violation_date'])),
                $r['location'] ?? ''
            ]);
        }

        fclose($output);
        exit;
    }
}
