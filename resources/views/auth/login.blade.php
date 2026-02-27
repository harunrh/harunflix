@extends('layouts.app')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="text-center mb-4">
            <img src="{{ asset('images/harunflix.png') }}" alt="HarunFlix" style="height: 50px;">
            <h2 class="mt-3">Sign In</h2>
            <p class="text-muted">Welcome back to HarunFlix</p>
        </div>

        <div class="card">
            <div class="card-body p-4">
                @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <form action="/login" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="{{ old('username') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-2">Login</button>
                </form>

                <p class="text-center mt-3 text-muted">
                    Don't have an account? <a href="/register" class="text-light">Register here</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection