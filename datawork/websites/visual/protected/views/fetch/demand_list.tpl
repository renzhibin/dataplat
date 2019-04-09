{/include file="layouts/lib.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" href="/assets/css/url-interface.css?version={/$version/}">
<div style="height: 10px; border-top: 2px solid #03a3da!important;"></div>
<div style='padding-left:15px;padding-right:15px;position: relative;'>
    <div class="table-list" style='position:relative;padding-top:35px' >
        <div class="table-responsive">
            <table class="table  table-hover">
                <thead class="title-list">
                    <tr>
                        <th class="col-md-2">需求名称</th>
                        <th class="col-md-2">外发单位名称</th>
                        <th class="col-md-2">创建人</th>
                        <th class="col-md-2">操作</th>
                    </tr>
                </thead>
                <tbody class="item-list">
                    {/foreach from =$visualList item= item key=key/}
                    <tr>
                        <td class="col-md-2">{/$item.demand_name/}</td>
                        <td class="col-md-2">{/$item.company_name/}</td>
                        <td class="col-md-2">{/$item.created_user/}</td>
                        <td class="col-md-4">
                            <a href='/fetch/downloadfile/{/$item.demand_id/}?action=eml_path' style='padding:3px 10px' class='btn btn-default btn-sm'>下载需求邮件</a>
                            <a href='/fetch/downloadfile/{/$item.demand_id/}?action=zip_path' style='padding:3px 10px' class='btn btn-default btn-sm'>下载结果数据</a>
                            <a href='/fetch/downloadfile/{/$item.demand_id/}?action=down_path' style='padding:3px 10px' class='btn btn-default btn-sm'>下载用户日志</a>
                            <a href='' onclick="delDemand({/$item.demand_id/}, '{/$item.demand_name/}')" style='padding:3px 10px' class='btn btn-default btn-sm'>删除</a>
                        </td>
                    </tr>
                    {//foreach/}
                </tbody>
            </table>
        </div>
    </div>
<div>
<script>
    $(function(){
        $('.table').dataTable({
            "iDisplayLength":5,
            "bJQueryUI": true,
            "sPaginationType": "full_numbers",
            "sDom": '<""l>t<"F"fp>',
            "bSort":false,
            //"bPaginate":false,
            "oLanguage": {
                'sSearch':'搜索:',
                "sLengthMenu": "每页显示 _MENU_ 条记录",
                "oPaginate":{
                    "sFirst":"第一页",
                    "sLast":"最后一页",
                    "sNext": "下一页",
                    "sPrevious": "上一页"
                },
                "sInfoEmtpy": "没有数据",
                "sZeroRecords": "没有检索到数据"
            }
        });
    });
    function delDemand(id, name) {
        if(confirm('确认是否删除' + name + '?')) {
            $.ajax({
                url: '/fetch/deldemand',
                data: {
                    id: id
                },
                cache: false,
                async: false,
                type: "POST",
                dataType: 'json',
                success: function (result) {
                    $('.table').dataTable();
                }
            });
        } else {
            return;
        }
    }
</script>
<script src="/assets/js/artTemplate.js?version={/$version/}"></script>
