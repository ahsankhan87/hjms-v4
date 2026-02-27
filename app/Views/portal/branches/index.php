<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="grid gap-6 lg:grid-cols-3">
        <article class="rounded-xl border border-slate-200 bg-white p-5 lg:col-span-1">
            <h3 class="text-lg font-semibold">Add Branch</h3>
            <form method="post" action="/app/branches" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <div>
                    <label class="text-sm font-medium">Code</label>
                    <input name="code" value="<?= esc(old('code')) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Name</label>
                    <input name="name" value="<?= esc(old('name')) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Phone</label>
                    <input name="phone" value="<?= esc(old('phone')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Address</label>
                    <textarea name="address" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc(old('address')) ?></textarea>
                </div>
                <button type="submit" class="btn btn-md btn-primary btn-block">Create Branch</button>
            </form>

            <hr class="my-5 border-slate-200">

            <h3 class="text-lg font-semibold">Update Branch</h3>
            <form method="post" action="/app/branches/update" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="number" name="branch_id" min="1" required placeholder="Branch ID" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="code" placeholder="New Code (optional)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="name" placeholder="New Name (optional)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="phone" placeholder="New Phone (optional)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <textarea name="address" rows="2" placeholder="New Address (optional)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                <select name="is_active" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Status (optional)</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <button type="submit" class="btn btn-md btn-primary btn-block">Update Branch</button>
            </form>

            <hr class="my-5 border-slate-200">

            <h3 class="text-lg font-semibold">Delete Branch</h3>
            <form method="post" action="/app/branches/delete" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="number" name="branch_id" min="1" required placeholder="Branch ID" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <button type="submit" class="btn btn-md btn-danger btn-block">Delete Branch</button>
            </form>
        </article>

        <article class="list-card lg:col-span-2 overflow-auto">
            <h3 class="text-lg font-semibold mb-4">Branch List</h3>
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Code</th>
                        <th class="px-3 py-2 text-left">Name</th>
                        <th class="px-3 py-2 text-left">Phone</th>
                        <th class="px-3 py-2 text-left">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="4" class="px-3 py-6 text-center text-slate-500">No branches found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2 font-medium"><?= esc($row['code']) ?></td>
                                <td class="px-3 py-2"><?= esc($row['name']) ?></td>
                                <td class="px-3 py-2"><?= esc($row['phone'] ?? '-') ?></td>
                                <td class="px-3 py-2"><?= (int) ($row['is_active'] ?? 0) === 1 ? 'Active' : 'Inactive' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </article>
    </section>
</main>
<?php $this->endSection() ?>