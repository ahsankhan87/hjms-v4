<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="space-y-4">
        <div class="list-toolbar">
            <div class="flex space-x-3">
                <a href="<?= site_url('/app/visas/add') ?>" class="btn btn-md btn-primary">
                    <i class="fa-solid fa-plus mr-2"></i>Add Visa
                </a>
            </div>
        </div>

        <form method="post" action="<?= site_url('/app/visas/bulk-status') ?>" class="list-card overflow-auto">
            <?= csrf_field() ?>
            <div class="flex flex-col gap-3 p-4 border-b border-slate-100 md:flex-row md:items-end">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Bulk Status</label>
                    <select name="status" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select status</option>
                        <?php foreach (['draft', 'submitted', 'approved', 'rejected'] as $status): ?>
                            <option value="<?= esc($status) ?>"><?= esc(ucfirst($status)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Submission Date</label>
                    <input type="date" name="submission_date" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Approval Date</label>
                    <input type="date" name="approval_date" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="flex-1">
                    <label class="mb-1 block text-xs font-medium text-slate-600">Rejection Reason</label>
                    <input type="text" name="rejection_reason" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Used when status = rejected">
                </div>
                <div>
                    <button type="submit" class="btn btn-md btn-primary">Bulk Update</button>
                </div>
            </div>

            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">
                            <input type="checkbox" id="selectAllVisas" class="h-4 w-4 rounded border-slate-300">
                        </th>
                        <th class="px-3 py-2 text-left">ID</th>
                        <th class="px-3 py-2 text-left">Pilgrim</th>
                        <th class="px-3 py-2 text-left">Booking</th>
                        <th class="px-3 py-2 text-left">Visa No</th>
                        <th class="px-3 py-2 text-left">Type</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Submission</th>
                        <th class="px-3 py-2 text-left">Approval</th>
                        <th class="px-3 py-2 text-left">File</th>
                        <th class="px-3 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="11" class="px-3 py-6 text-center text-slate-500">No visa records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2">
                                    <input type="checkbox" name="visa_ids[]" value="<?= esc($row['id']) ?>" class="visa-check h-4 w-4 rounded border-slate-300">
                                </td>
                                <td class="px-3 py-2 font-medium">#<?= esc($row['id']) ?></td>
                                <td class="px-3 py-2">
                                    <?php $fullName = trim(((string) ($row['first_name'] ?? '')) . ' ' . ((string) ($row['last_name'] ?? ''))); ?>
                                    <?= esc($fullName !== '' ? $fullName : ('#' . $row['pilgrim_id'])) ?>
                                    <?php if (!empty($row['passport_no'])): ?>
                                        <div class="text-xs text-slate-500"><?= esc($row['passport_no']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-2"><?= esc($row['booking_no'] ?? '-') ?></td>
                                <td class="px-3 py-2"><?= esc($row['visa_no'] ?? '-') ?></td>
                                <td class="px-3 py-2"><?= esc($row['visa_type']) ?></td>
                                <td class="px-3 py-2"><?= esc($row['status']) ?></td>
                                <td class="px-3 py-2"><?= esc($row['submission_date'] ?: '-') ?></td>
                                <td class="px-3 py-2"><?= esc($row['approval_date'] ?: '-') ?></td>
                                <td class="px-3 py-2"><?= esc($row['visa_file_name'] ?: '-') ?></td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center space-x-2">
                                        <a href="<?= site_url('/app/visas/' . (int) $row['id'] . '/edit') ?>" class="icon-btn" title="Edit Visa">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form method="post" action="<?= site_url('/app/visas/delete') ?>" class="inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="visa_id" value="<?= esc($row['id']) ?>">
                                            <button type="submit" class="icon-btn icon-btn-danger" onclick="return confirm('Delete this visa record?')" title="Delete Visa">
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
        </form>
    </section>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAllVisas');
        const checks = document.querySelectorAll('.visa-check');

        if (!selectAll) {
            return;
        }

        selectAll.addEventListener('change', function() {
            checks.forEach(function(box) {
                box.checked = selectAll.checked;
            });
        });
    });
</script>
<?php $this->endSection() ?>