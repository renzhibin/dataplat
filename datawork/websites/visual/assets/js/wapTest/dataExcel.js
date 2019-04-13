/**
 * 根据链接类型，返回类型和相应参数（json格式）
 * new dataTable({
 *  id:xxx, 对象id
 *  data:{}, 数据源

 * });
 *  header:
 * */

var dataExcel = function(option) {
  var defaults = {
    header: [{
      key: "type",
      name: "类型",
      width: "4%"
    }, {
      key: "name",
      name: "列显示名称",
      width: "10%"
    }, {
      key: "key",
      name: "列key",
      width: "8%"
    }, {
      key: "explain",
      name: "列说明",
      width: "13%"
    }, {
      key: "filter",
      name: "数据过滤",
      width: "10%"
    }, {
      key: "expression",
      name: "计算值",
      width: "12%"
    }, {
      key: "percent",
      name: "百分比",
      width: "5%"
    }, {
      key: "thousand",
      name: "千分位(隐藏)",
      width: "5%"
    }, {
      key: "issearch",
      name: "搜索",
      width: "10%"
    }, {
      key: "search",
      name: "即时过滤",
      width: "6%"
    }, {
      key: "otherlink",
      name: "外链",
      width: "6%"
    }, {
      key: "img_link",
      name: "图片显示",
      width: "6%"
    }, {
      key: "fixed",
      "name": "是否固定",
      width: "7%"
    }, {
      key: "sort",
      name: "默认排序",
      width: "4%"
    }, {
      key: "hide",
      name: "隐藏选择",
      width: "6%"
    }, {
      key: "converge",
      name: "聚合",
      width: "6%"
    }, {
      key: "width",
      name: "宽度<br/>(像素)",
      width: '6%'
    }, {
      key: "name_hide",
      name: "图例名称<br/>(隐藏选择)",
      width: '6%'
    }, {
      key: "xaxis",
      name: "自定义x轴",
      width: '6%'
    }],
    data: [{
      isgroup: 1, //   是否维度 指标2 udc3 自定义表格指标4
      type: "纬度", //   类型
      name: "时间", //   列显示名称
      key: "date",
      　 //   key值 复制功能
      explain: "显示时间", //   列说明
      filter: {
        op: "=",
        val: ["输入文字"] // 为 -  直接返回-
      }, //   数据过滤 默认为 "null"
      expression: "a/c", //   udc 计算 默认为 "null"
      percent: 1, //	 是否百分比 默认为 "null"  0 或 1
      thousand: '-',
      issearch: {
        is_accurate: 0,
        is_check: 1
      }, //	 是否搜索 默认为 "null"
      search: {
        key: 'date',
        is_check: 1,
        val: "团购:团购海",
        isshow: 1,
        reportgroup: 0,
        reportdimensions: 0
      }, //即时过滤 默认为 "null" isgroup 是否设置为搜索  isdimensions是否多维
      otherlink: null, //   外链
      fixed: 0, //   0/1是否固定
      sort: 'filter_not', //   默认为filter_not   asc 升序  desc降序
      hide: 0, //   0/1表格是否隐藏
      otherlink: null, //	 是否外链 默认为null
      img_link: '-', //   是否配图 默认为null - 为设置
      converge: null, //   聚合参数 {key:max ,name:'最大值'} 'max','min','sum','avg','count'
      width: 100, //	 列宽默认宽度
      name_hide: 0, //   0 或 1 名称隐藏(只针对图表）
      xaxis: []
    }],
    //   0/1 公共数据属性   null  表示没有相对比占比 例如 用户相对占比
    pubdata: {
      'isproportion': 0,
      'ispagesize': '1',
      'pagesize': '10',
      'not_tips': 1,
      'gradually': 0,
      // 是否显示排序内容
      'isShowSort': 0
    },
    contrast: { //   对比报表显示 没有就显示"null"
      header: ["显示", "中文名称", "绑定的key"],
      data: [{
          key: "today",
          name: "当日值",
          isshow: 1
        }, {
          key: "yesterday",
          name: "前一日值",
          isshow: 1
        }, {
          key: "yesterday_percent",
          name: "当日相比前一日变化率",
          isshow: 1
        }, // isshow 0/1
        {
          key: "lastweek",
          name: "上周同日值",
          isshow: 1
        }, {
          key: "lastweek_percent",
          name: "当日相比上周同日变化率",
          isshow: 1
        }, {
          key: "avgweek",
          name: "周平均值",
          isshow: 1
        }
      ]
    },
    contrast: null
  }
  this.option = option;
  if (isEmptyObject(option.data)) {
    this.option.data = defaults;
  } else {
    this.option.data = $.extend(defaults, this.option);
  }
  this.filterDataAjax = [];
  this.init();
}


