<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc((string) ($title ?? 'Agent Ledger 2 Print')) ?></title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #0f172a;
        }

        .actions {
            max-width: 1320px;
            margin: 0 auto;
            padding: 10px 14px 0;
            display: flex;
            gap: 8px;
        }

        .btn {
            display: inline-block;
            border: 1px solid #475569;
            background: #0f172a;
            color: #fff;
            text-decoration: none;
            padding: 7px 10px;
            font-size: 11px;
            font-weight: 700;
        }

        .btn.alt {
            background: #fff;
            color: #0f172a;
        }

        .sheet {
            max-width: 1320px;
            margin: 0 auto;
            padding: 14px;
        }

        .card {
            border: 1px solid #cbd5e1;
            margin-bottom: 10px;
        }

        .card h3 {
            margin: 0;
            padding: 7px 9px;
            background: #f1f5f9;
            border-bottom: 1px solid #cbd5e1;
            font-size: 12px;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
        }

        .meta td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
        }

        .summary-grid {
            display: grid;
            grid-auto-flow: column;
            grid-auto-columns: 1fr;
            gap: 6px;
            padding: 8px;
        }

        .summary-card {
            border: 1px solid #cbd5e1;
            padding: 6px;
            min-height: 64px;
        }

        .summary-card .label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            line-height: 1.2;
        }

        .summary-card .value {
            margin-top: 6px;
            font-size: 14px;
            font-weight: 700;
            line-height: 1;
        }

        .summary-card .hint {
            margin-top: 3px;
            font-size: 9px;
            line-height: 1.2;
        }

        .tone-slate {
            background: #f8fafc;
            color: #334155;
        }

        .tone-amber {
            background: #fffbeb;
            color: #92400e;
        }

        .tone-emerald {
            background: #ecfdf5;
            color: #166534;
        }

        .tone-rose {
            background: #fff1f2;
            color: #be123c;
        }

        .tone-blue {
            background: #f0f9ff;
            color: #0369a1;
        }

        table.tbl {
            width: 100%;
            border-collapse: collapse;
        }

        .tbl th {
            border: 1px solid #334155;
            background: #f8fafc;
            padding: 5px;
            text-align: left;
        }

        .tbl td {
            border-bottom: 1px dotted #94a3b8;
            padding: 5px;
            vertical-align: top;
        }

        .right {
            text-align: right;
        }

        .total-row td {
            border-top: 1px solid #334155;
            border-bottom: 1px solid #334155;
            font-weight: 700;
            background: #f8fafc;
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
                margin: 7mm;
            }

            @page {
                size: A4 landscape;
                margin: 7mm;
            }
        }
    </style>
</head>

