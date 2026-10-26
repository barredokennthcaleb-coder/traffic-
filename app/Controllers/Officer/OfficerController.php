<?php

namespace App\Controllers\Officer;

use App\Controllers\BaseController;
use App\Models\ViolatorModel;
use App\Models\ViolatorRecord;
use App\Models\ViolationTypeModel;
use App\Models\UserModel;

class OfficerController extends BaseController
{
    /** The duty states an officer can pick from the Field Status card. */
    private const DUTY_STATUSES = ['Active Patrol', 'On Break', 'Off Duty'];

    protected $violationRecord;
    protected $violationTypeModel;
    protected $userModel;
    protected $violatorModel;

    public function __construct()
    {
        $this->violationRecord = new ViolatorRecord();
        $this->violationTypeModel = new ViolationTypeModel();
        $this->userModel = new UserModel();
        $this->violatorModel = new ViolatorModel();
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

        // The enforcement record is tallied per period, so every new week,
        // month and year starts again from zero instead of carrying one
        // ever-growing total. 'all' keeps the lifetime view available.
        $period = strtolower((string) ($this->request->getGet('period') ?? 'month'));
        if (! in_array($period, ['week', 'month', 'year', 'all'], true)) {
            $period = 'month';
        }

        $today    = new \DateTimeImmutable('today');
        $start    = null;
        $end      = null;
        $dateNote = 'Every citation ever logged by this officer';

        switch ($period) {
            case 'week':
                $start    = $today->modify('monday this week');
                $end      = $start->modify('+6 days')->setTime(23, 59, 59);
                $dateNote = 'Week of ' . $start->format('M d') . ' – ' . $end->format('M d, Y');
                break;

            case 'year':
                $start    = $today->setDate((int) $today->format('Y'), 1, 1);
                $end      = $today->setDate((int) $today->format('Y'), 12, 31)->setTime(23, 59, 59);
                $dateNote = 'Jan 01 – Dec 31, ' . $today->format('Y');
                break;

            case 'all':
                break;

            case 'month':
            default:
                $start    = $today->modify('first day of this month');
                $end      = $today->modify('last day of this month')->setTime(23, 59, 59);
                $dateNote = $today->format('F Y') . ' (' . $start->format('M d') . ' – ' . $end->format('M d') . ')';
                break;
        }

        // Group by ticket_id only to get one row per unique ticket (not per ticket+status combo)
        $enforcementBuilder = $this->violationRecord
            ->select('ticket_id, status, SUM(penalty_amount) as penalty_amount')
            ->where('officer_id', $officerId)
            ->where('status !=', 'Cancelled');

        if ($start !== null && $end !== null) {
            $enforcementBuilder
                ->where('violation_date >=', $start->format('Y-m-d H:i:s'))
                ->where('violation_date <=', $end->format('Y-m-d H:i:s'));
        }

        $records = $enforcementBuilder
            ->groupBy('ticket_id')
            ->findAll();

        $totalAmount = array_sum(array_map(static function ($record) {
            return (float) ($record['penalty_amount'] ?? 0);
        }, $records));

        // Real-time enforcer duty and apprehension metrics
        $todayTickets = $this->violationRecord
            ->select('DISTINCT(ticket_id)')
            ->where('officer_id', $officerId)
            ->where('DATE(created_at)', date('Y-m-d'))
            ->findAll();
        $todayCount = count($todayTickets);

        $monthTickets = $this->violationRecord
            ->select('DISTINCT(ticket_id)')
            ->where('officer_id', $officerId)
            ->like('created_at', date('Y-m'), 'after')
            ->findAll();
        $thisMonthCount = count($monthTickets);

        // Top violation type apprehended by this officer
        // Every offense this officer has handled, most frequent first. The
        // Top Offense card shows the first row and the breakdown modal lists
        // the whole tally, so the two can never disagree.
        $offenseRows = $this->violationRecord
            ->select('violation_type, COUNT(*) as count')
            ->where('officer_id', $officerId)
            ->groupBy('violation_type')
            ->orderBy('count', 'DESC')
            ->findAll();

        $topOffenseRow = $offenseRows[0] ?? null;
        $offenseTotal  = 0;
        foreach ($offenseRows as $offenseRow) {
            $offenseTotal += (int) $offenseRow['count'];
        }

        $dutyStatus = trim((string) ($officer['duty_status'] ?? ''));
        if (! in_array($dutyStatus, self::DUTY_STATUSES, true)) {
            $dutyStatus = self::DUTY_STATUSES[0];
        }

        // Patrol areas worked by this officer, most frequent first
        $areaRows = $this->violationRecord
            ->select('location, COUNT(*) as count')
            ->where('officer_id', $officerId)
            ->where('location IS NOT NULL')
            ->where('location !=', '')
            ->groupBy('location')
            ->orderBy('count', 'DESC')
            ->findAll();

        $topLocationRow = $areaRows[0] ?? null;

        $data = [
            'title'               => 'Enforcer Profile & Operational Status',
            'officer'             => $officer,
            'records'             => $records,
            'pendingCount'        => count(array_filter($records, static fn($r) => ($r['status'] ?? '') === 'Pending')),
            'paidCount'           => count(array_filter($records, static fn($r) => ($r['status'] ?? '') === 'Paid')),
            'cancelledCount'      => count(array_filter($records, static fn($r) => ($r['status'] ?? '') === 'Cancelled')),
            'totalAmount'         => $totalAmount,
            'todayCount'          => $todayCount,
            'thisMonthCount'      => $thisMonthCount,
            'enforcementPeriod'   => $period,
            'enforcementDateNote' => $dateNote,
            'topOffense'          => $topOffenseRow['violation_type'] ?? 'None recorded',
            'topLocation'         => $topLocationRow['location'] ?? 'Kabankalan Proper',
            'badgeNumber'         => sprintf('CTRAMO-ENF-%03d', $officerId),
            'assignedStation'     => trim((string) ($officer['assigned_station'] ?? '')),
            'assignedArea'        => trim((string) ($officer['assigned_area'] ?? '')),
            'areas'               => $areaRows,
            'dutyStatus'          => $dutyStatus,
            'dutyStatuses'        => self::DUTY_STATUSES,
            'offenseBreakdown'    => $offenseRows,
            'offenseTotal'        => $offenseTotal,
        ];

        return view('officer/profile', $data);
    }

