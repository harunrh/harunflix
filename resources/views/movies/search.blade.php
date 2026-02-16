@extends('layouts.app')

@section('content')
<div class="content-row">
    <h2 class="h3 mb-4">
        <i class="fas fa-search me-2"></i>Search Results for "{{ $query }}"
    </h2>
    
    @if($totalResults > 0)
        <p class="text-muted">Found {{ $totalResults }} results</p>
        
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3 mb-4">
            @foreach($movies as $movie)
                <div class="col">
                    <a href="{{ route('movie.show', $movie['id']) }}" class="text-decoration-none">
                        <div class="movie-card">
                            <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] ?? '' }}" 
                                 alt="{{ $movie['title'] }}"
                                 onerror="this.src='{{ asset('images/no-poster.jpg') }}'">
                            
                            @if(isset($movie['vote_average']))
                            <div class="movie-rating">
                                {{ number_format($movie['vote_average'], 1) }}
                            </div>
                            @endif
                            
                            <div class="card-body">
                                <div class="movie-title">{{ $movie['title'] }}</div>
                                <div class="movie-year">
                                    {{ isset($movie['release_date']) ? date('Y', strtotime($movie['release_date'])) : 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @else
        <div class="card mb-4">
            <div class="card-body text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4>No Results Found</h4>
                <p class="text-muted">We couldn't find any movies matching "{{ $query }}"</p>
                <a href="{{ route('home') }}" class="btn btn-primary mt-2">Back to Home</a>
            </div>
        </div>
    @endif
</div>
@endsection