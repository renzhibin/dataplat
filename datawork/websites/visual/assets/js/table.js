/*
## exlain  表格公共方法－－重构
## date    2015-07-17
## data new Table ({"params":params,"mylocalParams":mylocalParams,"isEdit":"0/1",boxtag":'.configBox'});
*/
var Table = function(option) {
	//this.params = option.params;
	
	this.table = option.table ? option.table : {};
	this.mylocaltable = option.mylocaltable ? option.mylocaltable : JSON.parse(JSON.stringify(this.table));
	this.boxtag = option.boxtag;
	this.searchtag = option.boxtag.find('.filter');
	this.titletag = option.boxtag.find('.tabletitle');
	this.isEdit = option.isEdit;
	this.init();
};

Table.prototype = {
	init: function() {
		
		if (this.table == undefined || isEmptyObject(this.table)) {
			return false
		}
		
		var searchConfig = this.getSearchConfig(this.table), //表格search配置值

			tempurlsearch = this.getTableSearch(), // 获取url的search数组
			defaultsearch = []; // search的默认值
		//searchConfig= {"name2",is_accurate: 0, reportcheck: 0,reportdimensions: 0,reportgroup: 0, reportkey: "is_autm_new" ,reportsource: "1:name1↵2:name2"}
		if (searchConfig && typeof(searchConfig) != 'undefined' && searchConfig.length > 0) {
			// serachconfig 里的值 和url的值 合并
			if (tempurlsearch && tempurlsearch != 'undefined' && tempurlsearch.length > 0) {
				for (var i = 0, len = searchConfig.length; i < len; i++) {
					for (var n = 0, nlen = tempurlsearch.length; n < nlen; n++) {
						if (searchConfig[i].reportkey == tempurlsearch[n].key) {
							searchConfig[i].defaultsearch = tempurlsearch[n].val[0];
						}
					}

				}
			}
		}

		defaultsearch = getDefaultSearch(searchConfig, this.table.type);
		var xxxx =[];
		try{
			xxxx =  JSON.parse(this.table.filter);
		}catch(e){
			// console.log('解析失败');
		}
	 	if(xxxx.length >0){
 			//是否相对占比2015-05-27  apisearch－组合到filter字段 去重
			if (typeof(this.table.grade.pubdata.isproportion) != 'undefined' && parseInt(this.table.grade.pubdata.isproportion)) {
				var newObj  = JSON.parse(JSON.stringify(defaultsearch));
	 			var neaaaa  = $.merge(newObj,xxxx);
				this.table.filter = JSON.stringify(neaaaa);
			}
	 	} else {
            this.table.filter = JSON.stringify(defaultsearch);
		}
		this.table.search = JSON.stringify(defaultsearch);
		
		this.showTable();
		//this.bindEvent();
	},
	showTable: function() {
		
		//获取表格
		$tabletag = $(this.boxtag).find('.tablecontent');
		var type = parseInt(this.table.type);
		switch (type) {
			case 1:
				this.tableAjax(this.table, $tabletag, this.isEdit);
				break;
			case 2:
				this.tableContrast(this.table, $tabletag, this.isEdit);
				break;
			case 8:
				this.tableSqlData(this.table, $tabletag, this.isEdit);
				break;
			default:
				this.tableAjax(this.table, $tabletag, this.isEdit);
		}
		
		// 2016/9/27 下午8:59:33 增加移动端表头顶端固定功能
		var deviceType = browserRedirect();
		var len = params.tablelist.length;
		if (deviceType) {
			if (len === 1) {
				if (function() {
						try {
							return allcontent, true;
						} catch (e) {
							return false;
						}
					}()) {
					if (allcontent) {
						this.pinHeader({
							type: "1",
							top: 0
						});
					} else {
						this.pinHeader({
							type: "1",
							top: 40
						});
					}
				}
			}
		} else {
            this.tableHeaderPin();
			/*if (len === 1) {
				if (function() {
						try {
							return allcontent, true;
						} catch (e) {
							return false;
						}
					}()) {
					if (allcontent) {
						this.pinHeader({
							type: "2",
							top: 0
						});
					} else {
						this.pinHeader({
							type: "2",
							top: 35
						});
					}
				}
			}*/
		}
	},
	tableHeaderPin:function() {
        var _this = this;
        var navHeight = $(".navbar").height();
		$(window).on('scroll', function () {
			var tableArr = [];
            var tableTop = $(_this.boxtag).offset().top;
            var tableEnd = tableTop + $(_this.boxtag).height();
            var htables = $(".datagrid-htable");
            var htableHeight = 0;
            htables.each(function(){
                if (($(this).offset().top >= tableTop) && ($(this).offset().top <= tableEnd)) {
                    tableArr.push($(this));
                    htableHeight = $(this).height();
                }
            });
            for ( var i = 0; i <tableArr.length; i++){
                if (tableArr[i].width() > 1500) {
                	return;
				};
            }
            var p = $(window).scrollTop();
            tableEnd = tableEnd - 2*htableHeight;
			if ((p > tableTop) && (p < tableEnd)) {
                for ( var i = 0; i <tableArr.length; i++){
                    tableArr[i].css({'position': 'fixed', 'top': navHeight, 'z-index': 8900});
                }
			} else {
                for ( var i = 0; i <tableArr.length; i++){
                    tableArr[i].css('position', '');
                    tableArr[i].css('top', '');
                    tableArr[i].css('z-index', '');
                }
			}
        });
	},
	// 2016/9/27 下午8:59:33 增加移动端表头顶端固定方法
	pinHeader: function(params) {
		$(".datagrid-htable").pin({
			padding: {
				top: params.top,
				bottom: 10
			}
		});
		$($('#sidebar').find('ul')[0]).css({
			backgroundColor: "#f8fcff"
		});
		$('.datagrid-body').on("scroll", function() {
			if (params.type == 1) {
				var width = $($('.datagrid-btable')[0]).css("width");
				var scrollleft = $(this).scrollLeft();
				$('.datagrid-htable').css({
					left: $(this).offset().left - $(this).scrollLeft()
				});
				$($('.datagrid-htable')[0]).css({
					left: "0",
					zIndex: "999"
				});
				$($('.datagrid-btable')[0]).css({
					left: "0",
					zIndex: "999"
				});
			} else {
				$($('.datagrid-htable')[1]).css({
					left: $(this).offset().left - $(this).scrollLeft(),
					overflow: "hidden"
				});
				$($('.datagrid-htable')[0]).css({
					left: "100",
					zIndex: "999"
				});
				$($('.datagrid-btable')[0]).css({
					left: "0",
					zIndex: "999"
				});
				if ($($('#sidebar').find('ul')[0]).height() != $('body').height()) {
					$($('#sidebar').find('ul')[0]).css({
						height: $('body').height()
					});
				}
			}

		});

	},
	//serachConfig
	getSearchConfig: function(table) {
		var searchConfig = [];
		//新数据格式处理
		var searchArr = table.grade.data;
		if (searchArr != undefined && searchArr.length > 0) {
			for (var i = 0; i < searchArr.length; i++) {
				if (searchArr[i].issearch != null && searchArr[i].issearch != '' && searchArr[i].issearch !=
					'-') {

					if (searchArr[i].issearch.is_check) {
						var one = {};
						one.reportkey = searchArr[i].key;
						if (parseInt(searchArr[i].issearch.is_accurate) == 1) {
							one.is_accurate = parseInt(searchArr[i].issearch.is_accurate);
						} else {
							one.is_accurate = parseInt(searchArr[i].issearch.is_accurate);
						}
						if (searchArr[i].search != null && searchArr[i].search != '' && searchArr[i].search != '-') {
							one.reportsource = searchArr[i].search.val;
							//reprot check
							if (parseInt(searchArr[i].search.is_check) == 1) {
								one.reportcheck = parseInt(searchArr[i].search.is_check);
							} else {
								one.reportcheck = 0;
							}
							//是否维度
							if (parseInt(searchArr[i].search.reportgroup) == 1) {
								one.reportgroup = parseInt(searchArr[i].search.reportgroup);
							} else {
								one.reportgroup = 0;
							}
							//是否多维
							if (parseInt(searchArr[i].search.reportdimensions) == 1) {
								one.reportdimensions = parseInt(searchArr[i].search.reportdimensions);
							} else {
								one.reportdimensions = 0;
							}

							//是否有默认值
							one.defaultsearch = searchArr[i].search.defaultsearch ? searchArr[i].search.defaultsearch :
								"";
						}
						searchConfig.push(one);
					}
				}
			}
		}
		//处理all
		if (searchConfig.length > 0) {
			for (var i = 0; i < searchConfig.length; i++) {
				if (searchConfig[i].reportkey == 'all') {
					searchConfig.splice(i, 1);
					break;
				}
			}
		}

		return searchConfig;
	},

	getSrcfilter: function(table) {
		var srcfilter = [];
		if (typeof(this.table) != 'undefined' && this.table.hasOwnProperty('metric')) {
			srcfilter = this.table.filter ? this.table.filter : [];
		}
		return srcfilter;
	},
	//为searchContent渲染 获取数据，表格表头纬度过滤查询功能 例如：设备 版本的查询数据功能
	getSearchVal: function(data) {

		var newSearch = [],
			filter = [],
			_this = this;
		var tempurlsearch = _this.getTableSearch(); //url的值

		if (data.length > 0) {
			for (var i = 0; i < data.length; i++) {
				//获取搜索信息
				if (data[i].filter != '-' && data[i].filter != null && data[i].filter.op != 'filter_not' &&
					data[i].filter.op != '') {
					var one = data[i].filter;
					one.key = data[i].key;
					filter.push(one);
				}
				//搜索不为空
				if (data[i].issearch != '-' && data[i].issearch != null && data[i].issearch != '') {
					if (!data[i].issearch.is_check) {
						return;
					}
					var onesearch = {};
					onesearch.key = data[i].key;
					onesearch.title = data[i].name;
					//设置是否精确查找
					if (data[i].issearch.is_accurate != undefined && parseInt(data[i].issearch.is_accurate)) {
						onesearch.is_accurate = 1;
					}
					//;
					//设置多选与search类型 input 或 select
					if (data[i].search != null && data[i].search != '-' && data[i].search != '') {
						//判断是否是选择框还是输入框
						if (data[i].search.val != undefined && data[i].search.val != '') {
							//设置是否多选

							if (parseInt(data[i].search.is_check)) {
								onesearch.isadd = 1;
							} else {
								onesearch.isadd = 0;
							}
							onesearch.type = 'select';
							sourceArr = data[i].search.val.split("\n");

							onesearch.data_source = [];
							for (var k = 0; k < sourceArr.length; k++) {
								var one = {};
								onesource = sourceArr[k].split(":");
								one.key = onesource[0];
								one.value = onesource[1];
								onesearch.data_source.push(one);
							}
						} else {
							onesearch.type = 'input';
						}
						onesearch.tableType = _this.table.type;
						searchKey = onesearch.key.split(".").join("_");
						var searchObj = $(_this.searchtag).find('[name=' + searchKey + ']');
						var searchObjval = searchObj.val();
						//编辑的时候 不获取的搜索框里的值
						if (searchObj.length > 0 && searchObjval != undefined && _this.isEdit != 1) {
							onesearch.value = searchObj.val();
						} else {
							if (_this.table && _this.table.search) {
								var params_search = JSON.parse(_this.table.search);
								for (var p in params_search) {
									var paramskey = params_search[p]['key'].split(".").join("_");
									if (paramskey == onesearch.key) {
										onesearch.value = params_search[p]['val'][0];
										break;
									}
								}
							}
						}
						//为searchContent 纬度搜索过滤 设置默认值
						if (data[i].search.reportgroup != undefined && parseInt(data[i].search.reportgroup)) {
							onesearch.type = 'input';
						}
						//多维处理
						if (data[i].search.reportdimensions != undefined && parseInt(data[i].search.reportdimensions)) {
							continue;
						}
						//是否有默认值
						onesearch.defaultsearch = data[i].search.defaultsearch ? data[i].search.defaultsearch : "";

						if (tempurlsearch) {
							for (var p in tempurlsearch) {
								if (tempurlsearch[p].key == onesearch.key) {
									onesearch.value = tempurlsearch[p].val[0];
								}
							}
						}


					}
					//多维数据 不生成搜索框
					newSearch.push(onesearch);
				}
			}
			if (newSearch.length > 0) {
				var htmls = createNewElement(newSearch, this.table);
				$(this.searchtag).html(htmls);
				$('select').select2({
					allowClear: true
				});
				//还原多选
				for (var i = 0; i < newSearch.length; i++) {
					if (newSearch[i].isadd == 1 && newSearch[i].value != '') {
						var value = newSearch[i].value;
                        if(value != '' && value != undefined && value != null && value.constructor == String) {
                            value = value.split(',');
						}
						$(_this.searchtag).find('select[name="' + newSearch[i].key + '"]').select2('val', value);
					}
				}
			}
		}
	},
	// 获取表格维度模糊搜索的值 //数据过滤数值filter －》数据搜索设置值 getSearchConfig
	apifilter: function() {
		var table = this.table,
			searchConfig = [],
			apisearch = [],
			_this = this;
		if (typeof(table) == 'undefined') {
			return false
		}
		if (!table.filter) {
			table['filter'] = [];
		} else {
			//重新赋值
			table.filter = [];
			for (var p1 in table.grade.data) {
				if (table.grade.data[p1].filter && typeof(table.grade.data[p1].filter) == 'object' && table.grade
					.data[p1].filter.val.length > 0 && table.grade.data[p1].filter.op != 'filter_not' && typeof(
						table.grade.data[p1].filter.key) != 'undefined') {
					table.filter.push(table.grade.data[p1].filter);
				}
			}
		}

		searchConfig = this.getSearchConfig(table);
		if ("undefined" != typeof searchConfig) {

			for (var i = 0; i < searchConfig.length; i++) {

				var one = {};
				var str = searchConfig[i].reportkey.split(".").join("_");
				if (searchConfig[i].reportcheck == 1) {
                    // var val = $(_this.searchtag).find('select[name*=' + str + "]").select2('val');
					var val = $(_this.searchtag).find('select[name=' + str + "]").select2('val');
				} else {
					var val = $(_this.searchtag).find('[name=' + str + "]").val();
				}
				if (val != 'filter_not' && val != '' && val != undefined) {
					if (typeof(val) == 'object') {
						one.val = val;
					} else {
						one.val = val.split("?");
					}
					one.key = searchConfig[i].reportkey;
					if (searchConfig[i].reportcheck == 1) {
						one.op = 'in';
					} else {
						if (searchConfig[i].is_accurate == 1) {
							one.op = '=';
						} else {
							//对比报表只能精确查找
							if (_this.table.type == 2) {
								one.op = '=';
							} else {
								one.op = 'like';
							}

						}
					}
					//是否有默认值
					one.defaultsearch = searchConfig[i].defaultsearch ? searchConfig[i].defaultsearch : "";
					apisearch.push(one);
				}

			}
			if (apisearch.length > 0) {
				table.search = JSON.stringify(apisearch);
				//是否相对占比2015-05-27  apisearch－组合到filter字段 去重
				if (typeof(table.grade.pubdata.isproportion) != 'undefined' && parseInt(table.grade.pubdata.isproportion)) {
					for (var p in apisearch) {
						table.filter.push(apisearch[p]);
					}
				}

				//如果是对比报表，并且设置了维度过滤
				if (table.type == 2) {
					if (searchConfig[0].reportgroup == 1) {
						table.filter = apisearch;
					}
				}
			} else {
				if ("undefined" != typeof _this.table.search) {
					delete table.search;
				}
			}
			table.filter = JSON.stringify(table.filter);
			_this.table = table;
		}
		return apisearch;
	},
	//url的search
	getTableSearch: function() {
		//var searchConfig = this.getSearchConfig(this.table);
		//获取url后面的参数
		var locationsearch = window.location.search;
		var tempsearch = [],
			op = 'like',
			_this = this;
		if (locationsearch != "") {
			//去掉问号 ？
			locationsearch = decodeURI(locationsearch.substring(1, locationsearch.length));
			var tsearch = locationsearch.split("&");
			for (var i = 0; i < tsearch.length; i++) {
				var key = tsearch[i].split('=')[0];
				var value = tsearch[i].split('=')[1];
				//外链带edate数据
				if (key == 'edate') {
					_this.table.date = _this.table.edate = value;
					if (params.chart) {
						for (var p in params.chart) {
							params.chart[p].edate = params.chart[p].date = value;
						}
					}
					continue;
				} else if (key == 'date') {
					_this.table.date = value;
					if (params.chart) {
						for (var j in params.chart) {
							params.chart[j].date = value;
						}
					}
					continue;
				}
				/*//判断searchconfig 是否精确查找 is_accurate
				if(searchConfig&&searchConfig.length > 0){
				    for(var p in searchConfig){
				        if(searchConfig[p].reportkey == key){
				            op = (searchConfig[p].is_accurate=='1')?'=':'like';
				            break;
				        }
				    }
				}*/
				tempsearch.push({
					'key': key,
					"val": [value],
					"op": op
				});
			}
			return tempsearch;
		} else {
			return false;
		}


		/*else {
             //多维度首次加载时 search数值searchConfig
            //[{reportkey: "client_device",reportsource: "client_device:iphone'\n'client_device:android"}]

             if (_this.table.type == 2 && searchConfig && typeof(searchConfig)!='undefined' && searchConfig.length>0){
                tempsearch = getDefaultSearch(searchConfig);
                return  JSON.stringify(tempsearch);
             }


        }*/

	},

	loadSuccess: function(obj, result, fakecube) {
		var $errorbox = obj.closest('.configBox').find('.error_showmsg');
		$errorbox.hide();
		if (result.status && result.status != 0) {
			$.messager.alert('提示', result.msg, 'warning');
		} else {
			var view = obj.data().datagrid.dc.view2;
			var bodyCell = view.find("div.datagrid-body td[field]");
			bodyCell.contrast();
		}
		if (result.showMsg != '' && result.showMsg != undefined) {
			$errorbox.show().find('.text').text(result.showMsg);
		}

		if (fakecube.grade.pubdata.gradually == 1) {
			if (window.localStorage && typeof(params) != 'undefined' && params.reportId && window.localStorage[
					'gradually']) {
				var graduallystr = window.localStorage.gradually,
					gradually = JSON.parse(graduallystr);
				var temparr = gradually[params.reportId],
					len = temparr.length;
				//隐藏相对应列
				var keyList = [];
				for (var i = 0; i < len; i++) {
					if (temparr[i].tabletitle == this.table.title) {
						var tempjson = temparr[i].data;
						for (var p in tempjson) {
							var temp = (p.split('.')).join("_");
							if (parseInt(tempjson[p]) > 0) {
								keyList.push(temp);
							}
						}
					}
				}
				var setData = obj.datagrid('getData');
				getConverge(obj, setData.rows, keyList);
			} else {
				//获取全部key
				keyList = [];
				for (var i = 0; i < fakecube.grade.data.length; i++) {
					if (fakecube.grade.data[i].isgroup != 1) {
						var tep = (fakecube.grade.data[i].key.split('.')).join("_");
						keyList.push(tep);
					}
				}
				var setData = obj.datagrid('getData');
				getConverge(obj, setData.rows, keyList);
			}

		}
		//大图显示图片
		$('.imgShow').tooltip({
			position: 'right',
			content: function() {
				var imgurl = $(this).find('img').attr('src');
				var str = "<div><img src=" + imgurl + "  width='200' height='230'/></div>";
				return str;
			}
		});
		//下载赋值
		$('form[title="' + fakecube.title + '"]').find('input[name=downConfig]').val(encodeURIComponent(
			JSON.stringify(fakecube)));
		$('.coloumInfo').show();

		$('.showinfo').tooltip({
			'position': 'top'
		});
		if (fakecube.type == 1) {
			this.dealContrst(result, fakecube);
		} else if (fakecube.type == 2) {
			this.contrstDealContrast(result, fakecube);
		} else if (fakecube.type == 8) {
			this.dealSqlContrst(result, fakecube);
		} else if (fakecube.type == 7) {
			this.dealAggre(result, fakecube);
		}
		if (function() {
				try {
					return allcontent, true;
				} catch (e) {
					return false;
				}
			}()) {
			if (allcontent) {
				$("body").css({
					background: "none"
				});
				var aTag = document.getElementsByTagName("a");
				$.each(aTag, function(index, item) {
					if (item.href.indexOf('?') === -1 && item.href.indexOf('void') === -1) {
						item.href = item.href + "?allcontent=" + allcontent;
					} else if (item.href.indexOf('?') !== -1) {
						item.href = item.href + "&allcontent=" + allcontent;
					}
				});
			}
		}
	},
	contrstDealContrast: function(result, fakecube) {
		$th = this;
		$master = 0; //主副表判断,0为副表
		if (fakecube.master != null && fakecube.master != '' && fakecube.master != undefined) {
			$master = fakecube.master;
		};
		$('.tablelist .configBox:eq(' + $master + ') .datagrid-view2 .datagrid-header tr td:eq(0)').hide();
		$('.tablelist .configBox:eq(' + $master + ') .datagrid-view2 .datagrid-body tr').each(
			function(tr_index) {
				$('.tablelist .configBox:eq(' + $master + ') .datagrid-view2 .datagrid-body tr:eq(' +
					tr_index + ') td:eq(0)').hide();
				$(this).children('td').each(function(td_index) {
					$oldhtml = $(this).children('div').html();
					//$oldhtml=$th.toThousands($oldhtml);
					$width = $(this).width();
					//组装维度
					$pre = '';
					//维度过滤条件
					$filter = new Array();
					if (fakecube.group != '') {
						//group维度
						var group_arr = new Array();
						group_arr = fakecube.group.split(',');
						for (var group_i in group_arr) {
							//console.log($('.tablelist .configBox .datagrid-view2 .datagrid-body tr:eq(' + tr_index + ') td[field=' + group_arr[group_i] + '] div').html());
							if ($('.tablelist .configBox:eq(' + $master + ') .datagrid-view2 .datagrid-body tr:eq(' +
									tr_index + ') td[field=' + group_arr[group_i] + '] div').html() != undefined) {
								$filter[group_arr[group_i]] = $(
									'.tablelist .configBox .datagrid-view2 .datagrid-body tr:eq(' + tr_index +
									') td[field=' + group_arr[group_i] + '] div').html();
							}

						}
					}
					//根据filter获取pre
					for (var filter_i in $filter) {
						$pre += $filter[filter_i];
						$pre += '_';
					}
					//全keyname
					//console.log($pre);
					$keyName = $pre + $('.tablelist .configBox:eq(' + $master +
						') .datagrid-view2 .datagrid-body tr:eq(' + tr_index + ') td:eq(1) div span').text();
					$keyName = $.trim($keyName);
					$key_filter = '['; //组装key_filter
					//console.log($filter);
					//包装$filter
					for (var f_index in $filter) {
						$k_f_o = new Object(); //key_filter_object,临时对象
						$k_f_o.key = f_index;
						$k_f_o.op = '=';
						$k_f_o.val = $filter[f_index];
						$k_f = JSON.stringify($k_f_o); //key_filter
						//$key_filter_arr.f_index($k_f_a2);
						if ($key_filter == '[') {
							$key_filter += $k_f;
						} else {
							$key_filter += ',' + $k_f;
						}
					};
					$key_filter += ']';
					//console.log($key_filter_arr);
					$key_filter = eval('(' + $key_filter + ')');

					//对比报表获取filter用于对比
					if (typeof fakecube.filter == 'string') {
						$key_filter = JSON.parse(fakecube.filter);
					}

					$k_obj = new Object();
					$k_obj.project = fakecube.project;
					$k_obj.group = fakecube.group;
					$k_obj.metric = fakecube.metric;
					$k_obj.udc = fakecube.udc;
					$k_obj.udcconf = fakecube.udcconf;
					$k_obj.sql = fakecube.sql;
					$k_obj.filter = $key_filter;
					$k_obj.search = fakecube.search;
					$k_obj.showthis = $(this).parent("tr").children('td:eq(0)').text();
					//先url后json
					$key = encodeURIComponent(JSON.stringify($k_obj));
					//维度没有添加对比
					if ($k_obj.group.split(',').indexOf($k_obj.showthis) == -1 && $(this).attr('field') !=
						'true_name') {

						// 去掉了最小width   <div class='compros' style='width:"+$width+"px' >
						var oldClass = $(this).find("div").attr("class");
                        var oldStyle = $(this).find("div").attr("style");
						$(this).html("<div class='compros' style='width:" + $width +
							"px' ><span class='data_name " + oldClass + "' style='" + oldStyle + "'>" + $oldhtml + "</span>" +
							"<div class='showbox btn-group btn-group-xs'>" +
							"<button class='ePopup btn btn-default' type='button' url=" + $key + " keyname='" +
							$keyName + "'>添加对比</button>" +
							"<button class='trend btn btn-default' type='button' url=" + $key + " keyname='" +
							$keyName + "'>查看趋势</button></div>" +
							"</div>");
					}
				});
				//再循环一次,处理维度html
				$(this).children('td').each(function(td_index) {

					if (fakecube.group.split(',').indexOf($(this).attr("field")) != -1) {
						//维度要替换html
						$(this).find('div').html(result.rows_show[tr_index][$(this).attr("field")]);

					}
				});

			});

	},

	toThousands: function(num) {
		num+='';
		if (isNaN(num))
			return num;
		var decimal = num.length;
		if (num.indexOf('.') >= 0)
			decimal = num.indexOf('.');
		var result = [],
			counter = 0;
		num = (num || 0).toString().split('');
		for (var i = num.length; i >= decimal; i--) {
			result.unshift(num[i]);
		}
		for (var i = decimal - 1; i >= 0; i--) {
			counter++;
			result.unshift(num[i]);
			if (!(counter % 3) && i != 0) {
				result.unshift(',');
			}
		}

		return result.join('');
	},
	dealAggre: function(result, fakecube) {

		$th = this;
		if (fakecube.master == null || fakecube.master == 0) {
			$master = 0; //主副表判断,0为副表
			if (fakecube.master != null && fakecube.master != '' && fakecube.master != undefined) {
				$master = fakecube.master;
			}

			$k_obj = new Object();
			$k_obj.project = fakecube.project;
			$k_obj.group = fakecube.group;
			$k_obj.metric = fakecube.metric;
			$k_obj.udc = fakecube.udc;
			$k_obj.udcconf = fakecube.udcconf;
			$k_obj.sql = fakecube.sql;
			$k_obj.search = fakecube.search;

			//result.rows.each(function(tr_index){
			$('.tablelist .configBox:eq(' + $master + ') .datagrid-view2 .datagrid-body tr').each(function(
				tr_index) {
				$('.tablelist .configBox:eq(' + $master + ') .datagrid-view2 .datagrid-body tr:eq(' +
					tr_index + ')').children('td').each(function(e) {
					var _this = $(this);
					$oldhtml = _this.children('div').html();
					//$oldhtml = $th.toThousands($oldhtml);
					_this.children('div').html($oldhtml);
				});

				//再循环一次,处理维度html
				$('.tablelist .configBox:eq(' + $master + ') .datagrid-view1 .datagrid-body tr:eq(' +
					tr_index + ')').children('td').each(function(td_index) {

					if (fakecube.group.split(',').indexOf($(this).attr("field")) != -1) {
						//维度要替换html
						$(this).find('div').html(result.rows_show[tr_index][$(this).attr("field")]);
						if ($(this).attr("field") == 'twitter_id') {
							$('.tablelist .configBox:eq(' + $master + ') .datagrid-view2 .datagrid-body tr td').height(
								80);
						}
					}
				});

				//再循环一次,处理维度html
				$('.tablelist .configBox:eq(' + $master + ') .datagrid-view2 .datagrid-body tr:eq(' +
					tr_index + ')').children('td').each(function(td_index) {

					if (fakecube.group.split(',').indexOf($(this).attr("field")) != -1) {
						//维度要替换html
						$(this).find('div').html(result.rows_show[tr_index][$(this).attr("field")]);
						if ($(this).attr("field") == 'twitter_id') {
							$('.tablelist .configBox:eq(' + $master + ') .datagrid-view1 .datagrid-body tr td').height(
								80);
						}
					}
				});


			});
		} else {
			//主副报表会重复fakecube变量,无法优雅的使用onclick事件,单独使用一套逻辑

			$master = 0; //主副表判断,0为副表
			if (fakecube.master != null && fakecube.master != '' && fakecube.master != undefined) {
				$master = fakecube.master;
			}

			$k_obj2 = new Object();
			$k_obj2.project = fakecube.project;
			$k_obj2.group = fakecube.group;
			$k_obj2.metric = fakecube.metric;
			$k_obj2.udc = fakecube.udc;
			$k_obj2.udcconf = fakecube.udcconf;
			$k_obj2.sql = fakecube.sql;
			$k_obj2.search = fakecube.search;

			//result.rows.each(function(tr_index){
			$('.tablelist .configBox:eq(' + $master + ') .datagrid-view2 .datagrid-body tr').each(function(
				tr_index) {
				$('.tablelist .configBox:eq(' + $master + ') .datagrid-view2 .datagrid-body tr:eq(' +
					tr_index + ')').children('td').each(function(e) {
					var _this = $(this);
					$oldhtml = _this.children('div').html();
					//$oldhtml = $th.toThousands($oldhtml);
					_this.children('div').html($oldhtml);
				});

				//再循环一次,处理维度html
				$('.tablelist .configBox:eq(' + $master + ') .datagrid-view1 .datagrid-body tr:eq(' +
					tr_index + ')').children('td').each(function(td_index) {

					if (fakecube.group.split(',').indexOf($(this).attr("field")) != -1) {
						//维度要替换html
						$(this).find('div').html(result.rows_show[tr_index][$(this).attr("field")]);
						if ($(this).attr("field") == 'twitter_id') {
							$('.tablelist .configBox:eq(' + $master + ') .datagrid-view2 .datagrid-body tr td').height(
								80);
						}
					}
				});

				//再循环一次,处理维度html
				$('.tablelist .configBox:eq(' + $master + ') .datagrid-view2 .datagrid-body tr:eq(' +
					tr_index + ')').children('td').each(function(td_index) {

					if (fakecube.group.split(',').indexOf($(this).attr("field")) != -1) {
						//维度要替换html
						$(this).find('div').html(result.rows_show[tr_index][$(this).attr("field")]);
						if ($(this).attr("field") == 'twitter_id') {
							$('.tablelist .configBox:eq(' + $master + ') .datagrid-view1 .datagrid-body tr td').height(
								80);
						}
					}
				});


			});
		}

	},
	dealContrst: function(result, fakecube) {
		$th = this;
		if (fakecube.master == null || fakecube.master == 0) {
			$master = 0; //主副表判断,0为副表
			if (fakecube.master != null && fakecube.master != '' && fakecube.master != undefined) {
				$master = fakecube.master;
			}
			var $k_obj = new Object();
			$k_obj.project = fakecube.project;
			$k_obj.group = fakecube.group;
			$k_obj.metric = fakecube.metric;
			$k_obj.udc = fakecube.udc;
			$k_obj.date_type = fakecube.date_type;
			$k_obj.udcconf = fakecube.udcconf;
			$k_obj.sql = fakecube.sql;
			$k_obj.search = fakecube.search;
			var defaultFilter = this.getGroupSearch(fakecube);
			try{
				var tmp = JSON.parse($k_obj.search);
				var tmp1  = $.merge(tmp,defaultFilter);
				$k_obj.search =null;
				$k_obj.search =  JSON.stringify(tmp1);
			}catch(e){
				console.log('合并');
			}
			//result.rows.each(function(tr_index){
			grad_data = [];
			for (i in fakecube.grade.data) {
                if (fakecube.grade.data[i]['type'] == '维度') {
                    continue;
                }
                if (fakecube.grade.data[i]['hide'] == 1) {
                    continue;
                }
                grad_data.push(fakecube.grade.data[i])
            }
			$('.tablelist .configBox:eq(' + $master + ') .datagrid-view2 .datagrid-body tr').each(function(
				tr_index) {
				$('.tablelist .configBox:eq(' + $master + ') .datagrid-view2 .datagrid-body tr:eq(' +
					tr_index + ')').children('td').each(function(e) {
					var _this = $(this);
					$oldhtml = _this.children('div').html();
                    _this.children('div').html($oldhtml);
					$width = _this.width();
					//组装维度
					$pre = '';
					//维度过滤条件
					$filter = new Array();
					$filter_show = new Array();

					//group维度
					var group_arr = new Array();
					group_arr = fakecube.group.split(',');
					for (var group_i in group_arr) {
						//console.log($('.tablelist .configBox .datagrid-view2 .datagrid-body tr:eq(' + tr_index + ') td[field=' + group_arr[group_i] + '] div').html());
						//if ($('.tablelist .configBox:eq('+$master+') .datagrid-view2 .datagrid-body tr:eq(' + tr_index + ') td[field=' + group_arr[group_i] + '] div').html() != undefined) {
						if (result.rows[tr_index][group_arr[group_i]] != undefined) {
							$filter[group_arr[group_i]] = result.rows[tr_index][group_arr[group_i]];
							$filter_show[group_arr[group_i]] = result.rows_comment[tr_index][group_arr[group_i]];
						}
					}
					//根据filter获取pre
					for (var filter_i in $filter_show) {
						$dim_key_v = $filter_show[filter_i];
						if ($dim_key_v['commentdata'] != undefined) {
							$dim_key_v = $dim_key_v['commentdata'];
						}
						$pre += $dim_key_v;
						//$pre+=$filter_show[filter_i];
						$pre += '_';
					}
					$key_filter = '['; //组装key_filter
					//console.log($filter);
					//包装$filter
					for (var f_index in $filter) {
						$k_f_o = new Object(); //key_filter_object,临时对象
						$k_f_o.key = f_index;
						$k_f_o.op = '=';
						$k_f_o.val = $filter[f_index];
						$k_f = JSON.stringify($k_f_o); //key_filter
						//$key_filter_arr.f_index($k_f_a2);
						if ($key_filter == '[') {
							$key_filter += $k_f;
						} else {
							$key_filter += ',' + $k_f;
						}
					};
					$key_filter += ']';
					$key_filter = eval('(' + $key_filter + ')');
					$k_obj.filter = $key_filter;
					//全keyname
                    $k_obj.showthis = _this.attr("field");
					if (fakecube.grade.pubdata.reshape == 1) {
                        var metricIndex = _this.parent().index();
                        $keyName = $pre + $.trim(grad_data[metricIndex]['name']);
                        $k_obj.showthis = grad_data[metricIndex]['key'].replace(/\./g, "_");
                        if (fakecube.grade.pubdata.reshape_type == 1) {
                            //增加搜过条件
                            $keyName = $pre + $('.tablelist .configBox:eq(' + $master +
                                ') .datagrid-view2 .datagrid-header tr td[field=' + _this.attr('field') + '] div span'
                            ).text();
                            $keyName = $.trim($keyName) + "_" + $.trim(grad_data[metricIndex]['name']);
                            var reshapeOne = [];
                            var reshapeTmp = {};
                            reshapeTmp.key = fakecube.grade.pubdata.reshape_group
                            reshapeTmp.val = _this.attr('field');
                            reshapeTmp.op = '=';
                            reshapeTmp.defaultsearch = _this.attr('field');
                            reshapeOne.push(reshapeTmp);
                            var tmpsearch = JSON.parse(fakecube.search);
                            var newsearch = $.merge(tmpsearch, reshapeOne);
                            $k_obj.filter = newsearch;
                        }
					} else {
                        $keyName = $pre + $('.tablelist .configBox:eq(' + $master +
                                ') .datagrid-view2 .datagrid-header tr td[field=' + _this.attr('field') + '] div span'
                            ).text();
                        $keyName = $.trim($keyName);
					}
					if (_this.find('.data_name').length == 0) {
						$key = encodeURIComponent(JSON.stringify($k_obj));

						//维度没有添加对比
						if ($k_obj.group.split(',').indexOf($k_obj.showthis) == -1) {
							// 去掉了最小width   <div class='compros' style='width:"+$width+"px' >
							var oldStyle = $(_this).find("div").attr("style");
							var oldClass = $(_this).find("div").attr("class");
							_this.html("<div class='compros'><span class='data_name " + oldClass + "' style='" + oldStyle + "'>" + $oldhtml +
								"</span>" +
								"<div class='showbox btn-group btn-group-xs' style='display:none,position:absolute'>" +
								"<button class='ePopup btn btn-default' type='button' url=" + $key + " keyname='" +
								$keyName + "'>添加对比</button>" +
								"<button class='trend btn btn-default' type='button' url=" + $key + " keyname='" +
								$keyName + "'>查看趋势</button></div>" +
								"</div>");
						}
					}
				});
				//再循环一次,处理维度html
				$('.tablelist .configBox:eq(' + $master + ') .datagrid-view1 .datagrid-body tr:eq(' +
					tr_index + ')').children('td').each(function(td_index) {

					if (fakecube.group.split(',').indexOf($(this).attr("field")) != -1) {
						//维度要替换html
						$(this).find('div').html(result.rows_show[tr_index][$(this).attr("field")]);
						if ($(this).attr("field") == 'twitter_id') {
							$('.tablelist .configBox:eq(' + $master + ') .datagrid-view2 .datagrid-body tr td').height(
								80);
						}
					}
				});
                //再循环一次,处理维度html
                $('.tablelist .configBox:eq(' + $master + ') .datagrid-view2 .datagrid-body tr:eq(' +
                    tr_index + ')').children('td').each(function (td_index) {

                    if (fakecube.group.split(',').indexOf($(this).attr("field")) != -1) {
                        //维度要替换html
                        $(this).find('div').html(result.rows_show[tr_index][$(this).attr("field")]);
                        if ($(this).attr("field") == 'twitter_id') {
                            $('.tablelist .configBox:eq(' + $master + ') .datagrid-view1 .datagrid-body tr td').height(
                                80);
                        }
                    }
                });
			});
		} else {
			//主副报表会重复fakecube变量,无法优雅的使用onclick事件,单独使用一套逻辑
			$master = 0; //主副表判断,0为副表
			if (fakecube.master != null && fakecube.master != '' && fakecube.master != undefined) {
				$master = fakecube.master;
			}

			$k_obj2 = new Object();
			$k_obj2.project = fakecube.project;
			$k_obj2.group = fakecube.group;
			$k_obj2.metric = fakecube.metric;
			$k_obj2.udc = fakecube.udc;
			$k_obj2.udcconf = fakecube.udcconf;
			$k_obj2.sql = fakecube.sql;
			$k_obj2.search = fakecube.search;

			//result.rows.each(function(tr_index){
			grad_data = [];
			for (i in fakecube.grade.data) {
                if (fakecube.grade.data[i]['type'] == '维度') {
                    continue;
                }
                if (fakecube.grade.data[i]['hide'] == 1) {
                    continue;
                }
                grad_data.push(fakecube.grade.data[i])
            }
			$('.tablelist .configBox:eq(' + $master + ') .datagrid-view2 .datagrid-body tr').each(function(
				tr_index) {
				$('.tablelist .configBox:eq(' + $master + ') .datagrid-view2 .datagrid-body tr:eq(' +
					tr_index + ')').children('td').each(function(e) {
					var _this = $(this);
					$oldhtml = _this.children('div').html();
                    _this.children('div').html($oldhtml);
					$width = _this.width();
					//组装维度
					$pre = '';
					//维度过滤条件
					$filter2 = new Array();
					$filter2_show = new Array()
					//group维度
					var group_arr2 = new Array();
					group_arr2 = fakecube.group.split(',');
					for (var group_i in group_arr2) {
						//console.log($('.tablelist .configBox .datagrid-view2 .datagrid-body tr:eq(' + tr_index + ') td[field=' + group_arr[group_i] + '] div').html());
						//if ($('.tablelist .configBox:eq('+$master+') .datagrid-view2 .datagrid-body tr:eq(' + tr_index + ') td[field=' + group_arr[group_i] + '] div').html() != undefined) {
						if (result.rows[tr_index][group_arr2[group_i]] != undefined) {
							$filter2[group_arr2[group_i]] = result.rows[tr_index][group_arr2[group_i]];
							$filter2_show[group_arr2[group_i]] = result.rows_comment[tr_index][group_arr2[group_i]];
						}
					}

					//根据filter获取pre
					for (var filter_i in $filter2_show) {
						$dim_key_v = $filter2_show[filter_i];
						if ($dim_key_v['commentdata'] != undefined) {
							$dim_key_v = $dim_key_v['commentdata'];
						}
						$pre += $dim_key_v;
						//$pre += $filter2_show[filter_i];
						$pre += '_';
					}
					$key_filter = '['; //组装key_filter
					//console.log($filter2);
					//包装$filter2
					for (var f_index in $filter2) {
						$k_f_o2 = new Object(); //key_filter_object,临时对象
						$k_f_o2.key = f_index;
						$k_f_o2.op = '=';
						$k_f_o2.val = $filter2[f_index];
						$k_f = JSON.stringify($k_f_o2); //key_filter
						//$key_filter_arr.f_index($k_f_a2);
						if ($key_filter == '[') {
							$key_filter += $k_f;
						} else {
							$key_filter += ',' + $k_f;
						}
					};
					$key_filter += ']';
					$key_filter = eval('(' + $key_filter + ')');
					$k_obj2.filter = $key_filter;
					$k_obj2.showthis = _this.attr("field");
                    if (fakecube.grade.pubdata.reshape == 1) {
                    	var metricIndex = _this.parent().index();
                        $keyName = $pre + $.trim(grad_data[metricIndex]['name']);
                        $k_obj2.showthis = grad_data[metricIndex]['key'].replace(/\./g, "_");
                        if (fakecube.grade.pubdata.reshape_type == 1) {
                            //增加搜过条件
                            $keyName = $pre + $('.tablelist .configBox:eq(' + $master +
                                ') .datagrid-view2 .datagrid-header tr td[field=' + _this.attr('field') + '] div span'
                            ).text();
                            $keyName = $.trim($keyName) + "_" + $.trim(grad_data[metricIndex]['name']);
                            var reshapeOne = [];
                            var reshapeTmp = {};
                            reshapeTmp.key = fakecube.grade.pubdata.reshape_group
                            reshapeTmp.val = _this.attr('field');
                            reshapeTmp.op = '=';
                            reshapeTmp.defaultsearch = _this.attr('field');
                            reshapeOne.push(reshapeTmp);
                            var tmpsearch = JSON.parse(fakecube.search);
                            var newsearch = $.merge(tmpsearch, reshapeOne);
                            $k_obj2.filter = newsearch;
                        }
                    } else {
                        //全keyname
                        $keyName = $pre + $('.tablelist .configBox:eq(' + $master +
                                ') .datagrid-view2 .datagrid-header tr td[field=' + _this.attr('field') + '] div span'
                            ).text();
                        $keyName = $.trim($keyName);
					}
					if (_this.find('.data_name').length == 0) {
						$key = encodeURIComponent(JSON.stringify($k_obj2));
						//维度没有添加对比
						if ($k_obj2.group.split(',').indexOf($k_obj2.showthis) == -1) {
							// 去掉了最小width   <div class='compros' style='width:"+$width+"px' >
							var oldClass = $(_this).find("div").attr("class");
                            var oldStyle = $(_this).find("div").attr("style");
							_this.html("<div class='compros'><span class='data_name " + oldClass + "' style='" + oldStyle + "'>" + $oldhtml +
								"</span>" +
								"<div class='showbox btn-group btn-group-xs' style='display:none,position:absolute'>" +
								"<button class='ePopup btn btn-default' type='button' url=" + $key + " keyname='" +
								$keyName + "'>添加对比</button>" +
								"<button class='trend btn btn-default' type='button' url=" + $key + " keyname='" +
								$keyName + "'>查看趋势</button></div>" +
								"</div>");
						}
					}
				});
				//再循环一次,处理维度html
				$('.tablelist .configBox:eq(' + $master + ') .datagrid-view1 .datagrid-body tr:eq(' +
					tr_index + ')').children('td').each(function(td_index) {

					if (fakecube.group.split(',').indexOf($(this).attr("field")) != -1) {
						//维度要替换html
						$(this).find('div').html(result.rows_show[tr_index][$(this).attr("field")]);
						if ($(this).attr("field") == 'twitter_id') {
							$('.tablelist .configBox:eq(' + $master + ') .datagrid-view2 .datagrid-body tr td').height(
								80);
						}
					}
				});
                //再循环一次,处理维度html
                $('.tablelist .configBox:eq(' + $master + ') .datagrid-view2 .datagrid-body tr:eq(' +
                    tr_index + ')').children('td').each(function (td_index) {

                    if (fakecube.group.split(',').indexOf($(this).attr("field")) != -1) {
                        //维度要替换html
                        $(this).find('div').html(result.rows_show[tr_index][$(this).attr("field")]);
                        if ($(this).attr("field") == 'twitter_id') {
                            $('.tablelist .configBox:eq(' + $master + ') .datagrid-view1 .datagrid-body tr td').height(
                                80);
                        }
                    }
                });
			});
		}
	},
    getGroupSearch(fakecube){
    	var filterDefault =[];
    	for( var i=0; i< fakecube.grade.data.length; i++){
    		if( fakecube.grade.data[i].filter  !='-' &&  fakecube.grade.data[i].filter !=''  &&  fakecube.grade.data[i].filter !=null ){
    			filterDefault.push(fakecube.grade.data[i].filter);
    		}
    	}
    	return filterDefault;
    },
	dealSqlContrst: function(result, fakecube) {
		$master = 0; //主副表判断,0为副表
		if (fakecube.master != null && fakecube.master != '' && fakecube.master != undefined) {
			$master = fakecube.master;
		}
		$th = this;
		$('.tablelist .configBox:eq(' + $master + ') .datagrid-view2 .datagrid-body tr').each(
			function(tr_index) {
				$(this).children('td').each(function(td_index) {
					$oldhtml = $(this).children('div').html();
					//$oldhtml=$th.toThousands($oldhtml);
					$width = $(this).width();
					//组装维度
					$pre = '';
					//维度过滤条件
					$filter = new Array();
					$filter_show = new Array();
					$('.tablelist .configBox:eq(' + $master + ') .datagrid-view1 .datagrid-body tr:eq(' +
						tr_index + ') td div').each(function(wd_index) {
						if (wd_index > 1) {
							$filter[$(this).parent('td').attr("field")] = $(this).html();
						}
					});

					if (result.group != '' && result.group != null) {
						fakecube.group = result.group;
					}
					if (fakecube.group != '') {
						//group维度
						var group_arr = new Array();
						group_arr = fakecube.group.split(',');
						for (var group_i in group_arr) {
							//console.log($('.tablelist .configBox .datagrid-view2 .datagrid-body tr:eq(' + tr_index + ') td[field=' + group_arr[group_i] + '] div').html());
                            if (result.rows[tr_index][group_arr[group_i] + '_realdata'] != undefined && result.rows[tr_index][group_arr[group_i]] != undefined) {
                                $filter[group_arr[group_i]] = result.rows[tr_index][group_arr[group_i] + '_realdata'];
                                $filter_show[group_arr[group_i]] = result.rows[tr_index][group_arr[group_i]];
                                break;
                            }
							if ($('.tablelist .configBox:eq(' + $master + ') .datagrid-view2 .datagrid-body tr:eq(' +
									tr_index + ') td[field=' + group_arr[group_i] + '] div').html() !== undefined) {
                                $filter_show[group_arr[group_i]] = $filter[group_arr[group_i]] = $(
									'.tablelist .configBox .datagrid-view2 .datagrid-body tr:eq(' + tr_index +
									') td[field=' + group_arr[group_i] + '] div').html();
								break;
							}
                            if ($('.tablelist .configBox:eq(' + $master + ') .datagrid-view1 .datagrid-body tr:eq(' +
                                    tr_index + ') td[field=' + group_arr[group_i] + '] div').html() !== undefined) {
                                $filter_show[group_arr[group_i]] = $filter[group_arr[group_i]] = $(
                                    '.tablelist .configBox .datagrid-view1 .datagrid-body tr:eq(' + tr_index +
                                    ') td[field=' + group_arr[group_i] + '] div').html();
                                break;
                            }
						}
					}
					//根据filter获取pre
					for (var filter_i in $filter_show) {
						$pre += $filter_show[filter_i];
						$pre += '_';
					}
					//全keyname
					//console.log($pre);
					$keyName = $pre + $('.tablelist .configBox:eq(' + $master +
						') .datagrid-view2 .datagrid-header tr td:eq(' + td_index + ') div span').text();
					$keyName = $.trim($keyName);
					$key_filter = '['; //组装key_filter
					//console.log($filter);
					//包装$filter
					for (var f_index in $filter) {
						$k_f_o = new Object(); //key_filter_object,临时对象
						$k_f_o.key = f_index;
						$k_f_o.op = '=';
						$k_f_o.val = $filter[f_index];
						$k_f = JSON.stringify($k_f_o); //key_filter
						//$key_filter_arr.f_index($k_f_a2);
						if ($key_filter == '[') {
							$key_filter += $k_f;
						} else {
							$key_filter += ',' + $k_f;
						}
					};
					$key_filter += ']';
					//console.log($key_filter_arr);
					$key_filter = eval('(' + $key_filter + ')');
					$k_obj = new Object();
					$k_obj.project = fakecube.project;
					$k_obj.group = fakecube.group;
					$k_obj.metric = fakecube.metric;
					$k_obj.udc = fakecube.udc;
					$k_obj.udcconf = fakecube.udcconf;
					$k_obj.sql = fakecube.sql;
					$k_obj.filter = $key_filter;
					$k_obj.showthis = $(this).attr("field");
					//先url后json

					$key = encodeURIComponent(JSON.stringify($k_obj));
					//console.log($key);
					//console.log($k_obj);
					//维度没有添加对比
					if ($k_obj.group.split(',').indexOf($k_obj.showthis) == -1) {
						// 去掉了最小width   <div class='compros' style='width:"+$width+"px' >
						var oldClass = $(this).find("div").attr("class");
                        var oldStyle = $(this).find("div").attr("style");
						$(this).html("<div class='compros' style='width:" + $width +
							"px' ><span class='data_name " + oldClass + "' style='" + oldStyle + "'>" + $oldhtml + "</span>" +
							"<div class='showbox btn-group btn-group-xs'>" +
							"<button class='ePopup btn btn-default' type='button' url=" + $key + " keyname='" +
							$keyName + "'>添加对比</button>" +
							"<button class='trend btn btn-default' type='button' url=" + $key + " keyname='" +
							$keyName + "'>查看趋势</button></div>" +
							"</div>");
					}
				});

				if (result.rows_show) {
					//再循环一次,处理维度html
					$(this).children('td').each(function(td_index) {
						if (fakecube.group.split(',').indexOf($(this).attr("field")) != -1) {
							//维度要替换html
							$(this).find('div').html(result.rows_show[tr_index][$(this).attr("field")]);
						}
					});
				}
			});

	},

	//指标替换
	formatMetric: function(fakecube, result, header_config) {
		if(8 === fakecube.type) {
            thousandArr = [];
            if (header_config && header_config.length > 0) {
                for (var j = 0; j < header_config.length; j++) {
                    dataKey = header_config[j].key;
                    //不隐藏
                    if (!parseInt(header_config[j].hide)) {
                        if (header_config[j].thousand != undefined && header_config[j].thousand != '' &&
                            header_config[j].thousand != '-') {
                            thousandArr.push(dataKey);
                        }
                    }
                }

                for (var ii in result.rows) {
                    for (var jj in result.rows[ii]) {
                        if (fakecube.group.split(',').indexOf(jj) === -1) {
                            //千位符
                            if (thousandArr.indexOf(jj) < 0) {
                                result.rows[ii][jj] = this.toThousands(result.rows[ii][jj]);
                            }
                        }
                    }
                }
            }
        }

		if (result.rows_show) {
			thousandArr = [];
			if (header_config && header_config.length > 0) {
				for (var j = 0; j < header_config.length; j++) {

					dataKey = header_config[j].key.split(".").join("_");
					//不隐藏

					if (!parseInt(header_config[j].hide)) {
						if (header_config[j].thousand != undefined && header_config[j].thousand != '' &&
							header_config[j].thousand != '-') {
							thousandArr.push(dataKey);
						}
					}
				};
			}
			for (var ii in result.rows) {
				for (var jj in result.rows[ii]) {
					if (fakecube.group.split(',').indexOf(jj) == -1) {
						result.rows[ii][jj] = result.rows_show[ii][jj]
							//千位符
						if (thousandArr.indexOf(jj) < 0) {
							result.rows[ii][jj] = this.toThousands(result.rows[ii][jj]);
						}
					}
				}
			}
		}
		return result;
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
		console.log(flag +'table');
		return flag;

	},*/


	//生成datagrad表头
	getDatagrad: function(obj, data, fakecube, url) {
        var _this = this;
        _this.getSearchVal(data, fakecube);
		if (fakecube.grade.pubdata.reshape == 1) {
			data = getReshapeTableHeaderDate(fakecube, data);
		}
		if (fakecube.type == 10) {
			data = getCrossTableHeaderData(fakecube, data, url);
		}
		var pagesize;
		var columnArr = this.getcolumn(data);
		var title = fakecube.title + '<span style="color:#c0c0c0">(主表)</span>';
		if (fakecube.master != undefined && fakecube.master > 0) {
			title = fakecube.title + '<span style="color:#c0c0c0">(副表)</span>';
		}
		$(obj).closest('.configBox').find('.tabletitle').html(title);
		pagesize = (typeof(fakecube) != 'undefined' && typeof(fakecube.grade.pubdata) != 'undefined' &&
			fakecube.grade.pubdata['pagesize']) ? fakecube.grade.pubdata['pagesize'] : 10;
		var ispagesize = (typeof(fakecube) != 'undefined' && typeof(fakecube.grade.pubdata) !=
			'undefined' && fakecube.grade.pubdata['ispagesize'] != '0') ? true : false;
		//分页设置
		var pageList = [10, 50, 100, parseInt(pagesize)]; // 先排序后去重
		if (window.localStorage && typeof(params) != 'undefined' && params.reportId && window.localStorage[
				'pageSize']) {
			var reportId = params.reportId;
			var pageSize = JSON.parse(window.localStorage['pageSize']);
			if (pageSize[reportId]) {
				pagesize = pageSize[reportId].pagesize;
			}
		}
		pageList = pageList.sort(function(a, b) {
			return a > b ? 1 : -1
		});
		pageList = $.unique(pageList);
		var queryjson = {
			"datas": JSON.stringify(fakecube)
		};
        var reshape_option = {
            url: url,
            rownumbers: false,
            singleSelect: true,
            collapsible: false,
            multiSort: false,
            loadMsg: "数据正在加载。。。",
            autoRowHeight: true,
            //pagination: ispagesize,
            //pageSize: pagesize, //每页显示的记录条数，默认为10
            //pageList: pageList, //可以设置每页记录条数的列表
            method: 'post',
            remoteSort: false,
            frozenColumns: [columnArr.frozenColumns],
            columns: [columnArr.columns],
            //remoteSort:true,
            queryParams: queryjson,
            loadFilter: function (result) {
                return _this.formatMetric(fakecube, result, data);
            },
            // toolbar:[{
            // 		id:'btnadd',
            // 		text:'数据聚合',
            // 		iconCls:'icon-add',
            // 		handler:function(){
            // 			//$('#btnsave').linkbutton('enable');
            // 			var data = obj.datagrid('getData');
            // 			console.log(data);
            // 			//获取中文名称
            // 			//生成下拉框
            // 			//重新加载数据
            // 		}
            // }],
            onLoadSuccess: function (result) {
                _this.loadSuccess(obj, result, fakecube);
                var deviceType = false; // 手机不去掉序号browserRedirect();
                if (result.hide_dates) {
                    for (index in result.hide_dates) {
                        obj.datagrid("hideColumn", result.hide_dates[index]);
                    }

                }
                //手机模式下去掉id
                if (deviceType) {
                    //获取frozen列id宽度
                    $id_this = $('.datagrid-header-row td').eq(0);
                    $id_width = $id_this.width(); //id宽度
                    $frozen_width = $('.datagrid-view1 .datagrid-header').width(); //frozen宽度
                    $('.datagrid-view1').width($frozen_width - $id_width);
                    $('.datagrid-view1 .datagrid-header').width($frozen_width - $id_width);
                    $('.datagrid-view1 .datagrid-body').width($frozen_width - $id_width);
                    $('.datagrid-view1 .datagrid-footer').width($frozen_width - $id_width);
                    //frozen列宽度低了,还要增大活动列
                    $move_width = $('.datagrid-view2 .datagrid-header').width(); //活动列宽度
                    $('.datagrid-view2').width($move_width + $id_width);
                    $('.datagrid-view2 .datagrid-header').width($move_width + $id_width);
                    $('.datagrid-view2 .datagrid-body').width($move_width + $id_width);
                    $('.datagrid-view2 .datagrid-footer').width($move_width + $id_width);

                    //datagrid-view1
                    $id_this.remove();
                    $('.datagrid-td-rownumber').remove();
                }


            }
        };
        var cross_option = {
            url: url,
            rownumbers: false,
            singleSelect: true,
            collapsible: false,
            multiSort: false,
            loadMsg: "数据正在加载。。。",
            autoRowHeight: true,
            //pagination: ispagesize,
            //pageSize: pagesize, //每页显示的记录条数，默认为10
            //pageList: pageList, //可以设置每页记录条数的列表
            method: 'post',
            remoteSort: false,
            frozenColumns: [columnArr.frozenColumns],
            columns: [columnArr.columns],
            //remoteSort:true,
            queryParams: queryjson,
            loadFilter: function (result) {
                return _this.formatMetric(fakecube, result, data);
            },
            // toolbar:[{
            // 		id:'btnadd',
            // 		text:'数据聚合',
            // 		iconCls:'icon-add',
            // 		handler:function(){
            // 			//$('#btnsave').linkbutton('enable');
            // 			var data = obj.datagrid('getData');
            // 			console.log(data);
            // 			//获取中文名称
            // 			//生成下拉框
            // 			//重新加载数据
            // 		}
            // }],
            onLoadSuccess: function (result) {
                _this.loadSuccess(obj, result, fakecube);
                var deviceType = false; // 手机不去掉序号browserRedirect();
                if (result.hide_dates) {
                    for (index in result.hide_dates) {
                        obj.datagrid("hideColumn", result.hide_dates[index]);
                    }

                }
                //手机模式下去掉id
                if (deviceType) {
                    //获取frozen列id宽度
                    $id_this = $('.datagrid-header-row td').eq(0);
                    $id_width = $id_this.width(); //id宽度
                    $frozen_width = $('.datagrid-view1 .datagrid-header').width(); //frozen宽度
                    $('.datagrid-view1').width($frozen_width - $id_width);
                    $('.datagrid-view1 .datagrid-header').width($frozen_width - $id_width);
                    $('.datagrid-view1 .datagrid-body').width($frozen_width - $id_width);
                    $('.datagrid-view1 .datagrid-footer').width($frozen_width - $id_width);
                    //frozen列宽度低了,还要增大活动列
                    $move_width = $('.datagrid-view2 .datagrid-header').width(); //活动列宽度
                    $('.datagrid-view2').width($move_width + $id_width);
                    $('.datagrid-view2 .datagrid-header').width($move_width + $id_width);
                    $('.datagrid-view2 .datagrid-body').width($move_width + $id_width);
                    $('.datagrid-view2 .datagrid-footer').width($move_width + $id_width);

                    //datagrid-view1
                    $id_this.remove();
                    $('.datagrid-td-rownumber').remove();
                }


            }
        };
		var pagesize_option = {
			url: url,
			rownumbers: true,
			singleSelect: true,
			collapsible: false,
			multiSort: false,
			loadMsg: "数据正在加载。。。",
			autoRowHeight: true,
			pagination: ispagesize,
			pageSize: pagesize, //每页显示的记录条数，默认为10
			pageList: pageList, //可以设置每页记录条数的列表
			method: 'post',
			remoteSort: true,
			frozenColumns: [columnArr.frozenColumns],
			columns: [columnArr.columns],
			//remoteSort:true,
			queryParams: queryjson,
			loadFilter: function(result) {
				return _this.formatMetric(fakecube, result, data);
			},
			// toolbar:[{
			// 		id:'btnadd',
			// 		text:'数据聚合',
			// 		iconCls:'icon-add',
			// 		handler:function(){
			// 			//$('#btnsave').linkbutton('enable');
			// 			var data = obj.datagrid('getData');
			// 			console.log(data);
			// 			//获取中文名称
			// 			//生成下拉框
			// 			//重新加载数据
			// 		}
			// }],
			onLoadSuccess: function(result) {
				_this.loadSuccess(obj, result, fakecube);
				var deviceType = false; // 手机不去掉序号browserRedirect();
				//手机模式下去掉id
				if (deviceType) {
					//获取frozen列id宽度
					$id_this = $('.datagrid-header-row td').eq(0);
					$id_width = $id_this.width(); //id宽度
					$frozen_width = $('.datagrid-view1 .datagrid-header').width(); //frozen宽度
					$('.datagrid-view1').width($frozen_width - $id_width);
					$('.datagrid-view1 .datagrid-header').width($frozen_width - $id_width);
					$('.datagrid-view1 .datagrid-body').width($frozen_width - $id_width);
					$('.datagrid-view1 .datagrid-footer').width($frozen_width - $id_width);
					//frozen列宽度低了,还要增大活动列
					$move_width = $('.datagrid-view2 .datagrid-header').width(); //活动列宽度
					$('.datagrid-view2').width($move_width + $id_width);
					$('.datagrid-view2 .datagrid-header').width($move_width + $id_width);
					$('.datagrid-view2 .datagrid-body').width($move_width + $id_width);
					$('.datagrid-view2 .datagrid-footer').width($move_width + $id_width);

					//datagrid-view1
					$id_this.remove();
					$('.datagrid-td-rownumber').remove();
				}


			}
		};

		var iswap = false; //去掉原有的手机判断 window.location.href.indexOf('wap/') >=0 ? true :false;

		//var iswap = !_this.isPC();

		//bufferview 滚动加载
		var buffer_option = {
			view: bufferview,
			url: url,
			rownumbers: !iswap,
			singleSelect: true,
			//autoRowHeight:false,
			pagination: false,
			//pageSize:fakecube.grade.pubdata.pagesize, 瀑布流数据不能设置pagesize
			//nowrap:true,
			//multiSort:false,
			remoteSort: true,
			loadMsg: "数据正在加载。。。",
			method: 'post',
			queryParams: queryjson,
			height: 600,
			frozenColumns: [columnArr.frozenColumns],
			columns: [columnArr.columns],
			loadFilter: function(result) {
				return _this.formatMetric(fakecube, result, data);
			},
			onLoadSuccess: function(result) {
				result = obj.datagrid('getData');
				_this.loadSuccess(obj, result, fakecube);
			}
		};


		//分页设置
		if (ispagesize && !iswap) {
            if (fakecube.grade.pubdata.reshape == 1) {
                obj.datagrid(reshape_option);
            } else if (fakecube.type == 10) {
                obj.datagrid(cross_option);
			} else {
                obj.datagrid(pagesize_option);
            }

			var p = obj.datagrid('getPager');
			$(p).pagination({
				beforePageText: '', //页数文本框前显示的汉字
				afterPageText: '/ {pages} 页',
				displayMsg: '共 {total} 条数据',
				onChangePageSize: function(size) {
					if (window.localStorage && !window.localStorage['pageSize']) {
						window.localStorage.setItem("pageSize", "{}");
					}
					if (window.localStorage && typeof(params) != 'undefined' && params.reportId && window.localStorage[
							'pageSize']) {
						var reportId = params.reportId;
						var pageSize = JSON.parse(window.localStorage['pageSize']);
						if (!pageSize[reportId]) {
							pageSize[reportId] = {};
							pageSize[reportId].pagesize = size;
						} else {
							pageSize[reportId].pagesize = size;
						}
						window.localStorage.setItem("pageSize", JSON.stringify(pageSize));
					}
				}
			});
		} else {
			//如果是主表 第一表不能设置为滚动 默认最多显示100条数据
			if (typeof(fakecube.isaddmeter) != 'undefined' && fakecube.isaddmeter == '1') {
				delete buffer_option['view'];
				delete buffer_option['height'];
				buffer_option['pageSize'] = fakecube.grade.pubdata.pagesize = 100; //默认100条
				buffer_option['queryParams'] = {
					"datas": JSON.stringify(fakecube)
				};

			}
			/*else {
				buffer_option['view'] = bufferview;
				buffer_option['height']='300';
			}*/
            if (fakecube.grade.pubdata.reshape == 1) {
                obj.datagrid(reshape_option);
            } else if (fakecube.type == 10) {
                obj.datagrid(cross_option);
            } else {
                obj.datagrid(buffer_option);
            }
		}

		//下载数据
		if ($('input[name="report_title"]').length > 0) {
			//$('input[name=downConfig]').val(encodeURIComponent(JSON.stringify(params)));
			$('form[title="' + fakecube.title + '"]').find('input[name="downConfig"]').val(
				encodeURIComponent(JSON.stringify(fakecube)));
			$('form[title="' + fakecube.title + '"]').find('input[name="report_title"]').val(fakecube.title);
		}
	},
	//生成列
	getcolumn: function(data) {
		var columnArr = {};
		columnArr.frozenColumns = [];
		columnArr.columns = [];
		columnArr.frozenColumnsIndex = null; //需要固定列的序号
		//获取表头配置
		if (data && data.length > 0) {
			checkNoHideCol(); //移动端检测第一个需要固定列方法
			for (var j = 0; j < data.length; j++) {

				if (data[j].key == 'all') {
					continue;
				}

				dataKey = data[j].key.split(".").join("_");
				//不隐藏
				if (!parseInt(data[j].hide)) {
					var oneHeader = {};
					oneHeader.field = dataKey.toLocaleLowerCase();
					oneHeader.title = data[j].name;
					// 处理宽度
					if (data[j].width != undefined) {
						oneHeader.width = data[j].width;
					} else {
						oneHeader.width = '100';
					}
					//居中 居左 居右？
					if (data[j].align != undefined) {
						oneHeader.align = data[j].align;
					} else {
						oneHeader.align = 'left';
					}
					//处理说明
					if (data[j].explain != undefined && data[j].explain != '') {
						oneHeader.title = data[j].name + '&nbsp;<a data-toggle="tooltip" title="' + data[j].explain +
							'" class="showinfo glyphicon glyphicon-question-sign"></a>';
						//如果名称相同，则去掉名称 提示框
						if (data[j].explain == data[j].name) {
							oneHeader.title = data[j].name;
						}
					}
					//处理函数
					if (data[j].percent == 1) {
						oneHeader.formatter = formatPrice;
					}
					//处理颜色
					if (data[j].styler != undefined) {
						oneHeader.styler = data[j].styler;
					}
					oneHeader.sortable = true;
					if (this.table.grade.pubdata.reshape == 1) {
                        oneHeader.sortable = false;
					}
					//处理聚合
					if (data[j].converge != undefined && data[j].converge != '-' && data[j].converge != '') {
						var tmpname = data[j].name;
						oneHeader.title = tmpname + "<i>&nbsp;[" + data[j].converge.text + "]</i>";
					}
					//处理图片配置
					var imglinkObj = {};
					if (data[j].img_link != '-' && data[j].img_link != '' && data[j].img_link != undefined) {
						imglinkObj.width = data[j].width;
						imglinkObj.title = data[j].name + "<i>[图片]</i>";
						imglinkObj.field = dataKey + "_img";
					}

					var deviceType = browserRedirect();
					if (deviceType) {
						if (j !== columnArr.frozenColumnsIndex) {
							columnArr.columns.push(oneHeader);
						} else {
							columnArr.frozenColumns.push(oneHeader);
						}
						if (!isEmptyObject(imglinkObj)) {
							columnArr.columns.push(imglinkObj);
						}
					} else {
						//处理是否固定
						if (parseInt(data[j].fixed)) {
							columnArr.frozenColumns.push(oneHeader);
							if (!isEmptyObject(imglinkObj)) {
								columnArr.frozenColumns.push(imglinkObj);
							}
						} else {
							columnArr.columns.push(oneHeader);
							if (!isEmptyObject(imglinkObj)) {
								columnArr.columns.push(imglinkObj);
							}
						}
					}
            if(data[j].sum_ratio === 1) {
						var sumRatioColumn = copy(oneHeader);
            sumRatioColumn.field += '_sum_percent';
            sumRatioColumn.title += '占比';
            sumRatioColumn.sortable = false;
            columnArr.columns.push(sumRatioColumn);
          }
				}

			};
		}

		return columnArr;
		//移动端检测第一个需要固定列方法
		function checkNoHideCol() {
			var result = false;
			data.forEach(function(item, index) {
				var hide = item.hide;
				if (hide !== 1) {
					if (columnArr.frozenColumnsIndex === null) {
						columnArr.frozenColumnsIndex = index;
					}
				}
			})
		}
	},
	//表格ajax
	tableAjax: function(table, obj) {
		
		var _this = this;
		$(this.titletag).html(table.title);
		var url = '/visual/getData';
		_this.getDatagrad(obj, table.grade.data, table, url);
	},
	//对比报表ajax
	tableContrast: function(table, obj) {
		var isEditor = arguments[2] ? arguments[2] : 0;
		var title = table.title + '<span style="color:#c0c0c0">(主表)</span>';
		if (table.master != undefined && table.master > 0) {
			title = table.title + '<span style="color:#c0c0c0">(副表)</span>';
		}

		$(obj).closest('.configBox').find('.tabletitle').html(title);
		var url = '/visual/GetContrast';
		var _this = this;
		_this.getSearchVal(table.grade.data, table);
		//$('body').mask('数据正在加载...');
		var allData = {};
		allData.table = table;
		$.post(url, {
			'allData': allData
		}, function(data) {
			$('body').unmask();
			if (data.status == 0) {
				obj.closest('.configBox').find('.error_showmsg').hide();
				if (data.showMsg != '') {
					obj.closest('.configBox').find('.error_showmsg').show().find('.text').text(data.showMsg);
				}
				//var interText = doT.template($("#contrastTable").text());
				var info = {};
				var tempdate = allData.table.date,
					tempyesterday = getBeforeDate('1', tempdate),
					templastweek = getBeforeDate('7', tempdate),
					contrastArr = [];
				var tmpContrast = JSON.parse(JSON.stringify(table.grade.contrast.data));

				for (var i = 0; i < tmpContrast.length; i++) {
					if (tmpContrast[i].isshow) {
						var one = tmpContrast[i];
						switch (tmpContrast[i].key) {
							case 'today':
								one.name = tmpContrast[i].name + "(" + tempdate + ")";
								break;
							case 'yesterday':
								one.name = tmpContrast[i].name + "(" + tempyesterday + ")";
								break;
							case 'lastweek':
								one.name = tmpContrast[i].name + "(" + templastweek + ")";
								break;
							case "yesterday_percent":
								one.styler = cellStyler;
								one.percent = 1;
								break;
							case "lastweek_percent":
								one.styler = cellStyler;
								one.percent = 1;
								break;
						}
						one.width = "13%";
						contrastArr.push(one);
					}
				}
				info.header = contrastArr;
				info.data = data.data;
				convast = table.grade.data;
				//增加名称
				var nametmp = {};
				nametmp.name = '指标名称';
				nametmp.key = 'name';
				nametmp.isshow = 1;
				nametmp.width = "12%";
				info.header.unshift(nametmp);

				var nametmp = {};
				nametmp.name = 'truename';
				nametmp.key = 'true_name';
				nametmp.isshow = 0;
				nametmp.width = "12%";
				info.header.unshift(nametmp);

				//处理多维
				var dimKey = '';
				var metricNum = 0;
				var searchMap = [];
				for (var x = 0; x < convast.length; x++) {

					if (convast[x].search != '' && convast[x].search != null && typeof(convast[x].search) ==
						"object" && typeof(convast[x].search.reportdimensions) != "undefined" && convast[x].search
						.reportdimensions == 1) {
						var one = {};
						one.name = convast[x].name;
						dimKey = one.key = convast[x].key;
						one.isshow = 1;
						one.width = "11%";
						one.align = "center";
						info.header.unshift(one);
					}
					if (!parseInt(convast[x].hide) && convast[x].isgroup != 1) {
						metricNum++;
					}

				}
				var columnArr = _this.getcolumn(info.header);
				var deviceType = browserRedirect();
				if (deviceType) {
					for (var i = 0; i < columnArr.columns.length; i++) {
						columnArr.columns[i].width = 130;
					}
				}
				obj.datagrid({
					url: null,
					beforeload: function() {},
					singleSelect: true,
					collapsible: false,
					multiSort: false,
					autoRowHeight: true,
					pagination: false,
					data: info.data,
					loadMsg: "数据正在加载。。。",
					loading: true,
					//fitColumns:true,
					frozenColumns: [columnArr.frozenColumns],
					columns: [columnArr.columns],
					onLoadSuccess: function(redata) {
						if (dimKey != '') {
							var i = 0;
							var merges = [];
							while (i < redata.total) {
								var one = {};
								one.index = i;
								one.rowspan = metricNum;
								merges.push(one);
								i = i + metricNum;
							}
							for (var i = 0; i < merges.length; i++) {
								obj.datagrid('mergeCells', {
									index: merges[i].index,
									field: dimKey,
									rowspan: merges[i].rowspan
								});
							}
						}
						result = obj.datagrid('getData');
						_this.loadSuccess(obj, result, table);
					}
				});
				//表格宽度变化自适应
				obj.datagrid('resize', {
					width: function() {
						return document.body.clientWidth * 0.9;
					}
				});
				//obj.html(interText(info));
				if ($('input[name="report_title"]').length > 0) {
					//$('input[name=downConfig]').val(encodeURIComponent(JSON.stringify(params)));
					$('form[title="' + table.title + '"]').find('input[name="downConfig"]').val(
						encodeURIComponent(JSON.stringify(table)));
					$('form[title="' + table.title + '"]').find('input[name="report_title"]').val(table.title);
				}

			} else {
				$.messager.alert('提示', data.msg, 'warning');
			}
		}, 'json');
	},

	//自定义表格获取数据
	tableSqlData: function(table, obj) {
		var _this = this;
		$(this.titletag).html(table.title);
		var url = '/visual/getDefineData';
		_this.getDatagrad(obj, table.grade.data, table, url);


	},

	bindEvent: function() {
		var _this = this;
		$(_this.searchtag).off('click', '.btnSearch');
		//表格查询事件
		$(_this.searchtag).on('click', '.btnSearch', function() {
			var apisearch = _this.apifilter();
			var $configBox = $(this).closest('.configBox');
			//移动端点查询，收起筛选
			if ($('.muneIcon').css('display') != 'none') {
				$configBox.find('.filter').slideUp(400);

			}
			// _this.showTable();

			// _this.initcustom(params);

			//console.log('apisearch:'+JSON.stringify(apisearch));
			//查询时添加get参数的方法
			var tempurl = '?';
			if (apisearch.length > 0) {
				for (var p in apisearch) {
					if (apisearch[p]['val'].length > 0) {
						tempurl += apisearch[p]["key"] + '=' + apisearch[p]['val'].join(",") + '&';
					} else {
						tempurl += apisearch[p]["key"] + '=' + apisearch[p]['val'][0] + '&';
					}
				}
			} else {
				tempurl = window.location.pathname + " ";
			}
			var finalUrl = tempurl.substring(0, tempurl.length - 1);

			// 如果有allcontent则进行相关特殊操作
			if (function() {
					try {
						return allcontent, true;
					} catch (e) {
						return false;
					}
				}()) {
				if (allcontent) {
					if (finalUrl.indexOf("?") === -1) {
						finalUrl += "?allcontent=" + allcontent;
					} else {
						finalUrl += "&allcontent=" + allcontent;
					}
				}
			}

			// 2016-12-15 增加从query中获取时间参数调整默认值的功能
			var endDate = GetQueryString("edate");
			var startDate = GetQueryString("date");
			if (endDate || startDate) {
				var start, end;
				if (endDate && startDate) {
					start = startDate;
					end = endDate;
				} else if (endDate && !startDate) {
					start = end = endDate;
				} else if (!endDate && startDate) {
					start = end = startDate;
				}

				$("input[name=startTime]").val(start);
				$("input[name=endTime]").val(end);
			}

			window.history.pushState(null, null, finalUrl);

            _this.showTable();
            _this.initcustom(params);

			// 2016-12-15 增加从query中获取时间参数调整默认值的功能
			// function formatEdate(dateString) {
			// 	var count = 0;
			// 	var index = 0;
			// 	var result = {};
			// 	for (var i = 0; i < 3; i++) {
			// 		if (dateString.indexOf("-", index) != -1) {
			// 			index = dateString.indexOf("-", index) + 1;
			// 			count++;
			// 		}
			// 	}
			// 	if (count > 2) {
			// 		result.startDate = dateString.slice(0, index - 1);
			// 		result.endDate = dateString.slice(index);
			// 	} else {
			// 		result.startDate = result.endDate = dateString;
			// 	}
			// 	return result;
			//
			// }
		});

	},
	//自定义显示列 params 全局变量
	customkey: function(params) {
		var type = this.table.type;
		var $configBox = $('.configBox');
		var $filter = $configBox.find('.filter');
		//只有普通报表
		if (type != '2') {
			this.createCutomkey(params, this.table);
			//this.initcustom(params); //初始化保存的隐藏列
			this.savecustom(params); //保存自定义显示列事件
			this.closespan(); //自定义伸缩事件
			this.savegradually(params);
			//显示颜色渐变
		}
		if ($('.muneIcon').css('display') != 'none') {
			$configBox.each(function(k, v) {
				//如果pc没有筛选，则不显示查询按钮
				if ($(v).find('.filter').children().length <= 0) {
					$(v).find('.web-filter').addClass('filter-hide');
				}
			});
		}
	},
	createCutomkey: function(params, table) {
		if (table.grade.pubdata.reshape == 1) {
			return;
		}

		var tag = '<div class="customkey clearfix">';
		tag += '<ul class="nav nav-tabs my-nav-customkey closed" role="tablist">';
		tag +=
			'<li role="presentation" class="my-nav-customtitle closed"><span class="web-filter" style="position: absolute;text-align: center;top: -1px;padding-top:4px;padding-bottom:2px;right: 90px;width: 55px;background:#F9F9F9;color: #999;border: 1px solid #ddd;">查询</span>';
		tag += '<a href="javascript:void(0)" class="customtitle" style="margin: 0;">自定义显示</a>';
		if (table.grade.pubdata.gradually) {
			tag +=
				'<a href="javasript:void(0)" class="gradually closed"><span class="glyphicon glyphicon-align-left" style="color:#82B7EA;padding-right:3px"></span>数据热力显示</a>';
		}
		tag += '</li>';
		tag += '</ul>';

		tag += '<div class="row my-tab-customkey">';
		var gradually = '<div class="row my-tab-gradually">';
		var colums = table.grade.data,
			len = colums.length,
			tempgrade = {},
			temgradually = {};
		for (var i = 0; i < len; i++) {
			//生成自定列表
			if (colums[i].hide != '1' && colums[i].key != 'all') {
				tag += '<div class="col-sm-2"><label class="my-customlist"><input name="' + colums[i].key +
					'" type="checkbox" checked="checked" title="' + colums[i].name + '" text="' + colums[i].key +
					'" >' + colums[i].name + '</label></div>';
				tempgrade[colums[i].key] = 1;
			}
			if (colums[i].hide != '1' && colums[i].isgroup != 1) {
				gradually += '<div class="col-sm-2"><label class="my-customlist">';
				gradually += '<input name="' + colums[i].key + '" type="checkbox" checked="checked" title="' +
					colums[i].name + '" text="' + colums[i].key + '" >';
				gradually += colums[i].name + '</label></div>';
				temgradually[colums[i].key] = 1;
			}
		}
		gradually +=
			'<div class="col-sm-12"><a class="customkey-check all-select" style="margin-right: 10px;">全选</a>     <a class="customkey-check all-unselect" style="margin-right: 10px;">全不选</a>     <input type="button" value="确定" class="btn btn-default btnsavegradually" table="' +
			table.title + '" /></div>';
		gradually += '</div>';

		tag +=
			'<div class="col-sm-12"><a class="customkey-check all-select" style="margin-right: 10px;">全选</a>     <a class="customkey-check all-unselect" style="margin-right: 10px;">全不选</a>     <input type="button" value="确定" class="btn btn-default btnsavecustomkey" table="' +
			table.title + '" /></div>';
		tag += '</div>';
		tag += gradually;

		tag += '</div>';

		$(this.boxtag).find('.tabletitle').after(tag);

		$('.customkey-check.all-select').click(function() {
			$(this).parents('.row').find('input[type=checkbox]').prop("checked", true);
		});
		$('.customkey-check.all-unselect').click(function() {
			$(this).parents('.row').find('input[type=checkbox]').prop("checked", false);
		});

		this.initcustom(params, tempgrade); //初始化保存的隐藏列
		this.initgradually(params, tempgrade); //初使化颜色值



	},
	initgradually: function(params) {
		var $boxtag = $(this.boxtag);
		//初始化 customkey = {"报表id":[{"tabletitle":"报表名称","data":{"key1":"1","key2":"1"}}]}
		if (window.localStorage && typeof(params) != 'undefined' && params.reportId && window.localStorage[
				'gradually']) {
			var graduallystr = window.localStorage.gradually,
				gradually = JSON.parse(graduallystr);
			if (gradually[params.reportId]) {
				//兼容之前保存的数据
				var tablejson = {};
				//判断是否为数组Object.prototype.toString.call(value) == '[object Array]'

				if (Object.prototype.toString.call(gradually[params.reportId]) != '[object Array]') {
					tablejson['tabletitle'] = this.table.title;
					tablejson['data'] = gradually[params.reportId];
					gradually[params.reportId] = [tablejson];
					window.localStorage.setItem("gradually", JSON.stringify(gradually));
				}

				var temparr = gradually[params.reportId],
					len = temparr.length,
					tempcount = 0,
					deletecount = 0,
					tempgrade = arguments[1],
					$tablebox = $boxtag.find('.tablecontent');

				//隐藏相对应列
				var keyList = [];
				for (var i = 0; i < len; i++) {
					if (temparr[i].tabletitle == this.table.title) {
						var tempjson = temparr[i].data;
						for (var p in tempjson) {
							//数据处理 保存本地数据的key值 和 表头显示的key值 一致性 不一致的删除
							if (tempgrade && !tempgrade[p]) {
								delete gradually[params.reportId][i].data[p];
								deletecount++;
								continue;
							}
							//处理 . 改成下划线_
							var temp = (p.split('.')).join("_");
							if (tempjson[p] == '0') {
								$tablebox.closest('.configBox').find('.my-tab-gradually').find('.my-customlist').find(
									'input[name="' + p + '"]').prop('checked', false);
							} else {
								$tablebox.datagrid('showColumn', temp);
							}
						}

					}

				}
			}
		}
	},
	initcustom: function(params) {
        if (this.table.grade.pubdata.reshape == 1) {
            return;
        }
		var $boxtag = $(this.boxtag);
		var $id = $boxtag.find('.customkey');
		//初始化 customkey = {"报表id":[{"tabletitle":"报表名称","data":{"key1":"1","key2":"1"}}]}
		if (window.localStorage && typeof(params) != 'undefined' && params.reportId && window.localStorage[
				'customkey']) {
			var customkeystr = window.localStorage.customkey,
				customkey = JSON.parse(customkeystr);

			if (customkey[params.reportId]) {
				//兼容之前保存的数据
				var tablejson = {};
				//判断是否为数组Object.prototype.toString.call(value) == '[object Array]'

				if (Object.prototype.toString.call(customkey[params.reportId]) != '[object Array]') {
					tablejson['tabletitle'] = this.table.title;
					tablejson['data'] = customkey[params.reportId];
					customkey[params.reportId] = [tablejson];
					window.localStorage.setItem("customkey", JSON.stringify(customkey));
				}

				var temparr = customkey[params.reportId],
					len = temparr.length,
					tempcount = 0,
					deletecount = 0,
					tempgrade = arguments[1],
					$tablebox = $boxtag.find('.tablecontent');

				//隐藏相对应列
				for (var i = 0; i < len; i++) {
					if (temparr[i].tabletitle == this.table.title) {
						var tempjson = temparr[i].data;
						for (var p in tempjson) {
							//数据处理 保存本地数据的key值 和 表头显示的key值 一致性 不一致的删除
							if (tempgrade && !tempgrade[p]) {
								delete customkey[params.reportId][i].data[p];
								deletecount++;
								continue;
							}
							//处理 . 改成下划线_
							var temp = (p.split('.')).join("_");
							if (tempjson[p] == '0') {
								tempcount++;
								$tablebox.datagrid('hideColumn', temp);
								$tablebox.closest('.configBox').find('.my-tab-customkey').find('.my-customlist').find(
									'input[name="' + p + '"]').prop('checked', false);
							} else {
								$tablebox.datagrid('showColumn', temp);
							}


						}

					}

				}
				//移动端自定义按钮状态展开，加蓝色表示选中
				if ($('.muneIcon').css('display') != 'none' && tempcount > 0) {
					$id.find(".customtitle").addClass('webBtn-select');
				}

				if (tempcount > 0) {
					var deviceType = browserRedirect();
					var len = params.tablelist.length;
					if (deviceType) {

					} else {
						$id.find('.my-tab-customkey').slideDown(400);
						$id.find('.my-nav-customtitle').removeClass('closed').parent('.my-nav-customkey').removeClass(
							'closed');
					}

				}
				//是否被删除过 删除过的数据重新要被保存到本地
				if (deletecount > 0) {
					window.localStorage.setItem("customkey", JSON.stringify(customkey));
				}


			}
		}
	},
	savegradually: function(params) {
		//var $boxtag = $(this.boxtag);
		$('body').off('click', '.btnsavegradually');
		//绑定事件 customkey = {"报表id":[{"tabletitle":"报表名称","data":{"key1":"1","key2":"1"}}]}
		$('body').on('click', '.btnsavegradually', function() {
			var $listTag = $(this).closest('.my-tab-gradually').find('.my-customlist'),
				len = $listTag.length;
			var gradually = {},
				tempjson = {},
				temptable = {},
				tablearr = [];
			title = $(this).attr('table');
			var $boxtag = $(this).closest('div.configBox').find('.tablecontent');

			$($listTag).each(function(i) {
				var $input = $(this).find('input[type="checkbox"]'),
					key = $input.attr('text');
				key = key.toLowerCase();
				if ($input.is(':checked')) {
					tempjson[key] = "1";
				} else {
					tempjson[key] = "0";
				}

			});

			if (params.reportId) {

				temptable['tabletitle'] = title;
				temptable['data'] = tempjson;

				if (window.localStorage && window.localStorage['gradually']) {
					var graduallystr = window.localStorage['gradually'];
					gradually = JSON.parse(graduallystr);
					if (gradually[params.reportId]) {
						var tempcounts = 0;
						for (var p1 in gradually[params.reportId]) {
							if (gradually[params.reportId][p1].tabletitle == title) {
								gradually[params.reportId][p1]['data'] = tempjson;
								tempcounts++;
							}
						}
						//报表新增其他自定义列保存
						if (tempcounts == 0) {
							gradually[params.reportId].push(temptable);
						}

					} else {
						tablearr.push(temptable);
						gradually[params.reportId] = tablearr;
					}


				} else {
					tablearr.push(temptable);
					gradually[params.reportId] = tablearr;
				}
				window.localStorage.setItem("gradually", JSON.stringify(gradually));
			}
			var keyList = [];
			for (var p in tempjson) {
				//处理 . 改成下划线_
				var temp = (p.split('.')).join("_");
				temp = temp.toLowerCase();
				if (tempjson[p] == '1') {
					keyList.push(temp);
				}
			}
			var setData = $boxtag.datagrid('getData');
			getConverge($boxtag, setData.rows, keyList);

		});
	},
	savecustom: function(params) {
		var that = this;
		//var $boxtag = $(this.boxtag);
		$('body').off('click', '.btnsavecustomkey');
		//绑定事件 customkey = {"报表id":[{"tabletitle":"报表名称","data":{"key1":"1","key2":"1"}}]}
		$('body').on('click', '.btnsavecustomkey', function() {
			var $listTag = $(this).closest('.my-tab-customkey').find('.my-customlist'),
				len = $listTag.length;
			var customkey = {},
				tempjson = {},
				temptable = {},
				tablearr = [];
			title = $(this).attr('table');
			var $boxtag = $(this).closest('div.configBox').find('.tablecontent');

			$($listTag).each(function(i) {
				var $input = $(this).find('input[type="checkbox"]'),
					key = $input.attr('text');
				key = key.toLowerCase();
				if ($input.is(':checked')) {
					tempjson[key] = "1";
				} else {
					tempjson[key] = "0";
				}

			});

			if (params.reportId) {

				temptable['tabletitle'] = title;
				temptable['data'] = tempjson;

				if (window.localStorage && window.localStorage['customkey']) {
					var customkeystr = window.localStorage['customkey'];
					customkey = JSON.parse(customkeystr);
					if (customkey[params.reportId]) {
						var tempcounts = 0;
						for (var p1 in customkey[params.reportId]) {
							if (customkey[params.reportId][p1].tabletitle == title) {
								customkey[params.reportId][p1]['data'] = tempjson;
								tempcounts++;
							}
						}
						//报表新增其他自定义列保存
						if (tempcounts == 0) {
							customkey[params.reportId].push(temptable);
						}

					} else {
						tablearr.push(temptable);
						customkey[params.reportId] = tablearr;
					}


				} else {
					tablearr.push(temptable);
					customkey[params.reportId] = tablearr;
				}
				window.localStorage.setItem("customkey", JSON.stringify(customkey));
			}

			//隐藏相对应列
			for (var p in tempjson) {
				//处理 . 改成下划线_
				var temp = (p.split('.')).join("_");
				temp = temp.toLowerCase();
				if (tempjson[p] == '1') {
					$boxtag.datagrid('showColumn', temp);
				} else {
					$boxtag.datagrid('hideColumn', temp);
				}
			}
			$boxtag.datagrid('reload');
			var deviceType = browserRedirect();
			if (deviceType) {
				// location.reload();
				var len = params.tablelist.length;
				if (len === 1) {
					that.pinHeader({
						type: "1",
						top: 40
					});
				}
			} else {
				if (len === 1) {
					that.pinHeader({
						type: "2",
						top: 35
					});
				}
			}
		});
	},
	// 2016/9/27 下午8:59:33 增加移动端表头顶端固定方法
	pinHeader: function(params) {
		$(".datagrid-htable").pin({
			padding: {
				top: params.top,
				bottom: 10
			}
		});
		$($('#sidebar').find('ul')[0]).css({
			backgroundColor: "#f8fcff"
		});
		$('.datagrid-body').on("scroll", function() {
			if (params.type == 1) {
				var width = $($('.datagrid-btable')[0]).css("width");
				var scrollleft = $(this).scrollLeft();
				$('.datagrid-htable').css({
					left: $(this).offset().left - $(this).scrollLeft()
				});
				$($('.datagrid-htable')[0]).css({
					left: "0",
					zIndex: "999"
				});
				$($('.datagrid-btable')[0]).css({
					left: "0",
					zIndex: "999"
				});
			} else {
				$($('.datagrid-htable')[1]).css({
					left: $(this).offset().left - $(this).scrollLeft(),
					overflow: "hidden"
				});
				$($('.datagrid-htable')[0]).css({
					left: "100",
					zIndex: "999"
				});
				$($('.datagrid-btable')[0]).css({
					left: "0",
					zIndex: "999"
				});
				if ($($('#sidebar').find('ul')[0]).height() != $('body').height()) {
					$($('#sidebar').find('ul')[0]).css({
						height: $('body').height()
					});
				}
			}

		});

	},
	closespan: function() {
		//点击收缩
		$('body').off('click', '.my-nav-customtitle a');
		$('body').on('click', '.my-nav-customtitle a', function(e) {
			event.preventDefault();
			var className = $(this).hasClass('customtitle');
			var $configBox = $(this).closest('.configBox');
			var $filter = $configBox.find('.filter');

			if ($('.muneIcon').css('display') != 'none') {
				//查询与自定义互斥
				if ($filter.css('display') != 'none') {
					$filter.css('display', 'none');

				}
				$configBox.find('.my-tab-customkey').slideToggle(400);
				//按钮设置颜色
				$(this).toggleClass('webBtn-select');
				$configBox.find('.web-filter').removeClass('webBtn-select');
			} else {
				if (className) {
					$(this).closest('.customkey').find('.my-tab-customkey').slideToggle(400);
					$(this).toggleClass('closed').parent('.my-nav-customkey').toggleClass('closed');
					$(this).closest('.customkey').find('.my-tab-gradually').hide();
				} else {
					$(this).closest('.customkey').find('.my-tab-gradually').slideToggle(400);
					$(this).closest('.customkey').find('.my-tab-customkey').hide();
					$(this).toggleClass('closed').parent('.my-nav-customkey').toggleClass('closed');
				}
			}

		});
	}

};

