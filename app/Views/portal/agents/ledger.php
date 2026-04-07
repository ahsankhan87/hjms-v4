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
                        <h2 class="text-lg font-semibold">Agent Ledger - <?= esc((string) ($agent['name'] ?? 'N/A')) ?></h2>
                        <p class="text-[11px] text-slate-200"><?= !empty($agent['code']) ? 'Code: ' . esc((string) $agent['code']) : '' ?></p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="<?= site_url('/agents') ?>" class="inline-flex items-center gap-2 rounded-lg border border-slate-300/40 bg-slate-100/10 px-2.5 py-1.5 text-xs font-medium text-white transition hover:bg-slate-100/20">
                            <i class="fa-solid fa-arrow-left text-[11px]"></i>
                            Back to Agents
                        </a>
                        <a href="<?= site_url('/agents/' . (int) ($agent['id'] ?? 0) . '/ledger/print?from=' . urlencode((string) ($filterFrom ?? '')) . '&to=' . urlencode((string) ($filterTo ?? ''))) ?>" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-slate-300/40 bg-slate-100/10 px-2.5 py-1.5 text-xs font-medium text-white transition hover:bg-slate-100/20">
                            <i class="fa-solid fa-print text-[11px]"></i>
                            Print
                        </a>
                        <a href="<?= site_url('/agents/' . (int) ($agent['id'] ?? 0) . '/ledger/print?autoprint=1&from=' . urlencode((string) ($filterFrom ?? '')) . '&to=' . urlencode((string) ($filterTo ?? ''))) ?>" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-slate-300/40 bg-slate-100/10 px-2.5 py-1.5 text-xs font-medium text-white transition hover:bg-slate-100/20">
                            <i class="fa-solid fa-file-pdf text-[11px]"></i>
                            Export PDF
                        </a>
                        <a href="<?= site_url('/agents/' . (int) ($agent['id'] ?? 0) . '/ledger/manual-entry') ?>" class="inline-flex items-center gap-2 rounded-lg bg-emerald-400 px-2.5 py-1.5 text-xs font-semibold text-emerald-950 transition hover:bg-emerald-300">
                            <i class="fa-solid fa-plus text-[11px]"></i>
                            Post Manual Entry
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-200 px-4 py-3 sm:px-6">
                <form method="get" action="<?= site_url('/agents/' . (int) ($agent['id'] ?? 0) . '/ledger') ?>" class="grid gap-2 md:grid-cols-6">
                    <div>
                        <label for="from" class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">From Date</label>
                        <input id="from" type="date" name="from" value="<?= esc((string) ($filterFrom ?? '')) ?>" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-800 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    </div>
                    <div>
                        <label for="to" class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">To Date</label>
                        <input id="to" type="date" name="to" value="<?= esc((string) ($filterTo ?? '')) ?>" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-800 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    </div>
                    <div class="md:col-span-4 flex items-end gap-2">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-slate-700">
                            <i class="fa-solid fa-filter text-[11px]"></i>
                            Apply Filter
                        </button>
                        <a href="<?= site_url('/agents/' . (int) ($agent['id'] ?? 0) . '/ledger') ?>" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">
                            <i class="fa-solid fa-rotate-left text-[11px]"></i>
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="max-h-[68vh] overflow-auto border-t border-slate-200">
                <table id="agent-ledger-table" class="list-table min-w-full text-sm">
                    <thead class="sticky top-0 z-10 bg-slate-100 text-[11px] uppercase tracking-wide text-slate-600 shadow-[0_1px_0_0_rgba(148,163,184,0.45)]">
                        <tr>
                            <th class="whitespace-nowrap px-3 py-3 text-left">Date</th>
                            <th class="whitespace-nowrap px-3 py-3 text-left">Type</th>
                            <th class="whitespace-nowrap px-3 py-3 text-left">Trans #</th>
                            <th class="min-w-[300px] px-3 py-3 text-left">Particulars</th>
                            <th class="whitespace-nowrap px-3 py-3 text-left">Inv/Ref</th>
                            <th class="whitespace-nowrap px-3 py-3 text-right">Debit</th>
                            <th class="whitespace-nowrap px-3 py-3 text-right">Credit</th>
                            <th class="whitespace-nowrap px-3 py-3 text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-700">
                        <?php if (!empty($rows)): foreach ($rows as $row): ?>
                                <?php
                                $entryType = (string) ($row['entry_type'] ?? '');
                                $referenceType = (string) ($row['reference_type'] ?? '');
                                $referenceId = (int) ($row['reference_id'] ?? 0);
                                $debitAmount = (float) ($row['debit_amount'] ?? 0);
                                $creditAmount = (float) ($row['credit_amount'] ?? 0);
                                $runningBalance = (float) ($row['running_balance'] ?? 0);
                                ?>
                                <tr class="odd:bg-white even:bg-slate-50/60">
                                    <td class="whitespace-nowrap px-3 py-2.5"><?= esc((string) ($row['entry_date'] ?? '')) ?></td>
                                    <td class="whitespace-nowrap px-3 py-2.5">
                                        <span class="rounded-full bg-slate-200 px-2 py-1 text-[10px] font-semibold uppercase text-slate-700"><?= esc(str_replace('_', ' ', $entryType)) ?></span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-2.5 text-xs text-slate-500"><?= $referenceId > 0 ? esc((string) $referenceId) : '-' ?></td>
                                    <td class="px-3 py-2.5"><?= esc((string) ($row['description'] ?? '-')) ?></td>
                                    <td class="whitespace-nowrap px-3 py-2.5">
                                        <?php if ($referenceType === 'booking' && $referenceId > 0): ?>
                                            <a class="text-sky-700 hover:underline" href="<?= site_url('/bookings/' . $referenceId) ?>">Booking #<?= esc((string) $referenceId) ?></a>
                                        <?php elseif ($referenceType === 'payment' && $referenceId > 0): ?>
                                            <a class="text-sky-700 hover:underline" href="<?= site_url('/payments/' . $referenceId . '/receipt') ?>" target="_blank">Payment #<?= esc((string) $referenceId) ?></a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-2.5 text-right font-semibold <?= $debitAmount > 0 ? 'text-rose-700' : 'text-slate-500' ?>"><?= esc(number_format($debitAmount, 2)) ?></td>
                                    <td class="whitespace-nowrap px-3 py-2.5 text-right font-semibold <?= $creditAmount > 0 ? 'text-emerald-700' : 'text-slate-500' ?>"><?= esc(number_format($creditAmount, 2)) ?></td>
                                    <td class="whitespace-nowrap px-3 py-2.5 text-right font-semibold <?= $runningBalance < 0 ? 'text-rose-700' : 'text-slate-900' ?>"><?= esc(number_format($runningBalance, 2)) ?></td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="8" class="px-3 py-8 text-center text-slate-500">No ledger entries found for this account.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="bg-slate-100 text-slate-800">
                        <tr>
                            <td colspan="5" class="px-3 py-3 text-sm font-semibold">Total</td>
                            <td class="whitespace-nowrap px-3 py-3 text-right text-sm font-semibold"><?= esc(number_format((float) ($totalDebit ?? 0), 2)) ?></td>
                            <td class="whitespace-nowrap px-3 py-3 text-right text-sm font-semibold"><?= esc(number_format((float) ($totalCredit ?? 0), 2)) ?></td>
                            <td class="whitespace-nowrap px-3 py-3 text-right text-sm font-bold"><?= esc(number_format((float) ($closingBalance ?? 0), 2)) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </article>
    </section>
</main>
<?php $this->endSection() ?>