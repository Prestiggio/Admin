@extends("ryadmin::bs.layouts.panel")

@section("content")
<ul class="breadcrumbs-alt">
	<li><a href="/">@lang("accueil")</a></li>
	<li><a class="current">@lang("traductions")</a></li>
</ul>
<div class="row">
	<script type="application/json+ryTranslator">
        {!!json_encode($data)!!}
    </script>
</div>
@stop