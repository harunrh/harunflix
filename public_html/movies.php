<?php
require_once '../private/functions.php';

// Add the simple error handling here
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function log_error($message) {
    echo "<div style='color: red; background-color: #ffe6e6; padding: 10px; margin: 10px; border: 1px solid red;'>";
    echo "<strong>Error:</strong> $message";
    echo "</div>";
    error_log($message);
}

function safe_execute($callback, $error_message = "An error occurred") {
    try {
        return $callback();
    } catch (Exception $e) {
        log_error($error_message . ": " . $e->getMessage());
        return null;
    }
}

// Function to get all reviewed movies
function get_all_reviewed_movies() {
    global $conn;
    
    $query = "SELECT 
                r.movie_id,
                r.movie_title,
                COUNT(r.review_id) as review_count,
                AVG(r.rating) as avg_rating,
                MAX(r.created_at) as last_review_date
              FROM 
                reviews r
              GROUP BY 
                r.movie_id, r.movie_title
              ORDER BY 
                last_review_date DESC";
    
    $result = mysqli_query($conn, $query);
    
    $movies = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Calculate movie poster and details from TMDB API
        $movie_details = get_movie_details($row['movie_id']);
        
        // Add poster path and other details to our results
        $row['poster_path'] = $movie_details['poster_path'] ?? null;
        $row['backdrop_path'] = $movie_details['backdrop_path'] ?? null;
        $row['release_date'] = $movie_details['release_date'] ?? null;
        $row['overview'] = $movie_details['overview'] ?? null;
        
        $movies[] = $row;
    }
    
    return $movies;
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
        $row['backdrop_path'] = $movie_details['backdrop_path'] ?? null;
        $row['release_date'] = $movie_details['release_date'] ?? null;
        $row['overview'] = $movie_details['overview'] ?? null;
        
        $movies[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $movies;
}

