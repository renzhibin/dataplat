{/include file="homefocus/header.tpl"/}
{/include file='homefocus/source.tpl'/}
<link rel="stylesheet" type="text/css" href="/assets/homefocus/webupload/webuploader.css">
<script type="text/javascript" src="/assets/homefocus/webupload/webuploader.js"></script>
<script type="text/javascript" src="/assets/homefocus/app_TypeAndParams.js?version={/$version/}"></script>

<div class="container-fluid box">
    <div class="pad-bt10">
        <ol class="breadcrumb">
            <li><a href="/AppHomefocus/index">首页</a></li>
            <li><a href="javascript:void(0)"><b class="sourcetitle"></b>编辑</a></li>
        </ol>
    </div>
    <!-- applybox -->
    <div class="applybox">
        <div class="editinfo editsource"></div>
        <div class="showinfo"></div>
        <div class="editinfo editbanner"></div>
        <div class="info editinfo editonline"></div>

    </div>

    <div class="center">
        <div class="row applybtnbox">
            <div class="col-xs-6 text-r"><input type="button" class="btn btn-primary saveBtn" value="微信钱包上线" /></div>
            <div class="col-xs-5"><input type="button" class="btn btn-default applyback" value="返回" /></div>

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
        window.params = window.params?window.params:{};
        window.params['flowname'] = {/$flowname/};
        window.params["datainfo"]= {/$datas/};
        window.params['statusName'] = {/$statusName/};
        window.category = {/$category/};

    });
</script>
<script type="text/javascript" src="/assets/homefocus/apply.js?version={/$version/}"></script>
<script type="text/javascript">
$(document).ready(function(){
    var tempdata = JSON.parse(JSON.stringify(window.params.datainfo));
    tempdata['sourceinfo'] = tempdata['sourceinfo'] ? JSON.parse(tempdata['sourceinfo']):null;
    tempdata['bannerinfo'] = tempdata['bannerinfo'] ? JSON.parse(tempdata['bannerinfo']):null;
    tempdata['onlineinfo'] = tempdata['onlineinfo'] ? JSON.parse(tempdata['onlineinfo']):null;

    var tmpl = doT.template($("#wechatapplysourcetmpl").text());
    $('.editsource').html(tmpl([tempdata]));
    var tmpl = doT.template($("#wechatapplybannertmpl").text());
    $('.editbanner').html(tmpl([tempdata]));
    var tmpl = doT.template($("#applyonlinetmpl").text());
    $('.editonline').html(tmpl([tempdata]));

    $('.showinfo').find('.sourceinfo').hide();
    $('.showinfo').find(".bannerinfo").hide();
    $('.showinfo').find(".onlineinfo").hide();

    var uploader = myhomefocus.uploader();

    $('.editinfo input[name="location"]').prop('disabled','disabled');
    $('select[name="product_categroy"]').val(tempdata['sourceinfo']['product_categroy']);

    var id = myhomefocus.getUrlSearch('id');
    var status = myhomefocus.getUrlSearch('flow');
    var $info = $('.editinfo');

    //保存工作
    $('body').on('click','.saveBtn',function(){
        var getdata={},mydata={}, ckmsg;
        mydata = myhomefocus.getsoureinfo($info);
        ckmsg = myhomefocus.cksourinfo(mydata);
        getdata['active_name'] = myhomefocus.strTrim(mydata['active_name']);
        getdata['info'] = myhomefocus.strTrim(mydata['info']);
        getdata['sourceinfo'] = JSON.stringify(mydata);


        getdata['locationsort'] = parseInt(mydata.locationsort);
        getdata['starttime'] =mydata['starttime'];
        getdata['endtime'] =mydata['endtime'];
        var mydata2 = myhomefocus.getonlineinfo($info);
        var mydata3 = myhomefocus.getbannerinfo($info);

        var ckmsgs = $.merge(ckmsg,mydata2.errmsg);
        ckmsgs = $.merge(ckmsg,mydata3.errmsg);

        getdata['bannerinfo'] = JSON.stringify(mydata3['getdata']);
        getdata['onlineinfo'] = JSON.stringify(mydata2['getdata']);

        if(ckmsgs.length > 0){
            $('#mymodal').find('.mymodalcon').html(ckmsg.join("<br/>"));
            $('#mymodal').modal('show');
            return false;
        }

        var url = "/AppHomefocus/WechatPurseEditSave";
        var data_status = status!=null ?status:4;
        console.log(getdata);
        myhomefocus.sendAjax(url,{"data":JSON.stringify(getdata),"id":id,"status":data_status,"reply_status":1}, function(result){
            if(result.status == 0){
                window.location.href = "/AppHomefocus/index";
                $('#mymodal').modal('hide');
            } else {
                $('#mymodal').find('.mymodalcon').html(result.msg);
                $('#mymodal').modal('show');
            }
        },function(){
            $('#mymodal').find('.mymodalcon').html("服务器连接失败");
            $('#mymodal').modal('show');
            return false;
        });

    });

    //选择图片的参数
    $('.bannerinfo .uploader-demo').on('click','.filePicker',function(){
        window.pickey = $(this).attr('key');
        window.pictitle = $(this).attr('title');
        window.$filePicker = $(this);

    });
   /* //删除照片的绑定事件
    $('.bannerinfo').on('click','.removebtn',function(){

        var $fileList = $(this).closest('.uploader-demo').find('.fileList');
        var fileitem = $fileList.find('.file-item');
        if(fileitem.length > 0){
            var fileid= $(fileitem).attr('id');
            //编辑的时候不用删uploader remove
            if(fileid){
                uploader.removeFile(fileid);
            }
            $fileList.html('');
        }

    });*/



    $('.datetimepicker').datetimepicker({
        format: 'yyyy-mm-dd HH:00:00',
        language:  'zh-CN',
        weekStart: 1,
        todayBtn:  1,
        autoclose: 1,
        todayHighlight: 1,
        startView: 2,// 1小时  2 日 3月 4年
        minView: 1, // 1小时  2 日 3月 4年
        forceParse: 0
    });


});
</script>
{/include file="homefocus/footer.tpl"/}
