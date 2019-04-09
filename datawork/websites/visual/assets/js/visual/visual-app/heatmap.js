var resultList, heatmap, max, map, infoWindow, levelMap, layerWholesale;
var points = [];
var _heatmapLoaded = false;
// 定义区域选择模块，销售列表模块，主管区域模块
var $zoneSelect, zoneSelect, domList, managerAndSales, salesArea;
// 整个页面的请求url统一管理
var URL = {
  open: "/Heatmap/FetchMapdata?",
  closed: "/Heatmap/FetchClosedMarket?",
  saleZone: "/heatmap/SaleZoneCoords?region="
};

// 初次进入页面时需要渲染的select列表
var RENDER_LIST = ["type", "range", "user", "market"];

// 查询类型，按照订单还是按照金额
var SEARCH_TYPE = [{
  name: "订单",
  value: "order"
}, {
  name: "金额",
  value: "money"
}];

// 查询范围
var SEARCH_RANGE = [{
  name: "昨日",
  value: "yesterday"
}, {
  name: "30天内",
  value: "30"
}, {
  name: "累计",
  value: "total"
}];

// 查询用户类型
var SEARCH_USER = [{
  name: "全部",
  value: "all"
}, {
  name: "昨日新注册用户",
  value: "yest_newreg_flag"
}, {
  name: "昨日新下单用户",
  value: "yest_newcus_flag"
}, {
  name: "近30天新注册用户",
  value: "day30_newreg_flag"
}, {
  name: "近30天新下单用户",
  value: "day30_newcus_flag"
}, {
  name: "昨日关停用户",
  value: "yest"
}, {
  name: "近7日关停用户",
  value: "last"
}];

// 拼接后的超市统计字段类型
var DETAILS_TYPE = {
  orderyesterday: "ys_orders",
  order30: "days30_orders",
  ordertotal: "all_orders",
  moneyyesterday: "ys_money",
  money30: "days30_money",
  moneytotal: "all_money"
};

var MARKET_TYPE = [
  {
    name:"全部",
    value:"0"
  },{
    name:"限行",
    value:"1"
  },{
    name:"不限行",
    value:"2"
  }
];

// 初始化组件
var mapTool = new Map({
  container: 'container',
  mapOptions: {
    resizeEnable: true,
    zoom: 10,
    center: [116.39, 39.9]
  },
  infoWindowOpts: {
    needed: true,
    offset: [0, 0]
  },
  callback: action
});


// 初始化后执行的函数
function action() {
  $zoneSelect = $(".search-region-panel");
  domList = {
    $f_dom: $(".search-manager-panel"),
    $dom: $(".search-sales-panel")
  };
  map = mapTool.map;
  infoWindow = mapTool.infoWindow;
  // 引入分级图模块
  levelMap = new LevelMap();

  // 引入区域选择模块
  zoneSelect = new ZoneSelect($zoneSelect, actionZoneChanged, mapTool, 1);
  // 引入主管销售列表模块
  managerAndSales = new ManagerAndSalesSelect(domList, actionZoneChanged, mapTool, 1);
  // 引入围栏模块
  layerWholesale = new LayerWholesale(mapTool);
  // 引入主管区域模块
  salesArea = new SalesArea(mapTool);
  $.each(RENDER_LIST, function(index, item) {
    renderSearchList(item);
  });
  bindEvents();
}


/**
 * 添加交互事件绑定
 */

