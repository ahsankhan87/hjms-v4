<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Payment Receipt') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome-free-7.0.0-web/css/all.min.css') ?>">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 24px;
            background: #f5f7fb;
            color: #1f2937;
        }

        .actions {
            max-width: 860px;
            margin: 0 auto 12px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .btn {
            border: 1px solid #0f4a8a;
            background: #18589f;
            color: #fff;
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            cursor: pointer;
        }

        .btn.secondary {
            background: #fff;
            color: #18589f;
        }

        .sheet {
            max-width: 860px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #dbe3ef;
            box-shadow: 0 8px 20px -12px rgba(30, 41, 59, .3);
        }

        .header {
            padding: 18px;
            border-bottom: 2px solid #194f90;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            color: #113d77;
            font-size: 30px;
        }

        .header p {
            margin: 4px 0;
            font-size: 13px;
            color: #334155;
        }

        .meta {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #dbe3ef;
            padding: 10px 14px;
            font-size: 14px;
            color: #113d77;
            font-weight: 700;
        }

        .content {
            padding: 14px;
        }

        .section-title {
            margin: 10px 0 0;
            padding: 7px 10px;
            background: #154f95;
            color: #fff;
            font-size: 18px;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 8px;
        }

        th,
        td {
            border: 1px solid #9fb7d8;
            padding: 7px 8px;
            text-align: left;
        }

        th {
            background: #1b4f8f;
            color: #fff;
            font-weight: 700;
            width: 32%;
        }

        .amount {
            font-size: 20px;
            font-weight: 700;
            color: #0f4a8a;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .actions {
                display: none;
            }

            .sheet {
                border: none;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    <?php
    $companyData = is_array($company ?? null) ? $company : [];
    $companyName = (string) ($companyData['name'] ?? 'KARWAN-E-TAIF PVT LTD');
    $companyAddress = (string) ($companyData['address'] ?? '');
    $companyTagline = (string) ($companyData['tagline'] ?? 'Hajj & Umrah Management');
    $companyPhone = (string) ($companyData['phone'] ?? '');
    $companyEmail = (string) ($companyData['email'] ?? '');
    $companySaudiPartner = (string) ($companyData['saudi_partner'] ?? '');
    $companyLogo = (string) ($companyData['logo_url'] ?? '');
    $companyNtn = (string) ($companyData['ntn'] ?? '');
    $companyStrn = (string) ($companyData['strn'] ?? '');
    ?>

    <div class="actions">
        <a class="btn secondary" href="<?= site_url('/payments') ?>"><i class="fa-solid fa-arrow-left"></i> Back</a>
        <button type="button" class="btn" onclick="window.print()"><i class="fa-solid fa-print"></i> Print Receipt</button>
    </div>

    <article class="sheet">
        <header class="header">
            <?php if ($companyLogo !== ''): ?><p><img src="<?= esc($companyLogo) ?>" alt="Company Logo" style="max-width: 180px; max-height: 90px; object-fit: contain;"></p><?php endif; ?>
            <h1><?= esc($companyName) ?></h1>
            <p><?= esc($companyTagline) ?></p>
            <?php if ($companyAddress !== ''): ?><p><?= esc($companyAddress) ?></p><?php endif; ?>
            <?php if ($companyPhone !== '' || $companyEmail !== ''): ?><p><?= esc(trim($companyPhone . ($companyPhone !== '' && $companyEmail !== '' ? ' | ' : '') . $companyEmail)) ?></p><?php endif; ?>
            <?php if ($companySaudiPartner !== ''): ?><p>Saudi Company: <?= esc($companySaudiPartner) ?></p><?php endif; ?>
            <?php if ($companyNtn !== '' || $companyStrn !== ''): ?><p><?= esc(trim(($companyNtn !== '' ? 'NTN: ' . $companyNtn : '') . (($companyNtn !== '' && $companyStrn !== '') ? ' | ' : '') . ($companyStrn !== '' ? 'STRN: ' . $companyStrn : ''))) ?></p><?php endif; ?>
        </header>

        <div class="meta">
            <div>Receipt No: <?= esc($receiptNo ?? '') ?></div>
            <div>Date: <?= esc($receiptDate ?? '') ?></div>
        </div>

        <div class="content">
            <div class="section-title">Payment Details</div>
            <table>
                <tbody>
                    <tr>
                        <th>Payment No</th>
                        <td><?= esc((string) ($payment['payment_no'] ?? '')) ?></td>
                    </tr>
                    <tr>
                        <th>Booking No</th>
                        <td><?= esc((string) ($payment['booking_no'] ?? '')) ?></td>
                    </tr>
                    <tr>
                        <th>Agent</th>
                        <td><?= esc((string) ($payment['agent_name'] ?? '-')) ?></td>
                    </tr>
                    <tr>
                        <th>Package</th>
                        <td><?= esc((string) ($payment['package_code'] ?? '')) ?> - <?= esc((string) ($payment['package_name'] ?? '')) ?></td>
                    </tr>
                    <tr>
                        <th>Payment Type</th>
                        <td><?= esc(ucfirst((string) ($payment['payment_type'] ?? 'payment'))) ?></td>
                    </tr>
                    <tr>
                        <th>Channel</th>
                        <td><?= esc(ucfirst((string) ($payment['channel'] ?? 'manual'))) ?></td>
                    </tr>
                    <tr>
                        <th>Gateway Reference</th>
                        <td><?= esc((string) ($payment['gateway_reference'] ?? '-')) ?></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td><?= esc((string) ($payment['status'] ?? '-')) ?></td>
                    </tr>
                    <tr>
                        <th>Payment Date</th>
                        <td><?= esc((string) ($payment['payment_date'] ?? '')) ?></td>
                    </tr>
                    <tr>
                        <th>Amount</th>
                        <td class="amount"><?= esc(number_format((float) ($payment['amount'] ?? 0), 2)) ?></td>
                    </tr>
                    <tr>
                        <th>Booking Paid To Date</th>
                        <td><?= esc(number_format((float) ($bookingPaidAmount ?? 0), 2)) ?></td>
                    </tr>
                    <tr>
                        <th>Booking Outstanding</th>
                        <td><?= esc(number_format((float) ($bookingOutstandingAmount ?? 0), 2)) ?></td>
                    </tr>
                    <tr>
                        <th>Note</th>
                        <td><?= esc((string) ($payment['note'] ?? $payment['booking_remarks'] ?? '')) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </article>
</body>

</html>