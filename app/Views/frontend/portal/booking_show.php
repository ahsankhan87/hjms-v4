<?= $this->extend('frontend/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$booking = is_array($booking ?? null) ? $booking : [];
$details = is_array($details ?? null) ? $details : [];
$timeline = is_array($timeline ?? null) ? $timeline : [];
?>
<section x-data="{sidebarOpen:false}" class="lg:grid lg:grid-cols-12 lg:gap-6">
    <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden" @click="sidebarOpen=false"></div>
    <aside class="fixed inset-y-0 left-0 z-50 w-72 -translate-x-full overflow-y-auto bg-slate-50 p-4 transition-transform duration-200 lg:static lg:col-span-3 lg:w-auto lg:translate-x-0 lg:bg-transparent lg:p-0" :class="sidebarOpen ? 'translate-x-0' : ''">
        <?= $this->include('frontend/portal/_sidebar') ?>
    </aside>

    <div class="lg:col-span-9 space-y-6">
        <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium lg:hidden" @click="sidebarOpen=true">
            <i class="fa-solid fa-bars"></i> Menu
        </button>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Booking #<?= esc((string) ($booking['id'] ?? 0)) ?></h1>
                    <p class="mt-1 text-sm text-slate-500"><?= esc(ucfirst((string) ($booking['booking_type'] ?? 'booking'))) ?> · Created <?= esc((string) ($booking['created_at'] ?? '-')) ?></p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-700"><?= esc((string) ($booking['status'] ?? 'pending')) ?></span>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Item</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900"><?= esc((string) ($details['item_name'] ?? ('Item #' . (int) ($booking['item_id'] ?? 0)))) ?></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Amount</p>
                    <p class="mt-1 text-sm font-semibold text-sky-700"><?= 'PKR ' . esc(number_format((float) ($booking['total_amount'] ?? 0), 0)) ?></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Travel Date</p>
                    <p class="mt-1 text-sm text-slate-700"><?= esc((string) ($details['travel_date'] ?? $details['check_in'] ?? '-')) ?></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Travelers</p>
                    <p class="mt-1 text-sm text-slate-700"><?= esc((string) ($details['travelers_count'] ?? '-')) ?></p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Passenger & Request Details</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 text-sm">
                <div><span class="text-slate-500">Lead Name:</span> <span class="font-medium text-slate-800"><?= esc((string) ($details['lead_name'] ?? '-')) ?></span></div>
                <div><span class="text-slate-500">Email:</span> <span class="font-medium text-slate-800"><?= esc((string) ($details['lead_email'] ?? '-')) ?></span></div>
                <div><span class="text-slate-500">Phone:</span> <span class="font-medium text-slate-800"><?= esc((string) ($details['lead_phone'] ?? '-')) ?></span></div>
                <div><span class="text-slate-500">Passport:</span> <span class="font-medium text-slate-800"><?= esc((string) ($details['passport_no'] ?? '-')) ?></span></div>
                <?php if (trim((string) ($details['special_requests'] ?? '')) !== ''): ?>
                    <div class="sm:col-span-2"><span class="text-slate-500">Special Requests:</span> <span class="font-medium text-slate-800"><?= esc((string) $details['special_requests']) ?></span></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Status Timeline</h2>
            <ol class="mt-4 space-y-3">
                <?php foreach ($timeline as $step): ?>
                    <?php
                    $state = (string) ($step['state'] ?? 'upcoming');
                    $dotClass = $state === 'done' ? 'bg-emerald-500' : ($state === 'current' ? 'bg-sky-500' : 'bg-slate-300');
                    ?>
                    <li class="flex gap-3">
                        <span class="mt-1 h-2.5 w-2.5 rounded-full <?= esc($dotClass) ?>"></span>
                        <div>
                            <p class="text-sm font-semibold text-slate-900"><?= esc((string) ($step['title'] ?? '')) ?></p>
                            <p class="text-xs text-slate-500"><?= esc((string) ($step['note'] ?? '')) ?></p>
                            <?php if (trim((string) ($step['at'] ?? '')) !== ''): ?>
                                <p class="text-xs text-slate-400"><?= esc((string) $step['at']) ?></p>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>

        <a href="<?= site_url('/portal/bookings') ?>" class="inline-flex rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:border-sky-300 hover:text-sky-700">Back to Bookings</a>
    </div>
</section>
<?= $this->endSection() ?>
