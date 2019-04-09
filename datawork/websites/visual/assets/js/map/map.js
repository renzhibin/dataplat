var KEY = '9c9aefb35bc32899c6284b482007e3e4';
// 需要使用的高德地图插件
var PLUGIN = ['AMap.ToolBar', 'AMap.Geocoder', 'AMap.Autocomplete', 'AMap.PlaceSearch', 'AMap.MouseTool', 'AMap.Scale'];

var PRESET_POLYGON_STYLE = {
    strokeColor: "#F33", //线颜色
    strokeOpacity: 0.35, //线透明度
    strokeWeight: 0, //线粗细度
    fillColor: "#F33", //填充颜色
    fillOpacity: 0.35, //填充透明度
};

var PRESET_POLYLINE_STYLE = {
    strokeColor: "#46acf6", //线颜色
    strokeOpacity: 1, //线透明度
    strokeWeight: 2, //线粗细度
    strokeStyle: "solid", //线样式solid实线  dashed虚线
};

/**
 * 地图打点类
 * 基于高德地图，生成一个地图，提供查询及标点功能
 * @param {object} opts 地图参数，详细说明如下：
 * container   地图容器，地图大小默认为容器的100%，业务根据各自需要定置宽高
 * province    省
 * city        市
 * district    区
 * address     详细地址
 * position    经纬度
 * readonly    地图是否只读 ，设为只读后地图将只允许移动，不允许搜索和移动标点
 */
var Map = function(opts) {
    this.opts = opts;
    this.map = null; // 地图对象
    this.infoWindow = null; //信息窗体
    this.init();
    return this;
};

