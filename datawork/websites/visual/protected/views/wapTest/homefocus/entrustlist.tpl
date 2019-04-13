{/include file="homefocus/header.tpl"/}
<script type='text/javascript' src="/assets/lib/bootstrap-3.3/js/bootstrap-paginator.js"></script>

<div class="container-fluid box">
    <!--<div class="alert alert-success">
        首焦&splash资源位申请共分为五个环节：资源位申请->时尚度申请->banner图申请->上线申请->已上线 or 已下线，
        每个环节都有待审核、已通过、审核不通过和已撤销四个状态；
    </div>-->
    <div class="">
        <ol class="breadcrumb">
            <li><a href="/AppHomefocus/index">首页</a></li>
            <li><a href="javascript:void(0)"><b class="applistTitle">我的委托列表</b></a></li>
        </ol>
    </div>
    <table class="table table-striped table-hover table-bordered">
        <thead>
        <tr>
            <th>ID</th>
            <th>流程名称</th>
            <th>委托人</th>
            <th>操作</th>
        </tr>
        </thead>
        <tfoot></tfoot>
        <tbody class="tablelist reply_datalist">
        {/foreach from=$result key=k item=val /}
        <tr>
            <td>{/$val.id/}</td>
            <td>{/$val.process/}</td>
            <td>
                <input class="auditor" type="text" value="{/$val.auditor/}">
            </td>
            <td>
                <a class="clickBtn" key="offlinecode" flow="{/$val.status/}" id="{/$val.id/}" data-id="{/$val.id/}">变更</a>
            </td>
        </tr>
        {//foreach/}
        </tbody>
    </table>
</div>

<script type="text/javascript">
    $('.clickBtn').click(function(){
        content=$(this).parent().prev().find('.auditor').val();
        $.ajax({
            type: "POST",
            url: "/AppHomefocus/changeEntrust",
            data: {"content":content,"id":$(this).attr("data-id")},
            datatype:"JSON",
            success: function(result){
                if(result == "null") { console.log('result:'+result);return false; }
                var results = JSON.parse(result);
                if(results.status == 0){
                    alert(results.msg);
                    window.location.reload();
                } else {
                    alert(results.msg)
                }
            },
            error: function(){
                alert('服务器连接失败');
            }
        });
    })
</script>



{/include file="homefocus/footer.tpl"/}
