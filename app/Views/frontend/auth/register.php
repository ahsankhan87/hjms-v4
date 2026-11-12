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

$pageTitle = (string) ($title ?? 'Register');
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
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            background: radial-gradient(circle at top, #e0f2fe 0%, #f8fafc 42%);
        }
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="min-h-screen text-slate-800">
    <div class="min-h-screen flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-2xl rounded-3xl border border-slate-200/80 bg-white/95 p-7 shadow-2xl shadow-sky-100 backdrop-blur" x-data="{role:'<?= esc(old('role') ?: 'customer') ?>'}">
            <div class="text-center mb-6">
                <a href="<?= site_url('/') ?>" class="inline-flex flex-col items-center">
                    <img src="<?= esc($brandLogo) ?>" alt="<?= esc($brandName) ?>" class="h-16 w-auto object-contain">
                    <span class="mt-2 text-base font-bold text-slate-900"><?= esc($brandName) ?></span>
                </a>
                <h1 class="text-2xl font-bold text-slate-900">Create Your Account</h1>
                <p class="text-sm text-slate-500 mt-1">Choose customer or agent registration</p>
            </div>

            <?php if (! empty($error)): ?>
                <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700"><?= esc($error) ?></div>
            <?php endif; ?>
            <?php if (! empty($errors) && is_array($errors)): ?>
                <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                    <?php foreach ($errors as $msg): ?>
                        <div><?= esc($msg) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="mb-5 grid grid-cols-2 gap-2 rounded-xl bg-slate-100 p-1">
                <button type="button" @click="role='customer'" :class="role==='customer' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600'" class="rounded-lg py-2 text-sm font-semibold transition">Customer (B2C)</button>
                <button type="button" @click="role='agent'" :class="role==='agent' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600'" class="rounded-lg py-2 text-sm font-semibold transition">Agent (B2B)</button>
            </div>

            <form action="<?= site_url('/register') ?>" method="post" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="role" :value="role">

                <div x-show="role === 'customer'" x-cloak>
                    <label class="text-sm font-medium text-slate-700">Full Name</label>
                    <input type="text" name="name" value="<?= esc(old('name')) ?>" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                </div>

                <div x-show="role === 'agent'" x-cloak class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Company Name</label>
                        <input type="text" name="company_name" value="<?= esc(old('company_name')) ?>" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">License Number</label>
                        <input type="text" name="license_number" value="<?= esc(old('license_number')) ?>" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Contact Person</label>
                        <input type="text" name="contact_person" value="<?= esc(old('contact_person')) ?>" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Email</label>
                        <input type="email" name="email" value="<?= esc(old('email')) ?>" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Phone (optional)</label>
                        <input type="text" name="phone" value="<?= esc(old('phone')) ?>" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Password</label>
                        <input type="password" name="password" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Confirm Password</label>
                        <input type="password" name="confirm_password" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                    </div>
                </div>

                <button type="submit" class="w-full rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 text-sm font-semibold transition">Create Account</button>
            </form>

            <p class="text-sm text-slate-600 mt-5 text-center">
                Already have an account?
                <a href="<?= site_url('/login') ?>" class="text-sky-700 font-semibold hover:underline">Login</a>
            </p>
        </div>
    </div>
</body>

</html>
