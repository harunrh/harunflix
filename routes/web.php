<?php

use App\Http\Controllers\MovieController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\ReviewLikeController;
use App\Http\Controllers\CinemaController;

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/movie/{id}', [MovieController::class, 'show'])->name('movie.show');
Route::get('/search', [MovieController::class, 'search'])->name('movie.search');
Route::get('/search/live', [MovieController::class, 'liveSearch'])->name('movie.live-search');
Route::get('/users', [ProfileController::class, 'allUsers'])->name('users.index');
Route::get('/users/{username}', [ProfileController::class, 'show'])->name('users.show');
Route::get('/genre/{id}', [MovieController::class, 'byGenre'])->name('movie.genre');
Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');
Route::get('/cinemas/nearby', [CinemaController::class, 'nearby'])->name('cinemas.nearby');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/review', [ReviewController::class, 'store'])->name('review.store');
    Route::delete('/review/{id}', [ReviewController::class, 'destroy'])->name('review.destroy');
    Route::get('/my-reviews', [ReviewController::class, 'myReviews'])->name('reviews.my');
    Route::post('/watchlist/add', [WatchlistController::class, 'addToWatchlist'])->name('watchlist.add');
    Route::delete('/watchlist/remove/{movieId}', [WatchlistController::class, 'removeFromWatchlist'])->name('watchlist.remove');
    Route::post('/watched/add', [WatchlistController::class, 'markAsWatched'])->name('watched.add');
    Route::delete('/watched/remove/{movieId}', [WatchlistController::class, 'removeFromWatched'])->name('watched.remove');
    Route::post('/review/{id}/react', [ReviewLikeController::class, 'react'])->name('review.react');
    Route::get('/review/{id}/reactions', [ReviewLikeController::class, 'reactions'])->name('review.reactions');
});