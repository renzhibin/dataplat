//获取数据源
// function getSource(){
//   var visualParam ={};
//   visualParam.project = project;
//   visualParam.group = [];
//   visualParam.metric = [];
//   $('input.grouplist').each(function(k,v){     
//     if($(this).is(":checked")){
//       var one ={};
//       one.key = $(this).attr('dimensions');
//       one.name = $.trim($(this).parent().text());
//       one.explain = $.trim($(this).attr('explain'));
//       visualParam.group.push(one);
//     }
//   }); 
//   //获取指标
//   $(".metriclist").each(function(){  
//      //if( this.style.display !='none'){
//       if($(this).hasClass('checked')){
//         var one ={};
//         one.key = $(this).attr('name');
//         one.name = $.trim($(this).text());
//         one.explain = $.trim($(this).attr('explain'));
//         visualParam.metric.push(one);
//      }
//   });  
//   return visualParam;
// }
//获取英文列
// function getAllInfo(){
//   var params = getSource();
//   if(!params){
//     return false;
//   }
//   var tableMap = getExcelMap(params);
//   var  udcInfo = getUdc(tableMap);
//   params.group = getMte(params.group);
//   params.metric = getMte(params.metric);
//   var startTime = $('input[name=startTime]').val();
//   var endTime = $('input[name=endTime]').val();
//   if(startTime ==undefined){
//     params.date =  endTime;
//     params.edate = endTime;
//   }else{
//     params.date =  startTime;
//     params.edate = endTime;
//   }  
//   params.udc  = udcInfo.udc;
//   params.udcconf  = udcInfo.udcconf;  
//   //处理时间问题
//   return params;
// }
//表格ajax

//获取表格数据 并保存到本地临时变量 localTableData
//id = $('#reportgrade')/$('#contrasreport')
// function getTableData($obj){
//   /*var localgrade = {};
//       localgrade.sort =[];
//       localgrade.showsort =[];
//       localgrade.search =[];
//       localgrade.fiexd=[];//固定列
//       localgrade.isfiexd=1;//新增固定列的标识
//       localgrade.orderbyarr = [];
//       localgrade.isorderby=1;//新增是否排序列的标识
//       localgrade.percent =[];
//       localgrade.showGrade = {};// 显示隐藏的指标数据
//       grade.otherlink={};//钻取 其他链接  格式{"key":"url"}

//       localgrade.udcnameArr = []; //udc的name数组
//       var udcInfo ={};
//       udcInfo.udcArr =[];
//       udcInfo.udcconf =[];
//       localTableData.filter =[];
//       var  allArr =[];
//       var  realArr =[];*/

//      localTableData.filter =[];

//       //fiexd＝》固定列 isfiexd=1 ＝》新增固定列的标识  isorderby=1 ＝》新增是否排序列的标识  
//       //otherlink＝》钻取链接 格式{"key":"url"}
//       var localgrade = {'sort':[],'showsort':[],'search':[],'fiexd':[],'isfiexd':1,'orderbyarr':[],'isorderby':1,'percent':[],'showGrade':{},'otherlink':{},'udcnameArr':[] },
//           udcInfo = {"udcArr":[],"udcconf":[]},
//           allArr=[],realArr =[];


//       $obj.find('.gradebox').find('tr').each(function(){
//           //获取sort
//           var tempval ='';
//           if($(this).find('.reportkey').find('textarea').length >0){
//             //处理udc 隐藏的表格数据
//             tempval= $.trim($(this).find('.reportkey').find('textarea').val());
        
//             var  expObj ={};
//             expObj.name      = $.trim($(this).find('.reportkey').find('textarea').val()); 
//             expObj.cn_name   = $.trim($(this).find('.reportname').find('textarea').val()); 
//             expObj.explain   = $.trim($(this).find('.reportexplain').find('textarea').val());
//             expObj.expression= $.trim($(this).find('.reportexpression').find('textarea').val());
//             expObj.udc = expObj.name+"="+expObj.expression;
//             udcInfo.udcArr.push(expObj.udc);
//             udcInfo.udcconf.push(expObj);
//             localgrade.udcnameArr.push(expObj.name);
//           }else{
//             tempval = $.trim($(this).find('.reportkey b').text());
      
//           }
//           //显示的数据列
//           localgrade.showsort.push(tempval);
//           localgrade.showGrade[tempval] = false; 
//           if(!$(this).find('.operate').find('input').is(":checked")){
//               localgrade.sort.push(tempval);
//               localgrade.showGrade[tempval] = true; 
//           }
//           //表格列是否固定
//           if($(this).find('.fixed').find('input.isfixed').is(":checked")){
//             localgrade.fiexd.push(tempval);
//           }

//           //表格列是否排序
//           if($(this).find('.orderbyarr').find('input.coloumOrder').is(":checked")){
//             localgrade.orderbyarr.push(tempval);
//           }

//           //表格列钻取功能
//           var templink = $(this).find('td.linkbox input.otherlink').val();
//           if( templink != ''){
//             localgrade.otherlink[tempval] = templink;
//           }

