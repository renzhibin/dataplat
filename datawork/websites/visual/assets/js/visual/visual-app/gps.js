// 北京 天津 杭州仓库位置
var WAREHOUSES_POSITION = [{
    position: [
      [
        116.517659, 40.006212
      ],
      [
        116.519913, 40.006606
      ],
      [
        116.529869, 40.005801
      ],
      [
        116.530041, 40.004354
      ],
      [
        116.523453, 40.002875
      ],
      [
        116.522852, 40.002678
      ],
      [
        116.522359, 40.001774
      ],
      [
        116.52208, 40.001609
      ],
      [
        116.518689, 40.00133
      ],
      [
        116.517552, 40.001248
      ],
      [
        116.517338, 40.003549
      ],
      [
        116.517509, 40.004042
      ],
      [
        116.517681, 40.004667
      ]
    ],
    name: "DC南皋",
    center: [116.517659, 40.006212]
  }, {
    position: [
      [
        117.003643, 39.115615
      ],
      [
        117.004694, 39.115482
      ],
      [
        117.007849, 39.115474
      ],
      [
        117.007473, 39.112677
      ],
      [
        117.002752, 39.11271
      ]
    ],
    name: "DC09",
    center: [117.004694, 39.115482]
  },
  // 杭州仓库没有围栏信息,只有一个中心点信息,因此只做中心标注
  {
    name: "DC55",
    center: [120.308824, 30.330043]
  }
];

var layerTransPoint, layerMarket, layerReceipt, layerWholesale;
var map, mapObj, infoWindow;
//做数据预处理
var formatData = {
  startTime: 1466230853,
  markets: [],
  stillPoints: [],
  movePoints: [],
  payPoints: [],
  wholesale: []
};

// 地图初始化加载
var mapObj = new AMap.Map('container', {
  resizeEnable: true,
  zoom: 4
});
var map = mapObj; //兼容一下
var scale = new AMap.Scale({ //比例尺
  visible: true
});
map.addControl(scale);
//超市信息弹窗
var infoWindow = new AMap.InfoWindow();


function Action() {

  // 填入司机信息
  transUserInsert();
  // tpl中数据处理
  formatTplData();
  //增加是否显示停留点的显示
  whetherShowStill();
  // 绘制仓库
  drawWarehouse();
  // 各图层初始化
  layerInit();
  // 事件绑定
  bindEvents();
  // 移动端访问时的适配
  refreshRem();
}



