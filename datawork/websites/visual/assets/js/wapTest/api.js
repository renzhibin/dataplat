$(document).ready(function(){
	//项目列表
	var allproject = {};
	// 选择project 获取相对应的报表
	$('select[name="project"]').bind('change',function(){
		var project = $(this).val();
		$('.applyreportlistbox').hide().find('#applylisturl').html('');
		getReport(project,'select[name="reportlist"]');
		$('select[name="reportlist"]').hide();
		$('.ckreport').hide();
		$('.error_msg').text('').hide();
	}).select2();

	// 细节效果－ input 获取焦点 error 错误信息隐藏
	//$('select').select2();
	$('select[name="project"]').trigger('change');
	$('input[name="appname"]').bind('focus',function(){
		$('.error_msg').text('').hide();
	});

	//获取应用列表
	
	getAppList(user_name);

	//获取apptoken
	$('input[name="appkeyBtn"]').bind('click',function(){
		var appname = $('input[name="appname"]').val(), $error_appmsg = $('.error_appmsg');
			url='/addition/getfakecube?action=apply_app_token&user_name='+user_name+'&app_name='+appname;

		if(appname == ''){
			$error_appmsg.text('请输入应用名称').show();
			return false;
		}
		
		sendAjax(url,{},function(result){
		   	if(result.status == 0){
		   		$('#apptoken').html(result.data.token).closest('.form-group').show();
		   		$error_appmsg.text('').hide();
		   		$('.getapptoken').val(JSON.stringify(result.data));
		   		$(this).attr('disabled','disabled');
		   		$('input[name="appname"]').attr('disabled','disabled');
		   		getAppList(user_name);
			} else {
		   		$error_appmsg.text(result.msg).show(); 
		   	}
	   },function(){
	   		$error_appmsg.text('获取token失败').show();
	   		return false;
	   });

	});

	// 项目申请
	$('.projectBtn').bind('click',function(){
		var appVal = $('.getapptoken').val(), $proErrMsgTag = $('.err_projectmsg'),
			projectname = $('select[name="project"]').val();
		var $this = $(this);
		if(appVal == ""){
			$proErrMsgTag.html('请获取token后再申请项目').show();
			return false;
		}

		var appjson = JSON.parse(appVal);
		var url = '/addition/getfakecube?action=apply_project&user_name='+user_name+'&app_name='+appjson.app_name+'&token_val='+appjson.token+'&project_name='+projectname;

		sendAjax(url,{},function(result){
			if(result.status == 0){
				$('.ckreport').show();
				$proErrMsgTag.text('').hide();
				$this.attr('disabled','disabled');
				$('select[name="project"]').attr('disabled','disabled').select2();
			} else {
				$('.ckreport').hide();
				$proErrMsgTag.text(result.msg).show();
				return false;
			}
		},function(){
			$proErrMsgTag.html('项目申请失败').show();
		});
	});

	//获取报表url
	$('.applygeturl').bind('click',function(){
		$('.error_msg').text('').hide();
		$('.projectBtn').removeAttr('disabled');
	    $('select[name="project"]').attr('disabled',false).select2();
		var reportId = $('select.reportlistselect').val(),
			reportname = $('select.reportlistselect option:selected').text(),
			appjsonstr = $('.getapptoken').val(),
			$errtag = $(this).siblings('.error_msg');
		if(appjsonstr == ""){
			$errtag.text('请先申请应用获取token！').show();
			return false;
		}
		if(reportId == '-1'){
			$errtag.text('请选择其他项目!').show();
			return false;
		}
		var appjson = JSON.parse(appjsonstr), datajson={};
		/* datajson={"reportname":"", reportId":"","appname":"","appToken":""}*/ 
		datajson['appname'] = appjson.app_name;
		datajson['apptoken'] = appjson.token;
        datajson['reportname'] = reportname;
        datajson['reportId'] = reportId;
	    getUrllist(datajson,'applylisturl','applyreportlistbox');

	});

	//clickInfo 查看项目
	$('body').on('click','.clickInfo',function(){
		var $this =$(this).closest('td'), token= $this.attr('data-token'), name = $this.attr('data-name'),tag = [];
		$('.clickInfo').removeClass('checked');
		$(this).addClass('checked');
		console.log('data-name:'+name);
		/* 项目下的 报表list  datajson:{"username":"username","appname":"appname","apptoken":"token"} */
		getReportlist({"username":user_name,"appname":name,"apptoken":token});
		
	});

	//添加项目
	$('body').on('click','.addProjectBtn',function(){
		var $this =$(this).closest('td'), name = $this.attr('data-name'), token = $this.attr('data-token');
		$('.modalProApp').attr({'data-name':name,"data-token":token});
		$('#myModalpro').dialog('open');
		$('.error_modalpromsg').text('').hide();
	});

	//添加报表
	$('body').on('click','.addReportBtn',function(){
		var $this =$(this).closest('td'), datastr = $this.attr('data'), datas = JSON.parse(datastr);

		getReport(datas['proname'],'select[name="addreport"]');
		$('.modalPeportval').val(datastr);
		$('#myModalreport').dialog('open');
		
		$('.error_modalreportmsg').text('').hide();

	});


	//弹窗项目 
  $('#myModalpro').show().dialog({
    title: '申请项目',
    width: 450,
    //height:'',
    closed: true,
    cache: false,
    modal: true,
    buttons: [{
      text:'申请',
      handler:function(){    
        var projectname = $('select.addproject').val(), errtag = $('select.addproject').attr('errortag');
        var appname = $('.modalProApp').attr('data-name'),token = $('.modalProApp').attr('data-token');
        var url = '/addition/getfakecube?action=apply_project&user_name='+user_name+'&app_name='+appname+'&token_val='+token+'&project_name='+projectname;

		sendAjax(url,{},function(result){
			if(result.status == 0){
				$('.'+errtag).text('').hide();
				/* 项目下的 报表list  datajson:{"username":"username","appname":"appname","apptoken":"token"} */
				getReportlist({"username":user_name,"appname":appname,"apptoken":token});
				$('#myModalpro').dialog('close');
			} else {
				$('.'+errtag).text('错误提示：'+result.msg).show();
			}
		},function(){
			$('.'+errtag).text('项目申请失败').show();
		}); 

      }
    },{
      text:'取消',
      handler:function(){
        $('#myModalpro').dialog('close');
      }
    }]
  });

	//报表弹窗
	$('#myModalreport').show().dialog({
	    title: '添加报表',
	    width: 450,
	    //height:'',
	    closed: true,
	    cache: false,
	    modal: true,
	    buttons: [{
	      text:'生成url',
	      handler:function(){    	
		       var reportId = $('select[name="addreport"]').val();
		       var reportname =  $('select[name="addreport"] option:selected').text();
		       var datajson = JSON.parse($('.modalPeportval').val());
		       /* datajson={"reportname":"", reportId":"","appname":"","appToken":""}*/ 
		       datajson['reportname'] = reportname;
		       datajson['reportId'] = reportId;
		       if(reportId == '-1'){
		       		$('.error_modalreportmsg').text('错误提示：请您选择其他项目！').show();
		       		return false;
		       }
			   getUrllist(datajson,'listurl','reportlistcon',function(){
			   		$('#myModalreport').dialog('close');
			   })


	      }

	    },{
	      text:'取消',
	      handler:function(){
	        $('#myModalreport').dialog('close');
	      }
	    }]
	  });
	
});


