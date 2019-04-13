{/include file="homefocus/header.tpl"/}
{/include file='homefocus/source.tpl'/}

<div class="container-fluid box">
    <div class="pad-bt10">
        <ol class="breadcrumb">
            <li><a href="/AppHomefocus/index">首页</a></li>
            <li><a href="javascript:void(0)"><b class="sourcetitle">紧急上线申请</b></a></li>
        </ol>
    </div>
    <!-- applybox -->
    <div class="applybox">
        <div class="editinfo editsource"></div>
        <div class="editinfo editfashion"></div>
        <div class="editinfo editbanner"></div>
        <div class="info editinfo editonline"></div>
    </div>

    <div class="center">
        <div class="row applybtnbox">
            <div class="col-xs-6 text-r"><input type="button" class="btn btn-primary applybtn" value="申请" /></div>
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
        window.params["datainfo"]= {/$datas/};
        window.params['statusName'] = {/$statusName/};

        var myhomefocus = new HomeFocus();
        var datainfo = window.params.datainfo; //获取详细数据
        var tempdata = {};
        var uploader;
        var id = myhomefocus.getUrlSearch('id');
        id = id?id:0;

        if(JSON.stringify(datainfo)!='{}') {
            tempdata = JSON.parse(JSON.stringify(datainfo));
            tempdata['sourceinfo'] = tempdata['sourceinfo'] ? JSON.parse(tempdata['sourceinfo']) : null;
            tempdata['fashioninfo'] = tempdata['fashioninfo'] ? JSON.parse(tempdata['fashioninfo']) : null;
            tempdata['bannerinfo'] = tempdata['bannerinfo'] ? JSON.parse(tempdata['bannerinfo']) : null;
            tempdata['onlineinfo'] = tempdata['onlineinfo'] ? JSON.parse(tempdata['onlineinfo']) : null;
            tempdata['auditinfo'] = tempdata['auditinfo'] ? JSON.parse(tempdata['auditinfo']) : null;
        }

        var sourcetmpl = doT.template($("#applysourcetmpl").text());
        var fashiontmpl = doT.template($("#applyfashiontmpl").text());
        var bannertmpl = doT.template($("#applybannertmpl").text());
        var onlinetmpl = doT.template($("#applyonlinetmpl").text());

        $('.editsource').html(sourcetmpl([tempdata]));
        $('.editfashion').html(fashiontmpl([tempdata]));

        if(id>0 && tempdata['bannerinfo'] && tempdata['onlineinfo'] && tempdata['bannerinfo']!=null ){
            $('.editbanner').html(bannertmpl([tempdata]));
            $('.editonline').html(onlinetmpl([tempdata]));

            //初始化input 数据
            if( tempdata['sourceinfo'] && tempdata['sourceinfo'].location.indexOf('mob') >=0 ){
                 window.add_app_type_mob = new appTypeAndParams({
                    "id" : "add_app_type_mob",
                    "contain" : $(".editinfo").find('tr.mob .addtype')
                });

                if(tempdata['onlineinfo'] && tempdata['onlineinfo']['mob']){
                    $('#add_app_type_mob').find('select[name="url_type"]').val(tempdata['onlineinfo']['mob']['url_type']).change();
                    var url_params = JSON.parse(tempdata['onlineinfo']['mob']['url_params']);
                    add_app_type_mob.set_urlParams(tempdata['onlineinfo']['mob']['url_type'],url_params);
                }

            }

            uploader = myhomefocus.uploader();
            //禁选mob pc
            $('.editinfo input[name="location"]').prop('disabled','disabled');

        } else {
            //时间
            $('.datetimepicker').val(myhomefocus.getToday(0,false,'hour'));
        }

        $('.datetimepicker').datetimepicker({
            format: 'yyyy-mm-dd hh:00:00',
            language:  'zh-CN',
            weekStart: 1,
            todayBtn:  1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 2,// 1小时  2 日 3月 4年
            minView: 1, // 1小时  2 日 3月 4年
            forceParse: 0
        });

        var sourceinfo = {};

       // tempdata[0]['sourceinfo'] =JSON.stringify(tempsource);
        tempdata['sourceinfo'] = tempdata['sourceinfo']?tempdata['sourceinfo']:{};
        //申请位置
        $(".editinfo").on('click','input[name="location"]',function(){

            var $info = $('.editsource'), location = [];
            if($info.find('#ck-mob').is(":checked")){
                location.push($info.find('#ck-mob').val());
            }
            if($info.find('#ck-pc').is(":checked")){
                location.push($info.find('#ck-pc').val());
            }
            if($info.find('#ck-splash').is(":checked")){
                location.push($info.find('#ck-splash').val());
            }

            tempdata['sourceinfo']['location'] = sourceinfo['location'] = location.join(",");
            console.log(tempdata);

            $('.editbanner').html(bannertmpl([tempdata]));
            $('.editonline').html(onlinetmpl([tempdata]));

            //初始化上选图片按钮
            uploader =null;
            uploader = myhomefocus.uploader();

            //初始化input 数据
            if(sourceinfo.location.indexOf('mob') >=0 ){
                 window.add_app_type_mob = new appTypeAndParams({
                    "id" : "add_app_type_mob",
                    "contain" : $(".editinfo").find('tr.mob .addtype')
                });
            }

        });

        //选择图片的参数
        $('.editinfo').on('click','.uploader-demo .filePicker',function(){
            window.pickey = $(this).attr('key');
            window.pictitle = $(this).attr('title');
            window.$filePicker = $(this);
        });

        //返回按钮
        $('.applybtnbox').on('click','.applyback',function(){
            window.location.href = "/AppHomefocus/index";
        });

        //绑定事件 申请
        $('.box').on('click','.applybtn',function(){
            var $info = $('.editinfo'), getdata, ckmsg;

            var getdata={},mydata={}, ckmsg;
            mydata = myhomefocus.getsoureinfo($info);
            ckmsg = myhomefocus.cksourinfo(mydata);
            getdata['active_name'] = myhomefocus.strTrim(mydata['active_name']);
            getdata['info'] = myhomefocus.strTrim(mydata['info']);
            getdata['sourceinfo'] = JSON.stringify(mydata);
            getdata['locationsort'] = parseInt(mydata.locationsort?mydata.locationsort:1);
            getdata['product_categroy'] = mydata['product_categroy'];
            getdata['location'] = mydata['location'];
            getdata['starttime'] = mydata['starttime'];
            getdata['endtime'] = mydata['endtime'];

            var mydata2 = myhomefocus.getfashioninfo($info);
            var ckmsg2 = myhomefocus.ckfashioninfo(mydata2);
            var mydata3 = myhomefocus.getbannerinfo($info);
            var mydata4 = myhomefocus.getonlineinfo($info);

            var ckmsgs1 = $.merge(ckmsg,ckmsg2);
            var ckmsgs2 = $.merge(ckmsgs1,mydata3.errmsg);
            var ckmsgs = $.merge(ckmsgs2,mydata4.errmsg);

            getdata['fashioninfo'] = mydata2?JSON.stringify(mydata2):JSON.stringify({});
            getdata['bannerinfo'] = mydata3['getdata']?JSON.stringify(mydata3['getdata']):JSON.stringify({});
            getdata['onlineinfo'] = mydata4['getdata']?JSON.stringify(mydata4['getdata']):JSON.stringify({});


            if(ckmsg.length > 0){
                $('#mymodal').find('.mymodalcon').html(ckmsg.join("<br/>"));
                $('#mymodal').modal('show');
                return false;
            }

            $(".applybtn").hide();//防止重复提交
            var url = id == 0?'/AppHomefocus/Add' :"/AppHomefocus/Update";
            //url,datas,success,errorfunc
            myhomefocus.sendAjax(url,{"data":JSON.stringify(getdata),"id":id,"status":0,"reply_status":0}, function(result){
                if(result.status == 0){
                    window.location.href = "/AppHomefocus/index";
                }
            },function(){
                $('#mymodal').find('.mymodalcon').html("服务器连接失败");
                $('#mymodal').modal('show');
                return false;
            });

        });

    });
</script>

{/include file="homefocus/footer.tpl"/}