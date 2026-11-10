<?php

use App\Models\ViolatorRecord;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Pins the repeat-offender ranking used by the Violations Summary Report:
 * one ticket is one citation, two or more valid citations make a repeat
 * offender, and cancelled (void) tickets do not count unless the report
 * itself was filtered to cancelled citations.
 *
 * @internal
 */
final class RepeatViolatorRankingTest extends CIUnitTestCase
{
    public function testRanksByCitationCountAndAppliesTheRepeatMinimum(): void
    {
        $rows = [
            // Three citations — rank 1.
            ['violator_id' => 1, 'driver_name' => 'Ana Cruz', 'license_plate' => 'ABC 123', 'ticket_id' => '2026-0000001', 'status' => 'Paid', 'penalty_amount' => 100, 'violation_date' => '2026-03-01 09:00:00'],
            ['violator_id' => 1, 'driver_name' => 'Ana Cruz', 'license_plate' => 'ABC 123', 'ticket_id' => '2026-0000002', 'status' => 'Pending', 'penalty_amount' => 100, 'violation_date' => '2026-04-01 09:00:00'],
            ['violator_id' => 1, 'driver_name' => 'Ana Cruz', 'license_plate' => 'ABC 123', 'ticket_id' => '2026-0000003', 'status' => 'Paid', 'penalty_amount' => 100, 'violation_date' => '2026-05-01 09:00:00'],
            // Two citations — rank 2.
            ['violator_id' => 2, 'driver_name' => 'Ben Reyes', 'license_plate' => 'XYZ 987', 'ticket_id' => '2026-0000010', 'status' => 'Pending', 'penalty_amount' => 200, 'violation_date' => '2026-02-10 08:00:00'],
            ['violator_id' => 2, 'driver_name' => 'Ben Reyes', 'license_plate' => 'XYZ 987', 'ticket_id' => '2026-0000011', 'status' => 'Pending', 'penalty_amount' => 200, 'violation_date' => '2026-02-11 08:00:00'],
            // One citation — not a repeat offender.
            ['violator_id' => 3, 'driver_name' => 'Cara Lim', 'license_plate' => 'LMN 456', 'ticket_id' => '2026-0000020', 'status' => 'Paid', 'penalty_amount' => 300, 'violation_date' => '2026-01-05 07:00:00'],
        ];

        $ranked = ViolatorRecord::rankRepeatViolators($rows);

        $this->assertSame(['Ana Cruz', 'Ben Reyes'], array_column($ranked, 'driver_name'));
        $this->assertSame([3, 2], array_column($ranked, 'citations'));
        $this->assertSame([1, 2], array_column($ranked, 'violator_id'));
        $this->assertSame(['2026-05-01 09:00:00', '2026-02-11 08:00:00'], array_column($ranked, 'last_violation_at'));
    }

    public function testOneTicketWithSeveralOffensesIsOneCitation(): void
    {
        $rows = [
            ['violator_id' => 7, 'driver_name' => 'Dino Padilla', 'license_plate' => 'PQR 111', 'ticket_id' => '2026-0000030', 'status' => 'Paid', 'penalty_amount' => 100, 'violation_date' => '2026-06-01 10:00:00'],
            ['violator_id' => 7, 'driver_name' => 'Dino Padilla', 'license_plate' => 'PQR 111', 'ticket_id' => '2026-0000030', 'status' => 'Paid', 'penalty_amount' => 250, 'violation_date' => '2026-06-01 10:00:00'],
            ['violator_id' => 7, 'driver_name' => 'Dino Padilla', 'license_plate' => 'PQR 111', 'ticket_id' => '2026-0000031', 'status' => 'Paid', 'penalty_amount' => 300, 'violation_date' => '2026-06-02 10:00:00'],
        ];

        $ranked = ViolatorRecord::rankRepeatViolators($rows);

        $this->assertCount(1, $ranked);
        $this->assertSame(2, $ranked[0]['citations']);
    }

