<!DOCTYPE html>
<html ng-app="adminAppPublic" ng-strict-di>
<head>
<meta charset="utf-8" />
<title>Administration</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link type="text/css" href="{{url("vendor/ryadmin/css/style.min.css")}}" rel="stylesheet">
<link type="text/css" href="{{url("vendor/ryadmin/css/style.css")}}" rel="stylesheet">
<script type="text/javascript" src="{{url("vendor/ryadmin/js/script.min.js")}}"></script>
<script type="text/javascript" src="{{url("vendor/ryadmin/js/angular-material.js")}}"></script>
<script type="text/javascript" src="{{url("vendor/ryadmin/js/script.js")}}"></script>
<script type="application/ld+json" id="conf">
{!!$js!!}
</script>
<script type="text/javascript">
(function(window, angular, $, gameApp, undefined){

	angular.module("adminAppPublic", ["ngApp"]);
	
})(window, window.angular, window.jQuery, window.gameApp);
</script>
@yield("script")
</head>
<body>
@yield("body")
</body>
</html>