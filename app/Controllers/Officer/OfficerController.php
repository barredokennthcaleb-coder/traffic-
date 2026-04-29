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
        return redirect()->to(base_url('officer/violations'));
    }

    public function profile()
    {
        $session = session();
        $officerId = (int) $session->get('id');

        $officer = $this->userModel->find($officerId);
        if (!$officer) {
            return redirect()->to(base_url('officer'))->with('error', 'Officer profile not found.');
        }

        $records = $this->violationRecord
            ->where('officer_id', $officerId)
            ->orderBy('violation_date', 'DESC')
            ->findAll();

        $totalAmount = array_sum(array_map(static function ($record) {
            return (float) ($record['penalty_amount'] ?? 0);
        }, $records));

        $data = [
            'title'          => 'My Profile',
            'officer'        => $officer,
            'records'        => $records,
            'pendingCount'   => count(array_filter($records, static fn($r) => ($r['status'] ?? '') === 'Pending')),
            'paidCount'      => count(array_filter($records, static fn($r) => ($r['status'] ?? '') === 'Paid')),
            'cancelledCount' => count(array_filter($records, static fn($r) => ($r['status'] ?? '') === 'Cancelled')),
            'totalAmount'    => $totalAmount,
        ];

        return view('officer/profile', $data);
    }

    public function store()
    {
        $session = session();

        $rules = [
            'first_name'        => 'required|min_length[2]|max_length[100]',
            'last_name'         => 'required|min_length[2]|max_length[100]',
            'age'               => 'required|integer|greater_than_equal_to[16]|less_than_equal_to[120]',
            'address'           => 'required|min_length[5]|max_length[255]',
            'license_plate'     => 'required|min_length[2]|max_length[20]',
            'violation_type_id' => 'required|integer',
            'location'          => 'permit_empty|max_length[255]',
            'notes'             => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $violationTypeId = $this->request->getPost('violation_type_id');
        $violationType = $this->violationTypeModel->find($violationTypeId);

        if (!$violationType) {
            return redirect()->back()->with('error', 'Invalid violation type selected.');
        }

        $firstName = trim((string) $this->request->getPost('first_name'));
        $lastName = trim((string) $this->request->getPost('last_name'));
        $licensePlate = strtoupper(trim((string) $this->request->getPost('license_plate')));

        // Prevent accidental duplicate pending tickets for the same plate + violation type.
        $existingPending = $this->violationRecord
            ->where('license_plate', $licensePlate)
            ->where('violation_type_id', (int) $violationTypeId)
            ->where('status', 'Pending')
            ->orderBy('violation_date', 'DESC')
            ->first();

        if ($existingPending) {
            return redirect()->back()->withInput()->with(
                'error',
                'A pending ticket for this license plate and violation type already exists (Ticket: ' . ($existingPending['ticket_id'] ?? '#') . ').'
            );
        }
        $baseFine = (float) ($violationType['fine_amount'] ?? 0);
        $basePoints = (int) ($violationType['points'] ?? 0);
        $computedFine = $baseFine;
        $computedPoints = $basePoints;

        $data = [
            'first_name'       => $firstName,
            'last_name'        => $lastName,
            'age'              => (int) $this->request->getPost('age'),
            'address'          => trim((string) $this->request->getPost('address')),
            'driver_name'      => trim($firstName . ' ' . $lastName),
            'license_plate'    => $licensePlate,
            'officer_id'       => $session->get('id'),
            'violation_type_id' => $violationTypeId,
            'violation_type'   => $violationType['violation_name'],
            'penalty_amount'   => $computedFine,
            'points'           => $computedPoints,
            'status'           => 'Pending',
            'violation_date'   => date('Y-m-d H:i:s'),
            'created_by'       => $session->get('id'),
            'location'         => trim($this->request->getPost('location') ?? ''),
            'notes'            => trim($this->request->getPost('notes') ?? ''),
        ];

        if ($this->violationRecord->save($data)) {
            $ticketId = $this->violationRecord->getInsertID();
            return redirect()->to(base_url('officer/view/' . $ticketId . '?print=1&from=violations'))
                ->with('success', 'Violation recorded successfully.');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->violationRecord->errors());
        }
    }

    public function violations()
    {
        $officerId = (int) session()->get('id');

        $data = [
            'title' => 'Violation',
            'violationTypes' => $this->violationTypeModel->where('status', 'active')->findAll(),
            'violations' => $this->violationRecord
                ->where('officer_id', $officerId)
                ->orderBy('violation_date', 'DESC')
                ->findAll(),
        ];
        return view('officer/index', $data);
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

    public function update($id)
    {
        $session = session();
        $officerId = (int) $session->get('id');
        $violation = $this->violationRecord->find($id);

        if (!$violation || (int) ($violation['officer_id'] ?? 0) !== $officerId) {
            return redirect()->to(base_url('officer/violations'))->with('error', 'Violation record not found.');
        }

        if (($violation['status'] ?? '') !== 'Pending') {
            return redirect()->to(base_url('officer/violations'))->with('error', 'Only pending violations can be updated.');
        }

        $violationTypeId = (int) $this->request->getPost('violation_type_id');
        $violationType = $this->violationTypeModel->find($violationTypeId);
        if (!$violationType) {
            return redirect()->back()->withInput()->with('error', 'Invalid violation type selected.');
        }

        $firstName = trim((string) $this->request->getPost('first_name'));
        $lastName = trim((string) $this->request->getPost('last_name'));

        $rules = [
            'first_name'    => 'required|min_length[2]|max_length[100]',
            'last_name'     => 'required|min_length[2]|max_length[100]',
            'age'           => 'required|integer|greater_than_equal_to[16]|less_than_equal_to[120]',
            'address'       => 'required|min_length[5]|max_length[255]',
            'license_plate' => 'required|min_length[2]|max_length[20]',
            'location'      => 'permit_empty|max_length[255]',
            'notes'         => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id'               => $id,
            'first_name'       => $firstName,
            'last_name'        => $lastName,
            'age'              => (int) $this->request->getPost('age'),
            'address'          => trim((string) $this->request->getPost('address')),
            'driver_name'      => trim($firstName . ' ' . $lastName),
            'license_plate'    => trim((string) $this->request->getPost('license_plate')),
            'violation_type_id'=> $violationTypeId,
            'violation_type'   => (string) ($violationType['violation_name'] ?? ''),
            'penalty_amount'   => (float) ($violationType['fine_amount'] ?? 0),
            'points'           => (int) ($violationType['points'] ?? 0),
            'location'         => trim((string) ($this->request->getPost('location') ?? '')),
            'notes'            => trim((string) ($this->request->getPost('notes') ?? '')),
        ];

        if ($this->violationRecord->save($data)) {
            return redirect()->to(base_url('officer/violations'))->with('success', 'Violation updated successfully.');
        }

        return redirect()->back()->withInput()->with('errors', $this->violationRecord->errors());
    }

    public function delete($id)
    {
        $session = session();
        $officerId = (int) $session->get('id');
        $violation = $this->violationRecord->find($id);

        if (!$violation || (int) ($violation['officer_id'] ?? 0) !== $officerId) {
            return redirect()->to(base_url('officer/violations'))->with('error', 'Violation record not found.');
        }

        if (($violation['status'] ?? '') !== 'Pending') {
            return redirect()->to(base_url('officer/violations'))->with('error', 'Only pending violations can be deleted.');
        }

        if ($this->violationRecord->delete($id)) {
            return redirect()->to(base_url('officer/violations'))->with('success', 'Violation deleted successfully.');
        }

        return redirect()->to(base_url('officer/violations'))->with('error', 'Failed to delete violation.');
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
