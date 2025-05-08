$(document).ready(function() {
    // Navbar scroll effect
    $(window).scroll(function() {
        if ($(this).scrollTop() > 50) {
            $('.navbar').addClass('scrolled');
        } else {
            $('.navbar').removeClass('scrolled');
        }
    });
    
    // Movie search with delay to avoid too many requests
    let searchTimeout;
    
    // Create a container for search results if it doesn't exist
    if ($('#search-results').length === 0) {
        $('#movie-search').parent().append('<div id="search-results" class="position-absolute w-100 mt-1" style="z-index: 1000; top: 100%;"></div>');
    }
    
    $('#movie-search').on('input', function() {
        const query = $(this).val().trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        // Clear results if query is empty
        if (query === '') {
            $('#search-results').empty();
            return;
        }
        
        // Set a timeout to avoid sending too many requests while typing
        searchTimeout = setTimeout(function() {
            $.ajax({
                url: 'search.php',
                method: 'GET',
                data: { query: query },
                dataType: 'json',
                success: function(data) {
                    const results = data.results || [];
                    displaySearchResults(results);
                },
                error: function(xhr, status, error) {
                    console.error('Error searching movies:', error);
                }
            });
        }, 300); // 300ms delay
    });
    
    // Function to display search results
    function displaySearchResults(results) {
        const resultsContainer = $('#search-results');
        resultsContainer.empty();
        
        if (results.length === 0) {
            resultsContainer.append('<p class="text-center p-3 rounded">No movies found</p>');
            return;
        }
        
        // Create a list group for styling
        let listGroup = $('<div class="list-group shadow"></div>');
        
        // Display top 6 results
        const maxResults = Math.min(results.length, 6);
        
        for (let i = 0; i < maxResults; i++) {
            const movie = results[i];
            const posterUrl = movie.poster_path 
                ? 'https://image.tmdb.org/t/p/w200' + movie.poster_path 
                : 'assets/img/no-poster.jpg';
            
            const item = $(`
                <a href="review.php?id=${movie.id}" class="list-group-item list-group-item-action">
                    <div class="d-flex align-items-center">
                        <img src="${posterUrl}" alt="${movie.title}" onerror="this.src='assets/img/no-poster.jpg'" 
                             style="width: 60px; height: 90px; border-radius: 4px; margin-right: 15px; object-fit: cover;">
                        <div>
                            <h6 class="mb-0">${movie.title}</h6>
                            <small>${movie.release_date ? movie.release_date.substring(0, 4) : 'Unknown year'}</small>
                        </div>
                    </div>
                </a>
            `);
            
            listGroup.append(item);
        }
        
        resultsContainer.append(listGroup);
        
        // Add a "View all results" link
        if (results.length > maxResults) {
            const viewAllLink = $(`
                <a href="search_results.php?query=${encodeURIComponent($('#movie-search').val())}" class="list-group-item list-group-item-action text-center">
                    <small><i class="fas fa-search me-1"></i>View all ${results.length} results</small>
                </a>
            `);
            listGroup.append(viewAllLink);
        }
    }
    
    // Hide search results when clicking elsewhere
    $(document).on('click', function(event) {
        if (!$(event.target).closest('#movie-search, #search-results').length) {
            $('#search-results').empty();
        }
    });
    
    // For movie search in the hero section (if present)
    $('#movie-search-hero').on('input', function() {
        const query = $(this).val().trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        // Clear results if query is empty
        if (query === '') {
            $('#search-results-hero').empty();
            return;
        }
        
        // Set a timeout to avoid sending too many requests while typing
        searchTimeout = setTimeout(function() {
            $.ajax({
                url: 'search.php',
                method: 'GET',
                data: { query: query },
                dataType: 'json',
                success: function(data) {
                    const results = data.results || [];
                    displaySearchResultsHero(results);
                },
                error: function(xhr, status, error) {
                    console.error('Error searching movies:', error);
                }
            });
        }, 300); // 300ms delay
    });
    
    // Function to display search results in hero section
    function displaySearchResultsHero(results) {
        const resultsContainer = $('#search-results-hero');
        
        if (!resultsContainer.length) return;
        
        resultsContainer.empty();
        
        if (results.length === 0) {
            resultsContainer.append('<p class="text-center p-3 rounded">No movies found</p>');
            return;
        }
        
        // Display top 5 results
        const maxResults = Math.min(results.length, 5);
        const listGroup = $('<div class="list-group shadow"></div>');
        
        for (let i = 0; i < maxResults; i++) {
            const movie = results[i];
            const posterUrl = movie.poster_path 
                ? 'https://image.tmdb.org/t/p/w200' + movie.poster_path 
                : 'assets/img/no-poster.jpg';
            
            const item = $(`
                <a href="review.php?id=${movie.id}" class="list-group-item list-group-item-action">
                    <div class="d-flex align-items-center">
                        <img src="${posterUrl}" alt="${movie.title}" onerror="this.src='assets/img/no-poster.jpg'" 
                             style="width: 50px; height: 75px; object-fit: cover; border-radius: 4px; margin-right: 15px;">
                        <div>
                            <h6 class="mb-0">${movie.title}</h6>
                            <small>${movie.release_date ? movie.release_date.substring(0, 4) : 'Unknown year'}</small>
                        </div>
                    </div>
                </a>
            `);
            
            listGroup.append(item);
        }
        
        resultsContainer.append(listGroup);
    }
    
    // Hide search results when clicking elsewhere
    $(document).on('click', function(event) {
        if (!$(event.target).closest('#movie-search-hero, #search-results-hero').length) {
            $('#search-results-hero').empty();
        }
    });
    
    // Preview profile picture before upload (if on edit_profile page)
    if ($('#profile_picture').length) {
        document.getElementById('profile_picture').addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                const reader = new FileReader();
                
                // Create preview element if it doesn't exist
                let previewElement = document.getElementById('profile-picture-preview');
                if (!previewElement) {
                    previewElement = document.createElement('img');
                    previewElement.id = 'profile-picture-preview';
                    previewElement.className = 'img-fluid rounded-circle mb-3';
                    previewElement.style.width = '150px';
                    previewElement.style.height = '150px';
                    previewElement.style.objectFit = 'cover';
                    
                    // Replace the current avatar or image
                    const currentAvatar = document.querySelector('.avatar') || document.querySelector('.card-body > img');
                    if (currentAvatar) {
                        currentAvatar.parentNode.replaceChild(previewElement, currentAvatar);
                    }
                }
                
                // Read and display the file
                reader.onload = function(event) {
                    previewElement.src = event.target.result;
                };
                
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Add smooth scrolling for all anchor links
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if (target.length) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 80
            }, 500);
        }
    });
    
    // Netflix-style hover effect for movie cards
    const applyMovieCardHover = () => {
        $('.movie-card-container').hover(
            function() {
                $(this).css('z-index', '10');
                $(this).find('.movie-card').css('transform', 'scale(1.1)');
            },
            function() {
                $(this).css('z-index', '1');
                $(this).find('.movie-card').css('transform', 'scale(1)');
            }
        );
    };
    
    applyMovieCardHover();
    
    // Apply nice animation to elements when they come into view
    const animateOnScroll = () => {
        const elements = document.querySelectorAll('.content-row');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('slide-in');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1
        });
        
        elements.forEach(element => {
            observer.observe(element);
        });
    };
    
    if ('IntersectionObserver' in window) {
        animateOnScroll();
    }
    
    // Star rating functionality for review form
    if ($('#rating').length) {
        // Create star rating visual element
        const createStarRating = () => {
            const ratingContainer = $('<div class="star-rating mb-3"></div>');
            const starsContainer = $('<div class="stars"></div>');
            
            // Add stars
            for (let i = 1; i <= 10; i++) {
                const star = $(`<i class="far fa-star" data-rating="${i}"></i>`);
                starsContainer.append(star);
            }
            
            // Add value display
            const valueDisplay = $('<span class="rating-value ms-2">0.0</span>');
            
            ratingContainer.append(starsContainer);
            ratingContainer.append(valueDisplay);
            
            // Insert after the original input
            ratingContainer.insertAfter($('#rating'));
            
            // Hide the original input
            $('#rating').hide();
            
            // Set up click events
            starsContainer.find('i').on('click', function() {
                const rating = $(this).data('rating');
                $('#rating').val(rating);
                updateStars(rating);
                valueDisplay.text(rating.toFixed(1));
            });
            
            starsContainer.find('i').on('mouseover', function() {
                const rating = $(this).data('rating');
                
                // Highlight stars
                starsContainer.find('i').each(function() {
                    const starRating = $(this).data('rating');
                    if (starRating <= rating) {
                        $(this).removeClass('far fa-star-half-alt').addClass('fas fa-star');
                    } else {
                        $(this).removeClass('fas fa-star').addClass('far fa-star');
                    }
                });
            });
            
            starsContainer.on('mouseout', function() {
                const rating = parseFloat($('#rating').val()) || 0;
                updateStars(rating);
            });
            
            // Function to update stars based on rating
            const updateStars = (rating) => {
                starsContainer.find('i').each(function() {
                    const starRating = $(this).data('rating');
                    if (starRating <= rating) {
                        $(this).removeClass('far fa-star-half-alt').addClass('fas fa-star');
                    } else {
                        $(this).removeClass('fas fa-star').addClass('far fa-star');
                    }
                });
            };
            
            // Initialize with current value
            const initialRating = parseFloat($('#rating').val()) || 0;
            updateStars(initialRating);
            valueDisplay.text(initialRating.toFixed(1));
        };
        
        createStarRating();
    }
    
    // Simple content tabs for profile pages
    if ($('.content-tabs').length) {
        $('.tab-link').on('click', function(e) {
            e.preventDefault();
            
            const tabId = $(this).data('tab');
            
            // Remove active class from all links and contents
            $('.tab-link').removeClass('active');
            $('.tab-content').removeClass('active').hide();
            
            // Add active class to current link and content
            $(this).addClass('active');
            $(`#${tabId}`).addClass('active').show();
        });
        
        // Activate first tab by default
        $('.tab-link:first').click();
    }
    
    // Handle theme toggle
    $('#theme-toggle').on('click', function() {
        $('body').toggleClass('light-theme');
        
        if ($('body').hasClass('light-theme')) {
            localStorage.setItem('theme', 'light');
            $(this).find('i').removeClass('fa-sun').addClass('fa-moon');
        } else {
            localStorage.setItem('theme', 'dark');
            $(this).find('i').removeClass('fa-moon').addClass('fa-sun');
        }
    });
    
    // Check if there's a saved theme preference
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'light') {
        $('body').addClass('light-theme');
        $('#theme-toggle i').removeClass('fa-sun').addClass('fa-moon');
    }
    
    // Card slider scroll controls
    $('.card-slider-control-prev').on('click', function() {
        const slider = $(this).closest('.content-row').find('.card-slider');
        slider.animate({
            scrollLeft: '-=600'
        }, 300);
    });
    
    $('.card-slider-control-next').on('click', function() {
        const slider = $(this).closest('.content-row').find('.card-slider');
        slider.animate({
            scrollLeft: '+=600'
        }, 300);
    });
    
    // Mobile-friendly improvements
    if (window.innerWidth <= 768) {
        // Make horizontal scrolling more touch-friendly
        $('.card-slider').each(function() {
            let isDown = false;
            let startX;
            let scrollLeft;
            
            $(this).on('mousedown touchstart', function(e) {
                isDown = true;
                $(this).addClass('active');
                startX = (e.type === 'mousedown') ? e.pageX : e.originalEvent.touches[0].pageX;
                scrollLeft = $(this).scrollLeft();
            });
            
            $(this).on('mouseleave touchend', function() {
                isDown = false;
                $(this).removeClass('active');
            });
            
            $(this).on('mouseup touchend', function() {
                isDown = false;
                $(this).removeClass('active');
            });
            
            $(this).on('mousemove touchmove', function(e) {
                if (!isDown) return;
                e.preventDefault();
                const x = (e.type === 'mousemove') ? e.pageX : e.originalEvent.touches[0].pageX;
                const dist = (x - startX) * 2;
                $(this).scrollLeft(scrollLeft - dist);
            });
        });
    }
});