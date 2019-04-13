<!-- for now we put all third party script here -->
<script src="/assets/lib/wapTest/dataTables/jquery.dataTables.min.js?version={/$version/}"></script>
<link href="/assets/lib/wapTest/dataTables/dataTables.css?version={/$version/}" rel="stylesheet" />
<script src="/assets/lib/wapTest/select2/select2.min.js"></script>
<link href="/assets/lib/wapTest/select2/select2.css" rel="stylesheet" />
<!--对比插件-->
<script src="/assets/js/wapTest/contrast.js?version={/$version/}"></script>
<link href="/assets/css/wapTest/contrast.css?version={/$version/}" rel="stylesheet" />

<!--highcharts-->
<!-- <script src="/assets/lib/highcharts/highcharts.js"></script>
<script src="/assets/lib/highcharts/highcharts-exporting.js"></script>
<script src="/assets/lib/highcharts/export-excel.js"></script>  -->

<!--echart-->
<!--echart2  -->
<script type="text/javascript" src='/assets/lib/wapTest/echarts-2.2.3/build/dist/echarts-all.js'></script>

<!--echart3  -->
<!-- <script src="/assets/lib/echarts.min.js"></script> -->

<!--bootstrap-datepicker-->
<script src="/assets/lib/wapTest/bootstrap-datepicker/bootstrap-datepicker.js"></script>
<link href="/assets/lib/wapTest/bootstrap-datepicker/datepicker.css" rel="stylesheet" />

<!--bootstrap-range datepicker-->
<!-- <script src="/assets/lib/moment.min.js"></script>
<script src="/assets/lib/bootstrap-daterangepicker-slider/daterangepicker.js"></script>
<link href="/assets/lib/bootstrap-daterangepicker-slider/daterangepicker-bs2.css" rel="stylesheet" /> -->

<!--bootstrap-datepicker-->
<script src="/assets/lib/wapTest/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
<script src="/assets/lib/wapTest/bootstrap-datetimepicker/bootstrap-datetimepicker.zh-CN.js"></script>
<link href="/assets/lib/wapTest/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css" rel="stylesheet" />



<!--遮罩插件-->
<script src="/assets/js/wapTest/loading.js"></script>

<script src="/assets/js/wapTest/public.js?version={/$version/}"></script>
<script src="/assets/js/wapTest/datasource.js?version={/$version/}"></script>
<script src="/assets/js/wapTest/dataExcel.js?version={/$version/}"></script>
<script src="/assets/js/wapTest/reportpublic.js?version={/$version/}"></script>
<script src='/assets/lib/wapTest/html2canvas.min.js'></script>
<!--拖拽插件-->
<script src="/assets/lib/wapTest/jquery.dragsort-0.5.2.min.js"></script>
<!-- toolbar js 功能插件 -->
<script type="text/javascript" src="/assets/js/wapTest/toolbar.js?version={/$version/}"></script>
<!-- table js 功能插件 -->
<script type="text/javascript" src="/assets/js/wapTest/table.js?version={/$version/}"></script>

<!-- 顶部固定插件  -->
<script type="text/javascript" src="/assets/js/wapTest/jquery-pin.js"></script>

<!-- vconsole -->
<!-- <script src="http://wechatfe.github.io/vconsole/lib/vconsole.min.js?v=2.0.0"></script> -->


</script>
<!-- 添加水印的功能 -->
<script type="text/javascript" src="/assets/js/watermark.js"></script>
<script type="text/javascript">
    window.watermark({
        'txt': '{/Yii::app()->user->username/}', // 水印文案，默认为“机密数据，请勿外传”，推荐填写当前登录用户的邮箱
        'selector': 'body', // 需要添加水印的选择器，默认为“.page-content” ，根据实际需要添加水印的元素选择器填写
        'isForce': true // 是否强制添加水印，如果是，则会删除tr元素的背景等操作
    })
</script>