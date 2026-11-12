<?php $active = (string) ($portalActive ?? 'dashboard'); ?>
<div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="mb-4 border-b border-slate-100 pb-3">
        <p class="text-xs uppercase tracking-wide text-slate-500">Customer Portal</p>
        <p class="mt-1 text-sm font-semibold text-slate-900"><?= esc((string) (session('name') ?? 'Customer')) ?></p>
    </div>
    <nav class="space-y-1 text-sm">
        <a href="<?= site_url('/portal/dashboard') ?>" class="<?= $active === 'dashboard' ? 'bg-sky-50 text-sky-700' : 'text-slate-700 hover:bg-slate-50' ?> block rounded-lg px-3 py-2 font-medium">Dashboard</a>
        <a href="<?= site_url('/portal/bookings') ?>" class="<?= $active === 'bookings' ? 'bg-sky-50 text-sky-700' : 'text-slate-700 hover:bg-slate-50' ?> block rounded-lg px-3 py-2 font-medium">My Bookings</a>
        <a href="<?= site_url('/portal/profile') ?>" class="<?= $active === 'profile' ? 'bg-sky-50 text-sky-700' : 'text-slate-700 hover:bg-slate-50' ?> block rounded-lg px-3 py-2 font-medium">Profile Settings</a>
    </nav>
    <form action="<?= site_url('/logout') ?>" method="post" class="mt-4">
        <?= csrf_field() ?>
        <button type="submit" class="w-full rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">Logout</button>
    </form>
</div>
