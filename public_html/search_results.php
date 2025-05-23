<?php
require_once '../private/functions.php';

// Check if query parameter exists
if (!isset($_GET['query']) || empty($_GET['query'])) {
    header("Location: index.php");
    exit();
}

// Get search query
$query = sanitize_input($_GET['query']);

// Search for movies
$search_results = search_movies($query);
$results = $search_results['results'] ?? [];

// Pagination
$total_results = count($results);
$results_per_page = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $results_per_page;
$total_pages = ceil($total_results / $results_per_page);

// Get results for current page
$current_results = array_slice($results, $offset, $results_per_page);

include 'templates/header.php';
?>

<div class="content-row">
    <h2 class="h3 mb-4">
        <i class="fas fa-search me-2"></i>Search Results for "<?php echo htmlspecialchars($query); ?>"
    </h2>
    
    <?php if ($total_results > 0): ?>
        <p class="text-muted">Found <?php echo $total_results; ?> results</p>
        
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3 mb-4">
            <?php foreach ($current_results as $movie): ?>
                <div class="col">
                    <a href="review.php?id=<?php echo $movie['id']; ?>" class="text-decoration-none">
                        <div class="movie-card">
                            <?php 
                            $poster_url = $movie['poster_path'] 
                                ? TMDB_IMAGE_BASE_URL . $movie['poster_path'] 
                                : 'assets/img/no-poster.jpg';
                            ?>
                            <img src="<?php echo $poster_url; ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" 
                                 onerror="this.src='assets/img/no-poster.jpg'">
                            
                            <?php if (isset($movie['vote_average'])): ?>
                                <div class="movie-rating">
                                    <i class="fas fa-star me-1 small"></i><?php echo number_format($movie['vote_average'], 1); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <div class="movie-title"><?php echo $movie['title']; ?></div>
                                <div class="movie-year">
                                    <?php echo $movie['release_date'] ? date('Y', strtotime($movie['release_date'])) : 'N/A'; ?>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="d-flex justify-content-center mb-4">
            <nav aria-label="Search results navigation">
                <ul class="pagination">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?query=<?php echo urlencode($query); ?>&page=<?php echo $page - 1; ?>" aria-label="Previous">
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
                            <a class="page-link" href="?query=<?php echo urlencode($query); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?query=<?php echo urlencode($query); ?>&page=<?php echo $page + 1; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
        
    <?php else: ?>
        <div class="card mb-4">
            <div class="card-body text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4>No Results Found</h4>
                <p class="text-muted">We couldn't find any movies matching "<?php echo htmlspecialchars($query); ?>"</p>
                <a href="index.php" class="btn btn-primary mt-2">Back to Home</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'templates/footer.php'; ?>