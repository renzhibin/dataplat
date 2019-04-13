function IsURL(URL){
  var str=URL;

  var Expression=/http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/;
  var objExp=new RegExp(Expression);
  if(objExp.test(str)==true){
  return true;
  }else{
  return false;
  }
} 
function saveMenu(url){
    var  statu = 1;
    statu = checkData();
    if(statu){
      $('#fm').form('submit',{
          url: url,
          onSubmit: function(){
              return $(this).form('validate');
          },
          success: function(result){  
              var result = eval('('+result+')');
              if(result.status == 0){
                  $.messager.alert('提示',result.msg);   
                  var url  = '/visual/toolguiderlist';
                  window.location.href = url;
              }else{
                  $.messager.alert('提示',result.msg,'error');
              }
          }
      });
    }
}
function checkData(){
  if( $.trim($("input[name=name]").select2('val')) =='' ){
      $.messager.alert('提示','工具名称不能为空','info');
      return false;
  }
  if( $.trim($("input[name=content]").val()) =='' ){
      $.messager.alert('提示','注释不能为空','info');
      return false;
  }
    if( $.trim($("input[name=url]").val()) =='' ){
        $.messager.alert('提示','链接不能为空','info');
        return false;
    }
    if( $.trim($("input[name=icon]").val()) =='' ){
        $.messager.alert('提示','图标不能为空','info');
        return false;
    }
  return 1;
}
$(function(){
    $('body').on('change','.menuUrlType',function(){
      if($(this).val() ==1){
         $('.openUrl').hide();
         $('.innerUrl').show();
      }else{
         $('.innerUrl').hide();
         $('.openUrl').show();
      }
    });
    $('.opratecheck').click(function(){
         var url = "/visual/tooladdsave";
         saveMenu(url);   
    });
    if("undefined" != typeof id){
       $("#fm").find('input[name=id]').val(id);

        for(var i in menuInfo){

           switch( i ){
           case 'parent_id':
              $('#fm').find("select[name=parent_id]").select2('val',menuInfo[i]);
               break;
               case 'icon':
                 $('#fm').find(".icon-input").val(menuInfo[i]);
                   $('#fm').find(".icon-input").next().next().next().attr('class',menuInfo[i]);
                 break;
               case 'new_window':
                 $('#fm').find("select[name=new_window]").val(menuInfo[i]);
           break;

           // case 'type':
           //    $('#fm').find("select[name=type]").select2('val',menuInfo[i]);
           //    $('#fm').find("select[name=type]").trigger('change',menuInfo[i]);
           default:
            $("#fm").find('input[name='+i+']').val(menuInfo[i]);
           break;
         }
         
       }
       
    }
  })