// 超市点
function LayerMarket() {
  this.marks = [];
  this.lines = [];
  this.seqIcons = [];
  this.init();
}
// 超市点信息
LayerMarket.prototype = {
  init: function() {
    var that = this;
    //将超市显示上去
    var marketsInfo = formatData.markets;
    var artM = template.compile([
      '<p>id:&nbsp;{{market.info.address_id}}</p>',
      '<p>地址:&nbsp;{{market.info.address}}</p>',
      '<p>联系人:&nbsp;{{market.info.contact_name}}</p>',
      '<p>联系电话:&nbsp;{{market.info.contact_phone}}</p>',
      '<p>签收时间:&nbsp;{{market.info.arrived_at | stampFormat:"yyyy-MM-dd hh:mm:ss"}}',
      '<p>TMS排线顺序:{{market.seq}}</p>',
      '{{ if market.status ===1 }}<h4 style="color:red">司机未签收</h4>{{else if market.status ===0}}<h4 style="color:green">司机已签收</h4>{{ /if }}'
    ].join(''));
    var tipWindow = new AMap.InfoWindow({
      offset: new AMap.Pixel(0, 0)
    });
    var timer = null;

    function showDistance(e) {
      if (timer) {
        clearTimeout(timer);
        timer = null;
      }
      tipWindow.setContent(e.target.content);
      tipWindow.open(map, e.lnglat);
    }

    function hideDistance(e) {
      clearTimeout(timer);
      timer = setTimeout(function() {
        tipWindow.close();
        timer = null;
      }, 500);
    }

    for (var i = 0; i < marketsInfo.length; i += 1) {
      var market = marketsInfo[i];
      var marker;
      var seqIcon;
      if (market.status == 1) {
        marker = new AMap.Marker({
          icon: "/assets/img/gps/market-not-served-icon.png",
          position: [market.pos.lng, market.pos.lat],
          title: market.info.market_name,
          draggable: true,
          map: mapObj,
          topWhenMouseOver: true,
        });
      } else {
        marker = new AMap.Marker({
          icon: "/assets/img/gps/market-icon.png",
          position: [market.pos.lng, market.pos.lat],
          title: market.info.market_name,
          draggable: true,
          map: mapObj,
          topWhenMouseOver: true,
        });
      }
      seqIcon = new AMap.Marker({
        content: "<div class='seq-icon'>" + market.seq + "</div>",
        position: [market.pos.lng, market.pos.lat],
        title: market.info.market_name,
        draggable: true,
        // map: mapObj,
        zIndex: 300,
        topWhenMouseOver: true,
      });
      marker.content = artM({
        market: market
      });
      marker.id = market.info.address_id;
      marker.title = market.info.market_name;

      //处理“与超市最近拉卡拉打点”--停留点
      if (market.near) {
        if (market.near.lng != -1 && market.near.lat != -1) {
          var lineArr = [
            [market.pos.lng, market.pos.lat],
            [market.near.lng, market.near.lat]
          ];
          var polyline = new AMap.Polyline({
            path: lineArr, //设置线覆盖物路径
            strokeColor: "#3366FF", //线颜色
            strokeOpacity: 1, //线透明度
            strokeWeight: 5, //线宽
            strokeStyle: 'dashed', //线样式
            strokeDasharray: [10, 5] //补充线样式
          });
          //避免停留点出现问题是出现连线的问题
          if (polyline.getLength() < 150000) {
            var marketName = market.info.market_name || " ";
            polyline.content = [
              '<p>目标超市:' + marketName + '</p>',
              '<p>最近停留点距离: ' + polyline.getLength() + '米</p>',
              '<p>是否匹配: ' + (polyline.getLength() < 1000 ? '匹配' : '不匹配（超过1KM）') + '</p>'
            ].join('');
            polyline.setMap(null);
            polyline.on('mouseover', showDistance);
            polyline.on('mouseout', hideDistance);
            this.lines.push(polyline);
          }

        }
      }

      //给Marker绑定单击事件
      marker.on('click', marketMarkerClick);
      marker.on('dblclick', focusAndZoom);

      this.marks.push(marker);
      this.seqIcons.push(seqIcon);
    }
    map.on('zoomchange', function(e) {
      if (!that.isShow) {
        return;
      }
      var _map = null;
      if (map.getZoom() > 11) { //放大,才显示箭头
        _map = map;
      }
      that.lines.forEach(function(line) {
        line.setMap(_map);
      });
    });
    $('#menu .show-market').prop('checked', true);
    that.isShow = true;
  },
  isShow: false,
  show: function() {
    var that = this;
    that.isShow = true;
    this.marks.forEach(function(mark) {
      mark.setMap(map);
    });
    this.lines.forEach(function(line) {
      line.setMap(map);
    });
  },
  hide: function() {
    var that = this;
    that.isShow = false;
    this.marks.forEach(function(mark) {
      mark.setMap(null);
    });
    this.lines.forEach(function(line) {
      line.setMap(null);
    });
  },
  showSeq: function() {
    var that = this;
    that.isShowSeq = true;
    this.seqIcons.forEach(function(mark) {
      mark.setMap(map);
    });
  },
  hideSeq: function() {
    var that = this;
    that.isShowSeq = false;
    this.seqIcons.forEach(function(mark) {
      mark.setMap(null);
    });
  },
  close: function() {}
};

