/*
名称替换  更改了项目名称需要替换成新名称
*/
function nameReplace(config, configTable) {
  var projectInfo = config.data.project[0].categories;
  var groupArr = configTable.group.split(",");
  var metricArr = configTable.metric.split(",");
  var tableInfo = {};
  tableInfo.group = [];
  var tmpG = [],
    tmpM = [];
  tableInfo.metric = [];
  //获取维度
  for (var i = 0; i < projectInfo.length; i++) {
    for (j = 0; j < projectInfo[i].groups.length; j++) {
      var groups = projectInfo[i].groups[j].dimensions;
      for (var x = 0; x < groups.length; x++) {
        if (in_array(groups[x].name, groupArr) && !in_array(groups[x].name, tmpG)) {
          var one = {}
          one.key = groups[x].name;
          one.name = groups[x].cn_name;
          one.explain = groups[x].explain;
          tableInfo.group.push(one);
          tmpG.push(groups[x].name);
        }
      }
      var metric = projectInfo[i].groups[j].metrics;
      for (var y = 0; y < metric.length; y++) {
        var tmpkey = projectInfo[i].name + "." + projectInfo[i].groups[j].name + "." + metric[y].name;

        if (in_array(tmpkey, metricArr) && !in_array(tmpkey, tmpG)) {
          var one = {}
          one.key = tmpkey;
          one.name = metric[y].cn_name;
          one.explain = metric[y].explain;
          tableInfo.metric.push(one);
          tmpM.push(tmpkey);
        }

      }

    }
  }
  var data = configTable.grade.data;
  for (var i = 0; i < data.length; i++) {
    for (var j = 0; j < tableInfo.group.length; j++) {
      if (data[i].key == tableInfo.group[j].key) {
        data[i].name = tableInfo.group[j].name;
        data[i].explain = tableInfo.group[j].explain;
      }
    }
    for (var j = 0; j < tableInfo.metric.length; j++) {
      if (data[i].key == tableInfo.metric[j].key) {
        data[i].name = tableInfo.metric[j].name;
        data[i].explain = tableInfo.metric[j].explain;
      }
    }
  }
  return configTable;

}
/*
主副表参数设置
*/
function main_subtabulation(type, isaddmeter, defaults) {
  if (parseInt(type) == 1 && parseInt(isaddmeter) == 1) {
    defaults['pubdata']['ispagesize'] = 0;
    defaults['pubdata']['pagesize_disabled'] = 1;
  } else {
    defaults['pubdata']['ispagesize'] = 1
    defaults['pubdata']['pagesize_disabled'] = 0;
  }
  return defaults;
}

function setSrc(source, header, type) {
  var data = [];
  for (var i = 0; i < source.length; i++) {
    var one = {};

    for (var j = 0; j < header.length; j++) {
      if (source[i][header[j].key] == undefined) {
        one[header[j].key] = null;
      } else {
        one[header[j].key] = source[i][header[j].key];
      }
    }
    switch (type) {
      case 1:
        one.isgroup = 1;
        one.type = '维度';
        break;
      case 2:
        one.isgroup = 2;
        one.type = '指标';
        break;
      case 3:
        one.isgroup = 3;
        one.type = 'UDC';
        break;
      case 4:
        one.isgroup = 4;
        one.type = '列名';
        break;
    }
    data.push(one);
  }
  return data;
}
/*
  fakecube 转换成 new data 数据
  source.group   =[]
  source.metric  =[]
  source.udcconf
*/
function getDataTable(source, header) {
  //处理group
  //var data = [];
  var data = setSrc(source.group, header, 1);
  //处理metric
  var metric = setSrc(source.metric, header, 2);
  data = $.merge(data, metric);

  //处理UDC
  if (source.udcconf) {
    var udcconf = eval("(" + decodeURIComponent(source.udcconf) + ")");
    if (udcconf != 0 || udcconf.length > 0) {
      //处理兼容逻辑 udc 名称 与 新数据格式名称不一样的问题
      for (var i = 0; i < udcconf.length; i++) {
        //转成
        udcconf[i].key = udcconf[i].name;
        udcconf[i].name = udcconf[i].cn_name;
      }
      data = $.merge(data, setSrc(udcconf, header, 3));
    }
  }
  // 自定义格表格 处理sqldata
  if (source.type && source.type == '8' && source.sqldata) {
    var sqldata = setSrc(source.sqldata, header, 4);

    data = $.merge(data, sqldata);

  }

  return data;
}

