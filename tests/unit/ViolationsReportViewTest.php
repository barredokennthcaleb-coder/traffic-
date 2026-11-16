<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Renders the Violations Summary Report view with the same data contract
 * AdminController::violationsReport() passes. This catches undefined view
 * variables (and similar template typos) at test time instead of on the
 * live report page, where development mode turns them into an error screen.
 *
 * The data set intentionally mirrors the controller keys one-for-one: if the
 * controller stops passing a key the view needs, update both together.
 *
 * @internal
 */
final class ViolationsReportViewTest extends CIUnitTestCase
{
    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function reportData(array $overrides = []): array
    {
        return array_merge([
            'title'             => 'Traffic Violations Summary Report',
            'period_type'       => 'yearly',
            'awaiting_filter'   => false,
            'focus_mode'        => '',
            'status_filter'     => null,
            'type_filter'       => null,
            'all_types'         => [
                ['id' => 1, 'violation_name' => 'Obstruction'],
            ],
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-12-31',
            'month'             => '11',
            'year'              => '2026',
            'records'           => [],
            'summary'           => [
                'total_tickets'    => 3,
                'total_violations' => 4,
                'total_assessed'   => 1800.0,
                'total_paid'       => 800.0,
                'total_pending'    => 1000.0,
                'paid_tickets'     => 1,
                'collection_rate'  => 44.4,
            ],
            'nature_breakdown'  => [
                ['violation_type' => 'Obstruction', 'count' => 3, 'amount' => 1500.0],
                ['violation_type' => 'No Helmet',   'count' => 1, 'amount' => 300.0],
            ],
            'officer_breakdown' => [
                ['officer_name' => 'Rio', 'count' => 2, 'amount' => 1000.0, 'paid' => 1, 'pending' => 1],
            ],
            'timeline_buckets'  => [
                [
                    'label' => 'November 2026', 'short_label' => 'Nov 2026',
                    'start_date' => '2026-11-01', 'end_date' => '2026-11-30',
                    'count' => 3, 'revenue' => 1500.0,
                    'by_type' => ['Obstruction' => 3],
                    'by_revenue' => ['Obstruction' => 1500.0],
                    'types_count' => ['Obstruction' => 3],
                    'top_violation' => 'Obstruction', 'top_violation_count' => 3,
                ],
            ],
            'peak_period'       => ['label' => 'November 2026', 'count' => 3, 'top' => 'Obstruction', 'top_count' => 3],
            'top_types'         => ['Obstruction', 'No Helmet'],
            'top_offense'       => ['violation_type' => 'Obstruction', 'count' => 3],
            'top_offense_insight' => [
                'offense'       => 'Obstruction',
                'count'         => 3,
                'pct'           => 75.0,
                'tickets'       => 2,
                'status_counts' => ['Paid' => 1, 'Pending' => 1, 'Cancelled' => 1],
                'peak'          => ['label' => 'November 2026', 'count' => 3],
                'first_seen'    => '2026-11-02 09:00:00',
                'last_seen'     => '2026-11-20 15:00:00',
                'trend'         => [
                    ['label' => 'November 2026', 'count' => 3, 'pct' => 100.0],
                ],
                'officers'      => [
                    ['name' => 'Rio', 'count' => 3],
                ],
                'locations'     => [
                    ['name' => 'Kabankalan Proper', 'count' => 2],
                ],
            ],
            'unique_categories' => 2,
            'violator_count'    => 3,
            'personnel_matrix'  => [
                'period_label'       => 'CY 2026',
                'column_noun'        => 'Month',
                'columns'            => [
                    ['key' => 1, 'label' => 'Jan', 'title' => 'January 2026', 'sub' => null],
                    ['key' => 2, 'label' => 'Feb', 'title' => 'February 2026', 'sub' => null],
                    ['key' => 3, 'label' => 'Mar', 'title' => 'March 2026', 'sub' => null],
                ],
                'rows'               => [
                    ['name' => 'Rio',         'cells' => [1, 0, 1], 'total' => 2],
                    ['name' => 'Mara Santos', 'cells' => [0, 0, 1], 'total' => 1],
                ],
                'column_totals'      => [1, 0, 2],
                'grand_total'        => 3,
                'officer_count'      => 2,
                'active_officers'    => 2,
                'excluded_cancelled' => 0,
            ],
            'status_counts'     => ['Paid' => 1, 'Pending' => 2, 'Cancelled' => 1],
            'apprehension_matrix' => [
                'period_label'       => 'CY 2026',
                'period_short'       => 'CY 2026',
                'column_noun'        => 'Month',
                'tab_label'          => 'Violations • CY 2026',
                'panel_title'        => 'OFFENSES COMMITTED — CY 2026',
                'columns'            => [
                    ['key' => 1, 'label' => 'Jan', 'title' => 'January 2026', 'sub' => null],
                    ['key' => 2, 'label' => 'Feb', 'title' => 'February 2026', 'sub' => null],
                    ['key' => 3, 'label' => 'Mar', 'title' => 'March 2026', 'sub' => null],
                ],
                'rows'               => [
                    ['name' => 'Obstruction', 'cells' => [1, 0, 2], 'total' => 3, 'violators' => 2],
                    ['name' => 'No Helmet',   'cells' => [0, 0, 1], 'total' => 1, 'violators' => 1],
                ],
                'column_totals'      => [1, 0, 3],
                'grand_total'        => 4,
                'right_total'        => 3,
                'distinct_violators' => 3,
                'repeat_violators'   => 1,
                'excluded_cancelled' => 0,
                'peak_index'         => 2,
                'peak_label'         => 'March 2026',
            ],
            'violator_matrix' => [
                'period_short'    => 'CY 2026',
                'column_noun'     => 'Month',
                'tab_label'       => 'Violators • CY 2026',
                'panel_title'     => 'VIOLATORS — CY 2026',
                'columns'         => [
                    ['key' => 1, 'label' => 'Jan', 'title' => 'January 2026', 'sub' => null],
                    ['key' => 2, 'label' => 'Feb', 'title' => 'February 2026', 'sub' => null],
                    ['key' => 3, 'label' => 'Mar', 'title' => 'March 2026', 'sub' => null],
                ],
                'rows'            => [
                    ['name' => 'Juan D. Cruz', 'plate' => 'ABC 123', 'cells' => [1, 0, 1], 'total' => 2, 'offenses' => 3, 'details' => [
                        ['ticket_id' => '2026-0000004', 'date' => '2026-03-20 14:00:00', 'offenses' => ['Obstruction'], 'status' => 'Pending'],
                        ['ticket_id' => '2026-0000001', 'date' => '2026-03-02 09:00:00', 'offenses' => ['No Helmet'], 'status' => 'Pending'],
                    ]],
                    ['name' => 'Maria Santos', 'plate' => 'XYZ 789', 'cells' => [0, 0, 1], 'total' => 1, 'offenses' => 1, 'details' => []],
                ],
                'column_totals'   => [1, 0, 2],
                'grand_total'     => 3,
                'right_total'     => 4,
                'total_violators' => 2,
                'hidden_rows'     => 0,
                'ticket_total'    => 3,
                'peak_index'      => null,
            ],
        ], $overrides);
    }

