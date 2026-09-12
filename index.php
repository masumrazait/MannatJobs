<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Home';

$stmt = $conn->prepare('SELECT j.*, c.name AS category_name, u.name AS employer_name, ep.company_name, ep.company_logo FROM jobs j INNER JOIN job_categories c ON c.id = j.category_id INNER JOIN users u ON u.id = j.employer_id LEFT JOIN employer_profiles ep ON ep.user_id = u.id WHERE j.status = ? ORDER BY j.created_at DESC LIMIT 6');
$status = 'approved';
$stmt->bind_param('s', $status);
$stmt->execute();
$jobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt2 = $conn->prepare('SELECT COUNT(*) AS total FROM jobs WHERE status = ?');
$stmt2->bind_param('s', $status);
$stmt2->execute();
$totalJobs = $stmt2->get_result()->fetch_assoc()['total'];

$stmt3 = $conn->prepare('SELECT COUNT(*) AS total FROM users WHERE role IN ("jobseeker", "employer")');
$stmt3->execute();
$totalUsers = $stmt3->get_result()->fetch_assoc()['total'];

$stmt4 = $conn->prepare('SELECT COUNT(*) AS total FROM applications');
$stmt4->execute();
$totalApplications = $stmt4->get_result()->fetch_assoc()['total'];

include __DIR__ . '/includes/header.php';
?>

<section class="hero mb-5">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="badge bg-light text-primary mb-3">Find your next great opportunity</span>
                <h1 class="display-5 fw-bold">Discover jobs that match your ambition.</h1>
                <p class="lead">MannatJobs helps job seekers discover meaningful roles and employers hire the right talent faster.</p>
                <form method="GET" action="<?php echo e(url('jobs.php')); ?>" class="row g-2 mt-3 hero-search">
                    <div class="col-md-5">
                        <input type="text" name="keyword" class="form-control" placeholder="Job title or keyword">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="location" class="form-control" placeholder="Location">
                    </div>
                    <div class="col-md-3">
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <?php
                            $cats = $conn->query('SELECT * FROM job_categories ORDER BY name');
                            while ($cat = $cats->fetch_assoc()) {
                                echo '<option value="' . e($cat['id']) . '">' . e($cat['name']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="submit" class="btn btn-light">Search</button>
                    </div>
                </form>
            </div>
            <div class="col-lg-5 text-center">
                <div class="hero-panel text-start">
                    <h4 class="fw-bold text-primary">Why employers choose us</h4>
                    <ul class="list-unstyled mt-3 mb-0 text-start">
                        <li class="mb-2">✔ Fast hiring workflow</li>
                        <li class="mb-2">✔ Verified employer profiles</li>
                        <li class="mb-2">✔ Searchable talent network</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mb-5">
    <div class="container">
        <div class="row g-3 text-center">
            <div class="col-md-4">
                <div class="stat-box">
                    <h3><?php echo $totalJobs; ?></h3>
                    <p class="text-muted mb-0">Open Jobs</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box">
                    <h3><?php echo $totalUsers; ?></h3>
                    <p class="text-muted mb-0">Members</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box">
                    <h3><?php echo $totalApplications; ?></h3>
                    <p class="text-muted mb-0">Applications</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mb-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">Featured Jobs</h2>
            <a href="<?php echo e(url('jobs.php')); ?>" class="btn btn-outline-primary">View all</a>
        </div>
        <div class="row g-4">
            <?php if (empty($jobs)): ?>
                <div class="col-12">
                    <div class="empty-state">
                        <p class="mb-0 text-muted">No jobs available right now. Please check back soon.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($jobs as $job): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 job-card p-3">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?php echo e(url('assets/images/logo-placeholder.svg')); ?>" alt="Company logo" class="company-logo">
                                    <div>
                                        <h5 class="mb-1"><?php echo e($job['title']); ?></h5>
                                        <small class="text-muted"><?php echo e($job['company_name'] ?: $job['employer_name']); ?></small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <span class="job-badge bg-light text-primary me-2"><?php echo e($job['category_name']); ?></span>
                                <span class="job-badge bg-light text-dark"><?php echo e($job['location']); ?></span>
                            </div>
                            <p class="text-muted mb-3"><?php echo substr(strip_tags($job['description']), 0, 120); ?>...</p>
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <small class="text-muted"><?php echo e($job['job_type']); ?></small>
                                <a href="<?php echo e(url('job-details.php?id=' . (int)$job['id'])); ?>" class="btn btn-primary btn-sm">View Job</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="mb-5">
    <div class="container">
        <h2 class="fw-bold mb-4">Popular Categories</h2>
        <div class="row g-3">
            <?php
            $categories = $conn->query('SELECT * FROM job_categories ORDER BY name');
            while ($category = $categories->fetch_assoc()):
                $count = $conn->query('SELECT COUNT(*) AS total FROM jobs WHERE category_id = ' . (int)$category['id'] . ' AND status = "approved"')->fetch_assoc()['total'];
                ?>
                <div class="col-md-4 col-lg-2">
                    <div class="card h-100 p-3 text-center">
                        <h5 class="mb-1"><?php echo e($category['name']); ?></h5>
                        <small class="text-muted"><?php echo (int)$count; ?> openings</small>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
