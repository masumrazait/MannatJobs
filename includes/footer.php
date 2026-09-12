    </div>
</main>

<footer class="bg-dark text-white py-4 mt-5">
    <div class="container">
        <div class="row gy-3">
            <div class="col-md-6">
                <h5 class="fw-bold">MannatJobs</h5>
                <p class="mb-0 text-light-emphasis">Connecting people with opportunities and employers with talent.</p>
            </div>
            <div class="col-md-3">
                <h6>Company</h6>
                <ul class="list-unstyled mb-0">
                    <li><a href="<?php echo e(url('about.php')); ?>" class="text-white-50 text-decoration-none">About</a></li>
                    <li><a href="<?php echo e(url('contact.php')); ?>" class="text-white-50 text-decoration-none">Contact</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h6>Explore</h6>
                <ul class="list-unstyled mb-0">
                    <li><a href="<?php echo e(url('jobs.php')); ?>" class="text-white-50 text-decoration-none">Browse Jobs</a></li>
                    <li><a href="<?php echo e(url('register.php')); ?>" class="text-white-50 text-decoration-none">Register</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo e(url('assets/js/app.js')); ?>"></script>
</body>
</html>
