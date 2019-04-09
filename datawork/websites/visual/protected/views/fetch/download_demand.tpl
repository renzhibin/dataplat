{/include file="layouts/lib.tpl"/}
<link rel="stylesheet" href="/assets/css/url-interface.css?version={/$version/}">
<script src='/assets/lib/bootstrap-filestyle.min.js?version={/$version/}'></script>
<div style="height: 50px;border-top: 2px solid #03a3da!important;"></div>
<div>
    <div class="box box-info" style='0px;'>
        <form id="uploadForm" enctype="multipart/form-data" action='../fetch/download' method="post">
            <div class="form-horizontal">
                <div class="form-group">
                    <label class="col-sm-2 control-label">请输入提取码</label>
                    <div class="col-sm-2">
                        <input type="text" class="form-control" name='demand_pwd'>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"></label>
                    <div class="col-sm-6">
                        <button type="submit" class="btn btn-sm btn-primary" onclick="return check(this.form)" >确认下载</button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-1 control-label"></label>
                    <div class="col-sm-6">
                        <div class="modal-body" id="return_message">
                            {/if $code eq 400 /}
                                <span style='color:red'>{/$msg/}</span>
                            {//if/}
                        </div>
                    </div>
                </div>
                <input type="hidden" class="form-control" name="down_action" value="1">
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    function check(form) {
        if(form.demand_pwd.value == '') {
            alert("请输入提取码!");
            form.demand_pwd.focus();
            return false;
        }
        return true;
    }

</script>
