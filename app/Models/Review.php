<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'reviews';
    protected $primaryKey = 'review_id';
    
    protected $fillable = [
        'user_id',
        'movie_id',
        'movie_title',
        'rating',
        'review_text'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}