    /**
     * Save the duty status the officer picked in the Field Status modal.
     */
    public function updateDutyStatus()
    {
        $officerId = (int) session()->get('id');
        $officer   = $this->userModel->find($officerId);

        if (!$officer) {
            return redirect()->to(base_url('officer/profile'))->with('error', 'Officer profile not found.');
        }

        $status = trim((string) $this->request->getPost('duty_status'));

        if (! in_array($status, self::DUTY_STATUSES, true)) {
            return redirect()->back()->with('error', 'Please choose one of the available duty statuses.');
        }

        if ($this->userModel->update($officerId, ['duty_status' => $status])) {
            return redirect()->back()->with('success', 'Duty status updated to "' . $status . '".');
        }

        return redirect()->back()->with('error', 'Could not save the duty status. Please try again.');
    }

    /**
     * Save the area the officer picked in the jurisdiction modal as their
     * confirmed assigned area.
     */
    public function confirmAssignedArea()
    {
        $officerId = (int) session()->get('id');
        $officer   = $this->userModel->find($officerId);

        if (!$officer) {
            return redirect()->to(base_url('officer/profile'))->with('error', 'Officer profile not found.');
        }

        $area = trim((string) $this->request->getPost('assigned_area'));

        if ($area === '') {
            return redirect()->back()->with('error', 'Please select an area from the list first.');
        }

        if (mb_strlen($area) > 255) {
            return redirect()->back()->with('error', 'Assigned area is too long.');
        }

        if ($this->userModel->update($officerId, ['assigned_area' => $area])) {
            return redirect()->to(base_url('officer/profile'))->with('success', 'Assigned area confirmed: ' . $area);
        }

        return redirect()->back()->with('error', 'Failed to save the assigned area.');
    }

    public function updateProfilePhoto()
    {
        $isAjax = $this->request->isAJAX() || $this->request->hasHeader('X-Requested-With');
        $officerId = (int) session()->get('id');
        $officer = $this->userModel->find($officerId);

        if (!$officer) {
            if ($isAjax) {
                return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Officer profile not found.']);
            }
            return redirect()->to(base_url('officer/profile'))->with('error', 'Officer profile not found.');
        }

        $rules = [
            'profile_photo' => 'uploaded[profile_photo]|is_image[profile_photo]|mime_in[profile_photo,image/jpg,image/jpeg,image/png,image/webp]|max_size[profile_photo,10240]',
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            $msg = $errors['profile_photo'] ?? 'Invalid image upload. Please choose a valid JPG, PNG, or WEBP under 10MB.';
            if ($isAjax) {
                return $this->response->setStatusCode(422)->setJSON(['status' => 'error', 'message' => $msg]);
            }
            return redirect()->to(base_url('officer/profile'))->with('error', $msg);
        }

        $file = $this->request->getFile('profile_photo');
        if (!$file || !$file->isValid()) {
            if ($isAjax) {
                return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Failed to upload profile photo.']);
            }
            return redirect()->to(base_url('officer/profile'))->with('error', 'Failed to upload profile photo.');
        }

        $uploadPath = FCPATH . 'uploads/profile/';
        if (!is_dir($uploadPath) && !mkdir($uploadPath, 0755, true) && !is_dir($uploadPath)) {
            if ($isAjax) {
                return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Cannot create upload directory.']);
            }
            return redirect()->to(base_url('officer/profile'))->with('error', 'Cannot create upload directory.');
        }

        $extension = $file->getExtension() ?: 'jpg';
        $newName = 'officer_' . $officerId . '_' . time() . '.' . strtolower($extension);

        if (!$file->move($uploadPath, $newName, true)) {
            if ($isAjax) {
                return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Unable to save uploaded photo.']);
            }
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
            if ($isAjax) {
                return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Failed to update profile record in database.']);
            }
            return redirect()->to(base_url('officer/profile'))->with('error', 'Failed to update profile photo.');
        }

        // Sync session so topbar and layout update immediately
        session()->set('profile_image', $newName);
        $photoUrl = base_url('uploads/profile/' . $newName);

        if ($isAjax) {
            return $this->response->setJSON([
                'status'    => 'success',
                'message'   => 'Profile photo updated successfully.',
                'photo_url' => $photoUrl,
                'csrf_token'=> csrf_token(),
                'csrf_hash' => csrf_hash(),
            ]);
        }

        return redirect()->to(base_url('officer/profile'))->with('success', 'Profile photo updated successfully.');
    }

