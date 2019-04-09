{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">
<style type="text/css">
    .search_input {
        position: relative;
        margin-top: 15px;
        margin-bottom: 10px;
    }

    .search_input input{
        height: 30px;
        line-height: 30px;
        width: 230px;
        margin-right: 10px;
    }
    //#breadcrumbs-one{
    //   margin-top: 50px
    //}
    .item {
        padding: 10px 0px
    }
</style>
<script src="/assets/js/project.js?version={/$version/}"></script>
<script src="/assets/js/easyTable.js?version={/$version/}"></script>

<!--z-tree插件-->
<script src="/assets/lib/ztree/js/jquery.ztree.core.js?version={/$version/}"></script>
<script src="/assets/lib/ztree/js/jquery.ztree.excheck.js?version={/$version/}"></script>
<script src="/assets/lib/ztree/js/jquery.ztree.exhide.js?version={/$version/}"></script>
<link rel="stylesheet" href="/assets/lib/ztree/css/zTreeStyle.css" type="text/css">
<div>
    {/include file="layouts/menu.tpl"/}
    <div id='right'>
        <div id="content" class="content-top">
            <!--面包屑效果-->
            <div id="breadcrumbs-one" >
                {/foreach from = $guider item= place key=key/}
                    {/if $guider[0] eq $place /}
                        <span><a href="{/$place.href/}">{/$place.content/}</a></span>
                    {/else/}
                        {/if $place.href eq '#'/}
                            <span></span>
                            <span>{/$place.content/}</span>
                        {/else/}
                            <span></span>
                            <span><a href="{/$place.href/}">{/$place.content/}</a></span>
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
                    <label>姓名：</label>
                    <select name='user_id' multiple style='width:200px; visibility: hidden' placeholder="--请选择--">
                        {/foreach from =$userList item= item key=key/}
                        <option value="{/$item.id/}">{/$item.realname/}（{/$item.user_name|regex_replace:"/@qufenqi.com|@qudian.com/":""/}）</option>
                        {//foreach/}
                    </select>
                    <label>手机号：</label>
                    <input type="text" class="report-id" name="iphone" value=""/> 
                    <label>用户类型</label>
                    <select style="width:90px" name="group">
                        <option value="all">--all--</option>
                        <option value="5">超级管理员</option>
                        <option value="2">开发用户</option>
                        <option value="3">核心用户</option>
                        <option value="normal">普通用户</option>
                    </select>
                    <label>报表：</label>
                    <select name='reportIds' multiple class='userlist' style='width:200px;visibility: hidden' placeholder="--请选择--">
                        {/foreach from =$roleList item= item key=key/}
                            <option value='{/$item.id/}'>{/$item.id/}~{/$item.cn_name/}~{/$item.role_id/}</option>
                        {//foreach/}
                    </select>
                    <button class="search btn btn-primary btn-sm">搜索</button>
                
                </div>
                <div style='position:relative;'>
                     
                    <table id ='datagard'></table>
                </div>
                <div class='item'>
                    <button class='btn btn-primary addPower' roleType='add'>批量添加权限</button>
                    <button class='btn btn-danger addPower' roleType ='del'>批量删除权限</button>
                </div>
            </div>
        </div>
    </div>
    <!--添加关系弹框-->
    <div id="userReport" style='display:none' >
        <table class='table table-condensed table-bordered'>
            <tr>
                <td class='table_left'>用户</td>
                <td>
                    <select name='user' multiple style='width:300px' placeholder="--请选择--">
                        {/foreach from =$userList item= item key=key/}
                        <option value="{/$item.id/}">{/$item.realname/}（{/$item.user_name|regex_replace:"/@qufenqi.com|@qudian.com/":""/}）</option>
                        {//foreach/}
                    </select>
                </td>
            </tr>
            <tr>
                <td class='table_left'>报表</td>
                <td>
                    <select name='role' multiple style='width:300px' placeholder="--请选择--">
                        {/foreach from =$roleList item= item key=key/}
                        <option value="{/$item.role_id/}">{/$item.id/}~{/$item.cn_name/}</option>
                        {//foreach/}
                    </select>
                </td>
            </tr>

        </table>
        <input type='hidden' name="roleType"/>
    </div>
    <div class='ztreeBox' style="display: none;">
        <ul id="treeDemo" class="ztree"></ul>
        <input type='hidden' name='user_id'/>
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
                <td class='table_left'>用户类型</td>
                <td>
                    <select name="group">
                        <option value="5">超级管理员</option>
                        <option value="2">开发用户</option>
                        <option value="3">核心用户</option>
                        <option value="" selected="selected">普通用户</option>
                    </select>
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
<style>
    .ztreeBox{
        height: 400px;
        overflow:  auto;
    }
</style>
{/include file="layouts/menujs.tpl"/}
<script type="text/javascript">
    function showLink(value,row,index){
        var disable = '';
        if (row.group == 2 || row.group == 3 || row.realname.indexOf('超级管理员') >= 0) {
            disable = 'disabled="disabled"';
        }
        return "<button data-user_id =" + row.id + "  data-realname='" + row.realname + "' class='btn btn-default user_edit' >编辑</button> <button data-user_id =" + row.id + " data-realname='" + row.realname + "' class='btn btn-primary role_edit'" + disable + ">权限管理</button> ";
    }
    var setting = {
        check: {
            enable: true
        },
        data: {
            key: {
                title: "title"
            },
            simpleData: {
                enable: true
            }
        },
        callback: {
            onCheck: function(e, treeId, treeNode){
                var reportNode =[];
                var nodes = treeObj.getCheckedNodes();
                for(var i=0; i< nodes.length; i++){
                    if( nodes[i].src_id){
                        reportNode.push(nodes[i].id);
                    }
                }
                // console.log(reportNode)
            }
        }
    };
    window.treeObj ='';
    $('select').select2({
            allowClear: true
    });
    $('select').css({"visibility":"visible"});
    $(function(){

        var  coloumConfig  = [
            {key:'id',name:'用户ID',width:110},
            {key:'realname',name:'姓名',width:190},
            {key:'user_name',name:'邮箱',width:190},
            {key:'iphone',name:'手机号',width:120},
            {key:'oprate',name:'操作',formatter:showLink}
        ];
        var options  ={
            id:'datagard',
            config:coloumConfig,
            url:'/privilege/indexData',
            //搜索框 
            search:[
                {type:'select',key:'user_id'},
                {type:'input',key:'iphone'},
                {type:'select',key:'reportIds'},
                {type:"select",key:"group"}
            ],
            otherconf:{
                 checkbox:true //是否显示勾选框
            }
        } 
        var  table= new Table(options);
        
        //搜索 
        $('body').find(".search").click(function(){
               table.reloadData();
        }); 
        // 回车 
        $(document).on("keydown", function (e) {
            if (e.which ===13){
                table.reloadData();
            }
        });
        //添加报表
        $('#userReport').show().dialog({
            title: '批量操作',
            width: 450,
            closed: true,
            cache: false,
            modal: true,
            buttons: [{
                text:'确定',
                iconCls:'icon-ok',
                handler:function(){
                     
                    var roleType = $('input[name=roleType]').val(); 
                    var user_id = $('select[name=user]').val();
                    var role_id = $('select[name=role]').val();
                    
                    if(!role_id){
                        $.messager.alert('提示','请选择要操作的权限','info');
                        return false;
                    }
                    if(!user_id){
                        $.messager.alert('提示','用户信息不能为空','info');
                        return false;
                    }
                    if(roleType =='del'){
                        $.messager.confirm('提示', '确定删除用户权限吗？', function(r){
                            $.post('/privilege/delUserRolesMultiple', {'user_id':user_id,'role_id':role_id},function(data){
                                $('body').unmask();
                                if(data.status ==0){
                                    $.messager.alert('提示',data.msg,'info');
                                    $('#userReport').dialog('close');
                                    table.reloadData();
                                }else{
                                    $.messager.alert('提示',data.msg,'info');
                                }
                            }, 'json');
                        });
                    }else{
                        $.post('/privilege/AddUserRolesMultiple', {'user_id':user_id,'role_id':role_id},function(data){
                            $('body').unmask();
                            if(data.status ==0){
                                $.messager.alert('提示',data.msg,'info');
                                $('#userReport').dialog('close');
                                table.reloadData();
                            }else{
                                $.messager.alert('提示',data.msg,'info');
                            }
                        }, 'json');
                    }
                    
                }
            },{
                text:'取消',
                handler:function(){
                    $('#userReport').dialog('close');
                }
            }]
        });

        $('.ztreeBox').show().dialog({
            title: '权限修改',
            width: 450,
            height:400,
            closed: true,
            cache: false,
            modal: true,
            buttons: [{
                text:'确定',
                iconCls:'icon-ok',
                handler:function(){
                    var addNodes = [];
                    var delNodes = [];
                    var nodes = window.treeObj.getChangeCheckedNodes();

                    if (nodes.length <= 0) {
                        $.messager.alert('提示', '您没有选择要赋予的权限', 'waring');
                        return;
                    }

                    for (var i = 0; i < nodes.length; i++) {
                        if (nodes[i].src_id) {
                            if (nodes[i].checked === true) {
                                addNodes.push(nodes[i].id);
                            } else {
                                delNodes.push(nodes[i].id);
                            }
                        }
                    }

                    // console.log('addNodes', addNodes);
                    // console.log('delNodes', delNodes);

                    var user_id = $('input[name=user_id]').val();
                    $.messager.confirm('提示', '确认所选权限吗？', function(r){
                        if(r){
                            $.post('/privilege/editPower', {'addNodes':addNodes,'delNodes':delNodes,user_id:user_id}, function (data) {
                                if (parseInt(data.status) === 0) {
                                    table.reloadData();
                                    $.messager.alert('提示', data.msg, 'info');
                                    $('.ztreeBox').dialog('close');
                                } else {
                                    $.messager.alert('提示', data.msg, 'info');
                                }
                            }, 'json');
                        }
                    });   
                }
            },{
                text:'取消',
                handler:function(){
                    $('.ztreeBox').dialog('close');
                }
            }]
        }) 

        //添加/删除报表分组
        $("body").on("click",'.addPower',function(e){
            var rows = $('#datagard').datagrid('getSelections');
            var roleType = $(this).attr('roleType');
            var ids =[];
            for(var i=0; i<rows.length; i++){
                ids.push(rows[i].id);
            }
            $('select[name=user]').select2('val',ids);
            $('select[name=role]').select2('val',[]);
            $('input[name=roleType]').val(roleType);
            open("userReport",e);
            $('.window').css({"top":"50%","left":"50%",transform:"translate(-50%,-50%)"});
        });
         
        $('body').on('click','.role_edit',function(e){
            var user_id = $(this).attr('data-user_id');
            var realname = $(this).attr('data-realname');
            $.post('/privilege/getUserRoleTree', {'user_id':user_id},function(re){
                 window.treeObj = $.fn.zTree.init($("#treeDemo"), setting, re.data);
                 $(".ztreeBox").panel({
                      title: "修改用户："+realname
                    }).dialog('open');
                 $('.window').css({"top":"50%","left":"50%",transform:"translate(-50%,-50%)"});
            }, 'json');
            $('input[name=user_id]').val(user_id);
        });
        $('body').on("click", ".user_edit", function (e) {
            var $form = $('#basereporat');
            // 获取用户信息
            var user_id = $(this).data('user_id');
            $.post('/privilege/getUser', {'user_id':user_id }, function (re) {
                if (re.status == 0) {
                    $form.find('input[name=id]').val(re.data.id);
                    $form.find('input[name=user_name]').val(re.data.user_name);
                    $form.find('select[name=group]').select2('val',re.data.group);
                    $form.find('input[name=realname]').val(re.data.realname);
                    $form.find('input[name=iphone]').val(re.data.iphone);
                    // 打开dialog
                    $("#basereporat").panel({
                        title: "修改用户"
                    }).dialog('open');
                }else{
                    $.messager.alert('提示','获取不到用户信息','info');
                }
                $('.window').css({"top":"50%","left":"50%",transform:"translate(-50%,-50%)"});
            }, 'json');
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
                    var group = $form.find('select[name=group]').val();
                    var realname = $form.find('input[name=realname]').val();
                    var iphone = $form.find('input[name=iphone]').val();
                    $.post('/privilege/ModifyUser', {'id':id, 'user_name':user_name, 'group':group, 'realname':realname, 'iphone':iphone}, function (data) {
                        $('body').unmask();
                        if (data.status == 0) {
                             $.messager.alert('提示', data.msg, 'info');
                             table.reloadData();
                        } else {
                            $.messager.alert('提示', data.msg, 'info');
                        }
                        $('#basereporat').dialog('close');
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
            var $form = $('#basereporat');
            $("#basereporat").panel({
                title: "添加用户"
            }).dialog('open');
            $form.find('input[name=id]').val('');
            $form.find('input[name=user_name]').val('');
            $form.find('input[name=group]').val('');
            $form.find('input[name=realname]').val('');
            $form.find('input[name=iphone]').val('');
            $('.window').css({"top":"50%","left":"50%",transform:"translate(-50%,-50%)"});
        });
        $('body').on("click", ".user_del", function () {
            var $this = $(this);
            var user_id = $this.data("user_id");
            $.messager.confirm('提示', '确认删除用户吗？', function(r){
                if(r){
                    $.post('/privilege/DelUser', {'user_id':user_id}, function (data) {
                        if (data.status == 0) {
                            table.reloadData();
                            $.messager.alert('提示', data.msg, 'info');
                        } else {
                            $.messager.alert('提示', data.msg, 'info');
                        }
                    }, 'json');
        
                }
                
            });
            
             
        });
    });

</script>
