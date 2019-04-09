<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title>库存筛选获取</title>
<link rel="stylesheet" type="text/css" href="/assets/js/sparks/css/jquery-ui.css" />
<link rel="stylesheet" href="http://cdn.bootcss.com/bootstrap/3.3.0/css/bootstrap.min.css">
<style type="text/css">
a{color:#007bc4/*#424242*/; text-decoration:none;}
a:hover{text-decoration:underline}
ol,ul{list-style:none}
body{height:100%; font:12px/18px Tahoma, Helvetica, Arial, Verdana, "\5b8b\4f53", sans-serif; color:#51555C;}
img{border:none}
input{width:200px; height:20px; line-height:20px; padding:2px; border:1px solid #d3d3d3}
.main{
  margin-top:30px;
}
.main .title h2{
  text-align: center;
  margin: 20px auto;
}
.table-list{
  margin-top: 10px;
}
.ui-timepicker-div .ui-widget-header { margin-bottom: 8px;}
.ui-timepicker-div dl { text-align: left; }
.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
.ui-timepicker-div td { font-size: 90%; }
.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
.ui_tpicker_hour_label,.ui_tpicker_minute_label,.ui_tpicker_second_label,.ui_tpicker_millisec_label,.ui_tpicker_time_label{padding-left:20px}
.loading{
  position: fixed;
  top:40%;
  left:40%;
  display: none;
}
.submit{
  margin-right:15px;
}
.inputs{
  margin-left: 15px;
}
.line-chart{
  height:500px;
  display: none;
}
#chart{
  height:500px;
}
</style>
</head>

<body>
  <img class="loading" src="/assets/js/sparks/img/bird.gif" alt="" />
  <div class="container">
      <div class="main">
         <div class="search row">
             <form class="form-inline" role="form">
                   <input type="text" class="form-control col-md-2 col-sm-2 col-xs-2 inputs" id="begin" placeholder="开始时间">
                   <input type="text" class="form-control col-md-2 col-sm-2 col-xs-2 inputs" id="end" placeholder="结束时间">
                   <input type="text" class="form-control col-md-2 col-sm-2 col-xs-2 inputs" id="sku" placeholder="物美码">
                   <select class="form-control col-md-2 col-sm-2 col-xs-2 inputs" id="warehouse">
                      <option value="">选择仓库</option>
                      <option value="all">全部</option>
                      <option value="DC09">DC09</option>
                      <option value="DC10">DC10</option>
                      <option value="DC31">DC31</option>
                      <option value="DC37">DC37</option>
                      <option value="DC55">DC55</option>
                      <option value="DC59">DC59</option>
                   </select>
               <span class="submit btn btn-primary col-md-1 col-sm-1 col-xs-1 pull-right">提交</span>
             </form>
         </div>
      </div>
      <div class="line-chart row">
          <div class="col-xs-12" id="chart">

          </div>
      </div>
      <div class="table-list">
        <div class="table-responsive">
          <table class="table table-bordered table-hover">
            <thead class="title-list"></thead>
            <tbody class="item-list"></tbody>
          </table>
        </div>
      </div>
  </div>


  <script src="/assets/js/artTemplate.js"></script>
  <script type="text/javascript" src="/assets/js/sparks/js/jquery1.7.js"></script>
  <script type="text/javascript" src="/assets/js/sparks/js/jquery-ui.js"></script>
  <script type="text/javascript" src="/assets/js/sparks/js/jquery-ui-slide.min.js"></script>
  <script type="text/javascript" src="/assets/js/sparks/js/jquery-ui-timepicker-addon.js"></script>
  <script type="text/javascript" src="http://echarts.baidu.com/gallery/vendors/echarts/echarts-all-3.js"></script>
  <script type="text/javascript">
  var tpls = {
      theader:[
          "<tr>",
              "{{ each list as item }}",
                  "<th>{{item.name}}</th>",
              "{{ /each }}",
          "</tr>"
      ].join(""),
      tbody:[
          "{{ each list as item index}}",
              "<tr>",
                  "<td>{{index+1}}</td>",
                  "<td>{{item.mandt}}</td>",
                  "<td>{{item.werks}}</td>",
                  "<td>{{item.matnr}}</td>",
                  "<td>{{item.maktx}}</td>",
                  "<td>{{item.lbkum}}</td>",
                  "<td>{{item.zdate}}</td>",
                  "<td>{{item.ztime}}</td>",
              "</tr>",
          "{{ /each }}",
      ].join("")
  };
  var THEAD = [
    {name:"序号"},
    {name:"区域"},
    {name:"仓库名"},
    {name:"物美码"},
    {name:"商品名称"},
    {name:"库存数"},
    {name:"ERP生成日期"},
    {name:"ERP生成时间"}
  ];
  $(function(){
  	$('#begin').datetimepicker({
      showSecond: true,
      timeFormat: 'hh:mm:ss'
    });
    $('#end').datetimepicker({
      showSecond: true,
      timeFormat: 'hh:mm:ss'
    });
  });
  $(".table-list").find(".title-list").append(template.compile(tpls.theader)({list:THEAD}));
  $(".submit").on("click",function(){
      var mistiming =((new Date($('#end').val().split(" ")[0]))- (new Date($('#begin').val().split(" ")[0])))/86400000;
        if($('#begin').val() == "" || $('#end').val() == "" || $("#sku").val() == ""){
            alert("开始/结束时间与物美码为必填项，请填写后再进行查询！");
        }else{
            if(mistiming <= 1){
                $(this).attr('disabled','disabled');
                $('.loading').show();
                $(".table-list").find(".item-list").html("");
                $(".line-chart").hide();
                var addStr = getSearchParams();
                var sqlString = "select mandt,matnr,werks,maktx,lbkum,zdate,ztime from adhot.adhot_wm_zkucundelivery  where  dt>='"+$('#begin').val().split(" ")[0]+"' and dt<='"+$('#end').val().split(" ")[0]+"'"+addStr+" order by zdate desc, ztime desc";
                var paramsArr = ['mandt','matnr','werks','maktx','lbkum','zdate','ztime'].join(",");
                $.ajax({
                 url: 'http://115.182.215.149/inventoryStatus',
                 type: 'POST',
                 dataType: 'JSON',
                 timeout:600000,
                 data:{
                   cols:paramsArr,
                   sql:sqlString
                 }
                })
                .done(function(response) {
                    if(response.status == 0){
                      drawChart(response);
                      $(".table-list").find(".item-list").append(template.compile(tpls.tbody)({list:response.data}));
                      console.log("运行时间:"+response.run_spend);
                    }
                    $('.submit').attr('disabled',null);
                    $('.loading').hide();
                })
                .fail(function() {
                 alert("数据获取错误，请重新查询！");
                 $('.submit').attr('disabled',null);
                 $('.loading').hide();
               });
           }else{
             alert("最多只能查看两天内的库存变化数据!");
           }
        }

  });

  //获取查询条件
  function getSearchParams(){
    var sqlStr = "";
    if($("#sku").val()){
        sqlStr += " and matnr='"+$("#sku").val()+"'";
    }
    if($("#warehouse  option:selected").val() && $("#warehouse  option:selected").val() != "all"){
        sqlStr +=" and werks='"+$("#warehouse  option:selected").val()+"'";
    }
    return sqlStr;
  }

  function sourceDataFormat(dataList){
      var format = {
        legend:[],
        seriesData:[],
        wareName:''
      }


      dataList.forEach(function(item,index){
          var formatData = dateFormat(item);
          format.legend.push((formatData.date+"\n"+formatData.time));
          format.seriesData.push(item.lbkum);
      });
      format.wareName = dataList[0].werks;
      return format;

  }

  function dateFormat(item){
      var year = item.zdate.slice(0,4);
      var month = item.zdate.slice(4,6);
      var day = item.zdate.slice(6);
      var hour = item.ztime.slice(0,2);
      var minute = item.ztime.slice(2,4);
      var second = item.ztime.slice(4);
      var formatDate = {
        date:year+"-"+month+"-"+day,
        time:hour+":"+minute+":"+second
      }
      return formatDate;
  }

  function dataGrouping(sourceData){
      var format = {
        wareList:[],
        dataList:[],
        seriesDataList:[],
        series:[]
      }
      sourceData.forEach(function(item,index){
        if(format.wareList.indexOf(item.werks) === -1){
            format.wareList.push(item.werks);
            format.dataList.push([]);
        }
        format.wareList.forEach(function(wareItem,wareIndex){
          if(item.werks === wareItem){
            format.dataList[wareIndex].push(item);
          }
        })
      });
      format.dataList.forEach(function(item,index){
            format.seriesDataList.push(sourceDataFormat(item));
      });
      format.seriesDataList.forEach(function(item,index){
            var seriesOptions = {
                name:item.wareName,
                type:'line',
                step: 'end',
                data:item.seriesData.reverse(),
                label: {
                    normal: {
                        show: true,
                        position: 'top'
                    }
                },
                markPoint: {
                    symbol: "arrow",
                    symbolSize: "20",
                    data: [
                        {type: 'max', name: '最大值'},
                        {type: 'min', name: '最小值'}
                    ]
                }
            };
            format.series.push(seriesOptions);

      })
      return format;
  }


  function drawChart(inventory){
    var dataGroup = dataGrouping(inventory.data);
    $(".line-chart").show();
    var dom = document.getElementById("chart");
    var myChart = echarts.init(dom);
    var app = {};
    option = null;
    option = {
        title: {
            text: inventory.data[0].maktx,
            x: 'center',
            align: 'right',
            subtext:"数据查询时间:"+inventory.run_spend+"秒"
        },
        tooltip: {
            trigger: 'axis'
        },
        legend: {
            data:dataGroup.wareList,
            x: 'left',
            left:"20"
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        toolbox: {
            feature: {
              dataZoom: {
                yAxisIndex: 'none'
              },
              restore: {},
              saveAsImage: {}
            }
        },
        xAxis: {
            type: 'category',
            data: dataGroup.seriesDataList[0].legend.reverse(),
            name: '时间'
        },
        yAxis: {
            type: 'value',
            name: '库存量(EA)'
        },
        series: dataGroup.series
    };

    if (option && typeof option === "object") {
        myChart.setOption(option, true);
    }
  }




  </script>
</body>
</html>
