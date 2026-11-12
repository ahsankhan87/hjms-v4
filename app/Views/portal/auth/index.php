<?php $this->extend('portal/layouts/auth') ?>

<?php $this->section('main') ?>
<?php
$mainCompanyData = function_exists('main_company') ? (main_company() ?? []) : [];
$companyName = trim((string) ($mainCompanyData['name'] ?? 'KARWAN-E-TAIF PVT LTD'));
?>
<main class="min-h-screen grid grid-cols-1 lg:grid-cols-2">
    <div class="relative hidden overflow-hidden lg:flex items-center justify-center bg-slate-900 text-white p-12">
        <div class="absolute -top-20 -left-20 h-72 w-72 rounded-full bg-blue-500/20 blur-3xl"></div>
        <div class="absolute -bottom-24 -right-20 h-72 w-72 rounded-full bg-indigo-400/20 blur-3xl"></div>
        <div class="max-w-md">
            <div class="inline-flex items-center gap-2 rounded-full border border-slate-700 bg-slate-800/70 px-4 py-1.5 text-xs text-slate-200">
                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>ERP Platform
            </div>
            <h1 class="mt-6 text-4xl font-extrabold leading-tight"><?= esc($companyName) ?></h1>
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
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?= esc($companyName) ?></p>
            <h2 class="text-2xl font-bold text-slate-800">Welcome back</h2>
            <p class="mt-1 text-sm text-slate-500">Sign in to continue to your dashboard</p>

            <?php if (!empty($error)): ?>
                <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <?= esc($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= site_url('/admin/login') ?>" class="mt-6 space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" id="email" name="email" value="<?= esc(old('email') ?: '') ?>" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <div style="position:relative;margin-top:0.25rem;">
                        <input type="password" id="password" name="password" required class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" style="box-sizing:border-box;padding-right:3rem;">
                        <button type="button" id="toggle-password" style="position:absolute;top:0;right:0;height:100%;width:2.75rem;display:flex;align-items:center;justify-content:center;border:0;background:transparent;color:#64748b;cursor:pointer;z-index:2;" aria-label="Show password" aria-pressed="false">
                            <svg id="password-eye" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.25 9.75-6.25S21.75 12 21.75 12 18.25 18.25 12 18.25 2.25 12 2.25 12Z" />
                                <circle cx="12" cy="12" r="2.75" />
                            </svg>
                            <svg id="password-eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.8" aria-hidden="true" style="display:none;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 5.9A10.8 10.8 0 0 1 12 5.75c6.25 0 9.75 6.25 9.75 6.25a16 16 0 0 1-3.1 3.85M6.2 6.2C3.7 7.85 2.25 12 2.25 12s3.5 6.25 9.75 6.25c1.25 0 2.4-.28 3.45-.74" />
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300">Sign In</button>
            </form>

            <p class="mt-4 text-sm text-slate-600">
                <a href="<?= site_url('/admin/forgot-password') ?>" class="underline">Forgot password?</a>
            </p>

            <p class="mt-5 text-xs text-slate-500">Need API login? Endpoint available at /api/auth/login</p>
        </div>
    </div>
</main>
<script>
    (function () {
        var password = document.getElementById('password');
        var toggle = document.getElementById('toggle-password');
        var eye = document.getElementById('password-eye');
        var eyeOff = document.getElementById('password-eye-off');

        if (!password || !toggle || !eye || !eyeOff) return;

        toggle.addEventListener('click', function () {
            var showing = password.type === 'password';
            password.type = showing ? 'text' : 'password';
            toggle.setAttribute('aria-label', showing ? 'Hide password' : 'Show password');
            toggle.setAttribute('aria-pressed', showing ? 'true' : 'false');
            eye.style.display = showing ? 'none' : 'block';
            eyeOff.style.display = showing ? 'block' : 'none';
        });
    })();
</script>
<?php $this->endSection() ?>
