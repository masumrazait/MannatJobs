<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('employer');

$pageTitle = 'Employer Dashboard';

$jobs = $conn->query('SELECT * FROM jobs WHERE employer_id = ' . (int)$_SESSION['user_id'] . ' ORDER BY created_at DESC LIMIT 5');
$totalJobs = $conn->query('SELECT COUNT(*) AS total FROM jobs WHERE employer_id = ' . (int)$_SESSION['user_id'])->fetch_assoc()['total'];
$totalApplicants = $conn->query('SELECT COUNT(*) AS total FROM applications a INNER JOIN jobs j ON j.id = a.job_id WHERE j.employer_id = ' . (int)$_SESSION['user_id'])->fetch_assoc()['total'];
$pendingJobs = $conn->query('SELECT COUNT(*) AS total FROM jobs WHERE employer_id = ' . (int)$_SESSION['user_id'] . ' AND status = "pending"')->fetch_assoc()['total'];
$quota = getEmployerQuota($conn, (int)$_SESSION['user_id']);
$remainingPosts = max(0, (int)$quota['post_limit'] - (int)$quota['posts_used']);

include __DIR__ . '/../includes/header.php';
?>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-box">
            <h3><?php echo (int)$totalJobs; ?></h3>
            <p class="mb-0 text-muted">Posted Jobs</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-box">
            <h3><?php echo (int)$totalApplicants; ?></h3>
            <p class="mb-0 text-muted">Applicants</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-box">
            <h3><?php echo (int)$pendingJobs; ?></h3>
            <p class="mb-0 text-muted">Pending Review</p>
        </div>
    </div>
</div>

<div class="card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Job posting access</h4>
            <p class="text-muted mb-0"><?php echo (int)$quota['posts_used']; ?> of <?php echo (int)$quota['post_limit']; ?> posts used. <?php echo $remainingPosts; ?> remaining.</p>
        </div>
        <?php if ($remainingPosts > 0): ?><a href="<?php echo e(url('employer/post-job.php')); ?>" class="btn btn-primary">Post Job</a><?php endif; ?>
        <a href="<?php echo e(url('employer/quota-request.php')); ?>" class="btn btn-outline-primary">Manage Quota</a>
    </div>
</div>

<div class="card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Company profile</h4>
            <p class="text-muted mb-0">Update your company logo, description, and website.</p>
        </div>
        <a href="<?php echo e(url('employer/profile.php')); ?>" class="btn btn-outline-primary">Manage Company Profile</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card p-4">
            <h4 class="fw-bold mb-3">Quick Actions</h4>
            <div class="d-grid gap-2">
                <a href="<?php echo e(url('employer/post-job.php')); ?>" class="btn btn-primary">Post a New Job</a>
                <a href="<?php echo e(url('employer/jobs.php')); ?>" class="btn btn-outline-primary">Manage Jobs</a>
                <a href="<?php echo e(url('employer/applicants.php')); ?>" class="btn btn-outline-primary">View Applicants</a>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card p-4">
            <h4 class="fw-bold mb-3">Recent Jobs</h4>
            <?php if ($jobs->num_rows > 0): ?>
                <ul class="list-group list-group-flush">
                    <?php while ($job = $jobs->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo e($job['title']); ?></strong><br>
                                <small class="text-muted"><?php echo e($job['location']); ?></small>
                            </div>
                            <span class="status-pill status-<?php echo e($job['status']); ?>"><?php echo e(ucfirst($job['status'])); ?></span>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted mb-0">No jobs posted yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