/*  应用列表 */
function getAppList(user_name){
	var url='/addition/getfakecube?action=get_app_token_list&user_name='+user_name;
	var $appErrMsgTag = $('.applist-box .error_msg');
	sendAjax(url,{},function(result){
	   	if(result.status == 0){
	   		$appErrMsgTag.html('').hide();
	   		var tag = ["<tr><th>应用ID</th><th>应用名称</th><th>应用Token</th><th>操作</th></tr>"],datas = result.data.app_list;
	   		for(var p in datas){
	   			tag.push("<tr><td>"+datas[p].id+"</td><td>"+datas[p].app_name+"</td><td>"+datas[p].token_val+"</td><td data-tag='project' data-name='"+datas[p].app_name+"' data-token='"+datas[p].token_val+"'><button class='clickInfo btn btn-default btn-xs'>查看项目</button> <button class='addProjectBtn btn btn-default btn-xs'>添加项目</button></td></tr>");
	   		}
	   		$('.applist').html(tag.join(''));

	   	} else {
	   		$appErrMsgTag.html(result.msg).show();
	   		return false;
	   	}
	},function(){
		$appErrMsgTag.html('请求数据失败！').show();
	   	return false;
	});
}


/* 项目下的 报表list 
	datajson:{"username":"username","appname":"appname","apptoken":"apptoken"}

*/
function getReportlist(datajson){
	var prourl = '/addition/getfakecube?action=get_app_projects&user_name='+datajson.username+'&app_name='+datajson.appname;
	sendAjax(prourl,{},function(result){
		$('.reportlistcon').hide().find('#listurl').html('');
		if(result.status == 0){
			var datas= result.data.project_list, tag = ['<tr><th>序号</th><th>项目名称</th><th>应用名称</th><th>操作</th></tr>'],count= 1;
			for(var p in datas){
				var tempjson = JSON.stringify({"appname":datajson.appname,"apptoken":datajson.apptoken,"proname":datas[p]});
				tag.push("<tr><td>"+(count++)+"</td><td>"+datas[p]+"</td><td>"+datajson.appname+"</td><td data='"+tempjson+"'><button class='addReportBtn btn btn-default btn-xs'>添加报表</button></td></tr>");
			}
   			$('.projectlist').html(tag.join(''));
		} 
		$('#myTab a:last').tab('show');
		
	},function(){

	});

}

