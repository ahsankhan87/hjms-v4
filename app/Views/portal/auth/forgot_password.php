<?php $this->extend('portal/layouts/auth') ?>

<?php $this->section('main') ?>
<?php
$mainCompanyData = function_exists('main_company') ? (main_company() ?? []) : [];
$companyName = trim((string) ($mainCompanyData['name'] ?? 'KARWAN-E-TAIF PVT LTD'));
?>
<main class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/60">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?= esc($companyName) ?></p>
        <h2 class="text-2xl font-bold text-slate-800">Forgot Password</h2>
        <p class="mt-1 text-sm text-slate-500">Generate a reset link for your account.</p>

        <?php if (!empty($error)): ?>
            <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div>
        <?php endif; ?>
        <?php if (!empty($resetLink)): ?>
            <div class="mt-4 rounded-lg border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-700 break-all">
                <div class="font-semibold mb-1">Reset Link</div>
                <a class="underline" href="<?= esc($resetLink) ?>"><?= esc($resetLink) ?></a>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= site_url('/forgot-password') ?>" class="mt-6 space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-sm font-medium text-slate-700">Email</label>
                <input type="email" name="email" value="<?= esc(old('email') ?: '') ?>" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
            </div>
            <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300">Generate Reset Link</button>
        </form>

        <p class="mt-5 text-sm text-slate-600"><a href="<?= site_url('/login') ?>" class="underline">Back to login</a></p>
    </div>
</main>
<?php $this->endSection() ?>