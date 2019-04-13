<!--语法高亮插件-->
<script type="text/javascript">
    var  projectInfo ={};
    {/if $schedule_interval_offset !=''/}
        var schedule_interval_offset  = {/$schedule_interval_offset/};
    {//if/}
    {/if $field_type !=''/}
        var  field_type = {/$field_type/};
    {//if/}
    // $(function(){
    //     $('textarea[name=code]').snippet("sql");
    //     $("pre.styles").snippet("css",{style:"greenlcd"});
    // })
</script>
<script src="/assets/lib/ace-min/ace.js" type="text/javascript" charset="utf-8"></script>
<!-- load ace emmet extension -->
<script src="/assets/lib/ace-min/ext-emmet.js"></script>
<style type="text/css">
    .list-group-item span{
        color: #000000;
        display: inline-block;
        min-height: 22px;
    }
    .list-group-item{ color: #000000;}
    .list-group .active a{color: #fff; }
    .closeBtn{
        position: absolute;
        top: 1px;
        right: -2px;
        width: 14px;
        height: 14px;
        cursor: pointer;
        display: none;
    }
    .right_progress{
        position: absolute;
        top: 97px;
        right: 20px;
        width: 100px;
        height: auto;
        border: 1px solid #ccc;
        border-radius:5px;
    }
    .right_progress h3{
        background: #eee;
        font-size: 16px;
        padding:10px 0px;
        margin: 0px;
        text-align: center;
    }
    .row{ margin: 15px 10px ; padding: 5px; border: 1px solid #eee; }
    h3{ margin-top: 10px }
    .addtitle { margin-bottom:5px; color: #428bca; display: block; }
    .storetype,.hql_type{ width:300px;height:25px; }
    .storetypebox { display: none; }

    }
    .hdfsMsg{ font-size: 12px; color: red; }
</style>
<!--编辑页面-->
<div class='tablehtml' style="display:none">
    <span class="addtitle"></span>
    <table class='table table-bordered table-condensed'>
        <tr class="storetypebox" style="display:none">
            <td class='project_type'>项目类型</td>
            <td><select name="hql_type" class="hql_type"><option value="1">报表类</option><option value="2">调度类</option></select></td>
        </tr>
        <tr class="storetypebox storebox" style="display:none">
            <td>存储类型</td>
            <td><select name="storetype" class="storetype"><option value="2">mysql</option><!--<option value="3">hbase</option>--></select></td>
        </tr>
        <tr>
            <td class='cn_name'>中文名</td>
            <td><input type="text" style='width:300px' name='cn_name' ><b style='color:red'>*</b> </td>
        </tr>
        <tr>
            <td>英文名</td>
            <td><input type="text" style='width:300px' name ='name' placeholder="英文名称，不允许输入中文和特殊字符，最多20个字符" value="" /><b style='color:red'>*</b>  </td>
        </tr>
        <tr>
            <td>说明</td>
            <td><input type="text" style='width:300px' name='explain' > <b style='color:red'>*</b> </td>
        </tr>
        <tr class="authuser"  style="display:none">
            <td>指定操作人</td>
            <td><input type="text" style='width:300px' name='authuser' placeholder='用户邮箱前缀，多人请以英文逗号分隔' ></td>
        </tr>
        <tr>
            <td>操作</td>
            <td>
                <button class='btn btn-primary saveInfo'>确定</button>
                <input type='hidden' value='' sort='' name='type'/>
            </td>
        </tr>
    </table>
</div>
<!--hql页面-->
<div class='hqlHtml' style="display:none">
    <div class ='hqlInfo'>
        <span class="addtitle"></span>
        <h4>中文名</h4>
        <input type="input" name='cn_name' style='width:300px'/><b style='color:red'>*</b>
        <h4>英文名</h4>
        <input type="input" name='name' category_name='' style='width:300px'/> <b style='color:red'>*</b>
        <h4>说明</h4>
        <input type="input" name='explain' style='width:300px'/> <b style='color:red'></b>
        <h5>调度周期</h5>
        <select class="schedule_interval" name="schedule_interval" style="width:200px;border-color:#ccc; height:24px;">
            {/foreach $schedule_interval as $key => $value/}
                <option value="{/$value.key/}">{/$value.value/}</option>
            {//foreach/}
        </select>
        <h5>数据运行时间(即函数替换基准时间)和调度时间偏移量:</h5>
        <input type='text' style="width:100px;height:28px"  name="schedule_interval_offset" /><b style='color:red'>*</b>
        <h5>回溯次数(如无必要，请勿更改):</h5>
        <input type='number' min =0  style="width:100px;height:28px"  name="run_times" /><b style='color:#5bc0de'> 填写数字</b>
        <div class="custom_cdatebox">
            <h5><input type="checkbox" name="custom_cdate" value="" class="custom_cdate" /><lable for="custom_cdate">自定义数据展现时间(选中此项，必须要重新解析hql)</lable></h5>
            <div class="custom_cdatecon" style="display:none">
                数据展现时间<b style='color:red'>*</b><br/>
                起始时间:<input type="input" value="$DATE(0)" placeholder="$DATE(0)" class="custom_start" />
                终止时间:<input type="input" value="$DATE(0)" placeholder="$DATE(0)" class="custom_end" />
            </div>
        </div>
        <h5>参数配置</h5>
        <textarea class="attach" style="width:90%;height:60px" placeholder="set mapred.output.compression.codec=com.hadoop.compression.lzo.LzopCodec;
set mapred.reduce.tasks=10; "></textarea>
        <h4>hql<span class="hdfsMsg" style="font-size:12px;color:red;margin-left:20px;display:none">(提示：需要保证inf账号有指定hdfs路径下的写入权限)</span></h4>
        <!-- <textarea class='code' name='code' style='height:300px; width:90%'></textarea> -->
        <div class="codebox"><span class="codefull fullscreen"><i class="glyphicon glyphicon-fullscreen"></i></span><div class="editorcode"></div></div>
        <b style='color:red'>*</b>
        <div class='astbox' style='display:none'>
            <span style='color:red'>直接解析失败,请填写ast信息重新解析</span>
            <textarea class='ast' name='ast' style='height:100px; width:90%'></textarea>
        </div>
        <h4>hql执行设置</h4>
        <button class='btn btn-primary hqlAnalyse' style='display:block;margin-top:3px'>hql解析</button><span id="contain" style='display:none' >正在解析，请稍后。。。</span>
        <h4>解析信息</h4>
        <div class='metricsBox'></div>
        <div class='operation'>
            <button class='btn btn-primary saveInfo'>确定</button>
            <input type='hidden' value='' sort='' name='type'/>
        </div>
    </div>
</div>
<!--hql解析模板 写入数据库-->
<script id="interpolationtmpl" type="text/x-dot-template">
    <div class='metricsContent'>
        <h3>维度(dimensions)</h3>
        <table class='dimensions table table-bordered table-condensed'>
            <tr style='background:#eee'>
                {{ for(var k in it.dimensions[0]){ }}
                    {{? k =='cn_name'  }}
                        <td style='width:25%' data-key='{{=k}}' >中文名</td>
                    {{??  k =='name'}}
                        <td style='width:25%' data-key='{{=k}}'>英文名</td>
                    {{?? k =='explain'}}
                        <td style='width:25%' data-key='{{=k}}'>说明</td>
                    {{?? k =='type'}}
                        <td style='width:25%' data-key='{{=k}}'>字段类型</td>
                    {{??}}
                        <td style='width:25%' data-key='{{=k}}'>{{=k}}</td>
                    {{?}}
                {{ } }}
            </tr>
            {{~it.dimensions :value:index}}
            <tr>
                {{ for(var k in value ) { }}
                    {{? k !='name' &&  k !='type' }}
                        <td contenteditable="true" data-type='{{=k}}'
                            {{? k =='cn_name' || k=='explain' }}
                               style='-webkit-box-shadow:1px 1px 7px #d9edf7'
                            {{?? }}
                               style='-webkit-box-shadow:1px 1px 5px #d9edf7'
                            {{?}}
                            >{{=value[k]}}</td>
                    {{??}}
                        {{? k =='type' }}
                            <td data-key ='{{=value[k]}}'>
                                {{? $.trim(value[k]) ==''}}
                                    <select class='field_type'>
                                        {{? it.field_type !='undefined'}}
                                            {{~it.field_type :f:fv}}
                                                <option value='{{=f.key}}'
                                                 {{? f.key == 'varchar'}}
                                                     selected =selected
                                                 {{?}}
                                                >{{=f.value}}</option>
                                            {{~}}
                                        {{?}}
                                    </select>
                                {{?? }}
                                    {{~it.field_type :f:fv}}
                                        {{? value[k] == f.key }}
                                         {{=f.value}}
                                        {{?}}
                                    {{~}}
                                {{?}}
                            </td>
                        {{??}}
                            <td >{{=value[k]}}</td>
                        {{? }}
                    {{?}}
                {{ } }}
            </tr>
            {{~}}
        </table>
        <h3>指标(metrics)</h3>
        <table class='metrics table table-bordered table-condensed'>
            <tr style='background:#eee'>
                {{ for(var k in it.metrics[0]) { }}
                    {{? k =='cn_name'  }}
                        <td style='width:25%' data-key='{{=k}}' >中文名</td>
                    {{??  k =='name'}}
                        <td style='width:25%' data-key='{{=k}}'>英文名</td>
                    {{?? k =='explain'}}
                        <td style='width:25%' data-key='{{=k}}'>说明</td>
                    {{?? k =='type'}}
                        <td style='width:25%' data-key='{{=k}}'>字段类型</td>
                    {{??}}
                        <td style='width:10%' data-key='{{=k}}'>{{=k}}</td>
                    {{?}}
                {{ } }}
            </tr>
            {{~it.metrics :value:index}}
            <tr>
                {{ for(var k in value ) { }}
                    {{? k !='name' &&   k !='type' }}
                        <td contenteditable="true" data-type='{{=k}}'
                            {{? k =='cn_name' || k=='explain' }}
                               style='-webkit-box-shadow:1px 1px 7px #d9edf7'
                            {{?? }}
                               style='-webkit-box-shadow:1px 1px 5px #d9edf7'
                            {{?}}
                            >{{=value[k]}}</td>
                    {{??}}
                        {{? k =='type' }}
                            <td data-key ='{{=value[k]}}'>
                                {{? $.trim(value[k]) ==''}}
                                    <select class='field_type'>
                                        {{? it.field_type !='undefined'}}
                                            {{~it.field_type :f:fv}}
                                                <option value='{{=f.key}}'
                                                 {{? f.key == 'decimal'}}
                                                     selected =selected
                                                 {{?}}
                                                >{{=f.value}}</option>
                                            {{~}}
                                        {{?}}
                                    </select>
                                {{?? }}
                                    {{~it.field_type :f:fv}}
                                        {{? value[k] == f.key }}
                                         {{=f.value}}
                                        {{?}}
                                    {{~}}
                                {{?}}
                            </td>
                        {{??}}
                            <td >{{=value[k]}}</td>
                        {{? }}
                    {{?}}
                {{ } }}
            </tr>
            {{~}}
        </table>
        <h3>维度组合(dim_sets)</h3>
        <table class='dim_sets table table-bordered table-condensed'>
            <tr style='background:#eee'>
                {{ for(var k in it.dim_sets[0]) { }}
                    {{? k =='cn_name'  }}
                        <td style='width:25%' data-key='{{=k}}' >中文名</td>
                    {{??  k =='name'}}
                        <td style='width:25%' data-key='{{=k}}'>英文名</td>
                    {{?? k =='explain'}}
                        <td style='width:25%' data-key='{{=k}}'>说明</td>
                    {{?? k =='type'}}
                        <td style='width:25%' data-key='{{=k}}'>字段类型</td>
                    {{??}}
                        <td style='width:25%' data-key='{{=k}}'>{{=k}}</td>
                    {{?}}
                {{ } }}
            </tr>
            {{~it.dim_sets :value:index}}
            <tr>
                {{ for(var k in value ) { }}
                    {{? k !='name'}}
                    <td contenteditable="true" data-type='{{=k}}'
                        {{? k =='cn_name' || k=='explain' }}
                           style='-webkit-box-shadow:1px 1px 7px #d9edf7'
                        {{?? }}
                           style='-webkit-box-shadow:1px 1px 5px #d9edf7'
                        {{?}}
                        >{{=value[k]}}</td>
                    {{??}}
                        <td >{{=value[k]}}</td>
                    {{?}}
                {{ } }}
            </tr>
            {{~}}
        </table>
        <h3>依赖表(tables)</h3>
        <table class='tables table table-bordered table-condensed'>
            <tr style='background:#eee'>
                {{ for(var k in it.tables[0]) { }}
                    {{? k =='cn_name'  }}
                        <td style='width:25%' data-key='{{=k}}' >中文名</td>
                    {{??  k =='name'}}
                        <td style='width:25%' data-key='{{=k}}'>英文名</td>
                    {{?? k =='explain'}}
                        <td style='width:25%' data-key='{{=k}}'>说明</td>
                    {{?? k =='ischecktables'}}
                         {{ continue; }}
                    {{?? k =='time_depend'}}
                         <td style="text-align:center" data-key='time_depend' >起始时间/终止时间</td>
                    {{?? k !='undefined' }}
                        <td style='width:25%' data-key='{{=k}}'>{{=k}}</td>
                    {{?}}
                {{ } }}
                <td style="text-align:center" data-key='ischecktables'>是否校验</td>
            </tr>
            {{~it.tables :value:index}}
            <tr>
                {{ for(var k in value ) { }}
                    {{? k == 'name'}}
                    <td >{{=value[k]}}</td>
                    {{?? k =='ischecktables' }}
                           {{continue;}}
                    {{?? k !='undefined' }}
                        <td contenteditable="true" data-type='{{=k}}'
                        {{? (k =='cn_name' ||  k =='explain' )}}
                        style='-webkit-box-shadow:1px 1px 7px #d9edf7'
                        {{?? }}
                        style='-webkit-box-shadow:1px 1px 5px #d9edf7'
                        {{?}}
                            >{{=value[k]}}</td>
                    {{?}}
                {{ } }}
                <td style="text-align:center">
                    <input type="checkbox" 
                    class="ischecktables" name="ischecktables" 
                    {{? value['ischecktables'] == 1 }}checked {{?}} /></td>
            </tr>
            {{~}}
        </table>   
        <input type='hidden' name='metricsConfig'/>
    </div>
</script>
<!--hql解析模板2  写入hqlfs-->
<script id="interpolationtmpl2" type="text/x-dot-template">
<div class='metricsContent' style="margin-bottom:20px;">
    <span class="hqlAnalyseMsg">解析成功</span>
    <h3>依赖表(tables)</h3>
    <table class='tables table table-bordered table-condensed hdfstable' style="margin-bottom:0">
      <tbody>
        <tr style='background:#eee'>
             <td width="25%" data-key='name'>英文名</td>
             <td width="20%" data-key='cn_name'>中文名</td>
             <td width="17%" data-key='par'>par</td>
             <td width="18%" data-key='time_depend'>起始时间/终止时间</td>
             <td style="text-align:center;width:15%" data-key='ischecktables'>是否校验</td>
             <td width="5%">操作</td>
        </tr>
        {{~it.tables :value:index}}
            <tr>
                {{ for(var k in value ) { }}
                    {{? k == 'ischecktables'  ||  k.replace(/(^\s*)|(\s*$)/g, "") =='操作' }}
                    {{continue;}}
                    {{?? k !='undefined' }}
                        <td contenteditable="true" data-type='{{=k}}' style='-webkit-box-shadow:1px 1px 5px #d9edf7'>{{=value[k]}}</td>
                    {{? }}
                {{ } }}
                <td style="text-align:center">
                    <input type="checkbox" class="ischecktables" name="ischecktables"
                     {{? value['ischecktables'] == 1 }} checked {{? }} /></td>
                <td>
                    <button class="btn btn-default btn-xs removetableBtn">删除</button>
                </td>
            </tr>
        {{~}}
        <tr><td colspan=6><button class="btn btn-primary btn-xs addtableBtn">添加一行</button></td></tr>
      </tbody>
    </table>
</div>
</script>
<!--hql执行选择页面-->
<script id='hqlselecttmpl' type='text/x-dot-template'>
    <table class='table table-condensed table-bordered'>
        <tr style="background:#eee">
            <td style='width:30%'>类目名</td>
            <td>hql</td>
        </tr>
        {{~it:item:key}}
        <tr class='runList'>
            <td name='{{=item.content.name }}'>{{=item.content.cn_name }}</td>
            <td>
                {{ if ( item.children.length >0 ){ }}
                <ul class='list-group' style='margin:0px'>
                    {{~item.children:ctem:cname}}
                    <li class='list-group-item' name='{{=ctem.content.name }}'>
                        <input type='checkbox' {{ if(ctem.content.isoperate &&ctem.content.isoperate==1 || typeof(ctem.content.isoperate)=='undefined' ){ }} checked='checked' {{ } }}/>
                        &nbsp;&nbsp;{{=ctem.content.cn_name }}
                    </li>
                    {{~}}
                </ul>
                {{ }else{  }}
                没有sql
                {{ } }}
            </td>
        </tr>
        {{~}}
    </table>
</script>
<div class='right_progress'>
    <h3>配置快捷窗口</h3>
    <div>
        <ul class='list-group'>
            <li class='list-group-item'><a href='#data'>数据配置</a></li>
            <li class='list-group-item'><a href='#opreate'>操作配置</a></li>
        </ul>
    </div>
    <button class='btn btn-primary saveConfig'  disabled="disabled">保存配置</button>
    <div style='color:red'>
        请务必保存配置，否则页面操作将不会生效!
    </div>
</div>

<style type="text/css">
    .second {
        background: #CCCCFF;
    }

    .third {
        background: honeydew;
    }
    .two > a{
        display: none;

    }
    .control {
        margin-top: 1px;
        width: 140px;
    }

    .control button {
        height: 28px;
        background: white;
        border: solid 1px rgb(204, 204, 204);
        height: 28px;
    }

    #contain {
        width: 300px;
        color: #428bca;
    }
    .codebox{ position:relative; min-height: 200px;  }
    .editorcode{ position:relative; border: solid #fff 1px; width:90%; -moz-transition: all .2s linear; -webkit-transition: all .2s linear; transition: all .2s linear; overflow: hidden; }
    .codebox .big{ position:absolute; width:180%; height:100%; z-index:10000001; left:-42%; top: 0; }
    .codefull { position: absolute; right:14%; top:7px; z-index:100; color:#fff; }
    .codefullbig { position: absolute; right:0%; top:-100%; z-index:10000002; }

</style>
