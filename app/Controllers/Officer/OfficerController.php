<?php

namespace App\Controllers\Officer;

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
        return view('officer/index', $data);
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
            return redirect()->to(base_url('officer/violations'))->with('success', "Violation recorded successfully! Ticket ID: {$savedViolation['ticket_id']}");
        } else {
            return redirect()->back()->withInput()->with('errors', $this->violationRecord->errors());
        }
    }

    public function violations()
    {
        $session = session();

        $builder = $this->violationRecord->builder();
        if ($session->get('role') === 'enforcer') {
            $builder->where('officer_id', $session->get('id'));
        }

        $data = [
            'title' => 'My Recorded Violations',
            'violations' => $this->violationRecord->getAllDetailed(),
        ];
        return view('officer/violations', $data);
    }

    public function view($id)
    {
        $violation = $this->violationRecord->getDetailedViolation($id);

        if (!$violation) {
            return redirect()->to(base_url('officer/violations'))->with('error', 'Violation not found.');
        }

        $data = [
            'title' => 'Violation Details',
            'violation' => $violation
        ];
        return view('officer/view', $data);
    }

    public function cancel($id)
    {
        $violation = $this->violationRecord->find($id);

        if (!$violation) {
            return redirect()->to(base_url('officer/violations'))->with('error', 'Violation not found.');
        }

        if ($violation['status'] !== 'Pending') {
            return redirect()->to(base_url('officer/violations'))->with('error', 'Only pending violations can be cancelled.');
        }

        $reason = $this->request->getPost('reason') ?? 'Cancelled by officer';

        if ($this->violationRecord->cancelViolation($id, $reason)) {
            return redirect()->to(base_url('officer/violations'))->with('success', 'Violation cancelled successfully.');
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

    public function storeViolationType()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Bad request.']);
        }

        $role = (string) session()->get('role');
        if (!in_array($role, ['admin', 'enforcer'], true)) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Forbidden.']);
        }

        $rules = [
            'violation_name' => 'required|min_length[3]|max_length[100]|is_unique[violation_types.violation_name]',
            'description'    => 'permit_empty|max_length[500]',
            'fine_amount'    => 'required|decimal',
            'points'         => 'permit_empty|integer',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        $data = [
            'violation_name' => trim((string) $this->request->getPost('violation_name')),
            'description'    => trim((string) ($this->request->getPost('description') ?? '')),
            'fine_amount'    => (float) $this->request->getPost('fine_amount'),
            'points'         => (int) ($this->request->getPost('points') ?? 0),
            'status'         => 'active',
        ];

        if (!$this->violationTypeModel->insert($data)) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Failed to create violation type.',
                'errors'  => $this->violationTypeModel->errors(),
            ]);
        }

        $id = $this->violationTypeModel->getInsertID();
        $created = $this->violationTypeModel->find($id);

        return $this->response->setJSON([
            'success' => true,
            'type'    => $created,
        ]);
    }
}
