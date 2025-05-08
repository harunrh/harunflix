<?php
require_once '../private/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

// Get user info
$user_id = $_SESSION['user_id'];

// Function to get user info
function get_user_info($user_id) {
    global $conn;
    
    $query = "SELECT * FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $user;
}

// Function to update user profile
function update_profile($user_id, $data) {
    global $conn;
    
    $username = $data['username'];
    $email = $data['email'];
    
    $query = "UPDATE users SET username = ?, email = ? WHERE user_id = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssi", $username, $email, $user_id);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $success;
}

// Function to update profile picture
function update_profile_picture($user_id, $file) {
    global $conn;
    
    // Check if file was uploaded without errors
    if ($file['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        // Check file type and size
        if (!in_array($file['type'], $allowed_types)) {
            return ['success' => false, 'message' => 'Only JPG, PNG and GIF images are allowed.'];
        }
        
        if ($file['size'] > $max_size) {
            return ['success' => false, 'message' => 'File size should be less than 2MB.'];
        }
        
        // Create upload directory if it doesn't exist
        $upload_dir = 'uploads/profile_pictures/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate a unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $user_id . '_' . uniqid() . '.' . $file_extension;
        $target_file = $upload_dir . $filename;
        
        // Upload the file
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            // Delete old profile picture if exists
            $query = "SELECT profile_picture FROM users WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);
            
            if ($user['profile_picture'] && file_exists($user['profile_picture'])) {
                unlink($user['profile_picture']);
            }
            
            // Update profile picture in database
            $query = "UPDATE users SET profile_picture = ? WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "si", $target_file, $user_id);
            $success = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            
            if ($success) {
                return ['success' => true, 'filename' => $target_file];
            } else {
                return ['success' => false, 'message' => 'Failed to update profile picture in database.'];
            }
        } else {
            return ['success' => false, 'message' => 'Failed to upload file.'];
        }
    } else {
        return ['success' => false, 'message' => 'Error uploading file: ' . $file['error']];
    }
}

// Function to change password
function change_password($user_id, $current_password, $new_password) {
    global $conn;
    
    // Get current password hash
    $query = "SELECT password FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        return ['success' => false, 'message' => 'Current password is incorrect.'];
    }
    
    // Hash new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update password
    $query = "UPDATE users SET password = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    if ($success) {
        return ['success' => true];
    } else {
        return ['success' => false, 'message' => 'Failed to update password.'];
    }
}

// Get user info
$user = get_user_info($user_id);

// Initialize messages
$profile_success = '';
$profile_error = '';
$password_success = '';
$password_error = '';
$picture_success = '';
$picture_error = '';

// Process profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Profile update
    if (isset($_POST['update_profile'])) {
        $username = sanitize_input($_POST['username']);
        $email = sanitize_input($_POST['email']);
        
        // Validate input
        if (empty($username) || empty($email)) {
            $profile_error = "All fields are required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $profile_error = "Invalid email format";
        } else {
            // Check if username or email already exists (excluding current user)
            $check_query = "SELECT * FROM users WHERE (username = ? OR email = ?) AND user_id != ?";
            $stmt = mysqli_prepare($conn, $check_query);
            mysqli_stmt_bind_param($stmt, "ssi", $username, $email, $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                $profile_error = "Username or email already exists";
            } else {
                // Update profile
                if (update_profile($user_id, ['username' => $username, 'email' => $email])) {
                    $profile_success = "Profile updated successfully!";
                    $_SESSION['username'] = $username; // Update session
                    $user = get_user_info($user_id); // Refresh user info
                } else {
                    $profile_error = "Failed to update profile.";
                }
            }
            
            mysqli_stmt_close($stmt);
        }
    }
    
    // Password change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate input
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $password_error = "All password fields are required";
        } elseif ($new_password !== $confirm_password) {
            $password_error = "New passwords do not match";
        } elseif (strlen($new_password) < 6) {
            $password_error = "New password must be at least 6 characters long";
        } else {
            // Change password
            $result = change_password($user_id, $current_password, $new_password);
            if ($result['success']) {
                $password_success = "Password changed successfully!";
            } else {
                $password_error = $result['message'];
            }
        }
    }
    
    // Profile picture upload
    if (isset($_POST['upload_picture']) && isset($_FILES['profile_picture'])) {
        $result = update_profile_picture($user_id, $_FILES['profile_picture']);
        if ($result['success']) {
            $picture_success = "Profile picture updated successfully!";
            $user = get_user_info($user_id); // Refresh user info
        } else {
            $picture_error = $result['message'];
        }
    }
}

