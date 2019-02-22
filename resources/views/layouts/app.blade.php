<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    {{-- <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" type="text/css"> --}}
    <link href="https://fonts.googleapis.com/css?family=Cabin" rel="stylesheet">

    {{----}}
    {{--<link href="https://rsms.me/inter/inter-ui.css" rel="stylesheet">--}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.0/animate.min.css" rel="stylesheet">
    {{--<link rel="stylesheet"--}}
    {{--href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">--}}
    {{--<link rel="stylesheet" href="css/default.css" id="theme-color">--}}

    <script>
    
        window.app = {!! json_encode([
            'url' => url('')
        ]) !!}
    
    </script>
    
    <!-- Styles -->
    <link href="{{ asset('css/all.css') }}" rel="stylesheet">
    @stack('styles-bottom')
</head>
<body>
    <div id="app">

        {{--Nav--}}
        @section('nav')
            @include('nav')
        @show

        <main>
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://js.stripe.com/v3/"></script>
    @stack('scripts-top')
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    {{--<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>--}}
    {{--<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>--}}
    {{--<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>--}}
    {{--<script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>--}}
    {{--<script src="js/scripts.js"></script>--}}
</body>
</html>
