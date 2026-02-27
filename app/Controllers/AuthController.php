<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class AuthController extends BaseController
{
    public function loginForm()
    {
        if (session('is_logged_in')) {
            if (auth_is_super_admin() || auth_can('dashboard.view')) {
                return redirect()->to('/app');
            }

            service('authService')->logout();

            return redirect()->to('/app/login')->with('error', 'Your session is missing required permissions. Please sign in again.');
        }

        return view('portal/auth/index', [
            'title' => 'HJMS ERP | Login',
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function unauthorized()
    {
        return view('portal/auth/unauthorized', [
            'title' => 'HJMS ERP | Unauthorized',
            'headerTitle' => 'Unauthorized',
            'activePage' => '',
            'userEmail' => (string) session('user_email'),
            'error' => session()->getFlashdata('error') ?: 'You do not have permission to access this section.',
        ]);
    }

    public function loginSubmit()
    {
        $payload = [
            'email'    => (string) $this->request->getPost('email'),
            'password' => (string) $this->request->getPost('password'),
        ];

        if (! $this->validateData($payload, [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ])) {
            return redirect()->back()->withInput()->with('error', 'Invalid login input.');
        }

        try {
            service('authService')->login($payload['email'], $payload['password']);

            return redirect()->to('/app');
        } catch (\DomainException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function logout()
    {
        service('authService')->logout();

        return redirect()->to('/app/login');
    }

    public function forgotPasswordForm()
    {
        return view('portal/auth/forgot_password', [
            'title' => 'HJMS ERP | Forgot Password',
            'error' => session()->getFlashdata('error'),
            'success' => session()->getFlashdata('success'),
            'resetLink' => session()->getFlashdata('reset_link'),
        ]);
    }

    public function forgotPasswordSubmit()
    {
        $payload = [
            'email' => (string) $this->request->getPost('email'),
        ];

        if (! $this->validateData($payload, [
            'email' => 'required|valid_email',
        ])) {
            return redirect()->back()->withInput()->with('error', 'Invalid reset request input.');
        }

        $token = service('authService')->requestPasswordReset($payload['email']);

        if ($token === null) {
            return redirect()->back()->withInput()->with('success', 'If the account exists, a reset link has been generated.');
        }

        $resetLink = site_url('/app/reset-password?token=' . urlencode($token));

        return redirect()->back()->with('success', 'Reset link generated.')->with('reset_link', $resetLink);
    }

    public function resetPasswordForm()
    {
        return view('portal/auth/reset_password', [
            'title' => 'HJMS ERP | Reset Password',
            'token' => (string) ($this->request->getGet('token') ?? ''),
            'error' => session()->getFlashdata('error'),
            'success' => session()->getFlashdata('success'),
        ]);
    }

    public function resetPasswordSubmit()
    {
        $payload = [
            'token' => (string) $this->request->getPost('token'),
            'password' => (string) $this->request->getPost('password'),
            'confirm_password' => (string) $this->request->getPost('confirm_password'),
        ];

        if (! $this->validateData($payload, [
            'token' => 'required|min_length[32]',
            'password' => 'required|min_length[8]',
            'confirm_password' => 'required|matches[password]',
        ])) {
            return redirect()->back()->withInput()->with('error', 'Invalid reset payload.');
        }

        $ok = service('authService')->resetPasswordByToken($payload['token'], $payload['password']);
        if (! $ok) {
            return redirect()->back()->withInput()->with('error', 'Reset token is invalid or expired.');
        }

        return redirect()->to('/app/login')->with('success', 'Password reset complete. You can now sign in.');
    }
}
