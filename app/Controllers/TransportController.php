<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TransportLegModel;
use App\Models\TransportModel;

class TransportController extends BaseController
{
    public function index(): string
    {
        $model = new TransportModel();

        return view('portal/transports/index', [
            'title'       => 'HJMS ERP | Transports',
            'headerTitle' => 'Transport Management',
            'activePage'  => 'transports',
            'userEmail'   => (string) session('user_email'),
            'rows'        => $model->orderBy('id', 'DESC')->findAll(),
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function add(): string
    {
        return view('portal/transports/add', [
            'title'       => 'HJMS ERP | Add Transport',
            'headerTitle' => 'Transport Management',
            'activePage'  => 'transports',
            'userEmail'   => (string) session('user_email'),
            'vehicleTypeOptions' => $this->vehicleTypeOptions(),
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function edit(int $id)
    {
        $model = new TransportModel();
        $row = $model->find($id);

        if (empty($row)) {
            return redirect()->to('/app/transports')->with('error', 'Transport record not found.');
        }

        $db = db_connect();
        $legRows = [];
        if ($db->tableExists('transport_legs')) {
            $legRows = $db->table('transport_legs')
                ->where('transport_id', $id)
                ->orderBy('seq_no', 'ASC')
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();
        }

        return view('portal/transports/edit', [
            'title'       => 'HJMS ERP | Edit Transport',
            'headerTitle' => 'Transport Management',
            'activePage'  => 'transports',
            'userEmail'   => (string) session('user_email'),
            'row'         => $row,
            'legRows'     => $legRows,
            'compactRoute' => $this->buildCompactRoute($legRows),
            'vehicleTypeOptions' => $this->vehicleTypeOptions(),
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function createTransport()
    {
        $payload = $this->extractPayload();

        if (! $this->validateData($payload, $this->rules())) {
            return redirect()->to('/app/transports/add')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $model = new TransportModel();
            $model->insert([
                'transport_name' => $payload['transport_name'],
                'provider_name' => $payload['provider_name'],
                'vehicle_type'  => $payload['vehicle_type'],
                'driver_name'   => $payload['driver_name'] !== '' ? $payload['driver_name'] : null,
                'driver_phone'  => $payload['driver_phone'] !== '' ? $payload['driver_phone'] : null,
                'seat_capacity' => $payload['seat_capacity'] !== '' ? (int) $payload['seat_capacity'] : 0,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('/app/transports')->with('success', 'Transport provider added successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/transports/add')->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateTransport()
    {
        $transportId = (int) $this->request->getPost('transport_id');
        $payload = $this->extractPayload();

        if ($transportId < 1) {
            return redirect()->to('/app/transports')->withInput()->with('error', 'Valid transport ID is required.');
        }

        if (! $this->validateData($payload, $this->rules())) {
            return redirect()->to('/app/transports/' . $transportId . '/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $model = new TransportModel();
            $model->update($transportId, [
                'transport_name' => $payload['transport_name'],
                'provider_name' => $payload['provider_name'],
                'vehicle_type'  => $payload['vehicle_type'],
                'driver_name'   => $payload['driver_name'] !== '' ? $payload['driver_name'] : null,
                'driver_phone'  => $payload['driver_phone'] !== '' ? $payload['driver_phone'] : null,
                'seat_capacity' => $payload['seat_capacity'] !== '' ? (int) $payload['seat_capacity'] : 0,
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('/app/transports')->with('success', 'Transport updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/transports/' . $transportId . '/edit')->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteTransport()
    {
        $transportId = (int) $this->request->getPost('transport_id');

        if ($transportId < 1) {
            return redirect()->to('/app/transports')->with('error', 'Valid transport ID is required for delete.');
        }

        try {
            if (db_connect()->tableExists('transport_legs')) {
                db_connect()->table('transport_legs')->where('transport_id', $transportId)->delete();
            }

            $model = new TransportModel();
            $deleted = $model->delete($transportId);

            if (! $deleted) {
                return redirect()->to('/app/transports')->with('error', 'Transport not found or already removed.');
            }

            return redirect()->to('/app/transports')->with('success', 'Transport deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/transports')->with('error', $e->getMessage());
        }
    }

    public function createTransportLeg()
    {
        if (! db_connect()->tableExists('transport_legs')) {
            return redirect()->back()->with('error', 'Transport legs table is missing. Run latest migration first.');
        }

        $payload = [
            'transport_id' => (int) $this->request->getPost('transport_id'),
            'from_code'    => $this->normalizeLocationCode((string) $this->request->getPost('from_code')),
            'to_code'      => $this->normalizeLocationCode((string) $this->request->getPost('to_code')),
            'is_ziarat'    => $this->request->getPost('is_ziarat') !== null ? '1' : '0',
            'ziarat_site'  => trim((string) $this->request->getPost('ziarat_site')),
            'notes'        => trim((string) $this->request->getPost('notes')),
        ];

        if (! $this->validateData($payload, [
            'transport_id' => 'required|integer',
            'from_code'    => 'required|max_length[20]',
            'to_code'      => 'required|max_length[20]',
            'is_ziarat'    => 'required|in_list[0,1]',
            'ziarat_site'  => 'permit_empty|max_length[180]',
            'notes'        => 'permit_empty|max_length[255]',
        ])) {
            return redirect()->to('/app/transports/' . $payload['transport_id'] . '/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $legModel = new TransportLegModel();
            $nextSeq = (int) ($legModel->selectMax('seq_no')
                ->where('transport_id', $payload['transport_id'])
                ->first()['seq_no'] ?? 0) + 1;

            $legModel->insert([
                'transport_id' => $payload['transport_id'],
                'seq_no' => $nextSeq,
                'from_code' => $payload['from_code'],
                'to_code' => $payload['to_code'],
                'is_ziarat' => (int) $payload['is_ziarat'],
                'ziarat_site' => $payload['ziarat_site'] !== '' ? $payload['ziarat_site'] : null,
                'notes' => $payload['notes'] !== '' ? $payload['notes'] : null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('/app/transports/' . $payload['transport_id'] . '/edit')->with('success', 'Transport leg added successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/transports/' . $payload['transport_id'] . '/edit')->with('error', $e->getMessage());
        }
    }

    public function deleteTransportLeg()
    {
        if (! db_connect()->tableExists('transport_legs')) {
            return redirect()->back()->with('error', 'Transport legs table is missing. Run latest migration first.');
        }

        $legId = (int) $this->request->getPost('transport_leg_id');
        $transportId = (int) $this->request->getPost('transport_id');

        if ($legId < 1 || $transportId < 1) {
            return redirect()->to('/app/transports')->with('error', 'Valid transport and leg IDs are required for delete.');
        }

        try {
            $deleted = (new TransportLegModel())->delete($legId);
            if (! $deleted) {
                return redirect()->to('/app/transports/' . $transportId . '/edit')->with('error', 'Transport leg not found or already removed.');
            }

            return redirect()->to('/app/transports/' . $transportId . '/edit')->with('success', 'Transport leg deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/transports/' . $transportId . '/edit')->with('error', $e->getMessage());
        }
    }

    public function moveTransportLeg()
    {
        if (! db_connect()->tableExists('transport_legs')) {
            return redirect()->back()->with('error', 'Transport legs table is missing. Run latest migration first.');
        }

        $legId = (int) $this->request->getPost('transport_leg_id');
        $transportId = (int) $this->request->getPost('transport_id');
        $direction = (string) $this->request->getPost('direction');

        if ($legId < 1 || $transportId < 1 || ! in_array($direction, ['up', 'down'], true)) {
            return redirect()->to('/app/transports')->with('error', 'Valid transport leg move request is required.');
        }

        try {
            $model = new TransportLegModel();
            $current = $model->where('id', $legId)->where('transport_id', $transportId)->first();
            if (empty($current)) {
                return redirect()->to('/app/transports/' . $transportId . '/edit')->with('error', 'Transport leg not found.');
            }

            $targetBuilder = $model->where('transport_id', $transportId);
            if ($direction === 'up') {
                $targetBuilder = $targetBuilder->where('seq_no <', (int) $current['seq_no'])->orderBy('seq_no', 'DESC');
            } else {
                $targetBuilder = $targetBuilder->where('seq_no >', (int) $current['seq_no'])->orderBy('seq_no', 'ASC');
            }

            $target = $targetBuilder->first();
            if (empty($target)) {
                return redirect()->to('/app/transports/' . $transportId . '/edit');
            }

            $model->update((int) $current['id'], [
                'seq_no' => (int) $target['seq_no'],
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $model->update((int) $target['id'], [
                'seq_no' => (int) $current['seq_no'],
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('/app/transports/' . $transportId . '/edit')->with('success', 'Transport leg order updated.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/transports/' . $transportId . '/edit')->with('error', $e->getMessage());
        }
    }

    private function extractPayload(): array
    {
        return [
            'transport_name' => trim((string) $this->request->getPost('transport_name')),
            'provider_name' => (string) $this->request->getPost('provider_name'),
            'vehicle_type'  => strtolower(trim((string) $this->request->getPost('vehicle_type'))),
            'driver_name'   => (string) $this->request->getPost('driver_name'),
            'driver_phone'  => (string) $this->request->getPost('driver_phone'),
            'seat_capacity' => (string) $this->request->getPost('seat_capacity'),
        ];
    }

    private function rules(): array
    {
        $typeKeys = implode(',', array_keys($this->vehicleTypeOptions()));

        return [
            'transport_name' => 'required|max_length[180]',
            'provider_name' => 'required|max_length[180]',
            'vehicle_type'  => 'required|in_list[' . $typeKeys . ']',
            'driver_name'   => 'permit_empty|max_length[120]',
            'driver_phone'  => 'permit_empty|max_length[40]',
            'seat_capacity' => 'permit_empty|integer|greater_than_equal_to[0]',
        ];
    }

    private function vehicleTypeOptions(): array
    {
        return [
            'self' => 'Self',
            'coaster' => 'Coaster',
            'car' => 'Car',
            'bus' => 'Bus',
            'van' => 'Van',
            'minibus' => 'Minibus',
            'suv' => 'SUV',
        ];
    }

    private function normalizeLocationCode(string $value): string
    {
        $value = strtoupper(trim($value));
        return preg_replace('/\s+/', '', $value) ?? '';
    }

    private function buildCompactRoute(array $legs): string
    {
        if ($legs === []) {
            return '';
        }

        $points = [];
        foreach ($legs as $index => $leg) {
            $from = $this->normalizeLocationCode((string) ($leg['from_code'] ?? ''));
            $to = $this->normalizeLocationCode((string) ($leg['to_code'] ?? ''));

            if ($index === 0 && $from !== '') {
                $points[] = $from;
            }
            if ($to !== '') {
                $points[] = $to;
            }
        }

        $points = array_values(array_filter($points, static function (string $point): bool {
            return $point !== '';
        }));

        return implode('-', $points);
    }
}
