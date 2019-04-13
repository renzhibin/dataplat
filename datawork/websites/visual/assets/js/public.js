function isEmptyObject(o){
    for(var n in o){
        return false;
    }
    return true;
}

function toThousands(num) {
    num += '';
    if (isNaN(num))
        return num;
    var decimal = num.length;
    if (num.indexOf('.') >= 0)
        decimal = num.indexOf('.');
    var result = [],
        counter = 0;
    num = (num || 0).toString().split('');
    for (var i = num.length; i >= decimal; i--) {
        result.unshift(num[i]);
    }
    for (var i = decimal - 1; i >= 0; i--) {
        counter++;
        result.unshift(num[i]);
        if (!(counter % 3) && i != 0) {
            result.unshift(',');
        }
    }

    return result.join('');
}

//日期函数
function changeTimeFormat(time) {
       var ds=time;
       ds = ds.replace(/-/g, '/');
       var date = new Date(ds);
       var month = date.getMonth() + 1 < 10 ? "0" + (date.getMonth() + 1) : date.getMonth() + 1;
       var currentDate = date.getDate() < 10 ? "0" + date.getDate() : date.getDate();
       var hh = date.getHours() < 10 ? "0" + date.getHours() : date.getHours();
       var mm = date.getMinutes() < 10 ? "0" + date.getMinutes() : date.getMinutes();

       //time 为日期时 date.getHours为8的问题
       var temparr = time.split(' ');
       var week;
      if(date.getDay()==0)          week="周日"
      if(date.getDay()==1)          week="周一"
      if(date.getDay()==2)          week="周二"
      if(date.getDay()==3)          week="周三"
      if(date.getDay()==4)          week="周四"
      if(date.getDay()==5)          week="周五"
       if(date.getDay()==6)          week="周六"
      if((temparr.length == 1 || (temparr[1] && temparr[1]==' ')) && hh =='08' && mm =="00"){
        return date.getFullYear() + "-" + month + "-" + currentDate +" "+week;
      }else{
        return date.getFullYear() + "-" + month + "-" + currentDate +" "+hh+":"+mm +" "+week;
      }

}
//格式化时间转化成时间戳
function transdate(endTime){
  var date=new Date();
  date.setFullYear(endTime.substring(0,4));
  date.setMonth(endTime.substring(5,7)-1);
  date.setDate(endTime.substring(8,10));
  date.setHours(endTime.substring(11,13));
  date.setMinutes(endTime.substring(14,16));
  date.setSeconds(endTime.substring(17,19));
  return Date.parse(date);
}
//生成图表
function getDefaultSearch(searchConfig,type){
  var tempsearch =[];
    for(var p in searchConfig){

        if(searchConfig[p].reportdimensions){
          continue;
        }else{

          if(searchConfig[p].reportsource && searchConfig[p].reportsource !=''){
            //首个搜索过滤数值
            var keyval = searchConfig[p].reportsource.split('\n')[0];
            //判断默认值 是否在
            if(searchConfig[p].defaultsearch && searchConfig[p].defaultsearch !=''){
                var keyarr = searchConfig[p].reportsource.split('\n');
                for(var i = 0, len = keyarr.length; i<len; i++){
                  var tempkey = keyarr[i].split(":")[0],
                      tempval = keyarr[i].split(":")[1];
                      if(tempval == searchConfig[p].defaultsearch || tempkey==searchConfig[p].defaultsearch){
                          var temp = {'key':'','val':[],'op':'='};
                          temp.val.push(tempkey);
                          temp.key = searchConfig[p].reportkey;
                          temp.defaultsearch = searchConfig[p].defaultsearch;
                          temp.op = (searchConfig[p].is_accurate==0) ? 'like' : "=";
                          tempsearch.push(temp);
                      }
                }

            } else {
              if(type ==2){
                var temp = {'key':'','val':[],'op':'='};
                temp.key = searchConfig[p].reportkey;
                temp.val.push(keyval.split(':')[0]);
                temp.op='=';
                temp.defaultsearch = searchConfig[p].defaultsearch;
                tempsearch.push(temp);
              }

            }

          } else {
            if(searchConfig[p].defaultsearch && searchConfig[p].defaultsearch !=''){
              var temp = {'key':'','val':[],'op':'='};
              temp.key = searchConfig[p].reportkey;
              temp.val.push(searchConfig[p].defaultsearch);
              temp.defaultsearch = searchConfig[p].defaultsearch;
              temp.op = (searchConfig[p].is_accurate==0) ? 'like' : "=";
              tempsearch.push(temp);
            }
          }
        }
    }
    return tempsearch;
}

