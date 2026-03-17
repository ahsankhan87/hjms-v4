<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="w-full space-y-3">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <h3 class="text-sm font-semibold text-slate-800">Create User</h3>
            <p class="text-xs text-slate-500">Create a new portal user and optionally assign a role.</p>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <form method="post" action="<?= site_url('/users') ?>" class="space-y-3">
                <?= csrf_field() ?>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Name</label>
                    <input name="name" value="<?= esc(old('name')) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Email</label>
                    <input type="email" name="email" value="<?= esc(old('email')) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Password</label>
                    <input type="password" name="password" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                    <select name="is_active" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="1" <?= old('is_active') === '0' ? '' : 'selected' ?>>Active</option>
                        <option value="0" <?= old('is_active') === '0' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Assign Role (Optional)</label>
                    <select name="role_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">None</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= esc($role['id']) ?>" <?= old('role_id') == $role['id'] ? 'selected' : '' ?>><?= esc($role['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <a href="<?= site_url('/users') ?>" class="btn btn-md btn-secondary">Cancel</a>
                    <button class="btn btn-md btn-primary" type="submit"><i class="fa-solid fa-check"></i><span>Create User</span></button>
                </div>
            </form>
        </article>
    </section>
</main>
<?php $this->endSection() ?>