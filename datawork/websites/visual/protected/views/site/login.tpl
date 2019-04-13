<!DOCTYPE html>
<html lang="en">
    <head>
        <title>小猪数据分析平台</title><meta charset="UTF-8" />
        <link href="/assets/lib/bootstrap-3.3/css/bootstrap.min.css" rel="stylesheet" />
        <link href="/assets/css/bootstrap-over.css" rel="stylesheet" />
        <link href="/assets/css/login.css" rel="stylesheet" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    </head>
    <body>
        <div id="loginbox">
            <form id="loginform" method="post" class="form-vertical" action="/site/login">
                <div class="control-group normal_text"> <h1>小猪数据分析平台</h1></div>
                <div class="control-group">
                    <div class="controls">
                        <div class="main_input_box">
                            <input name="LoginForm[username]" type="text" placeholder="公司邮箱" />
                        </div>
                        <div class="main_input_box">
                            <input name="LoginForm[password]" type="password" placeholder="邮箱密码" />
                        </div>
                        {/if $loginError  neq ''/}
                        <div class="main_input_box" >
                            <div class="errorbox">{/$loginError/}</div>
                        </div>
                        {//if/}
                        <div class="main_input_box">
                            <div class='main_cont'>
                                <span class="pull-left">
                                    <!--
                                    <a class="flip-link btn btn-info" href="mailto:houyangyang@.com?subject=忘记密码了">忘记密码?</a>
                                    -->
                                </span>
                                <span class="pull-right">
                                    <a type="submit" href="#" class="btn btn-success" id="login"/> 登录</a>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <script src="/assets/lib/jquery.js"></script>
        <!--
        <script src="/assets/lib/jquery.md5.js"></script> -->
        <script src="/assets/lib/bootstrap-3.3/js/bootstrap.min.js"></script>
        <script src="/assets/js/login.js"></script>
    </body>
</html>
