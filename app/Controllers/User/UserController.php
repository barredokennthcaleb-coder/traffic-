<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\ViolationRecord;

class UserController extends BaseController
{
    protected $violationRecord;

    public function __construct()
    {
        $this->violationRecord = new ViolationRecord();
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
            return redirect()->to('/user/dashboard')->with('error', 'Violation not found.');
        }
        
        // Ensure the violation belongs to the logged-in user
        $session = session();
        if ($violation['license_plate'] !== $session->get('license_plate') && $violation['driver_name'] !== $session->get('username')) {
             return redirect()->to('/user/dashboard')->with('error', 'You do not have permission to view this violation.');
        }

        $data = [
            'title' => 'Violation Details',
            'violation' => $violation
        ];

        return view('user/view_violation', $data);
    }

    public function payViolation($ticketId = null)
    {
        $violation = $this->violationRecord->where('ticket_id', $ticketId)->first();

        if (!$violation) {
            return redirect()->to('/user/dashboard')->with('error', 'Violation not found.');
        }

        // Ensure the violation belongs to the logged-in user
        $session = session();
        if ($violation['license_plate'] !== $session->get('license_plate') && $violation['driver_name'] !== $session->get('username')) {
             return redirect()->to('/user/dashboard')->with('error', 'You do not have permission to pay this violation.');
        }

        if ($violation['status'] !== 'Pending') {
            return redirect()->to('/user/dashboard')->with('error', 'This violation has already been processed.');
        }

        $data = [
            'title' => 'Pay Violation',
            'violation' => $violation
        ];

        return view('user/pay_violation', $data);
    }

    public function processPayment()
    {
        $violationModel = new ViolationRecord();

        $ticketId = $this->request->getPost('ticket_id');
        $paymentMethod = $this->request->getPost('payment_method');

        $violation = $violationModel->where('ticket_id', $ticketId)->first();

        if (!$violation || $violation['status'] !== 'Pending') {
            return redirect()->to('/user/dashboard')->with('error', 'Invalid violation or already paid.');
        }

        // Ensure the violation belongs to the logged-in user
        $session = session();
        if ($violation['license_plate'] !== $session->get('license_plate') && $violation['driver_name'] !== $session->get('username')) {
             return redirect()->to('/user/dashboard')->with('error', 'You do not have permission to pay this violation.');
        }

        if ($violationModel->recordPayment($violation['id'], $paymentMethod)) {
            return redirect()->to('/user/receipt/' . $ticketId)->with('success', 'Payment successful!');
        } else {
            return redirect()->back()->with('error', 'Payment failed. Please try again.');
        }
    }

    public function receipt($ticketId = null)
    {
        $violation = $this->violationRecord->where('ticket_id', $ticketId)->first();

        if (!$violation || $violation['status'] !== 'Paid') {
            return redirect()->to('/user/dashboard')->with('error', 'Receipt not found.');
        }

        // Ensure the violation belongs to the logged-in user
        $session = session();
        if ($violation['license_plate'] !== $session->get('license_plate') && $violation['driver_name'] !== $session->get('username')) {
             return redirect()->to('/user/dashboard')->with('error', 'You do not have permission to view this receipt.');
        }

        $data = [
            'title' => 'Payment Receipt',
            'violation' => $violation
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
        $session = session();
        $licensePlate = $session->get('license_plate');
        $driverName = $session->get('username');

        $data = [
            'title' => 'My Payment History',
            'violations' => array_filter($this->violationRecord->getByDriver($licensePlate, $driverName), function($v) {
                return $v['status'] === 'Paid';
            }),
            'username' => $session->get('username'),
            'role' => $session->get('role')
        ];

        return view('user/history', $data);
    }
}
