@extends('layouts.app')

@section('content')

<!-- Page Header with Genre Dropdown -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="fas fa-film me-2"></i>{{ $genreView ? $selectedGenreName : 'Movies' }}</h2>
    <div class="d-flex align-items-center gap-2">
        <label class="text-muted mb-0 me-1">Genre:</label>
        <select class="form-select form-select-sm" style="width: auto; min-width: 180px;"
            onchange="if(this.value) window.location.href='{{ route('movies.index') }}?genre='+this.value; else window.location.href='{{ route('movies.index') }}'">
            <option value="">All Movies</option>
            @foreach($genres as $genre)
            <option value="{{ $genre['id'] }}" {{ isset($selectedGenreId) && $selectedGenreId == $genre['id'] ? 'selected' : '' }}>
                {{ $genre['name'] }}
            </option>
            @endforeach
        </select>
    </div>
</div>

@if($genreView)
    <!-- Genre Grid View -->
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

    <div class="d-flex justify-content-center align-items-center gap-3 mt-4">
        @if($currentPage > 1)
        <a href="{{ route('movies.index') }}?genre={{ $selectedGenreId }}&page={{ $currentPage - 1 }}" class="btn btn-outline-primary">
            <i class="fas fa-chevron-left me-1"></i>Previous
        </a>
        @endif
        <span class="text-muted">Page {{ $currentPage }}</span>
        @if($currentPage < $totalPages)
        <a href="{{ route('movies.index') }}?genre={{ $selectedGenreId }}&page={{ $currentPage + 1 }}" class="btn btn-outline-primary">
            Next<i class="fas fa-chevron-right ms-1"></i>
        </a>
        @endif
    </div>
    @endif

