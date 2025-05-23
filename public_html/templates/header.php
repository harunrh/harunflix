<?php require_once '../private/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HarunFlix - Movie Reviews</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom CSS -->
    <link href="assets/css/styles.css" rel="stylesheet">
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicon-16x16.png">
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="manifest" href="images/site.webmanifest">
    <meta name="theme-color" content="#000000">
    
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="images/harunflix.png" alt="HarunFlix Logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i>Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="movies.php"><i class="fas fa-film me-1"></i>Movies</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="activity.php"><i class="fas fa-history me-1"></i>Activity</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php"><i class="fas fa-users me-1"></i>Users</a>
                    </li>
                </ul>
                <form class="d-flex me-2 search-form position-relative" action="search_results.php" method="get">
                    <div class="input-group">
                        <input type="text" class="form-control" id="movie-search" name="query" placeholder="Search movies...">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div id="search-results" class="position-absolute w-100 mt-1" style="z-index: 1000; top: 100%;"></div>
                </form>
                <ul class="navbar-nav">
                    <li class="nav-item me-2">
                        <a class="nav-link theme-toggle" id="theme-toggle">
                            <i class="fas fa-sun"></i>
                        </a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <?php if (isset($_SESSION['profile_picture']) && !empty($_SESSION['profile_picture'])): ?>
                                    <img src="<?php echo $_SESSION['profile_picture']; ?>" alt="Profile" class="rounded-circle me-1" style="width: 24px; height: 24px; object-fit: cover;">
                                <?php else: ?>
                                    <i class="fas fa-user-circle me-1"></i>
                                <?php endif; ?>
                                <?php echo $_SESSION['username']; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile.php?id=<?php echo $_SESSION['user_id']; ?>"><i class="fas fa-user me-2"></i>My Profile</a></li>
                                <li><a class="dropdown-item" href="edit_profile.php"><i class="fas fa-cog me-2"></i>Edit Profile</a></li>
                                <li><a class="dropdown-item" href="my_reviews.php"><i class="fas fa-star me-2"></i>My Reviews</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt me-1"></i>Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php"><i class="fas fa-user-plus me-1"></i>Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Mobile Navigation Menu -->
    <div class="d-lg-none mobile-menu">
        <a href="index.php" class="mobile-menu-item <?php echo strpos($_SERVER['PHP_SELF'], 'index.php') !== false ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="movies.php" class="mobile-menu-item <?php echo strpos($_SERVER['PHP_SELF'], 'movies.php') !== false ? 'active' : ''; ?>">
            <i class="fas fa-film"></i>
            <span>Movies</span>
        </a>
        <a href="activity.php" class="mobile-menu-item <?php echo strpos($_SERVER['PHP_SELF'], 'activity.php') !== false ? 'active' : ''; ?>">
            <i class="fas fa-history"></i>
            <span>Activity</span>
        </a>
        <a href="users.php" class="mobile-menu-item <?php echo strpos($_SERVER['PHP_SELF'], 'users.php') !== false ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Users</span>
        </a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="profile.php?id=<?php echo $_SESSION['user_id']; ?>" class="mobile-menu-item <?php echo strpos($_SERVER['PHP_SELF'], 'profile.php') !== false ? 'active' : ''; ?>">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        <?php else: ?>
            <a href="login.php" class="mobile-menu-item <?php echo strpos($_SERVER['PHP_SELF'], 'login.php') !== false ? 'active' : ''; ?>">
                <i class="fas fa-sign-in-alt"></i>
                <span>Login</span>
            </a>
        <?php endif; ?>
    </div>
    
    <!-- Mobile Search Button - Fixed at bottom right -->
    <button class="btn btn-primary d-lg-none mobile-search-btn" type="button" data-bs-toggle="modal" data-bs-target="#searchModal">
        <i class="fas fa-search"></i>
    </button>
    
    <!-- Mobile Search Modal -->
    <div class="modal fade" id="searchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Search Movies</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="search_results.php" method="get">
                        <div class="mb-3">
                            <input type="text" class="form-control form-control-lg" id="mobile-search" name="query" placeholder="Type movie name...">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container mt-5 pt-4">