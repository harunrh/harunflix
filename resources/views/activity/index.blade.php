@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="fas fa-history me-2"></i>Activity</h2>
    <span class="text-muted small d-none d-md-inline">Latest activity across HarunFlix</span>
</div>

@if($feed->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-history fa-3x text-muted mb-3"></i>
            <h5>No Activity Yet</h5>
            <p class="text-muted">Be the first to review a movie!</p>
            <a href="{{ route('movies.index') }}" class="btn btn-primary">
                <i class="fas fa-film me-1"></i>Browse Movies
            </a>
        </div>
    </div>
@else
    <div class="card">
        <div class="list-group list-group-flush">
            @foreach($feed as $item)
            <div class="list-group-item px-3 py-3">
                <div class="d-flex gap-2 align-items-start">

                    {{-- Avatar with action icon badge --}}
                    <div class="position-relative flex-shrink-0">
                        <div class="letter-avatar" style="width:40px;height:40px;font-size:16px;">
                            {{ strtoupper(substr($item['username'], 0, 1)) }}
                        </div>
                        <span class="position-absolute bottom-0 end-0 rounded-circle d-flex align-items-center justify-content-center"
                            style="width:18px;height:18px;font-size:9px;
                            background:{{ $item['type'] === 'review' ? '#0d6efd' : ($item['reaction'] === 'like' ? '#198754' : '#dc3545') }}">
                            <i class="fas {{ $item['type'] === 'review' ? 'fa-star' : ($item['reaction'] === 'like' ? 'fa-thumbs-up' : 'fa-thumbs-down') }} text-white"></i>
                        </span>
                    </div>

                    {{-- Main content --}}
                    <div class="flex-grow-1" style="min-width:0;">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div style="min-width:0;">
                                @if($item['type'] === 'review')
                                    <p class="mb-1 lh-sm" style="font-size:0.9rem;">
                                        <a href="{{ route('users.show', $item['username']) }}" class="fw-bold text-decoration-none">{{ $item['username'] }}</a>
                                        <span class="text-muted"> reviewed </span>
                                        <a href="{{ route('movie.show', $item['movie_id']) }}" class="fw-bold text-decoration-none">{{ $item['movie_title'] }}</a>
                                    </p>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge bg-primary" style="font-size:0.75rem;">{{ number_format($item['rating'], 1) }}/10</span>
                                        @if($item['review_text'])
                                        <span class="text-muted fst-italic" style="font-size:0.8rem;">"{{ Str::limit($item['review_text'], 80) }}"</span>
                                        @endif
                                    </div>
                                @elseif($item['type'] === 'reaction')
                                    <p class="mb-0 lh-sm" style="font-size:0.9rem;">
                                        <a href="{{ route('users.show', $item['username']) }}" class="fw-bold text-decoration-none">{{ $item['username'] }}</a>
                                        @if($item['reaction'] === 'like')
                                            <span class="text-success"> liked </span>
                                        @else
                                            <span class="text-danger"> disliked </span>
                                        @endif
                                        <span class="text-muted">{{ $item['reviewed_by'] }}'s review of </span>
                                        <a href="{{ route('movie.show', $item['movie_id']) }}" class="fw-bold text-decoration-none">{{ $item['movie_title'] }}</a>
                                    </p>
                                @endif
                            </div>

                            {{-- Movie poster --}}
                            @if($item['poster_path'])
                            <a href="{{ route('movie.show', $item['movie_id']) }}" class="flex-shrink-0">
                                <img src="https://image.tmdb.org/t/p/w92{{ $item['poster_path'] }}"
                                     alt="{{ $item['movie_title'] }}"
                                     style="width:32px;height:48px;object-fit:cover;border-radius:3px;">
                            </a>
                            @endif
                        </div>

                        <small class="text-muted" style="font-size:0.75rem;">{{ $item['time']->diffForHumans() }}</small>
                    </div>

                </div>
            </div>
            @endforeach
        </div>
    </div>
@endif

@endsection