@else
    <!-- Category Slider Sections -->

    <!-- New Releases -->
    @if(count($newReleases) > 0)
    <div class="content-row">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4"><i class="fas fa-certificate me-2 text-danger"></i>New Releases</h2>
        </div>
        <div class="position-relative">
            <button class="card-slider-control-prev d-none d-md-block" aria-label="Previous">
                <i class="fas fa-chevron-left fa-2x"></i>
            </button>
            <div class="card-slider">
                @foreach($newReleases as $movie)
                <div class="movie-card-container">
                    <a href="{{ route('movie.show', $movie['id']) }}" class="text-decoration-none">
                        <div class="movie-card">
                            <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}"
                                 alt="{{ $movie['title'] }}"
                                 onerror="this.src='{{ asset('images/no-poster.jpg') }}'">
                            <div class="movie-rating">{{ number_format($movie['vote_average'], 1) }}</div>
                            <div class="card-body">
                                <div class="movie-title">{{ $movie['title'] }}</div>
                                <div class="movie-year">{{ isset($movie['release_date']) ? date('Y', strtotime($movie['release_date'])) : 'N/A' }}</div>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
            <button class="card-slider-control-next d-none d-md-block" aria-label="Next">
                <i class="fas fa-chevron-right fa-2x"></i>
            </button>
        </div>
    </div>
    @endif

    <!-- Top Rated by HarunFlix Users -->
    @if($topRatedByUsers->count() > 0)
    <div class="content-row">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4"><i class="fas fa-star me-2 text-warning"></i>Top Rated on HarunFlix</h2>
        </div>
        <div class="position-relative">
            <button class="card-slider-control-prev d-none d-md-block" aria-label="Previous">
                <i class="fas fa-chevron-left fa-2x"></i>
            </button>
            <div class="card-slider">
                @foreach($topRatedByUsers as $movie)
                <div class="movie-card-container">
                    <a href="{{ route('movie.show', $movie->movie_id) }}" class="text-decoration-none">
                        <div class="movie-card">
                            @if($movie->poster_path)
                            <img src="https://image.tmdb.org/t/p/w500{{ $movie->poster_path }}"
                                 alt="{{ $movie->movie_title }}"
                                 onerror="this.src='{{ asset('images/no-poster.jpg') }}'">
                            @else
                            <div class="bg-secondary d-flex align-items-center justify-content-center" style="height: 270px;">
                                <i class="fas fa-film fa-3x text-muted"></i>
                            </div>
                            @endif
                            <div class="movie-rating">{{ number_format($movie->avg_rating, 1) }}</div>
                            <div class="card-body">
                                <div class="movie-title">{{ $movie->movie_title }}</div>
                                <div class="movie-year">{{ $movie->review_count }} reviews</div>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
            <button class="card-slider-control-next d-none d-md-block" aria-label="Next">
                <i class="fas fa-chevron-right fa-2x"></i>
            </button>
        </div>
    </div>
    @endif
    

    <!-- Most Reviewed on HarunFlix -->
    @if($mostReviewed->count() > 0)
    <div class="content-row">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4"><i class="fas fa-comments me-2 text-primary"></i>Most Reviewed on HarunFlix</h2>
        </div>
        <div class="position-relative">
            <button class="card-slider-control-prev d-none d-md-block" aria-label="Previous">
                <i class="fas fa-chevron-left fa-2x"></i>
            </button>
            <div class="card-slider">
                @foreach($mostReviewed as $movie)
                <div class="movie-card-container">
                    <a href="{{ route('movie.show', $movie->movie_id) }}" class="text-decoration-none">
                        <div class="movie-card">
                            @if($movie->poster_path)
                            <img src="https://image.tmdb.org/t/p/w500{{ $movie->poster_path }}"
                                 alt="{{ $movie->movie_title }}"
                                 onerror="this.src='{{ asset('images/no-poster.jpg') }}'">
                            @else
                            <div class="bg-secondary d-flex align-items-center justify-content-center" style="height: 270px;">
                                <i class="fas fa-film fa-3x text-muted"></i>
                            </div>
                            @endif
                            <div class="movie-rating">{{ $movie->review_count }}</div>
                            <div class="card-body">
                                <div class="movie-title">{{ $movie->movie_title }}</div>
                                <div class="movie-year">Avg: {{ number_format($movie->avg_rating, 1) }}/10</div>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
            <button class="card-slider-control-next d-none d-md-block" aria-label="Next">
                <i class="fas fa-chevron-right fa-2x"></i>
            </button>
        </div>
    </div>
    @endif

    <!-- Recently Rated on HarunFlix -->
    @if($recentlyRated->count() > 0)
    <div class="content-row">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4"><i class="fas fa-clock me-2 text-info"></i>Recently Rated on HarunFlix</h2>
        </div>
        <div class="position-relative">
            <button class="card-slider-control-prev d-none d-md-block" aria-label="Previous">
                <i class="fas fa-chevron-left fa-2x"></i>
            </button>
            <div class="card-slider">
                @foreach($recentlyRated as $movie)
                <div class="movie-card-container">
                    <a href="{{ route('movie.show', $movie->movie_id) }}" class="text-decoration-none">
                        <div class="movie-card">
                            @if($movie->poster_path)
                            <img src="https://image.tmdb.org/t/p/w500{{ $movie->poster_path }}"
                                 alt="{{ $movie->movie_title }}"
                                 onerror="this.src='{{ asset('images/no-poster.jpg') }}'">
                            @else
                            <div class="bg-secondary d-flex align-items-center justify-content-center" style="height: 270px;">
                                <i class="fas fa-film fa-3x text-muted"></i>
                            </div>
                            @endif
                            <div class="movie-rating">{{ number_format($movie->rating, 1) }}</div>
                            <div class="card-body">
                                <div class="movie-title">{{ $movie->movie_title }}</div>
                                <div class="movie-year">{{ $movie->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
            <button class="card-slider-control-next d-none d-md-block" aria-label="Next">
                <i class="fas fa-chevron-right fa-2x"></i>
            </button>
        </div>
    </div>
    @endif

    <!-- Trending This Week (TMDB) -->
    @if(count($trendingMovies) > 0)
    <div class="content-row">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4"><i class="fas fa-chart-line me-2 text-success"></i>Trending This Week</h2>
        </div>
        <div class="position-relative">
            <button class="card-slider-control-prev d-none d-md-block" aria-label="Previous">
                <i class="fas fa-chevron-left fa-2x"></i>
            </button>
            <div class="card-slider">
                @foreach($trendingMovies as $movie)
                <div class="movie-card-container">
                    <a href="{{ route('movie.show', $movie['id']) }}" class="text-decoration-none">
                        <div class="movie-card">
                            <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}"
                                 alt="{{ $movie['title'] }}"
                                 onerror="this.src='{{ asset('images/no-poster.jpg') }}'">
                            <div class="movie-rating">{{ number_format($movie['vote_average'], 1) }}</div>
                            <div class="card-body">
                                <div class="movie-title">{{ $movie['title'] }}</div>
                                <div class="movie-year">{{ isset($movie['release_date']) ? date('Y', strtotime($movie['release_date'])) : 'N/A' }}</div>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
            <button class="card-slider-control-next d-none d-md-block" aria-label="Next">
                <i class="fas fa-chevron-right fa-2x"></i>
            </button>
        </div>
    </div>
    @endif

@endif

<script>
    document.addEventListener('DOMContentLoaded', () => {
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
    });
</script>

@endsection