function bindEvents() {
  $(".panel-right").on("click", "#heatmap", function(e) {
    //  热力图按钮事件
    var $target = $(e.target);
    var $heatLegend = $(".legend-heat");
    if (_heatmapLoaded === false) {
      initHeatmap(toggleHeatmap, $target, $heatLegend);
      _heatmapLoaded = true;
    } else {
      toggleHeatmap($target, $heatLegend);
    }
  }).on("click", "#level", function(e) {
    //  点状图按钮事件
    var $target = $(e.target);
    var $levelLegend = $(".legend-level");
    $(".drop-para").val(40);
    if (levelMap._massLevelLoaded === false) {
      initLevelMap();
    }
    toggleLevelmap($target, $levelLegend);
  }).on("click", ".drop-round-button", function() {
    //  抹平按钮处理
    var options = {};
    var dropParas = parseFloat($(".drop-para").val());
    var minimum = parseFloat($(".minimum").val());
    var maximum = $(".maximum").val() === "" ? false : parseFloat($(".maximum").val());
    if (isNaN(dropParas) || isNaN(minimum) || isNaN(maximum)) {
      Alert.show("请输入正确的数字！");
      return;
    } else {
      //  抹平/去峰数据
      roundData(maximum, minimum, dropParas);
    }
  }).on("click", ".btn-get-data", function() {
    //  获取数据按钮事件
    var urlType = "open";
    var user = $(".select-user").val();
    var callback = formatData;
    if (user == "yest" || user == "last") {
      urlType = "closed";
      callback = getClosedData;
    }
    var options = {
      region: $(".select-zone").val(),
      f_uid: $(".select-manager").val(),
      uid: $(".select-sales").val(),
      trans_limit:$(".select-market").val(),
      user: user,
      urlType: urlType
    };
    loading.show("数据加载中");
    actionGetData(callback, options);
  }).on("click", "#drawArea", function() {
    //  绘制主管区域
    if (!salesArea.inited) {
      var zoneId = $(".select-zone").val();
      $http(URL.saleZone + zoneId, "GET", {
        succCall: salesArea.init,
        errCall: function() {
          Alert.show("主管区域获取错误,请再试一次!");
        },
        others: {
          context: salesArea
        }
      });
    } else {
      if (salesArea.isShow === false) {
        salesArea.show();
      } else {
        salesArea.hide();
      }
    }
  });

  /**
   * 添加收起事件
   */

  $(".slide-up").on('click', function() {
    $(".panel-right").hide();
    $(".slide-down").show();
  });
  $(".slide-down").on('click', function() {
    $(".slide-down").hide();
    $(".panel-right").show();
  });
}



// 辅助方法

/**
 * 数据获取后的数据格式化处理
 * @param {object} result - 请求返回的数据
 * @param {string} infoType - 查询类型[订单/金额]与查询范围[昨日/30日内/累计]拼凑而成的类型
 * @param {string} region - 需要的地域代码 1000北京 1001天津 1002杭州
 * @param {string} f_uid - 销售/主管id
 * @param {string} userType - 查看的用户类型 SEARCH_USER 中的值
 */
function formatData(result, infoType, region, f_uid, userType) {
  if (points.length) {
    points = [];
  }
  var type = DETAILS_TYPE[infoType];
  if (result && result.data && result.data.length) {
    $.each(result.data, function(index, info) {
      if (info.position) {
        var position = JSON.parse(info.position);
        if (info.zone_id == region || region === "all") {
          if ((f_uid === "others" && info.f_salename === "NULL") || f_uid !== "others") {
            if (userType === "all" || info[userType] == 1) {
              points.push({
                lng: position.position.lng,
                lat: position.position.lat,
                count: info[type],
                lnglat: [position.position.lng, position.position.lat],
                market_name: info.market_name,
                address: info.address,
                contact_name: info.contact_name,
                contact_phone: info.contact_phone,
                f_salename: info.f_salename,
                sales_name: info.sales_name,
                mapLevel: 0,
                zone_id: info.zone_id
              });
            }
          }
        }
      }
    });
    _heatmapLoaded = false;
    max = getMax(points);
    $(".btn-draw").removeClass('btn-disabled').addClass('btn-success');
  } else {
    Alert.show("数据获取失败，请重试！");
  }
  loading.hide();
  $("input[type=checkbox]").removeAttr('disabled');
  $("#level").click();
}


/**
 * 获取关停用户相关数据
 * @param {object} result - 请求返回的数据
 * @param {string} infoType - 查询类型[订单/金额]与查询范围[昨日/30日内/累计]拼凑而成的类型
 * @param {string} region - 需要的地域代码 1000北京 1001天津 1002杭州
 * @param {string} f_uid - 销售/主管id
 * @param {string} userType - 查看的用户类型 SEARCH_USER 中的值
 */