//普通的获取所有的默认值
function getNewdefaultSearch(){
    var newtempsearch = [];
    for(var m=0, mlen =searchConfig.length; m<mlen; m++){
      var rkey = searchConfig[m].reportkey,
        rsearch = searchConfig[m].defaultsearch;
      if(rsearch && rsearch !=''){
        for(var n=0,nlen = tempsearch.length; n<nlen; n++){
          if(rkey == tempsearch[n].key){
            rsearch = tempsearch[n].val;
            break;
          }
        }

        newtempsearch.push({'key':key,"val":rsearch,"op":op,"defaultsearch":rsearch});
      }

    }

}


//创建图表
function createChart(data){
    if( data.legend.selected  !=undefined){
      // 获取排序后的legend顺序,使得多表同效(即前一时间取消显示后一时间同步生效)
      var title = data.title.text;
      if(title.indexOf("(") !== -1){
           title = title.slice(0,title.indexOf("("));
      }
      key = title  + "_" + data.legend.data.sort().join("_");
      var selectArr = data.legend.selected;
      for(var x in data.legend.selected){
        if( window.localStorage[key+'chart_legend'+ x]){
            data.legend.selected[x] = false;
        }
      }
    }
    switch(data.chart.type ){
      case 'funnel':
        data.tooltip.formatter = function(params){
            var title =  params[0] +"<br/>";
            return   params[1] +"："+ toThousands(params.data.funnel) + "（"+params[2]+ "%）<br>相对上个环节："+params.data.layter+"%";
        }
        //var myChart = echarts.init(document.getElementById(data.chart.renderTo),'shine');

       // myChart.setOption(data);
          break;
      case 'map':
           //var myChart = echarts.init(document.getElementById(data.chart.renderTo),'shine');
           // myChart.setOption(data);
           break;
      case  'pie':
            //var myChart = echarts.init(document.getElementById(data.chart.renderTo),'shine');
            data.tooltip.formatter = function(params){
              return  params[0] +"<br/>"+ params.data.detialname +"："+ toThousands(params[2]) + "（"+params[3]+"%）";
            }
           //myChart.setOption(data);
        break;
      default:
        //var myChart = echarts.init(document.getElementById(data.chart.renderTo),'shine');
        data.yAxis.axisLabel.formatter =function(value){
          var w =    parseInt(value)/10000;
          var y  = parseInt(value)/100000000;
          if( Math.abs(y) <1  && Math.abs(w)>=1){
            return w +"万";
          }else if( Math.abs(y) >=1 ){
            return y +"亿";
          }else{
            return value;
          }
        }

        data.tooltip.formatter = function(params){
            //debugger;
            var  arr =[];
            var str =  transdate( params[0].name);

            if(isNaN(str) || params[0].name.length>16 ){
               var title =  params[0].name +"<br/>";
            }else{
               var title =  changeTimeFormat(params[0].name) +"<br/>";
            }

            for(var i=0; i<params.length; i++){
                 if( typeof  params[i].data =='object'){
                   arr.push({name: params[i][0], y:params[i].data.value,event: params[i].data.event } );
                 }else{
                     // 解决tooltip中 name为undefine 修改 name:params[i][0] 为 name:params[i].seriesName
                   arr.push({name: params[i].seriesName, y:params[i].data } );
                 }

            }
            arr.sort(function(a,b){return b.y -a.y});
            var strArr =[];
            for(var j =0; j<  arr.length; j++){
                if(arr[j].y != undefined){
                  if( arr[j].event  !=undefined ){
                     var eventArr  =[];
                     for(var  p=0,q=arr[j].event.length; p<q; p++){
                         var t = p +1;
                        eventArr.push(  t +". "+ arr[j].event[p].title);
                     }
                  }
                  strArr.push( arr[j].name +"："+  toThousands(arr[j].y) );
                }else{
                  strArr.push( arr[j].name +"：0 " );
                }
            }
            if(eventArr !=undefined && eventArr.length >0 ){
                return  title +  strArr.join("<br/>") + "<br><div style='height:8px;'></div><div style='color:#27c9cb'>事件:<br>" + eventArr.join("<br>");
            }else{
                return  title +   strArr.join("<br/>");
            }
        }

          //myChart.setOption(datas);
          data.grid.x=80;
          break;
    }

    var myChart = echarts.init(document.getElementById(data.chart.renderTo),'shine');
    myChart.setOption(data);
    //tab图表，表格的筛选
    if ($('.muneIcon').css('display')!='none'){
        if($('#chartTpl').children()){
            $('.phone-tab').removeClass('hide');
            //调整面包屑位置
            $('.web-breadcrumbs').css("top","-30px");
        }
    }

    //处理图例
    /*
    if(data.chart.type !='pie'){
      var divCharts = $('#'+data.chart.renderTo);
      if(myChart.chart[data.series[0].type] ==undefined){
        return;
      }
      var legend = myChart.chart[data.series[0].type].component.legend;
      var lendBox = $('<div class="legend_layter"><b class="left"></b><b class="right"></b></div>').appendTo(divCharts);
      var divLegends = $('<div class="legend_box"></div>').appendTo(lendBox);
      $(data.legend.data).each(function(i,l){
        var color = legend.getColor(l);
        var status =  data.legend.selected[l];
        var labelLegend = $('<label class="legend">' +
                '<span class="label" style="background-color:'+color+'"></span>'+l+'</label>');
        if(!status){
            labelLegend.addClass('disabled');
        }
        labelLegend.mouseover(function(){
            labelLegend.css('color', color).css('font-weight', 'bold');
        }).mouseout(function(){
            labelLegend.css('color', '#333').css('font-weight', 'normal');
        }).click(function(){
            labelLegend.toggleClass('disabled');
            legend.setSelected(l,!labelLegend.hasClass('disabled'));
        });
        divLegends.append(labelLegend);
      });
      //隐藏左右铵钮
      var parentObj = divCharts.find('.legend_layter');
      var parentWidth = parentObj.width();
      var width = parentObj.find('.legend_box').width();
      if(width < parentWidth){
         parentObj.find('b').hide();
         parentObj.find('.legend_box').css({'position':'static'});
      }
      //绑定事件
      $('body').on('click','.legend_layter b.left',function(){
          var obj =  $(this).closest('.legend_layter').find('.legend_box');
          var left = obj.position().left;
          realleft = parseInt(left) + 60;
          if( left  < 0 ){
            obj.css({'left': realleft +"px"});
            left  = parseInt(left) +  60;
          }
      });
      $('body').on('click','.legend_layter b.right',function(){
          var parent  = $(this).closest('.legend_layter');
          var obj =  parent.find('.legend_box');
          var width = obj.width();
          var parentWidth = parent.width();
          var left = obj.position().left;
          realleft = parseInt(left) - 60;
          if(  Math.abs( width - parentWidth )  >  Math.abs(left) ){
            obj.css({ 'left': realleft +"px" });
            left  = parseInt(left) - 60;
          }
      });
    }
  */
    //加载兼听事件
    myChart.on('legendSelected', function(param){
       var title = data.title.text;
       if(title.indexOf("(") !== -1){
            title = title.slice(0,title.indexOf("("));
       }
       // 获取排序后的legend顺序,使得多表同效(即前一时间取消显示后一时间同步生效)
       key = title + "_" + data.legend.data.sort().join("_");
       if(!param.selected[param.target]){
          if( !window.localStorage[key+'chart_legend'+param.target]){
              window.localStorage[key+'chart_legend'+param.target] =1;
          }
       }else{
          localStorage.removeItem(key+'chart_legend'+param.target);
       }

    });
}


