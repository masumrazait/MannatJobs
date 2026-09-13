<?php
$host = 'localhost';
$dbName = 'mannatjobs';
$dbUser = 'root';
$dbPass = '';

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);

if ($conn->connect_error) {
    die('Database connection failed. Please create the database and update the credentials in includes/db.php.');
}

$conn->set_charset('utf8mb4');

$conn->query("CREATE TABLE IF NOT EXISTS employer_job_quotas (
    employer_id INT UNSIGNED PRIMARY KEY,
    post_limit INT UNSIGNED NOT NULL DEFAULT 30,
    posts_used INT UNSIGNED NOT NULL DEFAULT 0,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_runtime_quota_employer FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$conn->query("CREATE TABLE IF NOT EXISTS job_post_quota_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employer_id INT UNSIGNED NOT NULL,
    requested_posts INT UNSIGNED NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    admin_id INT UNSIGNED DEFAULT NULL,
    admin_note VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_runtime_request_employer FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_runtime_request_admin FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_runtime_quota_status (status),
    INDEX idx_runtime_quota_employer (employer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$conn->query("INSERT INTO employer_job_quotas (employer_id, post_limit, posts_used)
    SELECT u.id, 30, (SELECT COUNT(*) FROM jobs j WHERE j.employer_id = u.id)
    FROM users u WHERE u.role = 'employer'
    ON DUPLICATE KEY UPDATE posts_used = VALUES(posts_used)");

$employerProfileColumns = [
    'company_logo_data' => 'MEDIUMBLOB DEFAULT NULL',
    'company_logo_mime' => 'VARCHAR(100) DEFAULT NULL',
];
$employerColumnsResult = $conn->query("SHOW COLUMNS FROM employer_profiles");
$existingEmployerColumns = [];
if ($employerColumnsResult) {
    while ($column = $employerColumnsResult->fetch_assoc()) {
        $existingEmployerColumns[$column['Field']] = true;
    }
}

foreach ($employerProfileColumns as $columnName => $definition) {
    if (!isset($existingEmployerColumns[$columnName])) {
        $conn->query("ALTER TABLE employer_profiles ADD COLUMN `" . $conn->real_escape_string($columnName) . "` " . $definition);
    }
}

// Keep existing installations compatible with the expanded job seeker profile.
$profileColumns = [
    'resume_data' => 'MEDIUMBLOB DEFAULT NULL',
    'resume_mime' => 'VARCHAR(100) DEFAULT NULL',
    'degree' => 'VARCHAR(150) DEFAULT NULL',
    'university' => 'VARCHAR(180) DEFAULT NULL',
    'stream' => 'VARCHAR(150) DEFAULT NULL',
    'profession' => 'VARCHAR(150) DEFAULT NULL',
    'projects' => 'TEXT DEFAULT NULL',
    'company' => 'VARCHAR(180) DEFAULT NULL',
    'state' => 'VARCHAR(120) DEFAULT NULL',
    'city' => 'VARCHAR(120) DEFAULT NULL',
    'profile_photo_data' => 'MEDIUMBLOB DEFAULT NULL',
    'profile_photo_mime' => 'VARCHAR(100) DEFAULT NULL',
];

$columnsResult = $conn->query("SHOW COLUMNS FROM jobseeker_profiles");
$existingColumns = [];
if ($columnsResult) {
    while ($column = $columnsResult->fetch_assoc()) {
        $existingColumns[$column['Field']] = true;
    }
}

foreach ($profileColumns as $columnName => $definition) {
    if (!isset($existingColumns[$columnName])) {
        $conn->query("ALTER TABLE jobseeker_profiles ADD COLUMN `" . $conn->real_escape_string($columnName) . "` " . $definition);
    }
}

$applicationColumns = [
    'resume_data' => 'MEDIUMBLOB DEFAULT NULL',
    'resume_mime' => 'VARCHAR(100) DEFAULT NULL',
];
$applicationColumnsResult = $conn->query("SHOW COLUMNS FROM applications");
$existingApplicationColumns = [];
if ($applicationColumnsResult) {
    while ($column = $applicationColumnsResult->fetch_assoc()) {
        $existingApplicationColumns[$column['Field']] = true;
    }
}

foreach ($applicationColumns as $columnName => $definition) {
    if (!isset($existingApplicationColumns[$columnName])) {
        $conn->query("ALTER TABLE applications ADD COLUMN `" . $conn->real_escape_string($columnName) . "` " . $definition);
    }
}

// Move legacy file uploads into the database before removing their local copies.
$legacyFiles = [];
$applicationResult = $conn->query("SELECT id, resume_path FROM applications WHERE resume_path IS NOT NULL");
if ($applicationResult) {
    while ($row = $applicationResult->fetch_assoc()) {
        $legacyFiles[$row['resume_path']] = true;
        $absolutePath = realpath(__DIR__ . '/../' . ltrim(str_replace('\\', '/', $row['resume_path']), '/'));
        $mime = function_exists('mime_content_type') && $absolutePath && is_file($absolutePath) ? mime_content_type($absolutePath) : null;
        if (!$absolutePath || !is_file($absolutePath) || $mime !== 'application/pdf') {
            continue;
        }
        $data = file_get_contents($absolutePath);
        $mime = 'application/pdf';
        $migrationStmt = $conn->prepare('UPDATE applications SET resume_data = ?, resume_mime = ?, resume_path = NULL WHERE id = ?');
        $migrationStmt->bind_param('bsi', $data, $mime, $row['id']);
        $migrationStmt->send_long_data(0, $data);
        $migrationStmt->execute();
    }
}

$profileResult = $conn->query("SELECT user_id, resume_path, profile_photo FROM jobseeker_profiles WHERE resume_path IS NOT NULL OR profile_photo IS NOT NULL");
if ($profileResult) {
    while ($row = $profileResult->fetch_assoc()) {
        foreach (['resume_path' => 'application/pdf', 'profile_photo' => null] as $pathColumn => $expectedMime) {
            $path = $row[$pathColumn] ?? null;
            if (!$path) {
                continue;
            }
            $legacyFiles[$path] = true;
            $absolutePath = realpath(__DIR__ . '/../' . ltrim(str_replace('\\', '/', $path), '/'));
            if (!$absolutePath || !is_file($absolutePath)) {
                continue;
            }
            $mime = function_exists('mime_content_type') ? mime_content_type($absolutePath) : null;
            if (($expectedMime && $mime !== $expectedMime) || (!$expectedMime && !in_array($mime, ['image/jpeg', 'image/png'], true))) {
                continue;
            }
            $data = file_get_contents($absolutePath);
            if ($data === false) {
                continue;
            }
            if ($pathColumn === 'resume_path') {
                $migrationStmt = $conn->prepare('UPDATE jobseeker_profiles SET resume_data = ?, resume_mime = ?, resume_path = NULL WHERE user_id = ?');
                $migrationStmt->bind_param('bsi', $data, $mime, $row['user_id']);
            } else {
                $migrationStmt = $conn->prepare('UPDATE jobseeker_profiles SET profile_photo_data = ?, profile_photo_mime = ?, profile_photo = NULL WHERE user_id = ?');
                $migrationStmt->bind_param('bsi', $data, $mime, $row['user_id']);
            }
            $migrationStmt->send_long_data(0, $data);
            $migrationStmt->execute();
        }
    }
}

foreach (array_keys($legacyFiles) as $legacyPath) {
    $absolutePath = realpath(__DIR__ . '/../' . ltrim(str_replace('\\', '/', $legacyPath), '/'));
    $referenceStmt = $conn->prepare('SELECT (SELECT COUNT(*) FROM applications WHERE resume_path = ?) + (SELECT COUNT(*) FROM jobseeker_profiles WHERE resume_path = ? OR profile_photo = ?) AS total_references');
    $referenceStmt->bind_param('sss', $legacyPath, $legacyPath, $legacyPath);
    $referenceStmt->execute();
    $references = (int)($referenceStmt->get_result()->fetch_assoc()['total_references'] ?? 1);
    if ($absolutePath && is_file($absolutePath) && $references === 0) {
        @unlink($absolutePath);
    }
}
?>
