<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-3">
    <?php
    $filters = is_array($filters ?? null) ? $filters : [];
    $filterQ = (string) ($filters['q'] ?? '');
    $filterDeparture = strtoupper((string) ($filters['departure'] ?? ''));
    $filterDays = (int) ($filters['days'] ?? 0);
    $filterTab = (string) ($filters['tab'] ?? 'all');
    if (! in_array($filterTab, ['all', 'ticket', 'hotel', 'transport'], true)) {
        $filterTab = 'all';
    }
    $hasActiveFilters = $filterQ !== '' || $filterDeparture !== '' || $filterDays > 0 || $filterTab !== 'all';
    ?>

    <!-- Alerts -->
    <div class="space-y-2">
        <?php if (!empty($success)): ?>
            <div class="flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
                <i class="fa-solid fa-circle-check"></i> <?= esc($success) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700">
                <i class="fa-solid fa-circle-exclamation"></i> <?= esc($error) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
                <ul class="list-inside list-disc">
                    <?php foreach ($errors as $err): ?>
                        <li><?= esc($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <!-- Toolbar -->
    <section class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 md:px-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="min-w-0">
                <h1 class="flex items-center gap-2 text-base font-semibold text-slate-800">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50 text-[11px] text-emerald-600"><i class="fa-solid fa-suitcase-rolling"></i></span>
                    <span>Packages</span>
                    <span id="package-count" class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-600"><?= count($cards ?? []) ?></span>
                </h1>
                <p class="mt-0.5 text-[11px] text-slate-500">Flights, hotels and per-person costing for every package at a glance.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <?php if (!empty($cards)): ?>
                    <?php
                    $printQuery = array_filter([
                        'q' => $filterQ,
                        'departure' => strtolower($filterDeparture),
                        'days' => $filterDays > 0 ? (string) $filterDays : '',
                        'tab' => $filterTab !== 'all' ? $filterTab : '',
                    ], static function ($value): bool {
                        return trim((string) $value) !== '';
                    });
                    $printUrl = site_url('/admin/packages/print') . ($printQuery !== [] ? ('?' . http_build_query($printQuery)) : '');
                    ?>
                    <a id="package-print-link" href="<?= esc($printUrl) ?>" target="_blank" class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-50" title="Open the printable / PDF version of this list">
                        <i class="fa-solid fa-file-pdf text-[10px] text-rose-600"></i>
                        <span>PDF<span class="hidden sm:inline"> / Print</span></span>
                    </a>
                <?php endif; ?>
                <?php if (!empty($canManagePackage)): ?>
                    <a href="<?= site_url('/admin/packages/inactive') ?>" class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">
                        <i class="fa-solid fa-box-archive text-[10px]"></i>
                        <span>Inactive</span><?= isset($inactiveCount) ? ' <span class="rounded bg-slate-100 px-1 font-bold">' . (int) $inactiveCount . '</span>' : '' ?>
                    </a>
                    <a href="<?= site_url('/admin/packages/add') ?>" class="inline-flex h-8 items-center gap-1.5 rounded-lg bg-emerald-600 px-2.5 text-[11px] font-bold text-white shadow-sm hover:bg-emerald-700">
                        <i class="fa-solid fa-plus text-[10px]"></i>
                        <span>New package</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php
        $departureAirportOptions = [];
        $durationOptions = [];
        foreach (($allCards ?? $cards ?? []) as $filterCard) {
            $airport = trim((string) ($filterCard['departure_airport'] ?? ''));
            if ($airport !== '') {
                $departureAirportOptions[strtoupper($airport)] = strtoupper($airport);
            }

            $days = (int) ($filterCard['calculated_duration_days'] ?? $filterCard['duration_days'] ?? 0);
            if ($days > 0) {
                $durationOptions[$days] = $days;
            }
        }
        ksort($departureAirportOptions);
        ksort($durationOptions);
        $packageTabs = [
            'all'       => ['label' => 'All', 'icon' => 'fa-layer-group', 'title' => 'All active packages'],
            'ticket'    => ['label' => 'Ticket', 'icon' => 'fa-plane', 'title' => 'Packages with only a ticket (no hotel, no transport)'],
            'hotel'     => ['label' => 'Hotel', 'icon' => 'fa-hotel', 'title' => 'Packages with only a hotel (no ticket, no transport)'],
            'transport' => ['label' => 'Transport', 'icon' => 'fa-bus', 'title' => 'Packages with only transport (no ticket, no hotel)'],
        ];
        $tabBaseQuery = array_filter([
            'q' => $filterQ,
            'departure' => strtolower($filterDeparture),
            'days' => $filterDays > 0 ? (string) $filterDays : '',
        ], static function ($value): bool {
            return trim((string) $value) !== '';
        });
        ?>
            <form id="package-filter-form" method="get" action="<?= site_url('/admin/packages') ?>" class="mt-2 space-y-2 rounded-lg border border-slate-200 bg-slate-50/70 p-2.5">
                <input type="hidden" id="package-tab-input" name="tab" value="<?= esc($filterTab) ?>">
                <div id="package-tabs" class="flex flex-wrap items-center gap-1.5">
                    <?php foreach ($packageTabs as $tabKey => $tab): ?>
                        <?php
                        $isActiveTab = $tabKey === $filterTab;
                        $tabQuery = $tabBaseQuery;
                        if ($tabKey !== 'all') {
                            $tabQuery['tab'] = $tabKey;
                        }
                        $tabUrl = site_url('/admin/packages') . ($tabQuery !== [] ? ('?' . http_build_query($tabQuery)) : '');
                        ?>
                        <a href="<?= esc($tabUrl) ?>" aria-current="<?= $isActiveTab ? 'page' : 'false' ?>" title="<?= esc($tab['title'], 'attr') ?>" class="inline-flex h-7 items-center gap-1 rounded-full border px-2.5 text-[11px] font-semibold transition <?= $isActiveTab ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-emerald-300 hover:text-emerald-700' ?>">
                            <i class="fa-solid <?= esc($tab['icon'], 'attr') ?> text-[9px]"></i>
                            <span><?= esc($tab['label']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div class="grid gap-2 md:grid-cols-12">
                    <div class="relative md:col-span-5">
                        <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-[11px] text-slate-400"></i>
                        <input id="package-search" name="q" value="<?= esc($filterQ) ?>" type="search" autocomplete="off" placeholder="Search code, name, hotel, flight, city…" class="w-full rounded-lg border border-slate-300 bg-white py-1.5 pl-7 pr-2 text-sm text-slate-800 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div class="md:col-span-3">
                        <input id="package-departure-filter" name="departure" value="<?= esc($filterDeparture) ?>" list="departure-airport-options" placeholder="Departure city / airport (e.g. LHE)" class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        <datalist id="departure-airport-options">
                            <?php foreach ($departureAirportOptions as $airportOption): ?>
                                <option value="<?= esc($airportOption) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="md:col-span-2">
                        <select id="package-days-filter" name="days" class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <option value="">All days</option>
                            <?php foreach ($durationOptions as $dayOption): ?>
                                <option value="<?= esc((string) $dayOption) ?>" <?= $filterDays === (int) $dayOption ? 'selected' : '' ?>><?= esc((string) $dayOption) ?> days</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="md:col-span-1">
                        <button type="submit" id="package-apply-filters" class="inline-flex h-full w-full items-center justify-center rounded-lg bg-emerald-600 px-2 text-[11px] font-semibold text-white hover:bg-emerald-700">
                            Search
                        </button>
                    </div>
                    <div class="md:col-span-1">
                        <a href="<?= site_url('/admin/packages') ?>" id="package-clear-filters" class="inline-flex h-full w-full items-center justify-center rounded-lg border border-slate-300 bg-white px-2 text-[11px] font-semibold text-slate-700 hover:bg-slate-100">
                            Clear
                        </a>
                    </div>
                </div>
            </form>
    </section>

    <?php if (empty($cards)): ?>
        <div class="rounded-xl border border-slate-200 bg-white p-8 text-center">
            <div class="mx-auto mb-3 inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                <i class="fa-solid fa-box-open text-xl"></i>
            </div>
            <h3 class="text-sm font-semibold text-slate-900">No packages found</h3>
            <p class="mt-1 text-xs text-slate-500"><?= $hasActiveFilters ? 'No package matches your current filters.' : 'Get started by creating a new package.' ?></p>
            <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                <?php if ($hasActiveFilters): ?>
                    <a href="<?= site_url('/admin/packages') ?>" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        <i class="fa-solid fa-rotate-left text-[10px]"></i>
                        <span>Clear filters</span>
                    </a>
                <?php endif; ?>
                <?php if (!empty($canManagePackage)): ?>
                    <a href="<?= site_url('/admin/packages/add') ?>" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                        <i class="fa-solid fa-plus text-[10px]"></i>
                        <span>Create package</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <?php
        $tierLabels = \App\Services\HotelRateService::TIER_LABELS;
        $canViewHotel = function_exists('auth_can') && auth_can('hotels.view');

        // Rates (prices / exchange rate / costing) are admin-only on this page.
        $isAdminUser = function_exists('auth_is_super_admin') && auth_is_super_admin();
        if (! $isAdminUser && function_exists('auth_roles')) {
            foreach (auth_roles() as $roleName) {
                if (in_array(strtolower(trim((string) $roleName)), ['admin', 'administrator'], true)) {
                    $isAdminUser = true;
                    break;
                }
            }
        }
        $showRates = $isAdminUser;
        $shortDateTime = static function ($value): string {
            $value = trim((string) $value);
            if ($value === '') {
                return '';
            }
            $ts = strtotime($value);

            return $ts === false ? '' : date('d M H:i', $ts);
        };
        $shortDate = static function ($value): string {
            $value = trim((string) $value);
            if ($value === '') {
                return '';
            }
            $ts = strtotime($value);

            return $ts === false ? '' : date('d M Y', $ts);
        };
        $copyDate = static function ($value): string {
            $value = trim((string) $value);
            if ($value === '') {
                return '';
            }
            $ts = strtotime($value);

            return $ts === false ? '' : date('d/m/Y', $ts);
        };
        $copyDateTime = static function ($value): string {
            $value = trim((string) $value);
            if ($value === '') {
                return '';
            }
            $ts = strtotime($value);

            return $ts === false ? '' : date('d/m/Y H:i', $ts);
        };
        $copyCompany = function_exists('main_company') ? (array) main_company() : [];
        $copyCompanyName = trim((string) ($copyCompany['name'] ?? ''));
        $copyCompanyPhone = trim((string) ($copyCompany['phone'] ?? ''));
        ?>

        <?php
        // Each package gets its own accent (bar + header tint + badge) so neighbouring packages are easy to tell apart.
        $packageAccents = [
            ['bar' => '#10b981', 'tint' => 'bg-emerald-50', 'badge' => 'from-emerald-600 to-emerald-500'],
            ['bar' => '#0ea5e9', 'tint' => 'bg-sky-50', 'badge' => 'from-sky-600 to-sky-500'],
            ['bar' => '#8b5cf6', 'tint' => 'bg-violet-50', 'badge' => 'from-violet-600 to-violet-500'],
            ['bar' => '#f59e0b', 'tint' => 'bg-amber-50', 'badge' => 'from-amber-600 to-amber-500'],
            ['bar' => '#f43f5e', 'tint' => 'bg-rose-50', 'badge' => 'from-rose-600 to-rose-500'],
        ];
        $accentIndex = 0;
        ?>
        <div id="package-rows" class="space-y-3">
            <?php foreach ($cards as $card): ?>
                <?php $accent = $packageAccents[$accentIndex % count($packageAccents)];
                $accentIndex++; ?>
                <?php
                $outbound = $card['outbound_flight'] ?? null;
                $return = $card['return_flight'] ?? null;
                $hotelStays = array_values(array_filter((array) ($card['hotel_stays'] ?? [])));
                $priceMap = (array) ($card['price_map'] ?? []);
                $isFlat = ($card['package_mode'] ?? 'tiered') === 'flat';

                // Only the components this package actually includes are displayed.
                $includeTicket = (int) ($card['include_ticket'] ?? 1) === 1;
                $includeHotel = (int) ($card['include_hotel'] ?? 1) === 1;
                $includeTransport = (int) ($card['include_transport'] ?? 1) === 1;
                $standaloneTransport = ! $includeHotel && $includeTransport;
                $visiblePanels = (int) $includeTicket + (int) $includeHotel + (int) $standaloneTransport + 1;
                $panelGridClass = $visiblePanels >= 3
                    ? 'lg:grid-cols-[minmax(0,1fr)_minmax(0,1.45fr)_minmax(0,1fr)]'
                    : ($visiblePanels === 2 ? 'lg:grid-cols-2' : 'lg:grid-cols-1');

                $minPrice = null;
                $minTierLabel = '';
                foreach ($priceMap as $tierKey => $tierValue) {
                    if ($tierValue === null || ! is_numeric($tierValue) || (float) $tierValue <= 0) {
                        continue;
                    }
                    if ($minPrice === null || (float) $tierValue < $minPrice) {
                        $minPrice = (float) $tierValue;
                        $minTierLabel = (string) ($tierLabels[$tierKey] ?? ucfirst((string) $tierKey));
                    }
                }
                if ($isFlat && $card['flat_price'] !== null) {
                    $minPrice = (float) $card['flat_price'];
                    $minTierLabel = 'package price';
                }

                $seatTotal = max(0, (int) ($card['seat_capacity'] ?? 0));
                $seatLeft = max(0, (int) ($card['available_seats'] ?? 0));
                $seatBooked = max(0, (int) ($card['booked_seats'] ?? 0));
                $seatPct = $seatTotal > 0 ? (int) round($seatLeft / $seatTotal * 100) : 0;
                $seatTone = $seatLeft <= 0 ? 'bg-rose-500' : ($seatPct <= 20 ? 'bg-amber-500' : 'bg-emerald-500');

                $included = [];
                if ((float) ($card['visa_cost'] ?? 0) > 0) {
                    $included[] = 'Visa';
                }
                if ((int) ($card['include_hotel'] ?? 1)) {
                    $included[] = 'Hotel';
                }
                if ((int) ($card['include_ticket'] ?? 1)) {
                    $included[] = 'Ticket';
                }
                if ((int) ($card['include_transport'] ?? 1)) {
                    $included[] = 'Transport';
                }
                if ((int) ($card['include_ziarat'] ?? 0)) {
                    $included[] = 'Ziarat';
                }

                $transportLabel = implode(' / ', array_filter(array_map('strval', (array) ($card['transport_types'] ?? []))));
                $transportNames = implode(', ', array_filter(array_map('strval', (array) ($card['transport_names'] ?? []))));

                $searchText = strtolower(implode(' ', array_filter([
                    (string) ($card['code'] ?? ''),
                    (string) ($card['name'] ?? ''),
                    (string) ($card['airline_name'] ?? ''),
                    (string) ($card['route_label'] ?? ''),
                    (string) ($outbound['flight_no'] ?? ''),
                    (string) ($return['flight_no'] ?? ''),
                    $transportLabel,
                    $transportNames,
                    implode(' ', array_map(static fn($stay) => trim((string) ($stay['name'] ?? '') . ' ' . (string) ($stay['city'] ?? '')), $hotelStays)),
                ])));

                // Plain-text summary used by the "Copy" button (WhatsApp / SMS friendly).
                $copyLines = [];
                $copyLines[] = '*' . (((string) ($card['name'] ?? '')) !== '' ? (string) $card['name'] : 'Umrah Package') . '*';
                if ((string) ($card['code'] ?? '') !== '') {
                    $copyLines[] = 'Code: ' . (string) $card['code'];
                }
                $copyLines[] = 'Duration: ' . (int) ($card['duration_days'] ?? 0) . ' days';
                $copyDepartureDate = $copyDate($card['package_departure_date'] ?? '');
                $copyArrivalDate = $copyDate($card['package_arrival_date'] ?? '');
                if ($copyDepartureDate !== '' || $copyArrivalDate !== '') {
                    $copyLines[] = 'Dates: ' . ($copyDepartureDate !== '' ? $copyDepartureDate : '—') . ' to ' . ($copyArrivalDate !== '' ? $copyArrivalDate : '—');
                }

                if ($includeTicket) {
                    $copyOutboundLabel = trim((string) (($outbound['airline'] ?? '') . ' ' . ($outbound['flight_no'] ?? '')));
                    if ($copyOutboundLabel !== '') {
                        $copyRoute = trim((string) (($outbound['departure_airport'] ?? '') . ' - ' . ($outbound['arrival_airport'] ?? '')), ' -');
                        $copyOutboundAt = $copyDateTime($outbound['departure_at'] ?? '');
                        $copyLines[] = 'Flight: ' . $copyOutboundLabel . ($copyRoute !== '' ? ' (' . $copyRoute . ')' : '') . ($copyOutboundAt !== '' ? ' ' . $copyOutboundAt : '');
                    }
                    $copyReturnLabel = trim((string) (($return['airline'] ?? '') . ' ' . ($return['flight_no'] ?? '')));
                    if ($copyReturnLabel !== '') {
                        $copyReturnAt = $copyDateTime($return['departure_at'] ?? '');
                        $copyLines[] = 'Return: ' . $copyReturnLabel . ($copyReturnAt !== '' ? ' ' . $copyReturnAt : '');
                    }
                }

                if ($includeHotel) {
                    $copyHotelParts = [];
                    foreach ($hotelStays as $stay) {
                        $copyHotelName = trim((string) ($stay['name'] ?? ''));
                        if ($copyHotelName === '') {
                            continue;
                        }
                        $copyHotelCity = trim((string) ($stay['city'] ?? ''));
                        $copyHotelNights = max(0, (int) ($stay['nights'] ?? 0));
                        $copyHotelParts[] = ($copyHotelCity !== '' ? $copyHotelCity . ': ' : '') . $copyHotelName . ($copyHotelNights > 0 ? ' (' . $copyHotelNights . ' nights)' : '');
                    }
                    if ($copyHotelParts !== []) {
                        $copyLines[] = 'Hotels: ' . implode(', ', $copyHotelParts);
                    }
                }

                if ($includeTransport && $transportNames !== '') {
                    $copyLines[] = 'Transport: ' . $transportNames;
                }

                if ($included !== []) {
                    $copyLines[] = 'Included: ' . implode(', ', $included);
                }

                if ($showRates) {
                    $copyRateLines = [];
                    if ($isFlat) {
                        $copyFlatPrice = (float) ($card['flat_price'] ?? 0);
                        if ($copyFlatPrice > 0) {
                            $copyRateLines[] = '- Package: PKR ' . number_format($copyFlatPrice, 0);
                        }
                    } else {
                        foreach ($priceMap as $tierKey => $tierValue) {
                            if ($tierValue === null || ! is_numeric($tierValue) || (float) $tierValue <= 0) {
                                continue;
                            }
                            $copyRateLines[] = '- ' . (string) ($tierLabels[$tierKey] ?? ucfirst((string) $tierKey)) . ': PKR ' . number_format((float) $tierValue, 0);
                        }
                    }
                    if ($copyRateLines !== []) {
                        $copyLines[] = 'Rates (per person):';
                        foreach ($copyRateLines as $copyRateLine) {
                            $copyLines[] = $copyRateLine;
                        }
                    }
                }

                $copyLines[] = 'Seats left: ' . $seatLeft . ' / ' . $seatTotal;
                $copyLines[] = '';
                $copyLines[] = ($copyCompanyName !== '' ? $copyCompanyName : 'HJMS') . ($copyCompanyPhone !== '' ? ' - ' . $copyCompanyPhone : '');
                $copyText = implode("\n", $copyLines);
                ?>
                <article data-package-row data-search="<?= esc($searchText) ?>"
                    data-departure-airport="<?= esc(strtolower((string) ($card['departure_airport'] ?? ''))) ?>"
                    data-duration-days="<?= esc((string) ((int) ($card['calculated_duration_days'] ?? $card['duration_days'] ?? 0))) ?>"
                    data-inc-ticket="<?= (int) ($card['include_ticket'] ?? 1) ?>"
                    data-inc-hotel="<?= (int) ($card['include_hotel'] ?? 1) ?>"
                    data-inc-transport="<?= (int) ($card['include_transport'] ?? 1) ?>"
                    style="border-left:4px solid <?= esc($accent['bar'], 'attr') ?>" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:border-emerald-200 hover:shadow-md">

                    <!-- Header line -->
                    <div class="flex flex-col gap-2 <?= $accent['tint'] ?> px-3 py-2.5 lg:flex-row lg:items-start lg:gap-3">
                        <div class="flex min-w-0 flex-1 items-start gap-2.5">
                            <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br <?= $accent['badge'] ?> text-[12px] font-bold uppercase text-white shadow-sm"><?= esc(strtoupper(substr((string) (($card['code'] ?? '') !== '' ? $card['code'] : ($card['name'] ?? 'PK')), 0, 2))) ?></span>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <h3 class="text-sm font-bold text-slate-900" title="<?= esc((string) ($card['name'] ?? '')) ?>"><?= esc((string) ($card['name'] ?? 'Untitled package')) ?></h3>
                                    <span class="rounded-md bg-slate-800 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white"><?= esc((string) (($card['code'] ?? '') !== '' ? $card['code'] : '—')) ?></span>
                                    <?php if ($seatLeft <= 0): ?>
                                        <span class="rounded-full bg-rose-100 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-rose-700">Sold out</span>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-slate-600">
                                    <span class="inline-flex items-center gap-1 font-semibold text-slate-700"><i class="fa-regular fa-calendar text-[10px] text-slate-400"></i><?= esc($shortDate($card['package_departure_date'] ?? '') ?: '—') ?> <span class="text-slate-400">→</span> <?= esc($shortDate($card['package_arrival_date'] ?? '') ?: '—') ?></span>
                                    <span class="inline-flex items-center gap-1"><i class="fa-regular fa-clock text-[10px] text-slate-400"></i><?= (int) ($card['duration_days'] ?? 0) ?> days</span>
                                    <?php if ($includeTransport && $transportLabel !== ''): ?>
                                        <span class="inline-flex items-center gap-1"><i class="fa-solid fa-bus text-[10px] text-slate-400"></i><?= esc($transportLabel) ?></span>
                                    <?php endif; ?>
                                    <?php if ($showRates && (float) ($card['exchange_rate'] ?? 1) > 1): ?>
                                        <span class="inline-flex items-center gap-1 text-slate-400" title="SAR to PKR rate used for this package"><i class="fa-solid fa-right-left text-[10px]"></i>1 SAR = <?= esc(rtrim(rtrim(number_format((float) $card['exchange_rate'], 2, '.', ''), '0'), '.')) ?> PKR</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($included !== []): ?>
                                    <div class="mt-1 flex flex-wrap items-center gap-1">
                                        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Included</span>
                                        <?php foreach ($included as $inc): ?>
                                            <span class="inline-flex items-center gap-0.5 rounded bg-emerald-50 px-1.5 py-0.5 text-[9px] font-bold text-emerald-700"><i class="fa-solid fa-check text-[7px]"></i><?= esc($inc) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Price + seats + actions -->
                        <div class="flex flex-wrap items-center justify-between gap-2 lg:justify-end lg:gap-3">
                            <div class="flex items-center gap-3">
                                <div class="text-right">
                                    <?php if ($showRates): ?>
                                        <div class="text-[9px] font-bold uppercase tracking-wider text-slate-400">From</div>
                                        <?php if ($minPrice !== null): ?>
                                            <div class="text-base font-bold leading-tight text-slate-900">PKR <?= esc(number_format($minPrice, 0)) ?></div>
                                            <div class="text-[9px] text-slate-400"><?= esc($minTierLabel) ?></div>
                                        <?php else: ?>
                                            <div class="text-xs font-semibold text-slate-400">Not priced</div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-400"><i class="fa-solid fa-lock mr-0.5 text-[9px]"></i>Rates hidden</div>
                                    <?php endif; ?>
                                </div>
                                <div class="w-[86px] shrink-0">
                                    <div class="flex items-baseline justify-between text-[9px] font-bold uppercase tracking-wider text-slate-400">
                                        <span>Seats</span>
                                        <span class="<?= $seatLeft <= 0 ? 'text-rose-600' : 'text-slate-700' ?>"><?= $seatLeft ?>/<?= $seatTotal ?></span>
                                    </div>
                                    <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-slate-200">
                                        <div class="h-full rounded-full <?= $seatTone ?>" style="width: <?= $seatPct ?>%"></div>
                                    </div>
                                    <div class="mt-0.5 text-[9px] text-slate-400"><?= $seatBooked ?> sold</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <button type="button" data-toggle-detail aria-expanded="false" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-[11px] text-slate-500 transition hover:border-emerald-300 hover:text-emerald-700" title="Show stay dates & more">
                                    <i data-detail-chevron class="fa-solid fa-chevron-down text-[10px] transition-transform"></i>
                                </button>

                                <?php if (function_exists('auth_can') && auth_can('bookings.create')): ?>
                                    <a href="<?= site_url('/admin/bookings/add?package_id=' . (int) $card['id']) ?>" class="inline-flex h-8 items-center gap-1.5 rounded-lg bg-emerald-600 px-3 text-[11px] font-bold text-white shadow-sm transition hover:bg-emerald-700" title="Create booking for this package">
                                        <i class="fa-solid fa-calendar-plus text-[10px]"></i><span>Book</span>
                                    </a>
                                <?php endif; ?>

                                <button type="button" data-copy-package="<?= (int) $card['id'] ?>" class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 text-[11px] font-semibold text-slate-600 transition hover:border-emerald-300 hover:text-emerald-700" title="Copy package details to paste in WhatsApp">
                                    <i class="fa-regular fa-copy text-[10px]"></i><span class="hidden sm:inline">Copy</span>
                                </button>

                                <button type="button" onclick="sharePackage(<?= (int) $card['id'] ?>)" class="hidden h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-[11px] text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 sm:inline-flex" title="Download package image">
                                    <i class="fa-solid fa-download"></i>
                                </button>

                                <?php if (!empty($canManagePackage)): ?>
                                    <div class="relative">
                                        <button type="button" data-menu-toggle class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-[11px] text-slate-500 transition hover:border-slate-300 hover:bg-slate-50" title="More actions">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                        <div data-menu class="absolute right-0 top-full z-30 mt-1 hidden w-48 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-xl">
                                            <a href="<?= site_url('/admin/bookings/add?package_id=' . (int) $card['id']) ?>" class="flex items-center gap-2 px-3 py-2 text-[11px] font-semibold text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-file-circle-plus w-3 text-sky-600"></i>Create booking</a>
                                            <form method="post" action="<?= site_url('/admin/packages/status') ?>" onsubmit="return confirm('Move this package to the inactive list?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="package_id" value="<?= esc($card['id']) ?>">
                                                <input type="hidden" name="is_active" value="0">
                                                <input type="hidden" name="redirect_to" value="packages">
                                                <button type="submit" class="flex w-full items-center gap-2 px-3 py-2 text-left text-[11px] font-semibold text-amber-700 hover:bg-amber-50"><i class="fa-solid fa-box-archive w-3"></i>Move to inactive</button>
                                            </form>
                                            <form method="post" action="<?= site_url('/admin/packages/delete') ?>" onsubmit="return confirm('Delete this package and all of its linked flights, hotels and transport?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="package_id" value="<?= esc($card['id']) ?>">
                                                <button type="submit" class="flex w-full items-center gap-2 px-3 py-2 text-left text-[11px] font-semibold text-rose-600 hover:bg-rose-50"><i class="fa-solid fa-trash-can w-3"></i>Delete package</button>
                                            </form>
                                        </div>
                                    </div>
                                    <a href="<?= site_url('/admin/packages/' . (int) $card['id'] . '/edit') ?>" class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 text-[11px] font-bold text-emerald-700 transition hover:bg-emerald-100">
                                        <i class="fa-solid fa-pen-to-square text-[10px]"></i><span>Edit</span>
                                    </a>
                                <?php else: ?>
                                    <span class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-[11px] font-semibold text-slate-400" title="Read only access"><i class="fa-solid fa-eye text-[10px]"></i>View only</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Included components + costs -->
                    <div class="grid gap-2 border-t border-slate-200/70 bg-slate-100/70 px-3 py-2.5 <?= $panelGridClass ?>">

                        <?php if ($includeTicket): ?>
                            <!-- Flights -->
                            <div class="min-w-0 rounded-lg border border-slate-200 bg-white p-2">
                                <div class="flex items-center gap-1 text-[9px] font-bold uppercase tracking-wider text-sky-700">
                                    <?php if (!empty($card['airline_logo'])): ?>
                                        <img src="<?= esc((string) $card['airline_logo']) ?>" alt="<?= esc((string) ($card['airline_name'] ?? '')) ?>" class="h-5 w-auto max-w-[52px] object-contain" onerror="this.style.display='none'">
                                    <?php else: ?>
                                        <i class="fa-solid fa-plane text-[9px]"></i>
                                    <?php endif; ?>
                                    Flights
                                </div>
                                <div class="mt-1 space-y-1">
                                    <div class="flex items-start gap-1.5">
                                        <span class="mt-0.5 shrink-0 rounded bg-sky-100 px-1 py-0.5 text-[8px] font-bold uppercase tracking-wide text-sky-700">Departure</span>
                                        <div class="min-w-0 text-[11px] leading-tight">
                                            <div class="truncate font-semibold text-slate-700">
                                                <?= esc(trim((string) (($outbound['airline'] ?? '') . ' ' . ($outbound['flight_no'] ?? ''))) ?: 'TBA') ?>
                                                <?php if (trim((string) (($outbound['departure_airport'] ?? '') . ($outbound['arrival_airport'] ?? ''))) !== ''): ?>
                                                    <span class="font-normal text-slate-400">·</span>
                                                    <span class="font-medium text-slate-500"><?= esc(trim((string) (($outbound['departure_airport'] ?? '') . ' → ' . ($outbound['arrival_airport'] ?? '')), ' →')) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-[10px] text-slate-400"><?= esc($shortDateTime($outbound['departure_at'] ?? $card['departure_datetime'] ?? '')) ?></div>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-1.5">
                                        <span class="mt-0.5 shrink-0 rounded bg-indigo-100 px-1 py-0.5 text-[8px] font-bold uppercase tracking-wide text-indigo-700">Arrival</span>
                                        <div class="min-w-0 text-[11px] leading-tight">
                                            <div class="truncate font-semibold text-slate-700">
                                                <?= esc(trim((string) (($return['airline'] ?? '') . ' ' . ($return['flight_no'] ?? ''))) ?: 'TBA') ?>
                                                <?php if (trim((string) (($return['departure_airport'] ?? '') . ($return['arrival_airport'] ?? ''))) !== ''): ?>
                                                    <span class="font-normal text-slate-400">·</span>
                                                    <span class="font-medium text-slate-500"><?= esc(trim((string) (($return['departure_airport'] ?? '') . ' → ' . ($return['arrival_airport'] ?? '')), ' →')) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-[10px] text-slate-400"><?= esc($shortDateTime($return['arrival_at'] ?? $card['return_arrival_datetime'] ?? '')) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($includeHotel): ?>
                            <!-- Hotels -->
                            <div class="min-w-0 rounded-lg border border-slate-200 bg-white p-2">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-1 text-[9px] font-bold uppercase tracking-wider text-emerald-700">
                                        <i class="fa-solid fa-hotel text-[9px]"></i>Hotels
                                    </div>
                                    <?php if ($includeTransport && $transportNames !== ''): ?>
                                        <span class="inline-flex min-w-0 items-center gap-1 text-[9px] text-slate-400"><i class="fa-solid fa-bus text-[8px]"></i><span class="truncate"><?= esc($transportNames) ?></span></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (empty($hotelStays)): ?>
                                    <div class="mt-1 text-[11px] italic text-slate-400">No hotel linked yet</div>
                                <?php else: ?>
                                    <ul class="mt-1 space-y-1">
                                        <?php foreach (array_slice($hotelStays, 0, 4) as $stay): ?>
                                            <?php
                                            $cityText = strtolower(trim((string) ($stay['city'] ?? '')));
                                            $isMadina = preg_match('/madina|medina/i', $cityText) === 1;
                                            $cityLogo = base_url($isMadina ? 'assets/uploads/madina-logo.png' : 'assets/uploads/makkah-logo.png');
                                            $stayNights = max(0, (int) ($stay['nights'] ?? 0));
                                            $distance = (int) ($stay['distance_m'] ?? 0);
                                            ?>
                                            <li class="flex items-baseline gap-1.5 text-[11px] leading-tight">
                                                <img src="<?= esc($cityLogo) ?>" alt="" class="h-3.5 w-3.5 shrink-0 translate-y-0.5 object-contain">
                                                <span class="min-w-0 flex-1">
                                                    <span class="font-semibold text-slate-700"><?= esc((string) ($stay['city'] ?? 'Hotel')) ?></span>
                                                    <span class="text-slate-400">·</span>
                                                    <?php if (!empty($stay['id']) && $canViewHotel): ?>
                                                        <a href="<?= site_url('/admin/hotels/' . (int) $stay['id']) ?>" class="text-slate-600 underline decoration-slate-300 decoration-dotted underline-offset-2 hover:text-emerald-700 hover:decoration-emerald-500" title="Open hotel profile"><?= esc((string) ($stay['name'] ?? 'Hotel pending')) ?></a>
                                                    <?php else: ?>
                                                        <span class="text-slate-600"><?= esc((string) ($stay['name'] ?? 'Hotel pending')) ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($distance > 0): ?>
                                                        <span class="text-[10px] text-slate-400">(<?= esc(number_format($distance)) ?> m)</span>
                                                    <?php endif; ?>
                                                </span>
                                                <span class="shrink-0 rounded bg-slate-100 px-1.5 py-0.5 text-[9px] font-bold text-slate-600"><?= $stayNights ?>n</span>
                                            </li>
                                        <?php endforeach; ?>
                                        <?php if (count($hotelStays) > 4): ?>
                                            <li class="text-[10px] font-semibold text-slate-400">+<?= count($hotelStays) - 4 ?> more stay(s)</li>
                                        <?php endif; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($standaloneTransport): ?>
                            <!-- Transport (no hotel panel to carry it) -->
                            <div class="min-w-0 rounded-lg border border-slate-200 bg-white p-2">
                                <div class="flex items-center gap-1 text-[9px] font-bold uppercase tracking-wider text-slate-500">
                                    <i class="fa-solid fa-bus text-[9px]"></i>Transport
                                </div>
                                <?php if ($transportNames === ''): ?>
                                    <div class="mt-1 text-[11px] italic text-slate-400">No transport linked yet</div>
                                <?php else: ?>
                                    <ul class="mt-1 space-y-1 text-[11px] leading-tight text-slate-600">
                                        <?php foreach ((array) ($card['transport_names'] ?? []) as $transportName): ?>
                                            <li class="truncate"><?= esc((string) $transportName) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Costs -->
                        <div class="min-w-0 rounded-lg border border-slate-200 bg-white p-2">
                            <div class="flex items-center gap-1 text-[9px] font-bold uppercase tracking-wider text-slate-500">
                                <i class="fa-solid fa-tags text-[9px]"></i>Cost / person (PKR)
                            </div>
                            <?php if (! $showRates): ?>
                                <div class="mt-1 flex items-center gap-1.5 rounded-md bg-slate-50 px-2 py-1 text-[10px] font-semibold text-slate-400">
                                    <i class="fa-solid fa-lock text-[9px]"></i>Visible to admin only
                                </div>
                            <?php elseif ($isFlat): ?>
                                <div class="mt-1 flex items-baseline justify-between gap-2 rounded-md bg-emerald-50 px-2 py-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wide text-emerald-700">Package</span>
                                    <span class="text-[12px] font-bold text-emerald-900"><?= esc(number_format((float) ($card['flat_price'] ?? 0), 0)) ?></span>
                                </div>
                            <?php elseif ($priceMap === []): ?>
                                <div class="mt-1 text-[11px] italic text-slate-400">No price set</div>
                            <?php else: ?>
                                <div class="mt-1 grid grid-cols-1 gap-x-3 xl:grid-cols-2">
                                    <?php foreach ($priceMap as $tierKey => $tierValue): ?>
                                        <?php if ($tierValue === null || (float) $tierValue <= 0) {
                                            continue;
                                        } ?>
                                        <?php $isLowest = $minPrice !== null && abs((float) $tierValue - $minPrice) < 0.01; ?>
                                        <div class="flex items-baseline justify-between gap-2 border-b border-dashed border-slate-100 py-[1px] text-[11px] last:border-0">
                                            <span class="text-[10px] font-semibold uppercase tracking-wide <?= $isLowest ? 'text-emerald-700' : 'text-slate-400' ?>"><?= esc((string) ($tierLabels[$tierKey] ?? ucfirst((string) $tierKey))) ?></span>
                                            <span class="font-bold <?= $isLowest ? 'text-emerald-800' : 'text-slate-800' ?>"><?= esc(number_format((float) $tierValue, 0)) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Expandable detail: exact dates + costing inputs -->
                    <div data-package-detail class="hidden border-t border-slate-100 bg-white px-3 py-2.5">
                        <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                            <?php if ($includeHotel): ?>
                                <div>
                                    <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Stay dates</p>
                                    <ul class="mt-1 space-y-1">
                                        <?php if ($hotelStays === []): ?>
                                            <li class="text-[10px] text-slate-400">No hotel stay recorded</li>
                                        <?php else: ?>
                                            <?php foreach ($hotelStays as $stay): ?>
                                                <li class="flex items-center justify-between gap-2 rounded-md border border-slate-200 px-2 py-1 text-[10px]">
                                                    <span class="min-w-0 truncate text-slate-700"><?= esc((string) ($stay['name'] ?? '')) ?> <span class="text-slate-400">· <?= esc((string) ($stay['city'] ?? '')) ?></span></span>
                                                    <span class="shrink-0 font-semibold text-slate-600"><?= esc($shortDate($stay['check_in_date'] ?? '')) ?> → <?= esc($shortDate($stay['check_out_date'] ?? '')) ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <div>
                                <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Costing (SAR × rate)</p>
                                <ul class="mt-1 space-y-1 text-[10px] text-slate-600">
                                    <?php if ($showRates): ?>
                                        <li class="flex justify-between gap-2"><span>Visa</span><span class="font-semibold"><?= esc(number_format((float) ($card['visa_cost'] ?? 0), 2)) ?> SAR</span></li>
                                        <li class="flex justify-between gap-2"><span>Ziarat</span><span class="font-semibold"><?= (int) ($card['include_ziarat'] ?? 0) ? esc(number_format((float) ($card['ziarat_cost'] ?? 0), 2)) . ' SAR' : 'Not included' ?></span></li>
                                        <li class="flex justify-between gap-2"><span>Company profit</span><span class="font-semibold"><?php if ((string) ($card['profit_mode'] ?? 'amount') === 'percent'): ?><?= esc(rtrim(rtrim(number_format((float) ($card['profit_value'] ?? 0), 2, '.', ''), '0'), '.')) ?>% of cost<?php else: ?><?= esc(number_format((float) ($card['profit_value'] ?? 0), 0)) ?> PKR<?php endif; ?></span></li>
                                        <li class="flex justify-between gap-2"><span>SAR → PKR rate</span><span class="font-semibold"><?= esc(rtrim(rtrim(number_format((float) ($card['exchange_rate'] ?? 1), 4, '.', ''), '0'), '.')) ?></span></li>
                                    <?php else: ?>
                                        <li class="rounded-md bg-slate-50 px-2 py-1 text-slate-400"><i class="fa-solid fa-lock mr-1 text-[9px]"></i>Costing is visible to admin only</li>
                                    <?php endif; ?>
                                    <li class="flex justify-between gap-2"><span>Seats sold / total</span><span class="font-semibold"><?= $seatBooked ?> / <?= $seatTotal ?></span></li>
                                </ul>
                            </div>
                            <?php if ($includeTransport): ?>
                                <div>
                                    <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Transport</p>
                                    <ul class="mt-1 space-y-1 text-[10px] text-slate-600">
                                        <?php if ($transportNames === ''): ?>
                                            <li class="text-slate-400">No transport linked</li>
                                        <?php else: ?>
                                            <?php foreach ((array) ($card['transport_names'] ?? []) as $transportName): ?>
                                                <li class="rounded-md border border-slate-200 px-2 py-1"><?= esc((string) $transportName) ?></li>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div id="package-copy-<?= (int) $card['id'] ?>" class="hidden" aria-hidden="true"><?= esc($copyText) ?></div>
                </article>
            <?php endforeach; ?>
        </div>

        <div id="package-empty" class="hidden rounded-xl border border-dashed border-slate-300 bg-white p-6 text-center text-xs text-slate-500">
            <i class="fa-solid fa-magnifying-glass mb-1 block text-lg text-slate-300"></i>
            No package matches your search or filter.
        </div>

        <script>
            (function() {
                'use strict';

                var rows = Array.prototype.slice.call(document.querySelectorAll('[data-package-row]'));

                rows.forEach(function(row) {
                    var toggle = row.querySelector('[data-toggle-detail]');
                    var panel = row.querySelector('[data-package-detail]');
                    var chevron = row.querySelector('[data-detail-chevron]');

                    if (!toggle || !panel) {
                        return;
                    }

                    toggle.addEventListener('click', function(event) {
                        event.stopPropagation();
                        var open = panel.classList.contains('hidden');
                        panel.classList.toggle('hidden', !open);
                        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                        toggle.title = open ? 'Hide stay dates' : 'Show stay dates';
                        if (chevron) {
                            chevron.classList.toggle('rotate-180', open);
                        }
                    });
                });

                var search = document.getElementById('package-search');
                var departureFilter = document.getElementById('package-departure-filter');
                var daysFilter = document.getElementById('package-days-filter');
                var filterForm = document.getElementById('package-filter-form');
                var tabInput = document.getElementById('package-tab-input');
                var tabButtons = Array.prototype.slice.call(document.querySelectorAll('[data-package-tab]'));
                var activeTab = tabInput ? (tabInput.value || 'all') : 'all';
                var tabActiveClasses = ['border-emerald-600', 'bg-emerald-600', 'text-white'];
                var tabIdleClasses = ['border-slate-200', 'bg-white', 'text-slate-600'];

                tabButtons.forEach(function(button) {
                    button.addEventListener('click', function() {
                        activeTab = button.dataset.packageTab || 'all';
                        if (tabInput) {
                            tabInput.value = activeTab;
                        }
                        tabButtons.forEach(function(otherButton) {
                            var isActive = (otherButton.dataset.packageTab || 'all') === activeTab;
                            otherButton.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                            tabActiveClasses.forEach(function(name) {
                                otherButton.classList.toggle(name, isActive);
                            });
                            tabIdleClasses.forEach(function(name) {
                                otherButton.classList.toggle(name, !isActive);
                            });
                        });

                        if (filterForm) {
                            filterForm.submit();
                        }
                    });
                });

                function copyToast(message) {
                    var toast = document.getElementById('package-copy-toast');
                    if (!toast) {
                        toast = document.createElement('div');
                        toast.id = 'package-copy-toast';
                        toast.className = 'pointer-events-none fixed bottom-5 left-1/2 z-[60] -translate-x-1/2 rounded-lg bg-slate-900 px-3 py-2 text-[11px] font-semibold text-white opacity-0 shadow-lg transition-opacity duration-200';
                        document.body.appendChild(toast);
                    }

                    toast.textContent = message;
                    toast.classList.remove('opacity-0');
                    window.clearTimeout(copyToast.timer);
                    copyToast.timer = window.setTimeout(function() {
                        toast.classList.add('opacity-0');
                    }, 2200);
                }

                function legacyCopy(text, onDone) {
                    var area = document.createElement('textarea');
                    area.value = text;
                    area.setAttribute('readonly', '');
                    area.style.position = 'fixed';
                    area.style.top = '0';
                    area.style.left = '-9999px';
                    document.body.appendChild(area);
                    area.select();

                    try {
                        document.execCommand('copy');
                        onDone();
                    } catch (err) {
                        window.alert('Could not copy automatically. Please select and copy manually.');
                    }

                    document.body.removeChild(area);
                }

                function copyPackage(packageId) {
                    var source = document.getElementById('package-copy-' + packageId);
                    if (!source) {
                        return;
                    }

                    var text = source.textContent.replace(/[ \t]+\n/g, '\n').replace(/\n{3,}/g, '\n\n').trim();

                    function done() {
                        copyToast('Package details copied — paste it in WhatsApp.');
                    }

                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(text).then(done).catch(function() {
                            legacyCopy(text, done);
                        });
                    } else {
                        legacyCopy(text, done);
                    }
                }

                Array.prototype.slice.call(document.querySelectorAll('[data-copy-package]')).forEach(function(button) {
                    button.addEventListener('click', function(event) {
                        event.stopPropagation();
                        copyPackage(button.dataset.copyPackage);
                    });
                });

                function closeMenus(except) {
                    Array.prototype.slice.call(document.querySelectorAll('[data-menu]')).forEach(function(menu) {
                        if (menu !== except) {
                            menu.classList.add('hidden');
                        }
                    });
                }

                Array.prototype.slice.call(document.querySelectorAll('[data-menu-toggle]')).forEach(function(button) {
                    button.addEventListener('click', function(event) {
                        event.stopPropagation();
                        var menu = button.parentElement.querySelector('[data-menu]');
                        if (!menu) {
                            return;
                        }
                        closeMenus(menu);
                        menu.classList.toggle('hidden');
                    });
                });

                document.addEventListener('click', function() {
                    closeMenus(null);
                });
            })();
        </script>
    <?php endif; ?>
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
        $shareIsFlat   = ($card['package_mode'] ?? 'tiered') === 'flat';
        $shareFlatPrice = (float) ($card['flat_price'] ?? 0);
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
            <?php
            $sharePricingRows = [];
            if ($shareIsFlat && $shareFlatPrice > 0) {
                $sharePriceLabel = 'Package Price';
                if (
                    (int) ($card['include_ticket'] ?? 1) === 1
                    && (int) ($card['include_transport'] ?? 1) === 0
                    && (int) ($card['include_hotel'] ?? 1) === 0
                ) {
                    $sharePriceLabel = 'Ticket Price';
                } elseif (
                    (int) ($card['include_transport'] ?? 1) === 1
                    && (int) ($card['include_ticket'] ?? 1) === 0
                    && (int) ($card['include_hotel'] ?? 1) === 0
                ) {
                    $sharePriceLabel = 'Transport Price';
                }
                $sharePricingRows[] = ['label' => $sharePriceLabel, 'amount' => $shareFlatPrice];
            } else {
                foreach ((array) $sharePrices as $type => $amount) {
                    if ($amount === null || (float) $amount <= 0) {
                        continue;
                    }
                    $sharePricingRows[] = [
                        'label' => ucwords(str_replace('_', ' ', (string) $type)),
                        'amount' => (float) $amount,
                    ];
                }
            }
            ?>
            <?php if ($sharePricingRows !== []): ?>
                <div style="padding:12px 20px;background:#f8fffe;border-bottom:1px solid #f0f0f0;">
                    <div style="font-size:11px;font-weight:600;color:#4b5563;text-transform:uppercase;letter-spacing:.6px;margin-bottom:8px;"><?= $shareIsFlat ? 'Package Price' : 'Package Pricing' ?></div>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;">
                        <?php foreach ($sharePricingRows as $pricingRow): ?>
                            <div style="background:#fff;border:1px solid #d1fae5;border-radius:8px;padding:6px 12px;min-width:90px;">
                                <div style="font-size:10px;color:#6b7280;text-transform:capitalize;"><?= esc((string) ($pricingRow['label'] ?? '')) ?></div>
                                <div style="font-size:14px;font-weight:700;color:#065f46;">PKR <?= number_format((float) ($pricingRow['amount'] ?? 0)) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Flight -->
            <?php if ((int) ($card['include_ticket'] ?? 1) === 1 && ($shareOutbound || $shareReturn)): ?>
                <div style="padding:12px 20px;border-bottom:1px solid #f0f0f0;">
                    <div style="font-size:11px;font-weight:600;color:#4b5563;text-transform:uppercase;letter-spacing:.6px;margin-bottom:8px;">Flights</div>
                    <div style="display:flex;gap:12px;">
                        <?php if ($shareOutbound):
                            $outDepRaw = (string) ($shareOutbound['departure_at'] ?? '');
                            $outArrRaw = (string) ($shareOutbound['arrival_at'] ?? '');
                            $outDepFmt = $outDepRaw !== '' ? date('d M Y H:i', strtotime($outDepRaw)) : '';
                            $outArrFmt = $outArrRaw !== '' ? date('d M Y H:i', strtotime($outArrRaw)) : '';
                        ?>
                            <div style="flex:1;font-size:10px;border:1px solid #dbeafe;border-radius:6px;padding:8px;background:#f0f9ff;">
                                <div style="font-weight:600;color:#1d4ed8;margin-bottom:3px;font-size:9px;text-transform:uppercase;letter-spacing:.5px;">Outbound</div>
                                <div style="color:#1f2937;font-weight:600;margin-bottom:2px;font-size:11px;"><?= esc($shareOutbound['airline'] ?? '') ?> <?= esc($shareOutbound['flight_no'] ?? '') ?></div>
                                <?php if (!empty($shareOutbound['departure_airport']) || !empty($shareOutbound['arrival_airport'])): ?>
                                    <div style="color:#4b5563;margin-bottom:2px;font-size:9px;">
                                        <?= esc($shareOutbound['departure_airport'] ?? '') ?> → <?= esc($shareOutbound['arrival_airport'] ?? '') ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($outDepFmt !== ''): ?>
                                    <div style="color:#6b7280;font-size:9px;line-height:1.3;">Dep: <?= esc($outDepFmt) ?></div>
                                <?php endif; ?>
                                <?php if ($outArrFmt !== ''): ?>
                                    <div style="color:#6b7280;font-size:9px;">Arr: <?= esc($outArrFmt) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($shareReturn && $shareReturn['flight_no'] !== ($shareOutbound['flight_no'] ?? '')):
                            $retDepRaw = (string) ($shareReturn['departure_at'] ?? '');
                            $retArrRaw = (string) ($shareReturn['arrival_at'] ?? '');
                            $retDepFmt = $retDepRaw !== '' ? date('d M Y H:i', strtotime($retDepRaw)) : '';
                            $retArrFmt = $retArrRaw !== '' ? date('d M Y H:i', strtotime($retArrRaw)) : '';
                        ?>
                            <div style="flex:1;font-size:10px;border:1px solid #dcfce7;border-radius:6px;padding:8px;background:#f0fdf4;">
                                <div style="font-weight:600;color:#15803d;margin-bottom:3px;font-size:9px;text-transform:uppercase;letter-spacing:.5px;">Return</div>
                                <div style="color:#1f2937;font-weight:600;margin-bottom:2px;font-size:11px;"><?= esc($shareReturn['airline'] ?? '') ?> <?= esc($shareReturn['flight_no'] ?? '') ?></div>
                                <?php if (!empty($shareReturn['departure_airport']) || !empty($shareReturn['arrival_airport'])): ?>
                                    <div style="color:#4b5563;margin-bottom:2px;font-size:9px;">
                                        <?= esc($shareReturn['departure_airport'] ?? '') ?> → <?= esc($shareReturn['arrival_airport'] ?? '') ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($retDepFmt !== ''): ?>
                                    <div style="color:#6b7280;font-size:9px;line-height:1.3;">Dep: <?= esc($retDepFmt) ?></div>
                                <?php endif; ?>
                                <?php if ($retArrFmt !== ''): ?>
                                    <div style="color:#6b7280;font-size:9px;">Arr: <?= esc($retArrFmt) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Hotels -->
            <?php if ((int) ($card['include_hotel'] ?? 1) === 1 && !empty($shareHotels)): ?>
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
            <?php if ((int) ($card['include_transport'] ?? 1) === 1 && !empty($shareTransports)): ?>
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