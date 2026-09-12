<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$pageTitle = 'Admin Dashboard';

$totalUsers = $conn->query('SELECT COUNT(*) AS total FROM users')->fetch_assoc()['total'];
$totalJobs = $conn->query('SELECT COUNT(*) AS total FROM jobs')->fetch_assoc()['total'];
$totalApplications = $conn->query('SELECT COUNT(*) AS total FROM applications')->fetch_assoc()['total'];
$pendingEmployers = $conn->query('SELECT COUNT(*) AS total FROM users u INNER JOIN employer_profiles ep ON ep.user_id = u.id WHERE u.role = "employer" AND u.status = "pending"')->fetch_assoc()['total'];
$pendingJobs = $conn->query('SELECT COUNT(*) AS total FROM jobs WHERE status = "pending"')->fetch_assoc()['total'];
$pendingQuotaRequests = $conn->query("SELECT COUNT(*) AS total FROM job_post_quota_requests WHERE status = 'pending'")->fetch_assoc()['total'];

$recentJobs = $conn->query('SELECT j.*, c.name AS category_name, u.name AS employer_name FROM jobs j INNER JOIN job_categories c ON c.id = j.category_id INNER JOIN users u ON u.id = j.employer_id ORDER BY j.created_at DESC LIMIT 5');
$pendingEmployerList = $conn->query('SELECT u.*, ep.company_name FROM users u INNER JOIN employer_profiles ep ON ep.user_id = u.id WHERE u.role = "employer" AND u.status = "pending" ORDER BY u.created_at DESC LIMIT 5');

include __DIR__ . '/../includes/header.php';
?>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <a href="<?php echo e(url('admin/users.php')); ?>" class="stat-box d-block text-decoration-none text-reset">
            <h3><?php echo (int)$totalUsers; ?></h3>
            <p class="mb-0 text-muted">Total Users</p>
        </a>
    </div>
    <div class="col-md-3">
        <a href="<?php echo e(url('admin/jobs.php')); ?>" class="stat-box d-block text-decoration-none text-reset">
            <h3><?php echo (int)$totalJobs; ?></h3>
            <p class="mb-0 text-muted">Jobs</p>
        </a>
    </div>
    <div class="col-md-3">
        <a href="<?php echo e(url('admin/applications.php')); ?>" class="stat-box d-block text-decoration-none text-reset">
            <h3><?php echo (int)$totalApplications; ?></h3>
            <p class="mb-0 text-muted">Applications</p>
        </a>
    </div>
    <div class="col-md-3">
        <a href="<?php echo e(url('admin/jobs.php?status=pending')); ?>" class="stat-box d-block text-decoration-none text-reset">
            <h3><?php echo (int)$pendingJobs; ?></h3>
            <p class="mb-0 text-muted">Pending Jobs</p>
        </a>
    </div>
    <div class="col-md-3">
        <a href="<?php echo e(url('admin/quota-requests.php')); ?>" class="stat-box d-block text-decoration-none text-reset">
            <h3><?php echo (int)$pendingQuotaRequests; ?></h3>
            <p class="mb-0 text-muted">Quota Requests</p>
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="card p-4">
            <div class="d-flex flex-wrap gap-2">
                <a href="<?php echo e(url('admin/jobs.php')); ?>" class="btn btn-outline-primary">Manage Jobs</a>
                <a href="<?php echo e(url('admin/post-job.php')); ?>" class="btn btn-primary">Post Public Job</a>
                <a href="<?php echo e(url('admin/quota-requests.php')); ?>" class="btn btn-outline-primary">Employer Quota Requests</a>
                <a href="<?php echo e(url('admin/users.php')); ?>" class="btn btn-outline-primary">Manage Users</a>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-4">
            <h4 class="fw-bold mb-3">Pending Employers</h4>
            <?php if ($pendingEmployerList->num_rows > 0): ?>
                <ul class="list-group list-group-flush">
                    <?php while ($row = $pendingEmployerList->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo e($row['company_name']); ?></strong><br>
                                <small class="text-muted"><?php echo e($row['email']); ?></small>
                            </div>
                            <a href="<?php echo e(url('admin/users.php')); ?>" class="btn btn-sm btn-outline-primary">Manage</a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted mb-0">No pending employer accounts.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card p-4">
            <h4 class="fw-bold mb-3">Recent Job Posts</h4>
            <?php if ($recentJobs->num_rows > 0): ?>
                <ul class="list-group list-group-flush">
                    <?php while ($job = $recentJobs->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo e($job['title']); ?></strong><br>
                                <small class="text-muted"><?php echo e($job['employer_name']); ?> / <?php echo e($job['category_name']); ?></small>
                            </div>
                            <span class="status-pill status-<?php echo e($job['status']); ?>"><?php echo e(ucfirst($job['status'])); ?></span>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted mb-0">No jobs have been posted yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
