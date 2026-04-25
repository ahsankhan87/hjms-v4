<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">

    <?php if (!empty($success)): ?>
        <div class="flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
            <i class="fa-solid fa-circle-check shrink-0"></i> <?= esc($success) ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700">
            <i class="fa-solid fa-circle-exclamation shrink-0"></i> <?= esc($error) ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
            <div class="flex items-center gap-2 font-semibold mb-1"><i class="fa-solid fa-triangle-exclamation"></i> Please fix the following:</div>
            <?php foreach ($errors as $err): ?><div class="ml-5 list-item"><?= esc($err) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('/bookings') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="return_url" value="/bookings/add">

        <!-- ── Package & Tier ─────────────────────────────────── -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-4">
            <div class="px-5 py-3 border-b border-slate-100 bg-slate-50 flex items-center gap-2">
                <i class="fa-solid fa-box-open text-emerald-600"></i>
                <h3 class="text-sm font-semibold text-slate-800">Package Details</h3>
            </div>
            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Package <span class="text-rose-500">*</span></label>
                    <select id="booking-package" name="package_id" required class="js-select2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Select package…</option>
                        <?php foreach ($packages as $item): ?>
                            <option value="<?= esc($item['id']) ?>" <?= (string) old('package_id', (string) ($selectedPackageId ?? '')) === (string) $item['id'] ? 'selected' : '' ?>>
                                <?= esc($item['name']) ?> (<?= esc($item['code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Package Pricing <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-4 gap-2" id="tier-selector">
                        <?php $tierValue = (string) old('pricing_tier', 'sharing'); ?>
                        <?php foreach (($pricingTiers ?? ['sharing', 'quad', 'triple', 'double']) as $tier): ?>
                            <label class="tier-btn relative cursor-pointer rounded-lg border-2 py-2 text-center text-xs font-semibold transition-all
                                <?= $tierValue === $tier ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300' ?>">
                                <input type="radio" name="pricing_tier" value="<?= esc($tier) ?>" class="sr-only" <?= $tierValue === $tier ? 'checked' : '' ?>>
                                <?= esc(ucfirst($tier)) ?>
                                <span class="tier-price block text-[10px] font-normal text-slate-400 mt-0.5" data-tier="<?= esc($tier) ?>">—</span>
                                <span class="tier-seats block text-[10px] font-medium mt-0.5" data-tier-seats="<?= esc($tier) ?>"></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div id="flat-price-panel" class="hidden rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Package Price</div>
                        <div id="flat-package-price" class="mt-1 text-lg font-bold text-slate-800">—</div>
                        <div class="mt-1 text-[11px] text-slate-500">Flat package pricing applies because this package does not use hotel room tiers.</div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                    <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <?php $statusValue = old('status', 'draft'); ?>
                        <option value="draft" <?= $statusValue === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="confirmed" <?= $statusValue === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="cancelled" <?= $statusValue === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Shirka Company <span class="text-rose-500">*</span></label>
                    <select name="company_id" required class="js-select2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select shirka company…</option>
                        <?php foreach (($companies ?? []) as $item): ?>
                            <option value="<?= esc($item['id']) ?>" <?= (string) old('company_id') === (string) $item['id'] ? 'selected' : '' ?>><?= esc($item['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Agent <span class="text-slate-400 font-normal">(optional)</span></label>
                    <select name="agent_id" class="js-select2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">None</option>
                        <?php foreach ($agents as $item): ?>
                            <option value="<?= esc($item['id']) ?>" <?= (string) old('agent_id') === (string) $item['id'] ? 'selected' : '' ?>><?= esc($item['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Branch <span class="text-slate-400 font-normal">(optional)</span></label>
                    <select name="branch_id" class="js-select2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">None</option>
                        <?php foreach ($branches as $item): ?>
                            <option value="<?= esc($item['id']) ?>" <?= (string) old('branch_id') === (string) $item['id'] ? 'selected' : '' ?>><?= esc($item['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- ── Pilgrims ───────────────────────────────────────── -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-4">
            <div class="px-5 py-3 border-b border-slate-100 bg-slate-50 flex items-center gap-2">
                <i class="fa-solid fa-users text-emerald-600"></i>
                <h3 class="text-sm font-semibold text-slate-800">Select Pilgrims</h3>
            </div>
            <div class="p-5">
                <?php $oldPilgrimIds = array_map('intval', (array) old('pilgrim_ids')); ?>
                <select id="booking-pilgrims" name="pilgrim_ids[]" multiple required class="js-select2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <?php foreach ($pilgrims as $item): ?>
                        <option value="<?= esc($item['id']) ?>" <?= in_array((int) $item['id'], $oldPilgrimIds, true) ? 'selected' : '' ?>>
                            #<?= esc($item['id']) ?> — <?= esc($item['first_name'] . ' ' . $item['last_name']) ?><?= !empty($item['passport_no']) ? ' (' . esc($item['passport_no']) . ')' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="mt-2 text-xs text-slate-400"><i class="fa-solid fa-circle-info mr-1"></i>Only pilgrims not in a confirmed booking are shown.</p>
            </div>
        </div>

        <!-- ── Financial Summary ──────────────────────────────── -->
        <div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-2xl shadow-md overflow-hidden mb-4">
            <div class="px-5 py-3 border-b border-white/10 flex items-center gap-2">
                <i class="fa-solid fa-calculator text-white/80"></i>
                <h3 class="text-sm font-semibold text-white">Financial Summary</h3>
            </div>
            <div class="p-5 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 text-center border border-white/20">
                    <div class="text-xs text-white/70 mb-1">Unit Price</div>
                    <div id="booking-unit-price" class="text-lg font-bold text-white">—</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 text-center border border-white/20">
                    <div class="text-xs text-white/70 mb-1">Pilgrims</div>
                    <div id="booking-pilgrim-count" class="text-lg font-bold text-white">0</div>
                </div>
                <div id="seats-stat" class="bg-white/10 backdrop-blur-sm rounded-xl p-3 text-center border border-white/20 hidden">
                    <div class="text-xs text-white/70 mb-1">Seats Left</div>
                    <div id="booking-seats-left" class="text-lg font-bold text-white">—</div>
                    <div id="booking-seats-total" class="text-[10px] text-white/50 mt-0.5"></div>
                </div>
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3 text-center border border-white/30">
                    <div class="text-xs text-white/80 mb-1 font-medium">Estimated Total</div>
                    <div id="booking-estimated-total" class="text-xl font-extrabold text-white">—</div>
                </div>
            </div>
            <div id="pricing-warning" class="hidden px-5 pb-4">
                <div class="rounded-lg bg-amber-400/20 border border-amber-300/40 px-3 py-2 text-xs text-amber-100 flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    No price configured for the selected package. Please add the required component costs in Package Management.
                </div>
            </div>
            <div id="seats-warning" class="hidden px-5 pb-4">
                <div class="rounded-lg bg-rose-500/30 border border-rose-300/40 px-3 py-2 text-xs text-rose-100 flex items-center gap-2">
                    <i class="fa-solid fa-ban"></i>
                    <span id="seats-warning-text">Seats limit exceeded. Reduce the number of pilgrims.</span>
                </div>
            </div>
        </div>

        <!-- ── Remarks & Submit ───────────────────────────────── -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-4">
            <div class="px-5 py-3 border-b border-slate-100 bg-slate-50 flex items-center gap-2">
                <i class="fa-solid fa-comment-dots text-emerald-600"></i>
                <h3 class="text-sm font-semibold text-slate-800">Remarks</h3>
            </div>
            <div class="p-5">
                <textarea name="remarks" rows="3" placeholder="Optional notes about this booking…" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"><?= esc(old('remarks')) ?></textarea>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" id="submit-btn" class="btn btn-md btn-primary px-8">
                <i class="fa-solid fa-floppy-disk mr-2"></i>Create Booking
            </button>
            <a href="<?= site_url('/bookings') ?>" class="btn btn-md btn-secondary">Cancel</a>
        </div>
    </form>
</main>

<script>
    (function($) {
        const pricingMeta = <?= json_encode($packagePricingMeta ?? [], JSON_UNESCAPED_UNICODE) ?>;
        const bookedSeats = <?= json_encode($bookedSeatsCountByPackage ?? [], JSON_UNESCAPED_UNICODE) ?>;
        const bookedPackageCounts = <?= json_encode($bookedPackageCountByPackage ?? [], JSON_UNESCAPED_UNICODE) ?>;

        const $package = $('#booking-package');
        const $pilgrims = $('#booking-pilgrims');
        const $tiers = $('input[name="pricing_tier"]');

        const fmt = function(n) {
            return n > 0 ? 'PKR ' + n.toLocaleString('en-PK', {
                minimumFractionDigits: 0
            }) : '—';
        };

        function selectedTier() {
            return $('input[name="pricing_tier"]:checked').val() || '';
        }

        function selectedMeta() {
            const pkg = $package.val();
            return pkg && pricingMeta[pkg] ? pricingMeta[pkg] : {};
        }

        function isFlatPackage(meta) {
            return meta && meta.mode === 'flat';
        }

        function unitPrice() {
            const pkg = $package.val();
            const meta = selectedMeta();
            if (!pkg) return 0;
            const v = isFlatPackage(meta) ?
                Number(meta.flat_price || 0) :
                Number((meta.price_map || {})[selectedTier()] || 0);
            return isNaN(v) ? 0 : v;
        }

        function pilgrimCount() {
            const val = $pilgrims.val();
            return Array.isArray(val) ? val.length : (val ? 1 : 0);
        }

        function refreshSummary() {
            const unit = unitPrice();
            const count = pilgrimCount();
            const total = unit * count;
            const pkg = $package.val();
            const tier = selectedTier();
            const meta = selectedMeta();
            const flatMode = isFlatPackage(meta);

            if (flatMode) {
                $tiers.filter('[value="sharing"]').prop('checked', true);
            }

            $('#tier-selector').toggleClass('hidden', flatMode);
            $('#flat-price-panel').toggleClass('hidden', !flatMode);
            $('#flat-package-price').text(unit > 0 ? fmt(unit) : '—');

            $('#booking-unit-price').text(fmt(unit));
            $('#booking-pilgrim-count').text(count);
            $('#booking-estimated-total').text(total > 0 ? fmt(total) : '—');

            // Warn if package selected but no price
            const hasPkg = !!pkg;
            const hasPrice = unit > 0;
            $('#pricing-warning').toggleClass('hidden', !(hasPkg && !hasPrice));

            // ── Seats ────────────────────────────────────────────────────────
            const limitMap = meta.seat_limit_map || {};
            const bookedMap = pkg ? (bookedSeats[pkg] || {}) : {};
            const tierSelected = !!pkg && (!!tier || flatMode);

            const rawCap = flatMode ?
                (meta.flat_seats_limit !== undefined ? meta.flat_seats_limit : null) :
                ((tierSelected && limitMap[tier] !== undefined) ? limitMap[tier] : null);
            const noSeatsConfigured = tierSelected && (rawCap === null || rawCap === 0);
            const seatCap = (rawCap !== null && rawCap > 0) ? rawCap : null;

            const alreadyBook = flatMode ?
                Number(pkg && bookedPackageCounts[pkg] !== undefined ? bookedPackageCounts[pkg] : 0) :
                Number((tierSelected && bookedMap[tier] !== undefined) ? bookedMap[tier] : 0);
            const remaining = seatCap !== null ? Math.max(0, seatCap - alreadyBook) : null;
            const exceeded = remaining !== null && count > remaining;
            const blocked = noSeatsConfigured || exceeded;

            // Seats stat box
            if (seatCap !== null) {
                $('#seats-stat').removeClass('hidden');
                $('#booking-seats-left').text(remaining);
                $('#booking-seats-total').text('of ' + seatCap + ' total');
                const ratio = seatCap > 0 ? remaining / seatCap : 0;
                $('#booking-seats-left')
                    .toggleClass('text-rose-200', ratio < 0.2)
                    .toggleClass('text-amber-200', ratio >= 0.2 && ratio < 0.5)
                    .toggleClass('text-white', ratio >= 0.5);
            } else {
                $('#seats-stat').addClass('hidden');
            }

            // Seats warning
            if (noSeatsConfigured) {
                $('#seats-warning-text').text(flatMode ? 'No seats configured for this package. Please set package total seats before booking.' : 'No seats configured for this package & tier. Please set a seats limit in Package Management before booking.');
                $('#seats-warning').removeClass('hidden');
            } else if (exceeded) {
                $('#seats-warning-text').text(
                    'Only ' + remaining + ' seat(s) left for this ' + (flatMode ? 'package' : 'tier') + '. You selected ' + count + ' pilgrim(s).');
                $('#seats-warning').removeClass('hidden');
            } else {
                $('#seats-warning').addClass('hidden');
            }

            // Disable submit when no seats configured or limit exceeded
            $('#submit-btn').prop('disabled', blocked).toggleClass('opacity-50 cursor-not-allowed', blocked);

            // Update tier pills with prices AND seats for selected package
            $('[data-tier]').each(function() {
                const t = $(this).data('tier');
                const tierPrice = (meta.price_map || {})[t];
                $(this).text(tierPrice ? 'PKR ' + Number(tierPrice).toLocaleString('en-PK') : '—');
            });
            $('[data-tier-seats]').each(function() {
                const t = $(this).data('tier-seats');
                const cap = (!flatMode && pkg && limitMap[t] !== undefined) ? limitMap[t] : null;
                const bkd = (pkg && bookedMap[t] !== undefined) ? bookedMap[t] : 0;
                if (cap !== null) {
                    const left = Math.max(0, cap - bkd);
                    const ratio = cap > 0 ? left / cap : 0;
                    $(this).text(left + ' seats left')
                        .removeClass('text-emerald-600 text-amber-500 text-rose-500')
                        .addClass(ratio < 0.2 ? 'text-rose-500' : ratio < 0.5 ? 'text-amber-500' : 'text-emerald-600');
                } else {
                    $(this).text('');
                }
            });
        }

        // Tier pill visual toggle
        $(document).on('change', 'input[name="pricing_tier"]', function() {
            $('.tier-btn').removeClass('border-emerald-500 bg-emerald-50 text-emerald-700')
                .addClass('border-slate-200 bg-white text-slate-600');
            $(this).closest('.tier-btn')
                .addClass('border-emerald-500 bg-emerald-50 text-emerald-700')
                .removeClass('border-slate-200 bg-white text-slate-600');
            refreshSummary();
        });

        // Select2 fires jQuery 'change' on the underlying <select>
        $package.on('change', refreshSummary);
        $pilgrims.on('change', refreshSummary);

        refreshSummary();
    }(jQuery));
</script>
<?php $this->endSection() ?>