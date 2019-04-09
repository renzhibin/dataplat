{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">
<script src="/assets/js/project.js?version={/$version/}"></script>
<style type="text/css">
    .tdwidth {
        width: 30%;
        text-align: right;
    }

    .inputall {
        width: 300px
    }
</style>
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
                <div class="panel panel-info">
                    <div class="panel-heading">
                        编辑订阅邮件页面
                        <a href='/timemail/index' class='pull-right'>返回列表</a>
                    </div>
                    <div class="panel-body" style='padding:5px'>
                        <form id='fm' method='post'>
                            <table class='table table-condensed table-bordered' style='margin-bottom:5px'>
                                <tr>
                                    <td style='text-align:right;width:30%'>邮件标题</td>
                                    <td>
                                        <input name='title' style="width: 260px" type='text' value="{/$title/}"/><br>
                                        <span class='tipinfoother'>( 邮件标题为:【订阅】+标题+时间)</span><br>
                                        <span class="tipinfoother">(如果不填默认为：【订阅】+报表标题+时间)</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style='text-align:right;width:30%'>收件人<b style='color:red'>*</b></td>
                                    <td>
                                        <textarea name="addressee"
                                                  style="width: 300px; height: 100px">{/$addressee/}</textarea><br>
                                        <span class='tipinfoother'>(只填写邮箱前缀,多个请以英文逗号分隔)</span><br>
                                        <span class='tipinfoother'>(所填收件人必须具有报表的权限)</span>
                                    </td>
                                </tr>
                                <!--  <tr>
                                   <td  class='tdwidth'>菜单Url类型</td>
                                     <td>
                                       <select class='menuUrlType inputall' name ='type'>
                                         <option value=1 selected ='selected'>报表</option>
                                         <option value=2 >外链</option>
                                       </select>
                                     </td>
                                 </tr>  -->
                                <tr>
                                    <td style='text-align:right;width:30%'>要发送的报表</td>
                                    <td>
                                        {/$report_name/}
                                    </td>
                                </tr>
                                <tr>
                                    <td style='text-align:right;width:30%'>推送类型<b style='color:red'>*</b></td>
                                    <td>
                                        <select style="width: 80px" name="run_type">
                                            <option value="{/$run_type/}" selected="selected">
                                                {/if $run_type eq 0 /}
                                                天
                                                {/else/}
                                                小时
                                                {//if/}
                                            </option>
                                            <option value="0">天</option>
                                            <option value="1">小时</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td style='text-align:right;width:30%'>推送时间<b style='color:red'>*</b></td>
                                    <td>
                                        <div style="display: inline;" id="run_hour">
                                            <span>时:</span>
                                            <select style="width: 60px" name="hour">
                                                <option value={/$time_h/} selected="selected">{/$time_h/}</option>
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
                                            </select>
                                        </div>
                                        <div style="display: inline;" id="run_minute">
                                            <span>分:</span>
                                            <select style="width: 80px" name="minute">
                                                <option value="{/$time_m/}"
                                                        selected="selected">{/$time_m/}</option>
                                                <option value="00">00</option>
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
                                        <textarea name="warning_address"
                                                  style="width: 300px; height: 100px">{/$warning_address/}</textarea><br>
                                        <span class='tipinfoother'>(跟收件人格式一样)</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style='text-align:right;width:30%'>邮件注释</td>
                                    <td>
                                        <textarea name="mail_comments"
                                                  style="width: 300px; height: 100px">{/$comments/}</textarea><br>
                                        邮件注释位置：
                                        <select style="width: 100px;height: 30px" name="comments_place">
                                            <option value='1' {/if $type eq '1'/}selected="selected" {//if/} >邮件顶端
                                            </option>
                                            <option value='2' {/if $type eq '2'/}selected="selected" {//if/} >邮件底部
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                            <span style='padding-left:30%'></span>
                            <button type='button' class='btn btn-primary btn-sm updateMail'>保存</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var type = '{/$type/}';
    {/if $type eq 'editor'/}
    var id = {/$id/};
    var menuInfo = {/$menuInfo/};
    {//if/}


</script>

<script type="text/javascript">
    $(function () {
        $("#content").find('select[name=run_type]').change(function () {
            $('#run_hour').css("display", "inline");
            $('#run_minute').css("display", "inline");
            $('#begin_at').css("display","none");
            $('#end_at').css("display","none");
        });
        $("body").on("click", '.updateMail', function () {
            var form = document.getElementById('fm');
            var mailInfo = {};
            var hour = '';
            var minute = '';
            mailInfo.report_id = '{/$report_id/}'
            mailInfo.mail_id = '{/$id/}';
            mailInfo.title = $("#content").find('input[name=title]').val();
            mailInfo.addressee = $("#content").find('textarea[name=addressee]').val();
            mailInfo.warning_address = $("#content").find('textarea[name=warning_address]').val();
            mailInfo.run_type = $("#content").find('select[name=run_type]').val();
            hour = $("#content").find('select[name=hour]').val();
            minute = $("#content").find('select[name=minute]').val();
            mailInfo.time = hour + ":" + minute;
            mailInfo.comments = $("#content").find('textarea[name=mail_comments]').val();
            mailInfo.type = $("#content").find('select[name=comments_place]').val();

            if (mailInfo.time == '00:00') {
                $.messager.alert('提示', '报警时间不能设置为00:00', 'info');
                return;
            }
            if (mailInfo.addressee == '') {
                $.messager.alert('提示', '收件人不能为空', 'info');
                return;
            }
            if (mailInfo.warning_address == '') {
                $.messager.alert('提示', '收件人不能为空', 'info');
                return;
            }
            $.post('/timemail/updatemail',{'mailInfo':mailInfo}, function (data) {
                if (data.status == 0) {
                    $.messager.alert('提示', data.msg, 'info');
                    window.location.href = '/timemail/index';
                } else {
                    //$.messager.alert('提示',data.msg,'info');
                }
            }, 'json');
        })
    });
</script>

