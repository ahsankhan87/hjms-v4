        -- Migration: 2026-08-16-000041_AddPackageSaleFields
        -- Purpose: Add package sale related fields to sales table
        -- Date: 2026-08-16

        -- ============================================================================
        -- Add package sale fields to the sales table
        -- ============================================================================
        ALTER TABLE `sales`
        ADD COLUMN `package_name` VARCHAR(180) NULL AFTER `ticket_no` COMMENT 'Package name',
        ADD COLUMN `package_details` TEXT NULL AFTER `package_name` COMMENT 'Package details/description',
        ADD COLUMN `attachment` VARCHAR(255) NULL AFTER `package_details` COMMENT 'First attachment filename',
        ADD COLUMN `attachment2` VARCHAR(255) NULL AFTER `attachment` COMMENT 'Second attachment filename',
        ADD COLUMN `attachment3` VARCHAR(255) NULL AFTER `attachment2` COMMENT 'Third attachment filename';

        -- ============================================================================
        -- Verification Query (Optional - Run to verify the migration)
        -- ============================================================================
        -- DESCRIBE sales;
        -- SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_COMMENT 
        -- FROM INFORMATION_SCHEMA.COLUMNS 
        -- WHERE TABLE_NAME = 'sales' AND TABLE_SCHEMA = DATABASE()
        -- ORDER BY ORDINAL_POSITION;
