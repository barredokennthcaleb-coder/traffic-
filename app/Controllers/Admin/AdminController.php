<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\CitationReportGrouper;
use App\Libraries\ReportPeriod;
use App\Libraries\ViolatorIdentity;
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

        // The year selector scopes every analytics widget on the page, so it
        // is read before the data calls that depend on it.
        $year  = (int) ($this->request->getGet('year') ?? date('Y'));
        $month = $this->request->getGet('month');

        // Analytics Data
        $monthlyTrend          = $this->violationRecord->getMonthlyTrend($year);
        $violationTypesSummary = $this->violationRecord->getViolationTypesSummary();
        $statusDistribution    = $this->violationRecord->getViolatorStatusDistribution($year);

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
        // The KPI card follows the year selector (violators cited that year);
        // the all-time registry count stays available through $stats.
        $totalViolators  = $this->violationRecord->getViolatorCountForYear($year);

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
            // A past year's rolling window ends on its own December 31; the
            // current year keeps the true "last 12 weeks" ending today.
            $weeklyTrendLast12 = ($year === (int) date('Y'))
                ? $this->violationRecord->getWeeklyTrend(12)
                : $this->violationRecord->getWeeklyTrend(12, null, sprintf('%04d-12-31', $year));
        }

        // Daily apprehension report: the same kind of trend as the weekly card,
        // but one point per calendar day. It follows the selected month when
        // the admin drilled into one, otherwise it covers the last 14 days
        // (a past year stops on its own Dec 31).
        if ($selectedMonth !== null) {
            $dailyEnd  = date('Y-m-t', strtotime(sprintf('%04d-%02d-01', $year, $selectedMonth)));
            $dailyDays = (int) date('t', strtotime($dailyEnd));
        } else {
            $dailyEnd  = $year === (int) date('Y') ? date('Y-m-d') : sprintf('%04d-12-31', $year);
            $dailyDays = 14;
        }

        $dailyTrend = $this->violationRecord->getDailyTrend($dailyDays, null, $dailyEnd);

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
            'daily_trend'              => $dailyTrend,
            'monthly_breakdown'        => $monthlyBreakdown,
            'nature_summary'           => $natureSummary,
            'recent_violations'        => $recentViolations,
            'monthly_trend'            => $monthlyTrend,
            'violation_types'          => $violationTypesSummary,
            'status_distribution'      => $statusDistribution,
            'user_role_distribution'   => $userRoleDistribution,
            'user_status_distribution' => $userStatusDistribution,
            'top_violators_list'       => $this->violationRecord->getTopViolatorsList(3, 3, $year),
            // Scoped to the year the dashboard is showing, so the card and the
            // rankings modal agree with the money KPIs, the nature chart and
            // the Revenue Report for that same year.
            'top_violations_list'      => $this->violationRecord->getTopViolationsList(10, $year),
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

    /**
     * JSON API: the Weekly/Daily trend card's data for one year, optionally
     * narrowed to one month. The dashboard redraws the card from this instead
     * of reloading the whole page when the month dropdown changes.
     */
    public function dashboardTrendJson()
    {
        $year = (int) ($this->request->getGet('year') ?? date('Y'));

        if ($year < 2000 || $year > (int) date('Y') + 1) {
            $year = (int) date('Y');
        }

        $month = (int) ($this->request->getGet('month') ?? 0);

        if ($month < 1 || $month > 12) {
            $month = 0;
        }

        if ($month > 0) {
            // Same slice the page builds for a chosen month.
            $weekly    = $this->violationRecord->getWeeklyTrendForMonth($year, $month);
            $monthName = date('F', mktime(0, 0, 0, $month, 1, $year));
            $dailyEnd  = date('Y-m-t', strtotime(sprintf('%04d-%02d-01', $year, $month)));
            $dailyDays = (int) date('t', strtotime($dailyEnd));
        } else {
            $weekly    = ($year === (int) date('Y'))
                ? $this->violationRecord->getWeeklyTrend(12)
                : $this->violationRecord->getWeeklyTrend(12, null, sprintf('%04d-12-31', $year));
            $monthName = null;
            $dailyEnd  = $year === (int) date('Y') ? date('Y-m-d') : sprintf('%04d-12-31', $year);
            $dailyDays = 14;
        }

        $daily = $this->violationRecord->getDailyTrend($dailyDays, null, $dailyEnd);

        return $this->response
            ->setContentType('application/json')
            ->setJSON([
                'status'       => 'ok',
                'year'         => $year,
                'month'        => $month > 0 ? $month : null,
                'month_name'   => $monthName,
                'weekly_trend' => array_values($weekly),
                'daily_trend'  => array_values($daily),
            ]);
    }

    /**
     * JSON API: the Monthly Breakdown chart's ticket counts for one year.
     * Polled in place so the current month stays accurate as tickets are
     * issued or paid, without reloading the dashboard.
     */
    public function monthlyBreakdownJson()
    {
        $year = (int) ($this->request->getGet('year') ?? date('Y'));

        if ($year < 2000 || $year > (int) date('Y') + 1) {
            $year = (int) date('Y');
        }

        return $this->response
            ->setContentType('application/json')
            ->setJSON([
                'status'     => 'ok',
                'year'       => $year,
                'updated_at' => date('Y-m-d H:i:s'),
                'data'       => array_values($this->violationRecord->getMonthlyBreakdown($year)),
            ]);
    }

    public function reports()
    {
        // A report is only built once a period has been asked for. Landing on
        // the page with no period used to render the whole year, which read as
        // an unfiltered dump of every result; now the toolbar waits for
        // Weekly / Monthly / Full Year and the document appears with it.
        $periodType     = (string) ($this->request->getGet('period_type') ?? '');
        $awaitingFilter = ! ReportPeriod::isValid($periodType);
        $periodType     = $awaitingFilter ? 'yearly' : $periodType;

        $status     = $this->request->getGet('status');
        $typeId     = $this->request->getGet('violation_type_id');

        ['startDate' => $startDate, 'endDate' => $endDate, 'year' => $year, 'month' => $month]
            = $this->resolveDateRange($periodType);

        // A Daily Report carries a month sheet beside the day's detail. The
        // month comes from report_month, or from the chosen day when the month
        // is not supplied; the day then follows the month so the sheet and the
        // detail always describe the same calendar month.
        $dailyMonth = null;
        $monthStart = null;
        $monthEnd   = null;

        if (! $awaitingFilter && $periodType === 'daily') {
            $monthInput = (string) ($this->request->getGet('report_month') ?? '');

            $dailyMonth = preg_match('/^(\d{4})-(\d{2})$/', $monthInput, $monthMatch)
                ? ['year' => (int) $monthMatch[1], 'month' => (int) $monthMatch[2]]
                : ['year' => (int) date('Y', strtotime($startDate)), 'month' => (int) date('n', strtotime($startDate))];

            $monthStart = sprintf('%04d-%02d-01', $dailyMonth['year'], $dailyMonth['month']);
            $monthEnd   = date('Y-m-t', strtotime($monthStart));

            if (date('Y-m', strtotime($startDate)) !== date('Y-m', strtotime($monthStart))) {
                $startDate = $endDate = $monthStart;
            }
        }

        $reportData = $this->violationRecord->getReportData($startDate, $endDate, $status, $typeId);
        // One organized block per violator: every offense keeps its own status,
        // fine and payment date instead of a merged "Paid ×N" ticket row.
        $citationGroups = CitationReportGrouper::groupByViolator($reportData['records'] ?? []);
        $allTypes   = $this->violationTypes->where('status', 'active')->findAll();

        // Offense status split for the report's status bar — the same bar the
        // Violations Summary Report shows, so both reports state the status
        // mix the same way: every recorded offense counted once.
        $statusCounts = ['Paid' => 0, 'Pending' => 0, 'Cancelled' => 0];
        foreach ($reportData['records'] ?? [] as $rec) {
            $recStatus = (string) ($rec['status'] ?? 'Pending');
            if (isset($statusCounts[$recStatus])) {
                $statusCounts[$recStatus]++;
            }
        }

        $timelineInfo = $this->buildTimelineBuckets(
            $reportData['records'] ?? [],
            $periodType,
            $startDate,
            $endDate,
            $reportData['nature_breakdown'] ?? []
        );

        // The month sheet's three grids read one month record set, so their
        // footers, the day's summary and the dashboard agree by construction.
        $dailySheets = [];

        if ($dailyMonth !== null && $monthStart !== null && $monthEnd !== null) {
            $monthRecords = ($monthStart === $startDate && $monthEnd === $endDate)
                ? $reportData
                : $this->violationRecord->getReportData($monthStart, $monthEnd, $status, $typeId);

            $dailySheets = $this->buildDailyMonthSheets(
                $monthRecords['records'] ?? [],
                $dailyMonth['year'],
                $dailyMonth['month']
            );
        }

        $data = array_merge($reportData, [
            'title'            => 'Generated Traffic Violation Report',
            // An empty period keeps every period box closed and the document
            // hidden until the reader picks one.
            'period_type'      => $awaitingFilter ? '' : $periodType,
            'awaiting_filter'  => $awaitingFilter,
            'status_filter'    => $status,
            'type_filter'      => $typeId,
            'all_types'        => $allTypes,
            'start_date'       => $startDate,
            'end_date'         => $endDate,
            'month'            => ($periodType === 'monthly') ? sprintf('%02d', (int)$month) : date('m'),
            'year'             => ($periodType === 'monthly' || $periodType === 'yearly') ? (string)(int)$year : date('Y'),
            'timeline_buckets' => $timelineInfo['timeline_buckets'],
            'peak_period'      => $timelineInfo['peak_period'],
            'top_types'        => $timelineInfo['top_types'],
            'citation_groups'   => $citationGroups,
            'status_counts'     => $statusCounts,
            'daily_month'       => $dailyMonth,
            'daily_sheets'      => $dailySheets,
        ]);

        return view('admin/reports/index', $data);
    }

    /**
     * The Daily Report's month sheet: one column per calendar day and three
     * tallies — apprehensions (distinct tickets), money collected (Paid only)
     * and distinct violators — all read from the same month records.
     *
     * Cancelled (void) citations are excluded from every tally, a ticket with
     * several offenses is one apprehension, and a person caught twice in the
     * month is counted on each of those days but once in the unique total.
     *
     * @param array<int, array<string, mixed>> $records
     *
     * @return array<string, mixed>
     */
    private function buildDailyMonthSheets(array $records, int $year, int $month): array
    {
        $days = (int) date('t', strtotime(sprintf('%04d-%02d-01', $year, $month)));

        // Every array is keyed 1–31 so the sheet can print a full grid even for
        // February; days outside the month stay blank.
        $tickets = array_fill(1, 31, 0);
        $revenue = array_fill(1, 31, 0.0);
        $people  = array_fill(1, 31, 0);

        $seenTicketsByDay = [];
        $seenPeopleByDay  = [];
        $uniquePeople     = [];

        foreach ($records as $record) {
            $status = (string) ($record['status'] ?? 'Pending');

            if ($status === 'Cancelled') {
                continue;
            }

            $stamp = strtotime((string) ($record['violation_date'] ?? ''));

            if ($stamp === false) {
                continue;
            }

            $day = (int) date('j', $stamp);

            if ($day < 1 || $day > $days) {
                continue;
            }

            // One apprehension per ticket, however many offenses it carries.
            $ticketKey = trim((string) ($record['ticket_id'] ?? ''));

            if ($ticketKey === '') {
                $ticketKey = 'row-' . ($record['id'] ?? uniqid('', true));
            }

            if (! isset($seenTicketsByDay[$day][$ticketKey])) {
                $seenTicketsByDay[$day][$ticketKey] = true;
                $tickets[$day]++;
            }

            // Revenue is money actually collected, on the citation's own date
            // (the same basis the dashboard's revenue line uses).
            if ($status === 'Paid') {
                $revenue[$day] += (float) ($record['penalty_amount'] ?? 0);
            }

            // A person is the linked violator record, or their name and plate
            // for legacy rows that were never linked.
            $violatorId = (int) ($record['violator_id'] ?? 0);
            $personKey  = $violatorId > 0
                ? 'v' . $violatorId
                : 'n' . mb_strtolower(trim((string) ($record['driver_name'] ?? '')))
                    . '|' . mb_strtolower(trim((string) ($record['license_plate'] ?? '')));

            $seenPeopleByDay[$day][$personKey] = true;
            $uniquePeople[$personKey]          = true;
        }

        foreach ($seenPeopleByDay as $day => $set) {
            $people[$day] = count($set);
        }

        return [
            'year'          => $year,
            'month'         => $month,
            'days'          => $days,
            'month_value'   => sprintf('%04d-%02d', $year, $month),
            'label'         => date('F Y', strtotime(sprintf('%04d-%02d-01', $year, $month))),
            'short_label'   => date('M', strtotime(sprintf('%04d-%02d-01', $year, $month))),
            'tickets'       => $tickets,
            'tickets_total' => array_sum($tickets),
            'revenue'       => $revenue,
            'revenue_total' => array_sum($revenue),
            'people'        => $people,
            'people_total'  => count($uniquePeople),
        ];
    }

    /**
     * Dedicated Violations Summary Only Report (focused strictly on violation frequency,
     * counts, categories — without monetary amounts).
     */
    public function violationsReport()
    {
        // Same rule as the revenue report: nothing is rendered until a period
        // is chosen, so the page never opens on a full-year result set.
        $periodType     = (string) ($this->request->getGet('period_type') ?? '');
        $awaitingFilter = ! ReportPeriod::isValid($periodType);
        $periodType     = $awaitingFilter ? 'yearly' : $periodType;

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

        // Dedicated deep-dive for the period's single most committed offense,
        // rendered as the report document's own section (on-screen and CSV).
        $topOffenseInsight = $this->buildTopOffenseInsight(
            $reportData['records'] ?? [],
            $reportData['nature_breakdown'] ?? [],
            $timelineInfo['timeline_buckets']
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

        // Daily Traffic Apprehension Record: the station's two tally sheets
        // (offenses committed, violators), built from the very same records
        // the rest of the report reads so their grids and this report can
        // never disagree.
        $apprehensionMatrix = $this->buildApprehensionMatrix(
            $reportData['records'] ?? [],
            $periodType,
            $startDate,
            $endDate,
            is_string($status) ? $status : null
        );
        $violatorMatrix = $this->buildViolatorMatrix(
            $reportData['records'] ?? [],
            $periodType,
            $startDate,
            $endDate,
            is_string($status) ? $status : null
        );

        // Personnel sheet of the Daily Traffic Apprehension Record: roster
        // down the rows, day columns across the top.
        $personnelMatrix = $this->buildPersonnelMatrix(
            $reportData['records'] ?? [],
            $periodType,
            $startDate,
            $endDate,
            is_string($status) ? $status : null
        );

        // How many people were apprehended in the period. Rows are counted
        // once per person: the linked violator record when the citation is
        // attached to one, and otherwise the name and plate the citation was
        // written with, so the same driver is never counted twice.
        $violatorKeys = [];
        foreach ($reportData['records'] ?? [] as $rec) {
            $violatorId = (int) ($rec['violator_id'] ?? 0);

            if ($violatorId > 0) {
                $violatorKeys['v' . $violatorId] = true;
                continue;
            }

            $name  = mb_strtolower(trim((string) ($rec['driver_name'] ?? '')));
            $plate = mb_strtolower(trim((string) ($rec['license_plate'] ?? '')));
            $violatorKeys['n' . $name . '|' . $plate] = true;
        }
        $violatorCount = count($violatorKeys);

        $data = [
            'title'             => 'Traffic Violations Summary Report',
            'period_type'       => $awaitingFilter ? '' : $periodType,
            'awaiting_filter'   => $awaitingFilter,
            // "focus=offense" renders a dedicated document carrying only the
            // period's most committed violation — everything else is left out
            // of the sheet so it can be generated and printed on its own.
            'focus_mode'        => trim((string) $this->request->getGet('focus')) === 'offense' ? 'offense' : '',
            'status_filter'     => $status,
            'type_filter'       => $typeId,
            'all_types'         => $allTypes,
            'start_date'        => $startDate,
            'end_date'          => $endDate,
            'month'             => ($periodType === 'monthly') ? sprintf('%02d', (int)$month) : date('m'),
            'year'              => ($periodType === 'monthly' || $periodType === 'yearly') ? (string)(int)$year : date('Y'),
            'records'           => $reportData['records'] ?? [],
            'summary'           => $reportData['summary'] ?? [],
            'nature_breakdown'  => $reportData['nature_breakdown'] ?? [],
            'officer_breakdown' => $reportData['officer_breakdown'] ?? [],
            'timeline_buckets'  => $timelineInfo['timeline_buckets'],
            'peak_period'       => $timelineInfo['peak_period'],
            'top_types'         => $timelineInfo['top_types'],
            'top_offense'       => $topOffense,
            'top_offense_insight' => $topOffenseInsight,
            'unique_categories' => $uniqueCategories,
            'violator_count'    => $violatorCount,
            'personnel_matrix'  => $personnelMatrix,
            'status_counts'     => $statusCounts,
            'apprehension_matrix' => $apprehensionMatrix,
            'violator_matrix'     => $violatorMatrix,
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
        $width    = 10; // widest table: Detailed Citation Records

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
        $this->csvRow($output, ['Note: Cancelled (void) citations are excluded from this section.'], $width);
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

        // Section III — every citation recorded in the period, one line per
        // offense. (The most-committed deep dive is generated on demand from
        // the report page's "Most Committed Only" button, so it is not part
        // of this full-report export.)
        $this->csvRow($output, ['III. DETAILED CITATION RECORDS'], $width);
        $this->csvRow($output, ['Ticket ID', 'Driver Name', 'License Plate', "Driver's License (DL) Number", 'Violation Type', 'Status', 'Official Receipt (OR) Number', 'Apprehending Officer', 'Date & Time', 'Location'], $width);
        foreach ($report['records'] as $r) {
            $this->csvRow($output, [
                $r['ticket_id'],
                $r['driver_name'],
                $r['license_plate'],
                $r['license_number'] ?? 'N/A',
                $r['violation_name'] ?: $r['violation_type'],
                $r['status'],
                $r['receipt_number'] ?? '',
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
        $width    = 12; // widest table: Detailed Citation Records

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
        $this->csvRow($output, ['Total Fines (PHP)', number_format((float) $report['summary']['total_assessed'], 2, '.', '')], $width);
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
        $this->csvRow($output, ['Note: Cancelled (void) citations are excluded from this section.'], $width);
        $this->csvRow($output, [], $width);

        // Section II — mirrors the on-screen "Offense Category Breakdown"
        $this->csvRow($output, ['II. OFFENSE CATEGORY BREAKDOWN'], $width);
        $this->csvRow($output, ['#', 'Violation / Offense Nature', 'Total Incidents', 'Fine Amount (PHP)', 'Percentage Share (%)'], $width);
        $i = 1;
        $natureIncidentTotal = 0;
        $natureAmountTotal   = 0.0;
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

            $natureIncidentTotal += (int) $nb['count'];
            $natureAmountTotal   += (float) $nb['amount'];
        }
        // The same Total row the screen shows, so the export matches the page.
        $this->csvRow($output, [
            'Total',
            '',
            $natureIncidentTotal,
            number_format($natureAmountTotal, 2, '.', ''),
            '100.0',
        ], $width);
        $this->csvRow($output, [], $width);

        // Section III — mirrors the on-screen "Detailed Citation Records": one
        // line per offense so each fine and its payment date are specific,
        // ordered in one block per violator so the same person's tickets and
        // payments stay together.
        $citationGroups = CitationReportGrouper::groupByViolator($report['records'] ?? []);

        $this->csvRow($output, ['III. DETAILED CITATION RECORDS'], $width);
        $this->csvRow($output, ['Ticket ID', 'Driver Name', 'License Plate', "Driver's License (DL) Number", 'Violation Type', 'Amount (PHP)', 'Status', 'Official Receipt (OR) Number', 'Apprehending Officer', 'Date & Time', 'Date Paid', 'Location'], $width);

        foreach ($citationGroups as $group) {
            foreach ($group['tickets'] as $ticket) {
                foreach ($ticket['violations'] as $offense) {
                    $this->csvRow($output, [
                        $ticket['ticket_id'],
                        $group['driver_name'],
                        $group['license_plate'],
                        $group['license_number'] !== '' ? $group['license_number'] : 'N/A',
                        $offense['name'],
                        number_format((float) $offense['amount'], 2, '.', ''),
                        $offense['status'],
                        $ticket['receipt_number'],
                        $ticket['officer_name'] !== '' ? $ticket['officer_name'] : 'System',
                        ! empty($ticket['violation_date']) ? date('Y-m-d H:i', strtotime($ticket['violation_date'])) : '',
                        ! empty($offense['paid_date']) ? date('Y-m-d H:i', strtotime($offense['paid_date'])) : '',
                        $ticket['location'] ?? '',
                    ], $width);
                }
            }
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
     * The rules live in App\Libraries\ReportPeriod so that reports(),
     * violationsReport(), exportViolationsCsv(), exportReportCsv() — and the
     * Violator Reports under reports/violators — all share one implementation.
     *
     * @return array{startDate: string, endDate: string, year: string, month: string}
     */
    private function resolveDateRange(string $periodType): array
    {
        return ReportPeriod::resolve($this->request, $periodType);
    }

    /**
     * Daily Traffic Apprehension Record: the matrix sheet stations keep on
     * paper, generated from the report's own records. Offense types run down
     * the rows, the period's day/month slots run across, each cell counts the
     * offenses recorded in that slot, and every row ends with its own Total
     * and the number of distinct violators behind it. The sheet's footer
     * tallies the slot totals plus the period's violators and repeat
     * citations, so "what was recorded" and "who is on the record" read at a
     * glance.
     *
     * Cancelled citations are void and never count as apprehensions; when the
     * status filter itself asks for Cancelled, those become the sheet's
     * subject instead (the audit view of the same matrix).
     *
     * @param list<array<string, mixed>> $records
     *
     * @return array{
     *     period_label: string,
     *     column_noun: string,
     *     columns: list<array{key: int|string, label: string, title: string}>,
     *     rows: list<array{name: string, cells: list<int>, total: int, violators: int}>,
     *     column_totals: list<int>,
     *     grand_total: int,
     *     distinct_violators: int,
     *     repeat_violators: int,
     *     excluded_cancelled: int
     * }
     */
    private function buildApprehensionMatrix(array $records, string $periodType, string $startDate, string $endDate, ?string $status): array
    {
        $status             = is_string($status) ? trim($status) : '';
        $cancelledIsSubject = $status === 'Cancelled';

        // Columns follow the period the report is already showing, and each
        // month column carries its own month key, so the peak-month day sheet
        // can be generated directly from it later.
        $columnsInfo = $this->buildApprehensionColumns($periodType, $startDate, $endDate);
        $columns     = $columnsInfo['columns'];
        $customDaily = $columnsInfo['custom_daily'];

        $columnKeys = [];
        foreach ($columns as $index => $column) {
            $columnKeys[$column['key']] = $index;
        }
        $columnCount = count($columns);

        $typeRows          = [];
        $personTickets     = [];
        $allPersons        = [];
        $excludedCancelled = 0;

        foreach ($records as $record) {
            $recordStatus = (string) ($record['status'] ?? 'Pending');

            // The matrix counts apprehensions, and a cancelled citation is
            // void. In the audit view (status filter = Cancelled) the
            // cancelled rows are the only subject, so everything else is
            // skipped there instead.
            if ($cancelledIsSubject) {
                if ($recordStatus !== 'Cancelled') {
                    continue;
                }
            } elseif ($recordStatus === 'Cancelled') {
                $excludedCancelled++;
                continue;
            }

            $ts = strtotime((string) ($record['violation_date'] ?? ''));
            if ($ts === false) {
                continue;
            }

            $columnKey = $this->apprehensionColumnBucket($periodType, $customDaily, $ts);

            if (! isset($columnKeys[$columnKey])) {
                continue;
            }

            $name = trim((string) (! empty($record['violation_name']) ? $record['violation_name'] : ($record['violation_type'] ?? '')));
            if ($name === '') {
                $name = 'Unknown offense';
            }

            if (! isset($typeRows[$name])) {
                $typeRows[$name] = ['cells' => array_fill(0, $columnCount, 0), 'persons' => []];
            }

            $typeRows[$name]['cells'][$columnKeys[$columnKey]]++;

            // The same person keying the citation ledger: the linked violator
            // when one exists, the shared identity otherwise — so a repeat
            // offender is never counted twice under two spellings.
            $violatorId = (int) ($record['violator_id'] ?? 0);
            $personKey  = $violatorId > 0
                ? 'v:' . $violatorId
                : 'k:' . ViolatorIdentity::key($record, (string) ($record['ticket_id'] ?? ''));

            $typeRows[$name]['persons'][$personKey] = true;
            $allPersons[$personKey]                 = true;

            $ticketKey = trim((string) ($record['ticket_id'] ?? ''));
            if ($ticketKey === '') {
                $ticketKey = '#' . (string) ($record['id'] ?? '');
            }
            $personTickets[$personKey][$ticketKey] = true;
        }

        $rows = [];
        foreach ($typeRows as $name => $row) {
            $rows[] = [
                'name'      => $name,
                'cells'     => $row['cells'],
                'total'     => array_sum($row['cells']),
                'violators' => count($row['persons']),
            ];
        }
        // Most recorded offense first, alphabetical on a tie — the same order
        // the report's ranking tables use.
        usort($rows, static function ($a, $b) {
            return ($b['total'] <=> $a['total']) ?: strcasecmp($a['name'], $b['name']);
        });

        $columnTotals = array_fill(0, $columnCount, 0);
        foreach ($rows as $row) {
            foreach ($row['cells'] as $index => $count) {
                $columnTotals[$index] += $count;
            }
        }

        $repeatViolators = 0;
        foreach ($personTickets as $tickets) {
            if (count($tickets) >= 2) {
                $repeatViolators++;
            }
        }

        // Busiest slot of the sheet, highlighted on screen and named in the
        // legend.
        $peakIndex = null;
        $peakMax   = 0;
        foreach ($columnTotals as $index => $total) {
            if ($total > $peakMax) {
                $peakMax   = $total;
                $peakIndex = $index;
            }
        }

        $periodShort = $columnsInfo['period_short'];

        return [
            'period_label'       => $columnsInfo['period_label'],
            'period_short'       => $periodShort,
            'column_noun'        => $columnsInfo['column_noun'],
            'tab_label'          => 'Violations • ' . $periodShort,
            'panel_title'        => 'OFFENSES COMMITTED — ' . strtoupper($periodShort),
            'columns'            => $columns,
            'rows'               => $rows,
            'column_totals'      => $columnTotals,
            'grand_total'        => array_sum($columnTotals),
            'right_total'        => count($allPersons),
            'distinct_violators' => count($allPersons),
            'repeat_violators'   => $repeatViolators,
            'excluded_cancelled' => $excludedCancelled,
            'peak_index'         => $peakIndex,
            'peak_label'         => $peakIndex !== null ? ($columns[$peakIndex]['title'] ?? null) : null,
        ];
    }

    /**
     * Personnel sheet of the Daily Traffic Apprehension Record: the station
     * roster down the rows and the period's day columns across the top, each
     * cell counting the apprehensions an officer recorded in that day — the
     * paper tally the office keeps, generated from the citation ledger.
     *
     * One apprehension means one ticket, so a citation carrying three offenses
     * still puts a single mark in the cell. Cancelled citations are void and
     * never counted; when the status filter itself asks for Cancelled, those
     * become the sheet's subject instead.
     *
     * @param list<array<string, mixed>> $records
     *
     * @return array<string, mixed>
     */
    private function buildPersonnelMatrix(array $records, string $periodType, string $startDate, string $endDate, ?string $status): array
    {
        $status             = is_string($status) ? trim($status) : '';
        $cancelledIsSubject = $status === 'Cancelled';

        $columnsInfo = $this->buildApprehensionColumns($periodType, $startDate, $endDate);
        $columns     = $columnsInfo['columns'];
        $customDaily = $columnsInfo['custom_daily'];

        $columnKeys = [];
        foreach ($columns as $index => $column) {
            $columnKeys[$column['key']] = $index;
        }
        $columnCount = count($columns);

        // The roster is everyone who can put a citation on record, listed the
        // way the paper form lists them: surname first, alphabetically. A unit
        // with no apprehensions that day keeps its row and prints blank, which
        // is the whole point of a duty tally.
        $roster = $this->users
            ->select('id, firstname, lastname, username, role')
            ->whereIn('role', ['enforcer', 'admin'])
            ->orderBy('lastname', 'ASC')
            ->orderBy('firstname', 'ASC')
            ->findAll();

        $rows = [];
        foreach ($roster as $officer) {
            $officerId        = (int) $officer['id'];
            $rows[$officerId] = [
                'id'    => $officerId,
                'name'  => $this->personnelName($officer),
                'role'  => (string) ($officer['role'] ?? ''),
                'cells' => array_fill(0, $columnCount, 0),
                'total' => 0,
            ];
        }

        $seen              = [];
        $excludedCancelled = 0;

        foreach ($records as $record) {
            $recordStatus = (string) ($record['status'] ?? 'Pending');

            if ($cancelledIsSubject) {
                if ($recordStatus !== 'Cancelled') {
                    continue;
                }
            } elseif ($recordStatus === 'Cancelled') {
                $excludedCancelled++;
                continue;
            }

            $ts = strtotime((string) ($record['violation_date'] ?? ''));
            if ($ts === false) {
                continue;
            }

            $columnKey = $this->apprehensionColumnBucket($periodType, $customDaily, $ts);
            if (! isset($columnKeys[$columnKey])) {
                continue;
            }
            $index = $columnKeys[$columnKey];

            $recordedBy = (int) ($record['officer_id'] ?? 0);
            if ($recordedBy <= 0) {
                $recordedBy = (int) ($record['created_by'] ?? 0);
            }

            // A citation recorded by an account that is no longer on the
            // roster still belongs on the sheet, or the column totals would
            // not add up to the report's own figures.
            if (! isset($rows[$recordedBy])) {
                $rows[$recordedBy] = [
                    'id'    => $recordedBy,
                    'name'  => trim((string) ($record['officer_name'] ?? '')) ?: 'System / Admin',
                    'role'  => 'former',
                    'cells' => array_fill(0, $columnCount, 0),
                    'total' => 0,
                ];
            }

            $ticketKey = trim((string) ($record['ticket_id'] ?? ''));
            if ($ticketKey === '') {
                $ticketKey = '#' . (string) ($record['id'] ?? '');
            }

            // One mark per ticket per day, however many offenses it carries.
            $dedupe = $recordedBy . '|' . $ticketKey . '|' . $index;
            if (isset($seen[$dedupe])) {
                continue;
            }
            $seen[$dedupe] = true;

            $rows[$recordedBy]['cells'][$index]++;
            $rows[$recordedBy]['total']++;
        }

        $rows = array_values($rows);

        // Roster order first (already alphabetical), anyone else after it.
        usort($rows, static function ($a, $b) {
            $aFormer = ($a['role'] ?? '') === 'former';
            $bFormer = ($b['role'] ?? '') === 'former';

            if ($aFormer !== $bFormer) {
                return $aFormer ? 1 : -1;
            }

            return strcasecmp((string) $a['name'], (string) $b['name']);
        });

        $columnTotals = array_fill(0, $columnCount, 0);
        $activeRows   = 0;

        foreach ($rows as $row) {
            foreach ($row['cells'] as $index => $count) {
                $columnTotals[$index] += $count;
            }

            if ($row['total'] > 0) {
                $activeRows++;
            }
        }

        return [
            'period_label'       => $columnsInfo['period_label'],
            'column_noun'        => $columnsInfo['column_noun'],
            'columns'            => $columns,
            'rows'               => $rows,
            'column_totals'      => $columnTotals,
            'grand_total'        => array_sum($columnTotals),
            'officer_count'      => count($rows),
            'active_officers'    => $activeRows,
            'excluded_cancelled' => $excludedCancelled,
        ];
    }

    /**
     * A person's name the way the paper roster writes it: surname first.
     */
    private function personnelName(array $officer): string
    {
        $last  = $this->personnelNamePart((string) ($officer['lastname'] ?? ''));
        $first = $this->personnelNamePart((string) ($officer['firstname'] ?? ''));

        if ($last !== '' || $first !== '') {
            return $last !== '' ? $last . ', ' . $first : $first;
        }

        return trim((string) ($officer['username'] ?? '')) ?: 'Unnamed';
    }

    /**
     * Names are stored as typed; the roster sheet prints them in title case so
     * a lowercase entry does not stand out beside a properly written one.
     */
    private function personnelNamePart(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        $lower = mb_strtolower($value, 'UTF-8');

        if ($value === $lower || $value === mb_strtoupper($value, 'UTF-8')) {
            return mb_convert_case($lower, MB_CASE_TITLE, 'UTF-8');
        }

        return $value;
    }

    /**
     * Column set for the Daily Traffic Apprehension Record sheets, following
     * the period the report is already showing: a monthly sheet counts days
     * 1..31 like the printed form, a yearly sheet counts the twelve months, a
     * week counts its seven days, and a custom range stays daily up to two
     * weeks and falls back to months beyond that (the same rule the timeline
     * buckets use). Day columns carry the weekday initial under the number
     * ("6 / S"), the way the tally sheet reads.
     *
     * @return array{
     *     columns: list<array{key: int|string, label: string, title: string, sub: string|null}>,
     *     column_noun: string,
     *     period_label: string,
     *     period_short: string,
     *     custom_daily: bool
     * }
     */
    private function buildApprehensionColumns(string $periodType, string $startDate, string $endDate): array
    {
        $customDaily = $periodType === 'daily' || ($periodType === 'custom'
            && ((int) ceil((strtotime($endDate) - strtotime($startDate)) / 86400)) <= 14);

        $columns = [];

        $weekdayInitial = static function (string $date): string {
            return strtoupper(substr(date('D', strtotime($date)), 0, 1));
        };

        if ($periodType === 'monthly') {
            $days    = (int) date('t', strtotime($startDate));
            $monthTs = strtotime($startDate);
            for ($d = 1; $d <= $days; $d++) {
                $day       = date('Y-m-d', mktime(0, 0, 0, (int) date('n', $monthTs), $d, (int) date('Y', $monthTs)));
                $columns[] = [
                    'key'   => $d,
                    'label' => (string) $d,
                    'title' => date('M d, Y', strtotime($day)),
                    'sub'   => $weekdayInitial($day),
                ];
            }
            $periodLabel = 'For the Month of ' . date('F Y', $monthTs);
            $columnNoun  = 'Day';
        } elseif ($periodType === 'weekly' || $customDaily) {
            $daysCount = $periodType === 'weekly'
                ? 7
                : ((int) ceil((strtotime($endDate) - strtotime($startDate)) / 86400)) + 1;
            $startTs = strtotime($startDate);
            for ($i = 0; $i < $daysCount; $i++) {
                $day       = date('Y-m-d', strtotime("+$i days", $startTs));
                $columns[] = [
                    'key'   => $day,
                    'label' => date('j', strtotime($day)),
                    'title' => date('D, M d, Y', strtotime($day)),
                    'sub'   => $weekdayInitial($day),
                ];
            }
            $periodLabel = match ($periodType) {
                'weekly' => 'Week of ' . date('M d', strtotime($startDate)) . ' – ' . date('M d, Y', strtotime($endDate)),
                'daily'  => date('l, M d, Y', strtotime($startDate)),
                default  => date('M d, Y', strtotime($startDate)) . ' – ' . date('M d, Y', strtotime($endDate)),
            };
            $columnNoun  = 'Day';
        } elseif ($periodType === 'yearly') {
            $y = (int) date('Y', strtotime($startDate));
            for ($m = 1; $m <= 12; $m++) {
                $columns[] = [
                    'key'   => $m,
                    'label' => date('M', mktime(0, 0, 0, $m, 1, $y)),
                    'title' => date('F Y', mktime(0, 0, 0, $m, 1, $y)),
                    'sub'   => null,
                ];
            }
            $periodLabel = 'CY ' . $y;
            $columnNoun  = 'Month';
        } else {
            // Custom range longer than two weeks: one column per month.
            $start    = new \DateTime($startDate);
            $end      = new \DateTime($endDate);
            $interval = new \DateInterval('P1M');
            $period   = new \DatePeriod($start, $interval, $end->modify('+1 day'));
            foreach ($period as $dt) {
                $columns[] = [
                    'key'   => $dt->format('Y-m'),
                    'label' => $dt->format('M'),
                    'title' => $dt->format('F Y'),
                    'sub'   => null,
                ];
            }
            $periodLabel = date('M d, Y', strtotime($startDate)) . ' – ' . date('M d, Y', strtotime($endDate));
            $columnNoun  = 'Month';
        }

        return [
            'columns'      => $columns,
            'column_noun'  => $columnNoun,
            'period_label' => $periodLabel,
            'period_short' => $this->apprehensionPeriodShort($periodType, $startDate, $endDate, $customDaily),
            'custom_daily' => $customDaily,
        ];
    }

    /**
     * The column bucket a violation date falls into for a given sheet layout.
     */
    private function apprehensionColumnBucket(string $periodType, bool $customDaily, int $timestamp): int|string
    {
        if ($periodType === 'monthly') {
            return (int) date('j', $timestamp);
        }

        if ($periodType === 'weekly' || $customDaily) {
            return date('Y-m-d', $timestamp);
        }

        if ($periodType === 'yearly') {
            return (int) date('n', $timestamp);
        }

        return date('Y-m', $timestamp);
    }

    /**
     * Compact label for tab captions and sheet headings ("CY 2026",
     * "Nov 2026", "Nov 03–09").
     */
    private function apprehensionPeriodShort(string $periodType, string $startDate, string $endDate, bool $customDaily): string
    {
        if ($periodType === 'yearly') {
            return 'CY ' . date('Y', strtotime($startDate));
        }

        if ($periodType === 'monthly') {
            return date('M Y', strtotime($startDate));
        }

        if ($periodType === 'weekly') {
            return date('M d', strtotime($startDate)) . '–' . date('d', strtotime($endDate));
        }

        if ($periodType === 'daily') {
            return date('M d, Y', strtotime($startDate));
        }

        if ($customDaily) {
            return date('M d', strtotime($startDate)) . '–' . date('M d, Y', strtotime($endDate));
        }

        return date('M Y', strtotime($startDate)) . ' – ' . date('M Y', strtotime($endDate));
    }

    /**
     * Violator sheet of the Daily Traffic Apprehension Record: the period's
     * most-apprehended people down the rows, each cell counting the citations
     * (distinct tickets) recorded for them in that slot, with their total
     * citations and total offenses at the right. Only the top $topLimit rows
     * are carried (the footnote names how many were left off), so the sheet
     * stays a record and not a directory.
     *
     * @param list<array<string, mixed>> $records
     *
     * @return array<string, mixed>
     */
    private function buildViolatorMatrix(array $records, string $periodType, string $startDate, string $endDate, ?string $status, int $topLimit = 15): array
    {
        $status             = is_string($status) ? trim($status) : '';
        $cancelledIsSubject = $status === 'Cancelled';

        $columnsInfo = $this->buildApprehensionColumns($periodType, $startDate, $endDate);
        $columns     = $columnsInfo['columns'];
        $columnKeys  = [];
        foreach ($columns as $index => $column) {
            $columnKeys[$column['key']] = $index;
        }
        $columnCount = count($columns);

        $persons       = [];
        $periodTickets = [];

        foreach ($records as $record) {
            $recordStatus = (string) ($record['status'] ?? 'Pending');

            if ($cancelledIsSubject) {
                if ($recordStatus !== 'Cancelled') {
                    continue;
                }
            } elseif ($recordStatus === 'Cancelled') {
                continue;
            }

            $ts = strtotime((string) ($record['violation_date'] ?? ''));
            if ($ts === false) {
                continue;
            }

            $columnKey = $this->apprehensionColumnBucket($periodType, $columnsInfo['custom_daily'], $ts);
            if (! isset($columnKeys[$columnKey])) {
                continue;
            }

            $columnIndex = $columnKeys[$columnKey];

            $violatorId = (int) ($record['violator_id'] ?? 0);
            $personKey  = $violatorId > 0
                ? 'v:' . $violatorId
                : 'k:' . ViolatorIdentity::key($record, (string) ($record['ticket_id'] ?? ''));

            if (! isset($persons[$personKey])) {
                $name  = ViolatorIdentity::shortName(
                    $record['first_name'] ?? null,
                    $record['middle_name'] ?? null,
                    $record['last_name'] ?? null,
                    $record['driver_name'] ?? null
                );
                $plate = trim((string) ($record['license_plate'] ?? ''));

                $persons[$personKey] = [
                    'name'         => $name !== '' ? $name : ($plate !== '' ? $plate : 'Unknown violator'),
                    'plate'        => $plate,
                    'cells'        => array_fill(0, $columnCount, 0),
                    'cell_tickets' => [],
                    'ticket_meta'  => [],
                    'offenses'     => 0,
                ];
            }

            $ticketKey = trim((string) ($record['ticket_id'] ?? ''));
            if ($ticketKey === '') {
                $ticketKey = '#' . (string) ($record['id'] ?? '');
            }

            // One entry per ticket: the date it was recorded, the offenses
            // it carried and its status — the detail the sheet reads back
            // per violator, so "who, what and when" needs no second page.
            $offenseName = trim((string) (! empty($record['violation_name']) ? $record['violation_name'] : ($record['violation_type'] ?? '')));
            if ($offenseName === '') {
                $offenseName = 'Unknown offense';
            }

            if (! isset($persons[$personKey]['ticket_meta'][$ticketKey])) {
                $persons[$personKey]['ticket_meta'][$ticketKey] = [
                    'date'     => (string) ($record['violation_date'] ?? ''),
                    'offenses' => [],
                    'statuses' => [],
                ];
            }
            $persons[$personKey]['ticket_meta'][$ticketKey]['offenses'][$offenseName] = true;
            $persons[$personKey]['ticket_meta'][$ticketKey]['statuses'][$recordStatus] = true;

            $persons[$personKey]['cell_tickets'][$columnIndex][$ticketKey] = true;
            $persons[$personKey]['offenses']++;
            $periodTickets[$ticketKey] = true;
        }

        $rows = [];
        foreach ($persons as $person) {
            $cells = [];
            foreach ($person['cells'] as $index => $count) {
                $cells[$index] = count($person['cell_tickets'][$index] ?? []);
            }

            // The person's citations, newest first: date, the offenses the
            // ticket carried and its status.
            $details = [];
            foreach ($person['ticket_meta'] as $ticketKey => $meta) {
                $details[] = [
                    'ticket_id' => str_starts_with((string) $ticketKey, '#') ? '' : (string) $ticketKey,
                    'date'      => $meta['date'],
                    'offenses'  => array_keys($meta['offenses']),
                    'status'    => implode(' / ', array_keys($meta['statuses'])),
                ];
            }
            usort($details, static function ($a, $b) {
                return strcmp((string) $b['date'], (string) $a['date']);
            });

            $rows[] = [
                'name'     => $person['name'],
                'plate'    => $person['plate'],
                'cells'    => $cells,
                'total'    => array_sum($cells),
                'offenses' => $person['offenses'],
                'details'  => $details,
            ];
        }

        // Most apprehended first, alphabetical on a tie — the ledger's order.
        usort($rows, static function ($a, $b) {
            return ($b['total'] <=> $a['total']) ?: strcasecmp($a['name'], $b['name']);
        });

        $totalViolators = count($rows);
        $hiddenRows     = max(0, $totalViolators - max(1, $topLimit));
        $rows           = array_slice($rows, 0, max(1, $topLimit));

        $columnTotals = array_fill(0, $columnCount, 0);
        foreach ($rows as $row) {
            foreach ($row['cells'] as $index => $count) {
                $columnTotals[$index] += $count;
            }
        }

        return [
            'period_short'   => $columnsInfo['period_short'],
            'column_noun'    => $columnsInfo['column_noun'],
            'tab_label'      => 'Violators • ' . $columnsInfo['period_short'],
            'panel_title'    => 'VIOLATORS — ' . strtoupper($columnsInfo['period_short']),
            'columns'        => $columns,
            'rows'           => $rows,
            'column_totals'  => $columnTotals,
            'grand_total'    => array_sum($columnTotals),
            'right_total'    => array_sum(array_column($rows, 'offenses')),
            'total_violators' => $totalViolators,
            'hidden_rows'    => $hiddenRows,
            'ticket_total'   => count($periodTickets),
            'peak_index'     => null,
        ];
    }

    /**
     * Dedicated insight for the reporting period's single most committed
     * offense, used by the report document's "Most Committed Violation"
     * section and its CSV twin.
     *
     * Counts only, matching the rest of the Violations Summary Report: how
     * often the offense was recorded, its share of all offenses, the citation
     * tickets that carry it, its status split, its busiest period, and where
     * and by whom it is recorded most — the report itself stays violations
     * only, so no violator ranking is included.
     *
     * @return array<string, mixed>|null Null when the period has no offenses.
     */
    private function buildTopOffenseInsight(array $records, array $natureBreakdown, array $timelineBuckets): ?array
    {
        $offenseName = trim((string) ($natureBreakdown[0]['violation_type'] ?? ''));

        if ($offenseName === '' || $records === []) {
            return null;
        }

        $totalIncidents = count($records);
        $statusCounts   = ['Paid' => 0, 'Pending' => 0, 'Cancelled' => 0];
        $tickets        = [];
        $officers       = [];
        $locations      = [];
        $firstSeen      = null;
        $lastSeen       = null;
        $offenseCount   = 0;

        foreach ($records as $record) {
            // The offense name resolves exactly the way getReportData() keys
            // its nature breakdown: the catalog name first, the stored text
            // second.
            if ((string) ($record['violation_name'] ?: $record['violation_type']) !== $offenseName) {
                continue;
            }

            $offenseCount++;

            $status = (string) ($record['status'] ?? 'Pending');
            if (isset($statusCounts[$status])) {
                $statusCounts[$status]++;
            }

            $ticketId = trim((string) ($record['ticket_id'] ?? ''));
            if ($ticketId !== '') {
                $tickets[$ticketId] = true;
            }

            $officerName = trim((string) ($record['officer_name'] ?? '')) ?: 'System / Admin';
            $officers[$officerName] = ($officers[$officerName] ?? 0) + 1;

            $recordDate = (string) ($record['violation_date'] ?? '');
            if ($recordDate !== '') {
                if ($firstSeen === null || $recordDate < $firstSeen) {
                    $firstSeen = $recordDate;
                }
                if ($lastSeen === null || $recordDate > $lastSeen) {
                    $lastSeen = $recordDate;
                }
            }

            $location = trim((string) ($record['location'] ?? ''));
            if ($location !== '') {
                $locations[$location] = ($locations[$location] ?? 0) + 1;
            }
        }

        if ($offenseCount === 0) {
            return null;
        }

        // Rank the tallies this section shows: most first, ties alphabetical.
        $officerList = [];
        foreach ($officers as $name => $count) {
            $officerList[] = ['name' => $name, 'count' => $count];
        }
        usort($officerList, static function ($a, $b) {
            return ($b['count'] <=> $a['count']) ?: strcasecmp($a['name'], $b['name']);
        });

        $locationList = [];
        foreach ($locations as $name => $count) {
            $locationList[] = ['name' => $name, 'count' => $count];
        }
        usort($locationList, static function ($a, $b) {
            return ($b['count'] <=> $a['count']) ?: strcasecmp($a['name'], $b['name']);
        });

        // The offense's own period-by-period trend, read from the same
        // timeline buckets the rest of the report uses (month for a yearly
        // report, day for weekly, weeks for monthly, buckets for custom).
        $trend = [];
        $peak  = ['label' => '', 'count' => 0];

        foreach ($timelineBuckets as $bucket) {
            $bucketCount = (int) ($bucket['by_type'][$offenseName] ?? 0);

            $trend[] = [
                'label' => (string) ($bucket['label'] ?? ''),
                'count' => $bucketCount,
                'pct'   => round(($bucketCount / $offenseCount) * 100, 2),
            ];

            if ($bucketCount > $peak['count']) {
                $peak = ['label' => (string) ($bucket['label'] ?? ''), 'count' => $bucketCount];
            }
        }

        return [
            'offense'       => $offenseName,
            'count'         => $offenseCount,
            'pct'           => $totalIncidents > 0 ? round(($offenseCount / $totalIncidents) * 100, 2) : 0.0,
            'tickets'       => count($tickets),
            'status_counts' => $statusCounts,
            'peak'          => $peak,
            'first_seen'    => $firstSeen,
            'last_seen'     => $lastSeen,
            'trend'         => $trend,
            'officers'      => array_slice($officerList, 0, 5),
            'locations'     => array_slice($locationList, 0, 5),
        ];
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
                // Same rule as the offense ranking: most frequent wins, and a
                // tie is settled alphabetically so the same period always
                // reports the same top offense.
                $topVCnt   = max($b['types_count']);
                $tiedTypes = array_keys($b['types_count'], $topVCnt, true);
                sort($tiedTypes, SORT_NATURAL | SORT_FLAG_CASE);
                $topV = $tiedTypes[0];
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
