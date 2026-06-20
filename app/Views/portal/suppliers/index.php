<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700\"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700\"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700\"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="space-y-3">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Suppliers</h3>
                    <p class="text-xs text-slate-500">Manage supplier records and view supplier ledgers.</p>
                </div>
                <a href="<?= site_url('/suppliers/add') ?>" class="btn btn-md btn-primary"><i class="fa-solid fa-plus"></i><span>Add Supplier</span></a>
            </div>
        </article>

        <div class="list-card overflow-auto">
            <h3 class="px-4 pt-4 text-sm font-semibold text-slate-800">Suppliers</h3>
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">ID</th>
                        <th class="px-3 py-2 text-left">Name</th>
                        <th class="px-3 py-2 text-left">Type</th>
                        <th class="px-3 py-2 text-left">Contact</th>
                        <th class="px-3 py-2 text-left">Phone</th>
                        <th class="px-3 py-2 text-left">Balance</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="8" class="px-3 py-6 text-center text-slate-500">No suppliers found.</td>
                        </tr>
                        <?php else: foreach ($rows as $row): ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2">#<?= esc((string) $row['id']) ?></td>
                                <td class="px-3 py-2 font-medium"><?= esc((string) $row['supplier_name']) ?></td>
                                <td class="px-3 py-2"><?= esc(ucfirst((string) $row['supplier_type'])) ?></td>
                                <td class="px-3 py-2"><?= esc((string) ($row['contact_person'] ?? '-')) ?></td>
                                <td class="px-3 py-2"><?= esc((string) ($row['phone'] ?? '-')) ?></td>
                                <td class="px-3 py-2">
                                    <?php
                                    $balance = (float) ($row['closing_balance'] ?? ($row['opening_balance'] ?? 0));
                                    $balanceClass = 'text-slate-600';
                                    if ($balance > 0) {
                                        $balanceClass = 'text-emerald-700';
                                    } elseif ($balance < 0) {
                                        $balanceClass = 'text-rose-700';
                                    }
                                    ?>
                                    <span class="inline-flex items-center rounded-full <?= $balance > 0 ? 'bg-emerald-100' : ($balance < 0 ? 'bg-rose-100' : 'bg-slate-100') ?> px-3 py-1 text-xs font-semibold <?= $balanceClass ?>" data-col="balance" data-value="<?= esc((string) $balance) ?>">
                                        <?= $balance > 0 ? '+' : '' ?><?= esc(number_format($balance, 2)) ?>
                                    </span>
                                </td>
                                <td class="px-3 py-2"><?= ((int) ($row['is_active'] ?? 0) === 1) ? 'Active' : 'Inactive' ?></td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center space-x-2">
                                        <a href="<?= site_url('/suppliers/' . (int) $row['id'] . '/ledger') ?>" class="btn btn-sm btn-secondary">Ledger</a>
                                        <a href="<?= site_url('/suppliers/' . (int) $row['id'] . '/edit') ?>" class="icon-btn" title="Edit Supplier"><i class="fa-solid fa-pen"></i></a>
                                        <form method="post" action="<?= site_url('/suppliers/delete') ?>" class="inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="supplier_id" value="<?= esc((string) $row['id']) ?>">
                                            <button type="submit" class="icon-btn icon-btn-danger" onclick="return confirm('Delete this supplier?')" title="Delete Supplier"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-300 bg-slate-100 text-sm font-semibold text-slate-800">
                        <td colspan="5" class="px-3 py-2 text-right">Total Balance</td>
                        <td id="suppliers-total-balance" class="px-3 py-2 text-left">0.00</td>
                        <td colspan="2" class="px-3 py-2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </section>
</main>
<script>
    (function() {
        function numberFormat(value) {
            return Number(value || 0).toLocaleString('en-PK', {
                maximumFractionDigits: 2,
                minimumFractionDigits: 2
            });
        }

        function refreshSupplierBalanceTotal() {
            var target = document.getElementById('suppliers-total-balance');
            if (!target) {
                return;
            }

            var totalBalance = 0;

            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
                var $table = window.jQuery('table.list-table').first();
                if ($table.length && window.jQuery.fn.DataTable.isDataTable($table[0])) {
                    var api = $table.DataTable();
                    var nodes = api.rows({
                        search: 'applied'
                    }).nodes().to$();
                    nodes.each(function() {
                        var el = this.querySelector('[data-col="balance"]');
                        if (!el || !el.dataset) {
                            return;
                        }
                        totalBalance += Number(el.dataset.value || 0);
                    });

                    target.textContent = numberFormat(totalBalance);
                    return;
                }
            }

            var items = document.querySelectorAll('[data-col="balance"]');
            items.forEach(function(el) {
                totalBalance += Number((el.dataset && el.dataset.value) ? el.dataset.value : 0);
            });
            target.textContent = numberFormat(totalBalance);
        }

        document.addEventListener('DOMContentLoaded', function() {
            refreshSupplierBalanceTotal();

            if (!window.jQuery) {
                return;
            }

            var $table = window.jQuery('table.list-table').first();
            if ($table.length) {
                $table.on('draw.dt search.dt page.dt order.dt', refreshSupplierBalanceTotal);
            }
            setTimeout(refreshSupplierBalanceTotal, 0);
        });
    }());
</script>
<?php $this->endSection() ?>