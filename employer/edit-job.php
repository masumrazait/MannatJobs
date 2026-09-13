<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('employer');

$userId = (int)$_SESSION['user_id'];
$jobId = (int)($_GET['id'] ?? $_POST['job_id'] ?? 0);
if ($jobId <= 0) {
    setFlash('danger', 'Invalid job id.');
    redirect('employer/jobs.php');
}

$jobStmt = $conn->prepare('SELECT * FROM jobs WHERE id = ? AND employer_id = ? LIMIT 1');
$jobStmt->bind_param('ii', $jobId, $userId);
$jobStmt->execute();
$job = $jobStmt->get_result()->fetch_assoc();
if (!$job) {
    setFlash('danger', 'Job not found or you do not have permission to edit it.');
    redirect('employer/jobs.php');
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
    $deadlineDate = $deadline === null ? null : DateTime::createFromFormat('Y-m-d', $deadline);
    $deadlineIsValid = $deadline === null || ($deadlineDate && $deadlineDate->format('Y-m-d') === $deadline);

    if ($title === '' || $description === '' || $requirements === '' || $categoryId <= 0 || $location === '') {
        setFlash('danger', 'Please fill in all required fields.');
    } elseif (!in_array($jobType, $allowedJobTypes, true)) {
        setFlash('danger', 'Please select a valid job type.');
    } elseif (!$deadlineIsValid) {
        setFlash('danger', 'Please enter a valid application deadline.');
    } elseif (($salaryMin !== null && $salaryMin < 0) || ($salaryMax !== null && $salaryMax < 0) || ($salaryMin !== null && $salaryMax !== null && $salaryMax < $salaryMin)) {
        setFlash('danger', 'Please enter a valid salary range.');
    } else {
        $status = $job['status'] === 'closed' ? 'closed' : 'pending';
        $stmt = $conn->prepare('UPDATE jobs SET category_id = ?, title = ?, description = ?, requirements = ?, location = ?, job_type = ?, salary_min = ?, salary_max = ?, experience_level = ?, deadline = ?, status = ? WHERE id = ? AND employer_id = ?');
        $stmt->bind_param('isssssddsssii', $categoryId, $title, $description, $requirements, $location, $jobType, $salaryMin, $salaryMax, $experienceLevel, $deadline, $status, $jobId, $userId);
        if ($stmt->execute()) {
            setFlash('success', $status === 'pending' ? 'Job updated and sent for admin approval.' : 'Job updated successfully.');
            redirect('employer/jobs.php');
        }
        setFlash('danger', 'Job could not be updated.');
    }

    $job = array_merge($job, [
        'title' => $title,
        'description' => $description,
        'requirements' => $requirements,
        'category_id' => $categoryId,
        'location' => $location,
        'job_type' => $jobType,
        'salary_min' => $salaryMin,
        'salary_max' => $salaryMax,
        'experience_level' => $experienceLevel,
        'deadline' => $deadline,
    ]);
}

$categories = $conn->query('SELECT * FROM job_categories ORDER BY name');
$pageTitle = 'Edit Job';
include __DIR__ . '/../includes/header.php';
?>

<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <span class="eyebrow">Job listing</span>
            <h3 class="fw-bold mb-0">Edit Job</h3>
        </div>
        <span class="status-pill status-<?php echo e($job['status']); ?>"><?php echo e(ucfirst($job['status'])); ?></span>
    </div>
    <form method="POST" action="<?php echo e(url('employer/edit-job.php?id=' . $jobId)); ?>">
        <input type="hidden" name="job_id" value="<?php echo $jobId; ?>">
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Job Title</label><input type="text" class="form-control" name="title" maxlength="180" value="<?php echo e($job['title']); ?>" required></div>
            <div class="col-md-6"><label class="form-label">Category</label><select class="form-select" name="category_id" required><option value="">Choose a job category</option><?php while ($cat = $categories->fetch_assoc()): ?><option value="<?php echo (int)$cat['id']; ?>" <?php echo (int)$job['category_id'] === (int)$cat['id'] ? 'selected' : ''; ?>><?php echo e($cat['name']); ?></option><?php endwhile; ?></select></div>
            <div class="col-md-6"><label class="form-label">Location</label><input type="text" class="form-control" name="location" maxlength="120" value="<?php echo e($job['location']); ?>" required></div>
            <div class="col-md-6"><label class="form-label">Job Type</label><select class="form-select" name="job_type"><?php foreach (['Full-time', 'Part-time', 'Remote', 'Internship'] as $type): ?><option value="<?php echo e($type); ?>" <?php echo $job['job_type'] === $type ? 'selected' : ''; ?>><?php echo e($type); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-6"><label class="form-label">Salary Minimum (INR)</label><input type="number" class="form-control" name="salary_min" min="0" value="<?php echo e($job['salary_min']); ?>"></div>
            <div class="col-md-6"><label class="form-label">Salary Maximum (INR)</label><input type="number" class="form-control" name="salary_max" min="0" value="<?php echo e($job['salary_max']); ?>"></div>
            <div class="col-md-6"><label class="form-label">Experience Level</label><input type="text" class="form-control" name="experience_level" maxlength="80" value="<?php echo e($job['experience_level']); ?>"></div>
            <div class="col-md-6"><label class="form-label">Application Deadline</label><input type="date" class="form-control" name="deadline" value="<?php echo e($job['deadline']); ?>"></div>
            <div class="col-12"><label class="form-label">Job Description</label><textarea class="form-control" rows="5" name="description" required><?php echo e($job['description']); ?></textarea></div>
            <div class="col-12"><label class="form-label">Requirements</label><textarea class="form-control" rows="5" name="requirements" required><?php echo e($job['requirements']); ?></textarea></div>
        </div>
        <div class="d-flex gap-2 flex-wrap mt-3"><button type="submit" class="btn btn-primary">Save Changes</button><a href="<?php echo e(url('employer/jobs.php')); ?>" class="btn btn-outline-primary">Cancel</a></div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
