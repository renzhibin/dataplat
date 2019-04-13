/**
 * 主管区域绘制模块
 */
function SalesArea(mapTool) {
  this.polygons = [];
  this.marks = [];
  this.salesArea = [];
  this.inited = false;
  this.isShow = true;
  this.mapTool = mapTool;
}
SalesArea.prototype = {
  init: function(salesAreaList, others) {
    var that = others.context;
    that.inited = true;
    salesAreaList.forEach(function(salesItem) {
      var c = [salesItem.center.lng, salesItem.center.lat]; //中心点?
      var marker = that.mapTool.drawMarker({
        content: "<div class='sales-name'>" + salesItem.sales_name + "</div>",
        position: c,
        title: '主管名称',
      });
      marker.content = salesItem.sales_name;
      marker.title = '主管名';
      that.mapTool.on(marker, "click", markerClick);
      that.mapTool.on(marker, "dblclick", focusAndZoom);

      var lineArr = [];
      var firstPoint = null;
      salesItem.position.forEach(function(point) {
        point = [point.lng, point.lat];
        if (!firstPoint) {
          firstPoint = point;
        }
        lineArr.push(point);
      });
      lineArr.push(firstPoint); //连结成线...

      //主管管理区域（这个是多边形）
      var polygon = that.mapTool.drawPolygon({
        position: lineArr,
        style: {
          strokeColor: salesItem.style.strokeColor, //线颜色
          strokeOpacity: salesItem.style.strokeOpacity, //线透明度
          strokeWeight: salesItem.style.strokeWeight, //线粗细度
          fillColor: salesItem.style.fillColor, //填充颜色
          fillOpacity: salesItem.style.fillOpacity //填充透明度
        }
      });
      that.polygons.push(polygon);
      that.marks.push(marker);
    });
    that.mapTool.map.on('zoomchange', function(e) {
      var toggle = "hide";
      if (that.isShow) {
        if (that.mapTool.map.getZoom() > 6) { //放大,才显示围栏
          toggle = "show";
        }
        that.polygons.forEach(function(marker) {
          that.mapTool[toggle](marker);
        });
      }
    }); //zoom级别变化时,如果zoom>10则不显示轨迹方向,只有偏大时才显示
    that.show();
    this.isShow = true;
  },
  show: function() {
    var that = this;
    that.isShow = true;
    that.marks.forEach(function(mark) {
      that.mapTool.show(mark);
    });
    that.polygons.forEach(function(line) {
      that.mapTool.show(line);
    });
  },
  hide: function() {
    var that = this;
    that.isShow = false;
    that.marks.forEach(function(mark) {
      that.mapTool.hide(mark);
    });
    that.polygons.forEach(function(line) {
      that.mapTool.hide(line);
    });
  },
  close: function() {
    this.hide();
    this.polygons = [];
    this.marks = [];
    this.guard = [];
    this.salesArea = [];
    this.inited = false;
    this.isShow = false;
    $("#drawArea").attr("checked", false);
  }
};
