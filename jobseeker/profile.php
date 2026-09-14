<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('jobseeker');

$profile = $conn->query('SELECT * FROM jobseeker_profiles WHERE user_id = ' . (int)$_SESSION['user_id'])->fetch_assoc() ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)$_SESSION['user_id'];
    $skills = sanitize($_POST['skills'] ?? '');
    $experience = sanitize($_POST['experience'] ?? '');
    $education = sanitize($_POST['education'] ?? '');
    $degree = sanitize($_POST['degree'] ?? '');
    $university = sanitize($_POST['university'] ?? '');
    $stream = sanitize($_POST['stream'] ?? '');
    $profession = sanitize($_POST['profession'] ?? '');
    $noticePeriod = sanitize($_POST['notice_period'] ?? '');
    $projects = sanitize($_POST['projects'] ?? '');
    $company = sanitize($_POST['company'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $state = sanitize($_POST['state'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $resumePath = $profile['resume_path'] ?? null;
    $resumeData = $profile['resume_data'] ?? null;
    $resumeMime = $profile['resume_mime'] ?? null;
    $profilePhoto = $profile['profile_photo'] ?? null;
    $profileImage = null;

    if (isset($_FILES['resume']) && $_FILES['resume']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload = readUploadedResume($_FILES['resume']);
        if (!$upload['success']) {
            setFlash('danger', $upload['message']);
            redirect('jobseeker/profile.php');
        }
        $resumePath = null;
        $resumeData = $upload['data'];
        $resumeMime = $upload['mime'];
    }

    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload = readUploadedProfileImage($_FILES['profile_photo']);
        if (!$upload['success']) {
            setFlash('danger', $upload['message']);
            redirect('jobseeker/profile.php');
        }
        $profilePhoto = null;
        $profileImage = $upload;
    }

    $allowedNoticePeriods = ['Immediate joiner', '1 month', '2 months', '3 months'];
    if ($noticePeriod !== '' && !in_array($noticePeriod, $allowedNoticePeriods, true)) {
        setFlash('danger', 'Please select a valid notice period.');
        redirect('jobseeker/profile.php');
    }
    $stmt = $conn->prepare('UPDATE jobseeker_profiles SET resume_path = ?, profile_photo = ?, skills = ?, experience = ?, education = ?, degree = ?, university = ?, stream = ?, profession = ?, notice_period = ?, projects = ?, company = ?, address = ?, state = ?, city = ? WHERE user_id = ?');
    $stmt->bind_param('sssssssssssssssi', $resumePath, $profilePhoto, $skills, $experience, $education, $degree, $university, $stream, $profession, $noticePeriod, $projects, $company, $address, $state, $city, $userId);
    if ($stmt->execute()) {
        if ($profileImage) {
            $imageStmt = $conn->prepare('UPDATE jobseeker_profiles SET profile_photo_data = ?, profile_photo_mime = ? WHERE user_id = ?');
            $imageData = $profileImage['data'];
            $imageMime = $profileImage['mime'];
            $imageStmt->bind_param('bsi', $imageData, $imageMime, $userId);
            $imageStmt->send_long_data(0, $imageData);
            if (!$imageStmt->execute()) {
                setFlash('danger', 'Profile image could not be saved. Please try again.');
                redirect('jobseeker/profile.php');
            }
        }
        if ($resumeData !== null) {
            $resumeStmt = $conn->prepare('UPDATE jobseeker_profiles SET resume_data = ?, resume_mime = ? WHERE user_id = ?');
            $resumeStmt->bind_param('bsi', $resumeData, $resumeMime, $userId);
            $resumeStmt->send_long_data(0, $resumeData);
            if (!$resumeStmt->execute()) {
                setFlash('danger', 'Resume could not be saved. Please try again.');
                redirect('jobseeker/profile.php');
            }
        }
        setFlash('success', 'Profile updated successfully.');
        redirect('jobseeker/profile.php');
    }

    setFlash('danger', 'Profile update failed.');
}

$pageTitle = 'My Profile';
include __DIR__ . '/../includes/header.php';
?>

<div class="profile-heading mb-4">
    <div>
        <span class="eyebrow">Candidate profile</span>
        <h1 class="fw-bold mb-1">Make your next application count</h1>
        <p class="text-muted mb-0">Keep one complete profile ready to reuse across every job application.</p>
    </div>
    <?php $profilePhotoUrl = profileImageUrl($_SESSION['user_id'], $profile['profile_photo_data'] ?? null, $profile['profile_photo'] ?? null); ?>
    <?php if ($profilePhotoUrl): ?>
        <img src="<?php echo e($profilePhotoUrl); ?>" class="profile-avatar-large" alt="Profile photo">
    <?php else: ?>
        <span class="profile-avatar-large account-initial" aria-hidden="true"><?php echo e(userInitial($_SESSION['user_name'])); ?></span>
    <?php endif; ?>
</div>

<form method="POST" enctype="multipart/form-data" action="<?php echo e(url('jobseeker/profile.php')); ?>">
    <div class="card p-4 mb-4">
        <h3 class="fw-bold mb-1">Profile basics</h3>
        <p class="text-muted small mb-4">Your photo and professional identity help employers recognize you.</p>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Profile photo <span class="text-muted fw-normal">(JPG or PNG)</span></label>
                <input type="file" class="form-control" name="profile_photo" accept="image/jpeg,image/png">
            </div>
            <div class="col-md-6">
                <label class="form-label">Resume <span class="text-muted fw-normal">(PDF, max 2 MB)</span></label>
                <input type="file" class="form-control" name="resume" accept="application/pdf">
                <?php if (!empty($profile['resume_data'])): ?><small class="text-success d-block mt-2">Resume saved and ready to reuse.</small><?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label">Profession</label>
                <input type="text" class="form-control" name="profession" value="<?php echo e($profile['profession'] ?? ''); ?>" placeholder="e.g. Backend Developer">
            </div>
            <div class="col-md-6">
                <label class="form-label">Current or most recent company</label>
                <input type="text" class="form-control" name="company" value="<?php echo e($profile['company'] ?? ''); ?>" placeholder="Company name">
            </div>
            <div class="col-md-6">
                <label class="form-label">Notice period</label>
                <select class="form-select" name="notice_period">
                    <option value="">Select notice period</option>
                    <?php foreach (['Immediate joiner', '1 month', '2 months', '3 months'] as $noticeOption): ?>
                        <option value="<?php echo e($noticeOption); ?>" <?php echo ($profile['notice_period'] ?? '') === $noticeOption ? 'selected' : ''; ?>><?php echo e($noticeOption); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="card p-4 mb-4">
        <h3 class="fw-bold mb-1">Education</h3>
        <p class="text-muted small mb-4">Add the details employers typically look for when reviewing your background.</p>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Degree</label>
                <input type="text" class="form-control" name="degree" value="<?php echo e($profile['degree'] ?? ''); ?>" placeholder="e.g. BS Computer Science">
            </div>
            <div class="col-md-6">
                <label class="form-label">University / institute</label>
                <input type="text" class="form-control" name="university" value="<?php echo e($profile['university'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Stream / specialization</label>
                <input type="text" class="form-control" name="stream" value="<?php echo e($profile['stream'] ?? ''); ?>" placeholder="e.g. Software Engineering">
            </div>
            <div class="col-md-6">
                <label class="form-label">Education summary</label>
                <input type="text" class="form-control" name="education" value="<?php echo e($profile['education'] ?? ''); ?>" placeholder="Certifications, awards, or additional study">
            </div>
        </div>
    </div>

    <div class="card p-4 mb-4">
        <h3 class="fw-bold mb-1">Experience and skills</h3>
        <p class="text-muted small mb-4">Use clear keywords and outcomes so your profile is easy to scan.</p>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Skills</label>
                <textarea class="form-control" rows="5" name="skills" placeholder="PHP, MySQL, REST APIs, teamwork"><?php echo e($profile['skills'] ?? ''); ?></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Experience</label>
                <textarea class="form-control" rows="5" name="experience" placeholder="Roles, responsibilities, and years of experience"><?php echo e($profile['experience'] ?? ''); ?></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Projects</label>
                <textarea class="form-control" rows="5" name="projects" placeholder="Project name, your contribution, and tools used"><?php echo e($profile['projects'] ?? ''); ?></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Address</label>
                <textarea class="form-control" rows="5" name="address" placeholder="Street or area"><?php echo e($profile['address'] ?? ''); ?></textarea>
            </div>
        </div>
    </div>

    <div class="card p-4 mb-4">
        <h3 class="fw-bold mb-1">Location</h3>
        <div class="row g-3 mt-1">
            <div class="col-md-6"><label class="form-label">City</label><input type="text" class="form-control" name="city" value="<?php echo e($profile['city'] ?? ''); ?>"></div>
            <div class="col-md-6"><label class="form-label">State / province</label><input type="text" class="form-control" name="state" value="<?php echo e($profile['state'] ?? ''); ?>"></div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary btn-lg">Save profile</button>
</div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
