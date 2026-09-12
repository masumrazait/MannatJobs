<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = 'Page Not Found';
include __DIR__ . '/includes/header.php';
?>

<div class="text-center py-5">
    <h1 class="display-1 fw-bold text-primary">404</h1>
    <h3 class="mb-3">Page Not Found</h3>
    <p class="text-muted">The page you are looking for does not exist or has been moved.</p>
    <a href="<?php echo e(url('index.php')); ?>" class="btn btn-primary">Back to Home</a>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