// Function to get recently rated movies (added for new slider)
function get_recently_rated_movies($limit = 10) {
    global $conn;
    
    $query = "SELECT 
                r.movie_id,
                r.movie_title,
                COUNT(r.review_id) as review_count,
                AVG(r.rating) as avg_rating,
                MAX(r.created_at) as last_review_date
              FROM 
                reviews r
              GROUP BY 
                r.movie_id, r.movie_title
              ORDER BY 
                last_review_date DESC
              LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $movies = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Get movie details from TMDB API
        $movie_details = get_movie_details($row['movie_id']);
        
        // Add poster path and details to our results
        $row['poster_path'] = $movie_details['poster_path'] ?? null;
        $row['backdrop_path'] = $movie_details['backdrop_path'] ?? null;
        $row['release_date'] = $movie_details['release_date'] ?? null;
        $row['overview'] = $movie_details['overview'] ?? null;
        
        $movies[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $movies;
}

// Function to get most popular movies by review count (added for new slider)
function get_most_popular_movies($limit = 10) {
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
              ORDER BY 
                review_count DESC
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
        $row['backdrop_path'] = $movie_details['backdrop_path'] ?? null;
        $row['release_date'] = $movie_details['release_date'] ?? null;
        $row['overview'] = $movie_details['overview'] ?? null;
        
        $movies[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $movies;
}

// Function to get newly released movies from TMDB API with error handling
function get_new_releases($limit = 10) {
    try {
        // Get current date
        $current_date = date('Y-m-d');
        // Get date 3 months ago
        $three_months_ago = date('Y-m-d', strtotime('-3 months'));
        
        // Construct URL to get movies released in the last 3 months
        $url = TMDB_API_URL . "/discover/movie?api_key=" . TMDB_API_KEY 
             . "&language=en-US&sort_by=release_date.desc&include_adult=false&include_video=false"
             . "&primary_release_date.gte=" . $three_months_ago
             . "&primary_release_date.lte=" . $current_date
             . "&vote_count.gte=5"; // Ensure some votes exist
        
        $response = @file_get_contents($url);
        
        if ($response === false) {
            log_error("Failed to get new releases from URL: " . $url);
            return [];
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['results']) || !is_array($data['results'])) {
            log_error("Invalid response format for new releases");
            return [];
        }
        
        $movies = [];
        for ($i = 0; $i < min($limit, count($data['results'])); $i++) {
            $movie = $data['results'][$i];
            
            // Format movie data similar to our other functions
            $movie_formatted = [
                'movie_id' => $movie['id'] ?? 0,
                'movie_title' => $movie['title'] ?? 'Unknown',
                'poster_path' => $movie['poster_path'] ?? null,
                'release_date' => $movie['release_date'] ?? null,
                'overview' => $movie['overview'] ?? null,
                'avg_rating' => $movie['vote_average'] ?? 0,
                'review_count' => $movie['vote_count'] ?? 0,
                'source' => 'tmdb' // Mark as coming from TMDB
            ];
            
            $movies[] = $movie_formatted;
        }
        
        return $movies;
    } catch (Exception $e) {
        log_error("Exception in get_new_releases: " . $e->getMessage());
        return [];
    }
}

// Function to get trending movies from TMDB API with error handling
function get_trending_movies($limit = 10) {
    try {
        // Construct URL to get trending movies
        $url = TMDB_API_URL . "/trending/movie/week?api_key=" . TMDB_API_KEY;
        
        $response = @file_get_contents($url);
        
        if ($response === false) {
            log_error("Failed to get trending movies from URL: " . $url);
            return [];
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['results']) || !is_array($data['results'])) {
            log_error("Invalid response format for trending movies");
            return [];
        }
        
        $movies = [];
        for ($i = 0; $i < min($limit, count($data['results'])); $i++) {
            $movie = $data['results'][$i];
            
            // Format movie data similar to our other functions
            $movie_formatted = [
                'movie_id' => $movie['id'] ?? 0,
                'movie_title' => $movie['title'] ?? 'Unknown',
                'poster_path' => $movie['poster_path'] ?? null,
                'release_date' => $movie['release_date'] ?? null,
                'overview' => $movie['overview'] ?? null,
                'avg_rating' => $movie['vote_average'] ?? 0,
                'review_count' => $movie['vote_count'] ?? 0,
                'source' => 'tmdb' // Mark as coming from TMDB
            ];
            
            $movies[] = $movie_formatted;
        }
        
        return $movies;
    } catch (Exception $e) {
        log_error("Exception in get_trending_movies: " . $e->getMessage());
        return [];
    }
}

// Check if the TMDB API is accessible
try {
    // Test URL to check if TMDB is accessible
    $test_url = TMDB_API_URL . "/configuration?api_key=" . TMDB_API_KEY;
    $response = @file_get_contents($test_url);
    
    if ($response === false) {
        log_error("Cannot connect to TMDB API. Please check your API key and internet connection.");
    }
} catch (Exception $e) {
    log_error("TMDB API Error: " . $e->getMessage());
}

// Get all movie categories with error handling
try {
    // First, get internal movies (these should work if database is accessible)
    $all_movies = get_all_reviewed_movies();
    $top_movies = get_top_rated_movies(12);
    $recent_movies = get_recently_rated_movies(12);
    $popular_movies = get_most_popular_movies(12);
    
    // Now get external API data (TMDB) - these might fail
    $new_releases = safe_execute(
        function() { return get_new_releases(12); },
        "Failed to get new releases from TMDB"
    );
    if ($new_releases === null) $new_releases = [];
    
    $trending_movies = safe_execute(
        function() { return get_trending_movies(12); },
        "Failed to get trending movies from TMDB"
    );
    if ($trending_movies === null) $trending_movies = [];
    
} catch (Exception $e) {
    log_error("Major error while fetching movie data: " . $e->getMessage());
    
    // Initialize empty arrays to prevent further errors
    $all_movies = [];
    $top_movies = [];
    $recent_movies = [];
    $popular_movies = [];
    $new_releases = [];
    $trending_movies = [];
}

