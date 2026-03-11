<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewLike;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index()
    {
        // Recent reviews
        $reviews = Review::with('user')
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($review) {
                return [
                    'type'       => 'review',
                    'time'       => $review->created_at,
                    'username'   => $review->user->username ?? 'Unknown',
                    'movie_id'   => $review->movie_id,
                    'movie_title'=> $review->movie_title,
                    'poster_path'=> $review->poster_path,
                    'rating'     => $review->rating,
                    'review_text'=> $review->review_text,
                ];
            });

        // Recent reactions (likes/dislikes)
        $reactions = ReviewLike::with(['user', 'review'])
            ->latest()
            ->take(50)
            ->get()
            ->filter(fn($like) => $like->user && $like->review)
            ->map(function ($like) {
                return [
                    'type'       => 'reaction',
                    'time'       => $like->created_at,
                    'username'   => $like->user->username,
                    'reaction'   => $like->type, // 'like' or 'dislike'
                    'movie_id'   => $like->review->movie_id,
                    'movie_title'=> $like->review->movie_title,
                    'poster_path'=> $like->review->poster_path,
                    'reviewed_by'=> $like->review->user->username ?? 'someone',
                ];
            });

        // Merge and sort by time descending
        $feed = $reviews->concat($reactions)
            ->sortByDesc('time')
            ->take(40)
            ->values();

        return view('activity.index', compact('feed'));
    }
}