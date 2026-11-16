<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\ReportPeriod;
use App\Models\UserModel;
use App\Models\ViolationTypeModel;
use App\Models\ViolatorModel;
use App\Models\ViolatorRecord;

/**
 * Registry of every violator on record.
 *
 * Unlike Payment → All Violators (a working list that drops fully settled
 * people), this listing keeps everyone who was ever apprehended, settled or
 * not, and names the enforcer who logged the apprehension.
 */
class ViolatorController extends BaseController
{
    protected ViolatorModel $violators;
    protected UserModel $users;
    protected ViolatorRecord $violationRecord;
    protected ViolationTypeModel $violationTypes;

    public function __construct()
    {
        $this->violators       = new ViolatorModel();
        $this->users           = new UserModel();
        $this->violationRecord = new ViolatorRecord();
        $this->violationTypes  = new ViolationTypeModel();
    }

    public function index()
    {
        $filters = [
            'search'          => trim((string) $this->request->getGet('search')),
            'status'          => trim((string) $this->request->getGet('status')),
            'include_settled' => true,
        ];

        $enforcerId = (int) ($this->request->getGet('enforcer') ?? 0);

        // Ten people per page. The filters ride along on every page link, so
        // narrowing by search or by enforcer and then turning a page never
        // loses the question that was being asked.
        $perPage = 10;
        $page    = max(1, (int) ($this->request->getGet('page') ?? 1));

        $result = $this->violators->getViolatorList(
            $filters,
            $perPage,
            $page,
            $enforcerId > 0 ? $enforcerId : null
        );

        $total    = (int) $result['total'];
        $lastPage = max(1, (int) ceil($total / $perPage));

        if ($page > $lastPage) {
            // A narrowed filter can leave the reader on a page that no longer
            // exists (page 2 of a six-person result); the last page answers the
            // question better than an empty table does.
            $page   = $lastPage;
            $result = $this->violators->getViolatorList(
                $filters,
                $perPage,
                $page,
                $enforcerId > 0 ? $enforcerId : null
            );
        }

        // The same pager template the rest of the system uses, limited to the
        // three filters this page carries.
        $pager = service('pager');
        $pager->only(['search', 'status', 'enforcer']);
        $pagerLinks = $pager->makeLinks($page, $perPage, $total, 'bootstrap_pagination');

        // One page of results needs no pager; the markup would only offer a
        // single link back to where the reader already is.
        $showPager  = $total > $perPage;

        // Every enforcer, so the filter can answer "who recorded which violator".
        $enforcers = $this->users
            ->select('id, username, firstname, lastname')
            ->where('role', 'enforcer')
            ->orderBy('lastname', 'ASC')
            ->orderBy('firstname', 'ASC')
            ->findAll();

        $counts = $this->violators->directoryCounts($enforcerId > 0 ? $enforcerId : null);

        // The KPI strip is a directory snapshot, not a view of the filtered
        // rows: searching or switching status chips never changes it. The
        // enforcer selector does narrow it, because picking an officer asks a
        // different question - how much did that officer record. The ticket and
        // money figures come from the very same yearly computation the
        // dashboard uses, so with no enforcer selected the two screens can
        // never disagree about what was assessed, collected or is outstanding.
        $year        = (int) date('Y');
        $yearSummary = $this->violationRecord
            ->getReportData(
                sprintf('%04d-01-01', $year),
                sprintf('%04d-12-31', $year),
                null,
                null,
                $enforcerId > 0 ? $enforcerId : null
            )['summary'];

        $kpiStats = [
            'violators' => (int) ($counts['all'] ?? 0),
            'attempts'  => (int) ($yearSummary['total_tickets'] ?? 0),
            'fines'     => (float) ($yearSummary['total_assessed'] ?? 0),
            'paid'      => (float) ($yearSummary['total_paid'] ?? 0),
            'balance'   => (float) ($yearSummary['total_pending'] ?? 0),
        ];

        // Citation issuance straight from this directory: the same active
        // offense catalog and the same one-time token the officer form uses,
        // so the popup can post to officer/store without leaving the page.
        $session          = session();
        $submissionToken  = bin2hex(random_bytes(16));
        $tokens           = $session->get('violation_submission_tokens');
        $tokens           = is_array($tokens) ? $tokens : [];
        $tokens[]         = $submissionToken;
        $tokens           = array_slice(array_values(array_unique($tokens)), -20);
        $session->set('violation_submission_tokens', $tokens);

        $data = [
            'title'           => 'Violators - Admin',
            'violationTypes'  => $this->violationTypes
                ->where('status', 'active')
                ->orderBy('violation_name', 'ASC')
                ->findAll(),
            'submissionToken' => $submissionToken,
            'violators'  => $result['rows'],
            'total'      => $result['total'],
            'stats'      => $result['stats'],
            'kpi_stats'  => $kpiStats,
            'counts'     => $counts,
            'enforcers'  => $enforcers,
            'pagerLinks' => $pagerLinks,
            'showPager'  => $showPager,
            'perPage'    => $perPage,
            'search'     => $filters['search'],
            'status'     => $filters['status'],
            'enforcerId' => $enforcerId,
        ];

        return view('admin/violators/index', $data);
    }

