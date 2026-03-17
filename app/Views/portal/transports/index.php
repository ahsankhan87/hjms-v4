<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="space-y-3">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Transport Providers</h3>
                    <p class="text-xs text-slate-500">Manage transport resources and routing details.</p>
                </div>
                <a href="<?= site_url('/transports/add') ?>" class="btn btn-md btn-primary">
                    <i class="fa-solid fa-plus"></i><span>Add Transport Provider</span>
                </a>
            </div>
        </article>

        <div class="list-card overflow-auto">
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">ID</th>
                        <th class="px-3 py-2 text-left">Name</th>
                        <th class="px-3 py-2 text-left">Provider</th>
                        <th class="px-3 py-2 text-left">Type</th>
                        <th class="px-3 py-2 text-left">Driver</th>
                        <th class="px-3 py-2 text-left">Driver Phone</th>
                        <th class="px-3 py-2 text-left">Seat Capacity</th>
                        <th class="px-3 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="8" class="px-3 py-6 text-center text-slate-500">No transport providers found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2 font-medium">#<?= esc($row['id']) ?></td>
                                <td class="px-3 py-2"><?= esc((string) ($row['transport_name'] ?? '-')) ?></td>
                                <td class="px-3 py-2"><?= esc($row['provider_name']) ?></td>
                                <td class="px-3 py-2"><?= esc(ucfirst((string) ($row['vehicle_type'] ?? '-'))) ?></td>
                                <td class="px-3 py-2"><?= esc($row['driver_name'] ?: '-') ?></td>
                                <td class="px-3 py-2"><?= esc($row['driver_phone'] ?: '-') ?></td>
                                <td class="px-3 py-2"><?= esc((int) ($row['seat_capacity'] ?? 0)) ?></td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center space-x-2">
                                        <a href="<?= site_url('/transports/' . (int) $row['id'] . '/edit') ?>" class="icon-btn" title="Edit Transport">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form method="post" action="<?= site_url('/transports/delete') ?>" class="inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="transport_id" value="<?= esc($row['id']) ?>">
                                            <button type="submit" class="icon-btn icon-btn-danger" onclick="return confirm('Delete this transport provider?')" title="Delete Transport">
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