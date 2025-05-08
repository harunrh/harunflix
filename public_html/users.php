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

// Get all users
$users = get_all_users();

include 'templates/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <h2 class="display-6">
            <i class="fas fa-users me-2"></i>Community Members
        </h2>
        <p class="text-muted">Our movie reviewing community</p>
    </div>
</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
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
                                                    <div class="avatar me-2 bg-primary rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
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
</div>

<?php include 'templates/footer.php'; ?>