    public function removeProfilePhoto()
    {
        $isAjax = $this->request->isAJAX() || $this->request->hasHeader('X-Requested-With');
        $officerId = (int) session()->get('id');
        $officer = $this->userModel->find($officerId);

        if (!$officer) {
            if ($isAjax) {
                return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Officer profile not found.']);
            }
            return redirect()->to(base_url('officer/profile'))->with('error', 'Officer profile not found.');
        }

        $oldImage = trim((string) ($officer['profile_image'] ?? ''));
        if ($oldImage !== '') {
            $oldPath = FCPATH . 'uploads/profile/' . $oldImage;
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        $this->userModel->update($officerId, ['profile_image' => null]);
        session()->remove('profile_image');

        if ($isAjax) {
            return $this->response->setJSON([
                'status'    => 'success',
                'message'   => 'Profile photo removed.',
                'initials'  => strtoupper(substr((string) ($officer['username'] ?? 'O'), 0, 1)),
                'csrf_token'=> csrf_token(),
                'csrf_hash' => csrf_hash(),
            ]);
        }

        return redirect()->to(base_url('officer/profile'))->with('success', 'Profile photo removed successfully.');
    }

    public function updateProfile()
    {
        $officerId = (int) session()->get('id');
        $officer = $this->userModel->find($officerId);

        if (!$officer) {
            return redirect()->to(base_url('officer/profile'))->with('error', 'Officer record not found.');
        }

        $rules = [
            'firstname'      => 'required|min_length[2]|max_length[100]',
            'lastname'       => 'required|min_length[2]|max_length[100]',
            'middle_initial' => 'permit_empty|max_length[10]',
            'email'          => "required|valid_email|is_unique[users.email,id,{$officerId}]",
            'age'            => 'permit_empty|integer|greater_than_equal_to[18]|less_than_equal_to[120]',
            'address'        => 'permit_empty|max_length[255]',
            'assigned_station' => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->to(base_url('officer/profile'))->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'id'             => $officerId,
            'firstname'      => trim((string) $this->request->getPost('firstname')),
            'lastname'       => trim((string) $this->request->getPost('lastname')),
            'middle_initial' => trim((string) $this->request->getPost('middle_initial')),
            'email'          => trim((string) $this->request->getPost('email')),
            'age'            => $this->request->getPost('age') !== '' ? (int) $this->request->getPost('age') : null,
            'address'        => trim((string) $this->request->getPost('address')),
            'assigned_station' => trim((string) $this->request->getPost('assigned_station')),
        ];

        if ($this->userModel->update($officerId, $updateData)) {
            session()->set('email', $updateData['email']);
            session()->set('firstname', $updateData['firstname']);
            session()->set('lastname', $updateData['lastname']);
            session()->set('middle_initial', $updateData['middle_initial']);
            return redirect()->to(base_url('officer/profile'))->with('success', 'Credentials and personal details updated successfully.');
        }

        $modelErrors = $this->userModel->errors();
        return redirect()->to(base_url('officer/profile'))->withInput()->with('error', 'Failed to update credentials. Please try again.')->with('errors', $modelErrors);
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
            'violator_id'       => 'permit_empty|integer',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // One-time submission token: the same rendered form can never issue a
        // second ticket, even when the browser double-submits or re-posts.
        $submissionToken = trim((string) $this->request->getPost('submission_token'));
        $tokens          = $session->get('violation_submission_tokens');
        $tokens          = is_array($tokens) ? $tokens : [];
        $tokenConsumed   = false;

        if ($submissionToken === '' || ! in_array($submissionToken, $tokens, true)) {
            return redirect()->back()->withInput()->with('error', 'This citation form was already submitted. Open a new Record Violation form to issue another ticket.');
        }

        $rawViolationTypeIds = $this->request->getPost('violation_type_id');

        // Ensure it's an array and filter out empty values
        if (!is_array($rawViolationTypeIds)) {
            $rawViolationTypeIds = $rawViolationTypeIds ? [$rawViolationTypeIds] : [];
        }
        $violationTypeIds = array_values(array_filter(array_map('intval', $rawViolationTypeIds)));

        if (empty($violationTypeIds)) {
            return redirect()->back()->withInput()->with('error', 'Please select at least one violation type.');
        }

        if (count($violationTypeIds) !== count(array_unique($violationTypeIds))) {
            return redirect()->back()->withInput()->with('error', 'Duplicate infractions are not allowed. Please review the selected violations.');
        }

        $selectedViolationTypes = [];
        foreach ($violationTypeIds as $violationTypeId) {
            $type = $this->violationTypeModel->find($violationTypeId);
            if (!$type) {
                return redirect()->back()->withInput()->with('error', 'One or more selected violation types are invalid.');
            }
            $selectedViolationTypes[] = $type;
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

        $ticketYear = (int) date('Y', strtotime($resolvedViolationDate));
        $customTicketNo = trim((string) $this->request->getPost('custom_ticket_no'));

        if ($customTicketNo !== '') {
            $sharedTicketId = normalize_tct_number($customTicketNo, $ticketYear);
            if ($sharedTicketId === null) {
                return redirect()->back()->withInput()->with('error', 'Invalid TCT number. Use format YYYY-XXXXXXX (example: 2024-0030268).');
            }
            if ($this->violationRecord->ticketIdExists($sharedTicketId)) {
                return redirect()->back()->withInput()->with('error', 'TCT number already exists. Please use the next available number.');
            }
            $serial = (int) substr($sharedTicketId, strrpos($sharedTicketId, '-') + 1);
            $this->violationRecord->syncSequenceIfHigher($ticketYear, $serial);
        } else {
            $sharedTicketId = $this->violationRecord->generateNextTctNumber($ticketYear);
        }

        $driverSignature  = $this->request->getPost('driver_signature');
        $officerSignature = $this->request->getPost('officer_signature');

        // Consume the token before touching the database. Consuming it again
        // (or restoring it below on failure) is harmless.
        $tokens = array_values(array_filter($tokens, static fn($token) => $token !== $submissionToken));
        $session->set('violation_submission_tokens', $tokens);
        $tokenConsumed = true;

        $driverName = trim(preg_replace('/\s+/', ' ', $firstName . ' ' . $middleName . ' ' . $lastName));

        $ticketData = [
            'ticket_id'         => $sharedTicketId,
            'first_name'        => $firstName,
            'middle_name'       => $middleName,
            'last_name'         => $lastName,
            'age'               => (int) $this->request->getPost('age'),
            'address'           => trim((string) $this->request->getPost('address')),
            'driver_name'       => $driverName,
            'license_number'    => $licenseNumber,
            'license_plate'     => $licensePlate,
            'mtop_number'       => $mtopNumber,
            'owner_name'        => $ownerName,
            'owner_address'     => trim((string) $this->request->getPost('owner_address')),
            'acknowledgment'    => $acknowledgment,
            'officer_id'        => $session->get('id'),
            'violation_date'    => $resolvedViolationDate,
            'created_by'        => $session->get('id'),
            'location'          => trim($this->request->getPost('location') ?? ''),
            'notes'             => trim($this->request->getPost('notes') ?? ''),
            'driver_signature'  => $driverSignature ?: null,
            'officer_signature' => $officerSignature ?: null,
        ];

        $preferredViolatorId = (int) ($this->request->getPost('violator_id') ?? 0);
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $violatorId = $this->violatorModel->resolveForTicket(
                $ticketData,
                $preferredViolatorId > 0 ? $preferredViolatorId : null,
                $sharedTicketId
            );

            $firstInsertedId = null;

            foreach ($selectedViolationTypes as $violationType) {
                $data = array_merge($ticketData, [
                    'violator_id'       => $violatorId,
                    'violation_type_id' => (int) $violationType['id'],
                    'violation_type'    => $violationType['violation_name'],
                    'penalty_amount'    => (float) ($violationType['fine_amount'] ?? 0),
                    'points'            => (int) ($violationType['points'] ?? 0),
                    'status'            => 'Pending',
                ]);

                if (! $this->violationRecord->save($data)) {
                    throw new \RuntimeException('Unable to save the violation record: ' . implode(' ', $this->violationRecord->errors()));
                }

                if ($firstInsertedId === null) {
                    $firstInsertedId = $this->violationRecord->getInsertID();
                }
            }

            // All infractions on the ticket must point to the same violator.
            $this->violatorModel->syncTicket($sharedTicketId, $violatorId, $ticketData);

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Failed to record violations.');
            }

            $db->transCommit();

            return redirect()->to(base_url('officer/view/' . $firstInsertedId . '?print=1&from=violations'))
                ->with('success', 'Violations recorded successfully.');
        } catch (\Throwable $e) {
            $db->transRollback();

            if ($tokenConsumed) {
                $tokens = $session->get('violation_submission_tokens');
                $tokens = is_array($tokens) ? $tokens : [];
                $tokens[] = $submissionToken;
                $session->set('violation_submission_tokens', array_slice(array_values(array_unique($tokens)), -20));
            }

            log_message('error', 'Violation recording failed: {message}', ['message' => $e->getMessage()]);

            return redirect()->back()->withInput()
                ->with('errors', $this->violationRecord->errors() ?: ['Failed to record violations. Please try again.'])
                ->with('error', 'Failed to record violations. No ticket was issued.');
        }
    }

