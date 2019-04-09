{/include file="homefocus/header.tpl"/}
{/include file='homefocus/source.tpl'/}
<div class="container-fluid box">
    <div class="pad-bt10">
        <ol class="breadcrumb">
            <li><a href="/AppHomefocus/index">首页</a></li>
            <li><a href="javascript:void(0)"><b class="title"></b>详细信息</a></li>
        </ol>
    </div>

    <!-- applybox -->
    <div class="repybox">
        <div class="info"></div>
        <div class="审核信息">

        </div>
        <!-- <table class="table table-bordered sourceinfo">
             <tbody>
             <tr>
                 <td class="col-xs-2">活动名称：</td>
                 <td class="col-xs-4">跑男助威</td>
                 <td class="col-xs-2">商品类目：</td>
                 <td class="col-xs-4">上衣随便写点啥随便写点啥随便写点啥随便写点啥随便写点啥随便写点啥随便写点啥随便写点啥随便写点啥</td>
             </tr>
             <tr>
                 <td class="col-xs-2">申请帧位：</td>
                 <td class="col-xs-4">3</td>
                 <td class="col-xs-2">申请位置：</td>
                 <td class="col-xs-4">首焦</td>
             </tr>
             <tr>
                 <td class="col-xs-2">背景：</td>
                 <td class="col-xs-10" colspan="3">随便写点啥</td>
             </tr>
             </tbody>
         </table>-->
        <!-- 状态 -->

        <!-- 审核不通过时 才可以看到编辑按钮 -->
        <div class="text-center editbox">
        </div>

    </div>
    <div class="center">
        <div class="row applybtnbox">
            <div class="col-xs-6 text-r"><select id="process_select">
                    {/foreach from=$processList key=k item=val /}
                        <option value="{/$val['id']/}">{/$val['process']/}</option>
                    {//foreach/}
                </select></div>
            <div class="col-xs-5"><input type="button" class="btn btn-primary saveBtn" data-id="{/$source_id/}" id="process_save" value="确定" /></div>
        </div>
    </div>
</div>
<script>
    $('#process_save').click(function () {
        process=$('#process_select').val();
        $.ajax({
            type: "POST",
            url: "/AppHomefocus/hotUpdate",
            data: {"status":process,"id":$(this).attr("data-id"),"reply_status":2},
            datatype:"JSON",
            success: function(result){
                if(result == "null") { console.log('result:'+result);return false; }
                var results = JSON.parse(result);
                if(results.status == 0){
                    alert(results.msg);
                    window.location.href='/AppHomefocus/index';
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
<script type="text/javascript">
    $(document).ready(function() {
        var datajson = {/$result/};
        $info = $('.info');
        if(datajson.length>0){
            var tempdata = JSON.parse(JSON.stringify(datajson[0]));
            tempdata['sourceinfo'] = tempdata['sourceinfo'] ? JSON.parse(tempdata['sourceinfo']):null;
            tempdata['fashioninfo'] = tempdata['fashioninfo'] ? JSON.parse(tempdata['fashioninfo']):null;
            tempdata['bannerinfo'] = tempdata['bannerinfo'] ? JSON.parse(tempdata['bannerinfo']):null;
            tempdata['onlineinfo'] = tempdata['onlineinfo'] ? JSON.parse(tempdata['onlineinfo']):null;

            var tmplText = doT.template($("#applyinfotmpl").text());
            $info.html(tmplText([tempdata]));
            $('.replyinfo').show();

            //var myhomefocus = new HomeFocus({});
            //var status = myhomefocus.getUrlSearch('flow');
            //var statusjson = {"0":"紧急上线申请","1":"资源位申请","2":"时尚度申请","3":"banner图申请","4":"上线申请","5":"下线"};
            $('.title').html(tempdata.active_name);

            //<!-- 审核不通过时 才可以看到编辑按钮 -->
            if(tempdata.reply_status == 2 && tempdata.role == 'creater') {
                if(tempdata.status == 0){
                    var str = '<a href="/AppHomefocus/fastapply?flow=' + tempdata.status + '&id=' + tempdata.id + '" class="btn btn-primary applybtn">编辑</a>';
                }else
                {
                    var str = '<a href="/AppHomefocus/apply?flow=' + tempdata.status + '&id=' + tempdata.id + '" class="btn btn-primary applybtn">编辑</a>';
                }
                $('.editbox').html(str);
            }


        } else {
            $info.html("<p class='redcolor'>获取不到相应的数据，请返回列表页面</p>");
        }



    });
</script>
{/include file="homefocus/footer.tpl"/}
