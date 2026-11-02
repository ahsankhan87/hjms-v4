-- =============================================================================
--  HJMS v4  ·  Package module schema + data updates
--  Session: 2026-10-28   (migrations 000051 … 000056)
--
--  WHAT THIS DOES (matches the 6 CodeIgniter migrations applied in development)
--   1. package_hotels.quint_cost              DECIMAL(14,2) NOT NULL DEFAULT 0.00
--   2. packages.exchange_rate                 DECIMAL(12,4) NOT NULL DEFAULT 1.0000
--   3. packages.total_seats backfill          (seats now live on the package, not the hotel)
--   4. hotel_rooms.beds                       TINYINT(3) UNSIGNED NOT NULL DEFAULT 0  + seed
--   5. packages.include_ziarat / ziarat_cost  TINYINT(1) DEFAULT 0 / DECIMAL(14,2) DEFAULT 0.00
--   6. flights.fare                           DECIMAL(14,2) NOT NULL DEFAULT 0.00
--
--  HOW TO RUN (cPanel)
--   phpMyAdmin  ->  select your database  ->  SQL tab  ->  paste this file  ->  Go
--   or:  mysql -u USER -p DBNAME < 2026-10-28-package-module-updates.sql
--
--  SAFETY
--   * Every ALTER is guarded, so the script can be run more than once.
--   * Nothing is dropped and no existing value is overwritten (only empty/zero
--     values are filled in the two backfill steps).
--   * New columns are appended to the end of each table on purpose: the
--     application never relies on column order.
--   * Take a database backup before running this on live data.
-- =============================================================================

SET NAMES utf8mb4;

-- -----------------------------------------------------------------------------
-- 0. Safety net: packages.total_seats must exist (it is an old column, but the
--    seat backfill below depends on it).
-- -----------------------------------------------------------------------------
SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'packages' AND COLUMN_NAME = 'total_seats') = 0,
    'ALTER TABLE `packages` ADD COLUMN `total_seats` INT NOT NULL DEFAULT 0',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- 1. package_hotels.quint_cost   (5th room tier — "quint")
-- -----------------------------------------------------------------------------
SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'package_hotels' AND COLUMN_NAME = 'quint_cost') = 0,
    'ALTER TABLE `package_hotels` ADD COLUMN `quint_cost` DECIMAL(14,2) NOT NULL DEFAULT 0.00',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- 2. packages.exchange_rate   (SAR -> PKR rate per package)
-- -----------------------------------------------------------------------------
SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'packages' AND COLUMN_NAME = 'exchange_rate') = 0,
    'ALTER TABLE `packages` ADD COLUMN `exchange_rate` DECIMAL(12,4) NOT NULL DEFAULT 1.0000',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- 3. packages.include_ziarat + packages.ziarat_cost   (optional holy-sites visit, SAR)
-- -----------------------------------------------------------------------------
SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'packages' AND COLUMN_NAME = 'include_ziarat') = 0,
    'ALTER TABLE `packages` ADD COLUMN `include_ziarat` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'packages' AND COLUMN_NAME = 'ziarat_cost') = 0,
    'ALTER TABLE `packages` ADD COLUMN `ziarat_cost` DECIMAL(14,2) NOT NULL DEFAULT 0.00',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- 4. hotel_rooms.beds   (beds per room; 0 = derive from the room type)
-- -----------------------------------------------------------------------------
SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hotel_rooms' AND COLUMN_NAME = 'beds') = 0,
    'ALTER TABLE `hotel_rooms` ADD COLUMN `beds` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- 5. flights.fare   (ticket fare per pilgrim, PKR — loaded into package ticket cost)
-- -----------------------------------------------------------------------------
SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'flights' AND COLUMN_NAME = 'fare') = 0,
    'ALTER TABLE `flights` ADD COLUMN `fare` DECIMAL(14,2) NOT NULL DEFAULT 0.00',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- =============================================================================
--  DATA BACKFILLS  (idempotent — only empty / zero values are filled)
-- =============================================================================

-- 6. Seats moved from package_hotels to packages.
--    Existing packages inherit the smallest positive seat count they used to
--    carry on their hotel rows, so they keep the same sellable capacity.
UPDATE `packages` p
INNER JOIN (
    SELECT `package_id`, MIN(`total_seats`) AS `seats`
    FROM `package_hotels`
    WHERE `total_seats` > 0
    GROUP BY `package_id`
) h ON h.`package_id` = p.`id`
SET p.`total_seats` = h.`seats`
WHERE p.`total_seats` IS NULL OR p.`total_seats` = 0;

