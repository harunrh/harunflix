<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Watchlist;
use App\Models\WatchedMovie;

class WatchlistController extends Controller
{
    public function addToWatchlist(Request $request)
    {
        Watchlist::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'movie_id' => $request->movie_id
            ],
            [
                'movie_title' => $request->movie_title
            ]
        );

        return redirect()->back()->with('success', 'Added to watchlist!');
    }

    public function removeFromWatchlist($movieId)
    {
        Watchlist::where('user_id', Auth::id())
                 ->where('movie_id', $movieId)
                 ->delete();

        return redirect()->back()->with('success', 'Removed from watchlist!');
    }

    public function markAsWatched(Request $request)
    {
        WatchedMovie::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'movie_id' => $request->movie_id
            ],
            [
                'movie_title' => $request->movie_title,
                'runtime' => $request->runtime
            ]
        );

        return redirect()->back()->with('success', 'Marked as watched!');
    }

    public function removeFromWatched($movieId)
    {
        WatchedMovie::where('user_id', Auth::id())
                    ->where('movie_id', $movieId)
                    ->delete();

        return redirect()->back()->with('success', 'Removed from watched!');
    }
}