//           //获取搜索
//           if($(this).find('.isfilter').is(":checked") && !$(this).find('.operate').find('input').is(":checked")){
//             var  onesearch ={};
//             if($(this).find('.reportsearch').attr('data-config') != undefined && $(this).find('.reportsearch').attr('data-config') !='' ){
//                onesearch = eval("("+ $(this).find('.reportsearch').attr('data-config')+" )") ;          
//             }else{
//                //如果是输入框 获取输入框的值， 如果不是 获取td 的 text
//                if($(this).find('.reportkey').find('textarea').length >0){
//                   onesearch.reportkey = $.trim($(this).find('.reportkey').find('textarea').val()); 
//                }else{
//                   onesearch.reportkey = $.trim($(this).find('.reportkey b').text());
//                }

//             }
//             //设置是否是精确查找 对比报表设为精确查找
//             if($(this).find('.accurate').hasClass("btn-primary") || type == '2'){
//                 onesearch.is_accurate = 1;
//             }else{
//                 onesearch.is_accurate = 0;
//             }

//             if(onesearch.reportkey  !='all'){
//               localgrade.search.push(onesearch);
//             }
//           }
//           //获取百分比
//           if($(this).find(".ispercent").is(":checked")){
//              if($(this).find('.reportkey').find('textarea').length >0){
//                 localgrade.percent.push($.trim($(this).find('.reportkey').find('textarea').val()));
//              }else{
//                 localgrade.percent.push($(this).find('.reportkey b').text());
//              }      
//           }
//           //获取filter
//           var filterMark =$.trim($(this).find('.reportf').find('[name=op]').val());
//           var filterVal = $.trim($(this).find('.reportf').find('.op_val').val());
          
//           if(filterMark !='filter_not' && filterVal !=''){
//             var filterOne ={};
//             var textareaTag = $(this).find('.reportkey textarea');
//             var reportkey = '';

//             if(textareaTag.length > 0){
//               reportkey = textareaTag.val();
//             } else { 
//               reportkey = $(this).find('.reportkey b').text();
//             }

//             filterOne.key = $.trim(reportkey);
//             filterOne.op = filterMark;

//             if(filterMark  =='REGEXP'){
//               filterOne.val = [encodeURIComponent(filterVal)];
//             }else{
//                 filterOne.val = filterVal.split("?");
//             }
//             localTableData.filter.push(filterOne);     
//           }
//       });
       
//       //获取信息
//       localTableData.project = project;
//       var startTime = $('input[name=startTime]').val();
//       var endTime = $('input[name=endTime]').val();
//       if(startTime ==undefined){
//         localTableData.date =  endTime;
//         localTableData.edate = endTime;
//       }else{
//         localTableData.date =  startTime;
//         localTableData.edate = endTime;
//       } 
//       var one =[];
//       var two =[];
//       $obj.find('#groupid').find('tr').each(function(){
//           var key = $.trim($(this).find('.reportkey b').text());
//           if(key != 'date'){
//             one.push(key);     
//           }
               
//       });
//       $obj.find('#tableid').find('tr').each(function(){
//            if($(this).find('.reportkey').find('textarea').length  < 1  ){
//                two.push($.trim($(this).find('.reportkey b').text())); 
//            }          
//       });
//       localTableData.group = one.join(",");
//       localTableData.metric = two.join(",");
//       localTableData.udc = udcInfo.udcArr.join(",");
//       localTableData.udcconf = encodeURIComponent(JSON.stringify(udcInfo.udcconf));
//       localTableData.grade = localgrade;

// }

//数据还原
// function newDataSrc(obj){
//   var gooupArr = obj.group.split(",");
//   var metricArr =obj.metric.split(",");
//   //还原维度
//   var dims =[];
//   $('.grouplist').each(function(){
//       dimKey = $(this).attr('dimensions');
//       if($.inArray(dimKey,gooupArr) >=0){
//         var dim = $(this).attr('dim');
//         if(dim){
//           dim =  eval("("+ dim +")");
//         }
//         var dimArr = memgry(dim);
//         var objThis = $(this);
//         this.checked =true;
//         $("input.grouplist").each(function(){
//           if($(this).attr('disabled') == undefined){
//             if($.inArray($(this).attr('dimensions'),dimArr) >= 0){
//                $(this).removeAttr('disabled').css({
//                 '-webkit-box-shadow':'1px 1px 3px #000'
//               });
//             }else{
//               if(objThis.attr('dimensions') != $(this).attr('dimensions')){
//                 $(this).attr('disabled','disabled').css({
//                   '-webkit-box-shadow':"0px 0px 0px #eee"
//                 });
//               }   
//             }
//           }  
//         });
//         this.checked =true;  
//         dims.push(dimKey);
//       }
//   });
//   $('body').mask('正在请求加载数据...');
//    //获取被选中的维度
//     $.ajax({
//        type: "get",
//        url: "/visual/getMetric",
//        data: {'project':project,'dimensions':dims.join(",")},
//        dataType: "json",
//        success: function(data){
//          $('body').unmask();
//           if(data.status ==0){
//             $('.metricUl').show();
//             $('.metricUl').find('.list-group-item').each(function(){
//                 if($.inArray($(this).attr('name'),data.data) >= 0){
//                     $(this).show().removeClass('checked').addClass('show');
//                     if($.inArray($(this).attr('name'),metricArr) >= 0){
//                         $(this).addClass('checked');
//                     }
//                 }else{
//                     $(this).hide().removeClass('checked show');
//                 }
//             });

