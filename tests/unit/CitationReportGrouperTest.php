<?php

use App\Libraries\CitationReportGrouper;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class CitationReportGrouperTest extends CIUnitTestCase
{
    public function testGroupsOffensesByTicketAndPreservesPerOffenseStatus(): void
    {
        $groups = CitationReportGrouper::groupByTicket([
            [
                'id'             => 1,
                'ticket_id'      => '2026-0000001',
                'violation_name' => 'No helmet',
                'status'         => 'Paid',
                'penalty_amount' => 500,
                'receipt_number' => 'OR-123',
            ],
            [
                'id'             => 2,
                'ticket_id'      => '2026-0000001',
                'violation_name' => 'Unlicensed driver',
                'status'         => 'Pending',
                'penalty_amount' => 1000,
                'receipt_number' => '',
            ],
            [
                'id'             => 3,
                'ticket_id'      => '2026-0000002',
                'violation_name' => 'Invalid registration',
                'status'         => 'Cancelled',
                'penalty_amount' => 750,
                'receipt_number' => '',
            ],
        ]);

        $this->assertCount(2, $groups);
        $this->assertSame('2026-0000001', $groups[0]['ticket_id']);
        $this->assertSame(
            ['Paid', 'Pending'],
            array_column($groups[0]['violations'], 'status')
        );
        $this->assertSame(['Paid' => 1, 'Pending' => 1], $groups[0]['status_counts']);
        $this->assertSame('OR-123', $groups[0]['receipt_number']);
        $this->assertSame(1500.0, $groups[0]['amount_total']);
        $this->assertSame(0.0, $groups[1]['amount_total']);
    }

    public function testKeepsRecordsWithoutTicketNumbersSeparate(): void
    {
        $groups = CitationReportGrouper::groupByTicket([
            ['id' => 1, 'status' => 'Pending', 'penalty_amount' => 100],
            ['id' => 2, 'status' => 'Pending', 'penalty_amount' => 200],
        ]);

        $this->assertCount(2, $groups);
    }

    public function testGroupsOneViolatorsTicketsIntoASingleOrganizedBlock(): void
    {
        $groups = CitationReportGrouper::groupByViolator([
            [
                'id' => 1, 'violator_id' => 7, 'ticket_id' => '2026-0000001',
                'first_name' => 'Joel', 'last_name' => 'Dimagiba',
                'violation_name' => 'Unlicensed driver', 'status' => 'Pending', 'penalty_amount' => 1000,
                'violation_date' => '2026-10-27 07:15:00', 'receipt_number' => '',
            ],
            [
                'id' => 2, 'violator_id' => 7, 'ticket_id' => '2026-0000002',
                'first_name' => 'Joel', 'last_name' => 'Dimagiba',
                'violation_name' => 'Out of route', 'status' => 'Paid', 'penalty_amount' => 500,
                'violation_date' => '2026-10-24 23:59:00', 'receipt_number' => '4785',
                'paid_date' => '2026-10-25 08:10:00',
            ],
            [
                'id' => 3, 'violator_id' => 7, 'ticket_id' => '2026-0000002',
                'first_name' => 'Joel', 'last_name' => 'Dimagiba',
                'violation_name' => 'Discourteous driver', 'status' => 'Paid', 'penalty_amount' => 500,
                'violation_date' => '2026-10-24 23:59:00', 'receipt_number' => '4785',
                'paid_date' => '2026-10-25 08:10:00',
            ],
            [
                'id' => 4, 'violator_id' => 8, 'ticket_id' => '2026-0000003',
                'first_name' => 'Kobe', 'last_name' => 'Gicos',
                'violation_name' => 'Unregistered MV', 'status' => 'Pending', 'penalty_amount' => 500,
                'violation_date' => '2026-10-19 07:36:00', 'receipt_number' => '',
            ],
        ]);

        $this->assertCount(2, $groups);

        $joel = $groups[0];
        $this->assertSame(7, $joel['violator_id']);
        $this->assertSame('Joel Dimagiba', $joel['driver_name']);
        $this->assertSame(3, $joel['violation_count']);
        $this->assertCount(2, $joel['tickets']);
        $this->assertSame(['Pending' => 1, 'Paid' => 2], $joel['status_counts']);
        $this->assertSame(2000.0, $joel['amount_total']);
        $this->assertSame(1000.0, $joel['pending_amount']);
        $this->assertSame(1000.0, $joel['paid_amount']);

        // Each ticket carries its own money: the pending citation must never
        // borrow the paid ticket's amounts and vice versa.
        $this->assertSame(1000.0, $joel['tickets'][0]['pending_amount']);
        $this->assertSame(0.0, $joel['tickets'][0]['paid_amount']);
        $this->assertSame(1000.0, $joel['tickets'][0]['amount_total']);

        $paidTicket = $joel['tickets'][1];
        $this->assertSame('2026-0000002', $paidTicket['ticket_id']);
        $this->assertSame('4785', $paidTicket['receipt_number']);
        $this->assertSame('2026-10-25 08:10:00', $paidTicket['paid_date']);
        $this->assertSame(1000.0, $paidTicket['paid_amount']);
        $this->assertSame(0.0, $paidTicket['pending_amount']);
        $this->assertSame(['Paid', 'Paid'], array_column($paidTicket['violations'], 'status'));
        $this->assertSame([500.0, 500.0], array_column($paidTicket['violations'], 'amount'));
        $this->assertSame(
            array_fill(0, 2, '2026-10-25 08:10:00'),
            array_column($paidTicket['violations'], 'paid_date')
        );

        $this->assertSame('Kobe Gicos', $groups[1]['driver_name']);
        $this->assertSame(1, $groups[1]['violation_count']);
    }

    public function testFallsBackToTheSharedIdentityWhenViolatorIdIsMissing(): void
    {
        $groups = CitationReportGrouper::groupByViolator([
            [
                'id' => 1, 'ticket_id' => '2026-0000001',
                'driver_name' => 'Noel Sanchez', 'license_number' => 'DL-9000',
                'violation_name' => 'Out of route', 'status' => 'Paid', 'penalty_amount' => 500,
                'violation_date' => '2026-10-22 19:00:00', 'receipt_number' => '4576',
                'paid_date' => '2026-10-23 10:00:00',
            ],
            [
                'id' => 2, 'ticket_id' => '2026-0000002',
                'driver_name' => 'noel sanchez', 'license_number' => 'dl 9000',
                'violation_name' => 'Discourteous driver', 'status' => 'Paid', 'penalty_amount' => 500,
                'violation_date' => '2026-10-23 14:13:00', 'receipt_number' => '78945',
                'paid_date' => '2026-10-23 14:13:00',
            ],
        ]);

        $this->assertCount(1, $groups);
        $this->assertCount(2, $groups[0]['tickets']);
        $this->assertSame(1000.0, $groups[0]['amount_total']);
    }

    public function testCancelledOffensesAddNoMoneyToTheViolatorsBlock(): void
    {
        $groups = CitationReportGrouper::groupByViolator([
            [
                'id' => 1, 'violator_id' => 3, 'ticket_id' => '2026-0000001',
                'driver_name' => 'Arnold Ripique', 'violation_name' => 'Unregistered MV',
                'status' => 'Cancelled', 'penalty_amount' => 500,
                'violation_date' => '2026-10-19 07:36:00',
            ],
        ]);

        $this->assertSame(0.0, $groups[0]['amount_total']);
        $this->assertSame(0.0, $groups[0]['paid_amount']);
        $this->assertSame(0.0, $groups[0]['pending_amount']);
        $this->assertSame(0.0, $groups[0]['tickets'][0]['amount_total']);
        $this->assertSame(0.0, $groups[0]['tickets'][0]['paid_amount']);
        $this->assertSame(0.0, $groups[0]['tickets'][0]['pending_amount']);
        $this->assertSame(['Cancelled' => 1], $groups[0]['status_counts']);
    }
}
