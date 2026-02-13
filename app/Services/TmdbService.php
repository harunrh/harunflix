<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class TmdbService
{
    private $apiKey;
    private $baseUrl;
    private $imageBaseUrl;

    public function __construct()
    {
        $this->apiKey = env('TMDB_API_KEY', '2debada5a395e3de46824940ce1d6213');
        $this->baseUrl = 'https://api.themoviedb.org/3';
        $this->imageBaseUrl = 'https://image.tmdb.org/t/p/w500';
    }

    /**
     * Get popular movies
     */
    public function getPopularMovies($page = 1)
    {
        try {
            $response = Http::get("{$this->baseUrl}/movie/popular", [
                'api_key' => $this->apiKey,
                'page' => $page
            ]);

            return $response->json();
        } catch (\Exception $e) {
            return ['results' => []];
        }
    }

    /**
     * Get top rated movies
     */
    public function getTopRatedMovies($page = 1)
    {
        try {
            $response = Http::get("{$this->baseUrl}/movie/top_rated", [
                'api_key' => $this->apiKey,
                'page' => $page
            ]);

            return $response->json();
        } catch (\Exception $e) {
            return ['results' => []];
        }
    }

    /**
     * Get trending movies
     */
    public function getTrendingMovies($timeWindow = 'week')
    {
        try {
            $response = Http::get("{$this->baseUrl}/trending/movie/{$timeWindow}", [
                'api_key' => $this->apiKey
            ]);

            return $response->json();
        } catch (\Exception $e) {
            return ['results' => []];
        }
    }

    /**
     * Search movies
     */
    public function searchMovies($query, $page = 1)
    {
        try {
            $response = Http::get("{$this->baseUrl}/search/movie", [
                'api_key' => $this->apiKey,
                'query' => $query,
                'page' => $page
            ]);

            return $response->json();
        } catch (\Exception $e) {
            return ['results' => []];
        }
    }

    /**
     * Get movie details
     */
    public function getMovieDetails($movieId)
    {
        try {
            $response = Http::get("{$this->baseUrl}/movie/{$movieId}", [
                'api_key' => $this->apiKey,
                'append_to_response' => 'credits,videos,similar'
            ]);

            return $response->json();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get poster URL
     */
    public function getPosterUrl($posterPath, $size = 'w500')
    {
        if (!$posterPath) {
            return asset('images/no-poster.jpg');
        }
        return "https://image.tmdb.org/t/p/{$size}{$posterPath}";
    }

    /**
     * Get backdrop URL
     */
    public function getBackdropUrl($backdropPath, $size = 'original')
    {
        if (!$backdropPath) {
            return null;
        }
        return "https://image.tmdb.org/t/p/{$size}{$backdropPath}";
    }

    /**
     * Get random posters for hero banner
     */
    public function getRandomPosters($count = 14)
    {
        // Cache this for 1 hour to avoid too many API calls
        return Cache::remember('hero_posters', 3600, function () use ($count) {
            $popular = $this->getPopularMovies(1);
            $topRated = $this->getTopRatedMovies(1);
            
            $allMovies = array_merge(
                $popular['results'] ?? [],
                $topRated['results'] ?? []
            );

            // Shuffle and get random posters
            shuffle($allMovies);
            $posters = [];
            
            foreach ($allMovies as $movie) {
                if (!empty($movie['poster_path'])) {
                    $posters[] = $movie['poster_path'];
                }
                if (count($posters) >= $count) {
                    break;
                }
            }

            return $posters;
        });
    }
}