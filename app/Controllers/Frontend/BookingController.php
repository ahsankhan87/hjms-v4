<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\BookingPilgrimModel;
use App\Models\BookingModel;
use App\Models\Frontend\HotelFrontendModel;
use App\Models\Frontend\TicketFrontendModel;
use App\Models\Frontend\TransportFrontendModel;
use App\Models\PackageModel;
use App\Models\PilgrimModel;
use App\Services\PackagePricingService;

class BookingController extends BaseController
{
    public function package(int $id)
    {
        $item = $this->loadPackageItem($id);
        if ($item === null) {
            return redirect()->to('/packages/hajj')->with('error', 'Selected package was not found.');
        }

        return $this->renderForm('package', $item);
    }

    public function ticket(int $id)
    {
        $item = $this->loadTicketItem($id);
        if ($item === null) {
            return redirect()->to('/tickets')->with('error', 'Selected ticket was not found.');
        }

        return $this->renderForm('ticket', $item);
    }

    public function hotel(int $id)
    {
        $item = $this->loadHotelItem($id);
        if ($item === null) {
            return redirect()->to('/hotels')->with('error', 'Selected hotel was not found.');
        }

        return $this->renderForm('hotel', $item);
    }

    public function transport(int $id)
    {
        $item = $this->loadTransportItem($id);
        if ($item === null) {
            return redirect()->to('/transport')->with('error', 'Selected transport route was not found.');
        }

        return $this->renderForm('transport', $item);
    }

