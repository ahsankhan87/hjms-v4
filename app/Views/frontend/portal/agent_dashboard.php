<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Agent Dashboard') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.local.css') ?>">
</head>

<body class="min-h-screen bg-slate-50 text-slate-800">
    <div class="max-w-5xl mx-auto px-4 py-10">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-bold">Agent Dashboard</h1>
            <p class="text-sm text-slate-500 mt-1">Welcome, <?= esc($name ?? 'Agent') ?> (<?= esc(strtoupper($role ?? 'agent')) ?>)</p>
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <h2 class="font-semibold text-emerald-800">Role-based Pricing Prepared</h2>
                <p class="text-sm text-emerald-700 mt-1">
                    Agent-specific pricing access is enabled for this role:
                    <strong><?= ! empty($canSeeAgentPricing) ? 'YES' : 'NO' ?></strong>.
                </p>
            </div>
            <form action="<?= site_url('/logout') ?>" method="post" class="mt-6">
                <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Logout</button>
            </form>
        </div>
    </div>
</body>

</html>
