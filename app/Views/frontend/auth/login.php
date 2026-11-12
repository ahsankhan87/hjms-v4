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

$pageTitle = (string) ($title ?? 'Login');
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
    <style>
        body {
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            background: radial-gradient(circle at top, #e0f2fe 0%, #f8fafc 42%);
        }
    </style>
</head>

<body class="min-h-screen text-slate-800">
    <div class="min-h-screen flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-md rounded-3xl border border-slate-200/80 bg-white/95 p-7 shadow-2xl shadow-sky-100 backdrop-blur">
            <div class="mb-6 text-center">
                <a href="<?= site_url('/') ?>" class="inline-flex flex-col items-center">
                    <img src="<?= esc($brandLogo) ?>" alt="<?= esc($brandName) ?>" class="h-16 w-auto object-contain">
                    <span class="mt-2 text-base font-bold text-slate-900"><?= esc($brandName) ?></span>
                </a>
                <h1 class="mt-4 text-2xl font-bold text-slate-900">Welcome Back</h1>
                <p class="mt-1 text-sm text-slate-500">Login to your customer or agent portal</p>
            </div>

            <?php if (! empty($success)): ?>
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700"><?= esc($success) ?></div>
            <?php endif; ?>
            <?php if (! empty($error)): ?>
                <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700"><?= esc($error) ?></div>
            <?php endif; ?>

            <form action="<?= site_url('/login') ?>" method="post" class="space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label class="text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" value="<?= esc(old('email')) ?>" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Password</label>
                    <input type="password" name="password" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                </div>
                <button type="submit" class="w-full rounded-xl bg-sky-600 hover:bg-sky-700 text-white py-2.5 text-sm font-semibold transition">Sign In</button>
            </form>

            <p class="text-sm text-slate-600 mt-5 text-center">
                Don’t have an account?
                <a href="<?= site_url('/register') ?>" class="text-sky-700 font-semibold hover:underline">Create one</a>
            </p>
        </div>
    </div>
</body>

</html>
