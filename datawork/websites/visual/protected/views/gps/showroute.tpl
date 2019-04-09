<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="chrome=1">
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no, width=device-width">
    <link rel="stylesheet" href="http://cache.amap.com/lbs/static/main1119.css"/>
    <link rel="stylesheet" href="http://cache.amap.com/lbs/static/AMap.DrivingRender1120.css"/>
    <link rel="stylesheet" href="http://cache.amap.com/lbs/static/main1119.css"/>
    <link rel="stylesheet" href="http://at.alicdn.com/t/font_1467113294_2577608.css" />
    <style type="text/css">

        body,html,#container{
            height: 100%;
            margin: 0px;
            font:12px Arial;
        }
        #panel {
            z-index: 999;
            position: absolute;
            background-color: white;
            max-height: 100%;
            overflow-y: auto;
            right: 0;
            width: 280px;
        }
        #queryForm {
            position: absolute;
            top: 10px;
            right: 10px;
            height:50px;
            background-color: #FFF;
            padding-left: 14px;
            padding-right: 7px;
            padding-bottom: 7px;
            line-height: 30px;
            border: 1px solid rgba(51,51,51,.2);
            box-shadow: 2px 2px 2px 0 rgba(0,0,0,.2);
            border-top: 0;
            border-left: 0;
        }
        #menu {
            position: absolute;
            top: 100px;
            right: 10px;

            background-color: #FFF;
            padding-left: 14px;
            padding-right: 7px;
            padding-bottom: 7px;
            line-height: 30px;
            border: 1px solid rgba(51,51,51,.2);
            box-shadow: 2px 2px 2px 0 rgba(0,0,0,.2);
            border-top: 0;
            border-left: 0;
        }
        .ware-house{
            border: solid 1px red;
            color: red;
            float: left;
            min-width: 50px;
            text-align: center;
            background-color: rgba(255,0,0,0.1)
        }
        .wholesale {
            color: red;
            font-size: 20px;
        }
        .point-still {
            /*width: 10px;*/
            /*height: 10px;*/
            /*border-radius: 5px;*/
            /*background-color: red;*/
            font-size: 20px;
            color: #ff930a;
        }
        .pay-point {
            border-radius: 25px;
            background-color: red;
        }
        .point-move {
            width: 6px;
            height: 6px;
            border-radius: 3px;
            /*background-color: green;*/
            font-size: 12px;
            color: green;
        }
        .move-animate {
            width: 60px;
            height: 60px;
            border-radius: 30px;
        }

        /* 雷达波效果 */
        .dot-rider:after {
            content: '';
            position: absolute;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            box-shadow: 0 0 1px 2px rgba(255, 66, 0, 0.8);
            top: 50%;
            left: 50%;
            margin-top: -60px;
            margin-left: -60px;
            z-index: 3;
            opacity: 0;
            -webkit-animation: halo 1s 0.5s infinite ease-out;
            -moz-animation: halo 1s 0.5s infinite ease-out;
            animation: halo 1s 0.5s infinite ease-out;
        }
        #tips{
            position: fixed;
            width: 600px;
            height: 200px;
            left: 50%;
            top:50%;
            margin-left:-300px;
            margin-top: -100px;
            background-color: white;
            z-index: 999;
            border-radius: 5px;
            box-shadow: 0 0 1px 2px rgba(0, 176, 239, 0.8);
            font-size: 18px;
        }
        #tips .tip-message{
            width: 570px;
            border-bottom: 1px solid #B5B9D1;
            height: 50px;
            line-height: 50px;
            padding-left: 30px;
        }
        #tips #cellphone{
            padding:30px 0 0 30px;
        }
        #tips #cellphone .cell-option{
            vertical-align: middle;
            margin-bottom: 10px;
        }
        #tips #cellphone input[type=radio]{
            margin:-2px 10px 1px 0;
            display: inline-block;
            vertical-align:middle;
        }
        #searchBox input{
            width: 120px;
        }
        #searchBox label{
            display: inline-block;
            margin: 5px 5px 5px 0;
            height:30px;
            line-height: 40px;
        }

        .hidden{
            display: none;
        }
        #search{
            display: inline-block;
            width: 60px;
            height: 30px;
            margin: 14px 5px;
            line-height: 30px;
            text-align: center;
            color: white;
            background-color: #00B0EF;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            color:white;
        }
        #searchByPhone{
            margin:10px auto;
            width: 100px;
            height: 40px;
            line-height: 40px;
            text-align: center;
            background-color: #00B0EF;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
        #remarkPanel{
            display: none;
            position: fixed;
            width: 360px;
            height: 400px;
            left: 50%;
            top: 50%;
            margin-left: -180px;
            margin-top: -200px;
            padding: 30px;
            box-sizing:border-box;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 0 1px 2px black;
            z-index:999999;
        }
        #remarkPanel .remark-title{
            vertical-align: top;
        }
        #remarkPanel .remark-info{
            margin-top: 30px;
        }
        #remarkPanel #remarkContent{
            resize:none;
            width: 248px;
            height: 200px;
            overflow: scroll;
        }
        #remarkPanel .remark-buttons{
            margin: 20px auto;
            text-align: center;
            box-sizing:border-box;
        }
        #remarkPanel .remark-buttons .button{
            display: inline-block;
            margin:0 10px;
            width: 80px;
            height: 35px;
            text-align: center;
            line-height: 35px;
            font-size: 20px;
            border-radius: 5px;
            cursor: pointer;
            color:white;
        }
        #remarkPanel .remark-buttons .cancel{
            background-color: #FFC21D;
        }
        #remarkPanel .remark-buttons .ok{
            background-color: #72FF2C;
        }
        .set-remark{
            position: absolute;
            top: 25px;
            right: 15px;
            background-color: #FFB800;
            color: white;
            padding: 3px;
            cursor:pointer;
        }
        @-webkit-keyframes halo {
            0% {
                opacity: 0;
                -webkit-transform: scale(0.1);
            }
            50% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                -webkit-transform: scale(1.2);
            }
        }

        @-moz-keyframes halo {
            0% {
                opacity: 0;
                -moz-transform: scale(0.1);
            }
            50% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                -moz-transform: scale(1.2);
            }
        }

        @-ms-keyframes halo {
            0% {
                opacity: 0;
            }
            50% {
                opacity: 1;
            }
            100% {
                opacity: 0;
            }
        }

        @-o-keyframes halo {
            0% {
                opacity: 0;
                -o-transform: scale(0.1);
            }
            50% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                -o-transform: scale(1.2);
            }
        }

        @keyframes halo {
            0% {
                opacity: 0;
                transform: scale(0.1);
            }
            50% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                transform: scale(1.2);
            }
        }


        .dot-ripple:before{
            content:' ';
            position: absolute;
            z-index:2;
            left:0;
            top:0;
            width:10px;
            height:10px;
            background-color: #ff4200;
            border-radius: 50%;
        }

        .dot-ripple:after {
            content:' ';
            position: absolute;
            z-index:1;
            width:10px;
            height:10px;
            background-color: #ff4200;
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(0,0,0,.3) inset;
            -webkit-animation-name:'ripple';/*动画属性名，也就是我们前面keyframes定义的动画名*/
            -webkit-animation-duration: 1s;/*动画持续时间*/
            -webkit-animation-timing-function: ease; /*动画频率，和transition-timing-function是一样的*/
            -webkit-animation-delay: 0s;/*动画延迟时间*/
            -webkit-animation-iteration-count: infinite;/*定义循环资料，infinite为无限次*/
            -webkit-animation-direction: normal;/*定义动画方式*/
        }
        @keyframes ripple {
            0% {
                left:5px;
                top:5px;
                opcity:75;
                width:0;
                height:0;
            }
            100% {
                left:-20px;
                top:-20px;
                opacity: 0;
                width:50px;
                height:50px;
            }
        }
        /*.iconfont {*/
        /*font-size: 24px;*/
        /*line-height: 24px;*/
        /*}*/
        /*.wholesale {*/
        /*background-color: #09f;*/
        /*border-radius: 6px;*/
        /*border: solid 1px silver;*/
        /*width: 35px;*/
        /*height: 16px;*/
        /*padding: 3px;*/
        /*text-align: center;*/
        /*line-height: 18px;*/
        /*max-width: 50px;*/
        /*color:white;*/
        /*}*/
    </style>
    <style>
        /*style from: */
        /*.amap-marker {*/
            /*z-index: 12!important*/
        /*}*/

        .amap-marker .marker-geo,.amap-marker .marker-marker-plan-poi,.amap-marker .marker-marker-station,.amap-marker .marker-normal,.amap-marker .marker-route {
            position: absolute;
            width: 19px;
            height: 32px;
            color: #e90000;
            background: url(http://ditu.amap.com/assets/img/newpoi.png) no-repeat;
            cursor: pointer
        }

        .amap-marker .marker-geo.amap-marker .marker-fav-single,.amap-marker .marker-place,.amap-marker .marker-single,.amap-marker .marker-tmp {
            position: absolute;
            width: 19px;
            height: 32px;
            background: url(http://ditu.amap.com/assets/img/newpoi.png) -42px -243px no-repeat;
            cursor: pointer
        }

        /*.marker-poi {*/
            /*display: none*/
        /*}*/

        .amap-marker .marker-route {
            height: 37px;
            width: 22px
        }
    </style>
    <title>数据可视化--运输轨迹图</title>
</head>
<body>
<div id="container" tabindex="0"></div>
<div id="panel"></div>
<div id="queryForm">
    <form id="searchBox" action="" method="get">
        <label style="display:none">数据源：
            <input name="version" readonly value="2" type="text">
        </label>
        <label>司机姓名:
            <input name="trans_name" value="{/$smarty.get.trans_name/}" type="text">
        </label>
        <label>司机电话:
            <input name="trans_phone" value="{/$smarty.get.trans_phone/}" type="text">
        </label>
        <label>运单号:
            <input name="trans_bill" value="{/$smarty.get.trans_bill/}" type="text">
        </label>
        <label>时间:<input type="date" name="date" value="{/if $smarty.get.date/}{/$smarty.get.date|escape/}{/else/}{/$smarty.now|date_format:'%Y-%m-%d'/}{//if/}" /></label>
        <label>跟踪时长(小时):<input style="width:30px" id="time" type="number" name="runtime" value="{/if $smarty.get.runtime/}{/$smarty.get.runtime/}{/else/}24{//if/}" /></label>
        <span id="search">查询</span>

    </form>
</div>
<div id="menu">
    <div><label><input type="checkbox" class="show-market" />显示超市</label></div>
    <div><label><input type="checkbox" class="show-receipt" />显示签收点</label></div>
    <div><label><input type="checkbox" class="show-transpoint" />显示运输路线</label></div>
    <div><label><input type="checkbox" class="show-transpoint-move" />--移动点</label></div>
    <div><label><input type="checkbox" class="show-transpoint-still" />--静止点</label></div>
    <div><label><input type="checkbox" class="show-transpoint-animate" />--模拟动画</label></div>
    <div><label><input type="checkbox" class="show-wholesale" />显示电子围栏</label></div>
</div>

<div id="tips" class="hidden">
    <div class="tip-message">出现重名司机，请进一步选择司机联系电话：</div>
    <div id="cellphone"></div>
    <div id="searchByPhone">确定</div>
</div>

<!-- <div id="remarkPanel">
    <div class="remark-info">
        <span class="remark-title">备注: <textarea name="" id="remarkContent" cols="35" rows="10"></textarea></span>
    </div>
    <div class="remark-buttons">
        <span class="button cancel">取消</span>
        <span class="button ok">确定</span>
    </div>
</div> -->

<script src="http://webapi.amap.com/js/marker.js"></script>
<script type="text/javascript" src="http://webapi.amap.com/maps?v=1.3&key=9c9aefb35bc32899c6284b482007e3e4&plugin=AMap.Scale,AMap.OverView,AMap.ToolBar"></script>
<script type="text/javascript" src="http://cache.amap.com/lbs/static/DrivingRender1230.js"></script>
<script type="text/javascript" src="http://cache.amap.com/lbs/static/addToolbar.js"></script>
<script src="https://code.jquery.com/jquery-1.12.4.js"   integrity="sha256-Qw82+bXyGq6MydymqBxNPYTaUXXq7c8v3CwiYwLLNXU="   crossorigin="anonymous"></script>
<script src="/assets/js/artTemplate.js?version={/$version/}"></script>
<script>
    // 定义静止点信息，方便添加备注时使用
    var stillPointInfo={};
    var transInfo = {/json_encode($transInfo)/};
    // 获取smarty模板返回信息
    var smartyData = {
            gpsPosition: {/json_encode($gpsPosition)/},
            waybillInfos: {/json_encode($waybillInfos)/},
            wholesalerZone: {/json_encode($wholesalerZone)/}
    };


    //做数据预处理
    var newData = {
        markets: [],
        stillPoints: [],
        movePoints: [],
        payPoints: [],
        wholesale: []
    };

    //1. 超市信息
    smartyData.waybillInfos.forEach(function (info) {
        newData.markets.push({
            info: info,
            pos: {lng: info.shop_longitude,lat: info.shop_latitude},
            near: {
                lng: info.nearest_longitude,    //TODO ??
                lat: info.nearest_latitude,
                time: +info.nearest_time
            }
        });
    });
    newData.markets.sort(function (a, b) {  //按时间给超市排序
        return a.near.time - b.near.time
    });

    //2. 停留点/移动点
    smartyData.gpsPosition.forEach(function (info) {
        if(info.pos_type == 1) {    //停留点
            newData.stillPoints.push({
                info: {stay_span: +info.stay_span, location_time: +info.location_time},
                pos: {lng: info.longitude,lat: info.latitude},
                remarkinfo:info.remarkinfo
            });
        } else if(info.pos_type == 2) { //移动点（含停留点）
        }
        newData.movePoints.push({
            info: {stay_span: +info.stay_span, location_time: +info.location_time},
            pos: {lng: info.longitude,lat: info.latitude}
        });
    });
    //3. 签收点
    smartyData.waybillInfos.forEach(function (info) {
        //由于position为-1、0时为记录错误
        if(info.nearest_longitude != -1 && info.nearest_latitude != -1 &&info.nearest_longitude != 0 && info.nearest_latitude != 0){
            newData.payPoints.push({
                info: {
                    market_name: info.market_name,
                    startTime: +info.shipped_at,
                    time: +info.receipt_at,
                    shipping_order_id: info.shipping_order_id,
                    waybill_no: info.waybill_no
                },
                pos: {
                    lng: info.nearest_longitude,    //TODO ??
                    lat: info.nearest_latitude
                },
                near: {
                    lng: info.nearest_longitude,    //TODO ??
                    lat: info.nearest_latitude,
                    time: +info.nearest_time
                }
            });
        }

    });
    //4. 电子围栏信息
    for(var key in smartyData.wholesalerZone) {
        if(smartyData.wholesalerZone.hasOwnProperty(key)) {
            var info = smartyData.wholesalerZone[key];
            if(info.length == 0) {
                continue;
            }
            var polyline = [];
            info.forEach(function (p) {
                polyline.push({lng: p.zone_longitude, lat: p.zone_latitude});
            });
            newData.wholesale.push({
                name: key,
                pos: {lng: info[0].zone_longitude, lat: info[1].zone_latitude},
                polyline: polyline
            });

        }
    }

</script>
<script type="text/javascript">
    var data = {
        startTime: 1466230853,
        wholesale: newData.wholesale,
        markets: newData.markets,
        stillPoints: newData.stillPoints,
        movePoints: newData.movePoints,
        payPoints: newData.payPoints

    };

    function LayerMarket() {
        this.marks = [];
        this.lines = [];
    }
    // 超市点信息
    LayerMarket.prototype = {
        init: function () {
            var that = this;
            //将超市显示上去
            var marketsInfo = data.markets;
            var artM = template.compile([
                '<p>id: {{market.info.address_id}}</p>',
                '<p>地址: {{market.info.address}}</p>',
                '<p>联系人: {{market.info.contact_name}}</p>',
                '<p>联系电话: {{market.info.contact_phone}}</p>',
            ].join(''));
            var tipWindow = new AMap.InfoWindow({offset: new AMap.Pixel(0, 0)});
            var timer = null;
            function showDistance(e) {
                if(timer) {
                    clearTimeout(timer);
                    timer = null;
                }
                tipWindow.setContent(e.target.content);
                tipWindow.open(map, e.lnglat);
            }
            function hideDistance(e) {
                clearTimeout(timer);
                timer = setTimeout(function () {
                    tipWindow.close();
                    timer = null;
                }, 500);
            }

            for (var i = 0; i < marketsInfo.length; i += 1) {
                var market = marketsInfo[i];
                var marker;

                marker = new AMap.Marker({
                    icon: "/assets/img/gps/market-icon.png",
                    position: [market.pos.lng, market.pos.lat],
                    title: market.info.market_name,
                    map: mapObj,
                    topWhenMouseOver: true,
                });
                marker.content = artM({market: market});
                marker.title = market.info.market_name;

                //处理“与超市最近拉卡拉打点”--停留点
                if(market.near) {
                    if(market.near.lng != -1 && market.near.lat != -1){
                        var lineArr = [[market.pos.lng, market.pos.lat], [market.near.lng, market.near.lat]];
                        var polyline = new AMap.Polyline({
                            path: lineArr,          //设置线覆盖物路径
                            strokeColor: "#3366FF", //线颜色
                            strokeOpacity: 1,       //线透明度
                            strokeWeight: 5,        //线宽
                            strokeStyle: 'dashed',   //线样式
                            strokeDasharray: [10, 5] //补充线样式
                        });
                        //避免停留点出现问题是出现连线的问题
                        if(polyline.getLength() < 150000){
                            polyline.content = [
                                '<p>目标超市:'+market.info.market_name+'</p>',
                                '<p>最近停留点距离: '+polyline.getLength()+'米</p>',
                                '<p>是否匹配: ' + (polyline.getLength() < 1000 ? '匹配' : '不匹配（超过1KM）')+'</p>'
                            ].join('');
                            polyline.setMap(null);
                            polyline.on('mouseover', showDistance);
                            polyline.on('mouseout', hideDistance);
                            this.lines.push(polyline);
                        }

                    }
                }

                //给Marker绑定单击事件
                marker.on('click', markerClick);
                marker.on('dblclick', focusAndZoom);

                this.marks.push(marker);
            }
            map.on('zoomchange', function (e) {
                if(!that.isShow) {
                    return;
                }
                var _map = null;
                if(map.getZoom() > 11) {    //放大,才显示箭头
                    _map = map;
                }
                that.lines.forEach(function (line) {
                    line.setMap(_map);
                });
            });
            $('#menu .show-market').prop('checked', true);
            that.isShow = true;
        },
        isShow: false,
        show: function () {
            var that = this;
            that.isShow = true;
            this.marks.forEach(function (mark){
                mark.setMap(map) ;
            });
            this.lines.forEach(function (line){
                line.setMap(map) ;
            });
        },
        hide: function () {
            var that = this;
            that.isShow = false;
            this.marks.forEach(function (mark){
                mark.setMap(null) ;
            });
            this.lines.forEach(function (line){
                line.setMap(null) ;
            });
        },
        close: function () {}
    };
    // 移动点
    function LayerTransPoint() {
        this.moveMarks = [];
        this.stillMarks = [];
        this.lines = [];
        this.animateMarks = [];
    }
    LayerTransPoint.prototype = {
        init: function () {
            this._initLine();
//            this._initAnimate();
        },
        _initLine: function () {
            var lineArr = [];
            data.movePoints.forEach(function (point) {
                lineArr.push([point.pos.lng, point.pos.lat]);
            });
            var polyline = new AMap.Polyline({
                path: lineArr,
                strokeColor: "#0091ff", //线颜色
                strokeOpacity: 0.7, //线透明度
                strokeWeight: 6,    //线宽
                fillColor: "#1791fc", //填充色
                fillOpacity: 0.75//填充透明度
            });
            polyline.setMap(map);
            this.lines.push(polyline);

            $('#menu .show-transpoint').prop('checked', true);
        },
        _initMove: function () {
            var that = this;
            ///再画上面的点
            var moveMarks = [];

            this.moveMarks = moveMarks;

            // var colorList = [
            //     "#02450e","#024e10","#025611","#015c12","#016414","#026b16","#017216","#027a18","#028119","#02891b","#01911c","#01991d","#02a11f","#03aa22","#02b323","#02bb25","#03c527","#03d029","#04dc2c","#05e62e","#05ef30","#04f831","#2df752","#42f764","#5cf678","#70f689","#86f69b","#a2fcb3","#b0fabe","#bff8c9"
            //     ,"#1708f8","#1716f9","#1724f9","#1732f9","#1740f8","#174ef8","#175ef9","#176cf9","#177bf9","#178df8","#179ff8","#17b2f8","#17c4f8","#17d5f8","#17e6f8","#17f5f8"];
            var artM = template.compile('<div><h3>时间: {{point.info.location_time | stampFormat:"yyyy-MM-dd hh:mm:ss"}}</h3><p>地点:--</p></div>');
            for (var i = 0; i < data.movePoints.length; i += 1) {
                var point = data.movePoints[i];
                var nextPoint = data.movePoints[i+1];

                var marker;

                var deg = 0;
                if(nextPoint) {
                    var diffX = nextPoint.pos.lat - point.pos.lat;  //经度
                    var diffY = nextPoint.pos.lng - point.pos.lng;  //纬度
                    if(diffY == 0) {
                        if(diffX > 0) {
                            deg = 90;
                        } else if(diffX < 0) {    // < 0
                            deg = -90;
                        } else {
                            deg = 0;
                        }
                    } else {
                        //http://www.cppblog.com/fwxjj/archive/2012/05/17/175147.aspx
                        deg = 180 * Math.atan2(diffY , diffX)  / Math.PI;
                    }
                }
                //将线路,按段显示不同颜色,方便了解运输方向
                // var s = Math.floor(colorList.length * (i / data.movePoints.length));    //看当前是前多少比例,然后对应的参考点,向下取整
                // var color = colorList[s];

                // icon-arrow1-copy-copy-copy 原来》类名
                marker = new AMap.Marker({
                    content: '<div class="point-move iconfont" style="transform:rotate('+deg+'deg);"><img style="width:10px;" src="/assets/img/gps/way-icon2.png" alt="" /></div>',
                    icon:"/assets/img/gps/way-icon.png",
                    position: [point.pos.lng, point.pos.lat],
                    title: '移动点:'+point.info.location_time,
                    offset: new AMap.Pixel(-3, -3),
                    zoom:14
                });
                marker.content = artM({point: point});
                marker.title = '[移动点]'+i;

                //给Marker绑定单击事件
                marker.on('click', markerClick);

                moveMarks.push(marker);
            }
            map.on('zoomchange', function (e) {
                if(!that.isShowMove) {
                    return;
                }
                // var _map = null;
                var zoom = map.getZoom();
                // if(zoom >= 13) {    //放大,才显示箭头
                //     _map = map;
                // }
                moveMarks.forEach(function (marker,index) {
                    marker.setMap(null);
                    if(zoom <= 10){
                        if(index % 15 == 0){
                            marker.setMap(map);
                        }
                    }else if(zoom > 10 && zoom <= 13){
                        if(index % 10 == 0){
                            marker.setMap(map);
                        }
                    }else if(zoom >13 && zoom <= 15){
                        if(index % 5 == 0){
                            marker.setMap(map);
                        }
                    }else{
                        marker.setMap(map);
                    }
                });

            });
            //zoom级别变化时,如果zoom>10则不显示轨迹方向,只有偏大时才显示
            $('#menu .show-transpoint-move').prop('checked', true);
            this._isInitMove = true;
        },
        _initStill: function () {
            //将中途的静止的点打上
                var artS = template.compile([
                    '<div>',
                    '<h3>留停时长: {{point.info.stay_span | secFormat}}',
                    // '<span class="set-remark" onclick="showRemarkPanel()">备注</span></h3>',
                    '<p>时间点: {{point.info.location_time | stampFormat:"yyyy-MM-dd hh:mm:ss"}}</p>',
                    '<p>间隔耗时:{{div_time_span | secFormat}}</p>',
                    '<p>地点: --- </p>',
                    '{{ if point.remarkinfo.length != 0 }}',
                    '<p>备注人:{{point.remarkinfo.remark_name || ""}}</p>',
                    '<p>时间:{{point.remarkinfo.remark_time || ""}}</p>',
                    '<p>备注:{{point.remarkinfo.remark_content || ""}}</p>',
                    '{{ /if }}',
                    '</div>'].join(''));


            for (var i = 0; i < data.stillPoints.length; i += 1) {
                var point = data.stillPoints[i];
                var lastPoint = data.stillPoints[i-1] || data.stillPoints[i];   //如果没有就用自己

                var marker;

                marker = new AMap.Marker({
                    icon:"/assets/img/gps/still-icon.png",
                    position: [point.pos.lng, point.pos.lat],
                    title: '留停点:'+point.info.location_time,
                    topWhenClick: true,
                    topWhenMouseOver: true,
                    raiseOnDrag: true,
                    draggable: true,
                    map: null
                });
                marker.content = artS({point: point, div_time_span: ((0+point.info.location_time) - (0+lastPoint.info.location_time))});
                marker.title = '[留停点]'+i;

                //给Marker绑定单击事件
                marker.on('click', function(e){
                    markerClick(e);
                    stillPointInfo = data.stillPoints[(e.target.title).substring(5)];
                });

                this.stillMarks.push(marker);
            }
            var that = this;
            map.on('zoomchange', function (e) {
                if(!that.isShowStill) {
                    return;
                }
                var _map = null;
                if(map.getZoom() > 11) {    //放大,才显示箭头
                    _map = map;
                }
                that.stillMarks.forEach(function (marker) {
                    marker.setMap(_map);
                });
            });  //zoom级别变化时,如果zoom>10则不显示轨迹方向,只有偏大时才显示
            $('#menu .show-transpoint-still').prop('checked', true);
            this._isInitStill = true;
//
        },
        _isInitAnimate: false,
        _initAnimate: function () {
            //循环变色,显示轨迹
            var moveMarksLnglat = [];
            for (var i = 0; i < data.movePoints.length; i += 1) {
                var point =  data.movePoints[i];
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
            marker.moveAlong(moveMarksLnglat,   //必须是lnglat对象数组
                    5000,   //km/H
                    function (k) {
                        return k
                    },
                    false);
            this.animateMarks.push(marker);
            $('#menu .show-transpoint-animate').prop('checked', true);
            this._isInitAnimate = true;
        },
        show: function () {
            this.lines.forEach(function (mark){
                mark.setMap(map) ;
            });
        },
        hide: function () {
            this.lines.forEach(function (mark){
                mark.setMap(null) ;
            });
        },
        isShowMove: false,
        _isInitMove: false,
        showMove: function () {
            if(!this._isInitMove) {
                this._initMove();
            }
            this.isShowMove = true;
            var zoom = map.getZoom();
            this.moveMarks.forEach(function (mark,index){
                if(zoom <= 10){
                    if(index % 15 == 0){
                        mark.setMap(map);
                    }
                }else if(zoom > 10 && zoom <= 13){
                    if(index % 10 == 0){
                        mark.setMap(map);
                    }
                }else if(zoom >13 && zoom <= 15){
                    if(index % 5 == 0){
                        mark.setMap(map);
                    }
                }else{
                    mark.setMap(map);
                }

            });
        },
        hideMove: function () {
            this.isShowMove = false;
            this.moveMarks.forEach(function (mark){
                mark.setMap(null) ;
            });
        },
        _isInitStill: false,
        isShowStill: false,
        showStill: function () {
            if(!this._isInitStill) {
                this._initStill();
            }
            this.isShowStill = true;
            this.stillMarks.forEach(function (mark){
                mark.setMap(map) ;
            });
        },
        hideStill: function () {
            this.isShowStill = false;
            this.stillMarks.forEach(function (mark){
                mark.setMap(null) ;
            });
        },
        showAnimate: function () {
            if(!this._isInitAnimate) {
                this._initAnimate();
            }
            this.animateMarks.forEach(function (mark){
                mark.setMap(map) ;
            });
        },
        hideAnimate: function () {
            if(this._isInitAnimate) {
                this.animateMarks.forEach(function (mark){
                    mark.setMap(null) ;
                });
            }
        },
        close: function () {}
    };



    // 签收点
    function LayerReceipt() {
        this.marks = [];
    }
    LayerReceipt.prototype = {
        init: function () {
            //将签收收显示上去
            var payPoints = data.payPoints;
            var artP = template.compile([
                '<p>配送超市:{{payInfo.info.market_name}}</p>',
                '<p>订单号:{{payInfo.info.shipping_order_id}}</p>',
                '<p>运单号:{{payInfo.info.waybill_no}}</p>',
                '<p>签收时间:{{payInfo.info.time | stampFormat:"yyyy-MM-dd hh:mm:ss"}}</p>',
                '<p>最近停留点时间:{{payInfo.near.time | stampFormat:"yyyy-MM-dd hh:mm:ss"}}</p>',
                '<p>配送时间:{{deliver_time | secFormat}}</p>'
            ].join(''));
            for (var i = 0; i < payPoints.length; i += 1) {
                var payInfo = payPoints[i];
                if(payInfo.pos.lng !=0 || payInfo.pos.lat !=0){


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
                    if(payInfo.info.time < payInfo.near.time) {
                        marker.setLabel({
                            offset: new AMap.Pixel(20, 20),//修改label相对于maker的位置
                            content: "时间异常"
                        });
                    }
                    marker.content = artP({payInfo: payInfo, deliver_time: payInfo.info.time - payInfo.info.startTime});
                    marker.title = '运单签收';

                    //给Marker绑定单击事件
                    marker.on('click', markerClick);
                    marker.on('dblclick', focusAndZoom);


                    this.marks.push(marker);
                }
            }
            $('#menu .show-receipt').prop('checked', true);

        },
        show: function () {
            this.marks.forEach(function (mark){
                mark.setMap(map) ;
            });
        },
        hide: function () {
            this.marks.forEach(function (mark){
                mark.setMap(null) ;
            });
        },
        close: function () {}
    };

    // 批发市场
    function LayerWholesale() {
        this.polygons = [];
        this.marks = [];
        this.guard = [];
    }
    LayerWholesale.prototype = {
        init: function () {
            var that = this;
            //将“批发市场画进去”
            data.wholesale.forEach(function (wholesaleItem) {
                var c = [wholesaleItem.pos.lng, wholesaleItem.pos.lat];   //中心点?
                var marker = new AMap.Marker({
                   icon: "/assets/img/gps/wholesale-icon.png",
                    position : c,
                    title: '批发市场',
                    map : map
                });
                marker.content = wholesaleItem.name;
                marker.title = '批发市场';
                marker.on('click', markerClick);
                marker.on('dblclick', focusAndZoom);

                var lineArr = [];
                var firstPoint = null;
                wholesaleItem.polyline.forEach(function (point) {
                    point = [point.lng, point.lat];
                    if(!firstPoint) {
                        firstPoint = point;
                    }
                    lineArr.push(point);
                });
                lineArr.push(firstPoint);   //连结成线...

                //电子围栏画线（这个是多边形）
                var polygon = new AMap.Polygon({
                    path: lineArr,          //设置线覆盖物路径
                    strokeColor: "#F33", //线颜色
                    strokeOpacity: 1, //线透明度
                    strokeWeight: 3, //线粗细度
                    fillColor: "#ee2200", //填充颜色
                    fillOpacity: 0.35 //填充透明度
                });
//                polygon.setMap(null);

                //【注意】增加停留点与所有电子围栏的距离判断,要是接近,则显示一个线框标识
//                alert('点到线的距离为：' + lnglat.distance(path) + '米');
                var near = false;
                var nearPoint = null;
                data.stillPoints.forEach(function (point) {
                    var lnglat = new AMap.LngLat(point.pos.lng, point.pos.lat);

                    var distance = lnglat.distance(lineArr);
                    if(/*distance < 100 || */polygon.contains(lnglat)) {    //100m 或者 包含多边形内
                        near = true;
                        nearPoint = lnglat;
                        console.log(lnglat);
                    }
                });
                if(!near) { //检查超市在不在围栏内
                    data.markets.forEach(function (point) {
                        var lnglat = new AMap.LngLat(point.pos.lng, point.pos.lat);

                        var distance = lnglat.distance(lineArr);
                        if(/*distance < 100 || */polygon.contains(lnglat)) {    //100m 或者 包含多边形内
                            near = true;
                            nearPoint = lnglat;
                            console.log(lnglat);
                        }
                    });
                }
                if(near) {  //画一个图形出来
//                    var circle = new AMap.Circle({
//                        map: map,
//                        center: nearPoint,          //设置线覆盖物路径
//                        radius: 200, //半径
//                        strokeColor: "#F33", //线颜色
//                        strokeOpacity: 1, //线透明度
//                        strokeWeight: 3, //线粗细度
//                        fillColor: "#ee2200", //填充颜色
//                        fillOpacity: 0.35 , //填充透明度
//                        extData: {}
//                    });
//                    circle.on('click', function(e) {
//                        console.log(e.target.getExtData());
//                        alert('触发了围栏警告,周边有批市(100m范围内)');
//                    });
//                    that.guard.push(circle);

                    //画一个警告点
                    var rippleMark; //以轨迹距离点为参照

                    rippleMark = new AMap.Marker({
                        content: '<div class="dot-ripple1 dot-rider"></div>',
                        position: nearPoint,
                        offset: new AMap.Pixel(-5,-5),
                        map: mapObj,
                    });
                    that.marks.push(rippleMark);
                }

                that.polygons.push(polygon);
                that.marks.push(marker);
            });
            var that = this;
            map.on('zoomchange', function (e) {
                var _map = null;
                if(that.isShow) {
                    if(map.getZoom() > 11) {    //放大,才显示围栏
                        _map = map;
                    }
                    that.polygons.forEach(function (marker) {
                        marker.setMap(_map);
                    });
                }
            });  //zoom级别变化时,如果zoom>10则不显示轨迹方向,只有偏大时才显示
            this.isShow = true;
            $('#menu .show-wholesale').prop('checked', true);
        },
        isShow: false,
        show: function () {
            this.isShow = true;
            this.marks.forEach(function (mark){
                mark.setMap(map) ;
            });
            this.polygons.forEach(function (line){
                line.setMap(map) ;
            });
            this.guard.forEach(function (line){
                line.setMap(map) ;
            })
        },
        hide: function () {
            this.isShow = false;
            this.marks.forEach(function (mark){
                mark.setMap(null) ;
            });
            this.polygons.forEach(function (line){
                line.setMap(null) ;
            });
            this.guard.forEach(function (line){
                line.setMap(null) ;
            })
        },
        close: function () {}
    };


    function Layer() {
        this.marks = [];
    }
    Layer.prototype = {
        init: function () {

        },
        show: function () {
            this.marks.forEach(function (mark){
                mark.setMap(map) ;
            });
        },
        hide: function () {
            this.marks.forEach(function (mark){
                mark.setMap(null) ;
            });
        },
        close: function () {}
    };


    var mapObj = new AMap.Map('container', {resizeEnable: true, zoom: 4});
    map = mapObj;   //兼容一下
    var scale = new AMap.Scale({    //比例尺
        visible: true
    });
    map.addControl(scale);


    //画超市的点
    var markers = [];
    //超市信息弹窗
    var infoWindow = new AMap.InfoWindow();



    //显示送输路线中的打点（移动+停留）
    var layerTransPoint = new LayerTransPoint();
    layerTransPoint.init();

    //显示送货订单的“超市信息”
    var layerMarket = new LayerMarket();
    layerMarket.init();
    //
    //显示签收单点信息
    var layerReceipt = new LayerReceipt();
    layerReceipt.init();

    //显示电子围栏信息
    var layerWholesale = new LayerWholesale();
    layerWholesale.init();

    ////////////////////
    //#menu 操作
    $('#menu').on('click', '.show-market', function (e){
        var $this = $(e.currentTarget);
        if($this.prop('checked')) {
            layerMarket.show();
        } else {
            layerMarket.hide();
        }
    }).on('click', '.show-receipt', function (e) {
        var $this = $(e.currentTarget);
        if($this.prop('checked')) {
            layerReceipt.show();
        } else {
            layerReceipt.hide();
        }
    }).on('click', '.show-transpoint', function (e) {
        var $this = $(e.currentTarget);
        if($this.prop('checked')) {
            layerTransPoint.show();
        } else {
            layerTransPoint.hide();
        }
    }).on('click', '.show-transpoint-move', function (e) {
        var $this = $(e.currentTarget);
        if($this.prop('checked')) {
            layerTransPoint.showMove();
        } else {
            layerTransPoint.hideMove();
        }
    }).on('click', '.show-transpoint-still', function (e) {
        var $this = $(e.currentTarget);
        if($this.prop('checked')) {
            layerTransPoint.showStill();
        } else {
            layerTransPoint.hideStill();
        }
    }).on('click', '.show-transpoint-animate', function (e) {
        var $this = $(e.currentTarget);
        if($this.prop('checked')) {
            layerTransPoint.showAnimate();
        } else {
            layerTransPoint.hideAnimate();
        }
    }).on('click', '.show-wholesale', function (e) {
        var $this = $(e.currentTarget);
        if($this.prop('checked')) {
            layerWholesale.show();
        } else {
            layerWholesale.hide();
        }
    });


    //将仓库也加进去
    // var icon = new AMap.Icon({
    //     image : 'http://vdata.amap.com/icons/b18/1/2.png',//24px*24px
    //     //icon可缺省，缺省时为默认的蓝色水滴图标，
    //     size : new AMap.Size(24,24)
    // });
    // var marker = new AMap.Marker({
    //     icon : icon,//24px*24px
    //     position : [116.517659, 40.006212],
    //     offset : new AMap.Pixel(-12,-12),
    //     map : mapObj,
    //     content: '<div class="ware-house">DC南皋</div>'
    // });




    mapObj.setFitView();


    function markerClick(e) {
        infoWindow.setContent(
                ['<h3>'+e.target.title+'</h3>','<div>'+e.target.content+'</div>'].join('')
        );
        infoWindow.open(mapObj, e.target.getPosition());
    }
    function focusAndZoom(e) {
        mapObj.setZoomAndCenter(16, e.target.getPosition());
    }
    // 注释备注相关内容
    // function showRemarkPanel(){
    //     $("#remarkPanel").slideDown();
    // }
        var warehouses =[
            {
                position:[
                    [
                        116.517659,40.006212
                    ],
                    [
                        116.519913,40.006606
                    ],
                    [
                        116.529869,40.005801
                    ],
                    [
                        116.530041,40.004354
                    ],
                    [
                        116.523453,40.002875
                    ],
                    [
                        116.522852,40.002678
                    ],
                    [
                        116.522359,40.001774
                    ],
                    [
                        116.52208,40.001609
                    ],
                    [
                        116.518689,40.00133
                    ],
                    [
                        116.517552,40.001248
                    ],
                    [
                        116.517338,40.003549
                    ],
                    [
                        116.517509,40.004042
                    ],
                    [
                        116.517681,40.004667
                    ]
                ],
                name:"DC南皋",
                center:[116.517659, 40.006212]
            },
            {
                position:[
                    [
                        117.003643,39.115615
                    ],
                    [
                        117.004694,39.115482
                    ],
                    [
                        117.007849,39.115474
                    ],
                    [
                        117.007473,39.112677
                    ],
                    [
                        117.002752,39.11271
                    ]
                ],
                name:"DC09",
                center:[117.004694, 39.115482]
            }
        ]


      warehouses.forEach(function(ware){
            var  polygon = new AMap.Polygon({
                path: ware.position,//设置多边形边界路径
                strokeColor: "#ff9102", //线颜色
                strokeOpacity: 1, //线透明度
                strokeWeight: 3,    //线宽
                fillColor: "#ffd802", //填充色
                fillOpacity: 0.5//填充透明度
            });
            polygon.setMap(map);

            var marker = new AMap.Marker({
                position : ware.center,
                offset : new AMap.Pixel(-12,-12),
                map : mapObj,
                content: '<div class="ware-house">'+ware.name+'</div>'
            });
      })

</script>

<script>
    //添加交互监听
    $("#search").on("click",function(e){
        var name = $("input[name=trans_name]").val();
        var bill = $("input[name=trans_bill]").val();
        var phone = $("input[name=trans_phone]").val();
        var date = $("input[name=date]").val();
        if(date == ""){
            alert("请选择查询时间！");
        }else{
            if(name == "" && bill == "" && phone == ""){
                alert("请至少输入一项筛选条件！");
            }else{
                if(name != ""){
                    if(phone != "" || bill != ""){
                        $("#searchBox").submit();
                    }else{
                        $.ajax({
                            url: '/gps/GetPhone',
                            type: 'GET',
                            dataType: 'json',
                            data: {name: name},
                        })
                        .done(function(response) {
                            if(response.length >1){
                                var $cellphone = $("#cellphone");
                                var $tips = $("#tips");
                                var options="";
                                response.forEach(function(driver){
                                    var option = "<div class='cell-option'><input type='radio' name='cellphone' value='"+driver.cellphone+"' />"+driver.cellphone+"</div>";
                                    options +=option;
                                });
                                console.log(options);
                                $cellphone.html(options);
                                $tips.removeClass('hidden');
                            }else{
                                $("#searchBox").submit();
                            }
                        })
                        .fail(function() {
                            alert("查询失败");
                        })
                    }

                }else{
                    $("#searchBox").submit();
                }
            }
        }

    })


    $("#searchByPhone").on("click",function(e){
        var cellphone = $("input[name=cellphone]:checked").val();
        if(cellphone == undefined){
            alert("请选择一个司机电话！")
        }else{
            $("input[name=trans_phone]").val(cellphone);
            $("#searchBox").submit();
        }
    });

    // 注释备注相关内容
    // $(".cancel").on("click",function(){
    //     $("#remarkPanel").slideUp();
    //     $("#remarkContent").val("");
    // })
    // $(".ok").on("click",function(){
    //     var result = {
    //         remarkInfo:{
    //             trans_info:transInfo,
    //             still_timestamp:stillPointInfo.info.location_time,
    //             remark_content:$("#remarkContent").val()
    //         }
    //     }
    //     console.log(result);
    //     $.ajax({
    //         url: '/gps/AddRemark',
    //         type: 'POST',
    //         data: result,
    //     })
    //     .done(function(response) {
    //         console.log(response);
    //         var res = JSON.parse(response);
    //         console.log(res);
    //         if(res.ret == 1){
    //             alert("添加成功！");
    //             $("#remarkPanel").slideUp();
    //             $("#remarkContent").val("");
    //         }else{
    //             alert(response.msg);
    //         }
    //     })
    //     .fail(function() {
    //         console.log("error");
    //     })
    //     .always(function() {
    //     });

    // })


</script>

</body>
</html>
