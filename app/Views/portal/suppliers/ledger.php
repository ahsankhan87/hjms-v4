<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<style>
    .ledger-header {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    }

    .ledger-stat {
        transition: all 0.2s ease;
    }

    .ledger-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px -5px rgba(0, 0, 0, 0.1);
    }

    .modal-open {
        overflow: hidden;
    }

    .loader {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid rgba(16, 185, 129, 0.2);
        border-top-color: #10b981;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>
<main class="space-y-2">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><i class="fa-solid fa-check-circle mr-2"></i><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><i class="fa-solid fa-exclamation-circle mr-2"></i><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><i class="fa-solid fa-warning mr-2"></i><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="space-y-2">
        <!-- Header with Supplier Info & Quick Stats -->
        <article class="ledger-header rounded-xl border border-slate-200 bg-gradient-to-br from-slate-50 to-white p-3 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white shadow-sm">
                            <i class="fa-solid fa-handshake text-sm"></i>
                        </div>
                        <div>
                            <h1 class="text-lg font-bold text-slate-900"><?= esc((string) ($supplier['supplier_name'] ?? 'N/A')) ?></h1>
                            <p class="text-xs text-slate-500">Type: <span class="font-semibold text-slate-700"><?= esc(ucfirst((string) ($supplier['supplier_type'] ?? 'N/A'))) ?></span></p>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div class="ledger-stat text-center rounded-lg bg-white border border-slate-200 p-2.5 shadow-sm">
                        <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-wide">Opening</p>
                        <p class="mt-1 text-lg font-bold text-slate-800"><?= esc(number_format((float) ($supplier['opening_balance'] ?? 0), 2)) ?></p>
                    </div>
                    <div class="ledger-stat text-center rounded-lg <?= ((float) ($closingBalance ?? 0)) >= 0 ? 'bg-emerald-50 border-emerald-200' : 'bg-rose-50 border-rose-200' ?> border p-2.5 shadow-sm">
                        <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-wide">Closing</p>
                        <p class="mt-1 text-lg font-bold <?= ((float) ($closingBalance ?? 0)) >= 0 ? 'text-emerald-700' : 'text-rose-700' ?>"><?= esc(number_format((float) ($closingBalance ?? 0), 2)) ?></p>
                    </div>
                    <div class="ledger-stat text-center rounded-lg bg-sky-50 border border-sky-200 p-2.5 shadow-sm">
                        <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-wide">SAR Balance</p>
                        <p class="mt-1 text-lg font-bold text-sky-700"><?= esc(number_format((float) ($sarClosingBalance ?? 0), 2)) ?></p>
                    </div>
                </div>
            </div>
        </article>

        <!-- Filter & Action Bar -->
        <article class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label for="from" class="mb-1 block text-xs font-semibold text-slate-700">From</label>
                    <input id="from" type="date" value="<?= esc((string) ($filterFrom ?? '')) ?>" class="filter-input rounded-lg border border-slate-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                </div>
                <div>
                    <label for="to" class="mb-1 block text-xs font-semibold text-slate-700">To</label>
                    <input id="to" type="date" value="<?= esc((string) ($filterTo ?? '')) ?>" class="filter-input rounded-lg border border-slate-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                </div>
                <button onclick="applyLedgerFilter()" class="btn btn-sm btn-primary inline-flex items-center gap-1 px-2.5 py-1.5">
                    <i class="fa-solid fa-filter text-xs"></i><span class="text-xs">Filter</span>
                </button>
                <button onclick="resetLedgerFilter()" class="btn btn-sm btn-secondary inline-flex items-center gap-1 px-2.5 py-1.5">
                    <i class="fa-solid fa-rotate-left text-xs"></i><span class="text-xs">Reset</span>
                </button>
                <div class="flex-1"></div>
                <a href="<?= site_url('/admin/suppliers/' . (int) ($supplier['id'] ?? 0) . '/ledger/print?from=' . urlencode((string) ($filterFrom ?? '')) . '&to=' . urlencode((string) ($filterTo ?? ''))) ?>" target="_blank" class="btn btn-secondary inline-flex items-center gap-1 px-2.5 py-1.5">
                    <i class="fa-solid fa-print text-xs"></i><span class="text-xs">Print</span>
                </a>
                <a href="<?= site_url('/admin/suppliers/' . (int) ($supplier['id'] ?? 0) . '/ledger/print?autoprint=1&from=' . urlencode((string) ($filterFrom ?? '')) . '&to=' . urlencode((string) ($filterTo ?? ''))) ?>" target="_blank" class="btn btn-secondary inline-flex items-center gap-1 px-2.5 py-1.5">
                    <i class="fa-solid fa-file-pdf text-xs"></i><span class="text-xs">PDF</span>
                </a>
                <button id="addEntryBtn" class="btn btn-primary inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold shadow-lg hover:shadow-xl transition-shadow">
                    <i class="fa-solid fa-plus text-sm"></i><span>Add Entry</span>
                </button>
            </div>
        </article>

        <!-- Ledger Table -->
        <article class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table id="ledgerTable" class="w-full text-xs list-table">
                    <thead class="bg-gradient-to-r from-slate-100 to-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-2 py-1 text-left font-semibold text-slate-700 text-[11px]">S#</th>
                            <th class="px-2 py-1 text-left font-semibold text-slate-700 text-[11px]">Date</th>
                            <th class="px-2 py-1 text-left font-semibold text-slate-700 text-[11px]">Category</th>
                            <th class="px-2 py-1 text-left font-semibold text-slate-700 text-[11px]">Description</th>
                            <th class="px-1.5 py-1 text-center font-semibold text-slate-700 text-[11px]">Currency</th>
                            <th class="px-1.5 py-1 text-right font-semibold text-slate-700 text-[11px]">Debit (PKR)</th>
                            <th class="px-1.5 py-1 text-right font-semibold text-slate-700 text-[11px]">Credit (PKR)</th>
                            <th class="px-1.5 py-1 text-right font-semibold text-slate-700 text-[11px]">Balance (PKR)</th>
                            <th class="px-1.5 py-1 text-right font-semibold text-slate-700 text-[11px]">Debit (SAR)</th>
                            <th class="px-1.5 py-1 text-right font-semibold text-slate-700 text-[11px]">Credit (SAR)</th>
                            <th class="px-1.5 py-1 text-right font-semibold text-slate-700 text-[11px]">Balance (SAR)</th>
                            <th class="px-1.5 py-1 text-center font-semibold text-slate-700 text-[11px]">Attach</th>
                            <th class="px-1.5 py-1 text-center font-semibold text-slate-700 text-[11px]">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (!empty($rows)): $sr = 1;
                            foreach ($rows as $row): ?>
                                <?php
                                $rowCurrency = strtoupper((string) ($row['currency'] ?? 'PKR'));
                                $rowCategory = trim((string) ($row['category_name'] ?? ''));
                                $debit = (float) ($row['debit_amount'] ?? 0);
                                $credit = (float) ($row['credit_amount'] ?? 0);
                                $balance = (float) ($row['running_balance'] ?? 0);
                                ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-2 py-0.5 text-slate-600"><?= $sr++ ?></td>
                                    <td class="px-2 py-0.5 text-slate-700"><?= esc((string) ($row['entry_date'] ?? '')) ?></td>
                                    <td class="px-2 py-0.5">
                                        <?php if ($rowCategory !== ''): ?>
                                            <span class="inline-block px-1.5 py-0.25 rounded-full bg-slate-100 text-[9px] font-semibold text-slate-700"><?= esc($rowCategory) ?></span>
                                        <?php else: ?>
                                            <span class="text-slate-400">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-2 py-0.5 text-slate-700"><?= esc((string) ($row['description'] ?? '-')) ?></td>
                                    <td class="px-1.5 py-0.5 text-center">
                                        <span class="inline-block px-1 py-0.25 rounded bg-slate-100 text-[8px] font-bold text-slate-700"><?= esc($rowCurrency) ?></span>
                                        <?php if ($rowCurrency === 'SAR'): ?>
                                            <div class="text-[8px] text-slate-500 mt-0.5">@ <?= esc(number_format((float) ($row['exchange_rate'] ?? 1), 4)) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-1.5 py-0.5 text-right <?= $debit > 0 ? 'font-bold text-rose-700' : 'text-slate-600' ?>"><?= esc(number_format($debit, 2)) ?></td>
                                    <td class="px-1.5 py-0.5 text-right <?= $credit > 0 ? 'font-bold text-emerald-700' : 'text-slate-600' ?>"><?= esc(number_format($credit, 2)) ?></td>
                                    <td class="px-1.5 py-0.5 text-right font-bold <?= $balance < 0 ? 'text-rose-700' : 'text-slate-900' ?>"><?= esc(number_format($balance, 2)) ?></td>
                                    <td class="px-1.5 py-0.5 text-right text-slate-600"><?= esc(number_format((float) ($row['sar_debit_amount'] ?? 0), 2)) ?></td>
                                    <td class="px-1.5 py-0.5 text-right text-slate-600"><?= esc(number_format((float) ($row['sar_credit_amount'] ?? 0), 2)) ?></td>
                                    <td class="px-1.5 py-0.5 text-right font-semibold text-slate-700"><?= esc(number_format((float) ($row['sar_running_balance'] ?? 0), 2)) ?></td>
                                    <td class="px-1.5 py-0.5 text-center">
                                        <?php if (!empty($row['attachment'])): ?>
                                            <a href="<?= site_url('/admin/suppliers/attachment/download/' . esc((string) $row['attachment'])) ?>" class="inline-flex items-center gap-0.5 px-1 py-0.25 rounded bg-sky-100 text-sky-700 hover:bg-sky-200 transition-colors text-[8px] font-semibold">
                                                <i class="fa-solid fa-download text-[7px]"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-slate-300">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-1.5 py-0.5 text-center">
                                        <div class="flex items-center justify-center gap-0.5">
                                            <button type="button" class="icon-btn text-indigo-600 hover:bg-indigo-100" onclick="editLedgerEntry(<?= (int) ($row['id'] ?? 0) ?>, <?= esc(json_encode($row)) ?>)" title="Edit">
                                                <i class="fa-solid fa-pen text-[9px]"></i>
                                            </button>
                                            <form method="post" action="<?= site_url('/admin/suppliers/ledger/delete') ?>" class="inline" onsubmit="return confirm('Delete this entry permanently?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="supplier_id" value="<?= esc((string) $supplier['id']) ?>">
                                                <input type="hidden" name="entry_id" value="<?= esc((string) ($row['id'] ?? '0')) ?>">
                                                <button type="submit" class="icon-btn text-rose-600 hover:bg-rose-100" title="Delete">
                                                    <i class="fa-solid fa-trash text-[9px]"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="13" class="px-2 py-3 text-center text-slate-500">
                                    <i class="fa-solid fa-inbox text-xl mb-1 opacity-20"></i>
                                    <p class="text-xs">No ledger entries found.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="bg-slate-50 border-t-2 border-slate-300">
                        <tr>
                            <td colspan="5" class="px-2 py-1 text-right font-bold text-slate-800 text-[11px]">Totals</td>
                            <td class="px-1.5 py-1 text-right font-bold text-rose-700 text-xs"><?= esc(number_format((float) ($totalDebit ?? 0), 2)) ?></td>
                            <td class="px-1.5 py-1 text-right font-bold text-emerald-700 text-xs"><?= esc(number_format((float) ($totalCredit ?? 0), 2)) ?></td>
                            <td class="px-1.5 py-1 text-right font-bold text-slate-900 text-xs"><?= esc(number_format((float) ($closingBalance ?? 0), 2)) ?></td>
                            <td class="px-1.5 py-1 text-right font-bold text-slate-700 text-xs"><?= esc(number_format((float) ($sarTotalDebit ?? 0), 2)) ?></td>
                            <td class="px-1.5 py-1 text-right font-bold text-slate-700 text-xs"><?= esc(number_format((float) ($sarTotalCredit ?? 0), 2)) ?></td>
                            <td class="px-1.5 py-1 text-right font-bold text-slate-900 text-xs"><?= esc(number_format((float) ($sarClosingBalance ?? 0), 2)) ?></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </article>
    </section>
</main>

<!-- Add/Edit Entry Modal -->
<div id="entryModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden">
    <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="sticky top-0 z-10 border-b border-slate-200 bg-white px-4 py-3 flex items-center justify-between">
            <div>
                <h2 id="modalTitle" class="text-lg font-bold text-slate-900">Add Ledger Entry</h2>
                <p class="text-[10px] text-slate-500 mt-0.5">Supplier: <?= esc((string) ($supplier['supplier_name'] ?? 'N/A')) ?></p>
            </div>
            <button type="button" onclick="closeEntryModal()" class="text-slate-400 hover:text-slate-600 transition">
                <i class="fa-solid fa-times text-lg"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <form id="entryForm" method="post" action="<?= site_url('/admin/suppliers/ledger') ?>" enctype="multipart/form-data" class="p-4 space-y-3">
            <?= csrf_field() ?>
            <input type="hidden" name="supplier_id" value="<?= esc((string) $supplier['id']) ?>">
            <input type="hidden" id="isEditMode" value="0">
            <input type="hidden" id="editEntryId" name="entry_id" value="">

            <!-- Row 1: Date & Type -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Entry Date *</label>
                    <input type="date" id="entryDate" name="entry_date" value="<?= date('Y-m-d') ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Entry Type *</label>
                    <select id="entryType" name="entry_type" required class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                        <option value="bill">Bill (Credit)</option>
                        <option value="payment">Payment (Debit)</option>
                        <option value="adjustment">Adjustment</option>
                        <option value="refund">Refund</option>
                    </select>
                </div>
            </div>

            <!-- Row 2: Category & Currency -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Category</label>
                    <select id="category" name="category_id" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                        <option value="">Select Category</option>
                        <?php foreach (($categories ?? []) as $category): ?>
                            <option value="<?= esc((string) $category['id']) ?>"><?= esc((string) $category['name']) ?><?= ((int) ($category['is_active'] ?? 1) === 0) ? ' (inactive)' : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Currency *</label>
                    <select id="currency" name="currency" required onchange="syncCurrency()" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                        <option value="PKR">PKR (Pakistani Rupee)</option>
                        <option value="SAR">SAR (Saudi Riyal)</option>
                    </select>
                </div>
            </div>

            <!-- Row 3: Amount & Exchange Rate -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Amount *</label>
                    <input type="number" id="amount" name="amount" step="0.01" min="0" placeholder="0.00" required oninput="updatePreview()" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Exchange Rate (SAR → PKR)</label>
                    <input type="number" id="exchangeRate" name="exchange_rate" step="0.0001" min="0" value="1" disabled class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs bg-slate-100 disabled:cursor-not-allowed focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                </div>
            </div>

            <!-- Preview -->
            <div id="preview" class="hidden rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2">
                <p class="text-[10px] font-semibold text-emerald-700">PKR Equivalent (SAR × Rate)</p>
                <p id="previewValue" class="text-base font-bold text-emerald-900 mt-0.5">0.00</p>
            </div>

            <!-- Description & Attachment -->
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-700">Description</label>
                <textarea id="description" name="description" rows="2" placeholder="Enter description..." class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"></textarea>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-700">Attachment (Image/PDF)</label>
                <input type="file" id="attachment" name="attachment" accept="image/*,.pdf" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs cursor-pointer file:rounded file:border-0 file:bg-emerald-100 file:px-2 file:py-1 file:text-[10px] file:font-semibold file:text-emerald-700 hover:file:bg-emerald-200">
                <p class="text-[10px] text-slate-500 mt-1">Accepted: JPG, PNG, PDF (Max 5MB)</p>
                <div id="currentAttachment" class="hidden mt-2 rounded-lg bg-slate-100 p-2">
                    <p class="text-[10px] text-slate-600 mb-1.5">Current Attachment:</p>
                    <a id="attachmentLink" href="#" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 rounded bg-white border border-slate-300 text-[10px] font-semibold text-slate-700 hover:bg-slate-50">
                        <i class="fa-solid fa-file text-[10px]"></i><span>View</span>
                    </a>
                    <label class="mt-1.5 inline-flex items-center gap-1.5 text-[10px] font-medium text-rose-600 cursor-pointer hover:text-rose-700">
                        <input type="checkbox" name="remove_attachment" id="removeAttachment" class="rounded border-slate-300 cursor-pointer w-3 h-3">
                        <span>Remove Attachment</span>
                    </label>
                </div>
            </div>

            <!-- Loader -->
            <div id="entryLoader" class="hidden rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2.5 flex items-center justify-center gap-2">
                <div class="loader"></div>
                <p class="text-xs font-medium text-emerald-700">Processing entry...</p>
            </div>

            <!-- Modal Footer -->
            <div class="border-t border-slate-200 pt-3 flex items-center justify-end gap-2">
                <button type="button" onclick="closeEntryModal()" id="cancelBtn" class="btn btn-outline px-3 py-1.5 text-xs">Cancel</button>
                <button type="submit" id="submitBtn" class="btn btn-primary inline-flex items-center gap-1 px-3 py-1.5 text-xs">
                    <i class="fa-solid fa-save text-xs"></i><span id="submitText">Save Entry</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
        const modal = document.getElementById('entryModal');
        const form = document.getElementById('entryForm');
        const addBtn = document.getElementById('addEntryBtn');

        addBtn.addEventListener('click', function() {
            openAddEntryModal();
        });

        window.openAddEntryModal = function() {
            document.getElementById('modalTitle').textContent = 'Add Ledger Entry';
            document.getElementById('submitText').textContent = 'Save Entry';
            document.getElementById('isEditMode').value = '0';
            form.action = '<?= site_url('/admin/suppliers/ledger') ?>';
            form.method = 'POST';
            form.enctype = 'multipart/form-data';
            document.getElementById('entryDate').value = new Date().toISOString().split('T')[0];
            document.getElementById('entryType').value = 'bill';
            document.getElementById('category').value = '';
            document.getElementById('currency').value = 'PKR';
            document.getElementById('exchangeRate').value = '1';
            document.getElementById('exchangeRate').disabled = true;
            document.getElementById('amount').value = '';
            document.getElementById('description').value = '';
            document.getElementById('attachment').value = '';
            document.getElementById('currentAttachment').classList.add('hidden');
            document.getElementById('preview').classList.add('hidden');
            document.getElementById('entryLoader').classList.add('hidden');
            modal.classList.remove('hidden');
        };

        window.editLedgerEntry = function(entryId, entryData) {
            document.getElementById('modalTitle').textContent = 'Edit Ledger Entry';
            document.getElementById('submitText').textContent = 'Update Entry';
            document.getElementById('isEditMode').value = '1';
            document.getElementById('editEntryId').value = entryData.id || entryId;
            form.action = '<?= site_url('/admin/suppliers/ledger/update') ?>';

            document.getElementById('entryDate').value = entryData.entry_date || '';
            document.getElementById('entryType').value = entryData.entry_type || 'bill';
            document.getElementById('category').value = entryData.category_id ? String(entryData.category_id) : '';

            const currency = (entryData.currency || 'PKR').toUpperCase();
            document.getElementById('currency').value = currency;
            document.getElementById('exchangeRate').value = entryData.exchange_rate || 1;
            document.getElementById('exchangeRate').disabled = currency !== 'SAR';

            const isCredit = entryData.entry_type === 'bill' || entryData.entry_type === 'refund';
            const debitAmount = currency === 'SAR' ? (entryData.sar_debit_amount || 0) : (entryData.debit_amount || 0);
            const creditAmount = currency === 'SAR' ? (entryData.sar_credit_amount || 0) : (entryData.credit_amount || 0);
            const originalAmount = isCredit ? creditAmount : debitAmount;
            document.getElementById('amount').value = originalAmount || '';

            document.getElementById('description').value = entryData.description || '';
            document.getElementById('attachment').value = '';

            if (entryData.attachment) {
                document.getElementById('currentAttachment').classList.remove('hidden');
                document.getElementById('attachmentLink').href = '<?= site_url('/admin/suppliers/attachment/download/') ?>' + encodeURIComponent(entryData.attachment);
                document.getElementById('removeAttachment').checked = false;
            } else {
                document.getElementById('currentAttachment').classList.add('hidden');
            }

            updatePreview();
            document.getElementById('entryLoader').classList.add('hidden');
            modal.classList.remove('hidden');
        };

        window.closeEntryModal = function() {
            modal.classList.add('hidden');
            form.reset();
            // Reset loader and buttons
            const loader = document.getElementById('entryLoader');
            const submitBtn = document.getElementById('submitBtn');
            const cancelBtn = document.getElementById('cancelBtn');
            loader.classList.add('hidden');
            submitBtn.disabled = false;
            cancelBtn.disabled = false;
            submitBtn.style.opacity = '1';
            cancelBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
            cancelBtn.style.cursor = 'pointer';
        };

        window.syncCurrency = function() {
            const currency = document.getElementById('currency').value;
            const exchangeRateInput = document.getElementById('exchangeRate');
            exchangeRateInput.disabled = currency !== 'SAR';
            if (currency !== 'SAR') {
                exchangeRateInput.value = 1;
            }
            updatePreview();
        };

        window.updatePreview = function() {
            const currency = document.getElementById('currency').value;
            const amount = parseFloat(document.getElementById('amount').value) || 0;
            const rate = parseFloat(document.getElementById('exchangeRate').value) || 1;
            const preview = document.getElementById('preview');
            const previewValue = document.getElementById('previewValue');

            if (currency === 'SAR' && amount > 0) {
                previewValue.textContent = (amount * rate).toFixed(2);
                preview.classList.remove('hidden');
            } else {
                preview.classList.add('hidden');
            }
        };

        window.applyLedgerFilter = function() {
            const from = document.getElementById('from').value;
            const to = document.getElementById('to').value;
            const url = new URL(window.location);
            if (from) url.searchParams.set('from', from);
            if (to) url.searchParams.set('to', to);
            window.location = url.toString();
        };

        window.resetLedgerFilter = function() {
            window.location = window.location.pathname;
        };

        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeEntryModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeEntryModal();
            }
        });

        // Handle form submission with loader
        form.addEventListener('submit', function(e) {
            const loader = document.getElementById('entryLoader');
            const submitBtn = document.getElementById('submitBtn');
            const cancelBtn = document.getElementById('cancelBtn');

            // Show loader and disable submission buttons only
            loader.classList.remove('hidden');
            submitBtn.disabled = true;
            cancelBtn.disabled = true;
            submitBtn.style.opacity = '0.6';
            cancelBtn.style.opacity = '0.6';
            submitBtn.style.cursor = 'not-allowed';
            cancelBtn.style.cursor = 'not-allowed';
        });
    })();
</script>

<?php $this->endSection() ?>