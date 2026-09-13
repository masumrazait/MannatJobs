<?php
require_once __DIR__ . '/includes/db.php';

$userId = (int)($_GET['id'] ?? 0);
if ($userId <= 0) {
    http_response_code(404);
    exit;
}

$stmt = $conn->prepare('SELECT company_logo_data, company_logo_mime FROM employer_profiles WHERE user_id = ? LIMIT 1');
$stmt->bind_param('i', $userId);
$stmt->execute();
$logo = $stmt->get_result()->fetch_assoc();

$allowedMimes = ['image/jpeg', 'image/png'];
if (!$logo || empty($logo['company_logo_data']) || !in_array($logo['company_logo_mime'], $allowedMimes, true)) {
    http_response_code(404);
    exit;
}

header('Content-Type: ' . $logo['company_logo_mime']);
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: no-cache');
echo $logo['company_logo_data'];