// 移动点
function LayerTransPoint() {
  this.moveMarks = [];
  this.stillMarks = [];
  this.lines = [];
  this.animateMarks = [];
  this.beginEndMoveMarks = [];
  this.init();
}
LayerTransPoint.prototype = {
  init: function() {
    this._initLine();
    //            this._initAnimate();
    this._initBeginEndMovePoints();
  },
  _initLine: function() {
    var lineArr = [];
    formatData.movePoints.forEach(function(point) {
      lineArr.push([point.pos.lng, point.pos.lat]);
    });
    var polyline = new AMap.Polyline({
      path: lineArr,
      strokeColor: "#0091ff", //线颜色
      strokeOpacity: 0.7, //线透明度
      strokeWeight: 6, //线宽
      fillColor: "#1791fc", //填充色
      fillOpacity: 0.75 //填充透明度
    });
    polyline.setMap(map);
    this.lines.push(polyline);
    $('#menu .show-transpoint').prop('checked', true);

  },
  _initBeginEndMovePoints: function() {
    var beginEndMoveMarks = this.beginEndMoveMarks;
    var len = formatData.movePoints.length;
    var marker;
    if (len !== 0) {
      drawMarker(formatData.movePoints[0], "begin", "起", "起点");
      drawMarker(formatData.movePoints[len - 1], "end", "终", "终点");
      this.showBeginEndMoves();
      //2016-10-25 修复map.setFitView导致的无法正常聚焦问题，原因待查询 TODO
      var lngLat = new AMap.LngLat(formatData.movePoints[len - 1].pos.lng, formatData.movePoints[
          len - 1].pos
        .lat);
      map.setZoomAndCenter(10, lngLat);
    }

    function drawMarker(point, type, name, title) {
      var artM = template.compile(
        '<div><h3>时间: {{point.info.start_time | stampFormat:"yyyy-MM-dd hh:mm:ss"}}</h3></div>'
      );
      marker = new AMap.Marker({
        content: '<div class="' + type + '-move-point">' + name + '</div>',
        position: [point.pos.lng, point.pos.lat],
        title: title,
        topWhenMouseOver: true,
        offset: new AMap.Pixel(-3, -3),
        zIndex: 99999
      });
      marker.content = artM({
        point: point
      });
      marker.title = title;
      marker.on('click', markerClick);
      beginEndMoveMarks.push(marker);
    }
  },
  _initMove: function() {
    var that = this;
    ///再画上面的点
    var moveMarks = [];

    this.moveMarks = moveMarks;

    var artM = template.compile(
      '<div><h3>时间: {{point.info.start_time | stampFormat:"yyyy-MM-dd hh:mm:ss"}}</h3><p>地点:--</p></div>'
    );
    for (var i = 0; i < formatData.movePoints.length; i += 1) {
      var point = formatData.movePoints[i];
      var nextPoint = formatData.movePoints[i + 1];

      var marker;

      var deg = 0;
      if (nextPoint) {
        var diffX = nextPoint.pos.lat - point.pos.lat; //经度
        var diffY = nextPoint.pos.lng - point.pos.lng; //纬度
        if (diffY === 0) {
          if (diffX > 0) {
            deg = 90;
          } else if (diffX < 0) { // < 0
            deg = -90;
          } else {
            deg = 0;
          }
        } else {
          //http://www.cppblog.com/fwxjj/archive/2012/05/17/175147.aspx
          deg = 180 * Math.atan2(diffY, diffX) / Math.PI;
        }
      }

      marker = new AMap.Marker({
        content: '<div class="point-move iconfont" style="transform:rotate(' + deg +
          'deg);"><img style="width:10px;" src="/assets/img/gps/way-icon2.png" alt="" /></div>',
        icon: "/assets/img/gps/way-icon.png",
        position: [point.pos.lng, point.pos.lat],
        title: '移动点:' + point.info.start_time,
        offset: new AMap.Pixel(-3, -3),
        zoom: 14
      });
      marker.content = artM({
        point: point
      });
      marker.title = '[移动点]' + i;

      //给Marker绑定单击事件
      marker.on('click', markerClick);

      moveMarks.push(marker);
    }
    map.on('zoomchange', function(e) {
      if (!that.isShowMove) {
        return;
      }
      // var _map = null;
      var zoom = map.getZoom();
      // if(zoom >= 13) {    //放大,才显示箭头
      //     _map = map;
      // }
      moveMarks.forEach(function(marker, index) {
        marker.setMap(null);
        if (zoom <= 10) {
          if (index % 15 === 0) {
            marker.setMap(map);
          }
        } else if (zoom > 10 && zoom <= 13) {
          if (index % 10 === 0) {
            marker.setMap(map);
          }
        } else if (zoom > 13 && zoom <= 15) {
          if (index % 5 === 0) {
            marker.setMap(map);
          }
        } else {
          marker.setMap(map);
        }
      });

    });
    //zoom级别变化时,如果zoom>10则不显示轨迹方向,只有偏大时才显示
    $('#menu .show-transpoint-move').prop('checked', true);
    this._isInitMove = true;
  },
  _initStill: function() {
    //将中途的静止的点打上
    var artS = template.compile([
      '<div>',
      '<p>开始时间: {{point.info.start_time | stampFormat:"yyyy-MM-dd hh:mm:ss"}}</p>',
      '<p>结束时间: {{point.info.end_time | stampFormat:"yyyy-MM-dd hh:mm:ss"}}</p>',
      '<p>停留时长:{{div_time_span | secFormat}}</p>',
      '</div>'
    ].join(''));


    for (var i = 0; i < formatData.stillPoints.length; i += 1) {
      var point = formatData.stillPoints[i];
      var lastPoint = formatData.stillPoints[i - 1] || formatData.stillPoints[i]; //如果没有就用自己

      var marker;

      marker = new AMap.Marker({
        icon: "/assets/img/gps/still-icon.png",
        position: [point.pos.lng, point.pos.lat],
        title: '留停点:' + i,
        topWhenClick: true,
        topWhenMouseOver: true,
        raiseOnDrag: true,
        draggable: true,
        map: null
      });
      marker.content = artS({
        point: point,
        div_time_span: (0 + point.info.end_time) - (0 + point.info.start_time)
      });
      marker.title = '[停留点]' + i;

      //给Marker绑定单击事件
      marker.on('click', function(e) {
        markerClick(e);
        stillPointInfo = formatData.stillPoints[(e.target.title).substring(5)];
      });

      this.stillMarks.push(marker);
    }
    var that = this;
    map.on('zoomchange', function(e) {
      if (!that.isShowStill) {
        return;
      }

      that.stillMarks.forEach(function(marker) {
        marker.setMap(map);
      });
    }); //zoom级别变化时,如果zoom>10则不显示轨迹方向,只有偏大时才显示
    $('#menu .show-transpoint-still').prop('checked', true);
    this._isInitStill = true;
    //
  },
  _isInitAnimate: false,
  _initAnimate: function() {
    //循环变色,显示轨迹
    var moveMarksLnglat = [];
    for (var i = 0; i < formatData.movePoints.length; i += 1) {
      var point = formatData.movePoints[i];
      moveMarksLnglat.push(new AMap.LngLat(point.pos.lng, point.pos.lat));
    }

    //方案1: 以下是用移动方向来颜色区分

    //方案2: 以下是用高德的移动动画效果（好像消耗CPU有点大）
    var currentMoveIndex = 0;
    var moveLength = moveMarksLnglat.length;
    var marker;

    marker = new AMap.Marker({
      'icon': '/assets/img/gps/trans-type-2.png?version={/$version/}',
      autoRotation: true,
      offset: new AMap.Pixel(-5, -5),
      map: mapObj,
    });
    marker.moveAlong(moveMarksLnglat, //必须是lnglat对象数组
      5000, //km/H
      function(k) {
        return k;
      },
      false);
    this.animateMarks.push(marker);
    $('#menu .show-transpoint-animate').prop('checked', true);
    this._isInitAnimate = true;
  },
  show: function() {
    this.lines.forEach(function(mark) {
      mark.setMap(map);
    });
  },
  hide: function() {
    this.lines.forEach(function(mark) {
      mark.setMap(null);
    });
  },
  isShowMove: false,
  _isInitMove: false,
  showMove: function() {
    if (!this._isInitMove) {
      this._initMove();
    }
    this.isShowMove = true;
    var zoom = map.getZoom();
    this.moveMarks.forEach(function(mark, index) {
      if (zoom <= 10) {
        if (index % 15 === 0) {
          mark.setMap(map);
        }
      } else if (zoom > 10 && zoom <= 13) {
        if (index % 10 === 0) {
          mark.setMap(map);
        }
      } else if (zoom > 13 && zoom <= 15) {
        if (index % 5 === 0) {
          mark.setMap(map);
        }
      } else {
        mark.setMap(map);
      }

    });
  },
  hideMove: function() {
    this.isShowMove = false;
    this.moveMarks.forEach(function(mark) {
      mark.setMap(null);
    });
  },
  showBeginEndMoves: function() {
    this.beginEndMoveMarks.forEach(function(mark) {
      mark.setMap(map);
    });
  },
  _isInitStill: false,
  isShowStill: false,
  showStill: function() {
    if (!this._isInitStill) {
      this._initStill();
    }
    this.isShowStill = true;
    this.stillMarks.forEach(function(mark) {
      mark.setMap(map);
    });
  },
  hideStill: function() {
    this.isShowStill = false;
    this.stillMarks.forEach(function(mark) {
      mark.setMap(null);
    });
  },
  showAnimate: function() {
    if (!this._isInitAnimate) {
      this._initAnimate();
    }
    this.animateMarks.forEach(function(mark) {
      mark.setMap(map);
    });
  },
  hideAnimate: function() {
    if (this._isInitAnimate) {
      this.animateMarks.forEach(function(mark) {
        mark.setMap(null);
      });
    }
  },
  close: function() {}
};



