<?= $this->extend('frontend/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$bookingType = (string) ($bookingType ?? 'package');
$items = is_array($items ?? null) ? $items : [];
$clients = is_array($clients ?? null) ? $clients : [];
$errors = is_array($errors ?? null) ? $errors : [];
?>
<section x-data="{sidebarOpen:false}" class="lg:grid lg:grid-cols-12 lg:gap-6">
    <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden" @click="sidebarOpen=false"></div>
    <aside class="fixed inset-y-0 left-0 z-50 w-72 -translate-x-full overflow-y-auto bg-slate-50 p-4 transition-transform duration-200 lg:static lg:col-span-3 lg:w-auto lg:translate-x-0 lg:bg-transparent lg:p-0" :class="sidebarOpen ? 'translate-x-0' : ''">
        <?= $this->include('frontend/portal/agent/_sidebar') ?>
    </aside>

    <div class="lg:col-span-9 space-y-6">
        <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium lg:hidden" @click="sidebarOpen=true">
            <i class="fa-solid fa-bars"></i> Menu
        </button>

        <div>
            <h1 class="text-2xl font-bold text-slate-900">Book for Client</h1>
            <p class="text-sm text-slate-500">Agent pricing is shown below and used for booking totals.</p>
        </div>

        <?php if ($errors !== []): ?>
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <?php foreach ($errors as $msg): ?>
                    <div><?= esc((string) $msg) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($clients === []): ?>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-700">
                Please add at least one client first from <a href="<?= site_url('/portal/agent/clients') ?>" class="font-semibold underline">Clients</a> before creating bookings.
            </div>
        <?php endif; ?>

        <form method="get" action="<?= site_url('/portal/agent/book') ?>" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <label class="text-sm font-medium text-slate-700">Booking Type</label>
            <select name="booking_type" onchange="this.form.submit()" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="package" selected>Package</option>
            </select>
        </form>

        <form action="<?= site_url('/portal/agent/book') ?>" method="post" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <?= csrf_field() ?>
            <input type="hidden" name="booking_type" value="<?= esc($bookingType) ?>">

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-slate-700">Client</label>
                    <select name="client_id" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select client</option>
                        <?php foreach ($clients as $client): ?>
                            <?php $cid = (int) ($client['id'] ?? 0); ?>
                            <option value="<?= esc((string) $cid) ?>" <?= (int) old('client_id') === $cid ? 'selected' : '' ?>>
                                <?= esc((string) ($client['name'] ?? 'Client')) ?><?= trim((string) ($client['passport_no'] ?? '')) !== '' ? ' · ' . esc((string) $client['passport_no']) : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Item</label>
                    <select name="item_id" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select item</option>
                        <?php foreach ($items as $item): ?>
                            <?php $itemId = (int) ($item['id'] ?? 0); ?>
                            <option value="<?= esc((string) $itemId) ?>" <?= (int) old('item_id') === $itemId ? 'selected' : '' ?>>
                                <?= esc((string) ($item['name'] ?? 'Item')) ?> — Agent: PKR <?= esc(number_format((float) ($item['agent_price'] ?? 0), 0)) ?><?= (float) ($item['regular_price'] ?? 0) > 0 ? ' (Regular: PKR ' . esc(number_format((float) ($item['regular_price'] ?? 0), 0)) . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-slate-700">Travelers</label>
                    <input type="number" name="travelers_count" min="1" max="50" value="<?= esc(old('travelers_count') ?: '1') ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Travel Date</label>
                    <input type="date" name="travel_date" value="<?= esc(old('travel_date')) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700">Special Requests</label>
                <textarea name="special_requests" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc(old('special_requests')) ?></textarea>
            </div>

            <button type="submit" class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">Create Agent Booking</button>
        </form>
    </div>
</section>
<?= $this->endSection() ?>
