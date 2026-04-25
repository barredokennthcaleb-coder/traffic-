<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ViolationRecord;

class PenaltyController extends BaseController
{
    protected $violationRecord;

    public function __construct()
    {
        $this->violationRecord = new ViolationRecord();
    }

    public function index()
    {
        $data = [
            'title' => 'Penalty Management',
            'pending_violations' => $this->violationRecord->where('status', 'Pending')
                                                          ->orderBy('violation_date', 'DESC')
                                                          ->findAll(),
        ];
        return view('admin/penalties/index', $data);
    }

    public function all()
    {
        $data = [
            'title' => 'All Violations',
            'violations' => $this->violationRecord->getAllDetailed(),
        ];
        return view('admin/penalties/all', $data);
    }

    public function pay($id = null)
    {
        $violation = $this->violationRecord->find($id);

        if (!$violation || $violation['status'] != 'Pending') {
            return redirect()->to(base_url('penalties'))->with('error', 'Violation not found or already paid.');
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

        $violation = $this->violationRecord->find($violationId);

        if (!$violation) {
            return redirect()->to(base_url('penalties'))->with('error', 'Violation not found.');
        }

        if ($this->violationRecord->recordPayment($violationId, $paymentMethod)) {
            return redirect()->to(base_url('penalties/history'))->with('success', 'Payment recorded successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to record payment.');
        }
    }

    public function history()
    {
        $data = [
            'title' => 'Payment History',
            'payments' => $this->violationRecord->where('status', 'Paid')
                                                 ->orderBy('paid_date', 'DESC')
                                                 ->findAll(),
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

        if ($this->violationRecord->update($id, $updateData)) {
            return redirect()->to(base_url('penalties/history'))->with('success', 'Payment has been reversed and ticket is now Pending.');
        } else {
            return redirect()->to(base_url('penalties/history'))->with('error', 'Failed to reverse payment.');
        }
    }

    public function view($id)
    {
        $violation = $this->violationRecord->getDetailedViolation($id);

        if (!$violation) {
            return redirect()->to(base_url('penalties/all'))->with('error', 'Violation not found.');
        }

        $data = [
            'title' => 'Violation Details',
            'violation' => $violation,
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
