<script src="/assets/lib/moment.min.js"></script>
<script src="/assets/lib/bootstrap-daterangepicker-slider/daterangepicker.js"></script>
<link href="/assets/lib/bootstrap-daterangepicker-slider/daterangepicker-bs2.css" rel="stylesheet" />
<style type="text/css">
    /*移动端适配+.three-mnue类名*/
    @media screen and (max-width: 768px) {
        .three-mnue .downclick,.three-mnue .collclick{
            display: none;
        }
        .three-mnue .search_style{
            width: 10rem;
            height: 0;
            margin: 3px 0!important;
            text-align: left;
        }
        .three-mnue .big-input{
         /*   height: 38px;
            line-height: 38px;*/
        }
        .three-mnue .timestyle{
            padding: 0;
            float: none;
            display: inline-block;
        }
    }
</style>
<script id ='inputhiddentmpl' type='text/x-dot-template'>
    {{if (it.date_type == 2){  }}
        <input type='hidden' name='startTime'>
        <input type='hidden' name='endTime'>
    {{ }else{ }}
        <input type='hidden' name='endTime'>
    {{ } }}
</script>
<script id ='searchtmpl' type='text/x-dot-template'>
    <div class="navbar navbar-default timebar three-mnue" role="navigation" style="top:0px;height: 50px">
        <div class='row search_style d_search_wrapper'   style='padding:0px; margin:1px'>
            {{ if ( it.timereport.shortcut !=undefined &&  it.timereport.shortcut.length >0){  }}
                <div class='timestyle max-hide' style='padding-left:5px'>
                    <div  class='btn-group'>
                        {{~it.timereport.shortcut:shortlist:key }} 
                            {{? shortlist == 1}}
                                <a class='btn btn-default btn-xs  btn-special' data-option='{{=shortlist}}'>
                                    昨天
                                </a>
                            {{?? shortlist == 2}}
                                <a class='btn btn-default  btn-xs btn-special' data-option='{{=shortlist}}'>
                                    前天
                                </a>
                            {{??}}
                                <a class='btn btn-default  btn-xs btn-special' data-option='{{=shortlist}}' style="line-height: 50px;">
                                    最近{{=shortlist}}天
                                </a>
                            {{?}}
                        {{~}}  
                    </div>
                </div>
            {{  } }}
            {{ if(it.timereport.date_type == 1){ }}
                <div class='timestyle'>
                    <span class='spanlist' style="margin-left:5px;">时间：</span>
                    <input name='endTime' type=
                        {{ if(it.wap){ }}
                            {{ if(it.timereport.dateview_type && it.timereport.dateview_type =="1") { }} 'datetime'
                            {{ } else if(it.timereport.dateview_type == "2"){ }} 'date'
                            {{ } else if(it.timereport.dateview_type == "3"){ }} 'month'
                            {{ } else { }}'date' {{ } }}
                        {{ } else { }}'text' readonly {{ } }} class='form-control  datepicker inputlist' value＝""/>
                </div>
            {{  }else{ }}
                <div class='timestyle'>
                    <div class="max-show" style="margin-left: 5px;">时间：</div>
                    <span class='spanlist max-inline'>开始时间：</span>
                    <input name='startTime' <input name='endTime' type=
                        {{ if(it.wap){ }}
                            {{ if(it.timereport.dateview_type && it.timereport.dateview_type =="1") { }} 
                                'datetime'
                            {{ } else if(it.timereport.dateview_type == "2"){ }} 
                                'date'
                            {{ } else if(it.timereport.dateview_type == "3"){ }} 
                                'month'
                            {{ } else { }}
                                'date' 
                            {{ } }}
                        {{ } else { }}
                            'text' readonly 
                        {{ } }} 
                        class='form-control  datepicker inputlist big-input' value＝""/>
                </div>
                <div class='timestyle'>
                    <span class='spanlist max-inline'>结束时间：</span>
                    <input name='endTime' type=
                        {{ if(it.wap){ }}
                            {{ if(it.timereport.dateview_type && it.timereport.dateview_type =="1") { }} 
                                'datetime'
                            {{ } else if(it.timereport.dateview_type == "2"){ }} 
                                'date'
                            {{ } else if(it.timereport.dateview_type == "3"){ }} 
                                'month'
                            {{ } else { }}
                                'date' 
                            {{ } }}
                        {{ } else { }}
                            'text' readonly 
                        {{ } }} 
                        class='form-control  datepicker inputlist big-input' value＝""/>
                </div>
            {{ } }}
            <div class="search-rightbox max-hide">
                <span id="scroll" title="设置随屏滚动" class="scroll recordable pull-right open closed"></span>
                {/if $is_producer == 1/}
                    <!-- <span class="collnet collclick pull-right"> -->
                    <span class="collnet pull-right">
                        <div class="btn-group">
                            <span class="dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                更多
                                <span class="caret"></span>
                            </span>
                            <ul  style="min-width: inherit;font-size:8px" class="dropdown-menu">
                                <li><a href="/report/editorreport/{/$id/}" target="_blank">编辑</a></li>
                                <li><a class="addStart">回溯</a></li>
                            </ul>
                        </div>
                    </span>
                {//if/}
                {{ if ( it.basereport.author !=undefined ){  }}
                    <a class='collnet pull-right showinfo' data-toggle="tooltip"  title='报表负责人' target='_blank'  style='color:#333;text-decoration:none'  href='http://www..com/?q={{=it.basereport.author}}' onclick="return false;">
                        <i class='glyphicon glyphicon-user'></i>
                        <span>{{=it.basereport.author?it.basereport.author:'未知'}}</span>
                    </a> 
                {{ } }}
                {{ if(it.basereport.wiki !=undefined &&  it.basereport.wiki !='' ){ }}
                    <a class='collnet pull-right' style='color:#333;text-decoration:none' target='_blank' href='{{=it.basereport.wiki}}'>
                        <span>wiki</span>  
                    </a>
                {{ } }}
                <span class='collnet collclick pull-right' data-id='{/$confArr.id/}' data-custom='{/if $isCollectCustom /}{{=isCollectCustom}}{/else/}0{//if/}'>
                    {/if $isCollect  eq  1 /}
                        <i class='glyphicon glyphicon-star'></i><span>收藏</span>
                    {/else/}
                        <i class='glyphicon glyphicon-star-empty'></i><span>收藏</span>
                    {//if/}
                </span>
                <span class='collnet downclick pull-right'>
                    <i class='glyphicon  glyphicon-save'></i><span>下载</span>
                    <!--<form method='post' action='/visual/downData'   id='downData' >
                        <input type='hidden' name='downConfig' value=''/>
                        <input type='hidden' name='report_title' value=''/>
                    </form>-->
                </span>
            </div>
        </div>
    </div>
    <!--报表添加-->
    <div id='addbox'>
        <table class="table table-bordered table-condensed" style='margin:10px 0px 0px 0px'>
            <input type="hidden" name="report_id" value="{/$id/}">
            <input type="hidden" name="user_name" value="{/$user_name/}">
            <tr>
                <td style='text-align:right;width:30%'>开始时间<b style='color:red'>*</b></td>
                <td>
                    <input name='start_time'  type='text' class='daterange inputlist' readonly />
                </td>
            </tr>
            <tr>
                <td style='text-align:right;width:30%'>结束时间<b style='color:red'>*</b></td>
                <td>
                    <input name='end_time' type='text' class='daterange inputlist' readonly />
                </td>
            </tr>
        </table>
    </div>
</script>

<!-- wap search -->
<script id ='wap_searchtmpl' type='text/x-dot-template'>
    <div class="navbar navbar-default timebar" role="navigation">
        <div class='row search_style'   style='width:99.9%%;padding:0px; margin:1px'>
            <div class="search-rightbox search-collnet" style="display:none">
                <span id="scroll" title="设置随屏滚动" class="scroll recordable pull-right open"></span>
                {{ if ( it.basereport.author !=undefined ){  }}
                    <a class='collnet showinfo' data-toggle="tooltip"  title='报表负责人' target='_blank'  style='color:#333;text-decoration:none'  href='http://speed.meilishuo.com/contacts/?q={{=it.basereport.author}}'>
                        <i class='glyphicon glyphicon-user'></i><span>{{=it.basereport.author}}</span>
                    </a>
                {{ } }}
                {{ if(it.basereport.wiki !=undefined &&  it.basereport.wiki !='' ){ }}
                    <a class='collnet' style='color:#333;text-decoration:none' target='_blank' href='{{=it.basereport.wiki}}'>
                        <span>wiki</span>
                    </a>
                {{ } }}
                <span class='collnet collclick' data-id='{/$confArr.id/}' >
                    {/if $isCollect  eq  1 /}
                        <i class='glyphicon glyphicon-star'></i><span>收藏</span>
                    {/else/}
                        <i class='glyphicon glyphicon-star-empty'></i><span>收藏</span>
                    {//if/}
                </span>
            </div>
            {{ if ( it.timereport.shortcut !=undefined &&  it.timereport.shortcut.length >0){  }}
                <div class='timestyle' style='padding-left:5px;display: none'>
                    <div  class='btn-group'>
                        {{~it.timereport.shortcut:shortlist:key }}
                            {{? shortlist == 1}}
                                <a class='btn btn-default btn-xs  btn-special' data-option='{{=shortlist}}'>
                                    昨天
                                </a>
                            {{?? shortlist == 2}}
                                <a class='btn btn-default  btn-xs btn-special' data-option='{{=shortlist}}'>
                                    前天
                                </a>
                            {{??}}
                                <a class='btn btn-default  btn-xs btn-special' data-option='{{=shortlist}}'>
                                    最近{{=shortlist}}天
                                </a>
                            {{?}}
                        {{~}}
                    </div>
                </div>
            {{  } }}
            {{ if(it.timereport.date_type == 1){ }}
                <div class='timestyle'>
                    <!-- <span class='spanlist'>时间：</span>-->
                    <input name='endTime' style="width:160px" type=
                    {{ if(it.wap){ }}
                        {{ if(it.timereport.dateview_type && it.timereport.dateview_type =="1") { }} 
                            'datetime'
                        {{ } else if(it.timereport.dateview_type == "2"){ }} 
                            'date'
                        {{ } else if(it.timereport.dateview_type == "3"){ }} 
                            'month'
                        {{ } else { }}'
                            date' 
                        {{ } }}
                    {{ } else { }}
                        'text' readonly 
                    {{ } }} 
                    class='form-control datepicker inputlist' value＝""/>
                </div>
            {{  }else{ }}
                <div class='timestyle' style="display: inline-block">
                    <!--<span class='spanlist'>开始时间：</span>-->
                    <input name='startTime' <input name='endTime' style="width:160px" type=
                    {{ if(it.wap){ }}
                        {{ if(it.timereport.dateview_type && it.timereport.dateview_type =="1") { }} 
                            'datetime'
                        {{ } else if(it.timereport.dateview_type == "2"){ }} 
                            'date'
                        {{ } else if(it.timereport.dateview_type == "3"){ }} 
                            'month'
                        {{ } else { }}
                            'date' 
                        {{ } }}
                    {{ } else { }}
                        'text' readonly 
                    {{ } }} 
                    class='form-control datepicker inputlist' value＝""/>
                </div>
                <div class='timestyle' style="display: inline-block">
                    <!--<span class='spanlist'>结束时间：</span>-->
                    <input name='endTime' style="width:160px" type=
                    {{ if(it.wap){ }}
                        {{ if(it.timereport.dateview_type && it.timereport.dateview_type =="1") { }} 
                            'datetime'
                        {{ } else if(it.timereport.dateview_type == "2"){ }} 
                            'date'
                        {{ } else if(it.timereport.dateview_type == "3"){ }} 
                            'month'
                        {{ } else { }}
                            'date' 
                        {{ } }}
                    {{ } else { }}
                        'text' readonly 
                    {{ } }} 
                    class='form-control datepicker inputlist' value＝""/>
                </div>
            {{ } }}
        </div>
    </div>
    <!--报表添加-->
    <div id='addbox'>
        <table class="table table-bordered table-condensed" style='margin:10px 0px 0px 0px'>
            <input type="hidden" name="report_id" value="{/$id/}">
            <input type="hidden" name="user_name" value="{/$user_name/}">
            <tr>
                <td style='text-align:right;width:30%'>开始时间<b style='color:red'>*</b></td>
                <td>
                    <input name='start_time'  type='text' class='daterange inputlist' readonly />
                </td>
            </tr>
            <tr>
                <td style='text-align:right;width:30%'>结束时间<b style='color:red'>*</b></td>
                <td>
                    <input name='end_time' type='text' class='daterange inputlist' readonly />
                </td>
            </tr>
        </table>
    </div>
</script>
