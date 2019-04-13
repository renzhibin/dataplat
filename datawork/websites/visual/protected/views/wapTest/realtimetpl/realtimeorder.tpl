{/include file="layouts/lib.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}"
      xmlns="http://www.w3.org/1999/html">

<script src="/assets/js/project.js?version={/$version/}"></script>
<!-- ECharts单文件引入 -->
<!-- 测试注释 -->
<script src="/assets/lib/echarts-2.1.10/build/dist/echarts.js"></script>

<style type="text/css">
    body{
        background: #fff;
    }
    .order {
        display: inline-block;
        margin: 30px 0 -30px;
        text-align: left;
        font-size: 14px;
        line-height: 36px;
        background-color: #e6e6e6;
        padding: 0 10px;
        width: 33%;
    }
    .numPanel{
        text-align: center;
        margin-top: -10px;
        padding:  0 75px 0 30px;
    }
    .order .num{
        font-size: 16px;
        font-weight: bold;
    }
    #content#special{
        background-color: #fff;
        margin-left: 0;

    }
    .note{
        margin: 0 78px;
        width: 170px;
    }
    .predata {
        /*margin: 220px 78px 10px 78px;*/
        padding: 10px 10px;
        /*color: #ABAFB8;
        background-color: #e6e6e6;*/
        color: #ffffff;
        background-color: #ffffff;
        font-size: 14px;
    }

    @media screen and (max-width: 768px) {
        .order {
            display: inline-block;
            margin: 5px 0;
            text-align: left;
            font-size: 14px;
            line-height: 36px;
            background-color: #e6e6e6;
            padding: 0 10px;
            width: 49%;
        }
        .order.long{
            width: 100%;
        }
    }
</style>
<div>
    <div id="content special" class="content">
        <div class="rightreport">
        <!--面包屑效果-->
        <div id="breadcrumbs-one">
            {/foreach from = $guider item= place key=key/}
            {/if $guider[0] eq $place /}
            <span><a href="{/$place.href/}">{/$place.content/}</a></span>
            {/else/}
            {/if $place.href eq '#'/}
            <span>></span><span>{/$place.content/}</span>
            {/else/}
            <span>></span><span><a href="{/$place.href/}">{/$place.content/}</a></span>
            {//if/}
            {//if/}
            {//foreach/}
        </div>
        <div style='height:10px'></div>
        <div class='container'>
            <div class="numPanel">
                <div class="order long">今日预测-北京(更新时间:  {/$resultList.maxpre.hour/}:00)<br/><span class="num">
                  {/if $resultList.maxpre.prediction /}
                      {/$resultList.maxpre.prediction/}
                  {/else/}
                      0
                  {//if/}
                </span>&nbsp;单</div>
                <div class="order">昨日订单量<br/><span class="num">
                  {/if $resultList.yesorder/}
                      {/$resultList.yesorder/}
                  {/else/}
                      0
                  {//if/}
                </span>&nbsp;单</div>
                <div class="order">上周同期<br/><span class="num">
                  {/if $resultList.lastorder/}
                      {/$resultList.lastorder/}
                  {/else/}
                      0
                  {//if/}
                </span>&nbsp;单</div>
            </div>
            <!-- 为ECharts准备一个具备大小（宽高）的Dom -->
            <div id="chartPanel" style="height:400px;margin-top: -30px;"></div>
            <!--<div class="note">注：1时为0~1时</div>-->
            <!--
            <div class="predata">未来预测（更新时间：{/$resultList.future.data[(count($resultList.future.data)-1)].dt/}） <br/>
                {/foreach $resultList.future.data as $time/}
                <div class="num" style="margin: 10px 0">
                    {/$time.pre_dt/}: <span style="font-weight: bold">{/$time.prediction/} </span>单&nbsp;&nbsp;
                </div>
                {//foreach/}
                <div>备注： {/$resultList.future.intro/}</div>
            </div>
            -->
            <div class="predata">
                {/foreach $inventory as $time/}
                    {/$time.dt/} {/$time.hour/} 时 : {/$time.tag/} {/$time.name/}<br>
                {//foreach/}
            </div>
            <div class="predata">未来预测（更新时间：{/$resultList.future.data[(count($resultList.future.data)-1)].dt/}
                <div class="num" style="margin: 10px 0">
                {/foreach $resultList.future.data as $time/}

                    {/$time.pre_dt/}: <span style="font-weight: bold">{/$time.prediction/} </span>单&nbsp;&nbsp;
                {//foreach/}
                </div>
            </div>
        </div>

        </div>
    </div>
</div>

<script type="text/javascript">
    $(function(){
        var hourarr = [];


        //获取24小时的数组
        function gethour() {
            for( var i = 0, length = 24; i < length; i++ ){
                hourarr.push(i);
            }
        }
        gethour();
        // 路径配置
        require.config({
            paths: {
                echarts: '/assets/lib/echarts-2.1.10/build/dist'
            }
        });

        // 使用
        require(
                [
                    'echarts',
                    'echarts/chart/line' // 使用柱状图就加载bar模块，按需加载
                ],
                function (ec) {
                    // 基于准备好的dom，初始化echarts图表
                    var myChart = ec.init(document.getElementById('chartPanel'));

                    var option = {
                        tooltip : {
                            trigger: 'axis'
                        },
                        legend: {
                            data:['今日订单量','昨日订单量','上周同期订单量'],
                            y:'bottom'
                        },
                        xAxis : [
                            {
                                type : 'category',
                                boundaryGap : false,
                                axisLabel : {
                                    formatter: '{value} 时'
                                },
                                data : hourarr
                            }
                        ],
                        yAxis : [
                            {
                                type : 'value',
                                axisLabel : {
                                    formatter: '{value} 单'
                                }
                            }
                        ],
                        series : [
                            {
                                name:'今日订单量',
                                smooth  : true,
                                type:'line',
                                data:  {/$resultList.hourorder.torder|json_encode/}
                            },
                            {
                                name:'昨日订单量',
                                smooth  : true,
                                type:'line',
                                data: {/$resultList.hourorder.yorder|json_encode/}
                            },
                            {
                                name:'上周同期订单量',
                                smooth  : true,
                                type:'line',
                                data: {/$resultList.hourorder.lorder|json_encode/}
                            }
                        ]
                    };

                    // 为echarts对象加载数据
                    myChart.setOption(option);
                }
        );

        if ($('.muneIcon').css('display')!='none'){
            $('body').find('.phone-tab').addClass('hide');

        }





    });
</script>
