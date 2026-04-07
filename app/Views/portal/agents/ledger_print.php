<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc((string) ($title ?? 'Ledger Print')) ?></title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #0f172a;
            background: #ffffff;
            font-size: 12px;
        }

        .sheet {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            padding: 18px;
        }

        .top {
            border: 1px solid #cbd5e1;
            padding: 12px 14px;
            margin-bottom: 10px;
        }

        .top h1 {
            margin: 0;
            font-size: 22px;
            letter-spacing: 0.04em;
        }

        .top p {
            margin: 3px 0 0;
            color: #334155;
        }

        .heading {
            border: 1px solid #94a3b8;
            background: #e2e8f0;
            text-align: center;
            padding: 6px;
            font-weight: 700;
            font-size: 22px;
            margin-bottom: 10px;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .meta td {
            padding: 2px 4px;
            vertical-align: top;
            font-size: 12px;
        }

        .meta .label {
            font-weight: 700;
            width: 130px;
        }

        .meta .value {
            width: 40%;
        }

        .ledger {
            width: 100%;
            border-collapse: collapse;
        }

        .ledger th {
            border: 1px solid #334155;
            background: #f1f5f9;
            padding: 6px 5px;
            text-align: left;
            font-size: 12px;
        }

        .ledger td {
            border-bottom: 1px dotted #94a3b8;
            padding: 5px;
            font-size: 12px;
            vertical-align: top;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .text-debit {
            color: #9f1239;
            font-weight: 700;
        }

        .text-credit {
            color: #166534;
            font-weight: 700;
        }

        .text-negative {
            color: #b91c1c;
            font-weight: 700;
        }

        .totals {
            margin-top: 10px;
            width: 100%;
            border-collapse: collapse;
        }

        .totals td {
            padding: 6px 4px;
            font-size: 16px;
            font-weight: 700;
        }

        .actions {
            margin: 0 auto;
            max-width: 1100px;
            padding: 10px 18px 0;
            display: flex;
            gap: 8px;
        }

        .btn {
            display: inline-block;
            border: 1px solid #475569;
            background: #0f172a;
            color: #ffffff;
            text-decoration: none;
            padding: 8px 10px;
            font-size: 12px;
            font-weight: 700;
        }

        .btn.alt {
            background: #ffffff;
            color: #0f172a;
        }

        @media print {
            .actions {
                display: none;
            }

            .sheet {
                max-width: 100%;
                padding: 0;
            }

            body {
                margin: 8mm;
            }

            @page {
                size: A4 landscape;
                margin: 8mm;
            }
        }
    </style>
</head>

<body>
    <div class="actions">
        <button class="btn" onclick="window.print()">Print / Save PDF</button>
        <a class="btn alt" href="<?= site_url('/agents/' . (int) ($agent['id'] ?? 0) . '/ledger') ?>">Back to Ledger</a>
    </div>

    <main class="sheet">
        <section class="top">
            <h1>HJMS ERP</h1>
            <p>Agent ledger statement</p>
        </section>

        <section class="heading">Account Ledger</section>

        <table class="meta">
            <tr>
                <td class="label">Account Code:</td>
                <td class="value"><?= esc((string) ($agent['code'] ?? 'N/A')) ?></td>
                <td class="label">Closing Balance:</td>
                <td class="right"><?= esc(number_format((float) ($closingBalance ?? 0), 2)) ?></td>
            </tr>
            <tr>
                <td class="label">Account Title:</td>
                <td class="value"><?= esc((string) ($agent['name'] ?? 'N/A')) ?></td>
                <td class="label">Entries:</td>
                <td class="right"><?= esc((string) ($entryCount ?? 0)) ?></td>
            </tr>
            <tr>
                <td class="label">Period:</td>
                <td class="value"><?= !empty($periodFrom) ? esc((string) $periodFrom) : '-' ?> to <?= !empty($periodTo) ? esc((string) $periodTo) : '-' ?></td>
                <td class="label">Print Date:</td>
                <td class="right"><?= esc(date('Y-m-d')) ?></td>
            </tr>
        </table>

        <table class="ledger">
            <thead>
                <tr>
                    <th style="width: 82px;">Date</th>
                    <th style="width: 85px;">Type</th>
                    <th style="width: 90px;">Trans.#</th>
                    <th>Particulars</th>
                    <th style="width: 100px;">Inv/Ref</th>
                    <th class="right" style="width: 95px;">Debit</th>
                    <th class="right" style="width: 95px;">Credit</th>
                    <th class="right" style="width: 95px;">Balance</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($rows)): foreach ($rows as $row): ?>
                        <?php
                        $entryType = (string) ($row['entry_type'] ?? '');
                        $referenceType = (string) ($row['reference_type'] ?? '');
                        $referenceId = (int) ($row['reference_id'] ?? 0);
                        $debitAmount = (float) ($row['debit_amount'] ?? 0);
                        $creditAmount = (float) ($row['credit_amount'] ?? 0);
                        $runningBalance = (float) ($row['running_balance'] ?? 0);
                        ?>
                        <tr>
                            <td><?= esc((string) ($row['entry_date'] ?? '')) ?></td>
                            <td><?= esc(str_replace('_', ' ', $entryType)) ?></td>
                            <td><?= $referenceId > 0 ? esc((string) $referenceId) : '-' ?></td>
                            <td><?= esc((string) ($row['description'] ?? '-')) ?></td>
                            <td>
                                <?php if ($referenceType === 'booking' && $referenceId > 0): ?>
                                    BK-<?= esc((string) $referenceId) ?>
                                <?php elseif ($referenceType === 'payment' && $referenceId > 0): ?>
                                    PM-<?= esc((string) $referenceId) ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="right <?= $debitAmount > 0 ? 'text-debit' : '' ?>"><?= esc(number_format($debitAmount, 2)) ?></td>
                            <td class="right <?= $creditAmount > 0 ? 'text-credit' : '' ?>"><?= esc(number_format($creditAmount, 2)) ?></td>
                            <td class="right <?= $runningBalance < 0 ? 'text-negative' : '' ?>"><?= esc(number_format($runningBalance, 2)) ?></td>
                        </tr>
                    <?php endforeach;
                else: ?>
                    <tr>
                        <td colspan="8" class="center" style="padding: 18px;">No ledger entries found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td>Total</td>
                <td class="right">Debit: <?= esc(number_format((float) ($totalDebit ?? 0), 2)) ?></td>
                <td class="right">Credit: <?= esc(number_format((float) ($totalCredit ?? 0), 2)) ?></td>
                <td class="right">Balance: <?= esc(number_format((float) ($closingBalance ?? 0), 2)) ?></td>
            </tr>
        </table>
    </main>

    <?php if (!empty($autoPrint)): ?>
        <script>
            window.addEventListener('load', function() {
                window.print();
            });
        </script>
    <?php endif; ?>
</body>

</html>