// 签收点
function LayerReceipt() {
  this.marks = [];
  this.init();
}
LayerReceipt.prototype = {
  init: function() {
    //将签收收显示上去
    var payPoints = formatData.payPoints;
    var artP = template.compile([
      '<p>配送超市:<a target="_blank" href="http://mis.market.lsh123.com/marketmanage/market/view?address_id={{payInfo.info.id}}">{{payInfo.info.market_name}}</a></p>',
      '<p>订单号:<a target="_blank" href="http://mis.market.lsh123.com/order/user/view?order_id={{payInfo.info.shipping_order_id}}">{{payInfo.info.shipping_order_id}}</a></p>',
      '<p>运单号:<a target="_blank" href="http://mis.market.lsh123.com/order/waybill/view?waybill_no={{payInfo.info.waybill_no}}">{{payInfo.info.waybill_no}}</a></p>',
      '<p>签收时间:{{payInfo.info.time | stampFormat:"yyyy-MM-dd hh:mm:ss"}}</p>',
      '<p>最近停留点时间:{{payInfo.near.time | stampFormat:"yyyy-MM-dd hh:mm:ss"}}</p>',
      '<p>配送时间:{{deliver_time | secFormat}}</p>'
    ].join(''));
    for (var i = 0; i < payPoints.length; i += 1) {
      var payInfo = payPoints[i];
      if (payInfo.pos.lng !== 0 || payInfo.pos.lat !== 0) {


        var marker;

        marker = new AMap.Marker({
          icon: "/assets/img/gps/sign-icon.png",
          position: [payInfo.pos.lng, payInfo.pos.lat],
          title: '签收',
          map: mapObj,
          topWhenMouseOver: true,
          draggable: true,
        });
        //如果签收时间在接近点,则是异常的
        if (payInfo.info.time < payInfo.near.time) {
          marker.setLabel({
            offset: new AMap.Pixel(20, 20), //修改label相对于maker的位置
            content: "时间异常"
          });
        }
        marker.content = artP({
          payInfo: payInfo,
          deliver_time: payInfo.info.time - payInfo.info.startTime
        });
        marker.title = '运单签收';

        //给Marker绑定单击事件
        marker.on('click', markerClick);
        marker.on('dblclick', focusAndZoom);


        this.marks.push(marker);
      }
    }
    $('#menu .show-receipt').prop('checked', true);

  },
  show: function() {
    this.marks.forEach(function(mark) {
      mark.setMap(map);
    });
  },
  hide: function() {
    this.marks.forEach(function(mark) {
      mark.setMap(null);
    });
  },
  close: function() {}
};

