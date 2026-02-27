<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="max-w-3xl">
        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h2 class="text-sm font-semibold text-slate-900">Add User</h2>
            <form method="post" action="<?= site_url('/app/users') ?>" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Name</label>
                    <input name="name" value="<?= esc(old('name')) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Email</label>
                    <input type="email" name="email" value="<?= esc(old('email')) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Password</label>
                    <input type="password" name="password" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                    <select name="is_active" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                        <option value="1" <?= old('is_active') === '0' ? '' : 'selected' ?>>Active</option>
                        <option value="0" <?= old('is_active') === '0' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Assign Role (Optional)</label>
                    <select name="role_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                        <option value="">None</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= esc($role['id']) ?>" <?= old('role_id') == $role['id'] ? 'selected' : '' ?>><?= esc($role['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn btn-md btn-primary btn-block">Create User</button>
            </form>
        </article>
    </section>
</main>
<?php $this->endSection() ?>