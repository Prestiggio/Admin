@extends("ryadmin::bs.layouts.panel")

@section("content")
<ul class="breadcrumbs-alt">
	<li><a href="/">@lang("Accueil")</a></li>
	<li><a href="/">@lang("Gestion générale")</a></li>
	<li><a class="current">@lang("Utilisateurs")</a></li>
</ul>
<div class="row">
	<script type="application/json+ryUsers">
        {!!json_encode($users)!!}
    </script>
</div>
@stop