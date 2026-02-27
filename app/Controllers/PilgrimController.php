<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PilgrimModel;

class PilgrimController extends BaseController
{
    const MAHRAM_OPTIONS = [
        '',
        'Grand Father',
        'Father',
        'Son',
        'Grand Son',
        'Brother',
        'Nephew',
        'Uncle',
        'Husband',
        'Father in law',
        'Son-in-law',
        'Stepfather (Mother\'s husband)',
        'Stepson (Husband\'s son)',
        'Self',
        'Women Group',
    ];

    public function index()
    {
        $q = trim((string) $this->request->getGet('q'));
        $seasonId = $this->activeSeasonId();

        if ($seasonId === null) {
            return redirect()->to('/app/seasons')->with('error', 'Please create and activate a season first.');
        }

        $rows = [];
        try {
            $db = db_connect();
            $builder = $db->table('pilgrims p')
                ->select('p.*, v.status AS latest_visa_status, v.visa_type AS latest_visa_type, v.visa_no AS latest_visa_no')
                ->join('visas v', 'v.id = (SELECT MAX(v2.id) FROM visas v2 WHERE v2.pilgrim_id = p.id AND v2.season_id = p.season_id)', 'left')
                ->where('p.season_id', $seasonId);

            if ($q !== '') {
                $builder->groupStart()
                    ->like('p.first_name', $q)
                    ->orLike('p.last_name', $q)
                    ->orLike('p.passport_no', $q)
                    ->orLike('p.cnic', $q)
                    ->orLike('p.phone', $q)
                    ->orLike('p.mobile_no', $q)
                    ->orLike('p.email', $q)
                    ->orLike('v.visa_no', $q)
                    ->groupEnd();
            }

            $rows = $builder->orderBy('p.id', 'DESC')->get()->getResultArray();
        } catch (\Throwable $e) {
            $model = new PilgrimModel();
            if ($q !== '') {
                $model->groupStart()
                    ->like('first_name', $q)
                    ->orLike('last_name', $q)
                    ->orLike('passport_no', $q)
                    ->orLike('cnic', $q)
                    ->orLike('phone', $q)
                    ->orLike('mobile_no', $q)
                    ->orLike('email', $q)
                    ->groupEnd();
            }
            $rows = $model->orderBy('id', 'DESC')->findAll();
        }

        $pilgrims = array_map(static function (array $row): array {
            $row['full_name'] = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));

            return $row;
        }, $rows);

        return view('portal/pilgrims/index', [
            'title'       => 'HJMS ERP | Pilgrims',
            'headerTitle' => 'Pilgrim Management',
            'activePage'  => 'pilgrims',
            'userEmail'   => (string) session('user_email'),
            'pilgrims'    => $pilgrims,
            'q'           => $q,
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function importPage()
    {
        return view('portal/pilgrims/import', [
            'title'       => 'HJMS ERP | Import Pilgrims',
            'headerTitle' => 'Pilgrim Management',
            'activePage'  => 'pilgrims',
            'userEmail'   => (string) session('user_email'),
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function add()
    {
        return view('portal/pilgrims/add', [
            'title'       => 'HJMS ERP | Add Pilgrim',
            'headerTitle' => 'Pilgrim Management',
            'activePage'  => 'pilgrims',
            'userEmail'   => (string) session('user_email'),
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function edit(int $id)
    {
        $model = new PilgrimModel();
        $row = $model->where('id', $id)->where('season_id', $this->activeSeasonId())->first();

        if (empty($row)) {
            return redirect()->to('/app/pilgrims')->with('error', 'Pilgrim not found.');
        }

        $row['full_name'] = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));

        return view('portal/pilgrims/edit', [
            'title'       => 'HJMS ERP | Edit Pilgrim',
            'headerTitle' => 'Pilgrim Management',
            'activePage'  => 'pilgrims',
            'userEmail'   => (string) session('user_email'),
            'row'         => $row,
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function createPilgrim()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/app/seasons')->with('error', 'Please create and activate a season first.');
        }

        $payload = [
            'first_name'           => trim((string) $this->request->getPost('first_name')),
            'last_name'            => trim((string) $this->request->getPost('last_name')),
            'father_name'          => trim((string) $this->request->getPost('father_name')),
            'passport_no'          => trim((string) $this->request->getPost('passport_no')),
            'cnic'                 => trim((string) $this->request->getPost('cnic')),
            'country'              => trim((string) $this->request->getPost('country')),
            'gender'               => trim((string) $this->request->getPost('gender')),
            'date_of_birth'        => trim((string) $this->request->getPost('date_of_birth')),
            'passport_issue_date'  => trim((string) $this->request->getPost('passport_issue_date')),
            'passport_expiry_date' => trim((string) $this->request->getPost('passport_expiry_date')),
            'city'                 => trim((string) $this->request->getPost('city')),
            'mobile_no'            => trim((string) $this->request->getPost('mobile_no')),
            'mehram'               => trim((string) $this->request->getPost('mehram')),
            'description'          => trim((string) $this->request->getPost('description')),
            'phone'                => trim((string) $this->request->getPost('phone')),
            'email'                => trim((string) $this->request->getPost('email')),
        ];

        if (! $this->validateData($payload, [
            'first_name'           => 'required|min_length[2]|max_length[120]',
            'last_name'            => 'permit_empty|max_length[120]',
            'father_name'          => 'permit_empty|max_length[120]',
            'passport_no'          => 'required|max_length[50]',
            'cnic'                 => 'permit_empty|max_length[30]',
            'country'              => 'permit_empty|in_list[Pakistan,Others]',
            'gender'               => 'permit_empty|in_list[male,female]',
            'date_of_birth'        => 'permit_empty|valid_date[Y-m-d]',
            'passport_issue_date'  => 'permit_empty|valid_date[Y-m-d]',
            'passport_expiry_date' => 'permit_empty|valid_date[Y-m-d]',
            'city'                 => 'permit_empty|max_length[100]',
            'mobile_no'            => 'permit_empty|max_length[30]',
            'mehram'               => 'permit_empty|in_list[' . implode(',', self::MAHRAM_OPTIONS) . ']',
            'description'          => 'permit_empty|max_length[1000]',
            'phone'                => 'permit_empty|max_length[30]',
            'email'                => 'permit_empty|valid_email',
        ])) {
            return redirect()->to('/app/pilgrims')->withInput()->with('errors', $this->validator->getErrors());
        }

        $imageData = $this->handlePilgrimImageUploads();
        if (isset($imageData['error'])) {
            return redirect()->to('/app/pilgrims')->withInput()->with('error', (string) $imageData['error']);
        }

        try {
            $model = new PilgrimModel();
            $model->insert([
                'season_id'            => $seasonId,
                'first_name'           => $payload['first_name'],
                'last_name'            => $payload['last_name'] !== '' ? $payload['last_name'] : '-',
                'father_name'          => $payload['father_name'] !== '' ? $payload['father_name'] : null,
                'passport_no'          => $payload['passport_no'],
                'cnic'                 => $payload['cnic'] !== '' ? $payload['cnic'] : null,
                'country'              => $payload['country'] !== '' ? $payload['country'] : 'Pakistan',
                'gender'               => $payload['gender'] !== '' ? $payload['gender'] : null,
                'date_of_birth'        => $payload['date_of_birth'] !== '' ? $payload['date_of_birth'] : null,
                'passport_issue_date'  => $payload['passport_issue_date'] !== '' ? $payload['passport_issue_date'] : null,
                'passport_expiry_date' => $payload['passport_expiry_date'] !== '' ? $payload['passport_expiry_date'] : null,
                'city'                 => $payload['city'] !== '' ? $payload['city'] : null,
                'mobile_no'            => $payload['mobile_no'] !== '' ? $payload['mobile_no'] : null,
                'mehram'               => $payload['mehram'] !== '' ? $payload['mehram'] : null,
                'description'          => $payload['description'] !== '' ? $payload['description'] : null,
                'phone'                => $payload['phone'] !== '' ? $payload['phone'] : null,
                'email'                => $payload['email'] !== '' ? $payload['email'] : null,
                'is_active'            => 1,
                'created_at'           => date('Y-m-d H:i:s'),
                'updated_at'           => date('Y-m-d H:i:s'),
            ] + $imageData);

            return redirect()->to('/app/pilgrims')->with('success', 'Pilgrim created successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/pilgrims')->withInput()->with('error', $e->getMessage());
        }
    }

    public function updatePilgrim()
    {
        $pilgrimId = (int) $this->request->getPost('pilgrim_id');
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/app/seasons')->with('error', 'Please create and activate a season first.');
        }

        $payload = [
            'first_name'           => trim((string) $this->request->getPost('first_name')),
            'last_name'            => trim((string) $this->request->getPost('last_name')),
            'father_name'          => trim((string) $this->request->getPost('father_name')),
            'passport_no'          => trim((string) $this->request->getPost('passport_no')),
            'cnic'                 => trim((string) $this->request->getPost('cnic')),
            'country'              => trim((string) $this->request->getPost('country')),
            'gender'               => trim((string) $this->request->getPost('gender')),
            'date_of_birth'        => trim((string) $this->request->getPost('date_of_birth')),
            'passport_issue_date'  => trim((string) $this->request->getPost('passport_issue_date')),
            'passport_expiry_date' => trim((string) $this->request->getPost('passport_expiry_date')),
            'city'                 => trim((string) $this->request->getPost('city')),
            'mobile_no'            => trim((string) $this->request->getPost('mobile_no')),
            'mehram'               => trim((string) $this->request->getPost('mehram')),
            'description'          => trim((string) $this->request->getPost('description')),
            'phone'                => trim((string) $this->request->getPost('phone')),
            'email'                => trim((string) $this->request->getPost('email')),
            'is_active'            => (string) $this->request->getPost('is_active'),
        ];

        if ($pilgrimId < 1) {
            return redirect()->to('/app/pilgrims')->withInput()->with('error', 'Valid pilgrim ID is required.');
        }

        if (! $this->validateData($payload, [
            'first_name'           => 'required|min_length[2]|max_length[120]',
            'last_name'            => 'permit_empty|max_length[120]',
            'father_name'          => 'permit_empty|max_length[120]',
            'passport_no'          => 'required|max_length[50]',
            'cnic'                 => 'permit_empty|max_length[30]',
            'country'              => 'permit_empty|in_list[Pakistan,Others]',
            'gender'               => 'permit_empty|in_list[male,female]',
            'date_of_birth'        => 'permit_empty|valid_date[Y-m-d]',
            'passport_issue_date'  => 'permit_empty|valid_date[Y-m-d]',
            'passport_expiry_date' => 'permit_empty|valid_date[Y-m-d]',
            'city'                 => 'permit_empty|max_length[100]',
            'mobile_no'            => 'permit_empty|max_length[30]',
            'mehram'               => 'permit_empty|in_list[' . implode(',', self::MAHRAM_OPTIONS) . ']',
            'description'          => 'permit_empty|max_length[1000]',
            'phone'                => 'permit_empty|max_length[30]',
            'email'                => 'permit_empty|valid_email',
            'is_active'            => 'permit_empty|in_list[0,1]',
        ])) {
            return redirect()->to('/app/pilgrims')->withInput()->with('errors', $this->validator->getErrors());
        }

        $imageData = $this->handlePilgrimImageUploads();
        if (isset($imageData['error'])) {
            return redirect()->to('/app/pilgrims')->withInput()->with('error', (string) $imageData['error']);
        }

        try {
            $model = new PilgrimModel();
            $existing = $model->where('id', $pilgrimId)->where('season_id', $seasonId)->first();
            if (empty($existing)) {
                return redirect()->to('/app/pilgrims')->with('error', 'Pilgrim not found in active season.');
            }

            $model->update($pilgrimId, [
                'first_name'           => $payload['first_name'],
                'last_name'            => $payload['last_name'] !== '' ? $payload['last_name'] : '-',
                'father_name'          => $payload['father_name'] !== '' ? $payload['father_name'] : null,
                'passport_no'          => $payload['passport_no'],
                'cnic'                 => $payload['cnic'] !== '' ? $payload['cnic'] : null,
                'country'              => $payload['country'] !== '' ? $payload['country'] : null,
                'gender'               => $payload['gender'] !== '' ? $payload['gender'] : null,
                'date_of_birth'        => $payload['date_of_birth'] !== '' ? $payload['date_of_birth'] : null,
                'passport_issue_date'  => $payload['passport_issue_date'] !== '' ? $payload['passport_issue_date'] : null,
                'passport_expiry_date' => $payload['passport_expiry_date'] !== '' ? $payload['passport_expiry_date'] : null,
                'city'                 => $payload['city'] !== '' ? $payload['city'] : null,
                'mobile_no'            => $payload['mobile_no'] !== '' ? $payload['mobile_no'] : null,
                'mehram'               => $payload['mehram'] !== '' ? $payload['mehram'] : null,
                'description'          => $payload['description'] !== '' ? $payload['description'] : null,
                'phone'                => $payload['phone'] !== '' ? $payload['phone'] : null,
                'email'                => $payload['email'] !== '' ? $payload['email'] : null,
                'is_active'            => $payload['is_active'] !== '' ? (int) $payload['is_active'] : 1,
                'updated_at'           => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('/app/pilgrims')->with('success', 'Pilgrim updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/pilgrims')->withInput()->with('error', $e->getMessage());
        }
    }

    public function deletePilgrim()
    {
        $pilgrimId = (int) $this->request->getPost('pilgrim_id');
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/app/seasons')->with('error', 'Please create and activate a season first.');
        }

        if ($pilgrimId < 1) {
            return redirect()->to('/app/pilgrims')->with('error', 'Valid pilgrim ID is required for delete.');
        }

        try {
            $model = new PilgrimModel();
            $existing = $model->where('id', $pilgrimId)->where('season_id', $seasonId)->first();
            if (empty($existing)) {
                return redirect()->to('/app/pilgrims')->with('error', 'Pilgrim not found in active season.');
            }

            $deleted = $model->delete($pilgrimId);

            if (! $deleted) {
                return redirect()->to('/app/pilgrims')->with('error', 'Pilgrim not found or already removed.');
            }

            return redirect()->to('/app/pilgrims')->with('success', 'Pilgrim deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/pilgrims')->with('error', $e->getMessage());
        }
    }

    public function importMofaCsv()
    {
        if ($this->activeSeasonId() === null) {
            return redirect()->to('/app/seasons')->with('error', 'Please create and activate a season first.');
        }

        $file = $this->request->getFile('mofa_csv');
        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return redirect()->to('/app/pilgrims')->with('error', 'Please choose a CSV file to import.');
        }

        if (! $file->isValid()) {
            return redirect()->to('/app/pilgrims')->with('error', 'Uploaded file is invalid.');
        }

        $extension = strtolower((string) $file->getExtension());
        if ($extension !== 'csv') {
            return redirect()->to('/app/pilgrims')->with('error', 'Only CSV files are allowed.');
        }

        try {
            $rows = $this->readMofaCsvRows($file->getTempName());
            if (count($rows) < 2) {
                return redirect()->to('/app/pilgrims')->with('error', 'No data rows found in the uploaded file.');
            }

            $headerMap = $this->buildMofaHeaderMap($rows[0]);
            if (! isset($headerMap['mutamer_name']) || ! isset($headerMap['passport_no'])) {
                return redirect()->to('/app/pilgrims')->with('error', 'Required MOFA columns are missing. Ensure Mutamer Name and Passport No exist.');
            }

            $model = new PilgrimModel();
            $existingRows = $model->select('passport_no')->findAll();
            $existingPassports = [];
            foreach ($existingRows as $existingRow) {
                $key = strtoupper(trim((string) ($existingRow['passport_no'] ?? '')));
                if ($key !== '') {
                    $existingPassports[$key] = true;
                }
            }

            $inserted = 0;
            $duplicates = 0;
            $skipped = 0;

            for ($i = 1, $total = count($rows); $i < $total; $i++) {
                $mapped = $this->mapMofaRowToPilgrim($rows[$i], $headerMap);
                if ($mapped === null) {
                    $skipped++;
                    continue;
                }

                $passportKey = strtoupper(trim((string) ($mapped['passport_no'] ?? '')));
                if ($passportKey === '') {
                    $skipped++;
                    continue;
                }

                if (isset($existingPassports[$passportKey])) {
                    $duplicates++;
                    continue;
                }

                $ok = $model->insert($mapped);
                if ($ok === false) {
                    $skipped++;
                    continue;
                }

                $existingPassports[$passportKey] = true;
                $inserted++;
            }

            return redirect()->to('/app/pilgrims')->with('success', 'MOFA import complete. Added: ' . $inserted . ', duplicates skipped: ' . $duplicates . ', invalid/empty skipped: ' . $skipped . '.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/pilgrims')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    private function readMofaCsvRows(string $filePath): array
    {
        $rows = [];
        if (($handle = fopen($filePath, 'rb')) === false) {
            throw new \RuntimeException('Unable to open CSV file.');
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);

            return [];
        }

        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        rewind($handle);

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = $data;
        }
        fclose($handle);

        return $rows;
    }

    private function buildMofaHeaderMap(array $headerRow): array
    {
        $aliases = [
            'group_code' => ['group_code'],
            'group_name' => ['group_name'],
            'mutamer_code' => ['mutamer_code'],
            'mutamer_name' => ['mutamer_name', 'name'],
            'mofa_no' => ['mofa_no', 'mofa'],
            'gender' => ['gender'],
            'date_of_birth' => ['date_of_birth', 'dob', 'date_ofbirth'],
            'nationality' => ['nationality'],
            'passport_no' => ['passport_no', 'passport'],
            'dependant' => ['dependant', 'dependan', 'dependent'],
            'mahram' => ['mahram', 'maharm'],
            'serial_no' => ['serial_no', 'serial'],
            'relation' => ['relation'],
            'moi_no' => ['moi_no', 'moi'],
        ];

        $normalizedHeaders = [];
        foreach ($headerRow as $index => $value) {
            $normalizedHeaders[(int) $index] = $this->normalizeMofaHeader((string) $value);
        }

        $map = [];
        foreach ($aliases as $canonical => $candidates) {
            foreach ($normalizedHeaders as $index => $normalizedHeader) {
                if (in_array($normalizedHeader, $candidates, true)) {
                    $map[$canonical] = $index;
                    break;
                }
            }
        }

        return $map;
    }

    private function mapMofaRowToPilgrim(array $row, array $headerMap)
    {
        $name = trim((string) $this->valueByHeader($row, $headerMap, 'mutamer_name'));
        $passportNo = trim((string) $this->valueByHeader($row, $headerMap, 'passport_no'));

        if ($name === '' && $passportNo === '') {
            return null;
        }

        if ($passportNo === '') {
            return null;
        }

        list($firstName, $lastName) = $this->splitFullName($name);
        $gender = $this->mapGender((string) $this->valueByHeader($row, $headerMap, 'gender'));
        $country = $this->mapCountry((string) $this->valueByHeader($row, $headerMap, 'nationality'));
        $dateOfBirth = $this->parseMofaDate($this->valueByHeader($row, $headerMap, 'date_of_birth'));

        $relation = trim((string) $this->valueByHeader($row, $headerMap, 'relation'));
        $mehram = $this->mapMehram($relation);

        $descriptionParts = [];
        foreach (['group_code', 'group_name', 'mutamer_code', 'mofa_no', 'dependant', 'mahram', 'serial_no', 'relation', 'moi_no'] as $field) {
            $value = trim((string) $this->valueByHeader($row, $headerMap, $field));
            if ($value !== '') {
                $descriptionParts[] = strtoupper(str_replace('_', ' ', $field)) . ': ' . $value;
            }
        }

        return [
            'first_name'           => $firstName,
            'last_name'            => $lastName,
            'passport_no'          => $passportNo,
            'cnic'                 => trim((string) $this->valueByHeader($row, $headerMap, 'moi_no')) ?: null,
            'country'              => $country,
            'gender'               => $gender,
            'date_of_birth'        => $dateOfBirth,
            'mehram'               => $mehram,
            'description'          => $descriptionParts !== [] ? implode(' | ', $descriptionParts) : null,
            'is_active'            => 1,
            'created_at'           => date('Y-m-d H:i:s'),
            'updated_at'           => date('Y-m-d H:i:s'),
        ];
    }

    private function normalizeMofaHeader(string $header): string
    {
        $normalized = strtolower(trim($header));
        $normalized = preg_replace('/[^a-z0-9]+/i', '_', $normalized) ?? $normalized;

        return trim($normalized, '_');
    }

    private function valueByHeader(array $row, array $headerMap, string $key): string
    {
        if (! isset($headerMap[$key])) {
            return '';
        }

        $index = (int) $headerMap[$key];

        return isset($row[$index]) ? (string) $row[$index] : '';
    }

    private function splitFullName(string $fullName): array
    {
        $fullName = trim($fullName);
        if ($fullName === '') {
            return ['Unknown', '-'];
        }

        $parts = preg_split('/\s+/', $fullName) ?: [];
        $first = array_shift($parts) ?: 'Unknown';
        $last = $parts !== [] ? implode(' ', $parts) : '-';

        return [mb_substr($first, 0, 120), mb_substr($last, 0, 120)];
    }

    private function mapGender(string $gender)
    {
        $value = strtolower(trim($gender));
        if ($value === 'male' || $value === 'm') {
            return 'male';
        }
        if ($value === 'female' || $value === 'f') {
            return 'female';
        }

        return null;
    }

    private function mapCountry(string $nationality): string
    {
        $value = strtolower(trim($nationality));

        return $value === 'pakistan' || $value === 'pakistani' ? 'Pakistan' : 'Others';
    }

    private function mapMehram(string $relation)
    {
        $normalized = strtolower(trim($relation));
        if ($normalized === '') {
            return null;
        }

        foreach (self::MAHRAM_OPTIONS as $option) {
            if (strtolower($option) === $normalized) {
                return $option;
            }
        }

        return null;
    }

    private function parseMofaDate($value)
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            $serial = (float) $value;
            if ($serial > 0) {
                $timestamp = (int) (($serial - 25569) * 86400);

                return gmdate('Y-m-d', $timestamp);
            }
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'm/d/Y'];
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $raw);
            if ($date instanceof \DateTime) {
                return $date->format('Y-m-d');
            }
        }

        try {
            return (new \DateTime($raw))->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function handlePilgrimImageUploads(): array
    {
        $result = [];
        $imageFields = [
            'pilgrim_image' => ['name' => 'pilgrim_image_name', 'path' => 'pilgrim_image_path'],
            'passport_image' => ['name' => 'passport_image_name', 'path' => 'passport_image_path'],
        ];

        foreach ($imageFields as $input => $mapping) {
            $file = $this->request->getFile($input);
            if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if (! $file->isValid() || $file->hasMoved()) {
                return ['error' => 'Uploaded file for ' . $input . ' is invalid.'];
            }

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $extension = strtolower((string) $file->getExtension());
            if (! in_array($extension, $allowedExtensions, true)) {
                return ['error' => ucfirst(str_replace('_', ' ', $input)) . ' must be JPG, JPEG, PNG, or WEBP.'];
            }

            if ($file->getSize() > 5 * 1024 * 1024) {
                return ['error' => ucfirst(str_replace('_', ' ', $input)) . ' size must be 5MB or less.'];
            }

            $uploadDir = WRITEPATH . 'uploads/pilgrims';
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $storedName = $file->getRandomName();
            $file->move($uploadDir, $storedName);

            $result[$mapping['name']] = $file->getClientName();
            $result[$mapping['path']] = 'uploads/pilgrims/' . $storedName;
        }

        return $result;
    }
}
