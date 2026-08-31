-- ATMABISWAS — add Facebook + Instagram post URLs to the blogs table
-- Additive/nullable only — no existing data is touched and no column is dropped.
-- Run once in phpMyAdmin → SQL tab.
--
-- Both are the URL of the social post ABOUT a press item, not a share endpoint.
-- Instagram has no web share API (there is no instagram.com/share?url=), so a
-- stored URL is the only thing its button can point at. Facebook keeps its
-- share dialog as a fallback when no URL is set, which is why that column is
-- optional rather than required.
--
-- VARCHAR(500) matches source_link; social permalinks carry long tracking tails.

ALTER TABLE blogs ADD COLUMN facebook_url  VARCHAR(500) NULL DEFAULT NULL AFTER canonical_url;
ALTER TABLE blogs ADD COLUMN instagram_url VARCHAR(500) NULL DEFAULT NULL AFTER facebook_url;
