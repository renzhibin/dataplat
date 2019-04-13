{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
{/include file="visualtpl/search.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">

{/include file='report/reportpublic.tpl'/}
{/include file='report/data.tpl'/}

{/include file='report/reportbase.tpl'/}
<!--加载拖拽插件-->
<div>
  {/include file="layouts/menu.tpl"/}
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
    <div style='height:10px'></div>
    <div class="container" style='padding:0px'>

      
      <div class="container" style="margin-top:10px;padding:10px;text-align:right;background-color:#fdf2ef">
          <button class="btn btn-sm btn-danger saveConfig" disabled="disabled">保存配置</button>
          <button class="btn btn-sm btn-danger previewConfig" disabled="disabled">预&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;览</button>
      </div>

      <div class='configBox'>
          <button class='btn btn-primary' data-option='basereport'>基本信息设置</button>
          <div class='boxContent'></div>
      </div>
      <div class='configBox'>
          <button class='btn btn-primary' disabled="disabled" data-option='timecontrast'>时间条件设置</button>
          <div class='boxContent' id="timereport_box"></div>
      </div>
      <div class='configBox'>
          <button class='btn btn-primary' data-option='chartreport' disabled="disabled">图表区域设置</button>
          <div class='boxContent chartcontent'></div>
          <div class='clearfix'></div>
      </div>
      <div class='clearfix'></div>
      <div id='filter'></div>
      <div class='configBox'>
          <!-- <button class='btn btn-primary' data-option='contrasreport'  disabled="disabled">表格区域设置</button> -->
          <button class='btn btn-primary' data-option='reportgrade' disabled="disabled">表格区域设置</button>
          <div class='boxContent tablecontent' id="tablebox"></div>
      </div>
    </div>

    </div>
  </div>
</div>
{/include file="layouts/menujs.tpl"/}
{/include file='visualtpl/list.tpl'/}
{/include file='visualtpl/chart.tpl'/}
<script type="text/javascript">
$(document).ready(function(){
  /* toolbar功能
      引用toolbar js
  */
  var toolbar = new ToolBar({'params':{},'boxtag':'#timereport_box'});
  //时间设置弹窗
  var toolbarset = new ToolBarSet({'params':{},'boxtag':'#timecontrast'});
      toolbarset.bindEvent(toolbar);
});

</script>

