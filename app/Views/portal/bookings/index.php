<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<?php
$statusMap = [
    'draft'     => ['bg' => 'bg-slate-100',   'text' => 'text-slate-600'],
    'confirmed' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700'],
    'cancelled' => ['bg' => 'bg-rose-100',    'text' => 'text-rose-700'],
];
$tierMap = [
    'sharing' => ['bg' => 'bg-violet-100', 'text' => 'text-violet-700'],
    'quad'    => ['bg' => 'bg-sky-100',    'text' => 'text-sky-700'],
    'triple'  => ['bg' => 'bg-amber-100',  'text' => 'text-amber-700'],
    'double'  => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700'],
];
$pricingModeMap = [
    'flat' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'label' => 'Package'],
];
?>
<main class="space-y-4">
    <?php if (!empty($success)): ?>
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3">
            <i class="ri-checkbox-circle-line text-emerald-600"></i>
            <p class="text-sm text-emerald-700"><?= esc($success) ?></p>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flex items-center gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3">
            <i class="ri-error-warning-line text-rose-500"></i>
            <p class="text-sm text-rose-700"><?= esc($error) ?></p>
        </div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
            <?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="list-card overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <!-- Card header -->
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-4">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Bookings</h2>
                <p class="mt-0.5 text-xs text-slate-500"><?= count($rows) ?> booking<?= count($rows) !== 1 ? 's' : '' ?> in active season</p>
            </div>
            <a href="<?= site_url('/bookings/add') ?>" class="btn btn-sm btn-primary">
                <i class="ri-add-line mr-1"></i>Add Booking
            </a>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table id="bookings-table" class="list-table w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3">Booking No</th>
                        <th class="px-4 py-3">Package</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Tier</th>
                        <th class="px-4 py-3 text-center">Pilgrims</th>
                        <th class="px-4 py-3 text-right">Total (PKR)</th>
                        <th class="px-4 py-3 text-right">Outstanding</th>
                        <th class="px-4 py-3 text-right" data-orderable="false">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($rows as $row): ?>
                        <?php
                        $status = (string) ($row['status'] ?? 'draft');
                        $stc    = $statusMap[$status] ?? $statusMap['draft'];
                        $tier   = strtolower((string) ($row['pricing_tier'] ?? ''));
                        $tc     = $tierMap[$tier] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-600'];
                        $isFlatPackage = (int) ($row['include_hotel'] ?? 1) !== 1;
                        $outstanding = (float) ($row['outstanding_amount'] ?? 0);
                        $isOverpaid  = $outstanding < 0;
                        ?>
                        <tr class="group hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3">
                                <a href="<?= site_url('/bookings/' . (int) $row['id']) ?>" class="font-mono text-xs font-semibold text-sky-600 hover:underline">
                                    <?= esc($row['booking_no']) ?>
                                </a>
                            </td>
                            <td class="px-4 py-3 max-w-[180px]">
                                <p class="truncate font-medium text-slate-800"><?= esc((string) ($row['package_name'] ?? ('#' . $row['package_id']))) ?></p>
                                <?php if (!empty($row['company_name'])): ?>
                                    <p class="truncate text-xs text-slate-500"><?= esc($row['company_name']) ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full <?= $stc['bg'] ?> <?= $stc['text'] ?> px-2.5 py-0.5 text-xs font-semibold capitalize">
                                    <?= esc($status) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($isFlatPackage): ?>
                                    <span class="inline-flex items-center rounded-full <?= $pricingModeMap['flat']['bg'] ?> <?= $pricingModeMap['flat']['text'] ?> px-2.5 py-0.5 text-xs font-semibold">
                                        <?= esc($pricingModeMap['flat']['label']) ?>
                                    </span>
                                <?php elseif ($tier !== ''): ?>
                                    <span class="inline-flex items-center rounded-full <?= $tc['bg'] ?> <?= $tc['text'] ?> px-2.5 py-0.5 text-xs font-semibold capitalize">
                                        <?= esc($tier) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-400">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex h-6 min-w-[24px] items-center justify-center rounded-full bg-slate-100 px-2 text-xs font-bold text-slate-700">
                                    <?= esc((int) ($row['total_pilgrims'] ?? 0)) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-slate-800">
                                <?= esc(number_format((float) ($row['total_amount'] ?? 0), 0)) ?>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <?php if ($isOverpaid): ?>
                                    <span class="font-semibold text-emerald-600">+<?= esc(number_format(abs($outstanding), 0)) ?></span>
                                <?php elseif ($outstanding > 0): ?>
                                    <span class="font-semibold text-rose-600"><?= esc(number_format($outstanding, 0)) ?></span>
                                <?php else: ?>
                                    <span class="font-semibold text-emerald-600">Paid</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="js-action-wrap relative">
                                    <button type="button" class="btn btn-sm btn-secondary js-action-toggle inline-flex items-center gap-1">
                                        Actions <i class="fa-solid fa-chevron-down text-xs leading-none transition-transform duration-200 js-action-arrow"></i>
                                    </button>
                                    <div class="js-action-menu hidden w-44 rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                                        <a href="<?= site_url('/bookings/' . (int) $row['id']) ?>" class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                            <i class="fa-solid fa-eye text-slate-400"></i> View
                                        </a>
                                        <a href="<?= site_url('/bookings/' . (int) $row['id'] . '/edit') ?>" class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                            <i class="fa-solid fa-pen-to-square text-slate-400"></i> Edit
                                        </a>
                                        <a href="<?= site_url('/bookings/' . (int) $row['id'] . '/voucher') ?>" target="_blank" class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                            <i class="fa-solid fa-file-lines text-slate-400"></i> Voucher
                                        </a>
                                        <div class="my-1 border-t border-slate-100"></div>
                                        <button type="button"
                                            class="flex w-full items-center gap-2.5 px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 js-delete-btn"
                                            data-booking-id="<?= esc((string) $row['id']) ?>"
                                            data-booking-no="<?= esc((string) $row['booking_no']) ?>">
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Delete confirmation modal -->
<div id="delete-modal" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="mx-4 w-full max-w-md rounded-xl bg-white shadow-2xl">
        <div class="border-b border-slate-100 px-6 py-5">
            <h3 class="text-lg font-semibold text-slate-900">Delete Booking</h3>
            <p class="mt-1 text-sm text-slate-500">This will permanently remove the booking.</p>
        </div>
        <form method="post" action="<?= site_url('/bookings/delete') ?>" class="p-6 space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" id="delete-modal-booking-id" name="booking_id" value="">
            <div class="flex gap-3 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3">
                <i class="fa-solid fa-triangle-exclamation mt-0.5 shrink-0 text-lg text-rose-500"></i>
                <p class="text-sm text-rose-700">Deleting booking <strong id="delete-modal-booking-no"></strong> cannot be undone.</p>
            </div>
            <div class="flex gap-3 border-t border-slate-100 pt-4">
                <button type="submit" class="btn btn-md btn-danger flex-1">Confirm Delete</button>
                <button type="button" id="delete-modal-close" class="btn btn-md btn-secondary flex-1">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
        // ── Dropdown toggles — event-delegated so they work after DataTables redraws ──
        var activeMenu = null;
        var activeBtn = null;

        function setArrow(btn, open) {
            var arrow = btn.querySelector('.js-action-arrow');
            if (arrow) arrow.style.transform = open ? 'rotate(180deg)' : '';
        }

        function closeActive() {
            if (activeMenu) {
                activeMenu.classList.add('hidden');
                setArrow(activeBtn, false);
                activeMenu = null;
                activeBtn = null;
            }
        }

        function positionMenu(btn, menu) {
            var rect = btn.getBoundingClientRect();
            var menuW = 176; // w-44 = 11rem
            var left = rect.right - menuW;
            if (left < 8) left = 8;
            menu.style.position = 'fixed';
            menu.style.zIndex = '9999';
            menu.style.top = (rect.bottom + 4) + 'px';
            menu.style.left = left + 'px';
        }

        // Delegated — fires on rows that DataTables pushes in on any page
        document.addEventListener('click', function(e) {
            var toggleBtn = e.target.closest('.js-action-toggle');
            if (toggleBtn) {
                e.stopPropagation();
                var menu = toggleBtn.closest('.js-action-wrap').querySelector('.js-action-menu');
                if (activeMenu === menu) {
                    closeActive();
                    return;
                }
                closeActive();
                positionMenu(toggleBtn, menu);
                menu.classList.remove('hidden');
                setArrow(toggleBtn, true);
                activeMenu = menu;
                activeBtn = toggleBtn;
                return;
            }
            if (!e.target.closest('.js-action-menu')) {
                closeActive();
            }
        });

        window.addEventListener('scroll', function() {
            if (activeMenu && activeBtn) positionMenu(activeBtn, activeMenu);
        }, true);
        window.addEventListener('resize', function() {
            if (activeMenu && activeBtn) positionMenu(activeBtn, activeMenu);
        });

        // ── Delete modal ────────────────────────────────────────────────────
        var modal = document.getElementById('delete-modal');
        var bidInput = document.getElementById('delete-modal-booking-id');
        var bnoSpan = document.getElementById('delete-modal-booking-no');
        var closeBtn = document.getElementById('delete-modal-close');

        // Delegated — works on rows injected by DataTables pagination
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.js-delete-btn');
            if (!btn) return;
            bidInput.value = btn.dataset.bookingId;
            bnoSpan.textContent = btn.dataset.bookingNo;
            modal.style.display = 'flex';
        });

        if (closeBtn) closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        modal.addEventListener('click', function(e) {
            if (e.target === modal) modal.style.display = 'none';
        });
    }());
</script>
<?php $this->endSection() ?>