function sendChart(chart,id){
  var url = '/visual/GetChart';
  $.post(url, {
     'chartInfo':chart
    },function(data){
     $('body').unmask();
     if(data.status ==0){
        if(data.data !=undefined){
          if( data.data[0].showMsg  !=undefined && data.data[0].showMsg != null){
              var obj = $("#chart_box_init_"+id);
              obj.text(data.data[0].showMsg);
              obj.css({
                'line-height':'343px',
                'text-align':'center',
                'color':'#4E7156'
              });
              //obj.addClass('reportexplainbox');
              obj.next().hide();
              return;
          }
          //for(var  i=0; i<data.data.length; i++){
          if(data.data[0].chart == undefined){return false;}
          if(data.data[0].chart.type =='spline_time'  || data.data[0].chart.type =='area'){
              $("#chart_box_init_"+id).html(data.data[0].allhtml);
              data.data[0].chart.renderTo = data.data[0].id;
              $("#chart_box_init_"+id).next().hide();
              createChart(data.data[0]);
          }else{
              data.data[0].chart.renderTo = "chart_box_init_"+id;
              $("#"+data.data[0].chart.renderTo).next().hide();
              createChart(data.data[0]);
          }
          //}
        }
     }else{
        $.messager.alert('提示',data.msg,'warning');
         data.data[0].chart.renderTo = "chart_box_init_"+id;
         $("#"+data.data[0].chart.renderTo).next().hide();
         createChart(data.data[0]);
     }
  },'json');
}
//图表ajax
function chartAjax(chart,obj){
  var isEditor =  arguments[2]?arguments[2]:0;
  //$('body').mask('数据正在加载...');
  for(var i=0; i< chart.length; i++){
    sendChart([chart[i]],i);

  }
  //是否是编辑状态
  if(isEditor){
    $("#drapQ").dragsort("destroy");
    $("#drapQ").dragsort({
        dragSelector : "li",
        dragSelectorExclude:"select,button,input,textarea,b,small,span",
        dragEnd : function(){
          var sortChart  =[];
          $("#drapQ").find('li').each(function(){
               var nowIndex = $(this).index();
               var srcIndex  = $(this).attr('data-index');
               $(this).attr('data-index',nowIndex);
               sortChart.push(params.chart[srcIndex]);
          });
          params.chart = sortChart;
        },
        scrollSpeed:20,
        placeHolderTemplate: "<li class='placeHolder'><div ></div></li>"
    });
    editorChart();

  }

}
//生成图表框
function setChartBox(arr,obj){
  for(var  i=0; i<arr.length; i++){
    obj.find('.chart_box').eq(i).attr('id',arr[i].chart.renderTo);
  }
}
function getChartBox(arr,obj){
  var chartText = doT.template($("#charttpl").html());
  for(var i=0; i< arr.length; i++){
      if(arr[i] && arr[i].chartconf[0].chartWidth ==undefined){
           if(arr.length == 1 ){
              arr[i].chartconf[0].chartWidth = 100;
           }else if( arr.length%2==0 ){
              arr[i].chartconf[0].chartWidth = 50;
           }else{
              if( i == arr.length -1){
                  arr[i].chartconf[0].chartWidth  = 100;
              }else{
                  arr[i].chartconf[0].chartWidth  =50;
              }
           }
      }
  }
  obj.html(chartText(arr));
}

