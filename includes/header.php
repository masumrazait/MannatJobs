<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$currentPath = $_SERVER['PHP_SELF'] ?? '/index.php';
$role = $_SESSION['user_role'] ?? null;
$user = $_SESSION['user_name'] ?? null;
$loggedIn = isLoggedIn();
$avatarUrl = null;
$accountCount = 0;
if ($loggedIn && $role === 'jobseeker') {
    $avatarStmt = $conn->prepare('SELECT profile_photo FROM jobseeker_profiles WHERE user_id = ? LIMIT 1');
    $avatarStmt->bind_param('i', $_SESSION['user_id']);
    $avatarStmt->execute();
    $avatar = $avatarStmt->get_result()->fetch_assoc();
    $avatarUrl = uploadedFileUrl($avatar['profile_photo'] ?? null);
    $countStmt = $conn->prepare('SELECT COUNT(*) AS total FROM applications WHERE jobseeker_id = ?');
    $countStmt->bind_param('i', $_SESSION['user_id']);
    $countStmt->execute();
    $accountCount = (int)($countStmt->get_result()->fetch_assoc()['total'] ?? 0);
} elseif ($loggedIn && $role === 'employer') {
    $countStmt = $conn->prepare('SELECT COUNT(*) AS total FROM applications a INNER JOIN jobs j ON j.id = a.job_id WHERE j.employer_id = ?');
    $countStmt->bind_param('i', $_SESSION['user_id']);
    $countStmt->execute();
    $accountCount = (int)($countStmt->get_result()->fetch_assoc()['total'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' | MannatJobs' : 'MannatJobs'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(url('assets/css/style.css')); ?>">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark site-nav shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo e(url('index.php')); ?>">MannatJobs</a>
        <button class="navbar-toggler main-nav-toggler <?php echo $loggedIn ? 'd-none' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-label="Open navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link <?php echo strpos($currentPath, '/index.php') !== false ? 'active' : ''; ?>" href="<?php echo e(url('index.php')); ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link <?php echo strpos($currentPath, '/jobs.php') !== false ? 'active' : ''; ?>" href="<?php echo e(url('jobs.php')); ?>">Jobs</a></li>
                <li class="nav-item"><a class="nav-link <?php echo strpos($currentPath, '/about.php') !== false ? 'active' : ''; ?>" href="<?php echo e(url('about.php')); ?>">About</a></li>
                <li class="nav-item"><a class="nav-link <?php echo strpos($currentPath, '/contact.php') !== false ? 'active' : ''; ?>" href="<?php echo e(url('contact.php')); ?>">Contact</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <?php if ($loggedIn): ?>
                    <?php
                    $dashboardPath = url('login.php');
                    if ($role === 'admin') { $dashboardPath = url('admin/index.php'); }
                    elseif ($role === 'employer') { $dashboardPath = url('employer/index.php'); }
                    elseif ($role === 'jobseeker') { $dashboardPath = url('jobseeker/index.php'); }
                    ?>
                    <div class="account-shell">
                        <button class="account-menu-button" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-label="Open navigation">
                            <span></span><span></span><span></span>
                        </button>
                        <a href="<?php echo $dashboardPath; ?>" class="account-link" aria-label="Open <?php echo e($user); ?> profile">
                            <span class="account-avatar-wrap">
                                <?php if ($avatarUrl): ?>
                                    <img src="<?php echo e($avatarUrl); ?>" class="account-avatar" alt="<?php echo e($user); ?>">
                                <?php else: ?>
                                    <span class="account-avatar account-initial" aria-hidden="true"><?php echo e(userInitial($user)); ?></span>
                                <?php endif; ?>
                                <?php if ($accountCount > 0): ?><span class="account-badge"><?php echo $accountCount > 99 ? '99+' : (int)$accountCount; ?></span><?php endif; ?>
                            </span>
                            <span class="account-name d-none d-sm-inline"><?php echo e($user); ?></span>
                        </a>
                    </div>
                    <a href="<?php echo $dashboardPath; ?>" class="btn btn-light btn-sm">Dashboard</a>
                    <a href="<?php echo e(url('logout.php')); ?>" class="btn btn-outline-light btn-sm">Logout</a>
                <?php else: ?>
                    <a href="<?php echo e(url('login.php')); ?>" class="btn btn-light btn-sm">Login</a>
                    <a href="<?php echo e(url('register.php')); ?>" class="btn btn-outline-light btn-sm">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<main class="py-4">
    <div class="container">
        <?php displayFlash(); ?>
