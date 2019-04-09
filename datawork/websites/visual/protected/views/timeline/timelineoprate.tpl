{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">
<style type="text/css">
  .inputall{ width: 140px; height: 22px; line-height: 22px}
  .left{ float: left; width: 15%;}
  .box{ border-bottom: 1px solid #eee; padding: 10px;clear: both;}
  .right{ float: left; width: 85% }
</style>
<script src="/assets/js/project.js?version={/$version/}"></script> 
<script type="text/javascript">
   var project ='{/$project_name/}';
</script>

<div>
  {/include file="layouts/menu.tpl"/}
  <div id='right'>
    <div id="content" class="content">

      <div style='height:10px'></div>
      <div class='container'>
        <div class="panel panel-info">
        <div class="panel-heading">
          {/if  $event_id  neq  0  /} 
              timeline编辑
          {/else/}
              timeline添加
          {//if/}
          <a href='/Addition/showtimeline?event_id={/$event_id/}' class='pull-right'>返回列表</a>
        </div>
        <div class="panel-body" style='padding:5px'>
           <div class="box">
             <div class="left">时间线名称<br>(请尽量与项目中文名称同名)</div>
             <div class="right project_name" >
                  {/if  $event_id  neq  0  /} 
                      <input type="text" id='event_name'  class="form-control" value="{/$project[0].event_name/}" />
                  {/else/}
                      <input type="text" id='event_name' class="form-control" value="" />
                  {//if/}
                  <div class="error_box" style="color:red"></div>
             </div>
              <input type="hidden" id='event_id' value="{/$event_id/}" />
             <div class="clearfix"></div>
           </div>
           <div class="box">
             <div class="left">事件<b style='color:red'>*</b></div>
             <div class="right" id='eventBox'></div>
             <div class="clearfix"></div>
           </div>
           <div class="box" style="padding-left:10%">
                <button class="btn btn-primary saveInfo btn-sm">保存</button>
           </div>
        </div>
        </div>
      </div>

    </div>
  </div>
</div>
{/include file="layouts/menujs.tpl"/}

<script id='eventtpl' type='text/x-dot-template'>
  <table class='table table-bordered table-condensed' id='tableBox' style='margin:0px'>
    <tr style="background-color:#ddd">
       <td style="width:10%">开始时间</td>
       <td style="width:10%">结束时间</td>
       <td style="width:30%">标题</td>
       <td>摘要</td>
       <td>操作</td>
    </tr>
    <tbody id='groupid' class='gradebox'>
      {{~it:coloum:item}} 
        <tr>
          <td style='width:10%'>
            <input name='startDate'  type='date' style='line-height:20px'  value='{{ if (coloum.startDate !=undefined){  }}{{=coloum.startDate}}{{ } }}' />
          </td>
          <td  style='width:10%'>
            <input name='endDate'  type='date' style='line-height:20px' value='{{ if (coloum.endDate !=undefined){  }}{{=coloum.endDate}}{{ } }}' /> 
          </td>
          <td>
            <input style='width:100%' type='text' value='{{ if (coloum.headline !=undefined){  }}{{=coloum.headline}}{{ } }}'/>
           </td>
          <td>
            <textarea style='width:100%'>{{ if (coloum.text !=undefined){  }}{{=coloum.text}}{{  } }}</textarea> 
          </td> 
          <td><button class='btn btn-default btn-xs  eventdel'>删除</button</td>
        </tr>
      {{~}}
    </tbdoy>
  </table>
  <button class="addEvent">添加</button>
</script>
<script type="text/javascript">
  {/if $eventData !=''/}
      var eventData = {/$eventData/};
  {/else/}
      var eventData ={};
  {//if/}
</script>
<script type="text/javascript" src="/assets/js/timeline.js?version={/$version/}"></script>