-- ATMABISWAS — add Focus Keyword + Canonical URL to blogs table
-- Additive/nullable only — no existing data is touched.
-- Run this once in phpMyAdmin → SQL tab before deploying the new blog editor.

ALTER TABLE blogs ADD COLUMN focus_keyword VARCHAR(191) NULL DEFAULT NULL AFTER seo_keywords;
ALTER TABLE blogs ADD COLUMN canonical_url VARCHAR(255) NULL DEFAULT NULL AFTER social_image;
