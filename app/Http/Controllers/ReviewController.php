<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'movie_id' => 'required|integer',
            'movie_title' => 'required|string|max:255',
            'poster_path' => 'nullable|string',
            'release_year' => 'nullable|string|max:4',
            'rating' => 'required|numeric|min:0|max:10',
            'review_text' => 'nullable|string|max:1000'
        ]);

        $reviewData = [
            'user_id' => auth()->id(),
            'movie_id' => $validated['movie_id'],
            'movie_title' => $validated['movie_title'],
            'poster_path' => $validated['poster_path'] ?? null,
            'release_year' => $validated['release_year'] ?? null,
            'rating' => $validated['rating'],
            'review_text' => $validated['review_text']
        ];

        // Check if user already reviewed this movie
        $existingReview = Review::where('movie_id', $validated['movie_id'])
                                ->where('user_id', auth()->id())
                                ->first();

        if ($existingReview) {
            $existingReview->update($reviewData);
            return redirect()->route('movie.show', $validated['movie_id'])
                           ->with('success', 'Review updated successfully!');
        } else {
            Review::create($reviewData);
            return redirect()->route('movie.show', $validated['movie_id'])
                           ->with('success', 'Review added successfully!');
        }
    }

    public function destroy($id)
    {
        $review = Review::where('review_id', $id)->firstOrFail();
        
        if ($review->user_id !== auth()->id()) {
            return back()->with('error', 'Unauthorized action.');
        }
        
        $movieId = $review->movie_id;
        $review->delete();
        
        return redirect()->route('movie.show', $movieId)
                       ->with('success', 'Review deleted successfully!');
    }

    public function myReviews()
    {
        $reviews = Review::where('user_id', auth()->id())
                        ->orderBy('created_at', 'desc')
                        ->get();
        
        return view('reviews.my-reviews', compact('reviews'));
    }
}
