@if(env('APP_ENV')=='local')
<script type="text/javascript" src="{{env('APP_URL')}}:3000/rycentrale.amelior.js"></script>
@else
<script type="text/javascript" src="vendors~rycentrale.amelior.js"></script>
<script type="text/javascript" src="rycentrale.amelior.js"></script>
@endif