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
        $row['release_date'] = $movie_details['release_date'] ?? null;
        
        $movies[] = $row;
    }
    
    return $movies;
}

// Get all reviewed movies
$movies = get_all_reviewed_movies();

include 'templates/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <h2 class="display-6">
            <i class="fas fa-film me-2"></i>Reviewed Movies
        </h2>
        <p class="text-muted">All movies that have been reviewed by our community</p>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
    <?php if (count($movies) > 0): ?>
        <?php foreach ($movies as $movie): ?>
            <div class="col">
                <a href="review.php?id=<?php echo $movie['movie_id']; ?>" class="text-decoration-none">
                    <div class="card movie-card h-100">
                        <?php 
                        $poster_url = $movie['poster_path'] 
                            ? TMDB_IMAGE_BASE_URL . $movie['poster_path'] 
                            : 'assets/img/no-poster.jpg';
                        ?>
                        <img src="<?php echo $poster_url; ?>" class="card-img-top" alt="<?php echo $movie['movie_title']; ?>">
                        
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
                    <a href="index.php" class="btn btn-primary">Search for a movie</a>
                </p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'templates/footer.php'; ?>