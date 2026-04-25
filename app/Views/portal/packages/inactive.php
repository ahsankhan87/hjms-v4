<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?>
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700 flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i> <?= esc($success) ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700 flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation"></i> <?= esc($error) ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $err): ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="rounded-xl border border-slate-200 bg-white px-4 py-3">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-base font-semibold text-slate-800">Inactive Packages</h1>
                <p class="mt-1 text-xs text-slate-500">Packages are automatically moved here after departure date or can be archived manually.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?= site_url('/packages') ?>" class="btn btn-md btn-outline inline-flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Back To Active Packages</span>
                </a>
                <a href="<?= site_url('/packages/add') ?>" class="btn btn-md btn-primary inline-flex items-center justify-center gap-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Create New Package</span>
                </a>
            </div>
        </div>
    </section>

    <?php if (empty($rows)): ?>
        <div class="rounded-xl border border-slate-200 bg-white p-8 text-center">
            <div class="mx-auto mb-4 inline-flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                <i class="fa-solid fa-box-open text-2xl"></i>
            </div>
            <h3 class="text-base font-semibold text-slate-900">No inactive packages</h3>
            <p class="mt-1 text-sm text-slate-500">All packages are currently active.</p>
        </div>
    <?php else: ?>
        <section class="rounded-xl border border-slate-200 bg-white overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                        <tr>
                            <th class="px-3 py-2 text-left">Code</th>
                            <th class="px-3 py-2 text-left">Package</th>
                            <th class="px-3 py-2 text-left">Type</th>
                            <th class="px-3 py-2 text-left">Duration</th>
                            <th class="px-3 py-2 text-left">Departure</th>
                            <th class="px-3 py-2 text-left">Arrival</th>
                            <th class="px-3 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <?php foreach ($rows as $row): ?>
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-3 py-2 font-medium text-slate-700"><?= esc((string) ($row['code'] ?? '-')) ?></td>
                                <td class="px-3 py-2 text-slate-700"><?= esc((string) ($row['name'] ?? '-')) ?></td>
                                <td class="px-3 py-2 text-slate-600 uppercase"><?= esc((string) ($row['package_type'] ?? '-')) ?></td>
                                <td class="px-3 py-2 text-slate-600"><?= esc((string) ($row['duration_days'] ?? '-')) ?> days</td>
                                <td class="px-3 py-2 text-slate-600"><?= esc((string) ($row['departure_date'] ?? '-')) ?></td>
                                <td class="px-3 py-2 text-slate-600"><?= esc((string) ($row['arrival_date'] ?? '-')) ?></td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="post" action="<?= site_url('/packages/status') ?>" onsubmit="return confirm('Reactivate this package?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="package_id" value="<?= esc((string) ($row['id'] ?? '')) ?>">
                                            <input type="hidden" name="is_active" value="1">
                                            <input type="hidden" name="redirect_to" value="packages/inactive">
                                            <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100" title="Reactivate Package">
                                                <i class="fa-solid fa-rotate-left"></i>
                                                <span>Reactivate</span>
                                            </button>
                                        </form>

                                        <a href="<?= site_url('/packages/' . (int) ($row['id'] ?? 0) . '/edit') ?>" class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-50" title="Manage Package">
                                            <i class="fa-solid fa-gear"></i>
                                            <span>Manage</span>
                                        </a>

                                        <form method="post" action="<?= site_url('/packages/delete') ?>" onsubmit="return confirm('Delete this package permanently?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="package_id" value="<?= esc((string) ($row['id'] ?? '')) ?>">
                                            <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100" title="Delete Package">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php $this->endSection() ?>