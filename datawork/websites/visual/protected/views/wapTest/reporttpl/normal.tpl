<script src="/assets/js/wapTest/search.js?version={/$version/}"></script>
{/include file="wapTest/visualtpl/search.tpl"/}
<div style='width:98%;margin:auto' id='canvas_down'>
    <div class="reportexplainbox">
        <div class="arrow_box"></div>
        <span class="reportexplaincon"></span>
    </div>
    <!--  <div class="tooltip bottom reportexplainbox" role="tooltip">
         <div class="tooltip-arrow"></div>
         <div class="tooltip-inner reportexplaincon">

         </div>
      </div> -->
    <div id='search'></div>
    <div id='chartTpl'></div>
    <!-- chart 折叠 -->
    <div class='clearfix'></div>
    <div class="foldcontent close"><span>数据图</span></div>
    <div class="fold web-fold" style="display:none">
        <a href="javascript:void(0);" class="open close">
        </a>
    </div>
    <div class='clearfix'></div>
    <!-- 多个表格 -->
    <div class="tablelist">
        <!-- 自定义指标隐藏列 衍生报表id-->
        <!--<div class="customkey" style="display:none">
             <ul class="nav nav-tabs my-nav-customkey closed" role="tablist">
                 <li role="presentation" class="active my-nav-customtitle closed">
                     <a href="" class="customtitle">自定义显示</a></li>
             </ul>
             <div class="my-tab-customkey">
                 <div class="row" id="my-tab-customcon">
                       <label class="col-sm-2">时间</label>
                       <div class="col-sm-10">
                          <label class="my-customlist"><input type="checkbox" title="时间" value="date" >时间</label>
                       </div>

                 </div>

             </div>
         </div>-->
        <!--错误信息显示-->
        <!--<div class="error_showmsg">
            <div class="text"></div>
            <a class="error_close"></a>
        </div>-->
        <!-- 自定义指标隐藏列 -->
        <!-- <div id='filter'>
             <div id='searchContent'>
             </div>
         </div>
         <div id="result"></div>-->
    </div>
    <!--多个表格-->
</div>
<div id='downtpl' style="display:none;">
    <div style="padding: 10px 10px 30px 10px">
        <div>文件类型：
            <input name="downaa" value=2  checked="checked"  type="radio" id="excel"/> excel &nbsp;
            <input name="downaa" value=1  type="radio" id="png"/> png
        </div>
        <div class="excel_con">
            <!--<input type="checkbox" name="tablename1" value="报表名称1" /><lable for="tablename1">报表名称1</lable><br/>
            <input type="checkbox" name="tablename2" value="报表名称2" /><lable for="tablename2">报表名称2</lable>-->
        </div>
        <div class="down_error" style="color:red">请选择下载的报表</div>
        <form action="/visual/img" id='imgSub' method="post">
            <input type='hidden'  name="data" />
            <input type='hidden' name="report_id">
            <input type='hidden' name="name" />
        </form>
    </div>
