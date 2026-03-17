<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
        <h3 class="text-sm font-semibold text-slate-800">Role Management</h3>
        <p class="text-xs text-slate-500">Create and maintain access roles used in RBAC mappings.</p>
    </article>

    <article class="rounded-xl border border-slate-200 bg-white p-4">
        <h3 class="text-sm font-semibold text-slate-800">Create Role</h3>
        <form method="post" action="<?= site_url('/rbac/roles') ?>" class="mt-4 space-y-3">
            <?= csrf_field() ?>
            <input name="name" required placeholder="Role name (e.g. manager)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <textarea name="description" rows="2" placeholder="Description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
            <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
                <a class="btn btn-md btn-secondary" href="<?= site_url('/rbac') ?>">Back</a>
                <button class="btn btn-md btn-primary" type="submit"><i class="fa-solid fa-check"></i><span>Create Role</span></button>
            </div>
        </form>
    </article>

    <article class="list-card">
        <div class="p-4 border-b border-gray-100 text-sm font-semibold text-slate-800">Existing Roles</div>
        <table class="list-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $role): ?>
                    <tr>
                        <td><?= esc($role['name']) ?></td>
                        <td><?= esc($role['description'] ?? '-') ?></td>
                        <td><?= (int) ($role['is_active'] ?? 1) === 1 ? 'Active' : 'Inactive' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </article>
</main>
<?php $this->endSection() ?>