<?php
require_once __DIR__ . '/includes/db.php';

$userId = (int)($_GET['id'] ?? 0);
if ($userId <= 0) {
    http_response_code(404);
    exit;
}

$stmt = $conn->prepare('SELECT profile_photo_data, profile_photo_mime FROM jobseeker_profiles WHERE user_id = ? LIMIT 1');
$stmt->bind_param('i', $userId);
$stmt->execute();
$image = $stmt->get_result()->fetch_assoc();

if (!$image || empty($image['profile_photo_data']) || empty($image['profile_photo_mime'])) {
    http_response_code(404);
    exit;
}

$allowedMimes = ['image/jpeg', 'image/png'];
if (!in_array($image['profile_photo_mime'], $allowedMimes, true)) {
    http_response_code(404);
    exit;
}

header('Content-Type: ' . $image['profile_photo_mime']);
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: no-cache');
echo $image['profile_photo_data'];
