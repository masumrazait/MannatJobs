<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize(strtolower($_POST['email'] ?? ''));
    $message = sanitize($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        setFlash('danger', 'All contact form fields are required.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('danger', 'Please enter a valid email address.');
    } else {
        $stmt = $conn->prepare('INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $name, $email, $message);
        if ($stmt->execute()) {
            setFlash('success', 'Your message has been sent successfully.');
            redirect('contact.php');
        } else {
            setFlash('danger', 'Could not send the message. Please try again.');
        }
    }
}

$pageTitle = 'Contact';
include __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card p-4">
            <h2 class="fw-bold mb-3">Contact Us</h2>
            <form method="POST" action="<?php echo e(url('contact.php')); ?>">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea class="form-control" rows="5" name="message" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