//             $('.metricUl').find('li').each(function(){
//                 var _this = this;
//                 $(_this).find('p').each(function(){
//                   if($(this).has('a.show').length>0){
//                     $(this).show().addClass('show').find('span').show().addClass('show');
//                   } else{
//                     $(this).hide().removeClass('show');
//                   }
//                 });
                
//                 if($(_this).find('p.show').length>0){
//                   $(_this).show().addClass('show');
//                 }else{
//                   $(_this).hide().removeClass('show');
//                 }

//             });

//           }else{
//             $.messager.alert('提示',data.msg,'warning');
//           } 

//        }
//     });

// /*  //还原指标
//   $('.metricUl').show();
//   $('.metricUl').find('.list-group-item').each(function(){
//       if($.inArray($(this).attr('name'),metricArr) >= 0){
//           $(this).show().addClass('checked');
//       }else{
//           $(this).hide().removeClass('checked');
//       }
//   });*/
// }
//生成报表列
// function getReportlist(tmpTable){
//     var tmpTable;
//     if(arguments[0] !=undefined){
//         tmpTable = tmpTable;
//     }else{
//         tmpTable =table;
//     }
//     var  params;
//     var  tableMap;
//     if(tmpTable.hasOwnProperty('project') ){
//       srcParams= srcExcel(tmpTable);

//       //时间维度可配置 cdate
//       if(type && type!='2'){
//         //explain: "first_menu", key: "first_menu", mapStr: "", name: "first_menu"
//         //srcParams.group.splice(0,0,{'key':'cdate','name':'时间','explain':'','mapStr': ''});
//         srcParams.group.splice(0,0,{'key':'date','name':'时间','explain':'','mapStr': ''});
//         //2015-6-11 cdate 显示在 表哥高级设置
//         if(typeof(tmpTable.grade)!="undefined" && tmpTable.grade.showsort && $.inArray('date',tmpTable.grade.showsort) < 0 ){

//             tmpTable.grade.showsort.splice(0,0,'date');

//           if(typeof(tmpTable.grade)!="undefined" && tmpTable.grade.sort && $.inArray('date',tmpTable.grade.sort) < 0 ){
//             tmpTable.grade.sort.splice(0,0,'date');
//           }

//         }
//       }
 
//       tableMap= getExcelMap(srcParams); 
//       console.log(tableMap);
//       //处理udc
//       if(tmpTable.udcconf){
//         var udcconf = eval("("+ decodeURIComponent(tmpTable.udcconf) +")") ;
//         if(udcconf.length>0){
//            for(var x =0; x<udcconf.length; x++){
//              var oneconf  = udcconf[x];
//              oneconf.udctype = 'udc';
//              oneconf.key  = oneconf.name;
//              if("undefined" != typeof tmpTable.grade && tmpTable.grade.sort !=undefined){   
//                 if(!in_array(oneconf.key,tmpTable.grade.sort)){
//                    oneconf.ishide  = 1;
//                 }
//              }
//              //处理 search
//              tableMap.push(oneconf);
//            }
//         }
//       }
//       if( tmpTable.grade !=undefined ){
//           //处理搜索
//           if(tmpTable.grade.hasOwnProperty('search')){
//             tableMap =  setNumSer(tmpTable.grade.search,tableMap,'search');
//           } 
//           //处理百分比
//           if(tmpTable.grade.hasOwnProperty('percent')){
//              tableMap =  setNumSer(tmpTable.grade.percent,tableMap,'percent');
//           } 
//           //处理固定列
//           if(tmpTable.grade.hasOwnProperty('fiexd')){
//             tableMap = setNumSer(tmpTable.grade.fiexd,tableMap,'fiexd');
//           } else {
//             //首次编辑默人 时间为选中固定列
//              if(tmpTable.grade.showsort && $.inArray('date',table.grade.showsort) >=0){
//                 tmpTable.grade['fiexd']=[];
//                 tmpTable.grade['fiexd'].push('date');
//                 tableMap = setNumSer(tmpTable.grade.fiexd,tableMap,'fiexd');
//              }
//           }

//           //处理排序列
//           if(tmpTable.grade.hasOwnProperty('isorderby')){
//             if(tmpTable.grade.hasOwnProperty('orderbyarr')){
//                 tableMap = setNumSer(tmpTable.grade.orderbyarr,tableMap,'orderbyarr');
//             }
//           } else {
//             //默认兼容旧的报表排序 纬度时间 和第一个指标 排序
//             tmpTable.grade['orderbyarr'] = [];
//             if(tmpTable.grade.showsort && $.inArray('date',table.grade.showsort) >=0){
//               tmpTable.grade.orderbyarr.push('date');
//             }
//             tmpTable.grade.orderbyarr.push(tmpTable.metric.split(',')[0]);
//             tableMap = setNumSer(tmpTable.grade.orderbyarr,tableMap,'orderbyarr');
//           }

