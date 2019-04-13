{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}

<div style='height:10px'></div>
<div class='container'>
  <button  class='btn btn-primary btn-sm addTable'>
    <i class='glyphicon glyphicon-plus'></i>添加报表
  </button>
  <div style='position:relative'>
    <table class="table table-bordered data-table">
      <thead>
        <tr class="table_header">
          <th style='width:20%'>报表名称</th>
          <th style='width:20%'>报表说明</th>
          <th style='width:10%'>创建者</th>
          <th style='width:20%'>所属项目</th>
          <th style='width:20%'>操作</th>
        </tr>
      </thead>
      <tbody>
        {/foreach from =$visualList item= item key=key/}
          <tr class="gradeX">
            <td>{/$item.cn_name/}</td>
            <td>{/$item.explain/}</td>
            <td>{/$item.creater/}</td>
            <td>{/$item.project/}</td>
            <td>
              <a target='_blank' href='/report/showreport/{/$item.id/}' style='padding:3px 10px' class='btn btn-default btn-sm'>查看</a>
              <a href='/visual/VisualEditor/{/$item.id/}' style='padding:3px 10px' class='btn btn-default btn-sm'>编辑</a>
              <button style='padding:3px 10px' data-id="{/$item.id/}"
               class='btn btn-default btn-sm delVisual'>下线</button>
            </td>
          </tr>
        {//foreach/}
      </tbody>
    </table>
  </div>
</div>
<div id='addbox'>
  <form action='/report/addreport' method='post' id='tableConf'>
  <table class="table table-bordered table-condensed" style='margin:10px 0px 0px 0px'>
    <tr>
       <td style='text-align:right;width:30%'>请选择报表类型</td>
       <td>
        <select name='reporttype'>
          <option value=1>普通报表</option>
          <option value=2>概览型报表</option>
          <option value=3>对比报表</option>
        </select>
       </td>
    </tr>
    <tr>
       <td style='text-align:right;width:30%'>请选择项目</td>
       <td>
        <select name='project'>
          {/foreach from = $project item = item key=key/}
             <option value='{/$item.project/}'>{/$item.cn_name/}</option>
          {//foreach/}
        </select>
       </td>
    </tr>
  </table>
</from>
</div>
<script type="text/javascript">
  $(function(){
    //$('.addTable').hide();
    $('select').select2();
    $('#addbox').dialog({
        title: '报表配置',
        width: 450,
        height:190,
        closed: true,
        cache: false,
        modal: true,
        buttons: [{
          text:'下一步',
          iconCls:'icon-ok',
          handler:function(){
             $("#addbox").find('#tableConf').submit();             
          }
        },{
          text:'取消',
          handler:function(){
            $('#addbox').dialog('close');
          }
        }]
    });
    $("body").on("click",'.delVisual',function(){
      var id = $(this).attr('data-id');
      var obj = $(this);
      //获取当前报表对应的菜单
      $.get('/report/getmenu',{'id': id},function(data){
        $('body').unmask();
        console.log(data);
        if(data.status ==0){

            var tipInfo ="";
            var menu = data.data.menu;
            if(menu.length < 1){
               tipInfo +="";
            }else{
              tipInfo +="<h6>当前报表所属菜单</h6>";
              for(var i =0; i< menu.length ; i++){
                tipInfo +="<p style='color:red'>"+menu[i].first_menu + "=>"+menu[i].second_menu+"</p>";
              } 
            }
            var collect = data.data.collect;
            if(collect.length < 1){
               tipInfo +="";
            }else{
              tipInfo +="<h6> 当前报表被以下用户收藏</h6>";
              for(var i =0; i< collect.length; i++){
                tipInfo += "<p style='color:red'>"+ collect[i].user_name +"</p>";
              } 
            }
            tipInfo  += " 确定下线吗？";
            $.messager.confirm('提示',tipInfo, function(r){
              if(r){
               $('body').mask();
               $.get('/report/deletereport', {'id': id},function(data){
                  $('body').unmask();
                  if(data.status ==0){
                     obj.parent().parent().remove();
                  }
                  $.messager.alert('提示',data.msg,'info');
                }, 'json');
              } 
            });           
        }else{
           $.messager.alert('提示',data.msg,'info');
        }
        
      }, 'json');  
          
    });
    $('.addTable').click(function(){
      $('#addbox').dialog('open');
    });
    $('.data-table').dataTable({
      "iDisplayLength":10,
      "bJQueryUI": true,
      "sDom": '<""l>t<"F"fp>'
    });
  });
</script>
{/include file="layouts/footer.tpl"/}
