@extends('layouts.app')

@section('content')
<!-- Hero Section with Scrolling Movie Banner -->
<div class="hero-banner">
    <!-- Scrolling Poster Background -->
    <div class="poster-scroll-container">
        <div class="poster-scroll">
            <div class="poster-row poster-row-1">
                @foreach(array_slice($heroPosters, 0, 7) as $poster)
                <div class="poster">
                    <img src="https://image.tmdb.org/t/p/w500{{ $poster }}" alt="Movie Poster">
                </div>
                @endforeach
                
                <!-- Duplicate for seamless loop -->
                @foreach(array_slice($heroPosters, 0, 7) as $poster)
                <div class="poster">
                    <img src="https://image.tmdb.org/t/p/w500{{ $poster }}" alt="Movie Poster">
                </div>
                @endforeach
            </div>
            
            <div class="poster-row poster-row-2">
                @foreach(array_slice($heroPosters, 7, 7) as $poster)
                <div class="poster">
                    <img src="https://image.tmdb.org/t/p/w500{{ $poster }}" alt="Movie Poster">
                </div>
                @endforeach
                
                <!-- Duplicate for seamless loop -->
                @foreach(array_slice($heroPosters, 7, 7) as $poster)
                <div class="poster">
                    <img src="https://image.tmdb.org/t/p/w500{{ $poster }}" alt="Movie Poster">
                </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <!-- Overlay with Content -->
    <div class="hero-overlay"></div>
    
    <div class="container position-relative">
        <div class="hero-content text-center">
            <h1 class="mb-3">Discover & Share Movie Reviews</h1>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <form action="{{ route('movie.search') }}" method="GET">
                        <div class="input-group input-group-lg">
                            <input type="text" class="form-control" name="query" placeholder="Search movies..." required>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Rated Movies Section -->
<div class="content-row">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4"><i class="fas fa-trophy me-2"></i>Top Rated Movies</h2>
    </div>
    
    <div class="position-relative">
        <!-- Left Control Arrow -->
        <button class="card-slider-control-prev d-none d-md-block" aria-label="Previous">
            <i class="fas fa-chevron-left fa-2x"></i>
        </button>
        
        <!-- Card Slider -->
        <div class="card-slider">
            @foreach($topRatedMovies as $movie)
            <div class="movie-card-container">
                <a href="{{ route('movie.show', $movie['id']) }}" class="text-decoration-none">
                    <div class="movie-card">
                        <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}" 
                             alt="{{ $movie['title'] }}"
                             onerror="this.src='{{ asset('images/no-poster.jpg') }}'">
                        
                        <div class="movie-rating">
                            {{ number_format($movie['vote_average'], 1) }}
                        </div>
                        
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
        
        <!-- Right Control Arrow -->
        <button class="card-slider-control-next d-none d-md-block" aria-label="Next">
            <i class="fas fa-chevron-right fa-2x"></i>
        </button>
    </div>
</div>

<!-- Popular Movies Section -->
<div class="content-row">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4"><i class="fas fa-fire me-2"></i>Popular Movies</h2>
    </div>
    
    <div class="position-relative">
        <button class="card-slider-control-prev d-none d-md-block" aria-label="Previous">
            <i class="fas fa-chevron-left fa-2x"></i>
        </button>
        
        <div class="card-slider">
            @foreach($popularMovies as $movie)
            <div class="movie-card-container">
                <a href="{{ route('movie.show', $movie['id']) }}" class="text-decoration-none">
                    <div class="movie-card">
                        <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}" 
                             alt="{{ $movie['title'] }}"
                             onerror="this.src='{{ asset('images/no-poster.jpg') }}'">
                        
                        <div class="movie-rating">
                            {{ number_format($movie['vote_average'], 1) }}
                        </div>
                        
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
        
        <button class="card-slider-control-next d-none d-md-block" aria-label="Next">
            <i class="fas fa-chevron-right fa-2x"></i>
        </button>
    </div>
</div>

<!-- Trending This Week Section -->
<div class="content-row">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4"><i class="fas fa-chart-line me-2"></i>Trending This Week</h2>
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
                        
                        <div class="movie-rating">
                            {{ number_format($movie['vote_average'], 1) }}
                        </div>
                        
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
        
        <button class="card-slider-control-next d-none d-md-block" aria-label="Next">
            <i class="fas fa-chevron-right fa-2x"></i>
        </button>
    </div>
</div>

<!-- Recent Activity / Join CTA -->
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="content-row">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h4"><i class="fas fa-history me-2"></i>Recent Activity</h2>
            </div>
            
            <div class="card">
                <div class="card-body p-0">
                    @if($recentReviews->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentReviews as $review)
                            <div class="list-group-item p-3">
                                <div class="d-flex">
                                    <div class="avatar rounded-circle text-white d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 20px;">
                                        {{ strtoupper(substr($review->user->username, 0, 1)) }}
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between mb-1">
                                            <div>
                                                <strong>{{ $review->user->username }}</strong>
                                                <span>reviewed</span>
                                                <strong>{{ $review->movie_title }}</strong>
                                            </div>
                                            <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-primary me-2">{{ number_format($review->rating, 1) }}/10</span>
                                            <p class="mb-0 text-light">{{ Str::limit($review->review_text, 100) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center p-5">
                            <i class="fas fa-film fa-3x text-muted mb-3"></i>
                            <h5>No Activity Yet</h5>
                            <p class="text-muted">Be the first to review a movie!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="content-row">
            <div class="card">
                <div class="card-body text-center p-4">
                    <i class="fas fa-film fa-3x text-primary mb-3"></i>
                    <h5>Join Our Community</h5>
                    <p class="text-muted mb-4">Create an account to review movies and engage with other movie lovers.</p>
                    @guest
                    <div class="d-grid gap-2">
                        <a href="{{ route('register') }}" class="btn btn-primary">
                            <i class="fas fa-user-plus me-1"></i>Register Now
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </div>
                    @else
                    <div class="d-grid">
                        <a href="{{ route('profile') }}" class="btn btn-primary">
                            <i class="fas fa-user me-1"></i>My Profile
                        </a>
                    </div>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize sliders
    $('.card-slider-control-prev').on('click', function() {
        const slider = $(this).closest('.content-row').find('.card-slider');
        slider.animate({ scrollLeft: '-=600' }, 300);
    });
    
    $('.card-slider-control-next').on('click', function() {
        const slider = $(this).closest('.content-row').find('.card-slider');
        slider.animate({ scrollLeft: '+=600' }, 300);
    });
});
</script>
@endsection