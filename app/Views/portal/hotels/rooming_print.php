<?php
/**
 * Compact, print-only rooming list. Rendered without the portal chrome.
 *
 * @var array  $rooming
 * @var string $city
 * @var int    $packageId
 * @var array  $company
 * @var bool   $autoPrint
 */
$package = $rooming['package'] ?? null;
$stays = $rooming['stays'] ?? [];
$totals = $rooming['totals'] ?? ['pilgrims' => 0, 'rooms' => 0];
$fmtDate = static function ($value, string $format = 'd M Y'): string {
    $value = trim((string) ($value ?? ''));

    return $value === '' ? '-' : date($format, strtotime($value));
};
$cityFilter = trim((string) ($city ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rooming List<?= $package !== null ? ' — ' . esc((string) ($package['code'] ?? '')) : '' ?></title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 10px 12px;
            background: #f1f5f9;
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 9pt;
            color: #111827;
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
            gap: 8px;
            border-bottom: 1.5px solid #0f766e;
            padding-bottom: 3px;
            margin-bottom: 4px;
        }
        .brand { display: flex; align-items: center; gap: 6px; }
        .brand img { height: 26px; width: auto; }
        .brand-name { font-size: 11pt; font-weight: 700; letter-spacing: .3px; color: #0f172a; }
        .brand-sub { font-size: 7pt; color: #64748b; }
        .doc-title { text-align: right; }
        .doc-title h1 { margin: 0; font-size: 11pt; letter-spacing: .6px; text-transform: uppercase; color: #0f766e; }
        .doc-title div { font-size: 7pt; color: #475569; }

        .meta {
            display: flex;
            flex-wrap: wrap;
            gap: 4px 14px;
            font-size: 7.5pt;
            padding: 3px 0 5px;
            border-bottom: 1px solid #cbd5e1;
        }
        .meta b { font-weight: 600; }

        .stay { margin-top: 6px; break-inside: avoid; }
        .stay-head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 8px;
            background: #0f766e;
            color: #fff;
            padding: 2px 6px;
            font-size: 8pt;
            font-weight: 600;
        }
        .stay-head .stay-meta { font-weight: 400; font-size: 7pt; opacity: .95; }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 1.5px 4px;
            font-size: 7.5pt;
            vertical-align: top;
        }
        th { background: #f1f5f9; font-size: 6.8pt; text-transform: uppercase; letter-spacing: .3px; color: #334155; text-align: left; }
        td.num { text-align: center; white-space: nowrap; }
        td.room { white-space: nowrap; font-weight: 600; }
        .pax-line { display: block; }
        .pax-line + .pax-line { border-top: 1px dotted #e2e8f0; }
        .pax-idx { color: #64748b; margin-right: 3px; }
        .pax-doc { color: #475569; font-size: 6.8pt; }
        .muted { color: #64748b; }

        .totals {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 6px;
            padding-top: 3px;
            border-top: 1px solid #cbd5e1;
            font-size: 7.5pt;
            color: #334155;
        }
        .empty { padding: 18px; text-align: center; color: #64748b; font-size: 9pt; }

        .signoff {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            margin-top: 10px;
            padding-top: 6px;
            font-size: 7.5pt;
            color: #334155;
        }

        .toolbar {
            max-width: 210mm;
            margin: 0 auto 8px;
            display: flex;
            gap: 6px;
            justify-content: flex-end;
        }
        .toolbar button, .toolbar a {
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
        .toolbar button.primary { background: #0f766e; border-color: #0f766e; color: #fff; }

        @page { size: A4; margin: 8mm; }
        @media print {
            body { background: #fff; padding: 0; font-size: 8.6pt; }
            .sheet { border: 0; padding: 0; max-width: none; }
            .toolbar { display: none !important; }
            .stay { page-break-inside: avoid; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="<?= site_url('hotels/rooming-list?package_id=' . (int) $packageId . '&city=' . urlencode($cityFilter)) ?>">Back</a>
        <button type="button" class="primary" onclick="window.print()">Print</button>
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
                <h1>Rooming List</h1>
                <div>Printed <?= esc(date('d M Y H:i')) ?></div>
            </div>
        </div>

        <?php if ($package !== null): ?>
            <div class="meta">
                <span><b>Package:</b> <?= esc((string) ($package['code'] ?? '')) ?> — <?= esc((string) ($package['name'] ?? '')) ?></span>
                <span><b>Departure:</b> <?= esc($fmtDate($package['departure_date'] ?? '')) ?></span>
                <span><b>Return:</b> <?= esc($fmtDate($package['arrival_date'] ?? '')) ?></span>
                <span><b>Duration:</b> <?= esc((string) (int) ($package['duration_days'] ?? 0)) ?> days</span>
                <?php if ($cityFilter !== ''): ?><span><b>City:</b> <?= esc($cityFilter) ?></span><?php endif; ?>
                <span><b>Pilgrims:</b> <?= esc((string) (int) ($totals['pilgrims'] ?? 0)) ?></span>
                <span><b>Room allocations:</b> <?= esc((string) (int) ($totals['rooms'] ?? 0)) ?></span>
            </div>
        <?php endif; ?>

        <?php if (empty($stays)): ?>
            <div class="empty">No confirmed pilgrims with hotel stays for this package.</div>
        <?php else: ?>
            <?php foreach ($stays as $stay): ?>
                <?php $groups = $stay['groups'] ?? []; ?>
                <div class="stay">
                    <div class="stay-head">
                        <span><?= esc((string) ($stay['hotel_name'] ?? '-')) ?><?php if (($stay['city'] ?? '') !== ''): ?> · <?= esc((string) $stay['city']) ?><?php endif; ?><?php if ((int) ($stay['star_rating'] ?? 0) > 0): ?> · <?= esc((string) (int) $stay['star_rating']) ?>★<?php endif; ?></span>
                        <span class="stay-meta">
                            <?= esc($fmtDate($stay['check_in'] ?? '', 'd/m/Y')) ?> → <?= esc($fmtDate($stay['check_out'] ?? '', 'd/m/Y')) ?>
                            · <?= esc((string) (int) ($stay['nights'] ?? 0)) ?> nights
                            · <?= esc((string) (int) ($stay['rooms'] ?? 0)) ?> rooms
                            · <?= esc((string) (int) ($stay['pilgrims'] ?? 0)) ?> pax
                        </span>
                    </div>

                    <?php if ($groups === []): ?>
                        <table><tbody><tr><td class="muted">No pilgrims allocated for this stay.</td></tr></tbody></table>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 9mm;">#</th>
                                    <th style="width: 22mm;">Room</th>
                                    <th style="width: 12mm;">Occ.</th>
                                    <th>Pilgrims (name · passport · gender · contact)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($groups as $group): ?>
                                    <tr>
                                        <td class="num"><?= esc((string) (int) ($group['room_no'] ?? 0)) ?></td>
                                        <td class="room"><?= esc((string) ($group['tier_label'] ?? '')) ?></td>
                                        <td class="num"><?= esc((string) count($group['pilgrims'] ?? [])) ?>/<?= esc((string) (int) ($group['beds'] ?? 0)) ?></td>
                                        <td>
                                            <?php $idx = 0; ?>
                                            <?php foreach (($group['pilgrims'] ?? []) as $pilgrim): ?>
                                                <?php $idx++; ?>
                                                <span class="pax-line">
                                                    <span class="pax-idx"><?= esc((string) $idx) ?>.</span><?= esc((string) ($pilgrim['name'] ?? '-')) ?>
                                                    <span class="pax-doc">
                                                        <?php if (($pilgrim['passport_no'] ?? '') !== ''): ?>· <?= esc((string) $pilgrim['passport_no']) ?><?php endif; ?>
                                                        <?php if (($pilgrim['gender'] ?? '') !== ''): ?>· <?= esc((string) $pilgrim['gender']) ?><?php endif; ?>
                                                        <?php if (($pilgrim['contact'] ?? '') !== ''): ?>· <?= esc((string) $pilgrim['contact']) ?><?php endif; ?>
                                                    </span>
                                                </span>
                                            <?php endforeach; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="totals">
                <span><?= esc((string) ($company['name'] ?? '')) ?><?php if (!empty($company['phone'])): ?> · <?= esc((string) $company['phone']) ?><?php endif; ?></span>
                <span>Total: <?= esc((string) (int) ($totals['pilgrims'] ?? 0)) ?> pilgrims · <?= esc((string) (int) ($totals['rooms'] ?? 0)) ?> room allocations</span>
            </div>

            <div class="signoff">
                <div>Prepared by: ____________________</div>
                <div>Received by (KSA): ____________________</div>
                <div>Date: ______________</div>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($autoPrint)): ?>
        <script>
            window.addEventListener('load', function() { window.print(); });
        </script>
    <?php endif; ?>
</body>
</html>
