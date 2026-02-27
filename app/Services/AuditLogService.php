<?php

namespace App\Services;

use App\Models\AuditLogModel;

class AuditLogService
{
    private $sensitiveKeys = [
        'password',
        'confirm_password',
        'password_confirmation',
        'token',
        'token_hash',
        'authorization',
        'csrf_token',
    ];

    public function log(array $data): void
    {
        try {
            $payload = $data['payload'] ?? null;
            $sanitizedPayload = $this->sanitizePayload($payload);

            (new AuditLogModel())->insert([
                'user_id'      => isset($data['user_id']) ? (int) $data['user_id'] : null,
                'user_email'   => isset($data['user_email']) && $data['user_email'] !== '' ? (string) $data['user_email'] : null,
                'http_method'  => strtoupper((string) ($data['http_method'] ?? 'GET')),
                'request_path' => (string) ($data['request_path'] ?? ''),
                'action_label' => isset($data['action_label']) && $data['action_label'] !== '' ? (string) $data['action_label'] : null,
                'status_code'  => (int) ($data['status_code'] ?? 200),
                'ip_address'   => isset($data['ip_address']) && $data['ip_address'] !== '' ? (string) $data['ip_address'] : null,
                'user_agent'   => isset($data['user_agent']) && $data['user_agent'] !== '' ? (string) $data['user_agent'] : null,
                'payload_json' => $sanitizedPayload !== null ? json_encode($sanitizedPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
        }
    }

    private function sanitizePayload($payload)
    {
        if ($payload === null) {
            return null;
        }

        if (is_scalar($payload)) {
            return $this->truncate((string) $payload);
        }

        if (! is_array($payload)) {
            return $this->truncate((string) json_encode($payload));
        }

        $clean = [];
        foreach ($payload as $key => $value) {
            $normalizedKey = strtolower((string) $key);
            if (in_array($normalizedKey, $this->sensitiveKeys, true)) {
                $clean[$key] = '***';
                continue;
            }

            if (is_array($value)) {
                $clean[$key] = $this->sanitizePayload($value);
                continue;
            }

            if ($value === null) {
                $clean[$key] = null;
                continue;
            }

            if (is_scalar($value)) {
                $clean[$key] = $this->truncate((string) $value);
                continue;
            }

            $clean[$key] = $this->truncate((string) json_encode($value));
        }

        return $clean;
    }

    private function truncate(string $value): string
    {
        if (mb_strlen($value) <= 1000) {
            return $value;
        }

        return mb_substr($value, 0, 1000) . '...';
    }
}
