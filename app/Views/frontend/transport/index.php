<?= $this->extend('frontend/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$filters = $filters ?? [];
?>
<section x-data="{loading:false}" class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Transport Routes</h1>
        <p class="text-sm text-slate-500">Browse available route options and vehicle capacity.</p>
    </div>

    <form method="get" @submit="loading=true" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-3 md:grid-cols-3">
            <input type="text" name="from_city" value="<?= esc((string) ($filters['from_city'] ?? '')) ?>" placeholder="From (e.g., Jeddah Airport)" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="text" name="to_city" value="<?= esc((string) ($filters['to_city'] ?? '')) ?>" placeholder="To (e.g., Makkah)" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <button type="submit" class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">Search Routes</button>
        </div>
        <div class="mt-3 text-sm text-slate-500" x-show="loading">Loading routes...</div>
    </form>

    <?php if (! empty($rows)): ?>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($rows as $row): ?>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Route</p>
                    <h3 class="mt-1 text-lg font-semibold text-slate-900"><?= esc((string) ($row['from_code'] ?? '-')) ?> → <?= esc((string) ($row['to_code'] ?? '-')) ?></h3>
                    <div class="mt-3 space-y-1 text-sm text-slate-600">
                        <p><span class="font-medium text-slate-700">Vehicle:</span> <?= esc((string) ($row['vehicle_type'] ?? 'Standard')) ?></p>
                        <p><span class="font-medium text-slate-700">Capacity:</span> <?= esc((string) ($row['seat_capacity'] ?? '-')) ?> seats</p>
                        <p><span class="font-medium text-slate-700">Provider:</span> <?= esc((string) ($row['provider_name'] ?? '-')) ?></p>
                        <p><span class="font-medium text-slate-700">Price:</span> <?= (float) ($row['avg_price'] ?? 0) > 0 ? 'PKR ' . esc(number_format((float) $row['avg_price'], 0)) : 'On request' ?></p>
                    </div>
                    <a href="<?= site_url('/booking/transport/' . (int) ($row['id'] ?? 0)) ?>" class="mt-4 inline-flex rounded-lg bg-sky-600 px-3 py-2 text-sm font-semibold text-white hover:bg-sky-700">Book Transport</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php elseif (! empty($searched)): ?>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-700">
            No transport routes found. Try a different route combination.
        </div>
    <?php else: ?>
        <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-500">Use search to view available transport routes.</div>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>
