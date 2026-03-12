<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'HJMS | Packages') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.local.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome-free-7.0.0-web/css/all.min.css') ?>">
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
        }

        .pkg-wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem;
        }

        .pkg-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 1.25rem;
            box-shadow: 0 10px 24px -18px rgba(15, 23, 42, 0.45);
        }
    </style>
</head>

<body>
    <main class="pkg-wrap space-y-4">
        <section class="pkg-card flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-slate-800">Latest Umrah Group Packages</h1>
            <a href="<?= site_url('/login') ?>" class="btn btn-md btn-secondary">Login</a>
        </section>

        <?php if (!empty($error)): ?>
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div>
        <?php endif; ?>

        <?php if (empty($cards)): ?>
            <section class="pkg-card text-slate-500">No active packages available.</section>
        <?php else: ?>
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
                $hotelStays = $card['hotel_stays'] ?? [];
                $transportTypes = $card['transport_types'] ?? [];
                $transportNames = $card['transport_names'] ?? [];
                $transportTypeText = !empty($transportTypes) ? implode(' / ', $transportTypes) : 'TBA';
                $transportNameText = !empty($transportNames) ? implode(' | ', $transportNames) : 'Transport details pending';

                $priceMap = $card['price_map'] ?? [];
                $numericPrices = [];
                foreach ($priceMap as $value) {
                    if ($value !== null && is_numeric($value)) {
                        $numericPrices[] = (float) $value;
                    }
                }
                $startingPrice = !empty($numericPrices) ? min($numericPrices) : null;
                $sharing = $priceMap['sharing'] ?? null;
                $quad = $priceMap['quad'] ?? null;
                $triple = $priceMap['triple'] ?? null;
                $double = $priceMap['double'] ?? null;
                ?>

                <article class="pkg-card">
                    <div class="grid gap-5 lg:grid-cols-12 items-start">
                        <div class="lg:col-span-2 flex flex-col items-start gap-2.5">
                            <?php if (!empty($card['airline_logo'])): ?>
                                <img src="<?= esc($card['airline_logo']) ?>" alt="Airline" class="h-14 w-auto object-contain">
                            <?php else: ?>
                                <div class="h-14 w-24 rounded-lg bg-slate-100 flex items-center justify-center text-xs text-slate-600">No Logo</div>
                            <?php endif; ?>
                            <span class="inline-flex items-center rounded-md bg-amber-100 text-amber-700 px-2 py-1 text-xs font-semibold">
                                <?= esc($card['route_label'] !== '' ? $card['route_label'] : '-') ?>
                            </span>
                            <div class="text-xs text-slate-500">
                                <i class="fa-solid fa-calendar-days mr-1"></i><?= esc((string) ($card['duration_days'] ?? 0)) ?> Days
                            </div>
                        </div>

                        <div class="lg:col-span-7 space-y-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-xl font-semibold text-sky-700"><?= esc($card['name']) ?></h3>
                                <span class="inline-flex items-center rounded-md bg-sky-50 text-sky-700 px-2 py-1 text-xs font-semibold"><?= esc(strtoupper((string) ($card['code'] ?? '-'))) ?></span>
                            </div>
                            <div class="text-sm text-slate-700 font-medium"><?= esc($card['airline_name'] !== '' ? $card['airline_name'] : 'Airline pending from linked flight') ?></div>

                            <div class="rounded-lg border border-sky-100 bg-sky-50/70 p-2.5 space-y-2 text-sm">
                                <div class="font-semibold text-sky-700 text-xs uppercase tracking-wide">Journey</div>
                                <div class="grid sm:grid-cols-2 gap-2">
                                    <div class="rounded-md bg-white border border-slate-200 px-2 py-1.5">
                                        <div class="text-[10px] font-semibold uppercase text-sky-700">Outbound</div>
                                        <div class="text-slate-700"><?= !empty($outbound) ? esc(trim((string) (($outbound['departure_airport'] ?? '') . ' -> ' . ($outbound['arrival_airport'] ?? '')), ' ->')) : 'Route TBA' ?></div>
                                        <div class="text-[11px] text-slate-500"><?= esc($formatDateTime($outbound['departure_at'] ?? ($card['departure_datetime'] ?? ''))) ?></div>
                                    </div>
                                    <div class="rounded-md bg-white border border-slate-200 px-2 py-1.5">
                                        <div class="text-[10px] font-semibold uppercase text-emerald-700">Return</div>
                                        <div class="text-slate-700"><?= !empty($return) ? esc(trim((string) (($return['departure_airport'] ?? '') . ' -> ' . ($return['arrival_airport'] ?? '')), ' ->')) : 'Route TBA' ?></div>
                                        <div class="text-[11px] text-slate-500"><?= esc($formatDateTime($return['arrival_at'] ?? ($card['return_arrival_datetime'] ?? ''))) ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-1.5 text-sm text-slate-700">
                                <div class="font-semibold text-xs uppercase tracking-wide text-amber-700">Stay Plan</div>
                                <?php if (!empty($hotelStays)): ?>
                                    <?php foreach ($hotelStays as $stay): ?>
                                        <div class="flex items-center justify-between gap-2 rounded-md border border-slate-200 bg-white px-2 py-1.5">
                                            <div class="min-w-0 truncate">
                                                <i class="fa-solid fa-hotel mr-1 text-slate-500"></i><?= esc((string) ($stay['name'] ?? 'Hotel pending')) ?>
                                                <span class="text-slate-500">(<?= esc((string) ($stay['city'] ?? 'City')) ?>)</span>
                                            </div>
                                            <span class="shrink-0 rounded bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700"><?= esc((string) max(0, (int) ($stay['nights'] ?? 0))) ?>D</span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-slate-500">Hotel details pending</div>
                                <?php endif; ?>

                                <div class="rounded-md border border-slate-200 bg-slate-50 px-2 py-1.5 text-xs text-slate-700">
                                    <i class="fa-solid fa-bus mr-1"></i>
                                    <span class="font-semibold"><?= esc($transportTypeText) ?></span>
                                    <span class="text-slate-400 mx-1">|</span>
                                    <span class="text-slate-500"><?= esc($transportNameText) ?></span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sky-700">
                                <div>
                                    <div class="font-semibold">Sharing</div>
                                    <div><?= $sharing !== null ? 'PKR ' . esc(number_format((float) $sharing, 0)) : 'N/A' ?></div>
                                </div>
                                <div>
                                    <div class="font-semibold">Quad</div>
                                    <div><?= $quad !== null ? 'PKR ' . esc(number_format((float) $quad, 0)) : 'N/A' ?></div>
                                </div>
                                <div>
                                    <div class="font-semibold">Triple</div>
                                    <div><?= $triple !== null ? 'PKR ' . esc(number_format((float) $triple, 0)) : 'N/A' ?></div>
                                </div>
                                <div>
                                    <div class="font-semibold">Double</div>
                                    <div><?= $double !== null ? 'PKR ' . esc(number_format((float) $double, 0)) : 'N/A' ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-3 text-right space-y-3">
                            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-emerald-800">
                                <div class="text-xs uppercase tracking-wide">Starting From</div>
                                <div class="text-xl font-bold"><?= $startingPrice !== null ? 'PKR ' . esc(number_format((float) $startingPrice, 0)) : 'Price TBA' ?></div>
                            </div>
                            <div class="text-slate-700 text-2xl leading-7">
                                Available <span class="inline-flex bg-emerald-500 text-white rounded px-2 py-0.5 font-semibold"><?= esc((string) $card['available_seats']) ?></span> Seats
                            </div>
                            <div class="text-slate-700 text-2xl leading-8">
                                Traveling: <?= esc($card['travel_date'] !== '' ? date('d-M', strtotime($card['travel_date'])) : '-') ?><br>
                                <span class="font-semibold"><?= esc((string) $card['duration_days']) ?> Nights</span>
                            </div>
                            <div class="flex justify-end">
                                <a href="<?= site_url('/packages/register/' . (int) $card['id']) ?>" class="btn btn-md btn-primary">
                                    <i class="fa-solid fa-plus"></i> Register Now
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</body>

</html>