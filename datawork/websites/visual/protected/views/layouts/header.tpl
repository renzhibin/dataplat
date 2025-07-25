<!DOCTYPE html>
<html lang="en">
    <head>
        <title>{/env('TITLE')/}</title>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1, maximum-scale=1,user-scalable=no">
        {/include file="layouts/lib.tpl"/}
        <style type="text/css">
            .navbar-right li{ height: 40px;}
            .navbar-right li a{}
            .dropdown-menu .divider{ margin: 0px;}
            .muneIcon{
                display: none;
            }
            .dropdown-toggle{
                font-size:14px;
                margin-right:10px;
            }
            .navbar-nav a.web-title{
                font-size:18px;
                font-weight:bolder;
                padding: 0;
                height:40px;
                line-height:40px;
            }
            @media screen and (max-width: 768px){
                /*nav*/
                .navbar-header img{
                    width: 58px;
                    height: 40px;
                    position: absolute;
                    left: 1.8rem;
                }
                .navbar-fixed-top .dropdown-menu{
                    position: relative;
                    top:10px;
                }
                .navbar-fixed-top .pcmap{
                    display: none;
                }
                .navbar-inverse {
                    height: 40px;
                }
                .container-fluid{
                    position: fixed;
                    margin-left: auto;
                    width: 100%;
                    background-image: -webkit-gradient(linear, left top, left bottom, from(#0f59b1), color-stop(0.5, #155eb1), to(#124da1));

                }
                .muneIcon{
                    position: absolute;
                    top: 0;
                    display: block;
                    float: left;
                    font-size: 32px;
                    color: #fff;
                    margin: 8px -11px;
                }
                .tooltip {
                    top: 36px!important;
                    background-color: transparent;
                    border-color: transparent;
                    color: #404040;
                }
                /*用户信息*/
                .collapse{
                    display: block;
                    visibility: visible;
                }

                .dropdown-toggle{
                    margin-right:0;
                }
                .navbar-right{
                    position: absolute;
                    top: -22px;
                    right: 0;
                    font-size: 0.1rem;
                }
                .navbar-left{
                    position: absolute;
                    top:0;
                    left: 3.1rem;

                }
                /*网站标题*/
                .web-title{
                    /* 2016-12-12 取消了移动端的页头标题尺寸大小 */
                    /*font-size: 0.4rem!important;*/
                }
                .navbar-right li{
                    height: 23px;
                    width: 110px;
                }
                a.dropdown-toggle {
                    white-space: nowrap;
                    overflow: hidden;
                    text-align: right;
                    margin-right: 2px;
                    text-overflow: ellipsis;
                }
                /*tab*/
                .phone-tab {
                    z-index: 1;
                    width: 10rem;
                    position: relative;
                    top: 30px;
                    text-align: center;
                    height: 30px;
                    line-height: 30px;
                    font-size: 20px;
                }
                .phone-tab .list,.phone-tab .chart{
                    display: inline-block;
                    width: 4.8rem;
                    /*margin: 1px;*/
                    border: 1px #e7e7e7 solid;
                }


                .tab-normal{
                    /*把 a.active 样式盖过去*/
                    background-color: #fff!important;
                    color: #666!important;
                }
                .dropdown-menu{
                    float:left\9;
                }
            }
        </style>
    </head>
    <body>
{/if $allcontent eq '' /}
  <nav class="navbar navbar-inverse navbar-fixed-top" style='position:{/if $isMobile /}relative{/else /}fixed{//if/};margin-bottom:0px' role="navigation">
      <div class="container-fluid ">
          {/if $isMobile /}
          <div class="navbar-header" style="background:#0081AD;"><span class="navbar-brand" style='padding:3px 0px 0px 0px;height:40px' >
        <!--<img src='/assets/img/logo.png'  width="78px" height='45px'/>-->
        </span>
          </div>
          <i class='iconfont icon-guanlianniu muneIcon' data-toggle="tooltip" data-placement="bottom" title="菜单"></i>
          <div class="navbar-collapse" style="margin-right: 0\9 ">
              <ul class="nav navbar-nav navbar-left" style="width:150px; float:left;left:3.1rem;z-index: 999;">
                  <li><a class="web-title" href="/visual/index" style="color: #fafafa;">{/env('TITLE')/}</a></li>
              </ul>
              <ul class="nav navbar-nav navbar-right" style="line-height:40px;height:40px; float:right;">
                  <!--<li  data-placement="bottom" data-toggle="popover"
                 data-content="1.speed公众帐号:data平台<br><span style='white-space:nowrap'>路径:speed客户端->通迅录->公众帐号</span><br><span style='color:gray;font-size:12px;'>注:speed升级后用户不用二次登陆。</span><br>
                 2.直接访问<img src='/assets/img/erweima.png' width='100px' height='100px' />" >
                  <a href="javascript:void(0);"><i class="glyphicon glyphicon-phone"></i>移动版</a>
                </li>-->
                  <li><a style="padding:1px 2px 4px 2px;line-height: 40px;"></a></li>
                  {/if $show_sitemap ==1 || $is_admin == 1/}
                  <!--
                  <li class="max-hide" style="display: inline-block"><a style="padding:1px 2px 4px 2px;line-height: 40px; " target="_blank" href="/visual/ReportSitemap">报表地图</a></li>
                  <li class="max-hide" style="display: inline-block" ><a style="padding:1px 2px 4px 2px;line-height: 40px">|</a></li>
                  -->
                  {//if/}

                  <!--<li  data-placement="bottom" data-toggle="popover"
           data-content="{/if $is_admin ==1/}<a target='_blank' href='http://shiji.meiliworks.com/renzhibin/datameiliwork/wikis/manuals'>帮助文档</a>{//if/}<br>data用户群：379359600" >
            <a href="javascript:void(0);">问题求助</a>
          </li>
          <li><a style="padding:1px 2px 4px 2px">|</a></li>
          -->
                  <li  class="dropdown" id="profile-messages" >
                      <a title="" href="#" data-toggle="dropdown" data-target="#profile-messages" class="dropdown-toggle"><i class="icon icon-user" style="color: #fafafa;"></i>
                          <span class="text" style="line-height: 40px;font-size: 14px;color: #fafafa;">{/Yii::app()->user->name/}</span><b class="caret name-user"></b></a>
                      <ul class="dropdown-menu" >
                          <!--
                        <li><a target="_blank" href="http://speed.meilishuo.com/user"> 个人信息</a></li>
                        <li class="divider"></li>
                        <li><a target="_blank" href="http://speed.meilishuo.com/time/time_manage">我的日程</a></li>
                        <li class="divider"></li>
                        -->
                          <li><a  href="{/AuthService::SsoLogout()/}">退出登录</a></li>
                      </ul>
                  </li>
              </ul>
          </div>
          {//if/}
      </div>
      {/if !$isMobile /}
        <i class='iconfont icon-guanlianniu muneIcon' data-toggle="tooltip" data-placement="bottom" title="菜单"></i>
      <div class="navbar-collapse" style="background: #008FC0;">
        <ul class="nav navbar-nav navbar-left change-nav" style="width:200px; float:left">
            <li class="change-Lileft">
               <a class="change-h1" href="/visual/index" style="background:url(/assets/img/d_visual-png/{/env('LOGO')/}) no-repeat center;background-size:cover;"></a>
              <!--  <a class="change-split" href="/visual/index">|</a>
               <a class="change-h2" href="/visual/index">BI平台</a> -->
            </li>
        </ul>
        <ul class="nav navbar-nav navbar-right change-nav-right" style="line-height:40px;height:40px; float:right;">
          <li><a style="padding:1px 2px 4px 2px;line-height: 40px;"></a></li>
            <li>
               <!--
                <a title="" href="http://wiki.xiaozhu.com/pages/viewpage.action?pageId=7537258" target="_blank">
                     
                    <span class="text" style="line-height: 40px;font-size: 14px;height: 50px !important;line-height: 50px;padding-left: 10px;color: #fff;">权限申请流程</span></a>
                   -->
            </li>
            <li>
                <!--
                <a title="" href="/visual/reportsitemap">
                
                <span class="text" style="line-height: 40px;font-size: 14px;height: 50px !important;line-height: 50px;padding-left: 10px;color: #fff;">敏感报表地图</span></a>
               --> 
            </li>
          <li  class="dropdown" id="profile-messages" >
              <a title="" href="#" data-toggle="dropdown" data-target="#profile-messages" class="dropdown-toggl name-content"><i class="icon icon-user"></i>
              <span class="text" style="line-height: 40px;font-size: 14px;">{/Yii::app()->user->name/}</span><b class="caret name-user"></b></a>
              <ul class="dropdown-menu d_logout" >
                  {/if $login_type /}
                  <li><a  href="/site/logout">退出登录</a></li>
                  <!-- <li><a  href="/site/PwdPage">修改密码</a></li> -->
                  {/else/}
                <li><a  href="/site/logout">退出登录</a></li>
                  {//if/}
              </ul>
          </li>
        </ul>
      </div>
      {//if/}
  </nav>
  <!-- <nav style="width: 100%;height: 50px;"></nav> -->
<!--手机端tab -->
<div class="phone-tab hide">
    <div class="list tab-gray">表格数据</div>
    <div class="chart tab-blue">图表数据</div>
</div>
{//if/}
<script type="text/javascript">
    $(function(){
        var isWhiteTable;
        var $threeTab = $('.three-tab');
        var $sidebar = $('#sidebar');
        var $threeLine = $threeTab.find('a');
        var collect = {/$collect|json_encode/}; //收藏报表

        $('[data-toggle="popover"]').popover({html:true});
        //----------------js判断（muneIcon有没有）
        if ($('.muneIcon').css('display')!='none'){
            $('#chartTpl').addClass('tab-panel');
            //点击二级菜单不进行跳转
            thirdActive();
            bindEvent();

            FastClick.attach(document.body);
             //屏幕旋转后重新计算rem
            var screenEvt = "onorientationchange" in window ? "orientationchange" : "resize";

            window.addEventListener(screenEvt, function() {
                window.location.reload();
            }, false);
            //菜单提示
            //$('.muneIcon').tooltip('toggle');

            //移动端+首页/visual/index/+没有收藏展示
            if($.isEmptyObject(collect) && window.location.pathname=="/visual/index"){
                $sidebar.toggleClass('max-hide');
                $('.muneIcon').toggleClass('icon-guanlianniu icon-chahao-copy');
            }

            //web-title移动端标题不能改
            $(".web-title").removeAttr("href");
            refreshRem();
        }else{
            //pc外链显示正常需去掉<ul>
            $('.web-three-tab').remove();
        }

        function thirdActive(){
            $('a.two-tab').removeAttr('href');
            $threeTab.addClass('hide');

            var urlpath = window.location.pathname;
            var urlArr = urlpath.split('/');
            $threeLine.each(function(k,v) {
                //id与pathname中的匹配即为选中
                if(urlArr[urlArr.length-1]==$(v).data('id') ){
                    var thirdBread = $(v).text();
                    if(thirdBread){
                        //第三级面包屑
                        $('.third-bread').text("> "+$(v).text());
                    }else{
                        $('.third-bread').addClass("hide");
                        //添加优供
                        //$('.clloect-gy').removeClass('hide');
                    }
                }else{
                    $(v).addClass('tab-normal');
                }
            });

            $threeTab.each(function(k,v) {
                if(urlArr[4]==$(v).data('id')){
                    $(v).toggleClass('hide');
                }
            });
        }
        {/if $isWhiteTable/}
            isWhiteTable = {/$isWhiteTable/};
        {/else/}
            isWhiteTable = 0;
        {//if/}
        function bindEvent(){
            $('body').on("click", ".muneIcon, .web-breadcrumbs a", function () {
                //点击图标切换菜单+三级菜单
                $sidebar.toggleClass('max-hide');
                $('.muneIcon').toggleClass('icon-guanlianniu icon-chahao-copy');

                if (!$sidebar.hasClass('max-hide')){
                    $('html,body').css({'overflow':'hidden','height':'110%'});
                }else{
                    $('html,body').css({'overflow':'auto','height':'auto'});
                }
                if(isWhiteTable === 1){
                    $(".submenus > a").click();
                }
            }).on("click", ".phone-tab .list,.phone-tab .chart", function () {
                var $this = $(this);
                $this.removeClass('tab-blue').addClass('tab-gray');
                $this.siblings().removeClass('tab-gray').addClass('tab-blue');
                //图表和表格切换
                if($this.hasClass('list')){
                    $('#chartTpl').addClass('tab-panel');
                    $('.tablelist').removeClass('tab-panel');
                }else{
                    $('#chartTpl').removeClass('tab-panel');
                    $('.tablelist').addClass('tab-panel');
                }
            }).on("click", ".two-tab", function (e) {
                //点击二级菜单出现相应的三级菜单
                var $this = $(this);
                $threeTab.addClass('hide');
                $this.closest('#sidebar').find('li').removeClass('active');
                if($(e.target).attr("id") !=="whiteMenu"){
                    $this.closest('li').addClass('active');
                }
                $this.siblings('.three-tab').removeClass('hide');
            }).on("click", ".web-filter", function () {
                //筛选默认缩进
                var $configBox = $(this).closest('.configBox');
                //查询与自定义互斥
                if( $configBox.find('.my-tab-customkey').css('display')!='none'){
                    $configBox.find('.my-tab-customkey').css('display','none');
                }
                $configBox.find('.filter').slideToggle(400);
                //按钮设置颜色
                $(this).toggleClass('webBtn-select');
                $configBox.find('.customtitle').removeClass('webBtn-select');
            }).on("click", ".customkey-check", function () {
                $(this).addClass('webBtn-select');
                $(this).siblings('a').removeClass('webBtn-select');
            }).on("touchend", "select.selectChange", function (e) {
                //safari下拉框闪退
                e.preventDefault();
                $(this).focus();
            }).on("longtap", "select.selectChange", function (e) {
                e.preventDefault();
                $(this).focus();
            }).on("click", ".ygcp", function () {
                //外链，页面不跳转,手动设置三级面包屑
                var $this = $(this);
                var $sub = $this.closest('.submenu');
                $('.first-bread').text($sub.find('a span').text());
                $('.second-bread').text($this.closest('ul').siblings('.two-tab').text());
                $('.third-bread').text("> "+$this.text());
                $('body').find('.phone-tab').addClass('hide');
                //调整面包屑位置
                $('.web-breadcrumbs').css("top","0");
                $threeLine.addClass('tab-normal');
                $this.removeClass("tab-normal");
                $sidebar.addClass('max-hide');
                $('.muneIcon').toggleClass('icon-guanlianniu icon-chahao-copy');
            });
        }

        //设置html font-size
        //document.documentElement.style.fontSize = (document.documentElement.clientWidth / 375 * 10).toFixed(1) + 'px';
        function refreshRem() {
            var width = document.documentElement.getBoundingClientRect().width;
            if (width > 768) { // 最大宽度
                width = 768;
            }
            var rem = width / 10; // 将屏幕宽度分成10份， 1份为1rem
            document.documentElement.style.fontSize = rem + 'px';
        }
    });
 </script>
