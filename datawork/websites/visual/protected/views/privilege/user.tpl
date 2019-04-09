{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">
<style type="text/css">
    .search_input {
        position: relative;
        top: 15px;
    }

    .search_input input {
        height: 30px;
        line-height: 30px;
        width: 230px;
        margin-right: 10px;
    }
</style>
<script src="/assets/js/project.js?version={/$version/}"></script>
<div>
    {/include file="layouts/menu.tpl"/}
    <div id='right'>
        <div id="content" class="content">
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
            <div class='container'>
                <button class='btn btn-primary btn-sm addUser'>
                    <i class='glyphicon glyphicon-plus'></i>添加用户
                </button>
                <div class="search_input">
                    <label>用户ID</label>
                    <input type="text" class="user-id" name="user-id" value="{/$smarty.get.user_id|escape/}"/> 
                    <button class="search btn btn-primary btn-sm">搜索</button>
                </div>
                <div style='position:relative;padding-top:35px'>
                    <table class="table table-bordered user-table">
                        <thead>
                        <tr class="table_header"> 
                            <th style='width:5%'>用户ID</th>
                             
                            <th style='width:20%'>用户账号</th>
                             
                            <th style='width:20%'>用户分组</th>
                             
                            <th style='width:10%'>用户姓名</th>
                             
                            <th style='width:25%'>用户电话</th>
                             
                            <th style='width:25%'>操作</th>
                             
                        </tr>
                         
                        </thead>
                        <tbody>
                        {/foreach from =$userRolesList item=item key=key/}
                        <tr class="gradeX">
                            <td style='width:5%'>{/$item.id/}</td>
                            <td style='width:20%'>{/$item.user_name/}</td>
                            <td style='width:10%'>{/$item.group/}</td>
                            <td style='width:10%'>{/$item.realname/}</td>
                            <td style='width:10%'>{/$item.iphone/}</td>
                            <td style='width:10%'>
                                <button style='padding:3px 10px' data-id="{/$item.id/}"
                                " class='btn btn-default btn-sm editorser'>编辑</button>
                                <button style='padding:3px 10px' data-id="{/$item.id/}"
                                " class='btn btn-default btn-sm deluser'>删除</button>
                            </td>

                        </tr>
                        {//foreach/}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!--添加关系弹框-->
    <div id="basereporat" style='display:none'>
        <table class='table table-condensed table-bordered'>
            <tr>
                <td class='table_left'>用户ID</td>
                <td>
                    <input type='text' name='id' class='inputall' disabled="disabled"/>
                </td>
            </tr>
            <tr>
                <td class='table_left'>用户账号</td>
                <td>
                    <input type='text' name='user_name' class='inputall'/>
                </td>
            </tr>
            <tr>
                <td class='table_left'>用户分组</td>
                <td>
                    <input type='text' name='group' class='inputall'/>
                </td>
            </tr>
            <tr>
                <td class='table_left'>用户姓名</td>
                <td>
                    <input type='text' name='realname' class='inputall'/>
                </td>
            </tr>
            <tr>
                <td class='table_left'>用户电话</td>
                <td>
                    <input type='text' name='iphone' class='inputall'/>
                </td>
            </tr>
        </table>
    </div>
</div>
{/include file="layouts/menujs.tpl"/}
<script type="text/javascript">
    $(function () {
        $('body').on("click", ".deluser", function () {
            var $this = $(this);
            var user_id = $this.data("id");
            $.get('/privilege/DelUser', {'user_id':user_id}, function (data) {
                if (data.status == 0) {
                    location.reload();
                } else {
                    $.messager.alert('提示', data.msg, 'info');
                }
            }, 'json');
        });

        //搜索 
        $('body').find(".search").click(searchword);

        function searchword() {
            var query = {
                "user_id": $(".user-id").val()
            };

            window.location.href = window.location.pathname + "?" + $.param(query);
        }

        $('body').on("click", ".editorser", function (e) {
            // 获取用户信息
            var row = $(this).parents('tr').children();
            var id = row.eq(0).html();
            var user_name = row.eq(1).html();
            var group = row.eq(2).html();
            var realname = row.eq(3).html();
            var iphone = row.eq(4).html();
            var $form = $('#basereporat');
            $form.find('input[name=id]').val(id);
            $form.find('input[name=user_name]').val(user_name);
            $form.find('input[name=group]').val(group);
            $form.find('input[name=realname]').val(realname);
            $form.find('input[name=iphone]').val(iphone);

            // 打开dialog
            $("#basereporat").panel({
                title: "修改用户"
            }).dialog('open');
        });

        // 回车 
        $(document).on("keydown", function (e) {
            e.which === 13 && searchword();
        });

        //用户
        $('#basereporat').show().dialog({
            title: '',
            width: 450,
            //height:'',
            closed: true,
            cache: false,
            modal: true,
            buttons: [{
                text: '确定',
                iconCls: 'icon-ok',
                handler: function () {
                    var $form = $('#basereporat');
                    var id = $form.find('input[name=id]').val();
                    var user_name = $form.find('input[name=user_name]').val();
                    var group = $form.find('input[name=group]').val();
                    var realname = $form.find('input[name=realname]').val();
                    var iphone = $form.find('input[name=iphone]').val();
                    $.get('/privilege/ModifyUser', {'id':id, 'user_name':user_name, 'group':group, 'realname':realname, 'iphone':iphone}, function (data) {
                        $('body').unmask();
                        if (data.status == 0) {
                            location.reload();
                        } else {
                            $.messager.alert('提示', data.msg, 'info');
                        }
                    }, 'json');
                }
            }, {
                text: '取消',
                handler: function () {
                    $('#basereporat').dialog('close');
                }
            }]
        });


        //添加用户
        $("body").on("click", '.addUser', function (e) {
            $("#basereporat").panel({
                title: "添加用户"
            }).dialog('open');
            //open("basereporat", e);
        });


    });

</script>
































































