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
                <div style='position:relative'>
                    <span class='tipinfo'>
                        注：拖拽表格行进行排序
                    </span>
                    <a class="pull-right" href='/menu/index'>返回列表页</a>
                    <table class="table table-bordered">
                        <thead>
                            <tr class="table_header">
                                <th>id</th>
                                <th>名称</th>
                                <th>url</th>
                                <th>类型</th>
                            </tr>
                        </thead>
                        <tbody id ='sourtmenutable'>
                            {/if $reportinfo|@count neq 0 /}
                                {/foreach from =$reportinfo item= item key=key/}
                                    <tr class="gradeX">
                                        <td style="width:10%">{/$item.id/}</td>
                                        <td style="width:20%">{/$item.cn_name/}</td>
                                        <td style="width:40%">{/$item.url/}</td>
                                        <td>{/$item.type/}</td>         
                                    </tr>
                                {//foreach/}
                            {/else/}
                                <tr>
                                    <td colspan="4" class="tipinfo">(菜下面没有报表，请为菜单设置报表)</td>
                                </tr>
                            {//if/}
                        </tbody>
                    </table>
                    <button class="sortMenu btn btn-primary btn-sm" >排序保存</button>
                </div>
            </div>
        </div>
    </div>
</div>
{/include file="layouts/menujs.tpl"/}
<script type="text/javascript">
    {/if  $sort_id neq '' /}
        var sort_id = {/$sort_id/};
    {/else/}
        var sort_id =0;
    {//if/}
    $(function(){
        $("#sourtmenutable").dragsort({
            dragSelector : "tr",
            dragSelectorExclude:"button,input,textarea",
            dragEnd : function(){
            },
            scrollSpeed:0,
        });
        $('.sortMenu').on('click',function(){
            var  obj =[];
            if(!sort_id){
                return false;
            }
            $('#sourtmenutable').find('tr').each(function(){
                var oneObj ={};
                oneObj.id = $(this).find('td').eq(0).text();
                oneObj.name = $(this).find('td').eq(1).text();
                oneObj.url = $(this).find('td').eq(2).text();
                oneObj.type = $(this).find('td').eq(3).text();
                obj.push(oneObj);
            });
            $.post('/menu/savereport', {'sortinfo':obj,'menu_id':sort_id},function(data){
                if(data.status ==0){
                    $.messager.alert('提示',data.msg,'info');
                }else{
                    $.messager.alert('提示',data.msg,'info');
                }
            }, 'json'); 
        });
    });
</script>