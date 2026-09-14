<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('employer');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicationId = (int)($_POST['application_id'] ?? 0);
    $status = sanitize($_POST['status'] ?? 'applied');

    if ($applicationId > 0 && in_array($status, ['applied', 'shortlisted', 'rejected', 'hired'], true)) {
        $stmt = $conn->prepare('UPDATE applications a INNER JOIN jobs j ON j.id = a.job_id SET a.status = ? WHERE a.id = ? AND j.employer_id = ?');
        $stmt->bind_param('sii', $status, $applicationId, $_SESSION['user_id']);
        $stmt->execute();
        setFlash('success', 'Applicant status updated.');
    }

    redirect('employer/applicants.php');
}

$employerId = (int)$_SESSION['user_id'];
$applicationTotal = preparedQuery($conn, 'SELECT COUNT(*) AS total FROM applications a INNER JOIN jobs j ON j.id = a.job_id WHERE j.employer_id = ?', 'i', [$employerId])->fetch_assoc();
$pagination = paginationData($applicationTotal['total'] ?? 0, (int)($_GET['page'] ?? 1));
$applications = preparedQuery($conn, 'SELECT a.*, j.title AS job_title, u.name AS candidate_name, u.email AS candidate_email, p.skills, p.experience, p.degree, p.university, p.profession, p.notice_period, p.profile_photo, p.profile_photo_data FROM applications a INNER JOIN jobs j ON j.id = a.job_id INNER JOIN users u ON u.id = a.jobseeker_id LEFT JOIN jobseeker_profiles p ON p.user_id = a.jobseeker_id WHERE j.employer_id = ? ORDER BY a.applied_at DESC LIMIT ? OFFSET ?', 'iii', [$employerId, $pagination['per_page'], $pagination['offset']]);
$pageTitle = 'Applicants';
include __DIR__ . '/../includes/header.php';
?>

<div class="card p-4">
    <h3 class="fw-bold mb-3">Applicants</h3>

    <?php if ($applications->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Job</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($app = $applications->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-start gap-2">
                                    <?php $applicantPhoto = profileImageUrl($app['jobseeker_id'], $app['profile_photo_data'] ?? null, $app['profile_photo'] ?? null); ?>
                                    <?php if ($applicantPhoto): ?><img src="<?php echo e($applicantPhoto); ?>" class="profile-avatar-tiny" alt="<?php echo e($app['candidate_name']); ?>">
                                    <?php else: ?><span class="profile-avatar-tiny account-initial" aria-hidden="true"><?php echo e(userInitial($app['candidate_name'])); ?></span><?php endif; ?>
                                    <div><strong><?php echo e($app['candidate_name']); ?></strong><br><small class="text-muted"><?php echo e($app['candidate_email']); ?></small>
                                    <small class="d-block mt-1"><?php echo e($app['profession'] ?: 'Profession not added'); ?></small></div>
                                </div>
                                <div class="applicant-details mt-2"><small><strong>Education:</strong> <?php echo e(trim(($app['degree'] ?? '') . ' - ' . ($app['university'] ?? ''), ' -') ?: 'Not added'); ?></small><br><small><strong>Skills:</strong> <?php echo e($app['skills'] ?: 'Not added'); ?></small><br><small><strong>Experience:</strong> <?php echo e($app['experience'] ?: 'Not added'); ?></small><br><small><strong>Notice period:</strong> <?php echo e($app['notice_period'] ?: 'Not added'); ?></small></div>
                                <?php if (!empty($app['resume_data'])): ?><a href="<?php echo e(resumeUrl('application', $app['id'])); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary mt-2">View resume</a><?php endif; ?>
                            </td>
                            <td><?php echo e($app['job_title']); ?></td>
                            <td><span class="status-pill status-<?php echo e($app['status']); ?>"><?php echo e(getApplicationStatusLabel($app['status'])); ?></span></td>
                            <td>
                                <form method="POST" action="<?php echo e(url('employer/applicants.php')); ?>" class="d-flex gap-2 align-items-center">
                                    <input type="hidden" name="application_id" value="<?php echo (int)$app['id']; ?>">
                                    <select class="form-select form-select-sm" name="status">
                                        <option value="applied" <?php echo $app['status'] === 'applied' ? 'selected' : ''; ?>>Applied</option>
                                        <option value="shortlisted" <?php echo $app['status'] === 'shortlisted' ? 'selected' : ''; ?>>Shortlisted</option>
                                        <option value="rejected" <?php echo $app['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                        <option value="hired" <?php echo $app['status'] === 'hired' ? 'selected' : ''; ?>>Hired</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p class="mb-0 text-muted">No applications received yet.</p>
        </div>
    <?php endif; ?>
    <?php if ($pagination['total_pages'] > 1): ?><nav class="mt-3" aria-label="Applicants pages"><ul class="pagination mb-0"><?php foreach (paginationPages($pagination) as $page): ?><li class="page-item <?php echo $page === $pagination['page'] ? 'active' : ''; ?>"><a class="page-link" href="<?php echo e(paginationUrl('employer/applicants.php', [], $page)); ?>"><?php echo $page; ?></a></li><?php endforeach; ?></ul></nav><?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
