<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">

    <!-- Top Alert Messages -->
    <div class="space-y-4">
        <?php if (!empty($success)): ?>
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700 flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i> <?= esc($success) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i> <?= esc($error) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $err): ?>
                        <li><?= esc($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <section class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 class="text-base font-semibold text-slate-800">Packages</h1>
                    <p class="mt-1 text-xs text-slate-500">Browse package performance, facilities, prices, and availability with a richer card view.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="<?= site_url('/packages/inactive') ?>" class="btn btn-md btn-outline inline-flex items-center justify-center gap-2">
                        <i class="fa-solid fa-box-archive"></i>
                        <span>Inactive Packages<?= isset($inactiveCount) ? ' (' . (int) $inactiveCount . ')' : '' ?></span>
                    </a>
                    <a href="<?= site_url('/packages/add') ?>" class="btn btn-md btn-primary inline-flex items-center justify-center gap-2">
                        <i class="fa-solid fa-plus"></i>
                        <span>Create New Package</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- Content Grid -->
        <?php if (empty($cards)): ?>
            <div class="rounded-xl border border-slate-200 bg-white p-8 text-center">
                <div class="mx-auto mb-4 inline-flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <i class="fa-solid fa-box-open text-2xl"></i>
                </div>
                <h3 class="text-base font-semibold text-slate-900">No packages found</h3>
                <p class="mt-1 text-sm text-slate-500">Get started by creating a new package.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 2xl:grid-cols-3">
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
                    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-slate-200/70">

                        <!-- Card Header / Image Area -->
                        <div class="relative border-b border-slate-200 bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-900 px-3 py-3 text-white">
                            <div class="pointer-events-none absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, #ffffff 1px, transparent 1px); background-size: 14px 14px;"></div>
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 relative z-10">
                                    <h3 class="line-clamp-2 text-lg font-bold leading-tight text-white"><?= esc($card['name']) ?></h3>
                                    <div class="mt-1 text-xs text-emerald-100/90">
                                        <?= esc($card['code'] ?: 'Route TBA') ?>
                                    </div>
                                    <?php
                                    $pkgDep = (string) ($card['package_departure_date'] ?? '');
                                    $pkgArr = (string) ($card['package_arrival_date'] ?? '');
                                    $pkgDepTs = $pkgDep !== '' ? strtotime($pkgDep) : false;
                                    $pkgArrTs = $pkgArr !== '' ? strtotime($pkgArr) : false;
                                    ?>
                                    <?php if ($pkgDepTs || $pkgArrTs): ?>
                                        <div class="mt-1 flex items-center gap-1 text-[10px] text-slate-100/80">
                                            <i class="fa-solid fa-calendar-days text-slate-100/70"></i>
                                            <span><?= esc($pkgDepTs ? date('d M Y', $pkgDepTs) : '—') ?></span>
                                            <span class="text-slate-200/70">→</span>
                                            <span><?= esc($pkgArrTs ? date('d M Y', $pkgArrTs) : '—') ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="relative z-10 shrink-0 flex items-center gap-1">
                                    <div class="rounded-full border border-white/20 bg-white/10 px-2.5 py-1 text-[11px] font-semibold text-white backdrop-blur">
                                        <?= esc($card['duration_days']) ?> Days
                                    </div>
                                    <div class="rounded-full border border-emerald-200/40 bg-emerald-300/15 px-2.5 py-1 text-[11px] font-semibold text-emerald-100 backdrop-blur">
                                        <?= esc($card['available_seats']) ?> Seats Left
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="flex-1 space-y-2.5 p-3">
                            <div class="space-y-1 text-xs">
                                <?php if (!(int) ($card['include_ticket'] ?? 1)): ?>
                                    <div class="col-span-full rounded-lg border border-slate-200 bg-slate-50 px-2 py-2 text-center text-[11px] italic text-slate-400"><i class="fa-solid fa-plane mr-1"></i> Flight — Self-arranged</div>
                                <?php else: ?>
                                    <div class="grid grid-cols-[52px_1fr_auto] items-center gap-1 rounded-lg border border-sky-200/70 bg-sky-50 px-2 py-2">
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

                                    <div class="grid grid-cols-[52px_1fr_auto] items-center gap-1 rounded-lg border border-emerald-200/70 bg-emerald-50 px-2 py-2">
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
                            <div class="mb-2 mt-2.5 space-y-1 text-xs">
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
                                        <div class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50/80 px-2 py-1.5 text-sm text-slate-600">
                                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md border border-slate-200 bg-white p-0.5 shadow-sm">
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
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 text-[11px] italic text-slate-400"><i class="fa-solid fa-bed mr-1"></i> Hotel — Self-arranged</div>
                                <?php else: ?>
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 text-[11px] text-slate-500">Hotel stay distribution not available.</div>
                                <?php endif; ?>
                            </div>

                            <!-- Transport Info -->
                            <?php $transportTypes = $card['transport_types'] ?? []; ?>
                            <?php $transportNames = $card['transport_names'] ?? []; ?>
                            <?php $transportTypeText = !empty($transportTypes) ? implode(' / ', $transportTypes) : 'TBA'; ?>
                            <?php $transportNameText = !empty($transportNames) ? implode(' | ', $transportNames) : 'Transport details pending'; ?>
                            <?php if (!(int) ($card['include_transport'] ?? 1)): ?>
                                <div class="mb-2 rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 text-[11px] italic text-slate-400">
                                    <div class="flex items-center gap-1">
                                        <i class="fa-solid fa-bus"></i>
                                        <span>Transport — Self-arranged</span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="mb-2 rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 text-[11px] text-slate-700">
                                    <div class="flex items-center gap-1 min-w-0">
                                        <i class="fa-solid fa-bus text-slate-600"></i>
                                        <span class="font-semibold shrink-0"><?= esc($transportTypeText) ?></span>
                                        <span class="text-slate-400">|</span>
                                        <span class="text-slate-500 truncate"><?= esc($transportNameText) ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Pricing Grid -->
                            <?php if (($card['package_mode'] ?? 'tiered') === 'flat'): ?>
                                <div class="rounded-xl border border-slate-200 bg-gradient-to-br from-slate-50 to-emerald-50/60 p-2 text-xs">
                                    <div class="rounded-lg border border-emerald-200 bg-white px-3 py-2">
                                        <span class="block text-slate-500">Package Price</span>
                                        <span class="font-bold text-slate-800">PKR <?= number_format((float) ($card['flat_price'] ?? 0)) ?></span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="grid grid-cols-2 gap-1.5 rounded-xl border border-slate-200 bg-gradient-to-br from-slate-50 to-emerald-50/60 p-2 text-xs">
                                    <?php $sharingPrice = $card['price_map']['sharing'] ?? null; ?>
                                    <?php if ($sharingPrice !== null): ?>
                                        <div class="rounded-lg border border-emerald-200 bg-white px-2 py-1.5">
                                            <span class="block text-slate-500">Sharing</span>
                                            <span class="font-bold text-slate-800">PKR <?= number_format((float) $sharingPrice) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php $quadPrice = $card['price_map']['quad'] ?? null; ?>
                                    <?php if ($quadPrice !== null): ?>
                                        <div class="rounded-lg border border-emerald-200 bg-white px-2 py-1.5">
                                            <span class="block text-slate-500">Quad</span>
                                            <span class="font-bold text-slate-800">PKR <?= number_format((float) $quadPrice) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php $triplePrice = $card['price_map']['triple'] ?? null; ?>
                                    <?php if ($triplePrice !== null): ?>
                                        <div class="rounded-lg border border-emerald-200 bg-white px-2 py-1.5">
                                            <span class="block text-slate-500">Triple</span>
                                            <span class="font-bold text-slate-800">PKR <?= number_format((float) $triplePrice) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php $doublePrice = $card['price_map']['double'] ?? null; ?>
                                    <?php if ($doublePrice !== null): ?>
                                        <div class="rounded-lg border border-emerald-200 bg-white px-2 py-1.5">
                                            <span class="block text-slate-500">Double</span>
                                            <span class="font-bold text-slate-800">PKR <?= number_format((float) $doublePrice) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Card Footer / Actions -->
                        <div class="flex items-center justify-between gap-2 border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                            <div class="flex items-center gap-2">
                                <form method="post" action="<?= site_url('/packages/status') ?>" class="shrink-0" onsubmit="return confirm('Move this package to inactive list?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="package_id" value="<?= esc($card['id']) ?>">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="hidden" name="redirect_to" value="packages">
                                    <button type="submit" class="flex h-10 w-10 items-center justify-center rounded-xl border border-amber-200 bg-white text-amber-600 transition hover:bg-amber-50" title="Move To Inactive">
                                        <i class="fa-solid fa-box-archive"></i>
                                    </button>
                                </form>

                                <form method="post" action="<?= site_url('/packages/delete') ?>" class="shrink-0" onsubmit="return confirm('Are you sure you want to delete this package?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="package_id" value="<?= esc($card['id']) ?>">
                                    <button type="submit" class="flex h-10 w-10 items-center justify-center rounded-xl border border-rose-200 bg-white text-rose-600 transition hover:bg-rose-50" title="Delete Package">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>

                            <button type="button"
                                onclick="sharePackage(<?= (int) $card['id'] ?>)"
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-sky-200 bg-white text-sky-600 transition hover:bg-sky-50"
                                title="Download package image">
                                <i class="fa-solid fa-download"></i>
                            </button>

                            <a href="<?= site_url('/packages/' . (int) $card['id'] . '/edit') ?>" class="btn btn-md btn-primary inline-flex flex-1 items-center justify-center gap-2 text-center">
                                <i class="fa-solid fa-gear"></i>
                                <span>Manage Package</span>
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
$companyAddress = esc((string) ($company['address'] ?? ''));
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
                <?php if ($companyAddress): ?>
                    <div style="font-size:11px;line-height:1.45;color:#dcfce7;margin-top:8px;"><?= $companyAddress ?></div>
                <?php endif; ?>
                <?php if ($companyPhone): ?>
                    <div style="font-size:12px;font-weight:700;color:#ffffff;margin-top:4px;">Phone: <?= $companyPhone ?></div>
                <?php endif; ?>
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
                    <?= esc($companyName) ?>
                </div>
                <div style="font-size:10px;color:#9ca3af;">Seats Left: <?= (int) $card['available_seats'] ?></div>
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