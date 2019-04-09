<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="chrome=1">
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no, width=device-width">
    <link rel="stylesheet" href="/assets/css/visual/visual-base.css" media="screen" title="no title">
    <link rel="stylesheet" type="text/css" href="/assets/css/visual/visual-app/heatmap.css?version={/$version/}">
    <title>数据可视化--订单热力图</title>
</head>

<body>


    <div id="container" tabindex="0"></div>

    <div class="info-panel info-market">
        <span class="title">超市基础信息</span>
        <span>超市数：<span class="basic-number" id="marketNums">暂无数据</span></span>
        <span>统计超市数：<span class="basic-number" id="roundNums">暂无数据</span></span>
        <span>合计值：<span class="basic-number" id="sumNums">暂无数据</span></span>
        <span>最高值：<span class="basic-number" id="maxNums">暂无数据</span></span>
    </div>
    <div class="panel panel-right">
        <div class="title">筛选与数据获取</div>
        <div class="search-panel">
            <div class="search-region-panel">
                <label>查询区域:
                    <select class="field-control sm">
                        <option value=''>全部</option>
                    </select>
                </label>
            </div>
            <div class="search-manager-panel">
                <label>选择主管:
                    <select class="field-control sm select-manager" name="f_uid">
                        <option value=''>全部</option>
                    </select>
                </label>
            </div>
            <div class="search-sales-panel">
                <label>选择销售:
                    <select class="field-control sm select-sales" name="uid">
                        <option value=''>全部</option>
                    </select>
                </label>
            </div>
            <div>
                <label>用户范围:
                    <select class="field-control sm select-user" name="user"></select>
                </label>
            </div>
            <hr>
            <div>
                <label>查询类型:
                    <select class="field-control sm select-type" name="type"></select>
                </label>
            </div>
            <div>
                <label>超市类型:
                    <select class="field-control sm select-market" name="type"></select>
                </label>
            </div>
            <div>
                <label>查询范围:
                    <select class="field-control sm select-range" name="range"></select>
                </label>
            </div>
            <div class="btn btn-primary sm btn-get-data">获取数据</div>
        </div>
        <div class="title">功能显示</div>
        <span class="btn btn-success sm">
            <input id="drawArea" name="drawArea" type="checkbox">
            <label for="drawArea">绘制主管区域</label>
        </span>
        <span class="btn btn-disabled sm btn-draw">
        		<input id="level" class="close map" name="level" type="checkbox" disabled>
        		<label for="level">点状图</label>
      	</span>
        <div class="level-menu">
            <span title="将排序后的数据按用户输入比例归为最高级，剩余数据进行四级分层">
        峰值抹平：<input class="drop-para" name="drop-para" type="text" value=40>%
      </span>
            <span title="小于该值的统计数据将显示在地图中">
        统计去峰：<input class="maximum" name="maximum" type="text">
      </span>
            <span title="大于该值的统计数据将显示在地图中">
        统计去尾：<input class="minimum" name="minimum" type="text">
      </span>
            <div class="btn btn-warning sm drop-round-button">抹平/统计</div>
        </div>
        <span class="btn btn-disabled sm btn-draw">
  		<input id="heatmap" class="close map" name="heatmap" type="checkbox"disabled>
  		<label for="heatmap">热力图</label>
	</span>
        <div class="legend legend-level">
            <div class="menu-title">分级设色图划分</div>
            <div class="level-info"></div>
        </div>
        <div class="legend legend-heat">
            <div class="menu-title">数据分布图例</div>
            <div class="legend-layer">
                <div id="red" class="legends" style="background-color:red"></div>
                <div id="yellow" class="legends" style="background-color:#ffea00"></div>
                <div id="green" class="legends" style="background-color:rgb(0, 255, 0)"></div>
                <div id="lightBlue" class="legends" style="background-color:rgb(117,211,248)"></div>
                <div id="blue" class="legends" style="background-color:blue"></div>
            </div>
        </div>
        <div class="slide-up">收起</div>
        <div class="marker-circle-blue"></div>
    </div>

    <div class="btn btn-info sm slide-down" style="display:none">打开</div>


    <script src="//cdn.bootcss.com/jquery/2.1.4/jquery.min.js"></script>
    <!-- lodading加载组件 -->
    <script src="/assets/js/visual/loading.js?version={/$version/}"></script>
    <!-- alert弹窗组件 -->
    <script src="/assets/js/visual/alert.js?version={/$version/}"></script>
    <!-- map组件 -->
    <script src="/assets/js/map/map.js?version={/$version/}"></script>
    <!-- 分级图模块 -->
    <script src="/assets/js/levelMap.js?version={/$version/}"></script>
    <!-- 单独抽出的ajax方法 定义为$http -->
    <script src="/assets/js/visual/visual-tool/server.js?version={/$version/}"></script>
    <!-- 批市围栏绘制模块 -->
    <script src="/assets/js/visual/visual-tool/wholesale.js?version={/$version/}"></script>
    <!-- 区域切换模块 -->
    <script src="/assets/js/visual/visual-tool/zone-select.js?version={/$version/}"></script>
    <!-- 主管销售切换模块 -->
    <script src="/assets/js/visual/visual-tool/manage-sales-select.js?version={/$version/}"></script>
    <!-- 主管区域绘制模块 -->
    <script src="/assets/js/visual/visual-tool/sales-area.js?version={/$version/}"></script>
    <!-- 订单可视化主文件 -->
    <script src="/assets/js/visual/visual-app/heatmap.js?version={/$version/}"></script>
</body>

</html>
