{/include file="layouts/header.tpl"/}
<script src="/assets/lib/ace-min/ace.js" type="text/javascript" charset="utf-8"></script>
<!-- load ace emmet extension -->

<script src="/assets/js/tool/lodash.js"></script>
<script src="/assets/lib/ace-min/ext-emmet.js"></script>
<script src="/assets/js/sortable.min.js" type="text/javascript" charset="utf-8"></script>
{/include file="layouts/script.tpl"/}
{/include file="visualtpl/search.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">
{/include file='report/reportpublic.tpl'/}
{/include file='report/data.tpl'/}
{/include file='report/reportbase.tpl'/}
<script type="text/javascript">
    {/if $config neq '' /}
    var config = {/$config/};
    {//if/}
    {/if $dimensions neq ''/}
    var dimensions = {/$dimensions/};
    {//if/}

    var params = {/$params/};
    //设置项目
    var project = config.data.project[0].name;
    params.basereport['project'] = config.data.project[0].name;
    params.basereport['project_cn_name'] = config.data.project[0].cn_name;

    basereport = params.basereport;
    timereport = params.timereport;
    if(params.chart !=undefined){
      chart= params.chart;
    }else{
      params.chart =[];
    }

    table= params.table;
    //设置默认维度与指标
    params.sourceConfig = {};
    if(typeof(dimensions) != 'undefined' && typeof(config) != 'undefined'){
        params.sourceConfig.group = dimensions.data;
        params.sourceConfig.metric  = config.data.project[0];
    }

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
              <button class='btn btn-primary btnPosition btn-xs' data-option='timereport'>时间条件设置</button>
              <div class='boxContent' id="timereport_box"></div>
          </div>
          <div class='configBox'>
              <button class='btn btn-primary' data-option='chartreport'>图表区域设置</button>
              <div class='boxContent chartcontent'></div>
              <div class='clearfix'></div>
          </div>
          <div class='clearfix'></div>

            <ul class="tablelist selftablelist">

            </ul>
            <div class="configBox" id='addtable' style="display:none">
                <button class='btn btn-primary addTable'>添加副表</button>
            </div>
            <div class="configBox" id='addTableForMulti' style="border-top: 1px dashed #ccc; border-left: 1px dashed #ccc; background-color: #eee; display:none">
                <button class='btn btn-default btn-sm addTable'>添加表格</button>
            </div>
        </div>

    </div>
  </div>
</div>
{/include file="layouts/menujs.tpl"/}
<script type="text/javascript">
    +// 拖拽
        $("#drapQ").dragsort({
           dragSelector: "li",
            dragSelectorExclude: "button,input,textarea",
            dragEnd: function () {
            },
            scrollSpeed: 0
        })
        $(".tablelist").dragsort({
            dragSelector: "li",
            dragSelectorExclude: "button,input,textarea",
            dragEnd: function (arg) {
            },
            scrollSpeed: 0
        })
  function setmenu(){
    $("#basereport").find('select[name=second_menu]').select2('val',basereport.second_menu);
  }
  //处理基本信息
  $(function(){
    var $basreport = $('#basereport');
      $basreport.find('select[name=project]').select2('val',basereport.project);
      var projectid = $basreport.find('select[name=project]').find('option[value="'+basereport.project+'"]').attr('id');
      basereport['projectid'] = projectid;
      $basreport.find('select[name=project]').select2('disable',true);
      $basreport.find('input[name=cn_name]').val(basereport.cn_name);
      $basreport.find('tr.auth_hide').hide();
      $basreport.find('textarea[name=explain]').val(basereport.explain);
      $basreport.find('input[name=wiki]').val(basereport.wiki);

      $basreport.find('input[name=refresh_set]').attr("checked", basereport.refresh_set == 1 ? true : false);
      $basreport.find('.refresh_time_box').numberspinner('setValue',basereport.refresh_time ? basereport.refresh_time : 5);

    var tempcheck = (basereport.isexplainshow  && basereport.isexplainshow==1) ? true:false;
      $basreport.find('input[name=isexplainshow]').prop('checked',tempcheck);
    var html = "<p class='ptext'>项目名称："+basereport.project_cn_name+"</p><p class='ptext'>报表名称：" + basereport.cn_name+"</p>";
    if(basereport.explain !='' ){
       html += "<p class='ptext'>报表说明:" + basereport.explain+"</p>";
    }
    if(basereport.wiki != undefined  && basereport.wiki !='' ){
      html += "<p class='ptext'>wiki:" + basereport.wiki+"</p>";
    }
    var obj  = $('button[data-option=basereport]').next();
    obj.html("");
    obj.append(html);

   /* toolbar功能
      引用toolbar js
    */

  //还原图表
  if( typeof(params) !='undefined'){

     var toolbar = new ToolBar({"params":params,'boxtag':'#timereport_box'});
      //时间设置弹窗
      var toolbarset = new ToolBarSet({"params":params,'boxtag':'#timereport'});
      toolbarset.setData(params);
      toolbarset.bindEvent(toolbar);

      if(params.chart && params.chart.length >0){
         $('button[data-option=chartreport]').hide();
         getChartBox(params.chart,$('.chartcontent'));
         chartAjax(params.chart,$(".chartcontent"),1);
      }
     //将数据转换成新格式
      oldFromNew();
      if(params.tablelist){
          fakeCubeSort(params.tablelist);
      }

    //tables list
    if(params.tablelist && params.tablelist.length>0){
        var len = params.tablelist.length, tag = '', $tablelist = $('.tablelist');
        for(var i = 0; i < len; i++){
            //name = (i == 0) ? '编辑':"副表设置";
            tag +="<li class='configBox' data-index=" + i + "><div class='tabletitle'></div><div class='filter'></div>" +
                    "<div class='tableBtn'><button class='btn btn-primary btn-xs editTable' data-option='reportgrade'>编辑</button>" +
                    "<button class='btn btn-primary btn-xs deleteTable'>删除</button></div><div class='boxContent tablecontent'></div></li>";
        }
        $tablelist.html(tag);

        var tablesobj = {}, $boxtag = $('.tablelist').find('.configBox');
        window.tables = [];
        for(var i =0; i<len; i++){
            tablesobj = new Table({"table":params.tablelist[i],"boxtag":$boxtag.eq(i),"isEdit":"1"});
            tablesobj.bindEvent();
            window.tables.push(tablesobj);
        }

        if(params.tablelist.length >= 1) {
            $('#addTableForMulti').show();
        }

        if(params.tablelist.length == 1 && params.tablelist[0].isaddmeter =='1'){
            $('#addtable').show().find('button.addTable').text('添加副表');
        }

    } else {
        $('#addtable').show().find('button.addTable').text('主表区域设置');
    }
     $('button.saveConfig,button.previewConfig').removeAttr('disabled');

  }
// 编辑时项目禁用
$basreport.find('select[name=project]').select2('disable',true);

});
    
</script>
{/include file='visualtpl/list.tpl'/}
{/include file='visualtpl/chart.tpl'/}
