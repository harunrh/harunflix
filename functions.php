<?php
require_once '../private/config.php';

// Function to sanitize user input
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to register a new user
function register_user($username, $email, $password) {
    global $conn;
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user into database
    $query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sss", $username, $email, $hashed_password);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return true;
    } else {
        mysqli_stmt_close($stmt);
        return false;
    }
}

// Function to authenticate user
function login_user($username, $password) {
    global $conn;
    
    $query = "SELECT user_id, username, password, profile_picture FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $user['password'])) {
            // Password is correct, create session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['profile_picture'] = $user['profile_picture'];
            mysqli_stmt_close($stmt);
            return true;
        }
    }
    
    mysqli_stmt_close($stmt);
    return false;
}

// Function to search movies via TMDB API
function search_movies($query) {
    $url = TMDB_API_URL . "/search/movie?api_key=" . TMDB_API_KEY . "&query=" . urlencode($query);
    $response = file_get_contents($url);
    return json_decode($response, true);
}

// Function to get movie details from TMDB API
function get_movie_details($movie_id) {
    $url = TMDB_API_URL . "/movie/" . $movie_id . "?api_key=" . TMDB_API_KEY;
    $response = file_get_contents($url);
    return json_decode($response, true);
}

// Function to save a movie review
function save_review($user_id, $movie_id, $movie_title, $rating, $review_text) {
    global $conn;
    
    // First check if a review already exists
    $check_query = "SELECT review_id FROM reviews WHERE user_id = ? AND movie_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "ii", $user_id, $movie_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $exists = mysqli_num_rows($check_result) > 0;
    mysqli_stmt_close($check_stmt);
    
    if ($exists) {
        // Update existing review
        $query = "UPDATE reviews SET rating = ?, review_text = ?, movie_title = ? WHERE user_id = ? AND movie_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "dssii", $rating, $review_text, $movie_title, $user_id, $movie_id);
    } else {
        // Insert new review
        $query = "INSERT INTO reviews (user_id, movie_id, movie_title, rating, review_text) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "iisds", $user_id, $movie_id, $movie_title, $rating, $review_text);
    }
    
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $success;
}

// Function to get all reviews for a movie
function get_movie_reviews($movie_id) {
    global $conn;
    
    $query = "SELECT r.*, u.username, u.profile_picture
              FROM reviews r 
              JOIN users u ON r.user_id = u.user_id 
              WHERE r.movie_id = ? 
              ORDER BY r.created_at DESC";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $movie_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $reviews = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $reviews[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $reviews;
}
?>
