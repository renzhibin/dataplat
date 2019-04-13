
  <link href="/assets/img/favicon.png" type="image/png" rel="icon"/>
  <link href="/assets/lib/wapTest/bootstrap-3.3/css/bootstrap.min.css" rel="stylesheet" />
  <link href="/assets/css/wapTest/bootstrap-over.css?version={/$version/}" rel="stylesheet" />
  <link href="/assets/css/wapTest/font/iconfont.css" rel="stylesheet" />
  <link href="/assets/lib/wapTest/jquery-easyui-1.4.1/themes/metro-gray/easyui.css" rel="stylesheet" />
  <link href="/assets/css/wapTest/public.css?version={/$version/}" rel="stylesheet" />
  <link href="/assets/css/wapTest/easyui-over.css?version={/$version/}" rel="stylesheet" />
  <link href="/assets/css/wapTest/compat-ie.css" rel="stylesheet">
  <script src="/assets/lib/wapTest/jquery-1.11.1.min.js"></script>
  <script src="/assets/lib/wapTest/bootstrap-3.3/js/bootstrap.min.js"></script>
  <script type='application/javascript' src='/assets/js/wapTest/fastclick.js'></script>
  <script src="/assets/lib/wapTest/jquery-easyui-1.4.1/jquery.easyui.min.js"></script>
  <script src="/assets/lib/wapTest/jquery-easyui-1.4.1/bufferview.js"></script>
  <script src="/assets/lib/wapTest/doT.min.js"></script>
  <!-- 复制粘贴的js文件-->
  <script src="/assets/lib/wapTest/zeroclipboard/ZeroClipboard.min.js"></script>
  <canvas id='output' style='display:none;'></canvas>
<script>
    window.focus = {};
    focus.action = "{/$action/}";
    focus.controller = "{/$controller/}";
    $(function ()
    {
        $("[data-toggle='popover']").popover();
	var $style='<style> .contrasttr,.datagrid-view,#playground {background:' + xxxx() +' !important;}</style>'
        $('head').append($style);
    });
    function xxxx(s){
      var s = s || '.datagrid-view',
          canvas = document.getElementById('output'), context,
          p = '{/Yii::app()->user->username/}';
          //console.log(p);
      canvas.width=200;
      canvas.height=200;
      var ctx = canvas.getContext('2d');
      ctx.rotate(-30*Math.PI/180);
      ctx.font = "12px";
      ctx.fillStyle = "rgba(100,100,0,0.2)";
      //ctx.fillStyle="#eee";
      //ctx.fillText(parseInt(Math.random() * 10) + parseInt(Math.random() * 10) + p + parseInt(Math.random() * 10) + parseInt(Math.random() * 10),5,5);
        ctx.fillText(p,-25,100);
      //$(s)[0].style.backgroundImage = 'url("' + ctx.canvas.toDataURL() + '")';
      return 'url("' + ctx.canvas.toDataURL() + '")';
    }
  </script>
