//图表默认配置文件
var chartDefault = {
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
    width: "15%"
  }, {
    key: "hide",
    name: "隐藏选择",
    width: "6%"
  }, {
    key: "name_hide",
    name: "图例名称<br/>(隐藏选择)",
    width: '6%'
  }],
  data: [],
  contrast: null,
  pubdata: {
    isproportion: null,
    not_tips: 1
  }
}

$(function() {
  var sourceConfig = (typeof(params) != 'undefined' && params['sourceConfig']) ? params[
    'sourceConfig'] : {};
  var type = (typeof(params) != 'undefined' && params['type']) ? params.type : 1;
  var projectid = $('#basereport').find('select[name=project]').find('option[value="' +
    basereport.project + '"]').attr('id');
  //表格name
  var title = '';
  if (typeof(params) != 'undefined' && !params.tablelist && params.basereport['cn_name']) {
    title = params.basereport['cn_name'];
  }
  //初使化图表 数据源
  var chartSource = new dataSource({
    id: 'sourcebox',
    source: sourceConfig,
    project: {
      "id": projectid,
      "name": basereport.project
    }
  });
  //初使化 表格数据源
  var tableSource = new dataSource({
    id: 'tableSourceBox',
    source: sourceConfig,
    istype: '1', //表格选择报表类型
    type: type,
    project: {
      "id": projectid,
      "name": basereport.project
    }
  });

  //初使化图表 表格插件
  var chartReport = new dataExcel({
    id: 'tableBox',
    data: {}
  });

  //初使化表格 表格插件
  var tableReport = new dataExcel({
    id: 'tableReportBox',
    data: {}
  });

  window.chartSource = chartSource;
  window.tableSource = tableSource;

  //数据源选择按钮 事件处理 (图表)
  chartSource.select(function(obj, data) {
    obj.hide();
    $("#chartreport").find('button[data-option=dataSource]').show();
    $("#chartreport").next().show();
    //生成tablesgrade
    //验证图表是否是自定义曲线
    var custorType = $("#chartreport").find('select[name=chartType]').val();

    var defaults = JSON.parse(JSON.stringify(chartDefault));
    if (custorType == 'cursor_line') {
      defaults.header.push({
        key: "xaxis",
        name: "自定义x轴",
        width: '6%'
      });
    }
    var chartconf = chartReport.getConf();
    //如果存在配置文件  合并编辑信息
    if (chartconf) {
      chartconf = eval("(" + chartconf + ")");
      var chartInfo = chartSource.srcExcel(chartconf);
      chartInfo.udcconf = chartconf.udcconf;
      var srcchart = chartSource.getMte(chartInfo.group);
      var newchart = chartSource.getMte(data.group);
      if (srcchart.length != newchart.length && chartInfo.udcconf != '0' && chartInfo.udcconf !=
        undefined && chartInfo.udcconf != '') {
        $.messager.confirm('提示', '您选择的维度有变化，是否清除udc信息', function(r) {
          if (r) {
            defaults.data = getDataTable(data, defaults.header);
            chartHideSet(chartconf, defaults);
            //设置高级表格
            chartReport.setData(defaults);
          } else {
            data.udcconf = chartconf.udcconf;
            defaults.data = getDataTable(data, defaults.header);
            chartHideSet(chartconf, defaults);
            //设置高级表格
            chartReport.setData(defaults);
          }
        });
      } else {
        data.udcconf = chartconf.udcconf;
        defaults.data = getDataTable(data, defaults.header);
        chartHideSet(chartconf, defaults);
        //设置高级表格
        chartReport.setData(defaults);
      }
    } else {
      defaults.data = getDataTable(data, defaults.header);
      //设置高级表格
      chartReport.setData(defaults);
    }
    //还原图表表格高级数据
    $("#chartreport").find('#tableBox').show();
  });
  //数据源选择按钮 事件处理 (表格)
  tableSource.select(function(obj, data) {
    obj.hide();
    $("#reportgrade").find(".addsource").show();
    $("#reportgrade").find('input[name="tableconf"]').val(JSON.stringify(data));

    $('#reportgrade').next().show();

    //生成tablesgrade
    var srcDefault = JSON.parse(JSON.stringify(getDefaultData(data.type)));
    var timeData = JSON.parse(JSON.stringify(srcDefault.data));
    //contrast
    //var $chartconf = $('#'+tableReport.option.id).find('.chart_conf');
    //var chartconf = $chartconf.val();

    // if(chartconf && chartconf != ''){
    //     chartconf = JSON.parse(chartconf);
    //     if(!defaults.contrast){
    //         if(chartconf['contrast']){
    //             chartconf['contrast'] = null;
    //         }
    //     } else {
    //         if(!chartconf['contrast']){
    //             chartconf['contrast'] = JSON.parse(JSON.stringify(defaults.contrast));
    //         }
    //     }
    //     $chartconf.val(JSON.stringify(chartconf));

    // }

    var gradeconf = tableReport.getConf();
    //如果存在配置文件
    if (gradeconf != '') {
      var defaults = eval("(" + gradeconf + ")");
      //重新处理头部数据
      defaults.header = srcDefault.header;
      //重新处理对比
      if (data.type == 2) {
        if (!defaults.contrast) {
          defaults.contrast = srcDefault.contrast;
        }
      } else {
        defaults.contrast = srcDefault.contrast;
      }

      tableconf = defaults.data;
      var group = [],
        metric = [],
        udc = [],
        date = {};
      for (var i = 0; i < tableconf.length; i++) {
        if (tableconf[i].isgroup == 1 && tableconf[i].key != 'date') {
          group.push(tableconf[i]);
        }
        if (tableconf[i].isgroup == 3) {
          udc.push(tableconf[i]);
        }
        if (tableconf[i].isgroup == 2) {
          metric.push(tableconf[i]);
        }
        //提取时间列
        if (tableconf[i].key == 'date' && tableconf[i].name == '时间') {
          date = tableconf[i];
        }
      }
      var srctb = tableSource.getMte(group);
      var newtb = tableSource.getMte(data.group);
      if (srctb.length != newtb.length && udc.length > 0) {
        $.messager.confirm('提示', '您选择的维度有变化，是否清除udc信息', function(r) {
          if (r) {
            //设置高级表格
            defaults.data = getDataTable(data, defaults.header);
            if (!isEmptyObject(date)) {
              defaults.data.unshift(date);
            }

          } else {
            tmp = getDataTable(data, defaults.header);
            defaults.data = $.merge(tmp, udc);
            //设置高级表格
            if (!isEmptyObject(date)) {
              defaults.data.unshift(date);
            }

          }

          //如果设置了主表 副表 则默认设置不允许分页
          defaults = main_subtabulation(data.type, data.isaddmeter, defaults);
          tableReport.setData(defaults);
          tableReport.setConf(JSON.stringify(defaults));
          // 2016-12-21 在内部切换时要实现 TODO
          var sortItemList = [];
          $.each(defaults.data, function(index, item) {
            if (item.sort !== null) {
              var sortItem = {};
              sortItem.name = item.name;
              sortItem.order = item.sort;
              sortItem.key = item.key;
              sortItemList.push(sortItem);
            }
          });
          initSortList("clean", sortItemList);
        });
      } else {
        //设置高级表格
        tmp = getDataTable(data, defaults.header);
        defaults.data = $.merge(tmp, udc);
        if (!isEmptyObject(date)) {
          defaults.data.unshift(date);
        }
        //保存原有数据顺序,及操作信息
        defaults.data = dataSort(defaults.data, tableconf);
        //如果设置了主表 副表 则默认设置不允许分页
        defaults = main_subtabulation(data.type, data.isaddmeter, defaults);
        tableReport.setData(defaults);
        tableReport.setConf(JSON.stringify(defaults));
      }


    } else {
      srcDefault.data = getDataTable(data, srcDefault.header);
      //增加时间维度列
      if (timeData.length > 0) {
        srcDefault.data.unshift(timeData[0]);
      }
      //如果设置了主表 副表 则默认设置不允许分页
      srcDefault = main_subtabulation(data.type, data.isaddmeter, srcDefault);
      //设置高级表格
      tableReport.setData(srcDefault);
      tableReport.setConf(JSON.stringify(srcDefault));

      // 2016-12-21 在初始化时需要进行处理 TODO
      var sortItemList = [];
      $.each(srcDefault.data, function(index, item) {
        if (item.sort !== null) {
          var sortItem = {};
          sortItem.name = item.name;
          sortItem.order = item.sort;
          sortItem.key = item.key;
          sortItemList.push(sortItem);
        }
      });
      initSortList("clean", sortItemList);
    }
    //还原图表表格高级数据
    $("#reportgrade").find('#tableReportBox').show();
  });

  // sql检测事件 data=[{"uv":"","pv":""}];
  tableSource.checkSql(function(obj, data) {
    var $reportgrade = $("#reportgrade"),
      $tableconf = $reportgrade.find('input[name="tableconf"]');

    obj.hide();
    $reportgrade.find(".addsource").show();
    $reportgrade.next().show();
    //判断是否类型改变 清空chart_conf
    var oldsource_conf = $tableconf.val();
    if (oldsource_conf != '') {
      var oldsource = JSON.parse(oldsource_conf);
      if (oldsource.type != data.type) {
        tableReport.setConf("");
      }
    }

    var sqlDefault = getSqlDefaultData(data.type);
    sqlDefault.data = getDataTable(data, sqlDefault.header);
    //console.log(sqlDefault.data);
    sqlDefault = main_subtabulation(data.type, data.isaddmeter, sqlDefault);

    var gradeconf = tableReport.getConf();
    //如果存在配置文件
    if (gradeconf != '') {
      var gradejson = JSON.parse(gradeconf);
      sqlDefault.pubdata.pagesize = gradejson.pubdata.pagesize;
      //保存上次输入的内容表格数据
      var tableconf = gradejson.data,
        datalen = gradejson.data.length;
      for (var i = 0, len = sqlDefault.data.length; i < len; i++) {
        var tempkey = sqlDefault.data[i]['key'];
        for (var j = 0; j < datalen; j++) {
          if (gradejson.data[j]['key'] == tempkey) {
            sqlDefault.data[i] = JSON.parse(JSON.stringify(gradejson.data[j]));
          }
        }
      }

    }
    //console.log(sqlDefault);
    //设置高级表格
    tableReport.setData(sqlDefault);
    tableReport.setConf(JSON.stringify(sqlDefault));
    //还原图表表格高级数据

    $tableconf.val(JSON.stringify(data));
    $reportgrade.find('#tableReportBox').show();
    $reportgrade.find('.addColumn').hide();

  });

  //基本信息框
  $('#basereport').show().dialog({
    title: '基本信息设置',
    width: 450,
    //height:'',
    closed: true,
    cache: false,
    modal: true,
    buttons: [{
      text: '确定',
      iconCls: 'icon-ok',
      handler: function() {
        var baseConfig = {},
          $basebox = $('#basereport');
        //项目名称
        var projectinfo = $basebox.find('select[name="project"]').select2('data');
        baseConfig.project = projectinfo.id;
        baseConfig.projectid = $basebox.find('select[name="project"]').find(
          'option[value="' + baseConfig.project + '"]').attr('id');
        baseConfig.project_cn_name = $.trim(projectinfo.text);

        baseConfig.cn_name = $basebox.find('input[name=cn_name]').val();
        baseConfig.cn_name = $.trim(baseConfig.cn_name);
        baseConfig.wiki = $.trim($basebox.find('input[name=wiki]').val());
        // baseConfig.second_menu =$("#basereport").find('select[name=second_menu]').select2('val');
        baseConfig.group = ['all'];
        baseConfig.explain = $basebox.find('textarea[name=explain]').val();
        baseConfig.explain = $.trim(baseConfig.explain);

        if (baseConfig.project == 'filter_not') {
          $.messager.alert('提示', '请选择项目', 'info');
          return false;
        }
        if (baseConfig.cn_name == '') {
          $.messager.alert('提示', '报表名称不能为空', 'info');
          return;
        }
        //报表名称的校验
        var reg = /^[\u4e00-\u9fa5a-zA-Z0-9_()+]{1,20}$/;
        if (!reg.test(baseConfig.cn_name)) {
          $.messager.alert('提示', '报表名必须是中英文、数字、小括号或者下划线且不超过20个字符', 'info');
          return false;
        }

        //报表注释是否默认展开
        baseConfig.isexplainshow = $basebox.find('input[name="isexplainshow"]').is(
          ":checked") ? 1 : 0;



        // 项目名称 选取数据源
        $.ajax({
          type: "POST",
          url: '/report/getConfig',
          data: {
            "project": baseConfig.project
          },
          datatype: "JSON",
          success: function(result) {
            if (result == "null") {
              console.log('result:' + result);
              return false;
            }
            var results = JSON.parse(result);
            if (results.status == 0) {

              var result_dimensions = JSON.parse(results.data.dimensions),
                result_config = JSON.parse(results.data.config);
              if (result_dimensions && result_config) {
                params.sourceConfig['group'] = result_dimensions.data;
                params.sourceConfig['metric'] = result_config.data.project[0];
                window.chartSource.sourceconf = params.sourceConfig;
                window.tableSource.sourceconf = params.sourceConfig;

                var tempproject = {
                  id: baseConfig.projectid,
                  name: baseConfig.project
                };
                window.tableSource.project = window.chartSource.project =
                  tempproject;
                window.chartSource.init();
                window.tableSource.init();

                var html = "<p class='ptext'>项目名称：" + baseConfig.project_cn_name +
                  "</p><p class='ptext'>报表名称:" + baseConfig.cn_name;
                //html += " 所属菜单:" + baseConfig.first_menu +" >>"+ baseConfig.second_menu+"</p>";
                if (baseConfig.explain != '') {
                  html += "<p class='ptext'>报表说明:" + baseConfig.explain +
                    "</p>";
                }
                if (baseConfig.wiki != '') {
                  html += "<p class='ptext'>wiki:" + baseConfig.wiki + "</p>";
                }
                var obj = $('button[data-option=basereport]').next();
                obj.html("");
                obj.append(html);
                $('button[data-option=basereport]').addClass(
                  'btn-xs btnPosition');
                $basebox.dialog('close');
                //赋值
                basereport = baseConfig;
                $('button[data-option=timereport]').removeAttr('disabled');
                $('button[data-option=timecontrast]').removeAttr('disabled');
                basereport['auth'] = $('#basereport input.auth').is(":checked") ?
                  1 : 0;


              } else {
                $.messager.alert('提示', '该项目下没有纬度和指标,请您选择其他项目', 'info');
                return false;
              }

              //window.chartSource.metricSelect();
              //window.tableSource.metricSelect();
            }

          },
          error: function() {
            console.log('网络连接失败！');
          }
        });

      }
    }, {
      text: '取消',
      handler: function() {
        $('#basereport').dialog('close');
      }
    }]
  });
  //图表区域设置
  $('#chartreport').show().dialog({
    title: '图表区域设置',
    width: 900,
    //height:'',
    closed: true,
    cache: false,
    modal: true,
    buttons: [{
      text: '确定',
      iconCls: 'icon-ok',
      handler: function() {
        var data = chartReport.getData();
        var expObj = {};
        expObj.chartTitle = $('#chartreport').find('input[name=chartTitle]').val();
        expObj.chartType = $('#chartreport').find('select[name=chartType]').val();
        expObj.chartUnit = $('#chartreport').find('select[name=chartUnit]').val();
        expObj.chartWidth = $('#chartreport').find('select[name=chartWidth]').val();
        expObj.chartTop = $('#chartreport').find('select[name=chartTop]').val();

        var chartObj = $('#chartreport');
        if (chartObj.find('.chart_event').is(":visible")) {
          expObj.event = getEventInfo(chartObj.find('.eventData').find('li'));
        }
        //获取编辑信息
        var editInfo = $('#chartreport').find('button[data-option=dataSource]').attr(
          'data-conf');
        var chartsort = $('#chartreport').find('button[data-option=dataSource]').attr(
          'data-sort');
        if (params.chart != undefined && params.chart.length > 0) {
          for (var i = 0; i < params.chart.length; i++) {
            if (editInfo != undefined) {
              editObj = eval("(" + editInfo + ")");
              if (params.chart[i].chartconf[0].chartTitle == expObj.chartTitle &&
                params.chart[i].chartconf[0].chartTitle != editObj.chartconf[0].chartTitle
              ) {
                $.messager.alert("提示", '已经相同名称的图表，请不要重复添加', 'warning');
                return;
              }
            } else {
              if (params.chart[i].chartconf[0].chartTitle == expObj.chartTitle) {
                $.messager.alert("提示", '已经相同名称的图表，请不要重复添加', 'warning');
                return;
              }
            }
          }
        }
        //获取选择的维度
        expObj.name_hide = [], expObj.group = [];;
        expObj.chartData = [];
        expObj.xaxis = [];
        var udc = [],
          group = [];
        if (data) {
          for (var i = 0; i < data.data.length; i++) {

            if (data.data[i].xaxis == 1) {
              expObj.xaxis.push(data.data[i].key);
            }
            if (data.data[i].name_hide == 1) {
              //获取隐藏名称的指标
              if (data.data[i].isgroup != 1) {
                expObj.name_hide.push(data.data[i].key.split(".").join("_"));
              }
            } else {
              //获取维度
              if (data.data[i].isgroup == 1) {
                expObj.group.push(data.data[i].key);
              }
            }
            //获取指标
            if (!data.data[i].hide && data.data[i].isgroup == 2) {
              expObj.chartData.push(data.data[i].key);
            }
            //获取Udc
            if (!data.data[i].hide && data.data[i].isgroup == 3) {
              udc.push(data.data[i]);
            }

          }
        } else {
          return false;
        }
        //验证维度
        if (expObj.group.length < 1) {
          $.messager.alert("提示", '至少显示一个维度', 'info');
          return;
        }
        // var len = parseInt(udc.length) +  parseInt(expObj.chartData.length)  +  parseInt(group.length);
        // if( len  ==  expObj.name_hide.length ){
        //    $.messager.alert("提示",'图表名称不允许全部隐藏','info');
        //    return;
        // }
        //验证标题
        if (!expObj.chartType || !expObj.chartTitle) {
          $.messager.alert("提示", '图表标题或类型必填', 'info');
          return;
        }
        //验证宽度
        if (expObj.chartWidth == 'filter_not') {
          $.messager.alert("提示", '请设置图表显示状态', 'info');
          return;
        }
        //验证指标
        if (udc.length < 1 && expObj.chartData.length < 1) {
          $.messager.alert('提示', '请至少选一个指标!', 'info');
          return false;
        }
        //漏斗图表验证
        if (udc.length < 1 && expObj.chartData.length < 2 && expObj.chartType ==
          'funnel') {
          $.messager.alert('提示', '漏斗图至少需要2个指标!', 'info');
          return false;
        }
        //将 a.b.c 这样指标转换成 a_b_c形式
        if (expObj.chartData.length > 0) {
          for (var i = 0; i < expObj.chartData.length; i++) {
            var mStr = expObj.chartData[i];
            expObj.chartData[i] = mStr.split(".").join("_");
          }
        } else {
          expObj.chartData = [];
        }
        if (udc.length > 0) {
          var udcArr = [],
            udcStrArr = [];
          for (var j = 0; j < udc.length; j++) {
            var udcObj = {};
            expObj.chartData.push(udc[j].key);
            udcObj.cn_name = udc[j].name;
            udcObj.name = udc[j].key;
            udcObj.explain = udc[j].explain;
            udcObj.expression = udc[j].expression;
            udcObj.udc = udc[j].key + "=" + udc[j].expression;
            udcStrArr.push(udc[j].key + "=" + udc[j].expression);
            //替换udc
            //udcObj.udc = replaceUdc(udcObj.udc,excelMap);
            //udcObj.expression = replaceUdc(udcObj.expression,excelMap);
            udcArr.push(udcObj);
          }

        }
        var source = chartSource.getFakeCube();
        source = chartReport.getAllFakeCube(data, source);
        source.customSort = JSON.stringify([{
          "key": "date",
          "order": "desc"
        }]);
        source.chartconf = [];
        source.chartconf.push(expObj);
        if (udc.length > 0) {
          source.udc = udcStrArr.join(",");
          source.udcconf = encodeURIComponent(JSON.stringify(udcArr));
        }
        //判断指标是名称是否重复,避免画图出问题
        var nameCheck = [];
        for (var p = 0; p < data.data.length; p++) {
          if (data.data[p].hide != null && !data.data[p].hide) {
            var tpmkey = data.data[p].key.split(".").join("_");
            if (in_array(tpmkey, expObj.chartData)) {
              nameCheck.push(data.data[p].name);
            }
          }
        }
        var nary = nameCheck.sort();
        for (var i = 0; i < nameCheck.length; i++) {
          if (nary[i] == nary[i + 1]) {
            alert("指标重复名称：" + nary[i]);
            return;
          }
        }
        //检测指标隐藏情况  如果 group ==all  不允许隐藏指标
        if (source.group == 'all' && source.chartconf[0].name_hide.length > 0) {
          $.messager.alert('提示', '维度为系统生成时，指标名称不能隐藏!', 'info');
          return;
        }
        //验证饼图
        if (source.chartconf[0].chartData.length > 1 && source.chartconf[0].chartType ==
          'pie') {
          alert('饼图只能选择一个指标');
          //清空指标所选项
          $('#chartreport').find('.hand').removeClass('active');
          return false;
        }
        //source.udcconf= udcconf;
        //date_type hour小时级别的 day天 month月
        params.timereport = timereport;
        var dateview_type = (typeof(params) != 'undefined' && params.timereport &&
          params.timereport.dateview_type) ? params.timereport.dateview_type : "2";
        source.date_type = ["day", "hour", "day", "month"][dateview_type];

        if (editInfo != undefined) {
          params.chart[chartsort] = source;
        } else {
          params.chart.push(source);
        }

        $('button[data-option=chartreport]').hide();
        getChartBox(params.chart, $('.chartcontent'));
        chartAjax(params.chart, $(".chartcontent"), 1);
        $('#chartreport').dialog('close');
      }
    }, {
      text: '取消',
      handler: function() {
        $('#chartreport').dialog('close');
      }
    }]
  });
  //表格高级设置
  $('#reportgrade').show().dialog({
    title: '表格区域设置',
    width: 1020,
    //height:'',
    closed: true,
    cache: false,
    modal: true,
    buttons: [{
      text: '确定',
      handler: function() {
        var $reportgrade = $('#reportgrade');
        if ($reportgrade.find('#tableSourceBox').is(":visible")) {
          $.messager.alert('提示', '请先选择数据源', 'warning');
          return;
        }
        var type = $reportgrade.find('#tableSourceBox').find(
          'input[name="type"]:checked').val();
        type = parseInt(type);
        var title = $reportgrade.find('#tableSourceBox').find('input[name="title"]').val();
        var index = $reportgrade.find('#tableReportBox').attr('listindex');
        index = parseInt(index);
        var gradedata = tableReport.getData();
        if (!gradedata) {
          return false;
        }
        //判断聚合报表是否选择的数据源
        if (type == 7) {
          for (var i = 0, len = gradedata.data.length; i < len; i++) {
            if (gradedata.data[i].isgroup == 2) {
              if (gradedata.data[i].converge == undefined || gradedata.data[i].converge ==
                '') {
                $.messager.alert("提示", '聚合指标设置不完整', 'warning');
                return false;
              }
            }
          }
        }
        if (window.tables == undefined) {
          window.tables = [];
        }
        if (window.tables == undefined || (window.tables && !window.tables[index])) {
          //新建元素 新建报表时 的时候不需要创建

          var tag =
            "<div class='configBox'><div class='tabletitle'></div><div class='filter' index='" +
            index + "'></div>" +
            "<div class='tableBtn'><button class='btn btn-primary btn-xs editTable' data-option='reportgrade'>编辑</button>" +
            "<button class='btn btn-primary btn-xs deleteTable'>删除</button></div><div class='boxContent tablecontent'></div></div>";
          $('.tablelist').append(tag);


          var boxtag = $('.tablelist .configBox').eq(index);
          var tables = new Table({
            "params": params,
            "boxtag": boxtag,
            "isEdit": "1"
          });
          tables.bindEvent();
          window.tables.push(tables);
        }
        if (!params['tablelist']) {
          params['tablelist'] = [];
        }

        params.tablelist[index] = tableSource.getFakeCube();
        params.tablelist[index] = tableReport.getAllFakeCube(gradedata, params.tablelist[
          index]);
        params.tablelist[index]['title'] = title;
        params.tablelist[index]['type'] = type;
        params.tablelist[index]['master'] = index; //主表的标识: master:主表0 1副表

        //主表
        if (index == 0) {
          params.tablelist[index]['isaddmeter'] = $reportgrade.find('#tableSourceBox')
            .find('input[name="isaddmeter"]').is(":checked") ? "1" : "0";
        }
        params.timereport = timereport;
        var dateview_type = (typeof(params) != 'undefined' && params.timereport &&
          params.timereport.dateview_type) ? params.timereport.dateview_type : "2";
        params.tablelist[index].date_type = ["day", "hour", "day", "month"][
          dateview_type
        ];

        /* 主库默认不设置分页 */
        var templen = params.tablelist.length;

        //表格名称
        window.tables[index].table = params.tablelist[index];
        $tablecon = $(".tablecontent").eq(index);
        window.tables[index].init();
        editorOperate();
        /* switch(type){
             case 1:
                 window.tables[index].tableAjax(params.tablelist[index],$tablecon,1);
                 editorOperate();
                 break;
             case 2:
                 if(params.tablelist[index].grade.contrast.data.length < 1 ){
                     $.messager.alert('提示','至少选择一个对比值(例：当日值 dfdfd)','info');
                     return;
                 }
                 window.tables[index].tableContrast(params.tablelist[index],$tablecon,1);
                 editorContrast();
                 break;
             case 8:
                 window.tables[index].tableSqlData(params.tablelist[index],$tablecon,1);
                 editorOperate();
                 break;
             default:
                 window.tables[index].tableAjax(params.tablelist[index],$tablecon,1);
                 editorOperate();
         }*/
        //删除按钮
        $('.deleteTable').show();

        //目前只是支持表格2个
        var newlen = params.tablelist.length;
        if (params.tablelist[0]['isaddmeter'] == 0 || newlen >= 2) {
          $('#addtable').hide().find('.addTable').text('主表区域设置');
        } else if (newlen == 1) {
          $('#addtable').show().find('.addTable').text('添加副表');
        } else if (newlen > 1) {
          $('#addtable').hide();
        }

        $('#reportgrade').dialog('close');
      }
    }, {
      text: '取消',
      handler: function() {
        $('#reportgrade').dialog('close');
      }
    }]
  });
  //各种弹框操作
  $("body").on('click', '.btn', function(e) {
    var strid = $(this).attr('data-option');
    switch (strid) {
      case 'basereport':
        //还原数据
        break;
      case 'timereport':
        //还原数据
        var tipStr = "<br>(如果时间选择框为1天，那么查询时间的值为" + GetDateStr(-1) + "!)";
        $("#timereport").find('.tipinfo').html("");
        $("#timereport").find('.tipinfo').append(tipStr);
        break;
      case 'chartreport':
        clearChart(chartSource);
        break;
      case 'dataSource':
        var dataconf = $(this).attr('data-conf');
        if (dataconf != '' && dataconf != undefined) {
          chartReport.setConf(dataconf);
        }
        $("#chartreport").next().hide();
        $("#chartreport").find('#sourcebox').show();
        //$(this).hide();
        $("#chartreport").find('#tableBox').hide();
        break;
      case 'reportgrade':
        //设置数据源
        var index = $(this).closest('.configBox').index();
        var $reportgradebox = $('#reportgrade'),
          $tableSourceBox = $reportgradebox.find('#tableSourceBox'),
          $tableReportBox = $reportgradebox.find('#tableReportBox');

        if (isEmptyObject(params.tablelist)) {
          $reportgradebox.find('.addsource').hide();
          $tableSourceBox.attr('listindex', index).show().find('input[name="title"]').val(
            basereport.cn_name);
          $tableReportBox.attr('listindex', index).hide();
          $reportgradebox.find('.isaddmeterbox').show();
        } else {
          $('#reportgrade').next().show();
          tableSource.clear();
          $reportgradebox.find(".addsource").show();
          $('.chart_conf').val('');
          tableSource.title = params.tablelist[index].title;
          tableSource.master = params.tablelist[index]['master'] = index; //主表0 副表1,2,3
          tableSource.setSource(params.tablelist[index]);
          //设置高级表格
          var type = params.tablelist[index].type,
            srcDefault = null;
          if (type != 8) {
            srcDefault = getDefaultData(type);
            $tableSourceBox.find('.datasourcebox').show();
            $tableSourceBox.find('.custombox').hide();
          } else {
            srcDefault = getSqlDefaultData(type);
            srcDefault.pubdata = params.tablelist[index].grade.pubdata;
            $tableSourceBox.find('.datasourcebox').hide();
            $tableSourceBox.find('.custombox').show();
          }

          //处理数据兼容，功能 在不停的开发，但配置文件 保存
          params.tablelist[index].grade.header = srcDefault.header;

          if (params.tablelist.length > 1) {
            params.tablelist[0].grade['pubdata']['pagesize_disabled'] = "1";
          } else {
            params.tablelist[index].grade['pubdata']['pagesize_disabled'] = "0";
          }

          //高级设置
          tableReport.setData(params.tablelist[index].grade);
          var dataconf = JSON.stringify(params.tablelist[index].grade);
          if (type == 8) {
            $tableReportBox.find('.addColumn').hide();
          }
          tableReport.setConf(dataconf);
          $tableSourceBox.attr('listindex', index).hide().find('input[name="title"]').val(
            tableSource.title);
          $tableReportBox.attr('listindex', index).show();

        }
        break;
    }
    open(strid, e);
  });
  //设置数据源
  $('body').on('click', '.addsource', function() {
    var tmpData = tableReport.getData(0);
    if (!tmpData) {
      return;
    }
    $(this).hide();
    if (tmpData != undefined && tmpData.data.length > 0) {
      tableReport.setConf(JSON.stringify(tmpData));
    }
    $('#reportgrade').next().hide();
    //还原临时数据
    $("#reportgrade").find('#tableSourceBox').show();
    $("#reportgrade").find('#tableReportBox').hide();
  });
  // 图表========================================================================
  //图表切换事件
  $("#chartreport").on('change', 'select[name=chartType]', function() {
    var type = $(this).val();
    var parent = $(this).parent().parent().parent();
    $('#dataSource').hide();
    chartTypeSet(type, parent);
    chartSource.clear();
    $("#chartreport").next('.dialog-button').hide();
    $("#chartreport").next().hide();
    $("#chartreport").find('#sourcebox').hide();
    //$(this).hide();
    $("#chartreport").find('#tableBox').hide();
  });
  //增加图表
  $('body').on('click', '.addChart', function(e) {
    //显示dialog确定按钮
    $("#chartreport").next('.dialog-button').show();
    $("#chartreport").dialog('open');
    $("#chartreport").dialog("move", {
      top: e.pageY
    });
    $("#chartreport").next().hide();
    //清除编辑图表附带的信息
    $("#dataSource").hide();
    var sourceBtn = $("#chartreport").find('button[data-option=dataSource]');
    sourceBtn.removeAttr('data-conf');
    sourceBtn.removeAttr('data-sort');
    sourceBtn.show();
    clearChart(chartSource);
  })

  //图表编辑
  $('body').on('click', '.chartlist .chartedit', function(e) {
    $("#chartreport").next('.dialog-button').show();
    //清除数据源
    chartSource.clear();
    var index = $(this).parent().parent().parent().index();
    //还原图表本身数据
    var tmpChart = params.chart[index];
    chartSrc(tmpChart, index);
    //处理图表维度跟指示显示
    //判断是否新数据格式
    if (tmpChart.isnew != undefined && tmpChart.isnew == 1) {
      chartSource.setSource(tmpChart);
      //设置高级表格
      var srcDefault = JSON.parse(JSON.stringify(chartDefault));
      var custorType = $("#chartreport").find('select[name=chartType]').val();
      if (custorType == 'cursor_line') {
        srcDefault.header.push({
          key: "xaxis",
          name: "自定义x轴",
          width: '6%'
        });
      }
      //处理数据兼容，功能 在不停的开发，但配置文件 保存
      params.chart[index].grade.header = tmpChart.grade.header = srcDefault.header;
      params.chart[index].grade.pubdata = tmpChart.grade.pubdata = srcDefault.pubdata;
      var defaults = tmpChart.grade;
      chartReport.setData(defaults);
    } else {
      //还原表格高级数据
      var chartInfo = chartSource.srcExcel(params.chart[index]);
      chartInfo.udcconf = params.chart[index].udcconf;
      //引用图表默认配置模板
      var defaults = JSON.parse(JSON.stringify(chartDefault));
      var custorType = $("#chartreport").find('select[name=chartType]').val();
      if (custorType == 'cursor_line') {
        defaults.header.push({
          key: "xaxis",
          name: "自定义x轴",
          width: '6%'
        });
      }
      //重新处理指标显示或隐藏
      defaults = tableToNewData(chartInfo, params.chart[index], defaults);
      //设置数据源
      chartSource.setSource(params.chart[index]);
      //还原用户选中的指标
      defaults = chartHideSet(params.chart[index], defaults);
      //设置高级表格
      chartReport.setData(defaults);
    }
    //显示数据源按钮
    $("#chartreport").find('button[data-option=dataSource]').show();
    //显示表格插件框
    $("#chartreport").find('#tableBox').show();
    //隐藏数据源框
    $("#chartreport").find('#sourcebox').hide();
    $("#chartreport").dialog('open');
    $("#chartreport").dialog("move", {
      top: e.pageY
    });
  });
  //图表删除
  $('body').on('click', '.chartlist .chartclose', function() {
    $(this).removeAttr('style');
    $(this).removeClass('active');
    var obj = $(this);
    $.messager.confirm('提示', '确定删除吗？', function(r) {
      if (r) {
        var index = obj.parent().parent().parent().index();
        console.log(index);
        obj.parent().parent().parent().remove();
        params.chart.splice(index, 1);
      }
    });
  });

  $('body').off('click', '.addEvent');
  $('body').on('click', '.addEvent', function() {

    var id = $(this).prev().val();
    var text = $(this).prev().find('option:selected').text();
    var obj = $("#chartreport").find('.eventData');
    if (obj.length > 0) {
      var len = obj.find('li[event_id=' + id + ']').length;
      if (len > 0) {
        $.messager.alert('提示', '不能重复添加', 'warning');
        return;
      }
    }
    var html = "";
    if (id != '' && text != '') {
      html += "<li event_id='" + id +
        "' class='error_showmsg' style='display:block;width:360px'>";
      html += "<span class='text'>" + text +
        "</span><span class='error_close'></span></li>";
    }

    obj.append(html);
    obj.dragsort("destroy");
    obj.dragsort({
      dragSelector: "li",
      dragSelectorExclude: "select,button,input,textarea,b,small",
      scrollSpeed: 0,
    });
  });
  //删除事件
  $('.eventData').on("click", '.error_close', function() {
    var parent = $(this).parent();
    parent.remove();
  });
  //添加表格
  $('body').on('click', '.addTable', function(e) {
    tableSource.clear();
    var index = params.tablelist ? params.tablelist.length : 0;
    if (index != '0') {
      $("#reportgrade").find('#tableSourceBox .isaddmeterbox').hide();
    }
    if (index >= 2) {
      $.messager.alert('提示', '最多只能添加1个副表格', 'warning');
      return false;
    }
    $('#reportgrade').next().hide();
    $('#reportgrade').find('.addsource').hide();
    $("#reportgrade").find('#tableSourceBox').attr('listindex', index).show().find(
      'input[name="title"]').val(basereport.cn_name);
    $("#reportgrade").find('#tableReportBox').attr('listindex', index).hide();
    open('reportgrade', e);
    $(this).parent('#addTable').hide();

  });

  // 删除表格
  $('body').on('click', '.deleteTable', function() {
    var index = $(this).closest('.configBox').index(); //主表0 副表1,2,3
    //显示dialog确定按钮
    var len = params.tablelist ? params.tablelist.length : 0;
    var _this = this;
    $.messager.confirm('提示', '确定删除吗？', function(r) {
      if (r) {
        if (len >= index) {
          params.tablelist.splice(index, 1);
          window.tables.splice(index, 1);
          $(_this).closest('.configBox').remove();
        }
        //主表 副表 第一个主表不能设置页码
        var templen = params.tablelist.length;
        if (templen > 1) {
          params.tablelist[0].grade.pubdata['ispagesize'] = 0;
          params.tablelist[0].grade.pubdata['pagesize_disabled'] = 0;
        } else if (templen == 1) {
          params.tablelist[0].grade.pubdata['ispagesize'] = 1;
        }

        var newlen = params.tablelist.length;
        if (newlen == 1) {
          $('#addtable').show().find('.addTable').text('添加副表');
        } else if (newlen >= 2) {
          $('#addtable').hide().find('.addTable').text('添加副表');
        } else if (newlen == 0) {
          $('#addtable').show().find('.addTable').text('主表区域设置');
        } else {
          $('#addtable').hide().find('.addTable').text('主表区域设置');
        }

      }
    });

  });


  //图表插件快捷时间选择样式事件
  $('body').on('click', '.btn-group .btn', function() {
    if ($(this).attr('rul') == undefined) {
      setActive($(this), true);
    }
  });
  // 图表=========================================================================
  //时间类型切换
  $('body').on('change', '.date_type', function() {
    if ($(this).val() == 1) {
      $("#timereport").find('.single').show();
      $("#timereport").find('.interval').hide();
    } else {
      $("#timereport").find('.single').hide();
      $("#timereport").find('.interval').show();
    }
  });
  //保存成功提示信息
  $('#tipInfo').show().dialog({
    title: '提示',
    width: 300,
    //height:'',
    closed: true,
    cache: false,
    //modal: true,
    buttons: [{
      text: '确定',
      handler: function() {
        var idstr = $("#tipInfo").find('[name=id]').val();
        window.location.href = '/report/editorreport/' + idstr;
      }
    }]
  });

  function saveAjax() {
    // type = 5 为混合型报表
    var type = 1;
    var templen = params.tablelist.length;
    if (templen == 1) {
      type = params.tablelist[0].type;
    } else if (templen > 1) {
      type = 5; //主副型报表
    } else {
      type = 6; //图表
    }

    if (templen > 1) {
      params.tablelist[0].grade.pubdata['ispagesize'] = 0;
      params.tablelist[0].grade.pubdata['pagesize_disabled'] = 0;
      params.tablelist[0].isaddmeter = "1";
    }
    var url = '/report/savereport';
    var allconf = {
      'basereport': basereport,
      'timereport': timereport,
      'chart': params.chart,
      'tablelist': params.tablelist,
      'id': id,
      'type': type
    }
    $.post(url, {
      'params': JSON.stringify(allconf)
    }, function(data) {
      if (data.status == 0) {
        var tipstr = "<h5>操作成功！</h5><br/>";
        tipstr += '<a href="/menu/index">设置报表所属菜单</a><br/>';
        tipstr += '<a target="_blank" href="/report/showreport/' + data.data +
          '">查看报表</a><br/>';
        tipstr += '<a href="/report/reportlist">返回报表管理页面</a>';

        $("#tipInfo").find('.box').html(tipstr);
        $("#tipInfo").find('[name=id]').val(data.data);
        if (id) {
          $.messager.alert('提示', tipstr, 'info');
        } else {
          $('#tipInfo').dialog('open');
        }
        //$.messager.alert('提示',tipstr,'info');
        $('#visualBox').dialog('close');
      } else {
        $.messager.alert('提示', data.msg, 'warning');

      }
    }, 'json');
  }
  //保存操作
  $('.saveConfig').click(function() {
    //验证图表
    var status = 1;
    if (params.chart.length > 0) {
      if (params.chart.length % 2 != 0) {
        var lastchart = params.chart[params.chart.length - 1];
        if (lastchart != undefined && lastchart.chartconf[0].chartWidth == 50) {
          status = 0;
          $.messager.confirm('提示', '您的报表设置最后的一个图只占了一行的一半,显示会比较难看，确认操作吗？', function(r) {
            if (r) {
              status = 1;
              saveAjax();
            }
          });
        }
      }
    }
    if (!status) {
      return;
    }

    if (params.chart.length == 0 && params.tablelist.length == 0) {
      $.messager.alert('提示', '您需要设置至少一个图表或者表格', 'warning');
      return false;
    }
    saveAjax();
  });
  //预览操作
  $('.previewConfig').click(function() {
    // type = 5 为混合型报表
    // type = 5 为混合型报表
    var type = 1;
    var templen = params.tablelist.length;
    if (templen == 1) {
      type = params.tablelist[0].type;
    } else if (templen > 1) {
      type = 5; //主副型报表
    } else {
      type = 6; //图表
    }

    if (templen > 1) {
      params.tablelist[0].grade.pubdata['ispagesize'] = 0;
      params.tablelist[0].grade.pubdata['pagesize_disabled'] = 0;
      params.tablelist[0].isaddmeter = "1";
    }

    var postForm = document.createElement("form"); //表单对象
    postForm.method = "post";
    postForm.action = '/report/preview';
    var preview = {};
    preview.basereport = basereport;
    preview.timereport = timereport;
    preview.chart = params.chart;
    preview.tablelist = params.tablelist;
    preview.type = type;
    postForm.setAttribute("target", "_blank");
    var base = document.createElement("input"); //email input
    base.setAttribute("name", "preview");
    base.setAttribute("type", "hidden");

    base.setAttribute("value", JSON.stringify(preview));
    postForm.appendChild(base);
    document.body.appendChild(postForm);
    postForm.submit();
    $(postForm).remove();
  });

});
