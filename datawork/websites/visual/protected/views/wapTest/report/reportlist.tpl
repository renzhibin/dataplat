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
        <button  class='btn btn-primary btn-sm addTable'>
          <i class='glyphicon glyphicon-plus'></i>添加报表
        </button>
        <div style='position:relative;padding-top:35px'>
          <table class="table table-bordered data-table">
            <thead>
              <tr class="table_header">
                <th style='width:5%'>报表id</th>
                <th style='width:20%'>报表名称</th>
                <th style='width:20%'>报表说明</th>
                <th style='width:10%'>创建者</th>
                <th style='width:10%'>创建时间</th>
                <th style='width:10%'>报表类型</th>
                <th style='width:20%'>所属项目</th>
                <th style='width:25%'>操作</th>
              </tr>
            </thead>
            <tbody>
              {/foreach from =$visualList item= item key=key/}
                <tr class="gradeX">
                  <td  style='width:5%'>{/$item.id/}</td>
                  <td  style='width:20%'>{/$item.cn_name/}</td>
                  <td  style='width:10%'>{/$item.explain/}</td>
                  <td  style='width:10%'>{/$item.chinese_name/}</td>
                  {/if $item.create_date neq '0000-00-00 00:00:00'/}
                      <td  style='width:10%'>{/$item.create_date/}</td>
                  {/else/}
                      <td  style='width:10%'></td>
                  {//if/}
                   <td  style='width:10%'>
                      {/if $item.type eq 1/}
                         普通报表
                      {/elseif $item.type eq 2/}
                          对比报表
                      {/elseif $item.type eq 3/}
                          衍生报表
                      {/elseif $item.type eq 4/}
                          离线报表
                       {/elseif $item.type eq 5/}
                          主副报表
                       {/elseif $item.type eq 6/}
                         数据图报表
                       {/elseif $item.type eq 7/}
                          聚合报表
                       {/elseif $item.type eq 8/}
                        自定义报表
                      {/else/}
                          普通报表
                      {//if/}
                   </td>
                  <td  style='width:20%'>
                    <a  target='_blank' href='/project/cubeeidtor?project={/$item.project/}&id={/$item.pid/}'>{/$item.pname/}</a>
                  </td>
                  <td style='width:25%'>
                        {/if $item.flag eq 1/}
                      <!-- 报表小工具  -->
                          {/if $item.type eq 4/}
            
                            <a href='/tool/editreport/{/$item.id/}' style='padding:3px 10px' class='btn btn-default btn-sm'>编辑</a>
                              <a target='_blank' href='/report/showreport/{/$item.id/}' style='padding:3px 10px' class='btn btn-default btn-sm'>查看</a>
                              <button style='padding:3px 10px' data-id="{/$item.id/}"
                             class='btn btn-default btn-sm delVisual'>下线</button>
                             <a target='_blank' data-id='{/$item.id/}' data-type='{/$item.type/}' style='padding:3px 10px'  class='btn btn-default btn-sm showMore'>更多</a>
                             <div class="powerbox" style="display:inline-block;margin-top:3px"></div>
                          {/elseif $item.type eq 1 or $item.type eq 2 or $item.type eq 5  or $item.type eq 7 or $item.type eq 8/}
                            <a href='/report/editorreport/{/$item.id/}'
                                  style='padding:3px 10px' class='btn btn-default btn-sm'>编辑</a>
                                  <a target='_blank' href='/report/showreport/{/$item.id/}' style='padding:3px 10px' class='btn btn-default btn-sm'>查看</a>
                            <button style='padding:3px 10px' data-id="{/$item.id/}"
                             class='btn btn-default btn-sm delVisual'>下线</button>
                            <a target='_blank' data-id='{/$item.id/}' data-type='{/$item.type/}' style='padding:3px 10px'  class='btn btn-default btn-sm showMore'>更多</a>
                            <div class="powerbox" style="display:inline-block;margin-top:3px"></div>
                            {/else/}
                             <a target='_blank' href='/report/showreport/{/$item.id/}' style='padding:3px 10px' class='btn btn-default btn-sm'>查看</a>
                            <button style='padding:3px 10px' data-id="{/$item.id/}"
                             class='btn btn-default btn-sm delVisual'>下线</button>
                            <a target='_blank' data-id='{/$item.id/}' data-type='{/$item.type/}' style='padding:3px 10px'  class='btn btn-default btn-sm showMore'>更多</a>
                            <div class="powerbox" style="display:inline-block;margin-top:3px"></div>
                            <!-- {/if $item.code eq 0 /}
                                  <a target='_blank' data-id='{/$item.id/}' style='padding:3px 10px'  class='btn btn-primary btn-sm editorgroup'>申请审核</a>
                             {/elseif $item.code eq 1/}
                                  <a target='_blank' data-id='{/$item.id/}' style='padding:3px 10px' disabled class='btn btn-warning btn-sm'>待审核</a>
                             {/else $item.code eq 2 /}
                                  <a target='_blank' data-id='{/$item.id/}' style='padding:3px 10px' disabled class='btn btn-success btn-sm'>已审核</a>
                             {//if/} -->
                            {//if/}

                        {/else/}
                               <button style='padding:3px 10px' data-id="{/$item.id/}"
                              class='btn btn-default btn-sm showofflinereport'>查看</button>
                              <button style='padding:3px 10px' data-id="{/$item.id/}"
                              class='btn btn-default btn-sm enableVisual'>上线</button>
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
<!--权限设置-->
<div id="powerset">
  <table class='table table-condensed table-bordered'>
   <tr>
    <td class='table_left'>权限设置</td>
    <td>
        <select name='userlist' multiple class='userlist' style='width:300px'>
            {/foreach from =$group item= item key=key/}
              <option value="{/$item.id/}">{/$item.name/}</option>
            {//foreach/}
        </select>
        <input type='hidden' name='groupId'/>
    </td>
   </tr>
    
  </table>
</div>
<script type="text/javascript">
  $(function(){
    // $('select').select2();
    /*$('#addbox').dialog({
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
             var name  = $("#addbox").find('select[name=project]').select2('val');
             if(name =='filter_not'){
                $.messager.alert('提示','请选择项目','info');
                return;
             }else{
                $("#addbox").find('#tableConf').submit(); 
             }              
          }
        },{
          text:'取消',
          handler:function(){
            $('#addbox').dialog('close');
          }
        }]
    });*/

    $('#powerset').dialog({
        title: '权限设置',
        width: 450,
        //height:'',
        closed: true,
        cache: false,
        modal: true,
        buttons: [{
              text:'确定',
              handler:function(){   
                var id =$("#powerset").find('input[name=groupId]').val();
                var  group = $("#powerset").find('select[name=userlist]').select2('val');
                if(group.length <0){
                  $.messager.alert('提示','角色不能为空','info');
                  return;
                }
                $.get('/report/saveAuth', {'id':id,group:group},function(data){
                    $.messager.alert('提示',data.msg,'info');
                    $('#powerset').dialog('close');
                }, 'json');
              }
            },{
              text:'取消',
              handler:function(){
                $('#powerset').dialog('close');
              }
            }]
    });
    //申请审核
    $("body").on("click",'.editorgroup',function(){
      var id = $(this).attr('data-id');
      var obj = $(this);
      $('body').mask('正在提交审核...');
      $.get('/auth/AddAuthPoint', {'id':id},function(data){
            $('body').unmask();
            if(data.status ==0){
                $.messager.alert('提示',data.msg,'info');
                window.location.reload();
            }else{
                $.messager.alert('提示',data.msg,'info');
            }
       }, 'json');
      });
    //获取更多信息
    $("body").on("click",'.showMore',function(){
      var id = $(this).attr('data-id');
      var type = $(this).attr('data-type');
      var obj = $(this);
      //$('body').mask('数据正在加载...');
      $.get('/report/GetReportPower', {'id':id},function(data){
            //$('body').unmask();
            if(data.status ==0){
              var html ="";
                switch(parseInt(data.data.code)){
                   case 0:
                      html +="<a target='_blank' data-id='"+id+"' style='padding:3px 10px'  class='btn btn-primary btn-sm editorgroup'>申请审核</a>";
                      break;
                   case 1:
                      html +="<a target='_blank' data-id='"+id+"' style='padding:3px 10px' disabled class='btn btn-warning btn-sm'>待审核</a>";
                      break;
                   case 2:                        
                      html +="<a target='_blank' data-id='"+id+"' style='padding:3px 10px' disabled class='btn btn-success btn-sm'>已审核</a>";
                      break;
                    default:
                      html +="<a target='_blank' data-id='"+id+"' style='padding:3px 10px'  class='btn btn-primary btn-sm editorgroup'>申请审核</a>";
                      break;
                }
                //复制报表按钮
                if(type !='4'){
                  html+=" <a target='_blank' data-id='"+id+"' style='padding:3px 10px'  class='btn btn-default btn-sm copyReportBtn'>复制</a>";
                }
                
                obj.parent().find('.powerbox').html(html);
                obj.hide();
            }else{
                $.messager.alert('提示',data.msg,'info');
            }
       }, 'json');
    });

    $("body").on("click",'.delVisual',function(){
      var id = $(this).attr('data-id');
      var obj = $(this);
      //获取当前报表对应的菜单
      $.get('/report/getmenu',{'id': id},function(data){
        $('body').unmask();
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
                tipInfo += "<p style='color:red'>"+ collect[i].chinese_name +"</p>";
              } 
            }
            tipInfo  += " 确定下线吗？";
            $.messager.confirm('提示',tipInfo, function(r){
              if(r){
               $('body').mask();
               $.get('/report/deletereport', {'id': id},function(data){
                  $('body').unmask();
                  if(data.status ==0){
                     //obj.parent().parent().remove();
                     location.reload();
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
      window.location.href = '/report/addreport';
      //$("#addbox").dialog("move",{top:$(document).scrollTop() + ($(window).height()-400) * 0.5});
      //$('#addbox').dialog('open');
    });
  
      $("body").on("click",'.enableVisual',function(){
          var id = $(this).attr('data-id');
          var obj = $(this);
          //console.log(id);
          $.get('/report/upReport',{'id': id},function(data){

              if(data.status ==0){
                  $.messager.alert('提示',data.msg,'info');
                  location.reload();
                  //obj.parent().parent().remove();
                  //obj.removeClass('.enableVisual').addClass('.delVisual');
                  //obj.text('下线');
              }


          }, 'json');

      });
      //从前端发送请求以查看下线了的报表
      $("body").on("click",'.showofflinereport',function(){
          var id = $(this).attr('data-id');
          var temp = document.createElement("form");
          temp.action = '/report/showreport/'+id;
          temp.method = "post";
          temp.style.display = "none";
          temp.target = '_'
          temp.setAttribute("target" , '_blank');

          var newElement = document.createElement("input");
          newElement.name = 'isOffline';
          newElement.value = '1';
          // alert(opt.name)
          temp.appendChild(newElement);

          document.body.appendChild(temp);
          temp.submit();
          return temp;

      });

      //2015-06-29 复制报表 
      $('body').on('click','.copyReportBtn',function(){
        var id = $(this).attr('data-id');
        $.ajax({'url':'/report/copyReport',type:'post',
            data:{"id":id},
            success:function(data){
              var data=JSON.parse(data);
              if(data.status=='0'){
                window.location.reload();
              } else {
                alert(data.msg);
                return false;
              }
            },
            error:function(){
              console.log('net error');
            }
        });

      });


  });
</script>