-- 7. Seed hotel_rooms.beds from the room type name — step 1: names that carry
--    their own capacity, e.g. "Sharing 5 Bed", "quad 4 pax".
--    (REGEXP_SUBSTR requires MySQL 8.0+ / MariaDB 10.0+. On MySQL 5.7 this
--     step is skipped automatically — step 2 below still seeds every standard
--     room type.)
SET @sql := IF(
    (SELECT CASE WHEN VERSION() LIKE '5.7%' THEN 0 ELSE 1 END) = 1,
    'UPDATE `hotel_rooms`
        SET `beds` = GREATEST(1, CAST(REGEXP_SUBSTR(LOWER(`room_type`), ''[0-9]+'') AS UNSIGNED))
      WHERE `beds` = 0
        AND LOWER(`room_type`) REGEXP ''[0-9]+[[:space:]]*(bed|pax|person|sharing)''',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 8. Seed hotel_rooms.beds from the room type name — step 2: standard tier names.
UPDATE `hotel_rooms`
SET `beds` = CASE
        WHEN LOWER(`room_type`) LIKE '%sharing%' THEN 6
        WHEN LOWER(`room_type`) LIKE '%quint%'   THEN 5
        WHEN LOWER(`room_type`) LIKE '%quad%'    THEN 4
        WHEN LOWER(`room_type`) LIKE '%triple%'  THEN 3
        WHEN LOWER(`room_type`) LIKE '%trp%'     THEN 3
        WHEN LOWER(`room_type`) LIKE '%double%'  THEN 2
        WHEN LOWER(`room_type`) LIKE '%dbl%'     THEN 2
        WHEN LOWER(`room_type`) LIKE '%single%'  THEN 1
        ELSE `beds`
    END
WHERE `beds` = 0
  AND TRIM(`room_type`) <> '';

-- =============================================================================
--  OPTIONAL — review before uncommenting
-- =============================================================================

-- Existing packages receive exchange_rate = 1, which means their stored SAR
-- costs are still read as PKR (i.e. old prices stay exactly as they were).
-- Once existing packages have been re-priced in SAR, set the real rate, e.g.:
-- UPDATE `packages` SET `exchange_rate` = 75.0000 WHERE `exchange_rate` = 1.0000;

-- Give existing hotel stays a quint price equal to their sharing price
-- (the 5-tier grid prices quint like sharing by default):
-- UPDATE `package_hotels` SET `quint_cost` = `sharing_cost`
--  WHERE `quint_cost` = 0 AND `sharing_cost` > 0;

-- =============================================================================
--  VERIFY (expected: 7 rows — the 6 new columns plus packages.total_seats)
-- =============================================================================
SELECT `TABLE_NAME`, `COLUMN_NAME`, `COLUMN_TYPE`, `COLUMN_DEFAULT`
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND (
        (`TABLE_NAME` = 'package_hotels' AND `COLUMN_NAME` = 'quint_cost')
     OR (`TABLE_NAME` = 'packages'       AND `COLUMN_NAME` IN ('exchange_rate', 'include_ziarat', 'ziarat_cost', 'total_seats'))
     OR (`TABLE_NAME` = 'hotel_rooms'    AND `COLUMN_NAME` = 'beds')
     OR (`TABLE_NAME` = 'flights'        AND `COLUMN_NAME` = 'fare')
  )
ORDER BY `TABLE_NAME`, `COLUMN_NAME`;

-- =============================================================================
--  OPTIONAL — record these 6 migrations in CodeIgniter's `migrations` table
--  Only needed if you applied this file by hand and want `php spark migrate`
--  on the server to consider them done. Harmless if run twice.
-- =============================================================================
-- SET @batch := (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`);
--
-- INSERT INTO `migrations` (`version`, `class`, `group`, `namespace`, `time`, `batch`)
-- SELECT v.`version`, CONCAT('App', CHAR(92), 'Database', CHAR(92), 'Migrations', CHAR(92), v.`class`), 'default', 'App', UNIX_TIMESTAMP(), @batch
--   FROM (
--         SELECT '2026-10-28-000051' AS `version`, 'AddQuintCostToPackageHotels' AS `class`
--   UNION SELECT '2026-10-28-000052', 'AddExchangeRateToPackages'
--   UNION SELECT '2026-10-28-000053', 'BackfillPackageTotalSeats'
--   UNION SELECT '2026-10-28-000054', 'AddBedsToHotelRooms'
--   UNION SELECT '2026-10-28-000055', 'AddZiaratToPackages'
--   UNION SELECT '2026-10-28-000056', 'AddFareToFlights'
--        ) v
--  WHERE NOT EXISTS (SELECT 1 FROM `migrations` m WHERE m.`version` = v.`version`);
--
-- DELETE FROM `migrations` WHERE `version` IN (
--   '2026-10-28-000051','2026-10-28-000052','2026-10-28-000053',
--   '2026-10-28-000054','2026-10-28-000055','2026-10-28-000056'
-- );

-- =============================================================================
--  ROLLBACK (only if you ever need to undo this update — destructive)
-- =============================================================================
-- ALTER TABLE `package_hotels` DROP COLUMN `quint_cost`;
-- ALTER TABLE `packages`       DROP COLUMN `exchange_rate`;
-- ALTER TABLE `packages`       DROP COLUMN `include_ziarat`, DROP COLUMN `ziarat_cost`;
-- ALTER TABLE `hotel_rooms`    DROP COLUMN `beds`;
-- ALTER TABLE `flights`        DROP COLUMN `fare`;
