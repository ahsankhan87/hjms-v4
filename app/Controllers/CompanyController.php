<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CompanyModel;

class CompanyController extends BaseController
{
    public function index(): string
    {
        $model = new CompanyModel();

        return view('portal/companies/index', [
            'title'       => 'HJMS ERP | Companies',
            'headerTitle' => 'Company Management',
            'activePage'  => 'companies',
            'userEmail'   => (string) session('user_email'),
            'rows'        => $model->orderBy('id', 'DESC')->findAll(),
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function createCompany()
    {
        $payload = [
            'name' => trim((string) $this->request->getPost('name')),
            'tagline' => trim((string) $this->request->getPost('tagline')),
            'address' => trim((string) $this->request->getPost('address')),
            'phone' => trim((string) $this->request->getPost('phone')),
            'email' => trim((string) $this->request->getPost('email')),
            'website' => trim((string) $this->request->getPost('website')),
            'ntn' => trim((string) $this->request->getPost('ntn')),
            'strn' => trim((string) $this->request->getPost('strn')),
            'saudi_partner' => trim((string) $this->request->getPost('saudi_partner')),
            'is_active' => (string) ($this->request->getPost('is_active') ?? '1'),
        ];

        if (! $this->validateData($payload, [
            'name' => 'required|min_length[3]|max_length[160]',
            'tagline' => 'permit_empty|max_length[255]',
            'address' => 'permit_empty|max_length[5000]',
            'phone' => 'permit_empty|max_length[60]',
            'email' => 'permit_empty|valid_email|max_length[160]',
            'website' => 'permit_empty|max_length[160]',
            'ntn' => 'permit_empty|max_length[80]',
            'strn' => 'permit_empty|max_length[80]',
            'saudi_partner' => 'permit_empty|max_length[255]',
            'is_active' => 'required|in_list[0,1]',
        ])) {
            return redirect()->to('/companies')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $model = new CompanyModel();
            $db = db_connect();
            $logoPath = $this->storeLogoUpload('logo_file', 'shirka');

            $db->transStart();
            if ((int) $payload['is_active'] === 1) {
                $db->table('companies')->set(['is_active' => 0, 'updated_at' => date('Y-m-d H:i:s')])->update();
            }

            $model->insert([
                'name' => $payload['name'],
                'tagline' => $payload['tagline'] !== '' ? $payload['tagline'] : null,
                'address' => $payload['address'] !== '' ? $payload['address'] : null,
                'phone' => $payload['phone'] !== '' ? $payload['phone'] : null,
                'email' => $payload['email'] !== '' ? $payload['email'] : null,
                'website' => $payload['website'] !== '' ? $payload['website'] : null,
                'logo_url' => $logoPath,
                'ntn' => $payload['ntn'] !== '' ? $payload['ntn'] : null,
                'strn' => $payload['strn'] !== '' ? $payload['strn'] : null,
                'saudi_partner' => $payload['saudi_partner'] !== '' ? $payload['saudi_partner'] : null,
                'is_active' => (int) $payload['is_active'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $db->transComplete();

            if (! $db->transStatus()) {
                throw new \RuntimeException('Failed to create company.');
            }

            return redirect()->to('/companies')->with('success', 'Company created successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/companies')->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateCompany()
    {
        $companyId = (int) $this->request->getPost('company_id');
        $payload = [
            'name' => trim((string) $this->request->getPost('name')),
            'tagline' => trim((string) $this->request->getPost('tagline')),
            'address' => trim((string) $this->request->getPost('address')),
            'phone' => trim((string) $this->request->getPost('phone')),
            'email' => trim((string) $this->request->getPost('email')),
            'website' => trim((string) $this->request->getPost('website')),
            'ntn' => trim((string) $this->request->getPost('ntn')),
            'strn' => trim((string) $this->request->getPost('strn')),
            'saudi_partner' => trim((string) $this->request->getPost('saudi_partner')),
            'is_active' => (string) $this->request->getPost('is_active'),
        ];

        if ($companyId < 1) {
            return redirect()->to('/companies')->withInput()->with('error', 'Valid company ID is required.');
        }

        if (! $this->validateData($payload, [
            'name' => 'permit_empty|min_length[3]|max_length[160]',
            'tagline' => 'permit_empty|max_length[255]',
            'address' => 'permit_empty|max_length[5000]',
            'phone' => 'permit_empty|max_length[60]',
            'email' => 'permit_empty|valid_email|max_length[160]',
            'website' => 'permit_empty|max_length[160]',
            'ntn' => 'permit_empty|max_length[80]',
            'strn' => 'permit_empty|max_length[80]',
            'saudi_partner' => 'permit_empty|max_length[255]',
            'is_active' => 'permit_empty|in_list[0,1]',
        ])) {
            return redirect()->to('/companies')->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [];
        foreach ($payload as $field => $value) {
            if ($value === '') {
                continue;
            }
            $data[$field] = $value;
        }

        if ($data === []) {
            $logoFile = $this->request->getFile('logo_file');
            $hasLogoUpload = $logoFile !== null && $logoFile->getError() !== UPLOAD_ERR_NO_FILE;
            if (! $hasLogoUpload) {
                return redirect()->to('/companies')->withInput()->with('error', 'Provide at least one field to update for company.');
            }
        }

        if (isset($data['is_active'])) {
            $data['is_active'] = (int) $data['is_active'];
        }

        try {
            $model = new CompanyModel();
            $db = db_connect();
            $logoPath = $this->storeLogoUpload('logo_file', 'shirka');

            $existing = $model->where('id', $companyId)->first();
            if ($existing === null) {
                return redirect()->to('/companies')->with('error', 'Company not found.');
            }

            if ($logoPath !== null) {
                $data['logo_url'] = $logoPath;
            }

            $db->transStart();
            if (($data['is_active'] ?? 0) === 1) {
                $db->table('companies')->set(['is_active' => 0, 'updated_at' => date('Y-m-d H:i:s')])->update();
            }

            $model->update($companyId, $data + ['updated_at' => date('Y-m-d H:i:s')]);
            $db->transComplete();

            if (! $db->transStatus()) {
                throw new \RuntimeException('Failed to update company.');
            }

            return redirect()->to('/companies')->with('success', 'Company updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/companies')->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteCompany()
    {
        $companyId = (int) $this->request->getPost('company_id');
        if ($companyId < 1) {
            return redirect()->to('/companies')->with('error', 'Valid company ID is required for delete.');
        }

        try {
            $model = new CompanyModel();
            $existing = $model->where('id', $companyId)->first();

            if ($existing === null) {
                return redirect()->to('/companies')->with('error', 'Company not found or already removed.');
            }

            $model->delete($companyId);

            if ((int) ($existing['is_active'] ?? 0) === 1) {
                $latest = $model->orderBy('id', 'DESC')->first();
                if ($latest !== null) {
                    $model->update((int) $latest['id'], [
                        'is_active' => 1,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            return redirect()->to('/companies')->with('success', 'Company deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/companies')->with('error', $e->getMessage());
        }
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
