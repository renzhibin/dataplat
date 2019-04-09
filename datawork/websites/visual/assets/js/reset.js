$("#resetBtn").on("click",function(){
    var $msg = $(".errorbox");
    var username = $("#username").val();
    var pwd = $("#pwd").val();
    var newPwd = $("#newPwd").val().trim();
    var again = $("#again").val();
    $msg.text("");

    if(username === "" || pwd === ""){
        $msg.text("用户名/原密码不能为空!");
    }else if(newPwd === "" || again === ""){
        $msg.text("新密码/重复输入不能为空!");
    }else if(newPwd !== again){
        $msg.text("两次输入的新密码不同,请重新输入!");
    }else{
      var value = newPwd;
      // 判断含有大写
      var hasUpper = /[A-Z]/.test(value);
      // 判断含有小写
      var hasLower = /[a-z]/.test(value);
      // 判断含有数字
      var hasNum = /[0-9]/.test(value);

      // 至少8个字符
      if (value.length > 7 && hasUpper && hasLower && hasNum) {
        $.ajax({
         url: '/site/ResetPwd',
         type: 'POST',
         dataType: 'JSON',
         timeout:10000,
         data:{
           username:username,
           pwd:pwd,
           newpwd:newPwd
         }
        })
        .done(function(response) {
            if(response.status === 0){
              alert("修改成功!");
              location.href="/visual/index";
            }else{
              $msg.text(response.msg);
            }
        })
        .fail(function() {
          $msg.text("修改密码失败,请重试!");
       });
      } else {
          $msg.text("新密码不符合规范，需长度大于8且同时含有大小写字母和数字!");
      }
    }
});
