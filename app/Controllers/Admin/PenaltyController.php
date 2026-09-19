<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ViolatorRecord;

class PenaltyController extends BaseController
{
    protected $violationRecord;

    public function __construct()
    {
        $this->violationRecord = new ViolatorRecord();
    }

    public function index()
    {
        $data = [
            'title' => 'Penalty Management',
            'pending_violations' => $this->violationRecord
                ->select('violations.*, users.username as officer_name, GROUP_CONCAT(CONCAT(violation_type, "::", penalty_amount) SEPARATOR "||") as concatenated_violations, SUM(penalty_amount) as total_penalty_sum, SUM(points) as total_points_sum')
                ->join('users', 'users.id = violations.officer_id', 'left')
                ->where('violations.status', 'Pending')
                ->groupBy('violations.ticket_id')
                ->orderBy('violations.violation_date', 'DESC')
                ->paginate(10, 'penalties'),
            'pager' => $this->violationRecord->pager,
        ];
        return view('admin/penalties/index', $data);
    }

    public function all()
    {
        $data = [
            'title' => 'All Violations',
            'violations' => $this->violationRecord->select('violations.*, users.username as officer_name, GROUP_CONCAT(CONCAT(violation_type, "::", penalty_amount) SEPARATOR "||") as concatenated_violations, SUM(penalty_amount) as total_penalty_sum, SUM(points) as total_points_sum')
                                                   ->join('users', 'users.id = violations.officer_id', 'left')
                                                   ->where('violations.status !=', 'Cancelled')
                                                   ->groupBy('violations.ticket_id')
                                                   ->orderBy('violations.violation_date', 'DESC')
                                                   ->paginate(10, 'all'),
            'pager' => $this->violationRecord->pager,
        ];
        return view('admin/penalties/all', $data);
    }

    public function pay($id = null)
    {
        $violation = $this->violationRecord->find($id);

        if (!$violation || $violation['status'] != 'Pending') {
            return redirect()->to(base_url('penalties'))->with('error', 'Violation not found or already paid.');
        }

        if (!empty($violation['ticket_id'])) {
            $ticketData = $this->violationRecord
                ->select('GROUP_CONCAT(violation_type SEPARATOR "||") as concatenated_violations, SUM(penalty_amount) as total_penalty_sum')
                ->where('ticket_id', $violation['ticket_id'])
                ->first();
            
            if ($ticketData) {
                $violation['violation_type'] = str_replace('||', ', ', $ticketData['concatenated_violations']);
                $violation['penalty_amount'] = $ticketData['total_penalty_sum'];
            }
        }

        $data = [
            'title' => 'Record Payment',
            'violation' => $violation,
        ];
        return view('admin/penalties/pay', $data);
    }

    public function store()
    {
        $violationId = $this->request->getPost('violation_id');
        $paymentMethod = $this->request->getPost('payment_method');
        $remarks = $this->request->getPost('remarks');
        $transactionId = $this->request->getPost('transaction_id');

        $violation = $this->violationRecord->find($violationId);

        if (!$violation) {
            return redirect()->to(base_url('penalties'))->with('error', 'Violation not found.');
        }

        if ($this->violationRecord->recordPayment($violationId, $paymentMethod, $remarks, $transactionId)) {
            return redirect()->to(base_url('penalties'))->with('payment_settled', 'Payment has been settled successfully!');
        } else {
            return redirect()->back()->with('error', 'Failed to record payment.');
        }
    }

    public function history()
    {
        $data = [
            'title' => 'Payment History',
            'payments' => $this->violationRecord->select('violations.*, users.username as officer_name, GROUP_CONCAT(CONCAT(violation_type, "::", penalty_amount) SEPARATOR "||") as concatenated_violations, SUM(penalty_amount) as total_penalty_sum, SUM(points) as total_points_sum')
                                                 ->join('users', 'users.id = violations.officer_id', 'left')
                                                 ->where('violations.status', 'Paid')
                                                 ->groupBy('violations.ticket_id')
                                                 ->orderBy('violations.paid_date', 'DESC')
                                                 ->paginate(10, 'history'),
            'pager' => $this->violationRecord->pager,
        ];
        return view('admin/penalties/history', $data);
    }

    public function reverse($id = null)
    {
        $violation = $this->violationRecord->find($id);

        if (!$violation || $violation['status'] !== 'Paid') {
            return redirect()->to(base_url('penalties/history'))->with('error', 'Violation not found or not in Paid status.');
        }

        $updateData = [
            'status' => 'Pending',
            'paid_date' => null,
            'payment_method' => null,
            'receipt_number' => null
        ];

        if (!empty($violation['ticket_id'])) {
            if ($this->violationRecord->where('ticket_id', $violation['ticket_id'])->set($updateData)->update()) {
                return redirect()->to(base_url('penalties/history'))->with('success', 'Payment has been reversed and ticket is now Pending.');
            }
        } else {
            if ($this->violationRecord->update($id, $updateData)) {
                return redirect()->to(base_url('penalties/history'))->with('success', 'Payment has been reversed and ticket is now Pending.');
            }
        }
        
        return redirect()->to(base_url('penalties/history'))->with('error', 'Failed to reverse payment.');
    }

    public function view($id)
    {
        $violation = $this->violationRecord->getDetailedViolation($id);

        if (!$violation) {
            return redirect()->to(base_url('penalties/all'))->with('error', 'Violation not found.');
        }

        $allViolations = [];
        if (!empty($violation['ticket_id'])) {
            $allViolations = $this->violationRecord
                ->select('violations.*, vt.violation_name')
                ->join('violation_types vt', 'vt.id = violations.violation_type_id', 'left')
                ->where('violations.ticket_id', $violation['ticket_id'])
                ->findAll();
        }

        $data = [
            'title'          => 'Violation Details',
            'violation'      => $violation,
            'all_violations' => $allViolations,
        ];
        return view('admin/penalties/view', $data);
    }

    public function cancel($id)
    {
        $violation = $this->violationRecord->find($id);

        if (!$violation) {
            return redirect()->to(base_url('penalties/all'))->with('error', 'Violation not found.');
        }

        if ($violation['status'] !== 'Pending') {
            return redirect()->to(base_url('penalties/all'))->with('error', 'Only pending violations can be cancelled.');
        }

        $reason = $this->request->getPost('reason') ?? 'Cancelled by admin';

        if ($this->violationRecord->cancelViolation($id, $reason)) {
            return redirect()->to(base_url('penalties/all'))->with('success', 'Violation cancelled successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to cancel violation.');
        }
    }

    public function delete($id = null)
    {
        $violation = $this->violationRecord->find($id);

        if (!$violation) {
            return redirect()->to(base_url('penalties'))->with('error', 'Violation record not found.');
        }

        if (!empty($violation['ticket_id'])) {
            if ($this->violationRecord->where('ticket_id', $violation['ticket_id'])->delete()) {
                return redirect()->to(base_url('penalties'))->with('success', 'Violation ticket deleted successfully.');
            }
        } else {
            if ($this->violationRecord->delete($id)) {
                return redirect()->to(base_url('penalties'))->with('success', 'Violation record deleted successfully.');
            }
        }
        
        return redirect()->back()->with('error', 'Failed to delete violation record.');
    }

    public function search()
    {
        $keyword = $this->request->getGet('q');

        if (empty($keyword)) {
            return redirect()->to(base_url('penalties/all'));
        }

        $data = [
            'title' => 'Search Results',
            'violations' => $this->violationRecord->searchViolations($keyword),
            'keyword' => $keyword,
        ];
        return view('admin/penalties/search', $data);
    }
}
