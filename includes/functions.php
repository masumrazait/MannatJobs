<?php
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
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

function uploadFile($file, $targetDir, $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png'], $maxSize = 2097152)
{
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded or upload failed.'];
    }

    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File exceeds the size limit.'];
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedTypes, true)) {
        return ['success' => false, 'message' => 'Only PDF, JPG, JPEG, and PNG files are allowed.'];
    }

    $safeName = time() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', basename($file['name']));
    $destination = rtrim($targetDir, '/') . '/' . $safeName;

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0775, true);
    }

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'path' => $destination];
    }

    return ['success' => false, 'message' => 'The file could not be saved.'];
}

function userInitial($name)
{
    $name = trim((string)$name);
    return $name !== '' ? strtoupper(substr($name, 0, 1)) : '?';
}

function uploadRelativePath($absolutePath)
{
    return str_replace('\\', '/', str_replace(__DIR__ . '/../', '', $absolutePath));
}

function uploadedFileUrl($relativePath)
{
    if (!$relativePath) {
        return null;
    }

    $absolutePath = __DIR__ . '/../' . ltrim(str_replace('\\', '/', $relativePath), '/');
    return is_file($absolutePath) ? url($relativePath) : null;
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
