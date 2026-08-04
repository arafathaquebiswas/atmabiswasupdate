show tables;


CREATE TABLE branch(
    branchId int(255) AUTO_INCREMENT PRIMARY KEY NOT NULL,
    branchName VARCHAR(255) NOT NULL,
    branchLoc VARCHAR(255) NOT NULL,
    division VARCHAR(255) NOT NULL,
    dist VARCHAR(255) NOT NULL
);

CREATE TABLE admins(
    adminId INT(10) PRIMARY KEY AUTO_INCREMENT NOT NULL,
    username VARCHAR(255) NOT NULL,
    pswd VARCHAR(255) NOT NULL
);


describe branch;


INSERT INTO branch (branchName, branchLoc, division, dist) VALUES
('Maheshpur Branch', 'Maheshpur', 'Khulna', 'Jhenaidah'),
('Asmankhali Branch', 'Alamdanga', 'Khulna', 'Chuadanga'),
('Jibannagar Branch', 'Jiban Nagar', 'Khulna', 'Chuadanga'),
('ARPARA BRANCH', 'Shalikha', 'Khulna', 'Magura'),
('Sorojganj Branch', 'Chuadanga Sadar', 'Khulna', 'Chuadanga'),
('Navaran Branch', 'Sharsha', 'Khulna', 'Jessore'),
('JHIKARGACHA BRANCH', 'Jhikargachha', 'Khulna', 'Jessore'),
('Jhaudia Branch', 'Kushtia Sadar', 'Khulna', 'Kushtia'),
('Kalukhali Branch', 'Kalukhali', 'Khulna', 'Rajbari'),
('Kaliganj Branch', 'Kaliganj', 'Khulna', 'Jhenaidah'),
('Meherpur Branch', 'Meherpur Sadar', 'Khulna', 'Meherpur'),
('Poradaha Branch', 'Mirpur', 'Khulna', 'Kushtia'),
('Alamdanga Branch', 'Alamdanga', 'Khulna', 'Chuadanga'),
('Darsana Branch', 'Damurhuda', 'Khulna', 'Chuadanga'),
('Sadar Branch-1', 'Chuadanga Sadar', 'Khulna', 'Chuadanga'),
('Churamonkati Branch', 'Jessore Sadar', 'Khulna', 'Jessore'),
('Hatboalia Branch', 'Alamdanga', 'Khulna', 'Chuadanga'),
('Andulbaria Branch', 'Jiban Nagar', 'Khulna', 'Chuadanga'),
('Vairoba Branch', 'Maheshpur', 'Khulna', 'Jhenaidah'),
('Dingedah Branch', 'Chuadanga Sadar', 'Khulna', 'Chuadanga'),
('Harinakundu Branch', 'Harinakunda', 'Khulna', 'Jhenaidah'),
('Bamundi Branch', 'Gangni', 'Khulna', 'Meherpur'),
('PANGSHA Branch', 'Pangsha', 'Khulna', 'Rajbari'),
('CHHUTIPUR BRANCH', 'Jhikargachha', 'Khulna', 'Jessore'),
('Patikabari Branch', 'Kushtia Sadar', 'Khulna', 'Kushtia'),
('Alokdia Branch', 'Chuadanga Sadar', 'Khulna', 'Chuadanga'),
('Kotchandpur Branch', 'Kotchandpur', 'Khulna', 'Jhenaidah'),
('Karpashdanga Branch', 'Damurhuda', 'Khulna', 'Chuadanga'),
('Amla Branch', 'Mirpur', 'Khulna', 'Kushtia'),
('Khashkarra Branch', 'Alamdanga', 'Khulna', 'Chuadanga'),
('CHANCHRA', 'Jessore Sadar', 'Khulna', 'Jessore'),
('ATMA BISWAS ME', 'Chuadanga Sadar', 'Khulna', 'Chuadanga'),
('ISHARDI Branch', 'Ishwardi', 'Rajshahi', 'Pabna');


