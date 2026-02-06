@extends('layouts.app')

@section('content')
<div class="mb-5">
    <h2 class="mb-4">Popular Movies</h2>
    <div class="row g-3">
        @foreach($popularMovies as $movie)
        <div class="col-6 col-md-3 col-lg-2">
            <a href="/movie/{{ $movie['id'] }}" class="text-decoration-none text-white">
                <div class="movie-card">
                    <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}" 
                        class="img-fluid rounded" 
                        alt="{{ $movie['title'] }}">
                    <h6 class="mt-2">{{ $movie['title'] }}</h6>
                    <small class="text-muted">{{ substr($movie['release_date'] ?? 'N/A', 0, 4) }}</small>
                </div>
            </a>
        </div>
        @endforeach
    </div>
</div>

<div class="mb-5">
    <h2 class="mb-4">Trending This Week</h2>
    <div class="row g-3">
        @foreach($trendingMovies as $movie)
        <div class="col-6 col-md-3 col-lg-2">
            <a href="/movie/{{ $movie['id'] }}" class="text-decoration-none text-white">
                <div class="movie-card">
                    <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}" 
                        class="img-fluid rounded" 
                        alt="{{ $movie['title'] }}">
                    <h6 class="mt-2">{{ $movie['title'] }}</h6>
                    <small class="text-muted">{{ substr($movie['release_date'] ?? 'N/A', 0, 4) }}</small>
                </div>
            </a>
        </div>
        @endforeach
    </div>
</div>

@if($recentReviews->count() > 0)
<div class="mb-5">
    <h2 class="mb-4">Recent Reviews</h2>
    @foreach($recentReviews as $review)
    <div class="card mb-3">
        <div class="card-body">
            <h5>{{ $review->movie_title }}</h5>
            <p><strong>{{ $review->user->username }}</strong> rated it {{ $review->rating }}/10</p>
            <p>{{ $review->review_text }}</p>
            <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection