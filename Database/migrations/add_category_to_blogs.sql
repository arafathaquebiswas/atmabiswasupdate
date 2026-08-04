-- Add category column to blogs table for press/media categorisation
-- Run once on the database before deploying the updated press.php

ALTER TABLE blogs
    ADD COLUMN category VARCHAR(50) NOT NULL DEFAULT 'news' AFTER year;
