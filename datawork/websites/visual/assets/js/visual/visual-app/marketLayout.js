/**
 * 接口地址，统一管理
 */
var URLS = {
  lsh: '/Heatmap/FetchMapdata', //优供超市信息获取
  amap: '/heatmap/FetchGDMarket', //高德超市信息获取
  bmap: '/heatmap/FetchBaiduMarket', //百度超市信息获取
  contrast: '/heatmap/GYzoneRate', //对比区域信息获取
  saleZone: '/heatmap/SaleZoneCoords'
};

// 定义图层
var $modSelect, lshMassLayer, amapMassLayer, bmapMassLayer, contrastLayer, layerContrast;
// 初始化区域筛选模块 主管区域绘制模块
var zoneSelect, salesArea;

$modSelect = $('.search-panel');
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
  callback: layerInit
});

// 地图加载完成后需要执行的函数
function layerInit() {
  // 事件绑定
  bindEvents();
  // 优供超市图层
  lshMassLayer = new MassLayer();
  // 高德超市图层
  amapMassLayer = new MassLayer();
  // 高德超市图层
  bmapMassLayer = new MassLayer();
  // 对比超市图层
  contrastLayer = new MassLayer();
  // 对比区域图层
  layerContrast = new LayerContrast();
  // 引入区域切换模块
  zoneSelect = new ZoneSelect($modSelect, allClean, mapTool, 1);
  // 引入主管区域绘制模块
  salesArea = new SalesArea(mapTool);
}


/**
 * 通过ajax获取的相关点信息
 * gdPoints 高德超市信息
 * lshPoints 优供超市信息
 * tobaccoPoints 高德烟酒店超市信息
 * contrastPoints 对比区域相关信息 包括绘制区域样式areaStyle 区域内高德超市点原始信息gd 区域内优供超市点原始信息yg
 *                格式化后高德超市信息gdPoints 格式化后优供超市信息ygPoints
 */
var points = {
  gdPoints: [],
  lshPoints: [],
  bdPoints: [],
  contrastPoints: {
    yg: [],
    ygPoints: [],
    gd: [],
    gdPoints: [],
    areaStyle: null
  }
};


/**
 * 格式化优供超市信息 由于先前定义的数据结构与高德地图不同，因此分开两个内容进行格式化
 */
function formatLshData(result, infoType, region, type) {
  if (points.lshPoints.length) {
    points.lshPoints = [];
  }
  if (result && result.data && result.data.length) {
    result.data.forEach(function(info) {
      if (info.position) {
        var position = JSON.parse(info.position);
        if (info.zone_id == region || region === "all") {
          points.lshPoints.push({
            lng: position.position.lng,
            lat: position.position.lat,
            lnglat: [position.position.lng, position.position.lat],
            market_name: info.market_name,
            address: info.address,
            contact_name: info.contact_name,
            contact_phone: info.contact_phone,
            f_salename: info.f_salename,
            sales_name: info.sales_name,
            zone_id: info.zone_id
          });
        }
      }
    });
    $(".lsh").addClass('can-check');
    $(".lsh").removeClass("btn-disabled");
    $("#marketNums").text(points.lshPoints.length);
  } else {
    Alert.show("数据获取失败，请重试！");
  }
  $("input[name=" + type + "]").removeAttr('disabled');

}

/**
 * 格式化高德超市点数据 这里包括 高德超市信息的格式化
 */
function formatAmapData(result, type) {
  var point = null;
  if (type === "amap") {
    point = points.gdPoints;
  } else {
    point = points.bdPoints;
  }
  if (point.length) {
    point = [];
  }
  if (result && result.data && result.data.length) {
    result.data.forEach(function(info) {
      if (info.position) {
        point.push({
          lng: info.position.lng,
          lat: info.position.lat,
          lnglat: [info.position.lng, info.position.lat],
          market_name: info.market_name,
          address: info.address,
          contact_name: info.contact_name,
          contact_phone: info.contact_phone,
          zone_id: info.zone_id
        });

      }
    });
    $("." + type).removeClass('btn-disabled');
    if (type === "amap") {
      $("#amapMarketNums").text(point.length);
    } else {
      $("#bmapMarketNums").text(point.length);
    }
    $("input[name=" + type + "]").removeAttr('disabled');
  } else {
    Alert.show("数据获取失败，请重试！");
  }
}

