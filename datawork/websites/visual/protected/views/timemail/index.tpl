{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">

<script src="/assets/js/project.js?version={/$version/}"></script> 
<div>
  {/include file="layouts/menu.tpl"/}
  <div id='right'>
      <div id="content" class="content-top" >
          <!--面包屑效果-->
          <div id="breadcrumbs-one">
              {/foreach from = $guider item= place key=key/}
              {/if $guider[0] eq $place /}
              <span><a href="{/$place.href/}">{/$place.content/}</a></span>
              {/else/}
                {/if $place.href eq '#'/}
                    <span></span><span>{/$place.content/}</span>
                {/else/}
                    <span></span><span><a href="{/$place.href/}">{/$place.content/}</a></span>
                {//if/}
              {//if/}
              {//foreach/}
          </div>

        <div style='height:10px'></div>
        <div class='container'>
            <div>
                <button  class='btn btn-primary btn-sm addStart'>
                    <i class='glyphicon glyphicon-plus'></i>新增邮件
                </button>
                <button  class='btn btn-primary btn-sm listRefresh'>
                    <i class='glyphicon glyphicon-refresh'></i>手动刷新
                </button>
                <div style='position:relative;padding-top:35px'>
                    <table class="table table-bordered data-table">
                    <thead>
                    <tr class="table_header">
                        <th style='width:15%'>邮件标题</th>
                        <th style='width:5%'>例行状态</th>
                        <th style='width:5%'>邮件状态</th>
                        <th style="width: 10%">收件人</th>
                        <th style="width: 10%">报警收件人</th>
                        <th style='width:4%'>报表ID</th>
                        <th style='width:12%'>对应报表名称</th>
                        <th style='width:10%'>创建者</th>
                        <th style='width:5%'>推送类型</th>
                        <th style='width:5%'>推送时间(小时/分)</th>
                        <th style='width:5%'>开始/结束时间</th>
                        <th style='width:20%'>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    {/foreach from =$mailList item= item key=key/}
                    <tr class="gradeX">
                        <td>{/$item.title/}</td>
                        <td>
                            {/if  $item.alive eq 0/}
                                <b style="color: red">暂停发送</b>
                            {/else/}
                                <b style="color: green">例行发送</b>
                            {//if/}
                        </td>
                        <td>
                            {/if  $item.status eq 0/}
                                <b style="color: red">未发送</b>
                            {/else/}
                                <b style="color: green">发送成功</b>
                            {//if/}
                        </td>

                        <td>
                            {/if strrpos($item.addressee,',',0) != false/}
                                {/if count(explode(',',$item.addressee)) <= 2/}
                                    {/$item.addressee/}
                                {/else/}
                                    {/substr($item.addressee,0,strpos($item.addressee,',',0))/}
                                    <a class='showinfo' title="{/$item.addressee/}" >显示全部</a>
                                {//if/}
                            {/else/}
                                {/if count(explode(';',$item.addressee)) <= 2/}
                                    {/$item.addressee/}
                                {/else/}
                                    {/substr($item.addressee,0,strpos($item.addressee,';',0))/}
                                    <a class='showinfo' title="{/$item.addressee/}" >显示全部</a>
                                {//if/}
                            {//if/}
                        </td>
                        <td>
                            {/if strrpos($item.addressee,',',0) != false/}
                                {/if count(explode(',',$item.warning_address)) <= 2/}
                                    {/$item.warning_address/}
                                {/else/}
                                    {/substr($item.warning_address,0,strpos($item.warning_address,',',0))/}
                                <a class='showinfo2' title="{/$item.warning_address/}" >显示全部</a>
                                {//if/}
                            {/else/}
                                {/if count(explode(';',$item.warning_address)) <= 2/}
                                    {/$item.warning_address/}
                                {/else/}
                                    {/substr($item.warning_address,0,strpos($item.warning_address,';',0))/}
                                <a class='showinfo2' title="{/$item.warning_address/}" >显示全部</a>
                                {//if/}
                            {//if/}

                        </td>
                        <td><a target="_blank" href='/report/showreport/{/$item.report_id/}'>{/$item.report_id/}</a></td>
                        <td>{/$item.cn_name/}</td>
                        <td>{/$item.author/}</td>
                        <td>
                            {/if $item.run_type eq 0 /}
                                天
                            {/else/}
                                小时
                            {//if/}
                        </td>
                        <td>{/$item.time/}</td>
                        <td>{/$item.begin_at/}/{/$item.end_at/}</td>
                        <td>
                            <!--<button class='btn btn-default btn-xs editMail'>编辑</button>-->
                            <!--<a href='/timemail/edit?id={/$item.mail_id/}' style='padding:3px 10px' class='btn btn-default btn-sm'>编辑</a>-->
                            <button class='btn btn-default btn-xs editMail' data-options="{/$item.mail_id/}">编辑</button>
                            <button class='btn btn-default btn-xs delMail' data-options="{/$item.mail_id/}">删除</button>
                            <button class='btn btn-default btn-xs sendMail' data-options="{/$item.mail_id/}">手动发送</button>
                            <a href='/timemail/sendMailLog?id={/$item.mail_id/}' class='btn btn-default btn-xs' target='_blank'>运行详情</a>
                            <button class='btn btn-default btn-xs aliveMail' data-options="{/$item.mail_id/},{/$item.alive/}">{/if  $item.alive eq 0/}正常发送{/else/}暂停发送{//if/}</button>
                        </td>
                    </tr>
                    {//foreach/}
                    </tbody>
                </table>
                </div>
            </div>
        </div>


    </div>
  </div>
</div>
{/include file="layouts/menujs.tpl"/}

<!--报表添加-->
<div id='addbox' style="overflow: hidden">
    <table class="table table-bordered table-condensed" style="margin:0px">
        <tr>
            <td style='text-align:right;width:30%'>邮件标题</td>
            <td>
                <input name='title' style="width: 260px"  type='text' /><br>
                <span class='tipinfoother'>( 邮件标题为:【订阅】+标题+时间)</span><br>
                <span class="tipinfoother">(如果不填默认为：【订阅】+报表标题+时间)</span>
            </td>
        </tr>
        <tr>
            <td style='text-align:right;width:30%'>收件人<b style='color:red'>*</b></td>
            <td>
                <textarea name="addressee" style="width: 300px; height: 100px" ></textarea><br>
                <span class='tipinfoother'>(只填写邮箱前缀,多个请以英文逗号分隔)</span><br>
                <span class='tipinfoother'>(暂时不支持邮件组，所填收件人必须具有报表的权限)</span>
            </td>
        </tr>
        <tr>
            <td style='text-align:right;width:30%'>选择要发送的报表<b style='color:red'>*</b></td>
            <td>
                <select name='report_id' style="width: 70%">
                    <option value="filer_not" selected="selected">----</option>
                    {/foreach from = $visualList item = item  key =key/}
                    <option value='{/$item.id/}'>{/$item.id/}_{/$item.cn_name/}</option>
                    {//foreach/}
                </select>
            </td>
        </tr>
        <tr>
            <td style='text-align:right;width:30%'>推送类型<b style='color:red'>*</b></td>
            <td>
                <select style="width: 80px" name="run_type">
                    <option value="99999">请选择</option>
                    <option value="0">天</option>
                    <option value="1">小时</option>
                </select>
            </td>
        </tr>
        <tr>
            <td style='text-align:right;width:30%'>推送时间<b style='color:red'>*</b></td>
            <td>
                <div style="display: none;" id="run_hour">
                    <span>时</span>
                    <select style="width: 60px" name="hour">
                        <!-- <option value="00">00</option>
                        <option value="01">01</option>
                        <option value="02">02</option>
                        <option value="03">02</option>
                        <option value="03">03</option>
                        <option value="04">04</option>
                        <option value="05">05</option> -->
                        <option value="06">06</option>
                        <option value="07">07</option>
                        <option value="08">08</option>
                        <option value="09">09</option>
                        <option value="10" selected="selected">10</option>
                        <option value="11">11</option>
                        <option value="12">12</option>
                        <option value="13">13</option>
                        <option value="14">14</option>
                        <option value="15">15</option>
                        <option value="16">16</option>
                        <option value="17">17</option>
                        <option value="18">18</option>
                        <option value="19">19</option>
                        <option value="20">20</option>
                        <option value="21">21</option>
                        <option value="22">22</option>
                        <option value="23">23</option>
                    </select>
                </div>
                <div style="display: none;" id="run_minute">
                    <span>分:</span>
                    <select style="width: 80px" name="minute">
                        <option value="00" selected="selected">00</option>
                        <option value="01">01</option>
                        <option value="02">02</option>
                        <option value="03">03</option>
                        <option value="04">04</option>
                        <option value="05">05</option>
                        <option value="06">06</option>
                        <option value="07">07</option>
                        <option value="08">08</option>
                        <option value="09">09</option>
                        <option value="10">10</option>
                        <option value="11">11</option>
                        <option value="12">12</option>
                        <option value="13">13</option>
                        <option value="14">14</option>
                        <option value="15">15</option>
                        <option value="16">16</option>
                        <option value="17">17</option>
                        <option value="18">18</option>
                        <option value="19">19</option>
                        <option value="20">20</option>
                        <option value="21">21</option>
                        <option value="22">22</option>
                        <option value="23">23</option>
                        <option value="24">24</option>
                        <option value="25">25</option>
                        <option value="26">26</option>
                        <option value="27">27</option>
                        <option value="28">28</option>
                        <option value="29">29</option>
                        <option value="30">30</option>
                        <option value="31">31</option>
                        <option value="32">32</option>
                        <option value="33">33</option>
                        <option value="34">34</option>
                        <option value="35">35</option>
                        <option value="36">36</option>
                        <option value="37">37</option>
                        <option value="38">38</option>
                        <option value="38">39</option>
                        <option value="40">40</option>
                        <option value="41">41</option>
                        <option value="42">42</option>
                        <option value="43">43</option>
                        <option value="44">44</option>
                        <option value="45">45</option>
                        <option value="46">46</option>
                        <option value="47">47</option>
                        <option value="48">48</option>
                        <option value="49">49</option>
                        <option value="50">50</option>
                        <option value="51">51</option>
                        <option value="52">52</option>
                        <option value="53">53</option>
                        <option value="54">54</option>
                        <option value="55">55</option>
                        <option value="56">56</option>
                        <option value="57">57</option>
                        <option value="58">58</option>
                        <option value="59">59</option>
                    </select>
                </div>
                <br>
                <span class='tipinfoother'>(推送时间仍未生成数据将推送报警邮件，请及时处理)</span>
            </td>
        </tr>
        <tr>
            <td style='text-align:right;width:30%'>报警收件人<b style='color:red'>*</b></td>
            <td>
                <textarea name="warning_address" style="width: 300px; height: 100px" ></textarea><br>
                <span class='tipinfoother'>(跟收件人格式一样)</span>
            </td>
        </tr>
        <tr>
            <td style='text-align:right;width:30%'>数据说明</td>
            <td>
                <textarea name="mail_comments" style="width: 300px; height: 100px" ></textarea><br>
                数据说明位置：
                <select style="width: 100px;height: 30px" name="comments_place">
                    <option value = '1' selected="selected">邮件顶端</option>
                    <option value = '2'>邮件底部</option>
                </select>
            </td>
        </tr>
    </table>
</div>
<script type='text/javascript'>
    $(function(){
        $("#addbox").find('select[name=run_type]').change(function () {
            $('#run_hour').css("display","inline");
            $('#run_minute').css("display","inline");
            $('#begin_at').css("display","none");
            $('#end_at').css("display","none");
        });
        //$(".secondTimebox").datetimepicker({format: 'yyyy-mm-dd hh:ii'});
//        $('select').select2();
//        $('.data-table').dataTable({
//            "iDisplayLength":10,
//            "bJQueryUI": true,
//            "sPaginationType": "full_numbers",
//            "sDom": '<""l>t<"F"fp>',
//            "bSort":false,
//            "bPaginate":false,
//            "oLanguage": {
//                'sSearch':'搜索:',
//                "sLengthMenu": "每页显示 _MENU_ 条记录",
//                "oPaginate":{
//                    "sFirst":"第一页",
//                    "sLast":"最后一页",
//                    "sNext": "下一页",
//                    "sPrevious": "上一页"
//                },
//                "sInfoEmtpy": "没有数据",
//                "sZeroRecords": "没有检索到数据",
//            }
//        });
        $('#addbox').dialog({
            title: '邮件设置',
            width: 600,
           // height:290,
            closed: true,
            cache: false,
            modal: true,
            buttons: [{
                text:'测试发送',
                handler:function(){
                    var  mailInfo  ={};
                    mailInfo.report_id  = $("#addbox").find('select[name=report_id]').select2('val');
                    mailInfo.title     = $("#addbox").find('input[name=title]').val();
                    mailInfo.addressee = $("#addbox").find('textarea[name=addressee]').val();
                    mailInfo.warning_address = $("#addbox").find('textarea[name=warning_address]').val();
                    mailInfo.run_type = $("#addbox").find('select[name=run_type]').val();

                    if (mailInfo.run_type == 1) {
                        hour = $("#addbox").find('select[name=minute]').val();
                        minute = '00';
                    } else {
                        hour = $("#addbox").find('select[name=hour]').val();
                        minute = $("#addbox").find('select[name=minute]').val();
                    }
                    mailInfo.time      = hour +":"+ minute;
                    mailInfo.begin_at = $("#addbox").find('select[name=start_hour]').val() +":"+ $("#addbox").find('select[name=start_minute]').val();
                    mailInfo.end_at = $("#addbox").find('select[name=end_hour]').val() +":"+ $("#addbox").find('select[name=end_minute]').val();

                    mailInfo.comments = $("#addbox").find('textarea[name=mail_comments]').val();
                    mailInfo.type = $("#addbox").find('select[name=comments_place]').val();
                    if(mailInfo.report_id =='filer_not' ){
                        $.messager.alert('提示','请选择要发送邮件的报表','info');
                        return;
                    }
                    if(mailInfo.run_type == '99999' ){
                        $.messager.alert('提示','请选择推送类型','info');
                        return;
                    }
                    if(mailInfo.time =='00:00'){
                        $.messager.alert('提示','报警时间不能设置为00:00','info');
                        return;
                    }
                    if(mailInfo.addressee ==''){
                        $.messager.alert('提示','收件人不能为空','info');
                        return;
                    }
                    if(mailInfo.begin_at > mailInfo.end_at){
                        $.messager.alert('提示','结束时间不能大于开始时间','info');
                        return;
                    }
                    if(mailInfo.warning_address ==''){
                        $.messager.alert('提示','报警收件人不能为空','info');
                        return;
                    }
                    // $('body').mask('正在操作...');
                    $.messager.confirm('提示','测试将发送无图片的邮件'+'</br>'+'确认发送吗?', function(r){
                        if(r) {
                            $.post('/timemail/testmail',{'mailInfo':mailInfo},function(data){
                                //$('body').unmask();
                                if(data.status ==0){
                                    $.messager.alert('提示',data.msg,'info');

                                }else{
                                    $.messager.alert('提示',data.msg,'info');
                                }
                            }, 'json');
                        }
                    });

                }
            },{
                text:'确定',
                iconCls:'icon-ok',
                handler:function(){
                    var  mailInfo  ={};
                    mailInfo.report_id  = $("#addbox").find('select[name=report_id]').select2('val');
                    mailInfo.title     = $("#addbox").find('input[name=title]').val();
                    mailInfo.addressee = $("#addbox").find('textarea[name=addressee]').val();
                    mailInfo.warning_address = $("#addbox").find('textarea[name=warning_address]').val();
                    mailInfo.run_type = $("#addbox").find('select[name=run_type]').val();
                    hour = $("#addbox").find('select[name=hour]').val();
                    minute = $("#addbox").find('select[name=minute]').val();
                    mailInfo.time = hour+":"+ minute;
                    mailInfo.comments = $("#addbox").find('textarea[name=mail_comments]').val();
                    mailInfo.type = $("#addbox").find('select[name=comments_place]').val();
                    if(mailInfo.report_id =='filer_not' ){
                        $.messager.alert('提示','请选择要发送邮件的报表','info');
                        return;
                    }
                    if(mailInfo.run_type == '99999' ){
                        $.messager.alert('提示','请选择推送类型','info');
                        return;
                    }
                    if(mailInfo.time =='00:00'){
                        $.messager.alert('提示','报警时间不能设置为00:00','info');
                        return;
                    }
                    if(mailInfo.addressee ==''){
                        $.messager.alert('提示','收件人不能为空','info');
                        return;
                    }
                    if(mailInfo.warning_address ==''){
                        $.messager.alert('提示','收件人不能为空','info');
                        return;
                    }
                    // $('body').mask('正在操作...');
                    $.post('/timemail/savemail',{'mailInfo':mailInfo},function(data){
                        //$('body').unmask();
                        if(data.status ==0){
                            $('#addbox').dialog('close');
                            $.messager.alert('提示',data.msg,'info');

                            window.location.href ='/timemail/index';

                        }else if(data.status ==1){
                            $.messager.alert('提示',data.msg,'info');
                        }else{
                            $.messager.defaults = { ok: "确认", cancel: "取消" };
                            $.messager.confirm('提示','一张报表只允许被订阅一次。<br/>该报表已被'+data.data.author+'订阅,请与该用户沟通后点击确认按钮以编辑原订阅内容。', function(r){
                                if(r) {
                                    window.location.href ='/timemail/edit?id='+data.data.mail_id;
                                }
                            });
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
        //添加新启动功能
        $('body').on('click','.addStart',function(e){
            $('select[name=report_id]').select2('val','filer_not');
            $('input[name=title]').val('');
            $('input[name=time]').val('');
            $('#addbox').dialog('open');
            $("#addbox").dialog("move",{top:e.pageY});
        });
        // 手动刷新功能
        $('body').on('click','.listRefresh',function(e){
            window.location.reload();
        });
        //删除功能
        $('body').on('click','.delMail',function(e){
            var id = $(this).attr('data-options');
            var obj = $(this);
            $.messager.confirm('提示','确认取消吗？', function(r){
                if(r) {
                    $.post('/timemail/delmail', {'id':id}, function (data) {
                        //$('body').unmask();
                        if (data.status == 0) {
                            $.messager.alert('提示', data.msg, 'info');
                            obj.parent().parent().remove();
                        } else {
                            $.messager.alert('提示', data.msg, 'info');
                        }
                    }, 'json');
                }
            });

        });
        //手动发送
        $('body').on('click','.sendMail',function(e){
            var id = $(this).attr('data-options');
            var obj = $(this);
            $.messager.confirm('提示','确认发送吗？', function(r){
                if(r) {
                    $.post('/timemail/checkChart', {'id':id}, function (data) {
                        if(data.status){
                            $.messager.alert('提示', data.msg, 'info');
                        }

                    }, 'json');
                    $.post('/timemail/sendMail', {'id':id}, function (data) {
                        //$('body').unmask();
                        if (data.status == 0) {
                            $.messager.alert('提示', data.msg, 'info');
                            //obj.parent().parent().remove();
                            //window.location.href ='/timemail/index';
                        } else {
                            $.messager.alert('提示', data.msg, 'info');
                        }
                    }, 'json');
                }
            });
        });

        //手动发送
        $('body').on('click','.aliveMail',function(e){
            var id = $(this).attr('data-options');
            var obj = $(this);
            $.messager.confirm('提示','确认修改例行状态吗？', function(r){
                if(r) {
                    $.post('/timemail/modifyAlive', {'id':id}, function (data) {
                        if(data.status == 0){
                            $.messager.alert('提示', data.msg, 'info');
                            window.location.href ='/timemail/index';
                        }

                    }, 'json');
                }
            });
        });

        $('body').on('click','.editMail',function(e){
            var id = $(this).attr('data-options');
            window.location.href ='/timemail/edit?id='+id;

        });
        $('.showinfo').tooltip({ 'position':'top'});
        $('.showinfo2').tooltip({ 'position':'top'});

    });
</script>