 //格式化时间戳
 function changeTimeFormat(time) {
		var date = new Date(time);
		var month = date.getMonth() + 1 < 10 ? "0" + (date.getMonth() + 1) : date.getMonth() + 1;
		var currentDate = date.getDate() < 10 ? "0" + date.getDate() : date.getDate();
		var hh = date.getHours() < 10 ? "0" + date.getHours() : date.getHours();
		var mm = date.getMinutes() < 10 ? "0" + date.getMinutes() : date.getMinutes();
		return date.getFullYear() + "-" + month + "-" + currentDate +" "+hh+":"+mm;
		//返回格式：yyyy-MM-dd hh:mm
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
function loading(obj,type){
 	var loadingObj = obj.parent();
 	console.dirxml(loadingObj);
 	if(type ==1){
 		loadingObj.find('.chartloading').show();
 	}else{
 		loadingObj.find('.chartloading').hide();
 	}
 }
 //图表创建
// function createChart(data){
// 	$('body').append("<div class='showName'></div>");
//  	for(var i=0; i< data.length; i++){					 
// 		 var chart,str,options;			   				
// 		 str =  data[i];	
// 		 var strName = str.chart.renderTo;
// 		 $("#"+strName).hover(function(){},function(){
// 				 $(".showName").hide();
// 		  }); 									
// 		 options = str;						 	
// 		 if (str.series[0].type == 'pie'){
// 			chart = new Highcharts.Chart(options);
// 			chart.options.tooltip.formatter = function(){
// 					return "<b>" + this.point.name +":" + this.y + "%</b> ";
// 			};
// 		 }else{	
// 			chart = new Highcharts.Chart(options);
// 			Highcharts.setOptions({  
// 		            global: {   
// 		                useUTC: false                                                                                     
// 		    		}   
// 			});						
// 		}
// 	}
// }
 //初始化图表，确定指标
function getJsonStr(){
	var  ajaxObj = $('.chartContiner');
	if(ajaxObj.length || ajaxObj == null){
		 var  ajaxUrl = [];
		 $.each(ajaxObj,function(){ 	
			//为图表初始化指标信息
			ajaxUrl.push($(this).attr('url'));
		 })
		  return ajaxUrl;	
   }else{
	   return 0;
   }
}
 //检测数据是否填写完整
 function checkConfig($typesel,timeStatu){
	   var  status = 1;
	   var choseType =  $typesel.find('.typeChoise').val();
	   if(choseType == 1){
			//时间
			$typesel.find('.onlyQuota').find('.dateWdith').each(function() {
				  if(timeStatu  == 2){
					  if($(this).val() == ''){
						 alert('请把日期填写完整');
						 status = 0;
					  }
				  }
			});	   
	   }else{
			//指标
			if($typesel.find('.quotaContainer').children('em').length <1){
				 if(timeStatu  == 2){
					 alert('请选择指标');	
					 status = 0;
				 }
			 }
			 //多条时间曲线
			 $typesel.find('.difrentQuota').find('.dateWdith').each(function() {
				  if(timeStatu  == 2){
					  if($(this).val() == ''){
						 alert('请把日期填写完整');
						 status = 0;
					  }
				  }
			 }); 
	   }
	   return status;
 }
//获取图表配置信息 $typesel == 当前 changeSelect 对象
function getChartConfig($typesel,timeStatu){
	 //创建chartconfig 对象
	 var  changeInfo = new Object();
	 //获取ID
	 changeInfo.id = $typesel.parent().next('div.chartContent').find('.chartContiner').attr('id');	
	 //获取类型
	 var choseType =  $typesel.find('.typeChoise').val();
 	 changeInfo.type = choseType;
	 if( choseType == 1){
		 var value =  new Object();
		 value.key =  $typesel.find('.quotas').val();
		 value.name = $typesel.find('.quotas').find("option:selected").text();
		 //获取指标
		 changeInfo.values = JSON.stringify(value);
		 var time = [];
		 $typesel.find('.onlyQuota').find('.dateWdith').each(function() {
		 	  time.push($(this).val());
		 });
		//获取时间
	 	changeInfo.times = time;
	 }else{
		 var values = [];
		 $typesel.find('.quotaContainer').children('em').each(function() {
			 var value =  new Object();
			  value.key = $(this).attr('rul');
			  value.name = $(this).text();
			  values.push(value);
		 });
		 //获取指标
		 changeInfo.values = JSON.stringify(values);
		 var time = [];
		 $typesel.find('.difrentQuota').find('.dateWdith').each(function() {
			  time.push($(this).val());
		 });
		 //获取时间
		 changeInfo.times = time;	
	 }
     return changeInfo;
}
 
function openToolBox(obj){
   obj.dialog({
		title: '图表简单数据处理',
		//width: 240,
 		closed: false,
 		modal: false,
	});	 
}
function getTimeAjax(timeType,srcSecting,timeInterval,obj){
    loading(obj,1);
	$.ajax({
			type:"POST",
			url:"/jsRequest/showChart",
			dataType:"json",
			data:{'timeType':timeType,'srcSecting':srcSecting,'timeInterval':timeInterval},	
			success: function(data){
				loading(obj,2);
				createChart(data);
			} 
	 });
}
//初始化ajax请求
function intAjax(i,ajaxArr){
	//$('.chartloading').show();
	if(  i  == ajaxArr.length ){
		 return;
	}else{
		$.ajax({
			type:"POST",
			url:"/jsRequest/showChart",
			dataType:"json",
			//timeout:3200,
			data:{'setting[]':ajaxArr[i]},	
			success: function(data){
				var  showId =  data[0].chart.renderTo;
				if($("#"+ showId).next().attr('class') =='chartloading'){
					$("#"+ showId).next().hide();
				}
				intAjax(++i,ajaxArr);	
				//$('.chartloading').hide();
				createChart(data);
		   }
		});		
		
	}
}
$(function(){
	 //为图表初始化指标信息
	 //求和功能
	 $('.sumTotal').click(function(){
			 //获取changeSelect对象
		 $typesel =$(this);
		 changeInfo = getChartConfig($typesel,1);
			 //报表配置文件
			 var srcSecting = $('#'+ changeInfo.id).attr('url');
		 //其它条件
			 var timeType =  $(this).parent().parent().find('.changeTime').find('.active').attr('rul');
			 var timeInterval = $(this).parent().parent().find('.timeBox').find('.active').attr('rul');
		 if(timeInterval == undefined){
			 timeInterval ='';
		  }
		  loading($('#'+ changeInfo.id),1);
		  $.ajax({
			type:"POST",
			url:"/jsRequest/showChart",
			dataType:"json",
			data:{'timeType':timeType,'srcSecting':srcSecting,'timeInterval':timeInterval,sum:1},	
			success: function(data){
				 loading($('#'+ changeInfo.id),2);
					 //获取数据
					 var  str = "<table style='width:100%' class='table table-bordered table-condensed'>";
				 str  += "<tr>";
				 str += "<td style='width:150px'>计算的时间段</td>";
				 str += "<td style='width:100px;text-align:center'>曲线名称</td>";
				 str += "<td style='width:100px;text-align:center'>总和</td>";
				 str += "<td style='width:100px;text-align:center'>平均值("+data[0].type+")</td>";
				 str += "</tr>";
					 for(var i in data){
					 str  += "<tr>";
					 str += "<td>"+data[i].dt+"</td>";
					 str += "<td>"+data[i].name+"</td>";
					 str += "<td>"+data[i].total+"</td>";
					 str += "<td>"+data[i].avg+"</td>";
				     str += "</tr>";
				 }
					 str += "</table>";
				   var boxStr ="<div id='sumBox'><div>";
				  if ($("#sumBox").length >0){
					    var obj = $(str);
						$("#sumBox").html("");
							$("#sumBox").append(str);
							$("#sumBox").dialog({top:30}).dialog('open');
				  }else{
						var obj = $(boxStr);
						obj.append(str);
						$('body').append(obj);
							openToolBox(obj);
				  }
			} 
		  });	    
	  });
	 //年月日交互事件
	 $('.changeTime span').click(function(){
		 $(this).addClass('active').siblings('span').removeClass('active');
		 //获取changeSelect对象
		 $typesel =$(this).parent();
		 changeInfo = getChartConfig($typesel,1);
			 //报表配置文件
		 var srcSecting = $('#'+ changeInfo.id).attr('url');
		 //其它条件
			 var timeType = $(this).attr('rul');
			 var timeInterval = $(this).parent().parent().find('.timeBox').find('.active').attr('rul');
		 if(timeInterval == undefined){
			 timeInterval ='';
		  }
			  getTimeAjax(timeType,srcSecting,timeInterval,$('#'+ changeInfo.id));
	 });
	 //快速时间选择
	 $('.timeBox span').click(function(){
		 $(this).addClass('active').siblings('span').removeClass('active');
		 //获取changeSelect对象
		 $typesel =$(this).parent();
		 changeInfo = getChartConfig($typesel,1);
			 //报表配置文件
		 var srcSecting = $('#'+ changeInfo.id).attr('url');
		 //其它条件
			 var timeType = $(this).parent().parent().find('.changeTime').find('.active').attr('rul');
		 var timeInterval = $(this).attr('rul');
			 getTimeAjax(timeType,srcSecting,timeInterval,$('#'+ changeInfo.id));
	 });
	 //发送ajax请求
	 ajaxUrlArr = getJsonStr();
	 if( ajaxUrlArr == 0){
	     return;	
	 }else{
		 intAjax(0,ajaxUrlArr);				 
	 } 
});
