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
    <link rel="stylesheet" type="text/css" href="/assets/css/visual/visual-base.css?version={/$version/}">
    <link rel="stylesheet" type="text/css" href="/assets/css/visual/visual-app/gps.css?version={/$version/}">
    <title>数据可视化--运输轨迹图</title>
</head>
<body>
<div id="container" tabindex="0"></div>
<div class="panel panel-query">
    <form id="searchBox" action="" method="get">
        <label style="display:none">数据源:
            <input name="version" readonly value="2" type="text">
        </label>
        <label class="search-label search-name" style="{/if $smarty.get.search_scence === '2'/}display:none;{//if/}">司机姓名:
            <input class="search-value field-control sm search-name-input" name="trans_name" value="{/$smarty.get.trans_name/}" type="text">
        </label>
        <label class="search-label search-phone" style="{/if $smarty.get.search_scence === '2'/}display:none;{//if/}">司机电话:
            <input  class="search-value search-phone-input  field-control sm" name="trans_phone" value="{/$smarty.get.trans_phone/}" type="text">
        </label>
        <label class="search-label search-order" style="{/if $smarty.get.search_scence == '1' || $smarty.get.search_scence == '3'/}display:none;{//if/}">运单号:
            <input  class="search-value  field-control sm search-order-input" name="trans_bill" value="{/$smarty.get.trans_bill/}" type="text">
        </label>
        <label class="search-label search-time" style="{/if $smarty.get.search_scence == '2'/}display:none;{//if/}">开始日期:
            <input  class="search-value search-time-input field-control sm" type="date" name="start_date" value="{/if $smarty.get.search_scence !== '2'/}{/if $smarty.get.start_date/}{/$smarty.get.start_date|escape/}{/else/}{/$smarty.now|date_format:'%Y-%m-%d'/}{//if/}{//if/}" />
        </label>
        <label class="search-label search-time" style="{/if $smarty.get.search_scence == '2'/}display:none;{//if/}">结束日期:
            <input  class="search-value search-time-input field-control sm" type="date" name="end_date" value="{/if $smarty.get.search_scence !== '2'/}{/if $smarty.get.end_date/}{/$smarty.get.end_date|escape/}{/else/}{/$smarty.now|date_format:'%Y-%m-%d'/}{//if/}{//if/}" />
        </label>
        <label for="search_scence">查询方式:
            <select class="field-control sm" name="search_scence">
              <option {/if $smarty.get.search_scence === "1"/}selected {//if/} value="1">司机</option>
              <option {/if $smarty.get.search_scence === "2"/}selected {//if/}  value="2">运单</option>
            </select>
        </label>
        <span class="search-btn btn btn-primary sm">查询</span>
        <span class="retract-btn query-retract-btn btn btn-primary sm">收起</span>
    </form>
</div>
<div class="mobile-menu">
    <span class="action">功能</span>
    <div class="panel">
        <div class="menu-item" data-type="searchInfo">查询信息</div>
        <div class="menu-item" data-type="driver">在运司机</div>
        <div class="menu-item" data-type="label">标注显示</div>
    </div>
</div>
<div id="menu" class="panel panel-right">
    <div><label><input type="checkbox" class="show-market field-control sm" />显示超市</label></div>
    <div><label><input type="checkbox" class="show-receipt field-control sm" />显示签收点</label></div>
    <div><label><input type="checkbox" class="show-transpoint field-control sm" />显示运输路线</label></div>
    <div><label><input type="checkbox" class="show-transpoint-move field-control sm" />--移动点</label></div>
    <div><label><input type="checkbox" class="show-transpoint-still field-control sm" />--静止点</label></div>
    <div><label><input type="checkbox" class="show-transpoint-animate field-control sm" />--模拟动画</label></div>
    <div><label><input type="checkbox" class="show-wholesale field-control sm" />显示电子围栏</label></div>
    <div><label><input type="checkbox" class="show-seqIcons field-control sm" />显示TMS排线顺序</label></div>
    <div class="retract-btn menu-retract-btn">收起</div>
</div>
<div class="close btn btn-info sm driver-list-btn">在运司机</div>
<div id="driverList" class="info-driverlist">
    <div class="driver-list-content">
      <div class="info-panel driver-list"></div>
      <div class="driver-search-btn">查看</div>
      <div class="retract-btn driver-retract-btn">收起</div>
    </div>
</div>
<div class="close btn btn-success sm market-search-btn">超市定位</div>
<div class="info-panel info-market-search">
      <input placeholder="超市id" type="text" class="field-control sm market-address-id" />
      <div class="btn btn-warning sm search-submit-btn">确定</div>
</div>

<div class="modal hidden">
    <div class="tip-message">出现重名司机，请进一步选择司机联系电话：</div>
    <div id="cellphone"></div>
    <div id="searchByPhone">确定</div>
</div>

<script type="text/javascript" src="http://webapi.amap.com/maps?v=1.3&key=9c9aefb35bc32899c6284b482007e3e4&plugin=AMap.Scale,AMap.OverView,AMap.ToolBar"></script>
<script src="//cdn.bootcss.com/jquery/2.1.4/jquery.min.js"></script>
<script src="/assets/js/artTemplate.js?version={/$version/}"></script>
<script src="/assets/js/visual/alert.js?version={/$version/}"></script>
<script type="text/javascript">
    var transInfo = {/json_encode($transInfo)/};
    var transUserList = {/json_encode($gpstransuserlist)/};
    // 获取smarty模板返回信息
    var smartyData = {
            gpsPosition: {/json_encode($gpsPosition)/},
            waybillInfos: {/json_encode($waybillInfos)/},
            wholesalerZone: {/json_encode($wholesalerZone)/}
    };
    var scence = {/$smarty.get.search_scence/};
</script>
<script src="/assets/js/visual/visual-app/gps.js?version={/$version/}"></script>
</body>
</html>
