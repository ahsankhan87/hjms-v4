<?= $this->extend('frontend/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$filters = $filters ?? [];
?>
<section x-data="{loading:false}" class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Flight Tickets</h1>
        <p class="text-sm text-slate-500">Search best available ticket options for your route.</p>
    </div>

    <form method="get" @submit="loading=true" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-3 md:grid-cols-4">
            <input type="text" name="from_city" value="<?= esc((string) ($filters['from_city'] ?? '')) ?>" placeholder="From city" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="text" name="to_city" value="<?= esc((string) ($filters['to_city'] ?? '')) ?>" placeholder="To city" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="date" name="departure_date" value="<?= esc((string) ($filters['departure_date'] ?? '')) ?>" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <select name="class" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <?php $class = strtolower((string) ($filters['travel_class'] ?? 'economy')); ?>
                <option value="economy" <?= $class === 'economy' ? 'selected' : '' ?>>Economy</option>
                <option value="business" <?= $class === 'business' ? 'selected' : '' ?>>Business</option>
                <option value="first" <?= $class === 'first' ? 'selected' : '' ?>>First</option>
            </select>
        </div>
        <div class="mt-4 flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">Search Tickets</button>
            <span x-show="loading" class="text-sm text-slate-500">Searching...</span>
        </div>
    </form>

    <?php if (! empty($rows)): ?>
        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Airline</th>
                        <th class="px-4 py-3">Route</th>
                        <th class="px-4 py-3">Times</th>
                        <th class="px-4 py-3">Class</th>
                        <th class="px-4 py-3">Price</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3 font-medium text-slate-900"><?= esc((string) ($row['airline'] ?? '-')) ?></td>
                            <td class="px-4 py-3 text-slate-600"><?= esc((string) ($row['departure_airport'] ?? '-')) ?> → <?= esc((string) ($row['arrival_airport'] ?? '-')) ?></td>
                            <td class="px-4 py-3 text-slate-600"><?= esc((string) ($row['departure_at'] ?? '-')) ?><br><span class="text-xs text-slate-500"><?= esc((string) ($row['arrival_at'] ?? '-')) ?></span></td>
                            <td class="px-4 py-3 text-slate-600"><?= esc((string) ($row['travel_class'] ?? 'Economy')) ?></td>
                            <td class="px-4 py-3 font-semibold text-sky-700"><?= 'PKR ' . esc(number_format((float) ($row['fare'] ?? 0), 0)) ?></td>
                            <td class="px-4 py-3">
                                <a href="<?= site_url('/booking/ticket/' . (int) ($row['id'] ?? 0)) ?>" class="inline-flex rounded-lg bg-sky-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-sky-700">Select</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php elseif (! empty($searched)): ?>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-700">
            No tickets found for your search. Try changing city, date, or class.
        </div>
    <?php else: ?>
        <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-500">Use the search form to view available tickets.</div>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>
