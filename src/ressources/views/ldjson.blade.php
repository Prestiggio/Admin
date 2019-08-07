<?php 
$__vars = get_defined_vars();
$__ar = [];
foreach($__vars as $k => $v) {
    if(preg_match("/^__/", $k))
        continue;
    $__ar[$k] = $v;
}
?>
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
<title>{{ config('app.name', 'Laravel') }} : {{ $page["title"] }}</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
<meta name="viewport"
	content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32"
	href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16"
	href="/favicon-16x16.png">
<link rel="mask-icon" href="/favicon.ico" color="#5bbad5">
<meta name="msapplication-TileColor" content="#da532c">
<meta name="theme-color" content="#ffffff">
<script type="text/javascript" src="/ckeditor/ckeditor.js"></script>
@if(env('APP_ENV')!='local')
<link href="/style1.css" rel="stylesheet">
<link href="/style0.css" rel="stylesheet">
@endif
</head>
<body class="theme-cyan">
	<div id="app">
		<script type="application/ld+json">
            {!!json_encode($__ar)!!}
        </script>
	</div>
	@if(env('APP_ENV')=='local')
	<script type="text/javascript" src="/scripts/{{$theme}}.amelior.js"></script>
	@else
	@include("scripts.admin")
	<script type="text/javascript" src="/ryadmin.amelior.js"></script>
	@endif
</body>
</html>