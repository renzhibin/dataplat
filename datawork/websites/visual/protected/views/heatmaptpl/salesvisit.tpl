<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>销售拜访可视化</title>
    <link rel="stylesheet" type="text/css" href="/assets/js/sparks/css/jquery-ui.css" />
    <link rel="stylesheet" href="/assets/css/timepicker.css" media="screen" title="no title">
    <link rel="stylesheet" href="/assets/css/visual/visual-app/salesvisit.css" media="screen" title="no title">
    <link rel="stylesheet" href="/assets/css/visual/visual-base.css" media="screen" title="no title">
  </head>
  <body>
    <div class="mod-sales-visit">
        <div id="mapContainer"></div>
        <div class="unfold btn btn-info sm">展开</div>
        <div class="panel-part">
            <div class="panel operation">
              <div class="title">操作</div>
              <div class="container">
                <div class="opera-item">选择日期:<input type="text" class="search-time field-control lg time" id="time" placeholder="查询时间"></div>
                <div class="opera-item search-region-panel">查询地区:<select class="field-control lg zone"></select></div>
                <div class="opera-item search-manager-panel">选择主管:<select class="field-control lg manage"></select></div>
                <div class="opera-item search-sales-panel">选择销售:<select class="field-control lg sales"></select></div>
              </div>
              <div class="btn btn-primary submit sm">确定</div>
            </div>
            <div class="panel legend">
                <div class="title">图例</div>
                <div class="legend-item market-icon visit">销售拜访点</div>
                <div class="legend-item market-icon market">超市点</div>
                <span class="pack-up">收起</span>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="/assets/js/sparks/js/jquery1.7.js"></script>
    <script type="text/javascript" src="/assets/js/sparks/js/jquery-ui.js"></script>
    <script type="text/javascript" src="/assets/js/sparks/js/jquery-ui-slide.min.js"></script>
    <script type="text/javascript" src="/assets/js/sparks/js/jquery-ui-timepicker-addon.js"></script>
    <script type="text/javascript" src="/assets/js/map/map.js?version={/$version/}"></script>
    <script src="/assets/js/visual/loading.js?version={/$version/}"></script>
    <script src="/assets/js/visual/alert.js?version={/$version/}"></script>
    <script src="/assets/js/visual/visual-tool/server.js?version={/$version/}"></script>
    <script src="/assets/js/visual/visual-tool/zone-select.js?version={/$version/}"></script>
    <script src="/assets/js/visual/visual-tool/manage-sales-select.js?version={/$version/}"></script>
    <script type="text/javascript">
        var SALES = JSON.parse({/json_encode($saler_list)/});
    </script>
    <script type="text/javascript" src="/assets/js/visual/visual-app/salesvisit.js?version={/$version/}"></script>
  </body>
</html>
