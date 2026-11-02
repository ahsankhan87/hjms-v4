-- =============================================================================
--  HJMS v4  ·  Sales module schema update (fixes "error while saving sales")
--  Covers migrations:
--     2026-10-24-000049  CreateSaleItemsAndCurrencyFields
--     2026-10-25-000050  AddCurrencyToSaleItems
--
--  WHY SALES SAVING FAILS WITHOUT THIS
--   * `sale_items` (the component table: visa / ticket / hotel / transport /
--     package lines) does not exist  ->  "Table ...sale_items doesn't exist".
--   * `sales.currency`, `sales.exchange_rate`, `sales.amount_pkr` are missing
--     ->  "Unknown column 'currency' in 'field list'".
--   * `sales.sales_category_id` is still NOT NULL  ->  "Column
--     'sales_category_id' cannot be null" for component-based sales.
--
--  HOW TO RUN (cPanel)
--   phpMyAdmin  ->  select your database  ->  SQL tab  ->  paste this file  ->  Go
--   or:  mysql -u USER -p DBNAME < 2026-10-24-sales-items-and-currency.sql
--
--  SAFETY
--   * Idempotent: every step is guarded — safe to run more than once.
--   * Nothing is dropped. Existing sales rows are never modified except the
--     two backfills that only fill in zero values.
--   * Take a database backup first.
-- =============================================================================

SET NAMES utf8mb4;
SET @db := DATABASE();

-- -----------------------------------------------------------------------------
-- 1. sale_items  (component lines for a sale)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sale_items` (
    `id`             INT(11)          UNSIGNED NOT NULL AUTO_INCREMENT,
    `sale_id`        INT(11)          UNSIGNED NOT NULL,
    `item_type`      VARCHAR(20)               NOT NULL,
    `sort_order`     INT(11)          UNSIGNED NOT NULL DEFAULT 0,
    `description`    TEXT                      NULL,
    `amount`         DECIMAL(14,2)             NOT NULL DEFAULT 0.00,
    `currency`       VARCHAR(10)               NOT NULL DEFAULT 'PKR',
    `exchange_rate`  DECIMAL(14,4)             NOT NULL DEFAULT 1.0000,
    `amount_pkr`     DECIMAL(14,2)             NOT NULL DEFAULT 0.00,
    `attachment`     VARCHAR(255)              NULL,
    `visa_type`      VARCHAR(80)               NULL,
    `visa_no`        VARCHAR(80)               NULL,
    `pnr`            VARCHAR(50)               NULL,
    `sector`         VARCHAR(80)               NULL,
    `ticket_no`      VARCHAR(80)               NULL,
    `hotel_name`     VARCHAR(180)              NULL,
    `check_in_date`  DATE                      NULL,
    `check_out_date` DATE                      NULL,
    `no_of_nights`   INT(11)          UNSIGNED NULL,
    `room_qty`       INT(11)          UNSIGNED NULL,
    `room_type`      VARCHAR(120)              NULL,
    `transport_id`   BIGINT(20)       UNSIGNED NULL,
    `transport_type` VARCHAR(80)               NULL,
    `route`          VARCHAR(180)              NULL,
    `is_ziarat`      TINYINT(1)                NOT NULL DEFAULT 0,
    `package_id`     BIGINT(20)       UNSIGNED NULL,
    `package_name`   VARCHAR(180)              NULL,
    `departure_date` DATE                      NULL,
    `arrival_date`   DATE                      NULL,
    `created_at`     DATETIME                  NULL,
    `updated_at`     DATETIME                  NULL,
    PRIMARY KEY (`id`),
    KEY `sale_items_sale_id_index` (`sale_id`),
    KEY `sale_items_item_type_index` (`item_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Foreign key sale_items.sale_id -> sales.id (only when the column types match,
-- otherwise MySQL refuses the constraint and the script would abort).
SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
      WHERE CONSTRAINT_SCHEMA = @db AND TABLE_NAME = 'sale_items'
        AND CONSTRAINT_NAME = 'sale_items_sale_id_foreign') = 0
    AND (SELECT COLUMN_TYPE FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'sales' AND COLUMN_NAME = 'id')
      = (SELECT COLUMN_TYPE FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'sale_items' AND COLUMN_NAME = 'sale_id'),
    'ALTER TABLE `sale_items` ADD CONSTRAINT `sale_items_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- 2. sales.currency / exchange_rate / amount_pkr
-- -----------------------------------------------------------------------------
SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'sales' AND COLUMN_NAME = 'currency') = 0,
    'ALTER TABLE `sales` ADD COLUMN `currency` VARCHAR(10) NOT NULL DEFAULT ''PKR'' COMMENT ''Sale currency: PKR or SAR''',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'sales' AND COLUMN_NAME = 'exchange_rate') = 0,
    'ALTER TABLE `sales` ADD COLUMN `exchange_rate` DECIMAL(14,4) NOT NULL DEFAULT 1.0000 COMMENT ''Currency to PKR exchange rate''',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'sales' AND COLUMN_NAME = 'amount_pkr') = 0,
    'ALTER TABLE `sales` ADD COLUMN `amount_pkr` DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT ''Total amount converted to PKR''',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- 3. sale_items.currency / exchange_rate / amount_pkr
-- -----------------------------------------------------------------------------
SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'sale_items' AND COLUMN_NAME = 'currency') = 0,
    'ALTER TABLE `sale_items` ADD COLUMN `currency` VARCHAR(10) NOT NULL DEFAULT ''PKR'' COMMENT ''Component currency: PKR or SAR''',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'sale_items' AND COLUMN_NAME = 'exchange_rate') = 0,
    'ALTER TABLE `sale_items` ADD COLUMN `exchange_rate` DECIMAL(14,4) NOT NULL DEFAULT 1.0000 COMMENT ''Component currency to PKR exchange rate''',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'sale_items' AND COLUMN_NAME = 'amount_pkr') = 0,
    'ALTER TABLE `sale_items` ADD COLUMN `amount_pkr` DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT ''Component amount converted to PKR''',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- 4. Legacy sale columns used by the package sale form
