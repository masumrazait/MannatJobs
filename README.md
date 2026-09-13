# MannatJobs

MannatJobs is a role-based recruitment platform that connects employers with job seekers through a structured hiring workflow. The application provides job discovery, employer publishing, candidate profiles, applications, and administrative oversight in one web experience.

## Product Scope

The platform supports the complete journey from account creation to application management:

- Public browsing of approved job opportunities
- Account registration and secure login
- Job seeker profiles, resumes, and saved jobs
- Employer company profiles and job publishing
- Candidate application tracking and status management
- Employer posting quotas and quota requests
- Administrative review of users, jobs, applications, and quota requests
- Contact message collection

The application uses PHP, MySQL, HTML, CSS, Bootstrap, and vanilla JavaScript. It is designed to run in a standard PHP and MySQL environment without a build pipeline.

## Getting Started

### Prerequisites

Before starting, provide the application with:

- PHP 8.x or a compatible supported PHP runtime
- MySQL or MariaDB
- A web server capable of serving PHP applications
- PHP extensions required by `mysqli`, file uploads, and password hashing

### Installation

1. Create an empty database in the target environment.
2. Configure the application database connection using the environment's approved configuration process. Keep connection details outside public documentation and source control.
3. Import `mannatjobs.sql` for a new installation.
4. Use `database/upgrade.sql` when upgrading an existing installation so existing records can be retained.
5. Confirm that the application can connect to the database and that the web server can process PHP requests.
6. Review upload and session settings for the target environment before making the application available to users.

The SQL scripts create the required schema and initial application data. They should be executed only by an authorized operator and against the intended database.

## User Workflows

### Registration and Login

1. Open the registration page and provide a name, email address, phone number, password, and account type.
2. Choose either **Job Seeker** or **Employer**. Administrative accounts are managed separately.
3. Submit the form. The system validates the information and creates the corresponding profile.
4. Employers remain pending until approved by an administrator. Job seekers can sign in after registration unless their account is restricted.
5. Use the login page with the registered email address and password. Successful login routes the user to the dashboard for their role.
6. Use logout to end the current session.

### Job Seeker Workflow

Job seekers can:

1. Complete their professional profile, including skills, education, experience, location, and profile image.
2. Upload or maintain a resume.
3. Browse, search, and filter approved job listings.
4. Open a job detail page and submit one application per job.
5. Include a cover letter and track application status from the applications dashboard.
6. Save jobs for later review.

### Employer Workflow

Employers can:

1. Register an employer account and wait for administrative approval.
2. Complete company information, including company name, website, description, and logo.
3. Create job postings with category, description, requirements, location, employment type, salary range, experience level, and deadline.
4. Monitor the review status of submitted jobs. Approved jobs become visible to job seekers.
5. Review applications received for published jobs.
6. Update application outcomes such as shortlisted, rejected, or hired.
7. Monitor the available posting quota and submit a quota request when additional capacity is required.

### Administrative Workflow

Administrators oversee platform operations by reviewing employer accounts, moderating job postings, managing users, reviewing applications, and processing employer quota requests. Administrative access should be provisioned through the organization's controlled account-management process.

## Database Model

The database is organized around users, role-specific profiles, jobs, and hiring activity.

### Core Entities

- **users**: Identity, contact information, role, account status, and authentication data.
- **jobseeker_profiles**: Professional details, education, experience, location, resume information, and profile media for job seekers.
- **employer_profiles**: Company identity, website, description, branding, and approval state for employers.
- **job_categories**: Controlled categories used to classify job postings.
- **jobs**: Job descriptions, requirements, location, employment type, compensation range, deadline, employer, category, and moderation status.
- **applications**: The relationship between job seekers and jobs, including resume data, cover letters, timestamps, and hiring status.
- **saved_jobs**: Job seeker bookmarks for later review.
- **employer_job_quotas**: Posting limits and usage counters for employers.
- **job_post_quota_requests**: Requests for additional employer posting capacity and their administrative decisions.
- **contact_messages**: Messages submitted through the public contact channel.

### Relationships

- A user has one role and may have one job seeker profile or employer profile.
- An employer can publish many jobs, subject to approval and quota rules.
- Each job belongs to one employer and one category.
- A job seeker can submit applications to many jobs, while each job can receive applications from many job seekers.
- A job seeker can save many jobs, and a job can be saved by many job seekers.
- User-owned records are linked with foreign keys and are removed or restricted according to their business relationship.

## Operational Notes

- Keep database connection settings, production account information, and credentials in the deployment environment's secure configuration.
- Do not commit passwords, private keys, production exports, or other confidential material to the repository.
- Use the upgrade script for existing installations and back up the database before applying schema changes.
- Ensure the application can write uploaded resumes and profile media where the deployment requires file storage.
- Review PHP, database, upload, session, and web-server settings according to the organization's operational standards.

## Support and Maintenance

For an operational issue, record the affected user role, page, timestamp, and visible error message. For database or deployment issues, include the environment and migration version while excluding all credentials and confidential data.
