<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        setFlash('danger', 'Email and password are required.');
    } else {
        $stmt = $conn->prepare('SELECT id, name, password, role, status FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'blocked') {
                setFlash('danger', 'Your account has been blocked.');
            } elseif ($user['status'] === 'pending') {
                setFlash('warning', 'Your account is pending approval.');
            } else {
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_email'] = $email;

                $redirect = '/index.php';
                if ($user['role'] === 'admin') $redirect = '/admin/index.php';
                elseif ($user['role'] === 'employer') $redirect = '/employer/index.php';
                elseif ($user['role'] === 'jobseeker') $redirect = '/jobseeker/index.php';

                redirect(ltrim($redirect, '/'));
            }
        } else {
            setFlash('danger', 'Invalid email or password.');
        }
    }
}

$pageTitle = 'Login';
include __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-5 col-md-7">
        <div class="card p-4">
            <h2 class="fw-bold mb-3 text-center">Login to MannatJobs</h2>
            <form method="POST" action="<?php echo e(url('login.php')); ?>">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <p class="mt-3 mb-0 text-center text-muted">Don’t have an account? <a href="<?php echo e(url('register.php')); ?>">Create one</a></p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
