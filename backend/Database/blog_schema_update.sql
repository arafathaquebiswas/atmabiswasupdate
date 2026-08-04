-- Blog schema updates for ATMABISWAS
-- Run this to ensure all required columns exist

-- Add missing columns to blogs table if they don't exist
ALTER TABLE blogs 
ADD COLUMN IF NOT EXISTS summary TEXT,
ADD COLUMN IF NOT EXISTS year YEAR DEFAULT (YEAR(CURRENT_DATE)),
ADD COLUMN IF NOT EXISTS image_title VARCHAR(255),
ADD COLUMN IF NOT EXISTS source_link VARCHAR(500),
ADD COLUMN IF NOT EXISTS status ENUM('draft', 'published', 'archived') DEFAULT 'published',
ADD COLUMN IF NOT EXISTS views INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_blogs_status ON blogs(status);
CREATE INDEX IF NOT EXISTS idx_blogs_upload_date ON blogs(upload_date);
CREATE INDEX IF NOT EXISTS idx_blogs_year ON blogs(year);
CREATE INDEX IF NOT EXISTS idx_blogs_author ON blogs(blog_author);

-- Update existing records to have proper status
UPDATE blogs SET status = 'published' WHERE status IS NULL;
