@extends("ryadmin::bs.layouts.doctype")

@section("body")
<body>
	<nav class="navbar navbar-expand-lg navbar-light bg-white shadow fixed-top">
        <a class="navbar-brand bg-primary pt-md-4 pl-sm-4 pr-sm-4 text-center text-light" href="#">{{ config('app.name', 'Laravel') }}
            <br/>
            <small class="text-muted">Super Administration</small>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        	<span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto"@d(menu:top)>
            	<script type="application/json+ryNavigation">{!!json_encode($menu)!!}</script>
            </ul>
            <ul class="navbar-nav"@d(menu:user)>
            	<script type="application/json+ryUserbar">{!!json_encode($user)!!}</script>
            </ul>
        </div>
    </nav>
    <div class="top-container d-flex">
    	<nav class="left-drawer col pl-0">
    		<div class="drawer">
    			<ul class="ry-nav"@d(menu:sidebar)>
    				<script type="application/json+ryDrawer">{!!json_encode($menu)!!}</script>
                </ul>
    		</div>
        </nav>
        <main class="py-4 flex-fill col pl-xl-0">
            @yield('content')
        </main>
    </div>
    <nav class="navbar fixed-bottom navbar-dark bg-dark">
    	<script type="application/json+ryAdminTools" id="adminfooter">{!!json_encode(['admin' => $admin, 'page' => $page])!!}</script>
    </nav>
    @if(env('APP_ENV')=='local')
    <script type="text/javascript" src="{{env('APP_URL')}}:3000/admin.amelior.js"></script>
    @else
    @include("scripts.admin")
    <script type="text/javascript" src="/ryadmin.amelior.js"></script>
    @endif
</body>
@stop