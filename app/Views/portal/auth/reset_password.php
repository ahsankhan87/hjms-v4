<?php $this->extend('portal/layouts/auth') ?>

<?php $this->section('main') ?>
<main class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/60">
        <h2 class="text-2xl font-bold text-slate-800">Reset Password</h2>
        <p class="mt-1 text-sm text-slate-500">Set a new password for your account.</p>

        <?php if (!empty($error)): ?>
            <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= site_url('/app/reset-password') ?>" class="mt-6 space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-sm font-medium text-slate-700">Reset Token</label>
                <input type="text" name="token" value="<?= esc(old('token') ?: ($token ?? '')) ?>" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">New Password</label>
                <input type="password" name="password" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Confirm Password</label>
                <input type="password" name="confirm_password" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
            </div>
            <button type="submit" class="btn btn-md btn-primary btn-block">Reset Password</button>
        </form>

        <p class="mt-5 text-sm text-slate-600"><a href="<?= site_url('/app/login') ?>" class="underline">Back to login</a></p>
    </div>
</main>
<?php $this->endSection() ?>