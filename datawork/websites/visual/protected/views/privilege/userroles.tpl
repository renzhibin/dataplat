{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">
<style type="text/css">
    .search_input {
        position: relative;
        top: 15px;
    }
    .search_input input{
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
                <!-- <button  class='btn btn-primary btn-sm addGroup'>
                    <i class='glyphicon glyphicon-plus'></i>&nbsp;添加用户分组关系
                </button> -->
                <button  class='btn btn-primary btn-sm addGroupMultiple'>
                    <i class='glyphicon glyphicon-plus'></i>&nbsp;添加用户分组关系—选择
                </button>
                <button  class='btn btn-primary btn-sm addGroupMultipleTag'>
                    <i class='glyphicon glyphicon-plus'></i>&nbsp;添加用户分组关系-逗号
                </button>
                <a href="/privilege/reportroles" target="_blank" class='btn btn-primary btn-sm'>查看报表分组</a>
                <a href="/privilege/user" target="_blank" class='btn btn-primary btn-sm'>查看用户</a>
                <!-- <button  class='btn btn-primary btn-sm addFirstMenu'>
                    <i class='glyphicon glyphicon-plus'></i>&nbsp;添加一级菜单
                </button>
                <a  class='btn btn-primary btn-sm' href='/menu/firstSort'>
                    <i class='glyphicon glyphicon-plus'></i>&nbsp;一级菜单排序
                </a>-->
                <div class="search_input">
                    <label>用户ID</label>
                    <input type="text" class="user-id" name="user-id" value="{/$smarty.get.user_id|escape/}"/> 
                    <label>分组ID</label>
                    <input type="text" class="role-id" name="role-id" value="{/$smarty.get.role_id|escape/}"/> 
                    <button class="search btn btn-primary btn-sm">搜索</button>
                </div>
                <div style='position:relative;padding-top: 10px'>
                    <table class="table table-bordered user-table">
                        <thead>
                        <tr class="table_header"> 
                            <th style='width:5%'>用户ID</th> 
                            <th style='width:20%'>用户姓名</th> 
                            <th style='width:20%'>分组ID</th> 
                            <th style='width:10%'>分组名称</th> 
                            <th style='width:25%'>操作</th> 
                        </tr> 
                        </thead>
                        <tbody>
                        {/foreach from =$userRolesList item=item key=key/}
                        <tr class="gradeX">
                            <td  style='width:5%'>{/$item.user_id/}</td>
                            <td  style='width:20%'>{/$item.user_name/}</td>
                            <td  style='width:10%'>{/$item.role_id/}</td>
                            <td  style='width:10%'>{/$item.role_name/}</td>
                            <td  style='width:10%'>
                                <button style='padding:3px 10px' data-id="{/$item.user_id/}" data-role="{/$item.role_id/}" class='btn btn-default btn-sm deluser'>
                                    删除</button></td>

                        </tr>
                        {//foreach/}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!--添加关系弹框-->
    <div id="basereporat" style='display:none' >
        <table class='table table-condensed table-bordered'>
            <tr>
                <td class='table_left'>用户id</td>
                <td>
                    <input type='text' name='user' class='inputall'/>
                </td>
            </tr>
            <tr>
                <td class='table_left'>分组id</td>
                <td>
                    <input type='text' name='role' class='inputall'/>
                </td>
            </tr>

        </table>
    </div>

    <div id="baseGroupMultipleTag" style='display:none' >
        <table class='table table-condensed table-bordered'>
            <tr>
                <td class='table_left'>用户id</td>
                <td>
                    <input type='text' name='user' class='inputall'/>
                </td>
            </tr>
            <tr>
                <td class='table_left'>分组id</td>
                <td>
                    <input type='text' name='role' class='inputall'/>
                </td>
            </tr>

        </table>
    </div>

    <div id="baseGroupMultiple" style='display:none' >
        <table class='table table-condensed table-bordered'>
            <tr>
                <td class='table_left'>用户id</td>
                <td>
                    <select name='user' multiple style='width:300px' placeholder="--请选择--">
                        {/foreach from =$userList item= item key=key/}
                        <option value="{/$item.id/}">{/$item.realname/}（{/$item.user_name|regex_replace:"/@.com|@.com/":""/}）</option>
                        {//foreach/}
                    </select>
                </td>
            </tr>
            <tr>
                <td class='table_left'>分组id</td>
                <td>
                    <select name='role' multiple style='width:300px' placeholder="--请选择--">
                        {/foreach from =$roleList item= item key=key/}
                        <option value="{/$item.role_id/}">{/$item.role_name/}</option>
                        {//foreach/}
                    </select>
                </td>
            </tr>

        </table>
    </div>

    <!--添加添加一级菜单-->
    <div id="addFirstMenuModal" style='display:none' >
        <table class='table table-condensed table-bordered'>
            <tr>
                <td class='table_left'>一级菜单名称<b style='color:red'>*</b></td>
                <td>
                    <input type='text' name='first_menu' class='inputall'/>
                </td>
            </tr>
        </table>
    </div>
</div>
{/include file="layouts/menujs.tpl"/}
<script type="text/javascript">
    $(function(){
        //配置搜索按钮
        $('.user-table').dataTable({
            "searching": false,
            "iDisplayLength":10,
            "bJQueryUI": true,
            "sPaginationType": "full_numbers",
            "sDom": '<""l>t<"F"fp>',
            "bSort":false,
            "bPaginate":true,
            "oLanguage": {
                //'sSearch':'用户id:',
                "sLengthMenu": "每页显示 _MENU_ 条记录",
                "oPaginate":{
                    "sFirst":"第一页",
                    "sLast":"最后一页",
                    "sNext": "下一页",
                    "sPrevious": "上一页"
                },
                "sInfoEmtpy": "没有数据",
                "sZeroRecords": "没有检索到数据",
            }

        });

        $('body').on("click", ".deluser", function () {
            var $this = $(this);
            var user_id = $this.data("id");
            var role_id = $this.data("role");
            $.get('/privilege/DelUserRoles', {'user_id':user_id,'role_id':role_id},function(data){
                // $('body').unmask();
                if(data.status ==0){
                    location.reload();
                } else{
                    $.messager.alert('提示',data.msg,'info');
                }
            }, 'json');
        });

        //搜索 
        $('body').find(".search").click(searchword);

        function searchword() { 
            var query = {
                "user_id":$(".user-id").val(),
                "role_id":$(".role-id").val()
            };

            window.location.href = window.location.pathname + "?" + $.param(query); 
        }  
        
        // 回车 
        $(document).on("keydown", function (e) { 
            e.which === 13 && searchword(); 
        });  

        //用户关系
        $('#basereporat').show().dialog({
            title: '用户分组关系',
            width: 450,
            //height:'',
            closed: true,
            cache: false,
            modal: true,
            buttons: [{
                text:'确定',
                iconCls:'icon-ok',
                handler:function(){
                    var $form = $('#basereporat');
                    var user_id = $form.find('input[name=user]').val();
                    var role_id = $form.find('input[name=role]').val();
                    $.get('/privilege/AddUserRoles', {'user_id':user_id,'role_id':role_id},function(data){
                        $('body').unmask();
                        if(data.status ==0){
                            location.reload();
                        }else{
                            $.messager.alert('提示',data.msg,'info');
                        }
                    }, 'json');
                }
            },{
                text:'取消',
                handler:function(){
                    $('#basereporat').dialog('close');
                }
            }]
        });

        $('#baseGroupMultiple').show().dialog({
            title: '用户分组关系',
            width: 450,
            //height:'',
            closed: true,
            cache: false,
            modal: true,
            buttons: [{
                text:'确定',
                iconCls:'icon-ok',
                handler:function(){
                    var $form = $('#baseGroupMultiple');
                    var user_id = $form.find('select[name=user]').select2('val');
                    var role_id = $form.find('select[name=role]').select2('val');
                    if (user_id == "" || user_id == undefined || user_id == null) {
                        $.messager.alert('提示', '用户id不允许为空！', 'info');
                        return;
                    }
                    if (role_id == "" || role_id == undefined || role_id == null) {
                        $.messager.alert('提示', '分组id不允许为空！', 'info');
                        return;
                    }
                    $.get('/privilege/AddUserRolesMultiple', {'user_id':user_id,'role_id':role_id},function(data){
                        $('body').unmask();
                        if(data.status ==0){
                            location.reload();
                        }else{
                            $.messager.alert('提示',data.msg,'info');
                        }
                    }, 'json');
                }
            },{
                text:'取消',
                handler:function(){
                    $('#baseGroupMultiple').dialog('close');
                }
            }]
        });

        $('#baseGroupMultipleTag').show().dialog({
            title: '用户分组关系',
            width: 450,
            //height:'',
            closed: true,
            cache: false,
            modal: true,
            buttons: [{
                text:'确定',
                iconCls:'icon-ok',
                handler:function(){
                    var $form = $('#baseGroupMultipleTag');
                    var user_id = $form.find('input[name=user]').val();
                    var role_id = $form.find('input[name=role]').val();
                    if (user_id == "" || user_id == undefined || user_id == null) {
                        $.messager.alert('提示', '用户id不允许为空！', 'info');
                        return;
                    }
                    if (role_id == "" || role_id == undefined || role_id == null) {
                        $.messager.alert('提示', '分组id不允许为空！', 'info');
                        return;
                    }
                    if (!/^([0-9]+,)*[0-9]+$/.test(user_id)) {
                        $.messager.alert('提示', '用户id只允许数字或逗号(不允许连续逗号或开头结尾为逗号)！', 'info');
                        return;
                    }
                    if (!/^([0-9]+,)*[0-9]+$/.test(role_id)) {
                        $.messager.alert('提示', '分组id只允许数字或逗号(不允许连续逗号或开头结尾为逗号)！', 'info');
                        return;
                    }
                    user_id = user_id.split(',');
                    role_id = role_id.split(',');
                    $.get('/privilege/AddUserRolesMultiple', {'user_id':user_id,'role_id':role_id},function(data){
                        $('body').unmask();
                        if(data.status ==0){
                            location.reload();
                        }else{
                            $.messager.alert('提示',data.msg,'info');
                        }
                    }, 'json');
                }
            },{
                text:'取消',
                handler:function(){
                    $('#baseGroupMultipleTag').dialog('close');
                }
            }]
        });

        //添加关系
        $("body").on("click",'.addGroup',function(e){
            open("basereporat",e);
        });

        $("body").on("click",'.addGroupMultiple',function(e){
            open("baseGroupMultiple",e);
        });

        $("body").on("click",'.addGroupMultipleTag',function(e){
            open("baseGroupMultipleTag",e);
        });

        //添加一级菜单
        $("body").on("click",'.addFirstMenu',function(e){
            open("addFirstMenuModal",e);
        });

        $('#addFirstMenuModal').show().dialog({
            title: '添加一级菜单',
            width: 450,
            //height:'',
            closed: true,
            cache: false,
            modal: true,
            buttons: [{
                text:'确定',
                iconCls:'icon-ok',
                handler:function(){
                    var $form = $('#addFirstMenuModal');
                    var first_menu = $form.find('input[name=first_menu]').val();
                    $.get('/privilege/addFirstMenu', {'first_menu':first_menu},function(data){
                        $('body').unmask();
                        if(data.status ==0){
                            location.reload();
                        }else{
                            $.messager.alert('提示',data.msg,'info');
                        }
                    }, 'json');
                }
            },{
                text:'取消',
                handler:function(){
                    $('#addFirstMenuModal').dialog('close');
                }
            }]
        });


    });

</script>
































































