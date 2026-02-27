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
            background: #f3f4f6;
        }

        .pkg-wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem;
        }

        .pkg-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 8px 24px -16px rgba(15, 23, 42, .3);
        }
    </style>
</head>

<body>
    <main class="pkg-wrap space-y-4">
        <section class="pkg-card flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-slate-800">Latest Umrah Group Packages</h1>
            <a href="<?= site_url('/app/login') ?>" class="btn btn-md btn-secondary">Login</a>
        </section>

        <?php if (!empty($error)): ?>
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div>
        <?php endif; ?>

        <?php if (empty($cards)): ?>
            <section class="pkg-card text-slate-500">No active packages available.</section>
        <?php else: ?>
            <?php foreach ($cards as $card): ?>
                <article class="pkg-card">
                    <div class="grid gap-6 lg:grid-cols-12 items-start">
                        <div class="lg:col-span-2 flex flex-col items-start gap-3">
                            <?php if (!empty($card['airline_logo'])): ?>
                                <img src="<?= esc($card['airline_logo']) ?>" alt="Airline" class="h-14 w-auto object-contain">
                            <?php else: ?>
                                <div class="h-14 w-24 rounded-lg bg-slate-100 flex items-center justify-center text-xs text-slate-600">No Logo</div>
                            <?php endif; ?>
                            <span class="inline-flex items-center rounded-md bg-amber-100 text-amber-700 px-2 py-1 text-xs font-semibold">
                                <?= esc($card['route_label'] !== '' ? $card['route_label'] : '-') ?>
                            </span>
                            <?php if (!empty($card['ticket_refs'])): ?>
                                <div class="text-sm text-sky-700 leading-5">
                                    <?php foreach ($card['ticket_refs'] as $ref): ?>
                                        <div><?= esc($ref) ?></div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="lg:col-span-7 space-y-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-xl font-semibold text-sky-700"><?= esc($card['name']) ?></h3>
                                <span class="inline-flex items-center rounded-md bg-sky-50 text-sky-700 px-2 py-1 text-xs font-semibold"><?= esc(strtoupper($card['code'])) ?></span>
                            </div>
                            <div class="text-sm text-slate-700 font-medium"><?= esc($card['airline_name'] !== '' ? $card['airline_name'] : 'Airline pending from linked flight') ?></div>
                            <div class="space-y-1 text-sky-700 text-sm">
                                <div><i class="fa-solid fa-hotel mr-2"></i><?= esc($card['hotel_names'][0] ?? 'Hotel 1 pending from linked record') ?></div>
                                <div><i class="fa-solid fa-hotel mr-2"></i><?= esc($card['hotel_names'][1] ?? 'Hotel 2 pending from linked record') ?></div>
                                <div><i class="fa-solid fa-bus mr-2"></i><?= esc((string) $card['transport_count']) ?> linked transport(s)</div>
                            </div>

                            <?php
                            $priceMap = $card['price_map'];
                            $sharing = $priceMap['sharing'] ?? null;
                            $quad = $priceMap['quad'] ?? null;
                            $triple = $priceMap['triple'] ?? null;
                            $double = $priceMap['double'] ?? null;
                            ?>
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