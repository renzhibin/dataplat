<script src="/assets/js/search.js?version={/$version/}"></script>
{/include file="visualtpl/search.tpl"/}
<script type="text/javascript">
    var easyInfo = {/$easyInfo/};
</script>
<div style='width:98%;margin:auto' >
    <div class="navbar navbar-default reportexplainbox">
        <span class="reportexplaincon"></span>
    </div>
    <div id='search'></div>

    <!-- tab 表 -->
    <ul class="nav nav-tabs nav-tablist" role="tablist"">
    <li role="presentation" class="active col-xs-6 col-md-6"><a href="#tabTpl" aria-controls="tabTpl" role="tab" data-toggle="tab">表格数据</a></li>
    <li role="presentation" class="col-xs-6 col-md-6"><a href="#chartTpl" aria-controls="chartTpl" role="tab" data-toggle="tab">图表数据</a></li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <!-- tab 数据表 -->
        <div role="tabpanel" class="tab-pane active" id="tabTpl">
            <div class='clearfix'></div>
            <div id='filter'>
                <div id='searchContent'>
                </div>
            </div>
            <div id="result"></div>
        </div>

        <!-- tab 数据图 -->
        <div role="tabpanel" class="tab-pane" id="chartTpl">
            <div id='twitterChart'>{/$charthtml/}</div>
        </div>
    </div>

</div>
<div class="fixed_footer">
    <div class="row">
        <div class="col-xs-3 col-md-3 limd"><a href="/wap/index"><i class="glyphicon glyphicon-home"></i></a></div>
        <div class="col-xs-3 col-md-3 limd"><i class="glyphicon glyphicon-time"></i></div>
        <div class="col-xs-3 col-md-3 limd"><a href="/wap/collect"><i class="glyphicon glyphicon-heart"></i></a></div>
        <div class="col-xs-3 col-md-3 limd"><a href="/wap/recently"><i class="glyphicon glyphicon-star"></i></a></div>
    </div>
</div>

<script type='text/x-dot-template' id="tablelisttmp">
    <table class="easyui-datagrid" id="tablelist"
           data-options="singleSelect:true,collapsible:true">
        <thead data-options="frozen:true">
        <tr>
            <th data-options="field:'dt',width:80">时间</th>
        </tr>
        </thead>
        <thead>
        <tr>
            {{ for(var key in it.easyHeader){  }}
            <th data-options="field:'{{=key}}',align:'center'">{{=it.easyHeader[key]}}</th>
            {{ } }}
        </tr>
        </thead>
    </table>
