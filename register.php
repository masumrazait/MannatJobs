<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize(strtolower($_POST['email'] ?? ''));
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = sanitize($_POST['role'] ?? 'jobseeker');

    if (!in_array($role, ['jobseeker', 'employer'], true)) {
        setFlash('danger', 'Invalid account type selected.');
    } elseif ($name === '' || $email === '' || $password === '') {
        setFlash('danger', 'Please fill in the required fields.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('danger', 'Please enter a valid email address.');
    } elseif (!isStrongPassword($password)) {
        setFlash('danger', 'Password must be at least 8 characters and include upper/lowercase, number, and symbol.');
    } elseif ($password !== $confirmPassword) {
        setFlash('danger', 'Passwords do not match.');
    } else {
        $existing = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $existing->bind_param('s', $email);
        $existing->execute();
        if ($existing->get_result()->num_rows > 0) {
            setFlash('danger', 'This email is already registered.');
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $status = $role === 'employer' ? 'pending' : 'active';
            $stmt = $conn->prepare('INSERT INTO users (name, email, password, role, phone, status) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('ssssss', $name, $email, $hash, $role, $phone, $status);
            if ($stmt->execute()) {
                $userId = $conn->insert_id;

                if ($role === 'jobseeker') {
                    $ins = $conn->prepare('INSERT INTO jobseeker_profiles (user_id) VALUES (?)');
                    $ins->bind_param('i', $userId);
                    $ins->execute();
                } else {
                    $ins = $conn->prepare('INSERT INTO employer_profiles (user_id) VALUES (?)');
                    $ins->bind_param('i', $userId);
                    $ins->execute();
                    $quota = $conn->prepare('INSERT INTO employer_job_quotas (employer_id, post_limit, posts_used) VALUES (?, 30, 0)');
                    $quota->bind_param('i', $userId);
                    $quota->execute();
                }

                setFlash('success', $role === 'employer' ? 'Employer account created successfully. It is pending admin approval.' : 'Registration successful. You can now log in.');
                redirect('login.php');
            } else {
                setFlash('danger', 'Registration failed. Please try again.');
            }
        }
    }
}

$pageTitle = 'Register';
include __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="card p-4">
            <h2 class="fw-bold mb-3 text-center">Create Your Account</h2>
            <form method="POST" action="<?php echo e(url('register.php')); ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="phone">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Register As</label>
                        <select class="form-select" name="role">
                            <option value="jobseeker">Job Seeker</option>
                            <option value="employer">Employer</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 mt-4">Register</button>
            </form>
            <p class="mt-3 mb-0 text-center text-muted">Already registered? <a href="<?php echo e(url('login.php')); ?>">Login here</a></p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
