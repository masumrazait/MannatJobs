<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('employer');

$employerId = (int)$_SESSION['user_id'];
$quota = getEmployerQuota($conn, $employerId);
$pending = $conn->prepare("SELECT id, requested_posts, created_at FROM job_post_quota_requests WHERE employer_id = ? AND status = 'pending' ORDER BY created_at DESC LIMIT 1");
$pending->bind_param('i', $employerId);
$pending->execute();
$pendingRequest = $pending->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestedPosts = (int)($_POST['requested_posts'] ?? 0);
    if (!in_array($requestedPosts, [30, 50, 100], true)) {
        setFlash('danger', 'Please select 30, 50, or 100 additional posts.');
    } elseif ((int)$quota['posts_used'] < (int)$quota['post_limit']) {
        setFlash('warning', 'You can request more posts after using your current allowance.');
    } elseif ($pendingRequest) {
        setFlash('warning', 'Your previous quota request is still under admin review.');
    } else {
        $stmt = $conn->prepare("INSERT INTO job_post_quota_requests (employer_id, requested_posts) VALUES (?, ?)");
        $stmt->bind_param('ii', $employerId, $requestedPosts);
        if ($stmt->execute()) {
            setFlash('success', 'Your job post quota request was sent to the admin.');
        } else {
            setFlash('danger', 'Could not send the quota request.');
        }
    }
    redirect('employer/quota-request.php');
}

$pageTitle = 'Job Post Access';
include __DIR__ . '/../includes/header.php';
?>

<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <span class="eyebrow">Posting access</span>
            <h2 class="fw-bold mb-1">Job post quota</h2>
            <p class="text-muted mb-0">Request another posting allowance when your current quota is used.</p>
        </div>
        <span class="status-pill status-<?php echo $quota['posts_used'] >= $quota['post_limit'] ? 'pending' : 'approved'; ?>">
            <?php echo (int)$quota['posts_used']; ?> / <?php echo (int)$quota['post_limit']; ?> used
        </span>
    </div>

    <?php if ($pendingRequest): ?>
        <div class="alert alert-warning">A request for <?php echo (int)$pendingRequest['requested_posts']; ?> additional posts is awaiting admin review.</div>
    <?php elseif ((int)$quota['posts_used'] < (int)$quota['post_limit']): ?>
        <div class="alert alert-info">You still have <?php echo (int)$quota['post_limit'] - (int)$quota['posts_used']; ?> posts available.</div>
    <?php else: ?>
        <form method="POST" action="<?php echo e(url('employer/quota-request.php')); ?>" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Additional posts requested</label>
                <select name="requested_posts" class="form-select" required>
                    <option value="30">30 posts</option>
                    <option value="50">50 posts</option>
                    <option value="100">100 posts</option>
                </select>
            </div>
            <div class="col-md-6"><button type="submit" class="btn btn-primary">Request Admin Permission</button></div>
        </form>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
