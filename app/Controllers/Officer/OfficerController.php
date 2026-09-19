<?php

namespace App\Controllers\Officer;

use App\Controllers\BaseController;
use App\Models\ViolatorRecord;
use App\Models\ViolationTypeModel;
use App\Models\UserModel;

class OfficerController extends BaseController
{
    protected $violationRecord;
    protected $violationTypeModel;
    protected $userModel;

    public function __construct()
    {
        $this->violationRecord = new ViolatorRecord();
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
            ->select('ticket_id, status, SUM(penalty_amount) as penalty_amount')
            ->where('officer_id', $officerId)
            ->where('status !=', 'Cancelled')
            ->groupBy('ticket_id, status')
            ->findAll();

        $totalAmount = array_sum(array_map(static function ($record) {
            return (float) ($record['penalty_amount'] ?? 0);
        }, $records));

        $year = $this->request->getGet('year') ?? date('Y');

        $data = [
            'title'          => 'My Profile',
            'officer'        => $officer,
            'records'        => $records,
            'pendingCount'   => count(array_filter($records, static fn($r) => ($r['status'] ?? '') === 'Pending')),
            'paidCount'      => count(array_filter($records, static fn($r) => ($r['status'] ?? '') === 'Paid')),
            'cancelledCount' => count(array_filter($records, static fn($r) => ($r['status'] ?? '') === 'Cancelled')),
            'totalAmount'    => $totalAmount,
            'year'           => $year,
            // New Analytics Data
            'weekly_trend'       => $this->violationRecord->getWeeklyTrend(12, $officerId),
            'monthly_breakdown'  => $this->violationRecord->getMonthlyBreakdown($year, $officerId),
            'nature_summary'     => $this->violationRecord->getViolationNatureSummary($officerId),
        ];

        return view('officer/profile', $data);
    }

