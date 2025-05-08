<?php
require_once '../private/functions.php';

// Function to get all recent activity (reviews)
function get_activity($limit = 20, $offset = 0) {
    global $conn;
    
    $query = "SELECT 
                r.*,
                u.username,
                u.user_id,
                u.profile_picture
              FROM 
                reviews r
              JOIN 
                users u ON r.user_id = u.user_id
              ORDER BY 
                r.created_at DESC
              LIMIT ?, ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $offset, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $activities = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Get movie details for each review
        $movie_details = get_movie_details($row['movie_id']);
        
        // Add poster path and release date to our results
        $row['poster_path'] = $movie_details['poster_path'] ?? null;
        $row['release_date'] = $movie_details['release_date'] ?? null;
        
        $activities[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $activities;
}

// Function to get total activity count
function get_activity_count() {
    global $conn;
    
    $query = "SELECT COUNT(*) as total FROM reviews";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    return $row['total'];
}

// Pagination
$limit = 15; // Number of activities per page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Get activities and total count
$activities = get_activity($limit, $offset);
$total_activities = get_activity_count();
$total_pages = ceil($total_activities / $limit);

include 'templates/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <h2 class="display-6">
            <i class="fas fa-history me-2"></i>Activity Feed
        </h2>
        <p class="text-muted">See what our community has been reviewing recently</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-body p-0">
                <?php if (count($activities) > 0): ?>
                    <div class="timeline">
                        <?php 
                        $current_date = null;
                        foreach ($activities as $activity): 
                            $activity_date = date('Y-m-d', strtotime($activity['created_at']));
                            
                            // Display date header when date changes
                            if ($activity_date !== $current_date):
                                $current_date = $activity_date;
                                
                                // Format date label
                                $today = date('Y-m-d');
                                $yesterday = date('Y-m-d', strtotime('-1 day'));
                                
                                if ($activity_date === $today) {
                                    $date_label = 'Today';
                                } elseif ($activity_date === $yesterday) {
                                    $date_label = 'Yesterday';
                                } else {
                                    $date_label = date('F j, Y', strtotime($activity['created_at']));
                                }
                        ?>
                            <div class="date-header p-3 bg-light border-top border-bottom">
                                <h5 class="mb-0"><?php echo $date_label; ?></h5>
                            </div>
                        <?php endif; ?>
                        
                        <div class="activity-item p-3 border-bottom">
                            <div class="d-flex">
                                <div class="activity-user me-3 text-center">
                                    <a href="profile.php?id=<?php echo $activity['user_id']; ?>" class="text-decoration-none">
                                        <?php if (isset($activity['profile_picture']) && !empty($activity['profile_picture'])): ?>
                                            <img src="<?php echo $activity['profile_picture']; ?>" alt="<?php echo $activity['username']; ?>" class="rounded-circle mb-2" style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="avatar bg-primary rounded-circle text-white d-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px; font-size: 20px;">
                                                <?php echo strtoupper(substr($activity['username'], 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="small"><?php echo $activity['username']; ?></div>
                                    </a>
                                </div>
                                
                                <div class="activity-content flex-grow-1">
                                    <div class="d-flex mb-2">
                                        <div class="me-auto">
                                            <a href="profile.php?id=<?php echo $activity['user_id']; ?>" class="fw-bold text-decoration-none">
                                                <?php echo $activity['username']; ?>
                                            </a>
                                            <span class="text-muted">reviewed</span>
                                            <a href="review.php?id=<?php echo $activity['movie_id']; ?>" class="fw-bold text-decoration-none">
                                                <?php echo $activity['movie_title']; ?>
                                            </a>
                                        </div>
                                        <div class="text-muted small">
                                            <?php echo date('g:i A', strtotime($activity['created_at'])); ?>
                                        </div>
                                    </div>
                                    
                                    <div class="card activity-review-card">
                                        <div class="card-body p-3">
                                            <div class="d-flex">
                                                <?php 
                                                $poster_url = $activity['poster_path'] 
                                                    ? TMDB_IMAGE_BASE_URL . $activity['poster_path'] 
                                                    : 'assets/img/no-poster.jpg';
                                                ?>
                                                <a href="review.php?id=<?php echo $activity['movie_id']; ?>" class="me-3">
                                                    <img src="<?php echo $poster_url; ?>" alt="<?php echo $activity['movie_title']; ?>" 
                                                        style="width: 80px; height: 120px; object-fit: cover; border-radius: 8px;">
                                                </a>
                                                <div>
                                                    <div class="d-flex align-items-center mb-2">
                                                        <a href="review.php?id=<?php echo $activity['movie_id']; ?>" class="text-decoration-none">
                                                            <h5 class="mb-0"><?php echo $activity['movie_title']; ?></h5>
                                                        </a>
                                                        <span class="badge bg-primary ms-2"><?php echo number_format($activity['rating'], 1); ?>/10</span>
                                                    </div>
                                                    <p class="text-muted small mb-2">
                                                        <?php echo $activity['release_date'] ? date('Y', strtotime($activity['release_date'])) : 'N/A'; ?>
                                                    </p>
                                                    <p class="mb-0">
                                                        <?php 
                                                        echo !empty($activity['review_text']) 
                                                            ? $activity['review_text']
                                                            : '<em class="text-muted">No written review</em>'; 
                                                        ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="d-flex justify-content-center p-3">
                        <nav aria-label="Activity feed pagination">
                            <ul class="pagination">
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                
                                <?php
                                // Display a reasonable number of page links
                                $start = max(1, min($page - 2, $total_pages - 4));
                                $end = min($total_pages, max($page + 2, 5));
                                
                                for ($i = $start; $i <= $end; $i++): 
                                ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="text-center p-5">
                        <i class="fas fa-film fa-3x text-muted mb-3"></i>
                        <h4>No Activity Yet</h4>
                        <p class="text-muted">There are no movie reviews yet. Be the first to review a movie!</p>
                        <a href="index.php" class="btn btn-primary mt-2">
                            <i class="fas fa-search me-1"></i>Find Movies to Review
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>About Activity
                </h5>
            </div>
            <div class="card-body">
                <p>This feed shows the latest movie reviews from our community members in chronological order.</p>
                <p>Want to be part of the activity?</p>
                <?php if (!is_logged_in()): ?>
                    <div class="d-grid gap-2">
                        <a href="register.php" class="btn btn-primary">
                            <i class="fas fa-user-plus me-1"></i>Join Now
                        </a>
                    </div>
                <?php else: ?>
                    <div class="d-grid">
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-pen me-1"></i>Write a Review
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-star me-2"></i>Quick Stats
                </h5>
            </div>
            <div class="card-body">
                <?php
                // Get some quick stats
                $stats_query = "SELECT 
                                  (SELECT COUNT(*) FROM reviews) as total_reviews,
                                  (SELECT COUNT(DISTINCT movie_id) FROM reviews) as total_movies,
                                  (SELECT COUNT(*) FROM users) as total_users,
                                  (SELECT AVG(rating) FROM reviews) as avg_rating";
                $stats_result = mysqli_query($conn, $stats_query);
                $stats = mysqli_fetch_assoc($stats_result);
                ?>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-star me-2 text-warning"></i>Reviews</span>
                        <span class="badge bg-primary rounded-pill"><?php echo $stats['total_reviews']; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-film me-2 text-danger"></i>Movies</span>
                        <span class="badge bg-primary rounded-pill"><?php echo $stats['total_movies']; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-users me-2 text-success"></i>Members</span>
                        <span class="badge bg-primary rounded-pill"><?php echo $stats['total_users']; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-chart-line me-2 text-info"></i>Avg Rating</span>
                        <span class="badge bg-primary rounded-pill">
                            <?php echo $stats['avg_rating'] ? number_format($stats['avg_rating'], 1) : 'N/A'; ?>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>