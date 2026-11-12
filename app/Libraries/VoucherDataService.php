<?php

namespace App\Libraries;

/**
 * Builds the data set a final voucher needs.
 *
 * Shared by the authenticated voucher screen and the public QR voucher page so
 * both always render exactly the same content.
 */
class VoucherDataService
{
    /**
     * @param array<string, mixed> $booking Booking row (joined with package/company columns).
     *
     * @return array<string, mixed>
     */
    public function build(int $bookingId, array $booking): array
    {
        $db        = db_connect();
        $packageId = (int) ($booking['package_id'] ?? 0);

        $flightRows = $db->table('package_flights pf')
            ->select('pf.*, f.departure_airport, f.arrival_airport, f.pnr, f.return_airline, f.return_flight_no, f.return_pnr, f.return_departure_airport, f.return_arrival_airport, f.return_departure_at, f.return_arrival_at')
            ->join('flights f', 'f.id = pf.flight_id', 'left')
            ->where('pf.package_id', $packageId)
            ->orderBy('pf.departure_at', 'ASC')
            ->orderBy('pf.id', 'ASC')
            ->get()
            ->getResultArray();

        if ($db->tableExists('package_hotel_stays')) {
            $hotelRows = $db->table('package_hotel_stays phs')
                ->select('phs.id, phs.check_in_date, phs.check_out_date, ph.hotel_name, h.city AS hotel_city, h.name AS hotel_master_name')
                ->join('package_hotels ph', 'ph.id = phs.package_hotel_id', 'left')
                ->join('hotels h', 'h.id = ph.hotel_id', 'left')
                ->where('phs.package_id', $packageId)
                ->orderBy('phs.check_in_date', 'ASC')
                ->orderBy('phs.id', 'ASC')
                ->get()
                ->getResultArray();
        } else {
            $hotelRows = $db->table('package_hotels ph')
                ->select('ph.*, h.city AS hotel_city, h.name AS hotel_master_name')
                ->join('hotels h', 'h.id = ph.hotel_id', 'left')
                ->where('ph.package_id', $packageId)
                ->orderBy('ph.check_in_date', 'ASC')
                ->orderBy('ph.id', 'ASC')
                ->get()
                ->getResultArray();
        }

        $transportRows = $db->table('package_transports pt')
            ->select('pt.*, t.transport_name AS master_transport_name')
            ->join('transports t', 't.id = pt.transport_id', 'left')
            ->where('pt.package_id', $packageId)
            ->orderBy('pt.id', 'ASC')
            ->get()
            ->getResultArray();

        [$transportRouteByTransport, $transportZiaratByTransport] = $this->transportMeta($transportRows);

        $pilgrimRows = $db->table('booking_pilgrims bp')
            ->select('p.first_name, p.last_name, p.passport_no, p.date_of_birth, p.gender')
            ->join('pilgrims p', 'p.id = bp.pilgrim_id', 'left')
            ->where('bp.booking_id', $bookingId)
            ->orderBy('bp.id', 'ASC')
            ->get()
            ->getResultArray();

        $flightRow      = $flightRows[0] ?? null;
        $outboundFlight = $flightRow;
        $returnFlight   = $this->returnFlight($flightRow);

        $arrivalAt   = (string) ($outboundFlight['arrival_at'] ?? $outboundFlight['departure_at'] ?? '');
        $departureAt = (string) ($returnFlight['departure_at'] ?? $outboundFlight['departure_at'] ?? '');

        if ($arrivalAt !== '') {
            $booking['arrival_date'] = date('Y-m-d H:i', strtotime($arrivalAt));
        }
        if ($departureAt !== '') {
            $booking['departure_date'] = date('Y-m-d H:i', strtotime($departureAt));
        }

        $booking = $this->applyHotelDates($booking, $hotelRows);

        return [
            'booking'                    => $booking,
            'outboundFlight'             => $outboundFlight,
            'returnFlight'               => $returnFlight,
            'hotelRows'                  => $hotelRows,
            'transportRows'              => $transportRows,
            'transportRouteByTransport'  => $transportRouteByTransport,
            'transportZiaratByTransport' => $transportZiaratByTransport,
            'pilgrimRows'                => $pilgrimRows,
        ];
    }

    /**
     * @param array<string, mixed>|null $flightRow
     *
     * @return array<string, string>|null
     */
    private function returnFlight(?array $flightRow): ?array
    {
        if (empty($flightRow)) {
            return null;
        }

        $returnFlight = [
            'airline'           => trim((string) ($flightRow['return_airline'] ?? '')),
            'flight_no'         => trim((string) ($flightRow['return_flight_no'] ?? '')),
            'pnr'               => trim((string) ($flightRow['return_pnr'] ?? '')),
            'departure_airport' => trim((string) ($flightRow['return_departure_airport'] ?? '')),
            'arrival_airport'   => trim((string) ($flightRow['return_arrival_airport'] ?? '')),
            'departure_at'      => trim((string) ($flightRow['return_departure_at'] ?? '')),
            'arrival_at'        => trim((string) ($flightRow['return_arrival_at'] ?? '')),
        ];

        if (
            $returnFlight['departure_airport'] === ''
            && $returnFlight['arrival_airport'] === ''
            && $returnFlight['departure_at'] === ''
            && $returnFlight['arrival_at'] === ''
            && $returnFlight['flight_no'] === ''
            && $returnFlight['pnr'] === ''
        ) {
            return null;
        }

        return $returnFlight;
    }

    /**
     * @param array<int, array<string, mixed>> $transportRows
     *
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    private function transportMeta(array $transportRows): array
    {
        $transportRouteByTransport  = [];
        $transportZiaratByTransport = [];
        $db                         = db_connect();

        if (! $db->tableExists('transport_legs') || $transportRows === []) {
            return [$transportRouteByTransport, $transportZiaratByTransport];
        }

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

            $points      = [];
            $ziaratSites = [];
            foreach ($legs as $index => $leg) {
                $from = strtoupper(str_replace(' ', '', (string) ($leg['from_code'] ?? '')));
                $to   = strtoupper(str_replace(' ', '', (string) ($leg['to_code'] ?? '')));

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

        return [$transportRouteByTransport, $transportZiaratByTransport];
    }

    /**
     * @param array<string, mixed>             $booking
     * @param array<int, array<string, mixed>> $hotelRows
     *
     * @return array<string, mixed>
     */
    private function applyHotelDates(array $booking, array $hotelRows): array
    {
        if ($hotelRows !== []) {
            $hotelStart  = null;
            $hotelEnd    = null;
            $hotelNights = 0;

            foreach ($hotelRows as $hotel) {
                $checkIn  = (string) ($hotel['check_in_date'] ?? '');
                $checkOut = (string) ($hotel['check_out_date'] ?? '');

                if ($checkIn === '' || $checkOut === '') {
                    continue;
                }

                $startTs = strtotime($checkIn);
                $endTs   = strtotime($checkOut);
                if (! $startTs || ! $endTs || $endTs <= $startTs) {
                    continue;
                }

                $hotelNights += (int) floor(($endTs - $startTs) / 86400);
                $hotelStart = $hotelStart === null ? $startTs : min($hotelStart, $startTs);
                $hotelEnd   = $hotelEnd === null ? $endTs : max($hotelEnd, $endTs);
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

        if (empty($booking['duration_days']) && ! empty($booking['arrival_date']) && ! empty($booking['departure_date'])) {
            $start = strtotime((string) $booking['arrival_date']);
            $end   = strtotime((string) $booking['departure_date']);
            if ($start && $end && $end >= $start) {
                $booking['duration_days'] = (int) floor(($end - $start) / 86400);
            }
        }

        return $booking;
    }
}
