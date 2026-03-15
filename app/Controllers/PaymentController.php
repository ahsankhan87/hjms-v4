<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PaymentModel;
use App\Services\AgentLedgerService;

class PaymentController extends BaseController
{
    public function index()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $filters = [
            'from_date' => (string) $this->request->getGet('from_date'),
            'to_date' => (string) $this->request->getGet('to_date'),
            'channel' => (string) $this->request->getGet('channel'),
            'payment_type' => (string) $this->request->getGet('payment_type'),
            'status' => (string) $this->request->getGet('status'),
            'agent_id' => (string) $this->request->getGet('agent_id'),
            'booking_id' => (string) $this->request->getGet('booking_id'),
        ];

        $filterErrors = [];
        if (! $this->validateData($filters, [
            'from_date' => 'permit_empty|valid_date[Y-m-d]',
            'to_date' => 'permit_empty|valid_date[Y-m-d]',
            'channel' => 'permit_empty|in_list[manual,bank,online]',
            'payment_type' => 'permit_empty|in_list[payment,refund]',
            'status' => 'permit_empty|in_list[posted,voided,pending,failed]',
            'agent_id' => 'permit_empty|integer',
            'booking_id' => 'permit_empty|integer',
        ])) {
            $filterErrors = $this->validator->getErrors();
            $filters = [
                'from_date' => '',
                'to_date' => '',
                'channel' => '',
                'payment_type' => '',
                'status' => '',
                'agent_id' => '',
                'booking_id' => '',
            ];
        }

        $db = db_connect();

        $aggregateRows = $db->table('payments')
            ->select('booking_id, COALESCE(SUM(CASE WHEN payment_type = "refund" THEN -amount ELSE amount END), 0) AS paid_total')
            ->where('season_id', $seasonId)
            ->where('status', 'posted')
            ->groupBy('booking_id')
            ->get()
            ->getResultArray();

        $paidByBooking = [];
        foreach ($aggregateRows as $aggregate) {
            $paidByBooking[(int) ($aggregate['booking_id'] ?? 0)] = (float) ($aggregate['paid_total'] ?? 0);
        }

        $bookingRows = $db->table('bookings b')
            ->select('b.id, b.booking_no, b.status, b.total_amount, b.agent_id, a.name AS agent_name')
            ->join('agents a', 'a.id = b.agent_id', 'left')
            ->where('b.season_id', $seasonId)
            ->orderBy('b.id', 'DESC')
            ->get()
            ->getResultArray();

        $bookings = [];
        foreach ($bookingRows as $booking) {
            $bookingId = (int) ($booking['id'] ?? 0);
            $totalAmount = (float) ($booking['total_amount'] ?? 0);
            $paidAmount = (float) ($paidByBooking[$bookingId] ?? 0);
            $booking['paid_amount'] = $paidAmount;
            $booking['outstanding_amount'] = max(0, $totalAmount - $paidAmount);
            $bookings[] = $booking;
        }

        $recentQuery = $db->table('payments p')
            ->select('p.*, b.booking_no, b.agent_id, b.total_amount, a.name AS agent_name')
            ->join('bookings b', 'b.id = p.booking_id', 'left')
            ->join('agents a', 'a.id = b.agent_id', 'left')
            ->where('p.season_id', $seasonId);

        if ($filters['from_date'] !== '') {
            $recentQuery->where('DATE(p.payment_date) >=', $filters['from_date']);
        }
        if ($filters['to_date'] !== '') {
            $recentQuery->where('DATE(p.payment_date) <=', $filters['to_date']);
        }
        if ($filters['channel'] !== '') {
            $recentQuery->where('p.channel', $filters['channel']);
        }
        if ($filters['payment_type'] !== '') {
            $recentQuery->where('p.payment_type', $filters['payment_type']);
        }
        if ($filters['status'] !== '') {
            $recentQuery->where('p.status', $filters['status']);
        }
        if ($filters['agent_id'] !== '') {
            $recentQuery->where('b.agent_id', (int) $filters['agent_id']);
        }
        if ($filters['booking_id'] !== '') {
            $recentQuery->where('p.booking_id', (int) $filters['booking_id']);
        }

