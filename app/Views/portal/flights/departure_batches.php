<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="space-y-4">
        <div class="list-toolbar">
            <div class="flex space-x-3">
                <a href="<?= site_url('/app/flights') ?>" class="btn btn-md btn-secondary">
                    <i class="fa-solid fa-arrow-left mr-2"></i>Back to Flights
                </a>
            </div>
        </div>

        <div class="list-card overflow-auto">
            <h3 class="text-lg font-semibold mb-4 px-4 pt-4">Departure Batch View</h3>
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Departure Date</th>
                        <th class="px-3 py-2 text-left">Airline</th>
                        <th class="px-3 py-2 text-left">Flight No</th>
                        <th class="px-3 py-2 text-left">Packages</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="4" class="px-3 py-6 text-center text-slate-500">No departure batches found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2"><?= esc($row['departure_date'] ?: '-') ?></td>
                                <td class="px-3 py-2"><?= esc($row['airline'] ?: '-') ?></td>
                                <td class="px-3 py-2"><?= esc($row['flight_no'] ?: '-') ?></td>
                                <td class="px-3 py-2"><?= esc((int) ($row['packages_count'] ?? 0)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<?php $this->endSection() ?>