-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 03, 2025 at 10:54 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `atmabiswas`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `adminId` int(10) NOT NULL,
  `username` varchar(255) NOT NULL,
  `pswd` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`adminId`, `username`, `pswd`) VALUES
(1, 'ahsan@gmail.com', 'ahsanKhan67@');

-- --------------------------------------------------------

--
-- Table structure for table `branch`
--

CREATE TABLE `branch` (
  `branchId` int(255) NOT NULL,
  `branchName` varchar(255) NOT NULL,
  `branchLoc` varchar(255) NOT NULL,
  `division` varchar(255) NOT NULL,
  `dist` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch`
--

INSERT INTO `branch` (`branchId`, `branchName`, `branchLoc`, `division`, `dist`) VALUES
(1, 'Maheshpur Branch', 'Hamidpur Para, Maheshpur, Jhenaidah', 'Khulna', 'Jhenaidah'),
(2, 'Asmankhali Branch', 'C/O-Md. Ibrahim Munshi, Asmankhali Bazar, Alamdanga, Chuadanga', 'Khulna', 'Chuadanga'),
(3, 'Jibannagar Branch', 'C/O-Md. Kutubuddin Sarker, High School Para, Jibannagar, Chuadanga', 'Khulna', 'Chuadanga'),
(4, 'ARPARA BRANCH', 'Arpara Shalikha, Magura', 'Khulna', 'Magura'),
(5, 'Sorojganj Branch', 'C/O-Mst. Badrunnaher, Sorojganj Bazar, Chuadanga', 'Khulna', 'Chuadanga'),
(6, 'Navaran Branch', 'Uttar Buruzbagan Forest Para, Navaran Bazar, Sharsha, Jessore', 'Khulna', 'Jessore'),
(7, 'JHIKARGACHA BRANCH', 'Village: Raghurathagar, Khalasi Para, Post Office: Raghurathagar, Pouroshova/Upazila: Jhikargacha, District: Jessore', 'Khulna', 'Jessore'),
(8, 'Jhaudia Branch', 'C/O-Mr. Shawpan Chowdhury, Shahi Masjid Para, Jhaudia, Kushtia', 'Khulna', 'Kushtia'),
(9, 'Kalukhali Branch', 'Rotondia Kaliukhali, Rajbari', 'Khulna', 'Rajbari'),
(10, 'Kaliganj Branch', 'C/O-Md. Abdul Hamid (Rtd. ATO), Bihari Moar, Arpara, Kaliganj, Jhenaidah', 'Khulna', 'Jhenaidah'),
(11, 'Meherpur Branch', 'C/O-Md. Shad Ahmed (Beside of Kobi Nazrul Islam High School), Mollick Para, Meherpur', 'Khulna', 'Meherpur'),
(12, 'Poradaha Branch', 'C/O-Md. Mosaduzzaman, Harun Moar, Poradaha Bazar, Mirpur, Kushtia', 'Khulna', 'Kushtia'),
(13, 'Alamdanga Branch', 'C/O-Md. Sonjer Ali, Alamdanga Station Road, Alamdanga, Chuadanga', 'Khulna', 'Chuadanga'),
(14, 'Darsana Branch', 'C/O-Mst. Selina Begum, Darshana Bus Stand Para, Damurhuda, Chuadanga', 'Khulna', 'Chuadanga'),
(15, 'Sadar Branch-1', 'Behind of Head Office, Cinema Hall Para, Chuadanga', 'Khulna', 'Chuadanga'),
(16, 'Churamonkati Branch', 'Ghona Road, Churamonkati Bazar, Churamonkati, Jessore', 'Khulna', 'Jessore'),
(17, 'Hatboalia Branch', 'C/O-Md. Kauser Ahmad Bablu (Present UP Chairman), Mill Para, Hatboalia, Alamdanga, Chuadanga', 'Khulna', 'Chuadanga'),
(18, 'Andulbaria Branch', 'C/O-Md. Mofizur Rahaman, Andulbaria Mistri Para, Jiban Nagar, Chuadanga', 'Khulna', 'Chuadanga'),
(19, 'Vairoba Branch', 'C/O-Md. Ruhul Amin, Vairoba Dotola Jame Masjid, Vairoba Bazar, Maheshpur, Jhenaidah', 'Khulna', 'Jhenaidah'),
(20, 'Dingedah Branch', 'Previous UP Parishad, Dingedah Bazar, Chuadanga', 'Khulna', 'Chuadanga'),
(21, 'Harinakundu Branch', 'Village: Chithlia College Para, Union: Harinakundu, Upazila: Harinakundu, District: Jhenaidah', 'Khulna', 'Jhenaidah'),
(22, 'Bamundi Branch', 'C/O-Md. Abdur Rahim, Bamundi Bazar, Gangni, Meherpur', 'Khulna', 'Meherpur'),
(23, 'PANGSHA Branch', 'Pangsha Sub Registri Officer Pisone, Pangsha, Rajbari', 'Khulna', 'Rajbari'),
(24, 'CHHUTIPUR BRANCH', 'Md: Abu Talha Shilu, Village: Mohammadpur, Post Office: Ganganandapur, Union: Ganganandapur, Upazila: Jhikargacha, District: Jessore', 'Khulna', 'Jessore'),
(25, 'Patikabari Branch', 'C/O-Md. Mostafizur Rahaman, Patikabari Bazar Road, Kushtia', 'Khulna', 'Kushtia'),
(26, 'Alokdia Branch', 'Alokdia Bazar (Beside of Old Union Parashad), Chuadanga', 'Khulna', 'Chuadanga'),
(27, 'Kotchandpur Branch', 'C/O-Md. Nurul Islam, Aakh Center Moar, Gabtala Para, Kotchandpur, Jhenaidah', 'Khulna', 'Jhenaidah'),
(28, 'Karpashdanga Branch', 'C/O-Dr. Asabul Haque, Karpashdanga Bazar, Damurhuda, Chuadanga', 'Khulna', 'Chuadanga'),
(29, 'Amla Branch', 'Md. Abdur Razzak (Beside of Old Aakh Center), Amla Sadarpur, Amla, Mirpur, Kushtia', 'Khulna', 'Kushtia'),
(30, 'Khashkarra Branch', 'Khashkarra Bazar, Alamdanga, Chuadanga', 'Khulna', 'Chuadanga'),
(31, 'CHANCHRA', 'Jessore Sadar, Chanchra', 'Khulna', 'Jessore'),
(32, 'ATMA BISWAS ME', 'C/O-Husnara Ferdos (Behind of Head Office), Cinema Hall Para, Chuadanga', 'Khulna', 'Chuadanga'),
(33, 'ISHARDI Branch', 'Piarpur Upazila Road, Piarpur, Ishardi, Pabna', 'Rajshahi', 'Pabna');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `job_id` int(11) NOT NULL,
  `job_title` varchar(100) NOT NULL,
  `job_description` text NOT NULL,
  `job_skillset` text NOT NULL,
  `job_experience` varchar(20) NOT NULL,
  `job_benefits` text NOT NULL,
  `job_location` varchar(100) NOT NULL,
  `salary_range` varchar(50) NOT NULL,
  `job_type` varchar(20) NOT NULL,
  `job_req` varchar(255) DEFAULT 'No job requirements specified',
  `PostDate` date NOT NULL DEFAULT (CURRENT_DATE),
  `deadline` date DEFAULT NULL,
  `job_dept` varchar(255) DEFAULT 'Manager',
  `job_code` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`job_id`, `job_title`, `job_description`, `job_skillset`, `job_experience`, `job_benefits`, `job_location`, `salary_range`, `job_type`, `job_req`, `PostDate`, `deadline`, `job_dept`, `job_code`) VALUES
(4, 'oi kireee', 'Plan and execute online marketing. Make strategies to boost brand visibility. Boost social media engagement', 'SEO, Google Ads, Social media marketing, Email marketing.', '5 years', 'Remote work. Professional training. Flexible hours.', 'Chicago', 'BDT 50,000 - BDT 70,000', 'Full-time', 'Bachelor\'s degree in Marketing. 3+ years in SEO, SEM, and social media marketing. Proficiency in Google Analytics, email campaigns, and content creation. Strong analytical skills', '2025-03-28', '2025-07-09', 'Accounts Management', 'DMS1'),
(5, 'Full Stack Developer', 'Develop and maintain full-stack applications using modern technologies (Node.js, React, MongoDB).', 'Full-stack development, JavaScript frameworks, Version control (Git).', '6 years', 'Remote work. Flexible hours. Paid leave. Stock options.', 'Remote', 'BDT 90,000 - BDT 120,000', 'Full-time', 'Bachelor\'s degree in Computer Science. Proficient in front-end (HTML, CSS, JavaScript) and back-end (Node.js, PHP, or Python). Experience with RESTful APIs and relational databases', '2025-03-28', '2025-10-12', 'Field and Operations', 'FSD1'),
(6, 'Microfinance Officer', 'Manage microfinance programs. Assess loan applications. Oversee repayments', 'Financial analysis, Loan management, Risk assessment, Customer service.', '3 years', 'Health insurance. Performance bonus. Training programs.', 'Khulna', 'BDT 40,000 - BDT 60,000', 'Full-time', 'Bachelor\'s degree in Finance, Economics, or related field. Experience in microfinance or banking sector.', '2025-03-28', '2025-04-05', 'Micro Finance', 'MO1'),
(7, 'Project Manager', 'Plan, execute, and oversee NGO projects. Ensuring timely completion and resource allocation', 'Project management, Team leadership, Budgeting, Stakeholder communication.', '4 years', 'Health insurance. Annual bonuses. Flexible work schedule.', 'Dhaka', 'BDT 60,000 - BDT 80,000', 'Full-time', 'Bachelor\'s degree in Business Administration, Project Management, or related field. PMP certification is a plus.', '2025-03-28', '2025-04-07', 'Project Management', 'PM1'),
(14, 'Senior Software Engineer', 'Develop and maintain backend services. Optimize database performance. Implement and secure RESTful APIs. Troubleshoot and debug backend issues', 'Java, Spring Boot, Hibernate, RESTful APIs, Microservices, SQL &amp; NoSQL Databases (MySQL, PostgreSQL, MongoDB)', '5 years', 'Flexible hours. Remote work option. Health insurance. Professional development opportunities. Performance bonuses', 'Chuadanga', 'BDT 10,000 - BDT 200,000', '', 'Bachelor\'s degree in Computer Science or a related field. 5+ years of experience in Java backend development. Proficiency in Java, Spring Boot, and Hibernate. Strong understanding of RESTful APIs, Microservices, and database management. Experience wi', '2025-04-02', '2025-04-09', 'Information Technology(IT)', 'SE1');

-- --------------------------------------------------------

--
-- Table structure for table `sectors`
--

CREATE TABLE `sectors` (
  `sector_id` int(11) NOT NULL,
  `sector_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sectors`
--

INSERT INTO `sectors` (`sector_id`, `sector_name`) VALUES
(1, 'Information Technology(IT)'),
(2, 'Human Resource(HR)'),
(3, 'Accounts Management'),
(4, 'Field and Operations'),
(5, 'Micro Finance'),
(6, 'Project Management');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`adminId`);

--
-- Indexes for table `branch`
--
ALTER TABLE `branch`
  ADD PRIMARY KEY (`branchId`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`job_id`);

--
-- Indexes for table `sectors`
--
ALTER TABLE `sectors`
  ADD PRIMARY KEY (`sector_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `adminId` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `branch`
--
ALTER TABLE `branch`
  MODIFY `branchId` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `sectors`
--
ALTER TABLE `sectors`
  MODIFY `sector_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