    public function testViolationsReportViewRendersWithTheControllersDataContract(): void
    {
        $html = service('renderer')->setData($this->reportData())->render('admin/reports/violations');

        // The report carries violations only: no repeat-offender ranking, no
        // drivers-cited list and no focused generator button.
        $this->assertStringNotContainsString('Top Violators / Repeat Offenders', $html);
        $this->assertStringNotContainsString('Repeat offender', $html);
        $this->assertStringNotContainsString('Drivers Cited For It Most', $html);
        $this->assertStringNotContainsString('Ana Cruz', $html);
        $this->assertStringNotContainsString('id="mostCommittedSection"', $html);
        $this->assertStringNotContainsString('Where It Is Committed Most', $html);
        $this->assertStringNotContainsString('Kabankalan Proper', $html);
        $this->assertStringNotContainsString('Most Committed Only', $html);
        $this->assertStringNotContainsString('Situation at a Glance', $html);
        $this->assertStringNotContainsString('Offense Category Ranking', $html);

        // The four-part document: enforcer activity, the personnel Yearly
        // Traffic Apprehension Record, the violation trend and the ranked
        // most-committed offenses.
        $this->assertStringContainsString('TRAFFIC VIOLATIONS SUMMARY REPORT', $html);
        $this->assertStringContainsString('I. Enforcer / Officer Activity Summary', $html);
        $this->assertStringContainsString('Enforcer / Officer Activity Summary', $html);
        $this->assertStringContainsString('II. Yearly Traffic Apprehension Record', $html);
        $this->assertStringContainsString('vsum-matrix', $html);
        $this->assertStringContainsString('Rio', $html);
        $this->assertStringContainsString('III. Violation Trend', $html);
        $this->assertStringContainsString('IV. Most Committed Offenses', $html);

        // ...and the old sheet machinery stays out of the document.
        $this->assertStringNotContainsString('OFFENSES COMMITTED — CY 2026', $html);
        $this->assertStringNotContainsString('id="apprecDirectory"', $html);
        $this->assertStringNotContainsString('id="apprecShowZeros"', $html);
        $this->assertStringNotContainsString('apprec-panel-title', $html);

        // ...with the report-type dropdown and its period detail controls
        // (weekly, monthly, full year and custom range) still in the toolbar.
        $this->assertStringContainsString('Report Type', $html);
        $this->assertStringContainsString('id="period_type"', $html);
        $this->assertStringContainsString('Custom Range', $html);
        // ...and the read-only auto-update toggle still exists.
        $this->assertStringContainsString('id="autoSyncToggle"', $html);
    }

}
