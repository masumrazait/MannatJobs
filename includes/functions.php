<?php
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function formatIndianRupees($amount)
{
    if ($amount === null || $amount === '') {
        return 'Salary not disclosed';
    }

    $number = number_format((float)$amount, 0, '.', '');
    if (strlen($number) <= 3) {
        return '₹' . $number;
    }

    $lastThree = substr($number, -3);
    $remaining = substr($number, 0, -3);
    $remaining = preg_replace('/(?<=\d)(?=(\d{2})+$)/', ',', $remaining);
    return '₹' . $remaining . ',' . $lastThree;
}

function sanitize($value)
{
    if (is_array($value)) {
        return array_map('sanitize', $value);
    }

    if (is_string($value)) {
        return trim(strip_tags($value));
    }

    return $value;
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function appBasePath()
{
    static $basePath;

    if ($basePath !== null) {
        return $basePath;
    }

    $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $appRoot = realpath(__DIR__ . '/..');
    $basePath = '';

    if ($documentRoot && $appRoot) {
        $documentRoot = str_replace('\\', '/', rtrim($documentRoot, '\\/'));
        $appRoot = str_replace('\\', '/', $appRoot);
        if (str_starts_with(strtolower($appRoot), strtolower($documentRoot))) {
            $basePath = substr($appRoot, strlen($documentRoot));
        }
    }

    return rtrim($basePath, '/');
}

function url($path = '')
{
    $path = ltrim($path, '/');
    return appBasePath() . ($path === '' ? '/' : '/' . $path);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Please log in to continue.'];
        redirect('login.php');
        exit;
    }
}

function requireRole($role)
{
    requireLogin();

    if ($_SESSION['user_role'] !== $role) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'You do not have access to this page.'];
        redirect(($_SESSION['user_role'] === 'admin' ? 'admin/' : ($_SESSION['user_role'] === 'employer' ? 'employer/' : 'jobseeker/')) . 'index.php');
    }
}

function setFlash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function displayFlash()
{
    if (!isset($_SESSION['flash'])) {
        return;
    }

    $flash = $_SESSION['flash'];
    echo '<div class="alert alert-' . e($flash['type']) . ' alert-dismissible fade show" role="alert">';
    echo e($flash['message']);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
    unset($_SESSION['flash']);
}

function redirect($path)
{
    header('Location: ' . url($path));
    exit;
}

function isStrongPassword($password)
{
    return strlen($password) >= 8 && preg_match('/[A-Z]/', $password) && preg_match('/[a-z]/', $password) && preg_match('/[0-9]/', $password) && preg_match('/[^A-Za-z0-9]/', $password);
}

function userInitial($name)
{
    $name = trim((string)$name);
    return $name !== '' ? strtoupper(substr($name, 0, 1)) : '?';
}

function profileImageUrl($userId, $imageData = null, $legacyPath = null)
{
    if (!empty($imageData)) {
        return url('profile-image.php?id=' . (int)$userId);
    }

    return null;
}

function companyLogoUrl($userId, $imageData = null, $legacyPath = null)
{
    if (!empty($imageData)) {
        return url('company-logo.php?id=' . (int)$userId);
    }

    if (!empty($legacyPath)) {
        return url(ltrim($legacyPath, '/'));
    }

    return null;
}

function readUploadedProfileImage($file)
{
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No profile image uploaded or upload failed.'];
    }

    if ($file['size'] > 2 * 1024 * 1024 || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'message' => 'Profile image must be smaller than 2 MB.'];
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    $allowedMimes = ['image/jpeg', 'image/png'];
    if (!$imageInfo || !in_array($imageInfo['mime'], $allowedMimes, true)) {
        return ['success' => false, 'message' => 'Only JPG and PNG profile images are allowed.'];
    }

    $data = file_get_contents($file['tmp_name']);
    if ($data === false) {
        return ['success' => false, 'message' => 'The profile image could not be read.'];
    }

    return ['success' => true, 'data' => $data, 'mime' => $imageInfo['mime']];
}

