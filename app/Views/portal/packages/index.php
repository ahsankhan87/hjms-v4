<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="space-y-4">
        <div class="flex items-center justify-between bg-white rounded-xl shadow-sm p-4 border border-gray-100">
            <h2 class="text-lg font-semibold text-slate-800">Latest <?= esc(ucfirst((string) ($rows[0]['package_type'] ?? ''))) ?> Group Packages</h2>
            <a href="<?= site_url('/app/packages/add') ?>" class="btn btn-md btn-primary">
                <i class="fa-solid fa-plus mr-2"></i>Add Package
            </a>
        </div>

        <?php if (empty($cards)): ?>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 text-slate-500">No packages found.</div>
        <?php else: ?>
            <?php foreach ($cards as $card): ?>
                <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
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
                                <span class="status-badge status-approved"><?= esc(strtoupper($card['code'])) ?></span>
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
                            <div class="flex justify-end gap-2">
                                <a href="<?= site_url('/app/packages/' . (int) $card['id'] . '/edit') ?>" class="btn btn-md btn-primary">Manage</a>
                                <form method="post" action="<?= site_url('/app/packages/delete') ?>" class="inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="package_id" value="<?= esc($card['id']) ?>">
                                    <button type="submit" class="btn btn-md btn-danger" onclick="return confirm('Delete this package?')">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>
<?php $this->endSection() ?>