{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">
<link rel="stylesheet" type="text/css" href="/assets/css/searchtime.css?version={/$version/}">

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
      <div class="container" style='padding:0px'>
          <!--<textarea class='dataConfig'></textarea>-->
          <div class='showBox'>
          </div>
          <div class="panel panel-info">
            <div class="panel-heading panel_show">
              可视化配置
              <div class='queryBox'>
                <div class="btn-group btn-group-xs pull-right" >
                  <button type="button" class="btn btn-primary btn-xs" id='query'>查询</button>
                </div>
              </div>
            </div>
            <div class="panel-body panel_content" style='overflow:hidden'>
                <div class='col-lg-4' style='width:30%;float:left'> 
                  <h4 style='text-align:center'>数据维度</h4>
                  <div class='leftContent'></div>
                </div>
                <div class='col-lg-8'  style='width:70%;float:left'>
                  <div style='padding:5px;'>
                    <span class='pull-right'>&nbsp;全选当前维度下可选指标</span>
                    <input type='checkbox' class='pull-right checkAll' />
                  </div>
                  <div class='rightContent'></div>
                </div>
            </div>
          </div>
          <div class='coloumInfo' style='display:none;margin-top:10px'>
            <div class='colomlist pull-left'>
            </div>
            <button class='btn btn-primary btn-xs pull-right addChart'>增加图表</button>
            <button class='btn btn-primary btn-xs pull-right addColoum'>增加一列</button>
          </div>
          <div class='clearfix'></div>
          <div id='search'></div>
          <div id='chartTpl'></div>
          <div class='clearfix'></div>
      	  <div id='result' style='margin-top:10px'></div>
      </div>

    </div>
  </div>
</div>
{/include file="layouts/menujs.tpl"/}
{/include file="visualtpl/search.tpl"/}
<style type="text/css">
  .content:{ border:1px solid #eee}
  .dataConfig{ width:100%; height:105px; padding:10px; font-size:16px; margin-bottom:5px}
  .panel_show{ cursor: pointer; border-top: 1px solid #ccc; border-left: 1px solid #ccc;border-right: 1px solid #ccc; position: relative;}
  .queryBox{ position: absolute;top:4px; right: 10px;}

  input[type="radio"], input[type="checkbox"]{
     -webkit-box-shadow:1px 1px 3px #000;
  }
  #search{display: none;}
  .collnet{ padding-right: 10px; cursor: pointer; display: inline-block;}
  .expBtn{ margin-right: 10px;}
  .dimfilter{margin:0px;}
  #chartBox{ overflow: hidden;}
  .select2-container-multi .select2-choices .select2-search-field input{
    padding: 0px;
    height: 25px;
  }
  .chartlist{position: relative;}
  .chartlist .chartclose{
     display: none;
     position: absolute;
     top:0px;
     right: 0px;
     z-index: 10000;
     width: 20px;
     height: 20px;
     line-height: 20px;
     font-size: 20px;
     font-weight: normal;
     color: red;
     cursor: pointer;
  }
</style>
<script type="text/javascript">
  //图表全局函数
  var chartInfo =[];
  var allData ={};
  var config,dimensions; 
  var tableInfo ={};
  var targetMap =["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P",
    "Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AR","AS","AT","AU","AV","AW","AX","AY","AZ"];
  {/if $config neq ''/}
    config = {/$config/}
  {//if/}
  config = {/$config/};
  {/if $dimensions neq ''/}
    dimensions = {/$dimensions/};
  {//if/}
  dimensions = {/$dimensions/};
  if(dimensions.data ==''){
    $.messager.alert('提示',dimensions.msg,'info');
    setTimeout("history.back(-1)",2000);
  }else{
    var project = config.data.project[0].name;
  }

  Array.prototype.removeByValue = function(val) {
    for(var i=0; i<this.length; i++) {
        if(this[i] == val) {
          this.splice(i, 1);
          break;
        }
    }
  }
  //获取配置文件
  function getParams(){
    var visualParam ={};
    visualParam.project = project;
    visualParam.group = [];
    visualParam.metric = [];
    visualParam.filter=[];
    $('input.checkInfo').each(function(k,v){     
      if($(this).is(":checked")){
        var one ={};
        one.key = $(this).attr('dimensions');
        one.name = $.trim($(this).parent().prev().prev().prev().text());
        var filterMark = $(this).parent().prev().prev().find('select[name=filter]').val()
        var filterVal = $(this).parent().prev().text();
        if(filterMark !='filter_not' && filterVal !=''){
          var filterOne ={};
          filterOne.key = $(this).attr('dimensions');
          filterOne.op = filterMark;
          filterOne.val = filterVal.split("?");
          visualParam.filter.push(filterOne);
        }
        visualParam.group.push(one);
      }
    });    
    $(".list-group-item input:checked").each(function(){  
      if($(this).attr('disabled') == undefined){
          var one ={};
          one.key = $(this).parent().attr('name');
          one.name = $.trim($(this).attr('cn_name'));
          one.explain = $.trim($(this).attr('explain'));
          visualParam.metric.push(one);

          if($(this).parent().find('select[name=metric_filter]').val() !='filter_not'){
            var filterOne ={};
            filterOne.key = $(this).parent().attr('name');
            filterOne.op = $(this).parent().find('select[name=metric_filter]').val();
            filterOne.val = $(this).parent().find('input.metric_filter_value').val();
            if(filterOne.val ==''){
             
              status =0;
            }else{
              visualParam.filter.push(filterOne);
            }
          }
      }
    });   
    visualParam.edate = GetDateStr(-1);
     visualParam.date = GetDateStr(-1);
    return visualParam;
  }
  //攻取时间便移量
  function getOffset(num){
    var interval= arguments[1] ? arguments[1] :0;
    var str = "-"+(parseInt(num)+parseInt(interval));
    return GetDateStr( parseInt(str) );
  }
  //处理
  //获取时间
  function GetDateStr(AddDayCount){    
    var dd = new Date();    
    dd.setDate(dd.getDate()+AddDayCount);    
    var y = dd.getFullYear();    
    //var month = dd.getMonth()+1;
    //var d = dd.getDate();    
    var m = dd.getMonth() + 1 < 10 ? "0" + (dd.getMonth() + 1) : dd.getMonth() + 1;
    var d = dd.getDate() < 10 ? "0" + dd.getDate() : dd.getDate();

    return y+"-"+m+"-"+d;    
  }   
  function distinctArray(arr){
    var obj={},temp=[];
    for(var i=0;i<arr.length;i++){
      if(!obj[arr[i]]){
        temp.push(arr[i]);
        obj[arr[i]] =true;
      }
    }
    return temp;
  } 
  function memgry(dim){
      //合并数组
      var dimArr =[];
      for(var i=0; i<dim.length; i++){
          dimArr = $.merge(dimArr,dim[i]); 
      }
      dimArr = distinctArray(dimArr);//$.unique(dimArr);
      return dimArr;
  }
  function onLoadSuccess(){
    var view = $("#dashboard").data().datagrid.dc.view2;
    var bodyCell = view.find("div.datagrid-body td[field]");
    bodyCell.contrast();
  }
  //获取指标维度
  function getMte(arr){
    keyArr =[];
    for(var i=0; i<arr.length; i++){
      keyArr.push(arr[i].key);
    }
    return  keyArr.join(",",keyArr);
  }
  //获取udc
  function getUdc(){
    var udcInfo ={};
    var udcArr =[];
    var udcconf =[];
    $('.colomlist .expBtn').each(function(){   
      var  one = eval( "( "+ $(this).attr('data-option')+") ");
      udcArr.push(one.udc);
      udcconf.push( one ); 
    });
    if( udcArr.length>0 && udcconf.length >0 ){
       udcInfo.udc = udcArr.join(",");
       udcInfo.udcconf = encodeURIComponent(JSON.stringify(udcconf));
    }else{
       udcInfo.udc = '';
       udcInfo.udcconf = 0;
    } 
    return udcInfo;
  }
  //保存信息
  function saveAll(){
    var params = getParams();
    var tableMap = getExcelMap(params);
    var  udcInfo = getUdc(tableMap);
    params.group = getMte(params.group);
    params.metric = getMte(params.metric);
    var startTime = $('input[name=startTime]').val();
    var endTime = $('input[name=endTime]').val();

    if(startTime !=undefined && endTime !=undefined){
      params.date = startTime;
      params.edate = endTime;
    }else{
      params.date= GetDateStr(-1);
      params.edate= GetDateStr(-1);
    }  
    if(!params.group || !params.metric) {
      $.messager.alert('提示','请选择数据维度或者数据指标','info');
      return false;
    }
    
    params.udc  = udcInfo.udc;
    params.udcconf  = udcInfo.udcconf;  
    //处理时间问题
    allData ={};
    allData.table ={};
    allData.chart ={};
    allData.table = params;
    allData.chart = chartInfo;
    if(allData.chart !=undefined){
      for(var i=0; i<allData.chart.length; i++){
        chartInfo[i].edate = params.edate;
        chartInfo[i].date = params.date;
      }
    }
  }
  function srcExcel (param) {
     groupArr = param.group.split(",");
     metricArr =param.metric.split(",");
     var  obj ={
        group:[],
        metric:[]
     };
     for(var i=0; i < groupArr.length; i++){
      $('input.checkInfo').each(function(){   
        if(groupArr[i] == $(this).attr('dimensions') ){
          var one ={};
          one.key = $(this).attr('dimensions');
          one.name = $.trim($(this).parent().prev().prev().prev().text());
          one.mapStr ="";
          obj.group.push(one);
        }  
      });  
     }
     for(var i=0; i < metricArr.length; i++){
        $(".list-group-item input:checked").each(function(){  
          if(metricArr[i] == $(this).parent().attr('name')){
            var one ={};
            one.key = $(this).parent().attr('name');
            one.name = $.trim($(this).attr('cn_name'));
            one.explain = $.trim($(this).attr('explain'));
            one.mapStr ="";
            obj.metric.push(one);
          }
        });
     }   
     return obj;
  }
  //处理列数据
  function sendcoloum(){
    var coloum = allData.table;
    var obj = srcExcel(coloum);
    var tableMap = getExcelMap(obj);
    udcinfo = getUdc(tableMap);
    //var newUdc = replaceUdc(udcinfo.udc,tableMap);
    coloum.udc =udcinfo.udc;
    coloum.udcconf = udcinfo.udcconf;
    $.post('/visual/getColoum', {
         'coloum':coloum
        },function(data){
        var interText = doT.template($("#datagridtmpl").text());      
        var tableData  = changeHeader(tableMap,data.data);
         
        $('#result').html(interText(tableData));
        $('#dashboard').datagrid();
        $('.coloumInfo').show();
        $('.showinfo').tooltip({ 'position':'top'});
        $('#search').find('.down').attr("href",data.data.down);
        $('#coloumBox').dialog('close');
    },'json');
  }
  function getSelectHtml(data){
     var html ="";
     for(var i=0; i< data.length; i++){
       html +="<option value='"+data[i].key+"'>"+data[i].name+"</option>";
     }
     return html;
  }
  function setExcellist(data){
     var html ="";
     html +="<ul class='list-group'>";
     for(var i=0; i< data.length; i++){
       html +="<li class='list-group-item'>"+data[i].showName+ "</li>";
     }
     html +="</ul>";
     return html;
  }
  function getDimHtml(data){
     var html ="";    
     for(var i=0; i< data.length; i++){
       if(data[i].key !='date'){
         html +="<tr>";
         html +="<td style='width:20%'>"+data[i].name+"</td>";
         html +="<td style='width:35%'>";
         html +="<select name='filter' class='select'>";
         html +="<option value='filter_not' >----</option>";
         html +="<option value='=' selected =selected>=</option>";
         html +="<option value='like'>like</option>";
         html +="<option value='not like'>not like</option>";
         html +="<option value='start with'>not like</option>";
         html +="<option value='end with'>not like</option>";
         html +="<option value='in'>in</option>";
         html +="<option value='not in'>not in</option>";
         html +="<option value='=>'> => </option>";
         html +="<option value='<='> <= </option>";
         html +="<option value='<'> < </option>";
         html +="<option value='>'> > </option>";
         html +="<option value='!='> != </option>";
         html +=" </select>";
         html +="</td>";
         html +="<td contenteditable='true' style='-webkit-box-shadow:1px 1px 7px #d9edf7;width:35%' data-key='"+data[i].key+"'></td>";
         html +="</tr>";
       }
     }
     return html;
  }
  //获取map文件
  function getExcelMap(conf){
    var excelMap =[];
    for(var i=0; i<conf.group.length; i++){
      var one ={};
      one = conf.group[i];
      one.mapStr ="";
      excelMap.push(conf.group[i]);
    }
    for(var i=0; i<conf.metric.length; i++){
      var one ={};
      one = conf.metric[i];
      one.mapStr ="";
      excelMap.push(conf.metric[i]);
    }
    if(excelMap.length >0){
      for(var j=0; j<excelMap.length; j++){
        excelMap[j].showName =  excelMap[j].name + " ($"+ targetMap[j] +")";
        excelMap[j].mapStr = "$"+targetMap[j];
        excelMap[j].mapsub = targetMap[j];
      }
    }
    return excelMap;
  }
  //udc替换
  function  replaceUdc(src,data){
    //debugger;
    console.log(src);
    src = src.toLowerCase();
    for(var i =0; i<data.length; i++){
      var map = data[i].mapStr.toLowerCase();
      src  = src.split(map).join(data[i].key);
      //src = src.replace(reg,data[i].key);  
    }   
    return src;
  }
  //udc名称替换
  function  replaceUdcName(src,data){
    //debugger;
    src = src.toLowerCase();
    for(var i =0; i<data.length; i++){
      var map = data[i].mapStr.toLowerCase();
      src  = src.split(map).join(data[i].name);
      //src = src.replace(reg,data[i].key);  
    }   
    return src;
  }
  //表头名称替换
  function changeHeader(tableMap,data){
    if(data.header != undefined){
        for(var i =0; i< data.header.length; i++ ){
           for(var j=0; j< tableMap.length; j++){
              nameStr = tableMap[j].key.split(".");
              var keystr = nameStr.join("_")
              if( keystr == data.header[i].name){
                data.header[i].cn_name =   tableMap[j].showName;
              }
           }
        }
    } 
    if(data.fiexd != undefined){
        for(var i =0; i< data.fiexd.length; i++ ){
           for(var j=0; j< tableMap.length; j++){
              nameStr = tableMap[j].key.split(".");
              var keystr = nameStr.join("_")
              if( keystr == data.fiexd[i].name){
                data.fiexd[i].cn_name =   tableMap[j].showName;
              }
           }
        }
    }    
    return data;
  }

  $(function(){
    $('.collnet').hide();
    $("#excelMap").dialog({
        title: '对应关系',
        closed: true,
        cache: false,
        width: 300
    });
    $('#visualBox').dialog({
        title: '报表保存界面',
        width: 450,
        //height:'',
        closed: true,
        cache: false,
        modal: true,
        buttons: [{
          text:'确定',
          iconCls:'icon-ok',
          handler:function(){
              saveAll(); 
              if(tableInfo !=undefined){
                allData.table = tableInfo;
              }
              console.log(allData);
              if(!allData){
                return false;
              }
              var cn_name = $('#visualBox').find('input.cn_name').val();
              var explain = $("#visualBox").find('input.explain').val();
              var datetype = $("#visualBox").find('.datetype').is(":checked");
              var id = $("#visualBox").find('input.id').val();
              var url ='';
              if(id !=''){
                url = '/visual/EditorVisual';
              }else{
                url = '/visual/SaveVisual';
              } 
              if(!cn_name ||!explain) {
                $.messager.alert('提示','报表名称或报表说明请填写完整','info');
                return false;
              }
              $.get(url, {
                'cn_name':cn_name,
                'explain':explain,
                'datetype':datetype,
                'params':allData,
                'id':id
              },function(data){
                  if(data.status==0){
                     $.messager.alert('报表地址',
                      '<h4>报表地址:</h4><a target="_blank" href="'+data.data+'">跳转</a>');
                      $('#visualBox').dialog('close');   
                  }else{
                      $.messager.alert('提示',data.msg,'warning');

                  }
              }, 'json');            
          }
        },{
          text:'取消',
          handler:function(){
            $('#visualBox').dialog('close');
          }
        }]
    });
    //增加一列对话框
    $('#coloumBox').dialog({
        title: '列配置对话框',
        width: 450,
        //height:'',
        closed: true,
       // cache: false,
        modal: false,
        buttons: [{
          text:'确定',
          iconCls:'icon-ok',
          handler:function(){
              var expObj ={};
              expObj.cn_name=     $.trim($('#coloumBox').find('.cn_name').val()); 
              expObj.name=        $.trim($('#coloumBox').find('.name').val()); 
              expObj.explain=     $.trim($('#coloumBox').find('.explain').val()); 
              srcStr=  $.trim($('#coloumBox').find('.expression').val()); 

              if(!expObj.cn_name  ||!expObj.name || !expObj.explain || !srcStr ){
                $.messager.alert('提示','所有信息必填','info');
              }else{
              var tableMap = eval("("+ $.trim($('#coloumBox').find('.showpress').val())+")"); 
                
                expObj.expression = replaceUdc(srcStr,tableMap);
                expObj.udc = expObj.name+"="+expObj.expression;
                expObj.showpress = replaceUdcName(srcStr,tableMap);
                var str ="";
                str += expObj.name+"="+ expObj.showpress;
                var strHtml = "<span class='btn alert-info btn-xs expBtn' data-type='coloum' data-option='"+JSON.stringify(expObj)+"'>"+str+"</span>";
                var status =0;
                if( $('.colomlist').find('span').length>0){
                  $('.colomlist').find('span').each(function(){
                     if($(this).text() ==  str){
                      $.messager.alert('提示','该指标已经存在','info');
                      status =1;
                     }
                  });
                }
                if(!status){
                  $('.coloumInfo').find('.colomlist').append(strHtml);
                }else{
                  return false;
                }         
                sendcoloum();
              }
          }
        },{
          text:'取消',
          handler:function(){
            $('#coloumBox').dialog('close');
          }
        }]
    });
    //图表配置框
    $('#chartBox').dialog({
        title: '图表配置对话框',
        width: 450,
        closed: true,
       // cache: false,
        modal: true,
        buttons: [{
          text:'确定',
          iconCls:'icon-ok',
          handler:function(){
              var expObj ={};
              expObj.chartTitle=  $('#chartBox').find('input[name=chartTitle]').val(); 
              expObj.chartType =  $('#chartBox').find('select[name=chartType]').val(); 
              expObj.chartData =  $('#chartBox').find('select[name=chartData]').val(); 
              if(!expObj.chartType || !expObj.chartTitle){
                $.messager.alert("提示",'图表标题或类型必填','info');
                return;
              }
              var source = getParams();
              console.log(source);
              var udc = $.trim($("#chartBox").find('.udc').val());
              // var startTime = $('input[name=startTime]').val();
              // var endTime = $('input[name=endTime]').val();
              // source.date = startTime;
              // source.edate = endTime;
              if(!udc && expObj.chartData ==null){
                $.messager.alert('提示','请至少填一个指标!','info');
                return false;
              }
              if( expObj.chartData !=null ){
                //处理成表格数据
                for(var i=0; i< expObj.chartData.length; i++){
                   var mStr = expObj.chartData[i];
                   expObj.chartData[i] = mStr.split(".").join("_");
                }
              }else{
                  expObj.chartData  =[];
              }
              if(udc !=''){
                var mStrArr = udc.split(",");
                var udcArr =[];
                for(var j=0; j<mStrArr.length; j++){
                  if(mStrArr[j].indexOf("=") > 0 ){    
                    var udcObj = {};    
                    ms = mStrArr[j].split("=");
                    expObj.chartData.push(ms[0]);
                    udcObj.cn_name = ms[0];
                    udcObj.name = ms[0];
                    udcObj.explain = ms[0];
                    udcObj.expression = ms[1];
                    udcObj.udc = ms[0]+"="+ms[1];
                    //替换udc
                    var excelMap = getExcelMap(source);
                    udcObj.udc = replaceUdc(udcObj.udc,excelMap);
                    udcObj.expression = replaceUdc(udcObj.expression,excelMap);
                    udc = udcObj.udc;
                    udcArr.push(udcObj);
                  }else{
                    $.messager.alert("提示",'字符串格式不对 请按key=$A/$B 方式填写','info');
                    return;
                  }  
                } 
                
              }
              var dimStatu =0;
              if(expObj.chartType =='spline_time'){
                 var groupArr =[];
                 $("#chartBox").find('.dimfilter').find('tr').each(function(){
                      groupKey =  $(this).children().eq(2).attr('data-key');
                      gourpVal = $.trim($(this).children().eq(2).text());
                      gourpOp = $.trim($(this).children().eq(1).find('select[name=filter]').val());
                      if(gourpOp !='filter_not' && gourpVal !=''){
                        var filterOne ={};
                        filterOne.key = groupKey;
                        filterOne.op = gourpOp;
                        filterOne.val = gourpVal.split("?");
                        source.filter.push(filterOne);
                      }
                 });
              }else if( expObj.chartType =='pie'){
                if(expObj.chartData !=null){
                  if(expObj.chartData.length >1){
                     $.messager.alert('提示','饼图只能选择一个指标！','info');
                     return false;
                  } 
                }
              }    
              if(dimStatu){
                $.messager.alert('提示','趋势的条件必须填写完整!','info');
                return false;
              }    
              var udcconf =0;
              if( udc !=''){
                 source.udc = udc;
                 udcconf =  encodeURI(JSON.stringify(udcArr));
              }  
              if(!expObj.chartTitle  ||!expObj.chartType){
                $.messager.alert('提示','信息不完整!','info');
              }else{
                var str ="";
                str += expObj.chartTitle;
                var status =0
                if( $('.colomlist').find('span').length>0){
                  $('.colomlist').find('span').each(function(){
                     if($(this).text() ==  str){
                      $.messager.alert('提示','该指标已经存在!','info');
                      status =1;
                     }
                  });
                }
                if(!status){
                  source.group = getMte(source.group);
                  source.metric = getMte(source.metric);
                  source.chartconf = [];
                  source.chartconf.push(expObj);
                  source.udcconf= udcconf;
                  chartInfo.push(source);
                  var url = '/visual/GetChart';
                  $('body').mask('正在加载。。。');
                  $.get(url, {
                     'chartInfo':chartInfo
                    },function(data){
                     $('body').unmask();
                     if(data.status ==0){
                          var last = data.data[data.data.length -1];
                          var chartText = doT.template($("#charttpl").text());
                          $("#chartTpl").html(chartText(data.data));
                          if(data.data !=undefined){
                            for(var  i=0; i<data.data.length; i++){
                                var chart = new Highcharts.Chart(data.data[i]); 
                            }
                          }

                     }else{
                      $.messager.alert('提示',data.msg,'warning');
                     }
                     
                  },'json');
                }
                $('#chartBox').dialog('close');
              }
          }
        },{
          text:'取消',
          handler:function(){
            $('#chartBox').dialog('close');
          }
        }]
    });
    //鼠标移入移出事件
    $('body').on('mouseover','.chartlist',function(){
      $(this).children().eq(0).show(); 
    });
    $('body').on('mouseout','.chartlist',function(){
      $(this).children().eq(0).hide(); 
    });
    $('body').on('click','.chartlist .chartclose',function(){
      var index = $(this).parent().index();
      $(this).parent().remove();
      chartInfo.splice(index,1);
    });
    //处理维度
    var interText = doT.template($("#dimensionstmpl").text());
    $(".leftContent").html(interText(dimensions.data));
    $('select[name=filter]').select2();
    //处理指标
    var interText = doT.template($("#metricstmpl").text());
    $(".rightContent").html(interText(config.data.project[0]));   
    //点击维度处理
    $('.checkInfo').click(function(e){
      //处理维度
      var dim = $(this).attr('dim');
      if(dim){
        dim =  eval("("+ dim +")");
      }
      var dimArr = memgry(dim);
      var objThis = $(this);
      if($(this).is(':checked')){
        $("input.checkInfo").each(function(){
          if($(this).attr('disabled') == undefined){
            if($.inArray($(this).attr('dimensions'),dimArr) >= 0){
               $(this).removeAttr('disabled').css({
                '-webkit-box-shadow':'1px 1px 3px #000'
              });
            }else{
              if(objThis.attr('dimensions') != $(this).attr('dimensions')){
                $(this).attr('disabled','disabled').css({
                  '-webkit-box-shadow':"0px 0px 0px #eee"
                });
              }   
            }
          }  
        });
      }else{
        //还原当前与之不相关的组合
        $("input.checkInfo").each(function(){
            if($.inArray($(this).attr('dimensions'),dimArr) < 0){
              $(this).removeAttr('disabled').css({
                '-webkit-box-shadow':'1px 1px 3px #000'
              });
            }
        });
        var status =0;
        $("input.checkInfo").each(function(){         
            if($(this).attr('checked') == 'checked'){
              //获取当前arr
              var dim = $(this).attr('dim');
              if(dim){
                dim =  eval("("+ dim +")");
              }
              var dimArr = memgry(dim);
              $("input.checkInfo").each(function(){
                if($(this).attr('disabled') == undefined){
                  if($.inArray($(this).attr('dimensions'),dimArr) >= 0){
                     $(this).removeAttr('disabled').css({
                      '-webkit-box-shadow':'1px 1px 3px #000'
                    });
                  }else{
                    $(this).attr('disabled','disabled').css({
                      '-webkit-box-shadow':"0px 0px 0px #eee"
                    });
                  }
                } 
              });
              status =1;
            }
        });
        if(status <1){
          $("input.checkInfo").each(function(){ 
              $(this).removeAttr('disabled').css({
                '-webkit-box-shadow':'1px 1px 3px #000'
              });
          });
        }
      } 
      //获取被选中的维度
      var params = getParams();
      $.ajax({
         type: "get",
         url: "/visual/getMetric",
         data: {'project':project,'dimensions':getMte(params.group)},
         dataType: "json",
         success: function(data){
            if(data.status ==0){
              $('.list-group-item').each(function(){
                  if($.inArray($(this).attr('name'),data.data) >= 0){
                     $(this).find('.metric_explain').removeAttr('disabled').css({
                      '-webkit-box-shadow':'1px 1px 3px #000'
                    });;
                  }else{
                    $(this).find('.metric_explain').attr('disabled','disabled').css({
                      '-webkit-box-shadow':"0px 0px 0px #eee"
                    });
                  }
              });
              //produceQuery();
            }else{
              $.messager.alert('提示',data.msg,'warning');
            }      
         }
      });
    });
    //查询
    $('#query').click(function(){    
      saveAll(); 
      if(!allData){
        return false;
      }
      tableInfo = allData.table;
      $('.panel_content').hide();
      var url = '/visual/GetTable';
      $('body').mask('正在加载。。。');
      $.post(url, {
        'allData': allData
      },function(data){
        $('body').unmask();
        if(data.status == 0){
            var interText = doT.template($("#datagridtmpl").text());
            var obj = srcExcel(tableInfo);
            var tableMap = getExcelMap(obj);       
            var tableData  = changeHeader(tableMap,data.data.table);  
            $('#result').html(interText(tableData));
            if(data.data.chart !=undefined){
              var chartText = doT.template($("#charttpl").text());
              $("#chartTpl").html(chartText(data.data.chart));
              if(data.data.chart !=undefined){
                for(var  i=0; i<data.data.chart.length; i++){
                    var chart = new Highcharts.Chart(data.data.chart[i]); 
                }
              }
            }
            var downconfig = decodeURIComponent(data.data.table.downConfig);
            $('#dashboard').datagrid({
              queryParams:JSON.parse(downconfig),
            });        
            $('.coloumInfo').show();
            $('.showinfo').tooltip({ 'position':'top'});

            //加载search页面
            //var interText = doT.template($("#searchtmpl").text());
            var timereport = {}
            timereport.date_type =2;
            timereport.interval =0;
            timereport.offset = 1;
            timereport.shortcut = [];
            var searchConf = {};
            searchConf.timereport = {};
            searchConf.timereport = timereport;
            searchConf.basereport ={};
            $("#search").html(interText(searchConf));
            $('#search').show();
            $('#search').find('input[name=startTime]').val(allData.table.date) ;
            $('#search').find('input[name=endTime]').val(allData.table.edate) ;
            //下载
            var formtag = "<form method='post' action='/visual/downData' id='downData' >" +
                    "<input type='hidden' name='downConfig' value='"+data.data.table.downConfig+"'/>" +
                    "<input type='hidden' name='report_title' value='"+allData.table.project+"'/></form>";
            $('.downclick').append(formtag);

            $('.datepicker').datepicker({
                'format':"yyyy-mm-dd"
             });
            $('#search').find('.collclick').hide();
        }else{
            $.messager.alert('提示',data.msg,'warning');
        }
      }, 'json');
    });
    //下载
    $('body').on('click','.downclick',function(){
        $('#downData').submit();
    }); 
    $('.panel_show').click(function(event){
        if( $(event.target).attr('class') =='panel-heading panel_show' ){
          if($(this).next().is(":visible")){
            $(this).next().hide();
          }else{
            $(this).next().show();
          }
        }
    });   
    //全选维度
    $('.checkAll').click(function(){
      if($(this).is(":checked")){
        $(".list-group-item > .metric_explain").each(function(){
          if($(this).attr('disabled') == undefined){
            this.checked =true;
          }
        });
      }else{
        $(".list-group-item > .metric_explain").each(function(){
            this.checked =false;
        });
      }
      //produceQuery();
    });
    $('.dimensions').tooltip({position: 'top'});
    $('.checkInfo').tooltip({
          position: 'right',
          content: function(){  
            var dimensionsArr = dimensions.data;
            var dim = $(this).attr('dim');
            if(dim){
              dimArr = eval("(" +dim+ ")");
              var str ="<h4>当前维度可组合维度</h4>";
              str +="<ul class='list-group'>";
              for(var i=0; i<dimArr.length; i++){
                 var cn_nameArr = [];
                 for(var j=0; j< dimArr[i].length; j++){
                    for(var x =0; x< dimensionsArr.length; x++){
                       if(dimArr[i][j] == dimensionsArr[x].name){
                          if(dimensionsArr[x].cn_name !=''){
                            cn_nameArr.push(dimensionsArr[x].cn_name);
                          }else{
                            cn_nameArr.push(dimensionsArr[x].name);
                          }
                       }
                    }
                 }
                 str +="<li class='list-group-item'>"+ cn_nameArr.join(",")+"</li>";
              }
              str +="</ul>";
              return str;
            }else{
              return '无!';
            }
          }
    });
    $('.list-group-item').tooltip({
      position: 'top',
      content: function(){         
        var explain = $(this).find('.metric_explain').attr('explain');
        var pseudo_code = $(this).find('.metric_explain').attr('pseudo_code');
        if(explain || pseudo_code){
          var str ="<table class='table table-condensed table-bordered'>";
          str +="<tr>";
          str +="<td style='text-align:right'>explain</td>";
          str +="<td>"+explain+"</td>";
          str +='</tr>';
          str +="<tr>";
          str +="<td>pseudo_code</td>";
          str +="<td>"+pseudo_code+"</td>";
          str +='</tr>';
          str +="</table>";
          return str;
        }else{
          return '无!';
        }
      }
    });    
    $('#saveVisual').click(function(){

        var id = $('#visualBox').find('.id').val();
        if( id ==''){
          $('#visualBox').find('input.cn_name').val('');
          $("#visualBox").find('input.explain').val('');
        }  
        $('#visualBox').dialog('open');
    });
    //移除公式
    $('body').on('click','.expBtn',function(){
      var thiObj = $(this);
      $.messager.confirm('提示', '确定删除吗？', function(r){
        if(r){
          thiObj.remove();
          sendcoloum(); 
        }
      });
    });
    //增加一列
    $('.addColoum').click(function(){      
        $('#coloumBox').find('.cn_name').val(''); 
        $('#coloumBox').find('.explain').val(''); 
        $('#coloumBox').find('.expression').val(''); 
        $('#coloumBox').find('.name').val('');
        var obj = srcExcel(allData.table);
        var tableMap = getExcelMap(obj);
        $('#coloumBox').find('.showpress').val(JSON.stringify(tableMap));
        $('#coloumBox').dialog('open');
    });
    $("body").on('click','.mapclick',function(){
      $("#excelMap").dialog('open');
    });
    //增加图表
    $('.addChart').click(function(){
       // debugger;
        var param = getParams();
        if(param.length ==0){
           $.messager.alert('提示','请选择数据维度或者数据指标!');
           return false;
        }
        //给excelbox赋值
        var excelMap = getExcelMap(param);
        $("#excelMap").html(setExcellist(excelMap));
        $("#chartBox").find('.dimensionFilter').hide();
        $('#chartBox').find('.dimfilter').html(getDimHtml(param.group));
        $('#chartBox').find('select[name=chartData]').html(getSelectHtml(param.metric));
        //$('#chartBox').find('select[name=chartName]').html(getSelectHtml(param.group));
        $('#chartBox').find('input[name=chartTitle]').val(''); 
        $('#chartBox').find('.name').val(''); 
        $('#chartBox').find('.select').select2('val','');  
        $('#chartBox').find('.udc').val('');  
        $('#chartBox').dialog('open');   
    });
    //时间查询
    $('body').on('changeDate','.datepicker',function(ev){
       var startTime = $('input[name=startTime]').val();
       var endTime = $('input[name=endTime]').val();
       if($(this).attr('name')=='startTime'){
          if(startTime.valueOf() > endTime.valueOf()){
            $.messager.alert('提示','开始时间大于结束时间','warning');
            return false;
          }
       }else{
          if(startTime.valueOf() > endTime.valueOf()){
            $.messager.alert('提示','结束时间小于开始时间','warning');
            return false;
          }
       }
       $("#query").click();
    });
    //事件改变
    $(document).on('change','select[name=chartType]',function(){
        if($(this).val() =='spline_time'){
          $('#chartBox').find('.dimensionFilter').show();
          $('#chartBox').find('.compute').hide();
        }else{
           $('#chartBox').find('.dimensionFilter').hide();
           $('#chartBox').find('.compute').show();
        }
    });
  });
  
</script>
{/include file='visual/visualpublic.tpl'/}
{/include file='visualtpl/list.tpl'/}
{/include file='visualtpl/chart.tpl'/}