<?php
$pkg = $pkg ?? [];
$index = (int) ($index ?? 0);
$slug = (string) ($pkg['slug'] ?? ((string) ($pkg['id'] ?? '')));
$name = (string) ($pkg['name'] ?? 'Package');
$duration = (int) ($pkg['duration_days'] ?? 0);
$makkahHotel = trim((string) ($pkg['makkah_hotel'] ?? ''));
$madinahHotel = trim((string) ($pkg['madina_hotel'] ?? ''));
$price = (float) ($pkg['price_amount'] ?? 0);
$image = trim((string) ($pkg['hero_image'] ?? ''));
if ($image === '') {
    $image = base_url('assets/uploads/makkah-logo.png');
}
?>
<article
    class="package-card overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
    data-price-card="1"
    data-price="<?= esc((string) $price) ?>"
    data-initial-index="<?= esc((string) $index) ?>">
    <div class="h-44 w-full overflow-hidden bg-slate-100">
        <img src="<?= esc($image) ?>" alt="<?= esc($name) ?>" class="h-full w-full object-cover">
    </div>
    <div class="space-y-3 p-5">
        <h3 class="line-clamp-2 text-lg font-semibold text-slate-900"><?= esc($name) ?></h3>
        <p class="text-xs text-slate-500"><?= $duration > 0 ? esc((string) $duration) . ' Days' : 'Duration on request' ?></p>
        <div class="space-y-1 text-sm text-slate-600">
            <p><span class="font-medium text-slate-700">Makkah:</span> <?= esc($makkahHotel !== '' ? $makkahHotel : 'TBA') ?></p>
            <p><span class="font-medium text-slate-700">Madinah:</span> <?= esc($madinahHotel !== '' ? $madinahHotel : 'TBA') ?></p>
        </div>
        <div class="flex items-center justify-between pt-2">
            <div>
                <p class="text-xs text-slate-500">Starting from</p>
                <p class="text-lg font-bold text-sky-700"><?= $price > 0 ? 'PKR ' . esc(number_format($price, 0)) : 'Contact for price' ?></p>
            </div>
            <a href="<?= site_url('/packages/' . $slug) ?>" class="rounded-lg bg-sky-600 px-3 py-2 text-sm font-semibold text-white hover:bg-sky-700">View Details</a>
        </div>
    </div>
</article>
