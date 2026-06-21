<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MainCompanyModel;

class MainCompanyController extends BaseController
{
    public function index(): string
    {
        return view('portal/main_company/index', $this->mainCompanyViewData([
            'title' => 'HJMS ERP | Main Company',
            'headerTitle' => 'Main Company Settings',
            'activePage' => 'main-company',
        ]));
    }

    public function settings(): string
    {
        return view('portal/main_company/settings', $this->mainCompanyViewData([
            'title' => 'HJMS ERP | Voucher Settings',
            'headerTitle' => 'Main Company Settings',
            'activePage' => 'main-company',
        ]));
    }

    public function update()
    {
        $redirectPath = $this->request->getPost('redirect_to') === 'settings'
            ? '/main-company/settings'
            : '/main-company';

        $payload = [
            'name' => trim((string) $this->request->getPost('name')),
            'tagline' => trim((string) $this->request->getPost('tagline')),
            'address' => trim((string) $this->request->getPost('address')),
            'phone' => trim((string) $this->request->getPost('phone')),
            'email' => trim((string) $this->request->getPost('email')),
            'website' => trim((string) $this->request->getPost('website')),
            'ntn' => trim((string) $this->request->getPost('ntn')),
            'strn' => trim((string) $this->request->getPost('strn')),
        ];

        if (! $this->validateData($payload, [
            'name' => 'permit_empty|min_length[3]|max_length[160]',
            'tagline' => 'permit_empty|max_length[255]',
            'address' => 'permit_empty|max_length[5000]',
            'phone' => 'permit_empty|max_length[60]',
            'email' => 'permit_empty|valid_email|max_length[160]',
            'website' => 'permit_empty|max_length[160]',
            'ntn' => 'permit_empty|max_length[80]',
            'strn' => 'permit_empty|max_length[80]',
        ])) {
            return redirect()->to($redirectPath)->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $model = new MainCompanyModel();
            $logoPath = $this->storeLogoUpload('logo_file', 'main-company');
            $db = db_connect();

            $existing = $model->orderBy('id', 'ASC')->first();
            $now = date('Y-m-d H:i:s');
            $existingName = trim((string) ($existing['name'] ?? ''));
            $resolvedName = $payload['name'] !== '' ? $payload['name'] : ($existingName !== '' ? $existingName : 'KARWAN-E-TAIF PVT LTD');
            $data = [
                'name' => $resolvedName,
                'tagline' => $payload['tagline'] !== '' ? $payload['tagline'] : null,
                'address' => $payload['address'] !== '' ? $payload['address'] : null,
                'phone' => $payload['phone'] !== '' ? $payload['phone'] : null,
                'email' => $payload['email'] !== '' ? $payload['email'] : null,
                'website' => $payload['website'] !== '' ? $payload['website'] : null,
                'ntn' => $payload['ntn'] !== '' ? $payload['ntn'] : null,
                'strn' => $payload['strn'] !== '' ? $payload['strn'] : null,
                'updated_at' => $now,
            ];

            if ($logoPath !== null) {
                $data['logo_url'] = $logoPath;
            }

            $db->transStart();
            if ($existing === null) {
                $model->insert($data + ['created_at' => $now]);
            } else {
                $model->update((int) $existing['id'], $data);
            }
            $db->transComplete();

            if (! $db->transStatus()) {
                throw new \RuntimeException('Failed to update main company settings.');
            }

            return redirect()->to($redirectPath)->with('success', 'Main company updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to($redirectPath)->withInput()->with('error', $e->getMessage());
        }
    }

    private function mainCompanyViewData(array $overrides = []): array
    {
        $model = new MainCompanyModel();
        $row = $model->orderBy('id', 'ASC')->first();

        return $overrides + [
            'userEmail' => (string) session('user_email'),
            'row' => is_array($row) ? $row : main_company(),
            'companies' => company_table_ready()
                ? db_connect()->table('companies')->orderBy('name', 'ASC')->get()->getResultArray()
                : [],
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
            'errors' => session()->getFlashdata('errors') ?: [],
        ];
    }

    private function storeLogoUpload(string $fieldName, string $prefix)
    {
        $file = $this->request->getFile($fieldName);
        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (! $file->isValid()) {
            throw new \RuntimeException('Invalid logo upload: ' . $file->getErrorString());
        }

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $extension = strtolower((string) $file->getExtension());
        if (! in_array($extension, $allowed, true)) {
            throw new \RuntimeException('Logo must be a JPG, PNG, or WEBP image.');
        }

        $maxSizeBytes = 2 * 1024 * 1024;
        if ($file->getSize() > $maxSizeBytes) {
            throw new \RuntimeException('Logo image must be 2MB or smaller.');
        }

        $uploadDir = rtrim((string) FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'logos';
        if (! is_dir($uploadDir) && ! @mkdir($uploadDir, 0775, true) && ! is_dir($uploadDir)) {
            throw new \RuntimeException('Could not create logo upload directory.');
        }

        $newName = $prefix . '-' . date('YmdHis') . '-' . mt_rand(1000, 9999) . '.' . $extension;
        $file->move($uploadDir, $newName, true);

        return base_url('assets/uploads/logos/' . $newName);
    }
}
