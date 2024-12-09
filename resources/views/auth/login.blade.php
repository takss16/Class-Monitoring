<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" href="{{ asset('assets/img/logo2.png') }}">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
</head>
<body>
    <div class="container custom-container">
        <div class="left-panel">
            <div class="row">
                <div class="col-md-6"><img src="{{ asset('assets/img/logo2.png') }}" alt="College Logo"></div>
                <div class="col-md-6"><img src="assets/img/css.jpeg" alt="College Logo"></div>
            </div>
            
            
            <h1>ClaSS Monitoring</h1>
            <p>for the College of Computer Studies at Dominican College of Tarlac</p>
        </div>
        <div class="right-panel">
            <div class="card">
                <div class="card-header text-center">
                    <h2>USER LOGIN</h2>
                    <hr>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="form-group">
                            <label for="username">email</label>
                            <input type="text" class="form-control" id="username" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="form-group text-right">
                            <a href="{{ route('password.request') }}" class="btn btn-link">Forgot Password?</a>
                        </div>
                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary">Login</button>
                            {{-- <a href="{{ route('register') }}" class="btn btn-secondary">Sign up</a> --}}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="js/main.js"></script>
</body>
</html>
