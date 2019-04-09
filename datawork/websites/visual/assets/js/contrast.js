/*
 对比功能插件
*/
$.fn.contrast = function(options){
	var $contrastPanel=null;
	var defaults  ={
		type:'easyui',
		url:'/visual/contrast'
	}
	var opts = $.extend(defaults, options);
	if( !window.localStorage){
		$.messager.alert('提示','你的浏览器不支持对比功能，请换成chome或fiefox!','warning');
		return;
	}
	function addNew(content){
		$("#centBoxTemplate .contBoxCont").text(content);
		var newContent = $("#centBoxTemplate").html();
		$(".compresCont").append(newContent);
	}

	function removeAll(){
		$(".compresCont").html("");
		$('.window-shadow').css({
				'height':"auto"
		})
		//$.cookie('userData', null, { path: '/', expires: 1 });
		localStorage.removeItem("userDatav2");
	}

	//对比框显隐切换
	function visualChange(){
		$contrastPanel.toggleClass('icon-xiangzuozhankai').toggleClass('icon-xiangyoushouqi').toggleClass('contrast-flod').toggleClass('contrast-show');
		$contrastPanel.closest('.panel').toggleClass('contrast-flod').toggleClass('contrast-show').addClass('contrast-fixed');
		$contrastPanel.siblings().toggleClass('contrast-hide').toggleClass('contrast-show');
		if ($contrastPanel.hasClass('icon-xiangzuozhankai')) {
            $contrastPanel.find('.panel-tool').css('margin-right', '');
            $contrastPanel.find('.panel-tool').find('a').css('display', '');
			$("#addCatalog").dialog("move",{left:$(window).width() -40});
		}else{
            $contrastPanel.find('.panel-tool').css('margin-right', '-45px');
            $contrastPanel.find('.panel-tool').find('a').css('display', 'block');
			$("#addCatalog").dialog("move",{left:$(window).width() -140});
		}
	}


	function getHover(obj){
		var attr = "."+obj.attr('class');
		if(opts.type =='easyui'){
			obj.hover(function(e){
				var $td = $(this);
				if($td.find(".showbox").length==0)return;
				var $div = $td.find(".compros");

				//2017-11-9 byy, 鼠標hover到某一行多出滾動條
				$div.find(".data_name").css({"height":"auto","line-height":"30px","overflow":"hidden","display":"block"})
				$div.parent().css({"overflow":'visible'});


				$div.css({'position':'relative'});
				$div.children('.showbox').css({
					'display':'block',
					'position':'absolute',
					'color':'#000',
					'top':'23px',
					'left':"10px"
				})
				},function(){
				var $td = $(this);
				if($td.find(".showbox").length==0)return;
				var $div = $td.find(".compros");
				$div.parent().css({ "overflow":'hidden'})
				$div.css({'position':'static'});
				$div.children('.showbox').hide();
			});
		}else{
			$('body').on("mouseover",'.compros',function(){
				var thisObj = $(this);
				if(thisObj.find(".showbox").length==0)return;
				thisObj.css({'position':'relative'});
				thisObj.children('.showbox').css({
					'display':'block',
					'position':'absolute',
					'color':'#000',
					'top':'10px','left':'10px'});
			});
			$('body').on("mouseout",'.compros',function(){
				var thisObj = $(this);
				if(thisObj.find(".showbox").length==0)return;
				thisObj.css({'position':'static'});
				thisObj.children('.showbox').hide();
			});
		}

	}
	function showInfo(bodyCell){
	    bodyCell.delegate(".trend","click",function(){
			cookieName=[];
	        var data = $(this).attr("url");
			var keyname = $(this).attr('keyname');
			cookieName.push({'data':data,'keyname':keyname});
	        // var url = opts.url +'?keysCon='+keysCon;
	        // window.open(url);
	        var postForm = document.createElement("form");//表单对象
					postForm.method="post";
					postForm.action = opts.url ;
					postForm.setAttribute("target", "_blank") ;
					var valueObj = document.createElement("input") ; //email input
					valueObj.setAttribute("name", "keysCon") ;
					valueObj.setAttribute("type", "hidden") ;
					valueObj.setAttribute("value", JSON.stringify(cookieName));
					postForm.appendChild(valueObj) ;
					document.body.appendChild(postForm) ;

			// 是否为外链连接
			if(function(){try{return allcontent,true;}catch(e){ return false;}}()){
				var submitText;
				if(allcontent){
						submitText=allcontent;
				}else{
					submitText = "";
				}
				var valueObj4 = document.createElement("input") ; //email input
				valueObj4.setAttribute("name", "allcontent") ;
				valueObj4.setAttribute("type", "hidden") ;
				valueObj4.setAttribute("value", submitText);
				postForm.appendChild(valueObj4) ;
				document.body.appendChild(postForm) ;
			}



			//起始时间
			if($('.timestyle .inputlist[name=startTime]').val()!=undefined){
			var valueObj2 = document.createElement("input") ; //email input
			valueObj2.setAttribute("name", "startTime") ;
			valueObj2.setAttribute("type", "hidden") ;
			valueObj2.setAttribute("value", $('.timestyle .inputlist[name=startTime]').val());
			postForm.appendChild(valueObj2) ;
			document.body.appendChild(postForm) ;
			}
			//结束时间
			if($('.timestyle .inputlist[name=endTime]').val()!=undefined) {
				var valueObj3 = document.createElement("input"); //email input
				valueObj3.setAttribute("name", "endTime");
				valueObj3.setAttribute("type", "hidden");
				valueObj3.setAttribute("value", $('.timestyle .inputlist[name=endTime]').val());
				postForm.appendChild(valueObj3);
				document.body.appendChild(postForm);
			}
			postForm.submit();
					$(postForm).remove();
	    });
	}
	function clickCompros(bodyCell){
		bodyCell.on("click",".ePopup",function(){
		$('#addCatalog').dialog('open');
		//添加对比时，对比框打开
		if ($contrastPanel.hasClass('icon-xiangzuozhankai')) {
			visualChange();
		}
		var cookieName = [];
		cookieName.splice(0,cookieName.length);
		var data = $(this).attr('url');
			var keyname = $(this).attr('keyname');
			//var  dataName =  data.split('::');
		//var newContent = keyname;
		//有cookies的情况
		if(  window.localStorage['userDatav2']){
			var dataCookie = JSON.parse(window.localStorage['userDatav2']);
			var dataArr = dataCookie;

				  //判断是否重复
			//console.log(dataArr.length);

			if(dataArr.length <=0){
					  if(data == dataArr[0]){
						  $.messager.alert('提示','请不要添加重复的元素!','info');
						  return false;
					  }
				  }else{
					  for(var i=0; i<dataArr.length; i++){
							if(data == dataArr[i]['data']){
								$.messager.alert('提示','请不要添加重复的元素!','info');
								return false;
							}
					  }
				  }
				  //判断是否超过限制
				  if($(".compresCont .contBox").length >10 ){
						  $.messager.alert('提示','只能添加10个数据!','warning');
						  return false;
				  }else{
					  addNew(keyname);
					  for( var i= 0; i< dataArr.length ;i++){
						  cookieName.push(dataArr[i]);
					  }
					  cookieName.push({'data':data,'keyname':keyname});
					  localStorage.userDatav2 = JSON.stringify(cookieName);
				  }
			  //没有来过的情况
			}else{
				  cookieName.push({'data':data,'keyname':keyname});
					addNew(keyname);
				  $('#addCatalog').dialog('open');
				  localStorage.userDatav2 = JSON.stringify(cookieName);
			//console.log(JSON.parse(localStorage.userData));
			}
		});
	}
	var comprosHtml ='<div id="addCatalog" style="display:none">';
		comprosHtml +='<div class="compresCont"></div>';
		comprosHtml +='<form action="'+opts.url+'" id="sub" method="post" target="_blank">';
		comprosHtml +='<input type="hidden" name="keysCon" id="dataBox" value="" style="display:none"/>';
		comprosHtml +='<input type="hidden" name="allcontent" id="allContent" value="" style="display:none"/>';
		comprosHtml +='</form>';
		comprosHtml +='</div>';
		comprosHtml +='<div id="centBoxTemplate">';
		comprosHtml +='<div class="contBox">';
		comprosHtml +='<div class="closeBt panel-tool-close"></div>';
		comprosHtml +='<div class="contBoxCont"></div>';
		comprosHtml +='</div>';
		comprosHtml +='</div>';
		if($("#addCatalog").length ==0){
		 	 $("body").append(comprosHtml);
		}
	$('#addCatalog').show().dialog({
		title: '对比框',
		width: 126,
		height:400,
		cache: false,
		closed: true,
		resizable:true,
		left:$(window).width() -140,
		top:60,
		buttons: [{
			text:'对比',
			handler:function(){
		 	   var userData = window.localStorage['userDatav2'];
		 	   if(userData ==undefined){
		 	   	 $.messager.alert('提示','对比数据不能为空!','info');
		 	   }else{
					 if(function(){try{return allcontent,true;}catch(e){ return false;}}()){
						 if(allcontent){
							 $("#allContent").val(allcontent);
						 }
					 }
		 	   	$("#dataBox").val(userData);
			   	$("#sub").submit();
		 	   }

 			}
		},{
			text:'清空',
			handler:function(){
				removeAll();
			}
		}]
	});
	$(window).scroll(function(){
		var offsetTop = 50 + $(window).scrollTop();
 		$("#addCatalog").dialog("move",{top:offsetTop});
	});
	$(window).resize(function(){
		 var offsetTop = 50 + $(window).scrollTop();
		 $("#addCatalog").dialog("move",{left:$(window).width() -140,top:offsetTop});
	})
	getHover($(this));
	showInfo($(this));
	clickCompros($(this));
	if(window.localStorage['userDatav2']){
		var dataCookie =window.localStorage['userDatav2'];
		var dataArr = JSON.parse(dataCookie);

		//cookies为一个的情况
		$(".compresCont").html("");
	   //if(dataArr.length <=1){
		//	 var dataName =  dataArr[0]['keyname'];
		//	 addNew(dataName);
	   //}else{
			  for(var i=0;i<dataArr.length; i++){
				  var  dataName =  dataArr[i]['keyname'];
				  addNew(dataName);
			 }
	   //}
	   $('#addCatalog').dialog('open');
	}
	//绑定事件
	$("body").on('click','.compresCont .closeBt',function(){
			// 删除后localStorage中去掉此选项，对比框显示也去掉此项
			var data = $(this).next().text();
			var cookiesKey  = localStorage["userDatav2"];
			cookiesKey =  JSON.parse(cookiesKey);
			for(var i=0; i< cookiesKey.length; i++){
				console.log(cookiesKey[i]['keyname']);
				if( data == cookiesKey[i]['keyname'] ){
			   cookiesKey.splice(i,1);
			 }
			}
			localStorage["userDatav2"] = JSON.stringify(cookiesKey);
			$(this).parent().remove();
			$('.window-shadow').css({'height':"auto"});
	});
	$('body').on('mouseover','.compresCont .contBox',function(){
	//对比框中每一项的叉号的显隐
	 $(this).children().eq(0).show();
	});
	$('body').on('mouseout','.compresCont .contBox',function(){
	 $(this).children().eq(0).hide();
	});
	$("#clearData").click(function(){
	   removeAll();
	});

	//确定3个panel哪个为对比panel
	$.each($('.panel-header').find(".panel-title"),function(k,v){
		if ($(v).text()=='对比框') {
			$contrastPanel = $(this).closest('.panel-header');
		}
	});

	if ($contrastPanel) {
		$contrastPanel.on("click", function () {
			visualChange();
		});

		//初始化收起对比信息，点击展开
		$contrastPanel.addClass('contrast-flod iconfont icon-xiangzuozhankai');
		$contrastPanel.closest('.panel').addClass('contrast-flod');
		$contrastPanel.siblings().addClass('contrast-hide');
		if(  window.localStorage['userDatav2']){
			var offsetTop = 50 + $(window).scrollTop();
			$("#addCatalog").dialog("move",{left:$(window).width() -40,top:offsetTop});
		}

	}

}