    public function violations()
    {
        $officerId = (int) session()->get('id');

        // The ledger lists people, not tickets: every outstanding citation this
        // officer issued collapses into one row for that violator, so a repeat
        // apprehension never shows up as a second row with the same name. The
        // violator's history page carries each citation as its own record.
        $scopedCount = function (array $extra = []) use ($officerId) {
            $builder = $this->violationRecord
                ->select('COUNT(DISTINCT violations.violator_id) as total', false)
                ->where('violations.officer_id', $officerId)
                ->where('violations.violator_id IS NOT NULL', null, false)
                ->whereNotIn('violations.status', ['Paid', 'Cancelled']);

            if ($extra !== []) {
                $builder->where($extra);
            }

            return (int) $builder->get()->getRow()->total;
        };

        $totalViolators   = $scopedCount();
        $pendingViolators = $scopedCount(['violations.status' => 'Pending']);

        $violations = $this->violationRecord
            ->select('violations.violator_id, violators.driver_name, violators.first_name, violators.middle_name, violators.last_name, violators.license_plate, violators.license_number, violators.address, COUNT(DISTINCT violations.ticket_id) as citation_count, COUNT(violations.id) as infraction_count, SUM(violations.penalty_amount) as total_penalty_sum, MAX(violations.violation_date) as last_violation_date')
            ->join('violators', 'violators.id = violations.violator_id', 'left')
            ->where('violations.officer_id', $officerId)
            ->where('violations.violator_id IS NOT NULL', null, false)
            ->whereNotIn('violations.status', ['Paid', 'Cancelled'])
            ->groupBy('violations.violator_id')
            ->orderBy('MAX(violations.violation_date)', 'DESC')
            ->paginate(10, 'violations');
        $pager = $this->violationRecord->pager;

        // "Edit Citation" opened from a violator's history page comes back as
        // ?edit_ticket=<violation id>: the ledger re-opens the edit modal for
        // that one ticket instead of for a row of this table.
        $ticketInfractions = [];
        $editTicket        = null;
        $editTicketId      = (int) ($this->request->getGet('edit_ticket') ?? 0);

        if ($editTicketId > 0) {
            $primary = $this->violationRecord->find($editTicketId);

            if ($primary && (int) ($primary['officer_id'] ?? 0) === $officerId && ($primary['status'] ?? '') === 'Pending') {
                $editTicket = $primary;

                $infractionRows = \Config\Database::connect()
                    ->table('violations')
                    ->select('id, ticket_id, violation_type_id, violation_type, penalty_amount, status')
                    ->where('ticket_id', $primary['ticket_id'])
                    ->where('officer_id', $officerId)
                    ->where('status', 'Pending')
                    ->orderBy('id', 'ASC')
                    ->get()
                    ->getResultArray();

                foreach ($infractionRows as $row) {
                    $ticketInfractions[$row['ticket_id']][] = $row;
                }
            }
        }

        // One-time token used by the record form to stop duplicate tickets.
        $submissionToken = bin2hex(random_bytes(16));
        $tokens          = session()->get('violation_submission_tokens');
        $tokens          = is_array($tokens) ? $tokens : [];
        $tokens[]        = $submissionToken;
        $tokens          = array_slice(array_values(array_unique($tokens)), -20);
        session()->set('violation_submission_tokens', $tokens);

        // Optional prefill when "Record Violation" is clicked from a violator.
        $prefillViolator = null;
        $prefillId       = (int) ($this->request->getGet('violator_id') ?? 0);
        if ($prefillId > 0) {
            $prefillViolator = $this->violatorModel->getForPrefill($prefillId);

            if ($prefillViolator && session()->get('role') === 'enforcer') {
                $hasTicket = \Config\Database::connect()
                    ->table('violations')
                    ->where('violator_id', $prefillId)
                    ->where('officer_id', $officerId)
                    ->countAllResults() > 0;

                if (! $hasTicket) {
                    $prefillViolator = null;
                }
            }
        }

        $data = [
            'title'          => 'Violation',
            'nextTctNumber'  => $this->violationRecord->peekNextTctNumber((int) date('Y')),
            'violationTypes' => $this->violationTypeModel->where('status', 'active')->findAll(),
            'violations'     => $violations,
            'ticketInfractions' => $ticketInfractions,
            'editTicket'     => $editTicket,
            'pager'          => $pager,
            'totalViolators'   => $totalViolators,
            'pendingViolators' => $pendingViolators,
            'submissionToken' => $submissionToken,
            'prefillViolator' => $prefillViolator,
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

        $rawViolationTypeIds = $this->request->getPost('violation_type_id');
        if (!is_array($rawViolationTypeIds)) {
            $rawViolationTypeIds = $rawViolationTypeIds ? [$rawViolationTypeIds] : [];
        }
        $violationTypeIds = array_values(array_filter(array_map('intval', $rawViolationTypeIds)));

        if (empty($violationTypeIds)) {
            return redirect()->back()->withInput()->with('error', 'Please select at least one violation type.');
        }

        if (count($violationTypeIds) !== count(array_unique($violationTypeIds))) {
            return redirect()->back()->withInput()->with('error', 'Duplicate infractions are not allowed. Please review the selected violations.');
        }

        $selectedViolationTypes = [];
        foreach ($violationTypeIds as $violationTypeId) {
            $type = $this->violationTypeModel->find($violationTypeId);
            if (!$type) {
                return redirect()->back()->withInput()->with('error', 'Invalid violation type selected.');
            }
            $selectedViolationTypes[] = $type;
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

        $ticketId = trim((string) ($violation['ticket_id'] ?? ''));

        // Every infraction under the same ticket is edited together so the
        // ticket total, points, and displayed breakdown stay in sync.
        $existingRows = [];
        if ($ticketId !== '') {
            $existingRows = $this->violationRecord
                ->where('ticket_id', $ticketId)
                ->where('officer_id', $officerId)
                ->orderBy('id', 'ASC')
                ->findAll();
        }
        if (empty($existingRows)) {
            $existingRows = [$violation];
        }

        foreach ($existingRows as $row) {
            if (($row['status'] ?? '') !== 'Pending') {
                return redirect()->back()->withInput()->with('error', 'Only pending violations can be updated.');
            }
        }

        $ticketData = [
            'ticket_id'        => $ticketId,
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
            'owner_address'    => trim((string) $this->request->getPost('owner_address')),
            'acknowledgment'   => $acknowledgment,
            'location'         => trim((string) ($this->request->getPost('location') ?? '')),
            'notes'            => trim((string) ($this->request->getPost('notes') ?? '')),
        ];

        $primaryRow = $existingRows[0];
        $violatorId = (int) ($primaryRow['violator_id'] ?? 0);
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            if ($violatorId <= 0) {
                $violatorId = $this->violatorModel->resolveForTicket($ticketData, null, $ticketId);
            }

            foreach ($selectedViolationTypes as $index => $type) {
                $rowData = array_merge($ticketData, [
                    'violator_id'       => $violatorId,
                    'violation_type_id' => (int) $type['id'],
                    'violation_type'    => (string) ($type['violation_name'] ?? ''),
                    'penalty_amount'    => (float) ($type['fine_amount'] ?? 0),
                    'points'            => (int) ($type['points'] ?? 0),
                ]);

                if (isset($existingRows[$index])) {
                    $rowData['id'] = (int) $existingRows[$index]['id'];
                } else {
                    $rowData['officer_id']        = $officerId;
                    $rowData['status']            = 'Pending';
                    $rowData['violation_date']    = $primaryRow['violation_date'] ?? date('Y-m-d H:i:s');
                    $rowData['created_by']        = $primaryRow['created_by'] ?? $officerId;
                    $rowData['driver_signature']  = $primaryRow['driver_signature'] ?? null;
                    $rowData['officer_signature'] = $primaryRow['officer_signature'] ?? null;
                }

                if (!$this->violationRecord->save($rowData)) {
                    throw new \RuntimeException('Unable to update the violation record: ' . implode(' ', $this->violationRecord->errors()));
                }
            }

            // Remove infractions that were deleted in the edit form.
            $idsToDelete = array_slice(array_column($existingRows, 'id'), count($selectedViolationTypes));
            if (!empty($idsToDelete)) {
                $this->violationRecord->whereIn('id', $idsToDelete)->delete();
            }

            $this->violatorModel->syncTicket($ticketId, $violatorId, $ticketData);

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Failed to update violations.');
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Violation update failed: {message}', ['message' => $e->getMessage()]);

            return redirect()->back()->withInput()
                ->with('errors', $this->violationRecord->errors() ?: ['Failed to update violations. Please try again.'])
                ->with('error', 'Failed to update violations.');
        }

        return redirect()->to(base_url('officer/violations'))->with('success', 'Violation updated successfully.');
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

        $ticketId   = $violation['ticket_id'] ?? null;
        $violatorId = (int) ($violation['violator_id'] ?? 0);
        $deleted    = false;
        
        if ($ticketId) {
            // Delete all violations sharing the same ticket_id
            $deleted = $this->violationRecord->where('ticket_id', $ticketId)->where('officer_id', $officerId)->delete();
        } else {
            $deleted = $this->violationRecord->delete($id);
        }

        if ($deleted) {
            $this->violatorModel->pruneIfOrphan($violatorId);

            return redirect()->to(base_url('officer/violations'))->with('success', 'Violation ticket deleted successfully.');
        }

        return redirect()->to(base_url('officer/violations'))->with('error', 'Failed to delete violation.');
    }

    public function nextTctNumber()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Bad request.']);
        }