function readUploadedResume($file)
{
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No resume uploaded or upload failed.'];
    }

    if ($file['size'] > 2 * 1024 * 1024 || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'message' => 'Resume must be smaller than 2 MB.'];
    }

    $mime = function_exists('mime_content_type') ? mime_content_type($file['tmp_name']) : $file['type'];
    if ($mime !== 'application/pdf' || strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'pdf') {
        return ['success' => false, 'message' => 'Only PDF resumes are allowed.'];
    }

    $data = file_get_contents($file['tmp_name']);
    if ($data === false) {
        return ['success' => false, 'message' => 'The resume could not be read.'];
    }

    return ['success' => true, 'data' => $data, 'mime' => 'application/pdf'];
}

function resumeUrl($ownerType, $id)
{
    return url('resume-file.php?type=' . rawurlencode($ownerType) . '&id=' . (int)$id);
}

function getUserNameById($conn, $id)
{
    $stmt = $conn->prepare('SELECT name FROM users WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? $row['name'] : 'Unknown';
}

function getCategoryName($conn, $categoryId)
{
    $stmt = $conn->prepare('SELECT name FROM job_categories WHERE id = ?');
    $stmt->bind_param('i', $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? $row['name'] : 'General';
}

function getJobCount($conn, $status = null)
{
    if ($status) {
        $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM jobs WHERE status = ?');
        $stmt->bind_param('s', $status);
    } else {
        $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM jobs');
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return (int)$row['total'];
}

function getApplicationStatusLabel($status)
{
    $labels = [
        'applied' => 'Applied',
        'shortlisted' => 'Shortlisted',
        'rejected' => 'Rejected',
        'hired' => 'Hired',
    ];

    return $labels[$status] ?? ucfirst($status);
}

function ensureEmployerQuota($conn, $employerId)
{
    $stmt = $conn->prepare('INSERT IGNORE INTO employer_job_quotas (employer_id, post_limit, posts_used) VALUES (?, 30, 0)');
    $stmt->bind_param('i', $employerId);
    $stmt->execute();

    $stmt = $conn->prepare('SELECT post_limit, posts_used FROM employer_job_quotas WHERE employer_id = ? LIMIT 1');
    $stmt->bind_param('i', $employerId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?: ['post_limit' => 30, 'posts_used' => 0];
}

function getEmployerQuota($conn, $employerId)
{
    $quota = ensureEmployerQuota($conn, $employerId);
    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM jobs WHERE employer_id = ?');
    $stmt->bind_param('i', $employerId);
    $stmt->execute();
    $used = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $update = $conn->prepare('UPDATE employer_job_quotas SET posts_used = ? WHERE employer_id = ?');
    $update->bind_param('ii', $used, $employerId);
    $update->execute();
    $quota['posts_used'] = $used;
    return $quota;
}

function paginationData($total, $page, $perPage = 25)
{
    $perPage = max(1, (int)$perPage);
    $total = max(0, (int)$total);
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page = min(max(1, (int)$page), $totalPages);

    return [
        'page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'total_pages' => $totalPages,
        'offset' => ($page - 1) * $perPage,
    ];
}

function paginationUrl($path, $params, $page)
{
    $params['page'] = $page;
    return url($path . '?' . http_build_query(array_filter($params, static function ($value) {
        return $value !== '' && $value !== null;
    })));
}

function paginationPages($pagination)
{
    $pages = [1, $pagination['page'] - 1, $pagination['page'], $pagination['page'] + 1, $pagination['total_pages']];
    $pages = array_filter(array_unique($pages), static function ($page) use ($pagination) {
        return $page >= 1 && $page <= $pagination['total_pages'];
    });
    sort($pages);
    return $pages;
}

function preparedQuery($conn, $sql, $types = '', $params = [])
{
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    if ($types !== '' && $params) {
        $references = [];
        foreach ($params as $key => $value) {
            $references[$key] = &$params[$key];
        }
        array_unshift($references, $types);
        call_user_func_array([$stmt, 'bind_param'], $references);
    }

    if (!$stmt->execute()) {
        return false;
    }

    return $stmt->get_result();
}
