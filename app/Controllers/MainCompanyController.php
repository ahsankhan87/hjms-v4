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
            'voucher_instructions_ur' => trim((string) $this->request->getPost('voucher_instructions_ur')),
            'voucher_instructions_en' => trim((string) $this->request->getPost('voucher_instructions_en')),
            'makkah_contact' => trim((string) $this->request->getPost('makkah_contact')),
            'makkah_contact_en' => trim((string) $this->request->getPost('makkah_contact_en')),
            'madina_contact' => trim((string) $this->request->getPost('madina_contact')),
            'madina_contact_en' => trim((string) $this->request->getPost('madina_contact_en')),
            'transport_contact' => trim((string) $this->request->getPost('transport_contact')),
            'transport_contact_en' => trim((string) $this->request->getPost('transport_contact_en')),
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
            'voucher_instructions_ur' => 'permit_empty|max_length[12000]',
            'voucher_instructions_en' => 'permit_empty|max_length[12000]',
            'makkah_contact' => 'permit_empty|max_length[255]',
            'makkah_contact_en' => 'permit_empty|max_length[255]',
            'madina_contact' => 'permit_empty|max_length[255]',
            'madina_contact_en' => 'permit_empty|max_length[255]',
            'transport_contact' => 'permit_empty|max_length[255]',
            'transport_contact_en' => 'permit_empty|max_length[255]',
        ])) {
            return redirect()->to($redirectPath)->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $model = new MainCompanyModel();
            $db = db_connect();
            $logoPath = $this->storeLogoUpload('logo_file', 'main-company');

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
                'voucher_instructions_ur' => $payload['voucher_instructions_ur'] !== '' ? $payload['voucher_instructions_ur'] : null,
                'voucher_instructions_en' => $payload['voucher_instructions_en'] !== '' ? $payload['voucher_instructions_en'] : null,
                'makkah_contact' => $payload['makkah_contact'] !== '' ? $payload['makkah_contact'] : null,
                'makkah_contact_en' => $payload['makkah_contact_en'] !== '' ? $payload['makkah_contact_en'] : null,
                'madina_contact' => $payload['madina_contact'] !== '' ? $payload['madina_contact'] : null,
                'madina_contact_en' => $payload['madina_contact_en'] !== '' ? $payload['madina_contact_en'] : null,
                'transport_contact' => $payload['transport_contact'] !== '' ? $payload['transport_contact'] : null,
                'transport_contact_en' => $payload['transport_contact_en'] !== '' ? $payload['transport_contact_en'] : null,
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