Map.prototype = {
    /**
     * 初始化入口
     */
    init: function() {
        var that = this;
        that.getMapLib().done(function() {
            window.gdMapCbk = that.initMap.call(that);
        });
    },
    /**
     * 获取高德地图类库
     */
    getMapLib: function() {
        return $.ajax({
            url: 'http://webapi.amap.com/maps?v=1.3&key=' + KEY + '&plugin=' + PLUGIN.join(',') + '&callback=gdMapCbk',
            dataType: 'script'
        });
    },
    /**
     * 初始化地图
     */
    initMap: function() {
        var that = this;
        if (AMap.Map) {
            var container = that.opts.container || 'mapContainer';
            var mapOptions = that.opts.mapOptions || {};
            that.AMap = AMap;
            that.map = new AMap.Map(container, mapOptions);
            if(that.opts.infoWindowOpts && that.opts.infoWindowOpts.needed){
                that.initInfoWindow();
            }
            //是否需要使用地图默认打点,不需要则unMarker=true
            that.map.on('complete', function() {
                $(that).trigger('e-map-complete');
                if(that.opts.callback && $.isFunction(that.opts.callback) ){
                    that.opts.callback();
                }
                that.initControl();
                that.initScale();
            });
        } else {
            setTimeout(function() {
                that.initMap();
            }, 300);
        }

    },
    /**
     * 初始化InfoWindow
     */
    initInfoWindow: function() {
        var that = this;
        var opts = that.opts.infoWindowOpts.offset ||[0,-30];
        that.infoWindow = new AMap.InfoWindow({
            offset:new AMap.Pixel(opts[0], opts[1])
        });
    },

    /**
     * 初始化mousetool
     */
    initMoustTool: function() {
        var that = this;
        that.mouseTool = new AMap.MouseTool(that.map);
    },
    /**
     * 标点操作
     * @param  {Array} location 位置坐标
     */
    mark: function(location) {
        this.initMarker(location);
        this.map.setFitView();
    },
    /**
     * 在指定位置生成标点
     * @param {Object} opts 传入参数
         * @param  {Array}  opts.position 必填,位置坐标
         * @param  {String} opts.content 必填,marker内容,包括class等dom内容
         * @param  {Object} opts.extData 选填,附加信息
         * @param  {Number} opts.zIndex 选填,位置层级
         * @param  {Array}  opts.offset 选填,偏移位置,默认为AMap.Pixel(-10,-34)
         * @param  {Bool}   opts.draggable 选填,是否可拖动,默认false
         * @param  {Bool}   opts.topWhenMouseOver 选填,鼠标移动进入时是否置顶,默认true
         * @param  {Bool}   opts.raiseOnDrag 选填,拖动时是否离开地图,默认为true
     */
    drawMarker: function(opts) {
        if(!opts.position){
          return false;
        }
        var formatOpts = {
            cursor: "pointer",
            map: this.map,
            position: opts.position,
            extData: opts.extData || {},
            zIndex: opts.zIndex || 10,
            offset: opts.offset?new AMap.Pixel(opts.offset[0],opts.offset[1]):new AMap.Pixel(-10,-34),
            draggable: opts.draggable || false,
            topWhenMouseOver: opts.topWhenMouseOver || true,
            raiseOnDrag: opts.raiseOnDrag || true
        };
        if(opts.content){
          formatOpts.content = opts.content;
        }else if(opts.icon){
          formatOpts.icon = opts.icon;
        }
        var marker = new AMap.Marker(formatOpts);

        return marker;
    },
    /**
     * 绘制多边形区域
     * @param {Object} opts 传入参数
         * @param  {Array}  opts.position 必填,位置坐标数组
         * @param  {Object} opts.extData 选填,附加信息
         * @param  {Object} opts.style 选填,多边形样式,若不传入则为默认样式
         * @param  {Number} opts.zIndex 选填,位置层级,若不传入则为10
     */
    drawPolygon: function(opts) {
        if(!opts.position){
          return false;
        }
        var style = $.extend({},PRESET_POLYGON_STYLE,opts.style);
        var polygon = new AMap.Polygon({
            path: opts.position, //设置线覆盖物路径
            strokeColor: style.strokeColor, //线颜色
            strokeOpacity: style.strokeOpacity, //线透明度
            strokeWeight: style.strokeWeight, //线粗细度
            fillColor: style.fillColor, //填充颜色
            fillOpacity: style.fillOpacity, //填充透明度
            extData: opts.extData || {}, //补充数据
            zIndex: opts.index || 10,
            map: this.map,
        });
        return polygon;
    },
    /**
     * 绘制折线
     * @param {Object} opts 传入参数
         * @param  {Array}  opts.position 必填,位置坐标数组
         * @param  {Object} opts.extData 选填,附加信息
         * @param  {Object} opts.style 选填,折线样式,若不传入则为默认样式
         * @param  {Number} opts.zIndex 选填,位置层级,若不传入则为10
     */
    drawPolyline: function(opts) {
        var style = opts.style || PRESET_POLYLINE_STYLE;
        if(!opts.position){
          return false;
        }
        var polyline = new AMap.Polyline({
            path: opts.position, //设置线覆盖物路径
            strokeColor: style.strokeColor, //线颜色
            strokeOpacity: style.strokeOpacity, //线透明度
            strokeWeight: style.strokeWeight, //线粗细度
            strokeStyle: style.strokeStyle, //线样式solid实线  dashed虚线
            extData: opts.extData || {}, //补充数据
            zIndex:opts.zIndex || 10,
            map: this.map,
        });
        return polyline;
    },
    /**
     * 绘制麻点图层
     * @param {Object} opts 传入参数
         * @param  {Array}  opts.data 必填,位置坐标与信息数组,例[{lnglat: [116.405285, 39.904989], name: i,id:1},{}, …]
         * @param  {String} opts.url 必填,mass图标,必须为url地址
         * @param  {Array}  opts.anchor 必填,图标显示位置偏移量,例[3,7]
         * @param  {Array}  opts.size 必填,图标的尺寸,例[10,14]
         * @param  {Number} opts.zIndex 选填,位置层级,若不传则为30
         * @param  {Number} opts.opacity 选填,透明度,若不传则为1
         * @param  {Bool}   opts.alwaysRender 选填,是否每次拖动都重新渲染,默认false
     */
    drawMassMarks: function(opts) {
        if(!opts.url || !opts.data || !opts.anchor || !opts.size){
          console.log("配置参数错误,请重新调试");
          return false;
        }
        var massMarks = new AMap.MassMarks(opts.data, {
            cursor: 'pointer',
            url: opts.url,
            anchor: new AMap.Pixel(opts.anchor[0], opts.anchor[1]),
            size: new AMap.Size(opts.size[0], opts.size[1]),
            zIndex: opts.zIndex || 30,
            opacity: opts.opacity || 1,
            alwaysRender: opts.alwaysRender || false
        });
        return massMarks;
    },
    /**
     * 初始化地图工具栏
     */
    initControl: function() {
        this.map.addControl(new AMap.ToolBar({
            position: "LB",
            offset: new AMap.Pixel(0, 40)
        }));
    },
    /**
     * 获取标点位置坐标
     */
    getMarkPos: function() {
        return this.marker.getPosition();
    },
    /**
     * 初始化地图比例尺
     */
    initScale: function() {
        this.map.addControl(new AMap.Scale());
    },
    /**
     * 给地图上的元素增加监听事件
     * @param {AMapObject} target 需要被监听事件的地图元素
     * @param {String} events 需要监听的事件
     * @param {Function} callback 需要执行的函数
     */
    on: function(target, events, callback) {
        AMap.event.addListener(target, events, function(e) {
            callback(e);
        });
    },

    /**
     * 触发地图上的元素的指定事件
     * @param {AMapObject} target 需要被监听事件的地图元素
     * @param {String} events 需要监听的事件
     * @param {Function} extArgs 事件监听的回调函数可以接收到的参数
     */
    trigger: function(target, events, extArgs) {
        AMap.event.trigger(target, events, extArgs);
    },

    /**
     * 清空地图上的marker
     */
    clear: function() {
        if (this.map) {
            this.map.clearMap();
        }
    },

    /**
     * 开启框选
     * @param [Object] options 绘制参数
     */
    beginSelection: function(rectOptions) {
        var that = this;
        if (!that.mouseTool) {
            that.initMoustTool();
        }
        that.mouseTool.rectangle(rectOptions);
    },

    /**
     * 关闭框选
     */
    mouseToolClosed: function() {
        var that = this;
        that.mouseTool.close(true);
        that.map.setStatus({
            dragEnable: true
        });
    },

    /**
     * 显示传入对象
     * @param [AMap Object] item AMap中的对象
     */
    show: function(item) {
        item.setMap(this.map);
    },

    /**
     * 隐藏传入对象
     * @param [AMap Object] item AMap中的对象
     */
    hide: function(item) {
        item.setMap(null);
    }

};
