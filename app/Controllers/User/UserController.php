<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\ViolatorRecord;

class UserController extends BaseController
{
    protected $violationRecord;

    public function __construct()
    {
        $this->violationRecord = new ViolatorRecord();
    }

    public function index()
    {
        $session = session();
        $licensePlate = $session->get('license_plate'); // Assuming license_plate is stored in session for drivers

        // Fetch violations for the logged-in driver
        $allViolations = $this->violationRecord->getByDriver($licensePlate, $session->get('username'));

        $pendingViolations = array_filter($allViolations, function($v) {
            return $v['status'] === 'Pending';
        });

        $paidViolations = array_filter($allViolations, function($v) {
            return $v['status'] === 'Paid';
        });

        $totalPending = array_sum(array_column($pendingViolations, 'penalty_amount'));
        $totalPaid = array_sum(array_column($paidViolations, 'penalty_amount'));

        $data = [
            'title' => 'User Dashboard',
            'username' => $session->get('username'),
            'role' => $session->get('role'),
            'pending_violations' => $pendingViolations,
            'paid_violations' => $paidViolations,
            'total_pending' => $totalPending,
            'total_paid' => $totalPaid,
            'all_violations_count' => count($allViolations),
        ];

        return view('user/dashboard', $data);
    }

    public function viewViolation($ticketId = null)
    {
        $violation = $this->violationRecord->where('ticket_id', $ticketId)->first();

        if (!$violation) {
            return redirect()->to(base_url('user/dashboard'))->with('error', 'Violation not found.');
        }

        if (!$this->assertOwnership($violation)) {
            return redirect()->to(base_url('user/dashboard'))->with('error', 'You do not have permission to view this violation.');
        }

        $allViolations = $this->violationRecord
            ->select('violations.*, vt.violation_name')
            ->join('violation_types vt', 'vt.id = violations.violation_type_id', 'left')
            ->where('violations.ticket_id', $ticketId)
            ->findAll();

        $data = [
            'title'          => 'Violation Details',
            'violation'      => $violation,
            'all_violations' => $allViolations,
        ];

        return view('user/view_violation', $data);
    }

    public function payViolation($ticketId = null)
    {
        $violation = $this->violationRecord->where('ticket_id', $ticketId)->first();

        if (!$violation) {
            return redirect()->to(base_url('user/dashboard'))->with('error', 'Violation not found.');
        }

        if (!$this->assertOwnership($violation)) {
            return redirect()->to(base_url('user/dashboard'))->with('error', 'You do not have permission to pay this violation.');
        }

        if ($violation['status'] !== 'Pending') {
            return redirect()->to(base_url('user/dashboard'))->with('error', 'This violation has already been processed.');
        }

        $data = [
            'title'     => 'Pay Violation',
            'violation' => $violation,
        ];

        return view('user/pay_violation', $data);
    }

    public function processPayment()
    {
        $ticketId      = $this->request->getPost('ticket_id');
        $paymentMethod = $this->request->getPost('payment_method');

        $violation = $this->violationRecord->where('ticket_id', $ticketId)->first();

        if (!$violation || $violation['status'] !== 'Pending') {
            return redirect()->to(base_url('user/dashboard'))->with('error', 'Invalid violation or already paid.');
        }

        if (!$this->assertOwnership($violation)) {
            return redirect()->to(base_url('user/dashboard'))->with('error', 'You do not have permission to pay this violation.');
        }

        if ($this->violationRecord->recordPayment($violation['id'], $paymentMethod)) {
            return redirect()->to(base_url('user/receipt/' . $ticketId))->with('success', 'Payment successful!');
        }

        return redirect()->back()->with('error', 'Payment failed. Please try again.');
    }

    public function receipt($ticketId = null)
    {
        $violation = $this->violationRecord->where('ticket_id', $ticketId)->first();

        if (!$violation || $violation['status'] !== 'Paid') {
            return redirect()->to(base_url('user/dashboard'))->with('error', 'Receipt not found.');
        }

        if (!$this->assertOwnership($violation)) {
            return redirect()->to(base_url('user/dashboard'))->with('error', 'You do not have permission to view this receipt.');
        }

        // The receipt lists every infraction on the ticket on its own line,
        // each with the fine that was actually collected for it.
        $data = [
            'title'       => 'Payment Receipt',
            'violation'   => $violation,
            'infractions' => $this->violationRecord->getTicketInfractions($violation['ticket_id'] ?? ''),
        ];

        return view('user/receipt', $data);
    }

    public function violations()
    {
        $session = session();
        $licensePlate = $session->get('license_plate');
        $driverName = $session->get('username');

        $data = [
            'title' => 'My Violation Records',
            'violations' => $this->violationRecord->getByDriver($licensePlate, $driverName),
            'username' => $session->get('username'),
            'role' => $session->get('role')
        ];

        return view('user/violations', $data);
    }

    public function history()
    {
        $session      = session();
        $licensePlate = $session->get('license_plate');
        $driverName   = $session->get('username');

        $all = $this->violationRecord->getByDriver($licensePlate, $driverName);

        $data = [
            'title'      => 'My Payment History',
            'violations' => array_filter($all, static fn($v) => $v['status'] === 'Paid'),
            'username'   => $session->get('username'),
            'role'       => $session->get('role'),
        ];

        return view('user/history', $data);
    }

    // ───────────────────────────────────────────────────────────────────
    // Private Helpers
    // ───────────────────────────────────────────────────────────────────

    /**
     * Returns true when the logged-in user owns the given violation record
     * (matched by either license plate or driver name).
     *
     * Centralises the ownership check that was previously copy-pasted
     * in viewViolation(), payViolation(), processPayment(), and receipt().
     */
    private function assertOwnership(array $violation): bool
    {
        $session = session();
        return $violation['license_plate'] === $session->get('license_plate')
            || $violation['driver_name']   === $session->get('username');
    }
}
