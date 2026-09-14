-- Migration: 2026-08-16-000044_AddAttachmentToSupplierLedgerEntries
-- Purpose: Add attachment field to supplier_ledger_entries table
-- Date: 2026-08-16

-- ============================================================================
-- Add attachment column to supplier_ledger_entries table
-- ============================================================================
ALTER TABLE `supplier_ledger_entries`
ADD COLUMN `attachment` VARCHAR(255) NULL AFTER `description` COMMENT 'Attachment filename (image/pdf)';

-- ============================================================================
-- Verification Query (Optional - Run to verify the migration)
-- ============================================================================
-- DESCRIBE supplier_ledger_entries;
-- SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_COMMENT 
-- FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_NAME = 'supplier_ledger_entries' AND TABLE_SCHEMA = DATABASE()
-- ORDER BY ORDINAL_POSITION;