    /**
     * Report chooser: every violator, with the one button that opens their
     * apprehension and sanction sheet. Lives under Reports, so the registry
     * itself stays a registry.
     */
    public function directory()
    {
        $search = trim((string) $this->request->getGet('search'));
        $perPage = 15;
        $page    = max(1, (int) ($this->request->getGet('page') ?? 1));

        $result = $this->violators->getViolatorList(
            ['search' => $search, 'include_settled' => true],
            $perPage,
            $page
        );

        $total    = (int) $result['total'];
        $lastPage = max(1, (int) ceil($total / $perPage));

        if ($page > $lastPage) {
            $page   = $lastPage;
            $result = $this->violators->getViolatorList(
                ['search' => $search, 'include_settled' => true],
                $perPage,
                $page
            );
        }

        $pager = service('pager');
        $pager->only(['search']);
        $pagerLinks = $pager->makeLinks($page, $perPage, $total, 'bootstrap_pagination');

        // The report-type chooser offers the same year window the Reports
        // module reads (this year and the five before it).
        $currentYear = (int) date('Y');
        $years       = range($currentYear, $currentYear - 5);

        return view('admin/violators/directory', [
            'title'      => 'Violator Reports - Admin',
            'violators'  => $result['rows'],
            'total'      => $total,
            'search'     => $search,
            'years'      => $years,
            'pagerLinks' => $pagerLinks,
            'showPager'  => $total > $perPage,
        ]);
    }

    /**
     * Printable apprehension and sanction report for one violator.
     *
     * Two shapes of the same history: the monthly sheet groups a whole year by
     * month, the daily sheet lists every apprehension of one month with the
     * exact time and the place it was recorded. Both are built from the rows
     * the record screen reads (getProfile), so the figures here can never
     * disagree with the profile's own totals.
     */
    public function report($id = null)
    {
        $mode = strtolower(trim((string) $this->request->getGet('mode')));
        $mode = $mode === 'daily' ? 'daily' : 'monthly';

        $year  = $this->request->getGet('year');
        $month = $this->request->getGet('month');

        $data = $this->buildReport(
            (int) $id,
            $mode,
            $year === null || $year === '' ? null : (int) $year,
            $month === null || $month === '' ? null : (int) $month
        );

        if ($data === null) {
            return redirect()->to(base_url('reports/violators'))->with('error', 'Violator record not found.');
        }

        return view('admin/violators/report', $data);
    }

    /**
     * One document for a whole calendar year: every violator's apprehensions
     * pooled together and grouped month by month, so the panel reads the year
     * as a single report instead of a pile of per-person sheets.
     */
    public function yearReport()
    {
        $search = trim((string) $this->request->getGet('search'));

        $yearIn = $this->request->getGet('year');
        $year   = (int) ($yearIn === null || $yearIn === '' ? date('Y') : $yearIn);

        $monthNames = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];

        $list = $this->violators->getViolatorList(
            ['search' => $search, 'include_settled' => true],
            500,
            1
        );

        $rows        = [];   // every offense entry of the year, flat
        $perViolator = [];   // one line per person for the annual summary
        $calendar    = [];   // month => day => citations
        $years       = [(int) date('Y') => true];

        // Status counts for the revenue section: entries and amounts per status.
        $statusEntries   = ['Paid' => 0, 'Pending' => 0, 'Cancelled' => 0];
        $statusAmounts   = ['Paid' => 0.0, 'Pending' => 0.0, 'Cancelled' => 0.0];

        foreach ($list['rows'] as $row) {
            if (! empty($row['last_violation_at'])) {
                $years[(int) date('Y', strtotime((string) $row['last_violation_at']))] = true;
            }

            $sheet = $this->buildReport((int) $row['id'], 'monthly', $year, null);

            if ($sheet === null) {
                continue;
            }

            $entries  = 0;
            $assessed = 0.0;
            $paid     = 0.0;
            $pending  = 0.0;
            $tickets  = [];

            foreach ($sheet['infractions'] as $infraction) {
                if ($infraction['stamp'] <= 0) {
                    continue;
                }

                $years[(int) date('Y', (int) $infraction['stamp'])] = true;

                if ((int) date('Y', (int) $infraction['stamp']) !== $year) {
                    continue;
                }

                $entries++;

                if ($infraction['status'] !== 'Cancelled') {
                    $assessed += $infraction['amount'];
                }

                if ($infraction['status'] === 'Paid') {
                    $paid += $infraction['amount'];
                } elseif ($infraction['status'] === 'Pending') {
                    $pending += $infraction['amount'];
                }

                $status = (string) $infraction['status'];
                if (! isset($statusEntries[$status])) {
                    $statusEntries[$status] = 0;
                    $statusAmounts[$status] = 0.0;
                }
                $statusEntries[$status]++;
                $statusAmounts[$status] += $infraction['amount'];

                $tickets[$infraction['ticket_id']] = true;

                $monthNo = (int) date('n', (int) $infraction['stamp']);
                $dayNo   = (int) date('j', (int) $infraction['stamp']);

                $calendar[$monthNo][$dayNo][$infraction['ticket_id']] = true;

                $rows[] = $infraction + [
                    'violator_id'   => (int) $row['id'],
                    'violator_name' => $sheet['fullName'],
                    'plate'         => $sheet['plate'],
                ];
            }

            if ($tickets !== []) {
                $perViolator[] = [
                    'name'          => $sheet['fullName'],
                    'plate'         => $sheet['plate'],
                    'apprehensions' => count($tickets),
                    'entries'       => $entries,
                    'assessed'      => $assessed,
                    'paid'          => $paid,
                    'pending'       => $pending,
                ];
            }
        }