include 'templates/header.php';
?>

<!-- Search Section with Dynamic Background -->
<div class="hero-banner">
    <!-- Scrolling Poster Background -->
    <div class="poster-scroll-container">
        <div class="poster-scroll">
            <div class="poster-row poster-row-1">
                <?php 
                // Get 7 random movies for first row
                $random_posters_1 = array_slice($all_movies, 0, min(7, count($all_movies)));
                foreach ($random_posters_1 as $movie): 
                ?>
                    <div class="poster" data-movie-id="<?php echo $movie['movie_id']; ?>">
                        <?php 
                        $poster_url = $movie['poster_path'] 
                            ? TMDB_IMAGE_BASE_URL . $movie['poster_path'] 
                            : 'assets/img/no-poster.jpg';
                        ?>
                        <img src="<?php echo $poster_url; ?>" alt="Movie Poster" onerror="this.src='assets/img/no-poster.jpg'">
                    </div>
                <?php endforeach; ?>
                
                <!-- Duplicate the posters to create a seamless loop -->
                <?php foreach ($random_posters_1 as $movie): ?>
                    <div class="poster" data-movie-id="<?php echo $movie['movie_id']; ?>">
                        <?php 
                        $poster_url = $movie['poster_path'] 
                            ? TMDB_IMAGE_BASE_URL . $movie['poster_path'] 
                            : 'assets/img/no-poster.jpg';
                        ?>
                        <img src="<?php echo $poster_url; ?>" alt="Movie Poster" onerror="this.src='assets/img/no-poster.jpg'">
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="poster-row poster-row-2">
                <?php 
                // Get next 7 movies for second row
                $start_index = min(7, count($all_movies));
                $random_posters_2 = array_slice($all_movies, $start_index, min(7, max(0, count($all_movies) - $start_index)));
                foreach ($random_posters_2 as $movie): 
                ?>
                    <div class="poster" data-movie-id="<?php echo $movie['movie_id']; ?>">
                        <?php 
                        $poster_url = $movie['poster_path'] 
                            ? TMDB_IMAGE_BASE_URL . $movie['poster_path'] 
                            : 'assets/img/no-poster.jpg';
                        ?>
                        <img src="<?php echo $poster_url; ?>" alt="Movie Poster" onerror="this.src='assets/img/no-poster.jpg'">
                    </div>
                <?php endforeach; ?>
                
                <!-- Duplicate the posters to create a seamless loop -->
                <?php foreach ($random_posters_2 as $movie): ?>
                    <div class="poster" data-movie-id="<?php echo $movie['movie_id']; ?>">
                        <?php 
                        $poster_url = $movie['poster_path'] 
                            ? TMDB_IMAGE_BASE_URL . $movie['poster_path'] 
                            : 'assets/img/no-poster.jpg';
                        ?>
                        <img src="<?php echo $poster_url; ?>" alt="Movie Poster" onerror="this.src='assets/img/no-poster.jpg'">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Overlay with Content -->
    <div class="hero-overlay"></div>
    
    <div class="container position-relative">
        <div class="hero-content text-center">
            <h1 class="mb-3">Discover Movies</h1>
            <div class="row justify-content-center">
                <div class="col-md-8 position-relative">
                    <div class="input-group input-group-lg">
                        <input type="text" class="form-control" id="movie-search-hero" placeholder="Search for any movie...">
                        <button class="btn btn-primary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div id="search-results-hero" class="mt-2 text-start"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Releases Section -->