//编辑报表方法
function editorOperate(){
  $('.showinfo').tooltip({ 'position':'top'});
  //$('#dataSource').dialog('close');
  //$('.tablecontent').prev('button').addClass('btn-xs btnPosition');
  //$('button[data-option=reportgrade]').addClass('btn-xs btnPosition');
  //$('button[data-option=reportgrade]').removeClass('btnPosition').addClass('topPosition');
  $('button[data-option=reportgrade]').removeAttr('disabled');
  $('.saveConfig').removeAttr('disabled');
  $('.previewConfig').removeAttr('disabled');
}

function editorChart(){
  $('button[data-option=chartreport]').hide();
  var addHtml ="<div class='col-xs-6 addchartbox'><a class='btn btn-default btn-sm addChart'>增加图表</a></div>";
  $(".chartcontent").find('.row').append(addHtml);
}

function editorContrast(){
  //$('button[data-option=reportgrade]').addClass('btn-xs btnPosition');
  //$('button[data-option=reportgrade]').removeClass('btnPosition').addClass('topPosition');
  $('button[data-option=reportgrade]').removeAttr('disabled');
  $('.saveConfig').removeAttr('disabled');
  $('.previewConfig').removeAttr('disabled');
}
function commafy(num) {
    num = num + "";
    num = num.replace(/[ ]/g, ""); //去除空格
        if (num == "") {
        return;
        }
        if (isNaN(num)){
        return;
        }
        var index = num.indexOf(".");
        if (index==-1) {//无小数点
          var reg = /(-?\d+)(\d{3})/;
            while (reg.test(num)) {
            num = num.replace(reg, "$1,$2");
            }
        } else {
            var intPart = num.substring(0, index);
            var pointPart = num.substring(index + 1, num.length);
            var reg = /(-?\d+)(\d{3})/;
            while (reg.test(intPart)) {
            intPart = intPart.replace(reg, "$1,$2");
            }
           num = intPart +"."+ pointPart;
        }
    return num;
}
//还原千分位数据
function commafyback(num){
  var x = num.split(',');
   return parseFloat(x.join(""));
}
//获取单个值
function getOneData(data){
    var  objname  = $(data);
    var  content =0;
    if(typeof(objname) =='object' ){
      content = objname.find('span.data_name').text();
    }else{
      content = data[i][key];
    }
    content = commafyback(content);
    return content;
}
//设置颜色
function setColor(allData,coloum,key,obj){
    var max = Math.max.apply(null,allData);
    var min = Math.min.apply(null,allData);
    var  reObj =$('td[field='+key+']');
    for( var j =0; j< coloum.length; j++){
       if(coloum[j] ==0){
        continue;
       }
      var percent =  (coloum[j]- min )/(max- min);
      var  r  =  Math.ceil(255 - percent*255);
      var  g  =  Math.ceil(percent*255);
      //reObj.eq(j+1).css('background-color','rgb('+r+','+g+',0)');
      reObj.eq(j+1).css('background-color','rgb(255,'+r+',0)');
    }
}
//获取最大值
function getConverge(obj,data,keyList){
    var allData = [];
    var selectObj ={};
    var src = obj.datagrid('getPanel');
    src.find('.datagrid-body').find('td').css('background-color','rgb(255,255,255)');
    //获取所有值
    for(var i=0; i< data.length; i++){
      for(var j in  data[i]){
          if(j!='date'  && in_array(j,keyList) ){
              //var content = getOneData(data[i][j]);
              var content = data[i][j];
              if(!isNaN(content)){
                allData.push(content);
              }
          }
      }
    }
    for(var x=0; x< keyList.length; x++){
        var coloum = [];
        for(var p=0; p< data.length; p++){
            //var content = getOneData(data[p][keyList[x]]);
            var content = data[p][keyList[x]];
            if(!isNaN(content)){
              coloum.push(content);
            }
        }
        setColor(allData,coloum,keyList[x],obj);
    }
}
//价格设置
function formatPrice(val,row){
   if (typeof val === 'string' && val.indexOf('%') > 0) {
       return val;
   }
   if(!isNaN(parseInt(val))){
       return val +"%";
   } else {
       return val;
   }
  try{
    var  objname  = $(val);
  }catch (e){
     return val;
  }
  var  objname  = $(val);
  if(typeof(objname) =='object' ){
    var content = objname.find('span.data_name').text();
    var isaObj  = objname.find('span.data_name').find('a');
    var isiObj  = objname.find('span.data_name').find('i');
    var obj = $("<div id='box'></div>");
    if(content !='不存在'){
      if(isaObj.length >0){
        var href = isaObj.attr('href');

        if( isiObj.length >0){
           var newContent  =  "<a href='"+ href+"'  target='_blank'><i>"+content+"%</i></a>";
        }else{
           var newContent  =  "<a href='"+ href+"'  target='_blank'>"+content+"%</a>";
        }

        objname.find('span.data_name').html(newContent);
      }else{
        if( isiObj.length >0){
          var newContent = "<i>"+content+"%</i>";
        }else{
           var newContent = content+"%";
        }
        objname.find('span.data_name').html(newContent);
      }
      obj.append(objname);
      return obj.html();
    }else{
      return  val;
    }
  }else{
    if(contentArr[0] !='不存在'){
      return val+'%';
    }else{
      return val;
    }

  }
}

function cellStyler(val,row){
  if (val >0 ){
    return 'color:red;';
  }else{
    return 'color:green;';
  }
}

function loading(obj,type){
  var loadingObj = obj.parent();
  if(type ==1){
    loadingObj.find('.chartloading').show();
  }else{
    loadingObj.find('.chartloading').hide();
  }
 }

 //获取url中的参数
function GetQueryString(name)
{
    var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    //alert(r[2]);
    if(r!=null)
        return  r[2];
    return null;
}
//获取图表配置信息 $typesel == 当前 changeSelect 对象
function getChartConfig($typesel,timeStatu){
   //创建chartconfig 对象
   var  changeInfo = new Object();
   //获取ID
   changeInfo.id = $typesel.parent().next('div.chartContent').find('.chartContiner').attr('id');
   //获取类型
   var choseType =  $typesel.find('.typeChoise').val();
   changeInfo.type = choseType;
   if( choseType == 1){
     var value =  new Object();
     value.key =  $typesel.find('.quotas').val();
     value.name = $typesel.find('.quotas').find("option:selected").text();
     //获取指标
     changeInfo.values = JSON.stringify(value);
     var time = [];
     $typesel.find('.onlyQuota').find('.dateWdith').each(function() {
        time.push($(this).val());
     });
    //获取时间
    changeInfo.times = time;
   }else{
     var values = [];
     $typesel.find('.quotaContainer').children('em').each(function() {
       var value =  new Object();
        value.key = $(this).attr('rul');
        value.name = $(this).text();
        values.push(value);
     });
     //获取指标
     changeInfo.values = JSON.stringify(values);
     var time = [];
     $typesel.find('.difrentQuota').find('.dateWdith').each(function() {
        time.push($(this).val());
     });
     //获取时间
     changeInfo.times = time;
   }
     return changeInfo;
}

