<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobId = (int)($_POST['job_id'] ?? 0);
    $status = sanitize($_POST['status'] ?? 'approved');

    if ($jobId > 0 && in_array($status, ['approved', 'rejected', 'closed', 'pending'], true)) {
        $stmt = $conn->prepare('UPDATE jobs SET status = ? WHERE id = ?');
        $stmt->bind_param('si', $status, $jobId);
        $stmt->execute();
        setFlash('success', 'Job status updated.');
    }

    redirect('admin/jobs.php');
}

$jobs = $conn->query('SELECT j.*, c.name AS category_name, u.name AS employer_name FROM jobs j INNER JOIN job_categories c ON c.id = j.category_id INNER JOIN users u ON u.id = j.employer_id ORDER BY j.created_at DESC');
$pageTitle = 'Manage Jobs';
include __DIR__ . '/../includes/header.php';
?>

<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Jobs</h3>
        <a href="<?php echo e(url('admin/index.php')); ?>" class="btn btn-outline-primary btn-sm">Back to Dashboard</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Employer</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($job = $jobs->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo e($job['title']); ?></td>
                        <td><?php echo e($job['employer_name']); ?></td>
                        <td><?php echo e($job['category_name']); ?></td>
                        <td><span class="status-pill status-<?php echo e($job['status']); ?>"><?php echo e(ucfirst($job['status'])); ?></span></td>
                        <td>
                            <form method="POST" action="<?php echo e(url('admin/jobs.php')); ?>" class="d-flex gap-2 align-items-center">
                                <input type="hidden" name="job_id" value="<?php echo (int)$job['id']; ?>">
                                <select class="form-select form-select-sm" name="status">
                                    <option value="approved" <?php echo $job['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="pending" <?php echo $job['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="rejected" <?php echo $job['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    <option value="closed" <?php echo $job['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
