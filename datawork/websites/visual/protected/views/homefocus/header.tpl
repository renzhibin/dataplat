<!DOCTYPE html>
<html lang="en">
<head>
    <title>首焦资源位申请流程化工具</title>
    <meta charset="UTF-8" />
    <link href="/assets/img/favicon.png" type="image/png" rel="icon"/>
    <link href="/assets/lib/bootstrap-3.3/css/bootstrap.min.css" rel="stylesheet" />
    <link href="/assets/lib/bootstrap-3.3/css/bootstrap-theme.min.css" rel="stylesheet" />

    <link href="/assets/homefocus/homefocus.css?version={/$version/}" rel="stylesheet" />
    <script src="/assets/lib/jquery-1.11.1.min.js"></script>
    <script src="/assets/lib/bootstrap-3.3/js/bootstrap.min.js"></script>

    <!--bootstrap-datepicker-->
    <script src="/assets/lib/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
    <script src="/assets/lib/bootstrap-datetimepicker/bootstrap-datetimepicker.zh-CN.js"></script>
    <link href="/assets/lib/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css" rel="stylesheet" />
    <script src="/assets/lib/doT.min.js"></script>

    <link rel="stylesheet" type="text/css" href="/assets/homefocus/webupload/webuploader.css">
    <script type="text/javascript" src="/assets/homefocus/webupload/webuploader.js"></script>
    <script type="text/javascript" src="/assets/homefocus/app_TypeAndParams.js?version={/$version/}"></script>

    <script src="/assets/homefocus/homefocus.js?version={/$version/}"></script>
</head>

<body>
<nav class="navbar navbar-inverse navbar-fixed-top" style='position:relative;margin-bottom:0px' role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
        <span class="navbar-brand" style='padding:3px 0px 0px 0px;height:40px' >
         <img src='/assets/img/logo.png'  width="78px" height='45px'/>
        </span>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav navbar-left">
                <li><a href="/AppHomefocus/index" style='font-size:18px;font-weight:bolder;height:45px;line-height:40px'>首焦资源位申请流程化工具</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right" style="line-height:40px;height:40px">

                <li  class="dropdown" id="profile-messages" >
                    <a title="" href="#" data-toggle="dropdown" data-target="#profile-messages" class="dropdown-toggle" style='font-size:14px;margin-right:10px'><i class="icon icon-user"></i>
                        <span class="text">{/Yii::app()->user->username/}</span><b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a target="_blank" href="http://speed.meilishuo.com/user"> 个人信息</a></li>
                        <li class="divider"></li>
                        <li><a target="_blank" href="http://speed.meilishuo.com/time/time_manage">我的日程</a></li>
                        <li class="divider"></li>
                        <li><a href="/AppHomefocus/logout">退出登录</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>