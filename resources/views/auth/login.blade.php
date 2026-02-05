<!DOCTYPE html>
<html>
<head>
    <title>Login - HarunFlix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card bg-secondary">
                    <div class="card-body">
                        <h2 class="text-center mb-4">Sign In to HarunFlix</h2>
                        
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                {{ $errors->first() }}
                            </div>
                        @endif
                        
                        <form action="/login" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-danger w-100">Login</button>
                        </form>
                        
                        <p class="text-center mt-3">
                            Don't have an account? <a href="/register">Register here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>