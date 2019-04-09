/***
 * 
 * @常用公共函数库
 * @returns {Boolean}
 */
function isEmptyObject(o){
    for(var n in o){
        return false;
    }
    return true;
}
//日期函数
function changeTimeFormat(time) {
      var date = new Date(time);
       var month = date.getMonth() + 1 < 10 ? "0" + (date.getMonth() + 1) : date.getMonth() + 1;
       var currentDate = date.getDate() < 10 ? "0" + date.getDate() : date.getDate();
       var hh = date.getHours() < 10 ? "0" + date.getHours() : date.getHours();
       var mm = date.getMinutes() < 10 ? "0" + date.getMinutes() : date.getMinutes();
       //time 为日期时 date.getHours为8的问题
       var temparr = time.split(' ');
       var week;
      if(date.getDay()==0)          week="周日"
      if(date.getDay()==1)          week="周一"
      if(date.getDay()==2)          week="周二"
      if(date.getDay()==3)          week="周三"
      if(date.getDay()==4)          week="周四"
      if(date.getDay()==5)          week="周五"
       if(date.getDay()==6)          week="周六"
      if((temparr.length == 1 || (temparr[1] && temparr[1]==' ')) && hh =='08' && mm =="00"){
        return date.getFullYear() + "-" + month + "-" + currentDate +" "+week;
      }else{
        return date.getFullYear() + "-" + month + "-" + currentDate +" "+hh+":"+mm +" "+week;
      }

}
//格式化时间转化成时间戳
function transdate(endTime){
  var date=new Date();
  date.setFullYear(endTime.substring(0,4));
  date.setMonth(endTime.substring(5,7)-1);
  date.setDate(endTime.substring(8,10));
  date.setHours(endTime.substring(11,13));
  date.setMinutes(endTime.substring(14,16));
  date.setSeconds(endTime.substring(17,19));
  return Date.parse(date);
}
function formatPrice(val,row){
   if(!isNaN(parseInt(val))){
       return val +"%";
   }
  try{ 
    var  objname  = $(val);
  }catch (e){
     return val;
  }
  var  objname  = $(val);
  if(typeof(objname) =='object' ){
    content = objname.find('span.data_name').text();
    var isaObj  = objname.find('span.data_name').find('a');
    var isiObj  = objname.find('span.data_name').find('i');
    var obj = $("<div id='box'></div>");
    if(content !='不存在'){
      if(isaObj.length >0){
        var href = isaObj.attr('href');

        if( isiObj.length >0){
           var newContent  =  "<a href='"+ href+"'  target='_blank'><i>"+content+"%</i></a>";
        }else{
           var newContent  =  "<a href='"+ href+"'  target='_blank'>"+content+"%</a>";
        }
       
        objname.find('span.data_name').html(newContent);
      }else{
        if( isiObj.length >0){
          var newContent = "<i>"+content+"%</i>";
        }else{
           var newContent = content+"%";
        }
        objname.find('span.data_name').html(newContent);
      }     
      obj.append(objname);
      return obj.html(); 
    }else{
      return  val;
    }
  }else{
    if(contentArr[0] !='不存在'){
      return val+'%';
    }else{
      return val;
    }
    
  }
}

function cellStyler(val,row){
  if (val >0 ){
    return 'color:red;';
  }else{
    return 'color:green;';
  }
}

function GetUrlParms(){
    var args=new Object();   
    var query=location.search.substring(1); 
    var pairs=query.split("&"); 
    for(var   i=0;i<pairs.length;i++){   
        var pos=pairs[i].indexOf('='); 
        if(pos==-1)   continue; 
        var argname=pairs[i].substring(0,pos); 
        var value=pairs[i].substring(pos+1); 
        args[argname]=decodeURI(value); 
    }
    return args;
}

/**
 * 功能常用函数
 * 获取表单数据
 */
function getSearchVal(obj,keyArr){
    var searchVal = {};
    if(keyArr){
        for(var  i=0; i< keyArr.length; i++){
            switch( keyArr[i].type){
                case 'checkbox': 
                    if(obj.find('input[name='+keyArr[i].key+']').is(":checked")  ){
                        searchVal[keyArr[i].key] = 'yes';
                    }else{
                        searchVal[keyArr[i].key] ='no';
                    }
                    break;
                case 'multiple':
                case 'select':
                    //obj.find('select[name='+keyArr[i].key+']').select2();
                    searchVal[keyArr[i].key] = $.trim(obj.find('select[name='+keyArr[i].key+']').val());
                    break;
                default:
                    searchVal[keyArr[i].key] = $.trim(obj.find('[name='+keyArr[i].key+']').val());
                    break;
                    
            }
        }
    }
    return searchVal;
}
/**
 * 功能常用函数
 * 清空表单数据
 */
function clearSearchVal(obj,keyArr){
    var searchVal = {};
    if(keyArr){
        for(var  i=0; i< keyArr.length; i++){
            switch( keyArr[i].type){
                case 'checkbox': 
                    obj.find('input[name='+keyArr[i].key+']').attr('checked',false);
                    break;
                case 'multiple':
                    obj.find('select[name='+keyArr[i].key+']').select2();
                    $.trim(obj.find('select[name='+keyArr[i].key+']').select2('val',''));
                    break;
                case 'select':
                    obj.find('select[name='+keyArr[i].key+']').select2('val','');
                    break;
                default:
                    $.trim(obj.find('[name='+keyArr[i].key+']').val(''));
                    break;
            }
        }
    }
    return searchVal;
}
/**
 * 功能常用函数
 * 设置数据
 */
function setSearchVal(obj,keyArr,data){
    var searchVal = {};
    if(keyArr){
        for(var  i=0; i< keyArr.length; i++){
            switch( keyArr[i].type){
                case 'checkbox': 
                    obj.find('input[name='+keyArr[i].key+']').attr('checked',data[keyArr[i].key]);
                    break;
                case 'multiple':
                    obj.find('select[name='+keyArr[i].key+']').select2();
                    $.trim($('select[name='+keyArr[i].key+']').select2('val',data[keyArr[i].key]));
                    break;
                case 'select':
                    obj.find('select[name='+keyArr[i].key+']').select2('val',data[keyArr[i].key]);
                    break;
                default:
                    obj.find(keyArr[i].type+'[name='+keyArr[i].key+']').val(data[keyArr[i].key]);
                    break;
            }
        }
    }
    return searchVal;
}