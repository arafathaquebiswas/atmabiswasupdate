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


SELECT * FROM branch WHERE division = :division ORDER BY dist ASC;


INSERT INTO blogs (blog_title, blog_author, blog_content)
VALUES
('The Rise of AI in Education', 'Alice Johnson', 'Artificial Intelligence is transforming the education sector by personalizing learning experiences.'),
('Top 10 Web Development Trends in 2025', 'Brian Smith', 'From serverless architecture to AI-driven design, explore what’s shaping the web development world.'),
('Mental Health for Entrepreneurs', 'Chloe Adams', 'Starting a business is stressful. Here are some tips to maintain your mental well-being.'),
('Sustainable Fashion Practices', 'Dana Lee', 'Learn how sustainable fashion can reduce your environmental footprint and support ethical production.'),
('The Future of Remote Work', 'Ethan Parker', 'Remote work is here to stay. Discover trends and tools to make it work for your team.');


ALTER TABLE blogs CHANGE published_date upload_date DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;


ALTER TABLE blogs ADD COLUMN cover_img VARCHAR(255);


ALTER TABLE blogs 
MODIFY COLUMN blog_author VARCHAR(100) NOT NULL DEFAULT 'ATMABISWAS'; 


ALTER TABLE img_upload 
ADD COLUMN img_type VARCHAR(255) NOT NULL DEFAULT 'img_slider'; 

ALTER TABLE img_upload MODIFY COLUMN img_path VARCHAR(255) NOT NULL;



CREATE TABLE cv_applications(
    applicationId INT AUTO_INCREMENT PRIMARY KEY,
    jobId INT NOT NUll,
    fileDir VARCHAR(255) NOT NULL,
    appliedAt DATETIME DEFAULT CURRENT_TIMESTAMP
)

ALTER TABLE cv_applications ADD COLUMN fullname VARCHAR(255) NOT NULL;

ALTER TABLE cv_applications ADD COLUMN email VARCHAR(255) NOT NULL;

ALTER TABLE cv_applications ADD COLUMN phone_no VARCHAR(255) NOT NULL;


CREATE TABLE jobCodes (
    jobid INT(10) AUTO_INCREMENT PRIMARY KEY,
    JobTitle VARCHAR(255) NOT NULL,
    JobCode VARCHAR(255) NOT NULL
);

INSERT INTO jobCodes (jobTitle, jobCode)
VALUES
('Accounts Management', 'DMS1'),
('Full Stack Developer', 'FSD1'),
('Hello Hi Bye Bye', 'MO1'),
('Project Manager', 'PM1'),
('Senior Software Engineer', 'SE1');

ALTER TABLE jobs ADD COLUMN vacancy INT(100) NOT NULL;

ALTER TABLE cv_applications ADD COLUMN job_title VARCHAR(255) NOT NULL;

ALTER TABLE jobs MODIFY COLUMN vacancy INT NOT NULL DEFAULT 1;


ALTER TABLE jobs 
MODIFY COLUMN job_description VARCHAR(500) DEFAULT 'Will be disclosed in the Interview - (ATMABISWAS)';


ALTER TABLE jobs 
MODIFY COLUMN job_skillset VARCHAR(500) DEFAULT 'Will be disclosed in the Interview - (ATMABISWAS)';

ALTER TABLE jobs 
MODIFY COLUMN job_benefits VARCHAR(500) DEFAULT 'Will be disclosed in the Interview - (ATMABISWAS)';

ALTER TABLE jobs 
MODIFY COLUMN job_req VARCHAR(500) DEFAULT 'Will be disclosed in the Interview - (ATMABISWAS)';



ALTER TABLE jobcodes DROP COLUMN JobCode;


ALTER TABLE jobs DROP COLUMN job_code;


ALTER TABLE jobcodes 
ADD COLUMN JobCode VARCHAR(10) DEFAULT 'ABN1';


ALTER TABLE jobs 
ADD COLUMN job_code VARCHAR(10) DEFAULT 'ABN1';


ALTER TABLE jobs MODIFY COLUMN vacancy int(100) DEFAULT 1;



ALTER TABLE admins CHANGE username email VARCHAR(255);

ALTER TABLE admins ADD COLUMN fullname VARCHAR(255);
    


ALTER TABLE blogs ADD COLUMN source_link VARCHAR(255) DEFAULT "https://www.youtube.com/@ATMABISWAS01";


ALTER TABLE blogs ADD COLUMN image_title VARCHAR(255);

ALTER TABLE blogs MODIFY COLUMN image_title VARCHAR(255) NOT NULL DEFAULT 'Cover_image_title';
