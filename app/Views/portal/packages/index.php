<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="bg-slate-50 min-h-screen pb-10">

    <!-- Top Alert Messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
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
        <div class="flex items-center justify-between mb-6">
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
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 text-slate-400 mb-4">
                    <i class="fa-solid fa-box-open text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-slate-900">No packages found</h3>
                <p class="text-slate-500 mt-1">Get started by creating a new package.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mt-4">
                <?php foreach ($cards as $card): ?>
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden flex flex-col">

                        <!-- Card Header / Image Area -->
                        <div class="bg-slate-50 p-4 border-b border-slate-100 flex justify-between items-start">
                            <div class="flex items-center gap-3">
                                <?php if (!empty($card['airline_logo'])): ?>
                                    <img src="<?= esc($card['airline_logo']) ?>" alt="Airline" class="h-10 w-auto object-contain bg-white p-1 rounded border border-slate-200">
                                <?php else: ?>
                                    <div class="h-10 w-10 rounded bg-slate-200 flex items-center justify-center text-slate-400">
                                        <i class="fa-solid fa-plane"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Airline</span>
                                    <span class="text-sm font-semibold text-slate-800"><?= esc($card['route_label'] ?: 'N/A') ?></span>
                                </div>
                            </div>

                            <!-- Availability Badge -->
                            <div class="text-right text-xs font-semibold text-slate-600">
                                <?= esc($card['available_seats']) ?> Seats Left
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-5 flex-1">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-lg font-bold text-slate-800 leading-tight line-clamp-2"><?= esc($card['name']) ?></h3>
                            </div>

                            <!-- Dates -->
                            <div class="flex items-center gap-4 text-sm text-slate-600 mb-4 pb-4 border-b border-slate-100">
                                <div class="flex items-center gap-1.5">
                                    <i class="fa-regular fa-calendar text-sky-500"></i>
                                    <span><?= esc($card['travel_date'] ? date('M d, Y', strtotime($card['travel_date'])) : 'TBA') ?></span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <i class="fa-regular fa-clock text-sky-500"></i>
                                    <span><?= esc($card['duration_days']) ?> Days</span>
                                </div>
                            </div>

                            <!-- Hotel Info -->
                            <div class="space-y-2 mb-4">
                                <?php $hotelItem1 = $card['hotel_items'][0] ?? null; ?>
                                <?php $hotelItem2 = $card['hotel_items'][1] ?? null; ?>
                                <div class="flex items-start gap-2 text-sm text-slate-600">
                                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-slate-200 bg-white p-0.5">
                                        <img src="<?= esc(base_url('assets/uploads/makkah-logo.png')) ?>" alt="Makkah" class="max-h-8 max-w-8 object-contain">
                                    </span>
                                    <?php if (!empty($hotelItem1['id'])): ?>
                                        <a href="<?= site_url('/hotels/' . (int) $hotelItem1['id']) ?>" class="line-clamp-1 text-slate-700 hover:text-emerald-700 hover:underline" title="View hotel details">
                                            <?= esc((string) ($hotelItem1['name'] ?? ($card['hotel_names'][0] ?? 'Hotel pending'))) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="line-clamp-1"><?= esc((string) ($hotelItem1['name'] ?? ($card['hotel_names'][0] ?? 'Hotel pending'))) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($card['hotel_names'][1])): ?>
                                    <div class="flex items-start gap-2 text-sm text-slate-600">
                                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-slate-200 bg-white p-0.5">
                                            <img src="<?= esc(base_url('assets/uploads/madina-logo.png')) ?>" alt="Madina" class="max-h-8 max-w-8 object-contain">
                                        </span>
                                        <?php if (!empty($hotelItem2['id'])): ?>
                                            <a href="<?= site_url('/hotels/' . (int) $hotelItem2['id']) ?>" class="line-clamp-1 text-slate-700 hover:text-emerald-700 hover:underline" title="View hotel details">
                                                <?= esc((string) ($hotelItem2['name'] ?? $card['hotel_names'][1])) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="line-clamp-1"><?= esc((string) ($hotelItem2['name'] ?? $card['hotel_names'][1])) ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Pricing Grid -->
                            <div class="bg-slate-50 rounded-lg p-3 grid grid-cols-2 gap-2 text-xs">
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
                        <div class="p-4 bg-white border-t border-slate-100 flex items-center justify-between gap-3">
                            <form method="post" action="<?= site_url('/packages/delete') ?>" class="shrink-0" onsubmit="return confirm('Are you sure you want to delete this package?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="package_id" value="<?= esc($card['id']) ?>">
                                <button type="submit" class="w-10 h-10 rounded-lg border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 flex items-center justify-center" title="Delete Package">
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