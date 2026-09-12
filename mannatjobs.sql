CREATE DATABASE IF NOT EXISTS mannatjobs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mannatjobs;

DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS saved_jobs;
DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS jobs;
DROP TABLE IF EXISTS employer_profiles;
DROP TABLE IF EXISTS jobseeker_profiles;
DROP TABLE IF EXISTS job_categories;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('jobseeker', 'employer', 'admin') NOT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    status ENUM('active', 'blocked', 'pending') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_users_role (role),
    INDEX idx_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE jobseeker_profiles (
    user_id INT UNSIGNED NOT NULL PRIMARY KEY,
    resume_path VARCHAR(255) DEFAULT NULL,
    resume_data MEDIUMBLOB DEFAULT NULL,
    resume_mime VARCHAR(100) DEFAULT NULL,
    skills TEXT DEFAULT NULL,
    experience TEXT DEFAULT NULL,
    education TEXT DEFAULT NULL,
    degree VARCHAR(150) DEFAULT NULL,
    university VARCHAR(180) DEFAULT NULL,
    stream VARCHAR(150) DEFAULT NULL,
    profession VARCHAR(150) DEFAULT NULL,
    projects TEXT DEFAULT NULL,
    company VARCHAR(180) DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    state VARCHAR(120) DEFAULT NULL,
    city VARCHAR(120) DEFAULT NULL,
    profile_photo VARCHAR(255) DEFAULT NULL,
    profile_photo_data MEDIUMBLOB DEFAULT NULL,
    profile_photo_mime VARCHAR(100) DEFAULT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_jobseeker_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE employer_profiles (
    user_id INT UNSIGNED NOT NULL PRIMARY KEY,
    company_name VARCHAR(180) DEFAULT NULL,
    company_logo VARCHAR(255) DEFAULT NULL,
    company_website VARCHAR(255) DEFAULT NULL,
    company_description TEXT DEFAULT NULL,
    approved TINYINT(1) NOT NULL DEFAULT 0,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_employer_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE job_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE jobs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employer_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT NOT NULL,
    location VARCHAR(120) NOT NULL,
    job_type ENUM('Full-time', 'Part-time', 'Remote', 'Internship') NOT NULL,
    salary_min DECIMAL(12,2) DEFAULT NULL,
    salary_max DECIMAL(12,2) DEFAULT NULL,
    experience_level VARCHAR(80) DEFAULT NULL,
    deadline DATE DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected', 'closed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_jobs_employer FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_jobs_category FOREIGN KEY (category_id) REFERENCES job_categories(id) ON DELETE RESTRICT,
    INDEX idx_jobs_employer (employer_id),
    INDEX idx_jobs_status (status),
    INDEX idx_jobs_location (location),
    INDEX idx_jobs_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE applications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id INT UNSIGNED NOT NULL,
    jobseeker_id INT UNSIGNED NOT NULL,
    resume_path VARCHAR(255) DEFAULT NULL,
    resume_data MEDIUMBLOB DEFAULT NULL,
    resume_mime VARCHAR(100) DEFAULT NULL,
    cover_letter TEXT DEFAULT NULL,
    status ENUM('applied', 'shortlisted', 'rejected', 'hired') NOT NULL DEFAULT 'applied',
    applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_applications_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    CONSTRAINT fk_applications_jobseeker FOREIGN KEY (jobseeker_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_application_once (job_id, jobseeker_id),
    INDEX idx_applications_job (job_id),
    INDEX idx_applications_jobseeker (jobseeker_id),
    INDEX idx_applications_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE saved_jobs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    jobseeker_id INT UNSIGNED NOT NULL,
    job_id INT UNSIGNED NOT NULL,
    saved_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_saved_jobseeker FOREIGN KEY (jobseeker_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_saved_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    INDEX idx_saved_jobs_job (job_id),
    UNIQUE KEY uq_saved_job (jobseeker_id, job_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE contact_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(180) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_contact_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO job_categories (name) VALUES
('IT'),
('Marketing'),
('Sales'),
('Design'),
('Finance'),
('Customer Support');

INSERT INTO users (name, email, password, role, phone, status, created_at) VALUES
('System Administrator', 'admin@mannatjobs.com', '$2y$12$s2wVFCNcMkwDjzKSOyAT4uJ9BEuz4anIkv1LlEi1YXqsqzUuWo3p.', 'admin', '+923001112233', 'active', NOW()),
('Aisha Khan', 'employer@mannatjobs.com', '$2y$12$R1/e46I1P/HcTSivfFlwx.DnY48Aw4UZCtuJFz/sjySa2FuhyvNZa', 'employer', '+923001234567', 'active', NOW()),
('Zain Ali', 'jobseeker@mannatjobs.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923005556677', 'active', NOW());

INSERT INTO employer_profiles (user_id, company_name, company_logo, company_website, company_description, approved, updated_at) VALUES
(2, 'Mannat Tech Solutions', 'logo-default.png', 'https://mannattech.example', 'A modern technology company helping businesses grow with digital transformation and product innovation.', 1, NOW());

INSERT INTO jobseeker_profiles (user_id, resume_path, skills, experience, education, address, profile_photo, updated_at) VALUES
(3, 'resume-demo.pdf', 'PHP, MySQL, HTML, CSS, JavaScript, Team Collaboration', '2 years in web development and customer-facing technical support', 'BSc Computer Science', 'Lahore, Pakistan', 'avatar-default.jpg', NOW());

INSERT INTO jobs (
    employer_id,
    category_id,
    title,
    description,
    requirements,
    location,
    job_type,
    salary_min,
    salary_max,
    experience_level,
    deadline,
    status,
    created_at
) VALUES
(
    2,
    1,
    'Senior PHP Developer',
    'We are looking for a senior PHP developer to build secure and scalable web applications for client projects. The ideal candidate should have strong backend experience and a passion for clean code.',
    'Strong experience with PHP, MySQL, JavaScript, HTML, CSS. Ability to write clean, maintainable code and work in a team environment. Must understand basic security principles and REST-style API structures.',
    'Lahore',
    'Full-time',
    90000,
    140000,
    '3+ years',
    DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY),
    'approved',
    NOW()
),
(
    2,
    2,
    'Marketing Executive',
    'This role focuses on digital campaigns, brand visibility, lead generation, and social media performance. The candidate will work closely with the sales and content teams to improve campaign ROI.',
    'Bachelor\'s degree in marketing, communications, or business. Experience in social media marketing, copywriting, and campaign performance analysis. Strong communication and analytical skills.',
    'Islamabad',
    'Full-time',
    60000,
    90000,
    '1-3 years',
    DATE_ADD(CURRENT_DATE, INTERVAL 25 DAY),
    'approved',
    NOW()
),
(
    2,
    4,
    'UI/UX Designer',
    'We are hiring a UI/UX designer to create beautiful, user-friendly interfaces for our web and mobile products. The candidate should balance design aesthetics with functionality.',
    'Portfolio of modern UI/UX work, expertise in Figma, Adobe XD, and design systems. Strong visual communication and problem-solving skills. Ability to translate requirements into polished interfaces.',
    'Remote',
    'Remote',
    70000,
    110000,
    '1-2 years',
    DATE_ADD(CURRENT_DATE, INTERVAL 40 DAY),
    'approved',
    NOW()
),
(
    2,
    6,
    'Customer Support Representative',
    'The customer support representative will handle inquiries, process support tickets, and ensure customer satisfaction through effective communication and issue resolution.',
    'Excellent communication skills, problem-solving ability, and patience. Experience in customer support, helpdesk, or service roles preferred. Must be comfortable with chat and email support.',
    'Karachi',
    'Part-time',
    40000,
    65000,
    '0-1 year',
    DATE_ADD(CURRENT_DATE, INTERVAL 20 DAY),
    'approved',
    NOW()
);

INSERT INTO applications (job_id, jobseeker_id, resume_path, cover_letter, status, applied_at) VALUES
(1, 3, 'resume-demo.pdf', 'I am very interested in this PHP developer role and would love to contribute to your product team.', 'applied', NOW());

INSERT INTO users (name, email, password, role, phone, status, created_at) VALUES
('Bilal Ahmed', 'bilal.hr@techvista.example', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'employer', '+923101000001', 'active', DATE_SUB(NOW(), INTERVAL 32 DAY)),
('Sara Malik', 'sara.people@brightworks.example', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'employer', '+923101000002', 'active', DATE_SUB(NOW(), INTERVAL 30 DAY)),
('Hamza Raza', 'hamza.jobs@nextwave.example', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'employer', '+923101000003', 'active', DATE_SUB(NOW(), INTERVAL 28 DAY)),
('Mariam Shah', 'mariam.talent@pixelcraft.example', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'employer', '+923101000004', 'active', DATE_SUB(NOW(), INTERVAL 26 DAY)),
('Usman Tariq', 'usman.hiring@finpeak.example', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'employer', '+923101000005', 'active', DATE_SUB(NOW(), INTERVAL 24 DAY)),
('Hina Yousaf', 'hina.careers@caregrid.example', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'employer', '+923101000006', 'pending', DATE_SUB(NOW(), INTERVAL 20 DAY)),
('Omar Farooq', 'omar.team@cloudbridge.example', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'employer', '+923101000007', 'active', DATE_SUB(NOW(), INTERVAL 18 DAY)),
('Nadia Iqbal', 'nadia.hr@marketlane.example', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'employer', '+923101000008', 'active', DATE_SUB(NOW(), INTERVAL 15 DAY)),
('Ahmed Hassan', 'ahmed.khan@example.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923201000001', 'active', DATE_SUB(NOW(), INTERVAL 27 DAY)),
('Noor Fatima', 'noor.fatima@example.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923201000002', 'active', DATE_SUB(NOW(), INTERVAL 26 DAY)),
('Usama Khalid', 'usama.khalid@example.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923201000003', 'active', DATE_SUB(NOW(), INTERVAL 25 DAY)),
('Laiba Aslam', 'laiba.aslam@example.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923201000004', 'active', DATE_SUB(NOW(), INTERVAL 24 DAY)),
('Daniyal Noor', 'daniyal.noor@example.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923201000005', 'active', DATE_SUB(NOW(), INTERVAL 23 DAY)),
('Anaya Siddiqui', 'anaya.siddiqui@example.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923201000006', 'active', DATE_SUB(NOW(), INTERVAL 22 DAY)),
('Hassan Javed', 'hassan.javed@example.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923201000007', 'active', DATE_SUB(NOW(), INTERVAL 21 DAY)),
('Mehwish Rauf', 'mehwish.rauf@example.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923201000008', 'active', DATE_SUB(NOW(), INTERVAL 20 DAY)),
('Areeba Khalil', 'areeba.khalil@example.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923201000009', 'active', DATE_SUB(NOW(), INTERVAL 19 DAY)),
('Talha Rehman', 'talha.rehman@example.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923201000010', 'active', DATE_SUB(NOW(), INTERVAL 18 DAY)),
('Maha Nadeem', 'maha.nadeem@example.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923201000011', 'active', DATE_SUB(NOW(), INTERVAL 17 DAY)),
('Rayan Saeed', 'rayan.saeed@example.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923201000012', 'active', DATE_SUB(NOW(), INTERVAL 16 DAY)),
('Eman Zahid', 'eman.zahid@example.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923201000013', 'active', DATE_SUB(NOW(), INTERVAL 15 DAY)),
('Saad Waseem', 'saad.waseem@example.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923201000014', 'active', DATE_SUB(NOW(), INTERVAL 14 DAY)),
('Ibrahim Qureshi', 'ibrahim.qureshi@example.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923201000015', 'active', DATE_SUB(NOW(), INTERVAL 13 DAY));

INSERT INTO employer_profiles (user_id, company_name, company_logo, company_website, company_description, approved, updated_at) VALUES
(4, 'TechVista Labs', 'logo-default.png', 'https://techvista.example', 'Software engineering and cloud consulting for growing companies.', 1, NOW()),
(5, 'BrightWorks HR', 'logo-default.png', 'https://brightworks.example', 'People-first recruitment and workplace technology services.', 1, NOW()),
(6, 'NextWave Commerce', 'logo-default.png', 'https://nextwave.example', 'Digital commerce products for ambitious regional brands.', 1, NOW()),
(7, 'PixelCraft Studio', 'logo-default.png', 'https://pixelcraft.example', 'Product design and creative technology studio.', 1, NOW()),
(8, 'FinPeak Analytics', 'logo-default.png', 'https://finpeak.example', 'Financial data and reporting solutions for modern teams.', 1, NOW()),
(9, 'CareGrid Services', 'logo-default.png', 'https://caregrid.example', 'Customer operations and support services for online businesses.', 0, NOW()),
(10, 'CloudBridge Systems', 'logo-default.png', 'https://cloudbridge.example', 'Reliable infrastructure and managed cloud services.', 1, NOW()),
(11, 'MarketLane Media', 'logo-default.png', 'https://marketlane.example', 'Performance marketing and content strategy agency.', 1, NOW());

INSERT INTO jobseeker_profiles (user_id, skills, experience, education, address, updated_at) VALUES
(12, 'PHP, Laravel, MySQL, REST APIs', '3 years building business web applications', 'BS Software Engineering', 'Lahore, Pakistan', NOW()),
(13, 'Content writing, SEO, social media', '2 years in content and digital marketing', 'BS Mass Communication', 'Islamabad, Pakistan', NOW()),
(14, 'JavaScript, React, TypeScript, CSS', '2 years as a frontend developer', 'BS Computer Science', 'Karachi, Pakistan', NOW()),
(15, 'Figma, wireframing, design systems', '1 year designing SaaS products', 'Diploma in Visual Design', 'Lahore, Pakistan', NOW()),
(16, 'Excel, bookkeeping, financial reporting', '4 years in accounts and reporting', 'BCom Finance', 'Rawalpindi, Pakistan', NOW()),
(17, 'Customer support, Zendesk, communication', '2 years supporting international customers', 'BA English', 'Faisalabad, Pakistan', NOW()),
(18, 'Python, SQL, data analysis, Power BI', '1 year as a junior data analyst', 'BS Data Science', 'Peshawar, Pakistan', NOW()),
(19, 'Sales, CRM, negotiation, lead generation', '3 years in B2B sales', 'BBA Marketing', 'Lahore, Pakistan', NOW()),
(20, 'WordPress, PHP, SEO, analytics', '2 years managing business websites', 'BS IT', 'Multan, Pakistan', NOW()),
(21, 'Recruitment, sourcing, interviewing', '2 years in talent acquisition', 'MBA HR', 'Islamabad, Pakistan', NOW()),
(22, 'Illustrator, Photoshop, branding', '3 years in visual communication', 'BDes Graphic Design', 'Karachi, Pakistan', NOW()),
(23, 'Node.js, Express, MongoDB, Docker', '2 years in backend development', 'BS Computer Science', 'Lahore, Pakistan', NOW()),
(24, 'Email marketing, Google Ads, reporting', '1 year running paid campaigns', 'BBA Marketing', 'Sialkot, Pakistan', NOW()),
(25, 'Manual testing, QA, test cases', '2 years in software quality assurance', 'BS Computer Science', 'Gujranwala, Pakistan', NOW()),
(26, 'Technical writing, documentation, research', '2 years writing product documentation', 'BS Information Technology', 'Quetta, Pakistan', NOW());

INSERT INTO jobs (employer_id, category_id, title, description, requirements, location, job_type, salary_min, salary_max, experience_level, deadline, status, created_at) VALUES
(4, 1, 'Backend Engineer', 'Build APIs and integrations for a growing SaaS platform used by regional businesses.', 'PHP or Node.js experience, SQL knowledge, Git, and familiarity with REST APIs.', 'Lahore', 'Full-time', 110000, 180000, '2-4 years', DATE_ADD(CURRENT_DATE, INTERVAL 35 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 12 DAY)),
(4, 1, 'Junior Frontend Developer', 'Work with designers and backend engineers to ship responsive product experiences.', 'HTML, CSS, JavaScript, basic React, and willingness to learn from code reviews.', 'Remote', 'Remote', 65000, 100000, '0-1 year', DATE_ADD(CURRENT_DATE, INTERVAL 28 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(4, 1, 'QA Automation Engineer', 'Improve release confidence by building reliable automated test coverage.', 'Testing fundamentals, JavaScript or Python, API testing, and attention to detail.', 'Lahore', 'Full-time', 90000, 145000, '1-3 years', DATE_ADD(CURRENT_DATE, INTERVAL 32 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 8 DAY)),
(5, 2, 'Content Strategist', 'Plan and deliver content that supports brand growth and qualified inbound leads.', 'SEO, editorial planning, strong English writing, and analytics experience.', 'Islamabad', 'Full-time', 75000, 120000, '2-4 years', DATE_ADD(CURRENT_DATE, INTERVAL 27 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 11 DAY)),
(5, 3, 'Recruitment Coordinator', 'Coordinate interviews and keep candidates and hiring teams moving smoothly.', 'Strong organization, communication, spreadsheets, and interest in recruitment operations.', 'Islamabad', 'Part-time', 45000, 70000, '0-2 years', DATE_ADD(CURRENT_DATE, INTERVAL 22 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 9 DAY)),
(6, 3, 'Business Development Associate', 'Identify new partnerships and help merchants grow their online sales.', 'Communication, lead generation, CRM experience, and confident presentation skills.', 'Karachi', 'Full-time', 70000, 115000, '1-3 years', DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(6, 1, 'E-commerce Operations Intern', 'Support catalog quality, order tracking, and marketplace operations.', 'Basic spreadsheets, learning mindset, and clear written communication.', 'Karachi', 'Internship', 25000, 40000, 'Internship', DATE_ADD(CURRENT_DATE, INTERVAL 20 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(7, 4, 'Product Designer', 'Shape user journeys and polished interfaces for web and mobile products.', 'Figma portfolio, UX research, prototyping, and strong visual communication.', 'Remote', 'Remote', 100000, 170000, '2-5 years', DATE_ADD(CURRENT_DATE, INTERVAL 38 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 13 DAY)),
(7, 4, 'Graphic Design Intern', 'Create social assets, presentation graphics, and visual explorations with the studio team.', 'Illustrator or Photoshop basics, visual curiosity, and a portfolio of work.', 'Lahore', 'Internship', 25000, 45000, 'Internship', DATE_ADD(CURRENT_DATE, INTERVAL 24 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(8, 5, 'Financial Reporting Analyst', 'Prepare dashboards and reports that help clients make better financial decisions.', 'Excel, financial analysis, attention to detail, and clear reporting skills.', 'Lahore', 'Full-time', 85000, 135000, '1-3 years', DATE_ADD(CURRENT_DATE, INTERVAL 33 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 14 DAY)),
(8, 1, 'Data Analyst', 'Explore operational data and communicate insights to product and leadership teams.', 'SQL, Excel, Power BI or Tableau, and basic statistical reasoning.', 'Remote', 'Remote', 90000, 150000, '1-3 years', DATE_ADD(CURRENT_DATE, INTERVAL 31 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(10, 1, 'Cloud Support Engineer', 'Help customers troubleshoot deployments and maintain dependable cloud environments.', 'Linux basics, networking, customer communication, and willingness to work shifts.', 'Islamabad', 'Full-time', 80000, 130000, '1-2 years', DATE_ADD(CURRENT_DATE, INTERVAL 29 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(10, 1, 'DevOps Intern', 'Learn infrastructure automation and assist with monitoring and deployment workflows.', 'Basic Linux, scripting, Git, and an interest in cloud technologies.', 'Remote', 'Internship', 30000, 50000, 'Internship', DATE_ADD(CURRENT_DATE, INTERVAL 26 DAY), 'pending', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(11, 2, 'Performance Marketing Specialist', 'Manage paid campaigns and turn channel data into practical growth experiments.', 'Google Ads, Meta Ads, campaign reporting, and strong analytical thinking.', 'Karachi', 'Full-time', 80000, 130000, '2-4 years', DATE_ADD(CURRENT_DATE, INTERVAL 36 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 16 DAY)),
(11, 2, 'Social Media Executive', 'Build engaging social calendars and coordinate content across multiple brands.', 'Copywriting, social scheduling tools, trend awareness, and community management.', 'Remote', 'Part-time', 50000, 80000, '1-2 years', DATE_ADD(CURRENT_DATE, INTERVAL 23 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(4, 1, 'PHP Developer', 'Maintain and extend secure PHP applications for local and international clients.', 'PHP, MySQL, MVC concepts, Git, and practical debugging experience.', 'Lahore', 'Full-time', 80000, 130000, '1-3 years', DATE_ADD(CURRENT_DATE, INTERVAL 18 DAY), 'pending', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(5, 6, 'Customer Experience Associate', 'Respond to customer questions and turn support conversations into positive outcomes.', 'Excellent written communication, patience, and basic ticketing system experience.', 'Remote', 'Remote', 55000, 85000, '0-2 years', DATE_ADD(CURRENT_DATE, INTERVAL 21 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 17 DAY)),
(6, 3, 'Inside Sales Representative', 'Connect with prospective merchants and guide them through the product journey.', 'Confident communication, target orientation, CRM use, and follow-up discipline.', 'Lahore', 'Full-time', 60000, 100000, '1-3 years', DATE_ADD(CURRENT_DATE, INTERVAL 25 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 18 DAY)),
(8, 5, 'Accounts Assistant', 'Support monthly close, reconciliations, and accurate finance operations.', 'BCom or equivalent, Excel, bookkeeping fundamentals, and accuracy.', 'Rawalpindi', 'Full-time', 50000, 80000, '0-2 years', DATE_ADD(CURRENT_DATE, INTERVAL 19 DAY), 'closed', DATE_SUB(NOW(), INTERVAL 45 DAY)),
(11, 2, 'SEO Specialist', 'Improve organic visibility through technical audits, content briefs, and reporting.', 'SEO tools, keyword research, technical SEO basics, and concise reporting.', 'Remote', 'Remote', 70000, 115000, '1-3 years', DATE_ADD(CURRENT_DATE, INTERVAL 34 DAY), 'rejected', DATE_SUB(NOW(), INTERVAL 40 DAY));

INSERT INTO applications (job_id, jobseeker_id, resume_path, cover_letter, status, applied_at) VALUES
(1, 12, 'uploads/resumes/demo-ahmed.pdf', 'My PHP and API experience closely matches this position.', 'shortlisted', DATE_SUB(NOW(), INTERVAL 9 DAY)),
(5, 12, 'uploads/resumes/demo-ahmed.pdf', 'I enjoy organizing hiring processes and would be glad to support your team.', 'applied', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(2, 13, 'uploads/resumes/demo-noor.pdf', 'I can bring strong SEO and editorial planning experience to this role.', 'shortlisted', DATE_SUB(NOW(), INTERVAL 8 DAY)),
(6, 13, 'uploads/resumes/demo-noor.pdf', 'My communication and lead generation background would help your sales team.', 'applied', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(2, 14, 'uploads/resumes/demo-usama.pdf', 'I have built responsive interfaces with React and modern CSS.', 'applied', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(3, 14, 'uploads/resumes/demo-usama.pdf', 'I am interested in improving release quality through thoughtful testing.', 'rejected', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(8, 15, 'uploads/resumes/demo-laiba.pdf', 'My Figma portfolio includes product flows, prototypes, and design systems.', 'shortlisted', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(9, 15, 'uploads/resumes/demo-laiba.pdf', 'I would love to grow my visual design skills with your studio.', 'applied', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(10, 16, 'uploads/resumes/demo-daniyal.pdf', 'My finance reporting experience matches the requirements of this role.', 'hired', DATE_SUB(NOW(), INTERVAL 12 DAY)),
(11, 17, 'uploads/resumes/demo-anaya.pdf', 'I use SQL and dashboards to explain business performance clearly.', 'shortlisted', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(12, 18, 'uploads/resumes/demo-hassan.pdf', 'I have hands-on Linux and customer troubleshooting experience.', 'applied', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(14, 19, 'uploads/resumes/demo-mehwish.pdf', 'I have managed paid social campaigns and reported on acquisition metrics.', 'shortlisted', DATE_SUB(NOW(), INTERVAL 11 DAY)),
(15, 20, 'uploads/resumes/demo-areeba.pdf', 'I can create consistent social content and manage community conversations.', 'applied', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(16, 21, 'uploads/resumes/demo-talha.pdf', 'I have PHP and MySQL experience and enjoy solving production issues.', 'applied', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(17, 22, 'uploads/resumes/demo-maha.pdf', 'My support background and written communication are a strong match.', 'rejected', DATE_SUB(NOW(), INTERVAL 14 DAY)),
(18, 23, 'uploads/resumes/demo-rayan.pdf', 'I am motivated by consultative sales and building long-term customer relationships.', 'applied', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(19, 24, 'uploads/resumes/demo-eman.pdf', 'I have experience with reconciliations and spreadsheet-based reporting.', 'applied', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(20, 25, 'uploads/resumes/demo-saad.pdf', 'My SEO audits and keyword research experience can support your growth goals.', 'applied', DATE_SUB(NOW(), INTERVAL 4 DAY));

INSERT INTO saved_jobs (jobseeker_id, job_id, saved_at) VALUES
(12, 1, DATE_SUB(NOW(), INTERVAL 5 DAY)), (12, 8, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(13, 4, DATE_SUB(NOW(), INTERVAL 6 DAY)), (13, 14, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(14, 2, DATE_SUB(NOW(), INTERVAL 7 DAY)), (14, 11, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(15, 8, DATE_SUB(NOW(), INTERVAL 5 DAY)), (16, 10, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(17, 12, DATE_SUB(NOW(), INTERVAL 3 DAY)), (18, 3, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(19, 6, DATE_SUB(NOW(), INTERVAL 9 DAY)), (20, 15, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(21, 16, DATE_SUB(NOW(), INTERVAL 6 DAY)), (22, 17, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(23, 18, DATE_SUB(NOW(), INTERVAL 4 DAY)), (24, 19, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(25, 20, DATE_SUB(NOW(), INTERVAL 2 DAY)), (26, 7, DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT INTO saved_jobs (jobseeker_id, job_id, saved_at) VALUES
(3, 2, NOW()),
(3, 3, NOW());

INSERT INTO contact_messages (name, email, message, created_at) VALUES
('Sample Visitor', 'visitor@example.com', 'Hello team, I would like to know more about your internship opportunities.', NOW());

INSERT INTO users (name, email, password, role, phone, status, created_at) VALUES
('Fahad Mahmood', 'fahad.hr@orbitsoft.example', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'employer', '+923301000001', 'active', DATE_SUB(NOW(), INTERVAL 12 DAY)),
('Komal Arshad', 'komal.people@urbanbasket.example', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'employer', '+923301000002', 'active', DATE_SUB(NOW(), INTERVAL 11 DAY)),
('Waqas Mir', 'waqas.hiring@edulink.example', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'employer', '+923301000003', 'active', DATE_SUB(NOW(), INTERVAL 10 DAY)),
('Sana Javed', 'sana.talent@healthsync.example', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'employer', '+923301000004', 'active', DATE_SUB(NOW(), INTERVAL 9 DAY)),
('Rizwan Akhtar', 'rizwan.jobs@greenroute.example', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'employer', '+923301000005', 'active', DATE_SUB(NOW(), INTERVAL 8 DAY)),
('Aiman Tariq', 'aiman.tariq@example.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923301000006', 'active', DATE_SUB(NOW(), INTERVAL 11 DAY)),
('Muneeb Shah', 'muneeb.shah@example.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923301000007', 'active', DATE_SUB(NOW(), INTERVAL 10 DAY)),
('Rida Hassan', 'rida.hassan@example.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923301000008', 'active', DATE_SUB(NOW(), INTERVAL 9 DAY)),
('Shahzaib Khan', 'shahzaib.khan@example.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923301000009', 'active', DATE_SUB(NOW(), INTERVAL 8 DAY)),
('Iqra Zaman', 'iqra.zaman@example.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923301000010', 'active', DATE_SUB(NOW(), INTERVAL 7 DAY)),
('Arham Siddiq', 'arham.siddiq@example.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923301000011', 'active', DATE_SUB(NOW(), INTERVAL 6 DAY)),
('Maham Riaz', 'maham.riaz@example.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923301000012', 'active', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('Yahya Butt', 'yahya.butt@example.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923301000013', 'active', DATE_SUB(NOW(), INTERVAL 4 DAY)),
('Ayesha Noor', 'ayesha.noor@example.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923301000014', 'active', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('Haris Saleem', 'haris.saleem@example.com', '$2y$12$9kRWt.8Eua3vZ7LPelhyZuhbkPHk3o9jqzv91rynVQ8Z8J/I12Vbu', 'jobseeker', '+923301000015', 'active', DATE_SUB(NOW(), INTERVAL 2 DAY));

INSERT INTO employer_profiles (user_id, company_name, company_logo, company_website, company_description, approved, updated_at) VALUES
(27, 'OrbitSoft Technologies', 'logo-default.png', 'https://orbitsoft.example', 'Product engineering and software delivery partner for ambitious businesses.', 1, NOW()),
(28, 'UrbanBasket', 'logo-default.png', 'https://urbanbasket.example', 'A fast-growing online marketplace serving customers across Pakistan.', 1, NOW()),
(29, 'EduLink Academy', 'logo-default.png', 'https://edulink.example', 'Digital learning tools and services for students and educators.', 1, NOW()),
(30, 'HealthSync', 'logo-default.png', 'https://healthsync.example', 'Technology that makes healthcare coordination simpler and more human.', 1, NOW()),
(31, 'GreenRoute Logistics', 'logo-default.png', 'https://greenroute.example', 'Smarter delivery operations for sustainable commerce.', 1, NOW());

INSERT INTO jobseeker_profiles (user_id, skills, experience, education, address, updated_at) VALUES
(32, 'React, Next.js, JavaScript, Tailwind CSS', '2 years building modern web interfaces', 'BS Computer Science', 'Lahore, Pakistan', NOW()),
(33, 'Python, Django, PostgreSQL, APIs', '3 years developing backend services', 'BS Software Engineering', 'Karachi, Pakistan', NOW()),
(34, 'UX research, Figma, prototyping', '2 years designing digital products', 'BDes Interaction Design', 'Islamabad, Pakistan', NOW()),
(35, 'Excel, operations, inventory management', '4 years in retail operations', 'BBA Supply Chain', 'Rawalpindi, Pakistan', NOW()),
(36, 'Teaching, communication, curriculum design', '3 years in education and training', 'MEd Education', 'Peshawar, Pakistan', NOW()),
(37, 'Java, Spring Boot, SQL, testing', '2 years in enterprise application development', 'BS Computer Science', 'Lahore, Pakistan', NOW()),
(38, 'Recruitment, HRIS, employer branding', '2 years in people operations', 'MBA Human Resources', 'Islamabad, Pakistan', NOW()),
(39, 'Logistics, dispatch, route planning', '3 years coordinating delivery teams', 'BCom Management', 'Faisalabad, Pakistan', NOW()),
(40, 'Medical writing, research, documentation', '1 year in healthcare content', 'BS Biotechnology', 'Karachi, Pakistan', NOW()),
(41, 'Digital marketing, Canva, email campaigns', '2 years managing small-business campaigns', 'BBA Marketing', 'Multan, Pakistan', NOW());

INSERT INTO jobs (employer_id, category_id, title, description, requirements, location, job_type, salary_min, salary_max, experience_level, deadline, status, created_at) VALUES
(27, 1, 'Full Stack JavaScript Developer', 'Join a product team building fast, accessible applications for international clients.', 'JavaScript, React, Node.js, SQL, Git, and experience working with REST APIs.', 'Lahore', 'Full-time', 120000, 200000, '2-5 years', DATE_ADD(CURRENT_DATE, INTERVAL 40 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(27, 1, 'Technical Project Coordinator', 'Coordinate milestones, documentation, and communication across multiple engineering projects.', 'Strong organization, technical curiosity, documentation skills, and project tracking experience.', 'Remote', 'Remote', 70000, 110000, '1-3 years', DATE_ADD(CURRENT_DATE, INTERVAL 34 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(28, 3, 'Marketplace Operations Manager', 'Own catalog quality, seller support, and daily marketplace performance.', 'Operations experience, spreadsheets, process improvement, and strong problem-solving skills.', 'Karachi', 'Full-time', 90000, 145000, '3-5 years', DATE_ADD(CURRENT_DATE, INTERVAL 36 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(28, 2, 'Email Marketing Associate', 'Create campaigns that help customers discover useful products and offers.', 'Email marketing, copywriting, analytics, and familiarity with campaign tools.', 'Remote', 'Part-time', 50000, 85000, '1-2 years', DATE_ADD(CURRENT_DATE, INTERVAL 25 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(29, 2, 'Online Learning Content Writer', 'Turn subject-matter expertise into clear and engaging learning content.', 'Excellent writing, research skills, and interest in education or technology.', 'Islamabad', 'Full-time', 65000, 105000, '1-3 years', DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 8 DAY)),
(29, 6, 'Student Success Advisor', 'Support learners with guidance, follow-up, and practical study planning.', 'Empathy, clear communication, organization, and experience supporting students or customers.', 'Remote', 'Full-time', 55000, 90000, '1-2 years', DATE_ADD(CURRENT_DATE, INTERVAL 29 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(30, 1, 'Healthcare Data Coordinator', 'Maintain accurate records and help teams turn healthcare data into useful insights.', 'Excel or SQL, data accuracy, documentation, and strong confidentiality practices.', 'Lahore', 'Full-time', 75000, 120000, '1-3 years', DATE_ADD(CURRENT_DATE, INTERVAL 31 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 9 DAY)),
(30, 2, 'Medical Content Reviewer', 'Review patient-facing content for clarity, accuracy, and an empathetic tone.', 'Research ability, healthcare writing experience, and excellent attention to detail.', 'Remote', 'Part-time', 60000, 95000, '1-3 years', DATE_ADD(CURRENT_DATE, INTERVAL 24 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(31, 3, 'Logistics Planning Analyst', 'Use delivery data to improve route planning and service reliability.', 'Excel, operations analysis, planning skills, and comfort working with large datasets.', 'Faisalabad', 'Full-time', 70000, 115000, '1-3 years', DATE_ADD(CURRENT_DATE, INTERVAL 33 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(31, 3, 'Dispatch Team Lead', 'Lead dispatch coordinators and keep daily delivery operations running smoothly.', 'Team leadership, logistics experience, communication, and practical decision-making.', 'Lahore', 'Full-time', 80000, 125000, '3-5 years', DATE_ADD(CURRENT_DATE, INTERVAL 26 DAY), 'pending', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(27, 1, 'Software Support Intern', 'Learn product support, issue triage, and customer communication with an engineering team.', 'Basic web knowledge, clear communication, and willingness to learn technical concepts.', 'Remote', 'Internship', 30000, 50000, 'Internship', DATE_ADD(CURRENT_DATE, INTERVAL 22 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(28, 4, 'Brand Designer', 'Create visual campaigns and product storytelling for a busy digital marketplace.', 'Strong portfolio, Figma or Adobe tools, visual systems, and creative problem-solving.', 'Karachi', 'Full-time', 85000, 140000, '2-4 years', DATE_ADD(CURRENT_DATE, INTERVAL 37 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 12 DAY));

INSERT INTO applications (job_id, jobseeker_id, resume_path, cover_letter, status, applied_at) VALUES
(25, 32, 'uploads/resumes/demo-aiman.pdf', 'My React experience and focus on accessible interfaces match this role well.', 'shortlisted', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(26, 33, 'uploads/resumes/demo-muneeb.pdf', 'I have coordinated technical projects and enjoy keeping teams aligned.', 'applied', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(27, 35, 'uploads/resumes/demo-shahzaib.pdf', 'My operations background and marketplace experience are a strong fit.', 'shortlisted', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(28, 41, 'uploads/resumes/demo-ayesha.pdf', 'I have planned email campaigns and measured engagement for small businesses.', 'applied', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(29, 36, 'uploads/resumes/demo-arham.pdf', 'I enjoy explaining complex topics clearly and creating useful learning material.', 'shortlisted', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(30, 38, 'uploads/resumes/demo-maham.pdf', 'My people operations experience would help me support learners with empathy.', 'applied', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(31, 37, 'uploads/resumes/demo-yahya.pdf', 'My data skills and attention to privacy would support this healthcare role.', 'applied', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(32, 40, 'uploads/resumes/demo-ayesha.pdf', 'I have research and documentation experience with a strong attention to clarity.', 'shortlisted', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(33, 39, 'uploads/resumes/demo-rida.pdf', 'I have worked with route planning and delivery coordination teams.', 'applied', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(34, 35, 'uploads/resumes/demo-shahzaib.pdf', 'I have led operations teams and can improve dispatch reliability.', 'applied', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(35, 32, 'uploads/resumes/demo-aiman.pdf', 'I would value the opportunity to learn product support while helping customers.', 'applied', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(36, 34, 'uploads/resumes/demo-rida.pdf', 'My product design portfolio includes brand systems and campaign work.', 'hired', DATE_SUB(NOW(), INTERVAL 9 DAY));

INSERT INTO saved_jobs (jobseeker_id, job_id, saved_at) VALUES
(32, 25, DATE_SUB(NOW(), INTERVAL 2 DAY)), (32, 36, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(33, 25, DATE_SUB(NOW(), INTERVAL 4 DAY)), (33, 31, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(34, 36, DATE_SUB(NOW(), INTERVAL 2 DAY)), (34, 29, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(35, 27, DATE_SUB(NOW(), INTERVAL 5 DAY)), (36, 29, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(37, 31, DATE_SUB(NOW(), INTERVAL 2 DAY)), (38, 30, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(39, 33, DATE_SUB(NOW(), INTERVAL 3 DAY)), (40, 32, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(41, 28, DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT INTO contact_messages (name, email, message, created_at) VALUES
('Hiba Rehman', 'hiba@example.com', 'I would like to know whether remote employers can hire candidates from other cities.', DATE_SUB(NOW(), INTERVAL 4 DAY)),
('Farhan Qazi', 'farhan@example.com', 'Please share more information about posting internship opportunities.', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('Mina Asif', 'mina@example.com', 'I found a useful job listing and wanted to ask about application updates.', DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT INTO jobs (employer_id, category_id, title, description, requirements, location, job_type, salary_min, salary_max, experience_level, deadline, status, created_at) VALUES
(2, 1, 'Laravel Backend Developer', 'Build reliable business applications and APIs for clients across multiple industries.', 'PHP, Laravel, MySQL, REST APIs, Git, and experience writing maintainable production code.', 'Lahore', 'Full-time', 100000, 165000, '2-4 years', DATE_ADD(CURRENT_DATE, INTERVAL 42 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(4, 1, 'React Frontend Engineer', 'Create polished, responsive interfaces for a growing SaaS product suite.', 'React, JavaScript, TypeScript, CSS, accessibility, and component-driven development.', 'Remote', 'Remote', 110000, 180000, '2-5 years', DATE_ADD(CURRENT_DATE, INTERVAL 39 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(10, 1, 'DevOps Engineer', 'Own deployment pipelines, observability, and infrastructure improvements for cloud products.', 'Linux, Docker, CI/CD, cloud platforms, monitoring, and scripting with Bash or Python.', 'Islamabad', 'Full-time', 130000, 220000, '3-6 years', DATE_ADD(CURRENT_DATE, INTERVAL 45 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(27, 1, 'Mobile App Developer', 'Develop and improve cross-platform mobile experiences used by thousands of customers.', 'Flutter or React Native, mobile APIs, state management, and store release experience.', 'Karachi', 'Full-time', 100000, 175000, '2-4 years', DATE_ADD(CURRENT_DATE, INTERVAL 36 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(27, 1, 'Cloud Solutions Architect', 'Design secure and scalable cloud solutions for complex customer workloads.', 'AWS or Azure, networking, security, architecture diagrams, and technical communication.', 'Remote', 'Remote', 180000, 300000, '5+ years', DATE_ADD(CURRENT_DATE, INTERVAL 50 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(29, 1, 'Python Django Developer', 'Build education technology services that help learners and teachers succeed.', 'Python, Django, PostgreSQL, testing, Git, and experience designing REST APIs.', 'Islamabad', 'Full-time', 95000, 160000, '2-4 years', DATE_ADD(CURRENT_DATE, INTERVAL 41 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(30, 1, 'Data Engineer', 'Create dependable data pipelines for healthcare reporting and operational insights.', 'Python or Scala, SQL, ETL pipelines, data modeling, and cloud data platforms.', 'Lahore', 'Full-time', 120000, 200000, '2-5 years', DATE_ADD(CURRENT_DATE, INTERVAL 44 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 8 DAY)),
(31, 1, 'Cybersecurity Analyst', 'Monitor systems, investigate incidents, and improve security controls across the platform.', 'Security monitoring, incident response, networking, vulnerability assessment, and documentation.', 'Lahore', 'Full-time', 100000, 175000, '2-4 years', DATE_ADD(CURRENT_DATE, INTERVAL 38 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 9 DAY)),
(4, 1, 'QA Test Engineer', 'Plan and execute testing for web applications before every customer release.', 'Manual testing, test cases, API testing, bug reporting, and basic automation knowledge.', 'Lahore', 'Full-time', 75000, 125000, '1-3 years', DATE_ADD(CURRENT_DATE, INTERVAL 33 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(8, 1, 'Business Intelligence Developer', 'Turn operational data into dashboards and reporting tools for leadership teams.', 'SQL, Power BI or Tableau, data visualization, and strong stakeholder communication.', 'Remote', 'Remote', 105000, 170000, '2-4 years', DATE_ADD(CURRENT_DATE, INTERVAL 35 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 11 DAY)),
(10, 1, 'IT Support Specialist', 'Provide friendly technical support and keep internal systems running smoothly.', 'Windows and Linux basics, networking, ticketing systems, troubleshooting, and communication.', 'Islamabad', 'Full-time', 60000, 95000, '1-2 years', DATE_ADD(CURRENT_DATE, INTERVAL 29 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 12 DAY)),
(11, 1, 'WordPress Developer', 'Build fast, accessible websites and reusable content components for marketing clients.', 'WordPress, PHP, HTML, CSS, JavaScript, SEO fundamentals, and responsive design.', 'Karachi', 'Part-time', 65000, 110000, '1-3 years', DATE_ADD(CURRENT_DATE, INTERVAL 27 DAY), 'pending', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(5, 1, 'Technical Product Manager', 'Translate customer needs into clear product plans for a collaborative technology team.', 'Product discovery, roadmaps, agile delivery, technical communication, and analytics.', 'Lahore', 'Full-time', 140000, 230000, '4-7 years', DATE_ADD(CURRENT_DATE, INTERVAL 47 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 13 DAY)),
(6, 1, 'Junior Java Developer', 'Learn from experienced engineers while contributing to scalable commerce services.', 'Java basics, object-oriented programming, SQL, Git, and a strong learning mindset.', 'Karachi', 'Full-time', 70000, 110000, '0-1 year', DATE_ADD(CURRENT_DATE, INTERVAL 31 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 14 DAY)),
(7, 1, 'UI Developer Intern', 'Support the design and engineering team by turning interface concepts into clean markup.', 'HTML, CSS, basic JavaScript, attention to visual detail, and portfolio work.', 'Remote', 'Internship', 30000, 50000, 'Internship', DATE_ADD(CURRENT_DATE, INTERVAL 25 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 15 DAY)),
(27, 1, 'Machine Learning Engineer', 'Prototype and productionize machine learning features for customer-facing products.', 'Python, pandas, scikit-learn, model evaluation, APIs, and practical experimentation.', 'Lahore', 'Full-time', 150000, 250000, '3-6 years', DATE_ADD(CURRENT_DATE, INTERVAL 48 DAY), 'pending', DATE_SUB(NOW(), INTERVAL 2 DAY));

INSERT INTO applications (job_id, jobseeker_id, resume_path, cover_letter, status, applied_at) VALUES
(37, 12, 'uploads/resumes/demo-ahmed.pdf', 'My Laravel and API experience makes this backend role a strong match.', 'shortlisted', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(38, 32, 'uploads/resumes/demo-aiman.pdf', 'I have built responsive React interfaces and reusable components.', 'applied', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(39, 23, 'uploads/resumes/demo-rayan.pdf', 'My Docker, Linux, and deployment experience fits this DevOps position.', 'shortlisted', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(40, 14, 'uploads/resumes/demo-usama.pdf', 'I am excited to contribute mobile development and API integration skills.', 'applied', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(41, 18, 'uploads/resumes/demo-hassan.pdf', 'I have worked with cloud infrastructure and enjoy solving complex systems problems.', 'applied', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(42, 33, 'uploads/resumes/demo-muneeb.pdf', 'My Python and Django experience aligns closely with this education platform role.', 'shortlisted', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(43, 17, 'uploads/resumes/demo-anaya.pdf', 'I use SQL and Python to build reliable data workflows and clear reports.', 'applied', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(44, 25, 'uploads/resumes/demo-saad.pdf', 'My QA experience includes test planning, bug reporting, and API validation.', 'shortlisted', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(45, 18, 'uploads/resumes/demo-hassan.pdf', 'I can turn operational data into dashboards that help teams make decisions.', 'applied', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(46, 17, 'uploads/resumes/demo-anaya.pdf', 'I have strong troubleshooting and customer communication experience.', 'applied', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(47, 20, 'uploads/resumes/demo-areeba.pdf', 'My WordPress and PHP background would help deliver fast marketing websites.', 'applied', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(48, 21, 'uploads/resumes/demo-talha.pdf', 'I have experience coordinating technical work and translating requirements.', 'shortlisted', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(49, 23, 'uploads/resumes/demo-rayan.pdf', 'I am building my Java foundations and would value this learning opportunity.', 'applied', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(50, 34, 'uploads/resumes/demo-rida.pdf', 'My visual design and frontend fundamentals are a strong fit for this internship.', 'applied', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(52, 18, 'uploads/resumes/demo-hassan.pdf', 'I have Python data experience and want to work on practical ML products.', 'applied', DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT INTO saved_jobs (jobseeker_id, job_id, saved_at) VALUES
(12, 37, DATE_SUB(NOW(), INTERVAL 2 DAY)), (32, 38, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(23, 39, DATE_SUB(NOW(), INTERVAL 3 DAY)), (14, 40, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(18, 41, DATE_SUB(NOW(), INTERVAL 4 DAY)), (33, 42, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(17, 43, DATE_SUB(NOW(), INTERVAL 2 DAY)), (25, 44, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(18, 45, DATE_SUB(NOW(), INTERVAL 1 DAY)), (20, 47, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(21, 48, DATE_SUB(NOW(), INTERVAL 4 DAY)), (34, 50, DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT INTO jobs (employer_id, category_id, title, description, requirements, location, job_type, salary_min, salary_max, experience_level, deadline, status, created_at) VALUES
(2, 1, 'Senior API Engineer', 'Design and maintain secure APIs for a high-volume business platform.', 'PHP or Node.js, REST APIs, SQL, testing, and production debugging.', 'Lahore', 'Full-time', 125000, 210000, '4-6 years', DATE_ADD(CURRENT_DATE, INTERVAL 42 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(4, 1, 'Frontend React Developer', 'Build accessible and responsive web interfaces with a product engineering team.', 'React, TypeScript, CSS, component systems, and responsive design.', 'Islamabad', 'Full-time', 100000, 175000, '2-4 years', DATE_ADD(CURRENT_DATE, INTERVAL 39 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(10, 1, 'Cloud Infrastructure Engineer', 'Improve reliability, automation, and observability across cloud environments.', 'AWS or Azure, Linux, Docker, Terraform, CI/CD, and monitoring.', 'Remote', 'Remote', 135000, 225000, '3-6 years', DATE_ADD(CURRENT_DATE, INTERVAL 45 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(27, 1, 'Android Developer', 'Develop mobile features for a fast-growing customer application.', 'Kotlin or Java, Android SDK, REST integration, and release workflows.', 'Karachi', 'Full-time', 95000, 165000, '2-4 years', DATE_ADD(CURRENT_DATE, INTERVAL 35 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(29, 1, 'Django API Developer', 'Create scalable backend services for online learning products.', 'Python, Django, PostgreSQL, REST APIs, Git, and automated testing.', 'Islamabad', 'Full-time', 95000, 160000, '2-4 years', DATE_ADD(CURRENT_DATE, INTERVAL 38 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(30, 1, 'ETL Data Developer', 'Build data pipelines that support reporting and operational decisions.', 'SQL, Python, ETL concepts, data modeling, and pipeline monitoring.', 'Lahore', 'Full-time', 105000, 180000, '2-5 years', DATE_ADD(CURRENT_DATE, INTERVAL 40 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(31, 1, 'Security Operations Analyst', 'Monitor security events and help strengthen controls across business systems.', 'SIEM tools, incident response, networking, vulnerability management, and reporting.', 'Lahore', 'Full-time', 90000, 155000, '1-4 years', DATE_ADD(CURRENT_DATE, INTERVAL 34 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(4, 1, 'Automation QA Developer', 'Create automated checks that protect product quality during rapid releases.', 'Selenium or Playwright, JavaScript or Python, API testing, and CI pipelines.', 'Remote', 'Remote', 85000, 145000, '2-4 years', DATE_ADD(CURRENT_DATE, INTERVAL 37 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 8 DAY)),
(8, 1, 'SQL Reporting Developer', 'Build reliable reports and dashboards for finance and operations teams.', 'Advanced SQL, Power BI, data validation, and stakeholder communication.', 'Rawalpindi', 'Full-time', 90000, 150000, '2-4 years', DATE_ADD(CURRENT_DATE, INTERVAL 32 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 9 DAY)),
(10, 1, 'Helpdesk Technician', 'Resolve technical issues and provide friendly support to distributed teams.', 'Ticketing systems, Windows, networking, hardware troubleshooting, and communication.', 'Islamabad', 'Full-time', 55000, 85000, '0-2 years', DATE_ADD(CURRENT_DATE, INTERVAL 28 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(11, 1, 'PHP WordPress Engineer', 'Deliver optimized WordPress websites and custom integrations for clients.', 'WordPress, PHP, MySQL, JavaScript, performance, and SEO basics.', 'Karachi', 'Part-time', 65000, 105000, '1-3 years', DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 11 DAY)),
(5, 1, 'Technical Delivery Manager', 'Lead delivery planning for cross-functional software initiatives.', 'Agile delivery, technical project management, roadmaps, and clear communication.', 'Lahore', 'Full-time', 145000, 235000, '5-8 years', DATE_ADD(CURRENT_DATE, INTERVAL 48 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 12 DAY)),
(6, 1, 'Java Spring Developer', 'Build dependable services for a growing digital commerce platform.', 'Java, Spring Boot, SQL, REST services, testing, and object-oriented design.', 'Karachi', 'Full-time', 90000, 155000, '1-3 years', DATE_ADD(CURRENT_DATE, INTERVAL 36 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 13 DAY)),
(7, 1, 'UX Engineering Intern', 'Help transform product designs into polished and responsive experiences.', 'HTML, CSS, JavaScript, design awareness, and a portfolio of work.', 'Remote', 'Internship', 25000, 45000, 'Internship', DATE_ADD(CURRENT_DATE, INTERVAL 25 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 14 DAY)),
(27, 1, 'Applied ML Developer', 'Experiment with machine learning features and integrate models into products.', 'Python, scikit-learn, pandas, model evaluation, APIs, and SQL.', 'Lahore', 'Full-time', 140000, 240000, '2-5 years', DATE_ADD(CURRENT_DATE, INTERVAL 46 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 15 DAY)),
(2, 2, 'Digital Content Manager', 'Lead content planning and publishing for technology brands.', 'Editorial planning, SEO, analytics, strong writing, and team coordination.', 'Lahore', 'Full-time', 75000, 125000, '2-4 years', DATE_ADD(CURRENT_DATE, INTERVAL 31 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(5, 2, 'SEO Content Specialist', 'Create search-focused content that attracts qualified visitors.', 'Keyword research, SEO tools, copywriting, content briefs, and reporting.', 'Remote', 'Remote', 65000, 110000, '1-3 years', DATE_ADD(CURRENT_DATE, INTERVAL 29 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(11, 2, 'Brand Communications Executive', 'Build clear brand messaging across campaigns and digital channels.', 'Copywriting, social media, campaign coordination, and strong communication.', 'Karachi', 'Full-time', 70000, 115000, '1-3 years', DATE_ADD(CURRENT_DATE, INTERVAL 33 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(28, 3, 'Sales Operations Analyst', 'Improve sales processes with clean data, reports, and practical insights.', 'CRM experience, Excel, reporting, process improvement, and analysis.', 'Islamabad', 'Full-time', 70000, 115000, '1-3 years', DATE_ADD(CURRENT_DATE, INTERVAL 35 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(6, 3, 'Account Executive', 'Build relationships with merchants and grow a portfolio of business accounts.', 'B2B sales, presentations, negotiation, CRM, and target ownership.', 'Lahore', 'Full-time', 65000, 120000, '2-4 years', DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(31, 3, 'Fleet Sales Coordinator', 'Coordinate commercial accounts and support delivery partnerships.', 'Sales coordination, logistics understanding, communication, and reporting.', 'Faisalabad', 'Full-time', 60000, 95000, '1-3 years', DATE_ADD(CURRENT_DATE, INTERVAL 27 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(7, 4, 'Visual Product Designer', 'Create thoughtful interface systems for web and mobile products.', 'Figma, UX research, prototyping, visual design, and portfolio evidence.', 'Remote', 'Remote', 105000, 180000, '3-5 years', DATE_ADD(CURRENT_DATE, INTERVAL 43 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(28, 4, 'Motion Graphics Designer', 'Produce engaging animated assets for campaigns and product launches.', 'After Effects, Illustrator, visual storytelling, and strong creative judgment.', 'Karachi', 'Full-time', 80000, 135000, '2-4 years', DATE_ADD(CURRENT_DATE, INTERVAL 34 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 8 DAY)),
(5, 5, 'Financial Systems Analyst', 'Improve finance workflows through reporting, automation, and system analysis.', 'Excel, SQL, financial reporting, process mapping, and analytical thinking.', 'Islamabad', 'Full-time', 95000, 155000, '2-5 years', DATE_ADD(CURRENT_DATE, INTERVAL 41 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 9 DAY)),
(8, 5, 'Junior Accounts Officer', 'Support reconciliations, reporting, and accurate monthly finance operations.', 'BCom or equivalent, Excel, bookkeeping fundamentals, and attention to detail.', 'Lahore', 'Full-time', 50000, 80000, '0-2 years', DATE_ADD(CURRENT_DATE, INTERVAL 26 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(30, 6, 'Customer Care Specialist', 'Help patients and partners receive fast, respectful, and accurate support.', 'Strong written communication, patience, ticketing tools, and problem-solving.', 'Remote', 'Remote', 55000, 90000, '1-2 years', DATE_ADD(CURRENT_DATE, INTERVAL 28 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 11 DAY)),
(29, 6, 'Learning Support Coordinator', 'Coordinate learner support requests and keep education programs on track.', 'Organization, communication, spreadsheets, empathy, and follow-up discipline.', 'Islamabad', 'Part-time', 45000, 75000, '0-2 years', DATE_ADD(CURRENT_DATE, INTERVAL 24 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 12 DAY)),
(10, 1, 'Network Administrator', 'Maintain dependable network services and support secure office connectivity.', 'TCP/IP, routing, firewalls, Linux, monitoring, and troubleshooting experience.', 'Islamabad', 'Full-time', 85000, 140000, '2-5 years', DATE_ADD(CURRENT_DATE, INTERVAL 39 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 13 DAY)),
(4, 1, 'IT Project Intern', 'Support sprint planning, documentation, testing, and technical team coordination.', 'Basic project tools, documentation skills, communication, and learning mindset.', 'Lahore', 'Internship', 25000, 45000, 'Internship', DATE_ADD(CURRENT_DATE, INTERVAL 21 DAY), 'approved', DATE_SUB(NOW(), INTERVAL 14 DAY)),
(27, 1, 'Database Administrator', 'Protect performance, availability, and reliability across production databases.', 'MySQL or PostgreSQL, backups, indexing, monitoring, and incident response.', 'Remote', 'Remote', 130000, 215000, '4-7 years', DATE_ADD(CURRENT_DATE, INTERVAL 49 DAY), 'pending', DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT INTO applications (job_id, jobseeker_id, resume_path, cover_letter, status, applied_at) VALUES
(53, 12, 'uploads/resumes/demo-ahmed.pdf', 'My backend API experience matches this senior engineering opportunity.', 'shortlisted', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(54, 32, 'uploads/resumes/demo-aiman.pdf', 'I have strong React and TypeScript experience building responsive products.', 'applied', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(55, 23, 'uploads/resumes/demo-rayan.pdf', 'My cloud automation and Linux experience make me a strong candidate.', 'applied', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(56, 14, 'uploads/resumes/demo-usama.pdf', 'I have built Android features and integrated mobile APIs.', 'shortlisted', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(57, 33, 'uploads/resumes/demo-muneeb.pdf', 'My Django and PostgreSQL work aligns with this role.', 'applied', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(58, 18, 'uploads/resumes/demo-hassan.pdf', 'I can build reliable data pipelines using Python and SQL.', 'applied', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(59, 25, 'uploads/resumes/demo-saad.pdf', 'My testing and incident analysis experience suits security operations.', 'applied', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(60, 25, 'uploads/resumes/demo-saad.pdf', 'I have practical automation testing and API validation experience.', 'shortlisted', DATE_SUB(NOW(), INTERVAL 8 DAY)),
(61, 17, 'uploads/resumes/demo-anaya.pdf', 'My SQL reporting and dashboard experience is a strong fit.', 'applied', DATE_SUB(NOW(), INTERVAL 9 DAY)),
(62, 17, 'uploads/resumes/demo-anaya.pdf', 'I enjoy solving technical issues and helping users clearly.', 'applied', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(63, 20, 'uploads/resumes/demo-areeba.pdf', 'I have built WordPress sites with PHP and performance improvements.', 'applied', DATE_SUB(NOW(), INTERVAL 11 DAY)),
(64, 21, 'uploads/resumes/demo-talha.pdf', 'I have led technical delivery and coordinated cross-functional teams.', 'shortlisted', DATE_SUB(NOW(), INTERVAL 12 DAY)),
(65, 23, 'uploads/resumes/demo-rayan.pdf', 'My Java and Spring learning background matches this opportunity.', 'applied', DATE_SUB(NOW(), INTERVAL 13 DAY)),
(66, 34, 'uploads/resumes/demo-rida.pdf', 'My frontend and visual design skills suit this internship.', 'applied', DATE_SUB(NOW(), INTERVAL 14 DAY)),
(67, 18, 'uploads/resumes/demo-hassan.pdf', 'I have Python experimentation and data modeling experience.', 'applied', DATE_SUB(NOW(), INTERVAL 15 DAY)),
(68, 13, 'uploads/resumes/demo-noor.pdf', 'I can lead SEO content planning and performance reporting.', 'shortlisted', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(69, 24, 'uploads/resumes/demo-eman.pdf', 'My SEO writing and keyword research experience matches this role.', 'applied', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(70, 19, 'uploads/resumes/demo-mehwish.pdf', 'I have campaign communication and content coordination experience.', 'applied', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(71, 19, 'uploads/resumes/demo-mehwish.pdf', 'My CRM reporting and process improvement skills can support sales operations.', 'applied', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(72, 23, 'uploads/resumes/demo-rayan.pdf', 'I enjoy consultative B2B sales and building client relationships.', 'applied', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(73, 39, 'uploads/resumes/demo-rida.pdf', 'My logistics and account coordination experience fits this role.', 'applied', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(74, 15, 'uploads/resumes/demo-laiba.pdf', 'My Figma portfolio includes product flows and design systems.', 'shortlisted', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(75, 22, 'uploads/resumes/demo-maha.pdf', 'I create animated visual assets and enjoy collaborative creative work.', 'applied', DATE_SUB(NOW(), INTERVAL 8 DAY)),
(76, 16, 'uploads/resumes/demo-daniyal.pdf', 'My reporting and finance systems experience is directly relevant.', 'applied', DATE_SUB(NOW(), INTERVAL 9 DAY)),
(77, 16, 'uploads/resumes/demo-daniyal.pdf', 'I have strong Excel and accounting fundamentals.', 'applied', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(78, 17, 'uploads/resumes/demo-anaya.pdf', 'My customer support experience is empathetic and detail-oriented.', 'applied', DATE_SUB(NOW(), INTERVAL 11 DAY)),
(79, 21, 'uploads/resumes/demo-talha.pdf', 'I am organized and enjoy supporting learners and teams.', 'applied', DATE_SUB(NOW(), INTERVAL 12 DAY)),
(80, 23, 'uploads/resumes/demo-rayan.pdf', 'I understand networking fundamentals and technical troubleshooting.', 'shortlisted', DATE_SUB(NOW(), INTERVAL 13 DAY)),
(81, 21, 'uploads/resumes/demo-talha.pdf', 'I can support project documentation and agile team coordination.', 'applied', DATE_SUB(NOW(), INTERVAL 14 DAY)),
(82, 18, 'uploads/resumes/demo-hassan.pdf', 'I have database optimization and SQL administration experience.', 'applied', DATE_SUB(NOW(), INTERVAL 15 DAY));

INSERT INTO saved_jobs (jobseeker_id, job_id, saved_at) VALUES
(12, 53, DATE_SUB(NOW(), INTERVAL 1 DAY)), (32, 54, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(23, 55, DATE_SUB(NOW(), INTERVAL 3 DAY)), (14, 56, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(33, 57, DATE_SUB(NOW(), INTERVAL 5 DAY)), (18, 58, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(25, 59, DATE_SUB(NOW(), INTERVAL 7 DAY)), (25, 60, DATE_SUB(NOW(), INTERVAL 8 DAY)),
(17, 61, DATE_SUB(NOW(), INTERVAL 9 DAY)), (17, 62, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(20, 63, DATE_SUB(NOW(), INTERVAL 11 DAY)), (21, 64, DATE_SUB(NOW(), INTERVAL 12 DAY)),
(23, 65, DATE_SUB(NOW(), INTERVAL 13 DAY)), (34, 66, DATE_SUB(NOW(), INTERVAL 14 DAY)),
(18, 67, DATE_SUB(NOW(), INTERVAL 15 DAY)), (13, 68, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(24, 69, DATE_SUB(NOW(), INTERVAL 2 DAY)), (19, 70, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(19, 71, DATE_SUB(NOW(), INTERVAL 4 DAY)), (23, 72, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(39, 73, DATE_SUB(NOW(), INTERVAL 6 DAY)), (15, 74, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(22, 75, DATE_SUB(NOW(), INTERVAL 8 DAY)), (16, 76, DATE_SUB(NOW(), INTERVAL 9 DAY)),
(16, 77, DATE_SUB(NOW(), INTERVAL 10 DAY)), (17, 78, DATE_SUB(NOW(), INTERVAL 11 DAY)),
(21, 79, DATE_SUB(NOW(), INTERVAL 12 DAY)), (23, 80, DATE_SUB(NOW(), INTERVAL 13 DAY)),
(21, 81, DATE_SUB(NOW(), INTERVAL 14 DAY)), (18, 82, DATE_SUB(NOW(), INTERVAL 15 DAY));

SELECT 'MannatJobs database schema created successfully.' AS status;
