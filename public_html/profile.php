<?php
require_once '../private/functions.php';

// Check if user_id is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$user_id = intval($_GET['id']);

// Function to get user info
function get_user_info($user_id) {
    global $conn;
    
    $query = "SELECT 
                u.user_id,
                u.username,
                u.email,
                u.created_at,
                u.profile_picture,
                COUNT(r.review_id) as review_count,
                AVG(r.rating) as avg_rating
              FROM 
                users u
              LEFT JOIN 
                reviews r ON u.user_id = r.user_id
              WHERE 
                u.user_id = ?
              GROUP BY 
                u.user_id, u.username, u.email, u.created_at, u.profile_picture";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $user;
}

// Function to get user's reviews
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

// Get user info and reviews
$user = get_user_info($user_id);
if (!$user) {
    header("Location: users.php");
    exit();
}

$reviews = get_user_reviews($user_id);

include 'templates/header.php';
?>

<div class="profile-section">
    <div class="container mt-4 pt-3">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card mb-4 text-center">
                    <div class="card-body p-4">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img src="<?php echo $user['profile_picture']; ?>" alt="<?php echo $user['username']; ?>" class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                        <?php else: ?>
                            <div class="avatar rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 100px; height: 100px; font-size: 40px;">
                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        
                        <h3 class="mb-1"><?php echo $user['username']; ?></h3>
                        <p class="text-muted">
                            <i class="fas fa-calendar-alt me-1"></i> 
                            <span class="compact-date">Member since <?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                            <span class="compact-date-mobile">Joined <?php echo date('n/j/y', strtotime($user['created_at'])); ?></span>
                        </p>
                        
                        <div class="d-flex justify-content-center gap-4 my-3">
                            <div class="text-center">
                                <div class="stat-value"><?php echo $user['review_count']; ?></div>
                                <div class="stat-label">Reviews</div>
                            </div>
                            <div class="text-center">
                                <div class="stat-value"><?php echo $user['avg_rating'] ? number_format($user['avg_rating'], 1) : '-'; ?></div>
                                <div class="stat-label">Avg Rating</div>
                            </div>
                        </div>
                        
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id): ?>
                            <a href="edit_profile.php" class="btn btn-outline-primary mt-2">
                                <i class="fas fa-edit me-1"></i>Edit Profile
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="content-row">
                    <h4 class="mb-3">
                        <i class="fas fa-star me-2"></i>
                        <?php echo $user['username']; ?>'s Reviews
                    </h4>
                    
                    <?php if (count($reviews) > 0): ?>
                        <div class="movies-list">
                            <?php foreach ($reviews as $review): ?>
                                <a href="review.php?id=<?php echo $review['movie_id']; ?>" class="text-decoration-none">
                                    <div class="movie-list-item">
                                        <?php 
                                        $poster_url = $review['poster_path'] 
                                            ? TMDB_IMAGE_BASE_URL . $review['poster_path'] 
                                            : 'assets/img/no-poster.jpg';
                                        ?>
                                        <img src="<?php echo $poster_url; ?>" alt="<?php echo $review['movie_title']; ?>" onerror="this.src='assets/img/no-poster.jpg'">
                                        
                                        <div class="movie-info">
                                            <div class="d-flex justify-content-between">
                                                <h5 class="movie-title mb-0"><?php echo $review['movie_title']; ?></h5>
                                                <span class="rating-badge"><?php echo number_format($review['rating'], 1); ?></span>
                                            </div>
                                            <p class="small mb-1 text-muted">
                                                <?php echo $review['release_date'] ? date('Y', strtotime($review['release_date'])) : 'N/A'; ?> • 
                                                <span class="compact-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                                                <span class="compact-date-mobile"><?php echo date('n/j/y', strtotime($review['created_at'])); ?></span>
                                            </p>
                                            <p class="mb-0 text-light">
                                                <?php 
                                                echo !empty($review['review_text']) 
                                                    ? (strlen($review['review_text']) > 150 
                                                        ? substr($review['review_text'], 0, 150) . '...' 
                                                        : $review['review_text']) 
                                                    : '<em class="text-muted">No written review</em>'; 
                                                ?>
                                                <?php if (strlen($review['review_text']) > 150): ?>
                                                    <a href="review.php?id=<?php echo $review['movie_id']; ?>" class="text-decoration-none ms-1">Read more</a>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-body text-center p-5">
                                <i class="fas fa-film fa-3x text-muted mb-3"></i>
                                <h5>No reviews yet</h5>
                                <p class="text-muted">This user hasn't submitted any movie reviews yet.</p>
                                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id): ?>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#searchModal">
                                        <i class="fas fa-pen me-1"></i>Write Your First Review
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search Modal -->
<?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id): ?>
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
    
    // Make modals dismiss when clicking outside on mobile
    $(document).on('click touchstart', '.modal', function(e) {
        if ($(e.target).hasClass('modal')) {
            $(this).modal('hide');
        }
    });
});
</script>
<?php endif; ?>

<?php include 'templates/footer.php'; ?>