    public function updateProfilePhoto()
    {
        $officerId = (int) session()->get('id');
        $officer = $this->userModel->find($officerId);

        if (!$officer) {
            return redirect()->to(base_url('officer/profile'))->with('error', 'Officer profile not found.');
        }

        $rules = [
            'profile_photo' => 'uploaded[profile_photo]|is_image[profile_photo]|mime_in[profile_photo,image/jpg,image/jpeg,image/png,image/webp]|max_size[profile_photo,2048]',
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            return redirect()->to(base_url('officer/profile'))->with('error', $errors['profile_photo'] ?? 'Invalid image upload.');
        }

        $file = $this->request->getFile('profile_photo');
        if (!$file || !$file->isValid()) {
            return redirect()->to(base_url('officer/profile'))->with('error', 'Failed to upload profile photo.');
        }

        $uploadPath = FCPATH . 'uploads/profile/';
        if (!is_dir($uploadPath) && !mkdir($uploadPath, 0755, true) && !is_dir($uploadPath)) {
            return redirect()->to(base_url('officer/profile'))->with('error', 'Cannot create upload directory.');
        }

        $extension = $file->getExtension() ?: 'jpg';
        $newName = 'officer_' . $officerId . '_' . time() . '.' . strtolower($extension);

        if (!$file->move($uploadPath, $newName, true)) {
            return redirect()->to(base_url('officer/profile'))->with('error', 'Unable to save uploaded photo.');
        }

        $oldImage = trim((string) ($officer['profile_image'] ?? ''));
        if ($oldImage !== '') {
            $oldPath = $uploadPath . $oldImage;
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        if (!$this->userModel->update($officerId, ['profile_image' => $newName])) {
            $newPath = $uploadPath . $newName;
            if (is_file($newPath)) {
                @unlink($newPath);
            }
            return redirect()->to(base_url('officer/profile'))->with('error', 'Failed to update profile photo.');
        }

        return redirect()->to(base_url('officer/profile'))->with('success', 'Profile photo updated successfully.');
    }

    public function store()
    {
        $session = session();

        $rules = [
            'first_name'        => 'required|max_length[100]',
            'last_name'         => 'required|max_length[100]',
            'age'               => 'required|integer|greater_than_equal_to[16]|less_than_equal_to[120]',
            'address'           => 'required|max_length[255]',
            'license_plate'     => 'required|max_length[20]',
            'violation_type_id' => 'required',
            'location'          => 'permit_empty|max_length[255]',
            'notes'             => 'permit_empty|max_length[500]',
            'violation_datetime'=> 'permit_empty|valid_date[Y-m-d\TH:i]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $session = session();
        $violationTypeIds = $this->request->getPost('violation_type_id');
        
        // Ensure it's an array and filter out empty values
        if (!is_array($violationTypeIds)) {
            $violationTypeIds = $violationTypeIds ? [$violationTypeIds] : [];
        }
        $violationTypeIds = array_filter($violationTypeIds);

        if (empty($violationTypeIds)) {
            return redirect()->back()->withInput()->with('error', 'Please select at least one violation type.');
        }

        $firstName = trim((string) $this->request->getPost('first_name'));
        $middleName = trim((string) $this->request->getPost('middle_name'));
        $lastName = trim((string) $this->request->getPost('last_name'));
        $licensePlate = strtoupper(trim((string) $this->request->getPost('license_plate')));
        $licenseNumber = trim((string) $this->request->getPost('license_number'));
        $mtopNumber = trim((string) $this->request->getPost('mtop_number'));
        $ownerName = trim((string) $this->request->getPost('owner_name'));
        $acknowledgment = trim((string) $this->request->getPost('acknowledgment'));
        
        $inputViolationDateTime = (string) ($this->request->getPost('violation_datetime') ?? '');
        $resolvedViolationDate = date('Y-m-d H:i:s');
        if ($inputViolationDateTime !== '') {
            $parsed = strtotime($inputViolationDateTime);
            if ($parsed !== false) {
                $resolvedViolationDate = date('Y-m-d H:i:s', $parsed);
            }
        }

        // Custom TCT Booklet Number or Auto-generated Ticket ID
        $customTicketNo = trim((string) $this->request->getPost('custom_ticket_no'));
        if (!empty($customTicketNo)) {
            $sharedTicketId = strtoupper($customTicketNo);
        } else {
            $sharedTicketId = 'TKT-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        }

        $driverSignature = $this->request->getPost('driver_signature');
        $officerSignature = $this->request->getPost('officer_signature');

        $db = \Config\Database::connect();
        if (!$db->fieldExists('driver_signature', 'violations')) {
            $db->query("ALTER TABLE violations ADD COLUMN driver_signature LONGTEXT NULL");
        }
        if (!$db->fieldExists('officer_signature', 'violations')) {
            $db->query("ALTER TABLE violations ADD COLUMN officer_signature LONGTEXT NULL");
        }

        $firstInsertedId = null;
        $db->transStart();

        foreach ($violationTypeIds as $violationTypeId) {
            $violationType = $this->violationTypeModel->find((int) $violationTypeId);
            if (!$violationType) continue;

            $data = [
                'ticket_id'        => $sharedTicketId,
                'first_name'       => $firstName,
                'middle_name'      => $middleName,
                'last_name'        => $lastName,
                'age'              => (int) $this->request->getPost('age'),
                'address'          => trim((string) $this->request->getPost('address')),
                'driver_name'      => trim($firstName . ' ' . $middleName . ' ' . $lastName),
                'license_number'   => $licenseNumber,
                'license_plate'    => $licensePlate,
                'mtop_number'      => $mtopNumber,
                'owner_name'       => $ownerName,
                'acknowledgment'   => $acknowledgment,
                'officer_id'       => $session->get('id'),
                'violation_type_id' => (int) $violationTypeId,
                'violation_type'   => $violationType['violation_name'],
                'penalty_amount'   => (float) ($violationType['fine_amount'] ?? 0),
                'points'           => (int) ($violationType['points'] ?? 0),
                'status'           => 'Pending',
                'violation_date'   => $resolvedViolationDate,
                'created_by'       => $session->get('id'),
                'location'         => trim($this->request->getPost('location') ?? ''),
                'notes'            => trim($this->request->getPost('notes') ?? ''),
                'driver_signature'  => $driverSignature ?: null,
                'officer_signature' => $officerSignature ?: null,
            ];

            if ($this->violationRecord->save($data)) {
                if ($firstInsertedId === null) {
                    $firstInsertedId = $this->violationRecord->getInsertID();
                }
            } else {
                $db->transRollback();
                return redirect()->back()->withInput()->with('errors', $this->violationRecord->errors());
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to record violations.');
        }

        return redirect()->to(base_url('officer/view/' . $firstInsertedId . '?print=1&from=violations'))
            ->with('success', 'Violations recorded successfully.');
    }

    public function violations()
    {
        $officerId = (int) session()->get('id');

        $data = [
            'title' => 'Violation',
            'violationTypes' => $this->violationTypeModel->where('status', 'active')->findAll(),
            'violations' => $this->violationRecord
                ->select('violations.*, GROUP_CONCAT(CONCAT(violation_type, "::", penalty_amount) ORDER BY violation_date DESC SEPARATOR "||") as concatenated_violations, SUM(penalty_amount) as total_penalty_sum, SUM(points) as total_points_sum, MAX(violation_date) as max_violation_date')
                ->where('officer_id', $officerId)
                ->where('status !=', 'Cancelled')
                ->groupBy('ticket_id')
                ->orderBy('MAX(violation_date)', 'DESC')
                ->paginate(10, 'violations'),
            'pager' => $this->violationRecord->pager,
        ];
        return view('officer/index', $data);
    }

    public function view($id)
    {
        $primaryViolation = $this->violationRecord->getDetailedViolation($id);

        if (!$primaryViolation) {
            return redirect()->to(base_url('officer/violations'))->with('error', 'Violation not found.');
        }

        // Fetch all violations sharing the same ticket ID
        $allViolations = $this->violationRecord->select('violations.*, users.username as officer_name')
            ->join('users', 'users.id = violations.officer_id', 'left')
            ->where('violations.ticket_id', $primaryViolation['ticket_id'])
            ->findAll();

        $data = [
            'title' => 'Violation Details',
            'violation' => $primaryViolation,
            'all_violations' => $allViolations
        ];
        return view('officer/view', $data);
    }

    public function cancel($id)
    {
        $officerId = (int) session()->get('id');
        $violation = $this->violationRecord->find($id);

        if (!$violation || (int) ($violation['officer_id'] ?? 0) !== $officerId) {
            return redirect()->to(base_url('officer/violations'))->with('error', 'Violation record not found.');
        }

        if ($violation['status'] !== 'Pending') {
            return redirect()->to(base_url('officer/violations'))->with('error', 'Only pending violations can be cancelled.');
        }

        $reason = $this->request->getPost('reason') ?? 'Cancelled by officer';
        $ticketId = $violation['ticket_id'] ?? null;

        // Cancel all violations sharing the same ticket_id
        if ($ticketId) {
            $siblingViolations = $this->violationRecord
                ->where('ticket_id', $ticketId)
                ->where('officer_id', $officerId)
                ->where('status', 'Pending')
                ->findAll();

            $allSuccess = true;
            foreach ($siblingViolations as $sibling) {
                if (!$this->violationRecord->cancelViolation($sibling['id'], $reason)) {
                    $allSuccess = false;
                }
            }

            if ($allSuccess) {
                $count = count($siblingViolations);
                $msg = $count > 1
                    ? "All {$count} violations under ticket {$ticketId} cancelled successfully."
                    : 'Violation cancelled successfully.';
                return redirect()->to(base_url('officer/violations'))->with('success', $msg);
            } else {
                return redirect()->back()->with('error', 'Failed to cancel some violations.');
            }
        }

        // Fallback: cancel single violation if no ticket_id
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
        $middleName = trim((string) $this->request->getPost('middle_name'));
        $lastName = trim((string) $this->request->getPost('last_name'));
        
        $licenseNumber = trim((string) $this->request->getPost('license_number'));
        $mtopNumber = trim((string) $this->request->getPost('mtop_number'));
        $ownerName = trim((string) $this->request->getPost('owner_name'));
        $acknowledgment = trim((string) $this->request->getPost('acknowledgment'));

        $rules = [
            'first_name'    => 'required|max_length[100]',
            'last_name'     => 'required|max_length[100]',
            'age'           => 'required|integer|greater_than_equal_to[16]|less_than_equal_to[120]',
            'address'       => 'required|max_length[255]',
            'license_plate' => 'required|max_length[20]',
            'location'      => 'permit_empty|max_length[255]',
            'notes'         => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id'               => $id,
            'first_name'       => $firstName,
            'middle_name'      => $middleName,
            'last_name'        => $lastName,
            'age'              => (int) $this->request->getPost('age'),
            'address'          => trim((string) $this->request->getPost('address')),
            'driver_name'      => trim($firstName . ' ' . $middleName . ' ' . $lastName),
            'license_number'   => $licenseNumber,
            'license_plate'    => strtoupper(trim((string) $this->request->getPost('license_plate'))),
            'mtop_number'      => $mtopNumber,
            'owner_name'       => $ownerName,
            'acknowledgment'   => $acknowledgment,
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

        // Removed status check to allow deleting Paid/history violations

        $ticketId = $violation['ticket_id'] ?? null;
        
        if ($ticketId) {
            // Delete all violations sharing the same ticket_id
            $this->violationRecord->where('ticket_id', $ticketId)->where('officer_id', $officerId)->delete();
            return redirect()->to(base_url('officer/violations'))->with('success', 'Violation ticket deleted successfully.');
        } else {
            if ($this->violationRecord->delete($id)) {
                return redirect()->to(base_url('officer/violations'))->with('success', 'Violation deleted successfully.');
            }
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
