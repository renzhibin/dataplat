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
</style>
<script src="/assets/js/project.js?version={/$version/}"></script>
<script src="/assets/js/easyTable.js?version={/$version/}"></script>
<script src="/assets/js/until.js?version={/$version/}"></script>
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
                <button  class='btn btn-primary btn-sm addGroup'>
                    <i class='glyphicon glyphicon-plus'></i>添加分组
                </button>
                <button  class='btn btn-primary btn-sm addReport'>
                    <i class='glyphicon glyphicon-plus'></i>添加报表分组
                </button>
                <a href="/privilege/group" target="_blank" class='btn btn-primary btn-sm'>查看分组列表</a>
                <div class="search_input">
                    <label>分组ID</label>
                    <input type="text" class="role-id" name="role-id" value="{/$smarty.get.role_id|escape/}"/> 
                    <label>报表ID</label>
                    <input type="text" class="report-id" name="report-id" value="{/$smarty.get.report_id|escape/}"/> 
                    <button class="search btn btn-primary btn-sm">搜索</button>
                </div>
                <div style='position:relative;'>
                     
                    <table id ='datagard'></table>
                </div>
            </div>
        </div>
    </div>
    <!--添加关系弹框-->
    <div id="userReport" style='display:none' >
        <table class='table table-condensed table-bordered'>
            <tr>
                <td class='table_left'>分组id</td>
                <td>
                    <input type='text' name='role' class='inputall'/>
                </td>
            </tr>
           <tr>
                <td class='table_left'>报表id</td>
                <td>
                    <input type='text' name='report' class='inputall'/>
                </td>
            </tr>

        </table>
    </div>
    <div id="userGroup" style='display:none' >
        <table class='table table-condensed table-bordered'>
            <tr>
                <td class='table_left'>分组名称</td>
                <td>
                    <input type='text' name='group' class='inputall'/>
                </td>
            </tr>

        </table>
    </div>
</div>
{/include file="layouts/menujs.tpl"/}
<script type="text/javascript">
    function showLink(value,row,index){
        return "<button class='btn btn-default btn-xs deluser' data-id="+row.role_id+" data-report="+ $.trim(row.report_id)+">删除</button>";
    }
    $(function(){

        var  coloumConfig  = [
            {key:'role_id',name:'分组ID',width:110},
            {key:'role_name',name:'分组名称',width:190},
            {key:'report_id',name:'报表ID',width:120},
            {key:'report_name',name:'报表名称',width:220},
            {key:'oprate',name:'操作',formatter:showLink}
        ];
        var options  ={
            id:'datagard',
            config:coloumConfig,
            url:'/privilege/ReportRoleData',
            //搜索框 
            search:[
                {type:'input',key:'role-id'},
                {type:'input',key:'report-id'},
            ],
            otherconf:{
                checkbox:false
            }
        } 
        var  table= new Table(options);
        
        $('body').on("click", ".deluser", function () {
            var $this = $(this);
            var role_id = $this.data("id");
            var report_id = $this.data("report");
            $.get('/privilege/DelReportRoles', {'role_id':role_id,'report_id':report_id},function(data){
                // $('body').unmask();
                if(data.status ==0){
                    location.reload();
                } else{
                    $.messager.alert('提示',data.msg,'info');
                }
            }, 'json');
        });

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

        //报表关系
        $('#userReport').show().dialog({
            title: '分组报表',
            width: 450,
            //height:'',
            closed: true,
            cache: false,
            modal: true,
            buttons: [{
                text:'确定',
                iconCls:'icon-ok',
                handler:function(){
                    var $form = $('#userReport');
                    var report_id = $form.find('input[name=report]').val();
                    var role_id = $form.find('input[name=role]').val();
                    $.get('/privilege/AddReportRoles', {'report_id':report_id,'role_id':role_id},function(data){
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
                    $('#userReport').dialog('close');
                }
            }]
        });

        //分组
        $('#userGroup').show().dialog({
            title: '添加分组',
            width: 450,
            //height:'',
            closed: true,
            cache: false,
            modal: true,
            buttons: [{
                text:'确定',
                iconCls:'icon-ok',
                handler:function(){
                    var $form = $('#userGroup');
                    var role_name = $form.find('input[name=group]').val();
                    $.get('/privilege/AddRoles', {'role_name':role_name},function(data){
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
                    $('#userGroup').dialog('close');
                }
            }]
        });


        //添加报表分组
        $("body").on("click",'.addGroup',function(e){
            open("userGroup",e);
        });
        $("body").on("click",'.addReport',function(e){
            open("userReport",e);
        });


    });

</script>
































































