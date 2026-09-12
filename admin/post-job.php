<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $requirements = sanitize($_POST['requirements'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $location = sanitize($_POST['location'] ?? '');
    $jobType = sanitize($_POST['job_type'] ?? 'Full-time');
    $salaryMin = $_POST['salary_min'] === '' ? null : (float)$_POST['salary_min'];
    $salaryMax = $_POST['salary_max'] === '' ? null : (float)$_POST['salary_max'];
    $experienceLevel = sanitize($_POST['experience_level'] ?? '');
    $deadline = sanitize($_POST['deadline'] ?? '') ?: null;
    $allowedJobTypes = ['Full-time', 'Part-time', 'Remote', 'Internship'];
    $validDeadline = $deadline === null || (DateTime::createFromFormat('Y-m-d', $deadline) && DateTime::createFromFormat('Y-m-d', $deadline)->format('Y-m-d') === $deadline);

    if ($title === '' || $description === '' || $requirements === '' || $categoryId <= 0 || $location === '') {
        setFlash('danger', 'Please fill in all required fields.');
    } elseif (!in_array($jobType, $allowedJobTypes, true) || !$validDeadline || ($salaryMin !== null && $salaryMax !== null && $salaryMax < $salaryMin)) {
        setFlash('danger', 'Please enter valid job details.');
    } else {
        $status = 'approved';
        $stmt = $conn->prepare('INSERT INTO jobs (employer_id, category_id, title, description, requirements, location, job_type, salary_min, salary_max, experience_level, deadline, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $systemEmployerId = (int)($_SESSION['user_id']);
        $stmt->bind_param('iisssssddsss', $systemEmployerId, $categoryId, $title, $description, $requirements, $location, $jobType, $salaryMin, $salaryMax, $experienceLevel, $deadline, $status);
        if ($stmt->execute()) {
            setFlash('success', 'Job posted publicly as an admin listing.');
            redirect('admin/jobs.php');
        }
        setFlash('danger', 'Job could not be posted.');
    }
}

$categories = $conn->query('SELECT * FROM job_categories ORDER BY name');
$pageTitle = 'Post Public Job';
include __DIR__ . '/../includes/header.php';
?>

<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3"><h3 class="fw-bold mb-0">Post Public Job</h3><a href="<?php echo e(url('admin/jobs.php')); ?>" class="btn btn-outline-primary btn-sm">Manage Jobs</a></div>
    <form method="POST" action="<?php echo e(url('admin/post-job.php')); ?>">
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Job Title</label><input class="form-control" name="title" required></div>
            <div class="col-md-6"><label class="form-label">Category</label><select class="form-select" name="category_id" required><option value="">Choose category</option><?php while ($category = $categories->fetch_assoc()): ?><option value="<?php echo (int)$category['id']; ?>"><?php echo e($category['name']); ?></option><?php endwhile; ?></select></div>
            <div class="col-md-6"><label class="form-label">Location</label><input class="form-control" name="location" required></div>
            <div class="col-md-6"><label class="form-label">Job Type</label><select class="form-select" name="job_type"><option>Full-time</option><option>Part-time</option><option>Remote</option><option>Internship</option></select></div>
            <div class="col-md-6"><label class="form-label">Minimum Salary (INR)</label><input class="form-control" type="number" min="0" name="salary_min"></div>
            <div class="col-md-6"><label class="form-label">Maximum Salary (INR)</label><input class="form-control" type="number" min="0" name="salary_max"></div>
            <div class="col-md-6"><label class="form-label">Experience Level</label><input class="form-control" name="experience_level"></div>
            <div class="col-md-6"><label class="form-label">Deadline</label><input class="form-control" type="date" name="deadline"></div>
            <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" rows="5" name="description" required></textarea></div>
            <div class="col-12"><label class="form-label">Requirements</label><textarea class="form-control" rows="5" name="requirements" required></textarea></div>
        </div>
        <button class="btn btn-primary mt-3">Publish Job</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