/**
 * 格式化对比区域内数据格式方法
 */
function formatContrastData(data) {
  data.forEach(function(item) {
    points.contrastPoints.yg = points.contrastPoints.yg.concat(item.yg);
    points.contrastPoints.gd = points.contrastPoints.gd.concat(item.gaode);
  });
  points.contrastPoints.yg.forEach(function(info) {
    if (info.position) {
      points.contrastPoints.ygPoints.push({
        lng: info.position.lng,
        lat: info.position.lat,
        lnglat: [info.position.lng, info.position.lat],
        market_name: info.market_name,
        address: info.address,
        contact_name: info.contact_name,
        contact_phone: info.contact_phone,
        zone_id: info.zone_id
      });

    }
  });
  points.contrastPoints.gd.forEach(function(info) {
    if (info.position) {
      points.contrastPoints.gdPoints.push({
        lng: info.position.lng,
        lat: info.position.lat,
        lnglat: [info.position.lng, info.position.lat],
        market_name: info.market_name,
        address: info.address,
        contact_name: info.contact_name,
        contact_phone: info.contact_phone,
        zone_id: info.zone_id
      });

    }
  });
}



// 高德/优供超市对比区域绘制
function LayerContrast() {
  this.data = null;
  this.polygons = [];
  this.marks = [];
  this.inited = false;
  this.isShow = false;
}
LayerContrast.prototype = {
  init: function(response) {
    var that = this;
    that.inited = true;
    that.data = response.data.zone_low.concat(response.data.zone_high);
    //将“批发市场画进去”
    $.each(that.data, function(index, contrastItem) {

      // 绘制地图点相关
      var c = [contrastItem.zone_coords[0].lng, contrastItem.zone_coords[0].lat]; //中心点?
      var marker = mapTool.drawMarker({
        position: c,
        content: "<div class='market-name'>G:" + contrastItem.gaode_num + "<br>Y:" +
          contrastItem.yg_num + "</div>",
        offset: [-60, -60]
      });
      marker.content = contrastItem.rate;
      marker.title = '优供/高德超市对比';
      mapTool.on(marker, 'click', markerClick);

      // 绘制对比区域相关
      var lineArr = [];
      var firstPoint = null;
      $.each(contrastItem.zone_coords, function(index, point) {
        point = [point.lng, point.lat];
        if (!firstPoint) {
          firstPoint = point;
        }
        lineArr.push(point);
      });
      lineArr.push(firstPoint); //连结成线...

      //分块对比区域（这个是多边形）
      var polygon = mapTool.drawPolygon({
        position: lineArr,
        style: {
          strokeColor: points.contrastPoints.areaStyle[contrastItem.style_type].strokeColor, //线颜色
          strokeOpacity: 1, //线透明度
          strokeWeight: 3, //线粗细度
          fillColor: points.contrastPoints.areaStyle[contrastItem.style_type].fillColor, //填充颜色
          fillOpacity: 0.5 //填充透明度
        }
      });
      that.polygons.push(polygon);
      that.marks.push(marker);
    });
    $.each(that.polygons, function(index, polygon) {
      mapTool.show(polygon);
    });

    mapTool.map.on('zoomchange', function(e) {
      var _map = null;
      if (that.isShow) {
        if (mapTool.map.getZoom() > 11) { //放大,才显示
          _map = mapTool.map;
        }
        that.marks.forEach(function(mark) {
          mark.setMap(_map);
        });
      }
    });
    that.isShow = true;
  },
  show: function() {
    var that = this;
    that.isShow = true;
    if (mapTool.map.getZoom() > 11) { //放大,才显示
      $.each(that.marks, function(index, mark) {
        mapTool.show(mark);
      });
    }
    $.each(that.polygons, function(index, polygon) {
      mapTool.show(polygon);
    });
  },
  hide: function() {
    var that = this;
    if (that.marks && that.polygons) {
      that.isShow = false;
      $.each(that.marks, function(index, mark) {
        mapTool.hide(mark);
      });
      $.each(that.polygons, function(index, polygon) {
        mapTool.hide(polygon);
      });
    }
  },
  close: function() {
    this.hide();
    this.data = null;
    this.polygons = [];
    this.marks = [];
    this.inited = false;
    this.isShow = false;
  }
};



