<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('employer');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $jobId = (int)$_POST['delete_id'];
    $stmt = $conn->prepare('DELETE FROM jobs WHERE id = ? AND employer_id = ?');
    $stmt->bind_param('ii', $jobId, $_SESSION['user_id']);
    if ($stmt->execute()) {
        setFlash('success', 'Job deleted successfully.');
    } else {
        setFlash('danger', 'Could not delete the job.');
    }
    redirect('employer/jobs.php');
}

$jobs = $conn->query('SELECT * FROM jobs WHERE employer_id = ' . (int)$_SESSION['user_id'] . ' ORDER BY created_at DESC');
$pageTitle = 'My Jobs';
include __DIR__ . '/../includes/header.php';
?>

<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">My Job Listings</h3>
        <a href="<?php echo e(url('employer/post-job.php')); ?>" class="btn btn-primary btn-sm">New Job</a>
    </div>

    <?php if ($jobs->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($job = $jobs->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo e($job['title']); ?></td>
                            <td><?php echo e($job['location']); ?></td>
                            <td><span class="status-pill status-<?php echo e($job['status']); ?>"><?php echo e(ucfirst($job['status'])); ?></span></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="<?php echo e(url('job-details.php?id=' . (int)$job['id'])); ?>" class="btn btn-sm btn-outline-primary">View</a>
                                    <a href="<?php echo e(url('employer/edit-job.php?id=' . (int)$job['id'])); ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="POST" action="<?php echo e(url('employer/jobs.php')); ?>" onsubmit="return confirm('Delete this job?');">
                                        <input type="hidden" name="delete_id" value="<?php echo (int)$job['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p class="mb-0 text-muted">You have not posted any jobs yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
