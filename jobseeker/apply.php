<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('jobseeker');

$jobId = (int)($_GET['job_id'] ?? 0);
if ($jobId <= 0) {
    setFlash('danger', 'Invalid job selected.');
    redirect('jobs.php');
}

$job = $conn->query('SELECT * FROM jobs WHERE id = ' . $jobId . ' AND status = "approved"')->fetch_assoc();
if (!$job) {
    setFlash('danger', 'This job is not available for application.');
    redirect('jobs.php');
}
$profile = $conn->query('SELECT resume_path FROM jobseeker_profiles WHERE user_id = ' . (int)$_SESSION['user_id'])->fetch_assoc() ?: [];
$savedResume = $profile['resume_path'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $coverLetter = sanitize($_POST['cover_letter'] ?? '');
    $resumePath = $savedResume;

    $existing = $conn->prepare('SELECT id FROM applications WHERE job_id = ? AND jobseeker_id = ? LIMIT 1');
    $existing->bind_param('ii', $jobId, $_SESSION['user_id']);
    $existing->execute();
    if ($existing->get_result()->num_rows > 0) {
        setFlash('warning', 'You have already applied for this job.');
        redirect('jobseeker/applications.php');
    }

    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['resume'], __DIR__ . '/../uploads/resumes', ['pdf'], 2 * 1024 * 1024);
        if ($upload['success']) {
            $resumePath = uploadRelativePath($upload['path']);
            $saveResume = $conn->prepare('UPDATE jobseeker_profiles SET resume_path = ? WHERE user_id = ?');
            $saveResume->bind_param('si', $resumePath, $_SESSION['user_id']);
            $saveResume->execute();
        } else {
            setFlash('danger', $upload['message']);
            redirect('jobseeker/apply.php?job_id=' . $jobId);
        }
    }

    if (!$resumePath) {
        setFlash('warning', 'Please upload your resume once from your profile before applying.');
        redirect('jobseeker/profile.php');
    }

    $stmt = $conn->prepare('INSERT INTO applications (job_id, jobseeker_id, resume_path, cover_letter, status) VALUES (?, ?, ?, ?, "applied")');
    $stmt->bind_param('iiss', $jobId, $_SESSION['user_id'], $resumePath, $coverLetter);
    if ($stmt->execute()) {
        setFlash('success', 'Your application was submitted successfully.');
        redirect('jobseeker/applications.php');
    }

    setFlash('danger', 'Application could not be submitted.');
}

$pageTitle = 'Apply for Job';
include __DIR__ . '/../includes/header.php';
?>

<div class="card p-4">
    <h3 class="fw-bold mb-3">Apply for: <?php echo e($job['title']); ?></h3>
    <form method="POST" enctype="multipart/form-data" action="<?php echo e(url('jobseeker/apply.php?job_id=' . (int)$job['id'])); ?>">
        <div class="mb-3">
            <label class="form-label">Resume</label>
            <?php if ($savedResume): ?>
                <div class="saved-file d-flex align-items-center justify-content-between gap-3">
                    <span><strong>Saved resume ready</strong><small class="d-block text-muted">You can use it for this application or upload a replacement.</small></span>
                    <a href="<?php echo e(url($savedResume)); ?>" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">View PDF</a>
                </div>
                <input type="file" class="form-control mt-2" name="resume" accept="application/pdf">
            <?php else: ?>
                <input type="file" class="form-control" name="resume" accept="application/pdf">
                <small class="text-muted d-block mt-2">Upload once and it will be saved to your profile for future applications.</small>
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label class="form-label">Cover Letter</label>
            <textarea class="form-control" rows="6" name="cover_letter" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Application</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
