<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700\"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700\"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700\"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="space-y-3">
        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-700 px-4 py-3 text-white sm:px-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Agent Ledger 2 - <?= esc((string) ($agent['name'] ?? 'N/A')) ?></h2>
                        <p class="text-[11px] text-slate-200"><?= !empty($agent['code']) ? 'Code: ' . esc((string) $agent['code']) : '' ?></p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="<?= site_url('/agents') ?>" class="inline-flex items-center gap-2 rounded-lg border border-slate-300/40 bg-slate-100/10 px-2.5 py-1.5 text-xs font-medium text-white transition hover:bg-slate-100/20">
                            <i class="fa-solid fa-arrow-left text-[11px]"></i>
                            Back to Agents
                        </a>
                        <a href="<?= site_url('/agents/' . (int) ($agent['id'] ?? 0) . '/ledger?from=' . urlencode((string) ($filterFrom ?? '')) . '&to=' . urlencode((string) ($filterTo ?? ''))) ?>" class="inline-flex items-center gap-2 rounded-lg border border-slate-300/40 bg-slate-100/10 px-2.5 py-1.5 text-xs font-medium text-white transition hover:bg-slate-100/20">
                            <i class="fa-solid fa-book text-[11px]"></i>
                            Ledger 1
                        </a>
                        <a href="<?= site_url('/agents/' . (int) ($agent['id'] ?? 0) . '/ledger/second/print?from=' . urlencode((string) ($filterFrom ?? '')) . '&to=' . urlencode((string) ($filterTo ?? '')) . '&autoprint=1') ?>" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-slate-300/40 bg-slate-100/10 px-2.5 py-1.5 text-xs font-medium text-white transition hover:bg-slate-100/20">
                            <i class="fa-solid fa-print text-[11px]"></i>
                            Print
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-200 bg-slate-50 px-4 py-3 sm:px-6">
                <form method="get" action="<?= site_url('/agents/' . (int) ($agent['id'] ?? 0) . '/ledger/second') ?>" class="grid gap-2 md:grid-cols-6">
                    <div>
                        <label for="from" class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">From Date</label>
                        <input id="from" type="date" name="from" value="<?= esc((string) ($filterFrom ?? '')) ?>" class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-slate-800 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    </div>
                    <div>
                        <label for="to" class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">To Date</label>
                        <input id="to" type="date" name="to" value="<?= esc((string) ($filterTo ?? '')) ?>" class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-slate-800 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    </div>
                    <div class="md:col-span-4 flex items-end gap-2">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-slate-700">
                            <i class="fa-solid fa-filter text-[11px]"></i>
                            Apply Filter
                        </button>
                        <a href="<?= site_url('/agents/' . (int) ($agent['id'] ?? 0) . '/ledger/second') ?>" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">
                            <i class="fa-solid fa-rotate-left text-[11px]"></i>
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <h3 class="mb-3 text-sm font-semibold text-slate-900">Summary</h3>
            <?php
            $summaryItems = [
                [
                    'label' => 'Opening Balance',
                    'value' => (float) ($summary['openingBalance'] ?? 0),
                    'tone' => 'slate',
                    'hint' => 'B/F balance',
                ],
                [
                    'label' => 'Tickets',
                    'value' => (float) ($summary['ticketSaleTotal'] ?? 0),
                    'tone' => 'amber',
                    'hint' => 'Ticket receivable',
                ],
            ];

            $optionalSummaryItems = [
                [
                    'label' => 'Hotels',
                    'value' => (float) ($summary['hotelSaleTotal'] ?? 0),
                    'tone' => 'amber',
                    'hint' => 'Hotel receivable',
                ],
                [
                    'label' => 'Visas',
                    'value' => (float) ($summary['visaSaleTotal'] ?? 0),
                    'tone' => 'amber',
                    'hint' => 'Visa receivable',
                ],
                [
                    'label' => 'Booking',
                    'value' => (float) ($summary['bookingSaleTotal'] ?? 0),
                    'tone' => 'amber',
                    'hint' => 'Booking receivable',
                ],
            ];

            foreach ($optionalSummaryItems as $item) {
                if (abs((float) ($item['value'] ?? 0)) > 0.00001) {
                    $summaryItems[] = $item;
                }
            }

            $summaryItems[] = [
                'label' => 'Vouchers',
                'value' => (float) ($summary['voucherTotal'] ?? 0),
                'tone' => 'emerald',
                'hint' => 'Receipts',
            ];
            $summaryItems[] = [
                'label' => 'Refunds',
                'value' => (float) ($summary['creditNoteTotal'] ?? 0),
                'tone' => 'rose',
                'hint' => 'Credit notes',
            ];
            $summaryItems[] = [
                'label' => 'Closing Balance',
                'value' => (float) ($summary['closingBalance'] ?? 0),
                'tone' => 'blue',
                'hint' => 'Net balance',
            ];

            $toneClasses = [
                'slate' => 'border-slate-200 bg-slate-50 text-slate-700',
                'amber' => 'border-amber-200 bg-amber-50 text-amber-700',
                'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                'rose' => 'border-rose-200 bg-rose-50 text-rose-700',
                'blue' => 'border-sky-200 bg-sky-50 text-sky-700',
            ];
            ?>
            <div class="flex gap-1.5 overflow-x-auto pb-1">
                <?php foreach ($summaryItems as $item): ?>
                    <?php $toneClass = (string) ($toneClasses[(string) ($item['tone'] ?? 'slate')] ?? $toneClasses['slate']); ?>
                    <div class="min-w-[118px] flex-1 rounded-lg border px-2 py-2 shadow-sm <?= esc($toneClass) ?>">
                        <p class="text-[9px] font-semibold uppercase tracking-wide leading-tight"><?= esc((string) ($item['label'] ?? '')) ?></p>
                        <p class="mt-1 text-[15px] font-bold leading-none"><?= esc(number_format((float) ($item['value'] ?? 0), 2)) ?></p>
                        <p class="mt-1 text-[9px] leading-tight opacity-80"><?= esc((string) ($item['hint'] ?? '')) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <h3 class="mb-3 text-xl font-semibold text-slate-900">Ticket Sale</h3>
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold">Invoice #</th>
                            <th class="px-3 py-2 text-left font-semibold">Inv Date</th>
                            <th class="px-3 py-2 text-left font-semibold">Sector</th>
                            <th class="px-3 py-2 text-left font-semibold">Pax Name</th>
                            <th class="px-3 py-2 text-left font-semibold">PNR</th>
                            <th class="px-3 py-2 text-left font-semibold">Ticket No</th>
                            <th class="px-3 py-2 text-left font-semibold">Reference Number</th>
                            <th class="px-3 py-2 text-left font-semibold">Remarks</th>
                            <th class="px-3 py-2 text-right font-semibold">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (!empty($ticketSales)): foreach ($ticketSales as $row): ?>
                                <tr class="odd:bg-white even:bg-slate-50/60">
                                    <td class="px-3 py-2"><?= esc((string) ($row['invoice_no'] ?? ('S' . (int) ($row['id'] ?? 0)))) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['sale_date'] ?? '')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['sector'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['pax_name'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['pnr'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['ticket_no'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['reference_no'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['note'] ?? '-')) ?></td>
                                    <td class="px-3 py-2 text-right font-semibold"><?= esc(number_format((float) ($row['amount'] ?? 0), 2)) ?></td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="9" class="px-3 py-6 text-center text-slate-500">No Ticket Sale Found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="bg-slate-100 text-slate-800">
                        <tr>
                            <td colspan="8" class="px-3 py-2 text-right font-semibold">Total</td>
                            <td class="px-3 py-2 text-right font-bold"><?= esc(number_format((float) ($tableTotals['ticketSales'] ?? 0), 2)) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <h3 class="mb-3 text-xl font-semibold text-slate-900">Hotel Sale</h3>
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold">Invoice #</th>
                            <th class="px-3 py-2 text-left font-semibold">Inv Date</th>
                            <th class="px-3 py-2 text-left font-semibold">Hotel Name</th>
                            <th class="px-3 py-2 text-left font-semibold">CheckIn Date</th>
                            <th class="px-3 py-2 text-left font-semibold">Checkout Date</th>
                            <th class="px-3 py-2 text-left font-semibold">No Of Nights</th>
                            <th class="px-3 py-2 text-left font-semibold">Room</th>
                            <th class="px-3 py-2 text-left font-semibold">Room Qty</th>
                            <th class="px-3 py-2 text-left font-semibold">Pax Name</th>
                            <th class="px-3 py-2 text-left font-semibold">Reference Number</th>
                            <th class="px-3 py-2 text-left font-semibold">Remarks</th>
                            <th class="px-3 py-2 text-right font-semibold">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (!empty($hotelSales)): foreach ($hotelSales as $row): ?>
                                <tr class="odd:bg-white even:bg-slate-50/60">
                                    <td class="px-3 py-2"><?= esc((string) ($row['invoice_no'] ?? ('HS' . (int) ($row['id'] ?? 0)))) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['sale_date'] ?? '')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['hotel_name'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['checkin_date'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['checkout_date'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['no_of_nights'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['room_name'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['room_qty'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['pax_name'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['reference_no'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['note'] ?? '-')) ?></td>
                                    <td class="px-3 py-2 text-right font-semibold"><?= esc(number_format((float) ($row['amount'] ?? 0), 2)) ?></td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="12" class="px-3 py-6 text-center text-slate-500">No Hotel Sale Found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="bg-slate-100 text-slate-800">
                        <tr>
                            <td colspan="11" class="px-3 py-2 text-right font-semibold">Total</td>
                            <td class="px-3 py-2 text-right font-bold"><?= esc(number_format((float) ($tableTotals['hotelSales'] ?? 0), 2)) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <h3 class="mb-3 text-xl font-semibold text-slate-900">Visa Sale</h3>
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold">Invoice #</th>
                            <th class="px-3 py-2 text-left font-semibold">Inv Date</th>
                            <th class="px-3 py-2 text-left font-semibold">Category</th>
                            <th class="px-3 py-2 text-left font-semibold">Pax Name</th>
                            <th class="px-3 py-2 text-left font-semibold">Reference Number</th>
                            <th class="px-3 py-2 text-left font-semibold">Remarks</th>
                            <th class="px-3 py-2 text-left font-semibold">Visa Type</th>
                            <th class="px-3 py-2 text-left font-semibold">Visa Agency</th>
                            <th class="px-3 py-2 text-right font-semibold">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (!empty($visaSales)): foreach ($visaSales as $row): ?>
                                <tr class="odd:bg-white even:bg-slate-50/60">
                                    <td class="px-3 py-2"><?= esc((string) ($row['invoice_no'] ?? ('VS' . (int) ($row['id'] ?? 0)))) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['sale_date'] ?? '')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['category_name'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['pax_name'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['reference_no'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['note'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['visa_type'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['visa_agency'] ?? '-')) ?></td>
                                    <td class="px-3 py-2 text-right font-semibold"><?= esc(number_format((float) ($row['amount'] ?? 0), 2)) ?></td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="9" class="px-3 py-6 text-center text-slate-500">No Visa Booking Found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="bg-slate-100 text-slate-800">
                        <tr>
                            <td colspan="8" class="px-3 py-2 text-right font-semibold">Total</td>
                            <td class="px-3 py-2 text-right font-bold"><?= esc(number_format((float) ($tableTotals['visaSales'] ?? 0), 2)) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <h3 class="mb-3 text-xl font-semibold text-slate-900">General Booking Sale</h3>
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold">Invoice #</th>
                            <th class="px-3 py-2 text-left font-semibold">Inv Date</th>
                            <th class="px-3 py-2 text-left font-semibold">Category</th>
                            <th class="px-3 py-2 text-left font-semibold">Pax Name</th>
                            <th class="px-3 py-2 text-left font-semibold">Reference Number</th>
                            <th class="px-3 py-2 text-left font-semibold">Remarks</th>
                            <th class="px-3 py-2 text-left font-semibold">Trans Date</th>
                            <th class="px-3 py-2 text-left font-semibold">Vendor</th>
                            <th class="px-3 py-2 text-right font-semibold">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (!empty($generalSales)): foreach ($generalSales as $row): ?>
                                <tr class="odd:bg-white even:bg-slate-50/60">
                                    <td class="px-3 py-2"><?= esc((string) ($row['booking_no'] ?? ('B' . (int) ($row['id'] ?? 0)))) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['inv_date'] ?? '')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['category_name'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['pax_name'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['reference_number'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['remarks'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['trans_date'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['vendor_name'] ?? '-')) ?></td>
                                    <td class="px-3 py-2 text-right font-semibold"><?= esc(number_format((float) ($row['total_amount'] ?? 0), 2)) ?></td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="9" class="px-3 py-6 text-center text-slate-500">No General Bookings Found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="bg-slate-100 text-slate-800">
                        <tr>
                            <td colspan="8" class="px-3 py-2 text-right font-semibold">Total</td>
                            <td class="px-3 py-2 text-right font-bold"><?= esc(number_format((float) ($tableTotals['generalSales'] ?? 0), 2)) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <h3 class="mb-3 text-xl font-semibold text-slate-900">Voucher</h3>
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold">Voucher #</th>
                            <th class="px-3 py-2 text-left font-semibold">Voucher Date</th>
                            <th class="px-3 py-2 text-left font-semibold">Adj Date</th>
                            <th class="px-3 py-2 text-left font-semibold">Ref Number</th>
                            <th class="px-3 py-2 text-right font-semibold">Amount</th>
                            <th class="px-3 py-2 text-left font-semibold">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (!empty($vouchers)): foreach ($vouchers as $row): ?>
                                <tr class="odd:bg-white even:bg-slate-50/60">
                                    <td class="px-3 py-2"><?= esc((string) ($row['payment_no'] ?? ('V' . (int) ($row['id'] ?? 0)))) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['payment_date'] ?? '')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['payment_date'] ?? '')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['gateway_reference'] ?? '-')) ?></td>
                                    <td class="px-3 py-2 text-right font-semibold"><?= esc(number_format((float) ($row['signed_amount'] ?? 0), 2)) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['note'] ?? '-')) ?></td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="6" class="px-3 py-6 text-center text-slate-500">No Voucher Found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="bg-slate-100 text-slate-800">
                        <tr>
                            <td colspan="4" class="px-3 py-2 text-right font-semibold">Total</td>
                            <td class="px-3 py-2 text-right font-bold"><?= esc(number_format((float) ($tableTotals['vouchers'] ?? 0), 2)) ?></td>
                            <td class="px-3 py-2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <h3 class="mb-3 text-xl font-semibold text-slate-900">Credit Note</h3>
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold">Invoice #</th>
                            <th class="px-3 py-2 text-left font-semibold">Inv Date</th>
                            <th class="px-3 py-2 text-left font-semibold">Adj Date</th>
                            <th class="px-3 py-2 text-left font-semibold">Sector</th>
                            <th class="px-3 py-2 text-left font-semibold">Pax Name</th>
                            <th class="px-3 py-2 text-left font-semibold">PNR</th>
                            <th class="px-3 py-2 text-left font-semibold">Ticket No</th>
                            <th class="px-3 py-2 text-right font-semibold">Base Fare</th>
                            <th class="px-3 py-2 text-right font-semibold">Tax</th>
                            <th class="px-3 py-2 text-right font-semibold">Discount</th>
                            <th class="px-3 py-2 text-right font-semibold">PSF/WHT</th>
                            <th class="px-3 py-2 text-right font-semibold">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (!empty($creditNotes)): foreach ($creditNotes as $row): ?>
                                <tr class="odd:bg-white even:bg-slate-50/60">
                                    <td class="px-3 py-2"><?= esc((string) ($row['invoice_no'] ?? ('CN' . (int) ($row['id'] ?? 0)))) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['sale_date'] ?? '')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['credit_note_date'] ?? '')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['sector'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['pax_name'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['pnr'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['ticket_no'] ?? '-')) ?></td>
                                    <td class="px-3 py-2 text-right"><?= esc(number_format((float) ($row['base_fare'] ?? 0), 2)) ?></td>
                                    <td class="px-3 py-2 text-right"><?= esc(number_format((float) ($row['tax'] ?? 0), 2)) ?></td>
                                    <td class="px-3 py-2 text-right"><?= esc(number_format((float) ($row['discount'] ?? 0), 2)) ?></td>
                                    <td class="px-3 py-2 text-right"><?= esc(number_format((float) ($row['psf_wht'] ?? 0), 2)) ?></td>
                                    <td class="px-3 py-2 text-right font-semibold"><?= esc(number_format((float) ($row['amount'] ?? 0), 2)) ?></td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="12" class="px-3 py-6 text-center text-slate-500">No Credit Note Found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="bg-slate-100 text-slate-800">
                        <tr>
                            <td colspan="7" class="px-3 py-2 text-right font-semibold">Total</td>
                            <td class="px-3 py-2 text-right font-bold"><?= esc(number_format((float) ($tableTotals['creditNoteBaseFare'] ?? 0), 2)) ?></td>
                            <td class="px-3 py-2 text-right font-bold"><?= esc(number_format((float) ($tableTotals['creditNoteTax'] ?? 0), 2)) ?></td>
                            <td class="px-3 py-2 text-right font-bold"><?= esc(number_format((float) ($tableTotals['creditNoteDiscount'] ?? 0), 2)) ?></td>
                            <td class="px-3 py-2 text-right font-bold"><?= esc(number_format((float) ($tableTotals['creditNotePsfWht'] ?? 0), 2)) ?></td>
                            <td class="px-3 py-2 text-right font-bold"><?= esc(number_format((float) ($tableTotals['creditNotes'] ?? 0), 2)) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </article>
    </section>
</main>
<?php $this->endSection() ?>