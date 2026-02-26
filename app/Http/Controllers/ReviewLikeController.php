<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReviewLike;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewLikeController extends Controller
{
    public function react(Request $request, $reviewId)
    {
        $user = Auth::user();
        $type = $request->type; // 'like' or 'dislike'

        $existing = ReviewLike::where('user_id', $user->user_id)
            ->where('review_id', $reviewId)
            ->first();

        if ($existing) {
            if ($existing->type === $type) {
                // Same reaction — remove it (toggle off)
                $existing->delete();
                $userReaction = null;
            } else {
                // Different reaction — switch it
                $existing->type = $type;
                $existing->save();
                $userReaction = $type;
            }
        } else {
            ReviewLike::create([
                'user_id'   => $user->user_id,
                'review_id' => $reviewId,
                'type'      => $type,
            ]);
            $userReaction = $type;
        }

        $review = Review::with('likes', 'dislikes')->find($reviewId);

        return response()->json([
            'likes'        => $review->likes->count(),
            'dislikes'     => $review->dislikes->count(),
            'userReaction' => $userReaction,
        ]);
    }

    public function reactions($reviewId)
    {
        $review = Review::findOrFail($reviewId);

        $likes = ReviewLike::where('review_id', $reviewId)
            ->where('type', 'like')
            ->with('user')
            ->get()
            ->map(fn($r) => $r->user->username);

        $dislikes = ReviewLike::where('review_id', $reviewId)
            ->where('type', 'dislike')
            ->with('user')
            ->get()
            ->map(fn($r) => $r->user->username);

        return response()->json([
            'likes'    => $likes,
            'dislikes' => $dislikes,
        ]);
    }
}