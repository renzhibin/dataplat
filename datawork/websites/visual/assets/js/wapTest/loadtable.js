var Table=function(option){
	this.table = option.table ? option.table:{};
	this.init();
}

Table.prototype = {

	init:function() {
		this.showTable();
	},

	showTable:function(){
		//获取表格
		$tabletag = $('.configBox').find('.tablecontent');
		this.tableAjax(this.table,$tabletag);

	},

	//表格ajax
	tableAjax:function (table,obj) {
		var _this = this;
		var url = '../dd.json';
		_this.getDatagrad(obj,table,url);
	},

	//生成datagrad表头
	getDatagrad:function(obj,data,url) {
		var _this = this;
		var columnArr = this.getcolumn(data);
		var pagesize = 10;
		var ispagesize = true;
		//分页设置
		var pageList  = $.unique([10,50,100,parseInt(pagesize)]);
		pageList.sort(function(a,b){return a>b?1:-1});
		var queryjson = {};
		var pagesize_option = {
			url:url,
			rownumbers:true,
			singleSelect:true,
			collapsible:false,
			multiSort:false,
			loadMsg:"数据正在加载。。。",
			autoRowHeight:true,
			pagination:ispagesize,
			pageSize: pagesize,//每页显示的记录条数，默认为10 
		  	pageList: pageList,//可以设置每页记录条数的列表
			method:'post',
			remoteSort:true,
			frozenColumns:[columnArr.frozenColumns],
			columns:[columnArr.columns],
			queryParams:queryjson,//请求远程数据同时给action方法传参
			loadFilter:function(result) {
				return _this.formatMetric(result);//需要可以替换数据
			},
			onLoadSuccess:function(result){
				if (result.showMsg) {
					_this.loadSuccess(obj,result);
				}
			}
		};

 		//分页设置
 		if(ispagesize){
 			obj.datagrid(pagesize_option);
			var p = obj.datagrid('getPager');
			$(p).pagination({ 
			  beforePageText: '',//页数文本框前显示的汉字
			  afterPageText: '/ {pages} 页', 
			  displayMsg: '共 {total} 条数据'
			});
 		}
	},

	loadSuccess: function (obj,result) {
		var $errorbox = $('.configBox').find('.error_showmsg');
		$errorbox.show().find('.text').text(result.showMsg);
	},

	formatMetric:function (result) {
		return result;
	},

	//生成列
	getcolumn:function(data){
		var columnArr ={};
		columnArr.frozenColumns = [];
		columnArr.columns  =[];
		//获取表头配置
		if(data && data.length >0 ){
			for (var j = 0; j < data.length; j++) {

				var oneHeader = {};
				//列名
				oneHeader.title = data[j].name;
				//列属性
				oneHeader.field = data[j].name;
				//处理宽度
				if( data[j].width != undefined ){
					oneHeader.width = data[j].width;
				}else{
					oneHeader.width ='100';
				}

				//居中 居左 居右？
				if( data[j].align != undefined ){
					oneHeader.align = data[j].align;
				}else{
					oneHeader.align='left';
				}

				oneHeader.sortable = true;

				//处理说明
				if(data[j].explain !=undefined && data[j].explain  !='' ){
					oneHeader.title = data[j].name +'&nbsp;<a data-toggle="tooltip" title="'+data[j].explain+'" class="showinfo glyphicon glyphicon-question-sign"></a>';
					//如果名称相同，则去掉名称 提示框
					if(data[j].explain == data[j].name){
						oneHeader.title = data[j].name;
					}
				}

				//处理颜色
				if(data[j].styler !=undefined){
					oneHeader.styler = data[j].styler;
				}

				//处理是否固定
				if( parseInt(data[j].fixed) ){
					columnArr.frozenColumns.push(oneHeader);
				} else {
					columnArr.columns.push(oneHeader);
				}
			}
		}

		return columnArr;
	}
};