dataExcel.prototype = {
  init: function() {

    //初使化模板
    //设置数据
    //增加事件
    var _this = this;
    _this.getFilterData();
    _this.createtable();
    _this.addColumn();
    _this.columndel();
    _this.is_search();
    _this.search();

    //隐藏
    _this.hide();
    _this.is_hide();
    var source = $("#" + this.option.id);
    var dragtrObj = source.find('#dragtr');

    this.drag(dragtrObj);
    this.search_plus();
    this.sort_plus();
    this.is_pagesize();

    //过滤条件配置
    $('#reportsearch').show().dialog({
      title: '配置即时过滤信息',
      width: 450,
      //height:'',
      closed: true,
      cache: false,
      modal: true,
      buttons: [{
        text: '确定',
        handler: function() {
          search = {};
          var $reportsearch = $('#reportsearch');
          search.key = $reportsearch.find('.reportkey').text();
          if ($('#reportsearch').find('.reportcheck').is(":checked")) {
            search.is_check = 1;
          } else {
            search.is_check = 0;
          }
          //判断对比是否设置为输入框
          if ($reportsearch.find('.reportgroup').is(":checked")) {
            search.reportgroup = 1;
          } else {
            search.reportgroup = 0;
          }
          //判断是否设置了多维
          if ($reportsearch.find('.reportdimensions').is(":checked")) {
            search.reportdimensions = 1;
          } else {
            search.reportdimensions = 0;
          }
          str = $reportsearch.find('.reportsource').val();
          if (!$.trim(str) && search.reportdimensions == 1) {
            $.messager.alert('info', '过滤条件数据不能为空', 'info');
            return;
          }
          search.val = str;

          // 获取及时过滤条件
          /*var filterSelect = $reportsearch.find("select.jsgltj")||"";
          var  filterSelectOptions=$reportsearch.find("select.jsgltj option")||"";
          var filterMapkey="-";

          var filterMapName = $reportsearch.find("div.jsgltj").find("a").find(".select2-chosen").text();
          filterSelect.on('change', function () {
              filterMapName = filterSelect.val();
          });

          if(filterSelectOptions && filterSelectOptions.length>0){
              for(var i=0;i<filterSelectOptions.length;i++){
                  if($(filterSelectOptions[i]).text()==filterMapName){
                      filterMapkey=$(filterSelectOptions[i]).val();
                  }
              }
          }*/

          var selectMapKey = $reportsearch.find("select.jsgltj option:selected").val() ||
            "-";
          var filterSelect = $reportsearch.find("select.jsgltj") || "";

          filterSelect.on('change', function() {
            selectMapKey = filterSelect.val();
          });
          search.mapkey = selectMapKey;

          //获取搜索默认值
          search.defaultsearch = $reportsearch.find('.defaultsearch').val();
          //对比报表也需要搜索过滤
          var id = $reportsearch.find('.target_obj').val();
          // if(type == 2){
          //   filter.reportcheck =0;
          // }
          $reportgrade = $("#" + id);
          //确定时 填写的数值可以保存到相应的属性里。
          $reportgrade.find('.gradebox').find('tr').each(function() {
            var key = $(this).attr('data-key');
            if (key == undefined) {
              key = $(this).find('.key_report').find('textarea').val();
            }
            if ($.trim(search.key) == $.trim(key)) {
              $(this).find('.reportsearch').attr('data-config', JSON.stringify(
                search));

            }
          });
          $reportsearch.dialog('close');
        }
      }, {
        text: '取消',
        handler: function() {
          $('#reportsearch').dialog('close');
        }
      }]
    });
    //替换是否多维
    $("#reportsearch").on('click', '.reportdimensions', function() {
      var thisObj = $(this);
      if (thisObj.is(":checked")) {
        var status = _this.checkDimensions();
        if (status) {
          $.messager.confirm('提示', '已经设置过多维条件，是否覆盖设置的条件', function(r) {
            if (r) {
              _this.setDimensions();
            } else {
              thisObj.checked = false;
            }
          })
        }
        //清除筛选
        $('#reportsearch').find('.reportgroup').attr("checked", false);
        $('#reportsearch').find('.defaultsearch').val('').closest('tr').hide();
      } else {
        $('#reportsearch').find('.defaultsearch').closest('tr').show();
      }
    });
  },
  //createtable
  createtable: function() {
    var data = this.option.data;
    //生成表头
    var header = data.header;
    var tableHtml = "";
    tableHtml = "<table class='table table-bordered table-condensed' style='margin:0px'>";
    if (header.length > 0) {
      //生成头部
      tableHtml += "<tbody class='grade_header'>"
      tableHtml += "<tr class='table_header'>";
      for (var i = 0; i < header.length; i++) {

        switch (data.header[i].key) {
          case 'fixed':
          case 'hide':
            tableHtml += "<td style='width:" + header[i].width + "'>" + header[i].name + "<br>";
            tableHtml += "<input type='checkbox' class='checked_" + data.header[i].key +
              "'/><span class='fixedtext'>全选</span>";
            tableHtml += "</td>";
            break;
          default:
            tableHtml += "<td style='width:" + header[i].width + "'>" + header[i].name +
              "</td>";
            break;
        }
      }
      tableHtml += "</tbody>";
      //获取维度指标
      // var group = [];
      // var metric = [];
      //debugger;
      var numData = data.data;
      //生成指标 udc
      tableHtml += "<tbody id='dragtr' class='gradebox'>";
      for (var m = 0; m < numData.length; m++) {
        //维度隐藏特殊处理
        if (numData[m].hide != null && numData[m].hide == 1) {
          tableHtml += "<tr data-key='" + numData[m].key + "' class='disable'>";
        } else {
          tableHtml += "<tr data-key='" + numData[m].key + "'>";
        }
        //tableHtml +="<tr>";
        for (var s = 0; s < header.length; s++) {
          tableHtml += this.setCell(header[s].key, numData[m][header[s].key], numData[m].isgroup,
            "m_" + m);
        }
        tableHtml += "</tr>";
      }
      tableHtml += "</tbody>";
    }
    tableHtml += "</table>";
    //生成默认设置页码 普通报表设置翻页
    if ((data.contrast == '' || data.contrast == null) && data.pubdata.ispagesize != undefined) {
      tableHtml +=
        '<div class="ispagesizebox" style="padding:10px;height:42px; margin:10px 0; border:1px solid #ddd"><span>&nbsp;&nbsp;设置默认页码</span>&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" value="" name="ispagesize" class="ispagesize" /><span class="pagesizebox" style="margin-left:15px;">每页显示条数 <input class="easyui-numberspinner pagesize" name="pagesize" data-options="min:10,increment:5,max:100" style="width:75px;" /></span><lable style="padding-left:10px;color: #B9B9B9;">（提示：不设置默认页码，一次性最多显示3000条）</lable></div>';
    }
    //是否打开表格颜色渐变
    //if(  data.pubdata.gradually != undefined  &&  parseInt( data.pubdata.gradually)  >0 ){
    tableHtml +=
      '<div class="gradually" style="padding:10px;height:42px; margin:10px 0; border:1px solid #ddd">';
    tableHtml += '<span>&nbsp;&nbsp;表格颜色渐变</span>&nbsp;&nbsp;&nbsp;&nbsp';
    tableHtml += '<input type="checkbox" value="" name="gradually" class="gradually" ';
    if (data.pubdata.gradually) {
      tableHtml += " checked=checked";
    }
    tableHtml += "/>";
    tableHtml += '</div>';
    //}
    // 2016-12-21 排序顺序内容 TODO
    if (data.pubdata.isShowSort !== undefined && data.pubdata.isShowSort === 1) {
      tableHtml +=
        '<div class="new-sort-seq" style="padding:10px;height:42px; margin:10px 0; border:1px solid #ddd">';
      tableHtml +=
        '<span>&nbsp;&nbsp;排序顺序</span>&nbsp;&nbsp;&nbsp;&nbsp<ul class="sort-container" style="margin-top:-21px;margin-left:10px;"></ul>';
      tableHtml += '</div>';
    }
    //生成说明信息与相对占比
    if (data.pubdata.isproportion != undefined && data.pubdata.isproportion != null) {
      tableHtml +=
        '<div class="new_proportion" style="line-height:32px; padding:0 10px;margin:10px 0; border:1px solid #ddd">';
      tableHtml +=
        '<span style="color:red">&nbsp;&nbsp;慎用&nbsp;&nbsp;</span>用户自定义列 是否相对占比&nbsp;&nbsp;&nbsp;&nbsp';
      tableHtml += '<input type="checkbox" value="" name="isproportion"';
      if (data.pubdata.isproportion) {
        tableHtml += " checked =checked";
      }
      tableHtml += 'class="isproportion" /> </div>';
    }

    tableHtml +=
      '<button class="btn btn-primary btn-xs addColumn">增加一行</button><input type="hidden" class="chart_conf"/>';
    //控制提示信息
    if (data.pubdata.not_tips != undefined && data.pubdata.not_tips == 1) {

    } else {
      tableHtml += '<div>';
      //tableHtml +="<input type='hidden' class='chart_conf'/>";
      tableHtml += '<p class="tipinfoother" style="margin:5px">相对占比：用户自定义列的值根据页面搜索条件动态生成</p>';
      tableHtml +=
        '<p class="tipinfoother" style="margin:5px">百分比：只会在数据上加上百分号(%)，不会乘以100 比如一个数据为0.01 显示的报表为0.01% </p>';
      tableHtml += '<p class="tipinfoother" style="margin:5px">排序：请在表格第一列进行拖拽排序 </p>';
      tableHtml +=
        '<p class="tipinfoother" style="margin:5px">类型：类型分为维度和指标，排序只支持相同类型列排序，不同类型之间不可排序 </p>';
      tableHtml += '</div>';
    }
    if (data.contrast != '' && data.contrast != null) {
      tableHtml += this.getContrast(data.contrast);
    }
    $("#" + this.option.id).html(tableHtml).attr('');
    //2016-12-21 初始化带回参数并绑定sorable事件 TODO 在这个地方填充合适吗？
    initSortList("init");
    bindSortable();
    $('select[name="op"]').select2();
    this.clipboard(); //复制 粘贴
    $('.showinfo').tooltip({
      'position': 'top'
    }); // tooltip

    //默认设置页码 相对占比
    this.setpubData(this.option.data.pubdata);
    //$("#"+ this.option.id).find('input[name="pagesize"]').numberspinner({ min: 10, max: 100,value:pagesize});

  },
  // oprateColoum:function(coloumName,data,type){
  // 	if(type ==1){
  // 		for( var i=0;i<data.length;i++){
  //          if(data[i].key ==coloumName){
  //             data[i].splice(i,1);
  //          }
  //        }
  // 	}else{

  // 	}

  //       return data;
  // },
  // //删除列
  // delColoum:function(coloumName,defaults){
  // 	//删除头部
  // 	var _this  = this;
  // 	defaults.header =  _this.oprateColoum(coloumName,defaults.header);
  // 	defaults.data =  _this.oprateColoum(coloumName,defaults.data);
  // 	return defaults;
  // },
  // //还原列
  // recoveryColoum:function(coloumName,defaults){

  // },
  //设置udc名称
  setConf: function(udcstr) {
    var source = $("#" + this.option.id);
    source.find('.chart_conf').val(udcstr);
  },
  getConf: function() {
    var source = $("#" + this.option.id);
    var udcstr = source.find('.chart_conf').val();
    if (udcstr) {
      return udcstr;
    } else {
      return false;
    }
  },
  //生成对比报表 独有列
  getContrast: function(contrast) {
    var table = "";
    if (contrast != null) {
      table =
        "<table class='table table-bordered table-condensed  contrast_table' style='margin:0px'>";
      table += "<tr  class='table_header'>";
      for (var i = 0; i < contrast.header.length; i++) {
        table += "<td>" + contrast.header[i] + "</td>";
      }
      table += "</tr>";
      var bodyData = contrast.data;
      table += "<tbody  class='contrasttr'>";
      for (var j = 0; j < bodyData.length; j++) {
        table += "<tr>";
        table += "<td>";
        if (bodyData[j].isshow) {
          table += "<input class='contrast_check' type='checkbox'  checked ='checked'/>";
        } else {
          table += "<input class='contrast_check' type='checkbox'/>";
        }
        table += "</td>";
        table += "<td class='contrastname'>" + bodyData[j].name + "</td>";
        table += "<td class='contrastkey'>" + bodyData[j].key + "</td>";
        table += "</tr>";
      };
      //正负值以后扩展
      table += "</tbdoy>";
    }
    return table;
  },
  //生成某一列  设置这列数据
  setCell: function(key, keyVal, type, item) {
    var keyVal = keyVal ? keyVal : "";
    var td = "";
    td += "<td class='" + key + "_report'>";
    switch (key) {
      case 'type':
        td += keyVal;
        break;
      case 'name':
        if (type == 3 || type == 4) {
          td += "<textarea style='width:100%'>" + keyVal + "</textarea>";
        } else {
          td += keyVal;
        }
        break;
        //说明
      case 'key':
        //返回 复制插件；
        if (type == 3) {
          td += "<textarea style='width:100%'>" + keyVal + "</textarea>";
        } else if (type == 4) {
          td += "<b>" + keyVal + "</b>"
        } else {
          td += "<b style='width:100%;height:100%;display:none' id='clipContent_" + item + "'>" +
            keyVal + "</b>";
          td += "<small class='clipBtn' id='clipBtn_" + item + "' title='" + keyVal +
            "' data-clipboard-target='clipContent_" + item + "' data=" + item + ">复制</small>";
          td += "<a data-toggle='tooltip' title='" + keyVal +
            "' class='showinfo glyphicon glyphicon-question-sign'></a>";
        }
        break;
      case 'explain':
        if (type == 3 || type == 4) {
          td += "<textarea style='width:100%'>" + keyVal + "</textarea>";
        } else {
          if (keyVal.length > 0 && keyVal.length > 6) {
            td += "<span>" + keyVal + "</span>";
            td += '<a data-toggle="tooltip" title="' + keyVal +
              '" class="showinfo glyphicon glyphicon-question-sign"></a>';
          } else {
            td += "<span>" + keyVal + "</span>";
          }
        }

        break;
      case 'filter':
        //返回select  与输入框
        if (keyVal == '-') {
          td += "-";
        } else {
          if (keyVal) {
            if (typeof(keyVal) == 'string') {
              keyVal = JSON.parse(keyVal);
            }
          }
          td += "<select name='op' style='width:60px;display:inline-block'>";
          td += "<option value='filter_not' selected = selected>----</option>";
          td += "<option value='=' ";
          if (keyVal.op != undefined) {
            if (keyVal.op == '=') {
              td += " selected=selected";
            }
          }
          td += " >=</option>";


          td += "<option value='like' ";
          if (keyVal.op != undefined) {
            if (keyVal.op == 'like') {
              td += " selected=selected";
            }
          }
          td += " >like</option>";

          td += "<option value='not like' ";
          if (keyVal.op != undefined) {
            if (keyVal.op == 'not like') {
              td += " selected=selected";
            }
          }
          td += " >not like</option>";

          td += "<option value='start with' ";
          if (keyVal.op != undefined) {
            if (keyVal.op == 'start with') {
              td += " selected=selected";
            }
          }
          td += " >start with</option>";

          td += "<option value='end with' ";
          if (keyVal.op != undefined) {
            if (keyVal.op == 'end with') {
              td += " selected=selected";
            }
          }
          td += " >end with</option>";

          td += "<option value='in' ";
          if (keyVal.op != undefined) {
            if (keyVal.op == 'in') {
              td += " selected=selected";
            }
          }
          td += " >in</option>";

          td += "<option value='not in' ";
          if (keyVal.op != undefined) {
            if (keyVal.op == 'not in') {
              td += " selected=selected";
            }
          }
          td += " >not in</option>";

          td += "<option value='>=' ";
          if (keyVal.op != undefined) {
            if (keyVal.op == '>=') {
              td += " selected=selected";
            }
          }
          td += " >&gt;=</option>";

          td += "<option value='<=' ";
          if (keyVal.op != undefined) {
            if (keyVal.op == '<=') {
              td += " selected=selected";
            }
          }
          td += " >&lt;=</option>";

          td += "<option value='<' ";
          if (keyVal.op != undefined) {
            if (keyVal.op == '<') {
              td += " selected=selected";
            }
          }
          td += " > &lt;</option>";

          td += "<option value='>' ";
          if (keyVal.op != undefined) {
            if (keyVal.op == '>') {
              td += " selected=selected";
            }
          }
          td += " > &gt;</option>";

          td += "<option value='!=' ";
          if (keyVal.op != undefined) {
            if (keyVal.op == '!=') {
              td += " selected=selected";
            }
          }
          td += " >!=</option>";

          td += "<option value='REGEXP' ";
          if (keyVal.op != undefined) {
            if (keyVal.op == 'REGEXP') {
              td += " selected=selected";
            }
          }
          td += " >正则</option>";


          td += "  </select>";

          td += "  <input type='text' class='op_val'";
          if (keyVal.val != undefined) {
            td += " value='" + keyVal.val.join("?") + "'";
          }
          td += "   style='width:80px' /> ";
        }
        break;
      case 'expression':
        if (type == 3) {
          //返回输入框
          td += "<textarea>" + keyVal + "</textarea>";
        } else {
          td += "-";
        }
        break;
      case 'issearch':
        if (keyVal == '-') {
          td += "-";
        } else {
          if (keyVal.is_check) {
            td += "<input type='checkbox' class='ck_issearch'  ";
            td += " checked ='checked' ";
            td += " />";
            if (keyVal.is_accurate) {
              td += "<span class='accurate_box btn btn-xs btn-primary' ";
              td += " style='display:inline-block'>";
              td += "精确匹配</span>";
            } else {
              td += "<span class='accurate_box btn btn-xs btn-default'>";
              td += "模糊匹配</span>";
            }
          } else {
            td += "<input type='checkbox' class='ck_issearch' />";
            td +=
              "<span class='accurate_box btn btn-xs btn-default' style='display:none'>模糊匹配</span> ";
          }
        }
        break;
      case 'search':
        //返回设置按钮
        if (keyVal == '-') {
          td += "-";
        } else {
          td += "<button class='btn btn-default btn-xs reportsearch'";
          if (keyVal) {
            td += "data-config='" + JSON.stringify(keyVal) + "' data-key='" + key + "'";
          } else {
            td += "data-config='' data-key='" + key + "'";
          }
          if (!parseInt(keyVal.isshow)) {
            td += "disabled='disabled' ";
          }
          td += ">设置</button>";
        }
        break;
        //返回checkbox;
      case 'otherlink':
        if (keyVal == '-') {
          td += "-";
        } else {
          td += "<input type='text' class='otherlink' placeholder='a/b?c=${c}&d=${d}' ";
          if (keyVal != '') {
            td += " value ='" + keyVal + "' ";
          }
        }
        //返回输入框
        break;
      case 'img_link':
        if (keyVal == '-') {
          td += "-";
        } else {
          td += "<input type='text' class='img_link' placeholder='a/b?c=${c}&d=${d}' ";
          if (keyVal != '') {
            td += " value ='" + keyVal + "' ";
          }
        }
        //返回输入框
        break;
      case 'fixed':
        if (keyVal == '-') {
          td += "-";
        } else {
          td += "<input type='checkbox' class='" + key + "_checkbox' ";
          if (parseInt(keyVal)) {
            td += " checked ='checked' ";
          }
          td += " />";
        }
        break;
      case 'percent':
        if (type == 1) {
          td += "-";
        } else {
          if (keyVal == '-') {
            td += "-";
          } else {
            td += "<input type='checkbox' class='" + key + "_checkbox' ";
            if (parseInt(keyVal)) {
              td += " checked ='checked' ";
            }
            td += " />";
          }
        }
        //返回checkbox
        break;
      case 'thousand':
        if (type == 1) {
          td += "-";
        } else {
          if (keyVal == '-') {
            td += "-";
          } else {
            td += "<input type='checkbox' class='" + key + "_checkbox' ";
            if (parseInt(keyVal)) {
              td += " checked ='checked' ";
            }
            td += " />";
          }
        }
        //返回checkbox
        break;
      case 'sort':
        if (keyVal == '') {
          keyVal = 'filter_not';
        }
        td += "<select class='ck_sort'>";
        td += "<option value='filter_not' "
        if (keyVal == 'filter_not') {
          td += " selected ='selected' ";
        }
        td += ">不排序</option>";
        td += "<option value='asc' ";
        if (keyVal == 'asc') {
          td += " selected ='selected' ";
        }
        td += "'>升序</option>";
        td += "<option value='desc' ";
        if (keyVal == 'desc') {
          td += " selected ='selected' ";
        }
        td += "'>降序</option>";
        td += "</select>";
        break;
      case 'hide':
        if (keyVal == '-') {
          td += "-";
        } else {
          td += "<input type='checkbox' class='" + key + "_checkbox' ";
          if (keyVal == 1) {
            td += " checked ='checked' ";
          }
          td += " />";
          if (type == 3) {
            td += "<a class='columndel btn btn-default btn-xs'>删除</a>";
          }
        }
        //返回checkbox
        break;
      case 'converge':
        if (type == 3 || type == 1) {
          keyVal = '-';
        }
        if (keyVal == '-') {
          td += "-";
        } else {
          if (keyVal.key == '') {
            keyVal = {};
            keyVal.key = 'filter_not';
          }
          td += "<select class='ck_converge'>";
          td += "<option value='filter_not' ";
          if (keyVal.key == 'filter_not') {
            td += " selected ='selected' ";
          }
          td += ">-</option>";

          td += "<option value='max' ";
          if (keyVal.key == 'max') {
            td += " selected ='selected' ";
          }
          td += ">最大值</option>";

          td += "<option value='min' ";
          if (keyVal.key == 'min') {
            td += " selected ='selected' ";
          }
          td += ">最小值</option>";

          td += "<option value='sum' ";
          if (keyVal.key == 'sum') {
            td += " selected ='selected' ";
          }
          td += ">求和</option>";

          td += "<option value='avg' ";
          if (keyVal.key == 'avg') {
            td += " selected ='selected' ";
          }
          td += ">求平均</option>";

          td += "<option value='count' ";
          if (keyVal.key == 'count') {
            td += " selected ='selected' ";
          }
          td += ">计数</option>";
          td += "</select>";
        }
        break;
      case 'width':
        keyVal = keyVal ? parseInt(keyVal) : '100';
        td += '<input type="number" value="' + keyVal + '" class="width" placeholder="数字" />';
        break;
      case 'name_hide':
        td += "<input type='checkbox' class='" + key + "_checkbox' ";
        if (keyVal == 1) {
          td += " checked ='checked' ";
        }
        td += " />";
        break;
      case 'xaxis':
        if (type != 1) {
          td += '-';
        } else {
          td += "<input type='checkbox' class='" + key + "_checkbox' ";
          if (keyVal == 1) {
            td += " checked ='checked' ";
          }
          td += " />";
        }

        break;
    }
    td += "</td>";
    return td;
  },
  getCell: function(key, parent, type) {
    var val = '';
    var obj = parent.find("." + key + "_report");
    switch (key) {
      case 'type':
      case 'name':
        if (obj.find('textarea').length > 0) {
          val = obj.find('textarea').val();
        } else {
          val = obj.text();
        }
        break;
        //说明
      case 'explain':
        if (obj.find('textarea').length > 0) {
          val = obj.find('textarea').val();
        } else {
          val = obj.text();
        }
        break;
      case 'key':
        //返回 复制插件；
        if (obj.find('textarea').length > 0) {
          val = obj.find('textarea').val();
        } else {
          val = obj.find('b').text();
        }
        break;
      case 'filter':
        //返回select  与输入框
        if (obj.text() == '-') {
          val = '-';
        } else {
          val = {};
          var op = obj.find('select[name=op]').val();
          var tmpval = obj.find('input.op_val').val();
          if (op != 'filter_not') {
            val.op = op;
            var tmpArr = tmpval.split("?");
            val.val = tmpArr;
          } else {
            val = null;
          }

        }
        break;
      case 'expression':
        if (obj.find('textarea').length > 0) {
          val = obj.find('textarea').val();
        } else {
          val = null;
        }
        break;
      case 'issearch':
        if (obj.text() == '-') {
          val = "-";
        } else {
          val = {};
          if (obj.find('input.ck_issearch').is(":checked")) {
            val.is_check = 1;
            if (obj.find('.accurate_box').hasClass('btn-primary')) {
              val.is_accurate = 1;
            } else {
              val.is_accurate = 0;
            }
          } else {
            val = null;
          }
        }
        break;
      case 'search':
        //返回设置按钮
        var keyVal = obj.text();
        if (keyVal == '-') {
          val = '-';
        } else {
          if (obj.find('button').attr("disabled") != 'disabled') {
            var str = obj.find('button').attr('data-config');
            val = {};
            if (str != '') {
              val = eval("(" + str + ")");
            }
            val.isshow = 1;
          } else {
            val = {};
            val.isshow = 0;
          }
        }
        break;
        //返回checkbox;
      case 'otherlink':
        if (obj.text() == '-') {
          val = "-";
        } else {
          val = obj.find('.otherlink').val();
        }
        //返回输入框
        break;
      case 'img_link':
        if (obj.text() == '-') {
          val = "-";
        } else {
          val = obj.find('.img_link').val();
        }
        //返回输入框
        break;
      case 'fixed':
      case 'hide':
      case 'percent':
        if (obj.text() == '-') {
          val = "-";
        } else {
          var checkObj = obj.find("." + key + "_checkbox");
          if (checkObj.is(":checked")) {
            val = 1;
          } else {
            val = 0;
          }
        }
        break;
      case 'thousand':
        if (obj.text() == '-') {
          val = "-";
        } else {
          var checkObj = obj.find("." + key + "_checkbox");
          if (checkObj.is(":checked")) {
            val = 1;
          } else {
            val = 0;
          }
        }
        break;
        //数据排序
      case 'sort':
        val = obj.find("select.ck_sort").val();
        break;
      case 'converge':
        if (obj.text() == '-') {
          val = "-";
        } else {
          tmpval = obj.find("select.ck_converge").val();
          tmptext = obj.find("select.ck_converge").find("option:selected").text();
          if (tmpval != 'filter_not') {
            val = {}
            val.key = tmpval;
            val.text = tmptext;
          }
        }
        break;
      case 'width':
        val = obj.find('input[type="number"]').val();
        break;
      case 'name_hide':
        var checkObj = obj.find("." + key + "_checkbox");
        if (checkObj.is(":checked")) {
          val = 1;
        } else {
          val = 0;
        }
        break;
      case 'xaxis':
        var checkObj = obj.find("." + key + "_checkbox");
        if (checkObj.is(":checked")) {
          val = 1;
        } else {
          val = 0;
        }
    }
    return val;
  },
  //获取数据
  getData: function() {
    var ischeck = arguments[0] != undefined ? arguments[0] : 1;
    var grade = {};
    grade = this.option.data;
    var header = this.option.data.header;
    var that = this;
    grade.data = [];
    $('#' + this.option.id).find('.gradebox tr').each(function() {
      var one = {};
      for (var i = 0; i < header.length; i++) {
        one[header[i].key] = that.getCell(header[i].key, $(this));
      }
      //设置type类型
      var typeval = $(this).find(".type_report").text();
      if (typeval == '维度') {
        one.isgroup = 1;
      } else if (typeval == '指标') {
        one.isgroup = 2;
        // if( one.converge !=undefined  &&  one.converge !='-'  && one.converge !=''){
        // 	tmpname = one.name;
        // 	one.name = tmpname+ "("+ one.converge.text +")";
        // }
      } else if (typeval == 'UDC') {
        one.isgroup = 3;
      } else {
        one.isgroup = 4;
      }
      //data.push(one);
      grade.data.push(one);
    });

    //获取对比信息
    if ($('#' + this.option.id).find(".contrast_table").length > 0) {
      var contrast = [];
      $("#" + this.option.id).find(".contrasttr").find('tr').each(function() {
        var one = {};
        //{key:"today",name:"当日值",isshow:1},
        one.key = $(this).find('.contrastkey').text();
        one.name = $(this).find('.contrastname').text();
        if ($(this).find('.contrast_check').is(":checked")) {
          one.isshow = 1;
        } else {
          one.isshow = 0;
        }
        contrast.push(one);
      });
      grade.contrast.data = contrast;
    }
    var pubdata = this.getpubData();
    if (!pubdata) {
      return false;
    }

    //获取公共数据
    grade['pubdata'] = pubdata;

    if (ischeck) {
      //娄据验证
      if (that.checkData(grade.data)) {
        return grade;
      } else {
        return false;
      }
    } else {
      return grade;
    }

  },

  //获取公共pubdata数据
  getpubData: function() {
    var temppubdata = JSON.parse(JSON.stringify(this.option.data.pubdata));
    //if(typeof(params) !='undefined' && typeof(params.type)!='undefined' && params.type !=1){ return temppubdata; }
    //获取是否相对占比 是 1，否－1
    var isproportiontag = $("#" + this.option.id).find(
      '.new_proportion input[name="isproportion"]');
    if (isproportiontag.length > 0) {
      temppubdata['isproportion'] = isproportiontag.is(':checked') ? '1' : '0';
    }

    var graduallytag = $("#" + this.option.id).find('input[name="gradually"]');
    if (graduallytag.length > 0) {
      temppubdata['gradually'] = graduallytag.is(':checked') ? 1 : 0;
    }
    //获取页码设置
    var ispagesizetag = $("#" + this.option.id).find('.ispagesizebox input[name="ispagesize"]');
    if (ispagesizetag.length > 0) {
      temppubdata['ispagesize'] = ispagesizetag.is(':checked') ? '1' : '0';

      var pagesize = $("#" + this.option.id).find('.pagesizebox input[name="pagesize"]').val();

      if (pagesize == '' || pagesize < 0 || pagesize % 5 !== 0) {
        $.messager.alert('提示', '请输入被5整除的整数', 'info');
        return false;
      }
      temppubdata['pagesize'] = (pagesize && pagesize > 10) ? pagesize : 10;
    }


    return temppubdata;

  },
  //获取公共pubdata数据 参数可有可无
  setpubData: function(pubdata) {
    //if(typeof(params) !='undefined' && typeof(params.type)!='undefined' && params.type !=1){ return false }

    var temppubdata;
    if (typeof(pubdata) == 'undefined') {
      temppubdata = this.option.data.pubdata;
    } else {
      temppubdata = pubdata;
    }
    //相对占比设置
    var isproportion = (temppubdata.isproportion == 1) ? true : false;
    $("#" + this.option.id).find('.new_proportion input[name="isproportion"]').prop('checked',
      isproportion);

    //表格页码设置
    var ispagesizebox = $("#" + this.option.id).find('.ispagesizebox');
    if (ispagesizebox.length == 0) {
      return false
    }

    var pagesizetag = ispagesizebox.find('input[name="pagesize"]');
    var ispagesize = ((typeof(temppubdata.ispagesize) != 'undefined' && temppubdata.ispagesize ==
      '1') || typeof(temppubdata.ispagesize) == 'undefined') ? true : false;
    var pagesize = (typeof(temppubdata.pagesize) != 'undefined') ? temppubdata.pagesize : 10;

    ispagesizebox.find('input[name="ispagesize"]').prop('checked', ispagesize);
    //禁用的功能

    if (pubdata['pagesize_disabled'] && pubdata['pagesize_disabled'] == '1') {
      ispagesizebox.find('input[name="ispagesize"]').prop('disabled', 'true');
    }

    $(pagesizetag).numberspinner({
      min: 10,
      max: 100,
      increment: 5,
      value: pagesize
    });

    if (ispagesize) {
      ispagesizebox.find('.pagesizebox').show();
    } else {
      ispagesizebox.find('.pagesizebox').hide();
    }
  },

  //获取fakecub数据
  getAllFakeCube: function(allConf, table) {
    //debugger;
    newData = allConf.data;
    //生成filter
    var filter = [],
      udcConfArr = [],
      group = [],
      metric = [],
      udc = [],
      customSort = [],
      converge = [];
    for (var i = 0; i < newData.length; i++) {
      if (newData[i].filter != '-' && newData[i].filter != null) {
        var one = newData[i].filter;
        one.key = newData[i].key;
        filter.push(one);
      }
      //获取udc信息
      if (newData[i].isgroup == 3) {
        var one = {};
        one.name = newData[i].key;
        one.cn_name = newData[i].name;
        one.explain = newData[i].explain;
        one.expression = newData[i].expression;
        udcConfArr.push(one);
        udc.push(newData[i].key + "=" + newData[i].expression);
      }
      //获取search信息
      if (typeof(newData[i].search) == 'object') {
        var searchConf = newData[i].search;
        if (parseInt(searchConf.isgroup) == 0 && parseInt(searchConf.reportdimensions) == 1) {

        }
      }

      //获取默认排
      if (newData[i].sort != null && newData[i].sort != undefined && newData[i].sort != '' &&
        newData[i].sort != "filter_not") {
        var onesort = {};
        onesort.key = newData[i].key.split(".").join("_");
        onesort.order = newData[i].sort;
        customSort.push(onesort);
      }
      //获取聚合
      if (newData[i].converge != '-' && newData[i].converge != null && newData[i].converge !=
        undefined && newData[i].converge != '' && newData[i].converge != "filter_not") {
        var oneconverge = {};
        oneconverge.key = newData[i].key;
        oneconverge.fun = newData[i].converge.key;
        converge.push(oneconverge);
      }

    }
    // 2016-12-20 调整排序顺序获取方式。 TODO
    if (customSort.length > 0) {
      // table.customSort = JSON.stringify(customSort);
      table.customSort = JSON.stringify(formatSortItem());

    }
    if (converge.length > 0) {
      table.converge = JSON.stringify(converge);
    }
    //生成udc
    if (udc.length > 0) {
      table.udc = udc.join(",");
    } else {
      table.udc = '';
    }
    //生成udcconf
    if (udcConfArr.length > 0) {
      table.udcconf = encodeURIComponent(JSON.stringify(udcConfArr));
    } else {
      table.udcconf = encodeURIComponent('[]');
    }
    //生成filter
    if (filter.length > 0) {
      table.filter = JSON.stringify(filter);
    }
    //重新赋值
    table.grade = allConf;
    //设置新的标识位
    table.isnew = 1;
    return table;
  },

  getFilterData: function() {
    var _this = this;
    $.ajax({
      url: "/tool/ListMapData",
      async: false,
      data: {
        retu_type: "find_filter_data"
      },
      type: "post",
      success: function(res) {
        res = JSON.parse(res);
        if (res && res.status != undefined && res.status == 0) {
          if (res.data.mapdata) {
            _this.filterDataAjax = res.data.mapdata || [];
          }
        }
      },
      error: function() {}
    })
  },
  //增加一列
  addColumn: function() {
    var that = this;
    var source = $("#" + that.option.id);
    source.on('click', '.addColumn', function() {
      var header = that.option.data.header;
      var index = parseInt($(this).closest('div.reportbox').find('table tr').length) - 1;
      index = index < 0 ? 0 : index;
      var columndHtml = "";
      columndHtml += "<tr>";
      for (var i = 0; i < header.length; i++) {
        var tdKey = header[i].key + "_report";
        columndHtml += "<td style='width:" + header[i].width + "' class='" + tdKey + "'>";
        switch (header[i].key) {
          case "type":
            columndHtml += "UDC";
            break;
          case "key":
            columndHtml += "<textarea style='width:100%'></textarea>";
            break;
          case "name":
            columndHtml += "<textarea style='width:100%'></textarea>";
            break;
          case "explain":
            columndHtml += "<textarea style='width:100%'></textarea>";
            break;
          case "filter":
            columndHtml +=
              "<select name='op' style='width: 60px; display: none;'> <option value='filter_not' selected='selected'>----</option> <option value='='>=</option> <option value='like'>like</option> <option value='not like'>not like</option> <option value='start with'>start with </option> <option value='end with'>end with </option> <option value='in'>in</option> <option value='not in'>not in</option> <option value='>='> &gt;= </option> <option value='<='> &lt;= </option> <option value='<'> &lt; </option> <option value='>'> &gt; </option> <option value='!='> != </option> <option value='REGEXP'> 正则</option> </select><input type='text' class='op_val' style='width:80px'>";
            break;
          case "expression":
            columndHtml += "<textarea></textarea>";
            break;
          case "percent":
            columndHtml += "<input type='checkbox'>";
            break;
          case "thousand":
            columndHtml += "<input type='checkbox'>";
            break;
          case "issearch":
            columndHtml += "<input class='ck_issearch' type='checkbox'>";
            columndHtml += "<span class='accurate_box btn btn-xs btn-default'";
            columndHtml += " style='display:none' >";
            columndHtml += "模糊匹配</span>";
            break;
          case "search":
            columndHtml +=
              "<button class='btn btn-default btn-xs reportsearch' disabled='disabled'>设置</button>";
            break;
          case "otherlink":
            columndHtml +=
              "<input class='otherlink' type='text'  placeholder='a/b?c=${c}&d=${d}' />";
            break;
          case "fixed":
            columndHtml += "<input type='checkbox'>";
            break;
          case "sort":
            columndHtml += "<input type='checkbox'>";
            break;
          case "hide":
            columndHtml += "<input type='checkbox'  class='" + header[i].key +
              "_checkbox' ><br><a class='columndel btn btn-default btn-xs'>删除</a>";
            break;
          case 'converge':
            columndHtml += "-";
            break;
          case "width":
            columndHtml += '<input type="number" class="width" value="100"  />';
            break;
          case "name_hide":
            columndHtml += "<input type='checkbox'  class='" + header[i].key +
              "_checkbox' >";
            break;
            break;
        }
        columndHtml += "</td>";
      }
      var obj = $(this).siblings('table').find('.gradebox');
      obj.append(columndHtml);
      obj.find('select[name="op"]').select2();
      var dragtrObj = source.find('#dragtr');
      that.drag(dragtrObj);
    });
    //绑定拖拽插件

  },
  //复制粘贴功能
  clipboard: function() {
    if (navigator.userAgent.indexOf('chrome') < 0 && !window.chrome) {
      $('.clipBtn,.reportkey a').hide();
      $('.reportkey b').show();
    } else {
      var clip = null;
      clip = new ZeroClipboard(document.getElementsByClassName('clipBtn'), {
        moviePath: "/assets/lib/zeroclipboard/ZeroClipboard.swf"
      });
      clip.setHandCursor(true);
      clip.on("load", function(client) {
        client.on("complete", function(client, args) {
          console.log('clip:' + args.text);
          $('.clipBtn').removeClass('clipBtn-active');
          $(this).addClass('clipBtn-active');
        });
      });
    }
  },
  //拖拽功能
  drag: function(obj) {
    obj.dragsort("destroy");
    obj.dragsort({
      dragSelector: "tr",
      dragSelectorExclude: "select,button,input,textarea,b,small,span",
      dragEnd: function() {},
      scrollSpeed: 0
    });
  },
  //全选固定  特殊：如果第三行选中 那么前两行必须处于 选中状态～
  is_fixed: function() {
    var source = $("#" + this.option.id);
    source.on('click', 'input.fixed', function() {
      var index = $(this).closest('tr').attr('index');
      var $table = $(this).closest('table');
      if ($(this).is(":checked")) {
        $table.find('td.fixed').each(function(i) {
          if (i < index) {
            $(this).find('input.isfixed')[0].checked = true;
          }
        });
      } else {
        $table.find('td.fixed').each(function(i) {
          if (i > index) {
            $(this).find('input.isfixed')[0].checked = false;
          }
        });
      }
    });
  },
  fixed: function() {
    var source = $("#" + this.option.id);
    source.on('click', '.selectFixed', function() {
      var obj = $(this).closest('table').find('#groupid');
      var obj1 = $(this).closest('table').find('#tableid');
      if ($(this).is(":checked")) {
        obj.find('tr').each(function() {
          $(this).find('.isfixed')[0].checked = true;
        });
        $(this).next('.fixedtext').text('取消');
      } else {
        obj.find('tr').each(function() {
          $(this).find('.isfixed')[0].checked = false;
        });
        obj1.find('tr').each(function() {
          $(this).find('.isfixed')[0].checked = false;
        });
        $(this).next('.fixedtext').text('全选');
      }
    });
  },
  //全选隐藏
  hide: function() {
    var source = $("#" + this.option.id);
    source.on('click', '.checked_hide', function() {
      var obj = source.find('.gradebox');
      if ($(this).is(":checked")) {
        obj.find('tr').each(function() {
          $(this).addClass('disable');
          $(this).find('input[type=checkbox]').not($(this).find('.hide_checkbox')).attr(
            "disabled", 'disabled');
          $(this).find('.hide_checkbox')[0].checked = true;
        });
        $(this).next('.hidetext').text('取消');
      } else {
        obj.find('tr').each(function() {
          $(this).removeClass('disable');
          $(this).find('input[type=checkbox]').not($(this).find('.hide_checkbox')).removeAttr(
            "disabled");
          $(this).find('.hide_checkbox')[0].checked = false;
        });
        $(this).next('.hidetext').text('全选');
      }
    });
  },
  //单个隐藏 显示
  is_hide: function() {
    var source = $("#" + this.option.id);
    source.on('click', '.hide_checkbox', function() {
      var parent = $(this).closest('tr');
      if (!$(this).is(":checked")) {
        parent.removeClass('disable');
        parent.find('input[type=checkbox]').not($(this)).removeAttr("disabled");
      } else {
        parent.addClass('disable');
        parent.find('input[type=checkbox]').not($(this)).attr("disabled", 'disabled');
      }
    });
  },
  //页码是否设置
  is_pagesize: function() {
    var source = $("#" + this.option.id);
    source.on('click', 'input[name="ispagesize"]', function() {
      var obj = $(this);
      $this = $(this), $tag = $this.closest('.ispagesizebox').find('.pagesizebox');
      if ($this.is(':checked')) {
        $tag.show();
      } else {
        $tag.hide();
      }
    });
  },
  //删除
  columndel: function() {
    var source = $("#" + this.option.id);
    source.on('click', '.columndel', function() {
      var obj = $(this);
      $.messager.confirm('提示', '确定删除吗？', function(r) {
        if (r) {
          obj.parent().parent().remove();
        }
      });
    });
  },
  is_search: function() {
    var source = $("#" + this.option.id);
    source.on('click', '.ck_issearch', function() {
      if ($(this).is(":checked")) {
        $(this).parent().next().find('.reportsearch').removeAttr('disabled');
        $(this).parent().find('.accurate_box').show();
      } else {
        $(this).parent().next().find('.reportsearch').attr('disabled', 'disabled');
        $(this).parent().find('.accurate_box').hide();
      }
    });
  },
  search: function() {
    var _this = this;
    var id = this.option.id;
    var source = $("#" + id);
    var $reportsearch = $("#reportsearch");
    source.on('click', '.reportsearch', function(e) {
      //清空选上次选择的值
      $reportsearch.find('.reportkey').text();
      $reportsearch.find('.reportcheck').removeAttr('checked');
      $reportsearch.find('.reportdimensions').removeAttr('checked');
      $reportsearch.find('.reportgroup').removeAttr('checked');
      $reportsearch.find('.reportsource').val('');
      $reportsearch.find('.defaultsearch').val('');
      $reportsearch.find('.target_obj').val(id);
      var parent = $(this).closest('tr');

      //设置key
      var reportkey = $.trim(parent.find('.key_report b').text());
      if (reportkey != '') {
        $reportsearch.find('.reportkey').text(reportkey);
      } else {
        var key = $.trim(parent.find('.key_report').find('textarea').val());
        if (key == '') {
          $.messager.alert('提示', '请先填写字段名称', 'info');
          return;
        } else {
          $reportsearch.find('.reportkey').text(key);
        }
      }

      var searchStr = $(this).attr('data-config');
      var selectMapkey = "-";

      if (searchStr != undefined && searchStr != '') {
        searchArr = eval("(" + searchStr + ")");
        if (searchArr.mapkey != undefined && searchArr.mapkey.length > 0) {
          selectMapkey = searchArr.mapkey;
        }

        if (searchArr.is_check != undefined && parseInt(searchArr.is_check)) {
          $reportsearch.find('.reportcheck').attr('checked', true);
          $reportsearch.find('.reportcheck')[0].checked = true;
        } else {
          $reportsearch.find('.reportcheck').attr('checked', false);
        }
        //设置 reportgroup

        if (searchArr.reportgroup != undefined && parseInt(searchArr.reportgroup)) {
          $reportsearch.find('.reportgroup').attr('checked', true);
          $reportsearch.find('.reportgroup')[0].checked = true;
        } else {
          $reportsearch.find('.reportgroup').attr('checked', false);
        }

        if (searchArr.reportdimensions != undefined && parseInt(searchArr.reportdimensions)) {
          $reportsearch.find('.reportdimensions').attr('checked', true);
          $reportsearch.find('.reportdimensions')[0].checked = true;
          $reportsearch.find('.defaultsearch').closest('tr').hide();
        } else {
          $reportsearch.find('.reportdimensions').attr('checked', false);
          $reportsearch.find('.defaultsearch').closest('tr').show();
        }
        if (searchArr.val != undefined) {
          if (searchArr.val.length > 0) {
            $reportsearch.find('.reportsource').val(searchArr.val);
          }
        }
        if (searchArr.defaultsearch != undefined) {
          if (searchArr.defaultsearch != '') {
            $reportsearch.find('.defaultsearch').val(searchArr.defaultsearch);
          }
        }
      }
      renderSelectFilter(selectMapkey); // 新建报表 data-conf为空,但是仍需要去获取即时过滤数据并渲染

      // 对比报表 数据过滤不允许多选   比如  鞋子 衣服 的数据不能放在一起展示
      var iscontrast = source.find('.contrast_table');

      if (iscontrast.length > 0) {
        $reportsearch.find(".contrast_set").show();
        $reportsearch.find('.reportcheck').closest('tr').hide();
      } else {
        $reportsearch.find(".contrast_set").hide();
        $reportsearch.find('.reportcheck').closest('tr').show();
      }

      $reportsearch.dialog("open");
      $reportsearch.dialog("move", {
        top: e.pageY
      });
    });

    // 编辑报表表格部分时,点击 即时过滤 "设置"按钮打开dialog弹窗时获取即时过滤条件 2016-11-11,byy
    function renderSelectFilter(mapkey) {
      // 重置select标签
      if (!mapkey) {
        mapkey = "-";
      }
      var filterData = _this.filterDataAjax,
        mapkeyOptions = "<option value='-'>--请选择--</option>";
      var filterKeyValue = {};
      if (filterData && filterData.length > 0) {
        for (var i = 0; i < filterData.length; i++) {
          filterKeyValue[filterData[i].map_key] = filterData[i].map_data;
          var selected = '';
          if (mapkey == filterData[i].map_key) {
            selected = "selected ='selected'";
          }
          mapkeyOptions += "<option value='" + filterData[i].map_key + "'" + selected + ">" +
            filterData[i].map_name + "</option>";
        }
      }
      $("select.jsgltj").html(mapkeyOptions);
      $("select.jsgltj").select2();
      $("select.jsgltj").on("change", function() {
        var value = $("select.jsgltj").val();
        if (value !== "-") {
          var mapData = {
            map_data: filterKeyValue[value]
          };
          $.ajax({
            url: "/tool/CheckMapData",
            data: mapData,
            type: "post",
            success: function(response) {
              var res = JSON.parse(response);
              if (res.status === 0) {
                $(".preset-container-td").html(res.data.join(" "));
              }
            },
            error: function() {

            }
          });
        }
      })
      $(".show-preset").on("mouseover", function(e) {
        var value = $("select.jsgltj").val();
        if (value !== "-") {
          $(".preset-container").show();
        }
      }).on("mouseout", function(e) {
        $(".preset-container").hide();
      })
    }
  },

  //精确与模糊匹配切换
  search_plus: function() {
    var source = $("#" + this.option.id);
    source.on('click', '.accurate_box', function() {
      if ($(this).hasClass("btn-default")) {
        $(this).removeClass('btn-default').addClass('btn-primary');
        $(this).text("精确匹配");
      } else {
        $(this).removeClass('btn-primary').addClass('btn-default');
        $(this).text("模糊匹配");
      }
    });
  },
  sort_plus: function() {
    var source = $("#" + this.option.id);
    source.on('click', '.sort_box', function() {
      if ($(this).hasClass("btn-default")) {
        $(this).removeClass('btn-default').addClass('btn-primary');
        $(this).text("升序");
      } else {
        $(this).removeClass('btn-primary').addClass('btn-default');
        $(this).text("降序");
      }
    });
  },
  //数据验证
  checkData: function(data) {
    var converge = [],
      conCont = 0;
    for (var i = 0; i < data.length; i++) {
      if (data[i].key == '' && data[i].isgroup == 3) {
        $.messager.alert("提示", '列key必须填写完整', 'warning');
        return false;
      }
      if (data[i].name == '' && (data[i].isgroup == 3 || data[i].isgroup == 4)) {
        $.messager.alert("提示", '列名称必须填写完整', 'warning');
        return false;
      }
      if (data[i].expression == '' && data[i].isgroup == 3) {
        $.messager.alert("提示", '计算值必须填写完整', 'warning');
        return false;
      }
      if (data[i].converge != undefined && data[i].converge != '-' && data[i].converge != null &&
        data[i].converge != 'filter_not' && data[i].converge != '') {
        converge.push(data[i].converge.key);
      }
      if (data[i].isgroup == 2) {
        conCont++;
      }
    }
    if (converge.length > 0) {
      if (converge.length != conCont) {
        $.messager.alert("提示", '聚合指标设置不完整', 'warning');
        return false;
      }
    }
    //验证聚合函数是否全都选择了
    return true;
  },
  //重新设置数据
  setData: function(option) {
    //增加Udc 数据
    // 2016-12-22 增加是否显示排序的判断逻辑
    if (this.option.id === "tableReportBox") {
      option.pubdata.isShowSort = 1;
    } else {
      option.pubdata.isShowSort = 0;
    }
    this.option.data = option;
    this.createtable();
    var source = $("#" + this.option.id);
    var dragtrObj = source.find('#dragtr');
    this.drag(dragtrObj);
  },
  checkDimensions: function() {
    //跳过验证
    var data = this.getData(false);
    var newData = data.data;
    var status = 0;
    for (var i = 0; i < newData.length; i++) {
      //获取search信息
      if (typeof(newData[i].search) == 'object') {
        var searchConf = newData[i].search;
        if (parseInt(searchConf.reportdimensions) == 1) {
          status = 1;
        }
      }
    }
    if (status) {
      return true;
    } else {
      return false;
    }
  },
  setDimensions: function() {
      //跳过验证
      var _this = this;
      $('#' + this.option.id).find('.gradebox tr').each(function() {
        var one = {};
        search = _this.getCell('search', $(this));
        if (typeof(search) == 'object') {
          if (parseInt(search.reportdimensions) == 1) {
            search.reportdimensions = 0;
            $(this).find(".search_report").find('.reportsearch').attr('data-config', JSON.stringify(
              search));
          }
        }
      });
    }
    //数据验证功能
}

