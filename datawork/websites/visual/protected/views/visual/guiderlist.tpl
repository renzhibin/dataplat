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
        <a  class='btn btn-primary btn-sm' href='/visual/toolguideradd'>
          <i class='glyphicon glyphicon-plus'></i>添加工具
        </a>
        <a style='padding:5px 10px;' href='/visual/toolsort' class='btn btn-primary btn-sm sortMenu'>工具排序</a>
        <span style='color: red; padding-left: 5px; font-size: 13px;'>
        注：工具分类（增/删/改）需高玉石批准，详情咨询杨玉龙
        </span>
        <div style='position:relative;padding-top:35px'>
          <table class="table table-bordered data-table">
            <thead>
              <tr  class="table_header">
                <th>工具名称</th>
                <th>分类名称(父级菜单)</th>
                <th>创建人</th>
                <th>操作</th>
              </tr>
            </thead>
            <tbody>
              {/foreach from =$list item= item key=key/}
                <tr class="gradeX">
                  <td>{/$item.name/}</td>
                  <td>
                    {/$item.parent_name/}
                  </td>
                  <td>{/$item.user_name/}</td>
                  <td>
                    <a href='/visual/editool?id={/$item.id/}' style='padding:3px 10px' class='btn btn-default btn-sm'>编辑</a>
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
<script type="text/javascript">
  $(function(){
    $("body").on("click",'.deleteMenu',function(){
      var id = $(this).attr('data_id');
      var obj = $(this);
      $.messager.confirm('提示', '确定下线吗？', function(r){
        if(r){
         $('body').mask();
         $.get('/menu/deleteMenu', {'id': id},function(data){
            $('body').unmask();
            if(data.status ==0){
               obj.parent().parent().remove();
            }
            $.messager.alert('提示',data.msg,'info');
          }, 'json');
        }
      });     
    });
  });
</script>