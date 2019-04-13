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
              var urlinfo = $(this).find('textarea[name=url]').val();
              if(urlinfo !=''){
                urlArr = urlinfo.split("\n");
                for(var i=0; i< urlArr.length; i++){
                  var urlStrArr = urlArr[i].split(":");
                  if(urlStrArr[1] !=undefined){
                      urlStrArr.splice(0,1);
                      httpStr = urlStrArr.splice(0,1);
                      checkUrl = httpStr+":"+urlStrArr.join("");
                    if(!IsURL(checkUrl)){
                     $.messager.alert('提示','请填写有效的url地址','error');
                       return false;
                    }
                  }else{
                    $.messager.alert('提示','请按name:url格式填写','error');
                    return false;
                  }
                }
              }
              return $(this).form('validate');
              
          },
          success: function(result){  
              var result = eval('('+result+')');
              if(result.status == 0){
                  $.messager.alert('提示',result.msg);   
                  var url  = '/menu/index';
                  window.location.href = url;
              }else{
                  $.messager.alert('提示',result.msg,'error');
              }
          }
      });
    }
}
function checkData(){
  if( $.trim($("input[name=first_menu]").select2('val')) =='' ){
      $.messager.alert('提示','一级菜单不能为空','info');
      return false;
  }
  if( $.trim($("input[name=second_menu]").val()) =='' ){
      $.messager.alert('提示','二级菜单不能为空','info');
      return false;
  }
  if( $('.innerUrl').find(".inputall").select2('val').length <1  && $('.openUrl').find("input[name=url]").val() =='' ){
    $.messager.alert('提示','请为菜单添加报表或者外链','info');
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
         var url = "/menu/addsave";
         saveMenu(url);   
    });
    if("undefined" != typeof id){
       $("#fm").find('input[name=menu_id]').val(id);
       for(var i in menuInfo){
         switch( i ){
           case 'table_id':
              $('.innerUrl').find(".inputall").select2('val',menuInfo[i].split(","));
              break;
            case 'url':
              $('.openUrl').find('[name=url]').val(menuInfo[i]);
              break;
           case 'first_menu':
              $('#fm').find("select[name=first_menu]").select2('val',menuInfo[i]);
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