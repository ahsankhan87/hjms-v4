<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="space-y-4">
        <div class="list-toolbar">
            <div class="flex space-x-3">
                <a href="<?= site_url('/app/suppliers/add') ?>" class="btn btn-md btn-primary"><i class="fa-solid fa-plus mr-2"></i>Add Supplier</a>
            </div>
        </div>

        <div class="list-card overflow-auto">
            <h3 class="text-lg font-semibold mb-4 px-4 pt-4">Suppliers</h3>
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">ID</th>
                        <th class="px-3 py-2 text-left">Name</th>
                        <th class="px-3 py-2 text-left">Type</th>
                        <th class="px-3 py-2 text-left">Contact</th>
                        <th class="px-3 py-2 text-left">Phone</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="7" class="px-3 py-6 text-center text-slate-500">No suppliers found.</td>
                        </tr>
                        <?php else: foreach ($rows as $row): ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2">#<?= esc((string) $row['id']) ?></td>
                                <td class="px-3 py-2 font-medium"><?= esc((string) $row['supplier_name']) ?></td>
                                <td class="px-3 py-2"><?= esc(ucfirst((string) $row['supplier_type'])) ?></td>
                                <td class="px-3 py-2"><?= esc((string) ($row['contact_person'] ?? '-')) ?></td>
                                <td class="px-3 py-2"><?= esc((string) ($row['phone'] ?? '-')) ?></td>
                                <td class="px-3 py-2"><?= ((int) ($row['is_active'] ?? 0) === 1) ? 'Active' : 'Inactive' ?></td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center space-x-2">
                                        <a href="<?= site_url('/app/suppliers/' . (int) $row['id'] . '/ledger') ?>" class="btn btn-sm btn-secondary">Ledger</a>
                                        <a href="<?= site_url('/app/suppliers/' . (int) $row['id'] . '/edit') ?>" class="icon-btn" title="Edit Supplier"><i class="fa-solid fa-pen"></i></a>
                                        <form method="post" action="<?= site_url('/app/suppliers/delete') ?>" class="inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="supplier_id" value="<?= esc((string) $row['id']) ?>">
                                            <button type="submit" class="icon-btn icon-btn-danger" onclick="return confirm('Delete this supplier?')" title="Delete Supplier"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<?php $this->endSection() ?>