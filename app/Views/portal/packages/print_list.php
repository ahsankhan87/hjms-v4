<?php

/**
 * Printable / PDF package list (all active packages).
 * Standalone page — no portal chrome, A4 print stylesheet.
 *
 * @var array $cards
 * @var array $company
 */
$tierLabels = \App\Services\HotelRateService::TIER_LABELS;
$fmtDate = static function ($value, string $format = 'd M Y'): string {
    $value = trim((string) ($value ?? ''));

    return $value === '' ? '—' : date($format, strtotime($value));
};
$fmtMoney = static function ($value): string {
    return number_format((float) $value, 0);
};
$totalSeats = 0;
$totalAvailable = 0;
foreach ($cards as $cardRow) {
    $totalSeats += (int) ($cardRow['seat_capacity'] ?? 0);
    $totalAvailable += (int) ($cardRow['available_seats'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Package List</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 10px 12px;
            background: #eef2f6;
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 8.6pt;
            color: #0f172a;
        }

        .toolbar {
            max-width: 210mm;
            margin: 0 auto 8px;
            display: flex;
            gap: 6px;
            justify-content: flex-end;
        }

        .toolbar button,
        .toolbar a {
            font: inherit;
            font-size: 8pt;
            padding: 5px 12px;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #0f172a;
            cursor: pointer;
            text-decoration: none;
        }

        .toolbar button.primary {
            background: #0f766e;
            border-color: #0f766e;
            color: #fff;
        }

        .sheet {
            background: #fff;
            max-width: 210mm;
            margin: 0 auto;
            padding: 8mm 8mm 6mm;
            border: 1px solid #e2e8f0;
        }

        .head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            border-bottom: 1.5px solid #0f766e;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .brand img {
            height: 26px;
            width: auto;
        }

        .brand-name {
            font-size: 11pt;
            font-weight: 700;
            letter-spacing: .3px;
        }

        .brand-sub {
            font-size: 7pt;
            color: #64748b;
        }

        .doc-title {
            text-align: right;
        }

        .doc-title h1 {
            margin: 0;
            font-size: 11pt;
            letter-spacing: .6px;
            text-transform: uppercase;
            color: #0f766e;
        }

        .doc-title div {
            font-size: 7pt;
            color: #475569;
        }

        .summary {
            display: flex;
            flex-wrap: wrap;
            gap: 4px 16px;
            font-size: 7.6pt;
            color: #334155;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            padding: 3px 6px;
            margin-bottom: 7px;
        }

        .summary b {
            color: #0f172a;
        }

        .pkg {
            break-inside: avoid;
            margin-bottom: 7px;
            border: 1px solid #cbd5e1;
        }

        .pkg-head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 8px;
            background: #0f766e;
            color: #fff;
            padding: 3px 7px;
        }

        .pkg-head .name {
            font-size: 9.4pt;
            font-weight: 700;
        }

        .pkg-head .code {
            display: inline-block;
            margin-right: 5px;
            padding: 0 4px;
            background: rgba(255, 255, 255, .18);
            border: 1px solid rgba(255, 255, 255, .35);
            border-radius: 3px;
            font-size: 7.6pt;
            font-weight: 700;
            letter-spacing: .4px;
        }

        .pkg-head .meta {
            font-size: 7.6pt;
            text-align: right;
            white-space: nowrap;
        }

        .pkg-body {
            display: flex;
        }

        .pkg-left {
            flex: 1 1 auto;
            padding: 4px 7px;
            border-right: 1px solid #e2e8f0;
            min-width: 0;
        }

        .pkg-right {
            flex: 0 0 62mm;
            padding: 4px 7px;
        }

        .line {
            display: flex;
            gap: 5px;
            font-size: 7.8pt;
            line-height: 1.35;
        }

        .line+.line {
            margin-top: 1px;
        }

        .tag {
            flex: 0 0 auto;
            min-width: 13mm;
            text-align: center;
            font-size: 6.6pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .3px;
            border-radius: 2px;
            padding: 0 3px;
        }

        .tag.out {
            background: #e0f2fe;
            color: #0369a1;
        }

        .tag.ret {
            background: #e0e7ff;
            color: #4338ca;
        }

        .tag.hotel {
            background: #dcfce7;
            color: #15803d;
        }

        .tag.bus {
            background: #fef3c7;
            color: #b45309;
        }

        .line .val {
            min-width: 0;
        }

        .line .val b {
            font-weight: 600;
        }

        .muted {
            color: #64748b;
        }

        table.costs {
            width: 100%;
            border-collapse: collapse;
        }

        table.costs th,
        table.costs td {
            border-bottom: 1px solid #e2e8f0;
            padding: 1px 2px;
            font-size: 7.8pt;
        }

        table.costs th {
            text-align: left;
            font-size: 6.6pt;
            text-transform: uppercase;
            letter-spacing: .3px;
            color: #64748b;
            font-weight: 700;
        }

        table.costs td {
            text-align: right;
            font-weight: 700;
        }

        table.costs tr:last-child td,
        table.costs tr:last-child th {
            border-bottom: 0;
        }

        .from {
            margin-top: 3px;
            padding-top: 3px;
            border-top: 1.5px solid #0f766e;
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 6px;
        }

        .from span {
            font-size: 6.8pt;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: #64748b;
            font-weight: 700;
        }

        .from b {
            font-size: 11pt;
            color: #065f46;
        }

        .empty {
            padding: 24px;
            text-align: center;
            color: #64748b;
        }

        .foot {
            margin-top: 8px;
            padding-top: 4px;
            border-top: 1px solid #cbd5e1;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            font-size: 7pt;
            color: #475569;
        }

        @page {
            size: A4 portrait;
            margin: 8mm;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
                font-size: 8.2pt;
            }

            .sheet {
                border: 0;
                padding: 0;
                max-width: none;
            }

            .toolbar {
                display: none !important;
            }

            .pkg {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <a href="<?= site_url('packages') ?>">Back to list</a>
        <button type="button" class="primary" onclick="window.print()">Print / Save as PDF</button>
    </div>

    <div class="sheet">
        <div class="head">
            <div class="brand">
                <?php if (!empty($company['logo_url'])): ?>
                    <img src="<?= esc((string) $company['logo_url']) ?>" alt="Logo">
                <?php endif; ?>
                <div>
                    <div class="brand-name"><?= esc((string) ($company['name'] ?? 'KARWAN-E-TAIF PVT LTD')) ?></div>
                    <div class="brand-sub"><?= esc((string) ($company['tagline'] ?? 'Hajj & Umrah Management')) ?></div>
                </div>
            </div>
            <div class="doc-title">
                <h1>Package List</h1>
                <div>Generated <?= esc(date('d M Y H:i')) ?><?= !empty($company['phone']) ? ' · ' . esc((string) $company['phone']) : '' ?></div>
            </div>
        </div>

        <div class="summary">
            <span><b>Packages:</b> <?= esc((string) count($cards)) ?></span>
            <span><b>Total seats:</b> <?= esc((string) $totalSeats) ?></span>
            <span><b>Available:</b> <?= esc((string) $totalAvailable) ?></span>
        </div>

        <?php if (empty($cards)): ?>
            <div class="empty">No active packages to print.</div>
        <?php else: ?>
            <?php foreach ($cards as $card): ?>
                <?php
                $outbound = $card['outbound_flight'] ?? null;
                $return = $card['return_flight'] ?? null;
                $hotelStays = array_values(array_filter((array) ($card['hotel_stays'] ?? [])));
                $priceMap = (array) ($card['price_map'] ?? []);
                $isFlat = ($card['package_mode'] ?? 'tiered') === 'flat';

                $minPrice = null;
                foreach ($priceMap as $tierValue) {
                    if ($tierValue === null || ! is_numeric($tierValue) || (float) $tierValue <= 0) {
                        continue;
                    }
                    $minPrice = $minPrice === null ? (float) $tierValue : min($minPrice, (float) $tierValue);
                }
                if ($isFlat && $card['flat_price'] !== null) {
                    $minPrice = (float) $card['flat_price'];
                }

                $seatTotal = max(0, (int) ($card['seat_capacity'] ?? 0));
                $seatLeft = max(0, (int) ($card['available_seats'] ?? 0));
                $transportTypes = implode(' / ', array_filter(array_map('strval', (array) ($card['transport_types'] ?? []))));
                $transportNames = implode(', ', array_filter(array_map('strval', (array) ($card['transport_names'] ?? []))));
                ?>
                <div class="pkg">
                    <div class="pkg-head">
                        <div class="name"><span class="code"><?= esc((string) (($card['code'] ?? '') !== '' ? $card['code'] : '—')) ?></span><?= esc((string) ($card['name'] ?? 'Untitled package')) ?></div>
                        <div class="meta">
                            <?= esc($fmtDate($card['package_departure_date'] ?? '')) ?> → <?= esc($fmtDate($card['package_arrival_date'] ?? '')) ?>
                            · <?= esc((string) (int) ($card['duration_days'] ?? 0)) ?> days
                            · <?= esc((string) $seatLeft) ?>/<?= esc((string) $seatTotal) ?> seats
                        </div>
                    </div>

                    <div class="pkg-body">
                        <div class="pkg-left">
                            <?php if (!(int) ($card['include_ticket'] ?? 1)): ?>
                                <div class="line"><span class="tag out">Flight</span><span class="val muted">Ticket not included — self arranged</span></div>
                            <?php else: ?>
                                <div class="line">
                                    <span class="tag out">Out</span>
                                    <span class="val">
                                        <b><?= esc(trim((string) (($outbound['airline'] ?? '') . ' ' . ($outbound['flight_no'] ?? ''))) ?: 'TBA') ?></b>
                                        <?php if (trim((string) (($outbound['departure_airport'] ?? '') . ($outbound['arrival_airport'] ?? ''))) !== ''): ?>
                                            <span class="muted">· <?= esc(trim((string) (($outbound['departure_airport'] ?? '') . ' → ' . ($outbound['arrival_airport'] ?? '')), ' →')) ?></span>
                                        <?php endif; ?>
                                        <span class="muted">· <?= esc($fmtDate($outbound['departure_at'] ?? $card['departure_datetime'] ?? '', 'd M Y H:i')) ?></span>
                                    </span>
                                </div>
                                <div class="line">
                                    <span class="tag ret">Ret</span>
                                    <span class="val">
                                        <b><?= esc(trim((string) (($return['airline'] ?? '') . ' ' . ($return['flight_no'] ?? ''))) ?: 'TBA') ?></b>
                                        <?php if (trim((string) (($return['departure_airport'] ?? '') . ($return['arrival_airport'] ?? ''))) !== ''): ?>
                                            <span class="muted">· <?= esc(trim((string) (($return['departure_airport'] ?? '') . ' → ' . ($return['arrival_airport'] ?? '')), ' →')) ?></span>
                                        <?php endif; ?>
                                        <span class="muted">· <?= esc($fmtDate($return['arrival_at'] ?? $card['return_arrival_datetime'] ?? '', 'd M Y H:i')) ?></span>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <?php if ($hotelStays === []): ?>
                                <div class="line"><span class="tag hotel">Hotel</span><span class="val muted"><?= (int) ($card['include_hotel'] ?? 1) ? 'No hotel linked yet' : 'Hotel not included — self arranged' ?></span></div>
                            <?php else: ?>
                                <?php foreach (array_slice($hotelStays, 0, 6) as $stay): ?>
                                    <?php $distance = (int) ($stay['distance_m'] ?? 0); ?>
                                    <div class="line">
                                        <span class="tag hotel"><?= esc((string) ($stay['city'] ?? 'Hotel')) ?></span>
                                        <span class="val">
                                            <b><?= esc((string) ($stay['name'] ?? 'Hotel pending')) ?></b>
                                            <span class="muted">· <?= esc((string) (int) ($stay['nights'] ?? 0)) ?> nights<?= $distance > 0 ? ' · ' . esc(number_format($distance)) . ' m' : '' ?></span>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if ($transportTypes !== '' || $transportNames !== ''): ?>
                                <div class="line">
                                    <span class="tag bus">Trans</span>
                                    <span class="val">
                                        <?php if ($transportTypes !== ''): ?><b><?= esc($transportTypes) ?></b><?php endif; ?>
                                        <?php if ($transportNames !== ''): ?><span class="muted">· <?= esc($transportNames) ?></span><?php endif; ?>
                                        <span class="muted">· <?= esc((string) (int) ($card['transport_count'] ?? 0)) ?> vehicle(s)</span>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <div class="line">
                                <span class="tag" style="background:#f1f5f9;color:#334155;">Incl.</span>
                                <span class="val muted">
                                    <?php
                                    $bits = [];
                                    if ((int) ($card['include_hotel'] ?? 1)) {
                                        $bits[] = 'Hotel';
                                    }
                                    if ((int) ($card['include_ticket'] ?? 1)) {
                                        $bits[] = 'Ticket';
                                    }
                                    if ((int) ($card['include_transport'] ?? 1)) {
                                        $bits[] = 'Transport';
                                    }
                                    if ((float) ($card['visa_cost'] ?? 0) > 0) {
                                        $bits[] = 'Visa';
                                    }
                                    if ((int) ($card['include_ziarat'] ?? 0)) {
                                        $bits[] = 'Ziarat';
                                    }
                                    $bits[] = 'Booked ' . (int) ($card['booked_seats'] ?? 0);
                                    echo esc(implode(' · ', $bits));
                                    ?>
                                </span>
                            </div>
                        </div>

                        <div class="pkg-right">
                            <table class="costs">
                                <thead>
                                    <tr>
                                        <th colspan="2">Cost per person (PKR)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($isFlat): ?>
                                        <tr>
                                            <th>Package</th>
                                            <td><?= esc($fmtMoney($card['flat_price'] ?? 0)) ?></td>
                                        </tr>
                                    <?php elseif ($priceMap === []): ?>
                                        <tr>
                                            <td colspan="2" class="muted" style="text-align:left;font-weight:400;">No price set</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($priceMap as $tierKey => $tierValue): ?>
                                            <?php if ($tierValue === null || (float) $tierValue <= 0) {
                                                continue;
                                            } ?>
                                            <tr>
                                                <th><?= esc((string) ($tierLabels[$tierKey] ?? ucfirst((string) $tierKey))) ?></th>
                                                <td><?= esc($fmtMoney($tierValue)) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <?php if ($minPrice !== null): ?>
                                <div class="from">
                                    <span>From</span>
                                    <b>PKR <?= esc($fmtMoney($minPrice)) ?></b>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="foot">
            <span><?= esc((string) ($company['name'] ?? '')) ?><?= !empty($company['address']) ? ' · ' . esc((string) $company['address']) : '' ?></span>
            <span>Prices are per person in PKR and subject to availability.</span>
        </div>
    </div>
</body>

</html>