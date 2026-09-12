<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$applications = $conn->query('SELECT a.*, j.title AS job_title, eu.name AS employer_name, cu.name AS candidate_name, cu.email AS candidate_email FROM applications a INNER JOIN jobs j ON j.id = a.job_id INNER JOIN users eu ON eu.id = j.employer_id INNER JOIN users cu ON cu.id = a.jobseeker_id ORDER BY a.applied_at DESC');
$pageTitle = 'All Applications';
include __DIR__ . '/../includes/header.php';
?>

<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><div><h3 class="fw-bold mb-1">All Applications</h3><p class="text-muted mb-0">Review application activity across every employer.</p></div><a href="<?php echo e(url('admin/index.php')); ?>" class="btn btn-outline-primary btn-sm">Back to Dashboard</a></div>
    <div class="table-responsive">
        <table class="table table-striped align-middle"><thead><tr><th>Candidate</th><th>Job</th><th>Employer</th><th>Status</th><th>Applied</th></tr></thead><tbody>
        <?php while ($application = $applications->fetch_assoc()): ?>
            <tr><td><strong><?php echo e($application['candidate_name']); ?></strong><br><small class="text-muted"><?php echo e($application['candidate_email']); ?></small></td><td><?php echo e($application['job_title']); ?></td><td><?php echo e($application['employer_name']); ?></td><td><span class="status-pill status-<?php echo e($application['status']); ?>"><?php echo e(getApplicationStatusLabel($application['status'])); ?></span></td><td><?php echo e(date('M j, Y', strtotime($application['applied_at']))); ?></td></tr>
        <?php endwhile; ?>
        </tbody></table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
