<style>
    /* Add this style block in your CSS file or within a <style> tag */
    .logo span {
        color: #E3A833; /* Set the color for the title */
    }
</style>

<header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
        <a href="{{ route('dashboard') }}" class="logo d-flex align-items-center">
            <img src="{{ asset('assets/img/logo2.png') }}" alt="logo">
            <span class="d-none d-lg-block">Class Monitoring</span>
        </a>
        <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->
  
    <nav class="header-nav ms-auto">
        <ul class="d-flex align-items-center">
            <!-- Profile Dropdown -->
            <li class="nav-item dropdown pe-3">
                <!-- Profile Icon and Name -->
                <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                    <img src="{{ asset('assets/img/profile-img.jpg') }}" alt="Profile" class="rounded-circle">
                    <span class="d-none d-md-block dropdown-toggle ps-2">{{ Auth::user()->name }}</span>
                </a>
                <!-- Profile Dropdown Items -->
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                    <!-- Profile Information -->
                    <li class="dropdown-header">
                        <h6>{{ Auth::user()->name }}</h6>
                        <span>{{ Auth::user()->email }}</span>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
    
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('password.change') }}">
                            <i class="bi bi-key"></i>
                            <span>Change Password</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                    <!-- Logout Button -->
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Logout</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul><!-- End Profile Dropdown Items -->
            </li><!-- End Profile Nav -->
        </ul>
    </nav><!-- End Icons Navigation -->
</header>
