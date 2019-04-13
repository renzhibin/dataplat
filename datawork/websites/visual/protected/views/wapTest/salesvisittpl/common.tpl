<script src="/assets/wapTest/js/search.js?version={/$version/}"></script>
<style type="text/css">
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
<div style='width:98%;margin:auto' id='canvas_down'>
    <!-- content loading -->
    <div class="sale-tablelist">
        <div class='configBox'>
            <div class='error_showmsg'><div class='text'></div></div>
            <div class='boxContent tablecontent'></div>
             <table id="sale-table" style="width:auto;height:auto"
                  toolbar="#tb" loadMsg="数据正在加载。。。"
                   title="销售拜访分析表" iconCls="icon-save" pageSize="10" pageList=[10,20]
                   rownumbers="true" pagination="true">
            </table>
            <div id="tb" style="padding:3px">
                <span>区域</span>&nbsp;
                <select id="zone_area"></select>&nbsp;
                <span>销售主管</span>&nbsp;
                <select id="sale_leader"></select>&nbsp;
                <span>销售姓名</span>&nbsp;
                <select id="sale_list"></select>
                &nbsp;
                <!--
                <span>超市帐号</span>
                <input class="market-account">-->
                <a href="javascript:void(0);" class="easyui-linkbutton btn btn-sm search" plain="true">查询</a>
                <a href="javascript:void(0);" class="l-btn-text down-data btn btn-sm search sales-btn" plain="true">下载</a>
                <form method='post' action='/Salesvisit/LoadSaledata'  id='downData' >
                    <input type='hidden' name='zone_id' value=''/>
                    <input type='hidden' name='sales_name' value=''/>
                    <input type='hidden' name='leader_name' value=''/>
                    <input type='hidden' name='market_account' value=''/>
                </form>
                <div style="margin: 5px 0;"><span style="padding-right: 5px">说明:</span><span style="color: red">A:参加套餐活动</span><span style="padding: 0 4px;font-style: italic;">A:参加特价活动(斜体)</span><span style="padding: 0 4px;text-decoration: underline">A:参加满减活动(下划线)</span><span style="padding: 0 4px;background-color: green; color: white">A:销售拜访</span><span style="padding: 0 4px">A:参加秒杀活动(*)</span></div><input type="hidden" id="total_num_sort" value="desc"><input type="hidden" id="date_sort" value="">

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function(){
        //gettable();
        loadArea(1000);//加载区域
        loadLeader(1000);//加载主管
        loadTableHead();//加载表格
        function loadArea (zone_id) {
            var zone_html = [];
            //zone_html.push("<option value=''>--选择--</option>");
            $.each(zone_arr,function(i,el){
                var sel = '';
                if (i == zone_id) {
                    sel += 'selected="selected"';
                }
                zone_html.push("<option "+sel+" value='"+i+"'>"+el+"</option>");
            });
            $("#zone_area").html(zone_html.join(''));
        }

        function loadLeader (zone_id) {
            var leader_html = [];
            leader_html.push("<option value=''>--选择--</option>");
            if (saler_list[zone_id]) {
                $.each(saler_list[zone_id],function(i,el){
                    leader_html.push("<option  value='"+el.uid+"'>"+el.sales_name+"</option>");
                });
            }
            $("#sale_leader").html(leader_html.join(''));
            loadSale(zone_id);
        }

        function loadSale (z_id,f_uid) {
            var sale_html = [];
            sale_html.push("<option value=''>--选择--</option>");
            if (z_id && f_uid) {
                if (saler_list[z_id]) {
                      $.each(saler_list[z_id],function(i,el){
                        if(el[f_uid]){
                          if (el.uid == f_uid) {

                              $.each(el[f_uid],function(m,item){
                                  sale_html.push("<option  value='"+item.uid+"'>"+item.sales_name+"</option>");
                              });

                              return false;
                          }
                        }
                    });
                }
            } else {
                if (saler_list[z_id]) {
                    $.each(saler_list[z_id],function(i,el){
                        if (el[el.uid]){
                            $.each(el[el.uid],function(m,item){
                                sale_html.push("<option  value='"+item.uid+"'>"+item.sales_name+"</option>");
                            });
                        }
                    });
                }
            }
            $("#sale_list").html(sale_html.join(''));
        }

        //bind区域事件
        $("#zone_area").change(function(){
            var z_id = $(this).val();
            loadLeader(parseInt(z_id));
        })

        //bind主管事件
        $("#sale_leader").change(function(){
            var f_uid = $(this).val();
            var z_id = $("#zone_area").val();
            loadSale(z_id,f_uid);
        })

        function loadTableHead () {
            var frozencol = [];
            var columnsdata = [];
            var array =[];

            frozencol =[[
                //{field:'sales_name',title:'销售姓名',width:70},
                //{field:'leader_name',title:'所属主管',width:70},
                {field:'market_name',title:'超市名称',width:120},
                //{field:'market_account',title:'帐号',width:100},
                //{field:'total_order_nums',title:'总订单量',width:90},
                //{field:'regier_time',title:'注册时间',width:130}
            ]];

            array.push({field:'sales_name',title:'销售姓名',width:70});
            array.push({field:'leader_name',title:'所属主管',width:70});
            array.push({field:'total_order_nums',title:'总订单量',width:90});
            array.push({field:'regier_time',title:'注册时间',width:130});

            $.each(dt_list,function(){
                //获取日期
                array.push({field:'',title:'',width:0});
            });

            columnsdata.push(array);

            if (dt_list) {
                $.each(dt_list,function(i,el){
                    columnsdata[0][i+4]['field']= el;
                    columnsdata[0][i+4]['title']= el;
                    columnsdata[0][i+4]['width']= 90;
                });
            };

            //queryParams
            var z_id = $("#zone_area").val();
            var t_sort = $("#total_num_sort").val();
            var queryjson = {
                zone_id: z_id,
                total_num_sort:t_sort
            };
            var pageList  = [10,20,50];
            //获取表头后，建表
            $('#sale-table').datagrid({
                url:'/Salesvisit/FetchSaledata',
                frozenColumns : frozencol, //不动列数据展示
                columns: columnsdata, //移动动列数据展示
                queryParams:queryjson,
                pageList: pageList,
                //过滤数据
                loadFilter: function(data){
                    return  {
                        total: data.total,
                        rows: formatData(data.resultlist)
                    };
                },
                onLoadSuccess:function(result){
                }
            });

            //查询

            $('.easyui-linkbutton').on("click", function () {
                renderDatagrid();
            });
            //下载
            $('.down-data').on("click", function () {
                var zone_id=$('#zone_area').val();
                $("input[name=zone_id]").val(zone_id);
                $("input[name=sales_name]").val(getSaleName(zone_id,false));
                $("input[name=leader_name]").val(getSaleName(zone_id,true));
                $("input[name=market_account]").val($('.market-account').val());
                $("#downData").submit();
            });
            //总销量排序
            $('.datagrid-cell-c1-total_order_nums').on("click", function () {
                removeDateclass();
                var class_asc = $(this).hasClass('datagrid-sort-asc');
                var class_desc = $(this).hasClass('datagrid-sort-desc');
                if (class_asc === false && class_desc === true) {
                    $(this).removeClass(' datagrid-sort-desc');
                    $(this).addClass(' datagrid-sort-asc');
                    $("#total_num_sort").val('asc');
                } else if(class_asc === true && class_desc === false){
                    $(this).removeClass(' datagrid-sort-asc');
                    $(this).addClass(' datagrid-sort-desc');
                    $("#total_num_sort").val('desc');
                } else {
                    $(this).addClass(' datagrid-sort-asc');
                    $("#total_num_sort").val('asc');
                }
                $("#date_sort").val('');
                renderDatagrid();
            });

            //绑定日期事件
            if (dt_list) {
                $.each(dt_list,function(i,el){
                    var t = this;
                    t.dt = el;
                    $(".datagrid-cell-c1-"+el).on("click",function(){
                        removeDateclass(el);
                        var class_asc = $(this).hasClass('datagrid-sort-asc');
                        var class_desc = $(this).hasClass('datagrid-sort-desc');
                        if (class_asc === false && class_desc === true) {
                            $(this).removeClass(' datagrid-sort-desc');
                            $(this).addClass(' datagrid-sort-asc');
                            $("#date_sort").val(el+':asc');
                        } else if(class_asc === true && class_desc === false){
                            $(this).removeClass(' datagrid-sort-asc');
                            $(this).addClass(' datagrid-sort-desc');
                            $("#date_sort").val(el+':desc');
                        } else {
                            $(this).addClass(' datagrid-sort-desc');
                            $("#date_sort").val(el+':desc');
                        }
                        renderDatagrid();
                    });
                });
            }

            //分页
            $("#sale-table").datagrid('getPager').pagination({
                beforePageText: '',//页数文本框前显示的汉字
                afterPageText: '/ {pages} 页',
                displayMsg: '共 {total} 条数据'
            });
        }

        function removeDateclass(dt){
            $.each(dt_list,function(i,el){
                if (dt != el) {
                    if ($(".datagrid-cell-c1-"+el).hasClass('datagrid-sort-desc')) {
                        $(".datagrid-cell-c1-"+el).removeClass('datagrid-sort-desc');
                    }

                    if ($(".datagrid-cell-c1-"+el).hasClass('datagrid-sort-asc')) {
                        $(".datagrid-cell-c1-"+el).removeClass('datagrid-sort-asc');
                    }
                }
            });
        }

        function renderDatagrid (zone_id) {
            var zone_id = $('#zone_area').val();
            $('#sale-table').datagrid('load',{
                zone_id: zone_id,
                sales_name: getSaleName(zone_id,false),
                leader_name: getSaleName(zone_id,true),
                market_account: $('.market-account').val(),
                total_num_sort:$('#total_num_sort').val(),
                date_sort:$('#date_sort').val()
            });
        }

        function getSaleName (zid,isLeader) {
            var s_uid;
            if (!saler_list[zid]) {
                return false;
            }

            if (isLeader == true) {
                s_uid = $('#sale_leader').val();
                var s_name = '';
                $.each(saler_list[zid],function(i,el){
                    if (el.uid == s_uid) {
                        s_name = el.sales_name;
                        return false;
                    }
                });
                return s_name;
            } else {
                s_uid = $('#sale_list').val();
                var s_name = '';
                $.each(saler_list[zid],function(i,el){
                  if(el[el.uid]){
                    $.each(el[el.uid],function(m,item){
                       if (item.uid == s_uid) {
                           s_name = item.sales_name;
                           return false;
                       }
                    });
                  }
                });
                return s_name;
            }
        }

        function formatData(listData){
            var newListData = [];
            $.each(listData,function(index,value){
                var obj = {
                    "sales_name" : value.sales_name,
                    "leader_name" : value.leader_name,
                    "market_name" : value.market_name,
                    "market_account" : value.market_account,
                    "total_order_nums" : value.total_order_nums,
                    "regier_time" : value.regier_time
                };
                $.each(value.date_list,function(key,val){
                    var dayNum = '';
                    var sStyle = 'display:inline-block;margin: 0 -6px;width:117%;text-align:center;';

                    if (val.day_nums > 0) {
                        sStyle += 'font-weight:bold;font-size:13px;';
                    }

                    if(val.is_tejia>0){
                        sStyle += 'font-weight:bold;font-size:13px;font-style:italic;'
                    }
                    if(val.is_manjian>0){
                        sStyle += 'font-weight:bold;font-size:13px;text-decoration: underline;'
                    }
                    if(val.is_visit>0){
                        sStyle += 'height:30px;font-weight:bold;font-size:13px;background-color:green;color: white;';
                    }

                    if(val.is_taocan>0){
                        sStyle += 'font-weight:bold;font-size:13px;color:red;'
                    }

                    if(val.is_miaosha>0){
                        sStyle += 'font-weight:bold;font-size:14px;'
                    }

                    var space = (val.day_nums == 0) ? '' : val.day_nums;

                    if (val.is_miaosha > 0) {
                        space += '*'
                    }

                    dayNum = '<span style="'+sStyle+'" >'+space+'</span>';
                    obj[val.date] = dayNum;
                });
                newListData.push(obj);
            });
            return newListData;
        }
        function gettable(params){
            return false;
            var result = [];
            var columnsdata=[];
            var frozencol = [];
            $.ajax({
                url:'/Salesvisit/FetchSaledata',
                type:'POST',
                data:params?params:'',
                dataType:'json',
                success:function(data){
                    //TODO 加载数据过慢，需要优化
                    result = data;
                    var array =[];
                    frozencol =[[
                        {field:'sales_name',title:'销售姓名',width:90},
                        {field:'leader_name',title:'所属主管',width:90},
                        {field:'market_name',title:'超市名称',width:100},
                        {field:'market_account',title:'帐号',width:100},
                        {field:'total_order_nums',title:'总订单量',width:80},
                        {field:'regier_time',title:'注册时间',width:130}
                    ]];

                    if(result.resultlist.length>0){
                        $.each(result.resultlist[0].date_list,function(){
                            //获取日期
                            array.push({field:'',title:'',width:0});
                        });
                        columnsdata.push(array);
                        $.each(result.resultlist,function(k,v){
                            $.each(v.date_list,function(i,el){
                                columnsdata[0][i]['field']= el['date'];
                                columnsdata[0][i]['title']= el['date'] ;
                                columnsdata[0][i]['width']= 80;
                            });
                        });
                    }
                    var listData = formatData(data.resultlist);
                    //获取表头后，建表
                    $('#sale-table').datagrid({
                        //过滤数据
                        loadFilter: function(data){
                            return  {
                                total: data.total,
                                rows: listData
                            };

                        },
                        url:'/Salesvisit/FetchSaledata',
                        frozenColumns : frozencol, //不动列数据展示
                        columns: columnsdata //移动动列数据展示
                    });
                //查询
                $('.easyui-linkbutton').on("click", function () {
                    $('#sale-table').datagrid('load',{
                        sales_name: $('.sales-name').val(),
                        leader_name: $('.leader-name').val(),
                        market_account: $('.market-account').val()

                    });
                    //点击查询操作只有要家在数据
                    gettable({
                        sales_name: $('.sales-name').val(),
                        leader_name: $('.leader-name').val(),
                        market_account: $('.market-account').val()

                    });
                });
                //分页
                $("#sale-table").datagrid('getPager').pagination({
                    beforePageText: '',//页数文本框前显示的汉字
                    afterPageText: '/ {pages} 页',
                    displayMsg: '共 {total} 条数据'
                });
                }
            });
        }
    });
</script>
