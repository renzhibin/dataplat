var COLOR = {
  1: "#636d7d",
  2: "#ffba20",
  3: "#00ff00",
  4: "#00ff00",
  5: "#008000",
  6: "#ff0000",
  7: "#008000",
  9: "#636d7d",
  other: "#774e34"
};

var STATUS_EXPLAIN = {
  1: "阻塞",
  2: "准备中",
  3: "进行中",
  4: "进行中",
  5: "成功",
  6: "失败",
  7: "成功",
  9: "阻塞",
  other: "其他"
};

var URLS = {
  taskData:"/topo/topodata",
  taskList:"/topo/TopoCondition",
  showTask:"?show_task=",
  upstream:"&is_parent=1&is_child=0",
  downstream:"&is_child=1&is_parent=0"
};

// 基于准备好的dom，初始化echarts实例
var myChart = echarts.init(document.getElementById('main'));
/**
 * 获取查询列表
 */
getTaskList();
/**
 * 获取任务数据
 */
getTaskData(URLS.taskData);

bindEvents();

/**
 * 桑基图绘制方法
 */
function drawSankey(res) {
  var $graph = $("#main");
  if(res.data && res.data.nodes && res.data.nodes.length >0){
    var linksLen = res.data.links.length;
    if(linksLen === 0){
      Alert.show("上/下游无节点关系!");
      myChart.hideLoading();
    }else{
      if(linksLen <= 50){
        $graph.css("height","800px");
      }else if(linksLen>50 &&linksLen <=100){
        $graph.css("height","1200px");
      }else{
        $graph.css("height","1700px");
      }
      myChart.resize();
      myChart.hideLoading();
      var sankeyData = format(res.data);
      myChart.setOption(option = {
        tooltip: {
          formatter: function(params, ticket, callback) {
            var status = "";
            if (params.data.name) {
              var msg = [];
              if(STATUS_EXPLAIN[params.data.status]){
                msg.push("任务状态:"+(STATUS_EXPLAIN[params.data.status]));
              }else{
                msg.push("任务状态:其它");
              }
              msg.push("名称:"+params.data.name);
              msg.push("平台:"+params.data.other.plat);
              msg.push("创建人:"+params.data.other.creater);
              msg.push("开始时间:"+params.data.other.start_time);
              msg.push("结束时间:"+params.data.other.end_time);
              msg.push("数据量:"+params.data.other.data_size);
              return msg.join("<br>");
            }
          }
        },
        series: [{
          type: 'sankey',
          layout: 'none',
          data: sankeyData.data,
          links: sankeyData.links,
          // layoutIterations:0,
          itemStyle: {
            normal: {
              borderWidth: 1,
              borderColor: '#aaa'
            }
          },
          lineStyle: {
            normal: {
              curveness: 0.5,
              color: "source"
            }
          }
        }]
      });
    }
  }else{
    Alert.show("任务数据获取失败!请联系开发人员!");
  }

}

/**
 * 数据处理方法
 */
function format(source) {
  var data = {
    data: [],
    links: []
  };
  $.each(source.nodes, function(index, item) {
    var formatItem = {
      name: item.name,
      itemStyle: {
        normal: {
          color: COLOR[item.status] || "#774e34",
          borderColor: COLOR[item.status] || "#774e34",
          borderWidth:5
        }
      },
      status: item.status,
      other: item
    };
    data.data.push(formatItem);
  });
  $.each(source.links,function(index,item){
      item.value=100;
      data.links.push(item);
  });
  // data.links = source.links;
  return data;
}
/**
 * 任务列表获取成功后的处理与插入
 */
function formatTaskList(res) {
  var list = [];
  $.each(res.data, function(index, item) {
    var opt = "<option value=" + item + ">" + item + "</option>";
    list.push(opt);
  });
  $(".select-multiple").html(list.join(""));
  // // $('.select-multiple').selectpicker({
  // //   'selectedText': 'cat'
  // // });
  $("#selectMultiple").select2({
    allowClear: true,
    dropdownAutoWidth:true
  });
}

/**
 * 获取任务列表
 */
function getTaskList() {
  $http(URLS.taskList, "GET", {
    succCall: formatTaskList
  });
}
/**
 * 获取任务数据
 */
function getTaskData(url) {
  $http(url, "GET", {
    succCall: drawSankey,
    errCall:function(res){
      if (res.status == -1) {
        return;
      }
      Alert.show(res.msg);
    }
  });
}
/**
 * 获取任务数据url方法
 */
function getTaskUrl() {
  var taskUrl = URLS.taskData+URLS.showTask;
  var showTask = $("#selectMultiple").select2("val");
  var taskLen = showTask?showTask.length:0;
  var type = $(".task-type").val();
  // 判断查询任务
  if (taskLen === 0) {
    Alert.show("请选择查看的任务!");
    return false;
  } else if (taskLen === 1) {
    taskUrl += showTask[0];
  } else {
    taskUrl += showTask.join(",");
  }
  // 判断查询类型
  if (type == 1) {
    taskUrl += URLS.upstream;
  } else if (type == 2) {
    taskUrl += URLS.downstream;
  }
  return taskUrl;
}

/**
 * 设置模态框内容
 */
 function setModal(data){
   $(".modal-title").html(data.name);
   $(".platform").text(data.other.plat);
   $(".creater").text(data.other.creater);
   $(".startTime").text(data.other.start_time);
   $(".endTime").text(data.other.end_time);
   $(".dataSize").text(data.other.data_size);
   $(".run-date").val("");
 }

/**
 * 事件绑定方法
 */
function bindEvents() {
  // 图例展开与收起事件绑定
  $(".panel-legend").on("click", ".slide-up", function(e) {
    $(".panel-legend").hide();
    $(".slide-down").show();
  });
  $(".slide-down").on("click", function() {
    $(".panel-legend").show();
    $(".slide-down").hide();
  });

  // 获取任务数据事件绑定
  $(".submit").on("click", function() {
    // 获取任务url
    var taskUrl = getTaskUrl();
    if(taskUrl){
      myChart.showLoading();
      // 获取任务
      getTaskData(taskUrl);
    }
  });

  /**
   * 模态框内事件绑定
   */
  //  查看上游任务事件
  $(".modal").on("click",".upstream",function(e){
      var url = URLS.taskData+URLS.showTask+$(".modal-title").text()+URLS.upstream;
      getTaskData(url);
      $(".modal").modal("hide");

  // 查看下游事件
  }).on("click",".downstream",function(e){
    var url = URLS.taskData+URLS.showTask+$(".modal-title").text()+URLS.downstream;
    getTaskData(url);
    $(".modal").modal("hide");

  // 任务重跑事件
  }).on("click",".restart",function(e){
    $(".modal").modal("hide");
    var task = $(".modal-title").text();
    var time = $(".run-date").val();
    if (time == '') {
        Alert.show("重跑时间必须填写");
        return;
    }
    var content = "是否确认重跑？<br>"+"项目名:"+task+"<br>"+"重跑时间:"+time;
    Confirm.show(content,function(){
        runinfo  = {};
        runinfo.task = task;
        runinfo.time = time;
        myChart.showLoading();
        $.post('/project/savetoporun', {'runinfo':runinfo},function(data){
            myChart.hideLoading();
            Alert.show(data.msg);
        }, 'json');
    });
  });

  // 桑基图事件绑定，点击节点时弹出模态框
  myChart.on("click", function(params) {
    if (params.dataType === "node") {
      setModal(params.data);
      $(".modal").modal("show");
    }
  });
  $(".run-date").datetimepicker({
    format: "yyyy-mm-dd",
    autoclose: true,
    language: 'zh-CN',
    todayHighlight: true,
    minView: 'month'
  });
  window.onresize = myChart.resize;
}
