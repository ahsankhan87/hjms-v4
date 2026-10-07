<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors) || !empty($filterErrors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach (array_merge((array) ($errors ?? []), (array) ($filterErrors ?? [])) as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Package Sale</h3>
                <p class="text-xs text-slate-500">Manage package sales for pilgrims with attachment tracking.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="<?= site_url('/sales-categories') ?>" class="btn btn-md btn-secondary"><i class="fa-solid fa-tags"></i><span>Manage Categories</span></a>
                <a href="<?= site_url('/package-sales/credit-notes') ?>" class="btn btn-md btn-secondary"><i class="fa-solid fa-file-invoice-dollar"></i><span>Credit Notes</span></a>
                <a href="<?= site_url('/package-sales/add') ?>" class="btn btn-md btn-primary"><i class="fa-solid fa-plus"></i><span>Add Package Sale</span></a>
            </div>
        </div>
    </article>

    <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <form method="get" action="<?= site_url('/package-sales') ?>" class="grid gap-3 md:grid-cols-6">
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">From Date</label>
                <input type="date" name="from_date" value="<?= esc((string) ($filters['from_date'] ?? '')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">To Date</label>
                <input type="date" name="to_date" value="<?= esc((string) ($filters['to_date'] ?? '')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">Category</label>
                <select name="sales_category_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">All</option>
                    <?php foreach (($categories ?? []) as $category): ?>
                        <option value="<?= esc((string) $category['id']) ?>" <?= (string) ($filters['sales_category_id'] ?? '') === (string) $category['id'] ? 'selected' : '' ?>><?= esc((string) $category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">Customer Type</label>
                <select name="customer_type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">All</option>
                    <option value="agent" <?= (string) ($filters['customer_type'] ?? '') === 'agent' ? 'selected' : '' ?>>Agent</option>
                    <option value="walk_in" <?= (string) ($filters['customer_type'] ?? '') === 'walk_in' ? 'selected' : '' ?>>Walk-In</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">Payment Method</label>
                <select name="payment_method" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">All</option>
                    <?php foreach (['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'cheque' => 'Cheque', 'card' => 'Card', 'online' => 'Online', 'other' => 'Other'] as $value => $label): ?>
                        <option value="<?= esc($value) ?>" <?= (string) ($filters['payment_method'] ?? '') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">Status</label>
                <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">All</option>
                    <option value="posted" <?= (string) ($filters['status'] ?? '') === 'posted' ? 'selected' : '' ?>>Posted</option>
                    <option value="pending" <?= (string) ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="voided" <?= (string) ($filters['status'] ?? '') === 'voided' ? 'selected' : '' ?>>Voided</option>
                </select>
            </div>
            <div class="md:col-span-7 flex flex-wrap gap-2">
                <button type="submit" class="btn btn-md btn-primary"><i class="fa-solid fa-filter"></i><span>Apply Filters</span></button>
                <a href="<?= site_url('/package-sales') ?>" class="btn btn-md btn-secondary"><i class="fa-solid fa-rotate-right"></i><span>Reset</span></a>
                <div class="ml-auto rounded-lg bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700">Total: PKR <?= esc(number_format((float) ($totalAmount ?? 0), 2)) ?></div>
            </div>
        </form>
    </section>

    <div class="list-card overflow-auto">
        <table class="list-table">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-3 py-2 text-left">Invoice #</th>
                    <th class="px-3 py-2 text-left">Inv Date</th>
                    <th class="px-3 py-2 text-left">Category</th>
                    <th class="px-3 py-2 text-left">Pax Name</th>
                    <th class="px-3 py-2 text-left">Package Name</th>
                    <th class="px-3 py-2 text-left">Attachments</th>
                    <th class="px-3 py-2 text-left">Total</th>
                    <th class="px-3 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="8" class="px-3 py-6 text-center text-slate-500">No Package Sale Found.</td>
                    </tr>
                    <?php else: foreach ($rows as $row): ?>
                        <?php $amount = (float) ($row['amount'] ?? 0); ?>
                        <tr class="border-t border-slate-100">
                            <td class="px-3 py-2"><?= esc((string) (($row['invoice_no'] ?? '') !== '' ? $row['invoice_no'] : ('PS-' . $row['id']))) ?></td>
                            <td class="px-3 py-2 text-slate-700"><?= esc((string) ($row['sale_date'] ?? '-')) ?></td>
                            <td class="px-3 py-2 font-medium text-slate-800"><?= esc((string) ($row['category_name'] ?? '-')) ?></td>
                            <td class="px-3 py-2 text-slate-600"><?= esc((string) ($row['pax_display'] ?? '-')) ?></td>
                            <td class="px-3 py-2 text-slate-600"><?= esc((string) (($row['package_name'] ?? '') !== '' ? $row['package_name'] : '-')) ?></td>
                            <td class="px-3 py-2 text-slate-600">
                                <div class="flex flex-wrap gap-2">
                                    <?php if (!empty($row['attachment'])): ?>
                                        <a href="<?= esc(asset_upload_url((string) $row['attachment'], 'sales')) ?>" target="_blank" class="inline-flex items-center gap-1 rounded border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-50" title="Attachment 1">
                                            <i class="fa-solid fa-file"></i>
                                            <span>A1</span>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($row['attachment2'])): ?>
                                        <a href="<?= esc(asset_upload_url((string) $row['attachment2'], 'sales')) ?>" target="_blank" class="inline-flex items-center gap-1 rounded border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-50" title="Attachment 2">
                                            <i class="fa-solid fa-file"></i>
                                            <span>A2</span>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($row['attachment3'])): ?>
                                        <a href="<?= esc(asset_upload_url((string) $row['attachment3'], 'sales')) ?>" target="_blank" class="inline-flex items-center gap-1 rounded border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-50" title="Attachment 3">
                                            <i class="fa-solid fa-file"></i>
                                            <span>A3</span>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (empty($row['attachment']) && empty($row['attachment2']) && empty($row['attachment3'])): ?>
                                        <span class="text-slate-400">-</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-3 py-2 font-semibold text-slate-800 text-right" data-col="amount" data-value="<?= esc((string) $amount) ?>"><?= esc(number_format($amount, 2)) ?></td>
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <?php if ((string) ($row['customer_type'] ?? '') === 'agent' && (int) ($row['agent_id'] ?? 0) > 0 && (string) ($row['status'] ?? 'posted') !== 'voided'): ?>
                                        <a href="<?= site_url('/package-sales/credit-notes/add?sale_id=' . (int) $row['id']) ?>" class="icon-btn" title="Create Credit Note"><i class="fa-solid fa-file-circle-plus"></i></a>
                                    <?php endif; ?>
                                    <a href="<?= site_url('/package-sales/' . (int) $row['id'] . '/edit') ?>" class="icon-btn" title="Edit Package Sale"><i class="fa-solid fa-pen"></i></a>
                                    <form method="post" action="<?= site_url('/package-sales/delete') ?>" class="inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="sale_id" value="<?= esc((string) $row['id']) ?>">
                                        <button type="submit" class="icon-btn icon-btn-danger" onclick="return confirm('Delete this package sale?')" title="Delete Package Sale"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                <?php endforeach;
                endif; ?>
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-slate-300 bg-slate-100 text-sm font-semibold text-slate-800">
                    <td colspan="6" class="px-3 py-2 text-right">Total Amount</td>
                    <td id="sales-total-amount" class="px-3 py-2 text-right">0.00</td>
                    <td class="px-3 py-2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</main>
<script>
    (function() {
        function numberFormat(value) {
            return Number(value || 0).toLocaleString('en-PK', {
                maximumFractionDigits: 2,
                minimumFractionDigits: 2
            });
        }

        function getVisibleRows(table) {
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable && window.jQuery.fn.DataTable.isDataTable(table)) {
                return window.jQuery(table).DataTable().rows({
                    search: 'applied'
                }).nodes().toArray();
            }
            return Array.from(table.querySelectorAll('tbody tr:not(:last-child)'));
        }

        function computeSalesTotal() {
            var table = document.querySelector('.list-table');
            var rows = getVisibleRows(table);
            var total = 0;
            for (var i = 0; i < rows.length; i++) {
                var amountCell = rows[i].querySelector('[data-col="amount"]');
                if (amountCell) {
                    total += parseFloat(amountCell.getAttribute('data-value') || 0);
                }
            }
            var totalCell = document.getElementById('sales-total-amount');
            if (totalCell) {
                totalCell.textContent = numberFormat(total.toFixed(2));
            }
        }

        computeSalesTotal();
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
            window.jQuery(function($) {
                if ($.fn.dataTable.isDataTable('.list-table')) {
                    $(document).on('draw.dt', function() {
                        computeSalesTotal();
                    });
                }
            });
        }
    })();
</script>
<?php $this->endSection() ?>