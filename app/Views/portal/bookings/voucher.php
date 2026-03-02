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
            padding: 12px;
            background: #f3f4f6;
            color: #1f2937;
        }

        .sheet {
            max-width: 1100px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 8px 18px -14px rgba(15, 23, 42, .35);
        }

        .header {
            display: grid;
            grid-template-columns: 220px 1fr 220px;
            gap: 10px;
            padding: 10px 12px;
            border-bottom: 1px solid #94a3b8;
            align-items: center;
            background: #fff;
        }

        .company-block {
            border: none;
            border-radius: 0;
            padding: 2px 4px;
            min-height: 90px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 2px;
            background: transparent;
        }

        .company-block img {
            max-width: 170px;
            max-height: 64px;
            object-fit: contain;
        }

        .company-block h1 {
            margin: 0;
            color: #113d77;
            font-size: 17px;
            letter-spacing: .2px;
            font-weight: 700;
        }

        .company-block p {
            margin: 0;
            font-size: 11px;
            color: #334155;
            line-height: 1.25;
        }

        .urdu-text {
            direction: rtl;
            text-align: right;
        }

        .company-center h1 {
            font-size: 23px;
            letter-spacing: .35px;
        }

        @media (max-width: 900px) {
            .header {
                grid-template-columns: 1fr;
            }
        }

        .meta {
            display: flex;
            justify-content: space-between;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            padding: 7px 12px;
            font-size: 12px;
            color: #113d77;
            font-weight: 700;
            background: #f8fafc;
        }

        .section-title {
            margin: 8px 0 2px;
            padding: 4px 2px;
            background: transparent;
            color: #0f2f57;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .2px;
            border-bottom: 1px solid #93a9c8;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 4px;
            table-layout: fixed;
        }

        .hotel-table,
        .pilgrim-table {
            table-layout: auto;
        }

        .hotel-table th:nth-child(3),
        .hotel-table td:nth-child(3) {
            width: 24%;
        }

        .hotel-table th:nth-child(7),
        .hotel-table td:nth-child(7) {
            width: 16%;
        }

        .pilgrim-table th:nth-child(1),
        .pilgrim-table td:nth-child(1) {
            width: 24%;
        }

        .pilgrim-table th:nth-child(2),
        .pilgrim-table td:nth-child(2) {
            width: 17%;
        }

        .pilgrim-table th:nth-child(6),
        .pilgrim-table td:nth-child(6) {
            width: 14%;
        }

        th,
        td {
            border: 1px solid #8ea3bf;
            padding: 4px 5px;
            text-align: left;
            vertical-align: top;
            word-break: break-word;
        }

        th {
            background: #edf3fb;
            color: #0f2f57;
            font-weight: 700;
            font-size: 10px;
            text-transform: none;
            letter-spacing: 0;
        }

        .content {
            padding: 0 8px 8px;
        }

        .remarks {
            min-height: 38px;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 6px;
            margin-top: 3px;
        }

        .contact-item {
            border: 1px solid #8ea3bf;
            background: #f8fbff;
            padding: 6px;
        }

        .contact-label {
            font-size: 10px;
            color: #0f2f57;
            font-weight: 700;
            margin-bottom: 3px;
        }

        .contact-value {
            font-size: 11px;
            color: #0f172a;
            line-height: 1.25;
            word-break: break-word;
        }

        .actions {
            max-width: 1100px;
            margin: 0 auto 8px;
            display: flex;
            justify-content: flex-end;
            gap: 6px;
        }

        .btn {
            border: 1px solid #0f4a8a;
            background: #18589f;
            color: #fff;
            padding: 5px 10px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            cursor: pointer;
        }

        .btn.secondary {
            background: #fff;
            color: #18589f;
        }

        @page {
            size: A4 portrait;
            margin: 8mm;
        }

        @media print {

            html,
            body {
                background: #fff;
                padding: 0;
                margin: 0;
                width: 210mm;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .actions {
                display: none;
            }

            .sheet {
                border: none;
                border-radius: 0;
                box-shadow: none;
                width: 100%;
                max-width: none;
                margin: 0;
                overflow: visible;
            }

            .header {
                grid-template-columns: 170px 1fr 170px;
                gap: 6px;
                padding: 6px 8px;
            }

            .company-block {
                min-height: 72px;
                padding: 4px;
                gap: 2px;
            }

            .company-block img {
                max-height: 52px;
            }

            .company-block h1,
            .company-center h1 {
                font-size: 13px;
                line-height: 1.2;
            }

            .company-block p {
                font-size: 9px;
                line-height: 1.2;
            }

            .meta {
                font-size: 9px;
                padding: 4px 8px;
            }

            .section-title {
                font-size: 10px;
                padding: 3px 1px;
                margin-top: 4px;
            }

            .content {
                padding: 0 5px 5px;
            }

            table {
                font-size: 8.5px;
                margin-bottom: 3px;
            }

            th,
            td {
                padding: 2px 3px;
                font-size: 8px;
                line-height: 1.15;
            }

            th {
                font-size: 8px;
            }

            thead {
                display: table-header-group;
            }

            .remarks {
                min-height: 24px;
            }

            .contact-grid {
                gap: 4px;
            }

            .contact-item {
                padding: 4px;
            }

            .contact-label {
                font-size: 8px;
                margin-bottom: 2px;
            }

            .contact-value {
                font-size: 8px;
                line-height: 1.15;
            }

            .section-title,
            table,
            tr,
            td,
            th {
                page-break-inside: avoid;
                break-inside: avoid-page;
            }
        }

        @media (max-width: 900px) {
            .contact-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .contact-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php
    $mainCompanyData = is_array($mainCompany ?? null) ? $mainCompany : [];
    $shirkaData = is_array($shirkaCompany ?? null) ? $shirkaCompany : [];

    $mainCompanyName = (string) ($mainCompanyData['name'] ?? 'KARWAN-E-TAIF PVT LTD');
    $mainCompanyTagline = (string) ($mainCompanyData['tagline'] ?? 'Hajj & Umrah Management');
    $mainCompanyAddress = (string) ($mainCompanyData['address'] ?? '');
    $mainCompanyLogo = trim((string) ($mainCompanyData['logo_url'] ?? ''));

    $shirkaName = trim((string) ($shirkaData['name'] ?? ''));
    $shirkaLogo = trim((string) ($shirkaData['logo_url'] ?? ''));
    $shirkaAddress = trim((string) ($shirkaData['address'] ?? ''));

    $voucherInstructionsUr = trim((string) ($mainCompanyData['voucher_instructions_ur'] ?? ''));
    $voucherInstructionsEn = trim((string) ($mainCompanyData['voucher_instructions_en'] ?? ''));

    $makkahContact = trim((string) ($mainCompanyData['makkah_contact'] ?? ''));

    $madinaContact = trim((string) ($mainCompanyData['madina_contact'] ?? ''));

    $transportContact = trim((string) ($mainCompanyData['transport_contact'] ?? ''));
    ?>
    <div class="actions">
        <a class="btn secondary" href="<?= site_url('/bookings') ?>"><i class="fa-solid fa-arrow-left"></i> Back</a>
        <button type="button" class="btn" onclick="window.print()"><i class="fa-solid fa-print"></i> Print Voucher</button>
        <button type="button" class="btn" onclick="window.print()"><i class="fa-solid fa-file-pdf"></i> Save as PDF</button>
    </div>

    <article class="sheet">
        <header class="header">
            <div class="company-block">
                <?php if ($mainCompanyLogo !== ''): ?>
                    <img src="<?= esc($mainCompanyLogo) ?>" alt="Main Company Logo">
                <?php else: ?>
                    <h1><?= esc($mainCompanyName) ?></h1>
                <?php endif; ?>
            </div>

            <div class="company-block company-center">
                <h1><?= esc($mainCompanyName) ?></h1>
                <?php if ($mainCompanyTagline !== ''): ?><p><?= esc($mainCompanyTagline) ?></p><?php endif; ?>
                <?php if ($mainCompanyAddress !== ''): ?><p><?= esc($mainCompanyAddress) ?></p><?php endif; ?>
            </div>

            <div class="company-block">
                <?php if ($shirkaLogo !== ''): ?>
                    <img src="<?= esc($shirkaLogo) ?>" alt="Shirka Logo">
                <?php endif; ?>
                <h1><?= esc($shirkaName !== '' ? $shirkaName : 'Saudi Shirka') ?></h1>
                <?php if ($shirkaAddress !== ''): ?><p><?= esc($shirkaAddress) ?></p><?php endif; ?>
                <?php if ($shirkaName === '' && $shirkaLogo === ''): ?><p>No shirka selected for this booking.</p><?php endif; ?>
            </div>
        </header>

        <div class="meta">
            <div>Voucher No: <?= esc($voucherNo) ?></div>
            <div>Voucher Date: <?= esc($voucherDate) ?></div>
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
                <table class="hotel-table">
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

                            $hotelCity = strtolower(trim((string) ($hotel['hotel_city'] ?? '')));
                            $isMadina = strpos($hotelCity, 'madina') !== false || strpos($hotelCity, 'medina') !== false;
                            $hotelLogoPath = $isMadina
                                ? base_url('assets/uploads/madina-logo.svg')
                                : base_url('assets/uploads/makkah-logo.svg');
                            $hotelLogoAlt = $isMadina ? 'Madina' : 'Makkah';
                            ?>
                            <tr>
                                <td><?= esc((string) ($booking['package_code'] ?? '')) ?></td>
                                <td><?= esc((string) ($hotel['hotel_city'] ?? '')) ?></td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        <img src="<?= esc($hotelLogoPath) ?>" alt="<?= esc($hotelLogoAlt) ?>" style="width:18px; height:18px; object-fit:contain;">
                                        <span><?= esc((string) ($hotel['hotel_master_name'] ?? $hotel['hotel_name'] ?? '')) ?></span>
                                    </div>
                                </td>
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
                <table class="pilgrim-table">
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

            <div class="section-title">Remarks</div>
            <table>
                <tbody>
                    <tr>
                        <td class="remarks"><?= esc((string) ($booking['remarks'] ?? '')) ?></td>
                    </tr>
                </tbody>
            </table>

            <?php if ($voucherInstructionsUr !== '' || $voucherInstructionsEn !== ''): ?>
                <div class="section-title">Instructions</div>
                <table>
                    <tbody>
                        <?php if ($voucherInstructionsUr !== ''): ?>
                            <tr>
                                <td style="white-space: pre-line;" class="urdu-text"><?= esc($voucherInstructionsUr) ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($voucherInstructionsEn !== ''): ?>
                            <tr>
                                <td style="white-space: pre-line;"><?= esc($voucherInstructionsEn) ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if ($makkahContact !== '' || $madinaContact !== '' || $transportContact !== ''): ?>
                <div class="section-title">Contact Detail</div>
                <div class="contact-grid">
                    <?php if ($makkahContact !== ''): ?>
                        <div class="contact-item">
                            <div class="contact-label">Makkah Office</div>
                            <div class="contact-value"><?= esc($makkahContact) ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if ($madinaContact !== ''): ?>
                        <div class="contact-item">
                            <div class="contact-label">Madina Office</div>
                            <div class="contact-value"><?= esc($madinaContact) ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if ($transportContact !== ''): ?>
                        <div class="contact-item">
                            <div class="contact-label">Transport Contact</div>
                            <div class="contact-value"><?= esc($transportContact) ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </article>
</body>

</html>