<?php if (!empty($new_releases)): ?>
<div class="content-row">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4"><i class="fas fa-film me-2"></i>New Releases</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary view-all-btn" data-section="new-releases">
                See All
            </button>
        </div>
    </div>
    
    <div class="position-relative">
        <!-- Left Control Arrow -->
        <button class="card-slider-control-prev d-none d-md-block" aria-label="Previous">
            <i class="fas fa-chevron-left"></i>
        </button>
        
        <!-- Card Slider -->
        <div class="card-slider">
            <?php foreach ($new_releases as $movie): ?>
                <div class="movie-card-container">
                    <a href="review.php?id=<?php echo $movie['movie_id']; ?>" class="text-decoration-none">
                        <div class="movie-card">
                            <?php 
                            $poster_url = $movie['poster_path'] 
                                ? TMDB_IMAGE_BASE_URL . $movie['poster_path'] 
                                : 'assets/img/no-poster.jpg';
                            ?>
                            <img src="<?php echo $poster_url; ?>" alt="<?php echo htmlspecialchars($movie['movie_title']); ?>" onerror="this.src='assets/img/no-poster.jpg'">
                            
                            <?php if (isset($movie['source']) && $movie['source'] == 'tmdb'): ?>
                                <div class="movie-rating">
                                    <i class="fas fa-star me-1 small"></i><?php echo number_format($movie['avg_rating'], 1); ?>
                                </div>
                            <?php else: ?>
                                <div class="movie-rating">
                                    <?php echo number_format($movie['avg_rating'], 1); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <div class="movie-title"><?php echo $movie['movie_title']; ?></div>
                                <div class="d-flex justify-content-between">
                                    <div class="movie-year">
                                        <?php echo $movie['release_date'] ? date('Y', strtotime($movie['release_date'])) : 'N/A'; ?>
                                    </div>
                                    <div class="new-badge">
                                        <span class="badge bg-danger">New</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Right Control Arrow -->
        <button class="card-slider-control-next d-none d-md-block" aria-label="Next">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>
<?php endif; ?>

<!-- Top Rated Movies Section -->
<?php if (!empty($top_movies)): ?>
<div class="content-row">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4"><i class="fas fa-trophy me-2"></i>Top Rated Movies</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary view-all-btn" data-section="top-rated">
                See All
            </button>
        </div>
    </div>
    
    <div class="position-relative">
        <!-- Left Control Arrow -->
        <button class="card-slider-control-prev d-none d-md-block" aria-label="Previous">
            <i class="fas fa-chevron-left"></i>
        </button>
        
        <!-- Card Slider -->
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
                            <img src="<?php echo $poster_url; ?>" alt="<?php echo htmlspecialchars($movie['movie_title']); ?>" onerror="this.src='assets/img/no-poster.jpg'">
                            
                            <div class="movie-rating">
                                <?php echo number_format($movie['avg_rating'], 1); ?>
                            </div>
                            
                            <div class="card-body">
                                <div class="movie-title"><?php echo $movie['movie_title']; ?></div>
                                <div class="movie-year">
                                    <?php echo $movie['release_date'] ? date('Y', strtotime($movie['release_date'])) : 'N/A'; ?>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Right Control Arrow -->
        <button class="card-slider-control-next d-none d-md-block" aria-label="Next">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>
<?php endif; ?>

