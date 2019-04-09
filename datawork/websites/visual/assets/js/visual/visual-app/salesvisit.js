// 定义区域选择组件，销售列表组件
var $zoneSelect, zoneSelect, domList, managerAndSales;
// 获取当前页面根Dom
var $mod = $(".mod-sales-visit");
// 获取销售拜访信息url
var URL = "/heatmap/SalesvisitData";
// 初始化map组件,进行地图绘制
var mapTool = new Map({
  container: 'mapContainer',
  infoWindowOpts: {
    needed: true,
    offset: [0, -30]
  },
  // 回调方法
  callback: action
});

/**
 * action mapTool 加载完成后执行的方法
 */
function action() {
  // 区域选择组件容器
  $zoneSelect = $(".search-region-panel");
  // 主管销售列表组件容器
  domList = {
    $f_dom: $(".search-manager-panel"),
    $dom: $(".search-sales-panel")
  };
  // 初始化时间选择组件
  $('#time').datepicker({
    showSecond: true,
    timeFormat: 'hh:mm:ss'
  });
  // 设置输入框内时间为当前时间
  helper.setDate();
  // 引入区域选择组件
  zoneSelect = new ZoneSelect($zoneSelect, actionZoneChanged, mapTool, 0);
  // 引入主管销售列表组件
  managerAndSales = new ManagerAndSalesSelect(domList, actionZoneChanged, mapTool, 0);
  // 调整select框的样式
  $("select").removeClass("sm").addClass("lg");
  // 进行事件绑定
  bindEvents();
}

/**
 * 事件绑定方法
 */
function bindEvents() {
  // 点击提交销售信息获取拜访列表事件
  $mod.find(".panel").on("click", ".submit", function(e) {
    if ($(".time").val() === "") {
      alert("请先选择时间!");
    } else {
      var params = {
        uid: $(".select-sales").val(),
        date: $(".time").val()
      };
      loading.show("数据加载中");
      $http(URL, "GET", {
        params: params,
        succCall: helper.checkVisitList
      });
    }
    // 收齐事件绑定
  }).on("click", ".pack-up", function(e) {
    $(".panel").hide();
    $(".unfold").show();
  });
  // 展开事件绑定
  $mod.find(".unfold").on("click", function(e) {
    $(".unfold").hide();
    $(".panel").show();
  });
}

/**
 * helper 提供了一系列辅助方法,后续应该会统一维护起来
 * @function {Function} setDate - 默认进入页面时将时间设置为昨日方法
 * @function {Function} checkVisitList - 判断销售当天是否进行了拜访
 */
var helper = {
  /**
   * @function {Function} setDate 默认进入页面时将时间设置为昨日方法
   */
  setDate: function() {
    var timestamp = Date.parse(new Date());
    var yesterday = new Date(timestamp - 86400000);
    var year = yesterday.getFullYear();
    var month = (yesterday.getMonth() + 1) > 9 ? (yesterday.getMonth() + 1) : "0" + (yesterday.getMonth() +
      1);
    var date = yesterday.getDate() > 9 ? yesterday.getDate() : "0" + yesterday.getDate();
    $(".mod-sales-visit").find(".time").val(year + "-" + month + "-" + date);
  },
  /**
   * 判断销售当天是否进行了拜访
   * @param {object} data - 请求成功返回的数据
   */
  checkVisitList: function(data) {
    loading.hide();
    mapTool.clear();
    if (data.visit_list.length > 0) {
      formatVisitDataAndDraw(data);
    } else {
      Alert.show("该销售当天无拜访记录!");
    }
  }
};

/**
 * formatVisitDataAndDraw 格式化销售拜访数据并绘制相关内容方法
 * @param {Object} sourceData 请求获取的源数据
 */
function formatVisitDataAndDraw(sourceData) {
  var that = this;
  var visitLineList = [];
  $.each(sourceData.visit_list, function(index, item) {
    var visitContent = "<div class='market-icon market-text visit'>" + (index + 1) +
      "</div>";
    var marketContent = "<div class='market-icon market-text market'></div>";
    var marketExtData = item;
    var visitPosition = [JSON.parse(item.visit_pos).lng, JSON.parse(item.visit_pos).lat];
    var marketPosition = [JSON.parse(item.market_pos).lng, JSON.parse(item.market_pos).lat];
    var linPosition = [visitPosition, marketPosition];
    // 绘制销售拜访点
    var visitMarker = mapTool.drawMarker({
      position: visitPosition,
      content: visitContent,
      extData: marketExtData,
      zIndex: 200
    });
    // 绘制超市点
    var marketMarker = mapTool.drawMarker({
      position: marketPosition,
      content: marketContent,
      extData: marketExtData,
      zIndex: 100
    });
    // 绘制拜访点与超市点连线
    var polyline = mapTool.drawPolyline({
      position: linPosition,
      style: {
        strokeColor: "#46acf6", //线颜色
        strokeOpacity: 1, //线透明度
        strokeWeight: 2, //线粗细度
        strokeStyle: "dashed", //线样式solid实线  dashed虚线
      }
    });
    visitLineList.push(visitPosition);
    // 相关事件绑定
    mapTool.on(marketMarker, "mouseover", showContent);
    mapTool.on(marketMarker, "mouseout", hideContent);
    mapTool.on(visitMarker, "mouseover", showContent);
    mapTool.on(visitMarker, "mouseout", hideContent);
  });
  // 绘制拜访路线连线
  mapTool.drawPolyline({
    position: visitLineList,
    style: {
      strokeColor: "#46acf6", //线颜色
      strokeOpacity: 1, //线透明度
      strokeWeight: 2, //线粗细度
      strokeStyle: "solid", //线样式solid实线  dashed虚线
    }
  });
  // fitview
  mapTool.map.setFitView();
}

/**
 * showContent 展示hover对象相关信息方法
 * @param {Object} e hover响应事件对象
 */
function showContent(e) {
  var extData = e.target.getExtData();
  mapTool.infoWindow.setContent([
    '<div>',
    '<div class="market-title">' + extData.market_name + '</div>',
    '<div>超市地址:' + extData.address + '</div>',
    '<div>联系电话:' + extData.contact_phone + '</div>',
    '<div>拜访时间:' + extData.cate_date + '</div>',
    '</div>'
  ].join(''));
  mapTool.infoWindow.open(mapTool.map, e.target.getPosition());
}

/**
 * hideContent 隐藏hover对象相关信息方法
 * @param {Object} e hover响应事件对象
 */
function hideContent(e) {
  mapTool.infoWindow.close();
}

/**
 * actionZoneChanged 外部组件-区域切换后的回调函
 * @param {string} zoneId - 切换的区域id
 */
function actionZoneChanged(zoneId) {
  managerAndSales.appendManagerList(zoneId);
}