UPDATE branch
SET branchLoc = CASE 
    WHEN branchId = 1 THEN 'Hamidpur Para, Maheshpur, Jhenaidah'
    WHEN branchId = 2 THEN 'C/O-Md. Ibrahim Munshi, Asmankhali Bazar, Alamdanga, Chuadanga'
    WHEN branchId = 3 THEN 'C/O-Md. Kutubuddin Sarker, High School Para, Jibannagar, Chuadanga'
    WHEN branchId = 4 THEN 'Arpara Shalikha, Magura'
    WHEN branchId = 5 THEN 'C/O-Mst. Badrunnaher, Sorojganj Bazar, Chuadanga'
    WHEN branchId = 6 THEN 'Uttar Buruzbagan Forest Para, Navaran Bazar, Sharsha, Jessore'
    WHEN branchId = 7 THEN 'Village: Raghurathagar, Khalasi Para, Post Office: Raghurathagar, Pouroshova/Upazila: Jhikargacha, District: Jessore'
    WHEN branchId = 8 THEN 'C/O-Mr. Shawpan Chowdhury, Shahi Masjid Para, Jhaudia, Kushtia'
    WHEN branchId = 9 THEN 'Rotondia Kaliukhali, Rajbari'
    WHEN branchId = 10 THEN 'C/O-Md. Abdul Hamid (Rtd. ATO), Bihari Moar, Arpara, Kaliganj, Jhenaidah'
    WHEN branchId = 11 THEN 'C/O-Md. Shad Ahmed (Beside of Kobi Nazrul Islam High School), Mollick Para, Meherpur'
    WHEN branchId = 12 THEN 'C/O-Md. Mosaduzzaman, Harun Moar, Poradaha Bazar, Mirpur, Kushtia'
    WHEN branchId = 13 THEN 'C/O-Md. Sonjer Ali, Alamdanga Station Road, Alamdanga, Chuadanga'
    WHEN branchId = 14 THEN 'C/O-Mst. Selina Begum, Darshana Bus Stand Para, Damurhuda, Chuadanga'
    WHEN branchId = 15 THEN 'Behind of Head Office, Cinema Hall Para, Chuadanga'
    WHEN branchId = 16 THEN 'Ghona Road, Churamonkati Bazar, Churamonkati, Jessore'
    WHEN branchId = 17 THEN 'C/O-Md. Kauser Ahmad Bablu (Present UP Chairman), Mill Para, Hatboalia, Alamdanga, Chuadanga'
    WHEN branchId = 18 THEN 'C/O-Md. Mofizur Rahaman, Andulbaria Mistri Para, Jiban Nagar, Chuadanga'
    WHEN branchId = 19 THEN 'C/O-Md. Ruhul Amin, Vairoba Dotola Jame Masjid, Vairoba Bazar, Maheshpur, Jhenaidah'
    WHEN branchId = 20 THEN 'Previous UP Parishad, Dingedah Bazar, Chuadanga'
    WHEN branchId = 21 THEN 'Village: Chithlia College Para, Union: Harinakundu, Upazila: Harinakundu, District: Jhenaidah'
    WHEN branchId = 22 THEN 'C/O-Md. Abdur Rahim, Bamundi Bazar, Gangni, Meherpur'
    WHEN branchId = 23 THEN 'Pangsha Sub Registri Officer Pisone, Pangsha, Rajbari'
    WHEN branchId = 24 THEN 'Md: Abu Talha Shilu, Village: Mohammadpur, Post Office: Ganganandapur, Union: Ganganandapur, Upazila: Jhikargacha, District: Jessore'
    WHEN branchId = 25 THEN 'C/O-Md. Mostafizur Rahaman, Patikabari Bazar Road, Kushtia'
    WHEN branchId = 26 THEN 'Alokdia Bazar (Beside of Old Union Parashad), Chuadanga'
    WHEN branchId = 27 THEN 'C/O-Md. Nurul Islam, Aakh Center Moar, Gabtala Para, Kotchandpur, Jhenaidah'
    WHEN branchId = 28 THEN 'C/O-Dr. Asabul Haque, Karpashdanga Bazar, Damurhuda, Chuadanga'
    WHEN branchId = 29 THEN 'Md. Abdur Razzak (Beside of Old Aakh Center), Amla Sadarpur, Amla, Mirpur, Kushtia'
    WHEN branchId = 30 THEN 'Khashkarra Bazar, Alamdanga, Chuadanga'
    WHEN branchId = 31 THEN 'Jessore Sadar, Chanchra'
    WHEN branchId = 32 THEN 'C/O-Husnara Ferdos (Behind of Head Office), Cinema Hall Para, Chuadanga'
    WHEN branchId = 33 THEN 'Piarpur Upazila Road, Piarpur, Ishardi, Pabna'
    ELSE branchLoc -- Keep original value if no match
END;



SELECT DISTINCT dist FROM branch WHERE division = "khulna";

SELECT * FROM admins;