        // ── The tally grid, pooled across every violator ─────────────────
        $calendarMarks  = [];
        $calendarTotals = [];

        foreach ($calendar as $monthNo => $days) {
            ksort($days);

            foreach ($days as $dayNo => $dayTickets) {
                $calendarMarks[$monthNo][$dayNo] = count($dayTickets);
                $calendarTotals[$monthNo]        = ($calendarTotals[$monthNo] ?? 0) + count($dayTickets);
            }
        }

        // ── Month by month ───────────────────────────────────────────────
        usort($rows, static fn (array $a, array $b): int => $a['stamp'] <=> $b['stamp']);

        $byMonth = [];

        foreach ($rows as $offense) {
            $byMonth[(int) date('n', (int) $offense['stamp'])][] = $offense;
        }

        $monthSummary = [];
        $monthDetail  = [];

        foreach (range(1, 12) as $monthNo) {
            $monthRows = $byMonth[$monthNo] ?? [];

            $tickets    = [];
            $violators  = [];
            $assessed   = 0.0;
            $paid       = 0.0;
            $pending    = 0.0;
            $ticketRows = [];
            $grouped    = [];

            foreach ($monthRows as $offense) {
                $tickets[$offense['ticket_id']]   = true;
                $violators[$offense['violator_id']] = true;

                if ($offense['status'] !== 'Cancelled') {
                    $assessed += $offense['amount'];
                }

                if ($offense['status'] === 'Paid') {
                    $paid += $offense['amount'];
                } elseif ($offense['status'] === 'Pending') {
                    $pending += $offense['amount'];
                }

                $grouped[$offense['ticket_id']][] = $offense;
            }

            uasort($grouped, static fn (array $a, array $b): int => ($a[0]['stamp'] ?? 0) <=> ($b[0]['stamp'] ?? 0));

            foreach ($grouped as $ticketId => $group) {
                $first = $group[0];

                $types = array_values(array_unique(array_map(static fn (array $r): string => $r['type'], $group)));
                $statuses = array_values(array_unique(array_map(static fn (array $r): string => $r['status'], $group)));

                $status = in_array('Pending', $statuses, true)
                    ? 'Pending'
                    : (in_array('Paid', $statuses, true) ? 'Paid' : 'Cancelled');

                $amount = 0.0;

                foreach ($group as $entry) {
                    if ($entry['status'] !== 'Cancelled') {
                        $amount += $entry['amount'];
                    }
                }

                $locations = array_values(array_unique(array_filter(array_map(static fn (array $r): string => $r['location'], $group))));

                $ticketRows[] = [
                    'date'     => $first['date'],
                    'time'     => $first['time'],
                    'ticket_id'=> (string) $ticketId,
                    'violator' => $first['violator_name'],
                    'plate'    => $first['plate'],
                    'offenses' => implode('; ', $types),
                    'location' => $locations === [] ? 'Not recorded' : implode('; ', $locations),
                    'officer'  => $first['officer'] !== '' ? $first['officer'] : 'Not recorded',
                    'amount'   => $amount,
                    'status'   => $status,
                ];
            }

            $monthSummary[] = [
                'no'            => $monthNo,
                'label'         => $monthNames[$monthNo],
                'apprehensions' => count($tickets),
                'violators'     => count($violators),
                'entries'       => count($monthRows),
                'assessed'      => $assessed,
                'paid'          => $paid,
                'pending'       => $pending,
            ];

            if ($ticketRows !== []) {
                $monthDetail[] = [
                    'no'            => $monthNo,
                    'label'         => $monthNames[$monthNo] . ' ' . $year,
                    'apprehensions' => count($tickets),
                    'violators'     => count($violators),
                    'assessed'      => $assessed,
                    'pending'       => $pending,
                    'rows'          => $ticketRows,
                ];
            }
        }

        usort($perViolator, static fn (array $a, array $b): int => $b['apprehensions'] <=> $a['apprehensions'] ?: strcasecmp($a['name'], $b['name']));

