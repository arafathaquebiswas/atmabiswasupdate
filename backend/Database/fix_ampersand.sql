-- ============================================================
-- 1. Fix &amp; → & in all affected tables (run once)
-- ============================================================
UPDATE sectors  SET sector_name = REPLACE(sector_name, '&amp;', '&');
UPDATE jobcodes SET JobTitle    = REPLACE(JobTitle,    '&amp;', '&');
UPDATE jobs     SET job_title   = REPLACE(job_title,   '&amp;', '&');
UPDATE jobs     SET job_dept    = REPLACE(job_dept,    '&amp;', '&');

-- ============================================================
-- 2. Ensure jobcodes table has the correct structure
--    (JobCode with a sensible default so inserts never fail)
-- ============================================================
ALTER TABLE jobcodes
    MODIFY COLUMN JobTitle VARCHAR(255) NOT NULL,
    MODIFY COLUMN JobCode  VARCHAR(10)  NOT NULL DEFAULT 'TBD';

-- ============================================================
-- 3. Fix any existing rows that still have the old 'ABN1' default
--    by regenerating their code from the title initials.
--    Run manually in phpMyAdmin if needed.
-- ============================================================
-- Example: UPDATE jobcodes SET JobCode = 'FO10' WHERE JobTitle = 'Field Officer' AND JobCode = 'ABN1';

-- ============================================================
-- 4. Add bdjobs_link and apply_enabled columns to jobs table
--    Run once. Safe to run if columns already exist (will error
--    on duplicate column — just ignore that error).
-- ============================================================
ALTER TABLE jobs
    ADD COLUMN bdjobs_link   VARCHAR(500)   DEFAULT NULL,
    ADD COLUMN apply_enabled TINYINT(1)     NOT NULL DEFAULT 1;
