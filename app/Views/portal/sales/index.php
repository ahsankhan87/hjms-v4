<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<?php
$componentLabels = [
    'visa' => 'Visa',
    'ticket' => 'Ticket',
    'hotel' => 'Hotel',
    'transport' => 'Transport',
    'package' => 'Package',
];
$componentIcons = [
    'visa' => 'fa-passport',
    'ticket' => 'fa-plane-departure',
    'hotel' => 'fa-hotel',
    'transport' => 'fa-bus',
    'package' => 'fa-box-open',
];
$creditNotePaths = [
    'visa' => '/sales/credit-notes/add',
    'hotel' => '/hotel-sales/credit-notes/add',
    'ticket' => '/ticket-sales/credit-notes/add',
    'package' => '/package-sales/credit-notes/add',
];
?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors) || !empty($filterErrors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach (array_merge((array) ($errors ?? []), (array) ($filterErrors ?? [])) as $err): ?><div><?= esc(is_array($err) ? implode(' ', $err) : (string) $err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Sale</h3>
                <p class="text-xs text-slate-500">Visa, ticket, hotel, transport and package sales in one entry.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="<?= site_url('/reports/sale-components') ?>" class="btn btn-md btn-secondary"><i class="fa-solid fa-chart-simple"></i><span>Components Report</span></a>
                <a href="<?= site_url('/sales-categories') ?>" class="btn btn-md btn-secondary"><i class="fa-solid fa-tags"></i><span>Manage Categories</span></a>
                <a href="<?= site_url('/sales/credit-notes') ?>" class="btn btn-md btn-secondary"><i class="fa-solid fa-file-invoice-dollar"></i><span>Credit Notes</span></a>
                <a href="<?= site_url('/sales/add') ?>" class="btn btn-md btn-primary"><i class="fa-solid fa-plus"></i><span>New Sale</span></a>
            </div>
        </div>
    </article>

    <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <?php foreach (($componentSummary ?? []) as $type => $summary): ?>
            <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                <div class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    <i class="fa-solid <?= esc($componentIcons[$type] ?? 'fa-circle') ?> text-emerald-600"></i>
                    <span><?= esc($componentLabels[$type] ?? ucfirst((string) $type)) ?> Issued</span>
                </div>
                <p class="mt-1 text-lg font-semibold text-slate-900"><?= esc((string) ($summary['count'] ?? 0)) ?></p>
                <p class="text-[11px] text-slate-500">PKR <?= esc(number_format((float) ($summary['total_pkr'] ?? 0), 2)) ?></p>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <form method="get" action="<?= site_url('/sales') ?>" class="grid gap-3 md:grid-cols-6">
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">From Date</label>
                <input type="date" name="from_date" value="<?= esc((string) ($filters['from_date'] ?? '')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">To Date</label>
                <input type="date" name="to_date" value="<?= esc((string) ($filters['to_date'] ?? '')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">Component</label>
                <select name="component" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">All</option>
                    <?php foreach (($saleTypes ?? []) as $value => $label): ?>
                        <option value="<?= esc($value) ?>" <?= (string) ($filters['component'] ?? '') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
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
                <label class="mb-1 block text-xs font-semibold text-slate-600">Status</label>
                <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">All</option>
                    <option value="posted" <?= (string) ($filters['status'] ?? '') === 'posted' ? 'selected' : '' ?>>Posted</option>
                    <option value="pending" <?= (string) ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="voided" <?= (string) ($filters['status'] ?? '') === 'voided' ? 'selected' : '' ?>>Voided</option>
                </select>
            </div>
            <div class="md:col-span-6 flex flex-wrap gap-2">
                <button type="submit" class="btn btn-md btn-primary"><i class="fa-solid fa-filter"></i><span>Apply Filters</span></button>
                <a href="<?= site_url('/sales') ?>" class="btn btn-md btn-secondary"><i class="fa-solid fa-rotate-right"></i><span>Reset</span></a>
                <div class="ml-auto rounded-lg bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700">Total (PKR): <?= esc(number_format((float) ($totalAmountPkr ?? 0), 2)) ?></div>
            </div>
        </form>
    </section>

    <div class="list-card overflow-auto">
        <table class="list-table">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-3 py-2 text-left">Invoice #</th>
                    <th class="px-3 py-2 text-left">Date</th>
                    <th class="px-3 py-2 text-left">Pax Name</th>
                    <th class="px-3 py-2 text-left">Components</th>
                    <th class="px-3 py-2 text-left">Remarks</th>
                    <th class="px-3 py-2 text-right">Total</th>
                    <th class="px-3 py-2 text-left">Attachments</th>
                    <th class="px-3 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="8" class="px-3 py-6 text-center text-slate-500">No sale found.</td>
                    </tr>
                    <?php else: foreach ($rows as $row): ?>
                        <?php
                        $amount = (float) ($row['amount'] ?? 0);
                        $currencyCode = strtoupper((string) ($row['currency'] ?? 'PKR'));
                        $pkrAmount = (float) ($row['amount_pkr_total'] ?? $amount);
                        $rowType = (string) ($row['sale_type'] ?? 'visa');
                        $counts = (array) ($row['component_counts'] ?? []);
                        $saleAttachments = array_values(array_filter([
                            (string) ($row['attachment'] ?? ''),
                            (string) ($row['attachment2'] ?? ''),
                            (string) ($row['attachment3'] ?? ''),
                        ]));
                        $itemAttachments = (array) ($row['item_attachments'] ?? []);
                        ?>
                        <tr class="border-t border-slate-100">
                            <td class="px-3 py-2"><a href="<?= site_url('/sales/' . (int) $row['id']) ?>" class="font-semibold text-emerald-700 hover:underline"><?= esc((string) (($row['invoice_no'] ?? '') !== '' ? $row['invoice_no'] : ('#' . $row['id']))) ?></a></td>
                            <td class="px-3 py-2 text-slate-700"><?= esc((string) ($row['sale_date'] ?? '-')) ?></td>
                            <td class="px-3 py-2 text-slate-600"><?= esc((string) ($row['pax_display'] ?? '-')) ?></td>
                            <td class="px-3 py-2">
                                <div class="flex flex-wrap gap-1">
                                    <?php foreach ($counts as $type => $count): ?>
                                        <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-medium text-slate-600">
                                            <i class="fa-solid <?= esc($componentIcons[$type] ?? 'fa-circle') ?> text-[10px]"></i>
                                            <?= esc($componentLabels[$type] ?? ucfirst((string) $type)) ?><?= (int) $count > 1 ? ' ×' . (int) $count : '' ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td class="px-3 py-2 text-slate-600"><?= esc((string) (($row['note'] ?? '') !== '' ? $row['note'] : '-')) ?></td>
                            <td class="px-3 py-2 text-right">
                                <div class="font-semibold text-slate-800" data-col="amount" data-value="<?= esc((string) $pkrAmount) ?>"><?= esc($currencyCode) ?> <?= esc(number_format($amount, 2)) ?></div>
                                <?php if ($currencyCode !== 'PKR'): ?>
                                    <div class="text-[11px] text-slate-500">PKR <?= esc(number_format($pkrAmount, 2)) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-2 text-slate-600">
                                <div class="flex flex-wrap gap-1">
                                    <?php foreach ($saleAttachments as $index => $attachment): ?>
                                        <a href="<?= esc(asset_upload_url($attachment, 'sales')) ?>" target="_blank" class="inline-flex items-center gap-1 rounded border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-50" title="Sale attachment">
                                            <i class="fa-solid fa-file"></i><span>A<?= esc((string) ($index + 1)) ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                    <?php foreach ($itemAttachments as $index => $itemAttachment): ?>
                                        <a href="<?= esc(asset_upload_url((string) $itemAttachment['file'], 'sales')) ?>" target="_blank" class="inline-flex items-center gap-1 rounded border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-50" title="<?= esc(ucfirst((string) $itemAttachment['type'])) ?> attachment">
                                            <i class="fa-solid <?= esc($componentIcons[$itemAttachment['type']] ?? 'fa-file') ?>"></i><span><?= esc(ucfirst((string) $itemAttachment['type'])) ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                    <?php if ($saleAttachments === [] && $itemAttachments === []): ?>
                                        <span class="text-slate-400">-</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <?php if (isset($creditNotePaths[$rowType]) && (string) ($row['customer_type'] ?? '') === 'agent' && (int) ($row['agent_id'] ?? 0) > 0 && (string) ($row['status'] ?? 'posted') !== 'voided'): ?>
                                        <a href="<?= site_url($creditNotePaths[$rowType] . '?sale_id=' . (int) $row['id']) ?>" class="icon-btn" title="Create Credit Note"><i class="fa-solid fa-file-circle-plus"></i></a>
                                    <?php endif; ?>
                                    <a href="<?= site_url('/sales/' . (int) $row['id']) ?>" class="icon-btn" title="View Sale"><i class="fa-solid fa-eye"></i></a>
                                    <a href="<?= site_url('/sales/' . (int) $row['id'] . '/edit') ?>" class="icon-btn" title="Edit Sale"><i class="fa-solid fa-pen"></i></a>
                                    <form method="post" action="<?= site_url('/sales/delete') ?>" class="inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="sale_id" value="<?= esc((string) $row['id']) ?>">
                                        <button type="submit" class="icon-btn icon-btn-danger" onclick="return confirm('Delete this sale?')" title="Delete Sale"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                <?php endforeach;
                endif; ?>
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-slate-300 bg-slate-100 text-sm font-semibold text-slate-800">
                    <td colspan="5" class="px-3 py-2 text-right">Total Amount (PKR)</td>
                    <td id="sales-total-amount" class="px-3 py-2 text-right">0.00</td>
                    <td colspan="2" class="px-3 py-2"></td>
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

            return Array.prototype.slice.call(table.querySelectorAll('tbody tr'));
        }

        function refreshSalesTotalAmount() {
            var table = document.querySelector('table.list-table');
            if (!table) {
                return;
            }

            var totalAmount = 0;
            getVisibleRows(table).forEach(function(row) {
                var amountCell = row.querySelector('[data-col="amount"]');
                if (!amountCell || !amountCell.dataset) {
                    return;
                }

                totalAmount += Number(amountCell.dataset.value || 0);
            });

            var totalTarget = document.getElementById('sales-total-amount');
            if (totalTarget) {
                totalTarget.textContent = numberFormat(totalAmount);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            refreshSalesTotalAmount();

            if (window.jQuery) {
                var $table = window.jQuery('table.list-table').first();
                if ($table.length) {
                    $table.on('draw.dt search.dt page.dt order.dt', refreshSalesTotalAmount);
                    setTimeout(refreshSalesTotalAmount, 0);
                }
            }
        });
    }());
</script>
<?php $this->endSection() ?>
