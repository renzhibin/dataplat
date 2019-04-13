{/include file="layouts/lib.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">
<style type="text/css">
    body{
       background: none;
    }
    .pagination-page-list{
        font-size: 12px;
    }
    .pagination-num{
        width: 3em!important;
    }
    .content.special{
        margin-left: 10px;
    }
</style>
<script>
    {/if  $dt_list  neq ''/}
        var dt_list = {/$dt_list/};
        var saler_list = {/$saler_list/};
        var zone_arr = {/$zone_arr/}
    {//if/}
</script>

<div id="content special" class="content special" >
    <div  class="rightreport" style="margin-left:-10px">
        {/include file="salesvisittpl/common.tpl"/}
    </div>
</div>