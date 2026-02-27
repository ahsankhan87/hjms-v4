<?php $this->extend('portal/layouts/auth') ?>

<?php $this->section('main') ?>
<main class="min-h-screen grid grid-cols-1 lg:grid-cols-2">
    <div class="relative hidden overflow-hidden lg:flex items-center justify-center bg-slate-900 text-white p-12">
        <div class="absolute -top-20 -left-20 h-72 w-72 rounded-full bg-blue-500/20 blur-3xl"></div>
        <div class="absolute -bottom-24 -right-20 h-72 w-72 rounded-full bg-indigo-400/20 blur-3xl"></div>
        <div class="max-w-md">
            <div class="inline-flex items-center gap-2 rounded-full border border-slate-700 bg-slate-800/70 px-4 py-1.5 text-xs text-slate-200">
                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>ERP Platform
            </div>
            <h1 class="mt-6 text-4xl font-extrabold leading-tight">Hajj & Umrah ERP</h1>
            <p class="mt-4 text-slate-300">Manage pilgrims, bookings, payments, visa processing, operations, and reports from one modern workspace.</p>
            <ul class="mt-8 space-y-3 text-sm text-slate-200">
                <li>• Secure tenant-based data isolation</li>
                <li>• Complete booking and payment lifecycle</li>
                <li>• Fast operations and visa status workflow</li>
            </ul>
        </div>
    </div>

    <div class="flex items-center justify-center p-6">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/60">
            <h2 class="text-2xl font-bold text-slate-800">Welcome back</h2>
            <p class="mt-1 text-sm text-slate-500">Sign in to continue to your dashboard</p>

            <?php if (!empty($error)): ?>
                <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <?= esc($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= site_url('/app/login') ?>" class="mt-6 space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" id="email" name="email" value="<?= esc(old('email') ?: '') ?>" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <input type="password" id="password" name="password" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                </div>
                <button type="submit" class="btn btn-md btn-primary btn-block">Sign In</button>
            </form>

            <p class="mt-4 text-sm text-slate-600">
                <a href="<?= site_url('/app/forgot-password') ?>" class="underline">Forgot password?</a>
            </p>

            <p class="mt-5 text-xs text-slate-500">Need API login? Endpoint available at /api/auth/login</p>
        </div>
    </div>
</main>
<?php $this->endSection() ?>