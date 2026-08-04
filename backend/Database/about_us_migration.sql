-- ═══════════════════════════════════════════════════════════
--  ATMABISWAS — About Us Content Table
--  Run once on the database to enable backend editing.
-- ═══════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `about_us_content` (
  `id`           INT          NOT NULL AUTO_INCREMENT,
  `section_key`  VARCHAR(50)  NOT NULL,
  `image_path`   VARCHAR(500) NOT NULL DEFAULT '',
  `image_alt`    VARCHAR(255) NOT NULL DEFAULT '',
  `text_content` TEXT         NOT NULL,
  `updated_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                              ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `section_key_unique` (`section_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default content (safe to run multiple times)
INSERT INTO `about_us_content` (`section_key`, `image_path`, `image_alt`, `text_content`)
VALUES
(
  'about_us',
  'office_pic/office_pic.jpg',
  'ATMABISWAS Office',
  'ATMABISWAS is a non-governmental, non-profit, voluntary, and development-focused organization committed to creating meaningful social change and fostering sustainable development. Established in January 1991 under the Department of Social Welfare, ATMABISWAS has dedicated over three decades to empowering communities across Bangladesh. The organization primarily focuses on serving the disadvantaged populations, striving to uplift their living standards and enhance their access to essential resources and opportunities.

Since its inception, ATMABISWAS has worked tirelessly to support marginalized individuals and communities, with an initial emphasis on the district of Chuadanga. Through a range of social welfare programs, development projects, and micro-credit initiatives, the organization has impacted thousands of lives, enabling beneficiaries to break the cycle of poverty and build a better future.'
),
(
  'our_team',
  'office_pic/00000.jpg',
  'ATMABISWAS Team with PKSF',
  'Our team consists of dedicated professionals who are passionate about making a difference. We collaborate to create a positive impact and support each other in our mission to empower communities and foster sustainable development.

Our team members come from diverse backgrounds, bringing a wealth of experience and expertise to the organization. We are united by our shared commitment to social justice, equality, and sustainable development. Each member of our team plays a crucial role in driving our mission forward — from field workers to administrative staff, project managers, and volunteers. Together, we strive to create a positive and lasting impact on the communities we serve.'
)
ON DUPLICATE KEY UPDATE `section_key` = `section_key`;
