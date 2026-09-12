<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Jobs';

$where = ['j.status = ?'];
$params = ['approved'];
$types = 's';

if (!empty($_GET['keyword'])) {
    $keyword = '%' . sanitize($_GET['keyword']) . '%';
    $where[] = '(j.title LIKE ? OR j.description LIKE ? OR j.requirements LIKE ?)';
    $params[] = $keyword; $params[] = $keyword; $params[] = $keyword;
    $types .= 'sss';
}

if (!empty($_GET['location'])) {
    $location = '%' . sanitize($_GET['location']) . '%';
    $where[] = 'j.location LIKE ?';
    $params[] = $location;
    $types .= 's';
}

if (!empty($_GET['category'])) {
    $categoryId = (int)$_GET['category'];
    $where[] = 'j.category_id = ?';
    $params[] = $categoryId;
    $types .= 'i';
}

if (!empty($_GET['job_type'])) {
    $jobType = sanitize($_GET['job_type']);
    $where[] = 'j.job_type = ?';
    $params[] = $jobType;
    $types .= 's';
}

$sql = 'SELECT j.*, c.name AS category_name, u.name AS employer_name, ep.company_name FROM jobs j INNER JOIN job_categories c ON c.id = j.category_id INNER JOIN users u ON u.id = j.employer_id LEFT JOIN employer_profiles ep ON ep.user_id = u.id WHERE ' . implode(' AND ', $where) . ' ORDER BY j.created_at DESC';

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$jobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$categories = $conn->query('SELECT * FROM job_categories ORDER BY name');

include __DIR__ . '/includes/header.php';
?>

<div class="row g-4">
    <div class="col-lg-3">
        <div class="card p-3">
            <h4 class="fw-bold mb-3">Filters</h4>
            <form method="GET" action="<?php echo e(url('jobs.php')); ?>">
                <div class="mb-3">
                    <label class="form-label">Keyword</label>
                    <input type="text" class="form-control" name="keyword" value="<?php echo e($_GET['keyword'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Location</label>
                    <input type="text" class="form-control" name="location" value="<?php echo e($_GET['location'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category">
                        <option value="">All Categories</option>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?php echo (int)$cat['id']; ?>" <?php echo (isset($_GET['category']) && (int)$_GET['category'] === (int)$cat['id']) ? 'selected' : ''; ?>>
                                <?php echo e($cat['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Job Type</label>
                    <select class="form-select" name="job_type">
                        <option value="">All Types</option>
                        <option value="Full-time" <?php echo (($_GET['job_type'] ?? '') === 'Full-time') ? 'selected' : ''; ?>>Full-time</option>
                        <option value="Part-time" <?php echo (($_GET['job_type'] ?? '') === 'Part-time') ? 'selected' : ''; ?>>Part-time</option>
                        <option value="Remote" <?php echo (($_GET['job_type'] ?? '') === 'Remote') ? 'selected' : ''; ?>>Remote</option>
                        <option value="Internship" <?php echo (($_GET['job_type'] ?? '') === 'Internship') ? 'selected' : ''; ?>>Internship</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
            </form>
        </div>
    </div>

    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="fw-bold mb-0">Available Jobs</h2>
            <span class="text-muted"><?php echo count($jobs); ?> jobs found</span>
        </div>

        <?php if (empty($jobs)): ?>
            <div class="empty-state">
                <p class="mb-0 text-muted">No jobs match your search right now.</p>
            </div>
        <?php else: ?>
            <?php foreach ($jobs as $job): ?>
                <div class="card p-3 mb-3 job-card">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                        <div>
                            <h4 class="mb-1"><?php echo e($job['title']); ?></h4>
                            <p class="mb-1 text-muted"><?php echo e($job['company_name'] ?: $job['employer_name']); ?></p>
                            <small class="text-muted"><?php echo e($job['location']); ?> • <?php echo e($job['job_type']); ?></small>
                        </div>
                        <span class="job-badge bg-light text-primary"><?php echo e($job['category_name']); ?></span>
                    </div>
                    <p class="mt-3 mb-3"><?php echo substr(strip_tags($job['description']), 0, 180); ?>...</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted"><?php echo $job['salary_min'] ? 'PKR ' . number_format($job['salary_min']) . ' - ' . number_format($job['salary_max']) : 'Salary not disclosed'; ?></small>
                        <a href="<?php echo e(url('job-details.php?id=' . (int)$job['id'])); ?>" class="btn btn-primary">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
