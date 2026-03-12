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
                                <?php else: ?>
                                    <div class="text-[11px] text-slate-500">Hotel stay distribution not available.</div>
                                <?php endif; ?>
                            </div>

                            <!-- Transport Info -->
                            <?php $transportTypes = $card['transport_types'] ?? []; ?>
                            <?php $transportNames = $card['transport_names'] ?? []; ?>
                            <?php $transportTypeText = !empty($transportTypes) ? implode(' / ', $transportTypes) : 'TBA'; ?>
                            <?php $transportNameText = !empty($transportNames) ? implode(' | ', $transportNames) : 'Transport details pending'; ?>
                            <div class="mb-2 rounded-md border border-slate-200 bg-slate-50 px-2 py-1 text-[11px] text-slate-700">
                                <div class="flex items-center gap-1 min-w-0">
                                    <i class="fa-solid fa-bus text-slate-600"></i>
                                    <span class="font-semibold shrink-0"><?= esc($transportTypeText) ?></span>
                                    <span class="text-slate-400">|</span>
                                    <span class="text-slate-500 truncate"><?= esc($transportNameText) ?></span>
                                </div>
                            </div>

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
<?php $this->endSection() ?>