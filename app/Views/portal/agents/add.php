<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="max-w-3xl">
        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h2 class="text-sm font-semibold text-slate-900">Add Agent</h2>
            <form method="post" action="<?= site_url('/agents') ?>" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Code</label>
                        <input name="code" value="<?= esc(old('code')) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Name</label>
                        <input name="name" value="<?= esc(old('name')) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Email</label>
                        <input type="email" name="email" value="<?= esc(old('email')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Phone</label>
                        <input name="phone" value="<?= esc(old('phone')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Branch</label>
                    <select name="branch_id" class="js-select2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                        <option value="">None</option>
                        <?php foreach ($branches as $item): ?>
                            <option value="<?= esc($item['id']) ?>" <?= (string) old('branch_id') === (string) $item['id'] ? 'selected' : '' ?>><?= esc($item['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Commission Type</label>
                        <?php $commissionType = (string) old('commission_type', 'percentage'); ?>
                        <select name="commission_type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                            <option value="percentage" <?= $commissionType === 'percentage' ? 'selected' : '' ?>>Percentage</option>
                            <option value="fixed" <?= $commissionType === 'fixed' ? 'selected' : '' ?>>Fixed</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Commission Value</label>
                        <input name="commission_value" value="<?= esc(old('commission_value')) ?>" placeholder="0.00" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Credit Limit</label>
                    <input name="credit_limit" value="<?= esc(old('credit_limit')) ?>" placeholder="0.00" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                </div>
                <button class="btn btn-md btn-primary btn-block">Create Agent</button>
            </form>
        </article>
    </section>
</main>
<?php $this->endSection() ?>