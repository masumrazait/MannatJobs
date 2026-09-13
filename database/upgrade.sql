-- MannatJobs non-destructive database upgrade
-- Use this file for an existing installation. It does not drop or delete data.

USE mannatjobs;

CREATE TABLE IF NOT EXISTS users (
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

CREATE TABLE IF NOT EXISTS jobseeker_profiles (
    user_id INT UNSIGNED NOT NULL PRIMARY KEY,
    resume_path VARCHAR(255) DEFAULT NULL,
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
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_upgrade_jobseeker_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS employer_profiles (
    user_id INT UNSIGNED NOT NULL PRIMARY KEY,
    company_name VARCHAR(180) DEFAULT NULL,
    company_logo VARCHAR(255) DEFAULT NULL,
    company_website VARCHAR(255) DEFAULT NULL,
    company_description TEXT DEFAULT NULL,
    approved TINYINT(1) NOT NULL DEFAULT 0,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_upgrade_employer_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS employer_job_quotas (
    employer_id INT UNSIGNED PRIMARY KEY,
    post_limit INT UNSIGNED NOT NULL DEFAULT 30,
    posts_used INT UNSIGNED NOT NULL DEFAULT 0,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_upgrade_quota_employer FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS job_post_quota_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employer_id INT UNSIGNED NOT NULL,
    requested_posts INT UNSIGNED NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    admin_id INT UNSIGNED DEFAULT NULL,
    admin_note VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_upgrade_quota_request_employer FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_upgrade_quota_request_admin FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_upgrade_quota_requests_status (status),
    INDEX idx_upgrade_quota_requests_employer (employer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS job_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS jobs (
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
    CONSTRAINT fk_upgrade_jobs_employer FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_upgrade_jobs_category FOREIGN KEY (category_id) REFERENCES job_categories(id) ON DELETE RESTRICT,
    INDEX idx_upgrade_jobs_employer (employer_id),
    INDEX idx_upgrade_jobs_status (status),
    INDEX idx_upgrade_jobs_location (location),
    INDEX idx_upgrade_jobs_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS applications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id INT UNSIGNED NOT NULL,
    jobseeker_id INT UNSIGNED NOT NULL,
    resume_path VARCHAR(255) DEFAULT NULL,
    cover_letter TEXT DEFAULT NULL,
    status ENUM('applied', 'shortlisted', 'rejected', 'hired') NOT NULL DEFAULT 'applied',
    applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_upgrade_applications_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    CONSTRAINT fk_upgrade_applications_jobseeker FOREIGN KEY (jobseeker_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_upgrade_application_once (job_id, jobseeker_id),
    INDEX idx_upgrade_applications_job (job_id),
    INDEX idx_upgrade_applications_jobseeker (jobseeker_id),
    INDEX idx_upgrade_applications_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS saved_jobs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    jobseeker_id INT UNSIGNED NOT NULL,
    job_id INT UNSIGNED NOT NULL,
    saved_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_upgrade_saved_jobseeker FOREIGN KEY (jobseeker_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_upgrade_saved_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    UNIQUE KEY uq_upgrade_saved_job (jobseeker_id, job_id),
    INDEX idx_upgrade_saved_jobs_job (job_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS contact_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(180) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_upgrade_contact_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE jobseeker_profiles
    ADD COLUMN IF NOT EXISTS resume_data MEDIUMBLOB DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS resume_mime VARCHAR(100) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS degree VARCHAR(150) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS university VARCHAR(180) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS stream VARCHAR(150) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS profession VARCHAR(150) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS projects TEXT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS company VARCHAR(180) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS state VARCHAR(120) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS city VARCHAR(120) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS profile_photo_data MEDIUMBLOB DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS profile_photo_mime VARCHAR(100) DEFAULT NULL;

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS phone VARCHAR(30) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS status ENUM('active', 'blocked', 'pending') NOT NULL DEFAULT 'active',
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE employer_profiles
    ADD COLUMN IF NOT EXISTS company_name VARCHAR(180) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS company_logo VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS company_website VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS company_description TEXT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS approved TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE jobs
    ADD COLUMN IF NOT EXISTS salary_min DECIMAL(12,2) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS salary_max DECIMAL(12,2) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS experience_level VARCHAR(80) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS deadline DATE DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS status ENUM('pending', 'approved', 'rejected', 'closed') NOT NULL DEFAULT 'pending',
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE applications
    ADD COLUMN IF NOT EXISTS resume_path VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS resume_data MEDIUMBLOB DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS resume_mime VARCHAR(100) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS cover_letter TEXT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS status ENUM('applied', 'shortlisted', 'rejected', 'hired') NOT NULL DEFAULT 'applied',
    ADD COLUMN IF NOT EXISTS applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE saved_jobs
    ADD COLUMN IF NOT EXISTS saved_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE contact_messages
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

INSERT IGNORE INTO job_categories (name) VALUES
('IT'), ('Marketing'), ('Sales'), ('Design'), ('Finance'), ('Customer Support');

INSERT INTO employer_job_quotas (employer_id, post_limit, posts_used)
SELECT u.id, 30, (SELECT COUNT(*) FROM jobs j WHERE j.employer_id = u.id)
FROM users u
WHERE u.role = 'employer'
ON DUPLICATE KEY UPDATE posts_used = VALUES(posts_used);