// 批发市场
function LayerWholesale() {
  this.polygons = [];
  this.marks = [];
  this.guard = [];
  this.init();
}
LayerWholesale.prototype = {
  init: function() {
    var that = this;
    //将“批发市场画进去”
    formatData.wholesale.forEach(function(wholesaleItem) {
      var c = [wholesaleItem.pos.lng, wholesaleItem.pos.lat]; //中心点?
      var marker = new AMap.Marker({
        icon: "/assets/img/gps/wholesale-icon.png",
        position: c,
        title: '批发市场',
        map: map
      });
      marker.content = wholesaleItem.name;
      marker.title = '批发市场';
      marker.on('click', markerClick);
      marker.on('dblclick', focusAndZoom);

      var lineArr = [];
      var firstPoint = null;
      wholesaleItem.polyline.forEach(function(point) {
        point = [point.lng, point.lat];
        if (!firstPoint) {
          firstPoint = point;
        }
        lineArr.push(point);
      });
      lineArr.push(firstPoint); //连结成线...

      //电子围栏画线（这个是多边形）
      var polygon = new AMap.Polygon({
        path: lineArr, //设置线覆盖物路径
        strokeColor: "#F33", //线颜色
        strokeOpacity: 1, //线透明度
        strokeWeight: 3, //线粗细度
        fillColor: "#ee2200", //填充颜色
        fillOpacity: 0.35 //填充透明度
      });

      var near = false;
      var nearPoint = null;
      formatData.stillPoints.forEach(function(point) {
        var lnglat = new AMap.LngLat(point.pos.lng, point.pos.lat);

        var distance = lnglat.distance(lineArr);
        if ( /*distance < 100 || */ polygon.contains(lnglat)) { //100m 或者 包含多边形内
          near = true;
          nearPoint = lnglat;
        }
      });
      if (!near) { //检查超市在不在围栏内
        formatData.markets.forEach(function(point) {
          var lnglat = new AMap.LngLat(point.pos.lng, point.pos.lat);

          var distance = lnglat.distance(lineArr);
          if ( /*distance < 100 || */ polygon.contains(lnglat)) { //100m 或者 包含多边形内
            near = true;
            nearPoint = lnglat;
          }
        });
      }
      if (near) {
        //画一个警告点
        var rippleMark; //以轨迹距离点为参照

        rippleMark = new AMap.Marker({
          content: '<div class="dot-ripple1 dot-rider"></div>',
          position: nearPoint,
          offset: new AMap.Pixel(-5, -5),
          map: mapObj,
        });
        that.marks.push(rippleMark);
      }

      that.polygons.push(polygon);
      that.marks.push(marker);
    });
    map.on('zoomchange', function(e) {
      var _map = null;
      if (that.isShow) {
        if (map.getZoom() > 11) { //放大,才显示围栏
          _map = map;
        }
        that.polygons.forEach(function(marker) {
          marker.setMap(_map);
        });
      }
    }); //zoom级别变化时,如果zoom>10则不显示轨迹方向,只有偏大时才显示
    this.isShow = true;
    $('#menu .show-wholesale').prop('checked', true);
  },
  isShow: false,
  show: function() {
    this.isShow = true;
    this.marks.forEach(function(mark) {
      mark.setMap(map);
    });
    this.polygons.forEach(function(line) {
      line.setMap(map);
    });
    this.guard.forEach(function(line) {
      line.setMap(map);
    });
  },
  hide: function() {
    this.isShow = false;
    this.marks.forEach(function(mark) {
      mark.setMap(null);
    });
    this.polygons.forEach(function(line) {
      line.setMap(null);
    });
    this.guard.forEach(function(line) {
      line.setMap(null);
    });
  },
  close: function() {}
};


