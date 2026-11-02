-- Migration: 2026-08-16-000045_CreateSaleHotelStays
-- Purpose: Create sale_hotel_stays table and migrate existing hotel sales data
-- Date: 2026-08-16

-- ============================================================================
-- Step 1: Create sale_hotel_stays table
-- ============================================================================
CREATE TABLE IF NOT EXISTS `sale_hotel_stays` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) unsigned NOT NULL,
  `hotel_name` varchar(180) DEFAULT NULL,
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL,
  `room_name` varchar(120) DEFAULT NULL,
  `room_qty` int(11) unsigned NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_id` (`sale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Step 2: Migrate existing hotel sales data to the new table
-- ============================================================================
-- This query inserts hotel sale records from the sales table where:
-- - sale_type is 'hotel'
-- - check-in and check-out dates are populated
INSERT INTO `sale_hotel_stays` 
  (`sale_id`, `hotel_name`, `check_in_date`, `check_out_date`, `room_name`, `room_qty`, `created_at`)
SELECT 
  `id`,
  COALESCE(`hotel_name`, ''),
  `checkin_date`,
  `checkout_date`,
  COALESCE(`room_name`, ''),
  COALESCE(`room_qty`, 1),
  COALESCE(`created_at`, NOW())
FROM `sales`
WHERE 
  `sale_type` = 'hotel'
  AND `checkin_date` IS NOT NULL
  AND `checkout_date` IS NOT NULL
ON DUPLICATE KEY UPDATE
  `hotel_name` = VALUES(`hotel_name`),
  `check_in_date` = VALUES(`check_in_date`),
  `check_out_date` = VALUES(`check_out_date`),
  `room_name` = VALUES(`room_name`),
  `room_qty` = VALUES(`room_qty`);

-- ============================================================================
-- Verification Queries (Optional - Run to verify the migration)
-- ============================================================================
-- Show the structure of the new table
-- DESCRIBE sale_hotel_stays;

-- Count of migrated records
-- SELECT COUNT(*) as total_stays FROM sale_hotel_stays;

-- Sample of migrated data
-- SELECT * FROM sale_hotel_stays LIMIT 10;
