<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Config\VisaOptions;
use App\Models\VisaModel;

class VisaController extends BaseController
{
    public function index()
    {
        $db = db_connect();
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/app/seasons')->with('error', 'Please create and activate a season first.');
        }

        return view('portal/visas/index', [
            'title'       => 'HJMS ERP | Visas',
            'headerTitle' => 'Visa Processing',
            'activePage'  => 'visas',
            'userEmail'   => (string) session('user_email'),
            'rows'        => $db->table('visas v')
                ->select('v.*, p.first_name, p.last_name, p.passport_no, b.booking_no')
                ->join('pilgrims p', 'p.id = v.pilgrim_id', 'left')
                ->join('bookings b', 'b.id = v.booking_id', 'left')
                ->where('v.season_id', $seasonId)
                ->orderBy('v.id', 'DESC')
                ->get()
                ->getResultArray(),
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function add(): string
    {
        $lookups = $this->getLookupData();

        return view('portal/visas/add', [
            'title'       => 'HJMS ERP | Add Visa',
            'headerTitle' => 'Visa Processing',
            'activePage'  => 'visas',
            'userEmail'   => (string) session('user_email'),
            'pilgrims'    => $lookups['pilgrims'],
            'bookings'    => $lookups['bookings'],
            'visaTypes'   => VisaOptions::TYPES,
            'visaStatuses' => VisaOptions::STATUSES,
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function edit(int $id)
    {
        $model = new VisaModel();
        $row = $model->where('id', $id)->where('season_id', $this->activeSeasonId())->first();

        if (empty($row)) {
            return redirect()->to('/app/visas')->with('error', 'Visa record not found.');
        }

        $lookups = $this->getLookupData();

        return view('portal/visas/edit', [
            'title'       => 'HJMS ERP | Edit Visa',
            'headerTitle' => 'Visa Processing',
            'activePage'  => 'visas',
            'userEmail'   => (string) session('user_email'),
            'row'         => $row,
            'pilgrims'    => $lookups['pilgrims'],
            'bookings'    => $lookups['bookings'],
            'visaTypes'   => VisaOptions::TYPES,
            'visaStatuses' => VisaOptions::STATUSES,
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function createVisa()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/app/seasons')->with('error', 'Please create and activate a season first.');
        }

        $payload = [
            'pilgrim_id'      => (int) $this->request->getPost('pilgrim_id'),
            'booking_id'      => $this->request->getPost('booking_id') !== '' ? (int) $this->request->getPost('booking_id') : null,
            'visa_no'         => trim((string) $this->request->getPost('visa_no')),
            'visa_type'       => (string) ($this->request->getPost('visa_type') ?: 'umrah'),
            'status'          => (string) ($this->request->getPost('status') ?: 'draft'),
            'submission_date' => (string) $this->request->getPost('submission_date'),
            'approval_date'   => (string) $this->request->getPost('approval_date'),
            'notes'           => (string) $this->request->getPost('notes'),
            'rejection_reason' => (string) $this->request->getPost('rejection_reason'),
        ];

        if (! $this->validateData($payload, [
            'pilgrim_id'      => 'required|integer',
            'booking_id'      => 'permit_empty|integer',
            'visa_no'         => 'permit_empty|max_length[60]',
            'visa_type'       => 'permit_empty|in_list[' . implode(',', VisaOptions::typeKeys()) . ']',
            'status'          => 'permit_empty|in_list[' . implode(',', VisaOptions::statusKeys()) . ']',
            'submission_date' => 'permit_empty|valid_date[Y-m-d]',
            'approval_date'   => 'permit_empty|valid_date[Y-m-d]',
            'rejection_reason' => 'permit_empty|max_length[5000]',
        ])) {
            return redirect()->to('/app/visas/add')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $fileData = $this->handleVisaFileUpload();
            if (isset($fileData['error'])) {
                return redirect()->to('/app/visas/add')->withInput()->with('error', $fileData['error']);
            }

            $db = db_connect();
            $pilgrim = $db->table('pilgrims')->select('id')->where('id', $payload['pilgrim_id'])->where('season_id', $seasonId)->get()->getRowArray();
            if (empty($pilgrim)) {
                return redirect()->to('/app/visas/add')->withInput()->with('error', 'Selected pilgrim is not in active season.');
            }
            if ($payload['booking_id'] !== null) {
                $booking = $db->table('bookings')->select('id')->where('id', (int) $payload['booking_id'])->where('season_id', $seasonId)->get()->getRowArray();
                if (empty($booking)) {
                    return redirect()->to('/app/visas/add')->withInput()->with('error', 'Selected booking is not in active season.');
                }
            }

            $model = new VisaModel();
            $model->insert([
                'season_id'        => $seasonId,
                'booking_id'       => $payload['booking_id'],
                'pilgrim_id'       => $payload['pilgrim_id'],
                'visa_no'          => $payload['visa_no'] !== '' ? $payload['visa_no'] : null,
                'visa_type'        => $payload['visa_type'],
                'status'           => $payload['status'],
                'submission_date'  => $payload['submission_date'] !== '' ? $payload['submission_date'] : null,
                'approval_date'    => $payload['approval_date'] !== '' ? $payload['approval_date'] : null,
                'rejection_reason' => $payload['rejection_reason'] !== '' ? $payload['rejection_reason'] : null,
                'visa_file_name'   => $fileData['visa_file_name'] ?? null,
                'visa_file_path'   => $fileData['visa_file_path'] ?? null,
                'notes'            => $payload['notes'] !== '' ? $payload['notes'] : null,
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('/app/visas')->with('success', 'Visa record created successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/visas/add')->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateVisa()
    {
        $visaId = (int) $this->request->getPost('visa_id');
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/app/seasons')->with('error', 'Please create and activate a season first.');
        }

        $payload = [
            'pilgrim_id'       => (int) $this->request->getPost('pilgrim_id'),
            'booking_id'       => $this->request->getPost('booking_id') !== '' ? (int) $this->request->getPost('booking_id') : null,
            'visa_no'          => trim((string) $this->request->getPost('visa_no')),
            'visa_type'        => (string) ($this->request->getPost('visa_type') ?: 'umrah'),
            'status'           => (string) ($this->request->getPost('status') ?: 'draft'),
            'submission_date'  => (string) $this->request->getPost('submission_date'),
            'approval_date'    => (string) $this->request->getPost('approval_date'),
            'rejection_reason' => (string) $this->request->getPost('rejection_reason'),
            'notes'            => (string) $this->request->getPost('notes'),
        ];

        if ($visaId < 1) {
            return redirect()->to('/app/visas')->withInput()->with('error', 'Valid visa ID is required.');
        }

        if (! $this->validateData($payload, [
            'pilgrim_id'       => 'required|integer',
            'booking_id'       => 'permit_empty|integer',
            'visa_no'          => 'permit_empty|max_length[60]',
            'visa_type'        => 'permit_empty|in_list[' . implode(',', VisaOptions::typeKeys()) . ']',
            'status'           => 'permit_empty|in_list[' . implode(',', VisaOptions::statusKeys()) . ']',
            'submission_date'  => 'permit_empty|valid_date[Y-m-d]',
            'approval_date'    => 'permit_empty|valid_date[Y-m-d]',
            'rejection_reason' => 'permit_empty|max_length[5000]',
            'notes'            => 'permit_empty',
        ])) {
            return redirect()->to('/app/visas/' . $visaId . '/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $updateData = [
                'booking_id'       => $payload['booking_id'],
                'pilgrim_id'       => $payload['pilgrim_id'],
                'visa_no'          => $payload['visa_no'] !== '' ? $payload['visa_no'] : null,
                'visa_type'        => $payload['visa_type'],
                'status'           => $payload['status'],
                'submission_date'  => $payload['submission_date'] !== '' ? $payload['submission_date'] : null,
                'approval_date'    => $payload['approval_date'] !== '' ? $payload['approval_date'] : null,
                'rejection_reason' => $payload['rejection_reason'] !== '' ? $payload['rejection_reason'] : null,
                'notes'            => $payload['notes'] !== '' ? $payload['notes'] : null,
                'updated_at'       => date('Y-m-d H:i:s'),
            ];

            $fileData = $this->handleVisaFileUpload();
            if (isset($fileData['error'])) {
                return redirect()->to('/app/visas/' . $visaId . '/edit')->withInput()->with('error', $fileData['error']);
            }

            $db = db_connect();
            $pilgrim = $db->table('pilgrims')->select('id')->where('id', $payload['pilgrim_id'])->where('season_id', $seasonId)->get()->getRowArray();
            if (empty($pilgrim)) {
                return redirect()->to('/app/visas/' . $visaId . '/edit')->withInput()->with('error', 'Selected pilgrim is not in active season.');
            }
            if ($payload['booking_id'] !== null) {
                $booking = $db->table('bookings')->select('id')->where('id', (int) $payload['booking_id'])->where('season_id', $seasonId)->get()->getRowArray();
                if (empty($booking)) {
                    return redirect()->to('/app/visas/' . $visaId . '/edit')->withInput()->with('error', 'Selected booking is not in active season.');
                }
            }

            if (isset($fileData['visa_file_name'])) {
                $updateData['visa_file_name'] = $fileData['visa_file_name'];
                $updateData['visa_file_path'] = $fileData['visa_file_path'];
            }

            $model = new VisaModel();
            $existing = $model->where('id', $visaId)->where('season_id', $seasonId)->first();
            if (empty($existing)) {
                return redirect()->to('/app/visas')->with('error', 'Visa record not found in active season.');
            }
            $model->update($visaId, $updateData);

            return redirect()->to('/app/visas')->with('success', 'Visa record updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/visas/' . $visaId . '/edit')->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateVisaStatus()
    {
        $visaId = (int) $this->request->getPost('visa_id');
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/app/seasons')->with('error', 'Please create and activate a season first.');
        }

        $payload = [
            'status'           => (string) $this->request->getPost('status'),
            'submission_date'  => (string) $this->request->getPost('submission_date'),
            'approval_date'    => (string) $this->request->getPost('approval_date'),
            'rejection_reason' => (string) $this->request->getPost('rejection_reason'),
        ];

        if ($visaId < 1) {
            return redirect()->to('/app/visas')->withInput()->with('error', 'Valid visa ID is required.');
        }

        if (! $this->validateData($payload, [
            'status'           => 'required|in_list[' . implode(',', VisaOptions::statusKeys()) . ']',
            'submission_date'  => 'permit_empty|valid_date[Y-m-d]',
            'approval_date'    => 'permit_empty|valid_date[Y-m-d]',
            'rejection_reason' => 'permit_empty|max_length[5000]',
        ])) {
            return redirect()->to('/app/visas')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $updateData = $this->buildStatusUpdateData($payload);

            $model = new VisaModel();
            $existing = $model->where('id', $visaId)->where('season_id', $seasonId)->first();
            if (empty($existing)) {
                return redirect()->to('/app/visas')->with('error', 'Visa record not found in active season.');
            }
            $model->update($visaId, $updateData);

            return redirect()->to('/app/visas')->with('success', 'Visa status updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/visas')->withInput()->with('error', $e->getMessage());
        }
    }

    public function bulkUpdateVisaStatus()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/app/seasons')->with('error', 'Please create and activate a season first.');
        }

        $visaIds = $this->request->getPost('visa_ids');
        $payload = [
            'status'           => (string) $this->request->getPost('status'),
            'submission_date'  => (string) $this->request->getPost('submission_date'),
            'approval_date'    => (string) $this->request->getPost('approval_date'),
            'rejection_reason' => (string) $this->request->getPost('rejection_reason'),
        ];

        if (! is_array($visaIds) || $visaIds === []) {
            return redirect()->to('/app/visas')->withInput()->with('error', 'Select at least one visa record for bulk update.');
        }

        $visaIds = array_values(array_unique(array_filter(array_map('intval', $visaIds), static function (int $id): bool {
            return $id > 0;
        })));

        if ($visaIds === []) {
            return redirect()->to('/app/visas')->withInput()->with('error', 'Selected visa IDs are invalid.');
        }

        if (! $this->validateData($payload, [
            'status'           => 'required|in_list[' . implode(',', VisaOptions::statusKeys()) . ']',
            'submission_date'  => 'permit_empty|valid_date[Y-m-d]',
            'approval_date'    => 'permit_empty|valid_date[Y-m-d]',
            'rejection_reason' => 'permit_empty|max_length[5000]',
        ])) {
            return redirect()->to('/app/visas')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $updateData = $this->buildStatusUpdateData($payload);
            db_connect()->table('visas')->where('season_id', $seasonId)->whereIn('id', $visaIds)->update($updateData);

            return redirect()->to('/app/visas')->with('success', 'Bulk visa status updated for ' . count($visaIds) . ' record(s).');
        } catch (\Throwable $e) {
            return redirect()->to('/app/visas')->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteVisa()
    {
        $visaId = (int) $this->request->getPost('visa_id');
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/app/seasons')->with('error', 'Please create and activate a season first.');
        }
        if ($visaId < 1) {
            return redirect()->to('/app/visas')->with('error', 'Valid visa ID is required for delete.');
        }

        try {
            $model = new VisaModel();
            $existing = $model->where('id', $visaId)->where('season_id', $seasonId)->first();
            if (empty($existing)) {
                return redirect()->to('/app/visas')->with('error', 'Visa record not found in active season.');
            }
            $deleted = $model->delete($visaId);

            if (! $deleted) {
                return redirect()->to('/app/visas')->with('error', 'Visa record not found or already removed.');
            }

            return redirect()->to('/app/visas')->with('success', 'Visa record deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/visas')->with('error', $e->getMessage());
        }
    }

    private function getLookupData(): array
    {
        $db = db_connect();
        $seasonId = $this->activeSeasonId();

        return [
            'pilgrims' => $db->table('pilgrims')
                ->select('id, first_name, last_name, passport_no')
                ->where('season_id', $seasonId)
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray(),
            'bookings' => $db->table('bookings')
                ->select('id, booking_no')
                ->where('season_id', $seasonId)
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray(),
        ];
    }

    private function buildStatusUpdateData(array $payload): array
    {
        $updateData = [
            'status'     => $payload['status'],
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($payload['status'] === 'submitted') {
            $updateData['submission_date'] = $payload['submission_date'] !== '' ? $payload['submission_date'] : date('Y-m-d');
            $updateData['approval_date'] = null;
            $updateData['rejection_reason'] = null;
        }

        if ($payload['status'] === 'approved') {
            $updateData['approval_date'] = $payload['approval_date'] !== '' ? $payload['approval_date'] : date('Y-m-d');
            $updateData['rejection_reason'] = null;
        }

        if ($payload['status'] === 'rejected') {
            $updateData['approval_date'] = null;
            $updateData['rejection_reason'] = $payload['rejection_reason'] !== '' ? $payload['rejection_reason'] : null;
        }

        if ($payload['status'] === 'draft') {
            $updateData['approval_date'] = null;
            $updateData['rejection_reason'] = null;
        }

        return $updateData;
    }

    private function handleVisaFileUpload(): array
    {
        $file = $this->request->getFile('visa_file');

        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return [];
        }

        if (! $file->isValid() || $file->hasMoved()) {
            return ['error' => 'Uploaded visa file is invalid.'];
        }

        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
        $extension = strtolower((string) $file->getExtension());

        if (! in_array($extension, $allowedExtensions, true)) {
            return ['error' => 'Visa file must be PDF, JPG, JPEG, or PNG.'];
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            return ['error' => 'Visa file size must be 5MB or less.'];
        }

        $uploadDir = WRITEPATH . 'uploads/visas';
        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $storedName = $file->getRandomName();
        $file->move($uploadDir, $storedName);

        return [
            'visa_file_name' => $file->getClientName(),
            'visa_file_path' => 'uploads/visas/' . $storedName,
        ];
    }
}
