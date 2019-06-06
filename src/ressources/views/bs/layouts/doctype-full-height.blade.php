<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <!-- script src="{{ asset('js/app.js') }}" defer></script -->
    <!-- Styles -->
    <!-- link href="{{ asset('css/app.css') }}" rel="stylesheet" -->
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    @if(env('APP_ENV')!='local')
    <link href="/style1.css" rel="stylesheet">
    <link href="/style0.css" rel="stylesheet">
    <link href="/ryadmin.css" rel="stylesheet">
    @endif
</head>
<body class="full-screen">
    <div id="app" class="my-auto">
        <main class="py-4">
            @yield('content')
        </main>
    </div>
    @if(env('APP_ENV')=='local')
    <script type="text/javascript" src="{{env('APP_URL')}}:3000/ryadmin.amelior.js"></script>
    @else
    <script type="text/javascript" src="/vendors~cardit~manager~ryadmin.amelior.js"></script>
	<script type="text/javascript" src="/cardit~manager~ryadmin.amelior.js"></script>
    <script type="text/javascript" src="/ryadmin.amelior.js"></script>
    @endif
</body>
</html>
