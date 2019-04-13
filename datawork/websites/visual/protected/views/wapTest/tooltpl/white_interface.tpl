{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}"
      xmlns="http://www.w3.org/1999/html">
<link rel="stylesheet" href="/assets/css/url-interface.css?version={/$version/}">

<div id="right">
        {/include file='layouts/menu.tpl'/}
        <div class="container-filter">
            <!--面包屑效果-->
            <div id="breadcrumbs-one">
                <span><a href="../visual/index">首页</a></span>
                <span>></span>
                <span><a href="../visual/toolguider">常用工具</a></span>
                <span>></span>
                <span><a>预设数据</a></span>
            </div>
            <div class="function-buttions">
                <a class="add-item btn btn-primary" data-toggle="modal" data-target="#myModal">新增接口白名单</a>
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
                            <th class="col-md-1">id</th>
                            <th class="col-md-1">接口名称</th>
                            <th class="col-md-2">接口URL</th>
                            <th class="col-md-6">refers</th>
                            <th class="col-md-1">操作</th>
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
 <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
   <div class="modal-dialog">
     <div class="modal-content">
       <div class="modal-header">
         <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
         <h4 class="modal-title" id="myModalLabel">接口白名单管理</h4>
       </div>
       <div class="modal-body">
       </div>
       <div class="modal-footer">
         <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
         <button type="button" class="btn btn-primary submit">保存</button>
       </div>
     </div>
   </div>
 </div>
 <script>
     var data ={/json_encode($data)/} ||[];
 </script>
  <script src="/assets/js/artTemplate.js?version={/$version/}"></script>
 <script src="/assets/js/tool/white-interface.js?version={/$version/}"></script>
