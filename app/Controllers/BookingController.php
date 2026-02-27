<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BookingModel;
use App\Models\BookingPilgrimModel;

class BookingController extends BaseController
{
    public function index()
    {
        $db = db_connect();
        $bookingModel = new BookingModel();
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/app/seasons')->with('error', 'Please create and activate a season first.');
        }
        $selectedPackageId = (int) ($this->request->getGet('package_id') ?? 0);

        return view('portal/bookings/index', [
            'title'      => 'HJMS ERP | Bookings',
            'headerTitle' => 'Booking Operations',
            'activePage' => 'bookings',
            'userEmail' => (string) session('user_email'),
            'rows'      => $bookingModel->where('season_id', $seasonId)->orderBy('id', 'DESC')->findAll(),
            'packages'  => $db->table('packages')->where('season_id', $seasonId)->orderBy('departure_date', 'DESC')->get()->getResultArray(),
            'selectedPackageId' => $selectedPackageId,
            'agents'    => $db->table('agents')->orderBy('name', 'ASC')->get()->getResultArray(),
            'branches'  => $db->table('branches')->orderBy('name', 'ASC')->get()->getResultArray(),
            'pilgrims'  => $db->table('pilgrims')->select('id, first_name, last_name, passport_no')->where('season_id', $seasonId)->orderBy('id', 'DESC')->get()->getResultArray(),
            'success'   => session()->getFlashdata('success'),
            'error'     => session()->getFlashdata('error'),
            'errors'    => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function createBooking()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/app/seasons')->with('error', 'Please create and activate a season first.');
        }

        $pilgrimIds = (array) $this->request->getPost('pilgrim_ids');
        $pilgrimIds = array_values(array_filter(array_map('intval', $pilgrimIds)));

        $payload = [
            'package_id'  => (int) $this->request->getPost('package_id'),
            'agent_id'    => $this->request->getPost('agent_id') !== '' ? (int) $this->request->getPost('agent_id') : null,
            'branch_id'   => $this->request->getPost('branch_id') !== '' ? (int) $this->request->getPost('branch_id') : null,
            'status'      => (string) ($this->request->getPost('status') ?: 'draft'),
            'remarks'     => (string) $this->request->getPost('remarks'),
            'pilgrim_ids' => $pilgrimIds,
        ];

        if (! $this->validateData($payload, [
            'package_id'  => 'required|integer',
            'agent_id'    => 'permit_empty|integer',
            'branch_id'   => 'permit_empty|integer',
            'status'      => 'required|in_list[draft,confirmed,cancelled]',
            'remarks'     => 'permit_empty|max_length[5000]',
            'pilgrim_ids' => 'required',
        ])) {
            return redirect()->to('/app/bookings')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $bookingModel = new BookingModel();
            $bookingPilgrimModel = new BookingPilgrimModel();
            $db = db_connect();

            $package = $db->table('packages')->select('id')->where('id', $payload['package_id'])->where('season_id', $seasonId)->get()->getRowArray();
            if (empty($package)) {
                return redirect()->to('/app/bookings')->withInput()->with('error', 'Selected package is not in active season.');
            }

            if ($pilgrimIds === []) {
                return redirect()->to('/app/bookings')->withInput()->with('error', 'Please select at least one pilgrim.');
            }

            $seasonPilgrimCount = $db->table('pilgrims')->where('season_id', $seasonId)->whereIn('id', $pilgrimIds)->countAllResults();
            if ($seasonPilgrimCount !== count($pilgrimIds)) {
                return redirect()->to('/app/bookings')->withInput()->with('error', 'Some selected pilgrims do not belong to active season.');
            }

            $db->transStart();

            $bookingNo = 'BKG-' . date('YmdHis') . '-' . mt_rand(100, 999);
            $bookingModel->insert([
                'season_id'      => $seasonId,
                'booking_no'     => $bookingNo,
                'package_id'     => $payload['package_id'],
                'agent_id'       => $payload['agent_id'],
                'branch_id'      => $payload['branch_id'],
                'status'         => $payload['status'],
                'total_pilgrims' => count($pilgrimIds),
                'remarks'        => $payload['remarks'] !== '' ? $payload['remarks'] : null,
                'created_by'     => session('user_id') ? (int) session('user_id') : null,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);

            $bookingId = (int) $bookingModel->getInsertID();
            foreach ($pilgrimIds as $pilgrimId) {
                $bookingPilgrimModel->insert([
                    'booking_id' => $bookingId,
                    'pilgrim_id' => $pilgrimId,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $db->transComplete();

            if (! $db->transStatus()) {
                throw new \RuntimeException('Failed to create booking.');
            }

            return redirect()->to('/app/bookings')->with('success', 'Booking created successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/bookings')->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateBooking()
    {
        $bookingId = (int) $this->request->getPost('booking_id');
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/app/seasons')->with('error', 'Please create and activate a season first.');
        }
        $postedPilgrims = $this->request->getPost('pilgrim_ids');

        $payload = [
            'package_id'  => (string) $this->request->getPost('package_id'),
            'agent_id'    => (string) $this->request->getPost('agent_id'),
            'branch_id'   => (string) $this->request->getPost('branch_id'),
            'status'      => (string) $this->request->getPost('status'),
            'remarks'     => (string) $this->request->getPost('remarks'),
        ];

        if ($bookingId < 1) {
            return redirect()->to('/app/bookings')->withInput()->with('error', 'Valid booking ID is required.');
        }

        if (! $this->validateData($payload, [
            'package_id' => 'permit_empty|integer',
            'agent_id'   => 'permit_empty|integer',
            'branch_id'  => 'permit_empty|integer',
            'status'     => 'permit_empty|in_list[draft,confirmed,cancelled]',
            'remarks'    => 'permit_empty|max_length[5000]',
        ])) {
            return redirect()->to('/app/bookings')->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [];
        if ($payload['package_id'] !== '') {
            $data['package_id'] = (int) $payload['package_id'];
        }
        if ($payload['agent_id'] !== '') {
            $data['agent_id'] = (int) $payload['agent_id'];
        }
        if ($payload['branch_id'] !== '') {
            $data['branch_id'] = (int) $payload['branch_id'];
        }
        if ($payload['status'] !== '') {
            $data['status'] = $payload['status'];
        }
        if ($payload['remarks'] !== '') {
            $data['remarks'] = $payload['remarks'];
        }

        $pilgrimIds = [];
        if ($postedPilgrims !== null) {
            $pilgrimIds = array_values(array_filter(array_map('intval', (array) $postedPilgrims)));
            if ($pilgrimIds === []) {
                return redirect()->to('/app/bookings')->withInput()->with('error', 'Select at least one pilgrim when updating pilgrims list.');
            }
            $data['total_pilgrims'] = count($pilgrimIds);
        }

        if ($data === []) {
            return redirect()->to('/app/bookings')->withInput()->with('error', 'Provide at least one field to update for booking.');
        }

        try {
            $bookingModel = new BookingModel();
            $bookingPilgrimModel = new BookingPilgrimModel();
            $db = db_connect();

            $existing = $bookingModel->where('id', $bookingId)->where('season_id', $seasonId)->first();
            if (empty($existing)) {
                return redirect()->to('/app/bookings')->with('error', 'Booking not found in active season.');
            }

            if (isset($data['package_id'])) {
                $package = $db->table('packages')->select('id')->where('id', (int) $data['package_id'])->where('season_id', $seasonId)->get()->getRowArray();
                if (empty($package)) {
                    return redirect()->to('/app/bookings')->withInput()->with('error', 'Selected package is not in active season.');
                }
            }

            $db->transStart();

            $bookingModel->update($bookingId, $data + ['updated_at' => date('Y-m-d H:i:s')]);

            if ($postedPilgrims !== null) {
                $seasonPilgrimCount = $db->table('pilgrims')->where('season_id', $seasonId)->whereIn('id', $pilgrimIds)->countAllResults();
                if ($seasonPilgrimCount !== count($pilgrimIds)) {
                    return redirect()->to('/app/bookings')->withInput()->with('error', 'Some selected pilgrims do not belong to active season.');
                }

                $bookingPilgrimModel->where('booking_id', $bookingId)->delete();
                foreach ($pilgrimIds as $pilgrimId) {
                    $bookingPilgrimModel->insert([
                        'booking_id' => $bookingId,
                        'pilgrim_id' => $pilgrimId,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            $db->transComplete();
            if (! $db->transStatus()) {
                throw new \RuntimeException('Failed to update booking.');
            }

            return redirect()->to('/app/bookings')->with('success', 'Booking updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/bookings')->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteBooking()
    {
        $bookingId = (int) $this->request->getPost('booking_id');
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/app/seasons')->with('error', 'Please create and activate a season first.');
        }
        if ($bookingId < 1) {
            return redirect()->to('/app/bookings')->with('error', 'Valid booking ID is required for delete.');
        }

        try {
            $bookingModel = new BookingModel();
            $bookingPilgrimModel = new BookingPilgrimModel();
            $db = db_connect();

            $existing = $bookingModel->where('id', $bookingId)->where('season_id', $seasonId)->first();
            if (empty($existing)) {
                return redirect()->to('/app/bookings')->with('error', 'Booking not found in active season.');
            }

            $db->transStart();
            $bookingPilgrimModel->where('booking_id', $bookingId)->delete();
            $bookingModel->delete($bookingId);
            $db->transComplete();

            if (! $db->transStatus()) {
                throw new \RuntimeException('Failed to delete booking.');
            }

            return redirect()->to('/app/bookings')->with('success', 'Booking deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/bookings')->with('error', $e->getMessage());
        }
    }

    public function voucher(int $bookingId)
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/app/seasons')->with('error', 'Please create and activate a season first.');
        }

        if ($bookingId < 1) {
            return redirect()->to('/app/bookings')->with('error', 'Valid booking ID is required for voucher.');
        }

        $db = db_connect();

        $booking = $db->table('bookings b')
            ->select('b.*, p.code AS package_code, p.name AS package_name, p.package_type')
            ->join('packages p', 'p.id = b.package_id', 'left')
            ->where('b.season_id', $seasonId)
            ->where('b.id', $bookingId)
            ->get()
            ->getRowArray();

        if (empty($booking)) {
            return redirect()->to('/app/bookings')->with('error', 'Booking not found.');
        }

        $paymentRows = $db->table('payments')
            ->where('booking_id', $bookingId)
            ->where('season_id', $seasonId)
            ->where('status', 'posted')
            ->orderBy('payment_date', 'ASC')
            ->get()
            ->getResultArray();

        if ($paymentRows === []) {
            return redirect()->to('/app/bookings')->with('error', 'Final voucher is available after payment is posted for this booking.');
        }

        $flightRows = $db->table('package_flights pf')
            ->select('pf.*, f.departure_airport, f.arrival_airport, f.pnr')
            ->join('flights f', 'f.id = pf.flight_id', 'left')
            ->where('pf.package_id', (int) $booking['package_id'])
            ->orderBy('pf.departure_at', 'ASC')
            ->orderBy('pf.id', 'ASC')
            ->get()
            ->getResultArray();

        $hotelRows = $db->table('package_hotels ph')
            ->select('ph.*, h.city AS hotel_city, h.name AS hotel_master_name')
            ->join('hotels h', 'h.id = ph.hotel_id', 'left')
            ->where('ph.package_id', (int) $booking['package_id'])
            ->orderBy('ph.check_in_date', 'ASC')
            ->orderBy('ph.id', 'ASC')
            ->get()
            ->getResultArray();

        $transportRows = $db->table('package_transports pt')
            ->select('pt.*, t.transport_name AS master_transport_name')
            ->join('transports t', 't.id = pt.transport_id', 'left')
            ->where('pt.package_id', (int) $booking['package_id'])
            ->orderBy('pt.id', 'ASC')
            ->get()
            ->getResultArray();

        $transportRouteByTransport = [];
        $transportZiaratByTransport = [];

        if ($db->tableExists('transport_legs') && $transportRows !== []) {
            foreach ($transportRows as $transportRow) {
                $transportId = (int) ($transportRow['transport_id'] ?? 0);
                if ($transportId < 1) {
                    continue;
                }

                $legs = $db->table('transport_legs')
                    ->where('transport_id', $transportId)
                    ->orderBy('seq_no', 'ASC')
                    ->orderBy('id', 'ASC')
                    ->get()
                    ->getResultArray();

                if ($legs === []) {
                    continue;
                }

                $points = [];
                $ziaratSites = [];
                foreach ($legs as $index => $leg) {
                    $from = strtoupper(str_replace(' ', '', (string) ($leg['from_code'] ?? '')));
                    $to = strtoupper(str_replace(' ', '', (string) ($leg['to_code'] ?? '')));

                    if ($index === 0 && $from !== '') {
                        $points[] = $from;
                    }
                    if ($to !== '') {
                        $points[] = $to;
                    }

                    if ((int) ($leg['is_ziarat'] ?? 0) === 1) {
                        $site = trim((string) ($leg['ziarat_site'] ?? ''));
                        if ($site !== '') {
                            $ziaratSites[] = $site;
                        }
                    }
                }

                $route = implode('-', array_values(array_filter($points, static function (string $point): bool {
                    return $point !== '';
                })));

                if ($route !== '') {
                    $transportRouteByTransport[$transportId] = $route;
                }

                $transportZiaratByTransport[$transportId] = $ziaratSites !== []
                    ? implode(', ', array_values(array_unique($ziaratSites)))
                    : 'No';
            }
        }

        $pilgrimRows = $db->table('booking_pilgrims bp')
            ->select('p.first_name, p.last_name, p.passport_no, p.date_of_birth, p.gender')
            ->join('pilgrims p', 'p.id = bp.pilgrim_id', 'left')
            ->where('bp.booking_id', $bookingId)
            ->orderBy('bp.id', 'ASC')
            ->get()
            ->getResultArray();

        $totalPaid = 0.0;
        foreach ($paymentRows as $payment) {
            $amount = (float) ($payment['amount'] ?? 0);
            $totalPaid += (string) ($payment['payment_type'] ?? 'payment') === 'refund' ? -$amount : $amount;
        }

        $outboundFlight = $flightRows[0] ?? null;
        $returnFlight = count($flightRows) > 1 ? $flightRows[count($flightRows) - 1] : null;

        $arrivalAt = (string) ($outboundFlight['arrival_at'] ?? $outboundFlight['departure_at'] ?? '');
        $departureAt = (string) ($returnFlight['departure_at'] ?? $outboundFlight['departure_at'] ?? '');

        if ($arrivalAt !== '') {
            $booking['arrival_date'] = date('Y-m-d', strtotime($arrivalAt));
        }
        if ($departureAt !== '') {
            $booking['departure_date'] = date('Y-m-d', strtotime($departureAt));
        }

        if ($hotelRows !== []) {
            $hotelStart = null;
            $hotelEnd = null;
            $hotelNights = 0;

            foreach ($hotelRows as $hotel) {
                $checkIn = (string) ($hotel['check_in_date'] ?? '');
                $checkOut = (string) ($hotel['check_out_date'] ?? '');

                if ($checkIn === '' || $checkOut === '') {
                    continue;
                }

                $startTs = strtotime($checkIn);
                $endTs = strtotime($checkOut);
                if (! $startTs || ! $endTs || $endTs <= $startTs) {
                    continue;
                }

                $hotelNights += (int) floor(($endTs - $startTs) / 86400);
                $hotelStart = $hotelStart === null ? $startTs : min($hotelStart, $startTs);
                $hotelEnd = $hotelEnd === null ? $endTs : max($hotelEnd, $endTs);
            }

            if ($hotelNights > 0) {
                $booking['duration_days'] = $hotelNights;
            }
            if ($hotelStart !== null) {
                $booking['arrival_date'] = date('Y-m-d', $hotelStart);
            }
            if ($hotelEnd !== null) {
                $booking['departure_date'] = date('Y-m-d', $hotelEnd);
            }
        }

        if (empty($booking['duration_days']) && !empty($booking['arrival_date']) && !empty($booking['departure_date'])) {
            $start = strtotime((string) $booking['arrival_date']);
            $end = strtotime((string) $booking['departure_date']);
            if ($start && $end && $end >= $start) {
                $booking['duration_days'] = (int) floor(($end - $start) / 86400);
            }
        }

        return view('portal/bookings/voucher', [
            'title' => 'HJMS ERP | Final Voucher',
            'booking' => $booking,
            'payments' => $paymentRows,
            'totalPaid' => $totalPaid,
            'outboundFlight' => $outboundFlight,
            'returnFlight' => $returnFlight,
            'hotelRows' => $hotelRows,
            'transportRows' => $transportRows,
            'transportRouteByTransport' => $transportRouteByTransport,
            'transportZiaratByTransport' => $transportZiaratByTransport,
            'pilgrimRows' => $pilgrimRows,
            'voucherNo' => 'VCH-' . str_pad((string) $bookingId, 5, '0', STR_PAD_LEFT),
            'voucherDate' => date('d M Y'),
        ]);
    }
}
