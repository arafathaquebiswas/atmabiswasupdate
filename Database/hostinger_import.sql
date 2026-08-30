-- =====================================================================
--  ATMABISWAS - full database import for Hostinger
--
--  Target database : u168945187_ATMABISWAS
--  Generated from  : local MySQL database `u106340611_arafatbiswas`
--
--  HOW TO IMPORT
--    hPanel -> Databases -> phpMyAdmin (next to u168945187_ATMABISWAS)
--    -> Import tab -> choose this file -> Go.
--
--  There is deliberately no CREATE DATABASE / USE statement: phpMyAdmin
--  imports into the database you already have selected, and a USE line
--  naming the old database would abort the import.
--
--  Every table starts with DROP TABLE IF EXISTS, so re-importing this
--  file replaces schema and data cleanly instead of erroring on
--  duplicates. That also means importing over a live database DISCARDS
--  whatever those tables currently hold - export a backup first if the
--  target already has real content.
--
--  NOTE: `jobCodes` is created here as lowercase `jobcodes`. macOS MySQL
--  is case-insensitive about table names, Linux (Hostinger) is not, and
--  all nine PHP queries spell it `jobcodes`. Creating it with a capital
--  C would make every job-code page fail on the server.
-- =====================================================================


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

DROP TABLE IF EXISTS `about_us_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `about_us_content` (
  `id` int NOT NULL AUTO_INCREMENT,
  `section_key` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `image_alt` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `text_content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `section_key_unique` (`section_key`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `about_us_content` DISABLE KEYS */;
INSERT INTO `about_us_content` VALUES (1,'about_us','office_pic/office_pic.jpg','ATMABISWAS Office','ATMABISWAS is a non-governmental, non-profit, voluntary, and development-focused organization committed to creating meaningful social change and fostering sustainable development. Established in January 1991 under the Department of Social Welfare, ATMABISWAS has dedicated over three decades to empowering communities across Bangladesh. The organization primarily focuses on serving the disadvantaged populations, striving to uplift their living standards and enhance their access to essential resources and opportunities.\n\nSince its inception, ATMABISWAS has worked tirelessly to support marginalized individuals and communities, with an initial emphasis on the district of Chuadanga. Through a range of social welfare programs, development projects, and micro-credit initiatives, the organization has impacted thousands of lives, enabling beneficiaries to break the cycle of poverty and build a better future.','2026-08-30 09:25:44'),(2,'our_team','office_pic/00000.jpg','ATMABISWAS Team with PKSF','Our team consists of dedicated professionals who are passionate about making a difference. We collaborate to create a positive impact and support each other in our mission to empower communities and foster sustainable development.\n\nOur team members come from diverse backgrounds, bringing a wealth of experience and expertise to the organization. We are united by our shared commitment to social justice, equality, and sustainable development. Each member of our team plays a crucial role in driving our mission forward — from field workers to administrative staff, project managers, and volunteers. Together, we strive to create a positive and lasting impact on the communities we serve.','2026-08-30 09:25:44');
/*!40000 ALTER TABLE `about_us_content` ENABLE KEYS */;
DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `adminId` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fullname` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `username` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `pswd` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `reset_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `role` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'admin',
  PRIMARY KEY (`adminId`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'ahsan@gmail.com','Ahsan Khan','ahsan@gmail.com','$2a$10$cA4q2M6ceY/ZCIQeaAjI3.6iyXqn7DMpoJNLA4WpwpzHK7CqTXgdy',NULL,NULL,'active','2026-08-04 15:27:59','super_admin');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
DROP TABLE IF EXISTS `blogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blogs` (
  `blog_id` int NOT NULL AUTO_INCREMENT,
  `blog_title` varchar(255) NOT NULL,
  `slug` varchar(300) DEFAULT NULL,
  `blog_content` text NOT NULL,
  `blog_author` varchar(255) DEFAULT 'ATMABISWAS',
  `cover_img` varchar(255) DEFAULT NULL,
  `upload_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `summary` text,
  `year` year DEFAULT (year(curdate())),
  `category` varchar(50) NOT NULL DEFAULT 'news',
  `source_link` varchar(500) DEFAULT NULL,
  `views` int unsigned NOT NULL DEFAULT '0',
  `status` varchar(20) NOT NULL DEFAULT 'published',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `tags` varchar(500) DEFAULT NULL,
  `reading_time` tinyint unsigned NOT NULL DEFAULT '0',
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` text,
  `seo_keywords` varchar(500) DEFAULT NULL,
  `focus_keyword` varchar(191) DEFAULT NULL,
  `social_image` varchar(500) DEFAULT NULL,
  `canonical_url` varchar(255) DEFAULT NULL,
  `last_updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`blog_id`),
  KEY `idx_status` (`status`),
  KEY `idx_featured` (`featured`),
  KEY `idx_category` (`category`),
  KEY `idx_upload_date` (`upload_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `blogs` DISABLE KEYS */;
/*!40000 ALTER TABLE `blogs` ENABLE KEYS */;
DROP TABLE IF EXISTS `branch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `branch` (
  `branchId` int NOT NULL AUTO_INCREMENT,
  `branchName` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `branchLoc` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `division` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `dist` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`branchId`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `branch` DISABLE KEYS */;
INSERT INTO `branch` VALUES (1,'Maheshpur Branch','Hamidpur Para, Maheshpur, Jhenaidah','Khulna','Jhenaidah'),(2,'Asmankhali Branch','C/O-Md. Ibrahim Munshi, Asmankhali Bazar, Alamdanga, Chuadanga','Khulna','Chuadanga'),(3,'Jibannagar Branch','C/O-Md. Kutubuddin Sarker, High School Para, Jibannagar, Chuadanga','Khulna','Chuadanga'),(4,'ARPARA BRANCH','Arpara Shalikha, Magura','Khulna','Magura'),(5,'Sorojganj Branch','C/O-Mst. Badrunnaher, Sorojganj Bazar, Chuadanga','Khulna','Chuadanga'),(6,'Navaran Branch','Uttar Buruzbagan Forest Para, Navaran Bazar, Sharsha, Jessore','Khulna','Jessore'),(7,'JHIKARGACHA BRANCH','Village: Raghurathagar, Khalasi Para, Post Office: Raghurathagar, Pouroshova/Upazila: Jhikargacha, District: Jessore','Khulna','Jessore'),(8,'Jhaudia Branch','C/O-Mr. Shawpan Chowdhury, Shahi Masjid Para, Jhaudia, Kushtia','Khulna','Kushtia'),(9,'Kalukhali Branch','Rotondia Kaliukhali, Rajbari','Khulna','Rajbari'),(10,'Kaliganj Branch','C/O-Md. Abdul Hamid (Rtd. ATO), Bihari Moar, Arpara, Kaliganj, Jhenaidah','Khulna','Jhenaidah'),(11,'Meherpur Branch','C/O-Md. Shad Ahmed (Beside of Kobi Nazrul Islam High School), Mollick Para, Meherpur','Khulna','Meherpur'),(12,'Poradaha Branch','C/O-Md. Mosaduzzaman, Harun Moar, Poradaha Bazar, Mirpur, Kushtia','Khulna','Kushtia'),(13,'Alamdanga Branch','C/O-Md. Sonjer Ali, Alamdanga Station Road, Alamdanga, Chuadanga','Khulna','Chuadanga'),(14,'Darsana Branch','C/O-Mst. Selina Begum, Darshana Bus Stand Para, Damurhuda, Chuadanga','Khulna','Chuadanga'),(15,'Sadar Branch-1','Behind of Head Office, Cinema Hall Para, Chuadanga','Khulna','Chuadanga'),(16,'Churamonkati Branch','Ghona Road, Churamonkati Bazar, Churamonkati, Jessore','Khulna','Jessore'),(17,'Hatboalia Branch','C/O-Md. Kauser Ahmad Bablu (Present UP Chairman), Mill Para, Hatboalia, Alamdanga, Chuadanga','Khulna','Chuadanga'),(18,'Andulbaria Branch','C/O-Md. Mofizur Rahaman, Andulbaria Mistri Para, Jiban Nagar, Chuadanga','Khulna','Chuadanga'),(19,'Vairoba Branch','C/O-Md. Ruhul Amin, Vairoba Dotola Jame Masjid, Vairoba Bazar, Maheshpur, Jhenaidah','Khulna','Jhenaidah'),(20,'Dingedah Branch','Previous UP Parishad, Dingedah Bazar, Chuadanga','Khulna','Chuadanga'),(21,'Harinakundu Branch','Village: Chithlia College Para, Union: Harinakundu, Upazila: Harinakundu, District: Jhenaidah','Khulna','Jhenaidah'),(22,'Bamundi Branch','C/O-Md. Abdur Rahim, Bamundi Bazar, Gangni, Meherpur','Khulna','Meherpur'),(23,'PANGSHA Branch','Pangsha Sub Registri Officer Pisone, Pangsha, Rajbari','Khulna','Rajbari'),(24,'CHHUTIPUR BRANCH','Md: Abu Talha Shilu, Village: Mohammadpur, Post Office: Ganganandapur, Union: Ganganandapur, Upazila: Jhikargacha, District: Jessore','Khulna','Jessore'),(25,'Patikabari Branch','C/O-Md. Mostafizur Rahaman, Patikabari Bazar Road, Kushtia','Khulna','Kushtia'),(26,'Alokdia Branch','Alokdia Bazar (Beside of Old Union Parashad), Chuadanga','Khulna','Chuadanga'),(27,'Kotchandpur Branch','C/O-Md. Nurul Islam, Aakh Center Moar, Gabtala Para, Kotchandpur, Jhenaidah','Khulna','Jhenaidah'),(28,'Karpashdanga Branch','C/O-Dr. Asabul Haque, Karpashdanga Bazar, Damurhuda, Chuadanga','Khulna','Chuadanga'),(29,'Amla Branch','Md. Abdur Razzak (Beside of Old Aakh Center), Amla Sadarpur, Amla, Mirpur, Kushtia','Khulna','Kushtia'),(30,'Khashkarra Branch','Khashkarra Bazar, Alamdanga, Chuadanga','Khulna','Chuadanga'),(31,'CHANCHRA','Jessore Sadar, Chanchra','Khulna','Jessore'),(32,'ATMA BISWAS ME','C/O-Husnara Ferdos (Behind of Head Office), Cinema Hall Para, Chuadanga','Khulna','Chuadanga'),(33,'ISHARDI Branch','Piarpur Upazila Road, Piarpur, Ishardi, Pabna','Rajshahi','Pabna');
/*!40000 ALTER TABLE `branch` ENABLE KEYS */;
DROP TABLE IF EXISTS `branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `branches` (
  `id` int NOT NULL AUTO_INCREMENT,
  `branch_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `division` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `district` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_order` int NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=active 0=inactive',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_division` (`division`),
  KEY `idx_display_order` (`display_order`),
  KEY `idx_division_name` (`division`,`branch_name`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `branches` DISABLE KEYS */;
INSERT INTO `branches` VALUES (1,'Alamdanga Branch','C/O-Md. Sonjer Ali, Alamdanga Station Road, Alamdanga, Chuadanga','Khulna','Chuadanga',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(2,'Alokdia Branch','Alokdia Bazar (Beside of Old Union Parashad), Chuadanga','Khulna','Chuadanga',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(3,'Amla Branch','Md. Abdur Razzak (Beside of Old Aakh Center), Amla Sadarpur, Amla, Mirpur, Kushtia','Khulna','Kushtia',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(4,'Andulbaria Branch','C/O-Md. Mofizur Rahaman, Andulbaria Mistri Para, Jiban Nagar, Chuadanga','Khulna','Chuadanga',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(5,'ARPARA BRANCH','Arpara Shalikha, Magura','Khulna','Magura',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(6,'Asmankhali Branch','C/O-Md. Ibrahim Munshi, Asmankhali Bazar, Alamdanga, Chuadanga','Khulna','Chuadanga',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(7,'ATMA BISWAS ME','C/O-Husnara Ferdos (Behind of Head Office), Cinema Hall Para, Chuadanga','Khulna','Chuadanga',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(8,'Bamundi Branch','C/O-Md. Abdur Rahim, Bamundi Bazar, Gangni, Meherpur','Khulna','Meherpur',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(9,'CHANCHRA','Jessore Sadar, Chanchra','Khulna','Jessore',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(10,'CHHUTIPUR BRANCH','Md: Abu Talha Shilu, Village: Mohammadpur, Post Office: Ganganandapur, Union: Ganganandapur, Upazila: Jhikargacha, District: Jessore','Khulna','Jessore',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(11,'Churamonkati Branch','Ghona Road, Churamonkati Bazar, Churamonkati, Jessore','Khulna','Jessore',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(12,'Darsana Branch','C/O-Mst. Selina Begum, Darshana Bus Stand Para, Damurhuda, Chuadanga','Khulna','Chuadanga',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(13,'Dingedah Branch','Previous UP Parishad, Dingedah Bazar, Chuadanga','Khulna','Chuadanga',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(14,'Harinakundu Branch','Village: Chithlia College Para, Union: Harinakundu, Upazila: Harinakundu, District: Jhenaidah','Khulna','Jhenaidah',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(15,'Hatboalia Branch','C/O-Md. Kauser Ahmad Bablu (Present UP Chairman), Mill Para, Hatboalia, Alamdanga, Chuadanga','Khulna','Chuadanga',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(16,'Jhaudia Branch','C/O-Mr. Shawpan Chowdhury, Shahi Masjid Para, Jhaudia, Kushtia','Khulna','Kushtia',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(17,'JHIKARGACHA BRANCH','Village: Raghurathagar, Khalasi Para, Post Office: Raghurathagar, Pouroshova/Upazila: Jhikargacha, District: Jessore','Khulna','Jessore',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(18,'Jibannagar Branch','C/O-Md. Kutubuddin Sarker, High School Para, Jibannagar, Chuadanga','Khulna','Chuadanga',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(19,'Kaliganj Branch','C/O-Md. Abdul Hamid (Rtd. ATO), Bihari Moar, Arpara, Kaliganj, Jhenaidah','Khulna','Jhenaidah',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(20,'Kalukhali Branch','Rotondia Kaliukhali, Rajbari','Khulna','Rajbari',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(21,'Karpashdanga Branch','C/O-Dr. Asabul Haque, Karpashdanga Bazar, Damurhuda, Chuadanga','Khulna','Chuadanga',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(22,'Khashkarra Branch','Khashkarra Bazar, Alamdanga, Chuadanga','Khulna','Chuadanga',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(23,'Kotchandpur Branch','C/O-Md. Nurul Islam, Aakh Center Moar, Gabtala Para, Kotchandpur, Jhenaidah','Khulna','Jhenaidah',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(24,'Maheshpur Branch','Hamidpur Para, Maheshpur, Jhenaidah','Khulna','Jhenaidah',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(25,'Meherpur Branch','C/O-Md. Shad Ahmed (Beside of Kobi Nazrul Islam High School), Mollick Para, Meherpur','Khulna','Meherpur',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(26,'Navaran Branch','Uttar Buruzbagan Forest Para, Navaran Bazar, Sharsha, Jessore','Khulna','Jessore',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(27,'PANGSHA Branch','Pangsha Sub Registri Officer Pisone, Pangsha, Rajbari','Khulna','Rajbari',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(28,'Patikabari Branch','C/O-Md. Mostafizur Rahaman, Patikabari Bazar Road, Kushtia','Khulna','Kushtia',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(29,'Poradaha Branch','C/O-Md. Mosaduzzaman, Harun Moar, Poradaha Bazar, Mirpur, Kushtia','Khulna','Kushtia',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(30,'Sadar Branch-1','Behind of Head Office, Cinema Hall Para, Chuadanga','Khulna','Chuadanga',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(31,'Sorojganj Branch','C/O-Mst. Badrunnaher, Sorojganj Bazar, Chuadanga','Khulna','Chuadanga',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(32,'Vairoba Branch','C/O-Md. Ruhul Amin, Vairoba Dotola Jame Masjid, Vairoba Bazar, Maheshpur, Jhenaidah','Khulna','Jhenaidah',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(33,'ISHARDI Branch','Piarpur Upazila Road, Piarpur, Ishardi, Pabna','Rajshahi','Pabna',0,1,'2026-08-04 14:58:28','2026-08-04 14:58:28');
/*!40000 ALTER TABLE `branches` ENABLE KEYS */;
DROP TABLE IF EXISTS `cv_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cv_applications` (
  `applicationId` int NOT NULL AUTO_INCREMENT,
  `jobId` int NOT NULL,
  `fileDir` varchar(255) NOT NULL DEFAULT '',
  `appliedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_no` varchar(255) NOT NULL,
  `job_title` varchar(255) NOT NULL,
  PRIMARY KEY (`applicationId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `cv_applications` DISABLE KEYS */;
/*!40000 ALTER TABLE `cv_applications` ENABLE KEYS */;
DROP TABLE IF EXISTS `divisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `divisions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_name` (`name`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `divisions` DISABLE KEYS */;
INSERT INTO `divisions` VALUES (1,'Khulna',1,'2026-08-04 14:58:30','2026-08-04 14:58:30'),(2,'Rajshahi',1,'2026-08-04 14:58:30','2026-08-04 14:58:30');
/*!40000 ALTER TABLE `divisions` ENABLE KEYS */;
DROP TABLE IF EXISTS `img_upload`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `img_upload` (
  `img_id` int NOT NULL AUTO_INCREMENT,
  `img_name` varchar(255) NOT NULL,
  `img_path` varchar(255) NOT NULL,
  `img_type` varchar(255) NOT NULL DEFAULT 'img_slider',
  `display_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`img_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `img_upload` DISABLE KEYS */;
/*!40000 ALTER TABLE `img_upload` ENABLE KEYS */;
DROP TABLE IF EXISTS `jobcodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobcodes` (
  `jobid` int NOT NULL AUTO_INCREMENT,
  `JobTitle` varchar(255) NOT NULL,
  `JobCode` varchar(255) NOT NULL,
  PRIMARY KEY (`jobid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `jobcodes` DISABLE KEYS */;
INSERT INTO `jobcodes` VALUES (1,'Accounts Management','DMS1'),(2,'Full Stack Developer','FSD1'),(3,'Microfinance Officer','MO1'),(4,'Project Manager','PM1'),(5,'Senior Software Engineer','SE1');
/*!40000 ALTER TABLE `jobcodes` ENABLE KEYS */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `job_id` int NOT NULL AUTO_INCREMENT,
  `job_title` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `job_description` text COLLATE utf8mb4_general_ci NOT NULL,
  `job_skillset` text COLLATE utf8mb4_general_ci NOT NULL,
  `job_experience` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `job_benefits` text COLLATE utf8mb4_general_ci NOT NULL,
  `job_location` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `salary_range` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `job_type` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `job_req` varchar(255) COLLATE utf8mb4_general_ci DEFAULT 'No job requirements specified',
  `PostDate` date NOT NULL DEFAULT (curdate()),
  `deadline` date DEFAULT NULL,
  `job_dept` varchar(255) COLLATE utf8mb4_general_ci DEFAULT 'Manager',
  `job_code` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`job_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
INSERT INTO `jobs` VALUES (4,'oi kireee','Plan and execute online marketing. Make strategies to boost brand visibility. Boost social media engagement','SEO, Google Ads, Social media marketing, Email marketing.','5 years','Remote work. Professional training. Flexible hours.','Chicago','BDT 50,000 - BDT 70,000','Full-time','Bachelor\'s degree in Marketing. 3+ years in SEO, SEM, and social media marketing. Proficiency in Google Analytics, email campaigns, and content creation. Strong analytical skills','2025-03-28','2025-07-09','Accounts Management','DMS1'),(5,'Full Stack Developer','Develop and maintain full-stack applications using modern technologies (Node.js, React, MongoDB).','Full-stack development, JavaScript frameworks, Version control (Git).','6 years','Remote work. Flexible hours. Paid leave. Stock options.','Remote','BDT 90,000 - BDT 120,000','Full-time','Bachelor\'s degree in Computer Science. Proficient in front-end (HTML, CSS, JavaScript) and back-end (Node.js, PHP, or Python). Experience with RESTful APIs and relational databases','2025-03-28','2025-10-12','Field and Operations','FSD1'),(6,'Microfinance Officer','Manage microfinance programs. Assess loan applications. Oversee repayments','Financial analysis, Loan management, Risk assessment, Customer service.','3 years','Health insurance. Performance bonus. Training programs.','Khulna','BDT 40,000 - BDT 60,000','Full-time','Bachelor\'s degree in Finance, Economics, or related field. Experience in microfinance or banking sector.','2025-03-28','2025-04-05','Micro Finance','MO1'),(7,'Project Manager','Plan, execute, and oversee NGO projects. Ensuring timely completion and resource allocation','Project management, Team leadership, Budgeting, Stakeholder communication.','4 years','Health insurance. Annual bonuses. Flexible work schedule.','Dhaka','BDT 60,000 - BDT 80,000','Full-time','Bachelor\'s degree in Business Administration, Project Management, or related field. PMP certification is a plus.','2025-03-28','2025-04-07','Project Management','PM1'),(14,'Senior Software Engineer','Develop and maintain backend services. Optimize database performance. Implement and secure RESTful APIs. Troubleshoot and debug backend issues','Java, Spring Boot, Hibernate, RESTful APIs, Microservices, SQL &amp; NoSQL Databases (MySQL, PostgreSQL, MongoDB)','5 years','Flexible hours. Remote work option. Health insurance. Professional development opportunities. Performance bonuses','Chuadanga','BDT 10,000 - BDT 200,000','','Bachelor\'s degree in Computer Science or a related field. 5+ years of experience in Java backend development. Proficiency in Java, Spring Boot, and Hibernate. Strong understanding of RESTful APIs, Microservices, and database management. Experience wi','2025-04-02','2025-04-09','Information Technology(IT)','SE1');
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
DROP TABLE IF EXISTS `notices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` enum('Recruitment','Event','Scholarship','General','Training') NOT NULL,
  `pdf_file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `notices` DISABLE KEYS */;
/*!40000 ALTER TABLE `notices` ENABLE KEYS */;
DROP TABLE IF EXISTS `pdsfiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdsfiles` (
  `pdf_id` int NOT NULL AUTO_INCREMENT,
  `pdf_title` varchar(255) NOT NULL,
  `pdf_path` varchar(255) NOT NULL,
  `upload_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`pdf_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `pdsfiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `pdsfiles` ENABLE KEYS */;
DROP TABLE IF EXISTS `regional_offices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `regional_offices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `region_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `designation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Regional Manager',
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_order` int NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=active 0=inactive',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_display_order` (`display_order`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `regional_offices` DISABLE KEYS */;
INSERT INTO `regional_offices` VALUES (1,'Chuadanga Region','Cinama Hall Para, Chuadanga','Regional Manager','01725-683174',1,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(2,'Dingadah Region','Dingadah Khejura, Chuadanga Sadar, Chuadanga','Regional Manager','01958-573119',2,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(3,'AsmanKhali Region','AsmanKhali Bazar, AsmanKhali, Alamdanga, Chuadanga','Regional Manager','01725-186276',3,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(4,'Alamdanga Region','Rail Station Para, Alamdanga, Chuadanga','Regional Manager','01958-573194',4,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(5,'Kushtia Region','Stadium Para, Kushtia Sadar, Kushtia','Regional Manager','01958-573194',5,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(6,'Jibonnagar Region','Jibonnagar Eidga Para, Jibonnagar, Chuadanga','Regional Manager','01725-683174',6,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(7,'Jhikorgasa Region','Jhikorgasa Pazila Mor, Jhikorgasa, Jessore','Regional Manager','01721-505833',7,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(8,'Chowgasha Region','Isapur Dewan Para, Chowgasha, Jessore','Regional Manager','01722-603003',8,1,'2026-08-04 14:58:28','2026-08-04 14:58:28'),(9,'Pangsha Region','Dotto Para, Pangsha, Rajbari','Regional Manager','01958-573119',9,1,'2026-08-04 14:58:28','2026-08-04 14:58:28');
/*!40000 ALTER TABLE `regional_offices` ENABLE KEYS */;
DROP TABLE IF EXISTS `sectors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sectors` (
  `sector_id` int NOT NULL AUTO_INCREMENT,
  `sector_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`sector_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `sectors` DISABLE KEYS */;
INSERT INTO `sectors` VALUES (1,'Information Technology(IT)'),(2,'Human Resource(HR)'),(3,'Accounts Management'),(4,'Field and Operations'),(5,'Micro Finance'),(6,'Project Management');
/*!40000 ALTER TABLE `sectors` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

