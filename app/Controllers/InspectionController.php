<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\InspectionChecklist;
use App\Models\InspectionModel;

class InspectionController extends BaseController
{
    protected $inspectionModel;

    public function __construct()
    {
        $this->inspectionModel = new InspectionModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Inspections',
            'inspections' => $this->inspectionModel->orderBy('inspection_date', 'DESC')->paginate(10, 'inspections'),
            'pager' => $this->inspectionModel->pager,
        ];
        return view('inspection/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Create Inspection Report',
        ];
        return view('inspection/create', $data);
    }

    public function store()
    {
        $rules = [
            'mtop_no'       => 'required',
            'operator_name' => 'required',
            'inspection_date' => 'required|valid_date',
            'inspected_by'  => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = InspectionChecklist::normalize((array) $this->request->getPost());

        if ($this->inspectionModel->save($data)) {
            return redirect()->to(base_url('inspections'))->with('success', 'Inspection report saved successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to save inspection report.');
    }

    public function edit($id)
    {
        $inspection = $this->inspectionModel->find($id);
        if (!$inspection) {
            return redirect()->to(base_url('inspections'))->with('error', 'Inspection report not found.');
        }

        $data = [
            'title' => 'Edit Inspection Report',
            'inspection' => $inspection,
        ];
        return view('inspection/edit', $data);
    }

    public function update($id)
    {
        $inspection = $this->inspectionModel->find($id);
        if (! $inspection) {
            return redirect()->to(base_url('inspections'))->with('error', 'Inspection report not found.');
        }

        $rules = [
            'mtop_no'       => 'required',
            'operator_name' => 'required',
            'inspection_date' => 'required|valid_date',
            'inspected_by'  => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = InspectionChecklist::normalize((array) $this->request->getPost());
        $data['id'] = $id;

        if ($this->inspectionModel->save($data)) {
            return redirect()->to(base_url('inspections'))->with('success', 'Inspection report updated successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update inspection report.');
    }

    public function delete($id)
    {
        $inspection = $this->inspectionModel->find($id);
        if (! $inspection) {
            return redirect()->to(base_url('inspections'))->with('error', 'Inspection report not found.');
        }

        if ($this->inspectionModel->delete($id)) {
            return redirect()->to(base_url('inspections'))->with('success', 'Inspection report deleted successfully.');
        }
        return redirect()->to(base_url('inspections'))->with('error', 'Failed to delete inspection report.');
    }

    public function print($id)
    {
        $inspection = $this->inspectionModel->find($id);
        if (!$inspection) {
            return redirect()->to(base_url('inspections'))->with('error', 'Inspection report not found.');
        }

        $data = [
            'title' => 'Print Inspection Report',
            'inspection' => $inspection,
        ];
        return view('inspection/print', $data);
    }
}