function openToolBox(obj){
   obj.dialog({
    title: '图表简单数据处理',
    //width: 240,
    closed: false,
    modal: false
  });
}

function getJsonStr(){
    var  ajaxObj = $('.chartContiner');
    if(ajaxObj.length || ajaxObj == null){
         var  ajaxUrl = [];
         $.each(ajaxObj,function(){
            //为图表初始化指标信息
            ajaxUrl.push($(this).attr('url'));
         })
          return ajaxUrl;
   }else{
       return 0;
   }
}
function getTimeAjax(timeType,srcSecting,timeInterval,obj){
    loading(obj,1);
    $.ajax({
        type:"POST",
        url:"/chart/showChart",
        dataType:"json",
        data:{'timeType':timeType,'srcSecting':srcSecting,'timeInterval':timeInterval},
        success: function(data){
          loading(obj,2);
          createChart(data);
        }
     });
}

function intAjax(i,ajaxArr){
    $('.chartloading').show();
    if(  i  == ajaxArr.length ){
         return;
    }else{
        $.ajax({
            type:"POST",
            url:"/chart/showChart",
            dataType:"json",
            //timeout:3200,
            data:{'timeType':'day','srcSecting':ajaxArr[i],'timeInterval':''},
            success: function(data){
                var  showId =  data.chart.renderTo;
                if($("#"+ showId).next().attr('class') =='chartloading'){
                    $("#"+ showId).next().hide();
                }
                $('.chartloading').hide();
                createChart(data);
           }
        });

    }
 }

 //日期前几天
