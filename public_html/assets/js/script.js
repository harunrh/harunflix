$(document).ready(function() {
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
        }, 500); // 500ms delay
    });
    
    // Function to display search results
    function displaySearchResults(results) {
        const resultsContainer = $('#search-results');
        resultsContainer.empty();
        
        if (results.length === 0) {
            resultsContainer.append('<p class="text-center p-3 bg-light rounded">No movies found</p>');
            return;
        }
        
        // Create a list group for styling
        let listGroup = $('<div class="list-group shadow"></div>');
        
        // Display top 10 results
        const maxResults = Math.min(results.length, 10);
        
        for (let i = 0; i < maxResults; i++) {
            const movie = results[i];
            const posterUrl = movie.poster_path 
                ? 'https://image.tmdb.org/t/p/w200' + movie.poster_path 
                : 'assets/img/no-poster.jpg';
            
            const item = $(`
                <a href="review.php?id=${movie.id}" class="list-group-item list-group-item-action">
                    <div class="d-flex align-items-center">
                        <img src="${posterUrl}" alt="${movie.title}" onerror="this.src='assets/img/no-poster.jpg'" 
                             style="width: 60px; height: 90px; border-radius: 5px; margin-right: 15px; object-fit: cover;">
                        <div>
                            <h6 class="mb-0">${movie.title}</h6>
                            <small class="text-muted">${movie.release_date ? movie.release_date.substring(0, 4) : 'Unknown year'}</small>
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
        }, 500); // 500ms delay
    });
    
    // Function to display search results in hero section
    function displaySearchResultsHero(results) {
        const resultsContainer = $('#search-results-hero');
        
        if (!resultsContainer.length) return;
        
        resultsContainer.empty();
        
        if (results.length === 0) {
            resultsContainer.append('<p class="text-center p-3 bg-white shadow-sm rounded">No movies found</p>');
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
                <a href="review.php?id=${movie.id}" class="list-group-item list-group-item-action bg-white">
                    <div class="d-flex align-items-center">
                        <img src="${posterUrl}" alt="${movie.title}" onerror="this.src='assets/img/no-poster.jpg'" 
                             style="width: 50px; height: 75px; object-fit: cover; border-radius: 4px; margin-right: 15px;">
                        <div>
                            <h6 class="mb-0">${movie.title}</h6>
                            <small class="text-muted">${movie.release_date ? movie.release_date.substring(0, 4) : 'Unknown year'}</small>
                        </div>
                    </div>
                </a>
            `);
            
            listGroup.append(item);
        }
        
        resultsContainer.append(listGroup);
    }
    
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
});