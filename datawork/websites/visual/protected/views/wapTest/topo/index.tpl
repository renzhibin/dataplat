<!DOCTYPE html>
<html lang="en">
    <head>
        <title>任务系统</title><meta charset="UTF-8" />
        <link href="/assets/lib/bootstrap-3.3/css/bootstrap.min.css" rel="stylesheet" />
        <link href="/assets/lib/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css" rel="stylesheet" />
        <link href="/assets/css/sankey.css" rel="stylesheet" />
        <link href="/assets/lib/select2/select2.css" rel="stylesheet" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    </head>

    <body>

      <!-- echarts容器 -->
      <div id="main" style="margin:0 auto;width:100%;height:2000px"></div>

      <!-- 图例 -->
      <div class="panel panel-default panel-legend">
        <div class="panel-heading">图例 <span class="pull-right slide-up">收起</span></div>
        <div class="panel-body">
          <div class="legend-item">
            <span class="legend-color legend-ready"></span>
            <span class="legend-des">就绪</span>
          </div>
          <div class="legend-item">
            <span class="legend-color legend-waitting"></span>
            <span class="legend-des">阻塞</span>
          </div>
          <div class="legend-item">
            <span class="legend-color legend-doing"></span>
            <span class="legend-des">进行中</span>
          </div>
          <div class="legend-item">
            <span class="legend-color legend-success"></span>
            <span class="legend-des">完成</span>
          </div>
          <div class="legend-item">
            <span class="legend-color legend-failed"></span>
            <span class="legend-des">失败</span>
          </div>
          <div class="legend-item">
            <span class="legend-color legend-other"></span>
            <span class="legend-des">其它</span>
          </div>
        </div>
      </div>

      <!-- 查询面板 -->
      <div class="panel panel-default panel-search">
        <div class="panel-body">
          <form class="form-inline">
            <div class="form-group">
             <label class="control-label">任务</label>
             <select id="selectMultiple" class="select-multiple"  multiple>
             </select>
           </div>
           <div class="form-group">
            <label class="control-label">查看任务类型</label>
            <select class="form-control task-type" data-live-search="true">
                <option selected value=0>上下游</option>
                <option value=1>上游</option>
                <option value=2>下游</option>
            </select>
          </div>
          <span class="btn btn-success pull-right submit">确认</span>
          </form>

        </div>
      </div>

      <!-- 显示面板按钮 -->

      <div class="btn btn-info slide-down" style="display: none;">展开图例</div>

      <!-- Modal -->
      <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
              <h4 class="modal-title" id="myModalLabel">Modal title</h4>
            </div>
            <div class="modal-body">
              <div>任务平台：<span class="platform task-item"></span></div>
              <div>创建人：<span class="creater task-item"></span></div>
              <div>开始时间：<span class="startTime task-item"></span></div>
              <div>结束时间：<span class="endTime task-item"></span></div>
              <div>数据量：<span class="dataSize task-item"></span></div>
              <div>重跑时间：<input class="form-control run-date"></div>
              <div>重跑类型：
                <select class="form-control run-type" name="">
                  <option value="parent">上游</option>
                  <option value="child">下游</option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-warning pull-left upstream">上游任务</button>
              <button type="button" class="btn btn-info pull-left downstream">下游任务</button>
              <button type="button" class="btn btn-primary restart">任务重跑</button>
              <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
            </div>
          </div>
        </div>
      </div>

      <script src="http://cdn.bootcss.com/jquery/2.1.4/jquery.min.js"></script>
      <script src="/assets/lib/bootstrap-3.3/js/bootstrap.min.js" charset="utf-8"></script>
      <script src="/assets/lib/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>

      <script src="/assets/js/topo/echarts.min.js" charset="utf-8"></script>
      <script src="/assets/lib/select2/select2.min.js"></script>
      <!-- alert弹窗组件 -->
      <script src="/assets/js/visual/alert.js?version={/$version/}"></script>
      <script src="/assets/js/visual/confirm.js?version={/$version/}"></script>
      <script src="/assets/js/visual/visual-tool/server.js?version={/$version/}"></script>
      <script src="/assets/js/topo/index.js" charset="utf-8"></script>


    </body>
</html>