<body>
    <div class="actions">
        <button class="btn" onclick="window.print()">Print / Save PDF</button>
        <a class="btn alt" href="<?= site_url('/agents/' . (int) ($agent['id'] ?? 0) . '/ledger/second?from=' . urlencode((string) ($filterFrom ?? '')) . '&to=' . urlencode((string) ($filterTo ?? ''))) ?>">Back to Ledger 2</a>
    </div>

    <main class="sheet">
        <section class="card">
            <h3>Agent Ledger 2</h3>
            <table class="meta">
                <tr>
                    <td><strong>Agent:</strong> <?= esc((string) ($agent['name'] ?? 'N/A')) ?></td>
                    <td><strong>Code:</strong> <?= esc((string) ($agent['code'] ?? 'N/A')) ?></td>
                    <td><strong>Print Date:</strong> <?= esc(date('Y-m-d')) ?></td>
                </tr>
                <tr>
                    <td><strong>From:</strong> <?= esc((string) ($filterFrom ?? '-')) ?></td>
                    <td><strong>To:</strong> <?= esc((string) ($filterTo ?? '-')) ?></td>
                    <td><strong>Closing Balance:</strong> <?= esc(number_format((float) ($summary['closingBalance'] ?? 0), 2)) ?></td>
                </tr>
            </table>
        </section>

        <section class="card">
            <h3>Summary</h3>
            <?php
            $summaryCards = [
                ['label' => 'Op Bal', 'value' => (float) ($summary['openingBalance'] ?? 0), 'tone' => 'tone-slate', 'hint' => 'B/F balance'],
                ['label' => 'Tickets', 'value' => (float) ($summary['ticketSaleTotal'] ?? 0), 'tone' => 'tone-amber', 'hint' => 'Ticket receivable'],
            ];

            $optionalPrintCards = [
                ['label' => 'Hotels', 'value' => (float) ($summary['hotelSaleTotal'] ?? 0), 'tone' => 'tone-amber', 'hint' => 'Hotel receivable'],
                ['label' => 'Visas', 'value' => (float) ($summary['visaSaleTotal'] ?? 0), 'tone' => 'tone-amber', 'hint' => 'Visa receivable'],
                ['label' => 'Transport', 'value' => (float) ($summary['transportSaleTotal'] ?? 0), 'tone' => 'tone-amber', 'hint' => 'Transport receivable'],
                ['label' => 'Booking', 'value' => (float) ($summary['bookingSaleTotal'] ?? 0), 'tone' => 'tone-amber', 'hint' => 'Booking receivable'],
            ];

            foreach ($optionalPrintCards as $item) {
                if (abs((float) ($item['value'] ?? 0)) > 0.00001) {
                    $summaryCards[] = $item;
                }
            }

            $summaryCards[] = ['label' => 'Vchr', 'value' => (float) ($summary['voucherTotal'] ?? 0), 'tone' => 'tone-emerald', 'hint' => 'Receipts'];
            $summaryCards[] = ['label' => 'CN', 'value' => (float) ($summary['creditNoteTotal'] ?? 0), 'tone' => 'tone-rose', 'hint' => 'Credit notes'];
            $summaryCards[] = ['label' => 'Cl Bal', 'value' => (float) ($summary['closingBalance'] ?? 0), 'tone' => 'tone-blue', 'hint' => 'Net balance'];
            ?>
            <div class="summary-grid">
                <?php foreach ($summaryCards as $item): ?>
                    <div class="summary-card <?= esc((string) ($item['tone'] ?? 'tone-slate')) ?>">
                        <div class="label"><?= esc((string) ($item['label'] ?? '')) ?></div>
                        <div class="value"><?= esc(number_format((float) ($item['value'] ?? 0), 2)) ?></div>
                        <div class="hint"><?= esc((string) ($item['hint'] ?? '')) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="card">
            <h3>Ticket Sale</h3>
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Pax</th>
                        <th class="right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($ticketSales)): foreach ($ticketSales as $row): ?>
                            <tr>
                                <td><?= esc((string) ($row['invoice_no'] ?? ('S' . (int) ($row['id'] ?? 0)))) ?></td>
                                <td><?= esc((string) ($row['sale_date'] ?? '')) ?></td>
                                <td><?= esc((string) ($row['pax_name'] ?? '-')) ?></td>
                                <td class="right"><?= esc(number_format((float) ($row['amount'] ?? 0), 2)) ?></td>
                            </tr>
                        <?php endforeach;
                    else: ?>
                        <tr>
                            <td colspan="4">No Ticket Sale Found.</td>
                        </tr>
                    <?php endif; ?>
                    <tr class="total-row">
                        <td colspan="3" class="right">Total</td>
                        <td class="right"><?= esc(number_format((float) ($tableTotals['ticketSales'] ?? 0), 2)) ?></td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="card">
            <h3>Hotel Sale</h3>
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Pax</th>
                        <th class="right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($hotelSales)): foreach ($hotelSales as $row): ?>
                            <tr>
                                <td><?= esc((string) ($row['invoice_no'] ?? ('HS' . (int) ($row['id'] ?? 0)))) ?></td>
                                <td><?= esc((string) ($row['sale_date'] ?? '')) ?></td>
                                <td><?= esc((string) ($row['pax_name'] ?? '-')) ?></td>
                                <td class="right"><?= esc(number_format((float) ($row['amount'] ?? 0), 2)) ?></td>
                            </tr>
                        <?php endforeach;
                    else: ?>
                        <tr>
                            <td colspan="4">No Hotel Sale Found.</td>
                        </tr>
                    <?php endif; ?>
                    <tr class="total-row">
                        <td colspan="3" class="right">Total</td>
                        <td class="right"><?= esc(number_format((float) ($tableTotals['hotelSales'] ?? 0), 2)) ?></td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="card">
            <h3>Visa Sale</h3>
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Pax</th>
                        <th class="right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($visaSales)): foreach ($visaSales as $row): ?>
                            <tr>
                                <td><?= esc((string) ($row['invoice_no'] ?? ('VS' . (int) ($row['id'] ?? 0)))) ?></td>
                                <td><?= esc((string) ($row['sale_date'] ?? '')) ?></td>
                                <td><?= esc((string) ($row['pax_name'] ?? '-')) ?></td>
                                <td class="right"><?= esc(number_format((float) ($row['amount'] ?? 0), 2)) ?></td>
                            </tr>
                        <?php endforeach;
                    else: ?>
                        <tr>
                            <td colspan="4">No Visa Sale Found.</td>
                        </tr>
                    <?php endif; ?>
                    <tr class="total-row">
                        <td colspan="3" class="right">Total</td>
                        <td class="right"><?= esc(number_format((float) ($tableTotals['visaSales'] ?? 0), 2)) ?></td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="card">
            <h3>Transport Sale</h3>
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Pax</th>
                        <th class="right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($transportSales)): foreach ($transportSales as $row): ?>
                            <tr>
                                <td><?= esc((string) ($row['invoice_no'] ?? ('TS' . (int) ($row['id'] ?? 0)))) ?></td>
                                <td><?= esc((string) ($row['sale_date'] ?? '')) ?></td>
                                <td><?= esc((string) ($row['pax_name'] ?? '-')) ?></td>
                                <td class="right"><?= esc(number_format((float) ($row['amount'] ?? 0), 2)) ?></td>
                            </tr>
                        <?php endforeach;
                    else: ?>
                        <tr>
                            <td colspan="4">No Transport Sale Found.</td>
                        </tr>
                    <?php endif; ?>
                    <tr class="total-row">
                        <td colspan="3" class="right">Total</td>
                        <td class="right"><?= esc(number_format((float) ($tableTotals['transportSales'] ?? 0), 2)) ?></td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="card">
            <h3>General Booking Sale</h3>
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Booking #</th>
                        <th>Date</th>
                        <th>Pax</th>
                        <th class="right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($generalSales)): foreach ($generalSales as $row): ?>
                            <tr>
                                <td><?= esc((string) ($row['booking_no'] ?? ('B' . (int) ($row['id'] ?? 0)))) ?></td>
                                <td><?= esc((string) ($row['inv_date'] ?? '')) ?></td>
                                <td><?= esc((string) ($row['pax_name'] ?? '-')) ?></td>
                                <td class="right"><?= esc(number_format((float) ($row['total_amount'] ?? 0), 2)) ?></td>
                            </tr>
                        <?php endforeach;
                    else: ?>
                        <tr>
                            <td colspan="4">No General Booking Sale Found.</td>
                        </tr>
                    <?php endif; ?>
                    <tr class="total-row">
                        <td colspan="3" class="right">Total</td>
                        <td class="right"><?= esc(number_format((float) ($tableTotals['generalSales'] ?? 0), 2)) ?></td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="card">
            <h3>Voucher</h3>
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Voucher #</th>
                        <th>Date</th>
                        <th>Remarks</th>
                        <th class="right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($vouchers)): foreach ($vouchers as $row): ?>
                            <tr>
                                <td><?= esc((string) ($row['payment_no'] ?? ('V' . (int) ($row['id'] ?? 0)))) ?></td>
                                <td><?= esc((string) ($row['payment_date'] ?? '')) ?></td>
                                <td><?= esc((string) ($row['note'] ?? '-')) ?></td>
                                <td class="right"><?= esc(number_format((float) ($row['signed_amount'] ?? 0), 2)) ?></td>
                            </tr>
                        <?php endforeach;
                    else: ?>
                        <tr>
                            <td colspan="4">No Voucher Found.</td>
                        </tr>
                    <?php endif; ?>
                    <tr class="total-row">
                        <td colspan="3" class="right">Total</td>
                        <td class="right"><?= esc(number_format((float) ($tableTotals['vouchers'] ?? 0), 2)) ?></td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="card">
            <h3>Credit Note</h3>
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Pax</th>
                        <th class="right">Base Fare</th>
                        <th class="right">Tax</th>
                        <th class="right">Discount</th>
                        <th class="right">PSF/WHT</th>
                        <th class="right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($creditNotes)): foreach ($creditNotes as $row): ?>
                            <tr>
                                <td><?= esc((string) ($row['invoice_no'] ?? ('CN' . (int) ($row['id'] ?? 0)))) ?></td>
                                <td><?= esc((string) ($row['credit_note_date'] ?? '')) ?></td>
                                <td><?= esc((string) ($row['pax_name'] ?? '-')) ?></td>
                                <td class="right"><?= esc(number_format((float) ($row['base_fare'] ?? 0), 2)) ?></td>
                                <td class="right"><?= esc(number_format((float) ($row['tax'] ?? 0), 2)) ?></td>
                                <td class="right"><?= esc(number_format((float) ($row['discount'] ?? 0), 2)) ?></td>
                                <td class="right"><?= esc(number_format((float) ($row['psf_wht'] ?? 0), 2)) ?></td>
                                <td class="right"><?= esc(number_format((float) ($row['amount'] ?? 0), 2)) ?></td>
                            </tr>
                        <?php endforeach;
                    else: ?>
                        <tr>
                            <td colspan="8">No Credit Note Found.</td>
                        </tr>
                    <?php endif; ?>
                    <tr class="total-row">
                        <td colspan="3" class="right">Total</td>
                        <td class="right"><?= esc(number_format((float) ($tableTotals['creditNoteBaseFare'] ?? 0), 2)) ?></td>
                        <td class="right"><?= esc(number_format((float) ($tableTotals['creditNoteTax'] ?? 0), 2)) ?></td>
                        <td class="right"><?= esc(number_format((float) ($tableTotals['creditNoteDiscount'] ?? 0), 2)) ?></td>
                        <td class="right"><?= esc(number_format((float) ($tableTotals['creditNotePsfWht'] ?? 0), 2)) ?></td>
                        <td class="right"><?= esc(number_format((float) ($tableTotals['creditNotes'] ?? 0), 2)) ?></td>
                    </tr>
                </tbody>
            </table>
        </section>
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