function sort_grade(table, showsort) {
  var newData = [];
  //获取原来存在的数据 (保存原来数据顺序)
  for (var s = 0; s < showsort.length; s++) {
    var oneNew = {};
    for (h = 0; h < table.length; h++) {
      if (table[h].key == showsort[s]) {
        oneNew = table[h];
        newData.push(oneNew);
      }
    }
  }
  //处理新增加的数值  将新增数据增加到 数据后面
  for (var p = 0; p < table.length; p++) {
    if (!in_array(table[p].key, showsort)) {
      newData.push(table[p]);
    }
  }
  return newData;
}
//原table数据转换成新的表格数据
function getGradeData(source, table) {
  grade = source.grade;
  //处理显示隐藏
  for (var i = 0; i < table.length; i++) {
    //固定
    if (grade != undefined && grade.fiexd != undefined) {
      if (in_array(table[i].key, grade.fiexd)) {
        table[i].fixed = 1;
      }
      //默认把维度设置固定
      if (grade.fiexd != undefined && grade.fiexd.length == 1 && table[i].isgroup == 1 && grade.fiexd[
          0] == 'date') {
        table[i].fixed = 1;
      }
    }
    //显示隐藏
    if (grade != undefined && grade.sort != undefined && grade.showsort != undefined) {
      //如果当前指标不在sort里面  并且在 showsort 里面
      //全部转小写
      for (var s = 0; s < grade.sort.length; s++) {
        grade.sort[s] = grade.sort[s].toLocaleLowerCase();
      }
      for (var p = 0; p < grade.showsort.length; p++) {
        grade.showsort[p] = grade.showsort[p].toLocaleLowerCase();
      }
      if (!in_array(table[i].key, grade.sort) && in_array(table[i].key, grade.showsort)) {
        table[i].hide = 1;
      }
    }
    //filter
    if (source.filter != undefined && source.filter.length > 0) {
      for (var j = 0; j < source.filter.length; j++) {
        if (table[i].key == source.filter[j].key) {
          table[i].filter = source.filter[j];
        }
      }
    }
    //search is_search
    var one = {};
    if (grade != undefined && grade.search != undefined && grade.search.length > 0) {
      for (var j = 0; j < grade.search.length; j++) {
        if (table[i].key == grade.search[j].reportkey) {
          one.is_check = 1;
          if (grade.search[j].is_accurate != undefined) {
            one.is_accurate = parseInt(grade.search[j].is_accurate);
          } else {
            one.is_accurate = 0;
          }
          table[i].issearch = one;
          var searchone = {};
          searchone.isshow = 1;
          if (grade.search[j].reportsource != undefined) {
            searchone.is_check = parseInt(grade.search[j].reportcheck);
            searchone.val = grade.search[j].reportsource;
            searchone.reportgroup = parseInt(grade.search[j].reportgroup);
          }
          table[i].search = searchone;
        }
      }
    } else {
      one.is_check = 0;
      one.is_accurate = 0;
      table[i].issearch = one;
    }
    //percent
    if (grade != undefined && grade.percent != undefined) {
      if (in_array(table[i].key, grade.percent)) {
        table[i].percent = 1;
      } else {
        table[i].percent = 0;
      }
    }
    //otherlink
    if (grade != undefined && grade.otherlink != undefined) {
      for (var x in grade.otherlink) {
        if (table[i].key == x) {

          table[i].otherlink = grade.otherlink[x];
        }
      }
    }
    //默认排序
    if (grade != undefined && grade.orderbyarr != undefined) {
      if (in_array(table[i].key, grade.orderbyarr)) {
        table[i].sort = 'desc';
      }
    } else {
      if (table[i].key == 'date' && table[i].name == '时间') {
        table[i].sort = 'desc';
      }
    }

    //宽度特殊处理
    if (table[i].key == "mgj_item_id") {
      table[i]['width'] = '180';
    } else if (table[i].key == "twitter_id") {
      table[i]['width'] = '120';
    } else {
      table[i]['width'] = '100';
    }
  }
  //处理排序
  if (grade != undefined && grade.showsort != undefined && grade.showsort.length > 0) {
    table = sort_grade(table, grade.showsort);
  }
  return table;
}

