<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)($_POST['user_id'] ?? 0);
    $status = sanitize($_POST['status'] ?? 'active');

    if ($userId > 0 && in_array($status, ['active', 'blocked', 'pending'], true)) {
        $stmt = $conn->prepare('UPDATE users SET status = ? WHERE id = ?');
        $stmt->bind_param('si', $status, $userId);
        $stmt->execute();
        setFlash('success', 'User status updated.');
    }

    redirect('admin/users.php');
}

$users = $conn->query('SELECT u.*, ep.company_name FROM users u LEFT JOIN employer_profiles ep ON ep.user_id = u.id ORDER BY u.created_at DESC');
$pageTitle = 'Manage Users';
include __DIR__ . '/../includes/header.php';
?>

<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Users</h3>
        <a href="<?php echo e(url('admin/index.php')); ?>" class="btn btn-outline-primary btn-sm">Back to Dashboard</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Company</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo e($user['name']); ?></td>
                        <td><?php echo e($user['email']); ?></td>
                        <td><?php echo e($user['role']); ?></td>
                        <td><span class="status-pill status-<?php echo e($user['status']); ?>"><?php echo e(ucfirst($user['status'])); ?></span></td>
                        <td><?php echo e($user['company_name'] ?: '-'); ?></td>
                        <td>
                            <form method="POST" action="<?php echo e(url('admin/users.php')); ?>" class="d-flex gap-2 align-items-center">
                                <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                <select class="form-select form-select-sm" name="status">
                                    <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="blocked" <?php echo $user['status'] === 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                                    <option value="pending" <?php echo $user['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
