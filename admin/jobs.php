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

$filters = ['title' => sanitize($_GET['title'] ?? ''), 'employer' => sanitize($_GET['employer'] ?? ''), 'category' => (int)($_GET['category'] ?? 0), 'status_filter' => sanitize($_GET['status_filter'] ?? '')];
$where = [];
$types = '';
$params = [];
if ($filters['title'] !== '') { $where[] = 'j.title LIKE ?'; $types .= 's'; $params[] = '%' . $filters['title'] . '%'; }
if ($filters['employer'] !== '') { $where[] = '(u.name LIKE ? OR u.email LIKE ?)'; $types .= 'ss'; $params[] = '%' . $filters['employer'] . '%'; $params[] = '%' . $filters['employer'] . '%'; }
if ($filters['category'] > 0) { $where[] = 'j.category_id = ?'; $types .= 'i'; $params[] = $filters['category']; }
if (in_array($filters['status_filter'], ['approved', 'pending', 'rejected', 'closed'], true)) { $where[] = 'j.status = ?'; $types .= 's'; $params[] = $filters['status_filter']; }
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$totalRow = preparedQuery($conn, 'SELECT COUNT(*) AS total FROM jobs j INNER JOIN job_categories c ON c.id = j.category_id INNER JOIN users u ON u.id = j.employer_id' . $whereSql, $types, $params)->fetch_assoc();
$pagination = paginationData($totalRow['total'] ?? 0, (int)($_GET['page'] ?? 1));
$jobs = preparedQuery($conn, 'SELECT j.*, c.name AS category_name, u.name AS employer_name FROM jobs j INNER JOIN job_categories c ON c.id = j.category_id INNER JOIN users u ON u.id = j.employer_id' . $whereSql . ' ORDER BY j.created_at DESC LIMIT ? OFFSET ?', $types . 'ii', array_merge($params, [$pagination['per_page'], $pagination['offset']]));
$categories = $conn->query('SELECT id, name FROM job_categories ORDER BY name');
$pageTitle = 'Manage Jobs';
include __DIR__ . '/../includes/header.php';
?>

<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Jobs</h3>
        <a href="<?php echo e(url('admin/index.php')); ?>" class="btn btn-outline-primary btn-sm">Back to Dashboard</a>
    </div>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-3"><input class="form-control" name="title" placeholder="Job title" value="<?php echo e($filters['title']); ?>"></div>
        <div class="col-md-3"><input class="form-control" name="employer" placeholder="Employer name or email" value="<?php echo e($filters['employer']); ?>"></div>
        <div class="col-md-2"><select class="form-select" name="category"><option value="0">All categories</option><?php while ($category = $categories->fetch_assoc()): ?><option value="<?php echo (int)$category['id']; ?>" <?php echo $filters['category'] === (int)$category['id'] ? 'selected' : ''; ?>><?php echo e($category['name']); ?></option><?php endwhile; ?></select></div>
        <div class="col-md-2"><select class="form-select" name="status_filter"><option value="">All statuses</option><?php foreach (['approved', 'pending', 'rejected', 'closed'] as $status): ?><option value="<?php echo $status; ?>" <?php echo $filters['status_filter'] === $status ? 'selected' : ''; ?>><?php echo ucfirst($status); ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary" type="submit">Search</button><a class="btn btn-outline-primary" href="<?php echo e(url('admin/jobs.php')); ?>">Clear</a></div>
    </form>

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
    <?php if ($pagination['total_pages'] > 1): ?><nav class="mt-3" aria-label="Jobs pages"><ul class="pagination mb-0"><?php foreach (paginationPages($pagination) as $page): ?><li class="page-item <?php echo $page === $pagination['page'] ? 'active' : ''; ?>"><a class="page-link" href="<?php echo e(paginationUrl('admin/jobs.php', $filters, $page)); ?>"><?php echo $page; ?></a></li><?php endforeach; ?></ul></nav><?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
