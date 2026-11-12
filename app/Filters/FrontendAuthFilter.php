<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class FrontendAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $role = strtolower((string) session('role'));
        $isAuthenticated = (int) session('user_id') > 0
            && in_array($role, ['customer', 'agent'], true)
            && (bool) session('frontend_authenticated') === true;

        if (! $isAuthenticated) {
            return redirect()->to('/login')
                ->with('error', 'Please login to continue.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
