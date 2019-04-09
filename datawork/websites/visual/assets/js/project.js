$(function(){
	$('.data-table').dataTable({
        "iDisplayLength":10,
        "bJQueryUI": true,
        "sPaginationType": "full_numbers",
        "sDom": '<""l>t<"F"fp>',
        "bSort":false,
        //"bPaginate":false,
         "oLanguage": {
         	  'sSearch':'搜索:',
              "sLengthMenu": "每页显示 _MENU_ 条记录",
               "oPaginate":{
               	 "sFirst":"第一页",
               	 "sLast":"最后一页",
               	 "sNext": "下一页",
               	 "sPrevious": "上一页"
               },
               "sInfoEmtpy": "没有数据",
               "sZeroRecords": "没有检索到数据"
          }
      });
  if(window.localStorage){

      //var name = window.location.href.split("/");
      //console.log(name[name.length-2]);
      //if( name[name.length-2]  != undefined){
        //if(localStorage["search_"+ name[name.length-2]] != undefined){
          $('input[type=search]').val(localStorage[window.location.href]).trigger('keyup');
        //}      
      //}
       
    }
	$('select').select2({
		allowClear:true
	});
})