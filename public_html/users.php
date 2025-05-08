<?php
require_once '../private/functions.php';

// Function to get all users with review stats
function get_all_users() {
    global $conn;
    
    $query = "SELECT 
                u.user_id,
                u.username,
                u.profile_picture,
                u.created_at,
                COUNT(r.review_id) as review_count,
                AVG(r.rating) as avg_rating
              FROM 
                users u
              LEFT JOIN 
                reviews r ON u.user_id = r.user_id
              GROUP BY 
                u.user_id, u.username, u.profile_picture, u.created_at
              ORDER BY 
                review_count DESC";
    
    $result = mysqli_query($conn, $query);
    
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    
    return $users;
}

// Function to get most active users
function get_most_active_users($limit = 5) {
    global $conn;
    
    $query = "SELECT 
                u.user_id,
                u.username,
                u.profile_picture,
                u.created_at,
                COUNT(r.review_id) as review_count,
                AVG(r.rating) as avg_rating
              FROM 
                users u
              JOIN 
                reviews r ON u.user_id = r.user_id
              GROUP BY 
                u.user_id, u.username, u.profile_picture, u.created_at
              ORDER BY 
                review_count DESC
              LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $users;
}

// Get all users and most active users
$users = get_all_users();
$active_users = get_most_active_users();

include 'templates/header.php';
?>

<div class="content-row">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h3"><i class="fas fa-users me-2"></i>Community Members</h2>
        <?php if (is_logged_in()): ?>
            <a href="profile.php?id=<?php echo $_SESSION['user_id']; ?>" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-user me-1"></i>My Profile
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Featured Users -->
<div class="content-row">
    <h4 class="mb-3">Top Reviewers</h4>
    <div class="row row-cols-2 row-cols-md-5 g-3 mb-4">
        <?php foreach ($active_users as $user): ?>
            <div class="col">
                <a href="profile.php?id=<?php echo $user['user_id']; ?>" class="text-decoration-none">
                    <div class="card h-100 text-center">
                        <div class="card-body p-3">
                            <?php if (!empty($user['profile_picture'])): ?>
                                <img src="<?php echo $user['profile_picture']; ?>" class="rounded-circle mb-3" alt="<?php echo $user['username']; ?>" style="width: 80px; height: 80px; object-fit: cover;">
                            <?php else: ?>
                                <div class="avatar rounded-circle text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; font-size: 32px;">
                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <h6 class="mb-1"><?php echo $user['username']; ?></h6>
                            <div class="small">
                                <span class="badge bg-primary"><?php echo $user['review_count']; ?> reviews</span>
                            </div>
                            <?php if ($user['avg_rating']): ?>
                                <div class="small text-muted mt-2">
                                    Avg Rating: <?php echo number_format($user['avg_rating'], 1); ?>/10
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- All Users -->
<div class="content-row">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>All Members</h4>
        <div class="d-flex">
            <div class="input-group">
                <input type="text" class="form-control" id="user-search" placeholder="Search users...">
                <button class="btn btn-outline-primary" type="button" id="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover" id="users-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Joined On</th>
                            <th>Reviews</th>
                            <th>Average Rating</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($user['profile_picture']): ?>
                                                <img src="<?php echo $user['profile_picture']; ?>" alt="<?php echo $user['username']; ?>" class="me-2 rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="avatar me-2 rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                </div>
                                            <?php endif; ?>
                                            <span><?php echo $user['username']; ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <span class="badge bg-primary rounded-pill">
                                            <?php echo $user['review_count']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['avg_rating']): ?>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <?php echo number_format($user['avg_rating'], 1); ?>
                                                </div>
                                                <div class="text-warning">
                                                    <?php 
                                                    $rating = round($user['avg_rating']);
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
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">No ratings</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="profile.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-user me-1"></i>Profile
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No users found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // User search functionality
    $("#user-search").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#users-table tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>

<?php include 'templates/footer.php'; ?>