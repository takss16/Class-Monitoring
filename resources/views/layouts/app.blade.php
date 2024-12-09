<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard')</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    @include('includes.style')
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    {{-- <meta name="csrf-token" content="{{ csrf_token() }}"> --}}

</head>
<body>

    <!-- ======= Header ======= -->
    @include('layouts.navbar')
    <!-- End Header -->
  
    <!-- ======= Sidebar ======= -->
    @include('layouts.sidebar')
    <main id="main" class="main">
  
      <section class="section">
        @yield('content')
      </section>
  
    </main><!-- End #main -->
  
    <!-- ======= Footer ======= -->
    <footer id="footer" class="footer">
      <div class="row">
        <div class="col-12 text-center">
          <img src="{{ asset('assets/img/dct.svg') }}" alt="logo" height="100" width="100">
          <img src="{{ asset('assets/img/ccs.png') }}" alt="logo" height="100" width="100">
        </div>
      </div>
      <div class="copyright">
        <strong>
          DOMINICAN COLLEGE OF TARLAC
        </strong>
      </div>
      <div class="credits">
        A.Y: 2024 - 2025
      </div>
    </footer><!-- End Footer -->

    @include('includes.script')

</body>
</html>
