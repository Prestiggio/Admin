<form action="/update_menus" name="frm_menus" method="post">
    <div class="modal-header">
    	<h5 class="modal-title">
    		<i class="fa fa-cog"></i> @lang("Autorisations et raccourcis")
    	</h5>
    	<button type="button" class="close" data-dismiss="modal"
    		aria-label="Close">
    		<span aria-hidden="true">&times;</span>
    	</button>
    </div>
    <?php /* ?>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
    	<a class="navbar-brand" href="#">Navbar</a>
    	<button class="navbar-toggler" type="button" data-toggle="collapse"
    		data-target="#navbarSupportedContent"
    		aria-controls="navbarSupportedContent" aria-expanded="false"
    		aria-label="Toggle navigation">
    		<span class="navbar-toggler-icon"></span>
    	</button>
    
    	<div class="collapse navbar-collapse" id="navbarSupportedContent">
    		<ul class="navbar-nav mr-auto">
    			<li class="nav-item active"><a class="nav-link" href="#">Home <span
    					class="sr-only">(current)</span></a></li>
    			<li class="nav-item"><a class="nav-link" href="#">Link</a></li>
    			<li class="nav-item dropdown"><a class="nav-link dropdown-toggle"
    				href="#" id="navbarDropdown" role="button" data-toggle="dropdown"
    				aria-haspopup="true" aria-expanded="false"> Dropdown </a>
    				<div class="dropdown-menu" aria-labelledby="navbarDropdown">
    					<a class="dropdown-item" href="#">Action</a> <a
    						class="dropdown-item" href="#">Another action</a>
    					<div class="dropdown-divider"></div>
    					<a class="dropdown-item" href="#">Something else here</a>
    				</div></li>
    			<li class="nav-item"><a class="nav-link disabled" href="#"
    				tabIndex="-1" aria-disabled="true">Disabled</a></li>
    		</ul>
    		<form class="form-inline my-2 my-lg-0">
    			<input class="form-control mr-sm-2" type="search"
    				placeholder="Search" aria-label="Search" />
    			<button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
    		</form>
    	</div>
    </nav>
    <?php */ ?>
    <div class="modal-body">
    	<div class="alert alert-light">
    		@lang("Ont accès à") <span class="text-primary"><i class="{{$page['icon']}}"></i>
    			{{$page['title']}}</span>:
    	</div>
    	<div>
    		<script type="application/json+ryRy.Admin.NavigationByRole">
                {!!json_encode($navigationByRole)!!}
            </script>
    	</div>
    </div>
    <div class="modal-footer">
    	<input type="hidden" name="site_id" value="{{$site_id}}"/>
    	<button type="button" class="btn btn-secondary" data-dismiss="modal">@lang("Fermer")</button>
    	<button type="submit" class="btn btn-primary">@lang("Enregistrer")</button>
    </div>
</form>