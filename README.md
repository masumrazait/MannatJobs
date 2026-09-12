# MannatJobs

MannatJobs is a complete job portal built with plain PHP, MySQL, HTML, CSS, Bootstrap 5, and vanilla JavaScript. It is designed to be uploaded directly to InfinityFree hosting without Composer or Node tooling.

## Default demo credentials

After importing the SQL file, the following default users are available:

- Admin: admin@mannatjobs.com / Admin@123
- Employer: employer@mannatjobs.com / Employer@123
- Job Seeker: jobseeker@mannatjobs.com / Seeker@123

## 1) Create MySQL database on InfinityFree

1. Log in to your InfinityFree account.
2. Open the cPanel or Control Panel.
3. Go to MySQL Databases.
4. Create a new database.
5. Create a MySQL user and assign it to the database.
6. Note the database name, username, password, and host (usually something like sqlXXX.infinityfree.com).

## 2) Update database credentials

Open the file `includes/db.php` and replace the placeholders:

```php
$host = 'localhost';
$dbName = 'mannatjobs';
$dbUser = 'root';
$dbPass = '';
```

Use your InfinityFree values, for example:

```php
$host = 'sql123.infinityfree.com';
$dbName = 'your_db_name';
$dbUser = 'your_db_user';
$dbPass = 'your_db_password';
```

## 3) Import the SQL file

1. Open phpMyAdmin from InfinityFree.
2. Select the database you created.
3. Click Import.
4. Upload `mannatjobs.sql`.
5. Confirm the tables and seed data are created.

For an existing MannatJobs installation, use `database/upgrade.sql` instead. It creates any missing application tables, adds the structured job seeker profile fields, and keeps existing records intact.

## 4) Upload the project files

Upload the project folder to your hosting account’s public directory (usually `htdocs` or the primary web root using File Manager or FTP client).

Folder structure:

```text
/htdocs/
  mannatjobs/
    admin/
    employer/
    jobseeker/
    includes/
    assets/
    uploads/
    index.php
    login.php
    register.php
    jobs.php
    job-details.php
    contact.php
    about.php
    logout.php
    404.php
    mannatjobs.sql
    README.md
```

## 5) File permissions and uploads

- Ensure the `uploads/` folders are writable by the web server.
- Use the default PHP uploads settings on InfinityFree.
- Keep file names sanitized and the upload size below the hosting limit.

## 6) Features included

- User registration/login for job seekers and employers
- Admin approval workflow
- Job listings, filters, search, and details page
- Resume upload support
- Reusable resumes and structured job seeker profiles
- Employer job posting and applicant management
- Saved job functionality
- Contact form storage
- Responsive Bootstrap layout

## 7) Security notes

- All database queries use prepared statements.
- Passwords are hashed using `password_hash()`.
- Session checks are used before accessing dashboard pages.
- All user input is sanitized and output is escaped.

## 8) Troubleshooting

- If the database does not connect, check the credentials in `includes/db.php`.
- If there is a 404 error, ensure the project is uploaded into the correct root folder.
- If pages do not load, verify the PHP version is 8.x and MySQL is enabled.

## 9) Project summary

This project is intentionally built using beginner-friendly, direct PHP so it can be hosted on shared hosting without build steps.
