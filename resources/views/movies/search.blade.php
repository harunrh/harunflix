@extends('layouts.app')

@section('content')
<div class="mb-4">
    <h2>Search Results for "{{ $query }}"</h2>
    <p class="text-muted">Found {{ count($movies) }} results</p>
</div>

@if(count($movies) > 0)
<div class="row g-3">
    @foreach($movies as $movie)
    <div class="col-6 col-md-3 col-lg-2">
        <a href="/movie/{{ $movie['id'] }}" class="text-decoration-none text-white">
            <div class="movie-card">
                @if($movie['poster_path'])
                    <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}" 
                         class="img-fluid rounded" 
                         alt="{{ $movie['title'] }}">
                @else
                    <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="height: 300px;">
                        <span>No Poster</span>
                    </div>
                @endif
                <h6 class="mt-2">{{ $movie['title'] }}</h6>
                <small class="text-muted">{{ substr($movie['release_date'] ?? 'N/A', 0, 4) }}</small>
            </div>
        </a>
    </div>
    @endforeach
</div>
@else
<div class="alert alert-info">
    No movies found for "{{ $query }}". Try a different search term.
</div>
@endif
@endsection