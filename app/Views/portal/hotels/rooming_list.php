<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="space-y-4">
        <div class="list-toolbar">
            <div class="flex space-x-3">
                <a href="<?= site_url('/app/hotels') ?>" class="btn btn-md btn-secondary">
                    <i class="fa-solid fa-arrow-left mr-2"></i>Back to Hotels
                </a>
            </div>
        </div>

        <div class="list-card overflow-auto">
            <h3 class="text-lg font-semibold mb-4 px-4 pt-4">Rooming List Report</h3>
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Hotel</th>
                        <th class="px-3 py-2 text-left">City</th>
                        <th class="px-3 py-2 text-left">Star</th>
                        <th class="px-3 py-2 text-left">Room Type</th>
                        <th class="px-3 py-2 text-left">Total Rooms</th>
                        <th class="px-3 py-2 text-left">Allocated Rooms</th>
                        <th class="px-3 py-2 text-left">Available Rooms</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="7" class="px-3 py-6 text-center text-slate-500">No rooming data available.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <?php $available = max(0, (int) $row['total_rooms'] - (int) $row['allocated_rooms']); ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2 font-medium"><?= esc($row['hotel_name']) ?></td>
                                <td class="px-3 py-2"><?= esc($row['hotel_city'] ?: '-') ?></td>
                                <td class="px-3 py-2"><?= esc($row['star_rating'] ?: '-') ?></td>
                                <td class="px-3 py-2"><?= esc($row['room_type']) ?></td>
                                <td class="px-3 py-2"><?= esc($row['total_rooms']) ?></td>
                                <td class="px-3 py-2"><?= esc($row['allocated_rooms']) ?></td>
                                <td class="px-3 py-2"><?= esc($available) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<?php $this->endSection() ?>