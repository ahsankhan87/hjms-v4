<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h3 class="text-lg font-semibold">Create Season</h3>
        <form method="post" action="<?= site_url('/app/seasons') ?>" class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
            <?= csrf_field() ?>
            <div><label class="text-sm font-medium">Year Start</label><input type="number" name="year_start" min="2001" required value="<?= esc(old('year_start', date('Y'))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
            <div><label class="text-sm font-medium">Year End</label><input type="number" name="year_end" min="2002" required value="<?= esc(old('year_end', date('Y') + 1)) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
            <div><label class="text-sm font-medium">Name (optional)</label><input type="text" name="name" value="<?= esc(old('name')) ?>" placeholder="2026 - 2027 Season" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
            <div>
                <label class="text-sm font-medium">Active</label>
                <div class="mt-2 flex items-center gap-2">
                    <input id="is_active" type="checkbox" name="is_active" value="1" class="rounded border-slate-300" <?= old('is_active') ? 'checked' : '' ?>>
                    <label for="is_active" class="text-sm text-slate-600">Set as active season</label>
                </div>
            </div>
            <div class="md:col-span-4"><button type="submit" class="btn btn-md btn-primary">Create Season</button></div>
        </form>
    </section>

    <section class="list-card overflow-auto">
        <h3 class="text-lg font-semibold mb-4">Season List</h3>
        <table class="list-table">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-3 py-2 text-left">Name</th>
                    <th class="px-3 py-2 text-left">Years</th>
                    <th class="px-3 py-2 text-left">Status</th>
                    <th class="px-3 py-2 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr class="border-t border-slate-100">
                        <td class="px-3 py-2 font-medium"><?= esc($row['name']) ?></td>
                        <td class="px-3 py-2"><?= esc($row['year_start']) ?> - <?= esc($row['year_end']) ?></td>
                        <td class="px-3 py-2">
                            <?php if ((int) ($row['is_active'] ?? 0) === 1): ?>
                                <span class="inline-flex rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">Active</span>
                            <?php else: ?>
                                <span class="inline-flex rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 py-2">
                            <?php if ((int) ($row['is_active'] ?? 0) !== 1): ?>
                                <form method="post" action="<?= site_url('/app/seasons/activate') ?>" class="inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="season_id" value="<?= esc($row['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-secondary">Activate</button>
                                </form>
                            <?php else: ?>
                                <span class="text-xs text-slate-500">Current</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
<?php $this->endSection() ?>