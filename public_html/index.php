<?php 
require_once '../private/functions.php';

// Function to get recent reviews
function get_recent_reviews($limit = 6) {
    global $conn;
    
    $query = "SELECT 
                r.*,
                u.username
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
        
        $reviews[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $reviews;
}

// Function to get top rated movies
function get_top_rated_movies($limit = 4) {
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
        
        $movies[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $movies;
}

// Get recent reviews and top rated movies
$recent_reviews = get_recent_reviews();
$top_movies = get_top_rated_movies();

include 'templates/header.php'; 
?>

<div class="jumbotron bg-dark text-white p-5 mb-4 rounded">
    <h1 class="display-4">Rate Your Favorite Movies</h1>
    <p class="lead">Share your opinions and discover what others think about the latest blockbusters and classic films.</p>
    
    <div class="row justify-content-center mt-4">
        <div class="col-md-8">
            <div class="input-group">
                <input type="text" class="form-control form-control-lg" id="movie-search-hero" placeholder="Search for a movie...">
                <button class="btn btn-primary btn-lg" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <div id="search-results-hero" class="list-group mt-2 position-absolute w-100 z-index-dropdown"></div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i>Recent Reviews
                </h5>
                <a href="movies.php" class="btn btn-sm btn-light">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (count($recent_reviews) > 0): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_reviews as $review): ?>
                            <div class="list-group-item">
                                <div class="d-flex">
                                    <?php 
                                    $poster_url = $review['poster_path'] 
                                        ? TMDB_IMAGE_BASE_URL . $review['poster_path'] 
                                        : 'assets/img/no-poster.jpg';
                                    ?>
                                    <a href="review.php?id=<?php echo $review['movie_id']; ?>" class="me-3">
                                        <img src="<?php echo $poster_url; ?>" alt="<?php echo $review['movie_title']; ?>" 
                                            style="width: 70px; height: 105px; object-fit: cover; border-radius: 8px;">
                                    </a>
                                    <div>
                                        <div class="d-flex align-items-center mb-1">
                                            <a href="review.php?id=<?php echo $review['movie_id']; ?>" class="text-decoration-none">
                                                <h5 class="mb-0"><?php echo $review['movie_title']; ?></h5>
                                            </a>
                                            <span class="badge bg-primary ms-2"><?php echo $review['rating']; ?>/10</span>
                                        </div>
                                        <p class="text-muted small mb-2">
                                            <a href="profile.php?id=<?php echo $review['user_id']; ?>" class="text-decoration-none">
                                                <i class="fas fa-user-circle me-1"></i><?php echo $review['username']; ?>
                                            </a> • 
                                            <?php echo $review['release_date'] ? date('Y', strtotime($review['release_date'])) : 'N/A'; ?> •
                                            <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                        </p>
                                        <p class="mb-0">
                                            <?php 
                                            echo !empty($review['review_text']) 
                                                ? (strlen($review['review_text']) > 150 
                                                    ? substr($review['review_text'], 0, 150) . '...' 
                                                    : $review['review_text']) 
                                                : '<em class="text-muted">No written review</em>'; 
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center p-4">
                        <p>No reviews yet. Be the first to review a movie!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-trophy me-2"></i>Top Rated Movies
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (count($top_movies) > 0): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($top_movies as $movie): ?>
                            <a href="review.php?id=<?php echo $movie['movie_id']; ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex align-items-center">
                                    <?php 
                                    $poster_url = $movie['poster_path'] 
                                        ? TMDB_IMAGE_BASE_URL . $movie['poster_path'] 
                                        : 'assets/img/no-poster.jpg';
                                    ?>
                                    <img src="<?php echo $poster_url; ?>" alt="<?php echo $movie['movie_title']; ?>" 
                                         class="me-3" style="width: 50px; height: 75px; object-fit: cover; border-radius: 4px;">
                                    <div>
                                        <h6 class="mb-0"><?php echo $movie['movie_title']; ?></h6>
                                        <div class="d-flex align-items-center mt-1">
                                            <div class="text-warning me-2">
                                                <?php 
                                                $rating = round($movie['avg_rating']);
                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= floor($rating/2)) {
                                                        echo '<i class="fas fa-star"></i>';
                                                    } elseif ($i == ceil($rating/2) && $rating % 2 != 0) {
                                                        echo '<i class="fas fa-star-half-alt"></i>';
                                                    } else {
                                                        echo '<i class="far fa-star"></i>';
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo number_format($movie['avg_rating'], 1); ?>/10
                                                (<?php echo $movie['review_count']; ?> reviews)
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center p-4">
                        <p>No rated movies yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!is_logged_in()): ?>
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-user-plus me-2"></i>Join Our Community
                </h5>
            </div>
            <div class="card-body">
                <p>Create an account to review your favorite movies and engage with other movie lovers.</p>
                <div class="d-grid gap-2">
                    <a href="register.php" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i>Register
                    </a>
                    <a href="login.php" class="btn btn-outline-primary">
                        <i class="fas fa-sign-in-alt me-1"></i>Login
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Script for search functionality in the hero section
$(document).ready(function() {
    // Movie search with delay to avoid too many requests
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
        }, 500); // 500ms delay
    });
    
    // Function to display search results in hero section
    function displaySearchResultsHero(results) {
        const resultsContainer = $('#search-results-hero');
        resultsContainer.empty();
        
        if (results.length === 0) {
            resultsContainer.append('<p class="text-center p-3 bg-white shadow-sm rounded">No movies found</p>');
            return;
        }
        
        // Display top 5 results
        const maxResults = Math.min(results.length, 5);
        
        for (let i = 0; i < maxResults; i++) {
            const movie = results[i];
            const posterUrl = movie.poster_path 
                ? 'https://image.tmdb.org/t/p/w200' + movie.poster_path 
                : 'assets/img/no-poster.jpg';
            
            const item = $(`
                <a href="review.php?id=${movie.id}" class="list-group-item list-group-item-action bg-white shadow-sm">
                    <div class="d-flex align-items-center">
                        <img src="${posterUrl}" alt="${movie.title}" onerror="this.src='assets/img/no-poster.jpg'" 
                             style="width: 50px; height: 75px; object-fit: cover; border-radius: 4px; margin-right: 15px;">
                        <div>
                            <h6 class="mb-0">${movie.title}</h6>
                            <small class="text-muted">${movie.release_date ? movie.release_date.substring(0, 4) : 'Unknown year'}</small>
                        </div>
                    </div>
                </a>
            `);
            
            resultsContainer.append(item);
        }
    }
    
    // Hide search results when clicking elsewhere
    $(document).on('click', function(event) {
        if (!$(event.target).closest('#movie-search-hero, #search-results-hero').length) {
            $('#search-results-hero').empty();
        }
    });
});
</script>

<?php include 'templates/footer.php'; ?>