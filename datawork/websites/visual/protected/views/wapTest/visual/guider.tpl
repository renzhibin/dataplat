{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">
<script src="/assets/js/project.js?version={/$version/}"></script>
<style>
    .mytable tr:hover { background:none; }

    .td{
        width:150px;
        font-size:15px;
        text-align: left;
    }
    .title{
        font-weight: bold;
        font-size: 15px;
        margin-top: 10px;
        clear: both;
    }
    .outdiv{
        margin-top: 50px;
    }
    .hr{
        margin-top: 0px;
    }
    .link{
        float:left; width:180px; height:50px;
        margin-top: 0px;
        margin-left: 80px;
        font-size: 14px;
        font-family: Arial;
    }
    .explain{
        margin-top: 0px;
        font-size:5px;
        color: #808080;
    }

</style>

<div>{/include file="layouts/menu.tpl"/}</div>

<div id='right'>
    <div id="content" class="content" >
        <!--面包屑效果-->
        <div id="breadcrumbs-one">
            {/foreach from = $guider item= place key=key/}
            {/if $guider[0] eq $place /}
            <span><a href="{/$place.href/}">{/$place.content/}</a></span>
            {/else/}
            {/if $place.href eq '#'/}
            <span>></span><span>{/$place.content/}</span>
            {/else/}
            <span>></span><span><a href="{/$place.href/}">{/$place.content/}</a></span>
            {//if/}
            {//if/}
            {//foreach/}
        </div>
        {/if $isauth eq 'true'/}
        <span style="float:right; margin-right: 100px;"><a href="/visual/toolguiderlist" class="btn btn-primary btn-xs" style="font-size: 14px;">编辑</a></span>
        {//if/}
        {/foreach from = $menu item= menu_i key=key/}
        <div class="title" style="margin-top: 10px">{/$menu_i.name/}</div>
        <hr class="hr"/>
        <div>
            {/foreach from = $list[$menu_i.id] item= tool key=t_key/}
            <div class="link">
                <span class="{/$tool.icon/}"></span>&nbsp;<a href="{/$tool.url/}" {/if $tool.new_window eq '1'/}  target="_blank"  {//if/}>{/$tool.name/}</a>
                <div class="explain">{/$tool.content/}</div>
                <hr class="hr">
            </div>
            {//foreach/}
        </div>
        {//foreach/}

    </div>



</div>

{/include file="layouts/menujs.tpl"/}