function getBeforeDate(n,tempdate){
    var n = n;
    var d = new Date();
    if(tempdate){
      d = new Date(tempdate);
    }
    var year = d.getFullYear();
    var mon=d.getMonth()+1;
    var day=d.getDate();
    if(day <= n){
          if(mon>1) {
             mon=mon-1;
          } else {
           year = year-1;
           mon = 12;
           }
      }

    d.setDate(d.getDate()-n);
    year = d.getFullYear();
    mon=d.getMonth()+1;
    day=d.getDate();

    s = year+"-"+(mon<10?('0'+mon):mon)+"-"+(day<10?('0'+day):day);
    return s;
}
/* serach.js 和 reportpublic.js 公共方法  */
//计算时间偏移量
function getOffset(num){
  var interval= arguments[1] ? arguments[1] :0;
  var dataview_type = arguments[2]?arguments[2]:2; //默认为2天 1小时 3 为月级别
  var str = "-"+(parseInt(num)+parseInt(interval));
 return GetDateStr( parseInt(str),dataview_type);
}
//获取时间 月级别的返回月
function GetDateStr(AddDayCount,dateview_type){
  var dd = new Date();
  if (dateview_type == 1) {
      dd.setHours(dd.getHours()+AddDayCount);
  } else if(dateview_type == 3){
      dd.setMonth(dd.getMonth()+AddDayCount);
  } else {
      dd.setDate(dd.getDate()+AddDayCount);
  }

  var y = dd.getFullYear();
  var m = dd.getMonth() + 1 < 10 ? "0" + (dd.getMonth() + 1) : dd.getMonth() + 1;
  var d = dd.getDate() < 10 ? "0" + dd.getDate() : dd.getDate();
  var h = dd.getHours() < 10 ? "0" + dd.getHours() : dd.getHours();
  var mm = dd.getMinutes()<10? "0" +dd.getMinutes() : dd.getMinutes();
  var datetime = y+"-"+m+"-"+d;
  if(arguments[1]){
    switch (dateview_type) {
      case "1":
        datetime = y+"-"+m+"-"+d+' '+h+':00';
        break;
      case "2":
        datetime = y+"-"+m+"-"+d;
        break;
      case "3":
        datetime = y+"-"+m;
        break;
      default:
        datetime = y+"-"+m+"-"+d;
    }

  }

  return datetime;
}

