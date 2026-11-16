<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\ViolatorRecord;

class UserController extends BaseController
{
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

        // Counts first: countAllResults() resets the shared builder state, so
        // the role filter for the paginated list must be applied afterwards.
        $stats = [
            'total'    => $this->userModel->whereIn('role', $allowedRoles)->countAllResults(),
            'admin'    => $this->userModel->where('role', 'admin')->countAllResults(),
            'enforcer' => $this->userModel->where('role', 'enforcer')->countAllResults(),
        ];

        if ($role !== '') {
            $this->userModel->where('role', $role);
        } else {
            $this->userModel->whereIn('role', $allowedRoles);
        }

        $data = [
            'title'        => 'User Management',
            'selectedRole' => $role,
            'stats'        => $stats,
            'users'        => $this->userModel->paginate(10, 'users'),
            'pager'        => $this->userModel->pager,
        ];
        return view('admin/users/index', $data);
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

        // Group individual infractions by citation / ticket_id so that multiple
        // violations under the same citation are organized together in one ticket row.
        $tickets = [];
        foreach ($records as $record) {
            $tId = trim((string) ($record['ticket_id'] ?? ''));
            $groupKey = $tId !== '' ? $tId : ('single_' . $record['id']);

            if (! isset($tickets[$groupKey])) {
                $tickets[$groupKey] = [
                    'id'             => $record['id'],
                    'ticket_id'      => $record['ticket_id'] ?? 'N/A',
                    'violator_id'    => $record['violator_id'] ?? 0,
                    'first_name'     => $record['first_name'] ?? '',
                    'last_name'      => $record['last_name'] ?? '',
                    'driver_name'    => $record['driver_name'] ?? '',
                    'license_plate'  => $record['license_plate'] ?? '',
                    'status'         => $record['status'] ?? 'Pending',
                    'violation_date' => $record['violation_date'] ?? null,
                    'created_at'     => $record['created_at'] ?? null,
                    'total_amount'   => 0.0,
                    'infractions'    => [],
                ];
            }

            $tickets[$groupKey]['total_amount'] += (float) ($record['penalty_amount'] ?? 0);
            $tickets[$groupKey]['infractions'][] = [
                'id'             => $record['id'],
                'violation_type' => $record['violation_type'] ?? '-',
                'penalty_amount' => (float) ($record['penalty_amount'] ?? 0),
                'status'         => $record['status'] ?? 'Pending',
            ];

            // If any infraction is Pending, the citation status is Pending
            if (($record['status'] ?? '') === 'Pending') {
                $tickets[$groupKey]['status'] = 'Pending';
            }
        }
        $tickets = array_values($tickets);

        $data = [
            'title'               => 'Traffic Enforcer Profile: ' . $user['username'],
            'user'                => $user,
            'records'             => $records,
            'tickets'             => $tickets,
            'totalTickets'        => count($tickets),
            'totalInfractions'    => count($records),
            'pendingCount'        => count(array_filter($tickets, static fn($t) => ($t['status'] ?? '') === 'Pending')),
            'paidCount'           => count(array_filter($tickets, static fn($t) => ($t['status'] ?? '') === 'Paid')),
            'cancelledCount'      => count(array_filter($tickets, static fn($t) => ($t['status'] ?? '') === 'Cancelled')),
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
