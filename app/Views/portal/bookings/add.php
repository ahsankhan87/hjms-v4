<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="max-w-5xl">
        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-sm font-semibold text-slate-900">Add Booking</h2>
                <a href="<?= site_url('/bookings') ?>" class="btn btn-sm btn-secondary">Back to List</a>
            </div>

            <form method="post" action="<?= site_url('/bookings') ?>" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <?= csrf_field() ?>
                <input type="hidden" name="return_url" value="/bookings/add">

                <div>
                    <label class="text-sm font-medium">Package</label>
                    <select id="booking-package" name="package_id" required class="js-select2 mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select package</option>
                        <?php foreach ($packages as $item): ?>
                            <option value="<?= esc($item['id']) ?>" <?= (string) old('package_id', (string) ($selectedPackageId ?? '')) === (string) $item['id'] ? 'selected' : '' ?>><?= esc($item['name']) ?> (<?= esc($item['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php $tierValue = (string) old('pricing_tier', 'sharing'); ?>
                <div>
                    <label class="text-sm font-medium">Pricing Tier</label>
                    <select id="booking-pricing-tier" name="pricing_tier" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <?php foreach (($pricingTiers ?? ['sharing', 'quad', 'triple', 'double']) as $tier): ?>
                            <option value="<?= esc($tier) ?>" <?= $tierValue === $tier ? 'selected' : '' ?>><?= esc(ucfirst($tier)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php $statusValue = old('status', 'draft'); ?>
                <div>
                    <label class="text-sm font-medium">Status</label>
                    <select name="status" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="draft" <?= $statusValue === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="confirmed" <?= $statusValue === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="cancelled" <?= $statusValue === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium">Agent (optional)</label>
                    <select name="agent_id" class="js-select2 mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">None</option>
                        <?php foreach ($agents as $item): ?>
                            <option value="<?= esc($item['id']) ?>" <?= (string) old('agent_id') === (string) $item['id'] ? 'selected' : '' ?>><?= esc($item['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium">Branch (optional)</label>
                    <select name="branch_id" class="js-select2 mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">None</option>
                        <?php foreach ($branches as $item): ?>
                            <option value="<?= esc($item['id']) ?>" <?= (string) old('branch_id') === (string) $item['id'] ? 'selected' : '' ?>><?= esc($item['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium">Shirka Company</label>
                    <select name="company_id" required class="js-select2 mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select shirka company</option>
                        <?php foreach (($companies ?? []) as $item): ?>
                            <option value="<?= esc($item['id']) ?>" <?= (string) old('company_id') === (string) $item['id'] ? 'selected' : '' ?>><?= esc($item['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php $oldPilgrimIds = array_map('intval', (array) old('pilgrim_ids')); ?>
                <div class="md:col-span-2">
                    <label class="text-sm font-medium">Select Pilgrims</label>
                    <select id="booking-pilgrims" name="pilgrim_ids[]" multiple required class="js-select2 mt-1 h-36 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <?php foreach ($pilgrims as $item): ?>
                            <option value="<?= esc($item['id']) ?>" <?= in_array((int) $item['id'], $oldPilgrimIds, true) ? 'selected' : '' ?>>#<?= esc($item['id']) ?> - <?= esc($item['first_name'] . ' ' . $item['last_name']) ?><?= !empty($item['passport_no']) ? ' (' . esc($item['passport_no']) . ')' : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Hold Ctrl/Cmd to select multiple pilgrims.</p>
                </div>

                <div class="md:col-span-2 rounded-lg border border-slate-200 bg-slate-50 p-3">
                    <h4 class="text-sm font-semibold text-slate-800">Booking Financial Summary</h4>
                    <div class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div>
                            <label class="text-xs font-medium text-slate-600">Unit Price</label>
                            <input id="booking-unit-price" type="text" readonly class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm" value="0.00">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-slate-600">Pilgrim Count</label>
                            <input id="booking-pilgrim-count" type="text" readonly class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm" value="0">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-slate-600">Estimated Total</label>
                            <input id="booking-estimated-total" type="text" readonly class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold" value="0.00">
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="text-sm font-medium">Remarks</label>
                    <textarea name="remarks" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc(old('remarks')) ?></textarea>
                </div>

                <div class="md:col-span-2">
                    <button type="submit" class="btn btn-md btn-primary">Create Booking</button>
                </div>
            </form>
        </article>
    </section>
</main>
<script>
    (function() {
        const pricingByPackage = <?= json_encode($packagePricingOptions ?? [], JSON_UNESCAPED_UNICODE) ?>;
        const packageInput = document.getElementById('booking-package');
        const tierInput = document.getElementById('booking-pricing-tier');
        const pilgrimInput = document.getElementById('booking-pilgrims');
        const unitPriceInput = document.getElementById('booking-unit-price');
        const pilgrimCountInput = document.getElementById('booking-pilgrim-count');
        const totalInput = document.getElementById('booking-estimated-total');

        function selectedPilgrimCount() {
            if (!pilgrimInput) return 0;
            return Array.prototype.filter.call(pilgrimInput.options, function(opt) {
                return opt.selected;
            }).length;
        }

        function unitPriceForSelection() {
            const pkg = packageInput ? packageInput.value : '';
            const tier = tierInput ? tierInput.value : '';
            if (!pkg || !tier) {
                return 0;
            }
            if (!pricingByPackage[pkg] || typeof pricingByPackage[pkg][tier] === 'undefined') {
                return 0;
            }
            const value = Number(pricingByPackage[pkg][tier]);
            return isNaN(value) ? 0 : value;
        }

        function refreshSummary() {
            const unit = unitPriceForSelection();
            const count = selectedPilgrimCount();
            const total = unit * count;

            if (unitPriceInput) unitPriceInput.value = unit.toFixed(2);
            if (pilgrimCountInput) pilgrimCountInput.value = String(count);
            if (totalInput) totalInput.value = total.toFixed(2);
        }

        if (packageInput) packageInput.addEventListener('change', refreshSummary);
        if (tierInput) tierInput.addEventListener('change', refreshSummary);
        if (pilgrimInput) pilgrimInput.addEventListener('change', refreshSummary);
        refreshSummary();
    })();
</script>
<?php $this->endSection() ?>