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
          <a  class='btn btn-primary btn-sm' href='/project/realmain'>
              <i class='glyphicon glyphicon-plus'></i>添加项目
          </a>

          <a  class='btn btn-primary btn-sm' href='/project/real?type=myname'>
              我的项目
          </a>
          <a  class='btn btn-primary btn-sm' href='/project/real'>
              所有项目
          </a>

        <div style='position:relative;padding-top:35px'>
          <table class="table table-bordered data-table">
            <thead>
              <tr class="table_header">
                <th style='width:25%'>项目名称</th>
                <th style='width:25%'>项目英文名</th>
                <th style='width:20%'>项目作者</th>
                <th style='width:10%'>项目类型</th>
                <th style='width:20%'>操作</th>
              </tr>
            </thead>
            <tbody>
              {/foreach from =$list item= item key=key/}
                <tr class="gradeX">
                  <td>{/$item.cn_name/}</td>
                   <td>{/$item.appname/}</td>
                  <td>{/$item.creater/}</td>
                  <td>
                      实时类
                  </td>
                  <td>
                    <a href='/project/realcubeeidtor?project={/$item.appname/}&id={/$item.id/}'
                   class='btn btn-primary btn-xs'>编辑</a>
                   <a href='/project/realrunlist?project={/$item.appname/}&id={/$item.id/}'
                   class='btn btn-primary btn-xs'>运行详情</a>
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

