<!DOCTYPE html>
<html lang="en">
    <head>
        <title>小猪BI平台</title><meta charset="UTF-8" />
        <link href="/assets/lib/bootstrap-3.3/css/bootstrap.min.css" rel="stylesheet" />
        <link href="/assets/css/bootstrap-over.css" rel="stylesheet" />
        <link href="/assets/css/login.css" rel="stylesheet" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    </head>

    <body>
        <div id="loginbox">
            <form id="loginform" method="post" class="form-vertical">
                <div class="control-group normal_text"> <h1>修改密码</h1></div>
                <div class="control-group">
                    <div class="controls">
                        <div class="main_input_box">
                            <input id="username" name="username" type="text" placeholder="用户名" />
                        </div>
                        <div class="main_input_box">
                            <input id="pwd" name="pwd" type="password" placeholder="原密码" />
                        </div>
                        <div class="main_input_box">
                            <input id="newPwd" name="newPwd" type="password" placeholder="新密码,长度大于8且含有数字和大小写" />
                        </div>
                        <div class="main_input_box">
                            <input id="again" name="again" type="password" placeholder="重复一次" />
                        </div>
                        <div class="main_input_box" >
                            <div class="errorbox"></div>
                        </div>
                        <div class="main_input_box">
                            <div class='main_cont'>
                                <span class="pull-left">
                                    <a class="flip-link btn btn-info" href="/visual/index">返回</a>
                                </span>
                                <span class="pull-right">
                                    <a type="submit" href="#" class="btn btn-success" id="resetBtn"/> 确定</a>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <script src="/assets/lib/jquery-2.1.1.min.js"></script>
        <script src="/assets/js/reset.js"></script>
    </body>
</html>