SELECT * FROM branch WHERE division = :division ORDER BY dist ASC;


DROP TABLE jobs;



CREATE TABLE jobs (
    job_id INT AUTO_INCREMENT PRIMARY KEY,  -- Unique ID for each job
    job_code VARCHAR(3) NOT NULL UNIQUE,    -- 3-character job code
    job_title VARCHAR(100) NOT NULL,        -- Job title
    job_description TEXT NOT NULL,          -- Job description
    job_skillset TEXT NOT NULL,             -- Required skills for the job
    job_experience VARCHAR(20) NOT NULL,    -- Required experience (e.g., '5 years')
    job_benefits TEXT NOT NULL,             -- Benefits offered
    company_name VARCHAR(100) NOT NULL,     -- Name of the company
    job_location VARCHAR(100) NOT NULL,     -- Location of the job
    salary_range VARCHAR(50) NOT NULL,      -- Salary range (e.g., '$50,000 - $70,000')
    job_type VARCHAR(20) NOT NULL           -- Job type (e.g., Full-time, Remote)
);

INSERT INTO jobs (job_code, job_title, job_description, job_skillset, job_experience, job_benefits, company_name, job_location, salary_range, job_type)
VALUES 
('SE1', 
 'Software Engineer', 
 'Develop and maintain web applications, troubleshoot issues, and implement new features.',
 'Proficiency in PHP, JavaScript, MySQL, REST APIs, and frontend frameworks (React or Angular).',
 '5 years experience in software development.', 
 'Health insurance, 401(k) matching, Paid leave, Work-from-home flexibility.', 
 'Atmabiswas', 
 'New York', 
 '$80,000 - $100,000', 
 'Full-time'),

('DS2', 
 'Data Scientist', 
 'Analyze large datasets, build predictive models, and help improve decision-making processes.', 
 'Expertise in Python, R, machine learning, and statistical analysis.', 
 '5 years experience in data science.', 
 'Flexible hours, Remote work option, Stock options, Performance bonuses.', 
 'Atmabiswas', 
 'Remote', 
 '$100,000 - $130,000', 
 'Full-time'),

('GD3', 
 'Graphic Designer', 
 'Design creative visuals for marketing campaigns, websites, and social media.', 
 'Proficiency in Adobe Photoshop, Illustrator, and InDesign.', 
 '5 years experience in graphic design.', 
 'Health insurance, Paid leave, Professional development opportunities.', 
 'Atmabiswas', 
 'Los Angeles', 
 '$45,000 - $60,000', 
 'Full-time'),

('DM4', 
 'Digital Marketing Specialist', 
 'Plan and execute online marketing strategies to boost brand visibility and engagement.', 
 'Experience with SEO, Google Ads, social media marketing, and email marketing.', 
 '5 years experience in digital marketing.', 
 'Remote work, Professional training, Flexible hours.', 
 'Atmabiswas', 
 'Chicago', 
 '$50,000 - $70,000', 
 'Full-time'),

('FS5', 
 'Full Stack Developer', 
 'Develop and maintain full-stack applications using modern technologies (Node.js, React, MongoDB).', 
 'Proficiency in full-stack development, JavaScript frameworks, and version control (Git).', 
 '5 years experience in full-stack development.', 
 'Remote work, Flexible hours, Paid leave, Stock options.', 
 'Atmabiswas', 
 'Remote', 
 '$90,000 - $120,000', 
 'Full-time');


ALTER TABLE jobs ADD job_req VARCHAR(255);


    ("Bachelor's or Master's degree in Computer Science, Software Engineering, or a related field. 5+ years of professional software development experience. Proficiency in programming languages such as Java, Python, C++, or JavaScript. Strong understanding of software architecture, design patterns, and data structures. Experience with Agile methodologies, CI/CD pipelines, and version control (e.g., Git)."),

    ("Bachelor's or Master's degree in Data Science, Statistics, Computer Science, or related field. Strong proficiency in Python, R, SQL, and data visualization tools (e.g., Tableau, Power BI). Experience in machine learning, deep learning, and statistical modeling. Solid understanding of data wrangling, preprocessing, and feature engineering. Excellent problem-solving skills and the ability to communicate complex data insights clearly."),

    ("Bachelor's degree in Graphic Design, Visual Arts, or a related field. Proficiency in design software such as Adobe Photoshop, Illustrator, and InDesign. Strong portfolio showcasing creativity, visual design, and branding projects. Knowledge of typography, color theory, and layout design principles. Ability to meet deadlines, collaborate with teams, and adapt to client feedback."),

    ("Bachelor's degree in Marketing, Communications, or Business Administration. 3+ years of experience in digital marketing, SEO, SEM, and social media marketing. Proficiency in Google Analytics, Google Ads, and marketing automation tools. Strong content creation, email marketing, and campaign management skills. Excellent analytical skills to track and optimize digital marketing performance."),

    ("Bachelor's degree in Computer Science, Information Technology, or a related field. Proficiency in front-end technologies (HTML, CSS, JavaScript, React) and back-end (Node.js, PHP, or Python). Experience working with relational databases (e.g., MySQL, PostgreSQL) and NoSQL databases. Strong understanding of RESTful APIs, authentication, and deployment on cloud platforms (e.g., AWS, Azure). Knowledge of version control systems (e.g., Git) and Agile development practices.");


