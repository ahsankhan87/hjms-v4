<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="space-y-3">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <h3 class="text-sm font-semibold text-slate-800">Edit User</h3>
            <p class="text-xs text-slate-500">Update user details, credentials, role assignment, and account status.</p>
        </article>

        <div class="grid gap-3 lg:grid-cols-3">
            <article class="rounded-xl border border-slate-200 bg-white p-4 lg:col-span-1">
                <h2 class="text-sm font-semibold text-slate-900">Update User</h2>
                <form method="post" action="<?= site_url('/users/update') ?>" class="mt-4 space-y-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="user_id" value="<?= esc($row['id']) ?>">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Name</label>
                        <input name="name" value="<?= esc(old('name', (string) ($row['name'] ?? ''))) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Email</label>
                        <input type="email" name="email" value="<?= esc(old('email', (string) ($row['email'] ?? ''))) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">New Password (Optional)</label>
                        <input type="password" name="password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Leave blank to keep current password">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                        <?php $statusValue = old('is_active', (string) ((int) ($row['is_active'] ?? 1))); ?>
                        <select name="is_active" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="1" <?= $statusValue === '1' ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= $statusValue === '0' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Assign Additional Role (Optional)</label>
                        <select name="role_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">No change</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= esc($role['id']) ?>" <?= old('role_id') == $role['id'] ? 'selected' : '' ?>><?= esc($role['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
                        <a href="<?= site_url('/users') ?>" class="btn btn-md btn-secondary">Back</a>
                        <button class="btn btn-md btn-primary" type="submit"><i class="fa-solid fa-check"></i><span>Update User</span></button>
                    </div>
                </form>

                <hr class="my-5 border-slate-200">

                <h2 class="text-sm font-semibold text-slate-900">Delete User</h2>
                <form method="post" action="<?= site_url('/users/delete') ?>" class="mt-4 space-y-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="user_id" value="<?= esc($row['id']) ?>">
                    <button class="btn btn-md btn-danger btn-block" onclick="return confirm('Delete this user?')"><i class="fa-solid fa-trash"></i><span>Delete User</span></button>
                </form>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-4 lg:col-span-2">
                <h3 class="text-sm font-semibold text-slate-900">User Details</h3>
                <dl class="mt-4 grid gap-3 text-sm text-slate-600 md:grid-cols-2">
                    <div>
                        <dt class="font-medium">User ID</dt>
                        <dd>#<?= esc($row['id']) ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium">Current Roles</dt>
                        <dd><?= esc(!empty($userRoles) ? implode(', ', $userRoles) : '-') ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium">Created At</dt>
                        <dd><?= esc($row['created_at'] ?? '-') ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium">Updated At</dt>
                        <dd><?= esc($row['updated_at'] ?? '-') ?></dd>
                    </div>
                </dl>
            </article>
        </div>
    </section>
</main>
<?php $this->endSection() ?>