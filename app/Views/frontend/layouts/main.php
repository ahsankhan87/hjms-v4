<?php
$company = function_exists('main_company') ? (array) main_company() : [];
$brandName = trim((string) ($company['name'] ?? ''));
if ($brandName === '') {
    $brandName = 'Karwan-e-Taif';
}

$rawLogo = trim((string) ($company['logo_url'] ?? ''));
$brandLogo = $rawLogo !== ''
    ? (function_exists('asset_upload_url') ? asset_upload_url($rawLogo, 'logos') : $rawLogo)
    : base_url('assets/uploads/makkah-logo.png');

$brandEmail = trim((string) ($company['email'] ?? ''));
$brandPhone = trim((string) ($company['phone'] ?? ''));
$brandAddress = trim((string) ($company['address'] ?? ''));
$brandTagline = trim((string) ($company['tagline'] ?? ''));
if ($brandTagline === '') {
    $brandTagline = 'Trusted Hajj & Umrah services with premium support and transparent pricing.';
}

$pageTitle = (string) ($title ?? $brandName);
if (stripos($pageTitle, 'HJMS Travel') !== false) {
    $pageTitle = str_ireplace('HJMS Travel', $brandName, $pageTitle);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome-free-7.0.0-web/css/all.min.css') ?>">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            background: #f8fafc;
        }
    </style>
    <?= $this->renderSection('head') ?>
</head>

<body class="text-slate-800" x-data="{mobileMenu:false}">
    <header class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3">
            <a href="<?= site_url('/') ?>" class="inline-flex items-center gap-2">
                <img src="<?= esc($brandLogo) ?>" alt="<?= esc($brandName) ?>" class="h-10 w-auto object-contain">
                <span class="text-lg font-bold text-slate-900"><?= esc($brandName) ?></span>
            </a>
            <nav class="hidden items-center gap-6 text-sm font-medium md:flex">
                <a href="<?= site_url('/') ?>" class="text-slate-600 hover:text-sky-700">Home</a>
                <a href="<?= site_url('/packages/hajj') ?>" class="text-slate-600 hover:text-sky-700">Hajj</a>
                <a href="<?= site_url('/packages/umrah') ?>" class="text-slate-600 hover:text-sky-700">Umrah</a>
                <a href="<?= site_url('/tickets') ?>" class="text-slate-600 hover:text-sky-700">Tickets</a>
                <a href="<?= site_url('/hotels') ?>" class="text-slate-600 hover:text-sky-700">Hotels</a>
                <a href="<?= site_url('/transport') ?>" class="text-slate-600 hover:text-sky-700">Transport</a>
            </nav>
            <div class="hidden items-center gap-2 md:flex">
                <?php if ((int) session('user_id') > 0): ?>
                    <?php $isAgentRole = strtolower((string) session('role')) === 'agent'; ?>
                    <a href="<?= $isAgentRole ? site_url('/portal/agent/dashboard') : site_url('/portal/dashboard') ?>" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium">Dashboard</a>
                    <form action="<?= site_url('/logout') ?>" method="post">
                        <?= csrf_field() ?>
                        <button type="submit" class="rounded-lg bg-slate-900 px-3 py-1.5 text-sm font-medium text-white">Logout</button>
                    </form>
                <?php else: ?>
                    <a href="<?= site_url('/login') ?>" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium">Login</a>
                    <a href="<?= site_url('/register') ?>" class="rounded-lg bg-sky-600 px-3 py-1.5 text-sm font-medium text-white">Get Started</a>
                <?php endif; ?>
            </div>
            <button type="button" class="rounded-lg border border-slate-300 p-2 md:hidden" @click="mobileMenu=!mobileMenu">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
        <div class="border-t border-slate-200 px-4 py-3 md:hidden" x-show="mobileMenu" x-transition>
            <div class="flex flex-col gap-2 text-sm">
                <a href="<?= site_url('/') ?>">Home</a>
                <a href="<?= site_url('/packages/hajj') ?>">Hajj</a>
                <a href="<?= site_url('/packages/umrah') ?>">Umrah</a>
                <a href="<?= site_url('/tickets') ?>">Tickets</a>
                <a href="<?= site_url('/hotels') ?>">Hotels</a>
                <a href="<?= site_url('/transport') ?>">Transport</a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8">
        <?php if (! empty($success)): ?>
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 px-4 py-2 text-sm"><?= esc($success) ?></div>
        <?php endif; ?>
        <?php if (! empty($error)): ?>
            <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-700 px-4 py-2 text-sm"><?= esc($error) ?></div>
        <?php endif; ?>
        <?= $this->renderSection('content') ?>
    </main>

    <footer class="mt-16 border-t border-slate-200 bg-white">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 md:grid-cols-4">
            <div>
                <h3 class="text-base font-semibold text-slate-900"><?= esc($brandName) ?></h3>
                <p class="mt-2 text-sm text-slate-500"><?= esc($brandTagline) ?></p>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-slate-900">Quick Links</h4>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li><a href="<?= site_url('/packages/hajj') ?>" class="hover:text-sky-700">Hajj Packages</a></li>
                    <li><a href="<?= site_url('/packages/umrah') ?>" class="hover:text-sky-700">Umrah Packages</a></li>
                    <li><a href="<?= site_url('/tickets') ?>" class="hover:text-sky-700">Tickets</a></li>
                    <li><a href="<?= site_url('/hotels') ?>" class="hover:text-sky-700">Hotels</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-slate-900">Contact</h4>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li><i class="fa-solid fa-phone mr-2 text-sky-600"></i> <?= esc($brandPhone !== '' ? $brandPhone : '+92 300 0000000') ?></li>
                    <li><i class="fa-solid fa-envelope mr-2 text-sky-600"></i> <?= esc($brandEmail !== '' ? $brandEmail : 'support@karwanetaif.com') ?></li>
                    <li><i class="fa-solid fa-location-dot mr-2 text-sky-600"></i> <?= esc($brandAddress !== '' ? $brandAddress : 'Peshawar, Pakistan') ?></li>
                </ul>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-slate-900">Payments</h4>
                <div class="mt-3 flex items-center gap-3 text-2xl text-slate-500">
                    <i class="fa-brands fa-cc-visa"></i>
                    <i class="fa-brands fa-cc-mastercard"></i>
                    <i class="fa-brands fa-cc-paypal"></i>
                    <i class="fa-solid fa-building-columns"></i>
                </div>
            </div>
        </div>
        <div class="border-t border-slate-200 py-4 text-center text-xs text-slate-500">
            © <?= date('Y') ?> <?= esc($brandName) ?>. All rights reserved.
        </div>
    </footer>

    <?= $this->renderSection('scripts') ?>
</body>

</html>