//           //处理 钻取功能
//           if(tmpTable.grade['otherlink']){
//             var templink = tmpTable.grade['otherlink'];
//             for(var p in tableMap){
//                 var coloumkey = tableMap[p]['key'];
//               if(templink[coloumkey]){ //如果key值 存在 otherlink 的json对象
//                 tableMap[p]['otherlink'] = templink[coloumkey];
//               }
//             }
//           }

//       }
//       if("undefined" != typeof tmpTable.grade ){      
//         for(var j=0; j< tableMap.length; j++){
//            if("undefined" != typeof tmpTable.grade.sort   && tmpTable.grade.sort !=undefined ){
//              if(!in_array(tableMap[j].key,tmpTable.grade.sort)){
//               tableMap[j].ishide  = 1;
//              }
//            } 
//         }
//       }
//       //处理排序
//       if("undefined" != typeof tmpTable.grade){
//          var gradeObj =[];
//          if( "undefined" != typeof tmpTable.grade.showsort && tmpTable.grade.showsort.length >0 ){
//             for(var i=0; i<tmpTable.grade.showsort.length; i++){
//                for(var j=0; j<tableMap.length; j++){
//                  if(tmpTable.grade.showsort[i] == tableMap[j].key){
//                     gradeObj.push(tableMap[j]);
//                  }
//                }
//             }
//             tableMap = gradeObj;
//          }
//       } 
//       //处理过滤条件
//       if( tmpTable.filter  != undefined && tmpTable.filter.length >0){
//          for(var  x=0; x< tableMap.length; x++){
//            for(var p=0;p < tmpTable.filter.length; p++){
//               if( tableMap[x].key == tmpTable.filter[p].key){

//                  var one ={}; 
//                  one =  tableMap[x];
//                  console.log(tmpTable.filter[p]);
//                  if(tmpTable.filter[p].op =="REGEXP"){
//                    tmpTable.filter[p].val[0] = decodeURIComponent( tmpTable.filter[p].val[0]);
//                  }
//                  one.filter = tmpTable.filter[p];
//                 tableMap[x] = one;
//               }
//            }
//          }
//       }
//     }else{
//       params = getSource();
//       if(!params){
//         return false;
//       }
//       //时间维度可配置 cdate
//       if(type && type!='2'){
//         //explain: "first_menu", key: "first_menu", mapStr: "", name: "first_menu"
//         params.group.splice(0,0,{'key':'date','name':'时间','explain':'','mapStr': ''});
//       }

//       tableMap= getExcelMap(params);
      
//     }   
//     //console.log('add--tableMap---:'+JSON.stringify(tableMap));
//     var interText = doT.template($("#gradetmpl").text());

//     if(type ==2){
//        for(var q =0; q< tableMap.length; q++){
//           tableMap[q].type = type;
//        }
//     }
//     $(".reportbox").html(interText(tableMap)).show();

//     $new_proportionbox = $('.reportbox').find('.new_proportion');
//     $isproportionTag = $new_proportionbox.find('input[name="isproportion"]');
    
//     var isproportion = (typeof(tmpTable.isproportion)!='undefined' && (tmpTable.isproportion == '1'||tmpTable.isproportion=='true'))?true:false;
//     //普通报表 设置是否相对占比 显示
//     if(type && type==1 ){
//       $new_proportionbox.attr('report-type',type).show();
//       $isproportionTag.prop('checked',isproportion);
//     } else {
//       $new_proportionbox.attr('report-type',type).hide();
//     }
    
//     //高级表格拖拽 
//     drag($('#contrasreport').find("#tableid"));
//     drag($('#contrasreport').find("#groupid"));

//     drag($('#reportgrade').find("#tableid"));
//     drag($('#reportgrade').find("#groupid"));
//     // $("#tableid").dragsort("destroy");
//     // $("#tableid").dragsort({
//     //     dragSelector : "tr",
//     //     dragSelectorExclude:"select,button,input,textarea,b",
//     //     dragEnd : function(){},
//     //     scrollSpeed:0,
//     // });
//     // $("#groupid").dragsort("destroy");
//     // $("#groupid").dragsort({
//     //     dragSelector : "tr",
//     //     dragSelectorExclude:"select,button,input,textarea,b",
//     //     dragEnd : function(){},
//     //     scrollSpeed:0,
//     // });
// }
// function drag(obj){
//     obj.dragsort("destroy");
//     obj.dragsort({
//         dragSelector : "tr",
//         dragSelectorExclude:"select,button,input,textarea,b,small",
//         dragEnd : function(){},
//         scrollSpeed:0,
//     });
// }
// function clear(){
//   //数据清空
//   $('.groupUl').find('.grouplist').each(function(){
//       $(this).removeAttr('disabled').css({'-webkit-box-shadow':'1px 1px 3px #000'});
//       this.checked =false;
//   });
//   //还原指标
//   $('.metricUl').find('.list-group-item').each(function(){
//       $(this).removeClass('show checked').hide();
//       $(this).find('.metriclist').removeAttr('disabled').css({'-webkit-box-shadow':'1px 1px 3px #000'});
//       $(this).find('.metriclist').attr('checked',false);
//       $(this).find('.metriclist').removeClass('show checked');
//       $(this).hide();
//   });

