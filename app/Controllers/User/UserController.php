<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Libraries\ViolatorIdentity;
use App\Models\ViolatorModel;
use App\Models\ViolatorRecord;

class UserController extends BaseController
{
    protected $violationRecord;
    protected $violatorModel;

    /** @var list<int>|null */
    private ?array $resolvedViolatorIds = null;

    public function __construct()
    {
        $this->violationRecord = new ViolatorRecord();
        $this->violatorModel   = new ViolatorModel();
    }

    public function index()
    {
        $session = session();

        // Tickets linked to the driver's violator record(s), plus legacy rows
        // that carry the driver's exact full name and were never linked.
        $allViolations = $this->driverTickets();

        $pendingViolations = array_filter($allViolations, static fn ($v) => ($v['status'] ?? '') === 'Pending');
        $paidViolations    = array_filter($allViolations, static fn ($v) => ($v['status'] ?? '') === 'Paid');

        $totalPending = array_sum(array_column($pendingViolations, 'penalty_amount'));
        $totalPaid    = array_sum(array_column($paidViolations, 'penalty_amount'));

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

        $paymentMethod = is_string($paymentMethod) ? trim($paymentMethod) : '';
        if (! in_array($paymentMethod, ['Cash', 'Check', 'Money Order', 'Credit Card', 'GCash', 'PayMaya'], true)) {
            return redirect()->back()->with('error', 'Please select a valid payment method.');
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

        $data = [
            'title' => 'My Violation Records',
            'violations' => $this->driverTickets(),
            'username' => $session->get('username'),
            'role' => $session->get('role')
        ];

        return view('user/violations', $data);
    }

    public function history()
    {
        $session = session();

        $all = $this->driverTickets();

        $data = [
            'title'      => 'My Payment History',
            'violations' => array_filter($all, static fn($v) => ($v['status'] ?? '') === 'Paid'),
            'username'   => $session->get('username'),
            'role'       => $session->get('role'),
        ];

        return view('user/history', $data);
    }

    // ───────────────────────────────────────────────────────────────────
    // Private Helpers
    // ───────────────────────────────────────────────────────────────────

    /**
     * Every citation that belongs to the logged-in driver.
     */
    private function driverTickets(): array
    {
        return $this->violationRecord->getForDriver($this->driverViolatorIds(), $this->driverFullName());
    }

    /**
     * The driver's full name, composed the same way citations store it.
     */
    private function driverFullName(): string
    {
        $session = session();

        $parts = array_filter([
            (string) $session->get('firstname'),
            (string) $session->get('middle_initial'),
            (string) $session->get('lastname'),
        ], static fn ($part) => trim($part) !== '');

        $name = trim(preg_replace('/\s+/', ' ', implode(' ', $parts)));

        return $name !== '' ? $name : trim((string) $session->get('full_name'));
    }

    /**
     * Violator records that belong to this driver: an exact driver's-license
     * match first, then the normalized first+last name used by the registry.
     *
     * @return list<int>
     */
    private function driverViolatorIds(): array
    {
        if ($this->resolvedViolatorIds !== null) {
            return $this->resolvedViolatorIds;
        }

        $session    = session();
        $licenseKey = ViolatorIdentity::licenseKey((string) $session->get('license_number'));
        $nameKey    = ViolatorIdentity::nameKey(
            (string) $session->get('firstname'),
            (string) $session->get('lastname'),
            (string) $session->get('full_name')
        );

        if ($licenseKey === '' && $nameKey === '') {
            return $this->resolvedViolatorIds = [];
        }

        $this->violatorModel->groupStart();

        if ($licenseKey !== '') {
            $this->violatorModel->where('license_key', $licenseKey);
        }

        if ($nameKey !== '') {
            if ($licenseKey !== '') {
                $this->violatorModel->orWhere('name_key', $nameKey);
            } else {
                $this->violatorModel->where('name_key', $nameKey);
            }
        }

        $this->violatorModel->groupEnd();

        return $this->resolvedViolatorIds = array_map(
            static fn ($row) => (int) $row['id'],
            $this->violatorModel->findAll()
        );
    }

    /**
     * A driver owns a violation when it is linked to one of their violator
     * records; legacy unlinked rows must match the exact full name.
     */
    private function assertOwnership(array $violation): bool
    {
        $violatorId = (int) ($violation['violator_id'] ?? 0);

        if ($violatorId > 0) {
            return in_array($violatorId, $this->driverViolatorIds(), true);
        }

        $fullName = $this->driverFullName();

        return $fullName !== ''
            && strcasecmp(trim((string) ($violation['driver_name'] ?? '')), $fullName) === 0;
    }
}
