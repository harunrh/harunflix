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
        $existing = Watchlist::where('user_id', Auth::id())
            ->where('movie_id', $request->movie_id)
            ->first();

        if ($existing) {
            $existing->poster_path = $request->poster_path;
            $existing->save();
        } else {
            Watchlist::create([
                'user_id'     => Auth::id(),
                'movie_id'    => $request->movie_id,
                'movie_title' => $request->movie_title,
                'poster_path' => $request->poster_path,
            ]);
        }

        return response()->json(['message' => 'Added to watchlist!', 'status' => 'added']);
    }

    public function removeFromWatchlist($movieId)
    {
        Watchlist::where('user_id', Auth::id())
                 ->where('movie_id', $movieId)
                 ->delete();

        return response()->json(['message' => 'Removed from watchlist', 'status' => 'removed']);
    }

    public function markAsWatched(Request $request)
    {
        $existing = WatchedMovie::where('user_id', Auth::id())
            ->where('movie_id', $request->movie_id)
            ->first();

        if ($existing) {
            $existing->poster_path = $request->poster_path;
            $existing->save();
        } else {
            WatchedMovie::create([
                'user_id'     => Auth::id(),
                'movie_id'    => $request->movie_id,
                'movie_title' => $request->movie_title,
                'runtime'     => $request->runtime,
                'poster_path' => $request->poster_path,
            ]);
        }

        return response()->json(['message' => 'Marked as watched!', 'status' => 'watched']);
    }

    public function removeFromWatched($movieId)
    {
        WatchedMovie::where('user_id', Auth::id())
                    ->where('movie_id', $movieId)
                    ->delete();

        return response()->json(['message' => 'Removed from watched', 'status' => 'removed']);
    }
}