<?= $this->extend('frontend/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$hotel = is_array($hotel ?? null) ? $hotel : [];
$rooms = is_array($rooms ?? null) ? $rooms : [];
$img = trim((string) ($hotel['image_url'] ?? ''));
if ($img === '') {
    $img = base_url('assets/uploads/madina-logo.png');
}
?>
<section class="space-y-6">
    <?php if (! empty($error)): ?>
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc((string) $error) ?></div>
    <?php endif; ?>

    <a href="<?= site_url('/hotels') ?>" class="inline-flex items-center gap-2 text-sm font-medium text-sky-700 hover:text-sky-800">← Back to Hotels</a>

    <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="h-64 w-full overflow-hidden bg-slate-100">
            <img src="<?= esc($img) ?>" alt="<?= esc((string) ($hotel['name'] ?? 'Hotel')) ?>" class="h-full w-full object-cover">
        </div>
        <div class="grid gap-6 p-6 lg:grid-cols-12">
            <div class="lg:col-span-8 space-y-3">
                <h1 class="text-2xl font-bold text-slate-900"><?= esc((string) ($hotel['name'] ?? 'Hotel')) ?></h1>
                <p class="text-sm text-slate-600"><?= esc((string) ($hotel['city'] ?? '-')) ?> · <?= str_repeat('★', max(0, (int) ($hotel['star_rating'] ?? 0))) ?></p>
                <p class="text-sm text-slate-600"><?= esc((string) ($hotel['address'] ?? 'Address not available')) ?></p>
                <p class="text-sm text-slate-500">Distance from Haram: <?= esc(number_format((float) ($hotel['distance_m'] ?? 0))) ?>m</p>
            </div>
            <aside class="lg:col-span-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                <h2 class="text-sm font-semibold text-slate-900">Quick Actions</h2>
                <p class="mt-2 text-xs text-slate-500">Book this hotel using the protected booking flow.</p>
                <a href="<?= site_url('/booking/hotel/' . (int) ($hotel['id'] ?? 0)) ?>" class="mt-4 inline-flex rounded-lg bg-sky-600 px-3 py-2 text-sm font-semibold text-white hover:bg-sky-700">Book Hotel</a>
            </aside>
        </div>
    </article>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Available Rooms</h2>
        <?php if ($rooms !== []): ?>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th class="px-3 py-2">Room Type</th>
                            <th class="px-3 py-2">Beds</th>
                            <th class="px-3 py-2">Cost / Night</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rooms as $room): ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2 text-slate-800"><?= esc((string) ($room['room_type'] ?? 'Standard')) ?></td>
                                <td class="px-3 py-2 text-slate-600"><?= esc((string) ($room['beds'] ?? '-')) ?></td>
                                <td class="px-3 py-2 font-semibold text-sky-700"><?= 'PKR ' . esc(number_format((float) ($room['room_cost'] ?? 0), 0)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="mt-3 text-sm text-slate-500">Room details are not available at the moment. Please use booking to request latest options.</p>
        <?php endif; ?>
    </section>
</section>
<?= $this->endSection() ?>
