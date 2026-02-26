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

        // Merge reviewed movies into watched list so all rated films appear there
        $watchedMovieIds = $watchedMovies->pluck('movie_id')->toArray();

        $reviewedAsWatched = $reviews->filter(function ($review) use ($watchedMovieIds) {
            return !in_array($review->movie_id, $watchedMovieIds);
        })->map(function ($review) {
            return (object) [
                'movie_id'    => $review->movie_id,
                'movie_title' => $review->movie_title,
                'poster_path' => $review->poster_path ?? null,
                'created_at'  => $review->created_at,
                'from_review' => true,
            ];
        });

        $allWatched = collect($watchedMovies->all())
            ->concat($reviewedAsWatched)
            ->sortByDesc('created_at')
            ->values();

        // Stats
        $averageRating = $reviews->count() > 0
            ? round($reviews->avg('rating'), 1)
            : null;

        $totalWatchTime = $watchedMovies->sum('runtime');
        $totalHours = floor($totalWatchTime / 60);
        $totalMinutes = $totalWatchTime % 60;

        $totalWatched = $allWatched->count();

        $favouriteGenre = $this->getFavouriteGenre($reviews);

        $stats = [
            'review_count'   => $reviews->count(),
            'average_rating' => $averageRating,
            'watchlist_count'=> $watchlist->count(),
            'watched_count'  => $totalWatched,
            'total_hours'    => $totalHours,
            'total_minutes'  => $totalMinutes,
            'favourite_genre'=> $favouriteGenre,
        ];

        return view('profile.index', compact(
            'user', 'reviews', 'watchlist', 'watchedMovies', 'allWatched', 'stats'
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

    public function allUsers()
    {
        $users = \App\Models\User::withCount('reviews')
            ->orderBy('reviews_count', 'desc')
            ->paginate(20);

        return view('users.index', compact('users'));
    }

    public function show($username)
    {
        $user = \App\Models\User::where('username', $username)->firstOrFail();

        $reviews = Review::where('user_id', $user->user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $watchlist = Watchlist::where('user_id', $user->user_id)->get();

        $watchedMovies = WatchedMovie::where('user_id', $user->user_id)->get();

        $watchedMovieIds = $watchedMovies->pluck('movie_id')->toArray();

        $reviewedAsWatched = $reviews->filter(function ($review) use ($watchedMovieIds) {
            return !in_array($review->movie_id, $watchedMovieIds);
        })->map(function ($review) {
            return (object) [
                'movie_id'    => $review->movie_id,
                'movie_title' => $review->movie_title,
                'poster_path' => $review->poster_path ?? null,
                'created_at'  => $review->created_at,
            ];
        });

        $allWatched = collect($watchedMovies->all())
            ->concat($reviewedAsWatched)
            ->sortByDesc('created_at')
            ->values();

        $averageRating = $reviews->count() > 0 ? round($reviews->avg('rating'), 1) : null;
        $favouriteGenre = $this->getFavouriteGenre($reviews);

        $stats = [
            'review_count'    => $reviews->count(),
            'average_rating'  => $averageRating,
            'watchlist_count' => $watchlist->count(),
            'watched_count'   => $allWatched->count(),
            'favourite_genre' => $favouriteGenre,
        ];

        return view('users.show', compact('user', 'reviews', 'watchlist', 'allWatched', 'stats'));
    }
}