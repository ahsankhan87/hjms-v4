<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Final Voucher') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome-free-7.0.0-web/css/all.min.css') ?>">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 24px;
            background: #f5f7fb;
            color: #1f2937;
        }

        .sheet {
            max-width: 1100px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #dbe3ef;
            box-shadow: 0 8px 20px -12px rgba(30, 41, 59, .3);
        }

        .header {
            display: grid;
            grid-template-columns: 180px 1fr;
            gap: 16px;
            padding: 18px;
            border-bottom: 2px solid #194f90;
        }

        .brand {
            font-size: 30px;
            color: #194f90;
            font-weight: 700;
            line-height: 1.1;
        }

        .company {
            text-align: center;
        }

        .company h1 {
            margin: 0;
            color: #113d77;
            font-size: 34px;
            letter-spacing: .6px;
        }

        .company p {
            margin: 4px 0;
            font-size: 13px;
            color: #334155;
        }

        .meta {
            display: flex;
            justify-content: space-between;
            border-top: 1px solid #dbe3ef;
            border-bottom: 1px solid #dbe3ef;
            padding: 10px 14px;
            font-size: 14px;
            color: #113d77;
            font-weight: 700;
        }

        .section-title {
            margin: 10px 0 0;
            padding: 7px 10px;
            background: #154f95;
            color: #fff;
            font-size: 18px;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 8px;
        }

        th,
        td {
            border: 1px solid #9fb7d8;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background: #1b4f8f;
            color: #fff;
            font-weight: 700;
        }

        .content {
            padding: 0 14px 14px;
        }

        .remarks {
            min-height: 62px;
        }

        .actions {
            max-width: 1100px;
            margin: 0 auto 12px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .btn {
            border: 1px solid #0f4a8a;
            background: #18589f;
            color: #fff;
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            cursor: pointer;
        }

        .btn.secondary {
            background: #fff;
            color: #18589f;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .actions {
                display: none;
            }

            .sheet {
                border: none;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    <div class="actions">
        <a class="btn secondary" href="<?= site_url('/app/bookings') ?>"><i class="fa-solid fa-arrow-left"></i> Back</a>
        <button type="button" class="btn" onclick="window.print()"><i class="fa-solid fa-print"></i> Print Voucher</button>
        <button type="button" class="btn" onclick="window.print()"><i class="fa-solid fa-file-pdf"></i> Save as PDF</button>
    </div>

    <article class="sheet">
        <header class="header">
            <div class="brand">HJMS<br>Voucher</div>
            <div class="company">
                <h1>KARWAN-E-TAIF PVT LTD</h1>
                <p>Shop # B-7, B-8, Hanifullah Plaza Charsadda Road, Opposite Charsadda Bus Stand Peshawar</p>
                <p>Saudi Company: AlAhela Establishment for Umrah Services</p>
            </div>
        </header>

        <div class="meta">
            <div>Voucher No: <?= esc($voucherNo) ?></div>
            <div>Date Created: <?= esc($voucherDate) ?></div>
        </div>

        <div class="content">
            <div class="section-title">General Information About Service</div>
            <table>
                <thead>
                    <tr>
                        <th>Adults</th>
                        <th>Childs</th>
                        <th>Infants</th>
                        <th>Arrival To KSA Date</th>
                        <th>Departure From KSA Date</th>
                        <th>Nights</th>
                        <th>Group Name</th>
                        <th>Group Code</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= esc((string) count($pilgrimRows)) ?></td>
                        <td>0</td>
                        <td>0</td>
                        <td><?= esc((string) ($booking['arrival_date'] ?? '')) ?></td>
                        <td><?= esc((string) ($booking['departure_date'] ?? '')) ?></td>
                        <td><?= esc((string) ($booking['duration_days'] ?? '')) ?></td>
                        <td><?= esc((string) ($booking['package_name'] ?? '')) ?></td>
                        <td><?= esc((string) ($booking['package_code'] ?? '')) ?></td>
                    </tr>
                </tbody>
            </table>

            <?php if (!empty($outboundFlight)): ?>
                <div class="section-title">Flight To KSA</div>
                <table>
                    <thead>
                        <tr>
                            <th>Departure Airport</th>
                            <th>Sector Route</th>
                            <th>Flight Number</th>
                            <th>Departure Date</th>
                            <th>Departure Time</th>
                            <th>Arrival Date</th>
                            <th>Arrival Time</th>
                            <th>PNR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= esc((string) ($outboundFlight['departure_airport'] ?? '')) ?></td>
                            <td><?= esc((string) (($outboundFlight['departure_airport'] ?? '') . '-' . ($outboundFlight['arrival_airport'] ?? ''))) ?></td>
                            <td><?= esc((string) ($outboundFlight['flight_no'] ?? '')) ?></td>
                            <td><?= esc(!empty($outboundFlight['departure_at']) ? date('d M Y', strtotime((string) $outboundFlight['departure_at'])) : '') ?></td>
                            <td><?= esc(!empty($outboundFlight['departure_at']) ? date('H:i:s', strtotime((string) $outboundFlight['departure_at'])) : '') ?></td>
                            <td><?= esc(!empty($outboundFlight['arrival_at']) ? date('d M Y', strtotime((string) $outboundFlight['arrival_at'])) : '') ?></td>
                            <td><?= esc(!empty($outboundFlight['arrival_at']) ? date('H:i:s', strtotime((string) $outboundFlight['arrival_at'])) : '') ?></td>
                            <td><?= esc((string) ($outboundFlight['pnr'] ?? '')) ?></td>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if (!empty($returnFlight)): ?>
                <div class="section-title">Return Flight From KSA</div>
                <table>
                    <thead>
                        <tr>
                            <th>Departure Airport</th>
                            <th>Sector Route</th>
                            <th>Flight Number</th>
                            <th>Departure Date</th>
                            <th>Departure Time</th>
                            <th>Arrival Date</th>
                            <th>Arrival Time</th>
                            <th>PNR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= esc((string) ($returnFlight['departure_airport'] ?? '')) ?></td>
                            <td><?= esc((string) (($returnFlight['departure_airport'] ?? '') . '-' . ($returnFlight['arrival_airport'] ?? ''))) ?></td>
                            <td><?= esc((string) ($returnFlight['flight_no'] ?? '')) ?></td>
                            <td><?= esc(!empty($returnFlight['departure_at']) ? date('d M Y', strtotime((string) $returnFlight['departure_at'])) : '') ?></td>
                            <td><?= esc(!empty($returnFlight['departure_at']) ? date('H:i:s', strtotime((string) $returnFlight['departure_at'])) : '') ?></td>
                            <td><?= esc(!empty($returnFlight['arrival_at']) ? date('d M Y', strtotime((string) $returnFlight['arrival_at'])) : '') ?></td>
                            <td><?= esc(!empty($returnFlight['arrival_at']) ? date('H:i:s', strtotime((string) $returnFlight['arrival_at'])) : '') ?></td>
                            <td><?= esc((string) ($returnFlight['pnr'] ?? '')) ?></td>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if (!empty($hotelRows)): ?>
                <div class="section-title">Accommodation Detail</div>
                <table>
                    <thead>
                        <tr>
                            <th>Package</th>
                            <th>City</th>
                            <th>Hotel</th>
                            <th>Check-In</th>
                            <th>Check-Out</th>
                            <th>Nights</th>
                            <th>Room Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hotelRows as $hotel): ?>
                            <?php
                            $nights = '';
                            if (!empty($hotel['check_in_date']) && !empty($hotel['check_out_date'])) {
                                $start = strtotime((string) $hotel['check_in_date']);
                                $end = strtotime((string) $hotel['check_out_date']);
                                if ($start && $end && $end >= $start) {
                                    $nights = (string) floor(($end - $start) / 86400);
                                }
                            }
                            ?>
                            <tr>
                                <td><?= esc((string) ($booking['package_code'] ?? '')) ?></td>
                                <td><?= esc((string) ($hotel['hotel_city'] ?? '')) ?></td>
                                <td><?= esc((string) ($hotel['hotel_master_name'] ?? $hotel['hotel_name'] ?? '')) ?></td>
                                <td><?= esc((string) ($hotel['check_in_date'] ?? '')) ?></td>
                                <td><?= esc((string) ($hotel['check_out_date'] ?? '')) ?></td>
                                <td><?= esc($nights) ?></td>
                                <td><?= esc((string) ($hotel['room_type'] ?? '')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if (!empty($transportRows)): ?>
                <div class="section-title">Transportation Detail</div>
                <table>
                    <thead>
                        <tr>
                            <th>Transport Trip</th>
                            <th>Transport By</th>
                            <th>Qty</th>
                            <th>TRANS_BRN</th>
                            <th>Ziarat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transportRows as $transport): ?>
                            <?php
                            $transportId = (int) ($transport['transport_id'] ?? 0);
                            $fallbackTransportName = trim((string) (($transport['transport_name'] ?? '') !== '' ? $transport['transport_name'] : ($transport['master_transport_name'] ?? '')));
                            $routeLabel = (string) ($transportRouteByTransport[$transportId] ?? ($fallbackTransportName !== '' ? $fallbackTransportName : (($outboundFlight['departure_airport'] ?? 'N/A') . '-' . ($outboundFlight['arrival_airport'] ?? 'N/A'))));
                            $ziaratLabel = (string) ($transportZiaratByTransport[$transportId] ?? 'No');
                            ?>
                            <tr>
                                <td><?= esc($routeLabel) ?></td>
                                <td><?= esc((string) ($transport['provider_name'] ?? '')) ?></td>
                                <td><?= esc((string) ($transport['seat_capacity'] ?? '')) ?></td>
                                <td><?= esc((string) ($booking['branch_id'] ?? '')) ?></td>
                                <td><?= esc($ziaratLabel) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if (!empty($pilgrimRows)): ?>
                <div class="section-title">Mutamer's (Pilgrims) Detail</div>
                <table>
                    <thead>
                        <tr>
                            <th>PILGRIM NAME</th>
                            <th>PASSPORT NO</th>
                            <th>DOB</th>
                            <th>AGE_GROUP</th>
                            <th>ChildWithoutBed</th>
                            <th>VisaNo</th>
                            <th>IssueDate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pilgrimRows as $pilgrim): ?>
                            <?php
                            $ageGroup = 'Adult';
                            if (!empty($pilgrim['date_of_birth'])) {
                                $years = (int) floor((time() - strtotime((string) $pilgrim['date_of_birth'])) / (365 * 24 * 60 * 60));
                                $ageGroup = $years < 12 ? 'Child' : 'Adult';
                            }
                            ?>
                            <tr>
                                <td><?= esc(trim((string) (($pilgrim['first_name'] ?? '') . ' ' . ($pilgrim['last_name'] ?? '')))) ?></td>
                                <td><?= esc((string) ($pilgrim['passport_no'] ?? '')) ?></td>
                                <td><?= esc((string) ($pilgrim['date_of_birth'] ?? '')) ?></td>
                                <td><?= esc($ageGroup) ?></td>
                                <td>0</td>
                                <td></td>
                                <td></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <div class="section-title">Payment Detail</div>
            <table>
                <thead>
                    <tr>
                        <th>Total Posted</th>
                        <th>Entries</th>
                        <th>Last Payment Date</th>
                        <th>Last Payment No</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $paymentRows = is_array($payments ?? null) ? $payments : [];
                    $lastPayment = $paymentRows !== [] ? end($paymentRows) : null;
                    ?>
                    <tr>
                        <td><?= esc(number_format((float) $totalPaid, 2)) ?></td>
                        <td><?= esc((string) count($paymentRows)) ?></td>
                        <td><?= esc((string) ($lastPayment['payment_date'] ?? '')) ?></td>
                        <td><?= esc((string) ($lastPayment['payment_no'] ?? '')) ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="section-title">Remarks</div>
            <table>
                <tbody>
                    <tr>
                        <td class="remarks"><?= esc((string) ($booking['remarks'] ?? '')) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </article>
</body>

</html>