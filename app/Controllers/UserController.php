<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ViolationModel;

class UserController extends BaseController
{
    public function index()
    {
        $session = session();
        $violationModel = new ViolationModel();
        
        // For now, show all pending violations for the driver
        // In a real system, we'd filter by driver's license plate or user account
        $data = [
            'title' => 'User Dashboard',
            'username' => $session->get('username'),
            'role' => $session->get('role'),
            'pending_violations' => $violationModel->where('status', 'Pending')->orderBy('violation_date', 'DESC')->findAll(),
            'paid_violations' => $violationModel->where('status', 'Paid')->orderBy('paid_date', 'DESC')->findAll(),
        ];
        
        return view('user/dashboard', $data);
    }

    public function viewViolation($ticketId = null)
    {
        $violationModel = new ViolationModel();
        $violation = $violationModel->where('ticket_id', $ticketId)->first();
        
        if (!$violation) {
            return redirect()->to('/user/dashboard')->with('error', 'Violation not found.');
        }
        
        $data = [
            'title' => 'Violation Details',
            'violation' => $violation
        ];
        
        return view('user/view_violation', $data);
    }

    public function payViolation($ticketId = null)
    {
        $violationModel = new ViolationModel();
        $violation = $violationModel->where('ticket_id', $ticketId)->first();
        
        if (!$violation) {
            return redirect()->to('/user/dashboard')->with('error', 'Violation not found.');
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
        $session = session();
        $violationModel = new ViolationModel();
        
        $ticketId = $this->request->getPost('ticket_id');
        $paymentMethod = $this->request->getPost('payment_method');
        
        $violation = $violationModel->where('ticket_id', $ticketId)->first();
        
        if (!$violation || $violation['status'] !== 'Pending') {
            return redirect()->to('/user/dashboard')->with('error', 'Invalid violation or already paid.');
        }
        
        // Generate receipt number
        $receiptNumber = 'RCP-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));
        
        // Update violation to paid
        $updateData = [
            'status' => 'Paid',
            'paid_date' => date('Y-m-d H:i:s'),
            'payment_method' => $paymentMethod,
            'receipt_number' => $receiptNumber,
        ];
        
        if ($violationModel->where('ticket_id', $ticketId)->set($updateData)->update()) {
            // Get updated violation for receipt
            $updatedViolation = $violationModel->where('ticket_id', $ticketId)->first();
            
            return redirect()->to('/user/receipt/' . $ticketId)->with('success', 'Payment successful!');
        } else {
            return redirect()->back()->with('error', 'Payment failed. Please try again.');
        }
    }

    public function receipt($ticketId = null)
    {
        $violationModel = new ViolationModel();
        $violation = $violationModel->where('ticket_id', $ticketId)->first();
        
        if (!$violation || $violation['status'] !== 'Paid') {
            return redirect()->to('/user/dashboard')->with('error', 'Receipt not found.');
        }
        
        $data = [
            'title' => 'Payment Receipt',
            'violation' => $violation
        ];
        
        return view('user/receipt', $data);
    }
}
