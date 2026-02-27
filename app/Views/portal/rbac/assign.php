<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>

    <section class="grid gap-6 lg:grid-cols-2">
        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-semibold">Assign Role to User</h3>
            <p class="mt-1 text-sm text-slate-500">Pick any user and role to assign access quickly.</p>
            <form method="post" action="<?= site_url('/app/rbac/user-roles') ?>" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <select name="user_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Select user</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= esc($user['id']) ?>\"><?= esc($user['name'] ?: $user['email']) ?> (<?= esc($user['email']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <select name="role_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Select role</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= esc($role['id']) ?>\"><?= esc($role['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="flex gap-2">
                    <button class="btn btn-md btn-primary" type="submit">Assign Role</button>
                    <a class="btn btn-md btn-secondary" href="<?= site_url('/app/rbac') ?>">Back</a>
                </div>
            </form>
        </article>

        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-semibold">Tips</h3>
            <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-slate-600">
                <li>Assign base roles first, then fine-tune role permissions in the Permissions page.</li>
                <li>Users can hold multiple roles; existing roles are not removed by new assignment.</li>
                <li>Use quick assign in the table below to map users faster.</li>
            </ul>
        </article>
    </section>

    <article class="list-card">
        <div class="p-4 border-b border-gray-100 font-semibold">User → Roles</div>
        <table class="list-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Roles</th>
                    <th>Quick Assign</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= esc($user['name'] ?: 'User #' . $user['id']) ?></td>
                        <td><?= esc($user['email']) ?></td>
                        <td>
                            <?php $items = $userRoles[(int) $user['id']] ?? []; ?>
                            <?= esc($items !== [] ? implode(', ', $items) : '-') ?>
                        </td>
                        <td>
                            <form method="post" action="<?= site_url('/app/rbac/user-roles') ?>" class="flex items-center gap-2">
                                <?= csrf_field() ?>
                                <input type="hidden" name="user_id" value="<?= esc($user['id']) ?>">
                                <select name="role_id" required class="min-w-[180px] rounded-lg border border-slate-300 px-2 py-1 text-sm">
                                    <option value="">Role</option>
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?= esc($role['id']) ?>\"><?= esc($role['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-sm btn-secondary" type="submit">Assign</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </article>
</main>
<?php $this->endSection() ?>