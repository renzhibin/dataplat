{/include file="homefocus/header.tpl"/}
{/include file='homefocus/source.tpl'/}

<div class="container-fluid box">
<div class="pad-bt10">
    <ol class="breadcrumb">
        <li><a href="/AppHomefocus/index">首页</a></li>
        <li><a href="javascript:void(0)"><b class="sourcetitle"></b></a></li>
    </ol>
</div>
<!-- applybox -->
<div class="applybox">
    <div class="showinfo"></div>
    <div class="info">

    </div>

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
    window.params['flowname'] = {/$flowname/};
    window.params["datainfo"]= {/$datas/};
    window.params['statusName'] = {/$statusName/};
    window.category = {/$category/};

});
</script>
<script type="text/javascript" src="/assets/homefocus/apply.js?version={/$version/}"></script>
{/include file="homefocus/footer.tpl"/}
