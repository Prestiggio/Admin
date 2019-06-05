<?php 
$__vars = get_defined_vars();
$__ar = [];
foreach($__vars as $k => $v) {
    if(preg_match("/^__/", $k))
        continue;
    $__ar[$k] = $v;
}
?>
<script type="application/json+ry{{$view}}">
    {!!json_encode($__ar)!!}
</script>