<?= $this->extend('frontend/layouts/main') ?>

<?= $this->section('content') ?>
<?php $summary = is_array($summary ?? null) ? $summary : ['total' => 0, 'pending' => 0]; ?>
<section x-data="{sidebarOpen:false}" class="lg:grid lg:grid-cols-12 lg:gap-6">
    <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden" @click="sidebarOpen=false"></div>
    <aside class="fixed inset-y-0 left-0 z-50 w-72 -translate-x-full overflow-y-auto bg-slate-50 p-4 transition-transform duration-200 lg:static lg:col-span-3 lg:w-auto lg:translate-x-0 lg:bg-transparent lg:p-0" :class="sidebarOpen ? 'translate-x-0' : ''">
        <?= $this->include('frontend/portal/agent/_sidebar') ?>
    </aside>

    <div class="lg:col-span-9 space-y-6">
        <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium lg:hidden" @click="sidebarOpen=true">
            <i class="fa-solid fa-bars"></i> Menu
        </button>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h1 class="text-2xl font-bold text-slate-900">Agent Dashboard</h1>
            <p class="mt-1 text-sm text-slate-500">Manage client bookings with agent-specific pricing.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500">Total Agent Bookings</p>
                <p class="mt-2 text-2xl font-bold text-slate-900"><?= esc((string) (int) ($summary['total'] ?? 0)) ?></p>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-amber-700">Pending Confirmations</p>
                <p class="mt-2 text-2xl font-bold text-amber-800"><?= esc((string) (int) ($summary['pending'] ?? 0)) ?></p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Quick Book Links</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <a href="<?= site_url('/portal/agent/book?booking_type=package') ?>" class="rounded-xl border border-slate-200 p-3 text-sm font-medium text-slate-700 hover:border-sky-300 hover:text-sky-700">Book Package for Client</a>
                <a href="<?= site_url('/portal/agent/book?booking_type=ticket') ?>" class="rounded-xl border border-slate-200 p-3 text-sm font-medium text-slate-700 hover:border-sky-300 hover:text-sky-700">Book Ticket for Client</a>
                <a href="<?= site_url('/portal/agent/book?booking_type=hotel') ?>" class="rounded-xl border border-slate-200 p-3 text-sm font-medium text-slate-700 hover:border-sky-300 hover:text-sky-700">Book Hotel for Client</a>
                <a href="<?= site_url('/portal/agent/book?booking_type=transport') ?>" class="rounded-xl border border-slate-200 p-3 text-sm font-medium text-slate-700 hover:border-sky-300 hover:text-sky-700">Book Transport for Client</a>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
