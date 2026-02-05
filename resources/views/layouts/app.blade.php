<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <title>{{ !empty($title) ? $title . ' - ' . config('app.name') : config('app.name')  }}</title>
    <meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport'/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"/>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Vite (Tailwind + app CSS/JS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- App layout (sidebar, navbar, footer, main content fit) – loaded separately so styles are not stripped -->
    <link rel="stylesheet" href="{{ asset('css/layout-app.css') }}">
    @yield('style')
</head>
<body>
<div class="wrapper fullheight-side">
    <aside class="app-sidebar">
        @include('layouts.app.logo_header')
        @include('layouts.app.sidebar')
    </aside>

    <div class="app-body">
        @include('layouts.app.navbar_header')

        <div class="main-panel main-panel-fit mt-0">
            <div class="main-content-wrap">
                @if(empty($isDashboard))
                    @include('layouts.app.main_content')
                @else
                    @include('layouts.app.main_content_for_dashboard')
                @endif
            </div>
            @include('layouts.app.footer')
        </div>
    </div>
</div>
<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery (for existing scripts) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
@include('layouts.assets.custom-notification')
@yield('js')
@stack('scripts')
</body>
</html>