//tablelist 项目名称更改时  报表名称替换
function tablelistReplace(tablelist) {
  for (var i = 0, len = tablelist.length; i < len; i++) {
    tablelist[i] = nameReplace(config, tablelist[i]);
  }
  return tablelist;
}

//老数据切换成新数据操作
function oldFromNew(tableInfo) {
  /* if(params['tablelist'] && params['tablelist'].length >0 ){
       for(var i = 0,len = params['tablelist'].length; i<len; i++ ){
           params['tablelist'][i]  = nameReplace(config,params.tablelist[i]);
       }
       return false;
   }*/
  if (params['tablelist'] && params['tablelist'].length > 0) {
    params['tablelist'] = tablelistReplace(params.tablelist);
    return false;
  }
  //无报表状态
  if (!params.table) {
    return false;
  }
  if (parseInt(params.table.isnew) == 1) {
    //重新处理数据格式
    params.table = nameReplace(config, params.table);
    //console.log(nameReplace(config,params.table));
    //重新处理因增加一列，老数据初使值问题
    var defaults = JSON.parse(JSON.stringify(getDefaultData()));
    for (var i = 0; i < params.table.grade.data.length; i++) {
      if (params.table.grade.data[i].key == 'date' && params.table.grade.data[i].img_link ==
        undefined) {
        params.table.grade.data[i].img_link = defaults.data[0].img_link;
      }
    }
    delete params.table.grade.search;
  } else {
    var oldserch = [],
      customSort = [];
    if (params.table.grade != undefined && params.table.grade.search != undefined && params.table.grade
      .search.length > 0) {
      oldserch = params.table.grade.search;
    }
    if (window.tableSource) {
      tableInfo = null;
      tableInfo = window.tableSource.srcExcel(params.table);
    }
    params.table.grade = tableToNewData(tableInfo, params.table, getDefaultData(params.type));
    params.table.isnew = 1;

    if (oldserch.length > 0) {
      params.table.grade.search = oldserch;
    }
  }
  if (params.type == 2) {
    delete params.table.contrast;
  }

  if (!params['tablelist']) {
    var tables = [],
      table = {};
    params['tablelist'] = [];
    table = JSON.parse(JSON.stringify(params.table));
    table['type'] = params.type;
    table['title'] = params.basereport.cn_name;
    table['id'] = 0; //临时加的 table id
    params['tablelist'].push(table);

    delete params['table'];
  }
}
/*
  处理报表排序
*/
function dataSort(data, sortdata) {
  var newData = [],
    showsort = [];
  for (var i = 0; i < sortdata.length; i++) {
    var oneNew = {};
    for (h = 0; h < data.length; h++) {
      if (data[h].key == sortdata[i].key) {
        oneNew = sortdata[i];
        newData.push(oneNew);
        showsort.push(sortdata[i].key);
      }
    }
  }
  //处理新增加的数值  将新增数据增加到 数据后面
  for (var p = 0; p < data.length; p++) {
    if (!in_array(data[p].key, showsort)) {
      newData.push(data[p]);
    }
  }
  return newData;
}