        $year = (int) ($this->request->getGet('year') ?: date('Y'));

        return $this->response->setJSON([
            'success' => true,
            'number'  => $this->violationRecord->peekNextTctNumber($year),
        ]);
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

    /**
     * Violator dossier for enforcers and admins using the shared profile view.
     */
    public function violatorProfile($id)
    {
        $violatorId = (int) $id;
        $profile    = $this->violatorModel->getProfile($violatorId);

        if (! $profile) {
            return redirect()->to(base_url('officer/violations'))->with('error', 'Violator record not found.');
        }

        $role = (string) session()->get('role');

        if ($role === 'enforcer') {
            $hasTicket = \Config\Database::connect()
                ->table('violations')
                ->where('violator_id', $violatorId)
                ->where('officer_id', (int) session()->get('id'))
                ->countAllResults() > 0;

            if (! $hasTicket) {
                return redirect()->to(base_url('officer/violations'))->with('error', 'You do not have a recorded ticket for this violator.');
            }

            // One person, one history. Officers get the citation ledger for
            // this violator (outstanding citations only) instead of the
            // administrator's full profile.
            $history = $this->getViolatorHistoryData(null, $violatorId);

            return view('officer/violator_profile', [
                'title'          => 'Violator Record - ' . ($history['violator']['name'] ?: 'Record'),
                'violator'       => $history['violator'],
                'tickets'        => $history['tickets'],
                'violationTypes' => $this->violationTypeModel->where('status', 'active')->findAll(),
            ]);
        }

        $data = [
            'title'          => 'Violator Profile - ' . ($profile['violator']['driver_name'] ?: 'Record'),
            'violator'       => $profile['violator'],
            'summary'        => $profile['summary'],
            'tickets'        => $profile['tickets'],
            'vehicles'       => $profile['vehicles'],
            'backUrl'        => base_url('officer/violations' . ($role === 'admin' ? '?violator_id=' . $violatorId : '')),
            'backLabel'      => 'Back to Violations',
            'ticketViewBase' => base_url('officer/view/'),
            'recordUrl'      => base_url('officer/violations?violator_id=' . $violatorId),
            'isAdmin'        => false,
            'isEnforcer'     => true,
            'cancelBase'     => base_url('officer/cancel/'),
        ];

        return view('violators/profile', $data);
    }

