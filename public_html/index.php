<?php 
require_once '../private/functions.php';

// Function to get recent reviews
function get_recent_reviews($limit = 6) {
    global $conn;
    
    $query = "SELECT 
                r.*,
                u.username,
                u.user_id,
                u.profile_picture
              FROM 
                reviews r
              JOIN 
                users u ON r.user_id = u.user_id
              ORDER BY 
                r.created_at DESC
              LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $reviews = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Get movie details for each review
        $movie_details = get_movie_details($row['movie_id']);
        
        // Add poster path to our results
        $row['poster_path'] = $movie_details['poster_path'] ?? null;
        $row['release_date'] = $movie_details['release_date'] ?? null;
        $row['backdrop_path'] = $movie_details['backdrop_path'] ?? null;
        
        $reviews[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $reviews;
}

// Function to get top rated movies
function get_top_rated_movies($limit = 10) {
    global $conn;
    
    $query = "SELECT 
                r.movie_id,
                r.movie_title,
                COUNT(r.review_id) as review_count,
                AVG(r.rating) as avg_rating
              FROM 
                reviews r
              GROUP BY 
                r.movie_id, r.movie_title
              HAVING 
                COUNT(r.review_id) > 0
              ORDER BY 
                avg_rating DESC
              LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $movies = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Get movie details from TMDB API
        $movie_details = get_movie_details($row['movie_id']);
        
        // Add poster path to our results
        $row['poster_path'] = $movie_details['poster_path'] ?? null;
        $row['release_date'] = $movie_details['release_date'] ?? null;
        $row['backdrop_path'] = $movie_details['backdrop_path'] ?? null;
        
        $movies[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $movies;
}

// Function to get most active users
function get_most_active_users($limit = 5) {
    global $conn;
    
    $query = "SELECT 
                u.user_id,
                u.username,
                u.profile_picture,
                COUNT(r.review_id) as review_count
              FROM 
                users u
              JOIN 
                reviews r ON u.user_id = r.user_id
              GROUP BY 
                u.user_id, u.username, u.profile_picture
              ORDER BY 
                review_count DESC
              LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $users;
}

// Function to get recent activity
function get_recent_activity($limit = 5) {
    global $conn;
    
    $query = "SELECT 
                r.*,
                u.username,
                u.user_id,
                u.profile_picture
              FROM 
                reviews r
              JOIN 
                users u ON r.user_id = u.user_id
              ORDER BY 
                r.created_at DESC
              LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $activities = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Get movie details for each review
        $movie_details = get_movie_details($row['movie_id']);
        
        // Add poster path to our results
        $row['poster_path'] = $movie_details['poster_path'] ?? null;
        $row['release_date'] = $movie_details['release_date'] ?? null;
        
        $activities[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $activities;
}

// Get featured data
$recent_reviews = get_recent_reviews();
$top_movies = get_top_rated_movies();
$active_users = get_most_active_users();
$recent_activity = get_recent_activity();

// Select a random top movie for the hero section
$hero_movie = null;
if (!empty($recent_reviews)) {
    $hero_movie = $recent_reviews[array_rand($recent_reviews)];
}

include 'templates/header.php'; 
?>

<?php if ($hero_movie): ?>
<!-- Hero Section with Featured Movie -->
<div class="hero-section" style="background-image: url('<?php echo TMDB_IMAGE_BASE_URL . ($hero_movie['backdrop_path'] ?? $hero_movie['poster_path']); ?>');">
    <div class="container hero-content">
        <div class="row">
            <div class="col-md-7">
                <h1 class="hero-title"><?php echo $hero_movie['movie_title']; ?></h1>
                <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-primary me-2"><?php echo number_format($hero_movie['rating'], 1); ?>/10</span>
                    <span class="text-light me-3"><?php echo $hero_movie['release_date'] ? date('Y', strtotime($hero_movie['release_date'])) : 'N/A'; ?></span>
                    <span class="text-light"><i class="fas fa-user me-1"></i><?php echo $hero_movie['username']; ?></span>
                </div>
                <p class="mb-4"><?php echo !empty($hero_movie['review_text']) ? 
                    (strlen($hero_movie['review_text']) > 200 ? substr($hero_movie['review_text'], 0, 200) . '...' : $hero_movie['review_text']) 
                    : 'No written review'; ?></p>
                <div>
                    <a href="review.php?id=<?php echo $hero_movie['movie_id']; ?>" class="btn btn-primary me-2">
                        <i class="fas fa-play me-1"></i>View Details
                    </a>
                    <a href="#" class="btn btn-outline-light">
                        <i class="fas fa-info-circle me-1"></i>More Info
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Main Content -->
<div class="content-row">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4"><i class="fas fa-trophy me-2"></i>Top Rated Movies</h2>
        <a href="movies.php" class="btn btn-outline-primary btn-sm">See All</a>
    </div>
    
    <div class="card-slider">
        <?php foreach ($top_movies as $movie): ?>
            <div class="movie-card-container">
                <a href="review.php?id=<?php echo $movie['movie_id']; ?>" class="text-decoration-none">
                    <div class="movie-card">
                        <?php 
                        $poster_url = $movie['poster_path'] 
                            ? TMDB_IMAGE_BASE_URL . $movie['poster_path'] 
                            : 'assets/img/no-poster.jpg';
                        ?>
                        <img src="<?php echo $poster_url; ?>" alt="<?php echo $movie['movie_title']; ?>" class="img-fluid">
                        
                        <div class="movie-rating">
                            <?php echo number_format($movie['avg_rating'], 1); ?>
                        </div>
                        
                        <div class="card-body">
                            <h5 class="movie-title"><?php echo $movie['movie_title']; ?></h5>
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="movie-year mb-0">
                                    <?php echo $movie['release_date'] ? date('Y', strtotime($movie['release_date'])) : 'N/A'; ?>
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-comment me-1"></i> <?php echo $movie['review_count']; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="row">
    <!-- Recent Activity Section -->
    <div class="col-lg-8 mb-4">
        <div class="content-row">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h4"><i class="fas fa-history me-2"></i>Recent Activity</h2>
                <a href="activity.php" class="btn btn-outline-primary btn-sm">View All</a>
            </div>
            
            <div class="card">
                <div class="card-body p-0">
                    <?php if (count($recent_activity) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_activity as $activity): ?>
                                <div class="list-group-item p-3">
                                    <div class="d-flex">
                                        <a href="profile.php?id=<?php echo $activity['user_id']; ?>" class="me-3">
                                            <?php if (!empty($activity['profile_picture'])): ?>
                                                <img src="<?php echo $activity['profile_picture']; ?>" class="rounded-circle" alt="<?php echo $activity['username']; ?>" style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="avatar rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 20px;">
                                                    <?php echo strtoupper(substr($activity['username'], 0, 1)); ?>
                                                </div>
                                            <?php endif; ?>
                                        </a>
                                        
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between mb-1">
                                                <div>
                                                    <a href="profile.php?id=<?php echo $activity['user_id']; ?>" class="fw-bold text-decoration-none"><?php echo $activity['username']; ?></a>
                                                    <span>reviewed</span>
                                                    <a href="review.php?id=<?php echo $activity['movie_id']; ?>" class="fw-bold text-decoration-none"><?php echo $activity['movie_title']; ?></a>
                                                </div>
                                                <small class="text-muted"><?php echo date('g:i A · M d', strtotime($activity['created_at'])); ?></small>
                                            </div>
                                            
                                            <div class="d-flex activity-review-card p-2 rounded">
                                                <?php 
                                                $poster_url = $activity['poster_path'] 
                                                    ? TMDB_IMAGE_BASE_URL . $activity['poster_path'] 
                                                    : 'assets/img/no-poster.jpg';
                                                ?>
                                                <a href="review.php?id=<?php echo $activity['movie_id']; ?>" class="me-3">
                                                    <img src="<?php echo $poster_url; ?>" style="width: 60px; height: 90px; object-fit: cover; border-radius: 4px;">
                                                </a>
                                                <div>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-primary me-2"><?php echo number_format($activity['rating'], 1); ?>/10</span>
                                                        <small class="text-muted"><?php echo $activity['release_date'] ? date('Y', strtotime($activity['release_date'])) : 'N/A'; ?></small>
                                                    </div>
                                                    <p class="mt-1 mb-0 small">
                                                        <?php 
                                                        echo !empty($activity['review_text']) 
                                                            ? (strlen($activity['review_text']) > 100 
                                                                ? substr($activity['review_text'], 0, 100) . '...' 
                                                                : $activity['review_text']) 
                                                            : '<em class="text-muted">No written review</em>'; 
                                                        ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center p-4">
                            <i class="fas fa-film fa-3x text-muted mb-3"></i>
                            <h5>No Activity Yet</h5>
                            <p class="text-muted">There are no movie reviews yet. Be the first to review a movie!</p>
                            <a href="index.php" class="btn btn-primary mt-2">
                                <i class="fas fa-search me-1"></i>Find Movies to Review
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Top Users and Join Section -->
    <div class="col-lg-4">
        <div class="content-row">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h4"><i class="fas fa-users me-2"></i>Top Reviewers</h2>
                <a href="users.php" class="btn btn-outline-primary btn-sm">View All</a>
            </div>
            
            <div class="card mb-4">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($active_users as $user): ?>
                            <a href="profile.php?id=<?php echo $user['user_id']; ?>" class="list-group-item list-group-item-action p-3">
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($user['profile_picture'])): ?>
                                        <img src="<?php echo $user['profile_picture']; ?>" class="rounded-circle me-3" alt="<?php echo $user['username']; ?>" style="width: 40px; height: 40px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="avatar rounded-circle text-white d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-size: 16px;">
                                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <h6 class="mb-0"><?php echo $user['username']; ?></h6>
                                        <small class="text-muted"><?php echo $user['review_count']; ?> reviews</small>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <?php if (!is_logged_in()): ?>
            <div class="card">
                <div class="card-body text-center p-4">
                    <i class="fas fa-film fa-3x text-primary mb-3"></i>
                    <h5>Join Our Community</h5>
                    <p class="text-muted mb-4">Create an account to review your favorite movies and engage with other movie lovers.</p>
                    <div class="d-grid gap-2">
                        <a href="register.php" class="btn btn-primary">
                            <i class="fas fa-user-plus me-1"></i>Register Now
                        </a>
                        <a href="login.php" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="card">
                <div class="card-body text-center p-4">
                    <i class="fas fa-pen-fancy fa-3x text-primary mb-3"></i>
                    <h5>Share Your Thoughts</h5>
                    <p class="text-muted mb-4">Search for your favorite movies and let everyone know what you think!</p>
                    <div class="d-grid">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#searchModal">
                            <i class="fas fa-search me-1"></i>Search Movies
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Recent Reviews Section - Netflix Row Style -->
<div class="content-row">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4"><i class="fas fa-star me-2"></i>Recent Reviews</h2>
    </div>
    
    <div class="card-slider">
        <?php foreach ($recent_reviews as $review): ?>
            <div class="movie-card-container">
                <a href="review.php?id=<?php echo $review['movie_id']; ?>" class="text-decoration-none">
                    <div class="movie-card">
                        <?php 
                        $poster_url = $review['poster_path'] 
                            ? TMDB_IMAGE_BASE_URL . $review['poster_path'] 
                            : 'assets/img/no-poster.jpg';
                        ?>
                        <img src="<?php echo $poster_url; ?>" alt="<?php echo $review['movie_title']; ?>" class="img-fluid">
                        
                        <div class="movie-rating">
                            <?php echo number_format($review['rating'], 1); ?>
                        </div>
                        
                        <div class="card-body">
                            <h5 class="movie-title"><?php echo $review['movie_title']; ?></h5>
                            <p class="movie-year mb-0">
                                <?php echo $review['release_date'] ? date('Y', strtotime($review['release_date'])) : 'N/A'; ?> • 
                                <i class="fas fa-user me-1"></i><?php echo $review['username']; ?>
                            </p>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Search Movies</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control form-control-lg" id="movie-search-modal" placeholder="Type movie name...">
                </div>
                <div id="search-results-modal" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Movie search in modal
    let searchTimeoutModal;
    
    $('#movie-search-modal').on('input', function() {
        const query = $(this).val().trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeoutModal);
        
        // Clear results if query is empty
        if (query === '') {
            $('#search-results-modal').empty();
            return;
        }
        
        // Set a timeout to avoid sending too many requests while typing
        searchTimeoutModal = setTimeout(function() {
            $.ajax({
                url: 'search.php',
                method: 'GET',
                data: { query: query },
                dataType: 'json',
                success: function(data) {
                    const results = data.results || [];
                    displaySearchResultsModal(results);
                },
                error: function(xhr, status, error) {
                    console.error('Error searching movies:', error);
                }
            });
        }, 500); // 500ms delay
    });
    
    // Function to display search results in modal
    function displaySearchResultsModal(results) {
        const resultsContainer = $('#search-results-modal');
        resultsContainer.empty();
        
        if (results.length === 0) {
            resultsContainer.append('<p class="text-center p-3">No movies found</p>');
            return;
        }
        
        // Display top 8 results
        const maxResults = Math.min(results.length, 8);
        
        let html = '<div class="row row-cols-2 row-cols-md-4 g-3">';
        
        for (let i = 0; i < maxResults; i++) {
            const movie = results[i];
            const posterUrl = movie.poster_path 
                ? 'https://image.tmdb.org/t/p/w500' + movie.poster_path 
                : 'assets/img/no-poster.jpg';
            
            html += `
                <div class="col">
                    <a href="review.php?id=${movie.id}" class="text-decoration-none">
                        <div class="thumbnail-container">
                            <img src="${posterUrl}" alt="${movie.title}" class="img-fluid">
                            <div class="thumbnail-info">
                                <div class="thumbnail-title">${movie.title}</div>
                                <div class="thumbnail-year">${movie.release_date ? movie.release_date.substring(0, 4) : 'Unknown'}</div>
                            </div>
                        </div>
                    </a>
                </div>
            `;
        }
        
        html += '</div>';
        resultsContainer.append(html);
    }
});
</script>

<?php include 'templates/footer.php'; ?>