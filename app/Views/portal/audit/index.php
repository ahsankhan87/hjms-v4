<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>

    <section class="rounded-xl border border-slate-200 bg-white p-5">
        <form method="get" action="<?= site_url('/app/audit') ?>" class="grid gap-4 md:grid-cols-3 lg:grid-cols-6">
            <div>
                <label class="text-sm font-medium">User</label>
                <select name="user_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">All</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= esc($user['id']) ?>" <?= ($filters['user_id'] ?? '') == (string) $user['id'] ? 'selected' : '' ?>><?= esc(($user['name'] ?: 'User #' . $user['id']) . ' (' . $user['email'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-sm font-medium">Method</label>
                <select name="http_method" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <?php $method = $filters['http_method'] ?? ''; ?>
                    <option value="" <?= $method === '' ? 'selected' : '' ?>>All</option>
                    <option value="POST" <?= $method === 'POST' ? 'selected' : '' ?>>POST</option>
                    <option value="PUT" <?= $method === 'PUT' ? 'selected' : '' ?>>PUT</option>
                    <option value="PATCH" <?= $method === 'PATCH' ? 'selected' : '' ?>>PATCH</option>
                    <option value="DELETE" <?= $method === 'DELETE' ? 'selected' : '' ?>>DELETE</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-medium">Status</label>
                <input name="status_code" value="<?= esc($filters['status_code'] ?? '') ?>" placeholder="200" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Path</label>
                <input name="path" value="<?= esc($filters['path'] ?? '') ?>" placeholder="/app/users" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">From</label>
                <input type="date" name="from_date" value="<?= esc($filters['from_date'] ?? '') ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">To</label>
                <input type="date" name="to_date" value="<?= esc($filters['to_date'] ?? '') ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div class="md:col-span-3 lg:col-span-6 flex gap-2">
                <button type="submit" class="btn btn-md btn-primary">Apply Filter</button>
                <a href="<?= site_url('/app/audit') ?>" class="btn btn-md btn-secondary">Reset</a>
            </div>
        </form>
    </section>

    <section class="list-card overflow-auto">
        <table class="list-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Method</th>
                    <th>Path</th>
                    <th>Action</th>
                    <th>Status</th>
                    <th>IP</th>
                    <th>Payload</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-slate-500">No audit logs found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= esc($row['created_at'] ?? '-') ?></td>
                            <td><?= esc(($row['user_email'] ?? '-') . (!empty($row['user_id']) ? ' (#' . $row['user_id'] . ')' : '')) ?></td>
                            <td><?= esc($row['http_method'] ?? '-') ?></td>
                            <td><?= esc($row['request_path'] ?? '-') ?></td>
                            <td><?= esc($row['action_label'] ?? '-') ?></td>
                            <td><?= esc((string) ($row['status_code'] ?? '-')) ?></td>
                            <td><?= esc($row['ip_address'] ?? '-') ?></td>
                            <td>
                                <?php if (!empty($row['payload_json'])): ?>
                                    <details>
                                        <summary class="cursor-pointer text-slate-600">View</summary>
                                        <pre class="mt-2 max-h-40 overflow-auto rounded bg-slate-50 p-2 text-xs text-slate-700"><?= esc($row['payload_json']) ?></pre>
                                    </details>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>
<?php $this->endSection() ?>