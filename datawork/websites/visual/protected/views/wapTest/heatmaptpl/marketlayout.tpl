<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="chrome=1">
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no, width=device-width">
    <link rel="stylesheet" href="/assets/css/visual/visual-base.css" media="screen" title="no title">
    <link rel="stylesheet" href="/assets/css/visual/visual-app/marketlayout.css" media="screen" title="no title">
    <title>数据可视化--链商高德超市对比图</title>
</head>

<body>


    <div id="container" tabindex="0"></div>

    <div class="info-panel info-market">
        <span>高德超市数：<span class="basic-number" id="amapMarketNums">暂无数据</span></span>
        <span>百度超市数：<span class="basic-number" id="bmapMarketNums">暂无数据</span></span>
        <span>优供超市数：<span class="basic-number" id="marketNums">暂无数据</span></span>
    </div>
    <div class="panel panel-contrast">
        <div class="title">筛选与数据获取</div>
        <!-- 区域筛选组件 -->
        <div class="search-panel">
          <label>查询区域:
              <select class="field-control sm"></select>
          </label>
        </div>
        <div class="search-btn-list">
          <div id="searchAmapButton" class="btn btn-primary sm">高德</div>
          <div id="searchBmapButton" class="btn btn-primary sm">百度</div>
          <div id="searchButton" class="btn btn-primary sm">优供</div>
        </div>
        <div class="title">功能显示</div>
        <span class="amap btn btn-success sm btn-disabled">
            <input id="amap" class="close map" name="amap" type="checkbox" disabled>
            <label for="amap">高德超市</label>
        </span>
        <span class="bmap btn btn-success sm btn-disabled">
            <input id="bmap" class="close map" name="bmap" type="checkbox" disabled>
            <label for="bmap">百度超市</label>
        </span>
        <span class="lsh btn btn-success sm btn-disabled">
        		<input id="lsh" class="close map" name="lsh" type="checkbox" disabled>
        		<label for="lsh">优供超市</label>
      	</span>
        <hr>
        <span class="btn btn-success sm can-check">
            <input id="drawArea" name="drawArea" type="checkbox">
            <label for="drawArea">绘制主管区域</label>
        </span>
        <span class="btn btn-success sm contrast can-check">
            <input id="contrast" class="close map" name="contrast" type="checkbox">
            <label for="contrast">优供高德对比</label>
        </span>
        <span class="btn btn-success sm contrast-market btn-disabled">
            <input id="contrastMarket" class="close map" name="contrastMarket" type="checkbox" disabled>
            <label for="contrastMarket">对比区内超市</label>
        </span>
        <div class="slide-up">收起</div>
        <div class="marker-circle-blue"></div>
    </div>
    <div class="panel panel-legend">
      <div class="legend-part">
        <span class="title">超市标注说明</span>
        <div class="">
            <span class="market-tip gd-market-icon">高德超市</span><br>
            <span class="market-tip bd-market-icon">百度超市</span><br>
            <span class="market-tip yg-market-icon">优供超市</span>

        </div>
      </div>
      <div class="legend-part">
        <div class="slide-up-legend">收起</div>
        <span class="title">对比区域说明</span>
        <div class="">
          <span class="contrast-tip low-area">1.高德>5且高德/优供>2&nbsp;&nbsp;2.高德-优供>15</span><br>
          <span class="contrast-tip high-area">1.高德>30且高德/优供>3&nbsp;&nbsp;2.高德-优供>30</span>
        </div>
      </div>
    </div>

    <div class="slide-down btn btn-info sm" style="display:none">打开面板</div>
    <div class="slide-down-legend btn btn-warning sm">打开图例</div>


    <script src="//cdn.bootcss.com/jquery/2.1.4/jquery.min.js"></script>
    <!-- lodading加载组件 -->
    <script src="/assets/js/visual/loading.js?version={/$version/}"></script>
    <!-- alert弹窗组件 -->
    <script src="/assets/js/visual/alert.js?version={/$version/}"></script>
    <!-- map组件 -->
    <script src="/assets/js/map/map.js?version={/$version/}"></script>
    <!-- 单独抽出的ajax方法 定义为$http -->
    <script src="/assets/js/visual/visual-tool/server.js?version={/$version/}"></script>
    <!-- 区域切换模块 -->
    <script src="/assets/js/visual/visual-tool/zone-select.js?version={/$version/}"></script>
    <!-- 主管区域绘制模块 -->
    <script src="/assets/js/visual/visual-tool/sales-area.js?version={/$version/}"></script>
    <!-- 优供高德对比可视化主文件 -->
    <script src="/assets/js/visual/visual-app/marketLayout.js?version={/$version/}"></script>

</body>
</html>
