<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Review;
use App\Models\Watchlist;
use App\Models\WatchedMovie;

class ProfileController extends Controller
{
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

        return view('profile.index', compact('user', 'reviews', 'watchlist', 'watchedMovies'));
    }
}