<script type="text/javascript">

  function createFrame(obj){
    var html = '<iframe src="'+obj.attr('data-option')+'" marginheight="0" marginwidth="0" frameborder="0"   scrolling="auto" width="100%" height=800 id="iframepage" name="iframepage"></iframe>';
   obj.parent().parent().find('li').removeClass('active');
   obj.parent().addClass('active');
   $("#content").html('');
   $("#content").append(html);
  }
  //外链页面面包屑和页面跳转
  function breadMenu(threeMenu){
      var $sidebar = $('#sidebar');
      var $threeTab = $('.three-tab');
      var $threeLine = $threeTab.find('a');
      var $sub = threeMenu.closest('.submenu');
      //面包屑设置
      $('.web-breadcrumbs').html('<a class="first-bread">'+$sub.find('a span').text()+'</a> ><a class="second-bread">'+threeMenu.closest('ul').siblings('.two-tab').text()+'</a>>'+threeMenu.text()).css("top","0");
      //三级菜单样式
      $threeLine.addClass('tab-normal');
      threeMenu.removeClass("tab-normal");
      $sidebar.addClass('max-hide');
  }
  function setHeight(obj){
     obj.style.height = document.body.scrollHeight + 800;
  }
  $(function(){
    //菜单折叠打开功能
    $('.submenu > a').click(function(e){
      e.preventDefault();

        var submenu = $(this).siblings('ul');
        var li = $(this).parents('li');
        var submenus = $('#sidebar li.submenu ul');
        var submenus_parents = $('#sidebar li.submenu');
        //var  hideObj = $('#sidebar li.open');
        //hideObj.children('ul').hide();
       // hideObj.children("a").find('i').attr('class','pull-right glyphicon glyphicon-plus');
        if(submenu.is(":visible")){
          submenu.slideUp();
          //li.removeClass('open');
          $(this).addClass('icon-shousuojiahao').removeClass("icon-shousuojianhao");
        }else{
          submenu.slideDown();
          //submenus_parents.removeClass('open');
          //$(this).find('i').attr('class','pull-right glyphicon glyphicon-minus');
          $(this).addClass('icon-shousuojianhao').removeClass("icon-shousuojiahao");
          li.addClass('open');
        }
    });
    // 2016-12-23 增加优供概要数据菜单打开
    $('.submenus > a').click(function(e){

        var submenu = $(this).siblings('ul');
        var li = $(this).parents('li');
        if($(".white-menu-three").hasClass("hidden")){
          $(".white-menu-three").show();
          $(".white-menu-three").removeClass("hidden");

        }else{

          $(".white-menu-three").show();
          // $(".white-menu-three").addClass("hidden");
          li.addClass('open');
        }
    });

    $('.submenu > ul > li > a').click(function(){
       var type = $(this).attr('data-type');

       if(type ==2){
         var html ="";
         html +='<ul class="nav nav-tabs">';
         html +='<li class="active">';
         html +='<a>'+$(this).text()+'</a>';
         html +='</li>';
         html +='</ul>';
         html += '<iframe src="'+$(this).attr('data-option')+'" marginheight="0" marginwidth="0" frameborder="0" scrolling="auto" width="100%" height=800 id="iframepage" name="iframepage" onload="setHeight(this)"  ></iframe>';
         $(this).parent().parent().find('li').removeClass('active');
         $(this).parent().addClass('active');
         $("#content").html('');
         $("#content").append(html);
        }
       // } else if(type == 'frame'){
       //   createFrame($(this));
       // }
    });
    $('body').on('click','.openurl',function(){
        var $this = $(this);
        $(this).parent().siblings().removeClass('active');
        $(this).parent().addClass('active');
        var html = "";
        var frameHeight = $(window).height() - $('.navbar').height() - $('.nav-tabs').height()-5;
        html += '<iframe src="' + $(this).attr('data-option') + '" marginheight="0" marginwidth="0" frameborder="0" scrolling="auto" width="100%" height='+frameHeight+' id="iframepage" name="iframepage" onload="setHeight(this)"  ></iframe>';
        if ($('#iframepage').length > 0 ) {
                $('#iframepage').attr('src', $(this).attr('data-option'));
                if ($('.muneIcon').css('display')!='none') {
                    breadMenu($this);
                }
            } else {
                if ($('.muneIcon').css('display')!='none'){
                    breadMenu($this);
                }
                $('.rightreport').html('');
                $('.rightreport').append(html);
            }

        //记录外链记录访问日志
        var menu_id=$(this).attr('menu-id');

        var sendata={'menu_id':menu_id,"openurl":$(this).attr('data-option')};
        var url="/tool/BehaviorLog";
        $.ajax({
            type: "get",
            url: url,
            data:sendata,
            //async: false,
            dataType: "json",
            success: function (result) {
                console.log(result)
            }
        });


    });
    if("undefined" == typeof params){
        var conf = {/json_encode($confArr)/} || {type:null};
        var type = conf.type;
        //pc点击二级菜单需要在此点击tab，移动端不需要
        if ($('.muneIcon').css('display')=='none'){
            if(type != 9){
              $('.openurl').eq(0).click();
            }
        }else{
            if(type != 9){
                $('.web-breadcrumbs').text('首页');
            }
        }

     }
  })
  $("#header_hidden").show();
  $("#menu_hidden").show();
</script>
