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
        // ever-growing total.
        $window      = $this->enforcementWindow();
        $period      = $window['period'];
        $dateNote    = $window['dateNote'];
        $customStart = $window['customStart'];
        $customEnd   = $window['customEnd'];

        $records     = $this->enforcementRecords($officerId, $window['start'], $window['end']);
        $activity    = ViolatorRecord::summarizeTicketActivity($records);
        $totalAmount = $activity['amount'];

        // Real-time enforcer duty and apprehension metrics. Cancelled (void)
        // citations are not apprehensions, so a ticket whose offences are all
        // cancelled is excluded here exactly as it is from the enforcement
        // record card, and legacy rows follow the reports' attribution:
        // officer_id first, then the account that created the citation.
        $todayTickets = $this->violationRecord
            ->select('DISTINCT(ticket_id)')
            ->where('COALESCE(violations.officer_id, violations.created_by)', $officerId)
            ->where('status !=', 'Cancelled')
            ->where('DATE(created_at)', date('Y-m-d'))
            ->findAll();
        $todayCount = count($todayTickets);

        $monthTickets = $this->violationRecord
            ->select('DISTINCT(ticket_id)')
            ->where('COALESCE(violations.officer_id, violations.created_by)', $officerId)
            ->where('status !=', 'Cancelled')
            ->like('created_at', date('Y-m'), 'after')
            ->findAll();
        $thisMonthCount = count($monthTickets);

        // Top violation type apprehended by this officer
        // Every live offense this officer has handled, most frequent first.
        // The Top Offense card shows the first row and the breakdown modal
        // lists the whole tally, so the two can never disagree, and voided
        // (cancelled) offenses never enter the tally.
        $offenseRows = $this->violationRecord
            ->select('violation_type, COUNT(*) as count')
            ->where('COALESCE(violations.officer_id, violations.created_by)', $officerId)
            ->where('status !=', 'Cancelled')
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

        // Patrol areas worked by this officer, most frequent first. Voided
        // (cancelled) offenses never enter the tally, and attribution follows
        // the same officer_id-then-created_by rule as the reports.
        $areaRows = $this->violationRecord
            ->select('location, COUNT(*) as count')
            ->where('COALESCE(violations.officer_id, violations.created_by)', $officerId)
            ->where('status !=', 'Cancelled')
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
            // One ticket is one citation, cancelled tickets are void: issued,
            // settled and pending counts all come from the same tally the
            // reports use.
            'totalTickets'        => $activity['issued'],
            'pendingCount'        => $activity['pending'],
            'paidCount'           => $activity['paid'],
            'cancelledCount'      => count(array_filter($records, static fn($r) => ($r['status'] ?? '') === 'Cancelled')),
            'collectionPct'       => $activity['issued'] > 0 ? (int) round(($activity['paid'] / $activity['issued']) * 100) : 0,
            'totalAmount'         => $totalAmount,
            'todayCount'          => $todayCount,
            'thisMonthCount'      => $thisMonthCount,
            'enforcementPeriod'   => $period,
            'enforcementDateNote' => $dateNote,
            'enforcementStart'    => $customStart,
            'enforcementEnd'      => $customEnd,
            'topOffense'          => $topOffenseRow['violation_type'] ?? 'None recorded',
            'topLocation'         => $topLocationRow['location'] ?? 'Kabankalan Proper',
            'badgeNumber'         => sprintf('CTRAMO-ENF-%03d', $officerId),
            'assignedStation'     => trim((string) ($officer['assigned_station'] ?? '')),
            'assignedArea'        => trim((string) ($officer['assigned_area'] ?? '')),
            'areas'               => $areaRows,
            'dutyStatus'          => $dutyStatus,
            'dutyStatuses'        => self::DUTY_STATUSES,
            'breakStartedAt'      => $officer['break_started_at'] ?? null,
            'offenseBreakdown'    => $offenseRows,
            'offenseTotal'        => $offenseTotal,
        ];

        return view('officer/profile', $data);
    }

    /**
     * Resolves the window the Citation Enforcement Record covers for the period
     * asked for in the query string.
     */
    private function enforcementWindow(): array
    {
        $period = strtolower((string) ($this->request->getGet('period') ?? 'month'));
        if (! in_array($period, ['week', 'month', 'year', 'custom'], true)) {
            $period = 'month';
        }

        $today       = new \DateTimeImmutable('today');
        $start       = null;
        $end         = null;
        $dateNote    = 'This month';
        $customStart = '';
        $customEnd   = '';

        switch ($period) {
            case 'custom':
                // Any day, or any week or month, picked by hand: both dates are
                // inclusive and the card counts only what falls inside them.
                $fromDate = $this->parseIsoDate((string) $this->request->getGet('start_date'));
                $toDate   = $this->parseIsoDate((string) $this->request->getGet('end_date'));

                if ($fromDate === null || $toDate === null) {
                    // An unusable range falls back to the current month rather
                    // than silently counting every citation ever recorded.
                    $start    = $today->modify('first day of this month');
                    $end      = $today->modify('last day of this month')->setTime(23, 59, 59);
                    $dateNote = $today->format('F Y') . ' (' . $start->format('M d') . ' – ' . $end->format('M d') . ')';
                    break;
                }

                if ($toDate < $fromDate) {
                    [$fromDate, $toDate] = [$toDate, $fromDate];
                }

                $customStart = $fromDate->format('Y-m-d');
                $customEnd   = $toDate->format('Y-m-d');
                $start       = $fromDate;
                $end         = $toDate->setTime(23, 59, 59);
                $dateNote    = $customStart === $customEnd
                    ? $fromDate->format('M d, Y')
                    : $fromDate->format('M d, Y') . ' – ' . $toDate->format('M d, Y');
                break;

            case 'week':
                // The enforcement week is the working week, Monday to Friday, so
                // the current week of October reads Oct 26 – Oct 30 rather than
                // a Mon–Sun span that borrows Nov 01 from the next month.
                $start    = $today->modify('monday this week');
                $end      = $start->modify('+4 days')->setTime(23, 59, 59);
                $dateNote = 'Week of ' . $start->format('M d') . ' – ' . $end->format('M d, Y');
                break;

            case 'year':
                $start    = $today->setDate((int) $today->format('Y'), 1, 1);
                $end      = $today->setDate((int) $today->format('Y'), 12, 31)->setTime(23, 59, 59);
                $dateNote = 'Jan 01 – Dec 31, ' . $today->format('Y');
                break;

            case 'month':
            default:
                $start    = $today->modify('first day of this month');
                $end      = $today->modify('last day of this month')->setTime(23, 59, 59);
                $dateNote = $today->format('F Y') . ' (' . $start->format('M d') . ' – ' . $end->format('M d') . ')';
                break;
        }

        // The two date boxes always show the window the card is counting, so
        // clicking This Week reads Oct 26 – Oct 30 instead of leaving a stale
        // custom range on screen. An unusable hand-picked range falls back to
        // the period that is already resolved above.
        if ($customStart === '' || $customEnd === '') {
            $customStart = $start !== null
                ? $start->format('Y-m-d')
                : $today->modify('first day of this month')->format('Y-m-d');
            $customEnd   = $end !== null
                ? $end->format('Y-m-d')
                : $today->format('Y-m-d');
        }

        return [
            'period'      => $period,
            'start'       => $start,
            'end'         => $end,
            'dateNote'    => $dateNote,
            'customStart' => $customStart,
            'customEnd'   => $customEnd,
        ];
    }

    /**
     * Every offence row the officer registered inside the window — cancelled
     * offences included, so callers apply the same void rule the reports use
     * (ViolatorRecord::summarizeTicketActivity). Attribution matches the
     * reports: the officer who wrote the citation (officer_id), falling back
     * to the account that created it for legacy rows.
     */
    private function enforcementRecords(int $officerId, ?\DateTimeImmutable $start, ?\DateTimeImmutable $end): array
    {
        $builder = $this->violationRecord
            ->select('id, ticket_id, status, penalty_amount')
            ->where('COALESCE(violations.officer_id, violations.created_by)', $officerId);

        if ($start !== null && $end !== null) {
            $builder
                ->where('violation_date >=', $start->format('Y-m-d H:i:s'))
                ->where('violation_date <=', $end->format('Y-m-d H:i:s'));
        }

        return $builder->findAll();
    }

    /**
     * Live figures for the Citation Enforcement Record card, so switching the
     * period updates that card alone instead of reloading the whole profile.
     */
    public function enforcementSummary()
    {
        $officerId = (int) session()->get('id');
        $window    = $this->enforcementWindow();
        $records   = $this->enforcementRecords($officerId, $window['start'], $window['end']);
        $activity  = ViolatorRecord::summarizeTicketActivity($records);

        // The same tally the profile page renders on load: one ticket is one
        // citation, cancelled (void) tickets are not counted, and the
        // clearance rate is settled tickets over live citations.
        return $this->response->setJSON([
            'ok'          => true,
            'period'      => $window['period'],
            'dateNote'    => $window['dateNote'],
            'total'       => $activity['issued'],
            'paid'        => $activity['paid'],
            'pending'     => $activity['pending'],
            'amount'      => number_format($activity['amount'], 2),
            'pct'         => $activity['issued'] > 0 ? (int) round(($activity['paid'] / $activity['issued']) * 100) : 0,
            'customStart' => $window['customStart'],
            'customEnd'   => $window['customEnd'],
        ]);
    }

    /**
     * Turns a YYYY-MM-DD string into a date at midnight, or null when the value
     * is absent, malformed or a date that does not exist (e.g. 2026-02-31).
     */
    private function parseIsoDate(string $value): ?\DateTimeImmutable
    {
        $value = trim($value);

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        if ($date === false || $date->format('Y-m-d') !== $value) {
            return null;
        }

        return $date;
    }

    /**
     * Save the duty status the officer picked in the Field Status modal.
     */
    public function updateDutyStatus()
    {
        $officerId = (int) session()->get('id');
        $officer   = $this->userModel->find($officerId);

        $isAjax = $this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';

        if (!$officer) {
            if ($isAjax) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Officer profile not found.']);
            }
            return redirect()->to(base_url('officer/profile'))->with('error', 'Officer profile not found.');
        }

        $status = trim((string) $this->request->getPost('duty_status'));

        if (! in_array($status, self::DUTY_STATUSES, true)) {
            if ($isAjax) {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Please choose one of the available duty statuses.']);
            }
            return redirect()->back()->with('error', 'Please choose one of the available duty statuses.');
        }

        $now = date('Y-m-d H:i:s');
        $updateData = ['duty_status' => $status];
        if ($status === 'On Break') {
            $updateData['break_started_at'] = $now;
        } else {
            $updateData['break_started_at'] = null;
        }

        if ($this->userModel->update($officerId, $updateData)) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success'          => true,
                    'duty_status'      => $status,
                    'break_started_at' => $updateData['break_started_at'],
                    'message'          => $status === 'On Break'
                        ? 'Break time activated. Field assignments paused.'
                        : 'Duty status updated to "' . $status . '".'
                ]);
            }
            return redirect()->back()->with('success', 'Duty status updated to "' . $status . '".');
        }

        if ($isAjax) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Could not save the duty status. Please try again.']);
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

        // Only ever write whitelisted extensions into the upload folder,
        // even though the image itself is already validated above.
        $extension = strtolower($file->getExtension() ?: 'jpg');
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $extension = 'jpg';
        }

        $newName = 'officer_' . $officerId . '_' . time() . '.' . $extension;

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

        $age = $this->request->getPost('age');

        $updateData = [
            'id'             => $officerId,
            'firstname'      => trim((string) $this->request->getPost('firstname')),
            'lastname'       => trim((string) $this->request->getPost('lastname')),
            'middle_initial' => trim((string) $this->request->getPost('middle_initial')),
            'email'          => trim((string) $this->request->getPost('email')),
            'age'            => ($age === null || $age === '') ? null : (int) $age,
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
            'name_suffix'       => 'permit_empty|max_length[20]',
            'age'               => 'required|integer|greater_than_equal_to[16]|less_than_equal_to[120]',
            'address'           => 'required|max_length[255]',
            'license_plate'     => 'permit_empty|max_length[20]',
            'vehicle_type'      => 'permit_empty|max_length[100]',
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
        // The suffix has its own box on the ticket but is stored as part of the
        // last name, so every view and printout that already prints last_name or
        // driver_name shows the complete name ("Dimagiba Jr.").
        $lastName = tct_join_name_suffix($this->request->getPost('last_name'), $this->request->getPost('name_suffix'));
        // A cited driver may carry no plate, no license and no MTOP at all. Those
        // blanks are recorded as N/A instead of stopping the officer at a
        // "Please fill out this field" prompt; nothing else about the citation
        // changes.
        $licensePlate = strtoupper(trim((string) $this->request->getPost('license_plate')));
        $licenseNumber = trim((string) $this->request->getPost('license_number'));
        $vehicleType = trim((string) $this->request->getPost('vehicle_type'));
        $mtopNumber = trim((string) $this->request->getPost('mtop_number'));
        $licensePlate  = $licensePlate !== '' ? $licensePlate : 'N/A';
        $licenseNumber = $licenseNumber !== '' ? $licenseNumber : 'N/A';
        $mtopNumber    = $mtopNumber !== '' ? $mtopNumber : 'N/A';
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

            try {
                $this->violationRecord->syncSequenceIfHigher($ticketYear, $serial);
            } catch (\Throwable $e) {
                // Alignment is best-effort; the issuance itself must continue.
                log_message('error', 'TCT sequence sync failed: {message}', ['message' => $e->getMessage()]);
            }
        } else {
            try {
                $sharedTicketId = $this->violationRecord->generateNextTctNumber($ticketYear);
            } catch (\Throwable $e) {
                log_message('error', 'TCT allocation failed: {message}', ['message' => $e->getMessage()]);

                return redirect()->back()->withInput()->with('error', 'Could not reserve a ticket number. Please try again.');
            }
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
            'vehicle_type'      => $vehicleType,
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
            // The manual-number check above ran before the token was consumed;
            // repeat it here, immediately before the inserts, so two officers
            // submitting the same booklet number at the same moment cannot
            // both pass their pre-flight check. Auto-allocated numbers are
            // already protected by the tct_sequences lock.
            if ($customTicketNo !== '' && $this->violationRecord->ticketIdExists($sharedTicketId)) {
                $db->transRollback();
                $this->restoreSubmissionToken($submissionToken);

                return redirect()->back()->withInput()
                    ->with('error', 'That TCT number was just used by another ticket. Please choose a different number.');
            }

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

            if ($session->get('role') === 'admin') {
                return redirect()->to(base_url('violators'))
                    ->with('success', 'Violations recorded successfully.');
            }

            return redirect()->to(base_url('officer/view/' . $firstInsertedId . '?print=1&from=violations'))
                ->with('success', 'Violations recorded successfully.');
        } catch (\Throwable $e) {
            $db->transRollback();

            if ($tokenConsumed) {
                $this->restoreSubmissionToken($submissionToken);
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
        $statusFilter = strtolower(trim((string) $this->request->getGet('status')));

        if (! in_array($statusFilter, ['pending', 'paid'], true)) {
            $statusFilter = '';
        }
        $searchTerm = trim((string) $this->request->getGet('search'));

        // "Record another violation" arrives as ?violator_id=<id>. That is a
        // single visit, not a mode of the ledger: the id is copied into the
        // session and stripped from the address bar, so reloading the page or
        // turning to another page never re-opens the form with a violator's
        // details already filled in.
        $requestedPrefill = (int) ($this->request->getGet('violator_id') ?? 0);

        if ($requestedPrefill > 0) {
            session()->set('violation_prefill_id', $requestedPrefill);

            $query = array_filter(
                $this->request->getGet(),
                static fn ($value) => $value !== '' && $value !== null
            );
            unset($query['violator_id']);

            return redirect()->to(
                base_url('officer/violations') . ($query === [] ? '' : '?' . http_build_query($query))
            );
        }

        // The ledger belongs to enforcers. For an administrator the same
        // records already sit in the Violators registry, so a plain visit
        // returns there; the record and edit flows below still open the form.
        $role = (string) session()->get('role');

        if ($role === 'admin'
            && (int) ($this->request->getGet('edit_ticket') ?? 0) <= 0
            && (int) (session()->get('violation_prefill_id') ?? 0) <= 0) {
            return redirect()->to(
                base_url('violators') . ($statusFilter !== '' ? '?status=' . ucfirst($statusFilter) : '')
            );
        }

        // The ledger lists people, not tickets: every citation this officer
        // issued collapses into one row for that violator, so a repeat
        // apprehension never shows up as a second row with the same name. The
        // violator's history page carries each citation as its own record.
        //
        // Settled citations stay listed - a person who paid their ticket is
        // still someone this officer apprehended, and the row now reports what
        // is paid against what is still owed instead of the person vanishing
        // the moment the cashier posts the payment.
        $scopedCount = function (array $extra = []) use ($officerId) {
            $builder = $this->violationRecord
                ->select('COUNT(DISTINCT violations.violator_id) as total', false)
                ->where('violations.officer_id', $officerId)
                ->where('violations.violator_id IS NOT NULL', null, false)
                ->where('violations.status !=', 'Cancelled');

            if ($extra !== []) {
                $builder->where($extra);
            }

            return (int) ($builder->get()->getRow()->total ?? 0);
        };

        $totalViolators   = $scopedCount();
        $pendingViolators = $scopedCount(['violations.status' => 'Pending']);
        $paidViolators    = $scopedCount(['violations.status' => 'Paid']);

        $violationsQuery = $this->violationRecord
            // The ledger row names what the person was caught for, so the newest
            // offense is ordered first and the rest of the list is counted.
            ->select('violations.violator_id, violators.driver_name, violators.first_name, violators.middle_name, violators.last_name, violators.license_plate, COALESCE(NULLIF(TRIM(violators.vehicle_type), \'\'), (SELECT NULLIF(TRIM(vt.vehicle_type), \'\') FROM violations vt WHERE vt.violator_id = violations.violator_id AND TRIM(COALESCE(vt.vehicle_type, \'\')) <> \'\' ORDER BY vt.violation_date DESC LIMIT 1)) as vehicle_type, COALESCE(NULLIF(TRIM(violators.mtop_number), \'\'), (SELECT NULLIF(TRIM(mt.mtop_number), \'\') FROM violations mt WHERE mt.violator_id = violations.violator_id AND TRIM(COALESCE(mt.mtop_number, \'\')) <> \'\' ORDER BY mt.violation_date DESC LIMIT 1)) as mtop_number, violators.license_number, violators.address, COUNT(DISTINCT violations.ticket_id) as citation_count, (SELECT COUNT(DISTINCT sanc.ticket_id) FROM violations sanc WHERE sanc.violator_id = violations.violator_id AND sanc.status <> \'Cancelled\') as sanction_ticket_count, COUNT(violations.id) as infraction_count, SUM(violations.penalty_amount) as total_penalty_sum, MAX(violations.violation_date) as last_violation_date, SUM(CASE WHEN violations.status = \'Pending\' THEN violations.penalty_amount ELSE 0 END) as pending_amount, SUM(CASE WHEN violations.status = \'Paid\' THEN violations.penalty_amount ELSE 0 END) as paid_amount, COUNT(DISTINCT CASE WHEN violations.status = \'Pending\' THEN violations.ticket_id END) as pending_citation_count, COUNT(DISTINCT CASE WHEN violations.status = \'Paid\' THEN violations.ticket_id END) as paid_citation_count, GROUP_CONCAT(violations.violation_type ORDER BY violations.violation_date DESC, violations.id DESC SEPARATOR \'||\') as violation_type_list')
            ->join('violators', 'violators.id = violations.violator_id', 'left')
            ->where('violations.officer_id', $officerId)
            ->where('violations.violator_id IS NOT NULL', null, false)
            ->where('violations.status !=', 'Cancelled');

        if ($statusFilter !== '') {
            // The tab is a section, not a spotlight: choosing Pending lists only
            // the pending part of a violator's record, so the row's amounts, its
            // citation count and its offense list all describe pending citations
            // alone. A person who has both a paid and an unpaid ticket appears
            // under each tab with just that side of the record.
            $violationsQuery->where('violations.status', ucfirst($statusFilter));
        }

        // The search covers the identifiers shown on each ledger row, so an
        // officer can look someone up by plate, license or MTOP as well as by
        // name. These are extra OR terms on the query already being built —
        // no second lookup and no extra work on a normal page load.
        foreach (preg_split('/\s+/', $searchTerm, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $searchWord) {
            $violationsQuery
                ->groupStart()
                    ->like('violators.first_name', $searchWord)
                    ->orLike('violators.middle_name', $searchWord)
                    ->orLike('violators.last_name', $searchWord)
                    ->orLike('violators.driver_name', $searchWord)
                    ->orLike('violators.license_plate', $searchWord)
                    ->orLike('violators.license_number', $searchWord)
                    ->orLike('violators.mtop_number', $searchWord)
                ->groupEnd();
        }

        $violations = $violationsQuery
            ->groupBy('violations.violator_id')
            ->orderBy('MAX(violations.violation_date)', 'DESC')
            ->paginate(10, 'violations');
        $pager = $this->violationRecord->pager;

        // "Edit Citation" opened from a violator's history page comes back as
        // ?edit_ticket=<violation id>: the ledger re-opens the edit modal for
        // that one ticket instead of for a row of this table.
        [$editTicket, $ticketInfractions] = $this->loadEditTicketSeed(
            $officerId,
            (int) ($this->request->getGet('edit_ticket') ?? 0)
        );

        // One-time token used by the record form to stop duplicate tickets.
        $submissionToken = bin2hex(random_bytes(16));
        $tokens          = session()->get('violation_submission_tokens');
        $tokens          = is_array($tokens) ? $tokens : [];
        $tokens[]        = $submissionToken;
        $tokens          = array_slice(array_values(array_unique($tokens)), -20);
        session()->set('violation_submission_tokens', $tokens);

        // Optional prefill when "Record Violation" is clicked from a violator.
        // The id is staged in the session by the redirect above and read once,
        // so the next reload or page change starts with an empty form.
        $prefillViolator = null;
        $prefillId       = (int) (session()->get('violation_prefill_id') ?? 0);

        if ($prefillId > 0) {
            session()->remove('violation_prefill_id');
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

        // Offenses this person was cited for before, so the checklist can shade
        // them while the officer fills in a new ticket. Voided citations do not
        // count, and the list is by name because the catalog is matched on the
        // name (types can be deactivated without losing old citations).
        $prefillOffenses = [];
        if ($prefillViolator) {
            $historyRows = \Config\Database::connect()
                ->table('violations')
                ->select('violation_type')
                ->where('violator_id', $prefillId)
                ->where('status !=', 'Cancelled')
                ->groupBy('violation_type')
                ->get()->getResultArray();

            foreach ($historyRows as $historyRow) {
                $historyName = trim((string) ($historyRow['violation_type'] ?? ''));
                if ($historyName !== '') {
                    $prefillOffenses[] = $historyName;
                }
            }
        }

        // Editing a citation opened from a violator's history keeps that
        // context, so saving returns to the record instead of the whole ledger.
        $returnFrom     = $this->request->getGet('from') === 'violator-profile' ? 'violator-profile' : '';
        $returnViolator = (int) ($this->request->getGet('violator') ?? 0);

        // The ticket preview prints the officer's name above the signature box,
        // so the same name the citation will carry is visible while it is being
        // filled in.
        $officerAccount = $this->userModel->find($officerId) ?? [];
        $middleInitial  = trim((string) ($officerAccount['middle_initial'] ?? ''));
        $officerPrintName = trim(implode(' ', array_filter([
            trim((string) ($officerAccount['firstname'] ?? '')),
            $middleInitial !== '' ? $middleInitial . '.' : '',
            trim((string) ($officerAccount['lastname'] ?? '')),
        ]))) ?: (string) session()->get('username');

        $data = [
            'title'          => 'Violation',
            'officerPrintName' => $officerPrintName,
            'nextTctNumber'  => $this->violationRecord->peekNextTctNumber((int) date('Y')),
            'violationTypes' => $this->violationTypeModel->where('status', 'active')->findAll(),
            'violations'     => $violations,
            'returnFrom'     => $returnFrom,
            'returnViolator' => $returnViolator,
            'ticketInfractions' => $ticketInfractions,
            'editTicket'     => $editTicket,
            'pager'          => $pager,
            'totalViolators'   => $totalViolators,
            'pendingViolators' => $pendingViolators,
            'paidViolators'    => $paidViolators,
            'statusFilter'     => $statusFilter,
            'searchTerm'       => $searchTerm,
            'submissionToken' => $submissionToken,
            'prefillViolator' => $prefillViolator,
            'prefillOffenses' => $prefillOffenses,
        ];
        return view('officer/index', $data);
    }

    public function view($id)
    {
        // Administrators read citations from the Violators registry; sending
        // them to the enforcer ledger would strand them outside their own nav.
        $homeUrl = session()->get('role') === 'admin'
            ? base_url('violators')
            : base_url('officer/violations');

        $primaryViolation = $this->violationRecord->getDetailedViolation($id);

        if (!$primaryViolation) {
            return redirect()->to($homeUrl)->with('error', 'Violation not found.');
        }

        // A ticket belongs to the officer who issued it, and administrators
        // may open any record. An enforcer may also open tickets belonging
        // to a violator they have on their own ledger - the same rule the
        // violator profile applies - so the citation links in a person's
        // history keep working without leaving arbitrary ticket ids open
        // to guessing.
        $role      = (string) session()->get('role');
        $officerId = (int) session()->get('id');

        if ($role !== 'admin' && (int) ($primaryViolation['officer_id'] ?? 0) !== $officerId) {
            $violatorId     = (int) ($primaryViolation['violator_id'] ?? 0);
            $sharesViolator = $violatorId > 0 && $this->violationRecord
                ->where('violator_id', $violatorId)
                ->where('officer_id', $officerId)
                ->countAllResults() > 0;

            if (! $sharesViolator) {
                return redirect()->to($homeUrl)->with('error', 'Violation record not found.');
            }
        }

        // Fetch all violations sharing the same ticket ID
        $allViolations = $this->violationRecord->select('violations.*, ' . ViolatorRecord::officerNameSql() . ' as officer_name')
            ->join('users', 'users.id = violations.officer_id', 'left')
            ->where('violations.ticket_id', $primaryViolation['ticket_id'])
            ->findAll();

        $data = [
            'title' => 'Violation Details',
            'violation' => $primaryViolation,
            'all_violations' => $allViolations
        ];

        // "View Citation" on the violator profile opens the official TCT
        // document as a full page (same document the print view uses), while
        // the normal ticket view keeps the citation summary and its popup.
        if (service('request')->getGet('format') === 'tct') {
            return view('officer/tct', $data);
        }

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

        $reason = $this->request->getPost('reason');
        $reason = is_string($reason) ? trim($reason) : '';
        $reason = mb_substr($reason !== '' ? $reason : 'Cancelled by officer', 0, 500);
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
        $lastName = tct_join_name_suffix($this->request->getPost('last_name'), $this->request->getPost('name_suffix'));

        // Same rule as issuance: blank plate / license / MTOP is recorded as N/A
        // rather than blocking the save.
        $licenseNumber = trim((string) $this->request->getPost('license_number'));
        $vehicleType = trim((string) $this->request->getPost('vehicle_type'));
        $mtopNumber = trim((string) $this->request->getPost('mtop_number'));
        $ownerName = trim((string) $this->request->getPost('owner_name'));
        $acknowledgment = trim((string) $this->request->getPost('acknowledgment'));
        $licensePlate  = strtoupper(trim((string) $this->request->getPost('license_plate')));
        $licensePlate  = $licensePlate !== '' ? $licensePlate : 'N/A';
        $licenseNumber = $licenseNumber !== '' ? $licenseNumber : 'N/A';
        $mtopNumber    = $mtopNumber !== '' ? $mtopNumber : 'N/A';

        $rules = [
            'first_name'    => 'required|max_length[100]',
            'last_name'     => 'required|max_length[100]',
            'name_suffix'   => 'permit_empty|max_length[20]',
            'age'           => 'required|integer|greater_than_equal_to[16]|less_than_equal_to[120]',
            'address'       => 'required|max_length[255]',
            'license_plate' => 'permit_empty|max_length[20]',
            'vehicle_type'  => 'permit_empty|max_length[100]',
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
            'license_plate'    => $licensePlate,
            'vehicle_type'     => $vehicleType,
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

        // A save goes back where the edit was started: the violator's history
        // when it was opened from there, and the ledger otherwise. Reaching the
        // all-records list stays a deliberate Back click.
        $afterSaveUrl = ($this->request->getPost('from') === 'violator-profile' && (int) $this->request->getPost('violator') > 0)
            ? base_url('officer/violator-profile/' . (int) $this->request->getPost('violator'))
            : base_url('officer/violations');

        return redirect()->to($afterSaveUrl)->with('success', 'Violation updated successfully.');
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
    /**
     * Give a consumed one-time submission token back to the session after a
     * failed issuance, so the officer can fix the form and submit it again
     * without re-opening the Record Violation page.
     */
    private function restoreSubmissionToken(string $token): void
    {
        $tokens   = session()->get('violation_submission_tokens');
        $tokens   = is_array($tokens) ? $tokens : [];
        $tokens[] = $token;
        session()->set('violation_submission_tokens', array_slice(array_values(array_unique($tokens)), -20));
    }

    /**
     * Loads the ticket the edit modal should open with, together with its
     * infractions. Only a pending citation recorded by this officer qualifies,
     * so a link cannot open someone else's ticket.
     *
     * @return array{0: array<string, mixed>|null, 1: array<string, list<array<string, mixed>>>}
     */
    private function loadEditTicketSeed(int $officerId, int $editTicketId): array
    {
        $ticketInfractions = [];
        $editTicket        = null;

        if ($editTicketId <= 0) {
            return [$editTicket, $ticketInfractions];
        }

        $primary = $this->violationRecord->find($editTicketId);

        if (! $primary
            || (int) ($primary['officer_id'] ?? 0) !== $officerId
            || ($primary['status'] ?? '') !== 'Pending') {
            return [$editTicket, $ticketInfractions];
        }

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

        return [$editTicket, $ticketInfractions];
    }

    public function violatorProfile($id)
    {
        $violatorId = (int) $id;
        $profile    = $this->violatorModel->getProfile($violatorId);

        $role = (string) session()->get('role');

        if (! $profile) {
            return redirect()->to($role === 'admin' ? base_url('violators') : base_url('officer/violations'))
                ->with('error', 'Violator record not found.');
        }

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
            // this violator (every citation, settled ones marked Paid) instead
            // of the administrator's full profile. Administrators keep the
            // Violators registry as their way back.
            $history = $this->getViolatorHistoryData(null, $violatorId);

            // "Edit Citation" stays on this page: the modal opens here and
            // saving returns here instead of the whole citation ledger.
            [$editTicket, $ticketInfractions] = $this->loadEditTicketSeed(
                (int) session()->get('id'),
                (int) ($this->request->getGet('edit_ticket') ?? 0)
            );

            return view('officer/violator_profile', [
                'title'          => 'Violator Record - ' . ($history['violator']['name'] ?: 'Record'),
                'violator'       => $history['violator'],
                // Kept for the citation links, so opening a ticket can bring the
                // reader back to this same violator's records.
                'violatorId'     => $violatorId,
                'tickets'        => $history['tickets'],
                'violationTypes' => $this->violationTypeModel->where('status', 'active')->findAll(),
                'editTicket'        => $editTicket,
                'ticketInfractions' => $ticketInfractions,
                'returnFrom'        => 'violator-profile',
                'returnViolator'    => $violatorId,
            ]);
        }

        $data = [
            'title'          => 'Violator Profile - ' . ($profile['violator']['driver_name'] ?: 'Record'),
            'violator'       => $profile['violator'],
            'summary'        => $profile['summary'],
            'tickets'        => $profile['tickets'],
            'vehicles'       => $profile['vehicles'],
            'backUrl'        => $role === 'admin' ? base_url('violators') : base_url('officer/violations'),
            'backLabel'      => $role === 'admin' ? 'Back to Violators' : 'Back to My Recorded Violations',
            'ticketViewBase' => base_url('officer/view/'),
            'recordUrl'      => base_url('officer/violations?violator_id=' . $violatorId),
            'isAdmin'        => false,
            'isEnforcer'     => true,
            'cancelBase'     => base_url('officer/cancel/'),
        ];

        return view('violators/profile', $data);
    }

    /**
     * Whether the signed-in user may set or clear a violator's photo.
     * Administrators always may; an enforcer may only touch a violator
     * they have issued a ticket to — the same rule the profile page
     * itself applies before it shows the record.
     */
    private function canManageViolatorPhoto(int $violatorId): bool
    {
        $role = (string) session()->get('role');

        if ($role === 'admin') {
            return true;
        }

        if ($role !== 'enforcer') {
            return false;
        }

        return \Config\Database::connect()
            ->table('violations')
            ->where('violator_id', $violatorId)
            ->where('officer_id', (int) session()->get('id'))
            ->countAllResults() > 0;
    }

    /**
     * Two-letter initials (first + last name) shown when a violator has no
     * photo — the same wordmark the profile pages build.
     */
    private function violatorInitials(array $violator): string
    {
        $name = trim((string) ($violator['driver_name'] ?? ''));

        if ($name === '') {
            $name = trim(($violator['first_name'] ?? '') . ' ' . ($violator['last_name'] ?? ''));
        }

        $words = array_values(array_filter(preg_split('/\s+/', $name) ?: []));

        if ($words === []) {
            return 'VS';
        }

        if (count($words) === 1) {
            return mb_strtoupper(mb_substr($words[0], 0, 2));
        }

        return mb_strtoupper(
            mb_substr($words[0], 0, 1)
            . mb_substr($words[count($words) - 1], 0, 1)
        );
    }

    /**
     * Upload or replace a violator's photo. The file is stored under
     * public/uploads/violators/ with a generated name; the previous file
     * is removed so the folder never collects orphans.
     */
    public function updateViolatorPhoto($id)
    {
        $isAjax     = $this->request->isAJAX() || $this->request->hasHeader('X-Requested-With');
        $violatorId = (int) $id;
        $violator   = $this->violatorModel->find($violatorId);
        $backUrl    = base_url('officer/violator-profile/' . $violatorId);

        if (! $violator) {
            if ($isAjax) {
                return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Violator record not found.']);
            }
            return redirect()->to(base_url('officer/violations'))->with('error', 'Violator record not found.');
        }

        if (! $this->canManageViolatorPhoto($violatorId)) {
            $msg = 'You can only manage photos of violators you have recorded a ticket for.';
            if ($isAjax) {
                return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => $msg]);
            }
            return redirect()->to($backUrl)->with('error', $msg);
        }

        $rules = [
            'profile_photo' => 'uploaded[profile_photo]|is_image[profile_photo]|mime_in[profile_photo,image/jpg,image/jpeg,image/png,image/webp]|max_size[profile_photo,10240]',
        ];

        if (! $this->validate($rules)) {
            $errors = $this->validator->getErrors();
            $msg    = $errors['profile_photo'] ?? 'Invalid image upload. Please choose a valid JPG, PNG, or WEBP under 10MB.';
            if ($isAjax) {
                return $this->response->setStatusCode(422)->setJSON(['status' => 'error', 'message' => $msg]);
            }
            return redirect()->to($backUrl)->with('error', $msg);
        }

        $file = $this->request->getFile('profile_photo');
        if (! $file || ! $file->isValid()) {
            if ($isAjax) {
                return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Failed to upload photo.']);
            }
            return redirect()->to($backUrl)->with('error', 'Failed to upload photo.');
        }

        $uploadPath = FCPATH . 'uploads/violators/';
        if (! is_dir($uploadPath) && ! mkdir($uploadPath, 0755, true) && ! is_dir($uploadPath)) {
            if ($isAjax) {
                return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Cannot create upload directory.']);
            }
            return redirect()->to($backUrl)->with('error', 'Cannot create upload directory.');
        }

        // Only ever write whitelisted extensions into the upload folder,
        // even though the image itself is already validated above.
        $extension = strtolower($file->getExtension() ?: 'jpg');
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $extension = 'jpg';
        }

        $newName = 'violator_' . $violatorId . '_' . time() . '.' . $extension;

        if (! $file->move($uploadPath, $newName, true)) {
            if ($isAjax) {
                return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Unable to save uploaded photo.']);
            }
            return redirect()->to($backUrl)->with('error', 'Unable to save uploaded photo.');
        }

        $oldImage = trim((string) ($violator['profile_image'] ?? ''));
        if ($oldImage !== '') {
            $oldPath = $uploadPath . $oldImage;
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        if (! $this->violatorModel->update($violatorId, ['profile_image' => $newName])) {
            $newPath = $uploadPath . $newName;
            if (is_file($newPath)) {
                @unlink($newPath);
            }
            if ($isAjax) {
                return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Failed to update the violator record.']);
            }
            return redirect()->to($backUrl)->with('error', 'Failed to update the violator record.');
        }

        $photoUrl = base_url('uploads/violators/' . $newName);

        if ($isAjax) {
            return $this->response->setJSON([
                'status'     => 'success',
                'message'    => 'Violator photo updated successfully.',
                'photo_url'  => $photoUrl,
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        return redirect()->to($backUrl)->with('success', 'Violator photo updated successfully.');
    }

    /**
     * Remove a violator's photo and fall back to their initials.
     */
    public function removeViolatorPhoto($id)
    {
        $isAjax     = $this->request->isAJAX() || $this->request->hasHeader('X-Requested-With');
        $violatorId = (int) $id;
        $violator   = $this->violatorModel->find($violatorId);
        $backUrl    = base_url('officer/violator-profile/' . $violatorId);

        if (! $violator) {
            if ($isAjax) {
                return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Violator record not found.']);
            }
            return redirect()->to(base_url('officer/violations'))->with('error', 'Violator record not found.');
        }

        if (! $this->canManageViolatorPhoto($violatorId)) {
            $msg = 'You can only manage photos of violators you have recorded a ticket for.';
            if ($isAjax) {
                return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => $msg]);
            }
            return redirect()->to($backUrl)->with('error', $msg);
        }

        $oldImage = trim((string) ($violator['profile_image'] ?? ''));
        if ($oldImage !== '') {
            $oldPath = FCPATH . 'uploads/violators/' . $oldImage;
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        $this->violatorModel->update($violatorId, ['profile_image' => null]);

        $initials = $this->violatorInitials($violator);

        if ($isAjax) {
            return $this->response->setJSON([
                'status'     => 'success',
                'message'    => 'Violator photo removed.',
                'initials'   => $initials,
                'initial'    => mb_substr($initials, 0, 1),
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        return redirect()->to($backUrl)->with('success', 'Violator photo removed.');
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
            ->select('violations.*, ' . ViolatorRecord::officerNameSql() . ' as officer_name, users.firstname as officer_fname, users.lastname as officer_lname')
            ->join('users', 'users.id = violations.officer_id', 'left');

        if ($violatorId > 0) {
            // One person, one history: every citation linked to this violator
            // record is listed, no matter which officer issued it.
            $builder->where('violations.violator_id', $violatorId);
        } else {
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
                // "Match nothing" has to be raw SQL. A two-argument where() is
                // read as (column, value), and the numeric-looking '1' became an
                // int column name — which threw a TypeError and turned an empty
                // search into a 500 instead of an empty result.
                $builder->where('1 = 0', null, false);
            }

            $builder->groupEnd();
        }

        // A settled citation stays on the record: when the same driver is
        // apprehended again, the earlier ticket must still be visible (marked
        // Paid, with its receipt) instead of leaving a gap where it used to be.
        // Only cancelled tickets are left out.
        $builder->where('violations.status !=', 'Cancelled');
        $builder->orderBy('violations.violation_date', 'DESC');
        $rawRecords = $builder->findAll();

        // Attempts count every apprehension on record, settled ones included,
        // because a paid citation is still a prior offense.
        $attempts = 0;
        if ($violatorId > 0) {
            $attemptRow = \Config\Database::connect()
                ->table('violations')
                ->select('COUNT(DISTINCT ticket_id) as attempts', false)
                ->where('violator_id', $violatorId)
                ->get()->getRow();
            $attempts = (int) ($attemptRow->attempts ?? 0);
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
            // The unit actually driven, so the profile can name it next to the
            // plate and MTOP number instead of leaving them unexplained.
            'vehicle_type'   => $baseRecord['vehicle_type'] ?? '',
            'mtop_number'    => $baseRecord['mtop_number'] ?? '',
            'owner_name'     => $baseRecord['owner_name'] ?? '',
            // Carried from the registered violator row (when this page was
            // opened from a record); the photo controls only appear for them.
            'profile_image'  => $registered['profile_image'] ?? null,
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
            if (!empty($r['vehicle_type']) && empty($violator['vehicle_type'])) $violator['vehicle_type'] = $r['vehicle_type'];
            if (!empty($r['mtop_number']) && empty($violator['mtop_number'])) $violator['mtop_number'] = $r['mtop_number'];

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
                    // Kept so the page only offers edit / cancel / delete on the
                    // tickets this officer actually recorded.
                    'officer_id'     => (int) ($r['officer_id'] ?? 0),
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

        // One sanction per apprehension. The shared helper words this label, so
        // the enforcer record and the admin record never drift apart.
        $sanctionRecord = sanction_label($attempts);

        $violator['attempts']           = $attempts;
        $violator['sanction_record']    = $sanctionRecord;
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
