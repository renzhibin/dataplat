{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<!-- 流量分析 -->
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
<link rel="stylesheet" type="text/css" href="/assets/css/searchtime.css">
<div style='height:10px'></div>
<div class='container'>
    <div class="navbar navbar-default timebar" role="navigation">
     <div class='row search_style' style="margin: 0px">
      <form action='' method='post' id='contrastform'>
       <div class='timestyle'>
          <span class='spanlist'>开始时间：</span>
          <input name='startTime' type='text' value='{/$startTime/}' class='form-control datepicker inputlist' />
       </div>
       <div class='timestyle'>
          <span class='spanlist'>结束时间：</span>
          <input name='endTime'  type='text' value='{/$endTime/}'  class='form-control  datepicker inputlist' />
       </div>
       <div class='pull-right' style='padding:8px 10px 0px 0px'>
           <!--<input type='hidden' value='' name='keysCon'>keysCon中的值,经常包含单双引号导致错误,已经移到js添加-->
            <input type='hidden' value='0' name='down'>
            <span class='down' data-option='1' style='cursor:pointer'>
             <i class='glyphicon  glyphicon-save'></i><span>下载</span>
            </span>
           {/if $analyst  eq 1/}
                <span class='savereport' style='cursor:pointer;padding-left:7px'>保存为报表</span>
           {//if/}
       </div>
      </form>
     </div>
    </div>
    <div  style='width:100%;position:relative' id ='twitterChart'>
        {/$charthtml/}
    </div>
    <div id="result"></div>
</div>
<!--报表保存-->
<div id='addbox'>
    <table class="table table-bordered table-condensed" style='margin:10px 0px 0px 0px'>
        <tr>
            <td style='text-align:right;width:30%'>报表名称<b style='color:red'>*</b></td>
            <td>
                <input type="text" name="cn_name" style="width: 300px"/><br>
                <span class="tipinfoother">(报表名必须是中英文、数字、小括号或者下划线且不超过15个字符!)</span>
                <input type ='hidden' name="params" value="{/$params/}"/>
            </td>
        </tr>
        <tr class="auth_hide">
            <td class='table_left'>申请审核</td>
             <td><input type="checkbox" class="auth" checked='checked'/></td>
        </tr>
    </table>
</div>
<script type="text/javascript">
    //加载表格
    //var easyData = {/$easyData/};
    var easyInfo = {/$easyInfo/};
    var interText = doT.template($("#tablelisttmp").html());
    $("#result").html(interText(easyInfo));
    $('#tablelist').datagrid();
    if(easyInfo  !=''){
        $('#tablelist').datagrid('loadData',easyInfo.easyData);
    }else{
        // $('#tablelist').datagrid('loadData',easyInfo.easyData);
    }
    $(function(){
        $('#addbox').dialog({
            title: '报表保存',
            width: 500,
            height:210,
            closed: true,
            cache: false,
            modal: true,
            buttons: [{
                text:'确定',
                iconCls:'icon-ok',
                handler:function(){
                    var config   = $('input[name=params]').val();
                    var cn_name  = $("#addbox").find('input[name=cn_name]').val();
                    if($.trim(cn_name) ==''){
                        $.messager.alert('提示','请填写报表名称','info');
                        return;
                    }
                    var basereport ={};
                    var timereport ={};
                    basereport.cn_name = cn_name;
                    basereport['auth'] = $('#addbox input.auth').is(":checked")?1:0;
                    timereport.date_type =2;
                    timereport.interval = 7;
                    timereport.offset =1;
                    timereport.shortcut =[7,30];
                    var url = '/report/savereport';


                    var params ={};
                    params.basereport = basereport;
                    params.timereport = timereport;
                    params.type = 3;
                    params.config = config;

                    $.post(url, {'params':JSON.stringify(params)},function(data){
                        if(data.status==0){
                            var tipstr ="<h5>操作成功！</h5><br/>";
                            tipstr +='<a href="/menu/index">设置报表所属菜单</a><br/>';
                            tipstr +='<a target="_blank" href="/report/showreport/'+data.data+'">查看报表</a><br/>';
                            tipstr +='<a href="/report/reportlist">返回报表管理页面</a>';
                            $.messager.alert('提示',tipstr,'info');
                            $('#addbox').dialog('close');
                        }else{
                            $.messager.alert('提示',data.msg,'warning');

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

         ajaxUrlArr = getJsonStr();
         console.log(ajaxUrlArr);
         if( ajaxUrlArr == 0){
             return;
         }else{
             intAjax(0,ajaxUrlArr);
         }
       /* $('.search').on('click',function(){
            $('input[name=down]').val(0);
            $('#contrastform').submit();
        });*/
       $('.datepicker').on('changeDate',function(ev){
         var startTime = $('input[name=startTime]').val();
         var endTime = $('input[name=endTime]').val();
        if($(this).attr('name')=='startTime'){
             if(startTime.valueOf() > endTime.valueOf()){
                 $.messager.alert('提示','开始时间大于结束时间','warning');
                 return;
             }

         }else{
             if(startTime.valueOf() > endTime.valueOf()){
                 $.messager.alert('提示','结束时间小于开始时间','warning');
                 return;
             }

        }
         $('input[name=down]').val(0);
           var valueObj = document.createElement("input") ; //email input
           valueObj.setAttribute("name", "keysCon") ;
           valueObj.setAttribute("type", "hidden") ;
           valueObj.setAttribute("value", JSON.stringify({/$origin_keysCon/}));
           document.getElementById('contrastform').appendChild(valueObj) ;
           $('#contrastform').submit();
      });
        $('.down').on('click',function(){
            $('input[name=down]').val(1);
            var valueObj = document.createElement("input") ; //email input
            valueObj.setAttribute("name", "keysCon") ;
            valueObj.setAttribute("type", "hidden") ;
            valueObj.setAttribute("value", JSON.stringify({/$origin_keysCon/}));
            document.getElementById('contrastform').appendChild(valueObj) ;
            $('#contrastform').submit();
        });
        $('.savereport').on('click',function(){
            $('#addbox').dialog('open');
        });

    })

</script>
{/include file="layouts/footer.tpl"/}
