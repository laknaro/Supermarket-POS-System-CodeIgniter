<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Filters\RoleFilter;
use App\Models\UserModel;

/** index.php: login form + ?action=logout (same behaviour as the original, minus the backdoors). */
class Auth extends BaseController
{
    public function index()
    {
        if ($this->request->getGet('action') === 'logout') {
            session()->destroy();

            return redirect()->to('/');
        }

        if (session()->get('user_id')) {
            return redirect()->to(RoleFilter::homeFor((string) session()->get('role')));
        }

        $error = '';

        if ($this->request->is('post')) {
            $login    = trim((string) $this->request->getPost('username'));
            $password = (string) $this->request->getPost('password');

            if ($login === '' || $password === '') {
                $error = 'Please enter both Username/Employee ID and Password.';
            } elseif (! service('throttler')->check('login_' . md5($this->request->getIPAddress()), 10, MINUTE)) {
                $error = 'Too many login attempts. Please wait a minute and try again.';
            } else {
                $user = (new UserModel())
                    ->where('status', 'Active')
                    ->groupStart()->where('username', $login)->orWhere('employee_id', $login)->groupEnd()
                    ->first();

                // NOTE: the original also accepted the password "admin123" for ANY account and
                // auto-created admin accounts. Both were removed: only the real password works.
                if ($user && password_verify($password, (string) $user['password'])) {
                    session()->regenerate();
                    session()->set([
                        'user_id'     => (int) $user['id'],
                        'username'    => $user['username'],
                        'employee_id' => $user['employee_id'],
                        'role'        => $user['role'],
                        'branch'      => $user['branch'] ?: config('Store')->defaultBranch,
                    ]);

                    return redirect()->to(RoleFilter::homeFor($user['role']));
                }

                $error = 'Invalid Username/Employee ID or Password.';
            }
        }

        return view('index', ['error' => $error]);
    }
}
