@extends('layouts.app')

@section('content')
<!-- Hero Section with Scrolling Movie Banner -->
<div class="hero-banner">
    <!-- Scrolling Poster Background -->
    <div class="poster-scroll-container">
        <div class="poster-scroll">
            <div class="poster-row poster-row-1">
                <!-- These would be dynamic from your database -->
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/8Gxv8gSFCU0XGDykEGv7zR1n2ua.jpg" alt="Movie"></div>
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/qNBAXBIQlnOThrVvA6mA2B5ggV6.jpg" alt="Movie"></div>
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/cinER0ESG0eJ49kXlExM0MEWGxW.jpg" alt="Movie"></div>
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/7fn624j5lj3xTme2SgiLCeuedmO.jpg" alt="Movie"></div>
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/xvzxqKWLY8pH8dT0TZ0qLvE9Cv9.jpg" alt="Movie"></div>
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/gKkl37BQuKTanygYQG1pyYgLVgf.jpg" alt="Movie"></div>
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/kJwRqSzHzrdpvOMFalHQZbb2EJI.jpg" alt="Movie"></div>
                
                <!-- Duplicate for seamless loop -->
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/8Gxv8gSFCU0XGDykEGv7zR1n2ua.jpg" alt="Movie"></div>
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/qNBAXBIQlnOThrVvA6mA2B5ggV6.jpg" alt="Movie"></div>
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/cinER0ESG0eJ49kXlExM0MEWGxW.jpg" alt="Movie"></div>
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/7fn624j5lj3xTme2SgiLCeuedmO.jpg" alt="Movie"></div>
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/xvzxqKWLY8pH8dT0TZ0qLvE9Cv9.jpg" alt="Movie"></div>
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/gKkl37BQuKTanygYQG1pyYgLVgf.jpg" alt="Movie"></div>
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/kJwRqSzHzrdpvOMFalHQZbb2EJI.jpg" alt="Movie"></div>
            </div>
            
            <div class="poster-row poster-row-2">
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/9SSEUrSqhljBMzRe4aBTh17rUaC.jpg" alt="Movie"></div>
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/yDHYTfA3R0jFYba16jBB1ef8oIt.jpg" alt="Movie"></div>
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/aosm8NMQ3UyoBVpSxyimorCQykC.jpg" alt="Movie"></div>
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/oGythE98MYleE6mZlGs5oBGkux1.jpg" alt="Movie"></div>
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/1E5baAaEse26fej7uHcjOgEE2t2.jpg" alt="Movie"></div>
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/vZloFAK7NmvMGKE7VkF5UHaz0I.jpg" alt="Movie"></div>
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/pB8BM7pdSp6B6Ih7QZ4DrQ3PmJK.jpg" alt="Movie"></div>
                
                <!-- Duplicate -->
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/9SSEUrSqhljBMzRe4aBTh17rUaC.jpg" alt="Movie"></div>
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/yDHYTfA3R0jFYba16jBB1ef8oIt.jpg" alt="Movie"></div>
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/aosm8NMQ3UyoBVpSxyimorCQykC.jpg" alt="Movie"></div>
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/oGythE98MYleE6mZlGs5oBGkux1.jpg" alt="Movie"></div>
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/1E5baAaEse26fej7uHcjOgEE2t2.jpg" alt="Movie"></div>
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/vZloFAK7NmvMGKE7VkF5UHaz0I.jpg" alt="Movie"></div>
                <div class="poster"><img src="https://image.tmdb.org/t/p/w500/pB8BM7pdSp6B6Ih7QZ4DrQ3PmJK.jpg" alt="Movie"></div>
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
                            <input type="text" class="form-control" name="query" placeholder="Search movies...">
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
        <a href="#" class="btn btn-outline-primary btn-sm">See All</a>
    </div>
    
    <div class="position-relative">
        <!-- Left Control Arrow -->
        <button class="card-slider-control-prev d-none d-md-block" aria-label="Previous">
            <i class="fas fa-chevron-left fa-2x"></i>
        </button>
        
        <!-- Card Slider -->
        <div class="card-slider">
            <!-- Example movie cards - make these dynamic -->
            @for($i = 0; $i < 10; $i++)
            <div class="movie-card-container">
                <a href="#" class="text-decoration-none">
                    <div class="movie-card">
                        <img src="https://image.tmdb.org/t/p/w500/8Gxv8gSFCU0XGDykEGv7zR1n2ua.jpg" alt="Movie Title">
                        <div class="movie-rating">8.5</div>
                        <div class="card-body">
                            <div class="movie-title">The Shawshank Redemption</div>
                            <div class="movie-year">1994</div>
                        </div>
                    </div>
                </a>
            </div>
            @endfor
        </div>
        
        <!-- Right Control Arrow -->
        <button class="card-slider-control-next d-none d-md-block" aria-label="Next">
            <i class="fas fa-chevron-right fa-2x"></i>
        </button>
    </div>
</div>

<!-- Recent Activity Section -->
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="content-row">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h4"><i class="fas fa-history me-2"></i>Recent Activity</h2>
            </div>
            
            <div class="card">
                <div class="card-body p-0">
                    <div class="text-center p-5">
                        <i class="fas fa-film fa-3x text-muted mb-3"></i>
                        <h5>No Activity Yet</h5>
                        <p class="text-muted">There are no movie reviews yet.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="content-row">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h4"><i class="fas fa-users me-2"></i>Top Reviewers</h2>
            </div>
            
            <div class="card">
                <div class="card-body text-center p-4">
                    <i class="fas fa-film fa-3x text-primary mb-3"></i>
                    <h5>Join Our Community</h5>
                    <p class="text-muted mb-4">Create an account to review movies.</p>
                    @guest
                    <div class="d-grid gap-2">
                        <a href="{{ route('register') }}" class="btn btn-primary">
                            <i class="fas fa-user-plus me-1"></i>Register Now
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
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