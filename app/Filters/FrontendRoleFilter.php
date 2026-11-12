<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class FrontendRoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $requiredType = strtolower(trim((string) ($arguments[0] ?? '')));
        if ($requiredType === '') {
            return null;
        }

        $currentType = strtolower(trim((string) session('role')));
        if ($currentType !== $requiredType) {
            $redirect = $currentType === 'agent' ? '/portal/agent/dashboard' : '/portal/dashboard';

            return redirect()->to($redirect)
                ->with('error', 'You are not authorized to access this portal section.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
