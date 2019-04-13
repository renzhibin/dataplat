<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=3, user-scalable=no">
{/include file="layouts/lib.tpl"/}
<title>小猪数据分析平台</title>
<meta charset="UTF-8" />
</head>
<body>
{/if $allcontent eq '' /}
  <!--<nav class="navbar navbar-inverse navbar-fixed-top" style='position:relative;margin-bottom:0px' role="navigation">
    <div class="container-fluid">
       <div class="row">
         <div class="col-xs-2">
            <img src='/assets/img/logo.png' style="margin:3px 0px 0px -20px"  width="78px" height='45px'/>
         </div>
         <div class="col-xs-10">
           <div class="col-xs-9">
              <a href="/wap/index" style='font-size:18px;font-weight:bolder;height:45px;line-height:50px'>美丽说数据分析平台</a>
           </div>
           <div >
              <ul class="nav navbar-nav navbar-right" style="line-height:40px;height:40px">
                <li  class="dropdown" id="profile-messages" >
                    <a title="" href="#" data-toggle="dropdown" data-target="#profile-messages" class="dropdown-toggle" style='font-size:14px;'><i class="icon icon-user"></i>
                    <span class="text">{/Yii::app()->user->username/}</span><b class="caret"></b></a>
                    <ul class="dropdown-menu" style="position:absolute;right:0">
                      <li><a target="_blank" href="http://speed.meilishuo.com/user"> 个人信息</a></li>
                      <li class="divider"></li>
                      <li><a target="_blank" href="http://speed.meilishuo.com/time/time_manage">我的日程</a></li>
                      <li class="divider"></li>
                      <li><a href="/site/logout">退出登录</a></li>
                    </ul>
                </li>
              </ul>
           </div>
         </div>
       </div>
    </div>
  </nav>-->
{//if/}
  <style type="text/css">
  .navbar-right li{ height: 40px;}
  .navbar-right li a{}
  .dropdown-menu .divider{ margin: 0px;}
  </style>
  
