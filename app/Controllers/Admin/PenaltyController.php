<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ViolationModel;
use App\Models\PaymentModel;

class PenaltyController extends BaseController
{
    protected $violationModel;
    protected $paymentModel;

    public function __construct()
    {
        $this->violationModel = new ViolationModel();
        $this->paymentModel = new PaymentModel();
    }

    public function index()
    {
        $data = [
            'violations' => $this->violationModel->where('status', 'Pending')->findAll(),
            'title' => 'Penalty Management'
        ];
        return view('admin/penalties/index', $data);
    }

    public function pay($id = null)
    {
        $violation = $this->violationModel->find($id);
        if (!$violation || $violation['status'] != 'Pending') {
            return redirect()->to('/penalties')->with('error', 'Violation not found or already paid.');
        }

        $data = [
            'violation' => $violation,
            'title' => 'Record Payment'
        ];
        return view('admin/penalties/pay', $data);
    }

    public function store()
    {
        $violationId = $this->request->getPost('violation_id');
        $violation = $this->violationModel->find($violationId);

        if (!$violation) {
            return redirect()->to('/penalties')->with('error', 'Violation not found.');
        }

        $data = [
            'violation_id'   => $violationId,
            'amount_paid'    => $this->request->getPost('amount_paid'),
            'payment_method' => $this->request->getPost('payment_method'),
            'transaction_id' => $this->request->getPost('transaction_id'),
            'remarks'        => $this->request->getPost('remarks'),
            'payment_date'   => date('Y-m-d H:i:s')
        ];

        if ($this->paymentModel->save($data)) {
            // Update violation status to Paid
            $this->violationModel->update($violationId, ['status' => 'Paid']);
            return redirect()->to('/penalties')->with('success', 'Payment recorded successfully.');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->paymentModel->errors());
        }
    }

    public function history()
    {
        $data = [
            'payments' => $this->paymentModel->getPaymentWithViolation(),
            'title' => 'Payment History'
        ];
        return view('admin/penalties/history', $data);
    }
}
