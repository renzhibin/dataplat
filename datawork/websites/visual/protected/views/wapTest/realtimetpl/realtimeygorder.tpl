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
        margin: -30px 0 40px;
        text-align: left;
        font-size: 14px;
        line-height: 36px;
        background-color: #e6e6e6;
        padding: 0 10px;
        width: 24.6%;
    }
    .order-sub{
        font-size: 12px;
        font-style: normal;
    }
    .numPanel{
        text-align: center;
        padding: 0 75px 0 30px;
    }
    .order .num{
        font-size: 16px;
        font-weight: bold;
    }
    #content#special{
        background-color: #fff;
        margin-left: 0;

    }
    .itemnow {
        position: relative;
        right: 5px;
        top: -31px;
        height: 50px;
        line-height: 50px;
        text-align: right;
    }
    .timeview {
        margin-left: 10px;
        display: inline;
        color: #CCCCCC;
    }
    .frash,.zone .btn{
        padding: 1px 3px;
        background-color: #f4f4f4;
        border-color: #f4f4f4;
        margin-right: 5px;
    }
    .frash:hover,.zone .btn:hover{
        color: black;
        background-color: #f4f4f4;
        border-color: #f4f4f4;
    }
    .note{
        padding: 0 65px 0 25px;
    }
    .zone{
        position: relative;
        top: 25px;
        left: 10px;
        height: 10px;
        line-height: 10px;
        text-align: left;
    }
    .zone-checked{

        background-color: #ABAFB8!important;
    }

    @media screen and (max-width: 1000px){
        .order {
            display: inline-block;
            text-align: left;
            font-size: 14px;
            line-height: 36px;
            background-color: #e6e6e6;
            padding: 0 10px;
            width: 48%;
        }
        .numPanel{
            padding: 0 65px 0 25px;
        }
        .zone{
            left: 20px;
        }
        .itemnow{
            right:5px;
        }
        .timeview,.frash{
            display: none;
        }
    }