function getCrossTableHeaderData(fakecube, data, url) {
	newData = [];
    $.ajax({
        url : url,
        data:{
            datas:JSON.stringify(fakecube)
        },
        cache : false,
        async : false,
        type : "POST",
        dataType : 'json',
        success : function (result){
            newData = result['tableHeader'];
        }
    });
    return newData;
}

function getReshapeTableHeaderDate(fakecube, data) {
	var newData = [];
    var headerCloumn = {};
    var headerColumnTemple = {
    	'explain' : '',
		'expression' : null,
		'filter' : '-',
		'fixed' : 0,
		'hide' : 0,
		'img_link' : '-',
		'isgroup' : 1,
		'issearch' : '-',
		'key' : '',
		'name' : '',
		'otherlink' : '',
		'percent' : '-',
		'search' : '-',
		'sort' : '-',
		'sum_ratio' : '-',
		'thousand' : '-',
		'type' : '指标',
		'width' : 120

	};
	var tmpNewHeader  =[]
	for (headerCloumnIndex in data) {
        headerCloumn = data[headerCloumnIndex];
		if (headerCloumn.type != '维度' || headerCloumn.key == 'date') {
			continue;
		}
        headerCloumn.hide = 1;
        newData.push(headerCloumn);
        if ( headerCloumn.issearch instanceof Object ) {
        	if( fakecube.grade.pubdata.reshape_type ==1){
        		if (headerCloumn.search.defaultsearch ==''){
        			tmpNewHeader.push(headerCloumn);
        		}
        	}else{
        		tmpNewHeader.push(headerCloumn);
        	}
        }
	}
	//获取指标列最大的宽度
	var maxWidth = 200;
	for (columnIndex in fakecube.grade.data) {
		if (fakecube.grade.data[columnIndex]['hide'] == 1) {
			continue;
		}
		if (fakecube.grade.data[columnIndex]['width'] <= maxWidth) {
			continue;
		}
        maxWidth = fakecube.grade.data[columnIndex]['width'];
	}
	if(fakecube.grade.pubdata.reshape_type == 1){
		if(tmpNewHeader.length >1){
			var metricNameHeader = {};
		    $.extend(metricNameHeader, headerColumnTemple);
		    metricNameHeader.key = 'metric_name';
		    metricNameHeader.type = '行列转换维度设置错误';
		    metricNameHeader.fixed = 1;
		    metricNameHeader.width = maxWidth;
		    newData.unshift(metricNameHeader);
		}else if(tmpNewHeader.length > 0 ){
			newData =[];
			var metricNameHeader = {};
		    $.extend(metricNameHeader, headerColumnTemple);
		    metricNameHeader.key = 'metric_name';
		    metricNameHeader.name = '指标名称';
		    metricNameHeader.type = '维度';
		    metricNameHeader.fixed = 1;
		    metricNameHeader.width = maxWidth;
		    newData.push(metricNameHeader);
			var rowlist =   tmpNewHeader[0].search.val.split('\n');
			for(var i=0; i< rowlist.length; i++){
				onelist =  rowlist[i].split(":");
				var timeHeader = {};
		        $.extend(timeHeader, headerColumnTemple);
		        timeHeader.name = onelist[1];
		        timeHeader.key = onelist[0];
		        newData.push(timeHeader);
			}
		}
		 
	}else{
		var metricNameHeader = {};
	    $.extend(metricNameHeader, headerColumnTemple);
	    metricNameHeader.key = 'metric_name';
	    metricNameHeader.type = '维度';
	    metricNameHeader.fixed = 1;
	    metricNameHeader.width = maxWidth;
	    newData.unshift(metricNameHeader);
	    var timeRange = getTimeRange(fakecube.date, fakecube.edate);
	    for (dateIndex in timeRange) {
	    	var timeHeader = {};
	        $.extend(timeHeader, headerColumnTemple);
	        timeHeader.name = timeRange[dateIndex];
	        timeHeader.key = timeRange[dateIndex];
	        newData.push(timeHeader);
		}
	}
	return newData;
}

