@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h2 class="mb-4">{{ $user->username }}'s Profile</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-user fa-5x mb-3"></i>
                <h4>{{ $user->username }}</h4>
                <p class="text-muted">{{ $user->email }}</p>
                <hr>
                <div class="row">
                    <div class="col-4">
                        <h5>{{ $reviews->count() }}</h5>
                        <small>Reviews</small>
                    </div>
                    <div class="col-4">
                        <h5>{{ $watchlist->count() }}</h5>
                        <small>Watchlist</small>
                    </div>
                    <div class="col-4">
                        <h5>{{ $watchedMovies->count() }}</h5>
                        <small>Watched</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <ul class="nav nav-tabs mb-3" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button">
                    Reviews ({{ $reviews->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="watchlist-tab" data-bs-toggle="tab" data-bs-target="#watchlist" type="button">
                    Watchlist ({{ $watchlist->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="watched-tab" data-bs-toggle="tab" data-bs-target="#watched" type="button">
                    Watched ({{ $watchedMovies->count() }})
                </button>
            </li>
        </ul>

        <div class="tab-content" id="profileTabsContent">
            <div class="tab-pane fade show active" id="reviews" role="tabpanel">
                @if($reviews->count() > 0)
                    @foreach($reviews as $review)
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5>{{ $review->movie_title }}</h5>
                            <p><strong>Rating:</strong> {{ $review->rating }}/10</p>
                            <p>{{ $review->review_text }}</p>
                            <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                    @endforeach
                @else
                    <p class="text-muted">No reviews yet.</p>
                @endif
            </div>

            <div class="tab-pane fade" id="watchlist" role="tabpanel">
                @if($watchlist->count() > 0)
                    <div class="row g-3">
                        @foreach($watchlist as $item)
                        <div class="col-6 col-md-4">
                            <a href="/movie/{{ $item->movie_id }}" class="text-decoration-none text-white">
                                <div class="movie-card">
                                    <h6>{{ $item->movie_title }}</h6>
                                    <small class="text-muted">Added {{ $item->created_at->diffForHumans() }}</small>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">Your watchlist is empty.</p>
                @endif
            </div>

            <div class="tab-pane fade" id="watched" role="tabpanel">
                @if($watchedMovies->count() > 0)
                    <div class="row g-3">
                        @foreach($watchedMovies as $movie)
                        <div class="col-6 col-md-4">
                            <a href="/movie/{{ $movie->movie_id }}" class="text-decoration-none text-white">
                                <div class="movie-card">
                                    <h6>{{ $movie->movie_title }}</h6>
                                    <small class="text-muted">Watched {{ $movie->created_at->diffForHumans() }}</small>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No watched movies yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection