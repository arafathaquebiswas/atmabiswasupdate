-- ═══════════════════════════════════════════════════════════════
-- ATMABISWAS — blogs table complete upgrade migration
-- Safe to run: ignore any "Duplicate column name" or
-- "Duplicate key name" errors — those columns/indexes already exist.
-- Run the full block in phpMyAdmin → SQL tab.
-- ═══════════════════════════════════════════════════════════════

-- ── Core article columns ─────────────────────────────────────────
ALTER TABLE blogs ADD COLUMN category    VARCHAR(50)  NOT NULL DEFAULT 'news';
ALTER TABLE blogs ADD COLUMN source_link VARCHAR(500) NULL DEFAULT NULL;
ALTER TABLE blogs ADD COLUMN slug        VARCHAR(300) NULL DEFAULT NULL AFTER blog_title;

-- ── Engagement columns ───────────────────────────────────────────
ALTER TABLE blogs ADD COLUMN views    INT UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE blogs ADD COLUMN status   VARCHAR(20)  NOT NULL DEFAULT 'published';
ALTER TABLE blogs ADD COLUMN featured TINYINT(1)   NOT NULL DEFAULT 0;

-- ── Publishing metadata ──────────────────────────────────────────
ALTER TABLE blogs ADD COLUMN tags         VARCHAR(500)  NULL DEFAULT NULL;
ALTER TABLE blogs ADD COLUMN reading_time TINYINT UNSIGNED NOT NULL DEFAULT 0;

-- ── SEO fields ───────────────────────────────────────────────────
ALTER TABLE blogs ADD COLUMN seo_title       VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE blogs ADD COLUMN seo_description TEXT         NULL DEFAULT NULL;
ALTER TABLE blogs ADD COLUMN seo_keywords    VARCHAR(500) NULL DEFAULT NULL;
ALTER TABLE blogs ADD COLUMN social_image    VARCHAR(500) NULL DEFAULT NULL;

-- ── Timestamp (auto-updates on every row change) ─────────────────
ALTER TABLE blogs ADD COLUMN last_updated TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP;

-- ── Performance indexes (ignore "Duplicate key name" errors) ─────
ALTER TABLE blogs ADD INDEX idx_status      (status);
ALTER TABLE blogs ADD INDEX idx_featured    (featured);
ALTER TABLE blogs ADD INDEX idx_category    (category);
ALTER TABLE blogs ADD INDEX idx_upload_date (upload_date);
