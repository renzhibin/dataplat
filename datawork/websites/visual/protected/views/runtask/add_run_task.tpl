{/include file="layouts/lib.tpl"/}
<link rel="stylesheet" href="/assets/css/url-interface.css?version={/$version/}">
<script src='/assets/lib/bootstrap-filestyle.min.js?version={/$version/}'></script>
<script src="/assets/lib/jquery-1.11.1.min.js"></script>
<script src="/assets/lib/bootstrap-3.3/js/bootstrap.min.js"></script>
<script type='application/javascript' src='/assets/js/fastclick.js'></script>
<script src="/assets/lib/jquery-easyui-1.4.1/jquery.easyui.min.js"></script>
<script src="/assets/lib/jquery-easyui-1.4.1/locale/easyui-lang-zh_CN.js"></script>
<script src="/assets/lib/jquery-easyui-1.4.1/bufferview.js"></script>
<script src="/assets/lib/moment.min.js"></script>
<script src="/assets/lib/bootstrap-datepicker/bootstrap-datepicker.js"></script>
<link href="/assets/lib/bootstrap-datepicker/datepicker.css" rel="stylesheet" />
<script src="/assets/lib/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
<script src="/assets/lib/bootstrap-datetimepicker/bootstrap-datetimepicker.zh-CN.js"></script>
<link href="/assets/lib/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css" rel="stylesheet" />
<div style="height: 50px;border-top: 2px solid #03a3da!important;"></div>
<div>
    <div class="box box-info" style='padding:0px;'>
        <form id="uploadForm" enctype="multipart/form-data" method="post">
            <div class="form-horizontal">
                <div class="form-group">
                    <label class="col-sm-2 control-label">需求名称</label>
                    <div class="col-sm-3">
                        <input type="text" class="form-control" name='demand_name'>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">开始时间</label>
                    <div class="col-sm-2">
                        <input type="text" readonly="readonly" class="form-control  datepicker start inputlist big-input" class="form-control" name='start_time' id="start_time">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">结束时间</label>
                    <div class="col-sm-2">
                        <input type="text" readonly="readonly" class="form-control  datepicker start inputlist big-input" class="form-control" name='end_time' id="end_time">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">运行hql</label>
                    <div class="col-sm-6">
                        <textarea class="form-control" rows="10" placeholder="时间变量格式如：#dt" name='demand_hql'></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"></label>
                    <div class="col-sm-6">
                        <button type="button" id='addruntask' class="btn btn-sm btn-primary">提交</button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"></label>
                    <div class="col-sm-6">
                        <div class="modal-body" id="return_message"></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    $(function () {
        $("#start_time" ).datetimepicker({
            language:"zh-CN",    //语言选择中文
            format:"yyyy-mm-dd",    //格式化日期
            timepicker:true,     //关闭时间选项
            yearEnd:2050,        //设置最大年份
            todayButton:false,    //关闭选择今天按钮
            autoclose: 1,        //选择完日期后，弹出框自动关闭
            minView:2
        });
        $("#end_time" ).datetimepicker({
            language:"zh-CN",    //语言选择中文
            format:"yyyy-mm-dd",    //格式化日期
            timepicker:true,     //关闭时间选项
            yearEnd:2050,        //设置最大年份
            todayButton:false,    //关闭选择今天按钮
            autoclose: 1,        //选择完日期后，弹出框自动关闭
            minView:2
        });
        $('#addruntask').click(function(){
            if($("input[name='demand_name']").val() == '') {
                alert('请填写需求名称');
                return false;
            }

            if($("input[name='start_time']").val() == '') {
                alert('请填写开始时间');
                return false;
            }

            /*if (!checkData($("input[name='start_time']").val())) {
                alert('开始时间格式不正确');
                return false;
            }*/

            if($("input[name='end_time']").val() == '') {
                alert('请填写结束时间');
                return false;
            }

            /*if (!checkData($("input[name='end_time']").val())) {
                alert('结束时间格式不正确');
                return false;
            }*/

            if($("textarea[name='demand_hql']").val() == '') {
                alert('请填写hql');
                return false;
            }

            if(confirm('是否确认提交')) {
                $('#addruntask').attr("disabled","disabled").html('正在提交中,请等待...');
                $.ajax({
                    type: "POST",
                    url : "/RunTask/runtask",
                    data: $('#uploadForm').serialize(),
                    success: function(data) {
                        var res = $.parseJSON(data);
                        if (res.status == 0) {
                            $('#addruntask').attr("disabled", "disabled").html('任务已提交，请关闭窗口耐心等待...');
                        } else {
                            alert(res.msg);
                            $('#addruntask').removeAttr("disabled").html('提交');
                        }
                    },
                    error: function(e) {
                        alert('未知错误');
                        $('#addruntask').attr("disabled","disabled").html('服务异常，请联系管理员...');
                    }
                });
            }
        });
    });
</script>
