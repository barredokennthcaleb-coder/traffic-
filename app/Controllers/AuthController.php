<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return $this->redirectByRole((string) session()->get('role'));
        }
        return view('auth/login');
    }

    public function loginPost()
    {
        $session = session();
        $model = new UserModel();

        $email = trim($this->request->getVar('email'));
        $password = $this->request->getVar('password');

        if (empty($email) || empty($password)) {
            $session->setFlashdata('error', 'Please fill in all fields.');
            return redirect()->back()->withInput();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $session->setFlashdata('error', 'Please enter a valid email address.');
            return redirect()->back()->withInput();
        }

        // Throttle sign-in attempts per IP + email so the public form cannot
        // be used to brute-force a password, mirroring the limiter the
        // forgot-password endpoint already uses. Ten attempts per five
        // minutes leaves room for honest typos, and a successful sign-in
        // clears the counter below.
        $throttler   = \Config\Services::throttler();
        $throttleKey = 'login-' . md5($this->request->getIPAddress() . '|' . strtolower($email));

        if (! $throttler->check($throttleKey, 10, MINUTE * 5)) {
            $session->setFlashdata('error', 'Too many sign-in attempts. Please wait a few minutes and try again.');
            return redirect()->back()->withInput();
        }

        $user = $model->where('email', $email)->first();

        // The main password is the normal path. A stored backup password is a
        // reusable alternative for a user who has forgotten the main one; it
        // is checked only when the main password did not match.
        $primaryOk = $user && password_verify($password, (string) ($user['password'] ?? ''));
        $backupOk  = $user && ! $primaryOk
            && ! empty($user['backup_password_hash'])
            && password_verify($password, (string) $user['backup_password_hash']);

        // A wrong email and a wrong password return the same message so the
        // form never reveals which accounts exist.
        if (! $user || (! $primaryOk && ! $backupOk)) {
            $session->setFlashdata('error', 'The password you entered is incorrect.');
            return redirect()->back()->withInput();
        }

        if ($backupOk) {
            log_message('info', 'Backup password used to sign in as user {id} from IP {ip}.', [
                'id' => $user['id'],
                'ip' => $this->request->getIPAddress(),
            ]);
        }

        // The credentials checked out: clear the attempt counter and start a
        // fresh session ID, so a cookie planted before sign-in (session
        // fixation) cannot carry over into the authenticated session.
        $throttler->remove($throttleKey);
        $session->regenerate();

        if ($user['role'] === 'enforcer') {
            $model->update($user['id'], [
                'duty_status'      => 'Active Patrol',
                'break_started_at' => null,
            ]);
        }

        // The driver pages match tickets through the violator registry, which
        // keys off the license number and the first+last name.
        $nameParts = array_filter([
            (string) ($user['firstname'] ?? ''),
            (string) ($user['middle_initial'] ?? ''),
            (string) ($user['lastname'] ?? ''),
        ], static fn ($part) => trim($part) !== '');
        $fullName = trim(preg_replace('/\s+/', ' ', implode(' ', $nameParts)));

        $sessionData = [
            'id'              => $user['id'],
            'username'        => $user['username'],
            'email'           => $user['email'],
            'role'            => $user['role'],
            'profile_image'   => $user['profile_image'] ?? null,
            'firstname'       => (string) ($user['firstname'] ?? ''),
            'middle_initial'  => (string) ($user['middle_initial'] ?? ''),
            'lastname'        => (string) ($user['lastname'] ?? ''),
            'full_name'       => $fullName,
            'license_number'  => (string) ($user['license_number'] ?? ''),
            'isLoggedIn'      => true,
        ];
        $session->set($sessionData);
        $session->setFlashdata('success', 'Welcome back!');

        // One-time "welcome" flag for the admin dashboard's KPI count-up.
        // Flashdata lives for exactly one request, so the running numbers play
        // only on the first dashboard load after sign-in. Every later visit
        // (sidebar/filters/reload) renders the final figures straight away.
        $session->setFlashdata('kpi_countup', '1');

        // Picked up by app/Views/theme/posthog.php on the next page, so the
        // session replay of this sign-in is filed under a journey event.
        $session->set('posthog_journey', ['signed_in']);

        return $this->redirectByRole((string) $user['role']);
    }

    public function register()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to(base_url('dashboard'));
        }
        return view('auth/register');
    }

    public function registerPost()
    {
        $session = session();
        $model = new UserModel();

        $rules = [
            'username' => 'required|alpha_numeric_space|min_length[3]|is_unique[users.username]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'firstname'=> 'required|min_length[2]|max_length[100]',
            'lastname' => 'required|min_length[2]|max_length[100]',
            'middle_initial' => 'permit_empty|max_length[10]',
            'age'      => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[120]',
            'birthdate'=> 'required|valid_date',
            'address'  => 'required|max_length[255]',
            'license_number' => 'permit_empty|max_length[50]',
            'password' => 'required|min_length[8]',
            'confirm_password' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            $session->setFlashdata('errors', $this->validator->getErrors());
            return redirect()->back()->withInput();
        }

        $data = [
            'username'  => trim((string) $this->request->getVar('username')),
            'email'     => trim((string) $this->request->getVar('email')),
            'firstname' => trim((string) $this->request->getVar('firstname')),
            'lastname'  => trim((string) $this->request->getVar('lastname')),
            'middle_initial' => trim((string) $this->request->getVar('middle_initial')),
            'age'       => (int) $this->request->getVar('age'),
            'birthdate' => $this->request->getVar('birthdate'),
            'address'   => trim((string) $this->request->getVar('address')),
            'license_number' => trim((string) $this->request->getVar('license_number')),
            'password'  => (string) $this->request->getVar('password'),
            'role'      => 'driver',
            'status'    => 'active'
        ];

        if ($model->save($data)) {
            $session->setFlashdata('success', 'Account created successfully! Please log in with your credentials.');
            return redirect()->to(base_url('login'));
        } else {
            $session->setFlashdata('errors', $model->errors());
            return redirect()->back()->withInput();
        }
    }

    public function logout()
    {
        $userId = session()->get('id');
        $role   = session()->get('role');
        if ($userId && $role === 'enforcer') {
            $model = new UserModel();
            $model->update($userId, [
                'duty_status'      => 'Off Duty',
                'break_started_at' => null,
            ]);
        }

        session()->destroy();
        return redirect()->to(base_url('login'))->with('success', 'You have been logged out successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // Private Helpers
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Returns the correct redirect response for a given user role.
     * Centralises the role→URL mapping so login() and loginPost() share
     * one implementation instead of copy-pasting the same if/elseif block.
     */
    private function redirectByRole(string $role): \CodeIgniter\HTTP\RedirectResponse
    {
        return match ($role) {
            'admin'           => redirect()->to(base_url('dashboard')),
            'enforcer'        => redirect()->to(base_url('officer')),
            'driver', 'user'  => redirect()->to(base_url('user/dashboard')),
            default           => (function () {
                session()->destroy();
                return redirect()->to(base_url('login'))
                    ->with('error', 'This account role is not allowed to sign in.');
            })(),
        };
    }

    public function forgotPassword()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to(base_url('dashboard'));
        }
        return view('auth/forgot_password');
    }

    public function forgotPasswordPost()
    {
        $model = new UserModel();

        $email        = strtolower(trim((string) $this->request->getVar('email')));
        $recoveryCode = trim((string) $this->request->getVar('recovery_code'));
        $new          = (string) $this->request->getVar('new_password');
        $confirm      = (string) $this->request->getVar('confirm_password');

        // Throttle recovery attempts per IP + email so the public endpoint
        // cannot be used to brute-force someone's personal details.
        $throttler   = \Config\Services::throttler();
        $throttleKey = 'recover-' . md5($this->request->getIPAddress() . '|' . $email);

        if (! $throttler->check($throttleKey, 5, MINUTE * 10)) {
            return redirect()->back()->withInput()->with('error', 'Too many recovery attempts. Please wait a few minutes and try again.');
        }

        if ($email === '' || $recoveryCode === '' || $new === '' || $confirm === '') {
            return redirect()->back()->withInput()->with('error', 'Please fill in all fields.');
        }

        $user = $model->where('email', $email)->first();

        // Two ways in: the account's own one-time recovery code, or the
        // owner-level master key from .env (break-glass for the day even the
        // last administrator is locked out). Codes are stored as bcrypt
        // hashes, the master key is compared in constant time.
        $normalized = $this->normalizeRecoveryCode($recoveryCode);
        $masterKey  = $this->normalizeRecoveryCode((string) (env('auth.masterRecoveryKey') ?? ''));

        $usedMaster = $user && $masterKey !== '' && $normalized !== '' && hash_equals($masterKey, $normalized);
        $usedCode   = $user && ! $usedMaster && $normalized !== ''
            && password_verify($normalized, (string) ($user['recovery_code_hash'] ?? ''));

        // The same message goes out whenever the details do not line up, so
        // the page never reveals whether the email exists or the code was
        // simply wrong.
        if (! $user || (! $usedMaster && ! $usedCode)) {
            return redirect()->back()->withInput()->with(
                'error',
                'We could not verify that email and recovery code. Ask the administrator for a fresh recovery code.'
            );
        }

        if ($usedMaster) {
            log_message('warning', 'Master recovery key used to reset user {id} from IP {ip}.', [
                'id' => $user['id'],
                'ip' => $this->request->getIPAddress(),
            ]);
        }

        if (strlen($new) < 8) {
            return redirect()->back()->withInput()->with('error', 'The new password must be at least 8 characters long.');
        }

        if ($new !== $confirm) {
            return redirect()->back()->withInput()->with('error', 'The new passwords do not match.');
        }

        if (password_verify($new, (string) $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Please choose a password different from your current one.');
        }

        // Everything checked out: the account owner gets a fresh password and
        // the used recovery code is burned so it can never be replayed.
        $update = ['password' => $new];
        if ($usedCode) {
            $update['recovery_code_hash']   = null;
            $update['recovery_code_set_at'] = null;
        }

        if (! $model->update($user['id'], $update)) {
            return redirect()->back()->with('error', 'The password could not be saved. Please try again.');
        }

        $throttler->remove($throttleKey);
        log_message('info', 'Account recovery: password reset for user {id} from IP {ip}.', [
            'id' => $user['id'],
            'ip' => $this->request->getIPAddress(),
        ]);

        return redirect()->to(base_url('login'))->with(
            'success',
            'Your password has been reset. Sign in with your new password.'
        );
    }

    /**
     * Upper-case, dashless form used for hashing and comparisons, so a code
     * is accepted whether it is typed with or without the dashes.
     */
    private function normalizeRecoveryCode(string $code): string
    {
        return strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $code));
    }
}