function getClosedData(result, infoType, region, f_uid, userType) {
  if (points.length) {
    points = [];
  }
  var type = DETAILS_TYPE[infoType];
  if (result && result[userType] && result[userType].length) {
    result[userType].forEach(function(info) {
      if (info.position) {
        var position = JSON.parse(info.position);
        if (info.zone_id == region || region === "all") {
          points.push({
            lng: position.position.lng,
            lat: position.position.lat,
            count: 1,
            lnglat: [position.position.lng, position.position.lat],
            market_name: info.market_name,
            address: info.address,
            contact_name: info.contact_name,
            contact_phone: info.contact_phone,
            f_salename: info.f_salename,
            sales_name: info.sales_name,
            mapLevel: 0,
            zone_id: info.zone_id
          });
        }
      }
    });
    _heatmapLoaded = false;
    max = getMax(points);
    $(".btn-draw").removeClass('btn-disabled').addClass('btn-success');
  } else {
    $("#marketNums").text(0);
    $("#sumNums").text(0);
    $("#maxNums").text(0);
  }
  loading.hide();
  $("input[type=checkbox]").removeAttr('disabled');
  $("#level").click();
}

/**
 * 异步获取数据方法
 * @param {function} callback - 请求成功的回调函数
 * @param {object} opt - 回调函数中需要用到的参数
 */
function syncData(callback, opt) {
  var params = "";
  if (!opt) {
    return;
  }
  if (opt.f_uid === "all" || opt.f_uid === "others") {
    if (opt.region !== "all") {
      params = "zone_id=" + opt.region;
    } else {
      params = "";
    }
  } else if (opt.f_uid !== "" && opt.uid === "all") {
    params = "f_uid=" + opt.f_uid;
  } else if (opt.f_uid !== "" && opt.uid !== "") {
    params = "uid=" + opt.uid;
  }
  if(params === ""){
      params = "trans_limit=" + opt.trans_limit;
  }else{
      params += "&trans_limit=" + opt.trans_limit;
  }
  var type = $(".select-type").val() + $(".select-range").val();
  $http(URL[opt.urlType] + params, "GET", {
    others: {
      type: type,
      zoneId: opt.region,
      fUid: opt.f_uid,
      user: opt.user
    },
    succCall: function(result, others) {
      callback(result, others.type, others.zoneId, others.fUid, others.user);
    }
  });
}
/**
 * 渲染查询列表
 * @param {string} type - 需要渲染的列表类别
 */
function renderSearchList(type) {
  var list, options;
  if (type == "type") {
    list = SEARCH_TYPE;
  } else if (type == "range") {
    list = SEARCH_RANGE;
  } else if (type == "region") {
    list = SEARCH_REGION;
  } else if (type == "user"){
    list = SEARCH_USER;
  }else{
    list = MARKET_TYPE;
  }
  list.forEach(function(item) {
    var option = "<option value=" + item.value + ">" + item.name + "</option>";
    options += option;
  });
  $(".select-" + type).html(options);
}

/**
 * 获取最大值同时给热力图图例设置相应值
 * @param {array} dataList - 处理完成后的数据
 * @return {number} 返回数据中的最大值
 */
function getMax(dataList) {
  var max = 0;
  var sum = 0;
  var average = 0;
  var maxTrue = 0;
  var LEN = dataList.length;
  var list = [];
  for (var i = 0; i < LEN; i++) {
    list.push(dataList[i].count);
    sum += parseFloat(dataList[i].count);
  }
  if ((Math.max.apply(null, list)).toFixed(2) != -Infinity) {
    maxTrue = (Math.max.apply(null, list)).toFixed(2);
  } else {
    maxTrue = 0;
  }
  max = maxTrue / 2;
  average = (sum / LEN).toFixed(2);
  $("#marketNums").text(list.length);
  $("#sumNums").text(sum.toFixed(2));
  $("#maxNums").text(maxTrue);
  $("#red").text(max);
  $("#yellow").text((max * 0.85).toFixed(2));
  $("#green").text((max * 0.7).toFixed(2));
  $("#lightBlue").text((max * 0.65).toFixed(2));
  $("#blue").text((max * 0.5).toFixed(2));
  return max;
}

