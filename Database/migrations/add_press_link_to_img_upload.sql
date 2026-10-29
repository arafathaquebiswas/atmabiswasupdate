-- ATMABISWAS — add the Press article link to Latest News cards (img_upload)
-- Additive/nullable only — no existing row, image or link is touched.
-- Run once in phpMyAdmin → SQL tab. Optional: the first Latest News save from
-- the admin panel adds the same column automatically (img_upload_ensure_columns()
-- in backend/Database/db.php); a "Duplicate column name" error here just means
-- that has already happened.
--
-- Holds "press.php?id=<blog_id>" — the full article on the Press page for the
-- same story. fb_link (the Facebook post) already exists alongside it.
-- VARCHAR(500) matches fb_link.

ALTER TABLE img_upload ADD COLUMN press_link VARCHAR(500) NULL DEFAULT NULL;
