-- Migration: 2026-09-14-000047_CreateSupplierCategoriesAndLedgerCategory
-- Purpose: Create supplier_categories lookup table and add category_id to supplier_ledger_entries
-- Date: 2026-09-14

-- ============================================================================
-- Supplier categories lookup table
-- ============================================================================
CREATE TABLE IF NOT EXISTS `supplier_categories` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(160) NOT NULL,
    `description` VARCHAR(500) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_supplier_categories_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default categories (ignore duplicates)
INSERT IGNORE INTO `supplier_categories` (`name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
('Visa', NULL, 1, NOW(), NOW()),
('Transport', NULL, 1, NOW(), NOW()),
('Hotel', NULL, 1, NOW(), NOW()),
('Ticket', NULL, 1, NOW(), NOW()),
('Other', NULL, 1, NOW(), NOW());

-- ============================================================================
-- Add category_id to supplier_ledger_entries table
-- ============================================================================
ALTER TABLE `supplier_ledger_entries`
ADD COLUMN `category_id` INT(11) UNSIGNED NULL AFTER `entry_type` COMMENT 'Supplier category (supplier_categories.id)';

-- ============================================================================
-- Verification Query (Optional - Run to verify the migration)
-- ============================================================================
-- DESCRIBE supplier_categories;
-- DESCRIBE supplier_ledger_entries;
