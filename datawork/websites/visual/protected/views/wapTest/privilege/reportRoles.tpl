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
                <button  class='btn btn-primary btn-sm addGroup'>
                    <i class='glyphicon glyphicon-plus'></i>添加分组
                </button>
                <button  class='btn btn-primary btn-sm addReport'>
                    <i class='glyphicon glyphicon-plus'></i>添加报表分组
                </button>
                <div class="search_input">
                    <label>分组id</label>
                    <input type="text" class="role-id" name="role-id" value="{/$smarty.get.role_id|escape/}"/> 
                    <label>报表id</label>
                    <input type="text" class="report-id" name="report-id" value="{/$smarty.get.report_id|escape/}"/> 
                    <button class="search btn btn-primary btn-sm">搜索</button>
                    <a href="/privilege/userroles" target="_blank">查看用户分组</a>
                </div>
                <div style='position:relative;padding-top:35px'>
                    <table class="table table-bordered user-table">
                        <thead>
                        <tr class="table_header">
                            <th style='width:5%'>分组id</th>
                            <th style='width:20%'>分组名称</th>
                            <th style='width:20%'>报表id</th>
                            <th style='width:10%'>报表名称</th>
                            <th style='width:25%'>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        {/foreach from =$reportRolesList item=item key=key/}
                        <tr class="gradeX">
                            <td  style='width:5%'>{/$item.role_id/}</td>
                            <td  style='width:20%'>{/$item.role_name/}</td>
                            <td  style='width:10%'>{/$item.report_id/}</td>
                            <td  style='width:10%'>{/$item.report_name/}</td>
                            <td  style='width:10%'>
                                <button style='padding:3px 10px' data-id="{/$item.role_id/}"  data-report="{/$item.report_id/}" class='btn btn-default btn-sm deluser'>
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
    $(function(){
    //配置搜索按钮
	/*$('.user-table').dataTable({
				"iDisplayLength":10,
				"bJQueryUI": true,
				"sPaginationType": "full_numbers",
				"sDom": '<""l>t<"F"fp>',
				"bSort":false,
				"bPaginate":true,
				"oLanguage": {
					'sSearch':'用户id:',
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

    });*/
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
        $('body').find(".search").click(searchword);

        function searchword() {
            var  query = {
                "role_id" : $(".role-id").val(),
                "report_id" : $(".report-id").val()
            };
            window.location.href = window.location.pathname + "?" + $.param(query);
        }

        // 回车 
        $(document).on("keydown", function (e) {
            e.which === 13 && searchword();
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
































































