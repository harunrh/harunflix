<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'movie_id' => 'required|integer',
            'movie_title' => 'required|string',
            'rating' => 'required|numeric|min:0|max:10',
            'review_text' => 'nullable|string',
        ]);

        Review::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'movie_id' => $request->movie_id
            ],
            [
                'movie_title' => $request->movie_title,
                'rating' => $request->rating,
                'review_text' => $request->review_text
            ]
        );

        return redirect()->back()->with('success', 'Review submitted successfully!');
    }
}