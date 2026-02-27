@extends('layouts.app')

@section('content')

<!-- Movie Header Section -->
<div class="card mb-4">
    <div class="card-body p-0">
        <div class="row g-0">
            <div class="col-md-4 col-lg-3 text-center p-3">
                <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}" 
                     alt="{{ $movie['title'] }}" 
                     class="img-fluid rounded shadow" 
                     style="max-height: 450px;"
                     onerror="this.src='{{ asset('images/no-poster.jpg') }}'">
            </div>
            <div class="col-md-8 col-lg-9 p-4">
                <h1 class="h2 mb-2">{{ $movie['title'] }}</h1>
                
                <div class="d-flex align-items-center flex-wrap mb-3">
                    <span class="badge bg-warning text-dark me-2 mb-1">
                        <i class="fas fa-star"></i> {{ number_format($movie['vote_average'], 1) }}/10 TMDB
                    </span>
                    
                    @if($ourReviewCount > 0)
                    <span class="badge bg-primary me-2 mb-1" id="harunflixRatingBadge">
                        <i class="fas fa-star"></i> {{ number_format($ourAverageRating, 1) }}/10 HarunFlix ({{ $ourReviewCount }} reviews)
                    </span>
                    @else
                    <span class="badge bg-primary me-2 mb-1 d-none" id="harunflixRatingBadge"></span>
                    @endif
                    
                    <span class="text-light me-3 mb-1">{{ $movie['release_date'] ?? 'N/A' }}</span>
                    
                    @if(isset($movie['runtime']))
                    <span class="text-light mb-1">{{ floor($movie['runtime']/60) }}h {{ $movie['runtime']%60 }}m</span>
                    @endif
                </div>
                
                <p class="mb-4">{{ $movie['overview'] }}</p>
                
                @if(isset($movie['genres']) && !empty($movie['genres']))
                <div class="mb-3">
                    @foreach($movie['genres'] as $genre)
                    <span class="badge bg-secondary me-1 mb-1">{{ $genre['name'] }}</span>
                    @endforeach
                </div>
                @endif
                
                <!-- Action Buttons -->
                <div class="d-flex gap-2 flex-wrap" id="actionButtons">
                    @auth
                        @if(!$userReview)
                            <button class="btn btn-primary" id="writeReviewBtn" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                <i class="fas fa-star me-1"></i>Write a Review
                            </button>
                        @else
                            <button class="btn btn-success" disabled>
                                <i class="fas fa-check me-1"></i>You've Reviewed This
                            </button>
                            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                <i class="fas fa-edit me-1"></i>Edit Review
                            </button>
                        @endif

                        <button 
                            id="watchlistBtn"
                            class="btn {{ $inWatchlist ? 'btn-warning' : 'btn-outline-warning' }}"
                            data-movie-id="{{ $movie['id'] }}"
                            data-movie-title="{{ $movie['title'] }}"
                            data-poster="{{ $movie['poster_path'] ?? '' }}"
                            data-in-watchlist="{{ $inWatchlist ? 'true' : 'false' }}">
                            <i class="fas fa-bookmark me-1"></i>
                            <span>{{ $inWatchlist ? 'In Watchlist' : 'Add to Watchlist' }}</span>
                        </button>

                        <button 
                            id="watchedBtn"
                            class="btn {{ $inWatched ? 'btn-info' : 'btn-outline-info' }}"
                            data-movie-id="{{ $movie['id'] }}"
                            data-movie-title="{{ $movie['title'] }}"
                            data-runtime="{{ $movie['runtime'] ?? 0 }}"
                            data-poster="{{ $movie['poster_path'] ?? '' }}"
                            data-in-watched="{{ $inWatched ? 'true' : 'false' }}">
                            <i class="fas fa-eye me-1"></i>
                            <span>{{ $inWatched ? 'Watched' : 'Mark as Watched' }}</span>
                        </button>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-1"></i>Login to Review
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cast Section -->
@if(isset($movie['credits']['cast']) && !empty($movie['credits']['cast']))
<div class="content-row mb-4">
    <h2 class="h4 mb-3"><i class="fas fa-users me-2"></i>Cast</h2>
    <div class="position-relative">
        <button class="card-slider-control-prev d-none d-md-block" aria-label="Previous">
            <i class="fas fa-chevron-left fa-2x"></i>
        </button>
        <div class="card-slider">
            @foreach(array_slice($movie['credits']['cast'], 0, 15) as $cast)
            <div class="movie-card-container">
                <div class="card">
                    @if($cast['profile_path'])
                    <img src="https://image.tmdb.org/t/p/w200{{ $cast['profile_path'] }}" 
                         class="card-img-top" 
                         alt="{{ $cast['name'] }}"
                         style="height: 270px; object-fit: cover;">
                    @else
                    <div class="bg-secondary d-flex align-items-center justify-content-center" style="height: 270px;">
                        <i class="fas fa-user fa-3x text-muted"></i>
                    </div>
                    @endif
                    <div class="card-body">
                        <h6 class="card-title mb-1" style="font-size: 0.9rem;">{{ $cast['name'] }}</h6>
                        <p class="card-text small text-muted mb-0">{{ $cast['character'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <button class="card-slider-control-next d-none d-md-block" aria-label="Next">
            <i class="fas fa-chevron-right fa-2x"></i>
        </button>
    </div>
</div>
@endif

<!-- Reviews and Stats Section -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-comments me-2"></i>User Reviews</h5>
            </div>
            <div class="card-body p-0" id="reviewsContainer">
                @if($reviews->count() > 0)
                <div class="list-group list-group-flush" id="reviewsList">
                    @foreach($reviews as $review)
                    <div class="list-group-item p-3">
                        <div class="d-flex">
                            <div class="letter-avatar me-3" style="width: 50px; height: 50px; font-size: 20px;">
                                {{ strtoupper(substr($review->user->username, 0, 1)) }}
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between mb-2 flex-wrap">
                                    <div>
                                        <strong>{{ $review->user->username }}</strong>
                                        <span class="badge bg-primary ms-2">{{ number_format($review->rating, 1) }}/10</span>
                                        @if(auth()->check() && auth()->id() === $review->user_id)
                                        <span class="badge bg-success ms-1">Your Review</span>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <small class="text-muted">{{ $review->created_at->format('M d, Y') }}</small>
                                        @auth
                                        <button class="btn btn-sm btn-outline-success like-btn"
                                            data-review-id="{{ $review->review_id }}"
                                            data-type="like"
                                            style="{{ isset($userReactions[$review->review_id]) && $userReactions[$review->review_id] === 'like' ? 'background-color: #198754; color: white;' : '' }}">
                                            <i class="fas fa-thumbs-up me-1"></i>
                                            <span class="like-count">{{ $review->likes->count() }}</span>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger dislike-btn"
                                            data-review-id="{{ $review->review_id }}"
                                            data-type="dislike"
                                            style="{{ isset($userReactions[$review->review_id]) && $userReactions[$review->review_id] === 'dislike' ? 'background-color: #dc3545; color: white;' : '' }}">
                                            <i class="fas fa-thumbs-down me-1"></i>
                                            <span class="dislike-count">{{ $review->dislikes->count() }}</span>
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary reactions-btn"
                                            data-review-id="{{ $review->review_id }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#reactionsModal">
                                            <i class="fas fa-users me-1"></i>
                                        </button>
                                        @endauth
                                        @if(auth()->check() && auth()->id() === $review->user_id)
                                        <form action="{{ route('review.destroy', $review->review_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this review?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                                <p class="mb-0 text-light">{{ $review->review_text ?: 'No written review' }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-5" id="noReviewsMsg">
                    <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No reviews yet. Be the first to review!</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Movie Details</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Release Date</span>
                        <span>{{ $movie['release_date'] ?? 'N/A' }}</span>
                    </li>
                    @if(isset($movie['runtime']))
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Runtime</span>
                        <span>{{ floor($movie['runtime']/60) }}h {{ $movie['runtime']%60 }}m</span>
                    </li>
                    @endif
                    <li class="list-group-item d-flex justify-content-between">
                        <span>TMDB Rating</span>
                        <span class="d-flex align-items-center">
                            {{ number_format($movie['vote_average'], 1) }}
                            <i class="fas fa-star text-warning ms-1"></i>
                        </span>
                    </li>
                    @if(isset($movie['vote_count']))
                    <li class="list-group-item d-flex justify-content-between">
                        <span>TMDB Votes</span>
                        <span>{{ number_format($movie['vote_count']) }}</span>
                    </li>
                    @endif
                    <li class="list-group-item d-flex justify-content-between" id="harunflixRatingRow" @if($ourReviewCount == 0) style="display:none" @endif>
                        <span>HarunFlix Rating</span>
                        <span class="d-flex align-items-center" id="harunflixRatingValue">
                            {{ number_format($ourAverageRating ?? 0, 1) }}
                            <i class="fas fa-star text-primary ms-1"></i>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between" id="harunflixReviewCountRow" @if($ourReviewCount == 0) style="display:none" @endif>
                        <span>HarunFlix Reviews</span>
                        <span id="harunflixReviewCount">{{ $ourReviewCount }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal -->
@auth
<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Review {{ $movie['title'] }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reviewForm">
                @csrf
                <input type="hidden" name="movie_id" value="{{ $movie['id'] }}">
                <input type="hidden" name="movie_title" value="{{ $movie['title'] }}">
                <input type="hidden" name="poster_path" value="{{ $movie['poster_path'] ?? '' }}">
                <input type="hidden" name="release_year" value="{{ isset($movie['release_date']) ? substr($movie['release_date'], 0, 4) : '' }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rating" class="form-label">Your Rating (0-10):</label>
                        <div class="rating-input-container">
                            <input type="number" class="form-control rating-input" id="rating" name="rating"
                                   min="0" max="10" step="0.5" value="{{ $userReview->rating ?? 8.0 }}" required>
                            <span class="rating-suffix">/10</span>
                        </div>
                        <small class="text-muted">Ratings can be from 0 to 10 in steps of 0.5</small>
                    </div>
                    <div class="mb-3">
                        <label for="review_text" class="form-label">Your Review:</label>
                        <textarea class="form-control" id="review_text" name="review_text" rows="4"
                                  placeholder="Write your thoughts about the movie...">{{ $userReview->review_text ?? '' }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="reviewSubmitBtn">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endauth

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Slider controls
        document.querySelectorAll('.card-slider-control-prev').forEach(button => {
            button.addEventListener('click', () => {
                const slider = button.closest('.content-row').querySelector('.card-slider');
                slider.scrollBy({ left: -600, behavior: 'smooth' });
            });
        });
        document.querySelectorAll('.card-slider-control-next').forEach(button => {
            button.addEventListener('click', () => {
                const slider = button.closest('.content-row').querySelector('.card-slider');
                slider.scrollBy({ left: 600, behavior: 'smooth' });
            });
        });

        // Watchlist button
        const watchlistBtn = document.getElementById('watchlistBtn');
        if (watchlistBtn) {
            watchlistBtn.addEventListener('click', async () => {
                const movieId = watchlistBtn.dataset.movieId;
                const movieTitle = watchlistBtn.dataset.movieTitle;
                const poster = watchlistBtn.dataset.poster;
                const inWatchlist = watchlistBtn.dataset.inWatchlist === 'true';
                const url = inWatchlist ? `/watchlist/remove/${movieId}` : '/watchlist/add';
                const options = inWatchlist
                    ? { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' } }
                    : { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }, body: JSON.stringify({ movie_id: movieId, movie_title: movieTitle, poster_path: poster }) };
                const res = await fetch(url, options);
                const data = await res.json();
                showToast(data.message, 'success');
                watchlistBtn.dataset.inWatchlist = inWatchlist ? 'false' : 'true';
                watchlistBtn.classList.toggle('btn-warning');
                watchlistBtn.classList.toggle('btn-outline-warning');
                watchlistBtn.querySelector('span').textContent = inWatchlist ? 'Add to Watchlist' : 'In Watchlist';
            });
        }

        // Watched button
        const watchedBtn = document.getElementById('watchedBtn');
        if (watchedBtn) {
            watchedBtn.addEventListener('click', async () => {
                const movieId = watchedBtn.dataset.movieId;
                const movieTitle = watchedBtn.dataset.movieTitle;
                const runtime = watchedBtn.dataset.runtime;
                const poster = watchedBtn.dataset.poster;
                const inWatched = watchedBtn.dataset.inWatched === 'true';
                const url = inWatched ? `/watched/remove/${movieId}` : '/watched/add';
                const options = inWatched
                    ? { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' } }
                    : { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }, body: JSON.stringify({ movie_id: movieId, movie_title: movieTitle, runtime: runtime, poster_path: poster }) };
                const res = await fetch(url, options);
                const data = await res.json();
                showToast(data.message, 'success');
                watchedBtn.dataset.inWatched = inWatched ? 'false' : 'true';
                watchedBtn.classList.toggle('btn-info');
                watchedBtn.classList.toggle('btn-outline-info');
                watchedBtn.querySelector('span').textContent = inWatched ? 'Mark as Watched' : 'Watched';
            });
        }

        // Like/dislike buttons
        document.querySelectorAll('.like-btn, .dislike-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const reviewId = btn.dataset.reviewId;
                const type = btn.dataset.type;
                const res = await fetch(`/review/${reviewId}/react`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ type })
                });
                const data = await res.json();
                const card = btn.closest('.list-group-item');
                const likeBtn = card.querySelector('.like-btn');
                const dislikeBtn = card.querySelector('.dislike-btn');
                likeBtn.querySelector('.like-count').textContent = data.likes;
                dislikeBtn.querySelector('.dislike-count').textContent = data.dislikes;
                likeBtn.style.backgroundColor = data.userReaction === 'like' ? '#198754' : '';
                likeBtn.style.color = data.userReaction === 'like' ? 'white' : '';
                dislikeBtn.style.backgroundColor = data.userReaction === 'dislike' ? '#dc3545' : '';
                dislikeBtn.style.color = data.userReaction === 'dislike' ? 'white' : '';
            });
        });

        // Reactions modal
        document.querySelectorAll('.reactions-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const reviewId = btn.dataset.reviewId;
                document.getElementById('likesList').innerHTML = '<li class="text-muted small">Loading...</li>';
                document.getElementById('dislikesList').innerHTML = '<li class="text-muted small">Loading...</li>';
                const res = await fetch(`/review/${reviewId}/reactions`);
                const data = await res.json();
                document.getElementById('likesList').innerHTML = data.likes.length
                    ? data.likes.map(u => `<li><i class="fas fa-user me-1 text-muted"></i>${u}</li>`).join('')
                    : '<li class="text-muted small">Nobody yet</li>';
                document.getElementById('dislikesList').innerHTML = data.dislikes.length
                    ? data.dislikes.map(u => `<li><i class="fas fa-user me-1 text-muted"></i>${u}</li>`).join('')
                    : '<li class="text-muted small">Nobody yet</li>';
            });
        });

        // Async review submission
        const reviewForm = document.getElementById('reviewForm');
        if (reviewForm) {
            reviewForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const submitBtn = document.getElementById('reviewSubmitBtn');
                submitBtn.disabled = true;
                submitBtn.textContent = 'Submitting...';

                const res = await fetch('{{ route("review.store") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: new FormData(reviewForm)
                });

                const data = await res.json();

                if (data.success) {
                    showToast(data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('reviewModal')).hide();

                    const noReviewsMsg = document.getElementById('noReviewsMsg');
                    if (noReviewsMsg) noReviewsMsg.remove();

                    const newReview = `
                        <div class="list-group-item p-3">
                            <div class="d-flex">
                                <div class="letter-avatar me-3" style="width: 50px; height: 50px; font-size: 20px;">
                                    {{ strtoupper(substr(auth()->user()->username, 0, 1)) }}
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between mb-2 flex-wrap">
                                        <div>
                                            <strong>{{ auth()->user()->username }}</strong>
                                            <span class="badge bg-primary ms-2">${parseFloat(data.review.rating).toFixed(1)}/10</span>
                                            <span class="badge bg-success ms-1">Your Review</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <small class="text-muted">Just now</small>
                                        </div>
                                    </div>
                                    <p class="mb-0 text-light">${data.review.review_text || 'No written review'}</p>
                                </div>
                            </div>
                        </div>`;

                    let reviewsList = document.getElementById('reviewsList');
                    if (!reviewsList) {
                        document.getElementById('reviewsContainer').innerHTML = '<div class="list-group list-group-flush" id="reviewsList"></div>';
                        reviewsList = document.getElementById('reviewsList');
                    }
                    reviewsList.insertAdjacentHTML('afterbegin', newReview);

                    const countEl = document.getElementById('harunflixReviewCount');
                    const ratingEl = document.getElementById('harunflixRatingValue');
                    const ratingRow = document.getElementById('harunflixRatingRow');
                    const countRow = document.getElementById('harunflixReviewCountRow');
                    if (countEl) countEl.textContent = data.new_count;
                    if (ratingEl) ratingEl.innerHTML = `${parseFloat(data.new_average).toFixed(1)} <i class="fas fa-star text-primary ms-1"></i>`;
                    if (ratingRow) ratingRow.style.display = '';
                    if (countRow) countRow.style.display = '';

                    const writeBtn = document.getElementById('writeReviewBtn');
                    if (writeBtn) {
                        writeBtn.outerHTML = `<button class="btn btn-success" disabled><i class="fas fa-check me-1"></i>You've Reviewed This</button>`;
                    }
                } else {
                    showToast(data.message || 'Something went wrong', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Review';
                }
            });
        }
    });
</script>

<!-- Reactions Modal -->
<div class="modal fade" id="reactionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-users me-2"></i>Reactions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6">
                        <h6 class="text-success"><i class="fas fa-thumbs-up me-1"></i>Liked by</h6>
                        <ul class="list-unstyled" id="likesList">
                            <li class="text-muted small">Loading...</li>
                        </ul>
                    </div>
                    <div class="col-6">
                        <h6 class="text-danger"><i class="fas fa-thumbs-down me-1"></i>Disliked by</h6>
                        <ul class="list-unstyled" id="dislikesList">
                            <li class="text-muted small">Loading...</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection