{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">

<script src="/assets/js/project.js?version={/$version/}"></script>
<div>
  {/include file="layouts/menu.tpl"/}
  <div id='right'>
      <div id="content" class="content-top" >
          <!--面包屑效果-->
          <div id="breadcrumbs-one">
              {/foreach from = $guider item= place key=key/}
              {/if $guider[0] eq $place /}
              <span><a href="{/$place.href/}">{/$place.content/}</a></span>
              {/else/}
                {/if $place.href eq '#'/}
                    <span></span><span>{/$place.content/}</span>
                {/else/}
                    <span></span><span><a href="{/$place.href/}">{/$place.content/}</a></span>
                {//if/}
              {//if/}
              {//foreach/}
          </div>

        <div style='height:10px'></div>
        <div class='container'>
            <div>
                <div style='position:relative;padding-top:35px'>
                    <table class="table table-bordered data-table">
                    <thead>
                    <tr class="table_header">
                        <th style='width: 7%'>序号</th>
                        <th style='width: 7%'>邮件ID</th>
                        <th style='width: 25%'>邮件标题</th>
                        <th style='width: 15%'>发送日期</th>
                        <th style='width: 15%'>开始时间</th>
                        <th style="width: 15%">结束时间</th>
                        <th style="width: 8%">发送状态</th>
                        <th style='width: 8%'>发送类型</th>
                    </tr>
                    </thead>
                    <tbody>
                    {/foreach from =$list item= item key=key/}
                    <tr class="gradeX">
                        <td>{/$key + 1/}</td>
                        <td>{/$item.mail_id/}</td>
                        <td>{/$item.mail_title/}</td>
                        <td>{/$item.send_date/}</td>
                        <td>{/$item.start_at/}</td>
                        <td>{/$item.end_at/}</td>
                        <td>
                            {/if  $item.send_status eq 1/}
                                <b style="color: red">成功</b>
                            {/else/}
                                <b style="color: green">失败</b>
                            {//if/}
                        </td>
                        <td>
                            {/if  $item.send_type eq 1/}
                                <b style="color: red">例行</b>
                            {/else/}
                                <b style="color: green">手动</b>
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
</div>
{/include file="layouts/menujs.tpl"/}