    public function testCancelledCitationIsVoidAndDoesNotCountTowardRepeatStatus(): void
    {
        $rows = [
            ['violator_id' => 4, 'driver_name' => 'Elena Bautista', 'license_plate' => 'DEF 222', 'ticket_id' => '2026-0000040', 'status' => 'Paid', 'penalty_amount' => 100, 'violation_date' => '2026-07-01 09:00:00'],
            // Fully cancelled second ticket: void, so this driver has only
            // one valid citation and is not a repeat offender.
            ['violator_id' => 4, 'driver_name' => 'Elena Bautista', 'license_plate' => 'DEF 222', 'ticket_id' => '2026-0000041', 'status' => 'Cancelled', 'penalty_amount' => 300, 'violation_date' => '2026-07-02 09:00:00'],
        ];

        $this->assertSame([], ViolatorRecord::rankRepeatViolators($rows));

        // When the report itself is about cancelled citations, those voided
        // tickets are the subject and do count toward the ranking.
        $cancelledRows = [
            ['violator_id' => 5, 'driver_name' => 'Ferdie Ocampo', 'license_plate' => 'GHI 333', 'ticket_id' => '2026-0000042', 'status' => 'Cancelled', 'penalty_amount' => 150, 'violation_date' => '2026-07-03 09:00:00'],
            ['violator_id' => 5, 'driver_name' => 'Ferdie Ocampo', 'license_plate' => 'GHI 333', 'ticket_id' => '2026-0000043', 'status' => 'Cancelled', 'penalty_amount' => 150, 'violation_date' => '2026-07-04 09:00:00'],
        ];

        $this->assertSame([], ViolatorRecord::rankRepeatViolators($cancelledRows));

        $ranked = ViolatorRecord::rankRepeatViolators($cancelledRows, 10, 2, true);

        $this->assertCount(1, $ranked);
        $this->assertSame(2, $ranked[0]['citations']);
    }

    public function testAnonymousRowsFallBackToNameAndPlateGrouping(): void
    {
        $rows = [
            ['violator_id' => null, 'driver_name' => 'Gina Torres', 'license_plate' => 'JKL 444', 'ticket_id' => '2026-0000050', 'status' => 'Paid', 'penalty_amount' => 100, 'violation_date' => '2026-08-01 09:00:00'],
            ['violator_id' => null, 'driver_name' => 'gina torres', 'license_plate' => 'jkl 444', 'ticket_id' => '2026-0000051', 'status' => 'Pending', 'penalty_amount' => 100, 'violation_date' => '2026-08-02 09:00:00'],
        ];

        $ranked = ViolatorRecord::rankRepeatViolators($rows);

        $this->assertCount(1, $ranked);
        $this->assertNull($ranked[0]['violator_id']);
        $this->assertSame(2, $ranked[0]['citations']);
        // The first non-empty spelling is kept (rows arrive newest first).
        $this->assertSame('Gina Torres', $ranked[0]['driver_name']);
    }

    public function testTiesBreakByMostRecentCitationThenNameAndLimitSlices(): void
    {
        $rows = [
            ['violator_id' => 10, 'driver_name' => 'Zed Villa', 'license_plate' => 'AAA 000', 'ticket_id' => 'T-1', 'status' => 'Paid', 'penalty_amount' => 100, 'violation_date' => '2026-09-01 09:00:00'],
            ['violator_id' => 10, 'driver_name' => 'Zed Villa', 'license_plate' => 'AAA 000', 'ticket_id' => 'T-2', 'status' => 'Paid', 'penalty_amount' => 100, 'violation_date' => '2026-09-02 09:00:00'],
            ['violator_id' => 11, 'driver_name' => 'Amy Bautista', 'license_plate' => 'BBB 000', 'ticket_id' => 'T-3', 'status' => 'Paid', 'penalty_amount' => 100, 'violation_date' => '2026-09-05 09:00:00'],
            ['violator_id' => 11, 'driver_name' => 'Amy Bautista', 'license_plate' => 'BBB 000', 'ticket_id' => 'T-4', 'status' => 'Paid', 'penalty_amount' => 100, 'violation_date' => '2026-09-06 09:00:00'],
        ];

        $ranked = ViolatorRecord::rankRepeatViolators($rows);

        // Both have two citations; Amy's most recent citation is newer.
        $this->assertSame(['Amy Bautista', 'Zed Villa'], array_column($ranked, 'driver_name'));

        $topOne = ViolatorRecord::rankRepeatViolators($rows, 1);
        $this->assertCount(1, $topOne);
        $this->assertSame('Amy Bautista', $topOne[0]['driver_name']);
    }
}
