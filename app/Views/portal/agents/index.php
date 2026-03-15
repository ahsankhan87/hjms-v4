<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>
    <?php
    $branchMap = [];
    foreach ($branches as $item) {
        $branchMap[(int) $item['id']] = (string) $item['name'];
    }
    ?>

    <section class="grid gap-6 lg:grid-cols-1">
        <article class="lg:col-span-1">
            <div class="list-toolbar">
                <div class="flex space-x-3">
                    <a href="<?= site_url('/agents/add') ?>" class="btn btn-md btn-primary">
                        <i class="ri-user-add-line mr-2"></i>Add Agent
                    </a>
                </div>
            </div>
            <div class="list-card">
                <table class="list-table">
                    <thead>
                        <tr>
                            <th>Agent</th>
                            <th>Code</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Branch</th>
                            <th>Commission</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rows)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-slate-500">No agents found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rows as $row): ?>
                                <?php $statusActive = (int) ($row['is_active'] ?? 0) === 1; ?>
                                <tr>
                                    <td>
                                        <div>
                                            <p class="font-semibold text-gray-800"><?= esc((string) ($row['name'] ?? '-')) ?></p>
                                        </div>
                                    </td>
                                    <td><?= esc((string) ($row['code'] ?? '-')) ?></td>
                                    <td><?= esc((string) ($row['email'] ?? '-')) ?></td>
                                    <td><?= esc((string) ($row['phone'] ?? '-')) ?></td>
                                    <td><?= esc($branchMap[(int) ($row['branch_id'] ?? 0)] ?? '-') ?></td>
                                    <td><?= esc((string) ($row['commission_type'] ?? 'percentage')) ?> <?= esc((string) ($row['commission_value'] ?? '0')) ?></td>
                                    <td><?= esc((string) ($row['current_balance'] ?? '0')) ?></td>
                                    <td>
                                        <?php if ($statusActive): ?>
                                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">Active</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="flex space-x-2">
                                            <a href="<?= site_url('/agents/' . (int) $row['id'] . '/ledger') ?>" class="icon-btn" title="Ledger">
                                                <i class="fa-solid fa-book"></i>
                                            </a>
                                            <a href="<?= site_url('/agents/' . (int) $row['id'] . '/edit') ?>" class="icon-btn" title="View / Edit">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <form method="post" action="<?= site_url('/agents/delete') ?>" class="inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="agent_id" value="<?= esc($row['id']) ?>">
                                                <button type="submit" class="icon-btn icon-btn-danger" onclick="return confirm('Delete this agent?')" title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="list-footer">
                    <p>Showing 1-<?= esc(count($rows)) ?> of <?= esc(count($rows)) ?> agents</p>
                    <div class="flex space-x-2">
                        <button type="button" class="btn btn-sm btn-secondary">Previous</button>
                        <button type="button" class="btn btn-sm btn-primary">1</button>
                        <button type="button" class="btn btn-sm btn-secondary">Next</button>
                    </div>
                </div>
            </div>
        </article>
    </section>
</main>
<?php $this->endSection() ?>