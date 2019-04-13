var TPL = [
  '<div class="visual-tool-zone">',
  '<label>查询地区:',
  '<select class="field-control sm select-zone" name="zone">',
  '</select>',
  '</label>',
  '</div>'
].join("");


// 三个城市中心点,在切换区域时同步平移
var CENTER = {
  all: {
    lng: 116.397389,
    lat: 39.916576
  },
  1000: {
    lng: 116.397389,
    lat: 39.916576
  },
  1001: {
    lng: 117.212637,
    lat: 39.13765
  },
  1002: {
    lng: 120.184408,
    lat: 30.243531
  }
};
/**
 * 筛选区域
 */
var SEARCHREGION = [{
  name: "北京",
  value: 1000,
  center: CENTER["1000"]
}, {
  name: "天津",
  value: 1001,
  center: CENTER["1001"]
}, {
  name: "杭州",
  value: 1002,
  center: CENTER["1002"]
}];
var ALLSEARCH = [{
  name: "全部",
  value: "all",
  center: CENTER.all
}];


/**
 * 区域选择实例
 * @param {jQueryDom} $dom 需要插入区域选择实例的节点
 * @param {function} events 需要绑定的事件
 * @param {Object} mapTool 初始化后的mapTool对象
 * @param {number} type 地域组件类型 0不需要"全部"选项 1需要"全部"选项
 */
var ZoneSelect = function($mod, events, mapTool, type) {
  var that = this;
  that.$mod = $mod;
  that.mapTool = mapTool;
  that.events = events;
  // 通过类型判断是否需要附加 "全部" 选项
  if (type === 0) {
    this.SEARCHREGION = SEARCHREGION;
  } else {
    this.SEARCHREGION = ALLSEARCH.concat(SEARCHREGION);
  }
  that.init();
};
ZoneSelect.prototype = {
  init: function() {
    var that = this;
    that.$mod.html(TPL);
    that.appendZoneList();
    that.bindEvents();
  },
  appendZoneList: function() {
    var zoneList = [];
    var $zoneSelect = $(".select-zone");
    $.each(this.SEARCHREGION, function(index, item) {
      var option = '<option value=' + item.value + '>' + item.name + '</option>';
      zoneList.push(option);
    });
    $zoneSelect.empty().append(zoneList.join(""));
  },
  bindEvents: function() {
    var that = this;
    $(".visual-tool-zone").on("change", ".select-zone", function(e) {
      var value = $(e.target).val();
      if (CENTER[value]) {
        that.mapTool.map.setZoomAndCenter(10, new that.mapTool.AMap.LngLat(CENTER[value].lng,
          CENTER[
            value]
          .lat));
      }
      that.events(value);
    });
  }

};
