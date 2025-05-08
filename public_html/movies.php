<?php
require_once '../private/functions.php';

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
function get_top_rated_movies($limit = 5) {
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

// Get all reviewed movies
$movies = get_all_reviewed_movies();
$top_movies = get_top_rated_movies();

// Choose a featured movie for the hero (top rated with backdrop)
$featured_movie = null;
if (!empty($top_movies)) {
    foreach ($top_movies as $movie) {
        if (!empty($movie['backdrop_path'])) {
            $featured_movie = $movie;
            break;
        }
    }
    
    // If none have backdrops, just use the first one
    if (empty($featured_movie)) {
        $featured_movie = $top_movies[0];
    }
}

include 'templates/header.php';
?>

<?php if ($featured_movie): ?>
<!-- Hero Section with Featured Movie -->
<div class="hero-section" style="background-image: url('<?php echo TMDB_IMAGE_BASE_URL . ($featured_movie['backdrop_path'] ?? $featured_movie['poster_path']); ?>');">
    <div class="container hero-content">
        <div class="row">
            <div class="col-md-7">
                <div class="mb-2">
                    <span class="badge bg-danger">Top Rated</span>
                </div>
                <h1 class="hero-title"><?php echo $featured_movie['movie_title']; ?></h1>
                <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-primary me-2"><?php echo number_format($featured_movie['avg_rating'], 1); ?>/10</span>
                    <span class="text-light me-3">
                        <?php echo $featured_movie['release_date'] ? date('Y', strtotime($featured_movie['release_date'])) : 'N/A'; ?>
                    </span>
                    <span class="text-light">
                        <i class="fas fa-users me-1"></i><?php echo $featured_movie['review_count']; ?> reviews
                    </span>
                </div>
                <p class="mb-4">
                    <?php echo !empty($featured_movie['overview']) ? 
                        (strlen($featured_movie['overview']) > 200 ? substr($featured_movie['overview'], 0, 200) . '...' : $featured_movie['overview']) 
                        : 'No overview available'; ?>
                </p>
                <div>
                    <a href="review.php?id=<?php echo $featured_movie['movie_id']; ?>" class="btn btn-primary me-2">
                        <i class="fas fa-play me-1"></i>View Reviews
                    </a>
                    <?php if (is_logged_in()): ?>
                    <a href="review.php?id=<?php echo $featured_movie['movie_id']; ?>" class="btn btn-outline-light">
                        <i class="fas fa-star me-1"></i>Rate this
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Categories Section -->
<div class="content-row">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4"><i class="fas fa-trophy me-2"></i>Top Rated Movies</h2>
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

<!-- All Movies Section -->
<div class="content-row">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4"><i class="fas fa-film me-2"></i>All Movies</h2>
        
        <div class="d-flex align-items-center">
            <button class="btn btn-sm btn-outline-primary me-2" id="grid-view">
                <i class="fas fa-th"></i>
            </button>
            <button class="btn btn-sm btn-outline-primary" id="list-view">
                <i class="fas fa-list"></i>
            </button>
        </div>
    </div>
    
    <div id="grid-container" class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3">
        <?php if (count($movies) > 0): ?>
            <?php foreach ($movies as $movie): ?>
                <div class="col">
                    <a href="review.php?id=<?php echo $movie['movie_id']; ?>" class="text-decoration-none">
                        <div class="movie-card h-100">
                            <?php 
                            $poster_url = $movie['poster_path'] 
                                ? TMDB_IMAGE_BASE_URL . $movie['poster_path'] 
                                : 'assets/img/no-poster.jpg';
                            ?>
                            <img src="<?php echo $poster_url; ?>" class="img-fluid" alt="<?php echo $movie['movie_title']; ?>">
                            
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
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <h4 class="alert-heading">No movies reviewed yet!</h4>
                    <p>Be the first to leave a review for your favorite movie.</p>
                    <hr>
                    <p class="mb-0">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#searchModal">
                            <i class="fas fa-search me-1"></i>Search for a movie
                        </button>
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div id="list-container" class="row d-none">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (count($movies) > 0): ?>
                            <?php foreach ($movies as $movie): ?>
                                <a href="review.php?id=<?php echo $movie['movie_id']; ?>" class="list-group-item list-group-item-action p-3">
                                    <div class="d-flex">
                                        <?php 
                                        $poster_url = $movie['poster_path'] 
                                            ? TMDB_IMAGE_BASE_URL . $movie['poster_path'] 
                                            : 'assets/img/no-poster.jpg';
                                        ?>
                                        <img src="<?php echo $poster_url; ?>" alt="<?php echo $movie['movie_title']; ?>" 
                                             style="width: 70px; height: 105px; object-fit: cover; border-radius: 4px;" class="me-3 shadow-sm">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-start">
                                                <h5 class="mb-1"><?php echo $movie['movie_title']; ?></h5>
                                                <span class="badge bg-primary"><?php echo number_format($movie['avg_rating'], 1); ?>/10</span>
                                            </div>
                                            <p class="mb-1 small">
                                                <i class="fas fa-calendar-alt me-1"></i> 
                                                <?php echo $movie['release_date'] ? date('Y', strtotime($movie['release_date'])) : 'N/A'; ?> • 
                                                <i class="fas fa-comment me-1"></i> <?php echo $movie['review_count']; ?> reviews
                                            </p>
                                            <p class="mb-0 small">
                                                <?php echo !empty($movie['overview']) ? 
                                                    (strlen($movie['overview']) > 100 ? substr($movie['overview'], 0, 100) . '...' : $movie['overview']) 
                                                    : 'No overview available'; ?>
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="list-group-item p-4 text-center">
                                <i class="fas fa-film fa-3x text-muted mb-3"></i>
                                <h4>No movies reviewed yet!</h4>
                                <p class="text-muted">Be the first to leave a review for your favorite movie.</p>
                                <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#searchModal">
                                    <i class="fas fa-search me-1"></i>Search for a movie
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
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
    // Toggle between grid and list view
    $('#grid-view').click(function() {
        $('#grid-container').removeClass('d-none');
        $('#list-container').addClass('d-none');
        $(this).addClass('active');
        $('#list-view').removeClass('active');
    });
    
    $('#list-view').click(function() {
        $('#list-container').removeClass('d-none');
        $('#grid-container').addClass('d-none');
        $(this).addClass('active');
        $('#grid-view').removeClass('active');
    });
    
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