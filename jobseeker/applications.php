<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('jobseeker');

$applications = $conn->query('SELECT a.*, j.title, j.location FROM applications a INNER JOIN jobs j ON j.id = a.job_id WHERE a.jobseeker_id = ' . (int)$_SESSION['user_id'] . ' ORDER BY a.applied_at DESC');
$pageTitle = 'My Applications';
include __DIR__ . '/../includes/header.php';
?>

<div class="card p-4">
    <h3 class="fw-bold mb-3">My Applications</h3>

    <?php if ($applications->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Job</th>
                        <th>Location</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($app = $applications->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo e($app['title']); ?></td>
                            <td><?php echo e($app['location']); ?></td>
                            <td><span class="status-pill status-<?php echo e($app['status']); ?>"><?php echo e(getApplicationStatusLabel($app['status'])); ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p class="mb-0 text-muted">You have not applied to any jobs yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