    public function store()
    {
        $payload = [
            'booking_type' => strtolower(trim((string) $this->request->getPost('booking_type'))),
            'item_id' => (int) $this->request->getPost('item_id'),
            'lead_name' => trim((string) $this->request->getPost('lead_name')),
            'lead_email' => trim((string) $this->request->getPost('lead_email')),
            'lead_phone' => trim((string) $this->request->getPost('lead_phone')),
            'passport_no' => trim((string) $this->request->getPost('passport_no')),
            'travelers_count' => (int) $this->request->getPost('travelers_count'),
            'special_requests' => trim((string) $this->request->getPost('special_requests')),
            'check_in' => trim((string) $this->request->getPost('check_in')),
            'check_out' => trim((string) $this->request->getPost('check_out')),
            'travel_date' => trim((string) $this->request->getPost('travel_date')),
        ];

        if (! in_array($payload['booking_type'], ['package', 'ticket', 'hotel', 'transport'], true)) {
            return redirect()->back()->withInput()->with('error', 'Invalid booking type.');
        }
        if ($payload['booking_type'] !== 'package') {
            return redirect()->back()->withInput()->with('error', 'Currently only package bookings are submitted for admin confirmation.');
        }

        $rules = [
            'booking_type' => 'required|in_list[package]',
            'item_id' => 'required|integer|greater_than[0]',
            'lead_name' => 'required|min_length[2]|max_length[160]',
            'lead_email' => 'required|valid_email|max_length[191]',
            'lead_phone' => 'required|max_length[40]',
            'passport_no' => 'permit_empty|max_length[80]',
            'travelers_count' => 'required|integer|greater_than[0]|less_than_equal_to[50]',
            'special_requests' => 'permit_empty|max_length[2000]',
            'travel_date' => 'permit_empty|valid_date',
            'check_in' => 'permit_empty|valid_date',
            'check_out' => 'permit_empty|valid_date',
        ];

        $rules['travel_date'] = 'required|valid_date';

        if (! $this->validateData($payload, $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $item = $this->loadPackageItem($payload['item_id']);
        if ($item === null) {
            return redirect()->back()->withInput()->with('error', 'Selected item is no longer available.');
        }

        $payload['passport_no'] = PilgrimModel::normalizePassportNo($payload['passport_no']);
        if ($payload['passport_no'] === '') {
            return redirect()->back()->withInput()->with('error', 'Passport number is required to create pilgrim records for booking.');
        }
        if (! preg_match('/^[A-Z0-9]{5,20}$/', $payload['passport_no'])) {
            return redirect()->back()->withInput()->with('error', 'Passport number must be 5-20 alphanumeric characters.');
        }

        $amount = $this->calculateAmount('package', $item, $payload['travelers_count'], '', '');
        $userId = (int) session('user_id');
        if ($userId < 1) {
            return redirect()->to('/login')->with('error', 'Please login to continue booking.');
        }

        $details = [
            'item_name' => (string) ($item['display_name'] ?? $item['name'] ?? ''),
            'lead_name' => $payload['lead_name'],
            'lead_email' => $payload['lead_email'],
            'lead_phone' => $payload['lead_phone'],
            'passport_no' => $payload['passport_no'],
            'travelers_count' => $payload['travelers_count'],
            'special_requests' => $payload['special_requests'],
            'travel_date' => $payload['travel_date'],
            'check_in' => $payload['check_in'],
            'check_out' => $payload['check_out'],
        ];

        if (! function_exists('active_season_id') || (int) active_season_id() < 1) {
            return redirect()->back()->withInput()->with('error', 'Active season is required before placing a package booking.');
        }

        $seasonId = (int) active_season_id();
        $travelersCount = max(1, (int) $payload['travelers_count']);
        $unitPrice = round($amount / $travelersCount, 2);
        $bookingNo = 'FBKG-' . date('YmdHis') . '-' . mt_rand(100, 999);
        $db = db_connect();

        $pilgrimModel = new PilgrimModel();
        $bookingPilgrimModel = new BookingPilgrimModel();

        $db->transStart();

        $leadPilgrimId = $this->resolveOrCreateLeadPilgrim(
            $db,
            $pilgrimModel,
            $seasonId,
            $payload['lead_name'],
            $payload['lead_email'],
            $payload['lead_phone'],
            $payload['passport_no']
        );

        if ($leadPilgrimId < 1) {
            $db->transRollback();

            return redirect()->back()->withInput()->with('error', 'Could not create pilgrim record for the booking.');
        }

        $pilgrimIds = [$leadPilgrimId];
        for ($i = 2; $i <= $travelersCount; $i++) {
            $placeholderPassport = $this->generatePlaceholderPassport($pilgrimModel, $seasonId, $i);
            $pilgrimModel->insert([
                'season_id' => $seasonId,
                'first_name' => 'Traveler',
                'last_name' => '#' . $i,
                'passport_no' => $placeholderPassport,
                'country' => 'Pakistan',
                'phone' => $payload['lead_phone'] !== '' ? $payload['lead_phone'] : null,
                'email' => $payload['lead_email'] !== '' ? $payload['lead_email'] : null,
                'description' => 'Auto-created placeholder pilgrim from frontend booking ' . $bookingNo . '. Update details from admin panel.',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $createdId = (int) $pilgrimModel->getInsertID();
            if ($createdId < 1) {
                $db->transRollback();

                return redirect()->back()->withInput()->with('error', 'Failed to create placeholder pilgrim rows for all travelers.');
            }

            $pilgrimIds[] = $createdId;
        }

        $bookingData = [
            'season_id' => $seasonId,
            'booking_no' => $bookingNo,
            'package_id' => (int) $payload['item_id'],
            'status' => 'pending',
            'pricing_tier' => 'sharing',
            'unit_price' => $unitPrice,
            'total_amount' => $amount,
            'pricing_source' => 'frontend_portal',
            'price_locked_at' => date('Y-m-d H:i:s'),
            'total_pilgrims' => count($pilgrimIds),
            'remarks' => json_encode($details),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($db->fieldExists('frontend_user_id', 'bookings')) {
            $bookingData['frontend_user_id'] = $userId;
        } else {
            $bookingData['created_by'] = $userId;
        }

        $bookingModel = new BookingModel();
        $bookingModel->insert($bookingData);
        $bookingId = (int) $bookingModel->getInsertID();
        if ($bookingId < 1) {
            $db->transRollback();

            return redirect()->back()->withInput()->with('error', 'Failed to create booking.');
        }

        foreach ($pilgrimIds as $pilgrimId) {
            $bookingPilgrimModel->insert([
                'booking_id' => $bookingId,
                'pilgrim_id' => (int) $pilgrimId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'Failed to create booking with linked pilgrims.');
        }

        return redirect()->to('/portal/bookings')->with('success', 'Booking request submitted successfully. Status: Pending.');
    }

    public function portalBookings()
    {
        $userId = (int) session('user_id');
        $rows = [];
        if ($userId > 0 && db_connect()->tableExists('bookings')) {
            $builder = db_connect()->table('bookings')->orderBy('id', 'DESC');
            if (db_connect()->fieldExists('frontend_user_id', 'bookings')) {
                $builder->where('frontend_user_id', $userId);
            } else {
                $builder->where('created_by', $userId);
            }
            $rows = $builder->get()->getResultArray();
        }

        return view('frontend/portal/bookings', [
            'title' => 'My Bookings | HJMS Travel',
            'rows' => $rows,
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    private function renderForm(string $type, array $item)
    {
        return view('frontend/booking/form', [
            'title' => 'Book ' . ucfirst($type) . ' | HJMS Travel',
            'bookingType' => $type,
            'item' => $item,
            'errors' => session()->getFlashdata('errors') ?: [],
            'error' => session()->getFlashdata('error'),
        ]);
    }

    private function loadBookingItemByType(string $type, int $id): ?array
    {
        if ($type === 'package') {
            return $this->loadPackageItem($id);
        }
        if ($type === 'ticket') {
            return $this->loadTicketItem($id);
        }
        if ($type === 'hotel') {
            return $this->loadHotelItem($id);
        }

        return $this->loadTransportItem($id);
    }

    private function loadPackageItem(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        $row = (new PackageModel())->where('id', $id)->where('is_active', 1)->first();
        if ($row === null) {
            return null;
        }

        $summary = (new PackagePricingService())->summarizePackage($id);
        $amount = 0.0;
        if (($summary['mode'] ?? 'tiered') === 'flat') {
            $amount = (float) ($summary['flat_price'] ?? 0);
        } else {
            $map = (array) ($summary['price_map'] ?? []);
            if ($map !== []) {
                $amount = min(array_map('floatval', $map));
            }
        }
        if ($amount <= 0) {
            $amount = (float) ($row['selling_price'] ?? 0);
        }

        return [
            'id' => (int) $row['id'],
            'display_name' => (string) ($row['name'] ?? 'Package'),
            'subtitle' => (string) ($row['package_type'] ?? 'Package'),
            'price' => round($amount, 2),
            'date_hint' => (string) ($row['departure_date'] ?? ''),
            'meta' => [
                'duration_days' => (int) ($row['duration_days'] ?? 0),
            ],
        ];
    }

    private function loadTicketItem(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        $rows = (new TicketFrontendModel())->searchTickets(['limit' => 500]);
        foreach ($rows as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                return [
                    'id' => $id,
                    'display_name' => (string) (($row['airline'] ?? 'Ticket') . ' ' . ($row['flight_no'] ?? '')),
                    'subtitle' => (string) (($row['departure_airport'] ?? '-') . ' → ' . ($row['arrival_airport'] ?? '-')),
                    'price' => round((float) ($row['fare'] ?? 0), 2),
                    'date_hint' => (string) ($row['departure_at'] ?? ''),
                    'meta' => $row,
                ];
            }
        }

        return null;
    }

    private function loadHotelItem(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        $rows = (new HotelFrontendModel())->searchHotels(['limit' => 500]);
        foreach ($rows as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                return [
                    'id' => $id,
                    'display_name' => (string) ($row['name'] ?? 'Hotel'),
                    'subtitle' => (string) ($row['city'] ?? ''),
                    'price' => round((float) ($row['price_per_night'] ?? 0), 2),
                    'date_hint' => '',
                    'meta' => $row,
                ];
            }
        }

        return null;
    }

    private function loadTransportItem(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        $rows = (new TransportFrontendModel())->listRoutes(['limit' => 500]);
        foreach ($rows as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                return [
                    'id' => $id,
                    'display_name' => (string) (($row['from_code'] ?? '-') . ' → ' . ($row['to_code'] ?? '-')),
                    'subtitle' => (string) ($row['vehicle_type'] ?? 'Transport'),
                    'price' => round((float) ($row['avg_price'] ?? 0), 2),
                    'date_hint' => '',
                    'meta' => $row,
                ];
            }
        }

        return null;
    }

    private function calculateAmount(string $type, array $item, int $travelersCount, string $checkIn, string $checkOut): float
    {
        $base = (float) ($item['price'] ?? 0);
        if ($base <= 0) {
            return 0;
        }

        if ($type === 'hotel') {
            $nights = 1;
            if ($checkIn !== '' && $checkOut !== '') {
                $diff = (int) floor((strtotime($checkOut) - strtotime($checkIn)) / 86400);
                if ($diff > 0) {
                    $nights = $diff;
                }
            }

            return round($base * $nights * max(1, $travelersCount), 2);
        }

        return round($base * max(1, $travelersCount), 2);
    }

    private function resolveOrCreateLeadPilgrim(
        \CodeIgniter\Database\BaseConnection $db,
        PilgrimModel $pilgrimModel,
        int $seasonId,
        string $leadName,
        string $leadEmail,
        string $leadPhone,
        string $passportNo
    ): int {
        $existing = $db->table('pilgrims')
            ->select('id, first_name, last_name, phone, email')
            ->where('season_id', $seasonId)
            ->where("UPPER(REPLACE(passport_no, ' ', '')) = " . $db->escape(PilgrimModel::normalizePassportNo($passportNo)), null, false)
            ->get()
            ->getRowArray();

        if (is_array($existing)) {
            $update = [];
            if (trim((string) ($existing['phone'] ?? '')) === '' && $leadPhone !== '') {
                $update['phone'] = $leadPhone;
            }
            if (trim((string) ($existing['email'] ?? '')) === '' && $leadEmail !== '') {
                $update['email'] = $leadEmail;
            }
            if ($update !== []) {
                $update['updated_at'] = date('Y-m-d H:i:s');
                $pilgrimModel->update((int) $existing['id'], $update);
            }

            return (int) $existing['id'];
        }

        $nameParts = preg_split('/\s+/', trim($leadName)) ?: [];
        $firstName = trim((string) ($nameParts[0] ?? 'Pilgrim'));
        $lastName = trim((string) implode(' ', array_slice($nameParts, 1)));
        if ($lastName === '') {
            $lastName = '-';
        }

        $pilgrimModel->insert([
            'season_id' => $seasonId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'passport_no' => PilgrimModel::normalizePassportNo($passportNo),
            'country' => 'Pakistan',
            'phone' => $leadPhone !== '' ? $leadPhone : null,
            'email' => $leadEmail !== '' ? $leadEmail : null,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $pilgrimModel->getInsertID();
    }

    private function generatePlaceholderPassport(PilgrimModel $pilgrimModel, int $seasonId, int $index): string
    {
        for ($attempt = 1; $attempt <= 30; $attempt++) {
            $candidate = 'TMP' . $seasonId . $index . mt_rand(1000, 9999) . $attempt;
            $candidate = substr(preg_replace('/[^A-Z0-9]/', '', strtoupper($candidate)) ?? '', 0, 20);
            if (strlen($candidate) < 5) {
                continue;
            }
            if (! $pilgrimModel->passportExists($candidate, $seasonId, null)) {
                return $candidate;
            }
        }

        return 'TMP' . date('His') . mt_rand(1000, 9999);
    }
}
