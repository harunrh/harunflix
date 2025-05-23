<?php
require_once '../private/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Function to get user reviews
function get_user_reviews($user_id) {
    global $conn;
    
    $query = "SELECT 
                r.*,
                u.username
              FROM 
                reviews r
              JOIN 
                users u ON r.user_id = u.user_id
              WHERE 
                r.user_id = ?
              ORDER BY 
                r.created_at DESC";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $reviews = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Get movie details for each review
        $movie_details = get_movie_details($row['movie_id']);
        
        // Add poster path to our results
        $row['poster_path'] = $movie_details['poster_path'] ?? null;
        $row['backdrop_path'] = $movie_details['backdrop_path'] ?? null;
        $row['release_date'] = $movie_details['release_date'] ?? null;
        
        $reviews[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $reviews;
}

// Get user reviews
$reviews = get_user_reviews($user_id);

include 'templates/header.php';
?>

<div class="content-row">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3">
            <i class="fas fa-star me-2"></i>My Reviews
        </h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#searchModal">
            <i class="fas fa-pen me-1"></i>Write a Review
        </button>
    </div>
    
    <div class="card">
        <div class="card-body p-0">
            <?php if (count($reviews) > 0): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($reviews as $review): ?>
                        <div class="list-group-item p-3">
                            <div class="d-flex">
                                <?php 
                                $poster_url = $review['poster_path'] 
                                    ? TMDB_IMAGE_BASE_URL . $review['poster_path'] 
                                    : 'assets/img/no-poster.jpg';
                                ?>
                                <a href="review.php?id=<?php echo $review['movie_id']; ?>" class="me-3">
                                    <img src="<?php echo $poster_url; ?>" alt="<?php echo $review['movie_title']; ?>" style="width: 80px; height: 120px; object-fit: cover; border-radius: 4px;" class="shadow-sm" onerror="this.src='assets/img/no-poster.jpg'">
                                </a>
                                
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between mb-2">
                                        <a href="review.php?id=<?php echo $review['movie_id']; ?>" class="text-decoration-none">
                                            <h5 class="mb-0"><?php echo $review['movie_title']; ?></h5>
                                        </a>
                                        <span class="badge bg-primary"><?php echo number_format($review['rating'], 1); ?>/10</span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between mb-2">
                                        <small class="text-muted">
                                            <?php echo $review['release_date'] ? date('Y', strtotime($review['release_date'])) : 'N/A'; ?>
                                        </small>
                                        <small class="text-muted">
                                            <span class="compact-date">Reviewed on <?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                                            <span class="compact-date-mobile"><?php echo date('n/j/y', strtotime($review['created_at'])); ?></span>
                                        </small>
                                    </div>
                                    
                                    <p class="mb-0 text-light">
                                        <?php echo !empty($review['review_text']) ? $review['review_text'] : '<em class="text-muted">No written review</em>'; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center p-5">
                    <i class="fas fa-film fa-3x text-muted mb-3"></i>
                    <h4>No Reviews Yet</h4>
                    <p class="text-muted">You haven't reviewed any movies yet.</p>
                    <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#searchModal">
                        <i class="fas fa-pen me-1"></i>Write Your First Review
                    </button>
                </div>
            <?php endif; ?>
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
});
</script>

<?php include 'templates/footer.php'; ?>