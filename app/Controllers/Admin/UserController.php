<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\ViolatorRecord;

class UserController extends BaseController
{
    /** The same three field-status options the enforcer can pick in the officer portal. */
    private const DUTY_STATUSES = ['Active Patrol', 'On Break', 'Off Duty'];

    protected $userModel;
    protected $violationModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->violationModel = new ViolatorRecord();
    }

    public function index()
    {
        $role = (string) ($this->request->getGet('role') ?? '');
        $allowedRoles = ['admin', 'enforcer'];

        if ($role !== '' && ! in_array($role, $allowedRoles, true)) {
            $role = '';
        }

        // Duty status belongs to enforcers only, so picking one narrows the
        // list to enforcers even when no role filter was supplied.
        $duty = (string) ($this->request->getGet('duty') ?? '');
        if ($duty !== '' && ! in_array($duty, self::DUTY_STATUSES, true)) {
            $duty = '';
        }
        if ($duty !== '') {
            $role = 'enforcer';
        }

        // Server-side search covers every page, not just the rendered one. The
        // list view sends this on a debounced keystroke and swaps only the
        // results region, so typing never triggers a full page navigation.
        $search = trim((string) ($this->request->getGet('search') ?? ''));

        // Counts first: countAllResults() resets the shared builder state, so
        // the role filter for the paginated list must be applied afterwards.
        $stats = [
            'total'    => $this->userModel->whereIn('role', $allowedRoles)->countAllResults(),
            'admin'    => $this->userModel->where('role', 'admin')->countAllResults(),
            'enforcer' => $this->userModel->where('role', 'enforcer')->countAllResults(),
            'duty'     => [],
        ];
        foreach (self::DUTY_STATUSES as $dutyStatus) {
            $stats['duty'][$dutyStatus] = $this->userModel
                ->where('role', 'enforcer')
                ->where('duty_status', $dutyStatus)
                ->countAllResults();
        }

        if ($role !== '') {
            $this->userModel->where('role', $role);
        } else {
            $this->userModel->whereIn('role', $allowedRoles);
        }
        if ($duty !== '') {
            $this->userModel->where('duty_status', $duty);
        }
        if ($search !== '') {
            $this->userModel->groupStart()
                ->like('username', $search)
                ->orLike('email', $search)
                ->orLike('firstname', $search)
                ->orLike('lastname', $search)
                ->groupEnd();
        }

        $data = [
            'title'           => 'User Management',
            'selectedRole'    => $role,
            'selectedDuty'    => $duty,
            'selectedSearch'  => $search,
            'dutyStatuses'    => self::DUTY_STATUSES,
            'stats'           => $stats,
            'users'           => $this->userModel->paginate(10, 'users'),
            'pager'           => $this->userModel->pager,
        ];
        return view('admin/users/index', $data);
    }

    /**
     * Read-only duty status feed for User Management.
     *
     * Duty status belongs to the enforcer: it is set from the officer portal's
     * Field Status dialog only. An admin never writes it — the list simply
     * reads it and polls this endpoint so the badges always reflect whatever
     * the enforcer last set.
     */
    public function dutyStatusApi()
    {
        $enforcers = $this->userModel
            ->select('id, duty_status')
            ->where('role', 'enforcer')
            ->findAll();

        $statuses = [];
        foreach ($enforcers as $enforcer) {
            $statuses[(int) $enforcer['id']] = (string) ($enforcer['duty_status'] ?? 'Off Duty');
        }

        return $this->response->setJSON([
            'success'  => true,
            'statuses' => $statuses,
        ]);
    }

    public function viewDriver($id = null)
    {
        $user = $this->userModel->find($id);
        if (!$user || $user['role'] !== 'driver') {
            return redirect()->to(base_url('users?role=driver'))->with('error', 'Driver not found.');
        }

        $data = [
            'title' => 'Driver Details: ' . $user['username'],
            'user'  => $user,
            'violations' => $this->violationModel->where('driver_name', $user['username'])->findAll(),
        ];
        return view('admin/users/view_driver', $data);
    }

    public function viewEnforcer($id = null)
    {
        $user = $this->userModel->find($id);
        if (!$user || $user['role'] !== 'enforcer') {
            return redirect()->to(base_url('users?role=enforcer'))->with('error', 'Traffic enforcer not found.');
        }

        // Same officer attribution as the reports: officer_id first, then the
        // account that created the citation for legacy rows.
        $records = $this->violationModel
            ->select('violations.*, ' . ViolatorRecord::officerNameSql() . ' as officer_name')
            ->join('users', 'users.id = COALESCE(violations.officer_id, violations.created_by)', 'left')
            ->where('COALESCE(violations.officer_id, violations.created_by)', (int) $id)
            ->orderBy('violations.violation_date', 'DESC')
            ->findAll();

        // Assessed fines only: a cancelled citation is void and adds no money
        // anywhere, so assessed = paid + pending.
        $totalAssessedAmount = 0.0;
        foreach ($records as $record) {
            if (($record['status'] ?? '') !== 'Cancelled') {
                $totalAssessedAmount += (float) ($record['penalty_amount'] ?? 0);
            }
        }

        // Group by violator first, then keep every ticket underneath that
        // person. A driver caught on several dates appears once, with all of
        // their issued citations available in the expanded row.
        $violatorGroups = [];

        foreach ($records as $record) {
            $violatorId  = (int) ($record['violator_id'] ?? 0);
            $firstName   = trim((string) ($record['first_name'] ?? ''));
            $lastName    = trim((string) ($record['last_name'] ?? ''));
            $driverName  = trim((string) ($record['driver_name'] ?? ''));
            $displayName = trim($firstName . ' ' . $lastName);

            if ($displayName === '') {
                $displayName = $driverName !== '' ? $driverName : 'Unknown Violator';
            }

            // The linked violator id is the registry identity. Legacy rows
            // without one fall back to the normalised name + plate.
            $normalName = preg_replace('/\s+/', ' ', $displayName) ?? $displayName;
            $nameKey    = mb_strtolower($normalName);
            $plateKey   = strtoupper(trim((string) ($record['license_plate'] ?? '')));
            $groupKey   = $violatorId > 0 ? 'v' . $violatorId : 'n' . $nameKey . '|' . $plateKey;

            if (! isset($violatorGroups[$groupKey])) {
                $violatorGroups[$groupKey] = [
                    'violator_id'      => $violatorId,
                    'display_name'     => $displayName,
                    'driver_name'      => $driverName,
                    'plates'           => [],
                    'tickets'          => [],
                    'ticket_count'     => 0,
                    'infraction_count' => 0,
                    'total_amount'     => 0.0,
                    'assessed_amount'  => 0.0,
                    'latest_date'      => null,
                ];
            }

            $group =& $violatorGroups[$groupKey];

            $plate = trim((string) ($record['license_plate'] ?? ''));
            if ($plate !== '') {
                $group['plates'][$plate] = true;
            }

            $ticketId  = trim((string) ($record['ticket_id'] ?? ''));
            $ticketKey = $ticketId !== '' ? $ticketId : 'single_' . $record['id'];

            if (! isset($group['tickets'][$ticketKey])) {
                $group['tickets'][$ticketKey] = [
                    'id'             => (int) $record['id'],
                    'ticket_id'      => $ticketId !== '' ? $ticketId : 'N/A',
                    'status'         => (string) ($record['status'] ?? 'Pending'),
                    'statuses'       => [],
                    'violation_date' => $record['violation_date'] ?? null,
                    'latest_date'    => $record['violation_date'] ?? null,
                    'total_amount'   => 0.0,
                    'infractions'    => [],
                ];
            }

            $ticket =& $group['tickets'][$ticketKey];

            $recordStatus = (string) ($record['status'] ?? 'Pending');
            $ticket['statuses'][$recordStatus] = true;

            // A ticket is only settled when every offense line is paid; one
            // pending line keeps the whole citation open.
            if ($recordStatus === 'Pending') {
                $ticket['status'] = 'Pending';
            }

            $penalty = (float) ($record['penalty_amount'] ?? 0);
            $ticket['total_amount'] += $penalty;
            $ticket['infractions'][] = [
                'id'             => (int) $record['id'],
                'violation_type' => $record['violation_type'] ?? '-',
                'penalty_amount' => $penalty,
                'status'         => $recordStatus,
            ];

            if (! empty($record['violation_date'])) {
                $date = (string) $record['violation_date'];
                if ($ticket['latest_date'] === null || $date > $ticket['latest_date']) {
                    $ticket['latest_date'] = $date;
                }
                if ($group['latest_date'] === null || $date > $group['latest_date']) {
                    $group['latest_date'] = $date;
                }
            }

            $group['total_amount'] += $penalty;
            if ($recordStatus !== 'Cancelled') {
                $group['assessed_amount'] += $penalty;
            }
            $group['infraction_count']++;

            unset($group, $ticket);
        }

        // Resolve each ticket's final status from all of its offense lines,
        // then roll the tickets up into the person's overall standing. The
        // rule is exclusive: all cancelled -> Cancelled, any pending ->
        // Pending, otherwise Paid.
        $totalTickets     = 0;
        $pendingTickets   = 0;
        $paidTickets      = 0;
        $cancelledTickets = 0;

        foreach ($violatorGroups as &$group) {
            $group['tickets'] = array_values($group['tickets']);
            $group['ticket_count'] = count($group['tickets']);
            $group['plates'] = array_keys($group['plates']);

            $pendingInGroup   = 0;
            $paidInGroup      = 0;
            $cancelledInGroup = 0;

            foreach ($group['tickets'] as &$ticket) {
                $statuses = $ticket['statuses'] ?? [];

                if (isset($statuses['Pending'])) {
                    $ticket['status'] = 'Pending';
                } elseif (isset($statuses['Paid'])) {
                    $ticket['status'] = 'Paid';
                } elseif (isset($statuses['Cancelled'])) {
                    $ticket['status'] = 'Cancelled';
                } else {
                    $ticket['status'] = 'Pending';
                }

                unset($ticket['statuses']);

                if ($ticket['status'] === 'Pending') {
                    $pendingInGroup++;
                    $pendingTickets++;
                } elseif ($ticket['status'] === 'Paid') {
                    $paidInGroup++;
                    $paidTickets++;
                } else {
                    $cancelledInGroup++;
                    $cancelledTickets++;
                }

                $totalTickets++;
            }
            unset($ticket);

            $group['pending_tickets']   = $pendingInGroup;
            $group['paid_tickets']      = $paidInGroup;
            $group['cancelled_tickets'] = $cancelledInGroup;

            if ($group['ticket_count'] > 0 && $cancelledInGroup === $group['ticket_count']) {
                $group['status'] = 'Cancelled';
            } elseif ($pendingInGroup > 0) {
                $group['status'] = 'Pending';
            } elseif ($paidInGroup > 0) {
                $group['status'] = 'Paid';
            } else {
                $group['status'] = 'Pending';
            }

            // Newest ticket first inside the expanded citations list.
            usort($group['tickets'], static fn ($a, $b) =>
                strcmp((string) ($b['latest_date'] ?? ''), (string) ($a['latest_date'] ?? ''))
            );
        }
        unset($group);

        $violatorGroups = array_values($violatorGroups);

        usort($violatorGroups, static fn ($a, $b) =>
            strcmp((string) ($b['latest_date'] ?? ''), (string) ($a['latest_date'] ?? ''))
        );

        $totalViolators     = count($violatorGroups);
        $pendingViolators   = count(array_filter($violatorGroups, static fn ($group) => ($group['status'] ?? '') === 'Pending'));
        $paidViolators      = count(array_filter($violatorGroups, static fn ($group) => ($group['status'] ?? '') === 'Paid'));
        $cancelledViolators = count(array_filter($violatorGroups, static fn ($group) => ($group['status'] ?? '') === 'Cancelled'));

        $data = [
            'title'               => 'Traffic Enforcer Profile: ' . $user['username'],
            'user'                => $user,
            'records'             => $records,
            'violatorGroups'      => $violatorGroups,
            'totalViolators'      => $totalViolators,
            'totalTickets'        => $totalTickets,
            'totalInfractions'    => count($records),
            'pendingViolators'    => $pendingViolators,
            'paidViolators'       => $paidViolators,
            'cancelledViolators'  => $cancelledViolators,
            'pendingTickets'      => $pendingTickets,
            'paidTickets'         => $paidTickets,
            'cancelledTickets'    => $cancelledTickets,
            'totalAssessedAmount' => $totalAssessedAmount,
        ];

        return view('admin/users/view_enforcer', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Create New User',
            'defaults' => [
                'username'  => '',
                'firstname' => '',
                'lastname'  => '',
                'middle_initial' => '',
                'age'       => '',
                'birthdate' => '',
                'address'   => '',
                'password'  => '',
                'role'      => 'enforcer',
            ],
        ];
        return view('admin/users/create', $data);
    }

    public function store()
    {
        $defaultPassword = (string) (env('auth.defaultUserPassword') ?? '');
        $defaultRole = 'driver';

        $rules = [
            'username' => 'required|alpha_numeric_space|min_length[3]|is_unique[users.username]',
            'firstname'=> 'required|min_length[2]|max_length[100]',
            'lastname' => 'required|min_length[2]|max_length[100]',
            'middle_initial' => 'permit_empty|max_length[10]',
            'age'      => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[120]',
            'birthdate'=> 'required|valid_date',
            'address'  => 'required|max_length[255]',
            'license_number' => 'permit_empty|max_length[50]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            // allow shorter password when default env password is used
            'password' => $defaultPassword !== '' ? 'permit_empty|min_length[5]' : 'required|min_length[8]',
            'confirm_password' => 'matches[password]',
            'backup_password' => 'permit_empty|min_length[5]',
            'confirm_backup_password' => 'permit_empty|matches[backup_password]',
            'role'     => 'permit_empty|in_list[admin,driver,enforcer]',
            'assigned_station' => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $role = (string) ($this->request->getPost('role') ?: $defaultRole);
        $password = (string) $this->request->getPost('password');
        if ($password === '' && $defaultPassword !== '') {
            $password = $defaultPassword;
        }

        $data = array_merge($this->accountFieldsFromPost(), [
            'password'  => $password,
            'role'      => $role,
            'status'    => 'active',
        ]);

        // Optional second sign-in credential, used if the main password is
        // forgotten. Only its bcrypt hash is persisted.
        $backupPassword = (string) $this->request->getPost('backup_password');
        if ($backupPassword !== '') {
            $data['backup_password_hash']   = password_hash($backupPassword, PASSWORD_DEFAULT);
            $data['backup_password_set_at'] = date('Y-m-d H:i:s');
        }

        if ($this->userModel->save($data)) {
            return redirect()->to(base_url('users') . ($data['role'] ? '?role=' . $data['role'] : ''))->with('success', 'User created successfully.');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->userModel->errors());
        }
    }

    public function edit($id = null)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to(base_url('users'))->with('error', 'User not found.');
        }

        $data = [
            'title' => 'Edit User',
            'user'  => $user,
        ];
        return view('admin/users/edit', $data);
    }

    public function update($id = null)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to(base_url('users'))->with('error', 'User not found.');
        }

        $rules = [
            'username' => "required|alpha_numeric_space|min_length[3]|is_unique[users.username,id,{$id}]",
            'firstname'=> 'required|min_length[2]|max_length[100]',
            'lastname' => 'required|min_length[2]|max_length[100]',
            'middle_initial' => 'permit_empty|max_length[10]',
            'age'      => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[120]',
            'birthdate'=> 'required|valid_date',
            'address'  => 'required|max_length[255]',
            'license_number' => 'permit_empty|max_length[50]',
            'email'    => "required|valid_email|is_unique[users.email,id,{$id}]",
            'role'     => 'required|in_list[admin,driver,enforcer]',
            'assigned_station' => 'permit_empty|max_length[255]',
            'status'   => 'required|in_list[active,inactive,suspended]',
        ];

        if ($this->request->getPost('password')) {
            $rules['password'] = 'min_length[8]';
            $rules['confirm_password'] = 'matches[password]';
        }

        // Optional backup password. Leaving it blank keeps the current one;
        // supplying a new value replaces it.
        if ($this->request->getPost('backup_password')) {
            $rules['backup_password'] = 'min_length[5]';
            $rules['confirm_backup_password'] = 'matches[backup_password]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = array_merge($this->accountFieldsFromPost(), [
            'id'        => $id,
            'role'      => $this->request->getPost('role'),
            'status'    => $this->request->getPost('status'),
        ]);

        if ($this->request->getPost('password')) {
            $data['password'] = $this->request->getPost('password');
        }

        if ($this->request->getPost('backup_password')) {
            $data['backup_password_hash']   = password_hash((string) $this->request->getPost('backup_password'), PASSWORD_DEFAULT);
            $data['backup_password_set_at'] = date('Y-m-d H:i:s');
        }

        if ($this->userModel->save($data)) {
            return redirect()->to(base_url('users') . ($data['role'] ? '?role=' . $data['role'] : ''))->with('success', 'User updated successfully.');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->userModel->errors());
        }
    }

    public function delete($id = null)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $role = $user['role'];
        if ($this->userModel->delete($id)) {
            return redirect()->to(base_url('users') . ($role ? '?role=' . $role : ''))->with('success', 'User deleted successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to delete user.');
        }
    }

    public function resetPassword($id = null)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $newPassword = (string) $this->request->getPost('password');

        if (empty($newPassword)) {
            return redirect()->back()->with('error', 'Please enter a new password.');
        }

        if (strlen($newPassword) < 5) {
            return redirect()->back()->with('error', 'Password must be at least 5 characters long.');
        }

        if ($this->userModel->update($id, ['password' => $newPassword])) {
            return redirect()->to(base_url('users') . ($user['role'] ? '?role=' . $user['role'] : ''))->with('success', "Password for <strong>{$user['username']}</strong> has been reset successfully.");
        } else {
            return redirect()->back()->with('error', 'Failed to reset password.');
        }
    }

    /**
     * Issue a one-time recovery code for an account. The plaintext code is
     * flashed to the next page exactly once (for the admin to write down or
     * hand over); only its bcrypt hash is stored, and it is burned after a
     * successful reset on the Forgot Password page.
     */
    public function generateRecoveryCode($id = null)
    {
        $user = $this->userModel->find($id);

        if (! $user) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'User not found.']);
            }
            return redirect()->to(base_url('users'))->with('error', 'User not found.');
        }

        // 12 hex characters in three readable groups = 48 bits of entropy.
        // The Forgot Password page is rate-limited on top of that, so blind
        // guessing is hopeless.
        $code       = strtoupper(implode('-', str_split(bin2hex(random_bytes(6)), 4)));
        $normalized = $this->normalizeRecoveryCode($code);

        if (! $this->userModel->update($id, [
            'recovery_code_hash'   => password_hash($normalized, PASSWORD_DEFAULT),
            'recovery_code_set_at' => date('Y-m-d H:i:s'),
        ])) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to generate a recovery code.']);
            }
            return redirect()->to(base_url('users'))->with('error', 'Failed to generate a recovery code.');
        }

        log_message('info', 'Recovery code generated for user {id} by admin {admin}.', [
            'id'    => $user['id'],
            'admin' => session()->get('id'),
        ]);

        // AJAX: return the code directly in the response (shown in-modal).
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success'  => true,
                'username' => $user['username'],
                'code'     => $code,
            ]);
        }

        // Fallback: flash-based redirect for non-JS browsers.
        session()->setFlashdata('recovery_code_once', [
            'username' => $user['username'],
            'code'     => $code,
        ]);

        return redirect()->to(base_url('users') . ($user['role'] ? '?role=' . $user['role'] : ''))
            ->with('success', 'A new recovery code was generated for ' . $user['username'] . '. It is shown once on this page.');
    }

    /**
     * Upper-case, dashless form used for hashing and comparisons, so a code
     * is accepted whether it is typed with or without the dashes.
     */
    private function normalizeRecoveryCode(string $code): string
    {
        return strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $code));
    }

    private function accountFieldsFromPost(): array
    {
        return [
            'username'        => trim((string) $this->request->getPost('username')),
            'firstname'       => trim((string) $this->request->getPost('firstname')),
            'lastname'        => trim((string) $this->request->getPost('lastname')),
            'middle_initial'  => trim((string) $this->request->getPost('middle_initial')),
            'age'             => (int) $this->request->getPost('age'),
            'birthdate'       => $this->request->getPost('birthdate'),
            'address'         => trim((string) $this->request->getPost('address')),
            'license_number'  => trim((string) $this->request->getPost('license_number')),
            'email'           => trim((string) $this->request->getPost('email')),
            'assigned_station' => trim((string) $this->request->getPost('assigned_station')),
        ];
    }
}
