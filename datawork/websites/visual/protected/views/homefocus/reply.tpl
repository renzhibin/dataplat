{/include file="homefocus/header.tpl"/}
{/include file='homefocus/source.tpl'/}
<div class="container-fluid box">
<div class="pad-bt10">
    <ol class="breadcrumb">
        <li><a href="/AppHomefocus/index">首页</a></li>
        <li><a href="javascript:void(0)"><b class="sourcetitle"></b>审核</a></li>
    </ol>
</div>
<!-- applybox -->
<div class="repybox">
    <div class="showinfo"></div>
    <!-- 审批 -->
    <div class="info">
        <table class="table table-bordered">
            <tbody>
            <tr>
                <td class="col-xs-2">审核意见：</td>
                <td class="col-xs-10"><textarea class="form-control input-sm reply_info"></textarea></td>
            </tr>
            </tbody>
        </table>
    </div>

</div>

<div class="center">
    <div class="row replybtn">
        <div class="col-xs-6 text-r"><input type="button" class="btn btn-primary through" value="通过" reply_status=1 /></div>
        <div class="col-xs-5"><input type="button" class="btn btn-default refuse" value="驳回" reply_status=2 /></div>
    </div>
</div>

</div>

<div class="modal fade" id="mymodal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">提示</h4>
            </div>
            <div class="modal-body">
                <p class="mymodalcon"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <!--<button type="button" class="btn btn-primary">Save changes</button>-->
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script type="text/javascript">
$(document).ready(function(){
    var myhomefocus = new HomeFocus({});
    var flowname = {/$flowname/};
    var statusName = {/$statusName/};
    var flow = myhomefocus.getUrlSearch('flow');
    flow = flow?flow:1;
    var id = myhomefocus.getUrlSearch('id');
    $('.replybtn').find('input.button').attr({"flow":flow,"id":id});
    $('.sourcetitle').html(statusName[flow]);

    var datainfo = {/$datas/}; //获取详细数据
    if(datainfo){
        var tempdata = JSON.parse(JSON.stringify(datainfo));
        tempdata['sourceinfo'] = tempdata['sourceinfo'] ? JSON.parse(tempdata['sourceinfo']):null;
        tempdata['fashioninfo'] = tempdata['fashioninfo'] ? JSON.parse(tempdata['fashioninfo']):null;
        tempdata['bannerinfo'] = tempdata['bannerinfo'] ? JSON.parse(tempdata['bannerinfo']):null;
        tempdata['onlineinfo'] = tempdata['onlineinfo'] ? JSON.parse(tempdata['onlineinfo']):null;

        var tmplText = doT.template($("#applyinfotmpl").text());
        $('.showinfo').html(tmplText([tempdata]));
    } else {
        $('.showinfo').html("<p class='redcolor'>未获取到相关数据，请返回到列表页</p>");
        $('.info').html('');
    }

    //通过
    $('.replybtn').on('click','input.btn',function(){

        var reply_status = $(this).attr('reply_status');
        var reply_info = $('.reply_info').val();
        reply_info = myhomefocus.strTrim(reply_info);
        if(reply_status == 2 && reply_info == ''){
            $('.mymodalcon').html("请输入您驳回的意见");
            $('#mymodal').modal('show');
            return false;
        } else {
            $(this).prop('disabled','disabled');
        }

        var url = "/AppHomefocus/ReplyUpdate";

        myhomefocus.sendAjax(url,{"reply_status":reply_status,"reply_info":reply_info,"id":id,"status":flow}, function(result){
            if(result.status == 0){
                window.location.href = "/AppHomefocus/index";
            }
        },function(){
            $('#mymodal').find('.mymodalcon').html("服务器连接失败");
            $('#mymodal').modal('show');
        });

    });

    //图片放大的功能
    $('body').on('click','.bannerpic img',function(){
        var picinfostr = $(this).siblings('input[type="hidden"]').val();
        var picinfo = JSON.parse(picinfostr);
        var src = "http://imgtest.meiliworks.com/"+picinfo.n_pic_file;
        $('.mymodalcon').html("<img src='"+src+"' width='"+picinfo.nwidth+"' width='"+picinfo.nheight+"' />");
        $('#mymodal').modal('show');
    });


});

</script>

{/include file="homefocus/footer.tpl"/}
