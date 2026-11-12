<?= $this->extend('frontend/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$filters = $filters ?? [];
?>
<section x-data="{loading:false}" class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Hotels</h1>
        <p class="text-sm text-slate-500">Find the right stay in Makkah, Madinah, or Jeddah.</p>
    </div>

    <form method="get" @submit="loading=true" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-3 md:grid-cols-5">
            <?php $city = strtolower((string) ($filters['city'] ?? '')); ?>
            <select name="city" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Any city</option>
                <option value="Makkah" <?= $city === 'makkah' ? 'selected' : '' ?>>Makkah</option>
                <option value="Madinah" <?= $city === 'madinah' ? 'selected' : '' ?>>Madinah</option>
                <option value="Jeddah" <?= $city === 'jeddah' ? 'selected' : '' ?>>Jeddah</option>
            </select>
            <input type="date" name="check_in" value="<?= esc((string) ($filters['check_in'] ?? '')) ?>" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="date" name="check_out" value="<?= esc((string) ($filters['check_out'] ?? '')) ?>" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="number" min="1" name="guests" value="<?= esc((string) ($filters['guests'] ?? 1)) ?>" placeholder="Guests" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <button type="submit" class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">Search Hotels</button>
        </div>
        <div class="mt-3 text-sm text-slate-500" x-show="loading">Searching hotels...</div>
    </form>

    <?php if (! empty($rows)): ?>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($rows as $row): ?>
                <?php
                $img = trim((string) ($row['image_url'] ?? ''));
                if ($img === '') {
                    $img = base_url('assets/uploads/madina-logo.png');
                }
                $amenities = is_array($row['amenities'] ?? null) ? $row['amenities'] : [];
                ?>
                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="h-44 w-full overflow-hidden bg-slate-100">
                        <img src="<?= esc($img) ?>" alt="<?= esc((string) ($row['name'] ?? 'Hotel')) ?>" class="h-full w-full object-cover">
                    </div>
                    <div class="space-y-3 p-4">
                        <h3 class="text-lg font-semibold text-slate-900"><?= esc((string) ($row['name'] ?? 'Hotel')) ?></h3>
                        <p class="text-sm text-slate-600"><?= esc((string) ($row['city'] ?? '-')) ?> · <?= str_repeat('★', max(0, (int) ($row['star_rating'] ?? 0))) ?></p>
                        <p class="text-sm text-slate-500">Distance from Haram: <?= esc(number_format((float) ($row['distance_m'] ?? 0))) ?>m</p>
                        <div class="flex flex-wrap gap-2 text-xs text-slate-600">
                            <?php foreach ($amenities as $amenity): ?>
                                <span class="rounded-full bg-slate-100 px-2 py-1"><?= esc((string) $amenity) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="flex items-center justify-between gap-2 pt-2">
                            <p class="text-sky-700 font-bold"><?= (float) ($row['price_per_night'] ?? 0) > 0 ? 'PKR ' . esc(number_format((float) $row['price_per_night'], 0)) . '/night' : 'Price on request' ?></p>
                            <div class="flex items-center gap-2">
                                <a href="<?= site_url('/hotels/' . (int) ($row['id'] ?? 0)) ?>" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">View Rooms</a>
                                <a href="<?= site_url('/booking/hotel/' . (int) ($row['id'] ?? 0)) ?>" class="rounded-lg bg-sky-600 px-3 py-2 text-sm font-semibold text-white hover:bg-sky-700">Book</a>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php elseif (! empty($searched)): ?>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-700">
            No hotels match your criteria. Try another city, dates, or guest count.
        </div>
    <?php else: ?>
        <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-500">Search to see available hotels and room options.</div>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>
