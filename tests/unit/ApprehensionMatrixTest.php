<?php

use App\Controllers\Admin\AdminController;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Exercises the Daily Traffic Apprehension Record builder: per-slot counting,
 * cancelled-citation handling, row/column totals and the violator tallies
 * (distinct and repeat).
 *
 * @internal
 */
final class ApprehensionMatrixTest extends CIUnitTestCase
{
    /**
     * @param list<array<string, mixed>> $records
     *
     * @return array<string, mixed>
     */
    private function matrix(array $records, string $periodType, string $startDate, string $endDate, ?string $status = null): array
    {
        $controller = new AdminController();
        $method     = new ReflectionMethod(AdminController::class, 'buildApprehensionMatrix');
        $method->setAccessible(true);

        return $method->invoke($controller, $records, $periodType, $startDate, $endDate, $status);
    }

    /**
     * Two officers' March records: a duplicate No Helmet infraction on one
     * ticket, a paid Obstruction, a cancelled Obstruction (void) and a later
     * Obstruction by the same violator as the No Helmet ticket (repeat).
     *
     * @return list<array<string, mixed>>
     */
    private function marchRecords(): array
    {
        $juan  = ['first_name' => 'Juan', 'middle_name' => 'D.', 'last_name' => 'Cruz', 'license_plate' => 'ABC 123', 'driver_name' => 'Juan D. Cruz'];
        $maria = ['first_name' => 'Maria', 'middle_name' => '', 'last_name' => 'Santos', 'license_plate' => 'XYZ 789', 'driver_name' => 'Maria Santos'];
        $pedro = ['first_name' => 'Pedro', 'middle_name' => '', 'last_name' => 'Reyes', 'license_plate' => 'PQR 456', 'driver_name' => 'Pedro Reyes'];

        return [
            array_merge(['id' => 1, 'ticket_id' => '2026-0000001', 'violation_date' => '2026-03-02 09:00:00', 'status' => 'Pending',   'violator_id' => 1, 'violation_name' => 'No Helmet'], $juan),
            array_merge(['id' => 2, 'ticket_id' => '2026-0000001', 'violation_date' => '2026-03-02 09:00:00', 'status' => 'Pending',   'violator_id' => 1, 'violation_name' => 'No Helmet'], $juan),
            array_merge(['id' => 3, 'ticket_id' => '2026-0000002', 'violation_date' => '2026-03-15 10:00:00', 'status' => 'Paid',      'violator_id' => 2, 'violation_name' => 'Obstruction'], $maria),
            array_merge(['id' => 4, 'ticket_id' => '2026-0000003', 'violation_date' => '2026-03-15 11:00:00', 'status' => 'Cancelled', 'violator_id' => 3, 'violation_name' => 'Obstruction'], $pedro),
            array_merge(['id' => 5, 'ticket_id' => '2026-0000004', 'violation_date' => '2026-03-20 14:00:00', 'status' => 'Pending',   'violator_id' => 1, 'violation_name' => 'Obstruction'], $juan),
        ];
    }

    public function testMonthlyMatrixCountsOffensesTotalsAndViolators(): void
    {
        $result = $this->matrix($this->marchRecords(), 'monthly', '2026-03-01', '2026-03-31');

        $this->assertSame(31, count($result['columns']));
        $this->assertSame('For the Month of March 2026', $result['period_label']);
        $this->assertSame('Day', $result['column_noun']);

        // Most recorded offense first; the two-count tie stays alphabetical.
        $this->assertSame(['No Helmet', 'Obstruction'], array_column($result['rows'], 'name'));

        // No Helmet: both infractions land on March 2 and belong to violator 1.
        $this->assertSame(2, $result['rows'][0]['cells'][1]);
        $this->assertSame(2, $result['rows'][0]['total']);
        $this->assertSame(1, $result['rows'][0]['violators']);

        // Obstruction: March 15 (paid) and March 20; the cancelled March 15
        // copy stays out, and the row counts violators 2 and 1.
        $this->assertSame(1, $result['rows'][1]['cells'][14]);
        $this->assertSame(0, $result['rows'][1]['cells'][13]);
        $this->assertSame(1, $result['rows'][1]['cells'][19]);
        $this->assertSame(2, $result['rows'][1]['total']);
        $this->assertSame(2, $result['rows'][1]['violators']);

        // Column totals follow the cells; the cancelled row never inflates them.
        $this->assertSame(2, $result['column_totals'][1]);
        $this->assertSame(4, $result['grand_total']);

        // Violators 1 and 2 are on the record; violator 1 recurs on a second
        // ticket, so exactly one repeat citation is tallied.
        $this->assertSame(2, $result['distinct_violators']);
        $this->assertSame(1, $result['repeat_violators']);
        $this->assertSame(1, $result['excluded_cancelled']);
    }

