-- Migration: 2026-08-16-000043_AddTypeToAgents
-- Purpose: Add type field to agents table
-- Date: 2026-08-16

-- ============================================================================
-- Add type column to agents table
-- ============================================================================
ALTER TABLE `agents`
ADD COLUMN `type` ENUM('agent', 'customer') NOT NULL DEFAULT 'agent' AFTER `code` COMMENT 'Type of agent: agent or customer';

-- ============================================================================
-- Optional: Set default values for existing records
-- ============================================================================
-- UPDATE `agents` SET `type` = 'agent' WHERE `type` IS NULL;

-- ============================================================================
-- Verification Query (Optional - Run to verify the migration)
-- ============================================================================
-- DESCRIBE agents;
-- SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_COMMENT 
-- FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_NAME = 'agents' AND TABLE_SCHEMA = DATABASE()
-- ORDER BY ORDINAL_POSITION;