function Layer() {
  this.marks = [];
}
Layer.prototype = {
  init: function() {

  },
  show: function() {
    this.marks.forEach(function(mark) {
      mark.setMap(map);
    });
  },
  hide: function() {
    this.marks.forEach(function(mark) {
      mark.setMap(null);
    });
  },
  close: function() {}
};


// 事件绑定
function bindEvents() {
  //#menu 操作
  $('#menu').on('click', '.show-market', function(e) {
    var $this = $(e.currentTarget);
    if ($this.prop('checked')) {
      layerMarket.show();
    } else {
      layerMarket.hide();
    }
  }).on('click', '.show-receipt', function(e) {
    var $this = $(e.currentTarget);
    if ($this.prop('checked')) {
      layerReceipt.show();
    } else {
      layerReceipt.hide();
    }
  }).on('click', '.show-transpoint', function(e) {
    var $this = $(e.currentTarget);
    if ($this.prop('checked')) {
      layerTransPoint.show();
    } else {
      layerTransPoint.hide();
    }
  }).on('click', '.show-transpoint-move', function(e) {
    var $this = $(e.currentTarget);
    if ($this.prop('checked')) {
      layerTransPoint.showMove();
    } else {
      layerTransPoint.hideMove();
    }
  }).on('click', '.show-transpoint-still', function(e) {
    var $this = $(e.currentTarget);
    if ($this.prop('checked')) {
      layerTransPoint.showStill();
    } else {
      layerTransPoint.hideStill();
    }
  }).on('click', '.show-transpoint-animate', function(e) {
    var $this = $(e.currentTarget);
    if ($this.prop('checked')) {
      layerTransPoint.showAnimate();
    } else {
      layerTransPoint.hideAnimate();
    }
  }).on('click', '.show-wholesale', function(e) {
    var $this = $(e.currentTarget);
    if ($this.prop('checked')) {
      layerWholesale.show();
    } else {
      layerWholesale.hide();
    }
  }).on('click', '.show-seqIcons', function(e) {
    var $this = $(e.currentTarget);
    if ($this.prop('checked')) {
      layerMarket.showSeq();
    } else {
      layerMarket.hideSeq();
    }
  }).on("click", ".menu-retract-btn", function(e) {
    $("#menu").hide();
    $(".mobile-menu").show();
  });

  // 操控板事件绑定
  $(".panel-query").on("click", ".search-btn", function(e) {
    var name = $("input[name=trans_name]").val();
    var bill = $("input[name=trans_bill]").val();
    var phone = $("input[name=trans_phone]").val();
    var startDate = $("input[name=start_date]").val();
    var endDate = $("input[name=end_date]").val();
    var now = getDate();

    if (startDate !== "") {
      if (startDate != endDate || startDate != now) {
        $("option").val("3");
      }
    }

    if (name === "" && bill === "" && phone === "") {
      Alert.show("请输入查询条件！");
    } else {
      if (name !== "") {
        if (phone !== "" || bill !== "") {
          $("#searchBox").submit();
        } else {
          $.ajax({
              url: '/gps/GetPhone',
              type: 'GET',
              dataType: 'json',
              data: {
                name: name
              },
            })
            .done(function(response) {
              if (response.length > 1) {
                var $cellphone = $("#cellphone");
                var $modal = $(".modal");
                var options = "";
                response.forEach(function(driver) {
                  var option = "<div class='cell-option'><input type='radio' id='" +
                    driver
                    .cellphone + "' name='cellphone' value='" + driver.cellphone + "' />" +
                    "<label for='" + driver.cellphone + "'>" + driver.cellphone +
                    "</label></div>";
                  options += option;
                });
                $cellphone.html(options);
                $modal.removeClass('hidden');
              } else {
                $("#searchBox").submit();
              }
            })
            .fail(function() {
              Alert.show("查询失败");
            });
        }

      } else {
        $("#searchBox").submit();
      }
    }
  }).on("click", ".query-retract-btn", function() {
    $(".panel-query").hide();
    $(".mobile-menu").show();
  });

  /**
   * 移动端交互
   */

  $(".mobile-menu").on("click", ".action", function() {
    $(".mobile-menu").find(".panel").toggle();
  }).on("click", ".menu-item", function(e) {
    var type = $(e.currentTarget).data("type");
    if (type === "searchInfo") {
      $(".panel-query").show();
    } else if (type === "driver") {
      $(".driver-list-content").show();
    } else if (type === "label") {
      $("#menu").show();
    }
    $(".mobile-menu").toggle();
  });



  $("#searchByPhone").on("click", function(e) {
    var cellphone = $("input[name=cellphone]:checked").val();
    if (cellphone === undefined) {
      Alert.show("请选择一个司机电话！");
    } else {
      $("input[name=trans_phone]").val(cellphone);
      $("#searchBox").submit();
    }
  });

  /**
   * 今日在运司机列表交互
   */
  $("#driverList").on("click", ".driver-search-btn", function(e) {
    $target = $(e.target);
    $driverList = $(".driver-list-content");
    var cellphone = $("input[name=driver]:checked").val();
    var name = $("input[name=driver]:checked").data("name");
    if (cellphone === undefined) {
      Alert.show("您尚未选择司机，请选择后再查看！");
    } else {
      $target.addClass("close");
      $driverList.slideUp();
      searchByPhone(cellphone, name);
    }
  }).on("click", ".driver-retract-btn", function() {
    $(".driver-list-content").hide();
    $(".mobile-menu").show();

  });
  $(".driver-list-btn").on("click", function(e) {
    $target = $(e.target);
    $driverList = $(".driver-list-content");
    if ($target.hasClass("close")) {
      $target.removeClass("close");
      $driverList.slideDown();
    } else {
      $target.addClass("close");
      $driverList.slideUp();
    }
  });
  // 查询超市位置框显示
  $(".market-search-btn").on("click", function(e) {
    $(".info-market-search").toggle();
  });
  $(".search-submit-btn").on("click", function(e) {
    var addressId = $(".market-address-id").val().trim();
    if (addressId !== "") {
      searchMarkets(addressId);
    } else {
      Alert.show("请输入超市addressId!");
    }
  });

  $("select[name=search_scence]").on("change", function(e) {
    var value = $("select[name=search_scence]").val();
    $(".search-value").val("");
    $(".search-label").hide();
    if (value === "1") {
      $("input[name=version]").val("3");
      setDate();
      $(".search-label").show();
      $(".search-order").hide();
    } else if (value === "2") {
      $("input[name=version]").val("2");
      $(".search-order").show();
    }
  });

}

