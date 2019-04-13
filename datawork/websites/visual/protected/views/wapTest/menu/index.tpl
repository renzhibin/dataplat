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
        <a  class='btn btn-primary btn-sm' href='/menu/add'>
          <i class='glyphicon glyphicon-plus'></i>添加二级菜单
        </a>
        <a style='padding:5px 10px;' href='/menu/sort' class='btn btn-primary btn-sm sortMenu'>二级菜单排序</a><br>
        <span style='color:#999;padding-left:5px'> 
        
        <div style='position:relative;padding-top:35px'>
          <table class="table table-bordered data-table">
            <thead>
              <tr  class="table_header">
                <th>二级菜单名称</th>
                <th>一级菜单名称(父级菜单)</th>
                <th>创建人</th>
                <th>操作</th>
              </tr>
            </thead>
            <tbody>
              {/foreach from =$list item= item key=key/}
                <tr class="gradeX">
                  <td>{/$item.second_menu/}</td>
                  <td>
                    {/$item.first_menu/}
                  </td>
                  <td>{/$item.user_name/}</td>
                  <td>
                    <a href='/menu/editor?id={/$item.id/}' style='padding:3px 10px' class='btn btn-default btn-sm'>编辑</a>
                    <button style='padding:3px 10px' data_id='{/$item.id/}' class='btn btn-default btn-sm deleteMenu'>下线</button>
                    <a style='padding:3px 10px' href='/menu/report?sort_id={/$item.id/}' class='btn btn-default btn-sm'>报表排序</a>
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
