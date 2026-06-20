<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Expense Categories</h3>
                <p class="text-xs text-slate-500">Manage expense grouping for the active season.</p>
            </div>
            <a href="<?= site_url('/expense-categories/add') ?>" class="btn btn-md btn-primary"><i class="fa-solid fa-plus"></i><span>Add Category</span></a>
        </div>
    </article>

    <div class="list-card overflow-auto">
        <table class="list-table">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-3 py-2 text-left">ID</th>
                    <th class="px-3 py-2 text-left">Name</th>
                    <th class="px-3 py-2 text-left">Description</th>
                    <th class="px-3 py-2 text-left">Expenses</th>
                    <th class="px-3 py-2 text-left">Total</th>
                    <th class="px-3 py-2 text-left">Status</th>
                    <th class="px-3 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="7" class="px-3 py-6 text-center text-slate-500">No expense categories found.</td>
                    </tr>
                    <?php else: foreach ($rows as $row): ?>
                        <tr class="border-t border-slate-100">
                            <td class="px-3 py-2">#<?= esc((string) $row['id']) ?></td>
                            <td class="px-3 py-2 font-medium text-slate-800"><?= esc((string) $row['name']) ?></td>
                            <td class="px-3 py-2 text-slate-600"><?= esc((string) ($row['description'] ?? '-')) ?></td>
                            <td class="px-3 py-2"><?= esc((string) ((int) ($row['expense_count'] ?? 0))) ?></td>
                            <td class="px-3 py-2 font-semibold text-slate-800"><?= esc(number_format((float) ($row['total_amount'] ?? 0), 2)) ?></td>
                            <td class="px-3 py-2"><span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold <?= ((int) ($row['is_active'] ?? 0) === 1) ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' ?>"><?= ((int) ($row['is_active'] ?? 0) === 1) ? 'Active' : 'Inactive' ?></span></td>
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <a href="<?= site_url('/expense-categories/' . (int) $row['id'] . '/edit') ?>" class="icon-btn" title="Edit Category"><i class="fa-solid fa-pen"></i></a>
                                    <form method="post" action="<?= site_url('/expense-categories/delete') ?>" class="inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="category_id" value="<?= esc((string) $row['id']) ?>">
                                        <button type="submit" class="icon-btn icon-btn-danger" onclick="return confirm('Delete this expense category?')" title="Delete Category"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                <?php endforeach;
                endif; ?>
            </tbody>
        </table>
    </div>
</main>
<?php $this->endSection() ?>