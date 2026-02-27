<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\FlightModel;
use App\Models\PackageFlightModel;

class FlightController extends BaseController
{
    public function index(): string
    {
        $pnr = trim((string) $this->request->getGet('pnr'));
        $db = db_connect();

        $builder = $db->table('flights f')
            ->select('f.*, COUNT(pf.id) AS package_links')
            ->join('package_flights pf', 'pf.flight_id = f.id', 'left')
            ->groupBy('f.id')
            ->orderBy('f.departure_at', 'DESC');

        if ($pnr !== '') {
            $builder->like('f.pnr', $pnr);
        }

        return view('portal/flights/index', [
            'title'       => 'HJMS ERP | Flights',
            'headerTitle' => 'Flight Management',
            'activePage'  => 'flights',
            'userEmail'   => (string) session('user_email'),
            'rows'        => $builder->get()->getResultArray(),
            'pnr'         => $pnr,
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function add(): string
    {
        return view('portal/flights/add', [
            'title'       => 'HJMS ERP | Add Flight',
            'headerTitle' => 'Flight Management',
            'activePage'  => 'flights',
            'userEmail'   => (string) session('user_email'),
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function edit(int $id)
    {
        $model = new FlightModel();
        $row = $model->find($id);

        if (empty($row)) {
            return redirect()->to('/app/flights')->with('error', 'Flight not found.');
        }

        $db = db_connect();
        $packageLinks = $db->table('package_flights pf')
            ->select('pf.*, p.code AS package_code, p.name AS package_name')
            ->join('packages p', 'p.id = pf.package_id', 'left')
            ->where('pf.flight_id', $id)
            ->orderBy('pf.id', 'DESC')
            ->get()
            ->getResultArray();

        $packages = $db->table('packages')
            ->select('id, code, name')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        return view('portal/flights/edit', [
            'title'        => 'HJMS ERP | Edit Flight',
            'headerTitle'  => 'Flight Management',
            'activePage'   => 'flights',
            'userEmail'    => (string) session('user_email'),
            'row'          => $row,
            'packageLinks' => $packageLinks,
            'packages'     => $packages,
            'success'      => session()->getFlashdata('success'),
            'error'        => session()->getFlashdata('error'),
            'errors'       => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function departureBatches(): string
    {
        $rows = db_connect()->table('package_flights pf')
            ->select('DATE(pf.departure_at) AS departure_date, pf.airline, pf.flight_no, COUNT(DISTINCT pf.package_id) AS packages_count')
            ->groupBy('DATE(pf.departure_at), pf.airline, pf.flight_no')
            ->orderBy('DATE(pf.departure_at)', 'DESC')
            ->orderBy('pf.airline', 'ASC')
            ->get()
            ->getResultArray();

        return view('portal/flights/departure_batches', [
            'title'       => 'HJMS ERP | Departure Batches',
            'headerTitle' => 'Flight Management',
            'activePage'  => 'flights',
            'userEmail'   => (string) session('user_email'),
            'rows'        => $rows,
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function createFlight()
    {
        $payload = $this->extractFlightPayload();

        if (! $this->validateData($payload, $this->flightRules(true))) {
            return redirect()->to('/app/flights/add')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $fileData = $this->handleTicketUpload();
            if (isset($fileData['error'])) {
                return redirect()->to('/app/flights/add')->withInput()->with('error', $fileData['error']);
            }

            $model = new FlightModel();
            $model->insert($this->buildFlightData($payload, $fileData, true));

            return redirect()->to('/app/flights')->with('success', 'Flight created successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/flights/add')->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateFlight()
    {
        $flightId = (int) $this->request->getPost('flight_id');
        $payload = $this->extractFlightPayload();

        if ($flightId < 1) {
            return redirect()->to('/app/flights')->withInput()->with('error', 'Valid flight ID is required.');
        }

        if (! $this->validateData($payload, $this->flightRules(true))) {
            return redirect()->to('/app/flights/' . $flightId . '/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $fileData = $this->handleTicketUpload();
            if (isset($fileData['error'])) {
                return redirect()->to('/app/flights/' . $flightId . '/edit')->withInput()->with('error', $fileData['error']);
            }

            $model = new FlightModel();
            $model->update($flightId, $this->buildFlightData($payload, $fileData, false));

            return redirect()->to('/app/flights')->with('success', 'Flight updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/flights/' . $flightId . '/edit')->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteFlight()
    {
        $flightId = (int) $this->request->getPost('flight_id');

        if ($flightId < 1) {
            return redirect()->to('/app/flights')->with('error', 'Valid flight ID is required for delete.');
        }

        try {
            $linkModel = new PackageFlightModel();
            $linkModel->where('flight_id', $flightId)->delete();

            $model = new FlightModel();
            $deleted = $model->delete($flightId);

            if (! $deleted) {
                return redirect()->to('/app/flights')->with('error', 'Flight not found or already removed.');
            }

            return redirect()->to('/app/flights')->with('success', 'Flight deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/flights')->with('error', $e->getMessage());
        }
    }

    public function assignPackageFlight()
    {
        $payload = [
            'flight_id'    => (int) $this->request->getPost('flight_id'),
            'package_id'   => (int) $this->request->getPost('package_id'),
            'departure_at' => $this->normalizeDateTimeInput((string) $this->request->getPost('departure_at')),
            'arrival_at'   => $this->normalizeDateTimeInput((string) $this->request->getPost('arrival_at')),
        ];

        if (! $this->validateData($payload, [
            'flight_id'    => 'required|integer',
            'package_id'   => 'required|integer',
            'departure_at' => 'permit_empty|valid_date[Y-m-d H:i:s]',
            'arrival_at'   => 'permit_empty|valid_date[Y-m-d H:i:s]',
        ])) {
            return redirect()->to('/app/flights/' . $payload['flight_id'] . '/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $flight = (new FlightModel())->find($payload['flight_id']);
            if (empty($flight)) {
                return redirect()->to('/app/flights')->with('error', 'Flight not found for package assignment.');
            }

            $model = new PackageFlightModel();
            $model->insert([
                'package_id'    => $payload['package_id'],
                'flight_id'     => $payload['flight_id'],
                'airline'       => (string) ($flight['airline'] ?? ''),
                'flight_no'     => (string) ($flight['flight_no'] ?? ''),
                'departure_at'  => $payload['departure_at'] !== '' ? $payload['departure_at'] : ($flight['departure_at'] ?? null),
                'arrival_at'    => $payload['arrival_at'] !== '' ? $payload['arrival_at'] : ($flight['arrival_at'] ?? null),
                'created_at'    => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('/app/flights/' . $payload['flight_id'] . '/edit')->with('success', 'Package assigned to flight successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/flights/' . $payload['flight_id'] . '/edit')->withInput()->with('error', $e->getMessage());
        }
    }

    public function deletePackageFlight()
    {
        $linkId = (int) $this->request->getPost('link_id');
        $flightId = (int) $this->request->getPost('flight_id');

        if ($linkId < 1 || $flightId < 1) {
            return redirect()->to('/app/flights')->with('error', 'Valid assignment and flight IDs are required for delete.');
        }

        try {
            $model = new PackageFlightModel();
            $deleted = $model->delete($linkId);

            if (! $deleted) {
                return redirect()->to('/app/flights/' . $flightId . '/edit')->with('error', 'Package assignment not found or already removed.');
            }

            return redirect()->to('/app/flights/' . $flightId . '/edit')->with('success', 'Package assignment deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/flights/' . $flightId . '/edit')->with('error', $e->getMessage());
        }
    }

    private function extractFlightPayload(): array
    {
        return [
            'airline'           => (string) $this->request->getPost('airline'),
            'flight_no'         => (string) $this->request->getPost('flight_no'),
            'pnr'               => (string) $this->request->getPost('pnr'),
            'departure_airport' => (string) $this->request->getPost('departure_airport'),
            'arrival_airport'   => (string) $this->request->getPost('arrival_airport'),
            'departure_at'      => $this->normalizeDateTimeInput((string) $this->request->getPost('departure_at')),
            'arrival_at'        => $this->normalizeDateTimeInput((string) $this->request->getPost('arrival_at')),
        ];
    }

    private function normalizeDateTimeInput(string $value): string
    {
        $value = trim(str_replace('T', ' ', $value));

        if ($value === '') {
            return '';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value) === 1) {
            return $value . ':00';
        }

        return $value;
    }

    private function flightRules(bool $isRequired): array
    {
        $required = $isRequired ? 'required' : 'permit_empty';

        return [
            'airline'           => $required . '|max_length[120]',
            'flight_no'         => $required . '|max_length[30]',
            'pnr'               => 'permit_empty|max_length[30]',
            'departure_airport' => 'permit_empty|max_length[80]',
            'arrival_airport'   => 'permit_empty|max_length[80]',
            'departure_at'      => 'permit_empty|valid_date[Y-m-d H:i:s]',
            'arrival_at'        => 'permit_empty|valid_date[Y-m-d H:i:s]',
        ];
    }

    private function buildFlightData(array $payload, array $fileData, bool $isCreate): array
    {
        $data = [
            'airline'           => $payload['airline'],
            'flight_no'         => $payload['flight_no'],
            'pnr'               => $payload['pnr'] !== '' ? $payload['pnr'] : null,
            'departure_airport' => $payload['departure_airport'] !== '' ? $payload['departure_airport'] : null,
            'arrival_airport'   => $payload['arrival_airport'] !== '' ? $payload['arrival_airport'] : null,
            'departure_at'      => $payload['departure_at'] !== '' ? $payload['departure_at'] : null,
            'arrival_at'        => $payload['arrival_at'] !== '' ? $payload['arrival_at'] : null,
            'updated_at'        => date('Y-m-d H:i:s'),
        ];

        if ($isCreate) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        if (isset($fileData['ticket_file_name'])) {
            $data['ticket_file_name'] = $fileData['ticket_file_name'];
            $data['ticket_file_path'] = $fileData['ticket_file_path'];
        }

        return $data;
    }

    private function handleTicketUpload(): array
    {
        $file = $this->request->getFile('ticket_file');

        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return [];
        }

        if (! $file->isValid() || $file->hasMoved()) {
            return ['error' => 'Uploaded ticket file is invalid.'];
        }

        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
        $extension = strtolower((string) $file->getExtension());

        if (! in_array($extension, $allowedExtensions, true)) {
            return ['error' => 'Ticket file must be PDF, JPG, JPEG, or PNG.'];
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            return ['error' => 'Ticket file size must be 5MB or less.'];
        }

        $uploadDir = WRITEPATH . 'uploads/flights';
        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $storedName = $file->getRandomName();
        $file->move($uploadDir, $storedName);

        return [
            'ticket_file_name' => $file->getClientName(),
            'ticket_file_path' => 'uploads/flights/' . $storedName,
        ];
    }
}