/**
 * 高德/优供/对比分布图
 * @return {[type]} [description]
 */
var MassLayer = function() {
  this.data = null;
  this.type = null;
  this.url = null;
  this.map = null;
  this.massLayer = null;
  this.inited = false;
};
MassLayer.prototype = {
  init: function(data, map, type) {
    var that = this;
    that.type = type;
    that.map = mapTool.map;
    that.inited = true;
    var massLayerCount = data.length;
    var massLevelLayer = [];
    if (data.ygPoints) {
      var ygMarket = mapTool.drawMassMarks({
        data: data.ygPoints,
        url: 'http://' + location.host + '/assets/img/level_markers/level_1.png',
        anchor: [3, 7],
        size: [10, 14],
        zIndex: 999
      });
      var gdMarket = mapTool.drawMassMarks({
        data: data.gdPoints,
        url: 'http://' + location.host + '/assets/img/level_markers/level_3.png',
        anchor: [3, 7],
        size: [10, 14],
        zIndex: 999
      });
      that.massLayer = [gdMarket, ygMarket];
      that.massLayer.forEach(function(item) {
        mapTool.on(item, 'mouseover', showDetails);
      });
    } else {
      that.massLayer = mapTool.drawMassMarks({
        data: data,
        url: 'http://' + location.host + '/assets/img/level_markers/level_' + that.type +
          '.png',
        anchor: [3, 7],
        size: [10, 14],
        zIndex: 999
      });
      mapTool.on(that.massLayer, 'mouseover', showDetails);
    }
  },
  show: function() {
    if (this.massLayer.length === 2) {
      this.massLayer.forEach(function(item) {
        mapTool.show(item);
      });
    } else {
      mapTool.show(this.massLayer);
    }
  },
  hide: function() {
    if (this.massLayer) {
      if (this.massLayer.length === 2) {
        this.massLayer.forEach(function(item) {
          mapTool.hide(item);
        });
      } else {
        mapTool.hide(this.massLayer);
      }
    }
  },
  clean: function() {
    var that = this;
    that.hide();
    that.data = null;
    that.type = null;
    that.url = null;
    that.map = null;
    that.massLayer = null;
    that.inited = false;
    $("#contrast").attr("checked", false);
    $("#contrastMarket").attr("checked", false);
  }
};

/**
 * 事件绑定
 */
