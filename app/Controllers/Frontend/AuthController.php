<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\FrontendUserModel;

class AuthController extends BaseController
{
    public function showLogin()
    {
        if ($this->isFrontendAuthenticated()) {
            if ((string) session('role') === 'agent') {
                return redirect()->to('/portal/agent/dashboard');
            }

            return redirect()->to('/portal/dashboard');
        }

        return view('frontend/auth/login', [
            'title' => 'Login | HJMS Travel',
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function login()
    {
        $payload = [
            'email' => (string) $this->request->getPost('email'),
            'password' => (string) $this->request->getPost('password'),
        ];

        if (! $this->validateData($payload, [
            'email' => 'required|valid_email',
            'password' => 'required|min_length[8]',
        ])) {
            return redirect()->back()->withInput()->with('error', 'Please provide valid login credentials.');
        }

        $user = (new FrontendUserModel())
            ->where('email', strtolower(trim($payload['email'])))
            ->where('status', 'active')
            ->first();

        if ($user === null || ! password_verify($payload['password'], (string) ($user['password'] ?? ''))) {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }

        $role = strtolower((string) ($user['role'] ?? 'customer'));
        if (! in_array($role, ['customer', 'agent'], true)) {
            return redirect()->back()->withInput()->with('error', 'Account role is invalid. Please contact support.');
        }

        session()->set([
            'user_id' => (int) $user['id'],
            'role' => $role,
            'name' => (string) ($user['name'] ?? ''),
            'frontend_authenticated' => true,
        ]);

        if ($role === 'agent') {
            return redirect()->to('/portal/agent/dashboard');
        }

        return redirect()->to('/portal/dashboard');
    }

    public function showRegister()
    {
        if ($this->isFrontendAuthenticated()) {
            if ((string) session('role') === 'agent') {
                return redirect()->to('/portal/agent/dashboard');
            }

            return redirect()->to('/portal/dashboard');
        }

        return view('frontend/auth/register', [
            'title' => 'Register | HJMS Travel',
            'error' => session()->getFlashdata('error'),
            'errors' => session()->getFlashdata('errors') ?? [],
            'success' => session()->getFlashdata('success'),
        ]);
    }

    public function register()
    {
        $role = strtolower(trim((string) $this->request->getPost('role')));
        if (! in_array($role, ['customer', 'agent'], true)) {
            return redirect()->back()->withInput()->with('error', 'Please choose a valid role.');
        }

        $rules = [
            'role' => 'required|in_list[customer,agent]',
            'email' => 'required|valid_email|max_length[191]|is_unique[frontend_users.email]',
            'password' => 'required|min_length[8]',
            'confirm_password' => 'required|matches[password]',
            'phone' => 'permit_empty|max_length[40]',
        ];

        $payload = [
            'role' => $role,
            'email' => trim((string) $this->request->getPost('email')),
            'password' => (string) $this->request->getPost('password'),
            'confirm_password' => (string) $this->request->getPost('confirm_password'),
            'phone' => trim((string) $this->request->getPost('phone')),
            'name' => trim((string) $this->request->getPost('name')),
            'company_name' => trim((string) $this->request->getPost('company_name')),
            'license_number' => trim((string) $this->request->getPost('license_number')),
            'contact_person' => trim((string) $this->request->getPost('contact_person')),
        ];

        if ($role === 'agent') {
            $rules['company_name'] = 'required|max_length[191]';
            $rules['license_number'] = 'required|max_length[120]';
            $rules['contact_person'] = 'required|max_length[160]';
            $payload['name'] = $payload['contact_person'];
        } else {
            $rules['name'] = 'required|max_length[160]';
        }

        if (! $this->validateData($payload, $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        (new FrontendUserModel())->insert([
            'role' => $role,
            'name' => $payload['name'],
            'email' => strtolower($payload['email']),
            'password' => password_hash($payload['password'], PASSWORD_DEFAULT),
            'company_name' => $role === 'agent' ? $payload['company_name'] : null,
            'license_number' => $role === 'agent' ? $payload['license_number'] : null,
            'phone' => $payload['phone'] !== '' ? $payload['phone'] : null,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/login')->with('success', 'Registration completed. Please login.');
    }

    public function logout()
    {
        session()->remove(['user_id', 'role', 'name', 'frontend_authenticated']);
        session()->regenerate(true);

        return redirect()->to('/login')->with('success', 'You have been logged out.');
    }

    private function isFrontendAuthenticated(): bool
    {
        $role = strtolower((string) session('role'));

        return (int) session('user_id') > 0
            && in_array($role, ['customer', 'agent'], true)
            && (bool) session('frontend_authenticated') === true;
    }
}