function onLoadSuccess(data){
    if(data.status !=0 ){
        $.messager.alert('提示',data.msg,'warning');
    }else{
        var view = $("#dashboard").data().datagrid.dc.view2;
        var bodyCell = view.find("div.datagrid-body td[field]");
        bodyCell.contrast();
    }
}
/*
  判断设备类型
*/
function browserRedirect() {
    var sUserAgent = navigator.userAgent.toLowerCase();
    var bIsIpad = sUserAgent.match(/ipad/i) == "ipad";
    var bIsIphoneOs = sUserAgent.match(/iphone os/i) == "iphone os";
    var bIsMidp = sUserAgent.match(/midp/i) == "midp";
    var bIsUc7 = sUserAgent.match(/rv:1.2.3.4/i) == "rv:1.2.3.4";
    var bIsUc = sUserAgent.match(/ucweb/i) == "ucweb";
    var bIsAndroid = sUserAgent.match(/android/i) == "android";
    var bIsCE = sUserAgent.match(/windows ce/i) == "windows ce";
    var bIsWM = sUserAgent.match(/windows mobile/i) == "windows mobile";
    if (bIsIpad || bIsIphoneOs || bIsMidp || bIsUc7 || bIsUc || bIsAndroid || bIsCE || bIsWM) {
       return true;
    } else {
        return false;
    }
}
$(function(){
  //报表时间视图params.timereport.dateview_type
    var dateview_type = (typeof(params)!='undefined' && params && params.timereport && params.timereport.dateview_type) ?params.timereport.dateview_type:2;

    var formatarr = ['yyyy-mm-dd','yyyy-mm-dd hh:mm','yyyy-mm-dd','yyyy-mm'],
        startView = [2,2,2,3,4];

        if($('.datepicker').length>0){
            $('.datepicker').datetimepicker({
                format:formatarr[dateview_type],
                language:  'zh-CN',
                weekStart: 1,
                todayBtn:  1,
                autoclose: 1,
                todayHighlight: 1,
                startView: startView[dateview_type],// 1小时  2 日 3月 4年
                minView: dateview_type, // 1小时  2 日 3月 4年
                forceParse: 0
                //endDate: new Date(),
            });
        }


   $(window).resize(function () {
        $('#dashboard').datagrid('resize');
    });

  if ($('.select').length>0){
    $('.select').select2();
  }
   $('body').on('click','.sumTotal',function(){
         //获取changeSelect对象
         $typesel =$(this);
         changeInfo = getChartConfig($typesel,1);
           //报表配置文件
         var srcSecting = $('#'+ changeInfo.id).attr('url');
         //其它条件
         var timeType =  $(this).parent().parent().find('.changeTime').find('.active').attr('rul');
         var timeInterval = $(this).parent().parent().find('.timeBox').find('.active').attr('rul');
         if(timeInterval == undefined){
           timeInterval ='';
          }
          loading($('#'+ changeInfo.id),1);
          $.ajax({
          type:"POST",
          url:"/chart/showChart",
          dataType:"json",
          data:{'timeType':timeType,'srcSecting':srcSecting,'timeInterval':timeInterval,sum:1},
          success: function(data){
             loading($('#'+ changeInfo.id),2);
             //获取数据
             var  str = "<table style='width:500px' class='table table-bordered table-condensed'>";
             str  += "<tr>";
             str += "<td style='width:200px;text-align:center'>计算的时间段</td>";
             str += "<td style='width:100px;text-align:center'>曲线名称</td>";
             str += "<td style='width:100px;text-align:center'>总和</td>";
             str += "<td style='width:100px;text-align:center'>平均值("+data[0].type+")</td>";
             str += "</tr>";
             for(var i in data){
               str  += "<tr>";
               str += "<td style='width:200px;text-align:center'>"+data[i].dt+"</td>";
               str += "<td style='width:100px;text-align:center'>"+data[i].name+"</td>";
               str += "<td style='width:100px;text-align:center'>"+data[i].total+"</td>";
               str += "<td style='width:100px;text-align:center'>"+data[i].avg+"</td>";
                 str += "</tr>";
             }
             str += "</table>";
               var boxStr ="<div id='sumBox' style='height:300px; width:500px; overflow-x:hidden; overflow-y:auto;'><div>";
              if ($("#sumBox").length >0){
                  var obj = $(str);
                $("#sumBox").html("");
                $("#sumBox").append(str);
                $("#sumBox").dialog('open');
              }else{
                var obj = $(boxStr);
                obj.append(str);
                $('body').append(obj);
                  openToolBox(obj);
              }
          }
    });

  });
  //年月日交互事件
  $("body").on('click','.changeTime span',function(){
      $(this).addClass('active').siblings('span').removeClass('active');
         //获取changeSelect对象
         $typesel =$(this).parent();
         changeInfo = getChartConfig($typesel,1);
           //报表配置文件
         var srcSecting = $('#'+ changeInfo.id).attr('url');
         //获取数据配置文件
         var index = $(this).parent().parent().index();
         //其它条件
         var timeType = $(this).attr('rul');
         var timeInterval = $(this).parent().parent().find('.timeBox').find('.active').attr('rul');
         if(timeInterval == undefined){
           timeInterval ='';
          }
            getTimeAjax(timeType,srcSecting,timeInterval,$('#'+ changeInfo.id));
  });
  //快速时间选择
  $("body").on('click','.timeBox span',function(){
         $(this).addClass('active').siblings('span').removeClass('active');
         //获取changeSelect对象
         $typesel =$(this).parent();
         changeInfo = getChartConfig($typesel,1);
           //报表配置文件
         var srcSecting = $('#'+ changeInfo.id).attr('url');
         //其它条件
         var timeType = $(this).parent().parent().find('.changeTime').find('.active').attr('rul');
         var timeInterval = $(this).attr('rul');
         getTimeAjax(timeType,srcSecting,timeInterval,$('#'+ changeInfo.id));
  });
  //错误信息关闭按钮
  $('body').on('click','.error_close',function(){
     $(this).parent().hide();
  });
})
