{/include file="layouts/lib.tpl"/}
<link rel="stylesheet" href="/assets/css/url-interface.css?version={/$version/}">
<script src='/assets/lib/bootstrap-filestyle.min.js?version={/$version/}'></script>
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
                    <label class="col-sm-2 control-label">需求方</label>
                    <div class="col-sm-3">
                        <input type="text" class="form-control" name='demand_user'>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">需求方邮箱</label>
                    <div class="col-sm-6">
                        <textarea class="form-control" rows="5" placeholder="允许下载文件的用户列表,用','号分隔" name='demand_email'></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">外发单位名称</label>
                    <div class="col-sm-3">
                        <input type="text" class="form-control" name='company_name'>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">邮件EML文件</label>
                    <div class="col-sm-5">
                        <input type="file" style='display:inline;'class='filestyle' data-buttonText="选择上传文件" data-buttonBefore="true"  data-icon="false" id="imexcel" name="imexcel" value="上传" />
                        <span class="text-red"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">产出文件列表</label>
                    <div class="col-sm-6">
                        <textarea class="form-control" rows="5" placeholder="产出文件列表(仅支持Hadoop路径),用','号分隔" name='data_path'></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"></label>
                    <div class="col-sm-6">
                        <button type="button" id='adddemand' class="btn btn-sm btn-primary">提交需求</button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"></label>
                    <div class="col-sm-6">
                        <div class="modal-body" id="return_message"></div>
                    </div>
                </div>
                <input type="hidden" class="form-control" id="eml_path" name="eml_path" value="">
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    $(function () {
        //上传excel
        $('#imexcel').change(function(){
            $('#adddemand').attr("disabled","disabled").html('文件上传中...');
            $.ajax({
                type: "POST",
                url: "/fetch/uploadFile",
                data: new FormData($('#uploadForm')[0]),
                cache: false,
                processData: false,
                contentType: false,
                success: function (data) {
                    var res = $.parseJSON(data);
                    if(res.status == 400){
                        alert(res.data.file);
                    }else{
                        $('#eml_path').val(res.data.file);
                    }
                    $('#adddemand').removeAttr("disabled").html('提交需求');
                }
            });
        });
        
        //建表
        $('#adddemand').click(function(){
            if($("input[name='demand_name']").val() == '') {
                alert('请填写需求名称');
                return false;
            }

            if($("input[name='demand_user']").val() == '') {
                alert('请填写需求方');
                return false;
            }

            var reg = new RegExp("^[A-Za-z0-9]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$");
            var tag = false;
            if($("textarea[name='demand_email']").val() == '') {
                alert('请填写需求方邮箱');
                return false;
            }else {
                email_arr = $("textarea[name='demand_email']").val().split(',');
                $.each(email_arr,function(index,value){
                    if(!reg.test(value)) {
                        tag = true;
                        return false;
                    }
                });
            }
            if(tag) {
                tag = true;
                alert('请填写正确的需求方邮箱');
                return false;
            }

            if($("input[name='company_name']").val() == '') {
                alert('请填写外发单位名称');
                return false;
            }

            if($("textarea[name='data_path']").val() == '') {
                alert('请填写产出文件列表');
                return false;
            }

            if($("input[name='eml_path']").val() == '') {
                alert('请上传邮件EML文件');
                return false;
            }else{
                var index = $("input[name='eml_path']").val().lastIndexOf(".");
                var ext = $("input[name='eml_path']").val().substr(index+1);
                if(ext !== 'eml'){
                    alert('请上传正确的邮件EML文件');
                    return false;
                }
            }

            if(confirm('是否确认提交')) {
                $('#adddemand').attr("disabled","disabled").html('正在提交中,请等待...');
                $.ajax({
                    type: "POST",
                    url : "/fetch/saveDemand",
                    data: $('#uploadForm').serialize(),
                    success: function(data) {
                        var res = $.parseJSON(data);
                        alert(res.data.msg);
                        window.location.reload();
                    },
                    error: function(e) {
                        alert('未知错误');
                    }
                });
            }
        });
    });
</script>