// 2016-12-19 增加辅助方法，初始化排序内容
function initSortList(type, defaultSortList) {
  var $sort = $($(".sort-container")[0]);
  var $dom = $("#tableReportBox").find("#dragtr");
  var checkList = [];
  var finalList;

  // 判断原来是不是已经有内容了,如果原来有内容了,就把原来的内容取出来判断一次
  if (params && params.finalList && params.finalList.length > 0) {
    checkList = params.finalList;
  } else {
    if (type === "init") {
      if (params && params.tablelist && params.tablelist.length != 0 && params.tablelist[0].customSort) {
        // 获取用户预设
        var customSort = JSON.parse(params.tablelist[0].customSort);
        // 比较dom节点中与用户预设的内容，返回结果
        if ($dom.children().length > 1) {
          checkList = customSort;
        }
      }
    } else if (type === "clean") {
      if ($dom.children().length > 1) {
        checkList = defaultSortList;
      }
    }
  }
  finalList = getActualList($dom, checkList);
  $sort.empty();
  if (finalList && finalList.length > 0) {
    $.each(finalList, function(index, item) {
      // 生成排序html段并插入到页面中
      if (item) {
        if (!item.name) {
          if (params && params.tablelist[0]) {
            findSortName(params.tablelist[0].grade.data, item);
          }
        }
        createSortItem(item);
      }
    });
    params.finalList = finalList;
  }


}

