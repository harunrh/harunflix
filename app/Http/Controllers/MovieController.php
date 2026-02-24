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
        $movie = $this->tmdb->getMovieDetails($id);

        if (!$movie) {
            abort(404, 'Movie not found');
        }

        $reviews = Review::where('movie_id', $id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $ourAverageRating = $reviews->avg('rating');
        $ourReviewCount = $reviews->count();

        $userReview = null;
        $inWatchlist = false;
        $inWatched = false;

        if (auth()->check()) {
            $userId = auth()->id();

            $userReview = Review::where('movie_id', $id)
                ->where('user_id', $userId)
                ->first();

            $inWatchlist = \App\Models\Watchlist::where('user_id', $userId)
                ->where('movie_id', $id)
                ->exists();

            $inWatched = \App\Models\WatchedMovie::where('user_id', $userId)
                ->where('movie_id', $id)
                ->exists();
        }

        return view('movies.show', [
            'movie' => $movie,
            'reviews' => $reviews,
            'ourAverageRating' => $ourAverageRating,
            'ourReviewCount' => $ourReviewCount,
            'userReview' => $userReview,
            'inWatchlist' => $inWatchlist,
            'inWatched' => $inWatched,
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

        /**
     * Live search for movies - returns JSON
     */
    public function liveSearch(Request $request)
    {
        $query = $request->input('query');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = $this->tmdb->searchMovies($query);
        $movies = array_slice($results['results'] ?? [], 0, 6);

        $formatted = array_map(function ($movie) {
            return [
                'id' => $movie['id'],
                'title' => $movie['title'],
                'year' => isset($movie['release_date']) ? substr($movie['release_date'], 0, 4) : 'N/A',
                'poster' => $movie['poster_path']
                    ? 'https://image.tmdb.org/t/p/w92' . $movie['poster_path']
                    : null,
                'rating' => number_format($movie['vote_average'] ?? 0, 1),
                'url' => route('movie.show', $movie['id'])
            ];
        }, $movies);

        return response()->json($formatted);
    }
}