        $recentRows = $recentQuery
            ->orderBy('p.id', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($recentRows as &$row) {
            $bookingId = (int) ($row['booking_id'] ?? 0);
            $totalAmount = (float) ($row['total_amount'] ?? 0);
            $paidAmount = (float) ($paidByBooking[$bookingId] ?? 0);
            $row['booking_paid_amount'] = $paidAmount;
            $row['booking_outstanding_amount'] = max(0, $totalAmount - $paidAmount);
        }
        unset($row);

        $paymentSummary = [
            'transactions' => count($recentRows),
            'gross_in' => 0.0,
            'gross_out' => 0.0,
            'net_posted' => 0.0,
            'voided' => 0,
        ];

        foreach ($recentRows as $row) {
            $status = (string) ($row['status'] ?? 'posted');
            $amount = (float) ($row['amount'] ?? 0);
            $type = (string) ($row['payment_type'] ?? 'payment');

            if ($status === 'voided') {
                $paymentSummary['voided']++;
                continue;
            }

            if ($status !== 'posted') {
                continue;
            }

            if ($type === 'refund') {
                $paymentSummary['gross_out'] += $amount;
            } else {
                $paymentSummary['gross_in'] += $amount;
            }
        }
        $paymentSummary['net_posted'] = $paymentSummary['gross_in'] - $paymentSummary['gross_out'];

        return view('portal/payments/index', [
            'title'      => 'HJMS ERP | Payments',
            'headerTitle' => 'Payment Desk',
            'activePage' => 'payments',
            'userEmail' => (string) session('user_email'),
            'rows'      => $recentRows,
            'bookings'  => $bookings,
            'agents'    => $db->table('agents')->select('id, name')->orderBy('name', 'ASC')->get()->getResultArray(),
            'filters'   => $filters,
            'filterErrors' => $filterErrors,
            'paymentSummary' => $paymentSummary,
            'success'   => session()->getFlashdata('success'),
            'error'     => session()->getFlashdata('error'),
            'errors'    => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function createPayment()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $payload = [
            'booking_id'         => (int) $this->request->getPost('booking_id'),
            'installment_id'     => $this->request->getPost('installment_id') !== '' ? (int) $this->request->getPost('installment_id') : null,
            'amount'             => (string) $this->request->getPost('amount'),
            'payment_type'       => (string) ($this->request->getPost('payment_type') ?: 'payment'),
            'channel'            => (string) ($this->request->getPost('channel') ?: 'manual'),
            'payment_date'       => (string) ($this->request->getPost('payment_date') ?: date('Y-m-d H:i:s')),
            'gateway_reference'  => (string) $this->request->getPost('gateway_reference'),
            'note'               => (string) $this->request->getPost('note'),
        ];

        if (! $this->validateData($payload, [
            'booking_id'        => 'required|integer',
            'installment_id'    => 'permit_empty|integer',
            'amount'            => 'required|decimal',
            'payment_type'      => 'required|in_list[payment,refund]',
            'channel'           => 'required|in_list[manual,bank,online]',
            'payment_date'      => 'required',
            'gateway_reference' => 'permit_empty|max_length[120]',
            'note'              => 'permit_empty|max_length[5000]',
        ])) {
            return redirect()->to('/payments/create')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $amount = (float) $payload['amount'];
            if ($amount <= 0) {
                return redirect()->to('/payments/create')->withInput()->with('error', 'Amount must be greater than zero.');
            }

            $paymentDate = $this->normalizePaymentDate($payload['payment_date']);
            if ($paymentDate === null) {
                return redirect()->to('/payments/create')->withInput()->with('error', 'Invalid payment date/time format.');
            }

            $paymentNo = $this->generateUniquePaymentNo();

            $booking = db_connect()->table('bookings')->select('id')->where('id', $payload['booking_id'])->where('season_id', $seasonId)->get()->getRowArray();
            if (empty($booking)) {
                return redirect()->to('/payments/create')->withInput()->with('error', 'Selected booking is not in active season.');
            }

            $financials = $this->getBookingFinancials((int) $payload['booking_id'], $seasonId, null);
            if ($payload['payment_type'] === 'payment' && (float) ($financials['total_amount'] ?? 0) > 0) {
                $outstanding = max(0, (float) ($financials['total_amount'] ?? 0) - (float) ($financials['paid_amount'] ?? 0));
                if ($amount > $outstanding + 0.01) {
                    return redirect()->to('/payments/create')->withInput()->with('error', 'Payment amount exceeds booking outstanding amount.');
                }
            }

            if ($payload['payment_type'] === 'refund') {
                $paidAmount = (float) ($financials['paid_amount'] ?? 0);
                if ($amount > $paidAmount + 0.01) {
                    return redirect()->to('/payments/create')->withInput()->with('error', 'Refund amount cannot exceed paid amount for this booking.');
                }
            }

            $model = new PaymentModel();
            $model->insert([
                'season_id'          => $seasonId,
                'booking_id'         => $payload['booking_id'],
                'installment_id'     => $payload['installment_id'],
                'payment_no'         => $paymentNo,
                'payment_date'       => $paymentDate,
                'amount'             => $amount,
                'payment_type'       => $payload['payment_type'],
                'channel'            => $payload['channel'],
                'gateway_reference'  => $payload['gateway_reference'] !== '' ? $payload['gateway_reference'] : null,
                'status'             => 'posted',
                'note'               => $payload['note'] !== '' ? $payload['note'] : null,
                'created_by'         => session('user_id') ? (int) session('user_id') : null,
                'created_at'         => date('Y-m-d H:i:s'),
            ]);

            $this->syncBookingStatusByPayments((int) $payload['booking_id'], $seasonId);
            (new AgentLedgerService())->syncBookingLedger((int) $payload['booking_id'], $seasonId);

            return redirect()->to('/payments')->with('success', 'Payment posted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/payments/create')->withInput()->with('error', $e->getMessage());
        }
    }

    public function updatePayment()
    {
        $paymentId = (int) $this->request->getPost('payment_id');
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }
        $payload = [
            'booking_id'        => (string) $this->request->getPost('booking_id'),
            'installment_id'    => (string) $this->request->getPost('installment_id'),
            'amount'            => (string) $this->request->getPost('amount'),
            'payment_type'      => (string) $this->request->getPost('payment_type'),
            'channel'           => (string) $this->request->getPost('channel'),
            'payment_date'      => (string) $this->request->getPost('payment_date'),
            'gateway_reference' => (string) $this->request->getPost('gateway_reference'),
            'status'            => (string) $this->request->getPost('status'),
            'note'              => (string) $this->request->getPost('note'),
        ];

        $editUrl = $paymentId > 0 ? "/payments/{$paymentId}/edit" : '/payments';

        if ($paymentId < 1) {
            return redirect()->to('/payments')->with('error', 'Valid payment ID is required.');
        }

        if (! $this->validateData($payload, [
            'booking_id'        => 'permit_empty|integer',
            'installment_id'    => 'permit_empty|integer',
            'amount'            => 'permit_empty|decimal',
            'payment_type'      => 'permit_empty|in_list[payment,refund]',
            'channel'           => 'permit_empty|in_list[manual,bank,online]',
            'payment_date'      => 'permit_empty',
            'gateway_reference' => 'permit_empty|max_length[120]',
            'status'            => 'permit_empty|in_list[posted,voided,pending,failed]',
            'note'              => 'permit_empty|max_length[5000]',
        ])) {
            return redirect()->to($editUrl)->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [];
        if ($payload['booking_id'] !== '') {
            $data['booking_id'] = (int) $payload['booking_id'];
        }
        if ($payload['installment_id'] !== '') {
            $data['installment_id'] = (int) $payload['installment_id'];
        }
        if ($payload['amount'] !== '') {
            $data['amount'] = (float) $payload['amount'];
        }
        if ($payload['payment_type'] !== '') {
            $data['payment_type'] = $payload['payment_type'];
        }
        if ($payload['channel'] !== '') {
            $data['channel'] = $payload['channel'];
        }
        if ($payload['payment_date'] !== '') {
            $data['payment_date'] = str_replace('T', ' ', $payload['payment_date']);
        }
        if ($payload['gateway_reference'] !== '') {
            $data['gateway_reference'] = $payload['gateway_reference'];
        }
        if ($payload['status'] !== '') {
            $data['status'] = $payload['status'];
        }
        if ($payload['note'] !== '') {
            $data['note'] = $payload['note'];
        }

        if ($data === []) {
            return redirect()->to('/payments')->withInput()->with('error', 'Provide at least one field to update for payment.');
        }

        try {
            $model = new PaymentModel();
            $existing = $model->where('id', $paymentId)->where('season_id', $seasonId)->first();
            if (empty($existing)) {
                return redirect()->to('/payments')->with('error', 'Payment not found in active season.');
            }

            if (isset($data['booking_id'])) {
                $booking = db_connect()->table('bookings')->select('id')->where('id', (int) $data['booking_id'])->where('season_id', $seasonId)->get()->getRowArray();
                if (empty($booking)) {
                    return redirect()->to('/payments')->withInput()->with('error', 'Selected booking is not in active season.');
                }
            }

            if (isset($data['payment_date'])) {
                $normalizedDate = $this->normalizePaymentDate((string) $data['payment_date']);
                if ($normalizedDate === null) {
                    return redirect()->to('/payments')->withInput()->with('error', 'Invalid payment date/time format.');
                }
                $data['payment_date'] = $normalizedDate;
            }

            $effectiveBookingId = isset($data['booking_id']) ? (int) $data['booking_id'] : (int) ($existing['booking_id'] ?? 0);
            $effectiveAmount = isset($data['amount']) ? (float) $data['amount'] : (float) ($existing['amount'] ?? 0);
            $effectiveType = isset($data['payment_type']) ? (string) $data['payment_type'] : (string) ($existing['payment_type'] ?? 'payment');
            $effectiveStatus = isset($data['status']) ? (string) $data['status'] : (string) ($existing['status'] ?? 'posted');

            if ($effectiveAmount <= 0) {
                return redirect()->to($editUrl)->withInput()->with('error', 'Amount must be greater than zero.');
            }

            if ($effectiveStatus === 'posted') {
                $financials = $this->getBookingFinancials($effectiveBookingId, $seasonId, $paymentId);
                if ($effectiveType === 'payment' && (float) ($financials['total_amount'] ?? 0) > 0) {
                    $outstanding = max(0, (float) ($financials['total_amount'] ?? 0) - (float) ($financials['paid_amount'] ?? 0));
                    if ($effectiveAmount > $outstanding + 0.01) {
                        return redirect()->to($editUrl)->withInput()->with('error', 'Payment amount exceeds booking outstanding amount.');
                    }
                }

                if ($effectiveType === 'refund') {
                    $paidAmount = (float) ($financials['paid_amount'] ?? 0);
                    if ($effectiveAmount > $paidAmount + 0.01) {
                        return redirect()->to($editUrl)->withInput()->with('error', 'Refund amount cannot exceed paid amount for this booking.');
                    }
                }
            }

            $model->update($paymentId, $data);

            $this->syncBookingStatusByPayments((int) $existing['booking_id'], $seasonId);
            (new AgentLedgerService())->syncBookingLedger((int) $existing['booking_id'], $seasonId);
            if (isset($data['booking_id']) && (int) $data['booking_id'] !== (int) $existing['booking_id']) {
                $this->syncBookingStatusByPayments((int) $data['booking_id'], $seasonId);
                (new AgentLedgerService())->syncBookingLedger((int) $data['booking_id'], $seasonId);
            }

            return redirect()->to($editUrl)->with('success', 'Payment updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to($editUrl)->withInput()->with('error', $e->getMessage());
        }
    }

    public function deletePayment()
    {
        $paymentId = (int) $this->request->getPost('payment_id');
        $voidReason = trim((string) $this->request->getPost('void_reason'));
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }
        if ($paymentId < 1) {
            return redirect()->to('/payments')->with('error', 'Valid payment ID is required for delete.');
        }

        try {
            $model = new PaymentModel();
            $existing = $model->where('id', $paymentId)->where('season_id', $seasonId)->first();
            if (empty($existing)) {
                return redirect()->to('/payments')->with('error', 'Payment not found in active season.');
            }

            if ((string) ($existing['status'] ?? '') === 'voided') {
                return redirect()->to('/payments')->with('error', 'Payment is already voided.');
            }

            $auditNote = 'Voided on ' . date('Y-m-d H:i:s');
            if ($voidReason !== '') {
                $auditNote .= ': ' . $voidReason;
            }

            $existingNote = trim((string) ($existing['note'] ?? ''));
            $note = $existingNote !== '' ? ($existingNote . ' | ' . $auditNote) : $auditNote;

            $model->update($paymentId, [
                'status' => 'voided',
                'note' => $note,
            ]);

            $this->syncBookingStatusByPayments((int) $existing['booking_id'], $seasonId);
            (new AgentLedgerService())->syncBookingLedger((int) $existing['booking_id'], $seasonId);

            return redirect()->to('/payments')->with('success', 'Payment voided successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/payments')->with('error', $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // FORM PAGES
    // ─────────────────────────────────────────────────────────────────

    public function createForm()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $db = db_connect();
        $bookings = $this->buildBookingsList($db, $seasonId);

        return view('portal/payments/create', [
            'title'       => 'HJMS ERP | Post Payment',
            'headerTitle' => 'Post Payment',
            'activePage'  => 'payments',
            'userEmail'   => (string) session('user_email'),
            'bookings'    => $bookings,
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function editForm(int $paymentId)
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }
        if ($paymentId < 1) {
            return redirect()->to('/payments')->with('error', 'Invalid payment ID.');
        }

        $db = db_connect();
        $payment = $db->table('payments p')
            ->select('p.*, b.booking_no, b.agent_id, b.total_amount AS booking_total, a.name AS agent_name')
            ->join('bookings b', 'b.id = p.booking_id', 'left')
            ->join('agents a', 'a.id = b.agent_id', 'left')
            ->where('p.id', $paymentId)
            ->where('p.season_id', $seasonId)
            ->get()
            ->getRowArray();

        if (empty($payment)) {
            return redirect()->to('/payments')->with('error', 'Payment not found in active season.');
        }

        $bookings  = $this->buildBookingsList($db, $seasonId);
        $financials = $this->getBookingFinancials((int) ($payment['booking_id'] ?? 0), $seasonId, $paymentId);

        return view('portal/payments/edit', [
            'title'       => 'HJMS ERP | Edit Payment',
            'headerTitle' => 'Edit Payment',
            'activePage'  => 'payments',
            'userEmail'   => (string) session('user_email'),
            'payment'     => $payment,
            'financials'  => $financials,
            'bookings'    => $bookings,
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function show(int $paymentId)
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }
        if ($paymentId < 1) {
            return redirect()->to('/payments')->with('error', 'Invalid payment ID.');
        }

        $db = db_connect();
        $payment = $db->table('payments p')
            ->select('p.*, b.booking_no, b.status AS booking_status, b.agent_id, b.total_amount AS booking_total, pkg.name AS package_name, pkg.code AS package_code, a.name AS agent_name')
            ->join('bookings b', 'b.id = p.booking_id', 'left')
            ->join('agents a', 'a.id = b.agent_id', 'left')
            ->join('packages pkg', 'pkg.id = b.package_id', 'left')
            ->where('p.id', $paymentId)
            ->where('p.season_id', $seasonId)
            ->get()
            ->getRowArray();

        if (empty($payment)) {
            return redirect()->to('/payments')->with('error', 'Payment not found in active season.');
        }

        $financials = $this->getBookingFinancials((int) ($payment['booking_id'] ?? 0), $seasonId, null);

        return view('portal/payments/show', [
            'title'       => 'HJMS ERP | ' . ($payment['payment_no'] ?? 'Payment'),
            'headerTitle' => 'Payment Details',
            'activePage'  => 'payments',
            'userEmail'   => (string) session('user_email'),
            'payment'     => $payment,
            'financials'  => $financials,
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
        ]);
    }

    /** Shared helper: build bookings list with paid/outstanding amounts. */
    private function buildBookingsList(\CodeIgniter\Database\BaseConnection $db, int $seasonId): array
    {
        $aggregateRows = $db->table('payments')
            ->select('booking_id, COALESCE(SUM(CASE WHEN payment_type = "refund" THEN -amount ELSE amount END), 0) AS paid_total')
            ->where('season_id', $seasonId)
            ->where('status', 'posted')
            ->groupBy('booking_id')
            ->get()
            ->getResultArray();

        $paidByBooking = [];
        foreach ($aggregateRows as $agg) {
            $paidByBooking[(int) ($agg['booking_id'] ?? 0)] = (float) ($agg['paid_total'] ?? 0);
        }

        $bookingRows = $db->table('bookings b')
            ->select('b.id, b.booking_no, b.status, b.total_amount, b.agent_id, a.name AS agent_name')
            ->join('agents a', 'a.id = b.agent_id', 'left')
            ->where('b.season_id', $seasonId)
            ->orderBy('b.id', 'DESC')
            ->get()
            ->getResultArray();

        $bookings = [];
        foreach ($bookingRows as $booking) {
            $bId = (int) ($booking['id'] ?? 0);
            $total = (float) ($booking['total_amount'] ?? 0);
            $paid  = (float) ($paidByBooking[$bId] ?? 0);
            $booking['paid_amount']        = $paid;
            $booking['outstanding_amount'] = max(0, $total - $paid);
            $bookings[] = $booking;
        }

        return $bookings;
    }

    public function receipt(int $paymentId)
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        if ($paymentId < 1) {
            return redirect()->to('/payments')->with('error', 'Valid payment ID is required for receipt.');
        }

        $db = db_connect();
        $payment = $db->table('payments p')
            ->select('p.*, b.booking_no, b.remarks AS booking_remarks, b.total_amount, b.agent_id, pkg.code AS package_code, pkg.name AS package_name, a.name AS agent_name')
            ->join('bookings b', 'b.id = p.booking_id', 'left')
            ->join('packages pkg', 'pkg.id = b.package_id', 'left')
            ->join('agents a', 'a.id = b.agent_id', 'left')
            ->where('p.id', $paymentId)
            ->where('p.season_id', $seasonId)
            ->get()
            ->getRowArray();

        if (empty($payment)) {
            return redirect()->to('/payments')->with('error', 'Payment not found in active season.');
        }

        $paidAmount = (float) ($db->table('payments')
            ->select('COALESCE(SUM(CASE WHEN payment_type = "refund" THEN -amount ELSE amount END), 0) AS paid_total')
            ->where('booking_id', (int) ($payment['booking_id'] ?? 0))
            ->where('season_id', $seasonId)
            ->where('status', 'posted')
            ->get()
            ->getRowArray()['paid_total'] ?? 0);
        $totalAmount = (float) ($payment['total_amount'] ?? 0);

        return view('portal/payments/receipt', [
            'title' => 'HJMS ERP | Payment Receipt',
            'payment' => $payment,
            'bookingPaidAmount' => $paidAmount,
            'bookingOutstandingAmount' => max(0, $totalAmount - $paidAmount),
            'company' => main_company(),
            'receiptNo' => 'RCT-' . str_pad((string) $paymentId, 5, '0', STR_PAD_LEFT),
            'receiptDate' => date('d M Y', strtotime((string) ($payment['payment_date'] ?? date('Y-m-d H:i:s')))),
        ]);
    }

    private function syncBookingStatusByPayments(int $bookingId, int $seasonId)
    {
        if ($bookingId < 1 || $seasonId < 1) {
            return;
        }

        $db = db_connect();
        $booking = $db->table('bookings')
            ->select('id, status, total_amount')
            ->where('id', $bookingId)
            ->where('season_id', $seasonId)
            ->get()
            ->getRowArray();

        if (empty($booking)) {
            return;
        }

        if (! in_array('total_amount', $db->getFieldNames('bookings'), true)) {
            return;
        }

        $paidAmount = (float) ($db->table('payments')
            ->select('COALESCE(SUM(CASE WHEN payment_type = "refund" THEN -amount ELSE amount END), 0) AS paid_total')
            ->where('booking_id', $bookingId)
            ->where('season_id', $seasonId)
            ->where('status', 'posted')
            ->get()
            ->getRowArray()['paid_total'] ?? 0);

        if ($this->bookingConfirmMode() !== 'auto_on_full_payment') {
            return;
        }

        if ((string) ($booking['status'] ?? '') === 'cancelled') {
            return;
        }

        $totalAmount = (float) ($booking['total_amount'] ?? 0);
        if ($totalAmount <= 0) {
            return;
        }

        if ($paidAmount >= $totalAmount) {
            $db->table('bookings')->where('id', $bookingId)->update([
                'status' => 'confirmed',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            return;
        }

        if ((string) ($booking['status'] ?? '') === 'confirmed' && $paidAmount < $totalAmount) {
            $db->table('bookings')->where('id', $bookingId)->update([
                'status' => 'draft',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function bookingConfirmMode(): string
    {
        $mode = trim((string) getenv('BOOKING_CONFIRM_MODE'));

        return $mode !== '' ? strtolower($mode) : 'manual';
    }

    private function normalizePaymentDate(string $value)
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        $normalized = str_replace('T', ' ', $trimmed);
        $timestamp = strtotime($normalized);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    private function generateUniquePaymentNo(): string
    {
        $model = new PaymentModel();
        for ($i = 0; $i < 5; $i++) {
            $paymentNo = 'PMT-' . date('YmdHis') . '-' . mt_rand(100, 999);
            $exists = $model->where('payment_no', $paymentNo)->first();
            if (empty($exists)) {
                return $paymentNo;
            }
            usleep(50000);
        }

        return 'PMT-' . date('YmdHis') . '-' . mt_rand(1000, 9999);
    }

    private function getBookingFinancials(int $bookingId, int $seasonId, $excludePaymentId = null): array
    {
        $db = db_connect();
        $booking = $db->table('bookings')
            ->select('id, total_amount')
            ->where('id', $bookingId)
            ->where('season_id', $seasonId)
            ->get()
            ->getRowArray();

        if (empty($booking)) {
            return [
                'total_amount' => 0.0,
                'paid_amount' => 0.0,
            ];
        }

        $paidQuery = $db->table('payments')
            ->select('COALESCE(SUM(CASE WHEN payment_type = "refund" THEN -amount ELSE amount END), 0) AS paid_total')
            ->where('booking_id', $bookingId)
            ->where('season_id', $seasonId)
            ->where('status', 'posted');

        if ($excludePaymentId !== null) {
            $paidQuery->where('id !=', (int) $excludePaymentId);
        }

        $paidAmount = (float) ($paidQuery->get()->getRowArray()['paid_total'] ?? 0);

        return [
            'total_amount' => (float) ($booking['total_amount'] ?? 0),
            'paid_amount' => $paidAmount,
        ];
    }
}
