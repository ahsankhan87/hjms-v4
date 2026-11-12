<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= esc($title ?? 'Voucher Not Found') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome-free-7.0.0-web/css/all.min.css') ?>">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            color: #1f2937;
            padding: 16px;
        }

        .card {
            max-width: 420px;
            width: 100%;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 24px;
            text-align: center;
            box-shadow: 0 10px 20px -18px rgba(15, 23, 42, .5);
        }

        .icon {
            font-size: 28px;
            color: #dc2626;
            margin-bottom: 10px;
        }

        h1 {
            font-size: 16px;
            margin: 0 0 6px;
            color: #113d77;
        }

        p {
            font-size: 12px;
            line-height: 1.5;
            color: #475569;
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="icon"><i class="fa-solid fa-circle-xmark"></i></div>
        <h1>Voucher Not Available</h1>
        <p>This voucher link is invalid, has been revoked, or the voucher is not issued yet. Please contact the travel agent that shared this code.</p>
    </div>
</body>

</html>
