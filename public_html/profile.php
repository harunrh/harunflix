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

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <?php if (!empty($user['profile_picture'])): ?>
                    <img src="<?php echo $user['profile_picture']; ?>" alt="<?php echo $user['username']; ?>" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                <?php else: ?>
                    <div class="avatar bg-primary rounded-circle text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 150px; height: 150px; font-size: 60px;">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <h4 class="user-name"><?php echo $user['username']; ?></h4>
                <p class="text-muted">
                    <i class="fas fa-calendar-alt me-1"></i> Joined <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                </p>
                
                <div class="user-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $user['review_count']; ?></div>
                        <div class="stat-label">Reviews</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $user['avg_rating'] ? number_format($user['avg_rating'], 1) : '-'; ?></div>
                        <div class="stat-label">Avg Rating</div>
                    </div>
                </div>
                
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id): ?>
                    <a href="edit_profile.php" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-1"></i>Edit Profile
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-star me-1"></i>
                    <?php echo $user['username']; ?>'s Reviews
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (count($reviews) > 0): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($reviews as $review): ?>
                            <div class="list-group-item p-0">
                                <div class="d-flex p-3">
                                    <?php 
                                    $poster_url = $review['poster_path'] 
                                        ? TMDB_IMAGE_BASE_URL . $review['poster_path'] 
                                        : 'assets/img/no-poster.jpg';
                                    ?>
                                    <a href="review.php?id=<?php echo $review['movie_id']; ?>" class="me-3">
                                        <img src="<?php echo $poster_url; ?>" alt="<?php echo $review['movie_title']; ?>" 
                                             style="width: 80px; height: 120px; object-fit: cover; border-radius: 8px;">
                                    </a>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <a href="review.php?id=<?php echo $review['movie_id']; ?>" class="text-decoration-none">
                                                <h5 class="mb-0"><?php echo $review['movie_title']; ?></h5>
                                            </a>
                                            <span class="badge bg-primary"><?php echo number_format($review['rating'], 1); ?>/10</span>
                                        </div>
                                        <p class="text-muted small">
                                            <?php echo $review['release_date'] ? date('Y', strtotime($review['release_date'])) : 'N/A'; ?> •
                                            Reviewed <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                        </p>
                                        <p class="mb-0">
                                            <?php 
                                            echo !empty($review['review_text']) 
                                                ? $review['review_text'] 
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
                        <i class="fas fa-film fa-3x text-muted mb-3"></i>
                        <h5>No reviews yet</h5>
                        <p class="text-muted">This user hasn't submitted any movie reviews yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>