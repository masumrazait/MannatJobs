<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$filters = ['candidate' => sanitize($_GET['candidate'] ?? ''), 'job' => sanitize($_GET['job'] ?? ''), 'employer' => sanitize($_GET['employer'] ?? ''), 'status_filter' => sanitize($_GET['status_filter'] ?? ''), 'applied' => sanitize($_GET['applied'] ?? '')];
$where = [];
$types = '';
$params = [];
if ($filters['candidate'] !== '') { $where[] = '(cu.name LIKE ? OR cu.email LIKE ?)'; $types .= 'ss'; $params[] = '%' . $filters['candidate'] . '%'; $params[] = '%' . $filters['candidate'] . '%'; }
if ($filters['job'] !== '') { $where[] = 'j.title LIKE ?'; $types .= 's'; $params[] = '%' . $filters['job'] . '%'; }
if ($filters['employer'] !== '') { $where[] = '(eu.name LIKE ? OR eu.email LIKE ?)'; $types .= 'ss'; $params[] = '%' . $filters['employer'] . '%'; $params[] = '%' . $filters['employer'] . '%'; }
if (in_array($filters['status_filter'], ['applied', 'shortlisted', 'rejected', 'hired'], true)) { $where[] = 'a.status = ?'; $types .= 's'; $params[] = $filters['status_filter']; }
if ($filters['applied'] !== '') { $where[] = 'a.applied_at >= ? AND a.applied_at < DATE_ADD(?, INTERVAL 1 DAY)'; $types .= 'ss'; $params[] = $filters['applied']; $params[] = $filters['applied']; }
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$baseSql = ' FROM applications a INNER JOIN jobs j ON j.id = a.job_id INNER JOIN users eu ON eu.id = j.employer_id INNER JOIN users cu ON cu.id = a.jobseeker_id';
$totalRow = preparedQuery($conn, 'SELECT COUNT(*) AS total' . $baseSql . $whereSql, $types, $params)->fetch_assoc();
$pagination = paginationData($totalRow['total'] ?? 0, (int)($_GET['page'] ?? 1));
$applications = preparedQuery($conn, 'SELECT a.*, j.title AS job_title, eu.name AS employer_name, cu.name AS candidate_name, cu.email AS candidate_email' . $baseSql . $whereSql . ' ORDER BY a.applied_at DESC LIMIT ? OFFSET ?', $types . 'ii', array_merge($params, [$pagination['per_page'], $pagination['offset']]));
$pageTitle = 'All Applications';
include __DIR__ . '/../includes/header.php';
?>

<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><div><h3 class="fw-bold mb-1">All Applications</h3><p class="text-muted mb-0">Review application activity across every employer.</p></div><a href="<?php echo e(url('admin/index.php')); ?>" class="btn btn-outline-primary btn-sm">Back to Dashboard</a></div>
    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-3"><input class="form-control" name="candidate" placeholder="Candidate name or email" value="<?php echo e($filters['candidate']); ?>"></div>
        <div class="col-md-2"><input class="form-control" name="job" placeholder="Job title" value="<?php echo e($filters['job']); ?>"></div>
        <div class="col-md-3"><input class="form-control" name="employer" placeholder="Employer name or email" value="<?php echo e($filters['employer']); ?>"></div>
        <div class="col-md-2"><select class="form-select" name="status_filter"><option value="">All statuses</option><?php foreach (['applied', 'shortlisted', 'rejected', 'hired'] as $status): ?><option value="<?php echo $status; ?>" <?php echo $filters['status_filter'] === $status ? 'selected' : ''; ?>><?php echo ucfirst($status); ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><input class="form-control" type="date" name="applied" value="<?php echo e($filters['applied']); ?>"></div>
        <div class="col-12 d-flex gap-2"><button class="btn btn-primary" type="submit">Search</button><a class="btn btn-outline-primary" href="<?php echo e(url('admin/applications.php')); ?>">Clear</a></div>
    </form>
    <div class="table-responsive">
        <table class="table table-striped align-middle"><thead><tr><th>Candidate</th><th>Job</th><th>Employer</th><th>Status</th><th>Applied</th></tr></thead><tbody>
        <?php while ($application = $applications->fetch_assoc()): ?>
            <tr><td><strong><?php echo e($application['candidate_name']); ?></strong><br><small class="text-muted"><?php echo e($application['candidate_email']); ?></small></td><td><?php echo e($application['job_title']); ?></td><td><?php echo e($application['employer_name']); ?></td><td><span class="status-pill status-<?php echo e($application['status']); ?>"><?php echo e(getApplicationStatusLabel($application['status'])); ?></span></td><td><?php echo e(date('M j, Y', strtotime($application['applied_at']))); ?></td></tr>
        <?php endwhile; ?>
        </tbody></table>
    </div>
    <?php if ($pagination['total_pages'] > 1): ?><nav class="mt-3" aria-label="Applications pages"><ul class="pagination mb-0"><?php foreach (paginationPages($pagination) as $page): ?><li class="page-item <?php echo $page === $pagination['page'] ? 'active' : ''; ?>"><a class="page-link" href="<?php echo e(paginationUrl('admin/applications.php', $filters, $page)); ?>"><?php echo $page; ?></a></li><?php endforeach; ?></ul></nav><?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
