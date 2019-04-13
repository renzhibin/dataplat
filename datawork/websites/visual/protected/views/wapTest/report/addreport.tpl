{/include file="layouts/header.tpl"/}
<script src="/assets/lib/ace-min/ace.js" type="text/javascript" charset="utf-8"></script>
<!-- load ace emmet extension -->
<script src="/assets/lib/ace-min/ext-emmet.js"></script>
<script src="/assets/js/sortable.min.js" type="text/javascript" charset="utf-8"></script>
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
    <div id="content" class="content">
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
<div class="container">



  <div class="container" style="margin-top:10px;padding:10px;text-align:right;background-color:#fdf2ef">
      <button class="btn btn-sm btn-danger saveConfig" disabled="disabled">保存配置</button>
      <button class="btn btn-sm btn-danger previewConfig" disabled="disabled">预&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;览</button>
  </div>

  <div class='configBox'>
      <button class='btn btn-primary' data-option='basereport'>基本信息设置</button>
      <div class='boxContent'></div>
  </div>
  <div class='configBox'>
      <button class='btn btn-primary' disabled="disabled" data-option='timereport'>时间条件设置</button>
      <div class='boxContent' id="timereport_box"></div>
  </div>
  <div class='configBox'>
      <button class='btn btn-primary' data-option='chartreport' disabled="disabled">图表区域设置</button>
      <div class='boxContent chartcontent'></div>
      <div class='clearfix'></div>
  </div>
  <div class='clearfix'></div>
<!--master 主表-->
    <div class="tablelist">
        <!--<div class='configBox'>
            <div class='tabletitle'></div>
            <div class='filter'></div>
            <div class='tableBtn'>
                <button class='btn btn-primary btn-xs editTable' data-option='reportgrade' disabled="disabled">主表区域设置</button>
                <button class='btn btn-primary btn-xs deleteTable' style="display:none;">删除</button>
            </div>
            <div class='boxContent tablecontent'></div>
        </div>-->

    </div>
    <div class="configBox" id='addtable'>
        <button class='btn btn-primary addTable' disabled="disabled">主表区域设置</button>
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
    var urlsearch = window.location.search;
    if(urlsearch.length > 0){
       var searchcon =  urlsearch.split('?')[1];
       var searcharr = searchcon.split('=');
        if(searchcon && searcharr[0] =='project'){
          var project = decodeURIComponent(searcharr[1]);
          $('#basereport').find('select[name=project]').select2('val',project);
        }
    }
  var toolbar = new ToolBar({"params":{},'boxtag':'#timereport_box'});
  //时间设置弹窗
  var toolbarset = new ToolBarSet({"params":{},'boxtag':'#timereport'});
     // toolbarset.setData(params);
      toolbarset.bindEvent(toolbar);
});

</script>
