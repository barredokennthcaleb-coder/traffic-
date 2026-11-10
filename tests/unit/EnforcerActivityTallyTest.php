<?php

use App\Models\ViolatorRecord;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Pins the one rule every enforcer activity figure reads by:
 * one ticket is one citation, cancelled offences are void (no money, no
 * issued count), and a ticket is settled only when none of its live
 * offences is still pending.
 *
 * @internal
 */
final class EnforcerActivityTallyTest extends CIUnitTestCase
{
    public function testCountsOneTicketOnceNoMatterHowManyOffences(): void
    {
        $tally = ViolatorRecord::summarizeTicketActivity([
            ['ticket_id' => '2026-0000001', 'status' => 'Pending', 'penalty_amount' => 500],
            ['ticket_id' => '2026-0000001', 'status' => 'Pending', 'penalty_amount' => 1000],
        ]);

        $this->assertSame(1, $tally['issued']);
        $this->assertSame(0, $tally['paid']);
        $this->assertSame(1, $tally['pending']);
        $this->assertSame(1500.0, $tally['amount']);
    }

    public function testCancelledTicketIsVoidAndNeverCounted(): void
    {
        $tally = ViolatorRecord::summarizeTicketActivity([
            ['ticket_id' => '2026-0000002', 'status' => 'Cancelled', 'penalty_amount' => 700],
        ]);

        $this->assertSame(0, $tally['issued']);
        $this->assertSame(0, $tally['paid']);
        $this->assertSame(0, $tally['pending']);
        $this->assertSame(0.0, $tally['amount']);
    }

    public function testPartiallyCancelledTicketCountsOnlyItsLiveOffences(): void
    {
        $tally = ViolatorRecord::summarizeTicketActivity([
            ['ticket_id' => '2026-0000003', 'status' => 'Cancelled', 'penalty_amount' => 400],
            ['ticket_id' => '2026-0000003', 'status' => 'Paid', 'penalty_amount' => 600],
        ]);

        $this->assertSame(1, $tally['issued']);
        $this->assertSame(1, $tally['paid']);
        $this->assertSame(0, $tally['pending']);
        $this->assertSame(600.0, $tally['amount']);
    }

    public function testTicketWithAnyLivePendingOffenceStaysPending(): void
    {
        $tally = ViolatorRecord::summarizeTicketActivity([
            ['ticket_id' => '2026-0000004', 'status' => 'Paid', 'penalty_amount' => 300],
            ['ticket_id' => '2026-0000004', 'status' => 'Pending', 'penalty_amount' => 200],
        ]);

        $this->assertSame(1, $tally['issued']);
        $this->assertSame(0, $tally['paid']);
        $this->assertSame(1, $tally['pending']);
        $this->assertSame(500.0, $tally['amount']);
    }

    public function testCancelledSubjectModeKeepsCancelledTicketsCountedWithoutMoney(): void
    {
        // The cancelled-citations report is about the voided tickets
        // themselves, so they stay in the count — still worth zero pesos.
        $tally = ViolatorRecord::summarizeTicketActivity([
            ['ticket_id' => '2026-0000005', 'status' => 'Cancelled', 'penalty_amount' => 900],
            ['ticket_id' => '2026-0000006', 'status' => 'Cancelled', 'penalty_amount' => 100],
        ], true);

        $this->assertSame(2, $tally['issued']);
        $this->assertSame(0, $tally['paid']);
        $this->assertSame(0, $tally['pending']);
        $this->assertSame(0.0, $tally['amount']);
    }

    public function testRowsWithoutTicketIdFallBackToTheirOwnId(): void
    {
        $tally = ViolatorRecord::summarizeTicketActivity([
            ['id' => 11, 'ticket_id' => '', 'status' => 'Paid', 'penalty_amount' => 100],
            ['id' => 11, 'ticket_id' => '', 'status' => 'Paid', 'penalty_amount' => 50],
            ['id' => 12, 'ticket_id' => '', 'status' => 'Pending', 'penalty_amount' => 25],
        ]);

        $this->assertSame(2, $tally['issued']);
        $this->assertSame(1, $tally['paid']);
        $this->assertSame(1, $tally['pending']);
        $this->assertSame(175.0, $tally['amount']);
    }
}