//   $('.metricUl').find('li').removeClass('show').hide().find('p').removeClass('show').hide().find('span').removeClass('show');
// }

$(function(){

  //对比表格设置
  // $('#contrasreport').show().dialog({
  //   title: '表格设置',
  //   width: 1000,
  //   //height:'',
  //   closed: true,
  //   cache: false,
  //   modal: true,
  //   buttons: [{
  //     text:'确定',
  //     handler:function(){   

  //       if(!$("#contrasreport").find('.reportbox').is(":visible")){
  //          $.messager.alert('提示','请先选择数据源','warning');
  //          return;
  //       }
  //       var tempcount = 0;

  //       /*$('#contrasreport #groupid .op_val').each(function(i){
  //           if($(this).val()==""){
  //             tempcount++
  //           }
  //       })
  //       if(tempcount > 0){
  //         $.messager.alert('提示','对比报表（维度）必须填写完整的数据过滤条件，确保数据唯一性','warning');
  //         return false;
  //       }*/

  //       //对比报表 搜索功能 必选项
  //       var errmsg = [];
  //       $('#contrasreport #groupid tr').each(function(i){
  //           var keyname =$(this).find('.reportname').text();
  //           var key =$(this).find('.reportkey b').text();
  //           if(key == 'all'){
  //             return; //return false 跳出循环实现break功能  return 实现continue功能
  //           }
  //           if(!$(this).find('.isfilter').is(":checked")){
  //             errmsg.push('维度：'+keyname+' 搜索设置未选中');
  //           } else {
  //             // 验证对比报表纬度 即时过滤是否填写 以保证数据的唯一性。
  //             var dataconfig = $(this).find('.reportsearch').attr('data-config');
  //             if(dataconfig == '' || !dataconfig){
  //               errmsg.push('维度：'+keyname+' 即时过滤设置不能为空');
  //             } else{
  //               dataconfig = JSON.parse(dataconfig);
  //               if(!dataconfig['reportsource']||dataconfig['reportsource']==
  //                 ''){
  //                 errmsg.push('维度：'+keyname+'即时过滤设置不能为空');
  //               }
  //             }

  //           }
  //       })
  //       console.log('errmsg:'+errmsg);
  //       if(errmsg.length > 0){
  //         $.messager.alert('提示',errmsg.join('<br/>'),'warning');
  //         return false;
  //       }

  //          //处理ud
  //       grade.sort =[];
  //       grade.showsort =[];
  //       grade.search =[];
  //       grade.percent =[];
  //       grade.fiexd=[];//是否固定
  //       grade.isfiexd=1;//新增固定列的标识
  //       var udcInfo ={};
  //       udcInfo.udcArr =[];
  //       udcInfo.udcconf =[];
  //       table.filter =[];
  //       var  allArr =[];
  //       var  realArr =[];
  //       $('#contrasreport').find('.gradebox').find('tr').each(function(){
  //           //获取sort
  //           if($(this).find('.reportkey').find('textarea').length >0){
  //             var tempkey = $.trim($(this).find('.reportkey').find('textarea').val());
  //             //处理udc
  //             if(!$(this).find('.operate').find('input').is(":checked")){
  //               grade.sort.push(tempkey);
  //             }
  //             //是否固定
  //             //表格列是否固定
  //             if($(this).find('.fixed').find('input.isfixed').is(":checked")){
  //               grade.fiexd.push(tempkey);
  //             }

  //             grade.showsort.push(tempkey);      
  //             var expObj ={};
  //             expObj.name      = $.trim($(this).find('.reportkey').find('textarea').val()); 
  //             expObj.cn_name   = $.trim($(this).find('.reportname').find('textarea').val()); 
  //             expObj.explain   = $.trim($(this).find('.reportexplain').find('textarea').val());
  //             expObj.expression= $.trim($(this).find('.reportexpression').find('textarea').val());
  //             expObj.udc = expObj.name+"="+expObj.expression;
  //             udcInfo.udcArr.push(expObj.udc);
  //             udcInfo.udcconf.push(expObj);
  //           }else{

  //             var tempkey = $.trim($(this).find('.reportkey b').text());
  //             grade.showsort.push(tempkey);

  //             if(!$(this).find('.operate').find('input').is(":checked")){
  //                 grade.sort.push(tempkey);
  //             }
  //             //表格列是否固定
  //             if($(this).find('.fixed').find('input.isfixed').is(":checked")){
  //               grade.fiexd.push(tempkey);
  //             }

  //           }
  //           //获取搜索
  //           if($(this).find('.isfilter').is(":checked") && !$(this).find('.operate').find('input').is(":checked")){
  //             var  onesearch ={};
  //             if($(this).find('.reportsearch').attr('data-config') != undefined && $(this).find('.reportsearch').attr('data-config') !='' ){
  //                onesearch = eval("("+ $(this).find('.reportsearch').attr('data-config')+" )") ; 

  //             }else{
  //                //如果是输入框 获取输入框的值， 如果不是 获取td 的 text
  //                if($(this).find('.reportkey').find('textarea').length >0){
  //                   onesearch.reportkey = $.trim($(this).find('.reportkey').find('textarea').val()); 
  //                }else{
  //                   onesearch.reportkey = $.trim($(this).find('.reportkey b').text());
  //                }
  
  //             }
  //             grade.search.push(onesearch);
  //           }
  //           //获取百分比
  //           if($(this).find(".ispercent").is(":checked")){
  //              if($(this).find('.reportkey').find('textarea').length >0){
  //                 grade.percent.push($.trim($(this).find('.reportkey').find('textarea').val()));
  //              }else{
  //                 grade.percent.push($(this).find('.reportkey b').text());
  //              }      
  //           }
  //           //获取filter
  //           var filterMark =$.trim($(this).find('.reportf').find('[name=op]').val());
  //           var filterVal = $.trim($(this).find('.reportf').find('.op_val').val());
  //           /*if($(this).find('td').eq(0).text() =='维度'){
  //                allArr.push($(this).find('.reportkey b').text()); 
  //           }*/
  //           if(filterMark !='filter_not' && filterVal !=''){
  //             var filterOne ={};
  //             var textareaTag = $(this).find('.reportkey textarea');
  //             var reportkey = '';
              
  //             if(textareaTag.length > 0){
  //               reportkey = textareaTag.val();
  //             } else { 
  //               reportkey = $(this).find('.reportkey b').text();
  //             }

  //             filterOne.key = $.trim(reportkey);
  //             filterOne.op = filterMark;
  //             if(filterMark  =='REGEXP'){
  //               filterOne.val = [encodeURIComponent(filterVal)];
  //             }else{
  //                 filterOne.val = filterVal.split("?");
  //             }

  //             //filterOne.val = filterVal.split("?");
  //             table.filter.push(filterOne);
  //             /*if($(this).find('td').eq(0).text() =='维度'){
  //               realArr.push($(this).find('.reportkey b').text()); 
  //             }*/
  //           }
  //       });
  //       /*if(realArr.length < allArr.length){
  //           $.messager.alert('提示','对比报表（维度）必须填写完整的数据过滤条件，确保数据唯一性','warning');
  //           return;
  //       }*/
  //       if(udcInfo.udcconf.length >0){
  //         for(var i =0; i< udcInfo.udcconf.length; i++){
  //            if(udcInfo.udcconf[i].name ==''){
  //              $.messager.alert('提示','列key必填','warning');
  //              return;
  //            }
  //            if(udcInfo.udcconf[i].cn_name ==''){
  //              $.messager.alert('提示','列中文名称必填','warning');
  //              return;
  //            }
  //            if(udcInfo.udcconf[i].expression ==''){
  //              $.messager.alert('提示','列数据的计算表达式必填','warning');
  //              return;
  //            }
  //         }
  //       }
       

  //       table.project = project;
  //       var startTime = $('input[name=startTime]').val();
  //       var endTime = $('input[name=endTime]').val();
  //       if(startTime ==undefined){
  //         table.date =  endTime;
  //         table.edate = endTime;
  //       }else{
  //         table.date =  startTime;
  //         table.edate = endTime;
  //       } 
  //       var one =[];
  //       var two =[];
  //       $('#contrasreport').find('#groupid').find('tr').each(function(){
  //            one.push($.trim($(this).find('.reportkey b').text()));        
  //       });
  //        $('#contrasreport').find('#tableid').find('tr').each(function(){
  //            if($(this).find('.reportkey').find('textarea').length  < 1  ){
  //                two.push($.trim($(this).find('.reportkey b').text())); 
  //            }          
  //       });
  //       table.group = one.join(",");
  //       table.metric = two.join(",");

  //       table.udc = udcInfo.udcArr.join(",");
  //       table.udcconf = encodeURIComponent(JSON.stringify(udcInfo.udcconf));
  //       table.grade = grade;
  //       table.contrast =[];
  //       //获取对比报表展示的信息
  //       $('#contrasreport').find('.contrasttr').find('tr').each(function(){
  //           if($(this).find('.isshow').is(":checked")){
  //              var one  ={};
  //              one.key = $(this).find('.contrastkey').text();
  //              one.name = $(this).find('.contrastname').text();
  //              if($(this).find('.contrastformat').is(":checked")){
  //                one.format = 1;
  //              }
  //              if($(this).find('.minus').is(":checked")){
  //                one.minus = 1;
  //                one.val = $(this).find('.minusval').val();
  //              }
  //              if($(this).find('.plus').is(":checked")){
  //                one.plus = 1;
  //                one.val = $(this).find('.plusval').val();
  //              }
  //              table.contrast.push(one);
  //           }
  //       });  
  //       if(table.contrast.length <1){
  //         $.messager.alert('提示','至少选择一个对比值(例：当日值 )','info');
  //         return;
  //       }
  //       tableContrast(table,$(".tablecontent"),1);
  //       $('#contrasreport').dialog('close');
  //     }
  //   },{
  //     text:'取消',
  //     handler:function(){
  //       $('#contrasreport').dialog('close');
  //     }
  //   }]
  // });
  //操作顺序
  // $('body').on('click','.addsource',function(){
  //   //localTableData 获取表格数据保存到localTableData
  //   if(table.hasOwnProperty('contrast')){
  //     getTableData($('#contrasreport'));
  //   } else{
  //      getTableData($('#reportgrade'));
  //   }
    

  //   if(localTableData.hasOwnProperty('project')){
  //     newDataSrc(localTableData);
  //   }
  //   /*
  //     if(table.hasOwnProperty('project')){ 
  //       newDataSrc(table);
  //     }*/
  //     $(this).hide();
  //     $('.sourceBox').show();
  //     $('.reportbox').hide();
  //     $('.contrastbox').hide();
  // });
  // $('body').on('click','.saveSource',function(){

  //   var groupCount = 0, metricCount =0 ;
  //   $(".groupUl input.grouplist").each(function(){
  //         if($(this).is(":checked")){
  //            groupCount++; 
  //         } 
  //     });
  //   $(".metricUl a.metriclist").each(function(){
  //         if($(this).hasClass("checked")){
  //            metricCount++; 
  //         } 
  //     });

  //   if(groupCount >0 && metricCount >0){
  //   //if(table.group !=''  &&  table.metric !='' ){
       
  //       tmpTable = getAllInfo();
  //       //把 showsort 替换
  //       //吧  
  //       // 弹窗没点确定前，保存临时数据 localTableData={} 初始化临时table数据
  //       // 新建报表表格设置
  //       if(JSON.stringify(table) == '{}' && JSON.stringify(localTableData)=='{}' ){
  //           getReportlist(tmpTable);
  //           $('#metric_error').hide();
  //           $('.addsource').show();
  //           $('.sourceBox').hide();
  //           $('.reportbox').show();
  //           $('.contrastbox').show();
  //           $('select').select2(); 
  //           $('.showinfo').tooltip({ 'position':'top'});
  //           clipboard();//复制粘贴代码
  //         return false;
  //       } 

  //       if(undefined != typeof(table.isproportion)){
  //           tmpTable['isproportion'] = table.isproportion;
  //        }

  //       //编辑报表时 表格设置的数据处理
  //       //判断纬度是否改变 ---数组合并去重 利用数组长度判断是否相同
  //       var localgroups = localTableData.group, localgroupArr = localgroups.split(','),
  //           tmpTablegroup = tmpTable.group, tmpTablegroupArr = tmpTablegroup.split(','),
  //           len = tmpTablegroupArr.length;
  //       //合并后的数组去重
  //       var groupMergeArr =$.unique($.merge(localgroups.split(','),tmpTablegroupArr)); 
  //       if(localgroupArr.length == len && groupMergeArr.length == len){
  //               tmpTable.filter = localTableData.filter;
  //               tmpTable.udc = localTableData.udc;
  //               tmpTable.udcconf = localTableData.udcconf;
  //               //重组grade.showsort  grade.sort
  //               tmpTable.grade = JSON.parse(JSON.stringify(localTableData.grade));
  //               tmpTable.grade.sort = [];
  //               tmpTable.grade.showsort = [];
                 
  //                //合并维度group 和 指标metric udc的 数组 mergeArr2  然后拼成showsort数组展示
  //               var mergeArr1 = $.merge(tmpTable.group.split(','),tmpTable.metric.split(','));
  //               var mergeArr2 = $.merge(mergeArr1,localTableData.grade.udcnameArr);

  //               //保存上次的排序功能
  //               var localshowsort = localTableData.grade.showsort, newshowsort = localshowsort.join('@').split('@');
  //               var localsort = localTableData.grade.sort, newsort = localsort.join('@').split('@');

  //               for(var i = 0,len=mergeArr2.length; i<len; i++){
  //                   if($.inArray(mergeArr2[i], localshowsort) <0){
  //                      newshowsort.push(mergeArr2[i]);
  //                      newsort.push(mergeArr2[i]);
  //                   }

  //               }
  //               tmpTable.grade.showsort = newshowsort;
  //               tmpTable.grade.sort = newsort;
                    
  //               getReportlist(tmpTable);
                
  //               localTableData = null;
  //               localTableData = JSON.parse(JSON.stringify(tmpTable));

  //               $('#metric_error').hide();
  //               $('.addsource').show();
  //               $('.sourceBox').hide();
  //               $('.reportbox').show();
  //               $('.contrastbox').show();
  //               $('select').select2(); 
  //               $('.showinfo').tooltip({ 'position':'top'});
  //                clipboard();//复制粘贴代码
  //       } else{
  //          $.messager.confirm('提示','您选择的维度有变化，是否覆盖表格原有的设置', function(r){
  //           if(r){
  //               getReportlist(tmpTable);
  //               $('#metric_error').hide();
  //               $('.addsource').show();
  //               $('.sourceBox').hide();
  //               $('.reportbox').show();
  //               $('.contrastbox').show();
  //               $('select').select2(); 
  //               $('.showinfo').tooltip({ 'position':'top'});
  //                clipboard();//复制粘贴代码

  //             }
  //           }); 
  //       }
        
  //   }else{
  //       //$.messager.alert('提示','维度和指标不能为空','warning');
  //       $('#metric_error').show();
  //       return false;
  //   }
    

  // });
  // //数据选择
  // $('body').on('click','.grouplist',function(){
  //     // var dim = $(this).attr('dim');
  //     // if(dim){
  //     //   dim =  eval("("+ dim +")");
  //     // }
  //     // var dimArr = memgry(dim);
  //     var objThis = $(this);
  //     var   all =[];
  //     $("input.grouplist").each(function(){
  //         if($(this).is(":checked")){
  //             var dim = $(this).attr('dim');
  //             if(dim){
  //               dim =  eval("("+ dim +")");
  //             }
  //             var dimArr = memgry(dim);
  //             all.push(dimArr);
  //         } 
  //     });

  //     var diff = arry_diff(all);
  //     if(diff.length >0){
  //       $("input.grouplist").each(function(){
  //         if($.inArray($(this).attr('dimensions'),diff) >= 0){
  //           $(this).removeAttr('disabled').css({
  //             '-webkit-box-shadow':'1px 1px 3px #000'
  //           });
  //         }else{
  //           $(this).attr('disabled','disabled').css({
  //             '-webkit-box-shadow':"0px 0px 0px #eee"
  //           });
  //         }
  //       }); 
  //     }else{

  //       if(objThis.is(":checked") ){
  //         $("input.grouplist").each(function(){
  //            if($(this).attr('dimensions') !=objThis.attr('dimensions')){
  //               $(this).attr('disabled','disabled').css({
  //                 '-webkit-box-shadow':"0px 0px 0px #eee"
  //               });
  //            }    
  //         }); 
  //       }else{
  //         $("input.grouplist").each(function(){
  //           $(this).removeAttr('disabled').css({
  //             '-webkit-box-shadow':'1px 1px 3px #000'
  //           });
  //         }); 
  //       }
        
  //     }
      

  //     //处理指标
  //     var dims =[];
  //     $('input.grouplist').each(function(k,v){     
  //       if($(this).is(":checked")){
  //         dims.push($(this).attr('dimensions'));
  //       }
  //     }); 
  //     $('body').mask('正在请求加载数据...');
  //     //获取被选中的维度
  //     $.ajax({
  //        type: "get",
  //        url: "/visual/getMetric",
  //        data: {'project':project,'dimensions':dims.join(",")},
  //        dataType: "json",
  //        success: function(data){
  //         $('body').unmask();
  //           if(data.status ==0){
  //             $('.metricUl').show();
  //             $('.metricUl').find('.list-group-item').each(function(){
                 
  //                 if($.inArray($(this).attr('name'),data.data) >= 0){
  //                     $(this).show().addClass('show');
  //                 }else{
  //                     $(this).hide().removeClass('checked').removeClass('show');
  //                 }
  //             });

  //             // 类目是非显示隐藏
  //           $('.metricUl').find('li').each(function(){
  //               var _this = this;
  //               $(_this).find('p').each(function(){
  //                 if($(this).has('a.show').length>0){
  //                   $(this).show().addClass('show').find('span').show().addClass('show');
  //                 } else{
  //                   $(this).hide().removeClass('show');
  //                 }
  //               });
                
  //               if($(_this).find('p.show').length>0){
  //                 $(_this).show().addClass('show');
  //               }else{
  //                 $(_this).hide().removeClass('show');
  //               }

  //           });


  //           }else{
  //             $.messager.alert('提示',data.msg,'warning');
  //           }      
  //        }
  //     });
  // });
  // //全选指标
  // $('body').on('click','.selectAll',function(){
  //   var text = $(this).text();
  //   if(text =='全选'){
  //     $(".metriclist").each(function(){
  //       if($(this).attr('disabled') == undefined){
  //         this.checked =true;
  //       }
  //     });
  //     $(this).text('取消全选');
  //   }else{
  //     $(".metriclist").each(function(){
  //         this.checked =false;
  //     });
  //     $(this).text('全选');
  //   }
  // });
  // //清空维度指标
  // $('body').on('click','.clearAll',function(){
  //     clear();
  // });

  // //全选指标
  // $('body').on('click','.groupCheckAll',function(){
  //     var status = $(this).attr('data-status');
  //     if(status == 'clear'){
  //       $('.metricUl li.show p.show a.show').addClass('checked');
  //       $(this).attr('data-status','checked');
  //     } else {
  //        $('.metricUl li.show p.show a.show').removeClass('checked');
  //       $(this).attr('data-status','clear'); 
  //     }
  // });

  //可选指标点击事件
  // $('body').on('click','.metricUl li a',function(){
  //     $(this).toggleClass('checked');
  //     $('#metric_error').hide();
  //     var $parentP = $(this).closest('p');
  //     if( $parentP.has('a.checked').length > 0){
  //       $parentP.addClass('show');
  //     } else{
  //       $parentP.removeClass('show');
  //     }
  // });
});