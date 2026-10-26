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
        $this->users           = new UserModel();
        $this->violationTypes  = new ViolationTypeModel();
    }

    public function index()
    {
        // Basic Statistics
        $stats            = $this->violationRecord->getStatistics();
        $recentViolations = $this->violationRecord->getRecentViolations(5);

        // Analytics Data
        $monthlyTrend          = $this->violationRecord->getMonthlyTrend();
        $violationTypesSummary = $this->violationRecord->getViolationTypesSummary();
        $statusDistribution    = $this->violationRecord->getViolatorStatusDistribution();

        $year  = (int) ($this->request->getGet('year') ?? date('Y'));
        $month = $this->request->getGet('month');

        // Money and ticket figures are read from the very same computation the
        // Revenue Report uses for this calendar year: collected revenue, the
        // outstanding balance, the assessable total and the collection rate all
        // come out of one place, so the dashboard and the printed report can
        // never disagree. Cancelled citations are void and add no money.
        $reportStart = sprintf('%04d-01-01', $year);
        $reportEnd   = sprintf('%04d-12-31', $year);
        $yearReport  = $this->violationRecord->getReportData($reportStart, $reportEnd);
        $yearSummary = $yearReport['summary'];

        $totalCollected  = (float) $yearSummary['total_paid'];
        $totalPending    = (float) $yearSummary['total_pending'];
        $totalAssessed   = (float) $yearSummary['total_assessed'];
        $totalViolations = (int) $yearSummary['total_tickets'];
        $totalOffenses   = (int) $yearSummary['total_violations'];
        $paidTickets     = (int) $yearSummary['paid_tickets'];
        $collectionRate  = (float) $yearSummary['collection_rate'];
        $totalViolators  = $stats['total_violators'];

        $userRoleDistribution = $this->users
            ->select('role, COUNT(*) as count')
            ->groupBy('role')
            ->findAll();

        $userStatusDistribution = $this->users
            ->select('status, COUNT(*) as count')
            ->groupBy('status')
            ->findAll();

        $selectedMonth     = null;
        $selectedMonthName = null;
        if (!empty($month) && is_numeric($month) && (int) $month >= 1 && (int) $month <= 12) {
            $selectedMonth     = (int) $month;
            $selectedMonthName = date('F', mktime(0, 0, 0, $selectedMonth, 1, (int) $year));
            $weeklyTrendLast12 = $this->violationRecord->getWeeklyTrendForMonth($year, $selectedMonth);
        } else {
            $weeklyTrendLast12 = $this->violationRecord->getWeeklyTrend(12);
        }

        $monthlyBreakdown  = $this->violationRecord->getMonthlyBreakdown($year);
        $natureSummary     = $this->violationRecord->getViolationNatureSummary(null, $reportStart, $reportEnd);

        $violationTypeCatalog = [
            'active'   => $this->violationTypes->where('status', 'active')->countAllResults(),
            'inactive' => $this->violationTypes->where('status', 'inactive')->countAllResults(),
        ];

        $data = array_merge($stats, [
            'title'                    => 'Admin Dashboard',
            'year'                     => $year,
            'selected_month'           => $selectedMonth,
            'selected_month_name'      => $selectedMonthName,
            'weekly_trend'             => $weeklyTrendLast12,
            'monthly_breakdown'        => $monthlyBreakdown,
            'nature_summary'           => $natureSummary,
            'recent_violations'        => $recentViolations,
            'monthly_trend'            => $monthlyTrend,
            'violation_types'          => $violationTypesSummary,
            'status_distribution'      => $statusDistribution,
            'user_role_distribution'   => $userRoleDistribution,
            'user_status_distribution' => $userStatusDistribution,
            'top_violators_list'       => $this->violationRecord->getTopViolatorsList(10),
            'top_violations_list'      => $this->violationRecord->getTopViolationsList(10),
            'summary' => [
                'total_collected'        => $totalCollected,
                'total_pending'          => $totalPending,
                'total_assessed'         => $totalAssessed,
                'total_violations'       => $totalViolations,
                'total_offenses'         => $totalOffenses,
                'paid_tickets'           => $paidTickets,
                'total_violators'        => $totalViolators,
                'collection_rate'        => round($collectionRate, 1),
                'report_year'            => $year,
                'top_violation'          => $this->violationRecord->getTopViolationType(),
                'top_violator_person'    => $this->violationRecord->getTopViolatorPerson(),
                'total_users'            => $this->users->countAll(),
                'total_officers'         => $this->users->where('role', 'enforcer')->countAllResults(),
                'violation_types_active' => $violationTypeCatalog['active'],
            ],
        ]);

        return view('admin/main/dashboard', $data);
    }

    /**
     * JSON API: Returns the violation nature summary for real-time chart polling.
     */
    public function natureSummaryJson()
    {
        // Same calendar-year window as the dashboard KPI cards, so the live
        // refresh keeps showing the year the admin is looking at.
        $year = (int) ($this->request->getGet('year') ?? date('Y'));

        $data = $this->violationRecord->getViolationNatureSummary(
            null,
            sprintf('%04d-01-01', $year),
            sprintf('%04d-12-31', $year)
        );

        return $this->response
            ->setContentType('application/json')
            ->setJSON([
                'status'     => 'ok',
                'updated_at' => date('Y-m-d H:i:s'),
                'data'       => $data,
                'total'      => array_sum(array_column($data, 'count')),
                'top'        => !empty($data) ? $data[0] : null,
            ]);
    }

    public function reports()
    {
        // Both reports open on the same calendar-year window the dashboard KPI
        // cards use, so the figures agree the moment the page loads. The
        // weekly / monthly / custom selectors are still there for drill-down.
        $periodType = $this->request->getGet('period_type') ?? 'yearly';
        $status     = $this->request->getGet('status');
        $typeId     = $this->request->getGet('violation_type_id');

        ['startDate' => $startDate, 'endDate' => $endDate, 'year' => $year, 'month' => $month]
            = $this->resolveDateRange($periodType);

        $reportData = $this->violationRecord->getReportData($startDate, $endDate, $status, $typeId);
        $allTypes   = $this->violationTypes->where('status', 'active')->findAll();

        $timelineInfo = $this->buildTimelineBuckets(
            $reportData['records'] ?? [],
            $periodType,
            $startDate,
            $endDate,
            $reportData['nature_breakdown'] ?? []
        );

        $data = array_merge($reportData, [
            'title'            => 'Generated Traffic Violation Report',
            'period_type'      => $periodType,
            'status_filter'    => $status,
            'type_filter'      => $typeId,
            'all_types'        => $allTypes,
            'start_date'       => $startDate,
            'end_date'         => $endDate,
            'month'            => ($periodType === 'monthly') ? $month : date('m'),
            'year'             => ($periodType === 'monthly' || $periodType === 'yearly') ? $year : date('Y'),
            'timeline_buckets' => $timelineInfo['timeline_buckets'],
            'peak_period'      => $timelineInfo['peak_period'],
            'top_types'        => $timelineInfo['top_types'],
        ]);

        return view('admin/reports/index', $data);
    }

    /**
     * Dedicated Violations Summary Only Report (focused strictly on violation frequency,
     * counts, categories — without monetary amounts).
     */
    public function violationsReport()
    {
        // Both reports open on the same calendar-year window the dashboard KPI
        // cards use, so the figures agree the moment the page loads. The
        // weekly / monthly / custom selectors are still there for drill-down.
        $periodType = $this->request->getGet('period_type') ?? 'yearly';
        $status     = $this->request->getGet('status');
        $typeId     = $this->request->getGet('violation_type_id');

        ['startDate' => $startDate, 'endDate' => $endDate, 'year' => $year, 'month' => $month]
            = $this->resolveDateRange($periodType);

        $reportData = $this->violationRecord->getReportData($startDate, $endDate, $status, $typeId);
        $allTypes   = $this->violationTypes->where('status', 'active')->findAll();

        $timelineInfo = $this->buildTimelineBuckets(
            $reportData['records'] ?? [],
            $periodType,
            $startDate,
            $endDate,
            $reportData['nature_breakdown'] ?? []
        );

        $topOffense       = !empty($reportData['nature_breakdown']) ? $reportData['nature_breakdown'][0] : null;
        $uniqueCategories = count($reportData['nature_breakdown'] ?? []);

        $statusCounts = ['Paid' => 0, 'Pending' => 0, 'Cancelled' => 0];
        foreach ($reportData['records'] ?? [] as $rec) {
            $st = $rec['status'] ?? 'Pending';
            if (isset($statusCounts[$st])) {
                $statusCounts[$st]++;
            }
        }

        $data = [
            'title'             => 'Traffic Violations Summary Report',
            'period_type'       => $periodType,
            'status_filter'     => $status,
            'type_filter'       => $typeId,
            'all_types'         => $allTypes,
            'start_date'        => $startDate,
            'end_date'          => $endDate,
            'month'             => ($periodType === 'monthly') ? $month : date('m'),
            'year'              => ($periodType === 'monthly' || $periodType === 'yearly') ? $year : date('Y'),
            'records'           => $reportData['records'] ?? [],
            'summary'           => $reportData['summary'] ?? [],
            'nature_breakdown'  => $reportData['nature_breakdown'] ?? [],
            'officer_breakdown' => $reportData['officer_breakdown'] ?? [],
            'timeline_buckets'  => $timelineInfo['timeline_buckets'],
            'peak_period'       => $timelineInfo['peak_period'],
            'top_types'         => $timelineInfo['top_types'],
            'top_offense'       => $topOffense,
            'unique_categories' => $uniqueCategories,
            'status_counts'     => $statusCounts,
        ];

        return view('admin/reports/violations', $data);
    }

    public function exportViolationsCsv()
    {
        // Both reports open on the same calendar-year window the dashboard KPI
        // cards use, so the figures agree the moment the page loads. The
        // weekly / monthly / custom selectors are still there for drill-down.
        $periodType = $this->request->getGet('period_type') ?? 'yearly';
        $status     = $this->request->getGet('status');
        $typeId     = $this->request->getGet('violation_type_id');

        ['startDate' => $startDate, 'endDate' => $endDate]
            = $this->resolveDateRange($periodType);

        $report   = $this->violationRecord->getReportData($startDate, $endDate, $status, $typeId);
        $filename = "Traffic_Violations_Summary_{$periodType}_{$startDate}_to_{$endDate}.csv";
        $width    = 9; // widest table: Detailed Citation Records

        $output = $this->csvDownload($filename);

        $this->csvRow($output, ['REPUBLIC OF THE PHILIPPINES'], $width);
        $this->csvRow($output, ['OFFICE OF THE CITY MAYOR - KABANKALAN CITY'], $width);
        $this->csvRow($output, ['TRAFFIC VIOLATIONS & OFFENSE FREQUENCY SUMMARY REPORT'], $width);
        $this->csvRow($output, ['Report Period:', $startDate . ' to ' . $endDate], $width);
        $this->csvRow($output, [], $width);

        $this->csvRow($output, ['VIOLATION SUMMARY METRICS'], $width);
        $this->csvRow($output, ['Total Violation Incidents', $report['summary']['total_violations']], $width);
        $this->csvRow($output, ['Distinct Citation Tickets', $report['summary']['total_tickets']], $width);
        $this->csvRow($output, ['Unique Offense Categories', count($report['nature_breakdown'] ?? [])], $width);
        $this->csvRow($output, [], $width);

        // Same section order and numbering as the on-screen report.
        $this->csvRow($output, ['I. ENFORCER / OFFICER ACTIVITY SUMMARY'], $width);
        $this->csvRow($output, ['#', 'Apprehending Officer', 'Citations Issued', 'Percentage Share (%)'], $width);
        $totalCitations = array_sum(array_column($report['officer_breakdown'], 'count'));
        $j = 1;
        foreach ($report['officer_breakdown'] as $ob) {
            $offShare = $totalCitations > 0
                ? round(($ob['count'] / $totalCitations) * 100, 1)
                : 0;
            $this->csvRow($output, [
                $j++,
                $ob['officer_name'],
                $ob['count'],
                number_format((float) $offShare, 1, '.', ''),
            ], $width);
        }
        $this->csvRow($output, [], $width);

        $this->csvRow($output, ['II. OFFENSE CATEGORY BREAKDOWN'], $width);
        $this->csvRow($output, ['#', 'Violation / Offense Nature', 'Total Incidents', 'Percentage Share (%)'], $width);
        $i = 1;
        foreach ($report['nature_breakdown'] as $nb) {
            $share = $report['summary']['total_violations'] > 0
                ? round(($nb['count'] / $report['summary']['total_violations']) * 100, 1)
                : 0;
            $this->csvRow($output, [
                $i++,
                $nb['violation_type'],
                $nb['count'],
                number_format((float) $share, 1, '.', ''),
            ], $width);
        }
        $this->csvRow($output, [], $width);

        $this->csvRow($output, ['III. DETAILED CITATION RECORDS'], $width);
        $this->csvRow($output, ['Ticket ID', 'Driver Name', 'License Plate', 'DL Number', 'Violation Type', 'Status', 'Apprehending Officer', 'Date & Time', 'Location'], $width);
        foreach ($report['records'] as $r) {
            $this->csvRow($output, [
                $r['ticket_id'],
                $r['driver_name'],
                $r['license_plate'],
                $r['license_number'] ?? 'N/A',
                $r['violation_name'] ?: $r['violation_type'],
                $r['status'],
                $r['officer_name'] ?? 'System',
                date('Y-m-d H:i', strtotime($r['violation_date'])),
                $r['location'] ?? '',
            ], $width);
        }

        fclose($output);
        exit;
    }

    public function exportReportCsv()
    {
        // Both reports open on the same calendar-year window the dashboard KPI
        // cards use, so the figures agree the moment the page loads. The
        // weekly / monthly / custom selectors are still there for drill-down.
        $periodType = $this->request->getGet('period_type') ?? 'yearly';
        $status     = $this->request->getGet('status');
        $typeId     = $this->request->getGet('violation_type_id');

        ['startDate' => $startDate, 'endDate' => $endDate]
            = $this->resolveDateRange($periodType);

        $report   = $this->violationRecord->getReportData($startDate, $endDate, $status, $typeId);
        $filename = "Traffic_Report_{$periodType}_{$startDate}_to_{$endDate}.csv";
        $width    = 10; // widest table: Detailed Citation Records

        $output = $this->csvDownload($filename);

        // Executive Summary Headers
        $this->csvRow($output, ['REPUBLIC OF THE PHILIPPINES'], $width);
        $this->csvRow($output, ['OFFICE OF THE CITY MAYOR - KABANKALAN CITY'], $width);
        $this->csvRow($output, ['TRAFFIC CITATION & REVENUE SUMMARY REPORT'], $width);
        $this->csvRow($output, ['Report Period:', $startDate . ' to ' . $endDate], $width);
        $this->csvRow($output, [], $width);

        // KPI Summary Block
        $this->csvRow($output, ['SUMMARY METRICS'], $width);
        $this->csvRow($output, ['Total Tickets Issued', $report['summary']['total_tickets']], $width);
        $this->csvRow($output, ['Total Violations Recorded', $report['summary']['total_violations']], $width);
        $this->csvRow($output, ['Total Assessed Penalties (PHP)', number_format((float) $report['summary']['total_assessed'], 2, '.', '')], $width);
        $this->csvRow($output, ['Total Revenue Collected (PHP)', number_format((float) $report['summary']['total_paid'], 2, '.', '')], $width);
        $this->csvRow($output, ['Total Outstanding Pending (PHP)', number_format((float) $report['summary']['total_pending'], 2, '.', '')], $width);
        $this->csvRow($output, ['Collection Rate (%)', number_format((float) $report['summary']['collection_rate'], 1, '.', '')], $width);
        $this->csvRow($output, [], $width);

        // Section I — mirrors the on-screen "Enforcer / Officer Activity Summary"
        $this->csvRow($output, ['I. ENFORCER / OFFICER ACTIVITY SUMMARY'], $width);
        $this->csvRow($output, ['#', 'Apprehending Officer', 'Citations Issued', 'Total Fine Amount (PHP)'], $width);
        $i = 1;
        foreach ($report['officer_breakdown'] as $ob) {
            $this->csvRow($output, [
                $i++,
                $ob['officer_name'],
                $ob['count'],
                number_format((float) $ob['amount'], 2, '.', ''),
            ], $width);
        }
        $this->csvRow($output, [], $width);

        // Section II — mirrors the on-screen "Offense Category Breakdown"
        $this->csvRow($output, ['II. OFFENSE CATEGORY BREAKDOWN'], $width);
        $this->csvRow($output, ['#', 'Violation / Offense Nature', 'Total Incidents', 'Assessed Amount (PHP)', 'Percentage Share (%)'], $width);
        $i = 1;
        foreach ($report['nature_breakdown'] as $nb) {
            $share = $report['summary']['total_violations'] > 0
                ? round(($nb['count'] / $report['summary']['total_violations']) * 100, 1)
                : 0;
            $this->csvRow($output, [
                $i++,
                $nb['violation_type'],
                $nb['count'],
                number_format((float) $nb['amount'], 2, '.', ''),
                number_format((float) $share, 1, '.', ''),
            ], $width);
        }
        $this->csvRow($output, [], $width);

        // Section III — mirrors the on-screen "Detailed Citation Records"
        $this->csvRow($output, ['III. DETAILED CITATION RECORDS'], $width);
        $this->csvRow($output, ['Ticket ID', 'Driver Name', 'License Plate', 'DL Number', 'Violation Type', 'Amount (PHP)', 'Status', 'Apprehending Officer', 'Date & Time', 'Location'], $width);

        foreach ($report['records'] as $r) {
            $this->csvRow($output, [
                $r['ticket_id'],
                $r['driver_name'],
                $r['license_plate'],
                $r['license_number'] ?? 'N/A',
                $r['violation_name'] ?: $r['violation_type'],
                number_format((float) $r['penalty_amount'], 2, '.', ''),
                $r['status'],
                $r['officer_name'] ?? 'System',
                date('Y-m-d H:i', strtotime($r['violation_date'])),
                $r['location'] ?? '',
            ], $width);
        }

        fclose($output);
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Opens a UTF-8 CSV download stream.
     *
     * The byte-order mark lets Excel detect the encoding, the filename is
     * quoted, and both report exports share these headers so every download
     * behaves the same way.
     *
     * @return resource
     */
    private function csvDownload(string $filename)
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");

        return $output;
    }

    /**
     * Writes one row padded to $width cells using RFC 4180 line endings.
     *
     * Every row of an export carries the same number of cells, so the summary
     * blocks and the data tables always line up on the same columns when the
     * download is opened in a spreadsheet.
     *
     * @param resource $output
     */
    private function csvRow($output, array $fields, int $width): void
    {
        $cells = array_map(
            static fn ($value): string => $value === null ? '' : (string) $value,
            array_pad($fields, $width, '')
        );

        fputcsv($output, array_slice($cells, 0, $width), ',', '"', '\\', "\r\n");
    }

    /**
     * Resolves startDate, endDate, year, and month from query-string parameters
     * based on the given period type.
     *
     * Centralises date logic so that reports(), violationsReport(),
     * exportViolationsCsv(), and exportReportCsv() all share one implementation,
     * eliminating the four previously copy-pasted blocks.
     *
     * @return array{startDate: string, endDate: string, year: string, month: string}
     */
    private function resolveDateRange(string $periodType): array
    {
        $startDate = $this->request->getGet('start_date');
        $endDate   = $this->request->getGet('end_date');
        $year      = date('Y');
        $month     = date('m');

        if ($periodType === 'yearly') {
            $year      = $this->request->getGet('year') ?? date('Y');
            $startDate = "$year-01-01";
            $endDate   = "$year-12-31";

        } elseif ($periodType === 'monthly') {
            $monthYear = $this->request->getGet('month_year');
            if (!empty($monthYear) && preg_match('/^(\d{4})-(\d{2})$/', $monthYear, $mMatches)) {
                $year  = $mMatches[1];
                $month = $mMatches[2];
            } else {
                $month = $this->request->getGet('month') ?: date('m');
                $year  = $this->request->getGet('year')  ?: date('Y');
            }
            $startDate = date('Y-m-d', strtotime("$year-$month-01"));
            $endDate   = date('Y-m-t', strtotime($startDate));

        } elseif ($periodType === 'weekly') {
            $weekRef   = $this->request->getGet('start_date') ?: date('Y-m-d');
            $ts        = strtotime($weekRef);
            $dow       = (int) date('N', $ts); // 1 = Mon … 7 = Sun (ISO-8601)
            $startDate = date('Y-m-d', strtotime('-' . ($dow - 1) . ' days', $ts));
            $endDate   = date('Y-m-d', strtotime('+' . (7 - $dow) . ' days', $ts));

        } else {
            // custom range — fall back to current month if nothing was supplied
            if (empty($startDate) || empty($endDate)) {
                $startDate = date('Y-m-01');
                $endDate   = date('Y-m-t');
            }
        }

        return compact('startDate', 'endDate', 'year', 'month');
    }

    /**
     * Builds weekly, monthly, or yearly timeline buckets and peak metrics for bar chart summaries.
     */
    private function buildTimelineBuckets(array $records, string $periodType, string $startDate, string $endDate, array $natureBreakdown): array
    {
        $buckets = [];

        if ($periodType === 'monthly') {
            // Group month into 4 to 5 standard weeks
            $daysInMonth = (int) date('t', strtotime($startDate));
            $m           = date('m', strtotime($startDate));
            $y           = date('Y', strtotime($startDate));
            $monthShort  = date('M', strtotime($startDate));

            $weekRanges = [
                ['start' => 1,  'end' => 7,  'num' => 1],
                ['start' => 8,  'end' => 14, 'num' => 2],
                ['start' => 15, 'end' => 21, 'num' => 3],
                ['start' => 22, 'end' => 28, 'num' => 4],
            ];
            if ($daysInMonth > 28) {
                $weekRanges[] = ['start' => 29, 'end' => $daysInMonth, 'num' => 5];
            }

            foreach ($weekRanges as $wr) {
                $sDate = sprintf('%04d-%02d-%02d', $y, $m, $wr['start']);
                $eDate = sprintf('%04d-%02d-%02d', $y, $m, $wr['end']);
                $lbl   = sprintf('Week %d (%s %02d-%02d)', $wr['num'], $monthShort, $wr['start'], $wr['end']);
                $sLbl  = sprintf('W%d (%02d-%02d)', $wr['num'], $wr['start'], $wr['end']);

                $buckets[] = [
                    'label'       => $lbl,
                    'short_label' => $sLbl,
                    'start_date'  => $sDate,
                    'end_date'    => $eDate,
                    'count'       => 0,
                    'revenue'     => 0,
                    'by_type'     => [],
                    'by_revenue'  => [],
                    'types_count' => [],
                ];
            }

            foreach ($records as $r) {
                $rDay  = (int) date('j', strtotime($r['violation_date']));
                $vType = $r['violation_name'] ?: $r['violation_type'];
                $amt   = (float) $r['penalty_amount'];

                foreach ($weekRanges as $idx => $wr) {
                    if ($rDay >= $wr['start'] && $rDay <= $wr['end']) {
                        $buckets[$idx]['count']++;
                        $buckets[$idx]['revenue'] += $amt;
                        $buckets[$idx]['by_type'][$vType]     = ($buckets[$idx]['by_type'][$vType] ?? 0) + 1;
                        $buckets[$idx]['by_revenue'][$vType]  = ($buckets[$idx]['by_revenue'][$vType] ?? 0) + $amt;
                        $buckets[$idx]['types_count'][$vType] = ($buckets[$idx]['types_count'][$vType] ?? 0) + 1;
                        break;
                    }
                }
            }

        } elseif ($periodType === 'weekly') {
            // 7 days of the week: Mon through Sun
            $startTs = strtotime($startDate);
            for ($i = 0; $i < 7; $i++) {
                $dayDate = date('Y-m-d', strtotime("+$i days", $startTs));
                $dayName = date('D', strtotime($dayDate));
                $dayFull = date('D, M d', strtotime($dayDate));

                $buckets[] = [
                    'label'       => $dayFull,
                    'short_label' => $dayName,
                    'start_date'  => $dayDate,
                    'end_date'    => $dayDate,
                    'count'       => 0,
                    'revenue'     => 0,
                    'by_type'     => [],
                    'by_revenue'  => [],
                    'types_count' => [],
                ];
            }

            foreach ($records as $r) {
                $rDate = date('Y-m-d', strtotime($r['violation_date']));
                $vType = $r['violation_name'] ?: $r['violation_type'];
                $amt   = (float) $r['penalty_amount'];

                foreach ($buckets as &$b) {
                    if ($b['start_date'] === $rDate) {
                        $b['count']++;
                        $b['revenue'] += $amt;
                        $b['by_type'][$vType]     = ($b['by_type'][$vType] ?? 0) + 1;
                        $b['by_revenue'][$vType]  = ($b['by_revenue'][$vType] ?? 0) + $amt;
                        $b['types_count'][$vType] = ($b['types_count'][$vType] ?? 0) + 1;
                        break;
                    }
                }
                unset($b);
            }

        } elseif ($periodType === 'yearly') {
            $y = date('Y', strtotime($startDate));
            for ($m = 1; $m <= 12; $m++) {
                $mCode    = sprintf('%02d', $m);
                $mName    = date('M', strtotime("$y-$mCode-01"));
                $buckets[] = [
                    'label'       => date('F Y', strtotime("$y-$mCode-01")),
                    'short_label' => $mName,
                    'start_date'  => "$y-$mCode-01",
                    'end_date'    => date('Y-m-t', strtotime("$y-$mCode-01")),
                    'count'       => 0,
                    'revenue'     => 0,
                    'by_type'     => [],
                    'by_revenue'  => [],
                    'types_count' => [],
                ];
            }

            foreach ($records as $r) {
                $rM    = (int) date('n', strtotime($r['violation_date']));
                $vType = $r['violation_name'] ?: $r['violation_type'];
                $amt   = (float) $r['penalty_amount'];

                if (isset($buckets[$rM - 1])) {
                    $buckets[$rM - 1]['count']++;
                    $buckets[$rM - 1]['revenue'] += $amt;
                    $buckets[$rM - 1]['by_type'][$vType]     = ($buckets[$rM - 1]['by_type'][$vType] ?? 0) + 1;
                    $buckets[$rM - 1]['by_revenue'][$vType]  = ($buckets[$rM - 1]['by_revenue'][$vType] ?? 0) + $amt;
                    $buckets[$rM - 1]['types_count'][$vType] = ($buckets[$rM - 1]['types_count'][$vType] ?? 0) + 1;
                }
            }

        } else {
            // Custom Range: Daily if ≤ 14 days, else Monthly
            $diffDays = (int) ceil((strtotime($endDate) - strtotime($startDate)) / 86400);

            if ($diffDays <= 14) {
                for ($i = 0; $i <= $diffDays; $i++) {
                    $d         = date('Y-m-d', strtotime("+$i days", strtotime($startDate)));
                    $buckets[] = [
                        'label'       => date('M d, Y', strtotime($d)),
                        'short_label' => date('M d',    strtotime($d)),
                        'start_date'  => $d,
                        'end_date'    => $d,
                        'count'       => 0,
                        'revenue'     => 0,
                        'by_type'     => [],
                        'by_revenue'  => [],
                        'types_count' => [],
                    ];
                }
                foreach ($records as $r) {
                    $rDate = date('Y-m-d', strtotime($r['violation_date']));
                    $vType = $r['violation_name'] ?: $r['violation_type'];
                    $amt   = (float) $r['penalty_amount'];
                    foreach ($buckets as &$b) {
                        if ($b['start_date'] === $rDate) {
                            $b['count']++;
                            $b['revenue'] += $amt;
                            $b['by_type'][$vType]     = ($b['by_type'][$vType] ?? 0) + 1;
                            $b['by_revenue'][$vType]  = ($b['by_revenue'][$vType] ?? 0) + $amt;
                            $b['types_count'][$vType] = ($b['types_count'][$vType] ?? 0) + 1;
                            break;
                        }
                    }
                    unset($b);
                }
            } else {
                $start    = new \DateTime($startDate);
                $end      = new \DateTime($endDate);
                $interval = new \DateInterval('P1M');
                $period   = new \DatePeriod($start, $interval, $end->modify('+1 day'));

                foreach ($period as $dt) {
                    $mKey           = $dt->format('Y-m');
                    $buckets[$mKey] = [
                        'label'       => $dt->format('F Y'),
                        'short_label' => $dt->format('M Y'),
                        'start_date'  => $dt->format('Y-m-01'),
                        'end_date'    => $dt->format('Y-m-t'),
                        'count'       => 0,
                        'revenue'     => 0,
                        'by_type'     => [],
                        'by_revenue'  => [],
                        'types_count' => [],
                    ];
                }
                foreach ($records as $r) {
                    $rM    = date('Y-m', strtotime($r['violation_date']));
                    $vType = $r['violation_name'] ?: $r['violation_type'];
                    $amt   = (float) $r['penalty_amount'];
                    if (isset($buckets[$rM])) {
                        $buckets[$rM]['count']++;
                        $buckets[$rM]['revenue'] += $amt;
                        $buckets[$rM]['by_type'][$vType]     = ($buckets[$rM]['by_type'][$vType] ?? 0) + 1;
                        $buckets[$rM]['by_revenue'][$vType]  = ($buckets[$rM]['by_revenue'][$vType] ?? 0) + $amt;
                        $buckets[$rM]['types_count'][$vType] = ($buckets[$rM]['types_count'][$vType] ?? 0) + 1;
                    }
                }
                $buckets = array_values($buckets);
            }
        }

        // Peak and Top Violation Identification
        $peakBucket = null;
        $maxCount   = -1;

        foreach ($buckets as &$b) {
            $topV    = 'None';
            $topVCnt = 0;
            if (!empty($b['types_count'])) {
                arsort($b['types_count']);
                $topV    = key($b['types_count']);
                $topVCnt = current($b['types_count']);
            }
            $b['top_violation']       = $topV;
            $b['top_violation_count'] = $topVCnt;

            if ($b['count'] > $maxCount) {
                $maxCount   = $b['count'];
                $peakBucket = [
                    'label'     => $b['label'],
                    'count'     => $b['count'],
                    'top'       => $topV,
                    'top_count' => $topVCnt,
                ];
            }
        }
        unset($b);

        $topTypes = !empty($natureBreakdown)
            ? array_slice(array_column($natureBreakdown, 'violation_type'), 0, 5)
            : [];

        return [
            'timeline_buckets' => $buckets,
            'peak_period'      => $peakBucket,
            'top_types'        => $topTypes,
        ];
    }
}
