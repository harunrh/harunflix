<?php
require_once '../private/functions.php';

// Check if movie_id is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$movie_id = intval($_GET['id']);
$movie = get_movie_details($movie_id);
$reviews = get_movie_reviews($movie_id);

$error = '';
$success = '';

// Process review submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && is_logged_in()) {
    // Use floatval instead of intval to handle decimal ratings
    $rating = floatval($_POST['rating']);
    $review_text = sanitize_input($_POST['review_text']);
    $movie_title = sanitize_input($_POST['movie_title']);
    
    // Validate input
    if ($rating < 0 || $rating > 10) {
        $error = "Rating must be between 0 and 10";
    } else {
        // Save the review
        if (save_review($_SESSION['user_id'], $movie_id, $movie_title, $rating, $review_text)) {
            $success = "Your review has been submitted!";
            // Refresh reviews
            $reviews = get_movie_reviews($movie_id);
        } else {
            $error = "Failed to submit review. Please try again.";
        }
    }
}

include 'templates/header.php';
?>

<!-- Movie Header Section -->
<div class="container mt-5 pt-4">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-body p-0">
            <div class="row g-0">
                <div class="col-md-4 col-lg-3 text-center p-3">
                    <?php 
                    $poster_url = $movie['poster_path'] 
                        ? TMDB_IMAGE_BASE_URL . $movie['poster_path'] 
                        : 'assets/img/no-poster.jpg';
                    ?>
                    <img src="<?php echo $poster_url; ?>" alt="<?php echo $movie['title']; ?>" 
                         class="img-fluid rounded shadow" style="max-height: 300px;" 
                         onerror="this.src='assets/img/no-poster.jpg'">
                </div>
                <div class="col-md-8 col-lg-9 p-4">
                    <h1 class="h2 mb-2"><?php echo $movie['title']; ?></h1>
                    <div class="d-flex align-items-center flex-wrap mb-3">
                        <span class="badge bg-primary me-2 mb-1"><?php echo isset($movie['vote_average']) ? number_format($movie['vote_average'], 1) : 'N/A'; ?>/10 TMDB</span>
                        <span class="text-light me-3 mb-1"><?php echo $movie['release_date']; ?></span>
                        <?php if (isset($movie['runtime'])): ?>
                        <span class="text-light mb-1"><?php echo floor($movie['runtime']/60).'h '.($movie['runtime']%60).'m'; ?></span>
                        <?php endif; ?>
                    </div>
                    <p class="mb-4"><?php echo $movie['overview']; ?></p>
                    <?php if (isset($movie['genres']) && !empty($movie['genres'])): ?>
                    <div class="mb-3">
                        <?php 
                        foreach ($movie['genres'] as $genre) {
                            echo '<span class="badge bg-secondary me-1 mb-1">' . $genre['name'] . '</span>';
                        }
                        ?>
                    </div>
                    <?php endif; ?>
                    <?php if (is_logged_in()): ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reviewModal">
                        <i class="fas fa-star me-1"></i>Write a Review
                    </button>
                    <?php else: ?>
                    <a href="login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-1"></i>Login to Review
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="content-row">
                <h2 class="h4 mb-3"><i class="fas fa-star me-2"></i>Reviews for <?php echo $movie['title']; ?></h2>
                
                <div class="card">
                    <div class="card-body p-0">
                        <?php if (count($reviews) > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($reviews as $review): ?>
                                    <div class="list-group-item p-3">
                                        <div class="d-flex">
                                            <?php if (!empty($review['profile_picture'])): ?>
                                                <a href="profile.php?id=<?php echo $review['user_id']; ?>" class="me-3">
                                                    <img src="<?php echo $review['profile_picture']; ?>" class="rounded-circle" alt="<?php echo $review['username']; ?>" style="width: 50px; height: 50px; object-fit: cover;">
                                                </a>
                                            <?php else: ?>
                                                <a href="profile.php?id=<?php echo $review['user_id']; ?>" class="me-3">
                                                    <div class="avatar rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 20px;">
                                                        <?php echo strtoupper(substr($review['username'], 0, 1)); ?>
                                                    </div>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <div>
                                                        <a href="profile.php?id=<?php echo $review['user_id']; ?>" class="fw-bold text-decoration-none"><?php echo $review['username']; ?></a>
                                                        <span class="badge bg-primary ms-2"><?php echo number_format($review['rating'], 1); ?>/10</span>
                                                    </div>
                                                    <small class="text-muted">
                                                        <span class="compact-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                                                        <span class="compact-date-mobile"><?php echo date('n/j/y', strtotime($review['created_at'])); ?></span>
                                                    </small>
                                                </div>
                                                <p class="mb-0 text-light"><?php echo !empty($review['review_text']) ? $review['review_text'] : '<em class="text-muted">No written review</em>'; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center p-5">
                                <i class="fas fa-comment fa-3x text-muted mb-3"></i>
                                <h5>No Reviews Yet</h5>
                                <p class="text-muted">Be the first to review this movie!</p>
                                <?php if (is_logged_in()): ?>
                                <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                    <i class="fas fa-star me-1"></i>Write a Review
                                </button>
                                <?php else: ?>
                                <a href="login.php" class="btn btn-primary mt-2">
                                    <i class="fas fa-sign-in-alt me-1"></i>Login to Review
                                </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Movie Stats Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Review Stats</h5>
                </div>
                <div class="card-body">
                    <?php if (count($reviews) > 0): ?>
                        <div class="mb-3">
                            <h6 class="mb-2">Rating Distribution</h6>
                            <?php
                            // Calculate rating distribution
                            $rating_counts = array_fill(1, 10, 0);
                            $avg_rating = 0;
                            foreach ($reviews as $review) {
                                $rating = round($review['rating']);
                                $rating_counts[$rating]++;
                                $avg_rating += $review['rating'];
                            }
                            $avg_rating = $avg_rating / count($reviews);
                            
                            // Find max count for scaling
                            $max_count = max($rating_counts);
                            
                            // Display bar chart
                            for ($i = 10; $i >= 1; $i--): 
                                $percentage = ($max_count > 0) ? ($rating_counts[$i] / $max_count) * 100 : 0;
                            ?>
                            <div class="d-flex align-items-center mb-1">
                                <div style="width: 30px;"><?php echo $i; ?></div>
                                <div class="flex-grow-1 mx-2">
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar" role="progressbar" style="width: <?php echo $percentage; ?>%" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div style="width: 20px;"><?php echo $rating_counts[$i]; ?></div>
                            </div>
                            <?php endfor; ?>
                        </div>
                        
                        <div class="text-center">
                            <p class="mb-2">Average Rating</p>
                            <div class="display-4 fw-bold text-primary"><?php echo number_format($avg_rating, 1); ?></div>
                            <p class="text-muted">out of <?php echo count($reviews); ?> reviews</p>
                        </div>
                    <?php else: ?>
                        <div class="text-center p-4">
                            <p class="text-muted">No reviews yet for this movie.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Movie Details Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Movie Details</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Release Date</span>
                            <span><?php echo $movie['release_date']; ?></span>
                        </li>
                        <?php if (isset($movie['runtime'])): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Runtime</span>
                            <span><?php echo floor($movie['runtime']/60).'h '.($movie['runtime']%60).'m'; ?></span>
                        </li>
                        <?php endif; ?>
                        <?php if (isset($movie['vote_average'])): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>TMDB Rating</span>
                            <span class="d-flex align-items-center">
                                <?php echo number_format($movie['vote_average'], 1); ?>
                                <i class="fas fa-star text-warning ms-1"></i>
                            </span>
                        </li>
                        <?php endif; ?>
                        <?php if (isset($movie['vote_count'])): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>TMDB Votes</span>
                            <span><?php echo number_format($movie['vote_count']); ?></span>
                        </li>
                        <?php endif; ?>
                        <?php if (count($reviews) > 0): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>HarunFlix Rating</span>
                            <span class="d-flex align-items-center">
                                <?php echo number_format($avg_rating, 1); ?>
                                <i class="fas fa-star text-primary ms-1"></i>
                            </span>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal -->
<?php if (is_logged_in()): ?>
<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Review <?php echo $movie['title']; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="review.php?id=<?php echo $movie_id; ?>" method="post">
                <div class="modal-body">
                    <input type="hidden" name="movie_title" value="<?php echo $movie['title']; ?>">
                    
                    <div class="mb-3">
                        <label for="rating" class="form-label">Your Rating (0-10):</label>
                        <div class="rating-input-container">
                            <input type="number" class="form-control rating-input" id="rating" name="rating" min="0" max="10" step="0.5" value="8.0" required>
                            <span class="rating-suffix">/10</span>
                        </div>
                        <small class="text-muted">Ratings can be from 0 to 10 in steps of 0.5</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="review_text" class="form-label">Your Review:</label>
                        <textarea class="form-control" id="review_text" name="review_text" rows="4" placeholder="Write your thoughts about the movie..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'templates/footer.php'; ?>