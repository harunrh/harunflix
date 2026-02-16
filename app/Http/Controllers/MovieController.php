<?php

namespace App\Http\Controllers;

use App\Services\TmdbService;
use App\Models\Review;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    protected $tmdb;

    public function __construct(TmdbService $tmdb)
    {
        $this->tmdb = $tmdb;
    }

    /**
     * Show movie details
     */
    public function show($id)
    {
        // Get movie details from TMDB
        $movie = $this->tmdb->getMovieDetails($id);

        if (!$movie) {
            abort(404, 'Movie not found');
        }

        // Get reviews from database for this movie
        $reviews = Review::where('movie_id', $id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate average rating from our reviews
        $ourAverageRating = $reviews->avg('rating');
        $ourReviewCount = $reviews->count();

        // Check if current user has reviewed this movie
        $userReview = null;
        if (auth()->check()) {
            $userReview = Review::where('movie_id', $id)
                ->where('user_id', auth()->id())
                ->first();
        }

        return view('movies.show', [
            'movie' => $movie,
            'reviews' => $reviews,
            'ourAverageRating' => $ourAverageRating,
            'ourReviewCount' => $ourReviewCount,
            'userReview' => $userReview
        ]);
    }

    /**
     * Search movies
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        
        if (empty($query)) {
            return redirect()->route('home');
        }

        $results = $this->tmdb->searchMovies($query);

        return view('movies.search', [
            'query' => $query,
            'movies' => $results['results'] ?? [],
            'totalResults' => $results['total_results'] ?? 0
        ]);
    }
}