ALTER TABLE jobs MODIFY job_req VARCHAR(255) DEFAULT 'No job requirements specified';

ALTER TABLE jobs
ADD PostDate DATE NOT NULL DEFAULT (CURRENT_DATE),
ADD deadline DATE;


SELECT * FROM jobs WHERE job_code="SE1";


ALTER TABLE jobs ADD job_dept VARCHAR(255) DEFAULT 'Manager';



INSERT INTO `jobs` (`job_id`, `job_code`, `job_title`, `job_description`, `job_skillset`, `job_experience`, `job_benefits`, `company_name`, `job_location`, `salary_range`, `job_type`, `job_req`, `PostDate`, `deadline`, `job_dept`) VALUES
(6, 'MF1', 'Microfinance Officer', 'Manage microfinance programs, assess loan applications, and oversee repayments.', 'Financial analysis, Loan management, Risk assessment, Customer service.', '3 years', 'Health insurance, Performance bonus, Training programs.', 'ATMABISWAS', 'Khulna', 'BDT 40,000 - BDT 60,000', 'Full-time', 'Bachelor\'s degree in Finance, Economics, or related field. Experience in microfinance or banking sector.', '2025-03-28', '2025-04-05', 'Micro Finance Department'),

(7, 'PM1', 'Project Manager', 'Plan, execute, and oversee NGO projects, ensuring timely completion and resource allocation.', 'Project management, Team leadership, Budgeting, Stakeholder communication.', '4 years', 'Health insurance, Annual bonuses, Flexible work schedule.', 'ATMABISWAS', 'Dhaka', 'BDT 60,000 - BDT 80,000', 'Full-time', 'Bachelor\'s degree in Business Administration, Project Management, or related field. PMP certification is a plus.', '2025-03-28', '2025-04-07', 'Project Management Department');


SELECT job_dept, COUNT(*) AS job_count 
FROM jobs 
GROUP BY job_dept;



SELECT * FROM jobs WHERE job_dept = "field and operations";


ALTER TABLE jobs DROP company_name;


SELECT * FROM jobs;



CREATE TABLE sectors(
    sector_id INT PRIMARY KEY AUTO_INCREMENT,
    sector_name VARCHAR(255) NOT NULL 

);


INSERT INTO sectors (sector_id,sector_name) VALUES (1,"Information Technology(IT)"),(2,"Human Resource(HR)"),(3,"Accounts Management"),(4,"Field and Operations"),(5,"Micro Finance"),(6,"Project Management");



ALTER TABLE jobs DROP job_code;


ALTER TABLE jobs ADD job_code VARCHAR(255);


CREATE TABLE img_upload (
    img_id INT AUTO_INCREMENT PRIMARY KEY,
    img_title VARCHAR(255) NOT NULL,
    img_description TEXT,
    img_path INT NOT NULL,
    uploaded_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



CREATE TABLE pdsFiles(
    pdf_id INT AUTO_INCREMENT PRIMARY KEY,
    pdf_title VARCHAR(255) NOT NULL,
    pdf_path VARCHAR(255) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE blogs(
    blog_id int(11) AUTO_INCREMENT PRIMARY KEY,
    blog_title VARCHAR(255) NOT NULL,
    blog_content TEXT NOT NULL,
    blog_author VARCHAR(255) DEFAULT "ATMABISWAS",
    cover_img VARCHAR(255),
    upload_date TIMESTAMP  DEFAULT CURRENT_TIMESTAMP
);



ALTER TABLE blogs
ADD COLUMN summary TEXT,
ADD COLUMN year YEAR DEFAULT (YEAR(CURRENT_DATE));
