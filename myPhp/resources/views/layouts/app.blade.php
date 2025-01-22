<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Default Title')</title>
    
    @stack('styles')
</head>
<body>
    <nav>
        @include('partials.navigation')
    </nav>
    
    <main>
        @yield('content')
    </main>
    
    <footer>
        @include('partials.footer')
    </footer>
    
    @stack('scripts')
</body>
</html> 