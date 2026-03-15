<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="max-w-3xl">
        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="text-sm font-semibold text-slate-900">Update Agent</h2>
                <a href="<?= site_url('/agents/' . (int) ($row['id'] ?? 0) . '/ledger') ?>" class="btn btn-sm btn-secondary">Open Ledger</a>
            </div>
            <form method="post" action="<?= site_url('/agents/update') ?>" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="agent_id" value="<?= esc((string) ($row['id'] ?? '')) ?>">

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Code</label>
                        <input name="code" value="<?= esc(old('code', (string) ($row['code'] ?? ''))) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Name</label>
                        <input name="name" value="<?= esc(old('name', (string) ($row['name'] ?? ''))) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Email</label>
                        <input type="email" name="email" value="<?= esc(old('email', (string) ($row['email'] ?? ''))) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Phone</label>
                        <input name="phone" value="<?= esc(old('phone', (string) ($row['phone'] ?? ''))) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Branch</label>
                    <?php $branchValue = (string) old('branch_id', (string) ($row['branch_id'] ?? '')); ?>
                    <select name="branch_id" class="js-select2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">None</option>
                        <?php foreach ($branches as $item): ?>
                            <option value="<?= esc($item['id']) ?>" <?= $branchValue === (string) $item['id'] ? 'selected' : '' ?>><?= esc($item['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Commission Type</label>
                        <?php $commissionType = (string) old('commission_type', (string) ($row['commission_type'] ?? 'percentage')); ?>
                        <select name="commission_type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="percentage" <?= $commissionType === 'percentage' ? 'selected' : '' ?>>Percentage</option>
                            <option value="fixed" <?= $commissionType === 'fixed' ? 'selected' : '' ?>>Fixed</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Commission Value</label>
                        <input name="commission_value" value="<?= esc(old('commission_value', (string) ($row['commission_value'] ?? '0'))) ?>" placeholder="0.00" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Credit Limit</label>
                        <input name="credit_limit" value="<?= esc(old('credit_limit', (string) ($row['credit_limit'] ?? '0'))) ?>" placeholder="0.00" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                        <?php $statusValue = (string) old('is_active', (string) ($row['is_active'] ?? '1')); ?>
                        <select name="is_active" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="1" <?= $statusValue === '1' ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= $statusValue === '0' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <button class="btn btn-md btn-primary btn-block">Update Agent</button>
            </form>

            <hr class="my-5 border-slate-200">

            <h2 class="text-sm font-semibold text-slate-900">Delete Agent</h2>
            <form method="post" action="<?= site_url('/agents/delete') ?>" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="agent_id" value="<?= esc((string) ($row['id'] ?? '')) ?>">
                <button class="btn btn-md btn-danger btn-block" onclick="return confirm('Delete this agent?')">Delete Agent</button>
            </form>
        </article>
    </section>
</main>
<?php $this->endSection() ?>