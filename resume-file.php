<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0 || !in_array($type, ['profile', 'application'], true)) {
    http_response_code(404);
    exit;
}

if ($type === 'profile') {
    if (!isLoggedIn()) {
        http_response_code(403);
        exit;
    }
    $stmt = $conn->prepare('SELECT resume_data, resume_mime FROM jobseeker_profiles WHERE user_id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
} else {
    $stmt = $conn->prepare('SELECT a.resume_data, a.resume_mime, a.jobseeker_id, j.employer_id FROM applications a INNER JOIN jobs j ON j.id = a.job_id WHERE a.id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
}
$stmt->execute();
$resume = $stmt->get_result()->fetch_assoc();

if ($type === 'profile') {
    $canView = $_SESSION['user_role'] === 'admin' || (int)$_SESSION['user_id'] === $id;
} else {
    $canView = $resume && ($_SESSION['user_role'] === 'admin'
        || (int)$_SESSION['user_id'] === (int)$resume['jobseeker_id']
        || ($_SESSION['user_role'] === 'employer' && (int)$_SESSION['user_id'] === (int)$resume['employer_id']));
}

if (!$resume || !$canView) {
    http_response_code(403);
    exit;
}

if (!$resume || empty($resume['resume_data']) || $resume['resume_mime'] !== 'application/pdf') {
    http_response_code(404);
    exit;
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="resume.pdf"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: no-cache');
echo $resume['resume_data'];
