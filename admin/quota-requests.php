<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $decision = sanitize($_POST['decision'] ?? '');
    $note = sanitize($_POST['admin_note'] ?? '');

    if ($requestId > 0 && in_array($decision, ['approved', 'rejected'], true)) {
        $conn->begin_transaction();
        $requestStmt = $conn->prepare("SELECT employer_id, requested_posts, status FROM job_post_quota_requests WHERE id = ? FOR UPDATE");
        $requestStmt->bind_param('i', $requestId);
        $requestStmt->execute();
        $request = $requestStmt->get_result()->fetch_assoc();
        if ($request && $request['status'] === 'pending') {
            if ($decision === 'approved') {
                $quotaStmt = $conn->prepare('INSERT INTO employer_job_quotas (employer_id, post_limit, posts_used) VALUES (?, ?, 0) ON DUPLICATE KEY UPDATE post_limit = post_limit + VALUES(post_limit)');
                $quotaStmt->bind_param('ii', $request['employer_id'], $request['requested_posts']);
                $quotaStmt->execute();
            }
            $reviewStmt = $conn->prepare('UPDATE job_post_quota_requests SET status = ?, admin_id = ?, admin_note = ?, reviewed_at = NOW() WHERE id = ?');
            $adminId = (int)$_SESSION['user_id'];
            $reviewStmt->bind_param('sisi', $decision, $adminId, $note, $requestId);
            $reviewStmt->execute();
            $conn->commit();
            setFlash('success', 'Quota request ' . $decision . '.');
        } else {
            $conn->rollback();
            setFlash('warning', 'This quota request was already reviewed.');
        }
    }
    redirect('admin/quota-requests.php');
}

$requests = $conn->query("SELECT r.*, u.name AS employer_name, u.email, ep.company_name, q.post_limit, q.posts_used FROM job_post_quota_requests r INNER JOIN users u ON u.id = r.employer_id LEFT JOIN employer_profiles ep ON ep.user_id = u.id LEFT JOIN employer_job_quotas q ON q.employer_id = r.employer_id ORDER BY r.status = 'pending' DESC, r.created_at DESC");
$pageTitle = 'Job Post Permissions';
include __DIR__ . '/../includes/header.php';
?>

<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div><h3 class="fw-bold mb-1">Employer Job Permissions</h3><p class="text-muted mb-0">Approve 30, 50, or 100 additional job posts per request.</p></div>
        <a href="<?php echo e(url('admin/index.php')); ?>" class="btn btn-outline-primary btn-sm">Back to Dashboard</a>
    </div>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead><tr><th>Employer</th><th>Current Usage</th><th>Request</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php while ($request = $requests->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo e($request['company_name'] ?: $request['employer_name']); ?></strong><br><small class="text-muted"><?php echo e($request['email']); ?></small></td>
                    <td><?php echo (int)($request['posts_used'] ?? 0); ?> / <?php echo (int)($request['post_limit'] ?? 30); ?></td>
                    <td>+<?php echo (int)$request['requested_posts']; ?> posts</td>
                    <td><span class="status-pill status-<?php echo e($request['status']); ?>"><?php echo e(ucfirst($request['status'])); ?></span></td>
                    <td>
                        <?php if ($request['status'] === 'pending'): ?>
                            <form method="POST" action="<?php echo e(url('admin/quota-requests.php')); ?>" class="d-flex gap-2 align-items-center">
                                <input type="hidden" name="request_id" value="<?php echo (int)$request['id']; ?>">
                                <input type="text" name="admin_note" class="form-control form-control-sm" placeholder="Note">
                                <button name="decision" value="approved" class="btn btn-sm btn-primary">Approve</button>
                                <button name="decision" value="rejected" class="btn btn-sm btn-outline-danger">Reject</button>
                            </form>
                        <?php else: ?><small class="text-muted"><?php echo e($request['admin_note'] ?: 'Reviewed'); ?></small><?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
