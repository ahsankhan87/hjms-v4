-- Package variants and optional service components
-- Run this SQL in your database before using new variant/service package management.

CREATE TABLE IF NOT EXISTS `package_variants` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `package_id` BIGINT UNSIGNED NOT NULL,
  `variant_code` VARCHAR(40) NOT NULL,
  `variant_name` VARCHAR(120) NOT NULL,
  `room_type` VARCHAR(80) NOT NULL,
  `total_seats` INT NOT NULL DEFAULT 0,
  `selling_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `base_cost` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `sequence_no` INT NOT NULL DEFAULT 99,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `notes` TEXT NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_package_variant_code` (`package_id`, `variant_code`),
  KEY `idx_package_variants_package` (`package_id`),
  KEY `idx_package_variants_active` (`package_id`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `package_service_components` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `package_id` BIGINT UNSIGNED NOT NULL,
  `component_type` VARCHAR(30) NOT NULL,
  `component_name` VARCHAR(120) NOT NULL,
  `cost_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `selling_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_seats` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `notes` TEXT NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_package_services_package` (`package_id`),
  KEY `idx_package_services_type` (`component_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional booking-level selected variant support (safe if column already exists)
SET @bookingsTableExists := (
  SELECT COUNT(*)
  FROM information_schema.tables
  WHERE table_schema = DATABASE() AND table_name = 'bookings'
);

SET @bookingVariantColumnExists := (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = DATABASE() AND table_name = 'bookings' AND column_name = 'package_variant_id'
);

SET @sql := IF(
  @bookingsTableExists > 0 AND @bookingVariantColumnExists = 0,
  'ALTER TABLE `bookings` ADD COLUMN `package_variant_id` BIGINT UNSIGNED NULL AFTER `package_id`',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Foreign keys are intentionally omitted to avoid migration failure on inconsistent legacy data.
