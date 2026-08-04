-- =====================================================================
-- ATMABISWAS Contact Management System — Migration Script
-- Run this ONCE on the production database before deploying code
-- Database: u106340611_arafatbiswas
-- =====================================================================

-- Step 1: Create regional_offices table
CREATE TABLE IF NOT EXISTS `regional_offices` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `region_name`   VARCHAR(255)    NOT NULL,
    `address`       TEXT            NOT NULL,
    `designation`   VARCHAR(255)    NOT NULL DEFAULT 'Regional Manager',
    `phone`         VARCHAR(50)     NOT NULL,
    `display_order` INT             NOT NULL DEFAULT 0,
    `status`        TINYINT(1)      NOT NULL DEFAULT 1 COMMENT '1=active 0=inactive',
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status`        (`status`),
    INDEX `idx_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 2: Insert all existing regional office data (from regional.json)
INSERT INTO `regional_offices` (`region_name`, `address`, `designation`, `phone`, `display_order`, `status`) VALUES
('Chuadanga Region',  'Cinama Hall Para, Chuadanga',                          'Regional Manager', '01725-683174', 1, 1),
('Dingadah Region',   'Dingadah Khejura, Chuadanga Sadar, Chuadanga',         'Regional Manager', '01958-573119', 2, 1),
('AsmanKhali Region', 'AsmanKhali Bazar, AsmanKhali, Alamdanga, Chuadanga',   'Regional Manager', '01725-186276', 3, 1),
('Alamdanga Region',  'Rail Station Para, Alamdanga, Chuadanga',               'Regional Manager', '01958-573194', 4, 1),
('Kushtia Region',    'Stadium Para, Kushtia Sadar, Kushtia',                  'Regional Manager', '01958-573194', 5, 1),
('Jibonnagar Region', 'Jibonnagar Eidga Para, Jibonnagar, Chuadanga',          'Regional Manager', '01725-683174', 6, 1),
('Jhikorgasa Region', 'Jhikorgasa Pazila Mor, Jhikorgasa, Jessore',            'Regional Manager', '01721-505833', 7, 1),
('Chowgasha Region',  'Isapur Dewan Para, Chowgasha, Jessore',                 'Regional Manager', '01722-603003', 8, 1),
('Pangsha Region',    'Dotto Para, Pangsha, Rajbari',                          'Regional Manager', '01958-573119', 9, 1);

-- Step 3: Create branches table
CREATE TABLE IF NOT EXISTS `branches` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `branch_name`   VARCHAR(255)    NOT NULL,
    `address`       TEXT            NOT NULL,
    `division`      VARCHAR(100)    NOT NULL,
    `district`      VARCHAR(100)    NOT NULL,
    `display_order` INT             NOT NULL DEFAULT 0,
    `status`        TINYINT(1)      NOT NULL DEFAULT 1 COMMENT '1=active 0=inactive',
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status`        (`status`),
    INDEX `idx_division`      (`division`),
    INDEX `idx_display_order` (`display_order`),
    INDEX `idx_division_name` (`division`, `branch_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 4: Migrate all existing branch data from the `branch` table
-- (branchName -> branch_name, branchLoc -> address, dist -> district)
INSERT INTO `branches` (`branch_name`, `address`, `division`, `district`, `display_order`, `status`)
SELECT
    `branchName`,
    `branchLoc`,
    `division`,
    `dist`,
    0,
    1
FROM `branch`
WHERE `branchName` IS NOT NULL
  AND `branchName` != ''
ORDER BY `division`, `branchName`;

-- Step 5: Verify row counts
SELECT 'regional_offices'   AS table_name, COUNT(*) AS migrated_rows FROM `regional_offices`
UNION ALL
SELECT 'branches',                          COUNT(*)                  FROM `branches`
UNION ALL
SELECT 'branch (original source)',          COUNT(*)                  FROM `branch`;

-- Expected:
--   regional_offices  → 9 rows
--   branches          → same count as `branch` table
