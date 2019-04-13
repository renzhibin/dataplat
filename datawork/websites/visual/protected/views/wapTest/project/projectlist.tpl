{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">

<script src="/assets/js/project.js?version={/$version/}"></script> 

<div>
  {/include file="layouts/menu.tpl"/}
  <div id='right'>
    <div id="content" class="content">
        <!--面包屑效果-->
        <div id="breadcrumbs-one">
            {/foreach from = $guider item= place key=key/}
            {/if $guider[0] eq $place /}
            <span><a href="{/$place.href/}">{/$place.content/}</a></span>
            {/else/}
            {/if $place.href eq '#'/}
            <span>></span><span>{/$place.content/}</span>
            {/else/}
            <span>></span><span><a href="{/$place.href/}">{/$place.content/}</a></span>
            {//if/}
            {//if/}
            {//foreach/}
        </div>
      <div style='height:10px'></div>
      <div class='container'>
          <a  class='btn btn-primary btn-sm' href='/project/main'>
              <i class='glyphicon glyphicon-plus'></i>添加项目
          </a>

          <a  class='btn btn-primary btn-sm' href='/project/index?type=myname'>
              我的项目
          </a>
          <a  class='btn btn-primary btn-sm' href='/project/index'>
              所有项目
          </a>

        <div style='position:relative;padding-top:35px'>
          <table class="table table-bordered data-table">
            <thead>
              <tr class="table_header">
                <th style='width:10%'>项目名称</th>
                <th style='width:5%'>项目英文名</th>
                <th style='width:5%'>项目作者</th>
                <th style='width:10%'>项目类型</th>
               <!-- <th style='width:10%'>项目说明</th>-->
               <!-- <th style='width:10%'>项目周期</th>-->
                <th style='width:25%'>操作</th>
                <th style='width:15%'>项目报表</th>
              </tr>
            </thead>
            <tbody>
              {/foreach from =$list item= item key=key/}
                <tr class="gradeX">
                  <td>{/$item.cn_name/}</td>
                   <td>{/$item.project/}</td>
                  <td>{/$item.creater/}</td>
                  <td>
                    {/if $item.hql_type && $item.hql_type == '2' /}
                    调度类
                    {/else/}
                    报表类
                    {//if/}
                   
                  </td>
                  <td>
                    <a href='/project/cubeeidtor?project={/$item.project/}&id={/$item.id/}'
                   class='btn btn-primary btn-xs'>编辑</a>
                   <a href='/project/runlist?project={/$item.project/}&id={/$item.id/}'
                   class='btn btn-primary btn-xs'
                   >运行详情</a>
                    {/if $item.hql_type && $item.hql_type == '1' /}
                   <a href='/visual/VisualConfig?project={/$item.project/}' class='btn btn-primary btn-xs'>多维查询</a>
                   <a href='/project/dimconf?project={/$item.project/}' class='btn btn-primary btn-xs'>配置查询</a>
                   {//if/}

                  </td>
                  <td>
                    {/if $item.hql_type and $item.hql_type == '1' /}
                     <a href='/report/reportlist?project={/$item.project/}' class='btn btn-default btn-xs'>查看</a>
                        <a href="/report/addreport?project={/$item.project/}" class='btn btn-default btn-xs addTable' data-option='{/$item.project/}'>
                       新建报表
                     </a>
                       
                     {//if/}
                  </td>
                  
                </tr>
              {//foreach/}
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>
{/include file="layouts/menujs.tpl"/}

