/**
 * 根据链接类型，返回类型和相应参数（json格式）
 * new dataSource({
 *  id:xxx, 对象id
 *  params:'', 数据源 默认为null
 	source:{} 初使化数据
	istype:0/1 //表格选择报表类型 图表不需要
	project:{id:'',name:''}
 * });
 * */
var dataSource  =  function(option){
	//var defaults = [{"key":"type","text":"报表类型","data":{"1":"普通报表","2":"对比报表"} }];

	this.option = option;
	this.sourceconf = option.source;
	this.istype = option.istype?option.istype:'-1';
	this.type = option.type?option.type:1;//默认报表类型
	this.project = this.option.project?this.option.project:null;
	//编辑 this.sourceconf 不为{}  新建时还未选项目为{} 需要手动init
	if(JSON.stringify(this.sourceconf) != '{}'){
		this.init();
	}

}
dataSource.prototype = {
	//初使化
	init:function(){
		//初使化模板
	    var interText = doT.template($("#dataSourcetpl").text());
	    var source = $("#"+this.option.id);
		if(this.project){
			this.sourceconf['project'] = this.project;
		}

        source.html(interText(this.sourceconf));

		var $datasourcebox = source.find('.datasourcebox');
		var $custombox = source.find('.custombox');

		//报表类型的是否展现
		if(this.istype>='1'){
			this.creatDefaults();
			source.find('input[name="title"]').val(this.title?this.title:"");
			source.find('input[name="type"][value="'+this.type+'"]').prop({
				checked:true
			});
			var dd = new Date();
			var y = dd.getFullYear();
			var m = dd.getMonth() + 1 < 10 ? "0" + (dd.getMonth() + 1) : dd.getMonth() + 1;
			var d = dd.getDate() < 10 ? "0" + dd.getDate() : dd.getDate();
			var today =y+'-'+m+'-'+d;
			$custombox.find('input.customsql_start').val(today);
			var opt = {
				format:'yyyy-mm-dd',
				language:  'zh-CN',
				weekStart: 1,
				todayBtn:  1,
				autoclose: 1,
				todayHighlight: 1,
				startView: 2,// 1小时  2 日 3月 4年
				minView: 2, // 1小时  2 日 3月 4年
				forceParse: 0,
				endDate: new Date()
			};

			$custombox.find('.customsql_datepicker').datetimepicker(opt);


			$custombox.find('.editcode').attr('id','editcode1');
			//初始化hql编辑器
			window.editcode = ace.edit("editcode1");
			editcode.setTheme("ace/theme/tomorrow_night_eighties");
			editcode.session.setMode("ace/mode/sql");
			editcode.setAutoScrollEditorIntoView(true);
			editcode.setOption("minLines", 10);
			editcode.setOption("maxLines", 20);

			//自定义报表模式 this.option.params
			if(this.option.type == '8'){
				$datasourcebox.hide();
				$custombox.show();
			} else {
				$datasourcebox.show();
				$custombox.hide();
			}
		}

		//绑定维度过滤事件.等事件
		this.groupFilter();
		this.clearGroup();
		this.selectAll();
		this.metricSelect();
		this.selectTabletype();
		//console.log(this.option.params);
		//var params = (this.option.params != undefined && this.option.params !="") ? this.option.params : "";
		if(this.option.params != undefined  && this.option.params  != null){
			this.setSource(this.option.params);
		}

	},
	creatDefaults:function(){
		var source = $("#"+this.option.id);
		var str = '<div class="panel panel-info" style="margin:5px 0;"><div class="panel-heading">表格名称 <span style="font-size:12px;color:#ff0000">&nbsp;(提示：只有一个表格时，该名称默认不显示)</span></div>' +
			'<div class="panel-body" style="padding:5px; line-height:24px;"><input type="text" value="" name="title" style="width:90%" placeholder="请输入表格名称"/></div></div>' +
			'<div class="panel panel-info" id="reportType" style="margin:5px 0;"><div class="panel-heading">表格类型</div><div class="panel-body" style="padding:5px; line-height:24px;">' +
			'<label class="radio-inline" for="type1"><input type="radio" name="type" id="type1" value="1" checked>普通表格</label>' +
			'<label class="radio-inline" for="type2"><input type="radio" name="type" id="type2" value="2">对比表格</label>'+
		    '<label class="radio-inline" for="type7"><input type="radio" name="type" id="type7" value="7">聚合表格</label>'+
			'<label class="radio-inline" for="type8"><input type="radio" name="type" id="type8" value="8">自定义表格</label>'+
			'</div></div>'+
		    '<div class="panel panel-info isaddmeterbox" style="margin:5px 0;"><div class="panel-heading"><input type="checkbox" value="" name="isaddmeter" /> 新增副表 <lable style="color:#ff0000">(提示：新增副表时主表不分页，且只显示100条)</lable></div> </div>';

		source.prepend(str);

	},
	source:function(){
		return $("#"+this.option.id);
	},
	//获取维度信息
	sendAjax:function(confdata,metricArr,endFun){
		var  metric = metricArr?metricArr:[];
		source = $("#"+this.option.id);
	    var url = '/visual/getMetric';
	    $('body').mask('正在请求加载数据...');
		$.ajax({
			 type: "get",
			 url: url,
			 data: confdata,
			 //async: false,  
			 dataType: "json",
			 success: function(data){
				$('body').unmask();			
				if(data.status ==0){
					source.find('.metricUl').show();
					source.find('.metricUl').find('.list-group-item').each(function(){
					    if($.inArray($(this).attr('name'),data.data) >= 0){
					        $(this).show().removeClass('checked').addClass('show');
					        if($.inArray($(this).attr('name'),metric) >= 0){
					            $(this).addClass('checked');
					        }
					    }else{
					        $(this).hide().removeClass('checked show');
					    }
					});
					source.find('.metricUl').find('li').each(function(){
					    var _this = this;
					    $(_this).find('p').each(function(){
					      if($(this).has('a.show').length>0){
					        $(this).show().addClass('show').find('span').show().addClass('show');
					      } else{
					        $(this).hide().removeClass('show');
					      }
					    });
					    
					    if($(_this).find('p.show').length>0){
					      $(_this).show().addClass('show');
					    }else{
					      $(_this).hide().removeClass('show');
					    }
					});
				}else{
					$.messager.alert('提示',data.msg,'warning');
				}
				if(endFun){
				  endFun(data);
				}   
			 }
		});	
	},
	//维度选择事件
	groupFilter:function(){
		var id = this.option.id;
		source =$("#"+this.option.id);
		var that = this;
		source.on('click','.grouplist',function(){

			var objThis = $(this);
			var  all =[];
			source.find("input.grouplist").each(function(){
				if($(this).is(":checked")){
					var dim = $(this).attr('dim');
					if(dim){
						dim =  eval("("+ dim +")");
					}
					var dimArr = memgry(dim);
					all.push(dimArr);
				} 
			});
			var diff = arry_diff(all);
			if(diff.length >0){
				source.find("input.grouplist").each(function(){
				  if($.inArray($(this).attr('dimensions'),diff) >= 0){
				    $(this).removeAttr('disabled').css({
				      '-webkit-box-shadow':'1px 1px 3px #000'
				    });
				  }else{
				    $(this).attr('disabled','disabled').css({
				      '-webkit-box-shadow':"0px 0px 0px #eee"
				    });
				  }
				}); 
			}else{
				if(objThis.is(":checked") ){
				  source.find("input.grouplist").each(function(){
				     if($(this).attr('dimensions') !=objThis.attr('dimensions')){
				        $(this).attr('disabled','disabled').css({
				          '-webkit-box-shadow':"0px 0px 0px #eee"
				        });
				     }    
				  }); 
				}else{
				  source.find("input.grouplist").each(function(){
				    $(this).removeAttr('disabled').css({
				      '-webkit-box-shadow':'1px 1px 3px #000'
				    });
				  }); 
				}

			}
			//处理指标
			var dims =[];
			source.find('input.grouplist').each(function(k,v){     
			if($(this).is(":checked")){
			  dims.push($(this).attr('dimensions'));
			}
			});
			var project = that.sourceconf.metric.name;
			that.sendAjax({'project':project,'dimensions':dims.join(",")},false, function(){
				that.reset(source.find('.groupCheckAll'));
			});		
		});
	},
	//全选按钮重置
	reset:function(obj){
		obj.text("全选");
		obj.attr('data-status','clear');
	},
	clear:function(){
		source = $("#"+this.option.id);
		source.find('.grouplist').each(function(){
			$(this).removeAttr('disabled').css({'-webkit-box-shadow':'1px 1px 3px #000'});
			this.checked =false;
		});
		//还原指标
		source.find('.metricUl').find('.list-group-item').each(function(){
			$(this).removeClass('show checked').hide();
		});
		source.find('.metricUl').find('li').removeClass('show').hide().find('p').removeClass('show').hide().find('span').removeClass('show');

		if(this.istype >="1" && window.editcode){
			//sql 清空
			window.editcode.setValue('');
		}
		source.closest('#reportgrade').find('.chart_conf').val('');
	},
	//清空维度
	clearGroup:function(){
		source = $("#"+this.option.id);
		var that = this;
		source.on('click','.clearAll',function(){
			that.clear();
		});

		var $datasourcebox = source.find('.datasourcebox');
		var $custombox = source.find('.custombox');
		$custombox.hide();
		$datasourcebox.show();

	},
	//全选指标
	selectAll:function(){
		source = $("#"+this.option.id);
		source.on('click','.groupCheckAll',function(){
		  var status = $(this).attr('data-status');
		  if(status == 'clear'){
		    source.find('.metricUl li.show p.show a.show').addClass('checked');
		    $(this).attr('data-status','checked');
		    $(this).text("取消");
		  } else {
		     source.find('.metricUl li.show p.show a.show').removeClass('checked');
		    $(this).attr('data-status','clear'); 
		    $(this).text("全选");
		  }
		});
	},
	//指标选中事件
	metricSelect:function(){
	   var source = $("#"+this.option.id);
	   source.off('click','.metricUl li a.list-group-item');
	   source.on('click','.metricUl li a.list-group-item',function(){
	      $(this).toggleClass('checked');
	      $('#metric_error').hide();
	      var $parentP = $(this).closest('p');
	      if( $parentP.has('a.checked').length > 0){
	        $parentP.addClass('show');
	      } else{
	        $parentP.removeClass('show');
	      }
	  });
	},
	//获取数据源
	getSource:function(){
		var id = this.option.id;
		source = $("#"+this.option.id);
		var index = source.attr('listindex');
		var sourceParams ={};
		sourceParams.project = this.sourceconf.metric.name;
		sourceParams.title = source.find('input[name="title"]').val();
		sourceParams.type = source.find('input[name="type"]:checked').val();
		sourceParams.group = [];
		sourceParams.metric = [];
		//是否主表 建副表
		sourceParams.isaddmeter = (source.find('input[name="isaddmeter"]').is(":checked") && index == 0) ?"1":"0";
		//获取报表类型
		if(this.istype >= '1'){
			type = source.find('input[name="type"]:checked').val();
			params.type = this.type = sourceParams.type = type;
		}
		//自定义报表类型
		if(this.istype >= '1' && this.type == '8'){
			var sql = window.editcode.getValue();
			var customsql_start = source.find('.customsql_start').val();
			sourceParams.sql = sql;
			sourceParams.customsql_start = customsql_start;
		} else {
			source.find('input.grouplist').each(function(k,v){
				if($(this).is(":checked")){
					var one ={};
					one.key = $(this).attr('dimensions');
					one.name = $.trim($(this).parent().text());
					one.explain = $.trim($(this).attr('explain'));
					sourceParams.group.push(one);
				}
			});
			//获取指标
			source.find(".metriclist").each(function(){
				if($(this).hasClass('checked')){
					var one ={};
					one.key = $(this).attr('name');
					one.name = $.trim($(this).text());
					one.explain = $.trim($(this).attr('explain'));
					sourceParams.metric.push(one);
				}
			});
		}

		return sourceParams;
	},
	//fakecube 格式转化成 原 source格式
	srcExcel:function(param) {
	   groupArr = param.group.split(",");
	   metricArr =param.metric.split(",");
	   var  obj ={
	      group:[],
	      metric:[]
	   };
	   source = $("#"+this.option.id);
	   for(var i=0; i < groupArr.length; i++){
	    source.find('input.grouplist').each(function(){   
	      if(groupArr[i] == $(this).attr('dimensions') ){
	        var one ={};
	        one.key = $(this).attr('dimensions');
	        one.name = $.trim($(this).parent().text());
	        one.explain = $.trim($(this).attr('explain'));
	        obj.group.push(one);
	      }  
	    });  
	   }
	   for(var i=0; i < metricArr.length; i++){
	      source.find(".metriclist").each(function(){  
	        if(metricArr[i] == $(this).attr('name')){
	         var one ={};
			    one.key = $(this).attr('name');
			    one.name = $.trim($(this).text());
			    one.explain = $.trim($(this).attr('explain'));
	          	obj.metric.push(one);
	        }
	      });
	   }   
	   return obj;
	},
	//处理成fakecube数据源格式  
	getMte:function(arr){
	  keyArr =[];
	  for(var i=0; i<arr.length; i++){
	    keyArr.push(arr[i].key);
	  }
	  return  keyArr.join(",",keyArr);
	},
	//返回fakecube数据源生成 （注：此方法依赖 页面数据， 慎用）
	getFakeCube:function(){
		data = this.getSource();
		data.group = this.getMte(data.group);
	    data.metric = this.getMte(data.metric);
	    var startTime = $('input[name=startTime]').val();
	    var endTime = $('input[name=endTime]').val();
	    if(startTime ==undefined){
	      data.date =  endTime;
	      data.edate = endTime;
	    }else{
	      data.date =  startTime;
	      data.edate = endTime;
	    }  
	    return data;
	},
	//设置数据源 params ={"title":"","master":"0主表/1副表"}
	setSource:function(params){
		source = $("#"+this.option.id);
		var gooupArr = params.group.split(",");
		var metricArr =params.metric.split(",");
		//还原维度
		var dims =[];
		var that = this;
		source.find('.grouplist').each(function(){
		  dimKey = $(this).attr('dimensions');
		  if($.inArray(dimKey,gooupArr) >=0){
		    var dim = $(this).attr('dim');
		    if(dim){
		      dim =  eval("("+ dim +")");
		    }
		    var dimArr = memgry(dim);
		    var objThis = $(this);
		    this.checked =true;
		    source.find("input.grouplist").each(function(){
		      if($(this).attr('disabled') == undefined){
		        if($.inArray($(this).attr('dimensions'),dimArr) >= 0){
		           $(this).removeAttr('disabled').css({
		            '-webkit-box-shadow':'1px 1px 3px #000'
		          });
		        }else{
		          if(objThis.attr('dimensions') != $(this).attr('dimensions')){
		            $(this).attr('disabled','disabled').css({
		              '-webkit-box-shadow':"0px 0px 0px #eee"
		            });
		          }   
		        }
		      }  
		    });
		    this.checked =true;  
		    dims.push(dimKey);
		  }
		});
		//报表类型的是否展现
		if(this.istype>='1'){
			source.find('input[name="title"]').val(params.title?params.title:"");
			source.find('input[name="type"][value="'+params.type+'"]').prop({
				checked:true
			});
			//是否添加副表
			if(typeof(params.master) != 'undefined' && params.master == '0'){
				source.find('.isaddmeterbox').show();
			} else {
				source.find('.isaddmeterbox').hide();
				params.isaddmeter = 0;

			}

			var checkflag = (typeof(params.isaddmeter) !='undefined' && params.isaddmeter =='1') ? true : false;
			source.find('input[name="isaddmeter"]').prop({"checked":checkflag});

			//自定义表格
			if(params.type == '8' && params.sql && window.editcode){
				window.editcode.setValue(params.sql);
			}
			if(params.customsql_start){
				source.find('input.customsql_start').val(params.customsql_start);
			}

		}

		var project = that.sourceconf.metric.name;
		that.sendAjax({'project':project,'dimensions':dims.join(",")},metricArr);
	},
	//自定义操作 --预留接口
	select:function(endFun){
		var that = this;
		source = $("#"+this.option.id);
		source.on('click','.saveSource',function(){
			if(that.istype && that.istype >= '1'){
				var index = source.attr('listindex');
				var title = source.find('input[name="title"]').val();
				//判断表格名称是否重复
				if(typeof(params)!='undefined' && params['tablelist'] ){
					var len = params.tablelist.length;
					for(var i = 0; i<len; i++){
						if(title == params.tablelist[i].title && i != index){
							source.find('#metric_error').text('表格名称不能重复！').show();
							return false;
						}
					}
				}


				if(title == ''){
					source.find('#metric_error').text('请输入表格名称').show();
					return false;
				} else {
					source.find('#metric_error').text('提示：维度和可选指标为必选项').hide();
				}
			}

		    var groupCount = 0, metricCount =0 ;
		    source.find(".groupUl input.grouplist").each(function(){
		          if($(this).is(":checked")){
		             groupCount++; 
		          } 
		    });
		    source.find(".metricUl a.metriclist").each(function(){
		          if($(this).hasClass("checked")){
		             metricCount++; 
		          } 
		    });
		    if(groupCount >0 && metricCount >0){
		    	if(endFun){
			 		endFun(source,that.getSource());
				}
		    }else{
		        source.find('#metric_error').show();
		    }
		});
	},
	//自定义表格
	selectTabletype:function(){
		var source = $("#"+this.option.id),_this = this;
		var $datasourcebox = source.find('.datasourcebox');
		var $custombox = source.find('.custombox');
		source.off('click','input[name="type"]');
		source.on('click','input[name="type"]',function(){
			var type = $(this).val();
			_this.clear();
			if(type == '8'){
				$datasourcebox.hide();
				$custombox.show();
			} else {
				$datasourcebox.show();
				$custombox.hide();
			}
		});

	},
	// 校验sql
	checkSql:function(sucesscallback){
		var source = $("#"+this.option.id);
		var that = this;
		source.off('click','button.checksql');
		source.on('click','button.checksql',function(){
			var $sqlerror = $(this).next('.sql_error');
			$sqlerror.hide(),
			$customsqlerror = $(this).closest('.custombox').find('.customsql_starterror');

			if(that.istype && that.istype >= '1'){
				var index = source.attr('listindex');
				var title = source.find('input[name="title"]').val();
				var customsql_start = source.find('input.customsql_start').val();
				if(title == ''){
					$sqlerror.text('请输入表格名称').show();
					return false;
				}
				if(customsql_start == ''){
					$customsqlerror.text('请输入起始时间').show();
					return false;
				} else {
					$customsqlerror.hide();
				}

				//判断表格名称是否重复
				if(typeof(params)!='undefined' && params['tablelist'] ){
					var len = params.tablelist.length;
					for(var i = 0; i<len; i++){
						if(title == params.tablelist[i].title && i != index){
							$sqlerror.text('提示：表格名称不能重复！').show();
							return false;
						}
					}
				}

			}

			var sql = window.editcode.getValue();
			if(sql == ''){
				$sqlerror.text('提示：请输入sql').show();
				return false;
			}
			var date = customsql_start,edate = getBeforeDate(0);
			var sendata = {"type":8,"sql":sql,"date":date,"edate":edate};
			var getdata = that.getSource();

			var url = '/report/checksql';
			$.ajax({
				type: "get",
				url: url,
				data:sendata,
				//async: false,
				dataType: "json",
				success: function (result) {
					console.log(result);
					if(result.status == 0){
						if(result.data.data!=null && result.data.data && result.data.data.length>0){
							//处理data
							var sqldata = [],datas = result.data.data[0],colums =result.data.colums,reg = new RegExp("[@/'#()%&~!$%^*=+]"),
								errarr = [];
							//var reg =  new RegExp(temstr);
							for(var p in datas){
								if(reg.test(p)){
									errarr.push(p);
								} else {
									sqldata.push({"key":p,"name":"","explain":"","dim":colums[p].type});
								}

							}
							if(errarr.length>0){
								$sqlerror.text('提示:字段'+errarr.join(",")+' 含有特殊字符,请输入别名').show();
								return false;
							}
							getdata['sql'] = sql;
							getdata['sqldata'] = sqldata;
							getdata['customsql_start'] = customsql_start;
							sucesscallback(source,getdata);
						} else {
							$sqlerror.text('提示:返回数据为空').show();
						}

					} else {
						$sqlerror.text('提示:'+result.msg).show();
					}
				}
			});

		});

	}


}