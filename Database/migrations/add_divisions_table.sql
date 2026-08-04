-- ============================================================
-- Migration: Add divisions table
-- Run this against your Hostinger MySQL database.
-- Safe to run multiple times (DROP + CREATE / INSERT IGNORE).
-- ============================================================

-- 1. Drop old broken table if it exists, then recreate cleanly
DROP TABLE IF EXISTS `divisions`;

CREATE TABLE `divisions` (
    `id`         INT          AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(100) NOT NULL,
    `status`     TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_name` (`name`),
    INDEX `idx_status`   (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Seed from existing branch data (skips duplicates)
INSERT IGNORE INTO `divisions` (`name`, `status`)
SELECT DISTINCT `division`, 1
FROM   `branches`
WHERE  `division` IS NOT NULL
  AND  `division` != ''
ORDER  BY `division` ASC;

-- 3. Verify — shows the result
SELECT id, name, status, created_at
FROM   `divisions`
ORDER  BY `name` ASC;
