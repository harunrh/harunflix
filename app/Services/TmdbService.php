<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TmdbService
{
    private $apiKey;
    private $baseUrl;
    private $imageBaseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.tmdb.api_key');
        $this->baseUrl = config('services.tmdb.base_url');
        $this->imageBaseUrl = config('services.tmdb.image_base_url');
    }

    public function searchMovies($query)
    {
        $response = Http::get("{$this->baseUrl}/search/movie", [
            'api_key' => $this->apiKey,
            'query' => $query
        ]);

        return $response->json()['results'] ?? [];
    }

    public function getMovieDetails($movieId)
    {
        $response = Http::get("{$this->baseUrl}/movie/{$movieId}", [
            'api_key' => $this->apiKey
        ]);

        return $response->json();
    }

    public function getPopularMovies()
    {
        $response = Http::get("{$this->baseUrl}/movie/popular", [
            'api_key' => $this->apiKey
        ]);

        return $response->json();
    }

    public function getTrendingMovies()
    {
        $response = Http::get("{$this->baseUrl}/trending/movie/week", [
            'api_key' => $this->apiKey
        ]);

        return $response->json();
    }
}