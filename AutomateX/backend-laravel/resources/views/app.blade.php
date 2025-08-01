{{-- resources/views/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Page')</title> {{-- Allow page title override --}}

    {{-- For Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles') {{-- For page-specific CSS --}}

</head>
<body class="antialiased"> {{-- Add any global body classes here, e.g., for theming --}}
    <div id="app"> {{-- Vue.js or general app container --}}
        {{-- You might have a navbar here --}}
        {{-- @include('layouts.navigation') --}}

        <main>
            @yield('content')
        </main>

        {{-- You might have a footer here --}}
    </div>

    @stack('scripts') {{-- For page-specific JavaScript --}}
</body>
</html>