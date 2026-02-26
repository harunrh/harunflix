@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="fas fa-users me-2"></i>All Members</h2>
    <span class="text-muted">{{ $users->total() }} members</span>
</div>

<div class="row g-3">
    @foreach($users as $user)
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('users.show', $user->username) }}" class="text-decoration-none">
            <div class="card h-100 user-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="avatar rounded-circle text-white d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width: 60px; height: 60px; font-size: 24px; background: linear-gradient(135deg, #e50914, #b81d24);">
                        {{ strtoupper(substr($user->username, 0, 1)) }}
                    </div>
                    <div>
                        <h6 class="mb-1">{{ $user->username }}</h6>
                        <small class="text-muted">
                            <i class="fas fa-star me-1 text-warning"></i>{{ $user->reviews_count }} reviews
                        </small>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>

<div class="mt-4 d-flex justify-content-center">
    {{ $users->links() }}
</div>
@endsection