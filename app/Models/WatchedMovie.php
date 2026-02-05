<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchedMovie extends Model
{
    protected $table = 'watched_movies';
    
    protected $fillable = [
        'user_id',
        'movie_id',
        'movie_title',
        'watched_date',
        'runtime',
        'added_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}