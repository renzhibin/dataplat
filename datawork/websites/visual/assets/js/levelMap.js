/**
 * 定义分级图方法
 */
var LevelMap = function() {
  /**
   * @param  {[MapObject]} map marker加载的地图对象
   * @param  {[Boolean]} _massLevelLoaded 分级设色图是否已加载
   * @param  {[Array]} massDataList 分级设色图数据
   * @param  {[Array]} roundData 去尾后的数据源
   * @param  {[Array]} massLevelLayer 分级设色图层数组，用于保存每一分级mass图层
   * @param  {[Array]} levelArr 分级数组
   * @param  {[Number]} middleLevel 分组中位数
   * @param  {[Number]} level 分级数
   * @param  {[Number]} dropPara 需要从顶峰抹平的百分比，默认值为20%
   * @param  {[Number]} minimum 需要去尾的数据下限，默认值为-1
   * @param  {[Number]} maximum 需要去峰的数据上限，默认值为null
   * @param  {[String]} type 需要显示的统计字段
   * @param  {[Number]} roundNumber 抹平后的统计数字
   * @param  {[jQueryObject]} $legend 图例容器
   * @param  {[AMap Object]} infoWindow 可选参数,信息窗体
   */
  this.map = null;
  this._massLevelLoaded = false;
  this.massDataList = [];
  this.roundData = [];
  this.massLevelLayer = [];
  this.levelArr = [];
  this.middleLevel = null;
  this.level = 0;
  this.dropPara = 0;
  this.minimum = 0;
  this.maximum = null;
  this.type = null;
  this.roundNumber = null;
  this.$legend = null;
  this.infoWindow = null;
}
LevelMap.prototype = {
  /**
   * 初始化分级图
   * @param  {[Map Object]} map 地图对象
   * @param  {[Object Array]} dataList 传入数据
   * @param  {[Number]} level    需要分的级别份数
   * @param  {[String]} type     可选参数，数值要选取的字段
   * @param  {[Array]} userLevel     可选参数，用户定义的分级数组
   * @param  {[Number]} dropPara 需要从顶峰抹平的百分比，默认值为40%
   * @param  {[Number]} minimum 需要去尾的数据下限，默认值为-1
   * @param  {[jQuery Object]} $legend 图例容器
   * @param  {[AMap Object]} infoWindow 可选参数,信息窗体
   */
  init: function(opt) {
    var that = this;
    if (opt.map && opt.$legend) {
      that.map = opt.map;
      that.$legend = opt.$legend;
    } else {
      alert("没有传入map对象/图例容器！");
      return;
    }
    if (opt.infoWindow) {
      that.infoWindow = opt.infoWindow;
    } else {
      that.infoWindow = new AMap.InfoWindow();
    }
    that.type = opt.type ? opt.type : "count";
    that.dropPara = opt.dropPara ? opt.dropPara : 40;
    that.minimum = (opt.minimum || opt.minimum === 0) ? opt.minimum : -1;
    that.maximum = opt.maximum ? opt.maximum : null;
    if (opt.userLevel && opt.userLevel.length > 2) {
      that.getMiddleLevel(opt.userLevel.length);
      that.levelArr = opt.userLevel;
      that.level = opt.userLevel.length;
    } else {
      that.level = opt.level;
      that.setLevel(opt.dataList);
    }
    that.initMassDataList(opt.dataList);
  },
  /**
   * 初始化分级设色图数据(对原始数据进行分级),分级完成后调用initMassLayer进行图层显示
   */
  initMassDataList: function(dataList) {
    var that = this;
    var massDataList = []; //建立mass图层数据数组
    var roundData = [];
    var middle = that.middleLevel;
    var levelArr = that.levelArr;
    var level = that.level;
    var type = that.type;
    var minimum = that.minimum;
    var maximum = that.maximum;
    that.massDataList = massDataList;
    that.roundData = roundData;
    for (var j = 0; j < level; j++) {
      massDataList[j] = [];
    }
    dataList.forEach(function(item, index) {
      var num = parseFloat(item[type]);
      if (checkBetween(num, minimum, maximum)) {
        var mapLevel = 0;
        if (num == levelArr[middle]) {
          item.mapLevel = middle;
        } else if (num > parseFloat(levelArr[middle])) {
          for (var i = 1; i < level; i++) {
            if ((num > parseFloat(levelArr[middle]) && num < parseFloat(levelArr[middle + i])) ||
              (middle + i) == level) {
              item.mapLevel = (middle + i - 1);
              break;
            }
          }
        } else {
          for (var k = 1; k < level; k++) {
            if (num < parseFloat(levelArr[middle]) && num >= parseFloat(levelArr[middle - k])) {
              item.mapLevel = (middle - k);
              break;
            }
          }
        }
        massDataList[item.mapLevel].push(item);
        roundData.push(item);
      }


    });
    that.initMassLayer(massDataList);
    that._massLevelLoaded = true;
    that.setLegend();
    that.roundNumber = roundData.length;

    function checkBetween(num, min, max) {
      if (max) {
        return (num > min && num < max);
      } else {
        return (num > min);
      }
    }
  },
  /**
   * 初始化分级图层
   */
  initMassLayer: function(massDataList) {
    var that = this;
    var massLayerCount = massDataList.length;
    var massLevelLayer = [];
    that.massLevelLayer = massLevelLayer;
    for (var i = 0; i < massLayerCount; i++) {
      massLevelLayer[i] = new AMap.MassMarks(massDataList[i], {
        url: 'http://' + location.host + '/assets/img/level_markers/level_' + i + '.png',
        anchor: new AMap.Pixel(3, 7),
        size: new AMap.Size(10, 14),
        opacity: 1,
        cursor: 'pointer',
        zIndex: 9999,
        alwaysRender: false
      });
      var marker = new AMap.Marker({
        content: ' ',
        map: that.map
      });
      massLevelLayer[i].on('mouseover', function(e) {
        showDetails(e);
      });
      // massLevelLayer[i].on('mouseout',function(e){
      //    hideDetails(e);
      // });
    }

    function showDetails(e) {
      var title = e.data.market_name;
      var count = e.data.count;
      var phone = e.data.contact_phone;
      that.infoWindow.setContent([
        '<h3><a target="_blank" href="http://mis.market.lsh123.com/marketmanage/market/list?q=' +
        phone + '">' + title + '</a></h3>', '<h4>联系电话:' + phone + '</h4>', '<h4>销售主管:' + e.data
        .f_salename + '</h4>', '<h4>销售:' + e.data.sales_name + '</h4>', '<h4>统计结果:' + count +
        '</h4>'
      ].join(''));
      that.infoWindow.open(that.map, new AMap.LngLat(e.data.lng, e.data.lat));
      console.log(e.data);
    }

    function hideDetails(e) {
      that.infoWindow.close();
    }
  },
  /**
   * 数据分级函数，将源数据进行遍历，获取其中最大值
   * 根据用户分级数level，使用2/3 max值进行每份值获取
   * 将获取完成的分级分段置入levelArr中，在initMassDataList中进行使用
   */
  setLevel: function(dataList) {
    var max = 0;
    var levelArr = [];
    var perLevel = 0;
    this.levelArr = levelArr;
    var dropPara = this.dropPara;
    var averageSum = 0; //用于平分的总和
    var level = this.level;
    if (this.maximum) {
      max = this.maximum;
    } else {
      var list = [];
      var LEN = dataList.length;
      var type = this.type;
      for (var i = 0; i < LEN; i++) {
        list.push(dataList[i][type]);
      }
      if ((Math.max.apply(null, list)).toFixed(2) != -Infinity) {
        max = (Math.max.apply(null, list)).toFixed(2);
      } else {
        max = 0;
      }
    }
    averageSum = max * (100 - dropPara) / 100;
    perLevel = (averageSum / (level - 1)).toFixed(2);
    for (var j = 0; j <= level; j++) {
      if (j != level) {
        levelArr.push((perLevel * j).toFixed(2));
      } else {
        levelArr.push(max);
      }
    }
    this.getMiddleLevel(level);

  },
  /**
   * 实时计算图例并显示
   */
  setLegend: function() {
    var legendArr = this.levelArr;
    var len = legendArr.length;
    var legendList = [];
    var legendString;
    for (var i = 0; i < len; i++) {
      if (i == (len - 1)) {
        legendString = "<div class='mass-legend'>" + legendArr[i] + "</div>";
      } else {
        legendString = "<div class='mass-legend mass-" + i + "'>" + legendArr[i] + "</div>";
      }
      legendList.push(legendString);
    }
    this.$legend.html(legendList.reverse().join(""));
  },
  /**
   * 获取分级中间级数
   */
  getMiddleLevel: function(level) {
    if (level % 2 === 0) {
      this.middleLevel = level / 2 + 1;
    } else {
      this.middleLevel = (level - 1) / 2;
    }
  },
  /**
   * 显示分级图
   */
  show: function() {
    var that = this;
    this.massLevelLayer.forEach(function(item) {
      item.setMap(that.map);
    });
  },
  /**
   * 隐藏分级图
   */
  hide: function() {
    this.massLevelLayer.forEach(function(item) {
      item.setMap(null);
    });
  },
  /**
   * 清空分级图中数据
   */
  clear: function() {
    var that = this;
    that.hide();
    that._massLevelLoaded = false;
    that.massDataList = [];
    that.massLevelLayer = [];
    that.levelArr = [];
    that.middleLevel = null;
    that.level = 0;
    that.dropPara = 0;
    that.minimum = 0;
    that.maximum = null;
    that.roundData = [];
    that.type = null;
    that.roundNumber = null;
  },
  /**
   * 获取筛选后数量
   */
  getRoundNumber: function() {
    return this.roundNumber;
  }
};
