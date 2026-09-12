<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    setFlash('danger', 'Invalid job id.');
    redirect('jobs.php');
}

$stmt = $conn->prepare('SELECT j.*, c.name AS category_name, u.name AS employer_name, ep.company_name, ep.company_logo, ep.company_website, ep.company_description FROM jobs j INNER JOIN job_categories c ON c.id = j.category_id INNER JOIN users u ON u.id = j.employer_id LEFT JOIN employer_profiles ep ON ep.user_id = u.id WHERE j.id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();

if ($job && $job['status'] !== 'approved') {
    $canViewPrivateJob = isLoggedIn() && (
        $_SESSION['user_role'] === 'admin' ||
        ($_SESSION['user_role'] === 'employer' && (int)$_SESSION['user_id'] === (int)$job['employer_id'])
    );
    if (!$canViewPrivateJob) {
        $job = null;
    }
}

if (!$job) {
    header('HTTP/1.0 404 Not Found');
    include __DIR__ . '/404.php';
    exit;
}

$pageTitle = $job['title'];
include __DIR__ . '/includes/header.php';
?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                <div>
                    <h2 class="fw-bold mb-1"><?php echo e($job['title']); ?></h2>
                    <p class="mb-0 text-muted"><?php echo e($job['company_name'] ?: $job['employer_name']); ?> • <?php echo e($job['location']); ?></p>
                </div>
                <span class="job-badge bg-primary text-white"><?php echo e($job['job_type']); ?></span>
            </div>

            <?php if ($job['salary_min'] !== null): ?>
                <p class="text-muted mb-4"><strong>Salary:</strong> <?php echo e(formatIndianRupees($job['salary_min']) . ' - ' . formatIndianRupees($job['salary_max'])); ?></p>
            <?php endif; ?>

            <?php if ($job['status'] !== 'approved'): ?>
                <div class="alert alert-warning" role="status">
                    This listing is currently <?php echo e($job['status']); ?> and is not visible to public job seekers.
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <h5 class="fw-bold">Job Overview</h5>
                <p><?php echo nl2br(e($job['description'])); ?></p>
            </div>

            <div class="mb-4">
                <h5 class="fw-bold">Requirements</h5>
                <p><?php echo nl2br(e($job['requirements'])); ?></p>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <?php if (isLoggedIn() && $_SESSION['user_role'] === 'jobseeker'): ?>
                    <a href="<?php echo e(url('jobseeker/apply.php?job_id=' . (int)$job['id'])); ?>" class="btn btn-primary">Apply Now</a>
                <?php elseif (!isLoggedIn()): ?>
                    <a href="<?php echo e(url('login.php')); ?>" class="btn btn-primary">Login to Apply</a>
                <?php else: ?>
                    <button class="btn btn-secondary" disabled>Apply Now</button>
                <?php endif; ?>
                <a href="<?php echo e(url('jobs.php')); ?>" class="btn btn-outline-primary">Back to Jobs</a>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card p-4">
            <h4 class="fw-bold mb-3">Company Details</h4>
            <div class="d-flex align-items-center gap-3 mb-3">
                <img src="<?php echo e(url('assets/images/logo-placeholder.svg')); ?>" alt="Company logo" class="company-logo">
                <div>
                    <h5 class="mb-1"><?php echo e($job['company_name'] ?: $job['employer_name']); ?></h5>
                    <small class="text-muted"><?php echo e($job['location']); ?></small>
                </div>
            </div>
            <p><?php echo nl2br(e($job['company_description'] ?: 'Company profile not added yet.')); ?></p>
            <?php if (!empty($job['company_website'])): ?>
                <a href="<?php echo e($job['company_website']); ?>" class="btn btn-outline-primary w-100" target="_blank">Visit Website</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
