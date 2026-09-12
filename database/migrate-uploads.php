<?php
if (PHP_SAPI !== 'cli') {
    exit("Run this migration from the command line.\n");
}

require_once __DIR__ . '/../includes/db.php';

function migrateFile($conn, $table, $idColumn, $id, $path, $dataColumn, $mimeColumn)
{
    $absolutePath = realpath(__DIR__ . '/../' . ltrim(str_replace('\\', '/', $path), '/'));
    if (!$absolutePath || !is_file($absolutePath)) {
        return false;
    }

    $data = file_get_contents($absolutePath);
    if ($data === false) {
        return false;
    }

    $mime = mime_content_type($absolutePath);
    $allowedMime = $table === 'applications' ? 'application/pdf' : (in_array($mime, ['image/jpeg', 'image/png'], true) ? $mime : null);
    if ($table === 'applications' && $mime !== 'application/pdf') {
        return false;
    }
    if ($table === 'jobseeker_profiles' && !$allowedMime) {
        return false;
    }

    $sql = "UPDATE {$table} SET {$dataColumn} = ?, {$mimeColumn} = ?, " . ($table === 'applications' ? 'resume_path' : ($dataColumn === 'resume_data' ? 'resume_path' : 'profile_photo')) . " = NULL WHERE {$idColumn} = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('bsi', $data, $allowedMime, $id);
    $stmt->send_long_data(0, $data);
    if (!$stmt->execute()) {
        return false;
    }

    return unlink($absolutePath);
}

$applicationResult = $conn->query("SELECT id, resume_path FROM applications WHERE resume_path IS NOT NULL");
while ($application = $applicationResult->fetch_assoc()) {
    migrateFile($conn, 'applications', 'id', (int)$application['id'], $application['resume_path'], 'resume_data', 'resume_mime');
}

$profileResult = $conn->query("SELECT user_id, resume_path, profile_photo FROM jobseeker_profiles WHERE resume_path IS NOT NULL OR profile_photo IS NOT NULL");
while ($profile = $profileResult->fetch_assoc()) {
    if (!empty($profile['resume_path'])) {
        migrateFile($conn, 'jobseeker_profiles', 'user_id', (int)$profile['user_id'], $profile['resume_path'], 'resume_data', 'resume_mime');
    }
    if (!empty($profile['profile_photo'])) {
        migrateFile($conn, 'jobseeker_profiles', 'user_id', (int)$profile['user_id'], $profile['profile_photo'], 'profile_photo_data', 'profile_photo_mime');
    }
}

echo "Upload migration completed.\n";