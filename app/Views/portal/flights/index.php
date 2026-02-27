<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="space-y-4">
        <div class="list-toolbar">
            <div class="flex space-x-3">
                <a href="<?= site_url('/app/flights/add') ?>" class="btn btn-md btn-primary">
                    <i class="fa-solid fa-plus mr-2"></i>Add Flight
                </a>
                <a href="<?= site_url('/app/flights/departure-batches') ?>" class="btn btn-md btn-secondary">
                    <i class="fa-solid fa-layer-group mr-2"></i>Departure Batch View
                </a>
            </div>
            <form method="get" action="<?= site_url('/app/flights') ?>" class="flex items-center gap-2">
                <input type="text" name="pnr" value="<?= esc($pnr ?? '') ?>" placeholder="Track by PNR" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                <button type="submit" class="btn btn-md btn-primary">Search</button>
                <a href="<?= site_url('/app/flights') ?>" class="btn btn-md btn-secondary">Reset</a>
            </form>
        </div>

        <div class="list-card overflow-auto">
            <h3 class="text-lg font-semibold mb-4 px-4 pt-4">Flight List</h3>
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">ID</th>
                        <th class="px-3 py-2 text-left">Airline</th>
                        <th class="px-3 py-2 text-left">Flight No</th>
                        <th class="px-3 py-2 text-left">PNR</th>
                        <th class="px-3 py-2 text-left">Departure</th>
                        <th class="px-3 py-2 text-left">Arrival</th>
                        <th class="px-3 py-2 text-left">Ticket</th>
                        <th class="px-3 py-2 text-left">Pkg Links</th>
                        <th class="px-3 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="9" class="px-3 py-6 text-center text-slate-500">No flights found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2 font-medium">#<?= esc($row['id']) ?></td>
                                <td class="px-3 py-2"><?= esc($row['airline']) ?></td>
                                <td class="px-3 py-2"><?= esc($row['flight_no']) ?></td>
                                <td class="px-3 py-2"><?= esc($row['pnr'] ?: '-') ?></td>
                                <td class="px-3 py-2"><?= esc($row['departure_at'] ?: '-') ?></td>
                                <td class="px-3 py-2"><?= esc($row['arrival_at'] ?: '-') ?></td>
                                <td class="px-3 py-2"><?= esc($row['ticket_file_name'] ?: '-') ?></td>
                                <td class="px-3 py-2"><?= esc((int) ($row['package_links'] ?? 0)) ?></td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center space-x-2">
                                        <a href="<?= site_url('/app/flights/' . (int) $row['id'] . '/edit') ?>" class="icon-btn" title="Edit Flight">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form method="post" action="<?= site_url('/app/flights/delete') ?>" class="inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="flight_id" value="<?= esc($row['id']) ?>">
                                            <button type="submit" class="icon-btn icon-btn-danger" onclick="return confirm('Delete this flight?')" title="Delete Flight">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<?php $this->endSection() ?>