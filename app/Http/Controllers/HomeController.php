<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TmdbService;
use App\Models\Review;

class HomeController extends Controller
{
    protected $tmdb;

    public function __construct(TmdbService $tmdb)
    {
        $this->tmdb = $tmdb;
    }

    public function index()
    {
        $popularMovies = $this->tmdb->getPopularMovies();
        $trendingMovies = $this->tmdb->getTrendingMovies();
        
        $recentReviews = Review::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return view('home', [
            'popularMovies' => $popularMovies['results'] ?? [],
            'trendingMovies' => $trendingMovies['results'] ?? [],
            'recentReviews' => $recentReviews
        ]);
    }
}