    public function testCancelledStatusFilterTurnsTheMatrixIntoItsAuditView(): void
    {
        $result = $this->matrix($this->marchRecords(), 'yearly', '2026-01-01', '2026-12-31', 'Cancelled');

        $this->assertSame(12, count($result['columns']));
        $this->assertSame('CY 2026', $result['period_label']);
        $this->assertSame('Month', $result['column_noun']);
        $this->assertSame(0, $result['excluded_cancelled']);

        // Only the cancelled Obstruction is the subject now: one offense in
        // March, under its own violator, counting no money anywhere.
        $this->assertSame(['Obstruction'], array_column($result['rows'], 'name'));
        $this->assertSame(1, $result['rows'][0]['cells'][2]);
        $this->assertSame(1, $result['grand_total']);
        $this->assertSame(1, $result['distinct_violators']);
        $this->assertSame(0, $result['repeat_violators']);
    }

    public function testWeeklyMatrixKeepsOnlyRecordsInsideTheWeek(): void
    {
        $result = $this->matrix($this->marchRecords(), 'weekly', '2026-03-02', '2026-03-08');

        $this->assertSame(7, count($result['columns']));
        $this->assertSame(['No Helmet'], array_column($result['rows'], 'name'));
        $this->assertSame(2, $result['grand_total']);

        // Both infractions share one ticket, so this is one violator with no
        // repeat citation in the week.
        $this->assertSame(1, $result['distinct_violators']);
        $this->assertSame(0, $result['repeat_violators']);
    }

    public function testViolatorMatrixCountsCitationsPerPersonPerSlot(): void
    {
        $controller = new AdminController();
        $method     = new ReflectionMethod(AdminController::class, 'buildViolatorMatrix');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $this->marchRecords(), 'monthly', '2026-03-01', '2026-03-31', null);

        // Most-apprehended first; the cancelled ticket's violator stays out.
        $this->assertSame(['Juan D. Cruz', 'Maria Santos'], array_column($result['rows'], 'name'));
        $this->assertSame('ABC 123', $result['rows'][0]['plate']);

        // Violator 1 holds two tickets (March 2 and March 20). Each cell
        // counts citations, so the March 2 ticket counts once even though it
        // carries two infractions - three offenses in all.
        $this->assertSame(1, $result['rows'][0]['cells'][1]);
        $this->assertSame(1, $result['rows'][0]['cells'][19]);
        $this->assertSame(2, $result['rows'][0]['total']);
        $this->assertSame(3, $result['rows'][0]['offenses']);

        $this->assertSame(2, $result['total_violators']);
        $this->assertSame(0, $result['hidden_rows']);
        $this->assertSame(3, $result['grand_total']);
        $this->assertSame(3, $result['ticket_total']);

        // The citation detail names what each ticket carried and when,
        // newest first, so the sheet answers "who, what and when".
        $this->assertCount(2, $result['rows'][0]['details']);
        $this->assertSame('2026-03-20 14:00:00', $result['rows'][0]['details'][0]['date']);
        $this->assertSame(['Obstruction'], $result['rows'][0]['details'][0]['offenses']);
        $this->assertSame('Pending', $result['rows'][0]['details'][0]['status']);
        $this->assertSame('2026-0000004', $result['rows'][0]['details'][0]['ticket_id']);
        $this->assertSame(['No Helmet'], $result['rows'][0]['details'][1]['offenses']);
    }

    public function testViolatorMatrixKeepsOnlyTheTopRowsAndReportsTheRest(): void
    {
        $controller = new AdminController();
        $method     = new ReflectionMethod(AdminController::class, 'buildViolatorMatrix');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $this->marchRecords(), 'monthly', '2026-03-01', '2026-03-31', null, 1);

        $this->assertSame(2, $result['total_violators']);
        $this->assertSame(1, $result['hidden_rows']);
        $this->assertCount(1, $result['rows']);
        $this->assertSame('Juan D. Cruz', $result['rows'][0]['name']);
    }
}
