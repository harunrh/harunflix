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
    if ($rating < 1 || $rating > 10) {
        $error = "Rating must be between 1 and 10";
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

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <img src="<?php echo TMDB_IMAGE_BASE_URL . $movie['poster_path']; ?>" class="card-img-top" alt="<?php echo $movie['title']; ?> Poster">
            <div class="card-body">
                <h5 class="card-title"><?php echo $movie['title']; ?></h5>
                <p class="card-text"><small class="text-muted">Release Date: <?php echo $movie['release_date']; ?></small></p>
                <p class="card-text"><?php echo $movie['overview']; ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (is_logged_in()): ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Write a Review</h5>
                </div>
                <div class="card-body">
                    <form action="review.php?id=<?php echo $movie_id; ?>" method="post">
                        <input type="hidden" name="movie_title" value="<?php echo $movie['title']; ?>">
                        
                        <div class="mb-3">
                            <label for="rating" class="form-label">Rating (1-10):</label>
                            <input type="number" class="form-control" id="rating" name="rating" min="1" max="10" step="0.1" required>
                            <small class="text-muted">You can use decimal values (e.g., 8.5)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="review_text" class="form-label">Your Review:</label>
                            <textarea class="form-control" id="review_text" name="review_text" rows="4"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Submit Review</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info mb-4">
                <p>Please <a href="login.php">login</a> or <a href="register.php">register</a> to submit your review.</p>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">All Reviews for <?php echo $movie['title']; ?></h5>
            </div>
            <div class="card-body">
                <?php if (count($reviews) > 0): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between">
                                <h6><?php echo $review['username']; ?></h6>
                                <span class="badge bg-primary"><?php echo number_format($review['rating'], 1); ?>/10</span>
                            </div>
                            <p><?php echo $review['review_text']; ?></p>
                            <small class="text-muted">Posted on <?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center">No reviews yet. Be the first to review this movie!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>