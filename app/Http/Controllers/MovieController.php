<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TmdbService;
use App\Models\Review;
use App\Models\Watchlist;
use App\Models\WatchedMovie;
use Illuminate\Support\Facades\Auth;

class MovieController extends Controller
{
    protected $tmdb;

    public function __construct(TmdbService $tmdb)
    {
        $this->tmdb = $tmdb;
    }


    public function show($id)
    {
        $movie = $this->tmdb->getMovieDetails($id);
        
        $reviews = Review::where('movie_id', $id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $inWatchlist = false;
        $isWatched = false;

        if (Auth::check()) {
            $inWatchlist = Watchlist::where('user_id', Auth::id())
                                    ->where('movie_id', $id)
                                    ->exists();
            
            $isWatched = WatchedMovie::where('user_id', Auth::id())
                                    ->where('movie_id', $id)
                                    ->exists();
        }

        return view('movies.show', [
            'movie' => $movie,
            'reviews' => $reviews,
            'inWatchlist' => $inWatchlist,
            'isWatched' => $isWatched
        ]);
    }


    public function search(Request $request)
    {
        $query = $request->input('query');
        $movies = $this->tmdb->searchMovies($query);

        return view('movies.search', [
            'movies' => $movies,
            'query' => $query
        ]);
    }

}