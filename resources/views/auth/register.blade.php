@extends('layouts.app')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="text-center mb-4">
            <img src="{{ asset('images/harunflix.png') }}" alt="HarunFlix" style="height: 50px;">
            <h2 class="mt-3">Create Account</h2>
            <p class="text-muted">Join our movie review community</p>
        </div>

        <div class="card">
            <div class="card-body p-4">
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="/register" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="{{ old('username') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                        <small class="text-muted">Password must be at least 6 characters long.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-2">Register</button>
                </form>

                <p class="text-center mt-3 text-muted">
                    Already have an account? <a href="/login" class="text-light">Login here</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection