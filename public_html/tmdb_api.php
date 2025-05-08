<?php
require_once '../private/config.php';

/**
 * TMDB API Wrapper Class
 */
class TMDB_API {
    private $api_key;
    private $base_url;
    private $image_base_url;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->api_key = TMDB_API_KEY;
        $this->base_url = TMDB_API_URL;
        $this->image_base_url = TMDB_IMAGE_BASE_URL;
    }
    
    /**
     * Search for movies
     */
    public function searchMovies($query, $page = 1) {
        $url = $this->base_url . "/search/movie?api_key=" . $this->api_key 
             . "&query=" . urlencode($query) . "&page=" . $page;
        
        return $this->makeRequest($url);
    }
    
    /**
     * Get movie details
     */
    public function getMovie($movie_id) {
        $url = $this->base_url . "/movie/" . $movie_id . "?api_key=" . $this->api_key;
        
        return $this->makeRequest($url);
    }
    
    /**
     * Get movie credits (cast and crew)
     */
    public function getMovieCredits($movie_id) {
        $url = $this->base_url . "/movie/" . $movie_id . "/credits?api_key=" . $this->api_key;
        
        return $this->makeRequest($url);
    }
    
    /**
     * Get similar movies
     */
    public function getSimilarMovies($movie_id, $page = 1) {
        $url = $this->base_url . "/movie/" . $movie_id . "/similar?api_key=" . $this->api_key 
             . "&page=" . $page;
        
        return $this->makeRequest($url);
    }
    
    /**
     * Get popular movies
     */
    public function getPopularMovies($page = 1) {
        $url = $this->base_url . "/movie/popular?api_key=" . $this->api_key . "&page=" . $page;
        
        return $this->makeRequest($url);
    }
    
    /**
     * Helper function to make API requests
     */
    private function makeRequest($url) {
        $response = file_get_contents($url);
        
        if ($response === false) {
            return null;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Get image URL
     */
    public function getImageUrl($path, $size = 'w500') {
        if (empty($path)) {
            return null;
        }
        
        return "https://image.tmdb.org/t/p/" . $size . $path;
    }
}
?>