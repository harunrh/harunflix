@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="fas fa-tag me-2"></i>{{ $genreName }} Movies</h2>
    <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Back to Home
    </a>
</div>

<!-- Genre chips for quick switching -->
<div class="mb-4 d-flex flex-wrap gap-2">
    @foreach($genres as $g)
    <a href="{{ route('movie.genre', $g['id']) }}"
       class="btn btn-sm rounded-pill {{ $g['id'] == $genreId ? 'btn-primary' : 'btn-outline-primary' }}">
        {{ $g['name'] }}
    </a>
    @endforeach
</div>

<!-- Movie Grid -->
@if(count($movies) > 0)
<div class="row g-3">
    @foreach($movies as $movie)
    <div class="col-6 col-md-3 col-lg-2">
        <a href="{{ route('movie.show', $movie['id']) }}" class="text-decoration-none">
            <div class="card h-100" style="overflow: hidden;">
                @if($movie['poster_path'])
                <img src="https://image.tmdb.org/t/p/w342{{ $movie['poster_path'] }}"
                     alt="{{ $movie['title'] }}"
                     class="card-img-top"
                     style="width: 100%; aspect-ratio: 2/3; object-fit: cover;"
                     onerror="this.src='{{ asset('images/no-poster.jpg') }}'">
                @else
                <div class="bg-secondary d-flex align-items-center justify-content-center" style="aspect-ratio: 2/3;">
                    <i class="fas fa-film fa-2x text-muted"></i>
                </div>
                @endif
                <div class="card-body p-2">
                    <h6 class="card-title mb-1" style="font-size: 0.8rem;">{{ $movie['title'] }}</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">{{ isset($movie['release_date']) ? substr($movie['release_date'], 0, 4) : 'N/A' }}</small>
                        <span class="badge bg-warning text-dark" style="font-size: 0.7rem;">
                            <i class="fas fa-star"></i> {{ number_format($movie['vote_average'], 1) }}
                        </span>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>

<!-- Pagination -->
<div class="d-flex justify-content-center align-items-center gap-3 mt-4">
    @if($currentPage > 1)
    <a href="{{ route('movie.genre', $genreId) }}?page={{ $currentPage - 1 }}" class="btn btn-outline-primary">
        <i class="fas fa-chevron-left me-1"></i>Previous
    </a>
    @endif

    <span class="text-muted">Page {{ $currentPage }}</span>

    @if($currentPage < $totalPages)
    <a href="{{ route('movie.genre', $genreId) }}?page={{ $currentPage + 1 }}" class="btn btn-outline-primary">
        Next<i class="fas fa-chevron-right ms-1"></i>
    </a>
    @endif
</div>
@else
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-film fa-3x text-muted mb-3"></i>
        <p class="text-muted">No movies found for this genre.</p>
    </div>
</div>
@endif
@endsection