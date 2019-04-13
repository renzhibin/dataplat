{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">
<script src="/assets/js/project.js?version={/$version/}"></script> 
<div>
  {/include file="layouts/menu.tpl"/}
  <div id='right'>
    <div id="content" class="content" >

    <div style='height:10px'></div>
    <div class='container'>
      <div style='position:relative;padding-top:35px'>
        <table class="table table-bordered data-table">
          <thead>
            <tr  class="table_header">
              <th>角色Id</th>
              <th>角色名称</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            {/foreach from =$group item= item key=key/}
              <tr class="gradeX">
                <td>{/$item.id/}</td>
                <td>{/$item.name/}</td>
                <td>
                  <a data-id='{/$item.id/}' style='padding:3px 10px' class='btn btn-default btn-sm editorgroup'>编辑</a>
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
    $('#groupset').dialog({
    title: '角色设置',
    width: 450,
    //height:'',
    closed: true,
    cache: false,
    modal: true,
    buttons: [{
          text:'确定',
          handler:function(){   
            var id =$("#groupset").find('input[name=groupId]').val();
            var  user_name = $("#groupset").find('select[name=userlist]').select2('val');
            $.get('/auth/SaveGroup', {'id':id,user_name:user_name},function(data){
                $.messager.alert('提示',data.msg,'info');
                $('#groupset').dialog('close');
            }, 'json');
          }
        },{
          text:'取消',
          handler:function(){
            $('#groupset').dialog('close');
          }
        }]
    });
    $("body").on("click",'.editorgroup',function(){
      var id = $(this).attr('data-id');
      var obj = $(this);
      $('body').mask();
      $.get('/auth/GetGroup', {'id': id},function(data){
            $('body').unmask();
            if(data.status ==0){
                $("#groupset").find('input[name=groupId]').val(id);
                if(data.data.length >0){
                    var optionArr  =[];
                    for(var i=0; i<data.data.length; i++){
                       optionArr.push(data.data[i].user_name);
                    }
                    $("#groupset").find('select[name=userlist]').select2('val',optionArr);
                }
                $("#groupset").dialog('open');
            }else{
                $.messager.alert('提示',data.msg,'info');
            }
       }, 'json');
      });

  });
</script>

<!--基本信息设置-->
<div id="groupset">
  <table class='table table-condensed table-bordered'>
   <tr>
    <td class='table_left'>当前组所有用户</td>
    <td>
        <select name='userlist' multiple class='userlist' style='width:300px'>
            {/foreach from =$userlist item= item key=key/}
                <option value='{/$item.username/}'>{/$item.username/}</option>
            {//foreach/}
        </select>
        <input type='hidden' name='groupId'/>
    </td>
   </tr>
    
  </table>
</div>
{/include file="layouts/footer.tpl"/}