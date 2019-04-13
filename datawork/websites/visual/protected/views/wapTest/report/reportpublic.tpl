<!--基本信息设置-->
<div id="basereport" style='display:none'>
    <table class='table table-condensed table-bordered'>
        <tr>
            <td style='text-align:right;width:30%'>请选择项目</td>
            <td>
                <select name='project' style="width:100%">
                    <option value='filter_not' selected='selected'>----</option>
                    {/foreach from = $project item = item key=key/}
                    <option id="{/$item.id/}" value='{/$item.project/}'
                            {/if $projectname == $item.project &&  $projectname  neq ''/}
                            selected='selected'
                            {//if/}
                    >{/$item.cn_name/}</option>
                    {//foreach/}
                </select>
            </td>
        </tr>

        <tr>
            <td class='table_left'>报表名称<b class='tip'>*</b></td>
            <td>
                <input type='text' name='cn_name' class='inputall' maxlength='20'/>
                <span class='tipinfoother'>(报表名必须是中英文、数字、小括号或者下划线且不超过20个字符!)
            </td>
        </tr>
        <tr>
            <td class='table_left'>报表wiki</td>
            <td>
                <input type='text' name='wiki' class='inputall'/>
            </td>
        </tr>
        <tr>
            <td class='table_left'>报表注释</td>
            <td>
                <input type="checkbox" value="" name="isexplainshow"> 设为默认展开
                <textarea class='inputall' name='explain'></textarea>

            </td>
        </tr>
        <tr class="auth_hide">
            <td class='table_left'>申请审核</td>
            <td><input type="checkbox" class="auth" checked='checked'/></td>
        </tr>
    </table>
</div>
<!--时间信息设置-->
<div id="timereport" style='display:none'>
    <table class='table table-condensed table-bordered'>
        <tr>
            <td class='table_left'>时间区类型</td>
            <td>
                <select name='date_type' class='date_type' style='width:300px'>
                    <option value='1'>单天</option>
                    <option value='2'>区间</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class='table_left'>时间粒度</td>
            <td>
                <div class="dateview radio" style="margin:0">
                    <label class="radio-inline">
                        <input type="radio" name="dateview_type" value="1"/>小时
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="dateview_type" value="2" checked/>天
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="dateview_type" value="3"/>月
                    </label>
                </div>
            </td>
        </tr>
        <tr class='single'>
            <td class='table_left'>快捷时间设置</td>
            <td>
                <div class="btn-group single_shortcut" role="group">
                    <button type="button" class="btn btn-sm btn-default" data-option='1'>昨天</button>
                    <button type="button" class="btn btn-sm btn-default" data-option='2'>前天</button>
                </div>
                <span class='tipinfoother'>(点击可选多天!)</span>
            </td>
        </tr>
        <tr class='single'>
            <td class='table_left'>
                默认时间(偏移量)设置
                <i class='tipinfo'></i>
            </td>
            <td>
                <select class='default_time single_offset'>
                    <option value='1'>1天</option>
                    <option value='2'>2天</option>
                </select>
                <a href="javascript:void(0)" class="btn btn-default btn-sm" onclick="setvalue(this)">自定义时间(间隔以天为单位!)</a>
            </td>
        </tr>
        <tr class='interval' style='display:none'>
            <td class='table_left'>快捷时间设置</td>
            <td>
                <div class="btn-group interval_shortcut" role="group">
                    <button type="button" class="btn btn-sm btn-default" data-option='7'>最近7天</button>
                    <button type="button" class="btn btn-sm btn-default" data-option='30'>最近30天</button>
                </div>
                <span class='tipinfoother'>(点击可选多天!)</span>
            </td>
        </tr>
        <tr class='interval' style='display:none'>
            <td class='table_left'>默认时间间隔<b class='tip'>*</b></td>
            <td>
                <input class="easyui-numberspinner interval_offset_num" value="7" data-options="min:0,increment:1"
                       style="width:100px;"></input>
            </td>
        </tr>
        <tr class='interval' style='display:none'>
            <td class='table_left' rowspan="2">默认结束时间(偏移量)设置
                <i class='tipinfo'></i>
            </td>
            <td>
                <select class='default_time interval_offset'>
                    <option value='1'>1天</option>
                    <option value='2'>2天</option>
                </select>
                <a href="javascript:void(0)" class="btn btn-default btn-sm" onclick="setvalue(this)">自定义时间(间隔以天为单位)</a>
            </td>
        </tr>
        <tr class='interval' style='display:none'>
            <td class='tipinfoother'>(默认开始时间以默认间隔偏移!)</td>
        </tr>
    </table>
</div>
<!--时间信息设置-->
<div id="timecontrast" style='display:none'>
    <table class='table table-condensed table-bordered'>
        <tr>
            <td class='table_left'>时间区类型</td>
            <td>
                <span name='date_type'>单天</span><span class='tipinfo'></span>
            </td>
        </tr>
        <tr>
            <td class='table_left'>时间粒度</td>
            <td>
                <div class="radio dateview" style="margin:0">
                    <label class="radio-inline">
                        <input type="radio" name="constrast_dateview_type" id="timecontrastradio1" value="1"/>小时
                    </label>
                    <label class="radio-inline active">
                        <input type="radio" name="constrast_dateview_type" id="timecontrastradio2" value="2" checked/>天
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="constrast_dateview_type" id="timecontrastradio3" value="3"/>月
                    </label>
                </div>
            </td>
        </tr>
        <tr class='single'>
            <td class='table_left'>快捷时间设置<b class='tip'>*</b></td>
            <td>
                <div class="btn-group single_shortcut" role="group">
                    <button type="button" class="btn btn-sm btn-default" data-option='1'>昨天</button>
                    <button type="button" class="btn btn-sm btn-default" data-option='2'>前天</button>
                </div>
                <span class='tipinfoother'>(点击可选多天!)</span>
            </td>
        </tr>
        <tr class='single'>
            <td class='table_left'>
                默认时间(偏移量)设置<b class='tip'>*</b>
                <i class='tipinfo'></i>
            </td>
            <td>
                <select class='default_time single_offset'>
                    <option value='1'>1天</option>
                    <option value='2'>2天</option>
                </select>
                <a href="javascript:void(0)" class="btn btn-default btn-sm" onclick="setvalue(this)">自定义时间(间隔以天为单位!)</a>
            </td>
        </tr>
        <tr class='interval' style='display:none'>
            <td class='table_left'>快捷时间设置<b class='tip'>*</b></td>
            <td>
                <div class="btn-group interval_shortcut" role="group">
                    <button type="button" class="btn btn-sm btn-default" data-option='7'>最近7天</button>
                    <button type="button" class="btn btn-sm btn-default" data-option='30'>最近30天</button>
                </div>
                <span class='tipinfoother'>(点击可选多天!)</span>
            </td>
        </tr>
        <tr class='interval' style='display:none'>
            <td class='table_left'>默认时间间隔<b class='tip'>*</b></td>
            <td>
                <input class="easyui-numberspinner interval_offset_num" value="7" data-options="increment:1"
                       style="width:100px;"></input>
            </td>
        </tr>
        <tr class='interval' style='display:none'>
            <td class='table_left' rowspan="2">默认结束时间(偏移量)设置
                <b class='tip'>*</b>
                <i class='tipinfo'></i>
            </td>
            <td>
                <select class='default_time interval_offset'>
                    <option value='1'>1天</option>
                    <option value='2'>2天</option>
                </select>
                <a href="javascript:void(0)" class="btn btn-default btn-sm" onclick="setvalue(this)">自定义时间(间隔以天为单位)</a>
            </td>
        </tr>
        <tr class='interval' style='display:none'>
            <td class='tipinfoother'>(默认开始时间以默认间隔偏移!)</td>
        </tr>
    </table>
</div>
<!--提示框-->
<div id='tipInfo' style="display:none">
    <div class='box' style="padding:10px"></div>
    <input type='hidden' name='id' value="">
</div>
<!--图表设置-->
<div id="chartreport" style='display:none'>
    <table class="table table-bordered table-condensed tbHeader"
           style="margin:0px">
        <tr>
            <td style='width:100px;text-align:right'>图表标题<b style=" color:#ff0000">*</b>:</td>
            <td>
                <input type='text' name='chartTitle' style='width:274px; height:26px'/>
            </td>
        </tr>
        <tr>
            <td style='width:100px;text-align:right'>图表类型<b style=" color:#ff0000">*</b>:</td>
            <td>
                <select class='select' name='chartType' style="width:200px">
                    <option value='pie' selected='selected'>饼图</option>
                    <option value='column'>柱状图</option>
                    <!-- <option value='hour'>曲线图(小时曲线)</option> -->
                    <option value='map'>行政区划图</option>
                    <option value='funnel'>漏斗图</option>
                    <option value='spline_time'>曲线图(时间趋势图)</option>
                    <option value='area'>堆积图</option>
                    <option value='cursor_line'>自定义x轴曲线</option>
                </select>
            </td>
        </tr>
        <tr class="chart_data_box">
            <td style='width:100px;text-align:right'>数据处理</td>
            <td>
                <select type='text' name='chartTop' style="width:200px">
                    <option value="filter_not">----</option>
                    <option value="10">Top10</option>
                    <option value="15">Top15</option>
                    <option value="20">Top20</option>
                </select>
                <a href="javascript:void(0)" class="btn btn-default btn-sm" onclick="setTop(this)">自定义数字</a>
            </td>
        </tr>
        <tr class="chart_event">
            <td style='width:100px;text-align:right'>数据事件关联</td>
            <td>
                <select name='chart_event_checkbox'>
                    {/foreach from = $timeline  item = item  key =key/}
                    <option value="{/$item.event_id/}">{/$item.event_name/}</option>
                    {//foreach/}
                </select>
                <button class="btn btn-xs btn-default addEvent">关联</button>
                <br>
                <ul class="eventData" style="margin:0px;padding:0px"></ul>
            </td>
        </tr>
        <tr class="chart_unit_box">
            <td style='width:100px;text-align:right'>图表单位:</td>
            <td>
                <select type='text' name='chartUnit' style="width:200px">
                    <option value="filter_not">----</option>
                    {/foreach from= $unit item = item key=key/}
                    <option value="{/$item.name/}">{/$item.name/}</option>
                    {//foreach/}
                </select>
                <a href="javascript:void(0)" class="btn btn-default btn-sm" onclick="setUnit(this)">增加单位</a>
            </td>
        </tr>
        <tr class="chart_width_set">
            <td style='width:100px;text-align:right'>图表显示设置<b style=" color:#ff0000">*</b>:</td>
            <td>
                <select type='text' name='chartWidth' style="width:200px">
                    <option value="filter_not">----</option>
                    <option value="50">一行两个</option>
                    <option value="100">一行一个</option>
                </select>
            </td>
        </tr>
        <tr class="chart_data_source">
            <td style='width:100px;text-align:right'>数据源<b style=" color:#ff0000">*</b>:</td>
            <td>
                <button class='btn btn-default btn-sm' data-option='dataSource'>选择数据源</button>
                <!--数据框-->
                <div id="sourcebox" style="display:none"></div>
                <div id="tableBox" style="display:none"></div>
                <!-- <input type='hidden' class="tips_chart" data-option='chart'>
                <div class='dataShowBox' style='display:none'>
                  <div class='row' style='margin:0px'>
                    <div class='col-md-12'>
                      <table class='table table-condensed'>
                        <tr>
                          <td style='width:60px'>隐藏维度</td>
                          <td class='group'></td>
                        </tr>
                        <tr>
                          <td style='width:60px'>指标</td>
                          <td class='metric'></td>
                        </tr>
                      </table>
                    </div>
                  </div>
                  <div class='tipinfoother'>(点击指标指定图表需要的曲线!) <b class='tip'>饼图只能选择一种指标！</b></div>
                </div> -->
            </td>
        </tr>
    </table>
</div>
<!--table设置-->
<!--表格高级设置-->
<div id="reportgrade" style='display:none'>
    <button class="btn btn-primary btn-sm addsource">数据源设置</button>
    <input type="hidden" value="" name="tableconf"/>
    <div id="tableSourceBox" style="display:none;padding:5px 5px 50px 5px"></div>
    <div id="tableReportBox" style='display:none'></div>
</div>
<!--即时过滤条件设置-->
<div id="reportsearch" style='display:none'>
    <table class='table table-condensed table-bordered'>
        <tr>
            <td class='table_left'>过滤字段</td>
            <td class='reportkey'></td>
        </tr>
        <tr>
            <td class='table_left'>多选过滤条件</td>
            <td>
                <input type='checkbox' class='reportcheck'/>允许多选
            </td>
        </tr>
        <tr> 
            <td class="table_left">预设过滤条件数据</td> 
            <td> 
                <select class="jsgltj" style="width:100px;"> 
                </select>  
                <span class="show-preset">显示预设内容</span>
            </td>
        </tr>
        <tr class="preset-container"> 
            <td class="table_left">预设内容</td> 
            <td class="preset-td-container"> 
                <span class="preset-container-td"></span>
            </td>
        </tr>
        <tr class="contrast_set">
            <td class='table_left'>忽略筛选</td>
            <td>
                <input type='checkbox' class='reportgroup'/><br>
                <span class='tipinfo'>展示报表时下拉框变成输入框，由用户自已筛选数据.</span>
            </td>
        </tr>
        <tr class="contrast_set">
            <td class='table_left'>多维设置</td>
            <td>
                <input type='checkbox' class='reportdimensions'/><br>
                <span class='tipinfo'>目前只支持二维！</span>
            </td>
        </tr>
        <tr>
            <td class='table_left'>过滤条件数据<b class='tip'>*</b></td>
            <td>
                <textarea placeholder="key:value" style='height:200px;width:300px' class='reportsource'></textarea>
                <input type='hidden' class="target_obj" value=""/>
            </td>
        </tr>
        <tr>
            <td class='table_left'>默认搜索值<b class='tip'>*</b></td>
            <td>
                <input placeholder="value" class='defaultsearch' value=""/>
                <input type='hidden' class="defaultsearchobj" value=""/>
            </td>
        </tr>
    </table>
</div>