</style>
<div>
    <div id="content special" class="content special">
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
                <div class="itemnow">
                    <div class="zone">
                        {/foreach from = $resultList.city item= info key=key/}
                            <div class="btn btn-default  btn-sm" data-id="{/$info.id/}">{/$info.cn/}</div>
                        {//foreach/}
                    </div>
                    <div class="btn btn-default  btn-sm frash">刷新</div>
                    <div class="timeview">刷新时间:<span></span></div>
                </div>
                <div class="order">订单量&nbsp;<i class="order-sub">(单单价)</i><br/><span class="num"></span>&nbsp;单&nbsp;(<strong id="avgPrice" class="order-sub">0</strong>)</div>
                <div class="order">订单金额<br/><span class="num"></span>&nbsp;元</div>
                <div class="order">顾客数<br/><span class="num"></span>&nbsp;个</div>
                <div class="order">新增顾客数<br/><span class="num"></span>&nbsp;个</div>
            </div>
            <!-- 为ECharts准备一个具备大小（宽高）的Dom -->
            <div id="chartPanel" style="height:400px;margin-top: -80px;"></div>
            <!--<div class="note">注：1时为0~1时</div>-->
        </div>

        </div>
    </div>
</div>

<script type="text/javascript">
    $(function(){
        var hourarr = [];
        var $mod = $(".special");
        var paramZone = '';
        var cityArr = [];
        //获取现在数据的地域
        localStorage.zone_id = localStorage.zone_id ? localStorage.zone_id:'1000';
        function nowCity(){
            $('.zone .btn').each(function(k,v) {
                if(localStorage.zone_id== $(v).data('id')){
                    $(v).addClass('zone-checked');
                }
            });

        }
        nowCity();

        function setNewDate(data) {
            $mod.find('.timeview span').text(data.flushtime);
            //刷新页面数据
            $mod.find('.order span').each(function(k,v) {
                if(k==0){
                    $(v).text(data.ordernum || 0);
                }
                if(k==1){
                    $(v).text(commafy(data.orderamount || 0));
                }
                if(k==2){
                    $(v).text(data.ordercnum || 0);
                }
                if(k==3){
                    $(v).text(data.newordercum || 0);
                }
            });

            //set avg price
            if (data.orderamount > 0) {
              var avg_price;
              if(data.orderamount && data.ordernum){
                avg_price = Math.round(data.orderamount/data.ordernum);
              }else{
                avg_price = 0;
              }
              $("#avgPrice").text(avg_price)
            }
        }

        //格式化数字
        function  commafy(num){
            num  =  num+"";
            var  re=/(-?\d+)(\d{3})/;
            while(re.test(num)){
                num=num.replace(re,"$1,$2")
            }
            return  num;
        }

        function frashPage(params) {
            $.ajax({
                url:'/realtime/fetchygdata',
                data: params,
                dataType:'json',
                type:'POST'
            }).done(function(data) {
                //4个panel的数据
                console.log(data);
                setNewDate(data);
                getCitys(data.city);
                //绘图
                renderChart(data.hourorder?data.hourorder:data.allorder,data.hourorder?false:true);

               // console.log(noworder);
            });

        }

        paramZone = {
            zone_id:localStorage.zone_id,
            format:"json"
        };
        frashPage(paramZone);

        function getCitys(cityInfos) {
            cityArr =[];
            $.map(cityInfos,function(v,k){
                cityArr.push(v.cn);
                return cityArr;
            });
        }

        // 一分钟刷新一次
        setInterval(function() {
            frashPage(paramZone);
        },1000*60);


        //获取24小时的数组
        function gethour() {
            for( var i = 0, length = 24; i < length; i++ ){
                hourarr.push(i);
            }
        }
        gethour();

        $mod.on("click", ".zone .btn", function () {
            var $this = $(this);
            $this.siblings('.btn').removeClass('zone-checked');
            $this.addClass('zone-checked');
            //储存地域信息
            localStorage.zone_id=$this.data('id');
            //改变地域值
            paramZone = {
                zone_id:$this.data('id'),
                format:"json"
            };

            frashPage(paramZone);
        });


        // 路径配置
        require.config({
            paths: {
                echarts: '/assets/lib/echarts-2.1.10/build/dist'
            }
        });

       function renderChart(torder,boo){
           //判断是地域数据还是当地数据,boo false是地区数据，true是全国数据
           var allorder =!boo;

           // 使用
           require(
                   [
                       'echarts',
                       'echarts/chart/line' // 使用柱状图就加载bar模块，按需加载
                   ],
                   function (ec) {
                       // 基于准备好的dom，初始化echarts图表
                       var myChart = ec.init(document.getElementById('chartPanel'));
                       var allOrderOptions = [
                           {
                               name:'今日订单量',
                               smooth  : true,
                               type:'line',
                               data:torder.torder
                           },
                           {
                               name:'昨日订单量',
                               smooth  : true,
                               type:'line',
                               data:torder.yorder
                           },
                           {
                               name:'上周同期订单量',
                               smooth  : true,
                               type:'line',
                               data:torder.lorder
                           }
                       ];
                       var citysOrderOptions = [
                           {
                               name:cityArr[0],
                               smooth  : true,
                               type:'line',
                               data:torder.quanguo
                           },
                           {
                               name:cityArr[1],
                               smooth  : true,
                               type:'line',
                               data: torder.beijing
                           },
                           {
                               name:cityArr[2],
                               smooth  : true,
                               type:'line',
                               data:torder.tianjin
                           },
                           {
                               name:cityArr[3],
                               smooth  : true,
                               type:'line',
                               data:torder.hangzhou
                           }
                       ]
                       var option = {

                           tooltip : {
                               trigger: 'axis'
                           },
                           legend: {
                               data:allorder ? ['今日订单量','昨日订单量','上周同期订单量'] :cityArr,
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
                           series : allorder?allOrderOptions:citysOrderOptions
                       };

                       // 为echarts对象加载数据
                       myChart.setOption(option);
                   }
           );
       }

        $mod.on("click", ".frash", function () {
            window.location.reload();
        });

        if ($('.muneIcon').css('display')!='none'){
            $('body').find('.phone-tab').addClass('hide');

        }



    });
</script>
