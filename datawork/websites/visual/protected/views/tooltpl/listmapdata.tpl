{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}"
      xmlns="http://www.w3.org/1999/html">
<link rel="stylesheet" href="/assets/css/filter-list.css?version={/$version/}">

<div id="right">
        {/include file='layouts/menu.tpl'/}
        <div  id="content" class="container-filter content content-top">
            <!--面包屑效果-->
            <div id="breadcrumbs-one" class="breadcrumbs-left">
                <span><a href="../visual/index">首页</a></span>
                <span></span>
                <span><a href="../visual/toolguider">常用工具</a></span>
                <span></span>
                <span>预设数据</span>
            </div>
            <div class="function-buttions">
                <a class="add-item btn btn-primary " href="/tool/MapData?type=add">新增预设数据</a>
            </div>
            <div class="function-buttions  find-filter-wrap">
                <label>搜索:<input type="search" id="find-filter-data"></label>
            </div>
            <div class="table-list">
                <div class="table-responsive">
                    <table class="table  table-hover">
                        <thead class="title-list">
                        <tr>
                            <th class="col-md-1">序号</th>
                            <th class="col-md-2">中文名</th>
                            <th class="col-md-2">英文名</th>
                            <th class="col-md-2">创建人</th>
                            <th class="col-md-2">最近操作人</th>
                            <th class="col-md-3">操作</th>
                        </tr>
                        </thead>
                        <tbody class="item-list"></tbody>
                    </table>
                </div>
            </div>
            <div class="col-sm-12 pagination-wrap">
                <ul class="pagination"></ul>
            </div>
        </div>
        <div>
{/include file="layouts/menujs.tpl"/}

<script>
    var data ={/json_encode($mapdata)/} ||[];
 </script>
<script src="/assets/js/listMapData.js?version={/$version/}"></script>