</div>
<script type="text/javascript">


    {/if  $params  neq ''/}
    var params = {/$params/};
    var mylocalParams = {/$params/};
    {/if $config neq '' /}
    var config = {/$config/};
    {/else/}
    var config = 0;
    {//if/}
    {/if  $confArr  neq ''/}
    var report_id = {/$confArr.id/};
    var  groupkey =  '{/$confArr.group/}';
    var  metrickey = '{/$confArr.metric/}';
    {//if/}
    var deviceType   = browserRedirect();
    if(deviceType){
        if( JSON.stringify(params.chart) !=undefined ){
            if( params.chart.length >0){
                for(var i=0,len = params.chart.length; i< len; i++){
                     params.chart[i].chartconf[0].chartWidth = 100;
                }
            }
        }
    }
    //获取报表维度与指标的具体信息
    if(config  && groupkey !=''  && metrickey !='' ){
        var  projectInfo = config.data.project[0].categories;
        var  groupArr  =  groupkey.split(",");
        var  metricArr =  metrickey.split(",");
        var  tableInfo ={};
        tableInfo.group =[];
        var tmpG =[],tmpM =[];
        tableInfo.metric =[];
        //获取维度
        for(var i=0; i< projectInfo.length; i++){
            for( j=0;  j< projectInfo[i].groups.length ; j++){
                var groups = projectInfo[i].groups[j].dimensions;
                for( var x=0;  x<  groups.length ; x++){
                    if( in_array(groups[x].name,groupArr)  &&  !in_array(groups[x].name,tmpG)){
                        var  one ={};
                        one.key = groups[x].name;
                        one.name = groups[x].cn_name;
                        one.explain = groups[x].explain;
                        tableInfo.group.push(one);
                        tmpG.push(groups[x].name);
                    }
                }
                var metric = projectInfo[i].groups[j].metrics;
                for( var y=0;  y<  metric.length ; y++){
                    var tmpkey = projectInfo[i].name +"."+ projectInfo[i].groups[j].name +"."+  metric[y].name;

                    if( in_array(tmpkey,metricArr)  &&  !in_array(tmpkey,tmpG)){
                        var  one ={};
                        one.key = tmpkey;
                        one.name = metric[y].cn_name;
                        one.explain = metric[y].explain;
                        tableInfo.metric.push(one);
                        tmpM.push(tmpkey);
                    }

                }

            }
        }
    }
    //设置标题
    if( params.basereport.cn_name  !=undefined){
       // $('title').text(params.basereport.cn_name +"-小猪数据分析平台") ;
       // 兼容ie8 设置title
        document.title=params.basereport.cn_name +"-小猪数据分析平台";
    }

    {//if/}

    $(function(){
        //获取图表
        if(typeof(params)!= "undefined"){
            //报表注释
            var explain = (params&&params.basereport.explain) ? params.basereport.explain : '';
            $('.reportexplaincon').html(explain);

            //报表注释是否显示 2015-06-01
            if(params.basereport && params.basereport.explain){
                $('.navtab-reportexplain').show();
            }

            if(params['tablelist'] && params['tablelist'].length >0 && config ){
                params['tablelist'] = tablelistReplace(params.tablelist);
            }

            if(params['table']) {
                oldFromNew(tableInfo);
                fakeCubeSort(params.tablelist);
            }
            var toolbar = new ToolBar({"params":params,"boxtag":"#search"});
            window.tables = [];

            if(params.chart !=undefined) {
                getChartBox(params.chart, $("#chartTpl"));
                chartAjax(params.chart, $("#chartTpl"));
            }

            if(params.chart !=undefined && params.chart.length > 0){
                $('.fold').show();
            } else {
                $('.fold').hide();
            }

            if(params.tablelist){
                var tablesobj = {}, len = params.tablelist.length, tag='';
                var downstr = '', downtablestr ='<label class="down">请选择需要下载的报表</label><br/>';
                if(len > 0){

                    for(var i=0; i<len; i++){
                        //下载数据title统一获取，当只有一个报表的时候下载表名需要与tab一致
                        var title_name=params.tablelist[i].title;
//                        if(len>1){
//                            title_name=params.tablelist[i].title;
//                        }else{
//                            title_name=params.basereport.cn_name;
//                        }
                        tag+="<div class='configBox'><div class='tabletitle'></div><div class='error_showmsg'><div class='text'></div><a class='error_close'></a></div>" +
                                "<div class='filter'></div><div class='boxContent tablecontent'></div></div>";
                        //下载报表名称
                        downstr+="<form method='post' action='/visual/downData' id='downData"+i+"'  class='downData' title='"+title_name+"'><input type='hidden' name='downConfig' value='"+encodeURIComponent(JSON.stringify(params.tablelist[i]))+"'/>" +
                                "<input type='hidden' name='report_title' value='"+title_name+"'/></form>";

                        downtablestr+='<input type="checkbox" name="tablename'+i+'" value="'+title_name+'" checked /><lable for="tablename'+i+'">'+title_name+'</lable><br/>';
                    }
                } else {
                    downtablestr = '<span style="color:red">暂无表格</span>';
                }

                $('.tablelist').html(tag);
                $('.downclick').append(downstr);
                $('.excel_con').html(downtablestr);

                var $configbox = $('.tablelist').find('.configBox');
                for(var i =0; i<len; i++){
                    tablesobj = new Table({"table":params.tablelist[i],"boxtag":$configbox.eq(i),"mylocalParams":mylocalParams,"isEdit":"0"});
                    tablesobj.bindEvent();
                    // toolbar.bindEvent(tablesobj);
                    //自定义显示列 params  id
                    tablesobj.customkey(params);
                    window.tables.push(tablesobj);
                }
                //如果一个报表不显示表格title
                if (len == 1){
                    $('.tabletitle').hide();
                    $('.my-nav-customkey').css({"height":"24px"}).find('.my-nav-customtitle').css({"top":"1px"});
                }
            }

            toolbar.bindEvent(window.tables);

            //tab 报表注释
            if(typeof(params)!= 'undefined' && params.basereport.isexplainshow && params.basereport.isexplainshow==1){
                if($('.nav.nav-tabs .active .navtab-reportexplain').length>0 && explain!=''){
                    var left = $('.nav.nav-tabs .active .navtab-reportexplain').offset().left-210;
                    $('.reportexplainbox').slideToggle(400).find('.arrow_box').css({'left':left+'px'});
                }

            }

            $('body').on('click','.nav-tabs span.navtab-reportexplain',function(event){
                event.preventDefault();
                var pageX = parseInt(event.pageX);
                var left = pageX-212;
                var $box = $('.reportexplainbox');
                var style = $box.attr('style');
                $box.slideToggle(400).find('.arrow_box').css({'left':left+'px'});
            });

            //chart 显示隐藏
            var firstTime = true;
            $('body').on('click','.fold a',function(){
                $(this).toggleClass('close');
                $('#chartTpl').slideToggle(400);
                $('.foldcontent').toggleClass('close');
                setTimeout(function(){
                  // 2016.11.21 模拟点击以实现重新绑定pin事件的作用
                  if(firstTime){
                    $("#scroll").click();
                    firstTime = false;
                  }else{
                    $("#scroll").click();
                    $("#scroll").click();
                  }
                },400);
            });
        }

    });

    {/if  $downpic  eq 1/}
    //识别图片抓取
    setTimeout('downpic()',3000);
    function downpic(){
        $('body div').not('#chartTpl div').hide();
        $('nav').hide();
        $('#canvas_down').show();
        $('#chartTpl').show();
        $('.chartHeader').hide();
    }
    {//if/}

</script>
