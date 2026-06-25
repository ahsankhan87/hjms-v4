<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
        <h3 class="text-sm font-semibold text-slate-800">Edit Sales Category</h3>
        <p class="text-xs text-slate-500">Update category details for the active season.</p>
    </article>

    <article class="rounded-xl border border-slate-200 bg-white p-4">
        <form method="post" action="<?= site_url('/sales-categories/update') ?>" class="space-y-3">
            <?= csrf_field() ?>
            <input type="hidden" name="category_id" value="<?= esc((string) $row['id']) ?>">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Category Name</label>
                <input name="name" value="<?= esc(old('name', (string) $row['name'])) ?>" placeholder="Category Name" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Description</label>
                <textarea name="description" rows="3" placeholder="Optional description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc(old('description', (string) ($row['description'] ?? ''))) ?></textarea>
            </div>
            <?php $status = (string) old('is_active', (string) ($row['is_active'] ?? '1')); ?>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                <select name="is_active" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
                <a href="<?= site_url('/sales-categories') ?>" class="btn btn-md btn-secondary">Back</a>
                <button type="submit" class="btn btn-md btn-primary"><i class="fa-solid fa-check"></i><span>Update Category</span></button>
            </div>
        </form>
    </article>
</main>
<?php $this->endSection() ?>