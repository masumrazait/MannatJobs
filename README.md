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

## Support and Maintenance

For an operational issue, record the affected user role, page, timestamp, and visible error message. For database or deployment issues, include the environment and migration version while excluding all credentials and confidential data.
