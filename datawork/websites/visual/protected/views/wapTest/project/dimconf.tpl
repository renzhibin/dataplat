{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">
<style type="text/css">
.spanrow{ display: block;  }
.center { text-align: center; }
h5{ font-weight:bold; }
</style>

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
    <div style='position:relative;padding-top:35px'>
       <table class="table table-bordered data-table">
        <thead>
          <tr class="table_header">
            <th width="5%" class="center">序号</th>
            <th width="15%">项目名称</th>
            <th width="20%">维度组合</th>
            <th width="30%">指标组合</th>
            <th width="30%">sql字段组合</th>

          </tr>
        </thead>
        <tbody>
          {/foreach from=$result.data.dim_met item=item key=key/}
            <tr class="gradeX">
             <td class="center">{/$key+1/}</td>
             <td>{/$project/}</td>
             <td>
              {/foreach from=$item.dim_names item=value /}
                <span class="spanrow">{/$value/}</span>
              {//foreach/}
             </td>
             <td>
              {/foreach from=$item.met_sets item=value /}
                <span class="spanrow">{/$value/}</span>
              {//foreach/}
             </td>
              <td>
                  {/foreach from=$item.met_cols item=value /}
                  <span class="spanrow">{/$value/}</span>
                  {//foreach/}
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

<script type='text/javascript'>
   var project ='';
   $(function(){
      $('select').select2();
      // $('#dashboard').datagrid();
      $('.data-table').dataTable({
        "iDisplayLength":10,
        "bJQueryUI": true,
        "sPaginationType": "full_numbers",
        "sDom": '<""l>t<"F"fp>',
        "bSort":false,
        "bPaginate":false,
        "rownumbers":true,
         "oLanguage": {
            'sSearch':'搜索:',
              "sLengthMenu": "每页显示 _MENU_ 条记录",
               "oPaginate":{
                 "sFirst":"第一页",
                 "sLast":"最后一页",
                 "sNext": "下一页",
                 "sPrevious": "上一页"
               },
               "sInfoEmtpy": "没有数据",
               "sZeroRecords": "没有检索到数据",
          }

      });
   });
</script>