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

                <div style='position:relative;padding-top: 10px'>
                    <table class="table table-bordered user-table">
                        <thead>
                        <tr class="table_header">
                            <th style='width:5%'>分组id</th>
                            <th style='width:20%'>分组名称</th>
                            <th style='width:20%'>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        {/foreach from =$reportGroupList item=item key=key/}
                        <tr class="gradeX">
                            <td  style='width:5%'>{/$item.role_id/}</td>
                            <td  style='width:20%'>{/$item.role_name/}</td>
                            <td  style='width:10%'>
                                <button style='padding:3px 10px' data-id="{/$item.role_id/}"  data-name="{/$item.role_name/}" class='btn btn-default btn-sm delGroup'>
                                    删除</button>
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
{/include file="layouts/menujs.tpl"/}
<script type="text/javascript">
    $(function(){

        $('body').on("click", ".delGroup", function () {
            var $this = $(this);
            var role_id = $this.data("id");
            var role_name = $this.data("name");
            if (confirm("请确认分组{ "+ role_id + " -> " + role_name + " }是否要删除？")) {
                $.get('/privilege/DelGroup', {'role_id':role_id},function(data){
                    // $('body').unmask();
                    if(data.status ==0){
                        location.reload();
                    } else{
                        $.messager.alert('提示',data.msg,'info');
                    }
                }, 'json');
            }
        });
    });

</script>
































































