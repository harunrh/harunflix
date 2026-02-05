<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Watchlist extends Model
{
    protected $table = 'watchlist';
    
    protected $fillable = [
        'user_id',
        'movie_id',
        'movie_title',
        'added_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}