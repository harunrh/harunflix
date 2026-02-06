<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TmdbService;
use App\Models\Review;

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

        return view('movies.show', [
            'movie' => $movie,
            'reviews' => $reviews
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