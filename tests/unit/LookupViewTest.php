<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Renders the public lookup view with the same data contract
 * LookupController::index() emits, so the sanction badges (2nd = amber,
 * 3 or more = red, badges only — no coloured names), the name carried by
 * each citation, and the pending-only total rule are checked at test time
 * instead of on the live portal.
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

    private function render(array $records, string $query = 'kenneth'): string
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
            'query'    => $query,
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

        // "3rd Sanction" badge on the ticket row — the colour lives on the
        // badge alone.
        $this->assertStringContainsString('3rd Sanction', $html);
        $this->assertSame(1, substr_count($html, 'class="lookup-sanction-chip lookup-sanction-chip--repeat"'));
        $this->assertSame(0, substr_count($html, 'class="lookup-sanction-chip lookup-sanction-chip--second"'));
        $this->assertStringContainsString('class="lookup-sanction-legend"', $html);

        // Neither names nor rows are ever colour-coded.
        $this->assertSame(0, substr_count($html, 'lookup-sanction-name'));
        $this->assertSame(0, substr_count($html, 'lookup-sanction-row'));

        // The name carried by each ticket is shown with its citation.
        $this->assertStringContainsString('Noel Salvio Sanchez', $html);
        $this->assertStringContainsString('Single Driver', $html);
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
        $this->assertStringContainsString('Second Sanction Driver', $html);
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

    public function testPendingTotalCoversMatchedTicketsAndShowsTheirNames(): void
    {
        // Two different people matched one search: a settled driver and one
        // carrying a pending balance.
        $html = $this->render([
            $this->record(),
            $this->record([
                'ticket_id'       => '2026-0000055',
                'violator_id'     => 2,
                'driver_name'     => 'Private Driver Two',
                'license_plate'   => 'ABC1',
                'status'          => 'Pending',
                'receipt_number'  => '',
                'total_citations' => 1,
            ]),
        ]);

        // The footer totals only what is still outstanding across the matched
        // tickets, and each matched ticket states the name it was issued to.
        $this->assertStringContainsString('Total Pending', $html);
        $this->assertSame(1, substr_count($html, 'class="lookup-total-amount"'));
        $this->assertStringContainsString('₱1,500.00', $html);
        $this->assertStringNotContainsString('Not combined', $html);
        $this->assertStringContainsString('Noel Salvio Sanchez', $html);
        $this->assertStringContainsString('Private Driver Two', $html);
        $this->assertStringNotContainsString('Violator / Driver', $html);
    }

    public function testUppercaseNamesAreTidiedForDisplay(): void
    {
        $html = $this->render([
            $this->record(['driver_name' => 'BRYAN  GUANZON DIGNOS']),
        ]);

        $this->assertStringContainsString('Bryan Guanzon Dignos', $html);
        $this->assertStringNotContainsString('BRYAN  GUANZON DIGNOS', $html);
    }

    public function testOwnerNameIsShownWhenDriverNameIsMissing(): void
    {
        $html = $this->render([
            $this->record([
                'driver_name' => '',
                'owner_name'  => 'Registered Owner Name',
            ]),
        ]);

        $this->assertStringContainsString('Registered Owner Name', $html);
        $this->assertStringContainsString('data-label="Name"', $html);
    }

    public function testMissingNameFallsBackToADash(): void
    {
        $html = $this->render([
            $this->record([
                'driver_name' => '',
                'owner_name'  => '',
            ]),
        ]);

        $this->assertStringContainsString('data-label="Name"', $html);
        $this->assertStringContainsString('&mdash;', $html);
    }

    public function testMtopFromTheRecordIsShownInEachResultRow(): void
    {
        $html = $this->render([
            $this->record(['mtop_number' => '908070', 'total_citations' => 1]),
        ]);

        $this->assertStringContainsString('data-label="MTOP #"', $html);
        $this->assertStringContainsString('lookup-mtop', $html);
        $this->assertStringContainsString('908070', $html);
    }

    public function testSearchedMtopTicketAndPlateAreHighlighted(): void
    {
        $html = $this->render([
            $this->record([
                'ticket_id'      => '2026-0000030',
                'license_plate'  => '12387',
                'mtop_number'    => '908070',
                'total_citations' => 1,
            ]),
        ], '908070');

        // Only the MTOP matches this query: it is marked and its badge ringed,
        // while the plate and ticket stay plain.
        $this->assertStringContainsString('<mark class="lk-match">908070</mark>', $html);
        $this->assertSame(1, substr_count($html, '<mark class="lk-match">'));
        $this->assertStringContainsString('badge lookup-mtop px-2 py-1 lk-field-match', $html);
        $this->assertStringNotContainsString('lookup-plate lk-field-match', $html);

        $html = $this->render([$this->record(['mtop_number' => '908070', 'total_citations' => 1])], 'MYOP0');
        $this->assertStringContainsString('<mark class="lk-match">MYOP0</mark>', $html);
        $this->assertStringContainsString('lookup-plate lk-field-match', $html);

        $html = $this->render([$this->record(['mtop_number' => '908070', 'total_citations' => 1])], '2026-0000013');
        $this->assertStringContainsString('<mark class="lk-match">2026-0000013</mark>', $html);
        $this->assertStringContainsString('lookup-ticket-id lk-field-match', $html);
    }

    public function testUnmatchedQueryDoesNotHighlightAnyField(): void
    {
        $html = $this->render([
            $this->record(['mtop_number' => '908070', 'total_citations' => 1]),
        ]);

        $this->assertStringNotContainsString('<mark class="lk-match">', $html);
        // The CSS class exists in the stylesheet; only its use on a field
        // would mean a value was wrongly highlighted.
        $this->assertStringNotContainsString('lk-field-match"', $html);
    }
}