/*
   table.params sort
*/
function fakeCubeSort(tables) {
  //排序逻辑兼容 //聚合逻辑兼容
  var len = tables.length,
    newtables = [];
  for (var n = 0; n < len; n++) {
    var table = tables[n];
    var customSort = [],
      converge = [];
    var newData = table.grade.data;
    for (var i = 0; i < newData.length; i++) {
      if (newData[i].sort != null && newData[i].sort != undefined && newData[i].sort != '' &&
        newData[i].sort != "filter_not") {
        var onesort = {};
        onesort.key = newData[i].key.split(".").join("_");
        onesort.order = newData[i].sort;
        customSort.push(onesort);
      }
    }
    if (customSort.length > 0) {
      // 2012-12-21 为了兼容新的排序逻辑，将原有的顺序替换进行了注释 TODO
      // table.customSort = JSON.stringify(customSort);
    }
    newtables.push(table);
  }
  return newtables;


}
/*
  原table 转换成 新数据格式
*/
function tableToNewData(tableInfo, table, tableDefault) {
  var defaults = JSON.parse(JSON.stringify(tableDefault));
  //还原Udc
  if (table.udcconf != '0' && table.udcconf != undefined) {
    tableInfo.udcconf = table.udcconf;
  }
  //重新处理 维度 指标 和 Udc
  var timeData = JSON.parse(JSON.stringify(defaults.data));
  defaults.data = getDataTable(tableInfo, defaults.header);
  //处理表格原来兼容逻辑
  defaults.data = getGradeData(table, defaults.data);
  //处理公共数据
  if (table.isproportion != undefined && parseInt(table.isproportion)) {
    defaults.pubdata.isproportion = 1;
    //处理Udc
    for (var i = 0; i < defaults.data.length; i++) {
      if (defaults.data[i].isgroup == 3) {
        defaults.data[i].name = defaults.data[i].name + "<i>&nbsp;[相对占比]<i>";
      }
    }

  } else {
    defaults.pubdata.isproportion = 0;
  }

  //增加时间维度列
  if (timeData.length > 0) {
    defaults.data.unshift(timeData[0]);
  }
  return defaults;
}
//获取表格默认配置文件
function getDefaultData(type) {
  var tableDefault = {
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
      width: "10%"
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
    }],
    data: [],
    contrast: null,
    pubdata: {
      isproportion: 0
    }
  }
  var one = {
    isgroup: 1,
    type: "维度",
    name: "时间",
    key: "date",
    　
    explain: "显示时间",
    filter: '-',
    expression: null,
    percent: '-',
    thousand: '-',
    issearch: '-',
    search: '-',
    otherlink: '-',
    img_link: '-',
    fixed: 1,
    sort: 'desc',
    hide: 0,
    converge: '-',
    width: 100
  };
  switch (parseInt(type)) {
    case 2:
      var contrast = {
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
        }, {
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
        }]
      }
      tableDefault.contrast = contrast;
      //其它报表类型默认删除聚合
      for (var i = 0; i < tableDefault.header.length; i++) {
        if (tableDefault.header[i].key == 'converge') {
          tableDefault.header.splice(i, 1);
        }
      }
      delete one.converge;
      break;
      //聚合报表
    case 7:
      tableDefault.data.push(one);
      break;
    default:
      //其它报表类型默认删除聚合
      for (var i = 0; i < tableDefault.header.length; i++) {
        if (tableDefault.header[i].key == 'converge') {
          tableDefault.header.splice(i, 1);
        }
      }
      delete one.converge;
      tableDefault.data.push(one);
      break;
  }
  return tableDefault;
}

//自定义表格 default文件
function getSqlDefaultData(type) {
  var sqlDefault = {
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
      },
      /*{key:"filter",name:"数据过滤",width: "10%"},*/
      /*{key:"expression",name:"计算值",width: "10%"},*/
      /*{key:"percent",name:"百分比",width: "5%"},*/
      {
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
        key: "width",
        name: "宽度<br/>(像素)",
        width: '6%'
      },
      //{key:"dim",name:"是否维度",width:'6%'}
    ],
    data: [],
    contrast: null,
    pubdata: {
      'ispagesize': '1',
      'pagesize': '10',
      'not_tips': 1
    }
  };
  return sqlDefault;
}

