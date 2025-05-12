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

include 'templates/header.php';
?>

<!-- Search Section (replacing hero) -->
<div class="search-container pt-5 pb-4 text-center">
    <div class="container">
        <h1 class="mb-4">Find Movies to Review</h1>
        <div class="row justify-content-center">
            <div class="col-md-8">
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

<!-- Top Rated Movies Section -->
<div class="content-row">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4"><i class="fas fa-trophy me-2"></i>Top Rated Movies</h2>
        
        <div class="d-flex align-items-center">
            <button class="btn btn-sm btn-outline-primary me-2" id="grid-view">
                <i class="fas fa-th"></i>
            </button>
            <button class="btn btn-sm btn-outline-primary active" id="list-view">
                <i class="fas fa-list"></i>
            </button>
        </div>
    </div>
    
    <!-- List View (Default) -->
    <div class="movies-list">
        <?php foreach ($top_movies as $movie): ?>
            <a href="review.php?id=<?php echo $movie['movie_id']; ?>" class="text-decoration-none">
                <div class="movie-list-item">
                    <?php 
                    $poster_url = $movie['poster_path'] 
                        ? TMDB_IMAGE_BASE_URL . $movie['poster_path'] 
                        : 'assets/img/no-poster.jpg';
                    ?>
                    <img src="<?php echo $poster_url; ?>" alt="<?php echo $movie['movie_title']; ?>" onerror="this.src='assets/img/no-poster.jpg'">
                    
                    <div class="movie-info">
                        <div class="d-flex justify-content-between">
                            <h5 class="movie-title mb-0"><?php echo $movie['movie_title']; ?></h5>
                            <span class="rating-badge"><?php echo number_format($movie['avg_rating'], 1); ?></span>
                        </div>
                        <p class="small mb-1 text-muted">
                            <?php echo $movie['release_date'] ? date('Y', strtotime($movie['release_date'])) : 'N/A'; ?> • 
                            <?php echo $movie['review_count']; ?> reviews
                        </p>
                        <p class="small mb-0 d-none d-md-block text-light">
                            <?php 
                            echo !empty($movie['overview']) 
                                ? (strlen($movie['overview']) > 150 
                                    ? substr($movie['overview'], 0, 150) . '...' 
                                    : $movie['overview']) 
                                : '<em class="text-muted">No overview available</em>'; 
                            ?>
                        </p>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
    
    <!-- Grid View (Initially Hidden) -->
    <div class="grid-container row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3 d-none">
        <?php foreach ($top_movies as $movie): ?>
            <div class="col">
                <a href="review.php?id=<?php echo $movie['movie_id']; ?>" class="text-decoration-none">
                    <div class="movie-card h-100">
                        <?php 
                        $poster_url = $movie['poster_path'] 
                            ? TMDB_IMAGE_BASE_URL . $movie['poster_path'] 
                            : 'assets/img/no-poster.jpg';
                        ?>
                        <img src="<?php echo $poster_url; ?>" alt="<?php echo $movie['movie_title']; ?>" 
                             class="img-fluid" onerror="this.src='assets/img/no-poster.jpg'">
                        
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
    </div>
    
    <!-- List View (Default) -->
    <div class="movies-list">
        <?php if (count($movies) > 0): ?>
            <?php foreach ($movies as $movie): ?>
                <a href="review.php?id=<?php echo $movie['movie_id']; ?>" class="text-decoration-none">
                    <div class="movie-list-item">
                        <?php 
                        $poster_url = $movie['poster_path'] 
                            ? TMDB_IMAGE_BASE_URL . $movie['poster_path'] 
                            : 'assets/img/no-poster.jpg';
                        ?>
                        <img src="<?php echo $poster_url; ?>" alt="<?php echo $movie['movie_title']; ?>" 
                             onerror="this.src='assets/img/no-poster.jpg'">
                        
                        <div class="movie-info">
                            <div class="d-flex justify-content-between">
                                <h5 class="movie-title mb-0"><?php echo $movie['movie_title']; ?></h5>
                                <span class="rating-badge"><?php echo number_format($movie['avg_rating'], 1); ?></span>
                            </div>
                            <p class="small mb-1 text-muted">
                                <?php echo $movie['release_date'] ? date('Y', strtotime($movie['release_date'])) : 'N/A'; ?> • 
                                <?php echo $movie['review_count']; ?> reviews
                            </p>
                            <p class="small mb-0 d-none d-md-block text-light">
                                <?php 
                                echo !empty($movie['overview']) 
                                    ? (strlen($movie['overview']) > 150 
                                        ? substr($movie['overview'], 0, 150) . '...' 
                                        : $movie['overview']) 
                                    : '<em class="text-muted">No overview available</em>'; 
                                ?>
                            </p>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
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
        <?php endif; ?>
    </div>
    
    <!-- Grid View (Initially Hidden) -->
    <div class="grid-container row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3 d-none">
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
                            <img src="<?php echo $poster_url; ?>" alt="<?php echo $movie['movie_title']; ?>" 
                                 class="img-fluid" onerror="this.src='assets/img/no-poster.jpg'">
                            
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
    
    // Hero search functionality
    $('#movie-search-hero').on('input', function() {
        const query = $(this).val().trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeoutModal);
        
        // Clear results if query is empty
        if (query === '') {
            $('#search-results-hero').empty();
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
                    displaySearchResultsHero(results);
                },
                error: function(xhr, status, error) {
                    console.error('Error searching movies:', error);
                }
            });
        }, 300); // 300ms delay
    });
    
    // Function to display search results in the hero section
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
    
    // Hide search results when clicking elsewhere
    $(document).on('click', function(event) {
        if (!$(event.target).closest('#movie-search-hero, #search-results-hero').length) {
            $('#search-results-hero').empty();
        }
    });
    
    // Make modals dismiss when clicking outside on mobile
    $(document).on('click touchstart', '.modal', function(e) {
        if ($(e.target).hasClass('modal')) {
            $(this).modal('hide');
        }
    });
});
</script>

<?php include 'templates/footer.php'; ?>