/**
 * 生成排序item
 */

function createSortItem(item) {
  // 判断是升序还是降序,变为相应的符号
  item.sortDes = getSortDes(item);
  var itemHtml = [
    '<li draggable="true" class="sort-item" id="',
    item.key,
    '"order="',
    item.order,
    '">',
    item.name + item.sortDes,
    '</li>'
  ].join("");
  appendSortItem(itemHtml);
  // 实时更新finalList
  updateFinalList();
}

/**
 * 插入sortItem
 */
function appendSortItem(itemHtml) {
  var $sort = $($(".sort-container")[0]);
  $sort.append(itemHtml);
}

/**
 * 删除排序item
 */
function deleteSortItem(item) {
  var $nodeItem = getSortItem(item.key);
  $nodeItem.remove();
  // 实时更新finalList
  updateFinalList();
}

/**
 * 修改排序item
 */
function changeSortItem(item) {
  var $nodeItem = getSortItem(item.key);
  var des = getSortDes(item);
  $nodeItem.attr("order", item.order);
  $nodeItem.html(item.name + des);
  // 实时更新finalList
  updateFinalList();
}

/**
 * 查找是否存在排序item
 */
function whetherSortItem(key) {
  var $sort = $($(".sort-container")[0]);
  var checkResult = false;
  $.each($sort.children(), function(index, node) {
    if ($(node).attr("id") === key) {
      checkResult = true;
    }
  });
  return checkResult;
}
/**
 * 获取排序item用于替换属性参数
 */