/*
  新数据 转换成  table
*/
// function  newDataToTable(allConf,table){
//   newData = allConf.data;
//   //生成filter
//   var filter = [],udcConfArr =[],group =[],metric=[], udc =[];
//   for(var i=0; i< newData.length; i++){
//      if( newData[i].filter  !='-' &&  newData[i].filter.op !='filter_not' && newData[i].filter.op != '' ){
//         var  one = newData[i].filter;
//         one.key = newData[i].key;
//         filter.push(one);
//      }
//      //获取udc信息
//      if(newData[i].isgroup ==3){
//         var one ={};
//         one.name =  newData[i].key;
//         one.cn_name =  newData[i].name;
//         one.explain =  newData[i].explain;
//         one.expression =  newData[i].expression;
//         udcConfArr.push(one);
//         udc.push(newData[i].key +"="+newData[i].expression );
//      }
//      //获取search信息
//   }
//   //生成udc
//   if(udc.length >0){
//     table.udc =  udc.join(",");
//   }else{
//     table.udc =  '';
//   }
//   //生成udcconf
//   if(udcConfArr.length >0){
//      table.udcconf =  encodeURIComponent(JSON.stringify(udcConfArr));
//   }else{
//      table.udcconf =  encodeURIComponent('[]');
//   }
//   //生成filter
//   if(filter.length >0){
//      table.filter =  JSON.stringify(filter);
//   }
//   //重新赋值
//   table.grade = allConf;
//   //设置新的标识位
//   table.isnew = 1;
//   return table;
// }
/*
  图表chart操作
*/
function chartHideSet(chartConf, defaults, tablereport) {
  for (var i = 0; i < defaults.data.length; i++) {
    var str = defaults.data[i].key.split(".").join("_");
    //隐藏原来维度的指标
    if (defaults.data[i].isgroup != 1) {
      defaults.data[i].hide = 1;
    } else {
      defaults.data[i].hide = '-';
    }
    //处理指标
    if (in_array(str, chartConf.chartconf[0].chartData)) {
      defaults.data[i].hide = 0;
    }
    //处理维度
    if (chartConf.chartconf[0].group != undefined) {
      //属于维度，并且不在group里面
      if (!in_array(str, chartConf.chartconf[0].group) && defaults.data[i].isgroup == 1) {
        defaults.data[i].name_hide = 1;
      }
    } else {
      //兼容 没有 group 情况，所有维度都显示
      if (defaults.data[i].isgroup == 1) {
        //defaults.data[i].hide = 0;
        defaults.data[i].name_hide = 0;
      }
    }
  }
  //指标的情况不考虑，所有之前的指标名称都是显示的
  return defaults;
}

//设置时间
function setvalue(obj) {

  $.messager.prompt('自定义默认时间', '请输入一个数字:', function(v) {
    if (v) {
      var tmp = parseInt(v);
      if (isNaN(tmp)) {
        alert('请填定数字');
      } else if (tmp < 0) {
        alert('数字不能小于0');
      } else {
        var optionStr = "<option value='" + v + "'>" + v + "天</option>";
        $(obj).prev().append(optionStr);
        $(obj).prev().select2('val', [v]);
      }

    }
  });
}
//设置样式
function setTop(obj) {

  $.messager.prompt('自定义数据', '请输入一个数字:', function(v) {
    if (v) {
      var optionStr = "<option value='" + v + "'>Top" + v + "</option>";
      $(obj).prev().append(optionStr);
      $(obj).prev().select2('val', [v]);
    }
  });
}
//设置单位
function setUnit(obj) {

  $.messager.prompt('增加单位', '请输入单位名称:', function(v) {
    if (v) {
      var optionStr = "<option value='" + v + "'>" + v + "</option>";
      $.post('/report/addUnit', {
        'name': v
      }, function(data) {
        $('body').unmask();
        if (data.status == 0) {
          $(obj).prev().append(optionStr);
          $(obj).prev().select2('val', [v]);
        } else {
          $.messager.alert('提示', data.msg, 'warning');
        }
      }, 'json');
    }
  });
}
//打开操作
function open(strid, event) {
  if (strid != undefined) {
    $("#" + strid).dialog("move", {
      top: event.pageY
    });
    $("#" + strid).dialog('open');

  }
}
//设置样式
function setActive(obj, status) {
  if (obj.hasClass('active')) {
    obj.removeClass('active');
    if (status) {
      obj.css({
        "background-color": "#ffffff"
      });
    }
  } else {
    obj.addClass('active');
    if (status) {
      obj.css({
        "background-color": "#e6e6e6"
      });
    }

  }
}
//处理百分比，搜索等设置
// function setNumSer(srcObj,arr,type){
//   if("undefined" != typeof srcObj ){
//     if(srcObj.length >0){
//       for(var x =0; x< arr.length; x++){
//         for(var k=0; k< srcObj.length; k++){
//            if(type =='search'){
//               if( arr[x].key == srcObj[k].reportkey){
//                 arr[x][type] =  srcObj[k];
//               }
//            }else{
//               if( arr[x].key == srcObj[k]){
//                  arr[x][type] =  srcObj[k];
//               }
//            }

