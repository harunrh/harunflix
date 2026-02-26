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
            ->with(['user', 'likes', 'dislikes', 'allReactions'])
            ->orderBy('created_at', 'desc')
            ->get();

        $userReactions = [];
        if (auth()->check()) {
            $userReactions = \App\Models\ReviewLike::where('user_id', auth()->id())
                ->whereIn('review_id', $reviews->pluck('review_id'))
                ->pluck('type', 'review_id')
                ->toArray();
        }

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
            'movie'            => $movie,
            'reviews'          => $reviews,
            'ourAverageRating' => $ourAverageRating,
            'ourReviewCount'   => $ourReviewCount,
            'userReview'       => $userReview,
            'inWatchlist'      => $inWatchlist,
            'inWatched'        => $inWatched,
            'userReactions'    => $userReactions,
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
            'query'        => $query,
            'movies'       => $results['results'] ?? [],
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
                'id'     => $movie['id'],
                'title'  => $movie['title'],
                'year'   => isset($movie['release_date']) ? substr($movie['release_date'], 0, 4) : 'N/A',
                'poster' => $movie['poster_path']
                    ? 'https://image.tmdb.org/t/p/w92' . $movie['poster_path']
                    : null,
                'rating' => number_format($movie['vote_average'] ?? 0, 1),
                'url'    => route('movie.show', $movie['id'])
            ];
        }, $movies);

        return response()->json($formatted);
    }

        public function byGenre(Request $request, $id)
    {
        $page = $request->get('page', 1);
        $results = $this->tmdb->getMoviesByGenre($id, $page);
        $genres = $this->tmdb->getGenres();
        $genre = collect($genres)->firstWhere('id', (int)$id);

        return view('movies.genre', [
            'movies'      => $results['results'] ?? [],
            'totalPages'  => $results['total_pages'] ?? 1,
            'currentPage' => $page,
            'genreId'     => $id,
            'genreName'   => $genre['name'] ?? 'Unknown',
            'genres'      => $genres,

        ]);
    }

    public function index(Request $request)
    {
        $genreId = $request->get('genre');
        $page = $request->get('page', 1);
        $genres = $this->tmdb->getGenres();

        // If filtering by genre, show grid view
        if ($genreId) {
            $results = $this->tmdb->getMoviesByGenre($genreId, $page);
            $selectedGenre = collect($genres)->firstWhere('id', (int)$genreId);
            $selectedGenreName = $selectedGenre['name'] ?? 'All Movies';

            return view('movies.index', [
                'genreView'         => true,
                'movies'            => $results['results'] ?? [],
                'totalPages'        => $results['total_pages'] ?? 1,
                'currentPage'       => $page,
                'genres'            => $genres,
                'selectedGenreId'   => $genreId,
                'selectedGenreName' => $selectedGenreName,
            ]);
        }

        // Default: show categorised sections
        $newReleases    = $this->tmdb->getNowPlayingMovies();
        $popularMovies  = $this->tmdb->getPopularMovies(1);
        $trendingMovies = $this->tmdb->getTrendingMovies('week');

        // Top rated by HarunFlix users (min 2 reviews)
        $topRatedByUsers = \App\Models\Review::select('movie_id', 'movie_title', 'poster_path')
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as review_count')
            ->groupBy('movie_id', 'movie_title', 'poster_path')
            ->having('review_count', '>=', 1)
            ->orderByDesc('avg_rating')
            ->limit(15)
            ->get();

        // Most reviewed on HarunFlix
        $mostReviewed = \App\Models\Review::select('movie_id', 'movie_title', 'poster_path')
            ->selectRaw('COUNT(*) as review_count, AVG(rating) as avg_rating')
            ->groupBy('movie_id', 'movie_title', 'poster_path')
            ->orderByDesc('review_count')
            ->limit(15)
            ->get();

        // Recently rated on HarunFlix
        $recentlyRated = \App\Models\Review::select('movie_id', 'movie_title', 'poster_path', 'rating', 'created_at')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get()
            ->unique('movie_id')
            ->values();

        return view('movies.index', [
            'genreView'      => false,
            'genres'         => $genres,
            'selectedGenreId'=> null,
            'newReleases'    => $newReleases['results'] ?? [],
            'popularMovies'  => $popularMovies['results'] ?? [],
            'trendingMovies' => $trendingMovies['results'] ?? [],
            'topRatedByUsers'=> $topRatedByUsers,
            'mostReviewed'   => $mostReviewed,
            'recentlyRated'  => $recentlyRated,
        ]);
    }
}