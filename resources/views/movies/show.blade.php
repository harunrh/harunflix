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


    </div>
</div>

@auth
<div class="mb-4">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reviewModal">
        Write a Review
    </button>

    @if($inWatchlist)
        <form action="{{ route('watchlist.remove', $movie['id']) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-bookmark"></i> Remove from Watchlist
            </button>
        </form>
    @else
        <form action="{{ route('watchlist.add') }}" method="POST" class="d-inline">
            @csrf
            <input type="hidden" name="movie_id" value="{{ $movie['id'] }}">
            <input type="hidden" name="movie_title" value="{{ $movie['title'] }}">
            <button type="submit" class="btn btn-outline-warning">
                <i class="far fa-bookmark"></i> Add to Watchlist
            </button>
        </form>
    @endif

    @if($isWatched)
        <form action="{{ route('watched.remove', $movie['id']) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-success">
                <i class="fas fa-check-circle"></i> Watched
            </button>
        </form>
    @else
        <form action="{{ route('watched.add') }}" method="POST" class="d-inline">
            @csrf
            <input type="hidden" name="movie_id" value="{{ $movie['id'] }}">
            <input type="hidden" name="movie_title" value="{{ $movie['title'] }}">
            <input type="hidden" name="runtime" value="{{ $movie['runtime'] ?? null }}">
            <button type="submit" class="btn btn-outline-success">
                <i class="far fa-check-circle"></i> Mark as Watched
            </button>
        </form>
    @endif
</div>
@else
<p>Please <a href="/login">login</a> to write reviews and manage your watchlist.</p>
@endauth

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

<!-- Reviews Section -->
<div class="mt-5">
    <h3 class="mb-4">Reviews</h3>
    @if($reviews->count() > 0)
        @foreach($reviews as $review)
        <div class="card mb-3">
            <div class="card-body">
                <h5>{{ $review->user->username }}</h5>
                <p><strong>Rating:</strong> {{ $review->rating }}/10</p>
                <p>{{ $review->review_text }}</p>
                <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
            </div>
        </div>
        @endforeach
    @else
        <p class="text-muted">No reviews yet. Be the first to review this movie!</p>
    @endif
</div>
@endsection