<!-- Most Popular Movies Section -->
<?php if (!empty($popular_movies)): ?>
<div class="content-row">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4"><i class="fas fa-fire me-2"></i>Most Popular</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary view-all-btn" data-section="most-popular">
                See All
            </button>
        </div>
    </div>
    
    <div class="position-relative">
        <!-- Left Control Arrow -->
        <button class="card-slider-control-prev d-none d-md-block" aria-label="Previous">
            <i class="fas fa-chevron-left"></i>
        </button>
        
        <!-- Card Slider -->
        <div class="card-slider">
            <?php foreach ($popular_movies as $movie): ?>
                <div class="movie-card-container">
                    <a href="review.php?id=<?php echo $movie['movie_id']; ?>" class="text-decoration-none">
                        <div class="movie-card">
                            <?php 
                            $poster_url = $movie['poster_path'] 
                                ? TMDB_IMAGE_BASE_URL . $movie['poster_path'] 
                                : 'assets/img/no-poster.jpg';
                            ?>
                            <img src="<?php echo $poster_url; ?>" alt="<?php echo htmlspecialchars($movie['movie_title']); ?>" onerror="this.src='assets/img/no-poster.jpg'">
                            
                            <div class="movie-rating">
                                <?php echo number_format($movie['avg_rating'], 1); ?>
                            </div>
                            
                            <div class="card-body">
                                <div class="movie-title"><?php echo $movie['movie_title']; ?></div>
                                <div class="d-flex justify-content-between">
                                    <div class="movie-year">
                                        <?php echo $movie['release_date'] ? date('Y', strtotime($movie['release_date'])) : 'N/A'; ?>
                                    </div>
                                    <div>
                                        <i class="fas fa-users me-1 small"></i>
                                        <span class="small"><?php echo $movie['review_count']; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Right Control Arrow -->
        <button class="card-slider-control-next d-none d-md-block" aria-label="Next">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>
<?php endif; ?>

<!-- Recently Rated Movies Section -->
<?php if (!empty($recent_movies)): ?>
<div class="content-row">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4"><i class="fas fa-clock me-2"></i>Recently Rated</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary view-all-btn" data-section="recently-rated">
                See All
            </button>
        </div>
    </div>
    
    <div class="position-relative">
        <!-- Left Control Arrow -->
        <button class="card-slider-control-prev d-none d-md-block" aria-label="Previous">
            <i class="fas fa-chevron-left"></i>
        </button>
        
        <!-- Card Slider -->
        <div class="card-slider">
            <?php foreach ($recent_movies as $movie): ?>
                <div class="movie-card-container">
                    <a href="review.php?id=<?php echo $movie['movie_id']; ?>" class="text-decoration-none">
                        <div class="movie-card">
                            <?php 
                            $poster_url = $movie['poster_path'] 
                                ? TMDB_IMAGE_BASE_URL . $movie['poster_path'] 
                                : 'assets/img/no-poster.jpg';
                            ?>
                            <img src="<?php echo $poster_url; ?>" alt="<?php echo htmlspecialchars($movie['movie_title']); ?>" onerror="this.src='assets/img/no-poster.jpg'">
                            
                            <div class="movie-rating">
                                <?php echo number_format($movie['avg_rating'], 1); ?>
                            </div>
                            
                            <div class="card-body">
                                <div class="movie-title"><?php echo $movie['movie_title']; ?></div>
                                <div class="movie-year">
                                    <?php echo $movie['release_date'] ? date('Y', strtotime($movie['release_date'])) : 'N/A'; ?>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Right Control Arrow -->
        <button class="card-slider-control-next d-none d-md-block" aria-label="Next">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>
<?php endif; ?>

