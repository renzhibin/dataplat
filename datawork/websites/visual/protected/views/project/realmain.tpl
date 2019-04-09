{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">

<div>
  {/include file="layouts/menu.tpl"/}
  <div id='right'>
    <div id="content" class="content">
        <!--面包屑导航-->
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
        <div class="container" style='padding:0px'>
            <div class='row' style='margin-top:20px'>
                <h3 id='data'>数据配置</h3>
                <div class='col-lg-4' style='width:30%;float:left'>
                    <button class='btn btn-primary addProject'>添加项目</button> 

                    <ul class='list-group listmap_ul' style='padding:10px;'>
                    </ul>
                </div>
                <div class='col-lg-8 rightContent' style='width:70%;float:left'>
                </div>
            </div>
            <div class='row'>
                <h3 id='opreate'>操作配置<span class='tipinfo' style='font-size:14px'>(点击操作配置的确定按钮之后，保存按钮才会生效)</span></h3>
                <p style="padding-left:15px;color:#5bc0de;">温馨提示：<br/>
                    项目日期：只有在此时间区间内的项目才会运行。<br/>
                    执行sql列表：只有在执行sql列表中选定的sql才会执行。</p>
                <div class='col-lg-12'  style='width:70%;float:left'>
                    <table class='table table-bordered table-condensed runConfig' >
                        <tr>
                            <td>作者</td>
                            <td class='author'>{/$author/}</td>
                        </tr>
                        <tr>
                            <td>项目日期</td>
                            <td>
                                开始时间：<input type='text' class='datepicker start'/>
                                结束时间：<input type='text' class='datepicker end'/>
                            </td>
                        </tr>
                        <tr>
                            <td>执行sql列表</td>
                            <td class='hqllist'></td>
                        </tr>
                        <tr>
                            <td>操作</td>
                            <td><button class='btn btn-primary saveRun'>确定</button></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>
  </div>
</div>
{/include file="layouts/menujs.tpl"/}

{/include file="project/real.tpl"/}
<script type="text/javascript">var isCoreProject = 0;</script>
<script type="text/javascript" src='/assets/js/realpublic.js?version={/$version/}'></script>