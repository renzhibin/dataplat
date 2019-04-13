<link href="/assets/img/favicon.png" type="image/png" rel="icon"/>
<link href="/assets/lib/bootstrap-3.3/css/bootstrap.min.css" rel="stylesheet" />
<link href="/assets/css/bootstrap-over.css?version={/$version/}" rel="stylesheet" />
<link href="/assets/css/font/iconfont.css" rel="stylesheet" />
<link href="/assets/lib/jquery-easyui-1.4.1/themes/metro-gray/easyui.css" rel="stylesheet" />
<link href="/assets/css/public.css?version={/$version/}" rel="stylesheet" />
<link href="/assets/css/easyui-over.css?version={/$version/}" rel="stylesheet" />
<link href="/assets/css/compat-ie.css" rel="stylesheet">
<!-- 禁止删除：以下inject的注释 -->
<!-- inject:css -->
<link rel="stylesheet" href="/assets/css/theme/d_theme_main.css?v=c05fdcb7e6">
<!-- endinject -->
<link href="/assets/css/font-awesome.css" rel="stylesheet"/>
<script src="/assets/lib/jquery-1.11.1.min.js"></script>
<script src="/assets/lib/bootstrap-3.3/js/bootstrap.min.js"></script>
<script type='application/javascript' src='/assets/js/fastclick.js'></script>
<script src="/assets/lib/jquery-easyui-1.4.1/jquery.easyui.min.js"></script>
<script src="/assets/lib/jquery-easyui-1.4.1/locale/easyui-lang-zh_CN.js"></script>
<script src="/assets/lib/jquery-easyui-1.4.1/bufferview.js"></script>
<script src="/assets/lib/doT.min.js"></script>
<script src="/bower_components/less/dist/less.min.js"></script>
<!-- 复制粘贴的js文件-->
<script src="/assets/lib/zeroclipboard/ZeroClipboard.min.js"></script> <!--由于flash 暂时废弃 -->
<script src="/assets/lib/clipboard.js/dist/clipboard.min.js"></script>
<canvas id='output' style='display:none;'></canvas>
<script>
    $(function ()
    {
        $("[data-toggle='popover']").popover();
    });
</script>
<script type="text/javascript">
    //报表编辑时复制使用的复制操作
    var clipboard = new Clipboard('.clipBtn');
    clipboard.on('success', function (e) {
        $('.clipBtn').removeClass('clipBtn-active');
        $(e.trigger).addClass('clipBtn-active');
    });
    clipboard.on('error', function (e) {
        console.log('复制失败');
    });
</script>