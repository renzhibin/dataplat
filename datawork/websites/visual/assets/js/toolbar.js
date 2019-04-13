/*
## explain: toolbar－－重构信息
## date: 	manli  2015-07-22
## data:    params.timereport = {
				date_type: "2",              // 1 单天(对比报表指定单天)  2 区间时间 开始－结束
				dateview_type: "3",			 // 时间粒度 即时间插件 1小时 2天 3月
				interval: 数字/0,		     // 默认时间间隔0
				offset: "5",				 // 结束时间偏移量
				shortcut: ["7","30"]		 // 快捷时间设置
			 }
## method :  new ToolBar({"params":params,"boxtag":boxtag,"is_setpage":"0/1"});

*/

var ToolBar=function(option){
	this.params = option.params ? option.params : {};  // params 参数
	this.boxtag = option.boxtag; 					   // 显示视图的tag名称s
	this.init();
};

ToolBar.prototype = {
	init:function(paramsarg){
		var params = (arguments.length==0 || typeof(paramsarg) == 'undefined')?this.params : paramsarg;
		if(JSON.stringify(params)== '{}' || typeof (params) == 'undefined'){ return false }
		if(!params.basereport){
			params['basereport'] = basereport;
		}
		this.showView(params);

	},

	//判断pc和手机，适应屏幕
	/*isPC: function() {
		var ua = window.navigator.userAgent.toLowerCase();
		var agent = ["android", "iphone", "symbianos", "windows phone", "ipad", "ipod"];
		var flag = true;
		if ( flag ) {
			for (var i = 0; i < agent.length; i++) {
				if( ua.indexOf(agent[i]) +1 ) {
					flag = false;
					break;
				}
			}
		}
		console.log(flag +'tool');
		return flag;
	},*/

	//显示视图
	showView:function(params){
		var _this = this;
		if(typeof(params) == "undefined" || JSON.stringify(params) == '{}'){
			return false;
		}
		//判断是否 warp页
		params['wap'] = window.location.href.indexOf('wap/') >=0 ? true :false;
		//params['wap'] = !_this.isPC();

		var interText = doT.template($("#searchtmpl").html());
		if(params['wap']){
			interText = doT.template($("#wap_searchtmpl").text());
		}
		$(this.boxtag).html(interText(params));
		this.initData(params);


	},
	//隐藏视图
	hideView:function(){
		$(this.boxtag).hide();
	},
	//初始化设置数据
	initData:function(params){
		if(typeof(params)=="undefined" || !params.timereport){ return false }

		//时间视图 1小时 2天 3月级
		var dateview_type = (params.timereport.dateview_type) ? params.timereport.dateview_type:2,
		 	date_typearr = ["day","hour","day","month"],
			date_type = date_typearr[dateview_type];

	 	var startTime, endTime,
	 		 offset = params.timereport.offset;

		if(params.timereport.date_type ==1){
			endTime = startTime = getOffset(offset,"0",dateview_type);
		} else {
			startTime = getOffset(offset,params.timereport.interval,dateview_type);
			endTime = getOffset(offset,"0",dateview_type);
		}

		params.timereport['date'] = startTime;
		params.timereport['edate'] = endTime;

		var customsql_start = ''; //自定义起始时间
		if(params.tablelist){
			for(var p=0;  p < params.tablelist.length; p++){
				params.tablelist[p].date = startTime;
				params.tablelist[p].edate = endTime;
				params.tablelist[p]['date_type'] = date_type;

				if(params.tablelist[p].type == '8' && params.tablelist[p].customsql_start){
					customsql_start = params.tablelist[p].customsql_start;
				}
			}
		}

	    if(params.chart !=undefined && params.chart.length >0){
	    	var newchart = []; //chart［i］=null 时
	        for(var i=0; i< params.chart.length; i++){
	        	if(params.chart[i]){
	        		params.chart[i].date = startTime;
	            	params.chart[i].edate = endTime;
	            	params.chart[i]['date_type'] = date_type;
	            	newchart.push(params.chart[i]);
	        	}
	        }
	        params.chart = newchart;
	    }

	    $(this.boxtag).find('input[name=startTime]').val(startTime);
	    $(this.boxtag).find('input[name=endTime]').val(endTime);
	    $(this.boxtag).find('input[name=report_title]').val(params.basereport.cn_name);

	    //报表时间视图params.timereport.dateview_type
	    //var dateview_type = (typeof(params)!='undefined' && params.timereport && params.timereport.dateview_type) ?params.timereport.dateview_type:2;
	    var formatarr = ['yyyy-mm-dd','yyyy-mm-dd hh:00','yyyy-mm-dd','yyyy-mm'],
	        startView = [2,2,2,3,4];
		var opt = {
			format:formatarr[dateview_type],
			language:  'zh-CN',
			weekStart: 1,
			todayBtn:  1,
			autoclose: 1,
			todayHighlight: 1,
			startView: startView[dateview_type],// 1小时  2 日 3月 4年
			minView: dateview_type, // 1小时  2 日 3月 4年
			forceParse: 0,
			endDate: new Date()
			//startDate:new Date(params.tablelist[0].customsql_start)
		};
		if( customsql_start != ''){
			opt['startDate'] = new Date(customsql_start);
		}

		if(!params.wap){
			$('.datepicker').datetimepicker(opt);
		}


		this.dialog();


	},
	dialog:function(){
		$(function(){
			$('select').select2();
			//时间格式
			// $('#addbox input.datepicker').datepicker('hide');
			$('#addbox input.daterange').daterangepicker({
				'singleDatePicker': true,
				'timePicker': true,
				'showDropdowns': true,
				'format': 'YYYY-MM-DD HH:mm',
				'language':'zn-ch',
				'locale':{
					'applyLabel':'确定',
					'cancelLabel':'取消',
					'fromLabel':'开始',
					'toLabel':'结束',
					'monthNames':"一月_二月_三月_四月_五月_六月_七月_八月_九月_十月_十一月_十二月".split("_"),
					'daysOfWeek':"日_一_二_三_四_五_六".split("_")},
				'showDropdowns':false,
				'applyClass':'btn-success sure'});

			// $('#dashboard').datagrid();
			$('.data-table').dataTable({
				"iDisplayLength":10,
				"bJQueryUI": true,
				"sPaginationType": "full_numbers",
				"sDom": '<""l>t<"F"fp>',
				"bSort":false,
				"bPaginate":true,
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
			//添加新启动功能
			$('body').on('click','.addStart',function(e){
				$('input[name=start_time]').val('');
				$('input[name=end_time]').val('');
				$('#addbox').dialog('open');
				$("#addbox").dialog("move",{top:e.pageY});
			});

			$('#addbox').dialog({
				title: '报表配置',
				width: 450,
				//height:190,
				closed: true,
				cache: false,
				modal: true,
				buttons: [{
					text:'确定',
					iconCls:'icon-ok',
					handler:function(){
						var  runinfo  ={}, $addbox = $('#addbox');
						runinfo.report_id  = $addbox.find('input[name=report_id]').val();
						runinfo.user_name  = $addbox.find('input[name=user_name]').val();
						runinfo.start_time  = $addbox.find('input[name=start_time]').val();
						runinfo.end_time  = $addbox.find('input[name=end_time]').val();
						if(runinfo.start_time ==''){
							$.messager.alert('提示','请选择开始时间','info');
							return;
						}
						if(runinfo.end_time ==''){
							$.messager.alert('提示','请选择结束时间','info');
							return;
						}
						$('body').mask('正在操作...');
						$.post('/visual/run_task', runinfo,function(data){
							$('body').unmask();
							if(data.status ==0){
								$.messager.alert('提示',data.msg,'info');
								$('#addbox').dialog('close');
								location.reload();
							}else{
								$.messager.alert('提示',data.msg,'info');
							}
						}, 'json');
					}
				},{
					text:'取消',
					handler:function(){
						$('#addbox').dialog('close');
					}
				}]
			});
		});
	},
	bindEvent:function(tables){
		var _this = this, date_typearr = ["day","hour","day","month"],
			date_typenub = (typeof(_this.params)!='undefined' && _this.params.timereport &&_this.params.timereport.dateview_type) ?_this.params.timereport.dateview_type:2;
			date_type = date_typearr[date_typenub];

		// 前天 昨天点击事件
		$('body').on('click','.btn-special',function(){
			if(_this.params == undefined) { return false; }

	        var  num = $(this).attr('data-option');
			var starttime = '',endtime='';
			// 在页面点击快捷按钮不添加样式
	        // $(this).addClass('active').siblings().removeClass('active');
	        num = parseInt("-"+num);
	        if( _this.params.timereport.date_type ==1){
	            $('input[name=endTime]').val(GetDateStr(num,date_typenub));
	            $("#search").find('input[name=endTime]').val(GetDateStr(num,date_typenub));

				starttime = endtime = GetDateStr(num,date_typenub);

	        }else{
	            $('input[name=startTime]').val(GetDateStr(num,date_typenub));
	            $('input[name=endTime]').val(GetDateStr(-1,date_typenub));
				endtime = GetDateStr(-1,date_typenub);
				starttime= GetDateStr(num,date_typenub);

	        }
			params.timereport['date'] = starttime;
			params.timereport['edate'] = endtime;

			if(params.tablelist){
				for(var p=0;  p < params.tablelist.length; p++){
					_this.params.tablelist[p].date = starttime;
					_this.params.tablelist[p].edate = endtime;
					_this.params.tablelist[p]['date_type'] = date_type;
				}

			}

	        if(_this.params.chart  !=undefined){
	            for(var j =0; j< _this.params.chart.length; j++){
	                _this.params.chart[j].date = starttime;
	                _this.params.chart[j].edate = endtime;
	                _this.params.chart[j]['date_type'] = date_type;
	            }
	        }


	        //获取图表
	        if(_this.params.chart !=undefined){
	            getChartBox(_this.params.chart,$("#chartTpl"));
	            chartAjax(_this.params.chart,$("#chartTpl"));
	        }

	        //获取search 搜索
			if(params.tablelist){
				for(var n = 0; n< tables.length; n++){
					if(tables[n].table.grade.pubdata.reshape_type !=1){
						tables[n].apifilter();
					}
					tables[n].showTable();
					tables[n].initcustom(_this.params);
				}
			}
			/*//清空url的 时间
	        var href = window.location.href;
	        var indexof = href.indexOf('?');
	        if (indexof >= 0){
	           var hrefnew = href.substr(0,indexof);
	           window.history.replaceState( null, null, hrefnew);
	        }*/

    	});

		//时间插件change事件
		$('.datepicker').on('changeDate',function(ev){
			var startTime, endTime;
	        if( _this.params.timereport.date_type ==2 ){
	            startTime = $('input[name=startTime]').val();
	            endTime = $('input[name=endTime]').val();

	            if(startTime.valueOf() > endTime.valueOf()){
	                $.messager.alert('提示','开始时间大于结束时间','warning');
	                return false;
	            }

	            if(startTime.valueOf() > endTime.valueOf()){
	                $.messager.alert('提示','结束时间小于开始时间','warning');
	                return false;
	            }
	        } else {
	            startTime = endTime = $('input[name=endTime]').val();
	        }

			params.timereport['date'] = startTime;
			params.timereport['edate'] = endTime;

			if(params.tablelist){
				for(var p=0;  p < params.tablelist.length; p++){
					_this.params.tablelist[p].date = startTime;
					_this.params.tablelist[p].edate = endTime;
					_this.params.tablelist[p]['date_type'] = date_type;
				}

			}

	        if(_this.params.chart  !=undefined){
	            for(var j =0; j< params.chart.length; j++){
	                _this.params.chart[j].date = startTime;
	                _this.params.chart[j].edate = endTime;
	                _this.params.chart[j]['date_type'] = date_type;
	            }
	        }


	        //获取图表
	        if(_this.params.chart !=undefined){
	            getChartBox(_this.params.chart,$("#chartTpl"));
	            chartAjax(_this.params.chart,$("#chartTpl"));
	        }
			//获取search 搜索
			for(var n = 0; n<tables.length; n++){
				if(tables[n].table.grade.pubdata.reshape_type !=1){
					tables[n].apifilter();
				}
				tables[n].showTable();
				tables[n].initcustom(_this.params);
			}

			//清空url的 时间
	       /* var href = window.location.href;
	        var indexof = href.indexOf('?');
	        if (indexof >= 0){
	           var hrefnew = href.substr(0,indexof);
	           window.history.replaceState( null, null, hrefnew);
	        }*/

	    });

		if(window.location.href.indexOf('wap')>0){
			//时间插件change事件
			$('.datepicker').on('change',function(ev){
				var startTime, endTime;
				if( _this.params.timereport.date_type ==2 ){
					startTime = $('input[name=startTime]').val();
					endTime = $('input[name=endTime]').val();

					if(startTime.valueOf() > endTime.valueOf()){
						$.messager.alert('提示','开始时间大于结束时间','warning');
						return false;
					}

					if(startTime.valueOf() > endTime.valueOf()){
						$.messager.alert('提示','结束时间小于开始时间','warning');
						return false;
					}
				} else {
					startTime = endTime = $('input[name=endTime]').val();
				}

				params.timereport['date'] = startTime;
				params.timereport['edate'] = endTime;

				if(params.tablelist){
					for(var p=0;  p < params.tablelist.length; p++){
						_this.params.tablelist[p].date = startTime;
						_this.params.tablelist[p].edate = endTime;
						_this.params.tablelist[p]['date_type'] = date_type;
					}

				}

				if(_this.params.chart  !=undefined){
					for(var j =0; j< params.chart.length; j++){
						_this.params.chart[j].date = startTime;
						_this.params.chart[j].edate = endTime;
						_this.params.chart[j]['date_type'] = date_type;
					}
				}


				//获取图表
				if(_this.params.chart !=undefined){
					getChartBox(_this.params.chart,$("#chartTpl"));
					chartAjax(_this.params.chart,$("#chartTpl"));
				}
				//获取search 搜索
				for(var n = 0; n<tables.length; n++){
					if(tables[n].table.grade.pubdata.reshape_type !=1){
						tables[n].apifilter();
					}
					tables[n].showTable();
					tables[n].initcustom(_this.params);
				}

				//清空url的 时间
				/* var href = window.location.href;
				 var indexof = href.indexOf('?');
				 if (indexof >= 0){
				 var hrefnew = href.substr(0,indexof);
				 window.history.replaceState( null, null, hrefnew);
				 }*/

			});
		}

		//下载功能
	    $('#downtpl').show().dialog({
	        title: '下载报表',
	        width: 250,
	        //height:'',
			closed: true,
	        cache: false,
	        modal: true,
	        buttons: [{
	            text:'确定',
	            id:'download',
	            handler:function(){
	                var type = $('#downtpl').find('input[name=downaa]:checked').val();
	                var name = $('#downtpl').find('h5').text();
	                if(type ==1){
	                    var toDownPNG =GetQueryString("toDownPng");
	                    //如果是执行E-mail截图的功能就只下载图片
	                    if(toDownPNG !=null && toDownPNG.toString().length>=1 && toDownPNG==2)
	                    {
	                        var obj  = document.getElementById('chartTpl');
	                    }else{
	                        var obj  = document.getElementById('canvas_down');
	                    }
	                    html2canvas(obj, {
	                        "logging": true, //Enable log (use Web Console for get Errors and Warnings)
	                        "proxy":"/visual/img",
	                        "onrendered": function(canvas) {
	                            myImage = canvas.toDataURL("image/png");
	                            $('#imgSub').find('input[name=name]').val(name);
                            	if( typeof(report_id)!= "undefined"){
                            	 $('#imgSub').find('input[name=report_id]').val(report_id);
                            	}else{
                            		 $('#imgSub').find('input[name=report_id]').val('test');
                            	}
	                            $('#imgSub').find('input[name=data]').val(myImage);
	                            $('#imgSub').submit();
	                            // $.ajax({
	                            //     type : "POST",
	                            //     url : '/visual/img',
	                            //     data : {data:myImage,'name':name},
	                            //     success : function(data){
	                            //           console.log(data);
	                            //     }
	                            // });
	                        }
	                    });
	                }else{
	                    //$('#downData').submit();

						var $excel_con = $('.excel_con'), $checkbox = $excel_con.find('input[type="checkbox"]');
						var len = $checkbox.length;
						if(len ==0){ return false; }
						var count = 0;
						for(var i = 0; i <len; i++){
							if($checkbox.eq(i).is(":checked")){
								var title = $checkbox.eq(i).val(), $form = $('form[title="'+title+'"]') ;
								var dataval = $form.find('input[name="downConfig"]').val();
								if(dataval != ''){
									var datajson = JSON.parse(decodeURIComponent(dataval)),
									gradedata = datajson.grade.data,
									customkeydata = {};

									//获取本地保存的自定义 显示隐藏的数据
									if(window.localStorage && typeof(params) !='undefined'  && params.reportId && window.localStorage['customkey']){
										var customkeystr = window.localStorage.customkey,
											customkey = JSON.parse(customkeystr);
										if(customkey[params.reportId]){
											var temparr = customkey[params.reportId];
											for(var i= 0,len =temparr.length; i < len; i++){
												if(temparr[i].tabletitle == title){
													customkeydata = temparr[i].data;
													break;
												}
											}
										}
									}
									//如果有设置自定义显示隐藏 则替换下载报表的数据
									if(JSON.stringify(customkeydata) != '{}'){
										for(var n = 0; n<gradedata.length; n++){
											if(customkeydata[gradedata[n].key] && customkeydata[gradedata[n].key] == '0'){
												gradedata[n].hide = 1;
											}
										}
										datajson.grade.data = gradedata;
									}
									$form.find('input[name="downConfig"]').val(encodeURIComponent(JSON.stringify(datajson)));

								}

								if(i >0){
									setTimeout(function(){
										$form.submit();
									},5000);
								} else {
									$form.submit();
								}
								count++
							}
						}
						if(count==0){
							$('.down_error').html('请选择表格下载！').show();
							return false;
						}

	                }
	                $('#downtpl').dialog('close');
	            }
	        },{
	            text:'取消',
	            id:'cancel',
	            handler:function(){
	                $('#downtpl').dialog('close');
	            }
	        }]
	    });
	    //收藏功能
	    $('body').on('click','.collclick', function(){
	        var id = $(this).attr('data-id');
	        if(id ==0){
	            $.messager.alert('提示','预览报表不能收藏','info');
	        }else{
	            var url ="";

	            if($(this).find('i').hasClass('glyphicon-star-empty')){
	                 url ="/report/AddCollect";
	             }else{
	                 url ="/report/deletecollect";
	             }
	            // if(menu_id >0 && !isCollect){
	            //     url ="/report/AddCollect";
	            // }else{
	            //     url ="/report/deletecollect";
	            // }
	            $.get(url, {
	                'id': id
	            },function(data){
	                if(data.status ==0){
						$.messager.alert('提示',data.msg,'info');
						if( "undefined"  != typeof menu_id ){
							if(  menu_id < 1){
								window.location.href ='/visual/index';
							}else{
								window.location.reload();
							}
						}else{
							window.location.reload();
						}
	                }else{
	                    $.messager.alert('提示',data.msg,'info');
	                }
	            }, 'json');
	        }
	    });

		$('body').on('click','.downclick',function(){
	        $('#downtpl').find('h5').text(_this.params.basereport.cn_name);
			var top = (($(window).height() - 210) * 0.5+$(document).scrollTop());
			var left = ($(window).width() - 250) * 0.5;
			//弹出框居中的问题
			$('.down_error').hide();
			$('#downtpl').window('open').window('resize',{top: top,left:left});
    	});

		//选择excel事件
		$('body').on('click','input[name="downaa"]',function(){
			var val = $(this).val();
			if(val == '2'){
				$('.excel_con').show();
			} else {
				$('.excel_con').hide();
			}
		});

		if ($('.muneIcon').css('display')=='none'){
			//滚动时 元素固定事件，手机端不滚动
			_this.scrollbox();
		}


    	// 设置是否固定
    	$('body').on('click','.navbar #scroll',function(){
				var len = params.tablelist.length;
    		if($(this).hasClass('closed')){
                this.style.transform='rotate(-35deg)';
    			$(this).removeClass('closed');
    			_this.scrollbox();
					if(len === 1){
							_this.pinHeader({type:"2",top:35});
					}
    		} else {
                this.style.transform='rotate(0deg)'
    			$(this).addClass('closed');
                $('#search').removeClass('change');
    			_this.clearScroll();
					if(len === 1){
							_this.pinHeader({type:"2",top:0});
					}
    		}
    	});
	},
	//toolbar 随鼠标滚动而固定在顶部
	scrollbox:function(){
		$('.navbar #scroll').removeClass('closed');
		var _this = this;
		//滚动固定事件
    	$(this.boxtag).css('position','relative');
		var $elm = $(_this.boxtag).find('.navbar');
		var width = $elm.width();
	    $(window).on('scroll', function () {
			var obj_box = $(_this.boxtag).offset();
			if (typeof(obj_box) != "undefined") {
				// 解决特定宽度下抖动问题
				// if($(document).height()-$(window).height()<200){
				// 	$('.datagrid-pager.pagination:last').css({'margin-bottom': '200px'})
				// }else{
				// 	$('.datagrid-pager.pagination:last').css({'margin-bottom':'0px'})
				// }
				var startPos = obj_box.top;
				var p = $(window).scrollTop();
				// $elm.css('position', ((p) > startPos) ? 'absolute' : 'static');
				// $elm.css('top', ((p) > startPos) ? (p-startPos)+'px' : '');
                $($('.navbar')[1]).css({'position':'fixed','top':'0px','z-index':9999,'width':'100%'})
                $elm.css('position', ((p) > startPos) ? 'fixed' : 'relative');
                if(p>startPos){
                    $('#search').addClass('change');
                }else{
                    $('#search').removeClass('change');
                }
				$elm.css('z-index', ((p) > startPos) ?'111111' : '1');
				$elm.css('width', width+'px');
			}
	    });
	},

	//恢复固定状态
	clearScroll:function(){
		var _this = this;
		var $elm = $(_this.boxtag).find('.navbar');
		$elm.removeAttr('style');
		$(window).unbind ('scroll');
	},
	pinHeader:function(params){
				$(".datagrid-htable").pin({
					padding: {top: params.top,bottom: 10}
				});
				$('.datagrid-body').on("scroll",function(){
					if(params.type == 1){
						var width = $($('.datagrid-btable')[0]).css("width");
						var scrollleft = $(this).scrollLeft();
						$('.datagrid-htable').css({left:$(this).offset().left-$(this).scrollLeft()});
						$($('.datagrid-htable')[0]).css({left:"0",zIndex:"999"});
						$($('.datagrid-btable')[0]).css({left:"0",zIndex:"999"});
					}else{
						$($('.datagrid-htable')[1]).css({left:$(this).offset().left-$(this).scrollLeft()});
						$($('.datagrid-btable')[0]).css({left:"0",zIndex:"10"});
						$($('.datagrid-htable')[0]).css({left:"100",zIndex:"999"});
						$($('.datagrid-btable')[0]).css({left:"0",zIndex:"999"});
						if($($('#sidebar').find('ul')[0]).height() !=$('body').height()){
							$($('#sidebar').find('ul')[0]).css({height:$('body').height()});
						}
					}

				});
	}

};

/* 时间设置弹窗事件
## initdata:  params.timereport.initEditData={
				date_type: {"1":"单天",'2':'区间'},    			// 1 单天(对比报表指定单天)  2 区间时间 开始－结束
				dateview_type:{"1":"小时",'2':'天','3':'月'},		// 时间粒度 即时间插件 1小时 2天 3月
				interval: ‘0’,		    	 					// 默认时间间隔0
				offset: {"1":"1天"，“2”:"2天"},				 	// 结束时间偏移量
				shortcut: {"1":{"1":"昨天","2","前天"},
						   '2':{7:"最近7天","30":"最近30天"}},    // 快捷时间设置

			}

*/
//toolbar setting 设置弹窗模块
var ToolBarSet=function(option){
	this.params = option.params ? option.params : {};  // params 参数
	this.boxtag = option.boxtag; 					   // 显示视图的tag名称s
	this.init();
};

ToolBarSet.prototype = {
	init:function(){
		//报表类型 1普通报表  2对比报表
		//this.params.type = type;
		//this.params.type = 1;//没有对比普通报表之分
	},
	//初始化编辑弹窗的数据－－－暂时无用
	initEditData:function(){
		var initEditData;
		if(typeof(this.option.initEditData)=='undefined'){
			this.option.initEditData = {
				date_type: {"1":"单天",'2':'区间'},    			// 1 单天(对比报表指定单天)  2 区间时间 开始－结束
				dateview_type:{"1":"小时",'2':'天','3':'月'},		// 时间粒度 即时间插件 1小时 2天 3月
				interval: "0",		    	 					// 默认时间间隔0
				offset: {"1":"1天","2":"2天"},				 	// 结束时间偏移量
				shortcut: {"1":{"1":"昨天","2":"前天"},
						   "2":{"7":"最近7天","30":"最近30天"}} // 快捷时间设置
			};
		}
	},
	setData:function(params){
		if(typeof(params) == 'undefined' || !params.timereport) { return false; }
		var $boxtag = $(this.boxtag), timereport = params.timereport;
		//处理时间信息
	    $boxtag.find('select[name=date_type]').val(timereport.date_type);
	    $('select[name=date_type]').trigger('change',timereport.date_type);

	    var dateview_type = timereport.dateview_type?timereport.dateview_type:'2'; //默认为天
	    $boxtag.find('input[name="dateview_type"][value="'+dateview_type+'"]').prop('checked',true);

	    if(timereport.date_type ==1){
	       this.setSelectOption($(this.boxtag).find('select.single_offset'),timereport.offset,'天',2);
	       var obj =$(this.boxtag).find('.single_shortcut').children('button');
	       this.setShortCut(obj,timereport.shortcut);
	       shortArr =  this.getShortCut(obj);
	    }else{
	       $boxtag.find('.interval_offset_num').numberspinner('setValue',timereport.interval);

	       //$("#timereport").find('select.interval_offset').select2('val',timereport.offset);
	       this.setSelectOption($boxtag.find('select.interval_offset'),timereport.offset,'',2);
	       var obj = $boxtag.find('.interval_shortcut').children('button');
	       this.setShortCut(obj,timereport.shortcut);
	       shortArr = this.getShortCut(obj);
	    }

	},
	getData:function(){
		var report_type = 1,
			$boxtag = $(this.boxtag),
 	  		timeconfig ={};
 	  	//对比报表只能是 单天的
 	  	timeconfig.date_type = report_type == '1'?$boxtag.find('select.date_type').val():'1';
      	//2015-07-21 时间框架 时 天 月 级别
      	var input_dateviewtype = (report_type == 1)? "dateview_type":"constrast_dateview_type"
      	timeconfig.dateview_type = $boxtag.find('input[name="'+input_dateviewtype+'"]:checked').val();

      	if(timeconfig.date_type=='1'){
            timeconfig.interval =0;
            timeconfig.offset = $boxtag.find('select.single_offset').val();
            var obj =$boxtag.find('.single_shortcut').children('button');
            shortArr =  this.getShortCut(obj);
            timeconfig.shortcut = shortArr.key;
      	}else{
            timeconfig.interval = $boxtag.find('.interval_offset_num').val();
            timeconfig.offset = $boxtag.find('select.interval_offset').val();
            var obj = $boxtag.find('.interval_shortcut').children('button');
            shortArr =  this.getShortCut(obj);
            timeconfig.shortcut = shortArr.key;
      	}
		this.params.timereport = timereport = timeconfig;
		return timeconfig;

	},
	bindEvent:function(toolbar){
		var _this = this;
	  	//时间信息框
	  	$(this.boxtag).show().dialog({
		    title: '时间信息设置',
		    width: 450,
		    //height:'',
		    closed: true,
		    cache: false,
		    modal: true,
		    buttons: [{
		      text:'确定',
		      iconCls:'icon-ok',
		      handler:function(){
		      	  //获取值
		          var timeconfig = _this.getData();
		          //组织数据
		          toolbar.init(_this.params);
		          $('button[data-option=timereport],button[data-option=timecontrast]').addClass('btn-xs btnPosition');
		          $(_this.boxtag).dialog('close');
		          $('button[data-option=chartreport],button.addTable,button[data-option=dataSource],button[data-option=contrasreport],button[data-option=reportgrade]').removeAttr('disabled');
		          //$('.selectChange').select2({allowClear:true});
		      }
		    },{
		      text:'取消',
		      handler:function(){
		        $(_this.boxtag).dialog('close');
		      }
		    }]
	  	});

	  	//时间类型切换
		$('body').on('change','.date_type',function(){
		    if($(this).val() ==1){
		      $(this.boxtag).find('.single').show();
		      $(this.boxtag).find('.interval').hide();
		    }else{
		      $(this.boxtag).find('.single').hide();
		      $(this.boxtag).find('.interval').show();
		    }
		});
	},
	//获取当前偏移数据
	getShortCut:function(obj){
	  var data={};
	  data.key = [];
	  data.name =[];
	  obj.each(function(){
	    if($(this).hasClass('active')){
	      data.key.push($(this).attr('data-option'));
	      data.name.push($(this).text());
	    }
	  });
	  return data;
	},
	//设置发前偏移量
	setShortCut:function(obj,arr){
	  var _this = this;
	  if(typeof(arr) =='undefined'){
	    arr = [];
	  }
	  obj.each(function(){
	     if( _this.in_array($(this).attr('data-option'),arr) ){
	       $(this).addClass('active');
	     }else{
	       $(this).removeClass('active');
	     }
	  });
	},

	in_array:function(stringToSearch, arrayToSearch) {
	   for (s = 0; s < arrayToSearch.length; s++) {
	    thisEntry = arrayToSearch[s].toString();
	    if (thisEntry == stringToSearch) {
	     return true;
	    }
	   }
	   return false;
	},

	setSelectOption:function(obj,num,text,type){
	    var  optArr = [];
	    obj.find('option').each(function(){
	       optArr.push($(this).attr('value'));
	    });
	    if(!in_array( num,optArr)){
	      if(type ==1){
	        var  str ="<option value='"+num+"'>"+text+num+"</option>";
	      }else{
	        var  str ="<option value='"+num+"'>"+num+text+"</option>";
	      }

	      obj.append(str);
	    }
	    obj.select2('val',num);
	}

	/*//设置时间
	setvalue:function(obj){
	  $.messager.prompt('自定义默认时间','请输入一个数字:',function(v){
	    if (v){
	      var optionStr ="<option value='"+v+"'>"+v+"天</option>";
	      $(obj).prev().append(optionStr);
	      $(obj).prev().select2('val',[v]);
	    }
	  });
	},

	//设置样式
	setTop:function(obj){

	  $.messager.prompt('自定义数据','请输入一个数字:',function(v){
	    if (v){
	      var optionStr ="<option value='"+v+"'>Top"+v+"</option>";
	      $(obj).prev().append(optionStr);
	      $(obj).prev().select2('val',[v]);
	    }
	  });
	},
	//设置单位
	setUnit:function(obj){

	  $.messager.prompt('增加单位','请输入单位名称:',function(v){
	    if (v){
	      var optionStr ="<option value='"+v+"'>"+v+"</option>";
	      $.post('/report/addUnit', {
	         'name':v
	        },function(data){
	         $('body').unmask();
	         if(data.status ==0){
	            $(obj).prev().append(optionStr);
	            $(obj).prev().select2('val',[v]);
	         }else{
	            $.messager.alert('提示',data.msg,'warning');
	         }
	      },'json');
	    }
	  });
	},*/







}
