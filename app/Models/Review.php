<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $primaryKey = 'review_id';

    protected $fillable = [
        'user_id',
        'movie_id',
        'movie_title',
        'poster_path',
        'release_year',
        'rating',
        'review_text'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function likes()
    {
        return $this->hasMany(ReviewLike::class, 'review_id', 'review_id')
                    ->where('type', 'like');
    }

    public function dislikes()
    {
        return $this->hasMany(ReviewLike::class, 'review_id', 'review_id')
                    ->where('type', 'dislike');
    }

    public function allReactions()
    {
        return $this->hasMany(ReviewLike::class, 'review_id', 'review_id');
    }
}