{/include file="layouts/lib.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">
<style type="text/css">
    body{
        background: none;
    }
    .pagination-page-list{
        font-size: 12px;
    }
    .pagination-num{
        width: 3em!important;
    }
    .content.special{
        margin-left: 10px;
    }

    .sale-tablelist .search{
        background: #C0C0C0;
    }
    .sale-tablelist input{
        height: 20px!important;
        line-height: 20px;
        font-size: 12px;
    }
    #tb .search-filter span{
        height: 23px;
        border:1px solid #ccc;
    }
    .sales-btn{
        color:#404040;
        width: 32px;
    }
</style>
<script>
    {/if  $dt_list  neq ''/}
    var dt_list = {/$dt_list/};
    var zone_arr = {/$zone_arr/}
    {//if/}
</script>

<div id="content special" class="content special" >
    <div  class="rightreport" style="margin-left:-10px">
        <div style='width:98%;margin:auto' id='canvas_down'>
            <!-- content loading -->
            <div class="sale-tablelist">
                <div class='configBox'>
                    <div class='error_showmsg'><div class='text'></div></div>
                    <div class='boxContent tablecontent'></div>
                    <table id="sale-table" style="width:auto;height:auto"
                           toolbar="#tb" loadMsg="数据正在加载。。。"
                           title="商品销售数据展示表" iconCls="icon-save" pageSize="10" pageList=[10,20]
                           rownumbers="true" pagination="true" data-options="nowrap:true">
                    </table>
                    <div id="tb" style="padding:3px">
                        <span>区域</span>&nbsp;
                        <select id="zone_area"></select>&nbsp;
                        <span>商品码</span>&nbsp;
                        <input type="text" name='item_id' id="item_id" value=''/>&nbsp;
                        <a href="javascript:void(0);" class="easyui-linkbutton btn btn-sm search" plain="true">查询</a>&nbsp;
                        <a href="javascript:void(0);" class="l-btn-text down-data btn btn-sm search sales-btn" plain="true">下载</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(function(){
        //gettable();
        loadArea();//加载区域
        loadTableHead();//加载表格
        function loadArea (zone_id) {
            var zone_html = [];
            zone_html.push("<option value=''>--选择--</option>");
            $.each(zone_arr,function(i,el){
                var sel = '';
                if (i == zone_id) {
                    sel += 'selected="selected"';
                }
                zone_html.push("<option "+sel+" value='"+i+"'>"+el+"</option>");
            });
            $("#zone_area").html(zone_html.join(''));
        }

        //bind区域事件
        $("#zone_area").change(function(){
            loadTableHead();
        })

        function loadTableHead () {
            var frozencol = [];
            var columnsdata = [];
            var array =[];

            frozencol =[[
                //{field:'cdate',title:'日期',width:80},
                {field:'zone_id',title:'地域',width:50}
            ]];

            array.push({field:'first_cat_name',title:'一级品类',width:80});
            array.push({field:'second_cat_name',title:'二级品类',width:80});
            array.push({field:'item_id',title:'商品码',width:60});
            array.push({field:'w_code',title:'物美码',width:140});
            array.push({field:'sku_name',title:'商品名称',width:200});
            array.push({field:'brand',title:'品牌',width:80});

            $.each(dt_list,function(){
                //获取日期
                array.push({field:'',title:'',width:0});
            });

            columnsdata.push(array);

            if (dt_list) {
                $.each(dt_list,function(i,el){
                    columnsdata[0][i+6]['field']= el;
                    columnsdata[0][i+6]['title']= el;
                    columnsdata[0][i+6]['width']= 80;
                });
            };

            columnsdata[0].push({field:'qty_x_normal_14',title:'商品14天销量',width:90});
            columnsdata[0].push({field:'qty_x_14',title:'14天非售罄日销量',width:110});
            columnsdata[0].push({field:'avg_sale',title:'日均销量*7',width:80});
            columnsdata[0].push({field:'inventory_num',title:'库存量',width:70});
            columnsdata[0].push({field:'rule_name',title:'售卖规则',width:90});
            columnsdata[0].push({field:'status',title:'状态',width:70});
            columnsdata[0].push({field:'sku_level',title:'等级',width:40});
            columnsdata[0].push({field:'DC10',title:'北京南皋仓',width:80});
            columnsdata[0].push({field:'DC31',title:'北京寄售仓',width:80});
            columnsdata[0].push({field:'DC41',title:'北京冻品仓',width:80});
            columnsdata[0].push({field:'created_at',title:'创建时间',width:90});

            //queryParams
            var z_id = $("#zone_area").val();
            var item_id = $('#item_id').val();
            var queryjson = {
                zone_id: z_id,
                item_id: item_id
            };

            var pageList  = [10,20,50];
            //获取表头后，建表
            $('#sale-table').datagrid({
                url:'/Salesvisit/Goodsdata',
                frozenColumns : frozencol, //不动列数据展示
                columns: columnsdata, //移动动列数据展示
                queryParams:queryjson,
                pageList: pageList,
                //过滤数据
                loadFilter: function(data){
                    return  {
                        total: data.total,
                        rows: data.list
                    };
                },
                onLoadSuccess:function(result){
                }
            });

            //查询

            $('.easyui-linkbutton').on("click", function () {
                renderDatagrid();
            });

            //分页
            $("#sale-table").datagrid('getPager').pagination({
                beforePageText: '',//页数文本框前显示的汉字
                afterPageText: '/ {pages} 页',
                displayMsg: '共 {total} 条数据'
            });
        }

        function renderDatagrid () {
            var zone_id = $('#zone_area').val();
            var item_id = $('#item_id').val();
            $('#sale-table').datagrid('load',{
                zone_id: zone_id,
                item_id: item_id
            });
        }

        //下载
        $('.down-data').on("click", function () {
            var zone_id=$('#zone_area').val();
            var item_id = $('#item_id').val();
            location.href = "/Salesvisit/Downgoodsdata?zone_id="+zone_id+"&item_id="+item_id
        });
    });
</script>