function getTimeRange(start_time, end_time) {
	var dateType = start_time.split("-").length;
	switch (dateType) {
		case 3:
        	var bd = new Date(start_time), be = new Date(end_time);
            var bd_time = bd.getTime(), be_time = be.getTime(), time_diff = bd_time - be_time;
            var d_arr = [];
            for (var i = 0; i >= time_diff; i -= 86400000) {
                var ds = new Date(be_time + i);
                d_arr.push(ds.getFullYear() + '-' + pad((ds.getMonth() + 1), 2) + '-' + pad(ds.getDate(), 2))
            }
            return d_arr;
            break;
		case 2:
			var d_arr = [];
			timeDiff = getMonthsDiff(start_time, end_time);
			var currentDate = end_time;
            d_arr.push(end_time);
			for (var i = 0; i < timeDiff; i ++) {
				year = getYearByDate(currentDate);
				month = getMonthByDate(currentDate);
				if (month == 1) {
					year = year - 1;
					month = 12;
				} else {
					month = month - 1;
				}
				currentDate = year + '-' + pad(month, 2);
                d_arr.push(currentDate);
			}
            d_arr.push(start_time);
            return d_arr;
            break;
    }
}

function getMonthByDate(date) {
	return parseInt(date.split("-")[1]);
}

function getYearByDate(date) {
    return parseInt(date.split("-")[0]);
}

function getMonthsDiff(date1 , date2){
    //用-分成数组
    date1 = date1.split("-");
    date2 = date2.split("-");
    //获取年,月数
    var year1 = parseInt(date1[0]) ,
        month1 = parseInt(date1[1]) ,
        year2 = parseInt(date2[0]) ,
        month2 = parseInt(date2[1]) ,
        //通过年,月差计算月份差
        months = (year2 - year1) * 12 + (month2-month1) - 1;
    return months;
}

function pad(num, n) {
    var len = num.toString().length;
    while(len < n) {
        num = "0" + num;
        len++;
    }
    return num;
}


function copy(obj){
	var newobj = {};
	for ( var attr in obj) {
		newobj[attr] = obj[attr];
	}
	return newobj;
}
