<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuditLogFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $incoming = service('request');
        $method = strtoupper((string) $incoming->getMethod());
        if (! in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return null;
        }

        if (! session('is_logged_in')) {
            return null;
        }

        $path = trim((string) $incoming->getUri()->getPath(), '/');
        if ($path === '' || str_starts_with($path, 'assets/')) {
            return null;
        }

        $payload = $incoming->getPost();
        if (! is_array($payload) || $payload === []) {
            $json = $incoming->getJSON(true);
            if (is_array($json)) {
                $payload = $json;
            }
        }

        $payload = is_array($payload) ? $payload : [];

        service('auditLogService')->log([
            'user_id'      => (int) session('user_id'),
            'user_email'   => (string) session('user_email'),
            'http_method'  => $method,
            'request_path' => '/' . $path,
            'action_label' => $this->buildActionLabel($path, $method),
            'status_code'  => (int) $response->getStatusCode(),
            'ip_address'   => (string) $incoming->getIPAddress(),
            'user_agent'   => (string) $incoming->getUserAgent(),
            'payload'      => $payload,
        ]);

        return null;
    }

    private function buildActionLabel(string $path, string $method): string
    {
        $segments = explode('/', trim($path, '/'));
        $normalizedPath = implode('/', $segments);

        $exact = [
            'app/login|POST' => 'Sign In',
            'app/logout|POST' => 'Sign Out',
            'app/rbac/role-permissions|POST' => 'Update Role Permissions',
            'app/rbac/user-roles|POST' => 'Assign Role to User',
            'app/rbac/roles|POST' => 'Create Role',
            'app/rbac/permissions|POST' => 'Create Permission',
            'app/forgot-password|POST' => 'Request Password Reset',
            'app/reset-password|POST' => 'Reset Password',
        ];

        $exactKey = $normalizedPath . '|' . $method;
        if (isset($exact[$exactKey])) {
            return $exact[$exactKey];
        }

        $resourceLabel = $this->resolveResourceLabel($segments);
        $actionSegment = strtolower((string) ($segments[2] ?? ''));
        $verb = $this->resolveVerb($method, $actionSegment);

        return trim($verb . ' ' . $resourceLabel);
    }

    private function resolveVerb(string $method, string $actionSegment): string
    {
        if ($method === 'DELETE') {
            return 'Delete';
        }

        if ($method === 'PUT' || $method === 'PATCH') {
            return 'Update';
        }

        if ($method === 'POST') {
            if ($actionSegment === 'delete') {
                return 'Delete';
            }
            if ($actionSegment === 'update' || $actionSegment === 'status') {
                return 'Update';
            }
            if ($actionSegment === 'assign') {
                return 'Assign';
            }

            return 'Create';
        }

        return 'Access';
    }

    private function resolveResourceLabel(array $segments): string
    {
        $resource = strtolower((string) ($segments[1] ?? $segments[0] ?? 'module'));
        $subResource = strtolower((string) ($segments[2] ?? ''));

        $resourceMap = [
            'users' => 'User',
            'pilgrims' => 'Pilgrim',
            'bookings' => 'Booking',
            'payments' => 'Payment',
            'branches' => 'Branch',
            'agents' => 'Agent',
            'packages' => 'Package',
            'visas' => 'Visa',
            'reports' => 'Report',
            'rbac' => 'Access Control',
            'audit' => 'Audit Log',
            'ops' => 'Operation',
            'login' => 'Session',
        ];

        if ($resource === 'ops' && $subResource !== '') {
            return $this->titleFromSlug($subResource);
        }

        if (isset($resourceMap[$resource])) {
            return $resourceMap[$resource];
        }

        return $this->titleFromSlug($resource);
    }

    private function titleFromSlug(string $slug): string
    {
        $slug = trim($slug);
        if ($slug === '') {
            return 'Module';
        }

        $slug = str_replace(['-', '_'], ' ', $slug);

        return ucwords($slug);
    }
}