// 辅助方法
/**
 * 在运司机列表生成与插入
 */
function transUserInsert() {
  var len = transUserList.length;
  var driverListStr = [];
  transUserList.forEach(function(item) {
    var driverStr = [
      "<div class='driver-list-item'>",
      "<label for='" + item.cellphone + "'>",
      "<div>姓名:",
      item.name,
      "</div>",
      "<div>电话:",
      item.cellphone,
      "</div>",
      "<input id='" + item.cellphone + "' class='driver-list-radio' data-name='" + item.name +
      "' name='driver' type='radio' value='" + item.cellphone + "'>",
      "</label>",
      "</div>"
    ].join("");
    driverListStr.push(driverStr);
  });
  $(".driver-list").append(driverListStr.join(""));
}


function formatTplData() {
  //1. 超市信息
  smartyData.waybillInfos.forEach(function(info) {
    if (info.shop_longitude && info.shop_latitude) {
      formatData.markets.push({
        info: info,
        pos: {
          lng: info.shop_longitude,
          lat: info.shop_latitude
        },
        near: {
          lng: info.nearest_longitude, //TODO ??
          lat: info.nearest_latitude,
          time: +info.nearest_time
        },
        seq: info.seq,
        status: info.waybill_st
      });
    }
  });
  formatData.markets.sort(function(a, b) { //按时间给超市排序
    return a.near.time - b.near.time;
  });

  //2. 停留点/移动点
  smartyData.gpsPosition.forEach(function(info) {
    if (info.pos_type == 1) { //停留点
      formatData.stillPoints.push({
        info: {
          stay_span: +info.stay_span,
          start_time: info.start_time,
          end_time: info.end_time
        },
        pos: {
          lng: info.longitude,
          lat: info.latitude
        },
        remarkinfo: info.remarkinfo
      });
    } else if (info.pos_type == 2) { //移动点（含停留点）
    }
    formatData.movePoints.push({
      info: {
        stay_span: +info.stay_span,
        start_time: info.start_time
      },
      pos: {
        lng: info.longitude,
        lat: info.latitude
      }
    });
  });
  //3. 签收点
  smartyData.waybillInfos.forEach(function(info) {
    //由于position为-1、0时为记录错误
    if (info.nearest_longitude !== -1 && info.nearest_latitude != -1 && info.nearest_longitude !==
      0 && info.nearest_latitude !== 0) {
      formatData.payPoints.push({
        info: {
          market_name: info.market_name,
          startTime: +info.shipped_at,
          id: info.address_id,
          time: +info.arrived_at,
          shipping_order_id: info.shipping_order_id,
          waybill_no: info.waybill_no
        },
        pos: {
          lng: info.nearest_longitude,
          lat: info.nearest_latitude
        },
        near: {
          lng: info.nearest_longitude,
          lat: info.nearest_latitude,
          time: +info.nearest_time
        }
      });
    }

  });
  //4. 电子围栏信息
  for (var key in smartyData.wholesalerZone) {
    if (smartyData.wholesalerZone.hasOwnProperty(key)) {
      var info = smartyData.wholesalerZone[key];
      if (info.length === 0) {
        continue;
      }
      var polyline = [];
      info.forEach(function(p) {
        polyline.push({
          lng: p.zone_longitude,
          lat: p.zone_latitude
        });
      });
      formatData.wholesale.push({
        name: key,
        pos: {
          lng: info[0].zone_longitude,
          lat: info[1].zone_latitude
        },
        polyline: polyline
      });

    }
  }
}

