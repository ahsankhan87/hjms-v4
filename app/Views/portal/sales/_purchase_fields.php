<?php
/**
 * Supplier + purchase price picker shared by the sale components.
 * The caller assigns $purchaseFields before including this file.
 *
 * @var array $purchaseFields
 */

$purchaseFields = $purchaseFields ?? [];
$supplierName = (string) ($purchaseFields['supplierName'] ?? '');
$purchaseName = (string) ($purchaseFields['purchaseName'] ?? '');
$supplierValue = (int) ($purchaseFields['supplierValue'] ?? 0);
$purchaseValue = (string) ($purchaseFields['purchaseValue'] ?? '');
$suppliers = (array) ($purchaseFields['suppliers'] ?? []);
?>
<div>
    <label class="mb-1 block text-xs font-medium text-slate-600">Supplier</label>
    <select name="<?= esc($supplierName) ?>" class="js-supplier w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <option value="">Not purchased / no supplier</option>
        <?php foreach ($suppliers as $supplier): ?>
            <?php $supplierId = (int) ($supplier['id'] ?? 0); ?>
            <option value="<?= esc((string) $supplierId) ?>" <?= $supplierId === $supplierValue ? 'selected' : '' ?>>
                <?= esc((string) ($supplier['supplier_name'] ?? '')) ?><?= trim((string) ($supplier['supplier_type'] ?? '')) !== '' ? ' — ' . esc(ucfirst((string) $supplier['supplier_type'])) : '' ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
<div>
    <label class="mb-1 block text-xs font-medium text-slate-600">Purchase Price</label>
    <input type="number" step="0.01" min="0" name="<?= esc($purchaseName) ?>" value="<?= esc($purchaseValue) ?>" placeholder="0.00" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    <p class="mt-1 text-[11px] text-slate-400">Uses the component currency and exchange rate selected above</p>
</div>
