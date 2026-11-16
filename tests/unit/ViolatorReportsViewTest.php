<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Renders the two report documents under Reports → Violator Reports with the
 * same data contract ViolatorController passes: the directory with its
 * Report Type chooser, and the weekly / monthly / custom period report.
 *
 * This catches undefined view variables (and similar template typos) at test
 * time instead of on the live report page, where development mode turns them
 * into an error screen.
 *
 * @internal
 */
final class ViolatorReportsViewTest extends CIUnitTestCase
{
    /**
     * @return array<string, mixed>
     */
    private function directoryData(): array
    {
        return [
            'title'      => 'Violator Reports - Admin',
            'violators'  => [],
            'total'      => 0,
            'search'     => '',
            'years'      => [2026, 2025, 2024, 2023, 2022, 2021],
            'pagerLinks' => '',
            'showPager'  => false,
        ];
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function periodReportData(array $overrides = []): array
    {
        return array_merge([
            'title'        => 'Weekly Violator Report',
            'period_type'  => 'weekly',
            'period_name'  => 'Weekly',
            'range_label'  => 'Nov 09, 2026 – Nov 15, 2026',
            'start_date'   => '2026-11-09',
            'end_date'     => '2026-11-15',
            'year'         => '2026',
            'month'        => '11',
            'search'       => '',
            'years'        => [2026, 2025],
            'month_names'  => [
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
            ],
            'day_summary'  => [
                [
                    'label' => 'Nov 10, 2026', 'weekday' => 'Tue',
                    'apprehensions' => 1, 'violators' => 1, 'entries' => 1,
                    'assessed' => 1050.0, 'pending' => 1050.0,
                ],
                [
                    'label' => 'Nov 11, 2026', 'weekday' => 'Wed',
                    'apprehensions' => 0, 'violators' => 0, 'entries' => 0,
                    'assessed' => 0.0, 'pending' => 0.0,
                ],
            ],
            'day_detail'   => [
                [
                    'label' => 'November 10, 2026 (Tue)',
                    'apprehensions' => 1, 'violators' => 1, 'entries' => 1,
                    'assessed' => 1050.0, 'pending' => 1050.0,
                    'rows' => [
                        [
                            'date' => 'Nov 10, 2026', 'time' => '1:20 AM',
                            'ticket_id' => 'T-9001', 'violator' => 'Jake T. Cuenca',
                            'plate' => '123456', 'offenses' => 'Obstruction',
                            'location' => 'Kabankalan Proper', 'officer' => 'Rio',
                            'amount' => 1050.0, 'status' => 'Pending',
                        ],
                    ],
                ],
            ],
            'per_violator' => [
                [
                    'name' => 'Jake T. Cuenca', 'plate' => '123456',
                    'apprehensions' => 1, 'entries' => 1,
                    'assessed' => 1050.0, 'paid' => 0.0, 'pending' => 1050.0,
                ],
            ],
            'totals'       => [
                'apprehensions' => 1,
                'entries'       => 1,
                'assessed'      => 1050.0,
                'paid'          => 0.0,
                'pending'       => 1050.0,
                'violators'     => 1,
                'active_days'   => 1,
            ],
            'back_url'     => 'http://localhost/reports/violators',
        ], $overrides);
    }

    public function testDirectoryViewCarriesTheReportTypeChooser(): void
    {
        $html = service('renderer')->setData($this->directoryData())->render('admin/violators/directory');

        // The dropdown replaces the old Full-year report link: the same four
        // periods the Reports module reads, wired to the period endpoint.
        $this->assertStringContainsString('Report Type', $html);
        $this->assertStringContainsString('id="period_type"', $html);
        $this->assertStringContainsString('value="weekly"', $html);
        $this->assertStringContainsString('value="monthly"', $html);
        $this->assertStringContainsString('value="yearly"', $html);
        $this->assertStringContainsString('value="custom"', $html);
        $this->assertStringContainsString('Report Type', $html);
        $this->assertStringContainsString('reports/violators/period', $html);
        $this->assertStringContainsString('id="vdrReportForm"', $html);

        // The page itself is still the directory of violators.
        $this->assertStringContainsString('Violator Reports', $html);
        $this->assertStringContainsString('No violators on record yet.', $html);
    }

    public function testPeriodReportViewRendersWithTheControllersDataContract(): void
    {
        $html = service('renderer')->setData($this->periodReportData())->render('admin/violators/report_period');

        // The letterhead and the period's own three sections.
        $this->assertStringContainsString('APPREHENSION &amp; SANCTION REPORT', $html);
        $this->assertStringContainsString('Weekly Report:', $html);
        $this->assertStringContainsString('Nov 09, 2026 – Nov 15, 2026', $html);
        $this->assertStringContainsString('I. Daily Summary', $html);
        $this->assertStringContainsString('II. Apprehensions by Day', $html);
        $this->assertStringContainsString('III. Violator Summary for the Period', $html);
        $this->assertSame(3, substr_count($html, 'onclick="printViolatorReportSection(this)"'));
        $this->assertStringContainsString('function printViolatorReportSection(button)', $html);

        // The day figures, the ticket row and the violator line all print.
        $this->assertStringContainsString('Nov 10, 2026', $html);
        $this->assertStringContainsString('Jake T. Cuenca', $html);
        $this->assertStringContainsString('1:20 AM', $html);
        $this->assertStringContainsString('Kabankalan Proper', $html);

        // The toolbar keeps the Report Type switcher so the period can change
        // without going back: full year hands over to the annual document.
        $this->assertStringContainsString('Report Type', $html);
        $this->assertStringContainsString('id="period_type"', $html);
        $this->assertStringContainsString('Full Year', $html);
        $this->assertStringContainsString('Custom Range', $html);
        $this->assertStringContainsString('reports/violators/period', $html);

        // The shared annual machinery stays out of the period document.
        $this->assertStringNotContainsString('ANNUAL APPREHENSION', $html);
        $this->assertStringNotContainsString('MONTHLY SUMMARY', $html);
    }

    public function testYearReportNamesSectionOneYearlyApprehensionRecord(): void
    {
        $html = service('renderer')->setData([
            'title'           => 'Full-Year Violator Report 2026',
            'year'            => 2026,
            'years'           => [2026],
            'search'          => '',
            'month_names'     => [
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
            ],
            'calendar'        => [],
            'calendar_totals' => [],
            'month_summary'   => [],
            'month_detail'    => [],
            'per_violator'    => [],
            'totals'          => [
                'apprehensions' => 0,
                'violators'     => 0,
                'entries'       => 0,
                'assessed'      => 0.0,
                'paid'          => 0.0,
                'pending'       => 0.0,
                'status_amounts'=> ['Paid' => 0.0, 'Pending' => 0.0, 'Cancelled' => 0.0],
                'status_entries'=> ['Paid' => 0, 'Pending' => 0, 'Cancelled' => 0],
            ],
            'back_url'        => 'http://localhost/reports/violators',
        ])->render('admin/violators/report_year');

        $this->assertStringContainsString('I. Yearly Traffic Apprehension Record', $html);
        $this->assertStringNotContainsString('I. Daily Traffic Apprehension Record', $html);
        $this->assertSame(5, substr_count($html, 'onclick="printViolatorReportSection(this)"'));
        $this->assertStringContainsString('body.vpr-print-section .report-section:not(.vpr-print-target)', $html);
    }
}
