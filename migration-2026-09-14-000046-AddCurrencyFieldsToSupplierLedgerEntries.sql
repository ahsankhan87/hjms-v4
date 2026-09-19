-- Migration: 2026-09-14-000046_AddCurrencyFieldsToSupplierLedgerEntries
-- Purpose: Add dual-currency (PKR / SAR) support to supplier_ledger_entries
-- Date: 2026-09-14
-- Notes:
--   * debit_amount / credit_amount always store the PKR value (reporting currency).
--   * sar_debit_amount / sar_credit_amount store the SAR value (0 when the entry is in PKR).
--   * exchange_rate stores the SAR -> PKR rate used to derive the PKR value of an SAR entry.

-- ============================================================================
-- Add currency columns to supplier_ledger_entries table
-- ============================================================================
ALTER TABLE `supplier_ledger_entries`
ADD COLUMN `currency` VARCHAR(10) NOT NULL DEFAULT 'PKR' AFTER `entry_type` COMMENT 'Entry currency: PKR or SAR',
ADD COLUMN `exchange_rate` DECIMAL(14,4) NOT NULL DEFAULT 1 AFTER `currency` COMMENT 'SAR to PKR exchange rate (1 for PKR entries)',
ADD COLUMN `sar_debit_amount` DECIMAL(14,2) NOT NULL DEFAULT 0 AFTER `debit_amount` COMMENT 'Debit amount in SAR (0 for PKR entries)',
ADD COLUMN `sar_credit_amount` DECIMAL(14,2) NOT NULL DEFAULT 0 AFTER `credit_amount` COMMENT 'Credit amount in SAR (0 for PKR entries)';

-- ============================================================================
-- Verification Query (Optional - Run to verify the migration)
-- ============================================================================
-- DESCRIBE supplier_ledger_entries;
-- SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_COMMENT
-- FROM INFORMATION_SCHEMA.COLUMNS
-- WHERE TABLE_NAME = 'supplier_ledger_entries' AND TABLE_SCHEMA = DATABASE()
-- ORDER BY ORDINAL_POSITION;
