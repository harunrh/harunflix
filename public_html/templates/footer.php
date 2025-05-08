</div><!-- /.container -->

    <footer class="mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <img src="images/harunflix.png" alt="HarunFlix Logo" style="height: 30px;" class="mb-3">
                    <p class="small">Share your opinions and discover what others think about the latest blockbusters and classic films.</p>
                </div>
                <div class="col-md-3">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-muted text-decoration-none">Home</a></li>
                        <li><a href="movies.php" class="text-muted text-decoration-none">Movies</a></li>
                        <li><a href="activity.php" class="text-muted text-decoration-none">Activity</a></li>
                        <li><a href="users.php" class="text-muted text-decoration-none">Users</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Account</h6>
                    <ul class="list-unstyled">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="profile.php?id=<?php echo $_SESSION['user_id']; ?>" class="text-muted text-decoration-none">My Profile</a></li>
                            <li><a href="edit_profile.php" class="text-muted text-decoration-none">Edit Profile</a></li>
                            <li><a href="my_reviews.php" class="text-muted text-decoration-none">My Reviews</a></li>
                            <li><a href="logout.php" class="text-muted text-decoration-none">Logout</a></li>
                        <?php else: ?>
                            <li><a href="login.php" class="text-muted text-decoration-none">Login</a></li>
                            <li><a href="register.php" class="text-muted text-decoration-none">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <hr class="my-2" style="border-color: rgba(255,255,255,0.1);">
                    <p class="text-center small mb-0">&copy; <?php echo date('Y'); ?> HarunFlix. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap & jQuery JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
    
    <script>
    // Navbar scroll effect
    $(window).scroll(function() {
        if ($(this).scrollTop() > 50) {
            $('.navbar').addClass('scrolled');
        } else {
            $('.navbar').removeClass('scrolled');
        }
    });
    
    // Theme toggle functionality
    $(document).ready(function() {
        // Check if there's a saved preference
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'light') {
            $('body').addClass('light-theme');
            $('#theme-toggle i').removeClass('fa-sun').addClass('fa-moon');
        }
        
        // Toggle theme
        $('#theme-toggle').click(function() {
            $('body').toggleClass('light-theme');
            
            if ($('body').hasClass('light-theme')) {
                localStorage.setItem('theme', 'light');
                $('#theme-toggle i').removeClass('fa-sun').addClass('fa-moon');
            } else {
                localStorage.setItem('theme', 'dark');
                $('#theme-toggle i').removeClass('fa-moon').addClass('fa-sun');
            }
        });
    });
    </script>
</body>
</html>