{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">
<script src="/assets/js/project.js?version={/$version/}"></script>
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
                <a  class='btn btn-primary btn-sm' href='/menu/add' target='_blank'>
                    <i class='glyphicon glyphicon-plus'></i>添加二级菜单
                </a>
                <a class='btn btn-primary btn-sm sortMenu' href='/menu/sort' target='_blank' >
                    <i class='glyphicon glyphicon-sort'></i>&nbsp;二级菜单排序
                </a>
                {/if $isSuper eq 1/}
                    <a  class='btn btn-primary btn-sm addFirstMenu'>
                        <i class='glyphicon glyphicon-plus'></i>&nbsp;添加一级菜单
                    </a>
                    <a  class='btn btn-primary btn-sm' href='/menu/firstSort' target='_blank'>
                        <i class='glyphicon glyphicon-sort'></i>&nbsp;一级菜单排序
                    </a>
                {//if/}
                <br>
                <span style='color: red; padding-left: 5px; font-size: 13px;'>
                    <!--注：一级菜单（增/删/改）需高玉石批准，详情咨询侯阳阳或查看<a href="http://fe..com/agg/doc?agg=da&doc=dt_internal_authority" target='_blank'>申请流程</a>-->
                </span>
                <div style='position:relative;padding-top:35px'>
                    <table class="table table-bordered data-table">
                        <thead>
                            <tr  class="table_header">
                                <th>二级菜单名称</th>
                                <th>一级菜单名称(父级菜单)</th>
                                <th>创建人</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                        {/foreach from =$list item= item key=key/}
                            <tr class="gradeX">
                                <td>{/$item.second_menu/}</td>
                                <td>{/$item.first_menu/}</td>
                                <td>{/$item.user_name/}</td>
                                <td>
                                    <a href='/menu/editor?id={/$item.id/}' target='_blank' style='padding:3px 10px' class='btn btn-default btn-sm'>编辑</a>
                                    <a data_id='{/$item.id/}' style='padding:3px 10px' class='btn btn-default btn-sm deleteMenu'>下线</a>
                                    <a href='/menu/report?sort_id={/$item.id/}' target='_blank' style='padding:3px 10px' class='btn btn-default btn-sm'>报表排序</a>
                                </td>         
                            </tr>
                        {//foreach/}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!--添加一级菜单名称弹框-->           
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

{/include file="layouts/menujs.tpl"/}
<script type="text/javascript">
    $(function(){
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
        //添加一级菜单
        $("body").on("click",'.addFirstMenu',function(e){
            open("addFirstMenuModal",e);
        });

        $("body").on("click",'.deleteMenu',function(){
            var id = $(this).attr('data_id');
            var obj = $(this);
            $.messager.confirm('提示', '确定下线吗？', function(r){
                if(r){
                    $('body').mask();
                    $.get('/menu/deleteMenu', {'id': id},function(data){
                        $('body').unmask();
                        if(data.status ==0){
                            obj.parent().parent().remove();
                        }
                        $.messager.alert('提示',data.msg,'info');
                    }, 'json');
                }
            });     
        });
    });
</script>