        $totals = [
            'apprehensions' => array_sum(array_column($perViolator, 'apprehensions')),
            'entries'       => array_sum(array_column($perViolator, 'entries')),
            'assessed'      => array_sum(array_column($perViolator, 'assessed')),
            'paid'          => array_sum(array_column($perViolator, 'paid')),
            'pending'       => array_sum(array_column($perViolator, 'pending')),
            'cancelled'     => (float) $statusAmounts['Cancelled'],
            'status_entries'=> $statusEntries,
            'status_amounts'=> $statusAmounts,
            'violators'     => count($perViolator),
        ];

        $years = array_keys($years);
        rsort($years);

        return view('admin/violators/report_year', [
            'title'          => 'Full-Year Violator Report ' . $year,
            'year'           => $year,
            'years'          => $years,
            'search'         => $search,
            'month_names'    => $monthNames,
            'calendar'       => $calendarMarks,
            'calendar_totals'=> $calendarTotals,
            'month_summary'  => $monthSummary,
            'month_detail'   => $monthDetail,
            'per_violator'   => $perViolator,
            'totals'         => $totals,
            'back_url'       => base_url('reports/violators'),
        ]);
    }

    /**
     * One document for one period: weekly, monthly or a custom range, every
     * violator's apprehensions pooled together and summarised day by day.
     *
     * The full-year option keeps its own page (yearReport), because a calendar
     * year is read month by month rather than day by day; every yearly call
     * here hands over to it so there is only one annual document.
     */
    public function periodReport()
    {
        $periodType = strtolower(trim((string) $this->request->getGet('period_type')));
        $periodType = ReportPeriod::isValid($periodType) ? $periodType : 'yearly';

        $search = trim((string) $this->request->getGet('search'));

        // The yearly document has a month-by-month page of its own.
        if ($periodType === 'yearly') {
            $yearIn = $this->request->getGet('year');
            $query  = http_build_query(array_filter([
                'year'   => ($yearIn === null || $yearIn === '') ? null : (string) (int) $yearIn,
                'search' => $search !== '' ? $search : null,
            ]));

            return redirect()->to(base_url('reports/violators/year') . ($query !== '' ? '?' . $query : ''));
        }

        ['startDate' => $startDate, 'endDate' => $endDate, 'year' => $year, 'month' => $month]
            = ReportPeriod::resolve($this->request, $periodType);

        $startStamp = strtotime($startDate . ' 00:00:00');
        $endStamp   = strtotime($endDate . ' 23:59:59');

        $list = $this->violators->getViolatorList(
            ['search' => $search, 'include_settled' => true],
            500,
            1
        );

        $rows        = [];   // every offense entry of the period, flat
        $perViolator = [];   // one line per person for the period summary
        $dayBuckets  = [];   // day => tickets, violators and money for that day
        $years       = [(int) date('Y') => true];

        foreach ($list['rows'] as $row) {
            if (! empty($row['last_violation_at'])) {
                $years[(int) date('Y', strtotime((string) $row['last_violation_at']))] = true;
            }

            $sheet = $this->buildReport((int) $row['id'], 'monthly', (int) date('Y', $startStamp), null);

            if ($sheet === null) {
                continue;
            }

            $entries  = 0;
            $assessed = 0.0;
            $paid     = 0.0;
            $pending  = 0.0;
            $tickets  = [];

            foreach ($sheet['infractions'] as $infraction) {
                $stamp = (int) $infraction['stamp'];

                if ($stamp > 0) {
                    $years[(int) date('Y', $stamp)] = true;
                }

                // Only the period's own apprehensions belong to this document.
                if ($stamp <= 0 || $stamp < $startStamp || $stamp > $endStamp) {
                    continue;
                }

                $entries++;

                if ($infraction['status'] !== 'Cancelled') {
                    $assessed += $infraction['amount'];
                }

                if ($infraction['status'] === 'Paid') {
                    $paid += $infraction['amount'];
                } elseif ($infraction['status'] === 'Pending') {
                    $pending += $infraction['amount'];
                }

                $tickets[$infraction['ticket_id']] = true;

                $dayKey = $infraction['day'];

                $dayBuckets[$dayKey]['tickets'][$infraction['ticket_id']] = true;
                $dayBuckets[$dayKey]['violators'][(int) $row['id']]       = true;
                $dayBuckets[$dayKey]['entries']   = ($dayBuckets[$dayKey]['entries'] ?? 0) + 1;

                if ($infraction['status'] !== 'Cancelled') {
                    $dayBuckets[$dayKey]['assessed'] = ($dayBuckets[$dayKey]['assessed'] ?? 0) + $infraction['amount'];
                }

                if ($infraction['status'] === 'Pending') {
                    $dayBuckets[$dayKey]['pending'] = ($dayBuckets[$dayKey]['pending'] ?? 0) + $infraction['amount'];
                }

                $rows[] = $infraction + [
                    'violator_id'   => (int) $row['id'],
                    'violator_name' => $sheet['fullName'],
                    'plate'         => $sheet['plate'],
                ];
            }

            if ($tickets !== []) {
                $perViolator[] = [
                    'name'          => $sheet['fullName'],
                    'plate'         => $sheet['plate'],
                    'apprehensions' => count($tickets),
                    'entries'       => $entries,
                    'assessed'      => $assessed,
                    'paid'          => $paid,
                    'pending'       => $pending,
                ];
            }
        }

        // ── Day by day: every date of the period, with its own figures ────
        $daySummary = [];

        $cursor    = strtotime($startDate . ' 00:00:00');
        $cursorEnd = strtotime($endDate . ' 00:00:00');

        while ($cursor <= $cursorEnd) {
            $dayKey = date('Y-m-d', $cursor);
            $day    = $dayBuckets[$dayKey] ?? [];

            $daySummary[] = [
                'label'         => date('M d, Y', $cursor),
                'weekday'       => date('D', $cursor),
                'apprehensions' => count($day['tickets'] ?? []),
                'violators'     => count($day['violators'] ?? []),
                'entries'       => (int) ($day['entries'] ?? 0),
                'assessed'      => (float) ($day['assessed'] ?? 0),
                'pending'       => (float) ($day['pending'] ?? 0),
            ];

            $cursor = strtotime('+1 day', $cursor);
        }

        // ── Apprehensions grouped by the day they happened ────────────────
        usort($rows, static fn (array $a, array $b): int => $a['stamp'] <=> $b['stamp']);

        $byDay = [];

        foreach ($rows as $offense) {
            $byDay[$offense['day']][] = $offense;
        }

        $dayDetail = [];

        foreach ($byDay as $dayKey => $dayRows) {
            $tickets    = [];
            $violators  = [];
            $assessed   = 0.0;
            $pending    = 0.0;
            $ticketRows = [];
            $grouped    = [];

            foreach ($dayRows as $offense) {
                $tickets[$offense['ticket_id']]    = true;
                $violators[$offense['violator_id']] = true;

                if ($offense['status'] !== 'Cancelled') {
                    $assessed += $offense['amount'];
                }

                if ($offense['status'] === 'Pending') {
                    $pending += $offense['amount'];
                }

                $grouped[$offense['ticket_id']][] = $offense;
            }

            uasort($grouped, static fn (array $a, array $b): int => ($a[0]['stamp'] ?? 0) <=> ($b[0]['stamp'] ?? 0));

            foreach ($grouped as $ticketId => $group) {
                $first = $group[0];

                $types    = array_values(array_unique(array_map(static fn (array $r): string => $r['type'], $group)));
                $statuses = array_values(array_unique(array_map(static fn (array $r): string => $r['status'], $group)));

                $status = in_array('Pending', $statuses, true)
                    ? 'Pending'
                    : (in_array('Paid', $statuses, true) ? 'Paid' : 'Cancelled');

                $amount = 0.0;

                foreach ($group as $entry) {
                    if ($entry['status'] !== 'Cancelled') {
                        $amount += $entry['amount'];
                    }
                }

                $locations = array_values(array_unique(array_filter(array_map(static fn (array $r): string => $r['location'], $group))));

                $ticketRows[] = [
                    'date'      => $first['date'],
                    'time'      => $first['time'],
                    'ticket_id' => (string) $ticketId,
                    'violator'  => $first['violator_name'],
                    'plate'     => $first['plate'],
                    'offenses'  => implode('; ', $types),
                    'location'  => $locations === [] ? 'Not recorded' : implode('; ', $locations),
                    'officer'   => $first['officer'] !== '' ? $first['officer'] : 'Not recorded',
                    'amount'    => $amount,
                    'status'    => $status,
                ];
            }

            $dayDetail[] = [
                'label'         => date('F d, Y', strtotime($dayKey)) . ' (' . date('D', strtotime($dayKey)) . ')',
                'apprehensions' => count($tickets),
                'violators'     => count($violators),
                'entries'       => count($dayRows),
                'assessed'      => $assessed,
                'pending'       => $pending,
                'rows'          => $ticketRows,
            ];
        }

        usort($perViolator, static fn (array $a, array $b): int => $b['apprehensions'] <=> $a['apprehensions'] ?: strcasecmp($a['name'], $b['name']));

        $totals = [
            'apprehensions' => array_sum(array_column($perViolator, 'apprehensions')),
            'entries'       => array_sum(array_column($perViolator, 'entries')),
            'assessed'      => array_sum(array_column($perViolator, 'assessed')),
            'paid'          => array_sum(array_column($perViolator, 'paid')),
            'pending'       => array_sum(array_column($perViolator, 'pending')),
            'violators'     => count($perViolator),
            'active_days'   => count($dayDetail),
        ];

        $years = array_keys($years);
        rsort($years);

        // The requested year must always be selectable again from the toolbar.
        if (! in_array((int) $year, $years, true)) {
            $years[] = (int) $year;
            rsort($years);
        }

        $periodName = match ($periodType) {
            'daily'   => 'Daily',
            'weekly'  => 'Weekly',
            'monthly' => 'Monthly',
            default   => 'Custom Range',
        };

        $rangeLabel = match ($periodType) {
            'daily'   => date('M d, Y', $startStamp),
            'monthly' => date('F Y', $startStamp),
            default   => date('M d, Y', $startStamp) . ' – ' . date('M d, Y', $endStamp),
        };

        $monthNames = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];

        return view('admin/violators/report_period', [
            'title'        => $periodName . ' Violator Report',
            'period_type'  => $periodType,
            'period_name'  => $periodName,
            'range_label'  => $rangeLabel,
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'year'         => (string) (int) $year,
            'month'        => (string) (int) $month,
            'search'       => $search,
            'years'        => $years,
            'month_names'  => $monthNames,
            'day_summary'  => $daySummary,
            'day_detail'   => $dayDetail,
            'per_violator' => $perViolator,
            'totals'       => $totals,
            'back_url'     => base_url('reports/violators'),
        ]);
    }

    /**
     * Builds everything one printed sheet needs. Returns null when the violator
     * is not on record.
     */
    private function buildReport(int $violatorId, string $mode, ?int $yearIn, ?int $monthIn): ?array
    {
        $profile = $this->violators->getProfile($violatorId);

        if (! $profile) {
            return null;
        }

        $tickets = $profile['tickets'];
        $summary = $profile['summary'];

        // ── One row per infraction, newest first ──────────────────────────
        // A ticket carries several offenses; the sheet lists them separately
        // because each one has its own sanction status.
        $infractions = [];

        foreach ($tickets as $ticket) {
            foreach (($ticket['violations'] ?? []) as $entry) {
                $stamp = strtotime((string) ($entry['violation_date'] ?? $ticket['violation_date'] ?? ''));

                $infractions[] = [
                    'ticket_id'      => (string) $ticket['ticket_id'],
                    'ticket_status'  => (string) ($ticket['status'] ?? 'Pending'),
                    'type'           => (string) ($entry['type'] ?? 'Traffic Infraction'),
                    'status'         => (string) ($entry['status'] ?? 'Pending'),
                    'amount'         => (float) ($entry['amount'] ?? 0),
                    'stamp'          => $stamp ?: 0,
                    'date'           => $stamp ? date('M d, Y', $stamp) : '—',
                    'time'           => $stamp ? date('g:i A', $stamp) : '—',
                    'day'            => $stamp ? date('Y-m-d', $stamp) : '',
                    'month'          => $stamp ? date('Y-m', $stamp) : '',
                    'location'       => trim((string) ($ticket['location'] ?? '')),
                    'officer'        => trim((string) ($ticket['officer_name'] ?? '')),
                    'receipt_number' => trim((string) ($ticket['receipt_number'] ?? '')),
                    'paid_date'      => trim((string) ($ticket['paid_date'] ?? '')),
                ];
            }
        }

        usort($infractions, static fn (array $a, array $b): int => $b['stamp'] <=> $a['stamp']);

        // ── Which years and months the record actually covers ────────────
        $years = [];
        foreach ($infractions as $row) {
            if ($row['stamp'] > 0) {
                $years[(int) date('Y', $row['stamp'])] = true;
            }
        }

        $years = array_keys($years);
        rsort($years);

        $latestYear = $years[0] ?? (int) date('Y');

        $year = (int) ($yearIn ?: $latestYear);

        // The daily sheet needs a month as well; it opens on the month of the
        // newest apprehension so it never lands on an empty page by default.
        $latestMonth = 0;
        foreach ($infractions as $row) {
            if ($row['stamp'] > 0) {
                $latestMonth = (int) date('n', $row['stamp']);
                break;
            }
        }

        $month = (int) ($monthIn ?: ($latestMonth ?: (int) date('n')));
        $month = min(12, max(1, $month));

        $monthNames = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];

        // ── Section I: the period record ─────────────────────────────────
        $periodRows = [];

        if ($mode === 'monthly') {
            // One row per month of the chosen year that has apprehensions.
            $buckets = [];

            foreach ($infractions as $row) {
                if ((int) date('Y', (int) $row['stamp']) !== $year) {
                    continue;
                }

                $key = (int) date('n', $row['stamp']);
                $buckets[$key][] = $row;
            }

            ksort($buckets);

            foreach ($buckets as $monthNo => $rows) {
                $periodRows[] = $this->summarizePeriod($rows, $monthNames[$monthNo] . ' ' . $year, $monthNames[$monthNo]);
            }
        } else {
            // One row per apprehension (ticket) inside the chosen month, in the
            // order the days happened. Several offenses on the same ticket share
            // one row: it is one apprehension at one time and one place.
            $dayTickets = [];

            foreach ($infractions as $row) {
                if ($row['month'] !== sprintf('%04d-%02d', $year, $month)) {
                    continue;
                }

                $dayTickets[$row['ticket_id']][] = $row;
            }

            uasort($dayTickets, static function (array $a, array $b): int {
                return ($a[0]['stamp'] ?? 0) <=> ($b[0]['stamp'] ?? 0);
            });

            foreach ($dayTickets as $ticketId => $rows) {
                $first = $rows[0];

                $types = array_values(array_unique(array_map(static fn (array $r): string => $r['type'], $rows)));
                $locations = array_values(array_unique(array_filter(array_map(static fn (array $r): string => $r['location'], $rows))));
                $times = array_values(array_unique(array_map(static fn (array $r): string => $r['time'], $rows)));
                $statuses = array_values(array_unique(array_map(static fn (array $r): string => $r['status'], $rows)));

                $status = in_array('Pending', $statuses, true)
                    ? 'Pending'
                    : (in_array('Paid', $statuses, true) ? 'Paid' : 'Cancelled');

                $assessed = 0.0;
                $pendingAmount = 0.0;

                foreach ($rows as $r) {
                    if ($r['status'] === 'Cancelled') {
                        continue;
                    }

                    $assessed += $r['amount'];

                    if ($r['status'] === 'Pending') {
                        $pendingAmount += $r['amount'];
                    }
                }

                $periodRows[] = [
                    'ticket_id'   => (string) $ticketId,
                    'date'        => $first['date'],
                    'time'        => implode(', ', $times),
                    'offenses'    => $types,
                    'location'    => $locations === [] ? 'Not recorded' : implode('; ', $locations),
                    'officer'     => $first['officer'] !== '' ? $first['officer'] : 'Not recorded',
                    'entries'     => count($rows),
                    'amount'      => $assessed,
                    'pending'     => $pendingAmount,
                    'status'      => $status,
                    'has_location'=> $locations !== [],
                ];
            }
        }

        // ── The calendar grid: which days of the year carry an apprehension ──
        // Counted per citation, not per offense, so the marks match the
        // apprehension total and the sheet reads like the office tally board.
        $calendar = [];
        $calendarTotals = [];

        foreach ($infractions as $row) {
            if ($row['stamp'] <= 0 || (int) date('Y', (int) $row['stamp']) !== $year) {
                continue;
            }

            $monthNo = (int) date('n', (int) $row['stamp']);
            $dayNo   = (int) date('j', (int) $row['stamp']);

            $calendar[$monthNo][$dayNo][$row['ticket_id']] = true;
        }

        $calendarMarks = [];
        foreach ($calendar as $monthNo => $days) {
            ksort($days);
            foreach ($days as $dayNo => $tickets) {
                $calendarMarks[$monthNo][$dayNo] = count($tickets);
                $calendarTotals[$monthNo] = ($calendarTotals[$monthNo] ?? 0) + count($tickets);
            }
        }

        // ── Sanction standing ────────────────────────────────────────────
        $statusLedger = [
            'Paid'      => ['entries' => 0, 'amount' => 0.0],
            'Pending'   => ['entries' => 0, 'amount' => 0.0],
            'Cancelled' => ['entries' => 0, 'amount' => 0.0],
        ];

        foreach ($infractions as $row) {
            $status = isset($statusLedger[$row['status']]) ? $row['status'] : 'Pending';
            $statusLedger[$status]['entries']++;
            $statusLedger[$status]['amount'] += $row['amount'];
        }

        // Demerit points live on the citation rows; a voided infraction carries
        // no sanction, so it adds no points.
        $pointsRow = $this->violationRecord
            ->selectSum('points', 'points')
            ->where('violator_id', $violatorId)
            ->where('status !=', 'Cancelled')
            ->first();

        $points = (int) ($pointsRow['points'] ?? 0);

        // Places the person was apprehended, counted across the whole record:
        // the summary the office asks for when it reads "where was this person
        // caught", independent of the month being printed.
        $placeCounts = [];
        foreach ($infractions as $row) {
            if ($row['status'] === 'Cancelled' || $row['location'] === '') {
                continue;
            }
            $placeCounts[$row['location']] = ($placeCounts[$row['location']] ?? 0) + 1;
        }

        arsort($placeCounts);

        $data = [
            'title'        => 'Violator Report - ' . ($profile['violator']['driver_name'] ?? 'Record'),
            'violator'     => $profile['violator'],
            'summary'      => $summary,
            'vehicles'     => $profile['vehicles'],
            'infractions'  => $infractions,
            'period_rows'  => $periodRows,
            'mode'         => $mode,
            'year'         => $year,
            'month'        => $month,
            'years'        => $years === [] ? [(int) date('Y')] : $years,
            'month_names'  => $monthNames,
            'calendar'     => $calendarMarks,
            'calendar_totals' => $calendarTotals,
            'status_ledger'=> $statusLedger,
            'points'       => $points,
            'place_counts' => $placeCounts,
            'back_url'     => base_url('reports/violators'),
            'is_stack'     => false,
        ];

        return $this->prepareSheetData($data);
    }

    /**
     * Text and totals the sheet prints, worked out once here so the single
     * report and the bound stack share exactly the same markup.
     */
    private function prepareSheetData(array $data): array
    {
        $violator     = $data['violator'];
        $summary      = $data['summary'];
        $statusLedger = $data['status_ledger'];
        $mode         = $data['mode'];
        $month        = $data['month'];
        $year         = $data['year'];

        // ── Identity ─────────────────────────────────────────────────────
        $fullName = trim((string) ($violator['driver_name'] ?? ''));

        if ($fullName === '') {
            $fullName = trim(implode(' ', array_filter([
                $violator['first_name'] ?? '',
                $violator['middle_name'] ?? '',
                $violator['last_name'] ?? '',
            ], static fn ($part) => trim((string) $part) !== '')));
        }

        if ($fullName === '') {
            $fullName = 'Unknown Violator';
        }

        $overall = (string) ($summary['overall_status'] ?? 'Pending');

        // ── Totals across the printed period rows, so the footer reconciles
        // with the whole record instead of being taken on trust.
        $periodApprehensions = 0;
        $periodEntries       = 0;
        $periodAssessed      = 0.0;
        $periodPending       = 0.0;

        foreach ($data['period_rows'] as $row) {
            $periodApprehensions += $mode === 'daily' ? 1 : (int) $row['apprehensions'];
            $periodEntries       += (int) $row['entries'];
            $periodAssessed      += $mode === 'daily' ? (float) $row['amount'] : (float) $row['assessed'];
            $periodPending       += (float) ($row['pending'] ?? 0);
        }

        $data['fullName']    = $fullName;
        $data['license']     = trim((string) ($violator['license_number'] ?? ''));
        $data['plate']       = trim((string) ($violator['license_plate'] ?? ''));
        $data['vehicle']     = trim((string) ($violator['vehicle_type'] ?? ''));
        $data['mtop']        = trim((string) ($violator['mtop_number'] ?? ''));
        $data['address']     = trim((string) ($violator['address'] ?? ''));

        $data['attempts']     = (int) ($summary['attempt_count'] ?? 0);
        $data['entriesCount'] = (int) ($summary['infraction_count'] ?? 0);
        $data['assessed']     = (float) ($summary['total_fines'] ?? 0);
        $data['paidTotal']    = (float) ($summary['total_paid'] ?? 0);
        $data['balance']      = (float) ($summary['balance'] ?? 0);
        $data['overall']      = $overall;

        // Standing on record: what an office copy has to answer at a glance.
        $data['overallTone'] = $overall === 'Paid' ? 'paid' : ($overall === 'Cancelled' ? 'cancelled' : 'pending');

        $data['paidEntries']      = (int) ($statusLedger['Paid']['entries'] ?? 0);
        $data['pendingEntries']   = (int) ($statusLedger['Pending']['entries'] ?? 0);
        $data['cancelledEntries'] = (int) ($statusLedger['Cancelled']['entries'] ?? 0);

        // Period heading text for the chosen mode.
        if ($mode === 'daily') {
            $data['periodLabel'] = $data['month_names'][$month] . ' ' . $year;
            $data['modeLabel']   = 'Daily Apprehension Record';
        } else {
            $data['periodLabel'] = 'Calendar Year ' . $year;
            $data['modeLabel']   = 'Monthly Apprehension Record';
        }

        $data['periodApprehensions'] = $periodApprehensions;
        $data['periodEntries']       = $periodEntries;
        $data['periodAssessed']      = $periodAssessed;
        $data['periodPending']       = $periodPending;

        $data['queryBase'] = static function (array $overrides) use ($mode, $year, $month): string {
            return http_build_query(array_merge([
                'mode'  => $mode,
                'year'  => $year,
                'month' => $month,
            ], $overrides));
        };

        return $data;
    }

    /**
     * Collapses one month of infractions into the figures the monthly sheet
     * prints: distinct apprehensions, offense entries, the places and the
     * times they happened, and the money standing.
     */
    private function summarizePeriod(array $rows, string $label, string $shortLabel): array
    {
        $tickets   = [];
        $locations = [];
        $officers  = [];
        $times     = [];
        $paid      = 0.0;
        $pending   = 0.0;

        foreach ($rows as $row) {
            $tickets[$row['ticket_id']] = true;

            if ($row['location'] !== '') {
                $locations[$row['location']] = ($locations[$row['location']] ?? 0) + 1;
            }

            if ($row['officer'] !== '') {
                $officers[$row['officer']] = true;
            }

            if ($row['time'] !== '—') {
                $times[] = $row['time'];
            }

            if ($row['status'] === 'Paid') {
                $paid += $row['amount'];
            } elseif ($row['status'] === 'Pending') {
                $pending += $row['amount'];
            }
        }

        arsort($locations);

        $placeParts = [];
        foreach ($locations as $place => $count) {
            $placeParts[] = $count > 1 ? $place . ' (' . $count . ')' : $place;
        }

        sort($times);

        return [
            'label'       => $label,
            'short_label' => $shortLabel,
            'apprehensions' => count($tickets),
            'entries'     => count($rows),
            'locations'   => $placeParts === [] ? 'Not recorded' : implode('; ', $placeParts),
            'times'       => $times === [] ? '—' : implode(', ', array_values(array_unique($times))),
            'officers'    => $officers === [] ? 'Not recorded' : implode(', ', array_keys($officers)),
            'assessed'    => $paid + $pending,
            'paid'        => $paid,
            'pending'     => $pending,
        ];
    }
}
