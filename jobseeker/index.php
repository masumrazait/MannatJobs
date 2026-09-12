<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('jobseeker');

$pageTitle = 'Job Seeker Dashboard';

$profile = $conn->query('SELECT * FROM jobseeker_profiles WHERE user_id = ' . (int)$_SESSION['user_id'])->fetch_assoc();
$applied = $conn->query('SELECT a.*, j.title, j.location FROM applications a INNER JOIN jobs j ON j.id = a.job_id WHERE a.jobseeker_id = ' . (int)$_SESSION['user_id'] . ' ORDER BY a.applied_at DESC');
$saved = $conn->query('SELECT s.*, j.title, j.location FROM saved_jobs s INNER JOIN jobs j ON j.id = s.job_id WHERE s.jobseeker_id = ' . (int)$_SESSION['user_id'] . ' ORDER BY s.saved_at DESC');

include __DIR__ . '/../includes/header.php';
?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4">
            <div class="d-flex align-items-center gap-3 mb-3">
                <?php $dashboardPhoto = profileImageUrl($_SESSION['user_id'], $profile['profile_photo_data'] ?? null, $profile['profile_photo'] ?? null); ?>
                <?php if ($dashboardPhoto): ?>
                    <img src="<?php echo e($dashboardPhoto); ?>" class="profile-avatar-small" alt="Profile photo">
                <?php else: ?>
                    <span class="profile-avatar-small account-initial" aria-hidden="true"><?php echo e(userInitial($_SESSION['user_name'])); ?></span>
                <?php endif; ?>
                <div><h4 class="fw-bold mb-0"><?php echo e($_SESSION['user_name']); ?></h4><small class="text-muted"><?php echo e($profile['profession'] ?? 'Job seeker'); ?></small></div>
            </div>
            <p class="mb-1"><strong>Skills:</strong> <?php echo e($profile['skills'] ?? 'Not added yet'); ?></p>
            <p class="mb-1"><strong>Education:</strong> <?php echo e($profile['degree'] ?: ($profile['education'] ?? 'Not added yet')); ?></p>
            <p class="mb-1"><strong>Experience:</strong> <?php echo e($profile['experience'] ?? 'Not added yet'); ?></p>
            <p class="mb-1"><strong>Location:</strong> <?php echo e(trim(($profile['city'] ?? '') . ', ' . ($profile['state'] ?? ''), ', ') ?: ($profile['address'] ?? 'Not added yet')); ?></p>
            <a href="<?php echo e(url('jobseeker/profile.php')); ?>" class="btn btn-primary mt-3">Edit Profile</a>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card p-4">
            <h4 class="fw-bold mb-3">Quick Actions</h4>
            <div class="d-grid gap-2 d-md-flex">
                <a href="<?php echo e(url('jobseeker/profile.php')); ?>" class="btn btn-primary">Update Profile</a>
                <a href="<?php echo e(url('jobs.php')); ?>" class="btn btn-outline-primary">Browse Jobs</a>
                <a href="<?php echo e(url('jobseeker/applications.php')); ?>" class="btn btn-outline-primary">My Applications</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-6">
        <div class="card p-4">
            <h4 class="fw-bold mb-3">Applied Jobs</h4>
            <?php if ($applied->num_rows > 0): ?>
                <ul class="list-group list-group-flush">
                    <?php while ($item = $applied->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo e($item['title']); ?></strong><br>
                                <small class="text-muted"><?php echo e($item['location']); ?></small>
                            </div>
                            <span class="status-pill status-<?php echo e($item['status']); ?>"><?php echo e(getApplicationStatusLabel($item['status'])); ?></span>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted mb-0">No applied jobs yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card p-4">
            <h4 class="fw-bold mb-3">Saved Jobs</h4>
            <?php if ($saved->num_rows > 0): ?>
                <ul class="list-group list-group-flush">
                    <?php while ($item = $saved->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo e($item['title']); ?></strong><br>
                                <small class="text-muted"><?php echo e($item['location']); ?></small>
                            </div>
                            <a href="<?php echo e(url('job-details.php?id=' . (int)$item['job_id'])); ?>" class="btn btn-sm btn-outline-primary">Open</a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted mb-0">You have not saved any jobs yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
