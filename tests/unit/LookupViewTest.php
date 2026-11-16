<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Renders the public lookup view with the same data contract
 * LookupController::index() emits, so the sanction badges (2nd = amber,
 * 3 or more = red, badges only — never the names) and the pending-only
 * total rule are checked at test time instead of on the live portal.
 *
 * @internal
 */
final class LookupViewTest extends CIUnitTestCase
{
    private function record(array $overrides = []): array
    {
        return array_merge([
            'ticket_id'       => '2026-0000013',
            'violator_id'     => 1,
            'driver_name'     => 'Noel Salvio Sanchez',
            'first_name'      => 'Noel',
            'last_name'       => 'Sanchez',
            'address'         => 'Bocana',
            'license_plate'   => 'MYOP0',
            'penalty_amount'  => 1500.0,
            'status'          => 'Paid',
            'receipt_number'  => '78945',
            'violation_date'  => '2026-10-23 14:13:00',
            'created_at'      => '2026-10-23 14:13:00',
            'violation_type'  => 'Out of Route',
            'location'        => 'Biryain, Kabankalan City',
            'total_citations' => 3,
            'breakdown'       => [],
        ], $overrides);
    }

    private function render(array $records): string
    {
        $paid = array_values(array_filter($records, static fn ($r) => ($r['status'] ?? '') === 'Paid'));
        $pending = array_values(array_filter($records, static fn ($r) => ($r['status'] ?? '') === 'Pending'));
        $sum = static fn (array $rows): float => array_sum(array_map(static fn ($r) => (float) ($r['penalty_amount'] ?? 0), $rows));

        $stats = [
            'total'           => count($records),
            'pending_count'   => count($pending),
            'pending_amount'  => $sum($pending),
            'paid_count'      => count($paid),
            'paid_amount'     => $sum($paid),
            'violator_name'   => '',
            'license_plate'   => '',
            'violation_count' => count($records),
            'grand_total'     => $sum($paid) + $sum($pending),
        ];

        return service('renderer')->setData([
            'query'    => 'kenneth',
            'searched' => true,
            'records'  => $records,
            'stats'    => $stats,
        ])->render('lookup/index');
    }

    public function testQuickSearchExamplesAreNotRendered(): void
    {
        $html = $this->render([]);

        $this->assertStringNotContainsString('Quick Search Examples:', $html);
        $this->assertStringNotContainsString('lookup?query=MYOP09', $html);
    }

    public function testThirdSanctionAndAboveRenderARedBadgeOnly(): void
    {
        // Three sanctions on the register, plus a single-citation driver.
        $html = $this->render([
            $this->record(),
            $this->record([
                'ticket_id'       => '2026-0000030',
                'violator_id'     => 2,
                'driver_name'     => 'Single Driver',
                'first_name'      => 'Single',
                'last_name'       => 'Driver',
                'license_plate'   => 'ABC1',
                'status'          => 'Pending',
                'receipt_number'  => '',
                'total_citations' => 1,
            ]),
        ]);

        // "3rd Sanction" badge on the row and on the person divider — the
        // colour lives on the badge alone.
        $this->assertStringContainsString('3rd Sanction', $html);
        $this->assertSame(2, substr_count($html, 'class="lookup-sanction-chip lookup-sanction-chip--repeat"'));
        $this->assertSame(0, substr_count($html, 'class="lookup-sanction-chip lookup-sanction-chip--second"'));
        $this->assertStringContainsString('class="lookup-sanction-legend"', $html);

        // Neither names nor rows are ever colour-coded.
        $this->assertSame(0, substr_count($html, 'lookup-sanction-name'));
        $this->assertSame(0, substr_count($html, 'lookup-sanction-row'));
    }

    public function testSecondSanctionRendersAnAmberBadgeOnly(): void
    {
        $html = $this->render([
            $this->record([
                'ticket_id'       => '2026-0000042',
                'violator_id'     => 2,
                'driver_name'     => 'Second Sanction Driver',
                'total_citations' => 2,
            ]),
        ]);

        // A 2nd sanction is a warning, not an alarm: amber badge, never the
        // red repeat badge, and no name colouring.
        $this->assertStringContainsString('2nd Sanction', $html);
        $this->assertSame(1, substr_count($html, 'class="lookup-sanction-chip lookup-sanction-chip--second"'));
        $this->assertSame(0, substr_count($html, 'class="lookup-sanction-chip lookup-sanction-chip--repeat"'));
        $this->assertSame(0, substr_count($html, 'lookup-sanction-name'));
        $this->assertSame(0, substr_count($html, 'lookup-sanction-row'));
    }

    public function testSingleCitationDriverIsNotHighlightedAndSettledShowsNoTotal(): void
    {
        $html = $this->render([
            $this->record(['total_citations' => 1, 'driver_name' => 'Solo Driver']),
        ]);

        $this->assertSame(0, substr_count($html, 'class="lookup-sanction-chip'));
        $this->assertSame(0, substr_count($html, 'class="lookup-sanction-legend"'));

        // The one ticket is already paid: nothing is owed, so no total is
        // printed — the footer says the balance is settled.
        $this->assertStringContainsString('Fully settled', $html);
        $this->assertSame(0, substr_count($html, 'class="lookup-total-amount"'));
    }

    public function testOnlyPendingAmountsAreTotaled(): void
    {
        // One person, two tickets: ₱1,500 paid + ₱1,500 pending. Only the
        // outstanding half may appear as the total.
        $html = $this->render([
            $this->record(),
            $this->record(['ticket_id' => '2026-0000030', 'status' => 'Pending', 'receipt_number' => '']),
        ]);

        $this->assertStringContainsString('Total Pending', $html);
        $this->assertStringContainsString('already-paid citation not included', $html);
        $this->assertSame(1, substr_count($html, 'class="lookup-total-amount"'));
        $this->assertStringContainsString('₱1,500.00', $html);
        $this->assertStringNotContainsString('3,000.00', $html);
    }

    public function testAmountsAcrossDifferentDriversAreNeverCombined(): void
    {
        // Two different people matched one search: a settled driver and one
        // carrying a pending balance.
        $html = $this->render([
            $this->record(),
            $this->record([
                'ticket_id'       => '2026-0000055',
                'violator_id'     => 2,
                'driver_name'     => 'Kenneth Barredo',
                'license_plate'   => 'ABC1',
                'status'          => 'Pending',
                'receipt_number'  => '',
                'total_citations' => 1,
            ]),
        ]);

        // The footer states individual pending balances instead of a
        // combined amount.
        $this->assertStringContainsString('Individual Totals', $html);
        $this->assertStringContainsString('Not combined', $html);
        $this->assertSame(0, substr_count($html, 'class="lookup-total-amount"'));

        // Each driver's own state stays in their divider: settled vs pending.
        $this->assertStringContainsString('Fully settled', $html);
        $this->assertStringContainsString('₱1,500.00 pending', $html);
    }
}
