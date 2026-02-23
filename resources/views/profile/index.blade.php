@extends('layouts.app')

@section('content')
<div class="row">
    <!-- Left Column - User Info & Stats -->
    <div class="col-md-4 mb-4">
        <div class="card text-center p-4">
            <div class="avatar rounded-circle text-white d-flex align-items-center justify-content-center mx-auto mb-3" 
                style="width: 100px; height: 100px; font-size: 40px; background: linear-gradient(135deg, #e50914, #b81d24);">
                {{ strtoupper(substr($user->username, 0, 1)) }}
            </div>
            <h4 class="mb-1">{{ $user->username }}</h4>
            <p class="text-muted mb-4">{{ $user->email }}</p>

            <!-- Stats Grid -->
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <div class="stat-box">
                        <div class="stat-value">{{ $stats['review_count'] }}</div>
                        <div class="stat-label">Reviews</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="stat-box">
                        <div class="stat-value">{{ $stats['watched_count'] }}</div>
                        <div class="stat-label">Watched</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="stat-box">
                        <div class="stat-value">{{ $stats['average_rating'] ?? 'N/A' }}</div>
                        <div class="stat-label">Avg Rating</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="stat-box">
                        <div class="stat-value">{{ $stats['watchlist_count'] }}</div>
                        <div class="stat-label">Watchlist</div>
                    </div>
                </div>
            </div>

            <!-- Watch Time -->
            @if($stats['watched_count'] > 0)
            <div class="stat-box mb-3">
                <div class="stat-value">{{ $stats['total_hours'] }}h {{ $stats['total_minutes'] }}m</div>
                <div class="stat-label">Total Watch Time</div>
            </div>
            @endif

            <!-- Favourite Genre -->
            @if($stats['favourite_genre'])
            <div class="stat-box">
                <div class="stat-value" style="font-size: 1rem;">{{ $stats['favourite_genre'] }}</div>
                <div class="stat-label">Favourite Genre</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Right Column - Tabs -->
    <div class="col-md-8">
        <ul class="nav nav-tabs mb-3" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button">
                    <i class="fas fa-star me-1"></i>Reviews ({{ $stats['review_count'] }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="watchlist-tab" data-bs-toggle="tab" data-bs-target="#watchlist" type="button">
                    <i class="fas fa-bookmark me-1"></i>Watchlist ({{ $stats['watchlist_count'] }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="watched-tab" data-bs-toggle="tab" data-bs-target="#watched" type="button">
                    <i class="fas fa-check me-1"></i>Watched ({{ $stats['watched_count'] }})
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Reviews Tab -->
            <div class="tab-pane fade show active" id="reviews" role="tabpanel">
                @if($reviews->count() > 0)
                    @foreach($reviews as $review)
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex gap-3">
                                @if($review->poster_path)
                                <img src="https://image.tmdb.org/t/p/w92{{ $review->poster_path }}"
                                     alt="{{ $review->movie_title }}"
                                     class="rounded"
                                     style="width: 60px; height: 90px; object-fit: cover;">
                                @endif
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                <a href="{{ route('movie.show', $review->movie_id) }}" class="text-decoration-none">
                                                    {{ $review->movie_title }}
                                                </a>
                                            </h6>
                                            @if($review->release_year)
                                            <small class="text-muted">{{ $review->release_year }}</small>
                                            @endif
                                        </div>
                                        <span class="badge bg-primary">{{ number_format($review->rating, 1) }}/10</span>
                                    </div>
                                    <p class="mt-2 mb-1 small">{{ $review->review_text ?: 'No written review' }}</p>
                                    <small class="text-muted">{{ $review->created_at->format('M d, Y') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-star fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No reviews yet.</p>
                            <a href="{{ route('home') }}" class="btn btn-primary">Browse Movies</a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Watchlist Tab -->
            <div class="tab-pane fade" id="watchlist" role="tabpanel">
                @if($watchlist->count() > 0)
                    <div class="row g-3">
                        @foreach($watchlist as $item)
                        <div class="col-6 col-md-4">
                            <a href="{{ route('movie.show', $item->movie_id) }}" class="text-decoration-none">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $item->movie_title }}</h6>
                                        <small class="text-muted">Added {{ $item->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-bookmark fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Your watchlist is empty.</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Watched Tab -->
            <div class="tab-pane fade" id="watched" role="tabpanel">
                @if($watchedMovies->count() > 0)
                    <div class="row g-3">
                        @foreach($watchedMovies as $movie)
                        <div class="col-6 col-md-4">
                            <a href="{{ route('movie.show', $movie->movie_id) }}" class="text-decoration-none">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $movie->movie_title }}</h6>
                                        <small class="text-muted">Watched {{ $movie->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-check fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No watched movies yet.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection