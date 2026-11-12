<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\VoucherDataService;
use App\Libraries\VoucherShareService;

/**
 * Login-free voucher page served to whoever scans the QR code printed on a
 * final voucher. Access is granted purely by the unguessable share token, and
 * only for bookings that have already been confirmed and paid.
 */
class VoucherController extends BaseController
{
    public function show(string $token)
    {
        $bookingId = (new VoucherShareService())->bookingIdForToken($token);

        if ($bookingId === null) {
            return $this->notFound();
        }

        $db = db_connect();

        $booking = $db->table('bookings b')
            ->select('b.*, p.code AS package_code, p.name AS package_name, p.package_type, p.include_hotel, p.include_ticket, p.include_transport, p.departure_date AS package_departure_date, p.arrival_date AS package_arrival_date, p.voucher_instructions_ur, p.voucher_instructions_en, p.makkah_contact, p.makkah_contact_en, p.madina_contact, p.madina_contact_en, p.transport_contact, p.transport_contact_en, c.id AS shirka_id, c.name AS shirka_name, c.logo_url AS shirka_logo_url, c.address AS shirka_address')
            ->join('packages p', 'p.id = b.package_id', 'left')
            ->join('companies c', 'c.id = b.company_id', 'left')
            ->where('b.id', $bookingId)
            ->get()
            ->getRowArray();

        if (empty($booking) || (string) ($booking['status'] ?? '') !== 'confirmed') {
            return $this->notFound();
        }

        $hasPostedPayment = $db->table('payments')
            ->where('booking_id', $bookingId)
            ->where('status', 'posted')
            ->countAllResults() > 0;

        if (! $hasPostedPayment) {
            return $this->notFound();
        }

        $voucherData = (new VoucherDataService())->build($bookingId, $booking);
        $booking     = $voucherData['booking'];

        $shareService = new VoucherShareService();
        $qrUrl        = site_url('v/' . $token);

        $this->response->setHeader('X-Robots-Tag', 'noindex, nofollow');

        return view('portal/bookings/voucher', array_merge($voucherData, [
            'title' => 'HJMS | Voucher ' . 'VCH-' . str_pad((string) $bookingId, 5, '0', STR_PAD_LEFT),
            'mainCompany' => main_company(),
            'shirkaCompany' => [
                'id' => $booking['shirka_id'] ?? null,
                'name' => $booking['shirka_name'] ?? '',
                'logo_url' => $booking['shirka_logo_url'] ?? '',
                'address' => $booking['shirka_address'] ?? '',
            ],
            'payments' => [],
            'totalPaid' => 0.0,
            'includeHotel'     => (int) ($booking['include_hotel']     ?? 1) === 1,
            'includeTicket'    => (int) ($booking['include_ticket']    ?? 1) === 1,
            'includeTransport' => (int) ($booking['include_transport'] ?? 1) === 1,
            'voucherNo' => 'VCH-' . str_pad((string) $bookingId, 5, '0', STR_PAD_LEFT),
            'voucherDate' => date('d M Y'),
            'qrUrl' => $qrUrl,
            'qrDataUri' => $shareService->qrDataUri($qrUrl),
            'publicView' => true,
        ]));
    }

    private function notFound()
    {
        $this->response->setStatusCode(404);
        $this->response->setHeader('X-Robots-Tag', 'noindex, nofollow');

        return view('public/voucher/invalid', [
            'title' => 'HJMS | Voucher Not Found',
        ]);
    }
}
