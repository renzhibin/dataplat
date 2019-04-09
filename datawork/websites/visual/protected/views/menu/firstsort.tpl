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
                    <span class='tipinfo'>(拖拽表格行进行排序)</span>
                    <div class="changebox">
                        <div style='height:5px'></div>
                        <table class="table table-condensed table-bordered">
                            <thead>
                            <tr class="table_header">
                                <td>菜单Id</td>
                                <td>名称</td>
                            </tr>
                            </thead>
                            <tbody id='sourtmenutable'>
                            {/foreach from = $menuinfo item = coll key = key/}
                            <tr>
                                <td>{/$coll.id/}</td>
                                <td>{/$coll.first_menu/}</td>
                            </tr>
                            {//foreach/}
                            </tbody>
                        </table>
                    </div>
                    <button class="sortMenu btn btn-primary btn-sm">排序保存</button>
                </div>
            </div>
        </div>
    </div>
</div>
{/include file="layouts/menujs.tpl"/}
<script type="text/javascript">
    $(function () {
        $("#sourtmenutable").dragsort({
            dragSelector: "tr",
            dragSelectorExclude: "button,input,textarea",
            dragEnd: function () {
            },
            scrollSpeed: 0
        });
        $("body").on("click", '.sortMenu', function () {
            //获取信息
            var obj = [];
            $('#sourtmenutable').find('tr').each(function () {
                var oneObj ={};
                oneObj.id = $(this).find('td').eq(0).text();
                oneObj.second_menu = $(this).find('td').eq(1).text();
                obj.push(oneObj);
            });
            //$('body').mask();
            $.post('/menu/savesort', {'sortinfo':obj,'first_menu':''}, function (data) {
                //$('body').unmask();
                if (data.status == 0) {
                    $.messager.alert('提示', data.msg, 'info');
                } else {
                    $.messager.alert('提示', data.msg, 'info');
                }
            }, 'json');
        });
    });
</script>