function getSortItem(key) {
  var $sort = $($(".sort-container")[0]);
  var nodeItem;
  $.each($sort.children(), function(index, node) {
    if ($(node).attr("id") === key) {
      nodeItem = $(node);
    }
  });
  return nodeItem;
}

// 从原始数据中查询name
function findSortName(sourceData, newItem) {
  $.each(sourceData, function(index, item) {
    if (item.key === newItem.key) {
      newItem.name = item.name;
      return false;
    }
  })
}

// 从预设table中查找名称
function getSortName($dom) {
  var $name = $dom.find(".name_report");
  if ($name.find("textarea").html()) {
    return $name.find("textarea").html();
  } else {
    return $name.html();
  }
}

/**
 * 组织dom中的数据最后传输用
 * 也就是最后的customSort
 */
function formatSortItem() {
  var $sort = $($(".sort-container")[0]);
  var customSort = [];
  $.each($sort.children(), function(index, node) {
    customSort.push({
      key: $(node).attr("id").split(".").join("_"),
      order: $(node).attr("order")
    });
  });

  return customSort;
}

/**
 * 组织下方排序顺序dom中的数据下次进入时用,每次发生调整时就会更新
 */
function formatSortList() {
  var $sort = $($(".sort-container")[0]);
  var finalSortList = [];
  $.each($sort.children(), function(index, node) {
    finalSortList.push({
      key: $(node).attr("id"),
      order: $(node).attr("order"),
      name: getSortName($(node))
    });
  });

  return finalSortList;
}
/**
 * 获取sortDes
 */
