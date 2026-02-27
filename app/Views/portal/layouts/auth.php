<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'HJMS ERP | Login') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.local.css') ?>">
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
        }
    </style>
</head>

<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    <div class="absolute inset-0 -z-10 overflow-hidden">
        <div class="absolute -top-40 -left-20 h-96 w-96 rounded-full bg-blue-200/40 blur-3xl"></div>
        <div class="absolute -bottom-40 -right-20 h-96 w-96 rounded-full bg-slate-300/30 blur-3xl"></div>
    </div>
    <?= $this->renderSection('main') ?>
    <footer class="border-t border-slate-200/80 bg-white/90 px-6 py-3 text-center text-xs text-slate-500 backdrop-blur">
        HJMS ERP © <?= date('Y') ?>
    </footer>
</body>

</html>