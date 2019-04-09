{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">
<div>
    {/include file="layouts/menu.tpl"/}
    <div id='right'>
        <div id="content" class="content">
            <div data-spy="scroll" data-target="#navbar-example2" data-offset="0" class="scrollspy-example">
                <ul>
                    {/foreach from = $menuSensitiveTitle item= item key=key/}
                    <li class="submenu address h1">
                        <button type="button" class="btn btn-default for_parent">{/$key/}</button>
                        <ul>
                            {/foreach from = $item item= menuinfo key= mid/}
                            <li class="h3 tablee" id="position{/$key/}{/$mid/}">
                                {/$menuinfo.name/}
                                <ul>
                                    {/foreach from = $menuinfo.table item=tableinfo key= tbid/}
                                    <li class="auth{/$tableinfo.auth/} h5">
                                        {/$tableinfo.cn_name/}
                                        {/if $tableinfo.auth eq 1 /}
                                        (已有权限)
                                        {/else/}
                                        (暂无权限)
                                        {//if/}
                                    </li>
                                    {//foreach/}
                                </ul>
                            </li>
                            {//foreach/}
                        </ul>
                    </li>
                    {//foreach/}

                </ul>
            </div>
        </div>
    </div>
</div>
{/include file="layouts/menujs.tpl"/}
<style>
    .auth0{color:red}
    .auth1{color:green}

    .navbar-inverse .navbar-nav>.active>a, .navbar-inverse .navbar-nav>.active>a:hover, .navbar-inverse .navbar-nav>.active>a:focus {
        background-color:transparent;
    }
    .submenu {
        background:;
    }
    body {
        position: relative;
    }
    .tablee{
        position: relative;
        display: inline-block;
        vertical-align: top;
    }
    .max-show{
        display: none;
    }
    ul{
        /*在FF和IE8情况下，默认padding-left40px；只用padding-left为10px，可达到预期效果*/
        padding-left: 10px;
        /*用360浏览器测试了下（估计IE7也是这个情况，IE6应该没人用了吧，Mic都抛弃了），没有预期效果，需要加上margin-left: 10px;，达到预期效果*/
        /*list-style: none;*/
        margin-left: 10px;
    }
    .jian{
        border:2px dotted grey;
        padding: 10px;
    }
    .none{
        /*没有图片样式，即为默认样式*/
        list-style-image:none;
    }
    .scrollspy-example {
        position: relative;
        margin-top: 40px;
        overflow: auto;
    }
</style>
<script>
    $('.for_parent').click(function(){
        $(this).parent().click();
    })

    $('li.address').addClass('jia');
    //              在FF和IE8情况下， $('li.address').children('ul').addClass('none')，可达到预期效果。除了li.address，其他的均是默认样式。如果把class(none)的属性改为list-style:none，则FF和IE8同样达到预期效果，但是360没有，出现每个列表项前都有一个加号
    $('li.address').children('ul').addClass('none');
    //               在360下达不到，需要加入$('li.address').children().css('list-style', 'none')，如果把class(none)的属性改为list-style:none，如果加下面的语句，360同样没有效果，也是所有的列表项前有个加号。需先把class(none)的属性改为list-style-image:none，再list-style:none。似乎必须要回归原始的样式，才能清除样式。如果开始就改变成其他的样式图片，然后再list-style:none。则360下没有预期效果，依然是所有的列表项前出现加号。
    $('li.address').children().css('list-style', 'none');
    $('li.address').children().hide();
    $('li.address').each(function(index){
        $(this).click(function(event){
            if(this==event.target){
                if($(this).is('.jia')){
                    $(this).children().show();
                    $(this).removeClass('jia');
                    $(this).addClass('jian');
                    $(this).children('button').removeClass('btn-primary');
                    $(this).children('button').addClass('btn-default');
                    $(this).removeClass('jia');
                    $(this).addClass('jian');
                }else{
                    $(this).children().hide();
                    $(this).children('button').removeClass('btn-default');
                    $(this).children('button').addClass('btn-primary');
                    $(this).removeClass('jian');
                    $(this).addClass('jia');
                    $(this).children('button').show();

                }
                event.stopPropagation();
            }
        });
    });

    $('button').click();


    $('body').scrollspy({ target: '#navbar-example' });
    $('[data-spy="scroll"]').each(function () {
        var $spy = $(this).scrollspy('refresh');
    })
    $('#myScrollspy').on('activate.bs.scrollspy', function () {
        // do something…
    })
</script>
</body>