function bindEvents() {

  $(".panel-contrast").on("click", "#lsh", function(e) {
    // 点击优供超市checkbox时事件
    var $target = $(e.target);
    if (!lshMassLayer.inited) {
      lshMassLayer.init(points.lshPoints, mapTool.map, "1");
    }
    if ($target.hasClass("close")) {
      lshMassLayer.show();
      $target.removeClass("close");
    } else {
      lshMassLayer.hide();
      $target.addClass("close");
    }
  }).on("click", "#amap", function(e) {
    // 点击高德超市时响应事件

    var $target = $(e.target);
    if (!amapMassLayer.inited) {
      amapMassLayer.init(points.gdPoints, mapTool.map, "3");
    }
    if ($target.hasClass("close")) {
      amapMassLayer.show();
      $target.removeClass("close");
    } else {
      amapMassLayer.hide();
      $target.addClass("close");
    }
  }).on("click", "#bmap", function(e) {
    // 点击百度超市时响应事件

    var $target = $(e.target);
    if (!bmapMassLayer.inited) {
      bmapMassLayer.init(points.bdPoints, mapTool.map, "0");
    }
    if ($target.hasClass("close")) {
      bmapMassLayer.show();
      $target.removeClass("close");
    } else {
      bmapMassLayer.hide();
      $target.addClass("close");
    }
  }).on("click", "#contrastMarket", function(e) {
    // 点击对比区内超市时响应事件

    var $target = $(e.target);
    if (!contrastLayer.inited) {
      contrastLayer.init(points.contrastPoints, mapTool.map, "1");
    }
    if ($target.hasClass("close")) {
      contrastLayer.show();
      $target.removeClass("close");
    } else {
      contrastLayer.hide();
      $target.addClass("close");
    }
  }).on("click", "#contrast", function(e) {
    // 点击优供高德对比时响应事件

    var url = getUrl("contrast");
    var options = {
      type: "contrast",
      url: url
    };
    var $target = $(e.target);
    if (!layerContrast.inited) {
      loading.show("数据加载中~");
      syncData(formatAmapData, options);
    }
    if ($target.hasClass("close")) {
      layerContrast.show();
      $target.removeClass("close");
      $('.contrast-market').addClass('can-check');
      $('.contrast-market').removeClass('btn-disabled');
      $("#contrastMarket").removeAttr("disabled");
    } else {
      layerContrast.hide();
      $target.addClass("close");
      $('.contrast-market').addClass('btn-disabled');
      $("#contrastMarket").prop("disabled", "disabled");
    }
  }).on("click", "#drawArea", function() {
    // 绘制主管区域响应事件

    if (!salesArea.inited) {
      var url = getUrl("saleZone");
      $http(url, "GET", {
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
  }).on("click", "#searchButton", function(e) {
    // 获取优供超市按钮响应事件

    var url = getUrl("lsh");
    var options = {
      region: $(".select-zone").val(),
      type: "lsh",
      url: url
    };
    loading.show("数据加载中~");
    if (lshMassLayer.inited) {
      lshMassLayer.clean();
    }
    syncData(formatLshData, options);
    $(".level-info").text("");
  }).on("click", "#searchAmapButton", function() {
    // 获取高德超市按钮响应事件

    var url = getUrl("amap");
    var options = {
      region: $(".select-zone").val(),
      type: "amap",
      url: url
    };
    loading.show("数据加载中~");
    if (amapMassLayer.inited) {
      amapMassLayer.clean();
    }
    points.gdPoints = [];
    syncData(formatAmapData, options);
  }).on("click", "#searchBmapButton", function() {
    // 获取百度超市按钮响应事件

    var url = getUrl("bmap");
    var options = {
      region: $(".select-zone").val(),
      type: "bmap",
      url: url
    };
    loading.show("数据加载中~");
    if (bmapMassLayer.inited) {
      bmapMassLayer.clean();
    }
    points.bdPoints = [];
    syncData(formatAmapData, options);
  });

  /**
   * 添加收起事件
   */

  $(".slide-up").on('click', function() {
    $(".panel-contrast").hide();
    $(".slide-down").show();
  });
  $(".slide-down").on('click', function() {
    $(".slide-down").hide();
    $(".panel-contrast").show();
  });
  $(".slide-up-legend").on('click', function() {
    $(".panel-legend").hide();
    $(".slide-down-legend").show();
  });
  $(".slide-down-legend").on('click', function() {
    $(".slide-down-legend").hide();
    $(".panel-legend").show();
  });
}

/**
 * 异步获取数据方法，提取ajax
 */
function syncData(callback, opt) {
  if (!opt) {
    return false;
  }
  var type = 'orderyesterday';
  $.ajax({
      url: opt.url,
      type: 'GET',
      dataType: 'JSON'
    })
    .done(function(response) {
      var result = response.resultList;
      if (opt.type === "lsh") {
        callback(result, type, opt.region, opt.type);
      } else if (opt.type === "amap" || opt.type === "bmap") {
        callback(response, opt.type);
      } else if (opt.type === "contrast") {
        var arr = [];
        points.contrastPoints.areaStyle = response.other;
        if (response.data.zone_low) {
          layerContrast.init(response);
          formatContrastData(response.data.zone_high.concat(response.data.zone_low));
        }
      } else {
        callback(response, opt.type);
      }
    })
    .fail(function() {
      Alert.show("数据获取错误，请重新查询！");
    })
    .always(function() {
      loading.hide();
    });
}

/**
 * @description marker点click响应事件
 * @param {Object} e 点击响应事件对象
 */
function markerClick(e) {
  var title = e.target.title || "超市信息异常";
  var id = e.target.id;
  mapTool.infoWindow.setContent(
    ['<h3>' + title + '</a></h3>', '<div>' + e.target.content + '</div>'].join('')
  );
  mapTool.infoWindow.open(mapTool.map, e.target.getPosition());
}

/**
 * @description 展示hover点相关信息
 */
function showDetails(e) {
  var title = e.data.market_name;
  var phone = e.data.contact_phone;
  mapTool.infoWindow.setContent(['<h3>' + title + '</h3>', '<h4>联系电话:' + phone + '</h4>'].join(''));
  mapTool.infoWindow.open(mapTool.map, new mapTool.AMap.LngLat(e.data.lng, e.data.lat));
}

function focusAndZoom(e) {
  map.setZoomAndCenter(16, e.target.getPosition());
}
/**
 * @description 由于部分请求需要传递相关的地域信息<region>,因此要进行区别对待同时返回
 * @params {String} type 获取的接口名称
 */
function getUrl(type) {
  var region = $(".select-zone").val();
  var url;
  if (type === "lsh") {
    if (region === "all") {
      url = URLS[type];
    } else {
      url = URLS[type] + "?zone_id=" + region;
    }
  } else {
    url = URLS[type] + "?region=" + region;
  }
  return url;
}

/**
 * @description 当切换地域时清空全部的存储数据和按钮状态
 */
function allClean() {
  salesArea.close();
  layerContrast.close();
  amapMassLayer.clean();
  bmapMassLayer.clean();
  lshMassLayer.clean();
  contrastLayer.clean();
  points = {
    gdPoints: [],
    lshPoints: [],
    bdPoints: [],
    contrastPoints: {
      yg: [],
      ygPoints: [],
      gd: [],
      gdPoints: [],
      areaStyle: null
    }
  };

  // 对比区相关按钮清空
  $("#contrast").addClass("close");
  $("#contrastMarket").addClass("close");
  $('.contrast-market').removeClass('can-check');
  $('.contrast-market').addClass('btn-disabled');
  $("#contrastMarket").prop("disabled", "disabled");

  // 高德超市相关按钮清空
  $("#amap").addClass("close");
  $("#amap").prop("disabled", "disabled");
  $("#amap").prop("checked", false);
  $(".amap").removeClass('can-check');
  $('.amap').addClass('btn-disabled');

  // 百度超市相关按钮清空
  $("#bmap").addClass("close");
  $("#bmap").prop("disabled", "disabled");
  $("#bmap").prop("checked", false);
  $(".bmap").removeClass('can-check');
  $('.bmap').addClass('btn-disabled');

  // 优供超市相关按钮清空
  $("#lsh").addClass("close");
  $("#lsh").prop("disabled", "disabled");
  $("#lsh").prop("checked", false);
  $(".lsh").removeClass('can-check');
  $('.lsh').addClass('btn-disabled');
}
