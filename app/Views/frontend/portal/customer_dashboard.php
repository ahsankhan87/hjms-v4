<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Customer Dashboard') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.local.css') ?>">
</head>

<body class="min-h-screen bg-slate-50 text-slate-800">
    <div class="max-w-5xl mx-auto px-4 py-10">
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-sm text-rose-700"><?= esc((string) session()->getFlashdata('error')) ?></div>
        <?php endif; ?>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-bold">Customer Dashboard</h1>
            <p class="text-sm text-slate-500 mt-1">Welcome, <?= esc($name ?? 'User') ?> (<?= esc(strtoupper($role ?? 'customer')) ?>)</p>
            <div class="mt-6">
                <a href="<?= site_url('/portal/agent/dashboard') ?>" class="text-sm text-slate-400 pointer-events-none">Agent pricing area locked for customer role.</a>
            </div>
            <form action="<?= site_url('/logout') ?>" method="post" class="mt-6">
                <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Logout</button>
            </form>
        </div>
    </div>
</body>

</html>
