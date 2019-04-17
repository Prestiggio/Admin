@extends("ryadmin::bs.layouts.panel")

@section("content")
<ul class="breadcrumbs-alt">
	<li><a href="/">@lang("accueil")</a></li>
	<li><a href="/">@lang("gestion_generale")</a></li>
	<li><a class="current">{{$page['title']}}</a></li>
</ul>
<div class="row">
	<script type="application/json+ryAdmin.User">
        {!!json_encode($users)!!}
    </script>
</div>
@stop