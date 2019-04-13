{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
{/include file="visualtpl/search.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">

{/include file='report/reportpublic.tpl'/}
{/include file='report/data.tpl'/}

{/include file='report/reportbase.tpl'/}
{/include file='visualtpl/list.tpl'/}
{/include file='visualtpl/chart.tpl'/}
<script type="text/javascript">
    var params = {/$params/};
    basereport = params.basereport;
    timereport = params.timereport;
    if(params.chart !=undefined){
      chart= params.chart;
    }else{
      params.chart =[];
    }
    table= params.table; 
    // if(params.table.grade !=undefined){
    //   grade= params.table.grade; 
    // }
</script>

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
            <button class='btn btn-primary btnPosition btn-xs' data-option='basereport'>基本信息设置</button>
            <div class='boxContent'></div>
        </div>
        <div class='configBox'>
            <button class='btn btn-primary btnPosition btn-xs' data-option='timecontrast'>时间条件设置</button>
            <div class='boxContent' id="timereport_box"></div>
        </div>
        <div class='configBox'>
            <button class='btn btn-primary' data-option='chartreport'>图表区域设置</button>
            <div class='boxContent chartcontent'></div>
            <div class='clearfix'></div>
        </div>
        <div class='clearfix'></div>
        <div id='filter'></div>
        <div class='configBox'>
            <!-- <button class='btn btn-primary' data-option='contrasreport'>表格区域设置</button> -->
            <button class='btn btn-primary' data-option='reportgrade'>表格区域设置</button>
            <div class='boxContent tablecontent' id="tablebox"></div>
        </div>
      </div>

    </div>
  </div>
</div>
{/include file="layouts/menujs.tpl"/}    

<script type="text/javascript">
  function setmenu(){
    $("#basereport").find('select[name=second_menu]').select2('val',basereport.second_menu);
  }
  //处理基本信息
  $(function(){
    $("#basereport").find('input[name=cn_name]').val(basereport.cn_name);
    $('#basereport').find('tr.auth_hide').hide();
    $("#basereport").find('textarea[name=explain]').val(basereport.explain);
    $("#basereport").find('input[name=wiki]').val(basereport.wiki);
    var tempcheck = (basereport.isexplainshow  && basereport.isexplainshow==1) ? true:false;
    $("#basereport").find('input[name=isexplainshow]').prop('checked',tempcheck);
    var html = "<p class='ptext'>报表名称:" + basereport.cn_name;
    if(basereport.explain !='' ){
       html += "<p class='ptext'>报表说明:" + basereport.explain+"</p>";
    }
    if(basereport.wiki != undefined  && basereport.wiki !='' ){
      html += "<p class='ptext'>wiki:" + basereport.wiki+"</p>";
    }
    var obj  = $('button[data-option=basereport]').next();
    obj.html("");
    //setTimeout("setmenu()",1000);
    obj.append(html);

   /* toolbar功能
      引用toolbar js
    */

  var toolbar = new ToolBar({"params":params,'boxtag':'#timereport_box'});
  //时间设置弹窗
  var toolbarset = new ToolBarSet({"params":params,'boxtag':'#timecontrast'});
      toolbarset.setData(params);
      toolbarset.bindEvent(toolbar);
  //还原图表
  if( typeof(params) !='undefined'){
      if(params.chart && params.chart.length >0){
          $('button[data-option=chartreport]').hide();
          getChartBox(params.chart,$('.chartcontent'));
          chartAjax(params.chart,$(".chartcontent"),1);
      }
      oldFromNew();
      fakeCubeSort();
      var tables = new Table({"params":params,"boxtag":"#tablebox","searchtag":"#filter","isEdit":"1"});
      tables.bindEvent();
      window.tables = tables;
      $('button.saveConfig,button.previewConfig').removeAttr('disabled');
      $('button[data-option=contrasreport],button[data-option=reportgrade]').addClass('btn-xs btnPosition');
  }

 /* //获取表格
  if(params.type ==2){
      tableContrast(params.table,$(".tablecontent"),1);
  }else{
      tableAjax(params.table,$('.tablecontent'),1);
  }
  if(params.table.hasOwnProperty('project') && params.table.grade !=undefined &&params.table.grade.hasOwnProperty('search')  ){
    addSearch(params.table.grade,params.table);

  }*/

  });  
  
  /*var  endTime =  getOffset(timereport.offset);
  table.date =  endTime;
  table.edate =  endTime;
  if(chart !=undefined && chart.length >0){
    for(var i=0; i< chart.length; i++){
      chart[i].date = endTime;
      chart[i].edate =endTime;
    }
    $('button[data-option=chartreport]').hide();
    getChartBox(chart,$('.chartcontent'));
    chartAjax(chart,$(".chartcontent"),1);
  }
  //还原表格
  if(table.grade.search !=undefined){
    tempsearch =  getDefaultSearch(table.grade.search);
    table.search = tempsearch;
  }
  tableContrast(table,$(".tablecontent"),1);
  if(table.hasOwnProperty('project') && grade !=undefined && grade.hasOwnProperty('search')  ){
    addSearch(grade,table);
  }*/



</script>
