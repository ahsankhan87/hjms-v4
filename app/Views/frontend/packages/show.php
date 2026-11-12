<?= $this->extend('frontend/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$package = $package ?? [];
$relatedPackages = $relatedPackages ?? [];
$price = (float) ($package['price_amount'] ?? 0);
$heroImage = trim((string) ($package['hero_image'] ?? '')) !== '' ? (string) $package['hero_image'] : base_url('assets/uploads/makkah-logo.png');
$duration = (int) ($package['duration_days'] ?? 0);
$overview = trim((string) ($package['notes'] ?? ''));
$inclusions = [];
if ((int) ($package['include_hotel'] ?? 0) === 1) {
    $inclusions[] = 'Hotel accommodation';
}
if ((int) ($package['include_ticket'] ?? 0) === 1) {
    $inclusions[] = 'Flight ticket';
}
if ((int) ($package['include_transport'] ?? 0) === 1) {
    $inclusions[] = 'Ground transport';
}
if ((int) ($package['include_ziarat'] ?? 0) === 1) {
    $inclusions[] = 'Ziarat visits';
}
if ($inclusions === []) {
    $inclusions[] = 'Custom inclusions available on request.';
}
$exclusions = [
    'Personal expenses and shopping',
    'Any services not explicitly listed',
    'Additional nights or upgrades outside package terms',
];
?>
<section class="space-y-8" x-data="{tab:'overview'}">
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="relative h-72 w-full bg-slate-100 md:h-96">
            <img src="<?= esc($heroImage) ?>" alt="<?= esc((string) ($package['name'] ?? 'Package')) ?>" class="h-full w-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/55 via-black/20 to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 p-6 text-white md:p-8">
                <p class="text-xs uppercase tracking-wide text-sky-200"><?= esc((string) ($package['package_type'] ?? 'Package')) ?></p>
                <h1 class="mt-1 text-2xl font-bold md:text-4xl"><?= esc((string) ($package['name'] ?? 'Package Details')) ?></h1>
                <div class="mt-3 flex flex-wrap items-center gap-4 text-sm">
                    <span><?= $duration > 0 ? esc((string) $duration) . ' Days' : 'Duration on request' ?></span>
                    <span><?= $price > 0 ? 'Starting from PKR ' . esc(number_format($price, 0)) : 'Price on request' ?></span>
                </div>
                <a href="<?= site_url('/booking/package/' . (int) ($package['id'] ?? 0)) ?>" class="mt-4 inline-flex rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">Book Now</a>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-4">
            <button type="button" @click="tab='overview'" :class="tab==='overview' ? 'bg-sky-600 text-white' : 'border border-slate-300 text-slate-700'" class="rounded-lg px-3 py-1.5 text-sm font-medium">Overview</button>
            <button type="button" @click="tab='itinerary'" :class="tab==='itinerary' ? 'bg-sky-600 text-white' : 'border border-slate-300 text-slate-700'" class="rounded-lg px-3 py-1.5 text-sm font-medium">Itinerary</button>
            <button type="button" @click="tab='hotels'" :class="tab==='hotels' ? 'bg-sky-600 text-white' : 'border border-slate-300 text-slate-700'" class="rounded-lg px-3 py-1.5 text-sm font-medium">Hotels</button>
            <button type="button" @click="tab='transport'" :class="tab==='transport' ? 'bg-sky-600 text-white' : 'border border-slate-300 text-slate-700'" class="rounded-lg px-3 py-1.5 text-sm font-medium">Transport</button>
            <button type="button" @click="tab='inclusions'" :class="tab==='inclusions' ? 'bg-sky-600 text-white' : 'border border-slate-300 text-slate-700'" class="rounded-lg px-3 py-1.5 text-sm font-medium">Inclusions/Exclusions</button>
        </div>

        <div class="pt-5">
            <div x-show="tab==='overview'" class="text-sm leading-7 text-slate-700">
                <?php if ($overview !== ''): ?>
                    <p><?= nl2br(esc($overview)) ?></p>
                <?php else: ?>
                    <p>This package offers a spiritually focused and comfortable journey with trusted accommodations and organized support.</p>
                <?php endif; ?>
            </div>

            <div x-show="tab==='itinerary'" class="text-sm leading-7 text-slate-700">
                <p>Detailed day-wise itinerary will be shared after booking confirmation.</p>
            </div>

            <div x-show="tab==='hotels'" class="space-y-3 text-sm text-slate-700">
                <?php foreach (($package['hotels'] ?? []) as $hotel): ?>
                    <div class="rounded-lg border border-slate-200 p-3">
                        <p class="font-semibold text-slate-900"><?= esc((string) ($hotel['hotel_name'] ?? 'Hotel')) ?></p>
                        <p class="text-slate-600"><?= esc((string) ($hotel['city'] ?? 'City')) ?> · <?= esc((string) ($hotel['room_type'] ?? 'Room')) ?></p>
                        <p class="text-xs text-slate-500"><?= esc((string) ($hotel['check_in_date'] ?? '')) ?> - <?= esc((string) ($hotel['check_out_date'] ?? '')) ?></p>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($package['hotels'])): ?>
                    <p>Hotel details will be shared on request.</p>
                <?php endif; ?>
            </div>

            <div x-show="tab==='transport'" class="space-y-3 text-sm text-slate-700">
                <?php foreach (($package['transports'] ?? []) as $transport): ?>
                    <div class="rounded-lg border border-slate-200 p-3">
                        <p class="font-semibold text-slate-900"><?= esc((string) ($transport['transport_name'] ?? 'Transport')) ?></p>
                        <p class="text-slate-600"><?= esc((string) ($transport['vehicle_type'] ?? 'Vehicle')) ?> · Seats <?= esc((string) ($transport['seat_capacity'] ?? '-')) ?></p>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($package['transports'])): ?>
                    <p>Transport details will be shared on request.</p>
                <?php endif; ?>
            </div>

            <div x-show="tab==='inclusions'" class="grid gap-5 md:grid-cols-2">
                <div>
                    <h3 class="text-sm font-semibold text-emerald-700">Inclusions</h3>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-slate-700">
                        <?php foreach ($inclusions as $item): ?>
                            <li><?= esc($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-rose-700">Exclusions</h3>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-slate-700">
                        <?php foreach ($exclusions as $item): ?>
                            <li><?= esc($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <section class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-900">Related Packages</h2>
            <a href="<?= site_url('/packages/' . strtolower((string) ($package['package_type'] ?? 'hajj'))) ?>" class="text-sm font-semibold text-sky-700 hover:underline">View more</a>
        </div>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($relatedPackages as $i => $pkg): ?>
                <?= view('frontend/components/package-card', ['pkg' => $pkg, 'index' => $i]) ?>
            <?php endforeach; ?>
            <?php if ($relatedPackages === []): ?>
                <p class="text-sm text-slate-500">No related packages available.</p>
            <?php endif; ?>
        </div>
    </section>
</section>
<?= $this->endSection() ?>

