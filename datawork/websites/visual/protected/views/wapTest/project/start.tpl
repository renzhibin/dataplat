{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}

<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">


<script src="/assets/lib/moment.min.js"></script>
<script src="/assets/lib/bootstrap-daterangepicker-slider/daterangepicker.js"></script>
<link href="/assets/lib/bootstrap-daterangepicker-slider/daterangepicker-bs2.css" rel="stylesheet" />

<div>
  {/include file="layouts/menu.tpl"/}
  <div id='right'>
    <div id="content" class="content">

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
        <button  class='btn btn-primary btn-sm addStart'>
          <i class='glyphicon glyphicon-plus'></i>新增启动
        </button>
        <button  class='btn btn-primary btn-sm listRefresh'>
          <i class='glyphicon glyphicon-refresh'></i>手动刷新
        </button>
        <div style='position:relative;padding-top:35px'>
           <table class="table table-bordered data-table">
            <thead>
              <tr class="table_header">
                <th>id</th>
                <th>项目名称</th>
                <th>模块</th>
                <th>执行日期</th>
                <th >执行结果</th>
                <th>开始时间</th>
                <th>结束时间</th>
                <th>创建时间</th>
                <th>任务类型</th>
                <th>导入行数</th>
                <th>导入用时</th>
                <th>优先级</th>
                <th>创建人</th>
                <th>启动方式</th>
                <th style='width:40px'>日志</th>
                <!--<th>任务类型</th>-->
                <!--<th>test</th>-->
                <th>操作</th>
                  <th style='width:40px'>下载</th>
              </tr>
            </thead>
            <tbody>
              {/foreach from =$list item= item key=key/}
                <tr class="gradeX">
                  <td>{/$item.id/}</td>
                  <td>{/$item.app_name/}</td>
                  <td>{/$item.run_module/}</td>
                  <td>{/$item.stat_date/}</td>
                  <td>{/$item.status/}</td>
                  <td>{/$item.start_time/}</td>
                  <td>{/$item.end_time/}</td>
                  <td>{/$item.create_time/}</td>
                  <td>{/if $item.step eq 'all'/}
                        全部
                      {/else if $item.step eq 'hive'/}
                        hql任务
                      {/else if $item.step eq 'mysql'/}
                        导入数据
                      {/else if $item.step eq 'delete'/}
                        删除数据
                      {/else/}全部
                      {//if/}</td>
                  <td>{/$item.data_size/}</td>
                  <td>{/$item.load_time_spend/}</td>
                  <td>{/$item.priority/}</td>
                  <td>{/$item.submitter/}</td>
                  <!-- 启动方式 -->
                  <td>
                    {/if $item.creater != null /}
                    手动
                    {/else/}
                    例行
                    {//if/}
                  </td>
                  <td><a target="_blank" href={/$item.log/}>日志</a></td>
                 <!--  <td>{/$item.step/}</td>-->
                    <!-- <td>{/$item.is_test/}</td>-->
                  <td>
                      <button class="btn btn-primary btn-xs btn-kill" data='{/$item.killtask/}'>杀死</button>
                      <button class="btn btn-primary btn-xs btn-reday" data='{/$item.killtask/}'>置为就绪</button>

                  </td>
                    <td><a target="_blank" href={/$item.download/}>下载</a></td>
                </tr>
              {//foreach/}
            </tbody>
          </table> 
        </div>
      </div>

    </div>
  </div>
</div>
{/include file="layouts/menujs.tpl"/}
<!--报表添加-->
<div id='addbox'>
  <table class="table table-bordered table-condensed" style='margin:10px 0px 0px 0px'>
    <tr>
       <td style='text-align:right;width:30%'>选定hql<b style='color:red'>*</b></td>
       <td>
        <select name='module' multiple style="width:90%">
          {/foreach from = $module item = item  key =key/}
           <option value='{/$item.en_name/}'>{/$item.cn_name/}({/$item.en_name/})</option>
          {//foreach/}
        </select>
       </td>
    </tr>
      <tr>
          <td style='text-align:right;width:30%'>任务类型</td>
          <td>
              <select name="step" style="width:133px;">
                  <option value="all">全部</option>
                  <option value="hive">hql任务</option>
                  <option value="mysql">导入数据</option>
                  <option value="delete">删除数据</option>
              </select>
          </td>
      </tr>
    <tr>
       <td style='text-align:right;width:30%'>开始时间<b style='color:red'>*</b></td>
       <td>
         <input name='start_time'  type='text' class='daterange inputlist' readonly /> 
       </td>
    </tr>
    <tr>
       <td style='text-align:right;width:30%'>结束时间<b style='color:red'>*</b></td>
       <td>
         <input name='end_time'  type='text' class='daterange inputlist' readonly /> 
       </td>
    </tr>
  </table>
</div>
<script type='text/javascript'>
   var project ='{/$project/}';
   $(function(){
      $('select').select2();
      //时间格式
      // $('#addbox input.datepicker').datepicker('hide');
       $('#addbox input.daterange').daterangepicker({
        'singleDatePicker': true,
        'timePicker': true,
        'showDropdowns': true,
        'format': 'YYYY-MM-DD HH:mm',
        'language':'zn-ch',
        'locale':{
        'applyLabel':'确定',
        'cancelLabel':'取消',
        'fromLabel':'开始',
        'toLabel':'结束',
        'monthNames':"一月_二月_三月_四月_五月_六月_七月_八月_九月_十月_十一月_十二月".split("_"),
        'daysOfWeek':"日_一_二_三_四_五_六".split("_")},
        'showDropdowns':false,
        'applyClass':'btn-success sure'});

      // $('#dashboard').datagrid();
      $('.data-table').dataTable({
        "iDisplayLength":10,
        "bJQueryUI": true,
        "sPaginationType": "full_numbers",
        "sDom": '<""l>t<"F"fp>',
        "bSort":false,
        "bPaginate":true,
         "oLanguage": {
            'sSearch':'搜索:',
              "sLengthMenu": "每页显示 _MENU_ 条记录",
               "oPaginate":{
                 "sFirst":"第一页",
                 "sLast":"最后一页",
                 "sNext": "下一页",
                 "sPrevious": "上一页"
               },
               "sInfoEmtpy": "没有数据",
               "sZeroRecords": "没有检索到数据",
          }

      });
      //添加新启动功能
      $('body').on('click','.addStart',function(e){
        $('select[name=module]').select2('val',[]);
        $('input[name=start_time]').val('');
        $('input[name=end_time]').val('');
        $('#addbox').dialog('open');
        $("#addbox").dialog("move",{top:e.pageY});
      });
      // 手动刷新功能
      $('body').on('click','.listRefresh',function(e){
          window.location.reload();
      });
      // kill任务
      $('body').on('click','.btn-kill',function(){
         var dataurl = $(this).attr('data');
        $.messager.confirm('提示', '确定杀死吗？', function(r){
             if(r){
        $.ajax({
          type:"POST",
          url:"/project/KillTask?"+dataurl,
          dataType:"json",
          data:{}, 
          success:function(data){
            if(data.status == "0"){
              alert(data.msg);
              window.location.reload();
            } else {
              alert(data.msg);
            }
          },error:function(){
            console.log('网络连接失败');
          }
        });

                }
        }); 
      });

       // kill任务
       $('body').on('click','.btn-reday',function(){
           var dataurl = $(this).attr('data');
           $.messager.confirm('提示', '确定置为就绪吗？', function(r){
               if(r){
                   $.ajax({
                       type:"POST",
                       url:"/project/setready?"+dataurl,
                       dataType:"json",
                       data:{},
                       success:function(data){
                           if(data.status == "0"){
                               alert(data.msg);
                               window.location.reload();
                           } else {
                               alert(data.msg);
                           }
                       },error:function(){
                           console.log('网络连接失败');
                       }
                   });

               }
           });
       });


      $('#addbox').dialog({
        title: '报表配置',
        width: 450,
        //height:190,
        closed: true,
        cache: false,
        modal: true,
        buttons: [{
          text:'确定',
          iconCls:'icon-ok',
          handler:function(){
             var  runinfo  ={}, $addbox = $('#addbox');
             runinfo.project = project;
             runinfo.run_module  = $addbox.find('select[name=module]').select2('val');
             runinfo.start_time  = $addbox.find('input[name=start_time]').val();
             runinfo.end_time  = $addbox.find('input[name=end_time]').val();
             runinfo.step = $addbox.find('select[name="step"]').val();
             if(runinfo.run_module  =='' ){
                $.messager.alert('提示','请选择模块','info');
                return;
             } 
             if(runinfo.start_time ==''){
                $.messager.alert('提示','请选择开始时间','info');
                return;
             } 
             if(runinfo.end_time ==''){
                $.messager.alert('提示','请选择结束时间','info');
                return;
             } 
              $('body').mask('正在操作...');
              $.post('/project/saverun', {'runinfo':runinfo},function(data){
                $('body').unmask();
                if(data.status ==0){
                     $.messager.alert('提示',data.msg,'info');
                     $('#addbox').dialog('close');
                      window.location.href ='/project/runlist?project='+runinfo.project;

                }else{
                     $.messager.alert('提示',data.msg,'info');
                }
              }, 'json'); 
          }
        },{
          text:'取消',
          handler:function(){
            $('#addbox').dialog('close');
          }
        }]
      });
   });
</script>
