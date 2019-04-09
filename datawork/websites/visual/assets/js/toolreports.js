/*
## exlain  表格高级设置 －－重构
## date    2015-08-10 19:00
## data    

*/
var ToolReport=function(option){
	this.boxtag = option.boxtag;
	this.params = option.params;
	this.isEdit = option.isEdit;
	this.reportid = 0;
	this.init();
}

ToolReport.prototype = {
	init:function(){
		//编辑
		if(this.isEdit == '1' && this.params){
			this.setData(this.params);
			this.reportid = this.params.id;
		}
	},
	getData:function(){
		var errmsgArr = [];
		var name = $(this.boxtag).find('input[name="cn_name"]').val(),
			explain = $(this.boxtag).find('input[name="explain"]').val(),
			hql = $(this.boxtag).find('textarea[name="hql"]').val(),
			hqldata = this.getHqlData();

			if(name == ''){
				$.messager.alert('提示','离线查询工具名称不能为空','info');
				return false;
			}
			
			if(hql == ''){
				$.messager.alert('提示','hql不能为空','info');
				return false;
			}

			if(hqldata == 0 && hql !=''){
				$.messager.alert('提示','请您hql解析','info');
				return false;
			}

		var params = { "cn_name":name,'explain':explain,
						'hql':hql,'hqldata':hqldata };

		 return params;
	},
	getHqlData:function(){
		var trTag = $(this.boxtag).find('.hqltable tbody').find('tr'),
			len = trTag.length,
			datajson = [];

			if(len == 0){
				return 0
			} 

		$(trTag).each(function(i){
			var tdbox = $(this).find('td[data-type="cn_name"]'),
				key1 = $(tdbox).attr('data-key'),
				val = $(tdbox).text();
			var tempjson = {};
			tempjson[key1] = val;
			datajson.push(tempjson);
		});

		return datajson;
	},

	setData:function(params){
		if(typeof(params)=='undefined'){ console.log(111);return false; }

		$(this.boxtag).find('input[name="cn_name"]').val(params.cn_name?params.cn_name:"");
	    $(this.boxtag).find('input[name="explain"]').val(params.explain?params.explain:"");
	    var hqlparams = JSON.parse(params.params);
		$(this.boxtag).find('textarea[name="hql"]').val(hqlparams.hql?hqlparams.hql:"");
		var data = hqlparams.hqldata,str="";
		for(var i = 0; i < data.length; i++){
			for(var p in data[i]){
				str+='<tr><td>'+p+'</td><td contenteditable="true" data-type="cn_name" data-key="'+p+'" style="-webkit-box-shadow:1px 1px 7px #d9edf7;">'+data[i][p]+'</td></tr>';
			}	
			
		}
		$(this.boxtag).find('.hqlcontent').show().find('.hqltable tbody').html(str);

	},
	showHqlData:function(data){
		//data: {"status":"0","msg":"success","data":["name","age"]}
		var len = data.length, str = '';
		for(var i = 0; i < len; i++){
			str+='<tr><td>'+data[i]+'</td><td contenteditable="true" data-type="cn_name" data-key="'+data[i]+'" style="-webkit-box-shadow:1px 1px 7px #d9edf7;"></td></tr>';
		}
		$(this.boxtag).find('.hqlcontent').show().find('.hqltable tbody').html(str);
	},
	bindEvent:function(){
		var _this = this;
		//保存数据
		$(this.boxtag).on('click','.toolsaveinfo',function(){
			var params = _this.getData();
			if(!params) { return false; }
			data=JSON.parse(JSON.stringify(params));
			data['type'] = 4 ; //1 普通报表  2对比报表 3 衍生报表 4报表小工具
			data['isEdit'] = isEdit;
			data['hqldata'] = params['hqldata'];
			if(_this.params && _this.reportid !=0){
				data['id'] = _this.reportid;
			}
			$.ajax({
			   type: "POST",
			   url: '/tool/SaveReport',
			   'data': {"sendData":JSON.stringify(data)},
			   datatype:"JSON",
			   success: function(result){
			   		if(result == "null") { console.log('result:'+result);return false; }
			   		var results = JSON.parse(result);
			   		if(results.status==0){
			   			window.location.href="/report/reportlist";
			   			return false;
			   		} else {
			   			$.messager.alert('提示',results.msg,'info');
			   			return false;
			   		}
			   },
			   error: function(){
			   		$.messager.alert('提示','服务器连接失败','info');
			   		return false;
			   }
			});

			return false;

		});

		//hql 解析
		$('body').on('click','.hqlAnalyse',function(){
			var hql = $(_this.boxtag).find('textarea[name="hql"]').val();
			if(hql ==""){
				$.messager.alert('提示','请输入hql','info');
				console.log('hql:'+hql);
				return false;
			}

			$.ajax({
			   type: "POST",
			   url: '/tool/HqlAnalyse',
			   data: {"hql":hql},
			   datatype:"JSON",
			   success: function(result){
			   		if(result == "null") { console.log('result:'+result);return false; }
			   		var results = JSON.parse(result);
			   		if(results.status==0){
			   			_this.showHqlData(results.data);
			   		} else {
			   			$.messager.alert('提示',results.msg,'info');
			   			return false;
			   		}
			   },
			   error: function(){
			   		$.messager.alert('提示','服务器连接失败','info');
			   		return false;
			   }
			});

			return false;
		
		});


	}

};



