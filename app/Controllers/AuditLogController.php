<?php

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\UserModel;

class AuditLogController extends BaseController
{
    public function index(): string
    {
        $filters = [
            'user_id'     => (string) $this->request->getGet('user_id'),
            'http_method' => strtoupper((string) $this->request->getGet('http_method')),
            'status_code' => (string) $this->request->getGet('status_code'),
            'path'        => trim((string) $this->request->getGet('path')),
            'from_date'   => (string) $this->request->getGet('from_date'),
            'to_date'     => (string) $this->request->getGet('to_date'),
        ];

        if (! in_array($filters['http_method'], ['', 'POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $filters['http_method'] = '';
        }

        $model = new AuditLogModel();
        $query = $model->orderBy('id', 'DESC');

        if ($filters['user_id'] !== '' && ctype_digit($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }
        if ($filters['http_method'] !== '') {
            $query->where('http_method', $filters['http_method']);
        }
        if ($filters['status_code'] !== '' && ctype_digit($filters['status_code'])) {
            $query->where('status_code', (int) $filters['status_code']);
        }
        if ($filters['path'] !== '') {
            $query->like('request_path', $filters['path']);
        }
        if ($filters['from_date'] !== '') {
            $query->where('DATE(created_at) >=', $filters['from_date']);
        }
        if ($filters['to_date'] !== '') {
            $query->where('DATE(created_at) <=', $filters['to_date']);
        }

        $rows = $query->findAll(1000);

        $users = (new UserModel())
            ->select('id, name, email')
            ->orderBy('name', 'ASC')
            ->findAll();

        return view('portal/audit/index', [
            'title'       => 'HJMS ERP | Audit Logs',
            'headerTitle' => 'Audit Log',
            'activePage'  => 'audit',
            'userEmail'   => (string) session('user_email'),
            'rows'        => $rows,
            'users'       => $users,
            'filters'     => $filters,
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
        ]);
    }
}