function getSortDes(item) {
  var des;
  if (item.order === "desc") {
    des = "↓";
  } else {
    des = "↑";
  }
  return des;
}

// 绑定sortable插件
function bindSortable() {
  var $sort = $(".sort-container");
  if ($sort.length > 0) {
    var el = $(".sort-container")[0];
    var sortable = Sortable.create(el, {
      onEnd: function(evt) {
        updateFinalList();
        $(evt.item).removeClass("sort-item-move");
      },
      onStart: function( /**Event*/ evt) { // 拖拽
        $(evt.item).addClass("sort-item-move");
      },

    });
  }


}


/**
 * 合并两个比较的sortList并返回正确的值
 @param $dom 需要比较的table Dom
 @param checkList 被比较的
 @return actualList 实际的actualList
 */
function getActualList($dom, checkList) {
  var domSortList = [];
  // 中间用list
  var tempSortList = [];
  // 增加的list
  var addedList = [];
  // 最终的list
  var actualList = [];
  $.each($dom.children(), function(index, item) {
    var order = $(item).find(".ck_sort").val();
    var key = $(item).data("key");
    var name = getSortName($(item));
    var sortItem = {};
    var founded = false;
    if (order !== "filter_not") {
      sortItem = {
        key: key,
        order: order,
        name: name
      }
      $.each(checkList, function(index, checkItem) {
        if (checkItem !== undefined) {
          if (checkItem.key === sortItem.key && checkItem.order === sortItem.order) {
            tempSortList[index] = sortItem;
            founded = true;
          }
        }
      })
      if (founded === false) {
        addedList.push(sortItem);
      }
    }
  })
  actualList = tempSortList.concat(addedList);
  return actualList;
}

/**
 * 每次作调整后都要实时更新params中的finalList
 */
function updateFinalList() {
  params.finalList = formatSortList();
}

// 绑定 排序切换时的事件
$("body").on("change", ".ck_sort", function(e) {
  var $sort = $($(".sort-container")[0]);
  var $dom = $(e.currentTarget);
  var $parent = $dom.parents("tr");
  var value = $dom.val();
  var item = {};
  // 获取排序类型
  item.order = value;
  // 获取排序的列key
  item.key = $parent.data("key");
  // 获取名称
  item.name = getSortName($parent);
  if (value === "filter_not") {
    deleteSortItem(item);
  } else {
    if (!whetherSortItem(item.key)) {
      createSortItem(item);
    } else {
      changeSortItem(item);
    }
  }

})
