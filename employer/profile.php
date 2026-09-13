<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('employer');

$userId = (int)$_SESSION['user_id'];
$profileStmt = $conn->prepare('SELECT * FROM employer_profiles WHERE user_id = ? LIMIT 1');
$profileStmt->bind_param('i', $userId);
$profileStmt->execute();
$profile = $profileStmt->get_result()->fetch_assoc() ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $companyName = sanitize($_POST['company_name'] ?? '');
    $companyWebsite = sanitize($_POST['company_website'] ?? '');
    $companyDescription = sanitize($_POST['company_description'] ?? '');
    $logoData = null;
    $logoMime = null;
    $removeLogo = isset($_POST['remove_logo']);
    $hasValidationError = false;

    if ($companyName === '' || $companyDescription === '') {
        setFlash('danger', 'Company name and description are required.');
        $hasValidationError = true;
    } elseif ($companyWebsite !== '' && !filter_var($companyWebsite, FILTER_VALIDATE_URL)) {
        setFlash('danger', 'Please enter a valid company website URL.');
        $hasValidationError = true;
    } elseif (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload = readUploadedProfileImage($_FILES['company_logo']);
        if (!$upload['success']) {
            setFlash('danger', $upload['message']);
            $hasValidationError = true;
        } else {
            $logoData = $upload['data'];
            $logoMime = $upload['mime'];
        }
    }

    if (!$hasValidationError) {
        $logoPath = $profile['company_logo'] ?? null;
        $existingLogoData = $profile['company_logo_data'] ?? null;
        $existingLogoMime = $profile['company_logo_mime'] ?? null;
        if ($removeLogo || $logoData !== null) {
            $logoPath = null;
        }
        if ($logoData === null && !$removeLogo) {
            $logoData = $existingLogoData;
            $logoMime = $existingLogoMime;
        }

        $stmt = $conn->prepare('UPDATE employer_profiles SET company_name = ?, company_logo = ?, company_logo_data = ?, company_logo_mime = ?, company_website = ?, company_description = ? WHERE user_id = ?');
        $stmt->bind_param('ssbssss', $companyName, $logoPath, $logoData, $logoMime, $companyWebsite, $companyDescription, $userId);
        if ($logoData !== null) {
            $stmt->send_long_data(2, $logoData);
        }
        if ($stmt->execute()) {
            setFlash('success', 'Company profile updated successfully.');
            redirect('employer/profile.php');
        }
        setFlash('danger', 'Company profile could not be updated.');
    }

    $profile['company_name'] = $companyName;
    $profile['company_website'] = $companyWebsite;
    $profile['company_description'] = $companyDescription;
}

$pageTitle = 'Company Profile';
include __DIR__ . '/../includes/header.php';
$logoUrl = companyLogoUrl($userId, $profile['company_logo_data'] ?? null, $profile['company_logo'] ?? null);
?>

<div class="profile-heading mb-4">
    <div>
        <span class="eyebrow">Employer profile</span>
        <h1 class="fw-bold mb-1">Company Details</h1>
        <p class="text-muted mb-0">Keep the information shown to job seekers accurate and up to date.</p>
    </div>
    <?php if ($logoUrl): ?>
        <img src="<?php echo e($logoUrl); ?>" class="profile-avatar-large company-logo" alt="Company logo">
    <?php else: ?>
        <span class="profile-avatar-large account-initial" aria-hidden="true"><?php echo e(userInitial($profile['company_name'] ?? $_SESSION['user_name'])); ?></span>
    <?php endif; ?>
</div>

<form method="POST" enctype="multipart/form-data" action="<?php echo e(url('employer/profile.php')); ?>">
    <div class="card p-4 mb-4">
        <h3 class="fw-bold mb-1">Public company profile</h3>
        <p class="text-muted small mb-4">These details appear beside your approved job listings.</p>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Company name</label>
                <input type="text" class="form-control" name="company_name" maxlength="180" value="<?php echo e($profile['company_name'] ?? ''); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Company website</label>
                <input type="url" class="form-control" name="company_website" placeholder="https://example.com" value="<?php echo e($profile['company_website'] ?? ''); ?>">
            </div>
            <div class="col-12">
                <label class="form-label">Company logo <span class="text-muted fw-normal">(JPG or PNG, max 2 MB)</span></label>
                <input type="file" class="form-control" name="company_logo" accept="image/jpeg,image/png">
                <?php if ($logoUrl): ?>
                    <label class="form-check mt-2"><input class="form-check-input" type="checkbox" name="remove_logo" value="1"> Remove current logo</label>
                <?php endif; ?>
            </div>
            <div class="col-12">
                <label class="form-label">Company description</label>
                <textarea class="form-control" rows="6" name="company_description" maxlength="5000" required><?php echo e($profile['company_description'] ?? ''); ?></textarea>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary btn-lg">Save company profile</button>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
