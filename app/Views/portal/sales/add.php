<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
        <h3 class="text-sm font-semibold text-slate-800">Create Sale</h3>
        <p class="text-xs text-slate-500">Create office sale records for visas and related services.</p>
    </article>

    <article class="rounded-xl border border-slate-200 bg-white p-4">
        <?php if (empty($categories)): ?><div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">Create a sales category first before posting sales.</div><?php endif; ?>
        <form method="post" action="<?= site_url('/sales') ?>" class="space-y-3">
            <?= csrf_field() ?>
            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Sale Date</label>
                    <input type="date" name="sale_date" value="<?= esc(old('sale_date', date('Y-m-d'))) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Category</label>
                    <select name="sales_category_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select Category</option>
                        <?php foreach (($categories ?? []) as $category): ?>
                            <option value="<?= esc((string) $category['id']) ?>" <?= old('sales_category_id') === (string) $category['id'] ? 'selected' : '' ?>><?= esc((string) $category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Customer Type</label>
                    <?php $customerType = old('customer_type', 'walk_in'); ?>
                    <select id="customer_type" name="customer_type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="walk_in" <?= $customerType === 'walk_in' ? 'selected' : '' ?>>Walk-In</option>
                        <option value="agent" <?= $customerType === 'agent' ? 'selected' : '' ?>>Agent</option>
                    </select>
                </div>
                <div id="agent_wrap" class="<?= $customerType === 'agent' ? '' : 'hidden' ?>">
                    <label class="mb-1 block text-xs font-medium text-slate-600">Agent (Required for credit sale)</label>
                    <select name="agent_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select Agent</option>
                        <?php foreach (($agents ?? []) as $agent): ?>
                            <option value="<?= esc((string) $agent['id']) ?>" <?= old('agent_id') === (string) $agent['id'] ? 'selected' : '' ?>><?= esc((string) $agent['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div id="walkin_wrap" class="<?= $customerType === 'walk_in' ? '' : 'hidden' ?>">
                <label class="mb-1 block text-xs font-medium text-slate-600">Walk-In Customer Name</label>
                <input id="customer_name" name="customer_name" value="<?= esc(old('customer_name')) ?>" placeholder="Customer name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Total Amount</label>
                <input name="amount" value="<?= esc(old('amount')) ?>" placeholder="0.00" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Payment Method</label>
                    <select id="payment_method" name="payment_method" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <?php foreach (['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'cheque' => 'Cheque', 'card' => 'Card', 'online' => 'Online', 'other' => 'Other'] as $value => $label): ?>
                            <option value="<?= esc($value) ?>" <?= old('payment_method', 'cash') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                    <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="posted" <?= old('status', 'posted') === 'posted' ? 'selected' : '' ?>>Posted</option>
                        <option value="pending" <?= old('status') === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="voided" <?= old('status') === 'voided' ? 'selected' : '' ?>>Voided</option>
                    </select>
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Reference No</label>
                    <input name="reference_no" value="<?= esc(old('reference_no')) ?>" placeholder="Invoice / Voucher / Receipt No" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div></div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Note</label>
                <textarea name="note" rows="3" placeholder="Optional internal note" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc(old('note')) ?></textarea>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
                <a href="<?= site_url('/sales') ?>" class="btn btn-md btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-md btn-primary" <?= empty($categories) ? 'disabled' : '' ?>><i class="fa-solid fa-check"></i><span>Create Sale</span></button>
            </div>
        </form>
    </article>
</main>

<script>
    (function() {
        var customerType = document.getElementById('customer_type');
        var agentWrap = document.getElementById('agent_wrap');
        var walkinWrap = document.getElementById('walkin_wrap');
        var customerName = document.getElementById('customer_name');
        var paymentMethod = document.getElementById('payment_method');

        function toggleCustomerFields() {
            var isAgent = customerType.value === 'agent';
            agentWrap.classList.toggle('hidden', !isAgent);
            walkinWrap.classList.toggle('hidden', isAgent);

            if (!isAgent) {
                if (customerName && customerName.value.trim() === '') {
                    customerName.value = 'Walk-In';
                }
                if (paymentMethod) {
                    paymentMethod.value = 'cash';
                }
            }
        }

        customerType.addEventListener('change', toggleCustomerFields);
        toggleCustomerFields();
    })();
</script>
<?php $this->endSection() ?>