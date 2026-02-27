<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="grid gap-6 lg:grid-cols-3">
        <article class="rounded-xl border border-slate-200 bg-white p-5 lg:col-span-1">
            <h3 class="text-lg font-semibold">Add Agent</h3>
            <form method="post" action="/app/agents" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <div>
                    <label class="text-sm font-medium">Code</label>
                    <input name="code" value="<?= esc(old('code')) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Name</label>
                    <input name="name" value="<?= esc(old('name')) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Email</label>
                    <input type="email" name="email" value="<?= esc(old('email')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Phone</label>
                    <input name="phone" value="<?= esc(old('phone')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Branch</label>
                    <select name="branch_id" class="js-select2 mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">None</option>
                        <?php foreach ($branches as $item): ?>
                            <option value="<?= esc($item['id']) ?>" <?= old('branch_id') == $item['id'] ? 'selected' : '' ?>><?= esc($item['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium">Commission Type</label>
                        <select name="commission_type" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="percentage" <?= old('commission_type') === 'percentage' ? 'selected' : '' ?>>Percentage</option>
                            <option value="fixed" <?= old('commission_type') === 'fixed' ? 'selected' : '' ?>>Fixed</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium">Commission</label>
                        <input name="commission_value" value="<?= esc(old('commission_value')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="0.00">
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium">Credit Limit</label>
                    <input name="credit_limit" value="<?= esc(old('credit_limit')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="0.00">
                </div>
                <button type="submit" class="btn btn-md btn-primary btn-block">Create Agent</button>
            </form>

            <hr class="my-5 border-slate-200">

            <h3 class="text-lg font-semibold">Update Agent</h3>
            <form method="post" action="/app/agents/update" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="number" name="agent_id" min="1" required placeholder="Agent ID" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="code" placeholder="New Code (optional)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="name" placeholder="New Name (optional)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input type="email" name="email" placeholder="New Email (optional)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="phone" placeholder="New Phone (optional)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <div class="grid grid-cols-2 gap-3">
                    <select name="commission_type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Commission Type</option>
                        <option value="percentage">Percentage</option>
                        <option value="fixed">Fixed</option>
                    </select>
                    <input name="commission_value" placeholder="Commission Value" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <input name="credit_limit" placeholder="Credit Limit" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <select name="branch_id" class="js-select2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Branch (optional)</option>
                    <?php foreach ($branches as $item): ?>
                        <option value="<?= esc($item['id']) ?>"><?= esc($item['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="is_active" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Status (optional)</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <button type="submit" class="btn btn-md btn-primary btn-block">Update Agent</button>
            </form>

            <hr class="my-5 border-slate-200">

            <h3 class="text-lg font-semibold">Delete Agent</h3>
            <form method="post" action="/app/agents/delete" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="number" name="agent_id" min="1" required placeholder="Agent ID" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <button type="submit" class="btn btn-md btn-danger btn-block">Delete Agent</button>
            </form>
        </article>

        <article class="list-card lg:col-span-2 overflow-auto">
            <h3 class="text-lg font-semibold mb-4">Agent List</h3>
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Code</th>
                        <th class="px-3 py-2 text-left">Name</th>
                        <th class="px-3 py-2 text-left">Phone</th>
                        <th class="px-3 py-2 text-left">Commission</th>
                        <th class="px-3 py-2 text-left">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="5" class="px-3 py-6 text-center text-slate-500">No agents found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2 font-medium"><?= esc($row['code']) ?></td>
                                <td class="px-3 py-2"><?= esc($row['name']) ?></td>
                                <td class="px-3 py-2"><?= esc($row['phone'] ?? '-') ?></td>
                                <td class="px-3 py-2"><?= esc($row['commission_type']) ?> <?= esc($row['commission_value']) ?></td>
                                <td class="px-3 py-2"><?= esc($row['current_balance']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </article>
    </section>
</main>
<?php $this->endSection() ?>