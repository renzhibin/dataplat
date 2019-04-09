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
          <div style='position:relative'>
             <span>请选择分类：</span>
             <select class="menuChange" style='width:300px'>
                <option value='filter_not'>--请选择--</option>
                {/foreach from =$menuinfo item= item key=key/}
                  <option value="{/$item.id/}">{/$item.name/}</option>
                {//foreach/}
             </select><a  class="pull-right" href='/menu/index'>返回列表页</a><br>
             <span class='tipinfo' style='display:none'>(拖拽表格行进行排序)</span>
             <div class="changebox"></div>
             <button class="sortTool btn btn-primary btn-sm" style="display:none" >排序保存</button>
             
          </div>
        </div>


    </div>
  </div>
</div>
{/include file="layouts/menujs.tpl"/}
<script type='text/x-dot-template' id ='menulisttpl'>
      <div style='height:5px'></div>
      <table class="table table-condensed table-bordered" >
      <thead>
        <tr class="table_header">
          <td>菜单Id</td>
          <td>名称</td>
        </tr>
      </thead>
      <tbody id ='sourtmenutable'>
        {{~it :fiexd:key}} 
        <tr>
             <td>{{=fiexd.id}}</td>
             <td>{{=fiexd.name}}</td>
        </tr>
        {{~}} 
      </tbody>  
      </table>
</script>
<script type="text/javascript">
  $(function(){
    $('body').on('change','.menuChange',function(){
        var first_menu = $(this).val();
        if(first_menu =='filter_not'){
          return false;
        }
        if(first_menu  ==''){
           $.messager.alert('提示','请选择一级菜单','info');
           return false;
        }
        $.get('/visual/gettool', {'sort': first_menu},
        function(data){
          if(data.status ==0){
              var interText = doT.template($("#menulisttpl").text());
              $(".changebox").html(interText(data.data));

              $("#sourtmenutable").dragsort({
                  dragSelector : "tr",
                  dragSelectorExclude:"button,input,textarea",
                  dragEnd : function(){
                  },
                  scrollSpeed:0,
              });
              
              $('.sortTool').show();
              $('.tipinfo').show();
          }else{
            $.messager.alert('提示',data.msg,'info');
          }         
        },'json');
    });
    $("body").on("click",'.sortTool',function(){
      //获取信息
      var obj = [];
      var sort_id = $('.menuChange').select2('val');
      $('#sourtmenutable').find('tr').each(function(){
         var oneObj ={};
         oneObj.id = $(this).find('td').eq(0).text();
         oneObj.second_menu = $(this).find('td').eq(1).text();
         obj.push(oneObj);
      });
      //$('body').mask();
      $.post('/visual/savesorttool', {'sortinfo':obj,'parent_id':sort_id},function(data){
        //$('body').unmask();
        if(data.status ==0){
             $.messager.alert('提示',data.msg,'info');
        }else{
             $.messager.alert('提示',data.msg,'info');
        }
      }, 'json');   
    });
  });
</script>