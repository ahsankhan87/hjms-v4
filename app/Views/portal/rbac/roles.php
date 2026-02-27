<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 max-w-2xl">
        <h3 class="text-lg font-semibold">Create Role</h3>
        <form method="post" action="<?= site_url('/app/rbac/roles') ?>" class="mt-4 space-y-3">
            <?= csrf_field() ?>
            <input name="name" required placeholder="Role name (e.g. manager)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <textarea name="description" rows="2" placeholder="Description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
            <button class="btn btn-md btn-primary" type="submit">Create Role</button>
            <a class="btn btn-md btn-secondary" href="<?= site_url('/app/rbac') ?>">Back</a>
        </form>
    </article>

    <article class="list-card">
        <div class="p-4 border-b border-gray-100 font-semibold">Existing Roles</div>
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