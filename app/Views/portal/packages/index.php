<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="bg-slate-50 min-h-screen pb-2">

    <!-- Top Alert Messages -->
    <div class="max-w-7xl mx-auto px-2 sm:px-3 lg:px-2 py-2">
        <?php if (!empty($success)): ?>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-circle-check"></i> <?= esc($success) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800 flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-circle-exclamation"></i> <?= esc($error) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800 shadow-sm">
                <ul class="list-disc list-inside text-sm">
                    <?php foreach ($errors as $err): ?>
                        <li><?= esc($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="flex items-center justify-between mb-3">
            <div class="flex space-x-4">
                <a href="<?= site_url('/packages/add') ?>" class="btn btn-md btn-primary inline-flex items-center justify-center gap-2">
                    <i class="fa-solid fa-plus"></i> Create New Package
                </a>
            </div>
            <!-- <div class="flex space-x-2">
                <button class="px-3 py-2 bg-green-100 text-green-700 rounded-lg font-medium">All</button>
                <button class="px-3 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Hajj</button>
                <button class="px-3 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Umrah</button>
            </div> -->
        </div>

        <!-- Content Grid -->
        <?php if (empty($cards)): ?>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 text-slate-400 mb-4">
                    <i class="fa-solid fa-box-open text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-slate-900">No packages found</h3>
                <p class="text-slate-500 mt-1">Get started by creating a new package.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mt-2">
                <?php foreach ($cards as $card): ?>
                    <?php
                    $formatDateTime = static function ($value): string {
                        $value = trim((string) $value);
                        if ($value === '') {
                            return 'TBA';
                        }

                        $timestamp = strtotime($value);
                        if ($timestamp === false) {
                            return 'TBA';
                        }

                        return date('M d, Y h:i A', $timestamp);
                    };

                    $outbound = $card['outbound_flight'] ?? null;
                    $return = $card['return_flight'] ?? null;
                    ?>
                    <div class="bg-white rounded-lg border border-slate-200 overflow-hidden flex flex-col">

                        <!-- Card Header / Image Area -->
                        <div class="bg-slate-50 px-2 py-1.5 border-b border-slate-100">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <h3 class="text-lg font-bold text-slate-800 leading-tight line-clamp-2"><?= esc($card['name']) ?></h3>
                                    <div class="mt-1 text-xs text-slate-500">
                                        <?= esc($card['code'] ?: 'Route TBA') ?>
                                    </div>
                                    <?php
                                    $pkgDep = (string) ($card['package_departure_date'] ?? '');
                                    $pkgArr = (string) ($card['package_arrival_date'] ?? '');
                                    $pkgDepTs = $pkgDep !== '' ? strtotime($pkgDep) : false;
                                    $pkgArrTs = $pkgArr !== '' ? strtotime($pkgArr) : false;
                                    ?>
                                    <?php if ($pkgDepTs || $pkgArrTs): ?>
                                        <div class="mt-1 flex items-center gap-1 text-[10px] text-slate-500">
                                            <i class="fa-solid fa-calendar-days text-slate-400"></i>
                                            <span><?= esc($pkgDepTs ? date('d M Y', $pkgDepTs) : '—') ?></span>
                                            <span class="text-slate-300">→</span>
                                            <span><?= esc($pkgArrTs ? date('d M Y', $pkgArrTs) : '—') ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="shrink-0 flex items-center gap-1">
                                    <div class="rounded-md border border-slate-200 bg-white px-2 py-1 text-[11px] font-semibold text-slate-700">
                                        <?= esc($card['duration_days']) ?> Days
                                    </div>
                                    <div class="rounded-md border border-slate-200 bg-white px-2 py-1 text-[11px] font-semibold text-slate-700">
                                        <?= esc($card['available_seats']) ?> Seats Left
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-2.5 flex-1">
                            <div class="space-y-1 text-xs">
                                <?php if (!(int) ($card['include_ticket'] ?? 1)): ?>
                                    <div class="rounded-md bg-slate-50 border border-slate-200 px-2 py-2 text-[11px] text-slate-400 italic text-center col-span-full"><i class="fa-solid fa-plane mr-1"></i> Flight — Self-arranged</div>
                                <?php else: ?>
                                    <div class="grid grid-cols-[52px_1fr_auto] gap-1 items-center rounded-md bg-sky-50/80 px-1.5 py-1.5">
                                        <div class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wide text-sky-700">
                                            <span>Out</span>
                                            <span class="relative inline-flex h-7 w-7 items-center justify-center rounded-sm border border-slate-300 bg-white shadow-sm">
                                                <?php if (!empty($card['airline_logo'])): ?>
                                                    <img
                                                        src="<?= esc($card['airline_logo']) ?>"
                                                        alt="Airline"
                                                        class="h-6 w-6 object-contain"
                                                        onerror="this.style.display='none'; this.parentNode.querySelector('.airline-fallback-icon').style.display='flex';">
                                                    <span class="airline-fallback-icon hidden absolute inset-0 items-center justify-center text-[11px] text-slate-500">
                                                        <i class="fa-solid fa-plane"></i>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="airline-fallback-icon absolute inset-0 flex items-center justify-center text-[11px] text-slate-500">
                                                        <i class="fa-solid fa-plane"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-medium text-slate-700 truncate"><?= !empty($outbound) ? esc(trim((string) (($outbound['airline'] ?? '') . ' ' . ($outbound['flight_no'] ?? '')))) : 'TBA' ?></div>
                                            <div class="text-slate-500 truncate"><?= !empty($outbound) ? esc(trim((string) (($outbound['departure_airport'] ?? '') . ' -> ' . ($outbound['arrival_airport'] ?? '')), ' ->')) : 'Route TBA' ?></div>
                                        </div>
                                        <div class="text-[10px] text-slate-600 whitespace-nowrap"><?= esc($formatDateTime($outbound['departure_at'] ?? ($card['departure_datetime'] ?? ''))) ?></div>
                                    </div>

                                    <div class="grid grid-cols-[52px_1fr_auto] gap-1 items-center rounded-md bg-emerald-50/80 px-1.5 py-1.5">
                                        <div class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wide text-emerald-700">
                                            <span>Ret</span>
                                            <span class="relative inline-flex h-7 w-7 items-center justify-center rounded-sm border border-slate-300 bg-white shadow-sm">
                                                <?php if (!empty($card['return_airline_logo'])): ?>
                                                    <img
                                                        src="<?= esc($card['return_airline_logo']) ?>"
                                                        alt="Return Airline"
                                                        class="h-6 w-6 object-contain"
                                                        onerror="this.style.display='none'; this.parentNode.querySelector('.airline-fallback-icon').style.display='flex';">
                                                    <span class="airline-fallback-icon hidden absolute inset-0 items-center justify-center text-[11px] text-slate-500">
                                                        <i class="fa-solid fa-plane"></i>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="airline-fallback-icon absolute inset-0 flex items-center justify-center text-[11px] text-slate-500">
                                                        <i class="fa-solid fa-plane"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-medium text-slate-700 truncate"><?= !empty($return) ? esc(trim((string) (($return['airline'] ?? '') . ' ' . ($return['flight_no'] ?? '')))) : 'TBA' ?></div>
                                            <div class="text-slate-500 truncate"><?= !empty($return) ? esc(trim((string) (($return['departure_airport'] ?? '') . ' -> ' . ($return['arrival_airport'] ?? '')), ' ->')) : 'Route TBA' ?></div>
                                        </div>
                                        <div class="text-[10px] text-slate-600 whitespace-nowrap"><?= esc($formatDateTime($return['arrival_at'] ?? ($card['return_arrival_datetime'] ?? ''))) ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Hotel Info -->
                            <?php $hotelStays = $card['hotel_stays'] ?? []; ?>
                            <div class="space-y-1 mb-2 mt-2.5 text-xs">
                                <?php if (!empty($hotelStays)): ?>
                                    <?php foreach ($hotelStays as $stay): ?>
                                        <?php
                                        $cityText = strtolower(trim((string) ($stay['city'] ?? '')));
                                        $isMadina = preg_match('/madina|medina/i', $cityText) === 1;
                                        $cityLogo = $isMadina
                                            ? base_url('assets/uploads/madina-logo.png')
                                            : base_url('assets/uploads/makkah-logo.png');
                                        $cityAlt = $isMadina ? 'Madina' : 'Makkah';
                                        ?>
                                        <div class="flex items-center gap-1.5 text-sm text-slate-600">
                                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md border border-slate-200 bg-white p-0.5">
                                                <img src="<?= esc($cityLogo) ?>" alt="<?= esc($cityAlt) ?>" class="max-h-7 max-w-7 object-contain">
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <?php if (!empty($stay['id'])): ?>
                                                    <a href="<?= site_url('/hotels/' . (int) $stay['id']) ?>" class="line-clamp-1 text-slate-700 hover:text-emerald-700 hover:underline" title="View hotel details">
                                                        <?= esc((string) ($stay['name'] ?? 'Hotel pending')) ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="line-clamp-1"><?= esc((string) ($stay['name'] ?? 'Hotel pending')) ?></span>
                                                <?php endif; ?>
                                                <div class="text-[10px] text-slate-500">
                                                    <i class="fa-solid fa-location-dot mr-0.5"></i><?= esc((string) ($stay['city'] ?? $cityAlt)) ?>
                                                </div>
                                            </div>
                                            <span class="rounded-md border border-slate-200 bg-white px-2 py-0.5 text-[10px] font-semibold text-slate-700">
                                                <?= esc((string) max(0, (int) ($stay['nights'] ?? 0))) ?>D
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php elseif (!(int) ($card['include_hotel'] ?? 1)): ?>
                                    <div class="text-[11px] text-slate-400 italic"><i class="fa-solid fa-bed mr-1"></i> Hotel — Self-arranged</div>
                                <?php else: ?>
                                    <div class="text-[11px] text-slate-500">Hotel stay distribution not available.</div>
                                <?php endif; ?>
                            </div>

                            <!-- Transport Info -->
                            <?php $transportTypes = $card['transport_types'] ?? []; ?>
                            <?php $transportNames = $card['transport_names'] ?? []; ?>
                            <?php $transportTypeText = !empty($transportTypes) ? implode(' / ', $transportTypes) : 'TBA'; ?>
                            <?php $transportNameText = !empty($transportNames) ? implode(' | ', $transportNames) : 'Transport details pending'; ?>
                            <?php if (!(int) ($card['include_transport'] ?? 1)): ?>
                                <div class="mb-2 rounded-md border border-slate-200 bg-slate-50 px-2 py-1 text-[11px] text-slate-400 italic">
                                    <div class="flex items-center gap-1">
                                        <i class="fa-solid fa-bus"></i>
                                        <span>Transport — Self-arranged</span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="mb-2 rounded-md border border-slate-200 bg-slate-50 px-2 py-1 text-[11px] text-slate-700">
                                    <div class="flex items-center gap-1 min-w-0">
                                        <i class="fa-solid fa-bus text-slate-600"></i>
                                        <span class="font-semibold shrink-0"><?= esc($transportTypeText) ?></span>
                                        <span class="text-slate-400">|</span>
                                        <span class="text-slate-500 truncate"><?= esc($transportNameText) ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Pricing Grid -->
                            <div class="bg-slate-50 rounded-md p-1.5 grid grid-cols-2 gap-1 text-xs">
                                <?php $sharingPrice = $card['price_map']['sharing'] ?? null; ?>
                                <?php if ($sharingPrice !== null): ?>
                                    <div>
                                        <span class="text-slate-500 block">Sharing</span>
                                        <span class="font-bold text-slate-800">PKR <?= number_format((float) $sharingPrice) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php $quadPrice = $card['price_map']['quad'] ?? null; ?>
                                <?php if ($quadPrice !== null): ?>
                                    <div>
                                        <span class="text-slate-500 block">Quad</span>
                                        <span class="font-bold text-slate-800">PKR <?= number_format((float) $quadPrice) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php $triplePrice = $card['price_map']['triple'] ?? null; ?>
                                <?php if ($triplePrice !== null): ?>
                                    <div>
                                        <span class="text-slate-500 block">Triple</span>
                                        <span class="font-bold text-slate-800">PKR <?= number_format((float) $triplePrice) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php $doublePrice = $card['price_map']['double'] ?? null; ?>
                                <?php if ($doublePrice !== null): ?>
                                    <div>
                                        <span class="text-slate-500 block">Double</span>
                                        <span class="font-bold text-slate-800">PKR <?= number_format((float) $doublePrice) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Card Footer / Actions -->
                        <div class="px-2 py-1.5 bg-white border-t border-slate-100 flex items-center justify-between gap-1.5">
                            <form method="post" action="<?= site_url('/packages/delete') ?>" class="shrink-0" onsubmit="return confirm('Are you sure you want to delete this package?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="package_id" value="<?= esc($card['id']) ?>">
                                <button type="submit" class="w-10 h-10 rounded-lg border border-slate-200 text-rose-600 hover:bg-rose-100 hover:text-rose-600 hover:border-rose-200 flex items-center justify-center" title="Delete Package">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>

                            <button type="button"
                                onclick="sharePackage(<?= (int) $card['id'] ?>)"
                                class="w-10 h-10 shrink-0 rounded-lg border border-slate-200 text-sky-600 hover:bg-sky-50 hover:border-sky-300 flex items-center justify-center"
                                title="Download package image">
                                <i class="fa-solid fa-download"></i>
                            </button>

                            <a href="<?= site_url('/packages/' . (int) $card['id'] . '/edit') ?>" class="btn btn-md btn-primary flex-1 text-center">
                                Manage Package
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- ═══════════════════════════════════════════════════
     Hidden share-card templates (one per package)
     Rendered off-screen, captured by html2canvas as PNG
     ═══════════════════════════════════════════════════ -->
<?php
$company = main_company();
$companyName = esc((string) ($company['name'] ?? 'HJMS'));
$companyPhone = esc((string) ($company['phone'] ?? ''));
$companyEmail = esc((string) ($company['email'] ?? ''));
?>
<div id="share-cards-container" style="position:fixed;left:-9999px;top:0;pointer-events:none;z-index:-1;">
    <?php foreach ($cards as $card):
        $shareOutbound = $card['outbound_flight'] ?? null;
        $shareReturn   = $card['return_flight'] ?? null;
        $shareHotels   = $card['hotel_stays'] ?? [];
        $shareTransports = $card['transport_names'] ?? [];
        $sharePrices   = $card['price_map'] ?? [];
        $pkgDep2 = (string) ($card['package_departure_date'] ?? '');
        $pkgArr2 = (string) ($card['package_arrival_date'] ?? '');
        $pkgDepFmt = $pkgDep2 !== '' ? date('d M Y', strtotime($pkgDep2)) : '—';
        $pkgArrFmt = $pkgArr2 !== '' ? date('d M Y', strtotime($pkgArr2)) : '—';
    ?>
        <div id="share-card-<?= (int) $card['id'] ?>"
            style="width:480px;background:#ffffff;font-family:'Segoe UI',Arial,sans-serif;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.15);">

            <!-- Header -->
            <div style="background:linear-gradient(135deg,#1e6b3e 0%,#25a55a 100%);padding:18px 20px 14px;">
                <div style="font-size:20px;font-weight:700;color:#fff;letter-spacing:.5px;"><?= $companyName ?></div>
                <div style="font-size:11px;color:#a8f0c6;margin-top:2px;">Hajj &amp; Umrah Package</div>
            </div>

            <!-- Package Title -->
            <div style="padding:14px 20px 10px;border-bottom:1px solid #f0f0f0;">
                <div style="font-size:17px;font-weight:700;color:#1a1a2e;"><?= esc($card['name']) ?></div>
                <div style="font-size:12px;color:#6b7280;margin-top:3px;">
                    <?= esc($card['code']) ?>
                    &nbsp;·&nbsp;
                    <?= esc($card['duration_days']) ?> Days
                    <?php if ($pkgDepFmt !== '—' || $pkgArrFmt !== '—'): ?>
                        &nbsp;·&nbsp; <?= $pkgDepFmt ?> → <?= $pkgArrFmt ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pricing -->
            <?php if (!empty($sharePrices)): ?>
                <div style="padding:12px 20px;background:#f8fffe;border-bottom:1px solid #f0f0f0;">
                    <div style="font-size:11px;font-weight:600;color:#4b5563;text-transform:uppercase;letter-spacing:.6px;margin-bottom:8px;">Package Pricing</div>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;">
                        <?php foreach ($sharePrices as $type => $amount): ?>
                            <div style="background:#fff;border:1px solid #d1fae5;border-radius:8px;padding:6px 12px;min-width:90px;">
                                <div style="font-size:10px;color:#6b7280;text-transform:capitalize;"><?= esc($type) ?></div>
                                <div style="font-size:14px;font-weight:700;color:#065f46;">PKR <?= number_format((float) $amount) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Flight -->
            <?php if ((int) ($card['include_ticket'] ?? 1) === 0): ?>
                <div style="padding:10px 20px;border-bottom:1px solid #f0f0f0;color:#9ca3af;font-size:12px;font-style:italic;">
                    ✈ Flight — Self-arranged
                </div>
            <?php elseif ($shareOutbound || $shareReturn): ?>
                <div style="padding:12px 20px;border-bottom:1px solid #f0f0f0;">
                    <div style="font-size:11px;font-weight:600;color:#4b5563;text-transform:uppercase;letter-spacing:.6px;margin-bottom:8px;">Flights</div>
                    <?php if ($shareOutbound):
                        $outDepRaw = (string) ($shareOutbound['departure_at'] ?? '');
                        $outArrRaw = (string) ($shareOutbound['arrival_at'] ?? '');
                        $outDepFmt = $outDepRaw !== '' ? date('d M Y H:i', strtotime($outDepRaw)) : '';
                        $outArrFmt = $outArrRaw !== '' ? date('d M Y H:i', strtotime($outArrRaw)) : '';
                    ?>
                        <div style="margin-bottom:8px;font-size:12px;">
                            <span style="display:inline-block;background:#eff6ff;border-radius:4px;padding:2px 7px;font-size:10px;font-weight:600;color:#1d4ed8;margin-right:6px;">OUT</span>
                            <strong><?= esc($shareOutbound['airline'] ?? '') ?> <?= esc($shareOutbound['flight_no'] ?? '') ?></strong>
                            <?php if (!empty($shareOutbound['departure_airport']) || !empty($shareOutbound['arrival_airport'])): ?>
                                &nbsp;· <?= esc($shareOutbound['departure_airport'] ?? '') ?> → <?= esc($shareOutbound['arrival_airport'] ?? '') ?>
                            <?php endif; ?>
                            <?php if ($outDepFmt !== '' || $outArrFmt !== ''): ?>
                                <div style="color:#6b7280;font-size:10px;margin-top:2px;padding-left:2px;">
                                    <?php if ($outDepFmt !== ''): ?>Dep: <?= esc($outDepFmt) ?><?php endif; ?>
                                    <?php if ($outArrFmt !== ''): ?>&nbsp;&nbsp;Arr: <?= esc($outArrFmt) ?><?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($shareReturn && $shareReturn['flight_no'] !== ($shareOutbound['flight_no'] ?? '')):
                        $retDepRaw = (string) ($shareReturn['departure_at'] ?? '');
                        $retArrRaw = (string) ($shareReturn['arrival_at'] ?? '');
                        $retDepFmt = $retDepRaw !== '' ? date('d M Y H:i', strtotime($retDepRaw)) : '';
                        $retArrFmt = $retArrRaw !== '' ? date('d M Y H:i', strtotime($retArrRaw)) : '';
                    ?>
                        <div style="font-size:12px;">
                            <span style="display:inline-block;background:#f0fdf4;border-radius:4px;padding:2px 7px;font-size:10px;font-weight:600;color:#15803d;margin-right:6px;">RET</span>
                            <strong><?= esc($shareReturn['airline'] ?? '') ?> <?= esc($shareReturn['flight_no'] ?? '') ?></strong>
                            <?php if (!empty($shareReturn['departure_airport']) || !empty($shareReturn['arrival_airport'])): ?>
                                &nbsp;· <?= esc($shareReturn['departure_airport'] ?? '') ?> → <?= esc($shareReturn['arrival_airport'] ?? '') ?>
                            <?php endif; ?>
                            <?php if ($retDepFmt !== '' || $retArrFmt !== ''): ?>
                                <div style="color:#6b7280;font-size:10px;margin-top:2px;padding-left:2px;">
                                    <?php if ($retDepFmt !== ''): ?>Dep: <?= esc($retDepFmt) ?><?php endif; ?>
                                    <?php if ($retArrFmt !== ''): ?>&nbsp;&nbsp;Arr: <?= esc($retArrFmt) ?><?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Hotels -->
            <?php if ((int) ($card['include_hotel'] ?? 1) === 0): ?>
                <div style="padding:10px 20px;border-bottom:1px solid #f0f0f0;color:#9ca3af;font-size:12px;font-style:italic;">
                    🏨 Hotel — Self-arranged
                </div>
            <?php elseif (!empty($shareHotels)): ?>
                <div style="padding:12px 20px;border-bottom:1px solid #f0f0f0;">
                    <div style="font-size:11px;font-weight:600;color:#4b5563;text-transform:uppercase;letter-spacing:.6px;margin-bottom:8px;">Hotels</div>
                    <?php foreach ($shareHotels as $stay):
                        $shareCityText = strtolower(trim((string) ($stay['city'] ?? '')));
                        $shareIsMadina = preg_match('/madina|medina/i', $shareCityText) === 1;
                        $shareCityImg  = $shareIsMadina
                            ? base_url('assets/uploads/madina-logo.png')
                            : base_url('assets/uploads/makkah-logo.png');
                        $shareCityAlt  = $shareIsMadina ? 'Madina' : 'Makkah';
                    ?>
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                            <span style="display:inline-flex;width:32px;height:32px;flex-shrink:0;align-items:center;justify-content:center;border-radius:6px;border:1px solid #e2e8f0;background:#fff;padding:2px;">
                                <img src="<?= esc($shareCityImg) ?>" alt="<?= esc($shareCityAlt) ?>" style="max-width:26px;max-height:26px;object-fit:contain;" crossorigin="anonymous">
                            </span>
                            <div>
                                <div style="font-size:12px;font-weight:600;color:#1a1a2e;"><?= esc($stay['name'] ?? '') ?></div>
                                <div style="font-size:10px;color:#6b7280;">
                                    <?php if (!empty($stay['city'])): ?><?= esc($stay['city']) ?><?php endif; ?>
                                    <?php if (($stay['nights'] ?? 0) > 0): ?> · <?= (int) $stay['nights'] ?> nights<?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Transport -->
            <?php if ((int) ($card['include_transport'] ?? 1) === 0): ?>
                <div style="padding:10px 20px;border-bottom:1px solid #f0f0f0;color:#9ca3af;font-size:12px;font-style:italic;">
                    🚌 Transport — Self-arranged
                </div>
            <?php elseif (!empty($shareTransports)): ?>
                <div style="padding:10px 20px;border-bottom:1px solid #f0f0f0;">
                    <div style="font-size:12px;color:#374151;">
                        🚌 <?= esc(implode(' | ', $shareTransports)) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Footer -->
            <div style="background:#f9fafb;padding:10px 20px;display:flex;align-items:center;justify-content:space-between;">
                <div style="font-size:11px;color:#6b7280;">
                    <?php if ($companyPhone): ?><span>📞 <?= $companyPhone ?></span>&nbsp;&nbsp;<?php endif; ?>
                        <?php if ($companyEmail): ?><span>✉ <?= $companyEmail ?></span><?php endif; ?>
                </div>
                <div style="font-size:10px;color:#9ca3af;">Seats: <?= (int) $card['available_seats'] ?></div>
            </div>
            <!-- Powered by -->
            <div style="background:#1e6b3e;padding:5px 20px;text-align:center;">
                <span style="font-size:9px;color:#a8f0c6;letter-spacing:.4px;">Powered by </span><a style="font-size:9px;color:#ffffff;font-weight:600;text-decoration:none;letter-spacing:.4px;" href="https://khybersoft.com">khybersoft.com</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- html2canvas CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    function sharePackage(packageId) {
        var card = document.getElementById('share-card-' + packageId);
        if (!card) {
            alert('Share card not found.');
            return;
        }

        // Make temporarily visible for capture
        var container = document.getElementById('share-cards-container');
        var origStyle = container.style.cssText;
        container.style.cssText = 'position:fixed;left:-9999px;top:0;z-index:9999;';

        html2canvas(card, {
            scale: 2,
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            width: 480,
            windowWidth: 480,
        }).then(function(canvas) {
            container.style.cssText = origStyle;
            canvas.toBlob(function(blob) {
                // 1) Download the PNG
                var url = URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = 'package-' + packageId + '.png';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                setTimeout(function() {
                    URL.revokeObjectURL(url);
                }, 2000);
            }, 'image/png');
        }).catch(function(err) {
            container.style.cssText = origStyle;
            console.error('html2canvas error:', err);
            alert('Could not generate image. Please try again.');
        });
    }
</script>

<?php $this->endSection() ?>