<!-- Trending Movies Section -->
<?php if (!empty($trending_movies)): ?>
<div class="content-row">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4"><i class="fas fa-chart-line me-2"></i>Trending This Week</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary view-all-btn" data-section="trending">
                See All
            </button>
        </div>
    </div>
    
    <div class="position-relative">
        <!-- Left Control Arrow -->
        <button class="card-slider-control-prev d-none d-md-block" aria-label="Previous">
            <i class="fas fa-chevron-left"></i>
        </button>
        
        <!-- Card Slider -->
        <div class="card-slider">
            <?php foreach ($trending_movies as $movie): ?>
                <div class="movie-card-container">
                    <a href="review.php?id=<?php echo $movie['movie_id']; ?>" class="text-decoration-none">
                        <div class="movie-card">
                            <?php 
                            $poster_url = $movie['poster_path'] 
                                ? TMDB_IMAGE_BASE_URL . $movie['poster_path'] 
                                : 'assets/img/no-poster.jpg';
                            ?>
                            <img src="<?php echo $poster_url; ?>" alt="<?php echo htmlspecialchars($movie['movie_title']); ?>" onerror="this.src='assets/img/no-poster.jpg'">
                            
                            <?php if (isset($movie['source']) && $movie['source'] == 'tmdb'): ?>
                                <div class="movie-rating">
                                    <i class="fas fa-star me-1 small"></i><?php echo number_format($movie['avg_rating'], 1); ?>
                                </div>
                            <?php else: ?>
                                <div class="movie-rating">
                                    <?php echo number_format($movie['avg_rating'], 1); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <div class="movie-title"><?php echo $movie['movie_title']; ?></div>
                                <div class="d-flex justify-content-between">
                                    <div class="movie-year">
                                        <?php echo $movie['release_date'] ? date('Y', strtotime($movie['release_date'])) : 'N/A'; ?>
                                    </div>
                                    <div class="trending-badge">
                                        <i class="fas fa-arrow-trend-up text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Right Control Arrow -->
    <!-- Right Control Arrow -->
    <button class="card-slider-control-next d-none d-md-block" aria-label="Next">
        <i class="fas fa-chevron-right"></i>
    </button>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Expanded View Sections (initially hidden) -->
    <div id="section-expanded-view" class="d-none">
        <div class="content-row">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h4 section-title"></h2>
                <button class="btn btn-sm btn-outline-secondary return-btn">
                    <i class="fas fa-arrow-left me-1"></i>Back to Categories
                </button>
            </div>
            
            <div class="section-content row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3">
                <!-- Movies will be loaded here -->
            </div>
        </div>
    </div>
    
    <!-- Write a Review CTA -->
    <div class="content-row">
        <div class="card">
            <div class="card-body text-center p-4">
                <i class="fas fa-pen-fancy fa-3x text-primary mb-3"></i>
                <h4>Share Your Thoughts</h4>
                <p class="text-muted mb-4">Can't find what you're looking for? Search for any movie and share your review with our community!</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#searchModal">
                    <i class="fas fa-search me-1"></i>Find a Movie to Review
                </button>
            </div>
        </div>
    </div>
    
    <!-- Search Modal -->
    <div class="modal fade" id="searchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Find a Movie to Review</h5>
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
        // Initialize sliders
        $('.card-slider-control-prev').on('click', function() {
            const slider = $(this).closest('.content-row').find('.card-slider');
            slider.animate({
                scrollLeft: '-=600'
            }, 300);
        });
        
        $('.card-slider-control-next').on('click', function() {
            const slider = $(this).closest('.content-row').find('.card-slider');
            slider.animate({
                scrollLeft: '+=600'
            }, 300);
        });
        
        // View All button functionality
        $('.view-all-btn').on('click', function() {
            const section = $(this).data('section');
            
            // Hide all content rows
            $('.content-row').addClass('d-none');
            
            // Show expanded view
            $('#section-expanded-view').removeClass('d-none');
            
            // Set title based on section
            let title = "";
            switch(section) {
                case 'new-releases':
                    title = '<i class="fas fa-film me-2"></i>New Releases';
                    break;
                case 'top-rated':
                    title = '<i class="fas fa-trophy me-2"></i>Top Rated Movies';
                    break;
                case 'most-popular':
                    title = '<i class="fas fa-fire me-2"></i>Most Popular Movies';
                    break;
                case 'recently-rated':
                    title = '<i class="fas fa-clock me-2"></i>Recently Rated Movies';
                    break;
                case 'trending':
                    title = '<i class="fas fa-chart-line me-2"></i>Trending This Week';
                    break;
            }
            
            $('.section-title').html(title);
            
            // Clone movies from the corresponding section to the expanded view
            const sectionMovies = $(`[data-section="${section}"]`).closest('.content-row').find('.movie-card-container');
            const expandedContent = $('.section-content');
            
            expandedContent.empty();
            
            sectionMovies.each(function() {
                // Create a new column for the grid
                const col = $('<div class="col"></div>');
                
                // Clone the movie card
                const clone = $(this).clone();
                
                // Append to the expanded view
                col.append(clone);
                expandedContent.append(col);
            });
        });
        
        // Return button functionality
        $('.return-btn').on('click', function() {
            // Hide expanded view
            $('#section-expanded-view').addClass('d-none');
            
            // Show all content rows
            $('.content-row').removeClass('d-none');
        });
        
        // Movie search in hero section
        let searchTimeoutHero;
        
        $('#movie-search-hero').on('input', function() {
            const query = $(this).val().trim();
            
            // Clear previous timeout
            clearTimeout(searchTimeoutHero);
            
            // Clear results if query is empty
            if (query === '') {
                $('#search-results-hero').empty();
                return;
            }
            
            // Set a timeout to avoid sending too many requests while typing
            searchTimeoutHero = setTimeout(function() {
                $.ajax({
                    url: 'search.php',
                    method: 'GET',
                    data: { query: query },
                    dataType: 'json',
                    success: function(data) {
                        const results = data.results || [];
                        displaySearchResultsHero(results);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error searching movies:', error);
                    }
                });
            }, 300); // 300ms delay
        });
        
        // Function to display search results in hero section
        function displaySearchResultsHero(results) {
            const resultsContainer = $('#search-results-hero');
            resultsContainer.empty();
            
            if (results.length === 0) {
                resultsContainer.append('<p class="text-center p-3 rounded">No movies found</p>');
                return;
            }
            
            // Create a list group for styling
            let listGroup = $('<div class="list-group shadow"></div>');
            
            // Display top 6 results
            const maxResults = Math.min(results.length, 6);
            
            for (let i = 0; i < maxResults; i++) {
                const movie = results[i];
                const posterUrl = movie.poster_path 
                    ? 'https://image.tmdb.org/t/p/w200' + movie.poster_path 
                    : 'assets/img/no-poster.jpg';
                
                const item = $(`
                    <a href="review.php?id=${movie.id}" class="list-group-item list-group-item-action">
                        <div class="d-flex align-items-center">
                            <img src="${posterUrl}" alt="${movie.title}" onerror="this.src='assets/img/no-poster.jpg'" 
                                 style="width: 60px; height: 90px; border-radius: 4px; margin-right: 15px; object-fit: cover;">
                            <div>
                                <h6 class="mb-0">${movie.title}</h6>
                                <small>${movie.release_date ? movie.release_date.substring(0, 4) : 'Unknown year'}</small>
                            </div>
                        </div>
                    </a>
                `);
                
                listGroup.append(item);
            }
            
            resultsContainer.append(listGroup);
        }
        
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
                                <img src="${posterUrl}" alt="${movie.title}" class="img-fluid" onerror="this.src='assets/img/no-poster.jpg'">
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
        
        // Hide search results when clicking elsewhere
        $(document).on('click', function(event) {
            if (!$(event.target).closest('#movie-search-hero, #search-results-hero').length) {
                $('#search-results-hero').empty();
            }
        });
        
        // Make posters in hero banner clickable
        $('.poster').on('click', function() {
            const movieId = $(this).data('movie-id');
            if (movieId) {
                window.location.href = `review.php?id=${movieId}`;
            }
        });
        
        // Make modals dismiss when clicking outside on mobile
        $(document).on('click touchstart', '.modal', function(e) {
            if ($(e.target).hasClass('modal')) {
                $(this).modal('hide');
            }
        });
        
        // Add hover effects to movie cards
        $('.movie-card-container').hover(
            function() {
                $(this).css('z-index', '10');
            },
            function() {
                $(this).css('z-index', '1');
            }
        );
    });
    </script>
    
    <?php include 'templates/footer.php'; ?>
