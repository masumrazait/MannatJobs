<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('employer');

$quota = getEmployerQuota($conn, (int)$_SESSION['user_id']);
if ((int)$quota['posts_used'] >= (int)$quota['post_limit']) {
    setFlash('warning', 'Your job posting limit is complete. Request more posts from the admin.');
    redirect('employer/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $requirements = sanitize($_POST['requirements'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $location = sanitize($_POST['location'] ?? '');
    $jobType = sanitize($_POST['job_type'] ?? 'Full-time');
    $salaryMinInput = sanitize($_POST['salary_min'] ?? '');
    $salaryMaxInput = sanitize($_POST['salary_max'] ?? '');
    $salaryMin = $salaryMinInput === '' ? null : (float)$salaryMinInput;
    $salaryMax = $salaryMaxInput === '' ? null : (float)$salaryMaxInput;
    $experienceLevel = sanitize($_POST['experience_level'] ?? '');
    $deadlineInput = sanitize($_POST['deadline'] ?? '');
    $deadline = $deadlineInput === '' ? null : $deadlineInput;
    $allowedJobTypes = ['Full-time', 'Part-time', 'Remote', 'Internship'];
    $deadlineIsValid = $deadline === null || (DateTime::createFromFormat('Y-m-d', $deadline) && DateTime::createFromFormat('Y-m-d', $deadline)->format('Y-m-d') === $deadline);

    if ($title === '' || $description === '' || $requirements === '' || $categoryId <= 0 || $location === '') {
        setFlash('danger', 'Please fill in all required fields.');
    } elseif (!in_array($jobType, $allowedJobTypes, true)) {
        setFlash('danger', 'Please select a valid job type.');
    } elseif (!$deadlineIsValid) {
        setFlash('danger', 'Please enter a valid application deadline.');
    } elseif (($salaryMin !== null && $salaryMin < 0) || ($salaryMax !== null && $salaryMax < 0) || ($salaryMin !== null && $salaryMax !== null && $salaryMax < $salaryMin)) {
        setFlash('danger', 'Please enter a valid salary range.');
    } else {
        $conn->begin_transaction();
        $quotaStmt = $conn->prepare('SELECT post_limit, posts_used FROM employer_job_quotas WHERE employer_id = ? FOR UPDATE');
        $quotaStmt->bind_param('i', $_SESSION['user_id']);
        $quotaStmt->execute();
        $lockedQuota = $quotaStmt->get_result()->fetch_assoc();
        if (!$lockedQuota || (int)$lockedQuota['posts_used'] >= (int)$lockedQuota['post_limit']) {
            $conn->rollback();
            setFlash('warning', 'Your job posting limit is complete. Request more posts from the admin.');
            redirect('employer/index.php');
        }
        $stmt = $conn->prepare('INSERT INTO jobs (employer_id, category_id, title, description, requirements, location, job_type, salary_min, salary_max, experience_level, deadline, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $status = 'pending';
        $stmt->bind_param('iisssssddsss', $_SESSION['user_id'], $categoryId, $title, $description, $requirements, $location, $jobType, $salaryMin, $salaryMax, $experienceLevel, $deadline, $status);

        if ($stmt->execute()) {
            $newUsed = (int)$lockedQuota['posts_used'] + 1;
            $updateQuota = $conn->prepare('UPDATE employer_job_quotas SET posts_used = ? WHERE employer_id = ?');
            $updateQuota->bind_param('ii', $newUsed, $_SESSION['user_id']);
            $updateQuota->execute();
            $conn->commit();
            setFlash('success', 'Your job has been posted and is pending admin approval.');
            redirect('employer/jobs.php');
        } else {
            $conn->rollback();
            error_log('Job posting failed: ' . $stmt->error);
            setFlash('danger', 'Job posting failed. Please try again.');
        }
    }
}

$categories = $conn->query('SELECT * FROM job_categories ORDER BY name');
$pageTitle = 'Post Job';
include __DIR__ . '/../includes/header.php';
?>

<div class="card p-4">
    <h3 class="fw-bold mb-3">Post a New Job</h3>
    <form method="POST" action="<?php echo e(url('employer/post-job.php')); ?>">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Job Title</label>
                <input type="text" class="form-control" name="title" placeholder="e.g. Senior PHP Developer" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Category</label>
                <select class="form-select" name="category_id" required>
                    <option value="">Choose a job category</option>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?php echo (int)$cat['id']; ?>"><?php echo e($cat['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Location</label>
                <input type="text" class="form-control" name="location" placeholder="e.g. Lahore, Islamabad, or Remote" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Job Type</label>
                <select class="form-select" name="job_type">
                    <option value="Full-time">Full-time</option>
                    <option value="Part-time">Part-time</option>
                    <option value="Remote">Remote</option>
                    <option value="Internship">Internship</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Salary Minimum (INR)</label>
                <input type="number" class="form-control" name="salary_min" min="0" placeholder="e.g. 80000">
            </div>
            <div class="col-md-6">
                <label class="form-label">Salary Maximum (INR)</label>
                <input type="number" class="form-control" name="salary_max" min="0" placeholder="e.g. 120000">
            </div>
            <div class="col-md-6">
                <label class="form-label">Experience Level</label>
                <input type="text" class="form-control" name="experience_level" placeholder="e.g. 2-4 years or Entry level">
            </div>
            <div class="col-md-6">
                <label class="form-label">Application Deadline</label>
                <input type="date" class="form-control" name="deadline" min="<?php echo e(date('Y-m-d')); ?>">
            </div>
            <div class="col-12">
                <label class="form-label">Job Description</label>
                <textarea class="form-control" rows="5" name="description" placeholder="Explain the role, daily responsibilities, team, and what the successful candidate will do." required></textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Requirements</label>
                <textarea class="form-control" rows="5" name="requirements" placeholder="List required skills, education, tools, experience, and certifications. Example: PHP, MySQL, Git, and 2 years of experience." required></textarea>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Post Job</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
