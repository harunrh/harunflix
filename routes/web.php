<?php

use App\Http\Controllers\MovieController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WatchlistController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/review', [ReviewController::class, 'store'])->middleware('auth')->name('review.store');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/movie/{id}', [MovieController::class, 'show'])->name('movie.show');
Route::get('/search', [MovieController::class, 'search'])->name('movie.search');
Route::get('/profile', [ProfileController::class, 'index'])->middleware('auth')->name('profile');
Route::post('/watchlist/add', [WatchlistController::class, 'addToWatchlist'])->middleware('auth')->name('watchlist.add');
Route::delete('/watchlist/remove/{movieId}', [WatchlistController::class, 'removeFromWatchlist'])->middleware('auth')->name('watchlist.remove');
Route::post('/watched/add', [WatchlistController::class, 'markAsWatched'])->middleware('auth')->name('watched.add');
Route::delete('/watched/remove/{movieId}', [WatchlistController::class, 'removeFromWatched'])->middleware('auth')->name('watched.remove');