// 获取get url
/* datajson={"reportname":"", reportId":"","appname":"","appToken":""}*/ 
function getUrllist(datajson,tagId,boxid,endfunc){
   var url="GetReportUrl";
   sendAjax(url,{"reportId":datajson.reportId,"appName":datajson.appname,"appToken":datajson.apptoken},function(result){
   	if(result.status == 0){
   		var tag = [], chart = result.data.chart, table = result.data.tablelist;
   		if(chart.length >0 ){
       		for(var p in chart){
       			tag.push('<div class="highlight"><div class="zero-clipboard"><span class="btn-clipboard btn-clipboard-hover">复制</span></div><h5>图表名称：'+chart[p].chartTitle+'</h5><pre><code>'+chart[p].charturl+'</code></pre></div>');
       		}
   		}

   		if(table.length >0){
   			for(var p in table){
   				var title = '';
   				if(table.length >1){
   					title = table[p].title;
   				}
   				tag.push('<div class="highlight"><div class="zero-clipboard"><span class="btn-clipboard btn-clipboard-hover">复制</span></div><h5>表格:'+title+'</h5><pre><code>'+table[p].tableurl+'</code></pre></div>');
   			}
   		}

   		
		$('#'+tagId).html(tag.join(''));
       	clipboard();
       	$('.'+boxid).show().find('.li-panel-head').text("报表名称："+datajson.reportname);

   	} else {
   		
   	}

   	if(typeof(endfunc) != 'undefined'){
   		endfunc();
   	}
  
   	
   },function(){

   });

}

//复制粘帖
function clipboard(){
	var clip = null;  
      clip = new ZeroClipboard(document.getElementsByClassName('btn-clipboard'),{
                    moviePath: "/assets/lib/zeroclipboard/ZeroClipboard.swf"});
      clip.setHandCursor(true);
      clip.on("load", function (client) {
      	clip.on('mousedown',function(){
      		clip.setText($(this).closest('div.highlight').find('code').text());
      	});
        client.on("complete", function (client, args) {
          $('.btn-clipboard').removeClass('checked');
          $(this).addClass('checked');
          console.log('clip:'+args.text);
        });
      });
}

/* 获取 project下的 报表 */
 function getReport(project,selectTag){
 	var url = 'GetReport';
	sendAjax(url,{"project":project},function(result){
		if(result.status == 0){
			var tag=[];
		   	if(result.data.length >0){
			    for(var p in result.data){
			     	tag.push('<option value="'+result.data[p].id+'">'+result.data[p].cn_name+'</option>');
			     }
		 	} else{
		 		 tag = ['<option value="-1">该项目下暂无报表</option>'];
		 	}
		 	$(selectTag).html(tag.join('')).select2();

		 } else {
		     $(selectTag).html('<option value="-1">'+result.msg+'</option>').select2();
		 }

		}, function(){
			$(selectTag).html('<option value="-1">报表加载失败</option>').select2();
	   		return false;
	});
	
 }

/* ajax  post 数据 */
function sendAjax(url,datas,success,errorfunc){
	$.ajax({
	   type: "POST",
	   url: url,
	   data: datas,
	   datatype:"JSON",
	   success: function(result){
	   		if(result == "null") { console.log('result:'+result);return false; }
	   		var results = JSON.parse(result);
	   		success(results);
	   },
	   error: function(){
	   		errorfunc();
	   }
	});

}