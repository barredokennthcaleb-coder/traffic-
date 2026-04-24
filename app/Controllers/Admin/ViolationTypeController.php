<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ViolationTypeModel;

class ViolationTypeController extends BaseController
{
    protected $violationTypeModel;

    public function __construct()
    {
        $this->violationTypeModel = new ViolationTypeModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Violation Types',
            'violationTypes' => $this->violationTypeModel->orderBy('violation_name', 'ASC')->findAll(),
        ];
        return view('admin/violation_types/index', $data);
    }

    public function store()
    {
        $data = [
            'violation_name' => trim($this->request->getPost('violation_name')),
            'description'    => trim($this->request->getPost('description')),
            'fine_amount'    => $this->request->getPost('fine_amount'),
            'points'         => $this->request->getPost('points') ?? 0,
            'status'         => $this->request->getPost('status') ?? 'active',
        ];

        if ($this->violationTypeModel->save($data)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Violation type added successfully.',
                    'data' => $this->violationTypeModel->find($this->violationTypeModel->insertID())
                ]);
            }
            return redirect()->to('/violation-types')->with('success', 'Violation type added successfully.');
        } else {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'errors' => $this->violationTypeModel->errors()
                ]);
            }
            return redirect()->back()->withInput()->with('errors', $this->violationTypeModel->errors());
        }
    }

    public function update($id = null)
    {
        $id = $this->request->getPost('id'); // Get ID from POST for AJAX compatibility
        $violationType = $this->violationTypeModel->find($id);
        
        if (!$violationType) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Violation type not found.']);
            }
            return redirect()->to('/violation-types')->with('error', 'Violation type not found.');
        }

        $data = [
            'id'             => $id,
            'violation_name' => trim($this->request->getPost('violation_name')),
            'description'    => trim($this->request->getPost('description')),
            'fine_amount'    => $this->request->getPost('fine_amount'),
            'points'         => $this->request->getPost('points') ?? 0,
            'status'         => $this->request->getPost('status') ?? 'active',
        ];

        if ($this->violationTypeModel->save($data)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Violation type updated successfully.',
                    'data' => $this->violationTypeModel->find($id)
                ]);
            }
            return redirect()->to('/violation-types')->with('success', 'Violation type updated successfully.');
        } else {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'errors' => $this->violationTypeModel->errors()
                ]);
            }
            return redirect()->back()->withInput()->with('errors', $this->violationTypeModel->errors());
        }
    }

    public function delete($id = null)
    {
        $violationType = $this->violationTypeModel->find($id);
        
        if (!$violationType) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Violation type not found.']);
            }
            return redirect()->to('/violation-types')->with('error', 'Violation type not found.');
        }

        if ($this->violationTypeModel->delete($id)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Violation type deleted successfully.']);
            }
            return redirect()->to('/violation-types')->with('success', 'Violation type deleted successfully.');
        } else {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete violation type.']);
            }
            return redirect()->to('/violation-types')->with('error', 'Failed to delete violation type.');
        }
    }
}