//         }
//       }
//     }
//   }
//   return arr;
// }

//合并数组
function memgry(dim) {
  var dimArr = [];
  for (var i = 0; i < dim.length; i++) {
    dimArr = $.merge(dimArr, dim[i]);
  }
  dimArr = distinctArray(dimArr);
  return dimArr;
}

function in_array(stringToSearch, arrayToSearch) {
  for (s = 0; s < arrayToSearch.length; s++) {
    thisEntry = arrayToSearch[s].toString();
    if (thisEntry == stringToSearch) {
      return true;
    }
  }
  return false;
}
//数组聚合
function distinctArray(arr) {
  var obj = {},
    temp = [];
  for (var i = 0; i < arr.length; i++) {
    if (!obj[arr[i]]) {
      temp.push(arr[i]);
      obj[arr[i]] = true;
    }
  }
  return temp;
}
//数据diff
function arry_diff(all) {
  mapData = memgry(all);
  var diff = [];
  for (var i = 0; i < mapData.length; i++) {
    var num = 0;
    for (var j = 0; j < all.length; j++) {
      if (in_array(mapData[i], all[j])) {
        num++;
      }
    }
    if (num == all.length) {
      diff.push(mapData[i]);
    }
  }
  return diff;
}

//===========================图表处理方法（暂时没有好方法合并，整合在一起）======================================
//清除图表数据
function clearChart(clearobj) {
  //清除数据
  obj = $("#chartreport");
  obj.find('input[name=chartTitle]').val('');
  obj.find('select[name=chartUnit]').select2('val', 'filter_not');
  obj.find('select[name=chartTop]').select2('val', 'filter_not');
  obj.find('select[name=chartType]').select2('val', 'pie');
  obj.find('select[name=chartWidth]').select2('val', 'filter_not');
  obj.find('select[name=chart_event_checkbox]').select2('val', []);
  obj.find('.chart_data_box').show();
  obj.find('.chart_unit_box').hide();
  obj.find('.chart_event').hide();
  obj.find('#sourcebox').hide();
  obj.find('#tableBox').hide();
  //清除图表对象
  if (clearobj != undefined) {
    clearobj.clear();
  }
}

function setSelectOption(obj, num, text, type) {
  var optArr = [];
  obj.find('option').each(function() {
    optArr.push($(this).attr('value'));
  });
  if (!in_array(num, optArr)) {
    if (type == 1) {
      var str = "<option value='" + num + "'>" + text + num + "</option>";
    } else {
      var str = "<option value='" + num + "'>" + num + text + "</option>";
    }
    obj.append(str);
  }
  obj.select2('val', num);
}

