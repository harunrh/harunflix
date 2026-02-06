@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-4">
        <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}" 
             class="img-fluid rounded" 
             alt="{{ $movie['title'] }}">
    </div>
    <div class="col-md-8">
        <h1>{{ $movie['title'] }}</h1>
        <p class="text-muted">{{ $movie['release_date'] ?? 'N/A' }}</p>
        
        @if(isset($movie['vote_average']))
        <p><strong>TMDB Rating:</strong> {{ number_format($movie['vote_average'], 1) }}/10</p>
        @endif
        
        @if(isset($movie['runtime']))
        <p><strong>Runtime:</strong> {{ $movie['runtime'] }} minutes</p>
        @endif
        
        <p class="mt-3">{{ $movie['overview'] }}</p>
        
        @if(isset($movie['genres']))
        <p>
            <strong>Genres:</strong>
            @foreach($movie['genres'] as $genre)
                <span class="badge bg-secondary">{{ $genre['name'] }}</span>
            @endforeach
        </p>
        @endif

        @auth
        <button class="btn btn-danger mt-3" data-bs-toggle="modal" data-bs-target="#reviewModal">
            <i class="fas fa-star"></i> Write a Review
        </button>
        @else
        <a href="/login" class="btn btn-danger mt-3">Login to Review</a>
        @endauth
    </div>
</div>

<div class="mt-5">
    <h3>Reviews ({{ $reviews->count() }})</h3>
    
    @if($reviews->count() > 0)
        @foreach($reviews as $review)
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h5>{{ $review->user->username }}</h5>
                    <span class="badge bg-danger">{{ $review->rating }}/10</span>
                </div>
                <p class="mt-2">{{ $review->review_text }}</p>
                <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
            </div>
        </div>
        @endforeach
    @else
        <div class="card">
            <div class="card-body text-center">
                <p>No reviews yet. Be the first to review!</p>
            </div>
        </div>
    @endif
</div>

<!-- Review Modal -->
@auth
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title">Review {{ $movie['title'] }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="/review" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="movie_id" value="{{ $movie['id'] }}">
                    <input type="hidden" name="movie_title" value="{{ $movie['title'] }}">
                    
                    <div class="mb-3">
                        <label class="form-label">Your Rating (0-10)</label>
                        <input type="number" name="rating" class="form-control" min="0" max="10" step="0.5" value="8" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Your Review</label>
                        <textarea name="review_text" class="form-control" rows="4" placeholder="Write your thoughts..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endauth
@endsection