<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ViolationModel;
use App\Models\ViolationTypeModel;
use App\Models\UserModel;

class OfficerController extends BaseController
{
    protected $violationModel;
    protected $violationTypeModel;
    protected $userModel;

    public function __construct()
    {
        $this->violationModel = new ViolationModel();
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
        
        $violationType = $this->violationTypeModel->find($this->request->getPost('violation_type'));
        
        if (!$violationType) {
            return redirect()->back()->with('error', 'Invalid violation type selected.');
        }

        $ticketId = 'TKT-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

        $data = [
            'ticket_id'      => $ticketId,
            'driver_name'    => trim($this->request->getPost('driver_name')),
            'license_plate'  => trim($this->request->getPost('license_plate')),
            'officer_id'     => $session->get('id'),
            'violation_type' => $this->request->getPost('violation_type'),
            'penalty_amount' => $violationType['fine_amount'],
            'status'         => 'Pending',
            'violation_date' => date('Y-m-d H:i:s'),
            'created_by'     => $session->get('id'),
        ];

        if ($this->violationModel->save($data)) {
            return redirect()->to('/officer/violations')->with('success', "Violation recorded successfully! Ticket ID: {$ticketId}");
        } else {
            return redirect()->back()->withInput()->with('errors', $this->violationModel->errors());
        }
    }

    public function violations()
    {
        $session = session();
        
        $builder = $this->violationModel->builder();
        if ($session->get('role') === 'traffic_officer') {
            $builder->where('officer_id', $session->get('id'));
        }
        
        $data = [
            'title' => 'My Recorded Violations',
            'violations' => $this->violationModel->orderBy('created_at', 'DESC')->findAll(),
        ];
        return view('officer/violations', $data);
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