function chartTypeSet(type, obj) {
  switch (type) {
    case 'map':
    case 'funnel':
      obj.find('.chart_unit_box').hide();
      obj.find('.chart_data_box').hide();
      obj.find('.chart_event').hide();
      break;
    case 'pie':
      obj.find('.chart_unit_box').hide();
      obj.find('.chart_data_box').show();
      obj.find('.chart_event').hide();
      break;
    case 'spline_time':
    case 'area':
      obj.find('.chart_unit_box').show();
      obj.find('.chart_data_box').hide();
      obj.find('.chart_event').show();
      break;
    case 'cursor_line':
      obj.find('.chart_unit_box').show();
      obj.find('.chart_data_box').hide();
      obj.find('.chart_event').hide();
      break;
    default:
      obj.find('.chart_unit_box').show();
      obj.find('.chart_data_box').show();
      obj.find('.chart_event').hide();
      break;
  }
}
//图表还原
function chartSrc(allConf, sort) {
  var chartObj = $("#chartreport");
  chartObj.find('input[name=chartTitle]').val(allConf.chartconf[0].chartTitle);
  chartObj.find('select[name=chartType]').select2('val', allConf.chartconf[0].chartType);
  //单位设置
  if (allConf.chartconf[0].chartUnit == undefined) {
    chartObj.find('select[name=chartUnit]').select2('val', 'filter_not');
  } else {
    chartObj.find('select[name=chartUnit]').select2('val', allConf.chartconf[0].chartUnit);
  }
  //TopN
  if (allConf.chartconf[0].chartTop == undefined) {
    chartObj.find('select[name=chartTop]').select2('val', 'filter_not');
  } else {
    //判断是否在当前
    setSelectOption(chartObj.find('select[name=chartTop]'), allConf.chartconf[0].chartTop, 'Top', 1);
  }
  //宽度还原
  if (allConf.chartconf[0].chartWidth == undefined) {
    chartObj.find('select[name=chartWidth]').select2('val', 'filter_not');
  } else {
    chartObj.find('select[name=chartWidth]').select2('val', allConf.chartconf[0].chartWidth);
  }
  //还原事件关联状态
  if (allConf.chartconf[0].event != undefined) {
    var chartExcel = chartObj.find('select[name=chart_event_checkbox]').find('option');
    var eventInfo = [];
    chartExcel.each(function() {
      var one = {};
      one.id = $(this).attr('value');
      one.text = $(this).text();
      eventInfo.push(one);
    });
    var html = "";
    for (var i = 0, len = allConf.chartconf[0].event.length; i < len; i++) {
      var text = '';
      var id = allConf.chartconf[0].event[i];
      for (var x = 0, xlen = eventInfo.length; x < xlen; x++) {
        if (id == eventInfo[x].id) {
          text = eventInfo[x].text;
        }
      }
      if (id != '' && text != '') {
        html += "<li event_id='" + id +
          "' class='error_showmsg' style='display:block;width:360px'>";
        html += "<span class='text'>" + text + "</span><span class='error_close'></span></li>";
      }
    }
    var obj = chartObj.find('.eventData');
    obj.html('');
    obj.append(html);
    obj.dragsort("destroy");
    obj.dragsort({
      dragSelector: "li",
      dragSelectorExclude: "select,button,input,textarea,b,small",
      scrollSpeed: 0
    });
  }
  //还原数据处理与单位状态
  chartTypeSet(allConf.chartconf[0].chartType, chartObj);
  //设置状态位（编辑还是添加）
  chartObj.find('button[data-option=dataSource]').attr('data-sort', sort);
  chartObj.find('button[data-option=dataSource]').attr('data-conf', JSON.stringify(allConf));

}

/*
  获取图表事件信息
*/
function getEventInfo(obj) {
  var eventInfo = [];
  obj.each(function() {
    if ($(this).is(":visible")) {
      eventInfo.push($(this).attr('event_id'));
    }
  });
  return eventInfo;
}
/*
  设置图表事件信息
*/


/*
 新数据图表 隐藏选择  维度不可点
*/
function chartGroupHide(data) {
  for (var i = 0; i < data.length; i++) {
    if (data[i].isgroup == 1) {
      data[i].hide = '-';
    }
  }
  return data;
}
