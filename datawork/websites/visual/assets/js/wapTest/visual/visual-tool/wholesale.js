// 超市围栏信息处理与标注
function LayerWholesale(mapTool) {
  this.polygons = [];
  this.marks = [];
  this.wholesale = [];
  this.formatData = [];
  this.getData();
  this.mapTool = mapTool;
}
LayerWholesale.prototype = {
  init: function() {
    var that = this;
    //将“批发市场画进去”
    $.each(that.formatData, function(index, item) {
      var c = item.center;
      var marker = mapTool.drawMarker({
        position: c,
        icon: "/assets/img/gps/wholesale-icon.png"
      });
      marker.title = "批发市场";
      marker.content = item.name;
      mapTool.on(marker, "click", markerClick);
      mapTool.on(marker, "dblclick", focusAndZoom);

      var polygon = mapTool.drawPolygon({
        position: item.pointsArr,
        style: {
          strokeOpacity: 1, //线透明度
          strokeWeight: 3, //线粗细度
        }
      });
      that.polygons.push(polygon);
      that.marks.push(marker);

      that.mapTool.on(that.mapTool.map, "zoomchange", function() {
        var toggle = "hide";
        if (that.isShow) {
          if (map.getZoom() > 11) { //放大,才显示围栏
            toggle = "show";
          }
          that.polygons.forEach(function(polygon) {
            mapTool[toggle](polygon);
          });
        }
      });
      that.isShow = true;
    });

  },
  getData: function() {
    var that = this;
    $http("/getapi/Gettotalsalerzone", "GET", {
      params: {},
      succCall: this.formatWholesale,
      others: {
        context: that
      }

    });
  },
  formatWholesale: function(data, others) {
    var that = others.context;
    var wholesaleList = [];
    var result = $.map(data, function(wholesaleItem) {
      if (wholesaleItem.length > 0) {
        var wholesale = {
          name: wholesaleItem[0].wholesaler_name,
          center: [wholesaleItem[0].zone_longitude, wholesaleItem[0].zone_latitude],
          pointsArr: []
        };
        var firstPoint = null;
        $.each(wholesaleItem, function(index, item) {
          var point = [item.zone_longitude, item.zone_latitude];
          if (!firstPoint) {
            firstPoint = point;
          }
          wholesale.pointsArr.push(point);
        });
        wholesale.pointsArr.push(firstPoint);
        wholesaleList.push(wholesale);
      }
    });
    that.formatData = wholesaleList;
    that.init();
  },
  isShow: false,
  show: function() {
    this.isShow = true;
    this.marks.forEach(function(mark) {
      mapTool.show(mark);
    });
    this.polygons.forEach(function(line) {
      mapTool.show(line);
    });
  },
  hide: function() {
    this.isShow = false;
    this.marks.forEach(function(mark) {
      mapTool.hide(mark);
    });
    this.polygons.forEach(function(line) {
      mapTool.hide(line);
    });
  },
  close: function() {}
};
