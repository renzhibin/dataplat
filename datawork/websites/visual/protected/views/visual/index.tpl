{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">
<link rel="stylesheet" type="text/css" href="/assets/css/searchtime.css?version={/$version/}">
<script src="/assets/js/filter.js?version={/$version/}"></script>

<style type="text/css">
    .chartlist{position: relative;}
    .chartlist .chartclose,.chartedit{
        display: none;
    }
    .shadow{
        box-shadow: 0 1px 6px rgba(0, 0, 0, 0.12), 0 1px 6px rgba(0, 0, 0, 0.12);
        margin-top: 20px;
        padding: 20px 10px;
    }
    .framebefore{
        height: 0px;
    }
    /*移动端适配+.three-mnue类名*/
    @media screen and (max-width: 768px) {
        .nav.nav-tabs.three-mnue{
            /* position: fixed;
            left: 5.1rem;
            top: 0;
            width: 180px;
            z-index: 1111;*/
            display: none;
        }
        .nav-tabs.three-mnue>li>a{
            padding: 20px 20px;
            border: none;
            font-size: 14px;
            width: 5rem;
            background-color: #F6FBFF;
            color: #6191d4;
        }
        .nav-tabs.three-mnue>li.active a{
            height: 62px;
            background-image: none;
            background-color: #ea764f;
            color: #fff;
        }
        .nav-tabs.three-mnue>li {
            display: block;
        }
        .framebefore{
            height: 0;
        }
    }
</style>
<script type="text/javascript">
    //菜单id
    var menu_id= {/$menu_id/};
    //是否为自定义收藏
    {/if $isCollectCustom /}
        var isCollectCustom = 1;
    {/else/}
        var isCollectCustom = 0;
    {//if/}
    //报表id
    {/if $id neq ''/}
        var id = '{/$id/}';
    {/else/}
        var id =0;
    {//if/}
    //是否为配置报表收藏
    {/if $isCollect  eq 'true'/}
        var isCollect = 1;
    {/else/}
        var isCollect = 0;
    {//if/}
    //用途未知
    {/if $general neq ''/}
        var general = {/$general/};
    {/else/}
        var general = 0;
    {//if/}

    var isWhiteTable = {/$isWhiteTable/};

    // 是否定时刷新 1是 0否
    var refreshSet = 0;
    {/if $refreshSet /}
      refreshSet = 1;
    {//if/}

    // 刷新间隔分钟 数字 默认 5
    var refreshTime = 5;
    {/if $refreshTime /}
      refreshTime = {/$refreshTime/};
    {//if/}

    var refresh = function (fn,refreshTime) {
        return new Promise((resolve,rejecet)=>{
            setInterval(fn,refreshTime)
        })
    }
    const reload = ()=>window.location.reload()
    refreshSet ? refresh(reload,refreshTime * 1000 * 60) : '';
</script>
{/assign var=paramsValue value=$params|@json_decode:1/}
<div style="{/if $isMobile /}position: relative;{/else /}position: absolute;top: 50px{//if/};bottom: 0;right: 0;left: 0">
    <!-- 修正面包屑丢失 -->
    <!-- <div style='width:100%; height:50px;'></div> -->
    <div id ='right'>
        {/if $allcontent neq '' /}
            <div id="all-content" class="all-content" >
        {/else/}
            {/include file='layouts/menu.tpl'/}
            <div id="content" class="content" >
        {//if/}

        {/if $reportauth eq 'true' /}
            {/if $menu_id eq 0  /}
                {/if $collect|@count eq 0  && $id eq 0  /}
                    <!-- 首页移动端需要rightreport节点,填充外链页面-->
                    <div  class="rightreport" style="margin-left:-10px">
                        <div class='h4show'>
                        <!-- <h4 > 收藏报表，让您的首页不再空白!</h4>
                        <p>问：怎么收藏？&nbsp;&nbsp;答：点击报表页右上角&nbsp;
                            <span class='glyphicon glyphicon-star-empty'></span>
                            <span>收藏</span>
                        </p>
                        -->
                        <p style='padding-left:30%;text-align:left'></p>
                    </div>
                {/else/}
                    {/if $id eq 0 /}
                    {/else/}
                        <div>
                            <!--2016-12-23 增加概要数据的三级菜单显示-->
                            {/if $isWhiteTable === 1 && !empty($urlMenu[0].table_id) /}
                                <ul class="nav nav-tabs three-mnue d_nav_tabs">
                                    {/foreach from = $urlMenu[0].table_id item= tMenu key=tkey/}
                                        <li style='margin-top:5px;cursor:pointer'
                                            {/if $id eq $tMenu.id /}
                                                class="active"
                                            {//if/}
                                        >
                                            <a href="/visual/index/{/$tMenu.id/}">{/$tMenu.cn_name/}
                                            {/if $id eq $tMenu.id /}
                                                &nbsp;<span class='glyphicon glyphicon-question-sign navtab-reportexplain'></span>
                                            {//if/}
                                            </a>
                                         </li>
                                    {//foreach/}
                                </ul>
                            {//if/}
                            <!--2016-12-23 结束-->
                            {/if $isWhiteTable !== 1/}
                                {/foreach from = $collect item= coll key=key/}
                                    {/if $coll.id eq $id /}
                                        <li class='active nav-tabs'>
                                            <!-- 收藏页面和概要页面显示的tab在移动端隐藏-->
                                            <a class="max-hide" href="/visual/index/{/$coll.id/}">{/$coll.name/} &nbsp;
                                                <span class='glyphicon glyphicon-question-sign navtab-reportexplain'></span>
                                            </a>
                                        </li>
                                    {//if/}
                                {//foreach/}

                                <!-- 自定义收藏报表 -->
                                {/foreach from = $collectCustom item= coll key=key/}
                                    {/if $coll.id eq $id /}
                                        <li class='active'>
                                            <!-- 收藏页面和概要页面显示的tab在移动端隐藏-->
                                            <a class="max-hide" href="/visual/index/{/$coll.id/}">{/$coll.name/} &nbsp;
                                                <span class='glyphicon glyphicon-question-sign navtab-reportexplain'></span>
                                            </a>
                                        </li>
                                    {//if/}
                                {//foreach/}

                                <!--{/foreach from = $collect item= coll key=key/}
                                    {/if $coll.id eq $id /}
                                        <div>
                                            {/if $coll.first_menu neq '' &&  $coll.second_menu neq '' /}
                                               <span style='padding:3px 0px 3px 10px'>
                                                    来源菜单：{/$coll.first_menu/}  >> {/$coll.second_menu/} >> {/$coll.name/}
                                                </span>
                                            {//if/}
                                        </div>
                                    {//if/}
                                {//foreach/}-->
                            {//if/}
                            <div class="framebefore"></div>
                            <div  class="rightreport" style="margin-left:-10px">
                            {/if $confArr['type'] == '4'/}
                                {/include file='tooltpl/showreport.tpl'/}
                            {/elseif $confArr['type'] == '9'/}
                                {/include file='reporttpl/openurl.tpl'/}
                            {/else/}
                                {/include file='reporttpl/common.tpl'/}
                            {//if/}
                        </div>
                    {//if/}
                {//if/}
            {/else/}
                {/if  $allcontent neq 2 /}
                    {/if !empty($showTable.table) /}
                        <!--面包屑效果-->
                        {/if !$isMobile /}
                        <div id="breadcrumbs-one">
                            {/foreach from = $guider item= place key=key/}
                                {/if $guider[0] eq $place /}
                                    <span>
                                        <a href="{/$place.href/}">{/$place.content/}</a>
                                    </span>
                                {/else/}
                                    {/if $place.href eq '#'/}
                                        <span></span>
                                        <span>{/$place.content/}</span>
                                    {/else/}
                                        <span></span>
                                        <span>
                                            <a href="{/$place.href/}">{/$place.content/}</a>
                                        </span>
                                    {//if/}
                                {//if/}
                            {//foreach/}
                        </div>
                        {//if/}
                        <div class="nav nav-tabs" style="width: 5000px" id="menuContainer">
                            <span id="three_menu_left" title="向右移动菜单" style="display: none; margin-left: 5px; background: url(/assets/img/arrow.gif)  no-repeat 0 -48px; float: left; width: 20px; height: 50px;" onmouseover="this.style.background='url(/assets/img/arrow.gif)  no-repeat 0 -103px';" onmouseout="this.style.background='url(/assets/img/arrow.gif)  no-repeat 0 -48px';">
                            </span>
                            <ul class="nav nav-tabs three-mnue d_nav_tabs" style="white-space: nowrap;float:left;">
                                <!-- <ul class="nav nav-tabs  three-mnue d_nav_tabs"> -->
                                {/foreach from = $showTable.table item= tname key=tkey/}
                                    <li style='cursor:pointer'
                                        {/if $id eq $tname.id /}
                                            class="active"
                                        {//if/}
                                        {/if $tname.type  neq 1  /}
                                            title ='定制报表' name='openlink'
                                        {//if/}
                                    >
                                    {/if $tname.type  eq 1  /}
                                        <a class="innerurl" href="/visual/index/menu_id/{/$menu_id/}/id/{/$tname.id/}">
                                            {/$tname.cn_name/}
                                        {/if $id eq $tname.id and $paramsValue.basereport.explain ne null/}
                                            <span class='glyphicon glyphicon-question-sign navtab-reportexplain'>
                                            </span>
                                        {//if/}
                                        </a>
                                    {/else if $tname.type  eq 3  /}
                                        <a class="openlink" href="/visual/index/menu_id/{/$menu_id/}/id/{/$tname.id/}" id="{/$tname.id/}" menu-id="{/$menu_id/}">
                                            {/$tname.cn_name/}
                                            <!-- <span class='glyphicon glyphicon-globe'></span>  -->
                                        </a>
                                    {/else/}
                                        <!-- <span class='glyphicon glyphicon-globe'></span> -->
                                        <a data-option="{/$tname.url/}" class="openurl" menu-id="{/$menu_id/}">
                                            <!-- <span>{/$tname.cn_name/}</span>&nbsp;<span class='glyphicon glyphicon-globe'></span> -->
                                        </a>
                                    {//if/}
                                    </li>
                                {//foreach/}
                            </ul>
                            <span id="three_menu_right" title="向左移动菜单" style="display: none; margin-left: -37px; background: url(/assets/img/arrow.gif)  no-repeat -50px -48px; float: left; width: 20px; height: 50px;" onmouseover="this.style.background='url(/assets/img/arrow.gif)  no-repeat -50px -103px';" onmouseout="this.style.background='url(/assets/img/arrow.gif)  no-repeat -50px -48px';">
                            </span>
                        </div>
                    {//if/}
                {//if/}
                <div class="framebefore"></div>
                <div class="rightreport">
                    {/if $confArr['type'] == '4'/}
                        {/include file='tooltpl/showreport.tpl'/}
                    {/else if $confArr['type'] == '9'/}
                        {/include file='reporttpl/openurl.tpl'/}
                    {/else/}
                        {/include file='reporttpl/common.tpl'/}
                    {//if/}
                </div>
            {//if/}
        {/else/}
            <h4 class='h4show'>您没有权限</h4>
        {//if/}
    </div>
    <!--处理报表标题-->
    <script>
        var titleObj =$("#content").children('ul');
        if(titleObj.length >0){
            //点击tab，设置title，外链等页面需要设置，所以统一设置了
            titleObj.each(function(){
                if($(this).children('li').hasClass('active')){
                    document.title= $.trim($(this).children('.active').text())+ "-{/env('TITLE')/}";
                }else{
                    document.title=$.trim($(this).children().eq(0).text())+"-{/env('TITLE')/}";
                }
            });
        }
        $(document).ready(function(){
            //$('.filter .btnSearch').click();
            // 处理三级菜单长度
            var contentWidth = $('#content').width();
            var threeMenuWidth = $('.nav.nav-tabs.three-mnue.d_nav_tabs').width();
            var allThreeMenuLi = $('.nav.nav-tabs.three-mnue.d_nav_tabs li');
            var allLi = allThreeMenuLi.length;
            if(threeMenuWidth > contentWidth) {
                //15 + 40 + 5 - 37; 15为第一个li的margin；40为2个按钮的宽度 5为左侧按钮的margin -37为右侧按钮margin 以下类似
                var currentIndexStart = 0;
                var currentIndexEnd = 0;
                // var currentWidth = 23;
                // 多加 12px 否则在默写特定的长度 比如 998px 会出现消失的情况
                var currentWidth = 40;
                var currentActiveFlag = false;
                for(var i = 0; i < allLi; ++i) {
                    var currentItem = allThreeMenuLi.eq(i);
                    currentWidth += (currentItem.width() + 52); // 52为每个li的margin
                    if(currentWidth > contentWidth) {
                        if(currentActiveFlag) {
                            currentIndexEnd = i - 1;
                            break;
                        } else {
                            currentIndexStart = currentIndexEnd = i--;
                            currentWidth = 23;
                        }
                    } else {
                        currentIndexEnd = i;
                        currentActiveFlag = currentActiveFlag || currentItem.hasClass('active');
                    }
                }
                allThreeMenuLi.eq(currentIndexStart).css('margin-left', '15px')
                for(i = 0; i < allLi; ++i) {
                    if(i < currentIndexStart || i > currentIndexEnd) {
                        allThreeMenuLi.eq(i).hide();
                    }
                }
                $('#three_menu_left').show();
                $('#three_menu_right').show();
            }
            $('#menuContainer').css('width', contentWidth);
        });

        $("#three_menu_right").click(function(){
            var threeMenu = $('.nav.nav-tabs.three-mnue.d_nav_tabs');
            var allThreeMenuLi = threeMenu.find('li');
            var allLi = allThreeMenuLi.length;
            var visibleLastIndex = threeMenu.find('li:visible:last').index();
            // 是否到尾部
            if(visibleLastIndex === allLi - 1) {
                // $.messager.alert('提示', '不要点了... 右边真的没有了', 'info');
                return false;
            }
            for(var m = 0; m <= visibleLastIndex; ++m) {
                // 清除添加的首个元素的偏移
                allThreeMenuLi.eq(m).css('margin-left', '');
                allThreeMenuLi.eq(m).hide();
            }
            // 显示后面的菜单
            var currentWidth = 40;
            var contentWidth = $('#content').width();
            for(var n = visibleLastIndex + 1; n < allLi; ++n) {
                currentWidth += (allThreeMenuLi.eq(n).width() + 52);
                if(currentWidth > contentWidth) {
                    break;
                } else {
                    n === visibleLastIndex + 1 && n !== 0 && allThreeMenuLi.eq(n).css('margin-left', '15px');
                    allThreeMenuLi.eq(n).show();
                }
            }
        });
        $("#three_menu_left").click(function(){
            var threeMenu = $('.nav.nav-tabs.three-mnue.d_nav_tabs');
            var allThreeMenuLi = threeMenu.find('li');
            var allLi = allThreeMenuLi.length;
            var visibleFirstIndex = threeMenu.find('li:visible:first').index();
            var visibleLastIndex = threeMenu.find('li:visible:last').index();
            // 是否到尾部
            if(visibleFirstIndex === 0) {
                // $.messager.alert('提示', '不要点了... 左边真的没有了', 'info');
                return false;
            }
            for(var m = visibleFirstIndex; m <= visibleLastIndex; ++m) {
                // 清除添加的首个元素的偏移
                allThreeMenuLi.eq(m).css('margin-left', '');
                allThreeMenuLi.eq(m).hide();
            }
            // 显示前面的菜单
            var currentWidth = 40;
            var contentWidth = $('#content').width();
            for(var n = visibleFirstIndex - 1; n >= 0; --n) {
                currentWidth += (allThreeMenuLi.eq(n).width() + 52);
                if(currentWidth > contentWidth) {
                    n >= 1 && n < allLi && allThreeMenuLi.eq(n + 1).css('margin-left', '15px');
                    break;
                } else {
                    allThreeMenuLi.eq(n).show();
                }
            }
        });
        //$('[name=openlink]').tooltip({ 'position':'top'});
    </script>
</div>
</div>
{/include file='layouts/menujs.tpl'/}
{/include file='visualtpl/list.tpl'/}
{/include file='visualtpl/chart.tpl'/}
{/include file="layouts/footer.tpl"/}

{/if $allcontent neq '' /}
  <script>
      var allcontent = {/$allcontent/};
  </script>
{/else/}
  <script>
      var allcontent = false;
  </script>
{//if/}
