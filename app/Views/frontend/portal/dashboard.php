<?= $this->extend('frontend/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$summary = is_array($summary ?? null) ? $summary : ['total' => 0, 'pending' => 0, 'confirmed' => 0];
$upcoming = is_array($upcoming ?? null) ? $upcoming : [];
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
            <h1 class="text-2xl font-bold text-slate-900">Welcome back, <?= esc((string) ($user['name'] ?? session('name'))) ?></h1>
            <p class="mt-1 text-sm text-slate-500">Manage your pilgrimage and travel bookings in one place.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500">Total Bookings</p>
                <p class="mt-2 text-2xl font-bold text-slate-900"><?= esc((string) (int) ($summary['total'] ?? 0)) ?></p>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-amber-700">Pending</p>
                <p class="mt-2 text-2xl font-bold text-amber-800"><?= esc((string) (int) ($summary['pending'] ?? 0)) ?></p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-emerald-700">Confirmed</p>
                <p class="mt-2 text-2xl font-bold text-emerald-800"><?= esc((string) (int) ($summary['confirmed'] ?? 0)) ?></p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">Upcoming Bookings</h2>
                <a href="<?= site_url('/portal/bookings') ?>" class="text-sm font-medium text-sky-700 hover:text-sky-800">View all</a>
            </div>
            <?php if ($upcoming !== []): ?>
                <div class="mt-4 space-y-3">
                    <?php foreach ($upcoming as $booking): ?>
                        <?php $details = is_array($booking['parsed_details'] ?? null) ? $booking['parsed_details'] : []; ?>
                        <a href="<?= site_url('/portal/bookings/' . (int) ($booking['id'] ?? 0)) ?>" class="block rounded-xl border border-slate-200 p-3 hover:border-sky-300 hover:bg-sky-50/40">
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900"><?= esc((string) ($details['item_name'] ?? ('Booking #' . (int) ($booking['id'] ?? 0)))) ?></p>
                                    <p class="text-xs text-slate-500"><?= esc(ucfirst((string) ($booking['booking_type'] ?? 'booking'))) ?> · <?= esc((string) ($booking['display_date'] ?? '-')) ?></p>
                                </div>
                                <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700"><?= esc(ucfirst((string) ($booking['status'] ?? 'pending'))) ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="mt-4 text-sm text-slate-500">No upcoming bookings found yet.</p>
            <?php endif; ?>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <a href="<?= site_url('/packages/hajj') ?>" class="rounded-xl border border-slate-200 bg-white p-4 text-sm font-medium text-slate-700 shadow-sm hover:border-sky-300 hover:text-sky-700">Browse Hajj Packages</a>
            <a href="<?= site_url('/packages/umrah') ?>" class="rounded-xl border border-slate-200 bg-white p-4 text-sm font-medium text-slate-700 shadow-sm hover:border-sky-300 hover:text-sky-700">Browse Umrah Packages</a>
            <a href="<?= site_url('/tickets') ?>" class="rounded-xl border border-slate-200 bg-white p-4 text-sm font-medium text-slate-700 shadow-sm hover:border-sky-300 hover:text-sky-700">Search Tickets</a>
            <a href="<?= site_url('/hotels') ?>" class="rounded-xl border border-slate-200 bg-white p-4 text-sm font-medium text-slate-700 shadow-sm hover:border-sky-300 hover:text-sky-700">Find Hotels</a>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