</script>
<script type="text/javascript">
    {/if  $params  neq ''/}
    var params = {/$params/};
    var reportconfig = '{/$confArr.params.config/}';
    //设置标题
    if( params.basereport.cn_name  !=undefined){
        $('title').text(params.basereport.cn_name +"-趣店数据分析平台") ;
    }

    //加载search页面
    /*var interText = doT.template($("#searchtmpl").text());
     $("#search").html(interText(params));
     $('.showinfo').tooltip({ 'position':'top'});

     var dateview_type = (params.timereport.dateview_type) ? params.timereport.dateview_type:2;

     if(params.timereport.date_type ==1){
     var edate = getOffset(params.timereport.offset,"0",dateview_type);

     $('input[name=endTime]').val(edate);
     }else{
     var sdate = getOffset(params.timereport.offset,params.timereport.interval,dateview_type);
     var edate = getOffset(params.timereport.offset,"0",dateview_type)

     $('input[name=startTime]').val(sdate);
     $('input[name=endTime]').val(edate);
     }*/

    var toolbar = new ToolBar({"params":params,"boxtag":"#search"});
    toolbar.scrollbox();

    //加载表格
    var interText = doT.template($("#tablelisttmp").text());
    $("#result").html(interText(easyInfo));
    $('#tablelist').datagrid();
    if(easyInfo.easyData){
        $('#tablelist').datagrid('loadData',easyInfo.easyData);
    }
    {//if/}

    function getInfo(configall){
        ajaxUrlArr = getJsonStr();
        $('body').mask('数据正在加载...');
        var url = '/visual/ContrastSearch';
        $.post(url,{'configall':configall,'srcSecting':ajaxUrlArr[0]},function(data){
            $('body').unmask();
            if(data.status == 0){
                var interText = doT.template($("#tablelisttmp").text());
                $("#result").html(interText(data.data.easyInfo));
                $('#tablelist').datagrid();
                $('#tablelist').datagrid('loadData',data.data.easyInfo.easyData);
                createChart(data.data.chartInfo);
            }else{
                $.messager.alert('提示',data.msg,'warning');
            }
        }, 'json');
    }
    $(function(){
        ajaxUrlArr = getJsonStr();
        if( ajaxUrlArr == 0){
            return;
        }else{
            intAjax(0,ajaxUrlArr);
        }
        var formtag = "<form method='post' action='/visual/contrastdown' id='downData' >" +
                "<input type='hidden' name='downConfig' value='"+reportconfig+"'/>" +
                "<input type='hidden' name='report_title' value='"+params.basereport.cn_name+"'/></form>";
        $('.downclick').append(formtag);

        $('.nav-tablist a:last').click(function (e) {
            e.preventDefault();
            $('#twitterChart').css({"height":"auto"});
            $(this).tab('show');
        })

        $('body').on('click','.downclick',function(){
            var downObj = {};
            downObj.date = $('input[name=startTime]').val();
            downObj.edate = $('input[name=endTime]').val();
            downObj.config = reportconfig;
            $('#downData').find('input[name=downConfig]').val(JSON.stringify(downObj));
            $('#downData').find('input[name=report_title]').val(params.basereport.cn_name);
            $('#downData').submit();
        });
        //收藏功能
        $('body').on('click','.collclick',function(){
            var id = $(this).attr('data-id');
            if(id ==0){
                $.messager.alert('提示','概览的报表不用收藏','info');
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
        //快捷时间选择
        $('body').on('click','.btn-special',function(){
            var dateview_type = (typeof(params) != 'undefined' && params.timereport.dateview_type) ? params.timereport.dateview_type:2;
            var date_typearr = ["day","hour","day","month"];
            var  num = $(this).attr('data-option');
            $(this).addClass('active').siblings().removeClass('active');
            num = parseInt("-"+num);
            $('input[name=startTime]').val(GetDateStr(num,dateview_type));
            $('input[name=endTime]').val(GetDateStr(-1,dateview_type));
            var configall ={};
            configall.date = GetDateStr(num,dateview_type);
            configall.edate = GetDateStr(-1,dateview_type);
            configall['date_type'] = date_typearr[dateview_type];
            configall.config  = params.config;
            getInfo(configall);
        });
        $('.selectChange').select2({allowClear:true});

        //时间切换
        $('.datepicker').on('changeDate',function(ev){
            var configall ={};
            var dateview_type = (typeof(params) != 'undefined' && params.timereport.dateview_type) ? params.timereport.dateview_type:2;
            var date_typearr = ["day","hour","day","month"];

            configall.date = $('input[name=startTime]').val();
            configall.edate =  $('input[name=endTime]').val();
            configall['date_type'] = date_typearr[dateview_type];

            if(configall.date.valueOf() > configall.edate.valueOf()){
                $.messager.alert('提示','开始时间大于结束时间','warning');
                return;
            }
            configall.config  = params.config;
            getInfo(configall);
        });

        //报表注释
        if(typeof(params)!='undefined' ){
            var explain = (params&&params.basereport.explain) ? params.basereport.explain : '';
            $('.reportexplaincon').html(explain);
            //报表注释是否显示 2015-06-01
            if(params.basereport && params.basereport.explain){
                $('.reportexplain').show();
            }
        }

        //tab 报表注释
        $('body').on('click','.nav-tabs span.navtab-reportexplain',function(event){
            event.preventDefault();
            var pageX = parseInt(event.pageX);
            var left = pageX-247;
            var $box = $('.reportexplainbox');
            var style = $box.attr('style');

            if(typeof(style)!='undefined' && style ==  'display: block;'){
                $box.slideUp(200);
            } else {
                $box.slideDown().find('.arrow_box').css('left',left+'px');
            }
        });

    });
</script>
