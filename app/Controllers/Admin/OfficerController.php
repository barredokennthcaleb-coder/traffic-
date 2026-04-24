<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ViolationRecord;
use App\Models\ViolationTypeModel;
use App\Models\UserModel;

class OfficerController extends BaseController
{
    protected $violationRecord;
    protected $violationTypeModel;
    protected $userModel;

    public function __construct()
    {
        $this->violationRecord = new ViolationRecord();
        $this->violationTypeModel = new ViolationTypeModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Record Violation',
            'violationTypes' => $this->violationTypeModel->where('status', 'active')->findAll(),
        ];
        return view('admin/officer/index', $data);
    }

    public function store()
    {
        $session = session();

        $violationTypeId = $this->request->getPost('violation_type_id');
        $violationType = $this->violationTypeModel->find($violationTypeId);

        if (!$violationType) {
            return redirect()->back()->with('error', 'Invalid violation type selected.');
        }

        $data = [
            'driver_name'      => trim($this->request->getPost('driver_name')),
            'license_plate'    => trim($this->request->getPost('license_plate')),
            'officer_id'       => $session->get('id'),
            'violation_type_id' => $violationTypeId,
            'violation_type'   => $violationType['violation_name'],
            'penalty_amount'   => $violationType['fine_amount'],
            'points'           => $violationType['points'] ?? 0,
            'status'           => 'Pending',
            'violation_date'   => date('Y-m-d H:i:s'),
            'created_by'       => $session->get('id'),
            'location'         => trim($this->request->getPost('location') ?? ''),
            'notes'            => trim($this->request->getPost('notes') ?? ''),
        ];

        if ($this->violationRecord->save($data)) {
            $ticketId = $this->violationRecord->getInsertID();
            $savedViolation = $this->violationRecord->find($ticketId);
            return redirect()->to('/officer/violations')->with('success', "Violation recorded successfully! Ticket ID: {$savedViolation['ticket_id']}");
        } else {
            return redirect()->back()->withInput()->with('errors', $this->violationRecord->errors());
        }
    }

    public function violations()
    {
        $session = session();

        $builder = $this->violationRecord->builder();
        if ($session->get('role') === 'traffic_officer') {
            $builder->where('officer_id', $session->get('id'));
        }

        $data = [
            'title' => 'My Recorded Violations',
            'violations' => $this->violationRecord->getAllDetailed(),
        ];
        return view('admin/officer/violations', $data);
    }

    public function view($id)
    {
        $violation = $this->violationRecord->getDetailedViolation($id);

        if (!$violation) {
            return redirect()->to('/officer/violations')->with('error', 'Violation not found.');
        }

        $data = [
            'title' => 'Violation Details',
            'violation' => $violation
        ];
        return view('admin/officer/view', $data);
    }

    public function cancel($id)
    {
        $violation = $this->violationRecord->find($id);

        if (!$violation) {
            return redirect()->to('/officer/violations')->with('error', 'Violation not found.');
        }

        if ($violation['status'] !== 'Pending') {
            return redirect()->to('/officer/violations')->with('error', 'Only pending violations can be cancelled.');
        }

        $reason = $this->request->getPost('reason') ?? 'Cancelled by officer';

        if ($this->violationRecord->cancelViolation($id, $reason)) {
            return redirect()->to('/officer/violations')->with('success', 'Violation cancelled successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to cancel violation.');
        }
    }

    public function getViolationTypes()
    {
        if ($this->request->isAJAX()) {
            $types = $this->violationTypeModel->where('status', 'active')->findAll();
            return $this->response->setJSON($types);
        }
    }

    public function getFineAmount()
    {
        if ($this->request->isAJAX()) {
            $typeId = $this->request->getGet('type_id');
            $type = $this->violationTypeModel->find($typeId);

            if ($type) {
                return $this->response->setJSON([
                    'success' => true,
                    'fine_amount' => $type['fine_amount'],
                    'points' => $type['points'],
                    'description' => $type['description']
                ]);
            }
            return $this->response->setJSON(['success' => false]);
        }
    }
}
