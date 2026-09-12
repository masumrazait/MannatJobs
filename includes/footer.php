    </div>
</main>

<footer class="site-footer bg-dark text-white mt-5">
    <div class="container">
        <div class="row gy-4">
            <div class="col-lg-6 col-md-7">
                <h5 class="fw-bold mb-3">MannatJobs</h5>
                <p class="footer-copy mb-0">MannatJobs brings job seekers, trusted employers, and meaningful opportunities together in one simple hiring platform. Discover better roles, build stronger teams, and move your career forward with confidence.</p>
            </div>
            <div class="col-sm-6 col-lg-3">
                <h6>Company</h6>
                <ul class="list-unstyled mb-0">
                    <li><a href="<?php echo e(url('about.php')); ?>" class="text-white-50 text-decoration-none">About</a></li>
                    <li><a href="<?php echo e(url('contact.php')); ?>" class="text-white-50 text-decoration-none">Contact</a></li>
                </ul>
            </div>
            <div class="col-sm-6 col-lg-3">
                <h6>Explore</h6>
                <ul class="list-unstyled mb-0">
                    <li><a href="<?php echo e(url('jobs.php')); ?>" class="text-white-50 text-decoration-none">Browse Jobs</a></li>
                    <li><a href="<?php echo e(url('register.php')); ?>" class="text-white-50 text-decoration-none">Register</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom mt-4 pt-3">
            <small>© <?php echo date('Y'); ?> MannatJobs. Built for better career connections.</small>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo e(url('assets/js/app.js')); ?>"></script>
</body>
</html>
