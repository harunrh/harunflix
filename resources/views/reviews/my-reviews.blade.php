@extends('layouts.app')

@section('content')

<!-- Flash Messages -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">
        <i class="fas fa-star text-warning me-2"></i>My Reviews
    </h1>
    <a href="{{ route('home') }}" class="btn btn-primary">
        <i class="fas fa-edit me-2"></i>Write a Review
    </a>
</div>

@if($reviews->count() > 0)
    @foreach($reviews as $review)
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <!-- Movie Poster -->
                <div class="col-auto">
                    <a href="{{ route('movie.show', $review->movie_id) }}">
                        <img src="https://image.tmdb.org/t/p/w92{{ $review->poster_path ?? '' }}" 
                            alt="{{ $review->movie_title }}"
                            class="rounded shadow-sm"
                            style="width: 70px; height: 105px; object-fit: cover;"
                             onerror="this.src='{{ asset('images/no-poster.jpg') }}'">
                    </a>
                </div>
                
                <!-- Review Content -->
                <div class="col">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h5 class="mb-1">
                                <a href="{{ route('movie.show', $review->movie_id) }}" class="text-decoration-none">
                                    {{ $review->movie_title }}
                                </a>
                            </h5>
                            @if($review->release_year)
                            <small class="text-muted">{{ $review->release_year }}</small>
                            @endif
                        </div>
                        <span class="badge bg-primary" style="font-size: 0.9rem;">
                            {{ number_format($review->rating, 1) }}/10
                        </span>
                    </div>
                    
                    <p class="mb-2 fst-italic text-light">
                        {{ $review->review_text ?: 'No written review' }}
                    </p>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            Reviewed on {{ $review->created_at->format('M d, Y') }}
                        </small>
                        <form action="{{ route('review.destroy', $review->review_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this review?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash me-1"></i>Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
@else
    <!-- Empty State -->
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-star fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">No Reviews Yet</h4>
            <p class="text-muted mb-4">Start reviewing movies to see them here!</p>
            <a href="{{ route('home') }}" class="btn btn-primary">
                <i class="fas fa-home me-2"></i>Browse Movies
            </a>
        </div>
    </div>
@endif

@endsection
