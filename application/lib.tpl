
  <link href="/assets/img/favicon.png" type="image/png" rel="icon"/>
  <link href="/assets/lib/bootstrap-3.3/css/bootstrap.min.css" rel="stylesheet" />
  <link href="/assets/css/bootstrap-over.css?version={/$version/}" rel="stylesheet" />
  <link href="/assets/css/font/iconfont.css" rel="stylesheet" />
  <link href="/assets/lib/jquery-easyui-1.4.1/themes/metro-gray/easyui.css" rel="stylesheet" />
  <link href="/assets/css/public.css?version={/$version/}" rel="stylesheet" />
  <link href="/assets/css/easyui-over.css?version={/$version/}" rel="stylesheet" />
  <link href="/assets/css/compat-ie.css" rel="stylesheet">
  <!-- <link href="/assets/css/theme/d_theme_main.less?version={/$version/}" rel="stylesheet/less" type="text/css"> -->
  <link rel="stylesheet" type="text/css" href="/assets/css/theme/d_theme_main.css">
  <link href="/assets/css/font-awesome.css" rel="stylesheet"/>
  <script src="/assets/lib/jquery-1.11.1.min.js"></script>
  <script src="/assets/lib/bootstrap-3.3/js/bootstrap.min.js"></script>
  <script type='application/javascript' src='/assets/js/fastclick.js'></script>
  <script src="/assets/lib/jquery-easyui-1.4.1/jquery.easyui.min.js"></script>
  <script src="/assets/lib/jquery-easyui-1.4.1/bufferview.js"></script>
  <script src="/assets/lib/doT.min.js"></script>
  <script src="/bower_components/less/dist/less.min.js"></script>
  <!-- 复制粘贴的js文件-->
  <script src="/assets/lib/zeroclipboard/ZeroClipboard.min.js"></script>
  <canvas id='output' style='display:none;'></canvas>
<script>
    window.focus = {};
    focus.action = "{/$action/}";
    focus.controller = "{/$controller/}";
    $(function ()
    {
        $("[data-toggle='popover']").popover();
        // var $style='<style> #chartTpl { background-color: rgba(0, 0, 0, 0) !important} .panel { background-color: rgba(0, 0, 0, 0) !important;}</style>'
        // $('head').append($style);
    });
  </script>
