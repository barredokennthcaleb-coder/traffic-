<?php

namespace App\Controllers;

use App\Controllers\BaseController;
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

        $data = $this->request->getPost();
        
        // Handle checkboxes
        $checkboxes = [
            'cert_attendance', 'outside_route', 'city_proper', 'trash_can',
            'mtop_8x16_route', 'mtop_8x8_route', 'mtop_dashboard',
            'signal_light_left', 'signal_light_right', 'head_light',
            'stop_light', 'side_mirror', 'horn', 'standard_muffler',
            'color_coding', 'tarifa'
        ];

        foreach ($checkboxes as $cb) {
            $data[$cb] = isset($data[$cb]) ? 1 : 0;
        }

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
        $rules = [
            'mtop_no'       => 'required',
            'operator_name' => 'required',
            'inspection_date' => 'required|valid_date',
            'inspected_by'  => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        $data['id'] = $id;

        // Handle checkboxes
        $checkboxes = [
            'cert_attendance', 'outside_route', 'city_proper', 'trash_can',
            'mtop_8x16_route', 'mtop_8x8_route', 'mtop_dashboard',
            'signal_light_left', 'signal_light_right', 'head_light',
            'stop_light', 'side_mirror', 'horn', 'standard_muffler',
            'color_coding', 'tarifa'
        ];

        foreach ($checkboxes as $cb) {
            $data[$cb] = isset($data[$cb]) ? 1 : 0;
        }

        if ($this->inspectionModel->save($data)) {
            return redirect()->to(base_url('inspections'))->with('success', 'Inspection report updated successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update inspection report.');
    }

    public function delete($id)
    {
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