    /**
     * Retrieve complete history, statistics, and citations for a violator.
     *
     * Pass $violatorId to load one registered person's ledger directly; the
     * identifier lookup below stays available for ticket-id / name deep links.
     */
    public function getViolatorHistoryData($identifier = null, int $violatorId = 0): array
    {
        $driverName = trim((string) ($this->request->getGet('driver') ?? ''));
        $plate      = trim((string) ($this->request->getGet('plate') ?? ''));
        $dl         = trim((string) ($this->request->getGet('dl') ?? ''));
        $ticketId   = trim((string) ($this->request->getGet('ticket_id') ?? ''));
        $recordId   = (int) ($this->request->getGet('id') ?? 0);

        if ($violatorId > 0) {
            $registered = $this->violatorModel->find($violatorId);

            if ($registered) {
                $driverName = trim((string) ($registered['driver_name'] ?? ''));
                $plate      = trim((string) ($registered['license_plate'] ?? ''));
                $dl         = trim((string) ($registered['license_number'] ?? ''));
                $ticketId   = '';
                $recordId   = 0;
            }
        }

        if ($identifier !== null && is_string($identifier)) {
            $cleanIdent = trim(urldecode($identifier));
            if ($cleanIdent !== '') {
                if (is_numeric($cleanIdent)) {
                    $recordId = (int) $cleanIdent;
                } elseif (stripos($cleanIdent, 'TKT-') === 0) {
                    $ticketId = $cleanIdent;
                } else {
                    $driverName = $cleanIdent;
                }
            }
        }

        $baseRecord = null;
        if ($recordId > 0) {
            $baseRecord = $this->violationRecord->find($recordId);
        } elseif (!empty($ticketId)) {
            $baseRecord = $this->violationRecord->where('ticket_id', $ticketId)->first();
        } elseif (!empty($driverName)) {
            $baseRecord = $this->violationRecord
                ->groupStart()
                    ->where('driver_name', $driverName)
                    ->orWhere('CONCAT(first_name, " ", last_name)', $driverName)
                ->groupEnd()
                ->first();
        }

        if ($baseRecord) {
            if (empty($driverName) || $driverName === '-') {
                $driverName = trim(($baseRecord['first_name'] ?? '') . ' ' . ($baseRecord['last_name'] ?? '')) ?: ($baseRecord['driver_name'] ?? '');
            }
            if (empty($plate) || in_array(strtolower($plate), ['no', 'none', 'n/a', '-'])) {
                $plate = $baseRecord['license_plate'] ?? '';
            }
            if (empty($dl) || in_array(strtolower($dl), ['no', 'none', 'n/a', '-'])) {
                $dl = $baseRecord['license_number'] ?? '';
            }
        }

        $builder = $this->violationRecord
            ->select('violations.*, users.username as officer_name, users.firstname as officer_fname, users.lastname as officer_lname')
            ->join('users', 'users.id = violations.officer_id', 'left');

        $builder->groupStart();
        $hasFilter = false;

        if (!empty($dl) && !in_array(strtolower(trim($dl)), ['no', 'none', 'n/a', '-'])) {
            $builder->where('violations.license_number', $dl);
            $hasFilter = true;
        }

        if (!empty($driverName) && $driverName !== '-') {
            if ($hasFilter) {
                $builder->orWhere('violations.driver_name', $driverName);
            } else {
                $builder->where('violations.driver_name', $driverName);
                $hasFilter = true;
            }
            $builder->orWhere('CONCAT(violations.first_name, " ", violations.last_name)', $driverName);
        }

        if (!empty($plate) && !in_array(strtolower(trim($plate)), ['no', 'none', 'n/a', '-'])) {
            if ($hasFilter) {
                $builder->orWhere('violations.license_plate', $plate);
            } else {
                $builder->where('violations.license_plate', $plate);
                $hasFilter = true;
            }
        }

        if (!$hasFilter && $recordId > 0) {
            $builder->where('violations.id', $recordId);
            $hasFilter = true;
        }

        if (!$hasFilter) {
            $builder->where('1', '0');
        }

        $builder->groupEnd();

        if ($violatorId > 0) {
            // One person, one history: every citation linked to this violator
            // record is listed, no matter which officer issued it.
            $builder->where('violations.violator_id', $violatorId);
        }

        // Settled tickets live in the administrator's Payment History only, so
        // the officer-facing records (modal and profile page) show outstanding
        // citations exclusively.
        $builder->whereNotIn('violations.status', ['Paid', 'Cancelled']);
        $builder->orderBy('violations.violation_date', 'DESC');
        $rawRecords = $builder->findAll();

        // Attempts count every apprehension on record, settled ones included,
        // because a paid citation is still a prior offense.
        $attempts = $totalTicketsCountFallback = 0;
        if ($violatorId > 0) {
            $attempts = (int) \Config\Database::connect()
                ->table('violations')
                ->select('COUNT(DISTINCT ticket_id) as attempts', false)
                ->where('violator_id', $violatorId)
                ->get()->getRow()->attempts;
        }

        $tickets = [];
        $totalFines = 0.0;
        $pendingFines = 0.0;
        $paidFines = 0.0;
        $pendingTickets = 0;
        $paidTickets = 0;

        $violator = [
            'id'             => $violatorId,
            'name'           => !empty($driverName) ? $driverName : 'Unknown Violator',
            'first_name'     => $baseRecord['first_name'] ?? '',
            'middle_name'    => $baseRecord['middle_name'] ?? '',
            'last_name'      => $baseRecord['last_name'] ?? '',
            'address'        => $baseRecord['address'] ?? 'Kabankalan City',
            'age'            => $baseRecord['age'] ?? 'N/A',
            'license_plate'  => $plate ?: 'N/A',
            'license_number' => $dl ?: 'N/A',
            'owner_name'     => $baseRecord['owner_name'] ?? '',
        ];

        foreach ($rawRecords as $r) {
            if (!empty($r['first_name']) && empty($violator['first_name'])) $violator['first_name'] = $r['first_name'];
            if (!empty($r['middle_name']) && empty($violator['middle_name'])) $violator['middle_name'] = $r['middle_name'];
            if (!empty($r['last_name']) && empty($violator['last_name'])) $violator['last_name'] = $r['last_name'];
            if (!empty($r['address']) && ($violator['address'] === 'Kabankalan City' || empty($violator['address']))) $violator['address'] = $r['address'];
            if (!empty($r['age']) && ($violator['age'] === 'N/A' || empty($violator['age']))) $violator['age'] = $r['age'];
            if (!empty($r['owner_name']) && empty($violator['owner_name'])) $violator['owner_name'] = $r['owner_name'];
            if (!empty($r['license_plate']) && !in_array(strtolower(trim($r['license_plate'])), ['no', 'none', 'n/a', '-'])) {
                if ($violator['license_plate'] === 'N/A' || empty($violator['license_plate'])) $violator['license_plate'] = $r['license_plate'];
            }
            if (!empty($r['license_number']) && !in_array(strtolower(trim($r['license_number'])), ['no', 'none', 'n/a', '-'])) {
                if ($violator['license_number'] === 'N/A' || empty($violator['license_number'])) $violator['license_number'] = $r['license_number'];
            }

            $tId = !empty($r['ticket_id']) ? $r['ticket_id'] : ('ID-' . $r['id']);
            if (!isset($tickets[$tId])) {
                $officerFullName = trim(($r['officer_fname'] ?? '') . ' ' . ($r['officer_lname'] ?? ''));
                $officerDisplay  = !empty($officerFullName) ? $officerFullName : ($r['officer_name'] ?? 'Apprehending Officer');

                $tickets[$tId] = [
                    'id'             => $r['id'],
                    'ticket_id'      => $r['ticket_id'] ?: 'N/A',
                    'status'         => $r['status'] ?: 'Pending',
                    'violation_date' => $r['violation_date'],
                    'created_at'     => $r['created_at'] ?? $r['violation_date'],
                    'location'       => !empty($r['location']) ? $r['location'] : 'Kabankalan City',
                    'officer_name'   => $officerDisplay,
                    'license_plate'  => $r['license_plate'] ?: 'N/A',
                    'license_number' => $r['license_number'] ?: 'N/A',
                    'receipt_number' => $r['receipt_number'] ?? null,
                    'paid_date'      => $r['paid_date'] ?? null,
                    'violations'     => [],
                    'total_amount'   => 0.0,
                ];

                if (strtolower($r['status'] ?? '') === 'paid') {
                    $paidTickets++;
                } else if (strtolower($r['status'] ?? '') === 'pending') {
                    $pendingTickets++;
                }
            }

            $amt = (float)($r['penalty_amount'] ?? 0);
            $tickets[$tId]['violations'][] = [
                'type'   => $r['violation_type'] ?: 'Traffic Infraction',
                'amount' => $amt,
            ];
            $tickets[$tId]['total_amount'] += $amt;

            $totalFines += $amt;
            if (strtolower($r['status'] ?? '') === 'paid') {
                $paidFines += $amt;
            } else if (strtolower($r['status'] ?? '') === 'pending') {
                $pendingFines += $amt;
            }
        }

        $ticketList = array_values($tickets);
        usort($ticketList, static function($a, $b) {
            return strtotime($b['violation_date'] ?? '0') - strtotime($a['violation_date'] ?? '0');
        });

        $calculatedName = trim(($violator['first_name'] ?? '') . ' ' . ($violator['middle_name'] ?? '') . ' ' . ($violator['last_name'] ?? ''));
        if (!empty($calculatedName)) {
            $violator['name'] = $calculatedName;
        }

        $totalTicketsCount = count($ticketList);
        $attempts          = $attempts > 0 ? $attempts : $totalTicketsCount;
        $offenseDegree = match($attempts) {
            0 => 'No Prior Violations',
            1 => '1st Offense',
            2 => '2nd Offense',
            3 => '3rd Offense',
            default => $attempts . 'th Habitual Offense',
        };

        $violator['attempts']           = $attempts;
        $violator['offense_degree']     = $offenseDegree;
        $violator['total_tickets']      = $totalTicketsCount;
        $violator['total_infractions']  = count($rawRecords);
        $violator['total_fines']        = $totalFines;
        $violator['pending_fines']      = $pendingFines;
        $violator['paid_fines']         = $paidFines;
        $violator['pending_tickets']    = $pendingTickets;
        $violator['paid_tickets']       = $paidTickets;

        return [
            'violator' => $violator,
            'tickets'  => $ticketList,
            'total'    => $totalTicketsCount,
        ];
    }

    /**
     * JSON API Endpoint for instant violator records retrieval.
     */
    public function violatorRecordsApi()
    {
        $data = $this->getViolatorHistoryData();
        return $this->response->setJSON([
            'success'  => true,
            'violator' => $data['violator'],
            'tickets'  => $data['tickets'],
            'total'    => $data['total'],
        ]);
    }

    /**
     * Dedicated Profile & History Page for a Violator.
     */
    public function violatorRecord($driver = null)
    {
        $data = $this->getViolatorHistoryData($driver);
        $data['title'] = 'Violator Profile: ' . ($data['violator']['name'] ?? 'Records');
        return view('officer/violator_profile', $data);
    }
}
