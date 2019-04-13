<link rel="stylesheet" type="text/css" href="/assets/css/searchtime.css?version={/$version/}">
<link rel="stylesheet" type="text/css" href="/assets/css/report.css?version={/$version/}">
<!--加载拖拽插件-->
<script type="text/javascript">
  //数据区域设置
  //项目配置数据

  {/if $id neq '' /}
    var id = {/$id/};
  {/else/}
    id =0;
  {//if/}
  {/if $type neq '' /}
    var type = {/$type/};
  {/else/}
    type =1;
  {//if/}
  //设置报表配置数据
  var  params ={};
  params.basereport ={};
  params.timereport ={};
  params.chart =[];
  params.table ={};
  params.tablelist=[];
  params.type =type;
  params.sourceConfig = {};
  var  basereport ={};
  var  timereport ={};
  var  chart=[];
  var  table={};
  var  grade={};
  var  localTableData={};//本地当前保存临时table数据变量


</script>
<script src="/assets/js/wapTest/project.js?version={/$version/}"></script>
<script src="/assets/js/wapTest/report.js?version={/$version/}"></script>
<script src="/assets/js/wapTest/reportpublic.js?version={/$version/}"></script>
<script src="/assets/js/wapTest/search.js?version={/$version/}"></script>
