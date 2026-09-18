<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'LearnPoint - Sistem Informasi Siswa')</title>
    
    <!-- Link CSS Custom -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

    <!-- Header / Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="{{ route('siswa.index') }}" class="navbar-brand">
                Learn<span>Point</span>
            </a>
            <ul class="navbar-nav">
                <li>
                    <a href="{{ route('siswa.index') }}" class="nav-link {{ request()->routeIs('siswa.*') ? 'active' : '' }}">
                        Data Siswa
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Content Container -->
    <main class="container">
        <!-- Render Alerts -->
        @include('layouts.alert')

        <!-- Render View Content -->
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        &copy; {{ date('Y') }} <strong>LearnPoint</strong>. Hak Cipta Dilindungi.
    </footer>

</body>
</html>
