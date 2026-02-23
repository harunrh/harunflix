<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Review;
use App\Models\Watchlist;
use App\Models\WatchedMovie;
use App\Services\TmdbService;

class ProfileController extends Controller
{
    protected $tmdb;

    public function __construct(TmdbService $tmdb)
    {
        $this->tmdb = $tmdb;
    }

    public function index()
    {
        $user = Auth::user();

        $reviews = Review::where('user_id', $user->user_id)
                        ->orderBy('created_at', 'desc')
                        ->get();

        $watchlist = Watchlist::where('user_id', $user->user_id)
                             ->orderBy('created_at', 'desc')
                             ->get();

        $watchedMovies = WatchedMovie::where('user_id', $user->user_id)
                                    ->orderBy('created_at', 'desc')
                                    ->get();

        // Calculate stats
        $averageRating = $reviews->count() > 0
            ? round($reviews->avg('rating'), 1)
            : null;

        $totalWatchTime = $watchedMovies->sum('runtime');
        $totalHours = floor($totalWatchTime / 60);
        $totalMinutes = $totalWatchTime % 60;

        $totalWatched = max($watchedMovies->count(), $reviews->count());

        // Get favourite genre from reviews
        $favouriteGenre = $this->getFavouriteGenre($reviews);

        $stats = [
            'review_count' => $reviews->count(),
            'average_rating' => $averageRating,
            'watchlist_count' => $watchlist->count(),
            'watched_count' => $totalWatched,
            'total_hours' => $totalHours,
            'total_minutes' => $totalMinutes,
            'favourite_genre' => $favouriteGenre,
        ];

        return view('profile.index', compact(
            'user', 'reviews', 'watchlist', 'watchedMovies', 'stats'
        ));
    }

    private function getFavouriteGenre($reviews)
    {
        if ($reviews->count() === 0) return null;

        $genreCounts = [];

        foreach ($reviews as $review) {
            try {
                $movie = $this->tmdb->getMovieDetails($review->movie_id);
                if (isset($movie['genres'])) {
                    foreach ($movie['genres'] as $genre) {
                        $genreCounts[$genre['name']] = ($genreCounts[$genre['name']] ?? 0) + 1;
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        if (empty($genreCounts)) return null;

        arsort($genreCounts);
        return array_key_first($genreCounts);
    }
}