include 'templates/header.php';
?>

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-user-circle me-2"></i>Profile Picture
                </h5>
            </div>
            <div class="card-body text-center">
                <?php if ($picture_success): ?>
                    <div class="alert alert-success"><?php echo $picture_success; ?></div>
                <?php endif; ?>
                
                <?php if ($picture_error): ?>
                    <div class="alert alert-danger"><?php echo $picture_error; ?></div>
                <?php endif; ?>
                
                <?php if ($user['profile_picture']): ?>
                    <img src="<?php echo $user['profile_picture']; ?>" alt="<?php echo $user['username']; ?>" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                <?php else: ?>
                    <div class="avatar bg-primary rounded-circle text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 150px; height: 150px; font-size: 60px;">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
                
                <form action="edit_profile.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="profile_picture" class="form-label">Choose a new profile picture</label>
                        <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/jpeg, image/png, image/gif" required>
                        <div class="form-text">Maximum file size: 2MB. Allowed formats: JPG, PNG, GIF.</div>
                    </div>
                    <button type="submit" name="upload_picture" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i>Upload Picture
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-user-edit me-2"></i>Edit Profile
                </h5>
            </div>
            <div class="card-body">
                <?php if ($profile_success): ?>
                    <div class="alert alert-success"><?php echo $profile_success; ?></div>
                <?php endif; ?>
                
                <?php if ($profile_error): ?>
                    <div class="alert alert-danger"><?php echo $profile_error; ?></div>
                <?php endif; ?>
                
                <form action="edit_profile.php" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo $user['username']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save Changes
                    </button>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-key me-2"></i>Change Password
                </h5>
            </div>
            <div class="card-body">
                <?php if ($password_success): ?>
                    <div class="alert alert-success"><?php echo $password_success; ?></div>
                <?php endif; ?>
                
                <?php if ($password_error): ?>
                    <div class="alert alert-danger"><?php echo $password_error; ?></div>
                <?php endif; ?>
                
                <form action="edit_profile.php" method="post">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <div class="form-text">Password must be at least 6 characters long.</div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="fas fa-key me-1"></i>Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Preview profile picture before upload
document.getElementById('profile_picture').addEventListener('change', function(e) {
    if (e.target.files.length > 0) {
        const file = e.target.files[0];
        const reader = new FileReader();
        
        // Create preview element if it doesn't exist
        let previewElement = document.getElementById('profile-picture-preview');
        if (!previewElement) {
            previewElement = document.createElement('img');
            previewElement.id = 'profile-picture-preview';
            previewElement.className = 'img-fluid rounded-circle mb-3';
            previewElement.style.width = '150px';
            previewElement.style.height = '150px';
            previewElement.style.objectFit = 'cover';
            
            // Replace the current avatar or image
            const currentAvatar = document.querySelector('.avatar') || document.querySelector('.card-body > img');
            if (currentAvatar) {
                currentAvatar.parentNode.replaceChild(previewElement, currentAvatar);
            }
        }
        
        // Read and display the file
        reader.onload = function(event) {
            previewElement.src = event.target.result;
        };
        
        reader.readAsDataURL(file);
    }
});
</script>

<?php include 'templates/footer.php'; ?>