--    (sales.package_name / package_details / attachment)
-- -----------------------------------------------------------------------------
SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'sales' AND COLUMN_NAME = 'package_name') = 0,
    'ALTER TABLE `sales` ADD COLUMN `package_name` VARCHAR(180) NULL',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'sales' AND COLUMN_NAME = 'package_details') = 0,
    'ALTER TABLE `sales` ADD COLUMN `package_details` TEXT NULL',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'sales' AND COLUMN_NAME = 'attachment') = 0,
    'ALTER TABLE `sales` ADD COLUMN `attachment` VARCHAR(255) NULL',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- 5. sales.sales_category_id must accept NULL (category is derived from the
--    sale components now). Drop any existing FK, relax the column, re-add FK.
-- -----------------------------------------------------------------------------
SET @fk := (
    SELECT CONSTRAINT_NAME
      FROM information_schema.KEY_COLUMN_USAGE
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'sales'
       AND COLUMN_NAME = 'sales_category_id'
       AND REFERENCED_TABLE_NAME IS NOT NULL
     LIMIT 1
);
SET @sql := IF(@fk IS NULL, 'SELECT 1', CONCAT('ALTER TABLE `sales` DROP FOREIGN KEY `', @fk, '`'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT IS_NULLABLE FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'sales' AND COLUMN_NAME = 'sales_category_id') = 'YES',
    'SELECT 1',
    'ALTER TABLE `sales` MODIFY `sales_category_id` INT(11) UNSIGNED NULL'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
      WHERE CONSTRAINT_SCHEMA = @db AND TABLE_NAME = 'sales'
        AND CONSTRAINT_NAME = 'sales_sales_category_id_foreign') = 0
    AND (SELECT COUNT(*) FROM information_schema.TABLES
          WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'sales_categories') = 1,
    'ALTER TABLE `sales` ADD CONSTRAINT `sales_sales_category_id_foreign` FOREIGN KEY (`sales_category_id`) REFERENCES `sales_categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- =============================================================================
--  DATA BACKFILLS  (only zero / empty values are filled)
-- =============================================================================

-- Component amounts that predate the currency columns are PKR.
UPDATE `sale_items` SET `amount_pkr` = `amount` WHERE `amount_pkr` = 0 AND `amount` <> 0;

-- Sale totals that predate the currency columns are PKR.
UPDATE `sales` SET `amount_pkr` = `amount` WHERE `amount_pkr` = 0 AND `amount` <> 0;
UPDATE `sales` SET `currency` = 'PKR' WHERE `currency` IS NULL OR `currency` = '';
UPDATE `sales` SET `exchange_rate` = 1.0000 WHERE `exchange_rate` IS NULL OR `exchange_rate` = 0;
UPDATE `sale_items` SET `currency` = 'PKR' WHERE `currency` IS NULL OR `currency` = '';
UPDATE `sale_items` SET `exchange_rate` = 1.0000 WHERE `exchange_rate` IS NULL OR `exchange_rate` = 0;

-- =============================================================================
--  VERIFY
-- =============================================================================
-- Expect: sale_items present, and every listed column to show up.
SELECT `TABLE_NAME`, `COLUMN_NAME`, `COLUMN_TYPE`, `IS_NULLABLE`, `COLUMN_DEFAULT`
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @db
  AND (
        (`TABLE_NAME` = 'sales'      AND `COLUMN_NAME` IN ('currency', 'exchange_rate', 'amount_pkr', 'package_name', 'package_details', 'attachment'))
     OR (`TABLE_NAME` = 'sale_items' AND `COLUMN_NAME` IN ('sale_id', 'item_type', 'currency', 'exchange_rate', 'amount_pkr', 'is_ziarat'))
  )
ORDER BY `TABLE_NAME`, `ORDINAL_POSITION`;

-- sales_category_id must now be nullable (IS_NULLABLE = YES)
SELECT `COLUMN_NAME`, `COLUMN_TYPE`, `IS_NULLABLE`
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'sales' AND COLUMN_NAME = 'sales_category_id';

-- =============================================================================
--  OPTIONAL — record the two migrations as applied
--  Only if you applied this file by hand and want `php spark migrate` on the
--  server to skip them. Harmless if run twice.
-- =============================================================================
-- SET @batch := (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`);
--
-- INSERT INTO `migrations` (`version`, `class`, `group`, `namespace`, `time`, `batch`)
-- SELECT v.`version`, CONCAT('App', CHAR(92), 'Database', CHAR(92), 'Migrations', CHAR(92), v.`class`), 'default', 'App', UNIX_TIMESTAMP(), @batch
--   FROM (
--         SELECT '2026-10-24-000049' AS `version`, 'CreateSaleItemsAndCurrencyFields' AS `class`
--   UNION SELECT '2026-10-25-000050', 'AddCurrencyToSaleItems'
--        ) v
--  WHERE NOT EXISTS (SELECT 1 FROM `migrations` m WHERE m.`version` = v.`version`);

-- =============================================================================
--  ROLLBACK (destructive — only if you need to undo this file)
-- =============================================================================
-- ALTER TABLE `sale_items` DROP FOREIGN KEY `sale_items_sale_id_foreign`;
-- DROP TABLE IF EXISTS `sale_items`;
-- ALTER TABLE `sales`
--     DROP COLUMN `currency`,
--     DROP COLUMN `exchange_rate`,
--     DROP COLUMN `amount_pkr`;
