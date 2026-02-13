<?php

namespace App\Http\Controllers;

use App\Services\TmdbService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $tmdb;

    public function __construct(TmdbService $tmdb)
    {
        $this->tmdb = $tmdb;
    }

    public function index()
    {
        // Get movies from TMDB
        $topRatedMovies = $this->tmdb->getTopRatedMovies(1);
        $popularMovies = $this->tmdb->getPopularMovies(1);
        $trendingMovies = $this->tmdb->getTrendingMovies('week');
        
        // Get random posters for hero banner
        $heroPosters = $this->tmdb->getRandomPosters(14);

        // Get reviews from database (if any)
        $recentReviews = \App\Models\Review::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return view('home', [
            'topRatedMovies' => $topRatedMovies['results'] ?? [],
            'popularMovies' => $popularMovies['results'] ?? [],
            'trendingMovies' => $trendingMovies['results'] ?? [],
            'heroPosters' => $heroPosters,
            'recentReviews' => $recentReviews
        ]);
    }
}