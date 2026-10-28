<?php
/**
 * Currency / exchange rate / amount trio shared by every sale component.
 * The caller assigns $amountFields before including this file.
 *
 * @var array $amountFields
 */

$amountFields = $amountFields ?? [];
$currencyName = (string) ($amountFields['currencyName'] ?? '');
$rateName = (string) ($amountFields['rateName'] ?? '');
$amountName = (string) ($amountFields['amountName'] ?? '');
$currencyValue = strtoupper((string) ($amountFields['currencyValue'] ?? 'PKR'));
$rateValue = (string) ($amountFields['rateValue'] ?? '1');
$amountValue = (string) ($amountFields['amountValue'] ?? '');
?>
<div>
    <label class="mb-1 block text-xs font-medium text-slate-600">Currency</label>
    <select name="<?= esc($currencyName) ?>" class="js-currency w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <option value="PKR" <?= $currencyValue !== 'SAR' ? 'selected' : '' ?>>PKR</option>
        <option value="SAR" <?= $currencyValue === 'SAR' ? 'selected' : '' ?>>SAR</option>
    </select>
</div>
<div>
    <label class="mb-1 block text-xs font-medium text-slate-600">Exchange Rate</label>
    <input name="<?= esc($rateName) ?>" type="number" step="0.0001" min="0" value="<?= esc($rateValue) ?>" placeholder="1" class="js-rate w-full rounded-lg border border-slate-300 px-3 py-2 text-sm disabled:bg-slate-100 disabled:text-slate-500">
</div>
<div>
    <label class="mb-1 block text-xs font-medium text-slate-600">Amount</label>
    <input name="<?= esc($amountName) ?>" type="number" step="0.01" min="0" data-amount value="<?= esc($amountValue) ?>" placeholder="0.00" class="js-amount w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    <p class="js-pkr-preview mt-1 text-[11px] text-slate-500"></p>
</div>
