@extends("ryadmin::layouts.doctype")

@section("body")
<header>
	@section("nav")
	<nav>
		<ul>
			<li><a href="{{url("/admin")}}">Tableau de bord</a></li>
		</ul>
	</nav>
	@show
</header>
<main>
@if(session("message"))
<div class="md-warning" layout-fill>
	<h3 class="text-center">{!!session("message")!!}</h3>
	<?php Session::forget("message"); ?>
</div>
@endif
@yield("main")
</main>
@stop