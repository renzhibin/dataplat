/**
 * 根据链接类型，返回类型和相应参数（json格式）
 * new dataSource({
 *  id:xxx, 对象id
 * */

var Table=function(option){
	this.option = option;
	this.init();
}
Table.prototype = {
    init:function(){
        this.getDatagrad();
    }, 
    getSearch:function(){
        var keyArr = this.option.search;
        var searchVal = {};
        if(keyArr){
            for(var  i=0; i< keyArr.length; i++){
                switch( keyArr[i].type){
                    case 'checkbox': 
                        if($('input[name='+keyArr[i].key+']').is(":checked")  ){
                            searchVal[keyArr[i].key] = 'yes';
                        }else{
                            searchVal[keyArr[i].key] ='no';
                        }
                        break;
                    case 'multiple':
                        $('select[name='+keyArr[i].key+']').select2();
                        searchVal[keyArr[i].key] = $.trim($('select[name='+keyArr[i].key+']').select2('val'));
                        break;
                    default:
                     
                        searchVal[keyArr[i].key] = $.trim($(keyArr[i].type+'[name='+keyArr[i].key+']').val());
                        break;
                }
            }
        }
        return searchVal;
    },
    //加载成功执行操作
    loadSuccess:function(obj,result,search){
            //大图显示图片
            $('.imgShow').tooltip({
              position: 'right',
              content: function(){  
                  console.log(1);
                var imgurl = $(this).find('img').attr('src');
                var str = "<div><img src="+imgurl +"  width='200' height='230'/></div>";
                return str;
              }
            });
            $('.showinfo').tooltip({ 'position':'top'});
             
    },
    //获取第一个排序
    getFirstSort:function(){
         var config  = this.option.config;
         var sort ={};
        for(var i =0; i< config.length; i++){
            if( config[i].sort !=undefined){
                if(config[i].order !=undefined ){
                    sort.sortOrder = config[i].order;
                }else{
                    sort.sortOrder = 'desc';
                }
                sort.sortName = config[i].key;
                return sort;
            }
        }
        return false;
    },
    //生成datagrad表头
    getDatagrad:function(){
        var _this = this;
        var obj     = $("#"+_this.option.id);
        var config  = _this.option.config;
        var url     = _this.option.url;
        var  columnArr = _this.getcolumn(config);
        var beforeLoad = _this.option.beforeLoad;
        //分页设置
        var pageList  = [10,30,50,100];
        var search  = _this.getSearch();
        var queryjson = {"search":JSON.stringify(search)};
        var gardconf = _this.option.gardconf;
        //特殊设置 如页码,
        var special  =  _this.option.special;
        var datagardConfig = {
            url:url,
            rownumbers:special&&special.rownumbers?special.rownumbers:false,
            singleSelect:true,
            collapsible:false,
            multiSort:false,
            loadMsg:"数据正在加载。。。",
            autoRowHeight:true,
            pagination:special&&special.pagination !=undefined ?special.pagination:true, 
            pageSize: special&&special.pageSize  !=undefined ?special.pageSize:10,//每页显示的记录条数，默认为10 
            pageList: pageList,//可以设置每页记录条数的列表
            method:'post',
            remoteSort:true,
            fitColumns:special&&special.fitColumns  !=undefined?special.fitColumns:true,
            frozenColumns:[columnArr.frozenColumns],
            columns:[columnArr.columns],
            remoteSort:true,
            queryParams:queryjson,
            onBeforeLoad:function(param){
                var search =  _this.getSearch();
                return param.search = JSON.stringify(search);
            },
            onLoadSuccess:function(result){
                _this.loadSuccess(obj,result,search);
            }
        };
        //以buffer view的形式展示数据
        if(special && special.view ){ 
            datagardConfig.view = scrollview;
            datagardConfig.autoRowHeight =false;
            datagardConfig.pagination = false;
        }
        console.log(datagardConfig);
        if(gardconf !=undefined){
            var datagardConfig = {
                url:url,
                //rownumbers:true,
                singleSelect:true,
                collapsible:false,
                multiSort:false,
                loadMsg:"数据正在加载。。。",
                frozenColumns:[columnArr.frozenColumns],
                columns:[columnArr.columns],
                queryParams:queryjson,
                onBeforeLoad:function(param){
                    var search =  _this.getSearch();
                    return param.search = JSON.stringify(search);
                },
            };
            datagardConfig = $.extend(datagardConfig,gardconf);
        }
        var otherconf = _this.option.otherconf;
        if(otherconf !=undefined ){
            if(otherconf.checkbox){
                var one ={};
                one.field     = 'ck';
                one.checkbox  = true;
                columnArr.columns.insert(0, one);
                datagardConfig.columns = [columnArr.columns];
                datagardConfig.singleSelect = false;
            }
        }
        //合并排序
        var sort  = _this.getFirstSort();
        if(sort){
            datagardConfig = $.extend(datagardConfig,sort);
        }
        obj.datagrid(datagardConfig);
        //设置分页
        var p = obj.datagrid('getPager'); 
        $(p).pagination({ 
          beforePageText: '',//页数文本框前显示的汉字 
          afterPageText: '/ {pages} 页', 
          displayMsg: '共 {total} 条数据'
        }); 
    },
    //生成列
    getcolumn:function(data){
        var columnArr ={};
        columnArr.frozenColumns = [];
        columnArr.columns  =[];
        //获取表头配置
        if(data && data.length >0 ){
            for (var j = 0; j < data.length; j++) {
                dataKey = data[j].key;
                //不隐藏
                if(!parseInt(data[j].hide) ){
                    var oneHeader = {};
                    oneHeader.field = dataKey.toLocaleLowerCase();
                    oneHeader.title = data[j].name;
                    //处理宽度
                    if( data[j].width != undefined ){
                    oneHeader.width = data[j].width;	
                    }else{
                            oneHeader.width ='100';
                    }
                    //居中 居左 居右？
                    if( data[j].align != undefined ){
                    oneHeader.align = data[j].align;	
                    }else{
                            oneHeader.align='left';
                    }
                    //处理说明
                    if(data[j].explain !=undefined && data[j].explain  !='' ){
                        oneHeader.title = data[j].name +'&nbsp;<a data-toggle="tooltip" title="'+data[j].explain+'" class="showinfo glyphicon glyphicon-question-sign"></a>';
                        //如果名称相同，则去掉名称 提示框
                        if(data[j].explain == data[j].name){
                                oneHeader.title = data[j].name;
                        }
                    }
                    //处理函数
                    if( data[j].percent ==1){
                            oneHeader.formatter = formatPrice;
                    }
                    //处理列
                    if( data[j].formatter !=undefined){
                            oneHeader.formatter = data[j].formatter;
                    }
                    //处理颜色
                    if(data[j].styler !=undefined){
                            oneHeader.styler = data[j].styler;
                    }
                    //处理排序
                    if(data[j].sort !=undefined){
                            oneHeader.sortable = true;	
                    }
                    //处理图片配置
                    var imglinkObj = {};
                    if(data[j].img_link !='-' &&  data[j].img_link !='' && data[j].img_link !=undefined){
                            imglinkObj.width = data[j].width;
                            imglinkObj.title = data[j].name+"<i>[图片]</i>";
                            imglinkObj.field = dataKey+"_img";
                    }
                    //处理是否固定
                    if( parseInt(data[j].fixed) ){
                        columnArr.frozenColumns.push(oneHeader);
                        if(!isEmptyObject(imglinkObj)){
                                columnArr.frozenColumns.push(imglinkObj);
                        }
                    }else{
                        columnArr.columns.push(oneHeader);
                        if(!isEmptyObject(imglinkObj)){
                                columnArr.columns.push(imglinkObj);
                        }
                    }
                }

            };
        }
        return columnArr;
    },
    reloadData:function(){
        var obj = $("#"+this.option.id);
        obj.datagrid('reload');
    }
    
};
Array.prototype.insert = function (index, item) {  
  this.splice(index, 0, item);  
};  