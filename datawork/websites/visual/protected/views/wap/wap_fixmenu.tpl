<div class="fixed_footer">
    <div class="row">
        <div class="col-xs-3 col-md-3 limd"><a href="/wap/index"><i class="glyphicon glyphicon-home"></i></a></div>
        <div class="col-xs-3 col-md-3 limd other" data-toggle="modal"><i class="glyphicon glyphicon-th-large"></i></div>
        <div class="col-xs-3 col-md-3 limd"><a href="/wap/collect"><i class="glyphicon glyphicon-heart"></i></a></div>
        <div class="col-xs-3 col-md-3 limd"><a href="/wap/recently"><i class="glyphicon glyphicon-star"></i></a></div>
    </div>
</div>

<div class="modal fade" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">表格搜索功能</h4>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <!--<button type="button" class="btn btn-primary">确定</button>-->
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<script type="text/javascript">
    $(document).ready(function() {
        var $configbox = $(".configBox");
        $configbox.find(".customkey").hide();
        $configbox.find(".filter").hide();

        var $mymodal = $('#myModal');
        $("body").on('click','.fixed_footer .other',function(){
            var temphtml ="";
            $('.configBox').each(function(i){
               var _this = $(this);
               temphtml += '<div class="configBoxs" index="'+i+'"><div class="tabletitle">'+_this.find('.tabletitle').html()+'</div><div class="filter" >'+_this.find('.filter').html()+'</div></div>';
            });
            $mymodal.find('.modal-body').html(temphtml);
            $mymodal.modal('show');
        });

        $('#myModal').on('click','.btnSearch',function(){
            var index = $(this).closest(".configBoxs").attr("index");
            var apisearch = window.tables[index].apifilter();
            window.tables[index].searchtag = $(this).closest(".configBoxs").find(".filter");
            window.tables[index].showTable();
        });
    });
</script>