/**
 * initHeatmap 初始化热力图插件
 */
function initHeatmap(callback, $target, $heatLegend) {
  map.plugin(["AMap.Heatmap"], function() {
    //初始化heatmap对象
    heatmap = new AMap.Heatmap(null, {
      radius: 30, //给定半径
      opacity: [0, 0.8],
      gradient: {
        0.5: 'blue',
        0.65: 'rgb(117,211,248)',
        0.7: 'rgb(0, 255, 0)',
        0.85: '#ffea00',
        1.0: 'red'
      }
    });
    heatmap.setDataSet({
      data: points,
      max: max
    });
    callback($target, $heatLegend);
  });
}
/**
 * initLevelMap 初始化分级点图
 */
function initLevelMap() {
  levelMap.init({
    dataList: points,
    level: 5,
    dropPara: 40,
    map: map,
    $legend: $(".level-info"),
    infoWindow: infoWindow
  });
  $(".level-menu").slideDown();
  $("#roundNums").text(levelMap.getRoundNumber());
}

function toggleHeatmap($target, $heatLegend) {
  if ($target.hasClass("close")) {
    heatmap.setMap(map);
    $heatLegend.show();
    $target.removeClass("close");
  } else {
    heatmap.setMap(null);
    $heatLegend.hide();
    $target.addClass("close");
  }
}

function toggleLevelmap($target, $levelLegend) {
  if ($target.hasClass("close")) {
    levelMap.show();
    $levelLegend.show();
    $target.removeClass("close");
    $(".level-menu").slideDown();
  } else {
    levelMap.hide();
    $levelLegend.hide();
    $target.addClass("close");
    $(".level-menu").slideUp();
  }
  if ($(".select-type").val() === "order") {
    $(".legend-level").find(".menu-title").text("订单统计图例");
  } else {
    $(".legend-level").find(".menu-title").text("金额统计图例");

  }
}
// 抹平数据处理
function roundData(maximum, minimum, dropParas) {
  var $levelLegend = $(".legend-level");
  var options = {
    dataList: points,
    level: 5,
    dropPara: dropParas,
    minimum: minimum,
    map: map,
    $legend: $(".level-info"),
    infoWindow: infoWindow
  };
  levelMap.clear();
  if (maximum !== false) {
    options.maximum = maximum;
  }
  levelMap.init(options);
  $("#roundNums").text(levelMap.getRoundNumber());
  levelMap.show();
  $levelLegend.show();
}

// 点击获取数据按钮后执行的方法
function actionGetData(callback, options) {
  allClean();
  syncData(callback, options);
}

// 清空页面所有已修改参数方法
function allClean() {
  $(".minimum").val(-1);
  $(".maximum").val("");
  $("#roundNums").text("暂无数据");
  $(".level-info").text("");
  $(".legend").hide();
  salesArea.close();
  if (heatmap && heatmap.setMap) {
    heatmap.setMap(null);
    if (!$(".toggle-heatmap").hasClass('close')) {
      $(".toggle-heatmap").addClass('close');
    }
  }
  if (levelMap) {
    levelMap.hide();
    levelMap.clear();
    $(".level-menu").slideUp();
    $(".drop-para").val("");
  }
  if ($(".btn-draw").hasClass('btn-success')) {
    $("input[type=checkbox]").attr('disabled', true);
    $(".btn-draw").removeClass('btn-success').addClass('btn-disabled');
    $(".map").attr("checked", false);
    $(".map").addClass('close');
  }
}

// 外部组件的回调函数
function actionZoneChanged(value) {
  allClean();
  managerAndSales.appendManagerList(value);
  $(".select-sales").html("<option value=''>全部</option>");
}


// 地图上的辅助方法
function markerClick(e) {
  var title = e.target.title || "超市信息异常";
  var id = e.target.id;
  infoWindow.setContent(
    ['<h3>' + title + '</a></h3>', '<div>' + e.target.content + '</div>'].join('')
  );
  infoWindow.open(map, e.target.getPosition());
}

function focusAndZoom(e) {
  map.setZoomAndCenter(16, e.target.getPosition());
}