// 绘制仓库位置
function drawWarehouse() {
  WAREHOUSES_POSITION.forEach(function(ware) {
    if (ware.position) {
      var polygon = new AMap.Polygon({
        path: ware.position, //设置多边形边界路径
        strokeColor: "#ff9102", //线颜色
        strokeOpacity: 1, //线透明度
        strokeWeight: 3, //线宽
        fillColor: "#ffd802", //填充色
        fillOpacity: 0.5 //填充透明度
      });
      polygon.setMap(map);
    }
    var marker = new AMap.Marker({
      position: ware.center,
      offset: new AMap.Pixel(-12, -12),
      map: mapObj,
      content: '<div class="ware-house">' + ware.name + '</div>'
    });
  });
}

/**
 * 各点图层初始化
 */
function layerInit() {
  //显示送输路线中的打点（移动+停留）
  layerTransPoint = new LayerTransPoint();
  //显示送货订单的“超市信息”
  layerMarket = new LayerMarket();
  //显示签收单点信息
  layerReceipt = new LayerReceipt();
  //显示电子围栏信息
  layerWholesale = new LayerWholesale();
}

//当查询状态为实时轨迹时直接显示出停留点
function whetherShowStill() {
  if (scence === 1) {
    $(".show-transpoint-still").trigger("click");
  }
}

// 移动端访问时的适配
function refreshRem() {
  var width = document.documentElement.getBoundingClientRect().width;
  if (width > 768) { // 最大宽度
    width = 768;
  }
  var rem = width / 10; // 将屏幕宽度分成10份， 1份为1rem
  document.documentElement.style.fontSize = rem + 'px';
}

//切换为查询司机时填充默认时间
function getDate() {
  var date = new Date();
  var month = date.getMonth() > 10 ? (date.getMonth() + 1) : "0" + (date.getMonth() + 1);
  var dateString = date.getFullYear() + "-" + month + "-" + date.getDate();
  return dateString;
}

function setDate() {
  var date = new Date();
  var month = date.getMonth() > 10 ? (date.getMonth() + 1) : "0" + (date.getMonth() + 1);
  var dateString = date.getFullYear() + "-" + month + "-" + date.getDate();
  $("input[name=start_date]").val(dateString);
  $("input[name=end_date]").val(dateString);
}

//选择司机列表中司机后实现查询
function searchByPhone(cellphone, name) {
  $(".search-value").val("");
  $("input[name=trans_phone]").val(cellphone);
  $("input[name=trans_name]").val(name);
  $("option").val("1");
  $("#searchBox").submit();
}

// 查找相关超市
function searchMarkets(addressId) {
  var found = false;
  $.each(formatData.markets, function(index, market) {
    if (market.info.address_id == addressId) {
      found = true;
      var lngLat = new AMap.LngLat(market.pos.lng, market.pos.lat);
      map.setZoomAndCenter(16, lngLat);
      return false;
    }
  });
  if (!found) {
    Alert.show("未找到相关超市!");
  }
}

// 地图辅助相关事件
function markerClick(e) {
  var title = e.target.title || "超市信息异常";
  var id = e.target.id;
  infoWindow.setContent(
    ['<h3>' + title + '</a></h3>', '<div>' + e.target.content + '</div>'].join('')
  );
  infoWindow.open(mapObj, e.target.getPosition());
}

function marketMarkerClick(e) {
  var title = e.target.title || "超市信息异常";
  var id = e.target.id;
  infoWindow.setContent(
    [
      '<h3><a target="_blank" href="http://mis.market.lsh123.com/marketmanage/market/view?address_id=' +
      id + '">' + title + '</a></h3>', '<div>' + e.target.content + '</div>'
    ].join('')
  );
  infoWindow.open(mapObj, e.target.getPosition());
}

function focusAndZoom(e) {
  mapObj.setZoomAndCenter(16, e.target.getPosition());
}


Action();
