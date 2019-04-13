{/include file="layouts/header.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}"
      xmlns="http://www.w3.org/1999/html">
<link rel="stylesheet" href="/assets/css/filter-edit.css?version={/$version/}">
{/include file="layouts/script.tpl"/}
<div id="right">
    {/include file='layouts/menu.tpl'/}
    <div class="container-filter">
        <!--面包屑效果-->
        <div id="breadcrumbs-one">
            <span><a href="../visual/index">首页</a></span>
            <span>></span>
            <span><a href="../tool/listMapData">预设数据</a></span>
            <span>></span>
            <span>数据详情</span>
        </div>
        <div class="details-panel">
            <form class="form-horizontal" role="form">
                <fieldset class="fieldset">
                    <div class="form-group">
                        <label for="cn-name" class="col-sm-1 control-label">中文名称</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="cn-name" placeholder="中文名称">
                        </div>
                        <span class="required-filed cn-required col-sm-2">*</span>
                    </div>
                    <div class="form-group">
                        <label for="en-name" class="col-sm-1 control-label">英文名称</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="en-name" placeholder="英文名称">
                        </div>
                        <span class="required-filed en-required col-sm-2">*</span>
                    </div>
                    <div class="form-group">
                        <label for="filter-data" class="col-sm-1 control-label">过滤条件</label>
                        <div class="col-sm-4">
                            <textarea class="form-control sql" id="mapData" rows="5" placeholder="select key,value from table_name"></textarea>
                        </div>
                        <span class="required-filed mapdata-required sol-sm-2"></span>
                    </div>
                    <div class="form-group">
                        <label for="filter-data" class="col-sm-1 control-label">sql结果</label>
                        <div class="col-sm-4">
                            <textarea class="form-control sql-result" id="sqlData" rows="5" placeholder="key:value" disabled></textarea>
                        </div>
                        <span class="required-filed sql-required sol-sm-2"></span>
                    </div>
                </fieldset>
                <div class="form-group">
                    <div class="col-sm-offset-1 col-sm-1 area-view area-hide">
                        <input type="button" class="btn btn-success view-result" value="查看sql结果">
                    </div>
                    <div class="col-sm-offset-1 col-sm-1 area-checkout area-hide">
                        <input type="button" class="btn btn-warning checkout-result" value="验证过滤条件">
                    </div>
                    <div class="col-sm-offset-1 col-sm-1 area-confirm area-hide">
                        <input type="button" class="btn btn-primary confirm" value="确定">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal fade alert-win">
            <div class="modal-sm modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">提示</h4>
                    </div>
                    <div class="modal-body">操作成功</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">知道了</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{/include file="layouts/menujs.tpl"/}
<script>
    $(function () {
        var successSql;
        var query = queryLocationSearch();
        var $btnConfirm = $(".confirm");
        var $viewResult = $(".view-result");
        var $checkResult = $(".checkout-result");
        var $areaView = $(".area-view");
        var $areaConfirm = $(".area-confirm");
        var $areaCheck = $(".area-checkout");
        var $enName = $("#en-name"),
            $cnName = $("#cn-name"),
            $mapData = $("#mapData"),
            $sqlData = $("#sqlData");

        if (query.type === "view") {
            $(".fieldset").attr("disabled", "disabled");
            $areaView.show();
            $(".required-filed").hide();
            renderView();
        }

        if (query.type === "add") {
            $areaConfirm.show();
            $areaCheck.show();
            $btnConfirm.attr("disabled","disabled");
            renderView();
        }

        if (query.type === "edit") {
            $enName.attr("disabled", "disabled");
            $areaConfirm.show();
            $areaCheck.show();
            $btnConfirm.attr("disabled","disabled");
            renderView();
        }

        $enName.focus(function () {
            $(".en-required").text("*");
        });

        // 英文名输入框失焦时校验输入的英文名
        $enName.blur(function () {
            var enNameVal = $enName.val().replace(/(^\s*)|(\s*$)/g, "") || "";
            var data = {
                mapkey: enNameVal,
                retu_type: 'checkMapKey'
            };
            if (enNameVal && enNameVal != "") {
                syncData("/tool/MapData",data,checkEnName);
                function checkEnName(response){
                  if (response && response.data && response.data.mapdata && response.data.mapdata.length > 0) {
                      $(".en-required").text("该英文名称已存在");
                      $(".confirm").attr("disabled", "disabled");
                      return false;
                  }
                }
            }
        });

        // 点击"确定" 提交修改或新增的数据
        $btnConfirm.on('click', function () {
            $(".cn-required").text("*");
            $(".en-required").text("*");
            $(".mapdata-required").text("");

            var cnNameVal = $cnName.val().replace(/(^\s*)|(\s*$)/g, "") || "";
            var enNameVal = $enName.val().replace(/(^\s*)|(\s*$)/g, "") || "";
            var mapDataVal = $mapData.val().replace(/(^\s*)|(\s*$)/g, "") || "";

            if (cnNameVal == "") {
                $(".cn-required").text("中文名称不能为空");
                return false;
            }
            if (enNameVal == "") {
                $(".en-required").text("英文名称不能为空");
                return false;
            }
            if(successSql != $mapData.val()){
                $(".mapdata-required").text("过滤条件进行了调整,需要重新校验!");
                return false;
            }
            var data = {
                mapname: cnNameVal,
                mapkey: enNameVal,
                map_data: mapDataVal
            };
            syncData("/tool/saveMapData",data,submit)

            function submit(response){
                $(".alert-win .modal-body").text("修改成功!");
                $(".alert-win").modal();
                $(".alert-win").on("hide.bs.modal",function() {
                    location.href="/tool/listMapData"
                })
            }
        });

        $viewResult.on("click",function(){
            var mapData ={map_data:$mapData.val()};
            syncData("/tool/CheckMapData",mapData,showResult);
            function showResult(response){
              if(response.data.length>0){
                $sqlData.val(response.data.join('\n'));
              }
            }
        });

        $checkResult.on("click",function(){
            var mapData ={map_data:$mapData.val()};
            $(".mapdata-required").text("");
            syncData("/tool/CheckMapData",mapData,checkResult);
            function checkResult(response){
              if(response.data.length>0){
                $sqlData.val(response.data.join('\n'));
                $btnConfirm.attr("disabled",null);
                successSql = $mapData.val();
              }
            }
        });
    });

    function renderView() {
        var data ={/json_encode($mapdata)/};
        if(data && data.length > 0){
            $("#cn-name").val(data[0].map_name || "");
            $("#en-name").val(data[0].map_key || "");
            $("#mapData").text(data[0].map_data || "");
        }
    }

    function queryLocationSearch() {
        var result = {};
        var rSpace = /\+/g;
        var search = window.location.search.substr(1);

        if (search.length) {
            var params = search.split("&");
            for (var i = 0, length = params.length; i < length; i++) {
                var item = params[i].split("=");
                var key = decodeURIComponent(item[0]) || "";
                var value = decodeURIComponent(item[1]) || "";
                result[key.replace(rSpace, ' ')] = value.replace(rSpace, "");
            }
        }
        return result
    }


    function syncData(url,params,callback){
      $.ajax({
          url: url,
          data: params,
          type: "post",
          success: function (response) {
              var res = JSON.parse(response);
              if(res.status === 0){
                callback(res);
              }else{
                $(".alert-win .modal-body").text(res.msg);
                $(".alert-win").modal();
              }
          },
          error: function () {
              $(".alert-win .modal-body").text("出错啦");
              $(".alert-win").modal();
          }
      });
    }

</script>
