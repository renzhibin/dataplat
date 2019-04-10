
{/include file="layouts/lib.tpl"/}
<style type="text/css">
    .search_input {
        position: relative;
        top: 15px;
    }

    .search_input input {
        height: 30px;
        line-height: 30px;
        width: 230px;
        margin-right: 10px;
    }
    .a-upload {
        padding: 4px 10px;
        height: 20px;
        line-height: 20px;
        position: relative;
        cursor: pointer;
        color: #888;
        background: #fafafa;
        border: 1px solid #ddd;
        border-radius: 4px;
        overflow: hidden;
        display: inline-block;
        *display: inline;
        *zoom: 1
    }

    .a-upload  input {
        position: absolute;
        font-size: 100px;
        right: 0;
        top: 0;
        opacity: 0;
        filter: alpha(opacity=0);
        cursor: pointer
    }

    .a-upload:hover {
        color: #444;
        background: #eee;
        border-color: #ccc;
        text-decoration: none
    }
    .tips{
        background: #e2edfb;
        padding: 10px;
        border-radius: 5px;
        font-size: 14px;
        border: 1px solid #eee;
        color:#0965b8;
        margin-bottom: 10px;
    }
    .con_box{
        position:  relative;
    }
</style>
<script src='/assets/lib/bootstrap-filestyle.min.js?version={/$version/}'></script>
<div>
    <div class='container'>
        <div class="tips">
            <p>1: 请上传.csv文件</p>
            <p>2: 表名和列名必须为[英文] 或 [英文+下划线]组合,且回避关键字( type,flag,name,date )等</p>
            <p>3: 请等待预览效果出现后再点击生成按钮,目前只显示2条数据</p>
            <p>4: 创建成功后, 请复制表名到 <a target='_blank' href='http://dq.xiaozhu.com/#datasource=presto1&tab=treeview'>http://dq.xiaozhu.com</a> 中查询,默认创建表到tmp库</p>
            <p>5: 选择追加，是把上传的数据追加到所填写的数据表中！（注：追加只需要填写临时表名称）</p>
            <p>6: 创建的临时表全称为： 您输入的临时表名称+ _tools_tmp_table 如： abc_tools_tmp_table</p>
        </div>
        <div class="con_box">
            <form id="uploadForm" enctype="multipart/form-data" method="post" style="width:400px">
                <input type="file" class='filestyle' data-buttonText="选择上传文件" data-buttonBefore="false"  data-icon="false" id="imexcel" name="imexcel" value="上传" />
                <div class="input-group" style="margin-top:20px">
                    <span class="input-group-addon" id="basic-addon3">临时表名称</span>
                    <input type="text" class="form-control" id="name" name="name" aria-describedby="basic-addon3">  
                </div>
                <div style="margin-top:10px">
                    <span>是否为追加: </span><input type='checkbox' name="is_add">
                </div>
            </form>
        </div>
        <div class="box-body" style="width:100%">
            <div id="true-body" style="width:100%; overflow: auto" ></div>
            <div style="width:100%; overflow: auto;margin-top:10px;" >
                <span id='showbutton'><button type="button" id='uploadfile' class="btn btn-primary">确认生成临时表</button></span>
            </div>
        </div>
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">系统提示</h4>
                    </div>
                    <div class="modal-body" id="return_message"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" class="form-control" id="_json" name="_json" value="">
</div>
<script type="text/javascript">
    $(function () {
        //上传excel
        $('#imexcel').change(function(){
            $('#uploadfile').attr("disabled","disabled").html('文件上传中...');
            $.ajax({
                type: "POST",
                url: "/tool/GetFileUp",
                data: new FormData($('#uploadForm')[0]),
                cache: false,
                processData: false,
                contentType: false,
                success: function (data) {
                    var str = "<table>";
                    var res = $.parseJSON(data);
                    $('#_json').val(res.data.file);
                    var count = 0;
                    $.each(res.data.row, function(i, item) {
                        if(item != "" && count < 10) {
                            str = str + "<tr>";
                            $.each(item, function(ii, val) {
                                str = str + "<td>" + val + "</td>";
                            });
                            str = str + "</tr>";
                            count++;
                        }
                        else {
                            return false;
                        }
                    });
                    $('#uploadfile').removeAttr("disabled").html('确认生成临时表');
                    str = str + "</table>";
                    $("#true-body").html(str);
                    $("table").css({"width":'1000'});
                    $("table").addClass('table table-bordered table-hover table-striped');
                    $("td").css({"width":'100px', "border":"1px solid black"});
                }
            });
        });
        //建表
        $('#uploadfile').click(function(){

            var mydate = new Date();
            var is_add = $('input[name=is_add]').is(':checked')?1:0;
            if($('#name').val() == '') {
                alert('请填写表名');
                return false;
            }
            var file =$('#_json').val();
            if(!file){
                 alert('没有上传excel文件');
                 return false;
            }
            
            if(confirm('是否确认创建临时表')) {

                $('#uploadfile').attr("disabled","disabled").html('建表或数据导入中,请等待...');
                
                $.ajax({
                    type: "POST",
                    url : "/tool/CreateHiveData",
                    data: {
                        'is_add':is_add,
                        'file': $('#_json').val(),
                        'name': $('#name').val() + '_tools_tmp_table'
                    },
                    success: function(data) {
                        $('#uploadfile').attr("disabled", false).html('确认生成临时表');
                        $('#return_message').html(data);
                        $('#myModal').modal('toggle');
                    },
                    error: function(e) {
                        alert('未知错误');
                    }
                });
            }
        });

    });

</script>
































































