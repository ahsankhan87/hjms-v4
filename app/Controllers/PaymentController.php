<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PaymentModel;

class PaymentController extends BaseController
{
    public function index()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/app/seasons')->with('error', 'Please create and activate a season first.');
        }

        $db = db_connect();
        $paymentModel = new PaymentModel();

        return view('portal/payments/index', [
            'title'      => 'HJMS ERP | Payments',
            'headerTitle' => 'Payment Desk',
            'activePage' => 'payments',
            'userEmail' => (string) session('user_email'),
            'rows'      => $paymentModel->where('season_id', $seasonId)->orderBy('id', 'DESC')->findAll(100),
            'bookings'  => $db->table('bookings')->select('id, booking_no, status')->where('season_id', $seasonId)->orderBy('id', 'DESC')->get()->getResultArray(),
            'success'   => session()->getFlashdata('success'),
            'error'     => session()->getFlashdata('error'),
            'errors'    => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function createPayment()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/app/seasons')->with('error', 'Please create and activate a season first.');
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
            return redirect()->to('/app/payments')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $paymentDate = str_replace('T', ' ', $payload['payment_date']);
            $paymentNo = 'PMT-' . date('YmdHis') . '-' . mt_rand(100, 999);

            $booking = db_connect()->table('bookings')->select('id')->where('id', $payload['booking_id'])->where('season_id', $seasonId)->get()->getRowArray();
            if (empty($booking)) {
                return redirect()->to('/app/payments')->withInput()->with('error', 'Selected booking is not in active season.');
            }

            $model = new PaymentModel();
            $model->insert([
                'season_id'          => $seasonId,
                'booking_id'         => $payload['booking_id'],
                'installment_id'     => $payload['installment_id'],
                'payment_no'         => $paymentNo,
                'payment_date'       => $paymentDate,
                'amount'             => (float) $payload['amount'],
                'payment_type'       => $payload['payment_type'],
                'channel'            => $payload['channel'],
                'gateway_reference'  => $payload['gateway_reference'] !== '' ? $payload['gateway_reference'] : null,
                'status'             => 'posted',
                'note'               => $payload['note'] !== '' ? $payload['note'] : null,
                'created_by'         => session('user_id') ? (int) session('user_id') : null,
                'created_at'         => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('/app/payments')->with('success', 'Payment posted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/payments')->withInput()->with('error', $e->getMessage());
        }
    }

    public function updatePayment()
    {
        $paymentId = (int) $this->request->getPost('payment_id');
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/app/seasons')->with('error', 'Please create and activate a season first.');
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

        if ($paymentId < 1) {
            return redirect()->to('/app/payments')->withInput()->with('error', 'Valid payment ID is required.');
        }

        if (! $this->validateData($payload, [
            'booking_id'        => 'permit_empty|integer',
            'installment_id'    => 'permit_empty|integer',
            'amount'            => 'permit_empty|decimal',
            'payment_type'      => 'permit_empty|in_list[payment,refund]',
            'channel'           => 'permit_empty|in_list[manual,bank,online]',
            'payment_date'      => 'permit_empty',
            'gateway_reference' => 'permit_empty|max_length[120]',
            'status'            => 'permit_empty|max_length[40]',
            'note'              => 'permit_empty|max_length[5000]',
        ])) {
            return redirect()->to('/app/payments')->withInput()->with('errors', $this->validator->getErrors());
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
            return redirect()->to('/app/payments')->withInput()->with('error', 'Provide at least one field to update for payment.');
        }

        try {
            $model = new PaymentModel();
            $existing = $model->where('id', $paymentId)->where('season_id', $seasonId)->first();
            if (empty($existing)) {
                return redirect()->to('/app/payments')->with('error', 'Payment not found in active season.');
            }

            if (isset($data['booking_id'])) {
                $booking = db_connect()->table('bookings')->select('id')->where('id', (int) $data['booking_id'])->where('season_id', $seasonId)->get()->getRowArray();
                if (empty($booking)) {
                    return redirect()->to('/app/payments')->withInput()->with('error', 'Selected booking is not in active season.');
                }
            }

            $model->update($paymentId, $data);

            return redirect()->to('/app/payments')->with('success', 'Payment updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/payments')->withInput()->with('error', $e->getMessage());
        }
    }

    public function deletePayment()
    {
        $paymentId = (int) $this->request->getPost('payment_id');
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/app/seasons')->with('error', 'Please create and activate a season first.');
        }
        if ($paymentId < 1) {
            return redirect()->to('/app/payments')->with('error', 'Valid payment ID is required for delete.');
        }

        try {
            $model = new PaymentModel();
            $existing = $model->where('id', $paymentId)->where('season_id', $seasonId)->first();
            if (empty($existing)) {
                return redirect()->to('/app/payments')->with('error', 'Payment not found in active season.');
            }
            $deleted = $model->delete($paymentId);

            if (! $deleted) {
                return redirect()->to('/app/payments')->with('error', 'Payment not found or already removed.');
            }

            return redirect()->to('/app/payments')->with('success', 'Payment deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/payments')->with('error', $e->getMessage());
        }
    }
}
