<?= $this->extend('frontend/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$user = is_array($user ?? null) ? $user : [];
$errors = is_array($errors ?? null) ? $errors : [];
?>
<section x-data="{sidebarOpen:false}" class="lg:grid lg:grid-cols-12 lg:gap-6">
    <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden" @click="sidebarOpen=false"></div>
    <aside class="fixed inset-y-0 left-0 z-50 w-72 -translate-x-full overflow-y-auto bg-slate-50 p-4 transition-transform duration-200 lg:static lg:col-span-3 lg:w-auto lg:translate-x-0 lg:bg-transparent lg:p-0" :class="sidebarOpen ? 'translate-x-0' : ''">
        <?= $this->include('frontend/portal/_sidebar') ?>
    </aside>

    <div class="lg:col-span-9 space-y-6">
        <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium lg:hidden" @click="sidebarOpen=true">
            <i class="fa-solid fa-bars"></i> Menu
        </button>

        <div>
            <h1 class="text-2xl font-bold text-slate-900">Profile Settings</h1>
            <p class="text-sm text-slate-500">Update your account details and password.</p>
        </div>

        <?php if ($errors !== []): ?>
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <?php foreach ($errors as $msg): ?>
                    <div><?= esc((string) $msg) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('/portal/profile') ?>" method="post" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <?= csrf_field() ?>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-slate-700">Full Name</label>
                    <input type="text" name="name" value="<?= esc(old('name') ?: (string) ($user['name'] ?? '')) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Email</label>
                    <input type="email" value="<?= esc((string) ($user['email'] ?? '')) ?>" disabled class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500">
                </div>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">Phone</label>
                <input type="text" name="phone" value="<?= esc(old('phone') ?: (string) ($user['phone'] ?? '')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div class="border-t border-slate-200 pt-4">
                <h2 class="text-base font-semibold text-slate-900">Change Password</h2>
                <p class="mt-1 text-xs text-slate-500">Leave password fields blank if you do not want to change your password.</p>
                <div class="mt-3 grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Current Password</label>
                        <input type="password" name="current_password" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">New Password</label>
                        <input type="password" name="new_password" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Confirm Password</label>
                        <input type="password" name="confirm_password" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
            </div>

            <button type="submit" class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">Save Changes</button>
        </form>
    </div>
</section>
<?= $this->endSection() ?>
