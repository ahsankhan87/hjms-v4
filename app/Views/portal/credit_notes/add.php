<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <?php
    $saleType = (string) ($saleType ?? 'visa');
    $isHotel = $saleType === 'hotel';
    $isTicket = $saleType === 'ticket';
    $basePath = $isHotel ? '/hotel-sales' : ($isTicket ? '/ticket-sales' : '/sales');
    $creditPrefix = trim((string) ($creditPrefix ?? 'CNV'));
    if ($creditPrefix === '') {
        $creditPrefix = 'CNV';
    }
    ?>

    <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
        <h3 class="text-sm font-semibold text-slate-800"><?= $isHotel ? 'Create Hotel Credit Note' : ($isTicket ? 'Create Ticket Credit Note' : 'Create Visa Credit Note') ?></h3>
        <p class="text-xs text-slate-500">Creates <?= esc($creditPrefix) ?> invoice and auto-posts a credit entry in agent ledger.</p>
    </article>

    <article class="rounded-xl border border-slate-200 bg-white p-4">
        <?php if (empty($sales)): ?><div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">No eligible agent sales found for this module.</div><?php endif; ?>
        <form method="post" action="<?= site_url($basePath . '/credit-notes') ?>" class="space-y-3">
            <?= csrf_field() ?>

            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Credit Note #</label>
                    <input value="Auto: <?= esc($creditPrefix) ?>0001" readonly class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-500">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Adjustment Date</label>
                    <input type="date" name="credit_note_date" value="<?= esc(old('credit_note_date', date('Y-m-d'))) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Select Sale (Agent Sales Only)</label>
                <p class="mb-1 text-[11px] text-slate-500">Each option shows Total, Credited, and Remaining refundable balance.</p>
                <?php $oldSaleId = (int) old('sale_id', (int) ($preselectedSaleId ?? 0)); ?>
                <select name="sale_id" required class="js-select2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Select Sale</option>
                    <?php foreach (($sales ?? []) as $sale): ?>
                        <?php $saleId = (int) ($sale['id'] ?? 0); ?>
                        <?php if ($saleId < 1) {
                            continue;
                        } ?>
                        <?php
                        $saleInvoice = trim((string) ($sale['invoice_no'] ?? ''));
                        if ($saleInvoice === '') {
                            $saleInvoice = 'Sale #' . $saleId;
                        }
                        $salePax = trim((string) ($sale['pax_name'] ?? ''));
                        if ($salePax === '') {
                            $salePax = trim((string) ($sale['customer_name'] ?? ''));
                        }
                        $saleTotalAmount = (float) ($sale['amount'] ?? 0);
                        $saleCreditedAmount = (float) ($sale['credited_amount'] ?? 0);
                        $saleRemainingAmount = (float) ($sale['remaining_amount'] ?? max(0, $saleTotalAmount - $saleCreditedAmount));
                        $saleLabel = $saleInvoice . ' | ' . (string) ($sale['sale_date'] ?? '-')
                            . ' | Total PKR ' . number_format($saleTotalAmount, 2)
                            . ' | Credited PKR ' . number_format($saleCreditedAmount, 2)
                            . ' | Remaining PKR ' . number_format($saleRemainingAmount, 2);
                        if ($salePax !== '') {
                            $saleLabel .= ' | ' . $salePax;
                        }
                        ?>
                        <option value="<?= esc((string) $saleId) ?>" <?= $oldSaleId === $saleId ? 'selected' : '' ?>><?= esc($saleLabel) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Credit Amount</label>
                    <input name="amount" value="<?= esc(old('amount')) ?>" placeholder="0.00" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                    <?php $status = (string) old('status', 'posted'); ?>
                    <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="posted" <?= $status === 'posted' ? 'selected' : '' ?>>Posted</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="voided" <?= $status === 'voided' ? 'selected' : '' ?>>Voided</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Reason / Remarks</label>
                <textarea name="reason" rows="3" placeholder="Optional reason for credit adjustment" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc(old('reason')) ?></textarea>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
                <a href="<?= site_url($basePath . '/credit-notes') ?>" class="btn btn-md btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-md btn-primary" <?= empty($sales) ? 'disabled' : '' ?>><i class="fa-solid fa-check"></i><span>Create Credit Note</span></button>
            </div>
        </form>
    </article>
</main>
<?php $this->endSection() ?>