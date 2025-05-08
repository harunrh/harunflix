<?php
require_once '../private/functions.php';

// Set header to return JSON
header('Content-Type: application/json');

// Check if query parameter exists
if (!isset($_GET['query']) || empty($_GET['query'])) {
    echo json_encode(['results' => []]);
    exit();
}

// Get search query
$query = sanitize_input($_GET['query']);

// Search for movies
$results = search_movies($query);

// Return results
echo json_encode($results);
?>