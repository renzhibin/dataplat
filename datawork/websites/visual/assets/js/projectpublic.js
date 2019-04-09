var id ;
var data_id = 0;
var project  = [];
var categories = [];
var localConfigHql= {};//本地临时hql 
var localtempcount= -1; //新建项目时 提醒  
var commentInfo = [];

function Base64() {

    // private property
    _keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

    // public method for encoding
    this.encode = function (input) {
        var output = "";
        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
        var i = 0;
        input = _utf8_encode(input);
        while (i < input.length) {
            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);
            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;
            if (isNaN(chr2)) {
                enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
                enc4 = 64;
            }
            output = output +
            _keyStr.charAt(enc1) + _keyStr.charAt(enc2) +
            _keyStr.charAt(enc3) + _keyStr.charAt(enc4);
        }
        return output;
    }

    // public method for decoding
    this.decode = function (input) {
        var output = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;
        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
        while (i < input.length) {
            enc1 = _keyStr.indexOf(input.charAt(i++));
            enc2 = _keyStr.indexOf(input.charAt(i++));
            enc3 = _keyStr.indexOf(input.charAt(i++));
            enc4 = _keyStr.indexOf(input.charAt(i++));
            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;
            output = output + String.fromCharCode(chr1);
            if (enc3 != 64) {
                output = output + String.fromCharCode(chr2);
            }
            if (enc4 != 64) {
                output = output + String.fromCharCode(chr3);
            }
        }
        output = _utf8_decode(output);
        return output;
    }

    // private method for UTF-8 encoding
    _utf8_encode = function (string) {
        string = string.replace(/\r\n/g,"\n");
        var utftext = "";
        for (var n = 0; n < string.length; n++) {
            var c = string.charCodeAt(n);
            if (c < 128) {
                utftext += String.fromCharCode(c);
            } else if((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            } else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }

        }
        return utftext;
    }

    // private method for UTF-8 decoding
    _utf8_decode = function (utftext) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;
        while ( i < utftext.length ) {
            c = utftext.charCodeAt(i);
            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            } else if((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i+1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            } else {
                c2 = utftext.charCodeAt(i+1);
                c3 = utftext.charCodeAt(i+2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }
        }
        return string;
    }
}

//获取最大值
function changeData(){
    project  = [];
    categories = [];
    if(projectInfo != undefined){
        for(var  i  in  projectInfo){

            var id = i;
            var idArr =  id.split("@");
            var idInfo = idArr[0].split("_");
            var tmeObj = {};
            if(idInfo[0] =='project'){
                tmeObj.id = id;
                tmeObj.content =eval( "("+ projectInfo[i] + ")");
                project.push(tmeObj);
            }else if( idInfo[0] =='categories'){
                tmeObj.id = id;
                tmeObj.content =eval( "("+ projectInfo[i] + ")");
                tmeObj.children =[];
                for (var j in projectInfo) {
                    var  groupsId = j;
                    var groupsInfo = groupsId.split("@");
                    if(id == groupsInfo[1]){
                        var groupid = groupsInfo[0].split("_")[1];
                        if(parseInt(data_id) < parseInt(groupid) ){
                            data_id = groupid ;
                        }
                        var one  = {};
                        one.id = groupsId;
                        one.content = eval( "("+ projectInfo[j] + ")");
                        tmeObj.children.push(one);
                    }
                };
                categories.push(tmeObj);
            }
            if(parseInt(data_id) < parseInt(idInfo[1]) ){
                data_id = idInfo[1];
            }
        }

    }
}
changeData();
//获取数据
function getData(key){
    var value = projectInfo[key];
    if(value){
        return eval( "(" + value+ ")" );
    }else{
        return false;
    }
}
//获取表格数据
function getTableInfo(table){
    var obj =[];
    var keylist =[];
    table.find("tr").eq(0).children('td').each(function(){
        var key = $(this).attr('data-key');
        if(key !=undefined){
            key = (key == '是否校验') ? 'ischecktables' : key;
            key = (key == '起始时间/终止时间') ? 'time_depend' : key;
            keylist.push(key);
        }
    });
    table.find("tr:not(:first)").each(function(){
        var one = {};
        $(this).find('td').each(function(index){
             var _this = $(this);
            switch( keylist[index]){
                case 'ischecktables':
                    one[keylist[index]] = !!_this.find('input[type="checkbox"]').is(':checked') ? 1:-1;
                    break;
                case 'type':
                    //如果为下拉框 获取下拉框的值 
                    if( _this.find('select.field_type').length >0 ){
                        one[keylist[index]] = _this.find('select.field_type').val();
                    }else{

                        one[keylist[index]] = _this.attr('data-key');
                    }
                    
                    break;
                default:
                    one[keylist[index]] = _this.text();
                    break;
            }
        });
        obj.push(one);
    });
    return obj;
}
//数据检测
function checkData(){
    var status =0;
    $('.rightContent').find('td[data-type=cn_name]').each(function(){
        if($(this).html() ==''){
            $(this).css({'-webkit-box-shadow':'1px 1px 5px #a94442'});
            status =1;
        }
    });
    /*  explain 不是必填项
    $('.rightContent').find('td[data-type=explain]').each(function(){
        if($(this).html() ==''){
            $(this).css({'-webkit-box-shadow':'1px 1px 5px #a94442'});
            status =1;
        }
    });*/
    return status;
}

//数据检测table 获取
function ckgetTableData(tables){
    var status =-1;
    var tablearr = [], table = {};
    tables.find('tr td').removeAttr('style');
    var len = tables.find('tr').length;
    if( len == 2){
        var obj = {"status":-1,"tablearr":[]};
        return obj;
    }

    tables.find('tr:not(:first)').each(function(index){
        if(index != (len-1)){
            var name = $(this).find('td[data-type="name"]').text();
            var cn_name = $(this).find('td[data-type="cn_name"]').text();
            var par = $(this).find('td[data-type="par"]').text();
            var time_depend  = $(this).find('td[data-type="time_depend"]').text();
            var ischecktables = $(this).find('input[name="ischecktables"]').is(":checked")?1:-1;


            if( name && name != '' && cn_name && cn_name != '' ){
                table = {"name":name, "cn_name":cn_name,"par":par,"time_depend":time_depend,"ischecktables":ischecktables};
                tablearr.push(table);
            } else if (name == '' && cn_name == '' && par ==''){
                console.log('status');
            } else {
                if(name == ''){
                    $(this).find('td[data-type="name"]').css({'-webkit-box-shadow':'1px 1px 5px #a94442'});
                    status = 1;
                }

                if(cn_name==''){
                    $(this).find('td[data-type="cn_name"]').css({'-webkit-box-shadow':'1px 1px 5px #a94442'});
                    status = 1;
                }
             }
        }

    });
    var obj = {"status":status,"tablearr":tablearr};
    return obj;
}

//
function getHqllist(){
    changeData();
    var interText = doT.template($("#hqlselecttmpl").text());
    var tempoperate = {} , tempcategories = JSON.parse(JSON.stringify(categories));

    if(typeof(projectInfo) !='undefined'&&projectInfo['operate']){
        var projectoperate = JSON.parse(projectInfo['operate']);
        if(projectoperate&& projectoperate['run_instance'] && projectoperate['run_instance']['group']){
             for(var p in projectoperate['run_instance']['group']){
                var key = projectoperate['run_instance']['group'][p].name;
                     tmpkey = key.split(".").join("_");
                    tempoperate[tmpkey] = 1;
            }


       }
        //for循环 categories
        for(var p in tempcategories){
            var childrens = tempcategories[p].children;
            var parentkey = tempcategories[p].content.name;
            for(var p1 in childrens){
                var childernkey = parentkey+'_'+childrens[p1].content.name;
                if(tempoperate[childernkey]){
                    childrens[p1].content['isoperate'] = 1;
                } else {
                    childrens[p1].content['isoperate'] = 0;
                }
            }
            tempcategories[p].children = childrens;
        }
    }
    console.log(tempcategories);
    $(".hqllist").html(interText(tempcategories));
}
//焦点功能
function focusData(obj){
    $('body').on('blur',obj,function(){
       if($(this).text() !=''){
            $(this).css({'-webkit-box-shadow':'1px 1px 5px #3c763d'});
        }else{
            $(this).css({'-webkit-box-shadow':'1px 1px 5px #a94442'});
        } 
    });
}
//保存操作配置
function gerOperate(){
    //增加操作配置项
    var run = {};
    run.creater = $('.runConfig').find('.author').text();
    run.date_s  = $('.runConfig').find('.start').val();
    run.date_e  = $('.runConfig').find('.end').val();
    run.run_instance = {};
    run.run_instance.group = [];
    $('.runConfig').find('.runList').each(function(){
        var obj = $(this);
        $(this).find('td').eq(1).find('ul').find('li').each(function(){
            if($(this).find('input').is(':checked')){
                var  oneSql ={};
                var  str  = obj.find('td').eq(0).attr('name');
                str += "." + $(this).attr('name');
                oneSql.name = str;
                run.run_instance.group.push(oneSql);
            }
        });
    });
    return run;
}
//比较两个对象返回有值的对象
function objectMerge(arr1,arr2){
    keyMap =[];
    for(var i =0; i< arr1.length; i++){
        keyMap.push(arr1[i].name);
    }
    for(var i =0; i< arr2.length; i++){
        keyMap.push(arr2[i].name);
    }
    var keyArr =[];
    keyMap = $.unique(keyMap);
    newArr = $.merge(arr1,arr2);
    var oneArr =[];
    for(var j =0; j< keyMap.length; j++){
        for(var x =0; x< newArr.length; x++){
            if(keyMap[j] == newArr[x].name){
                if($.inArray(newArr[x].name,oneArr) <0 ){
                    keyArr.push(newArr[x]);
                    oneArr.push(newArr[x].name);
                }
            }
        }
    }
    return keyArr;
}
//取值
function getMergeInfo(source,type){
    for(var i=0; i< source.length; i++){
        for(var j=0;j< commentInfo[type].length; j++){
            if(source[i].name == commentInfo[type][j].name){
                source[i] = $.extend(source[i], commentInfo[type][j]);
            }
        }
    }
    return source;
}
//保存解析信息
function saveAnalyse(){
    commentInfo.metrics =getTableInfo($('.rightContent').find('.metricsContent').find('table.metrics'));
    if(commentInfo.dimensions !=undefined){
        if($('.rightContent').find('.metricsContent').find('table.dimensions').length >0){
            var dim = getTableInfo($('.rightContent').find('.metricsContent').find('table.dimensions'));
            commentInfo.dimensions = objectMerge(commentInfo.dimensions,dim);
        }
    }else{
        commentInfo.dimensions =getTableInfo($('.rightContent').find('.metricsContent').find('table.dimensions'));
    }
    if(commentInfo.tables !=undefined){
        if($('.rightContent').find('.metricsContent').find('table.tables').length >0){
            var tb= getTableInfo($('.rightContent').find('.metricsContent').find('table.tables'));
            commentInfo.tables = objectMerge(commentInfo.tables,tb);

            //console.log(commentInfo.tables);
        }
    }else{
        commentInfo.tables =getTableInfo($('.rightContent').find('.metricsContent').find('table.tables'));
    }

    commentInfo.dim_sets =getTableInfo($('.rightContent').find('.metricsContent').find('table.dim_sets'));
}

//获取偏移量数据
function getOffsetVal(key){
    for(var i=0; i< schedule_interval_offset.length; i++){
        if(schedule_interval_offset[i].key == key){
            return schedule_interval_offset[i].offset;
        }
    }
}
$(function(){

    //数据配置 name 验证
/*    $('.container').on('blur','input[name="name"]',function(){
        //$(this).focus().next("b").html('111');
        var val = $(this).val(), reg = /^\w+$/;
        *//*if(val.length > 20){
            $(this).focus().next("b").html('*字符长度不能超过20个');
            return false;
        }*//*
        if(!reg.test(val) && val!=""){
            //$(this).focus().next("b").html('*英文名称不能输入中文和特殊字符');
        } else{
            $(this).next("b").html('*');
        }
    });*/

/*    window.onload=function(){
        $('.saveInfo').attr("disabled",true);
    }*/

/*    $('.container').on('focus','input[name="name"]',function(){
        $('.saveInfo').attr("disabled",true);
    });*/

    //检查项目名称长度
    $('.container').on('blur','input[name="name"]',function(){
        var project_name = $(this).val();
        //var type = $(this).attr('data-type');
        var type = $("input[name=type]").val();
        var parentTag = $(this).closest('tbody');
        if(type=='project'){
            var hql_type = parentTag.find('select[name=hql_type]').val();
        }else{
            var hql_type = JSON.parse(projectInfo.project_1).hql_type;
            //type = str.parseJSON(projectInfo.project_1);
        }

        var store_type = $('select[name=storetype]').val();
        var parentTag = $(this).closest('tbody');
        var type = 'project';
        if($("input[name=type]").val()=='project'){
            type = 'project';
        }else if($("input[name=type]").val()=='categories'){
            type = 'group';
        }else if($("input[name=type]").val()=='groups'){
            type = 'hql';
        }
        var checkParams = {"item_val":project_name,"hql_type":hql_type,"store_type":store_type,"item_type":type};
        $.get('/project/checkprojectdata',checkParams,function(data){
                if(data.status=='0'){
                    $("input[name=name]").next("b").html("*");
                    //$('.saveInfo').attr("disabled",false);
                }else{
                    $("input[name=name]").next("b").html(data.msg);
                    //$('.saveInfo').attr("disabled",true);
                }

            }, 'json');

    });


    focusData($('td[data-type=cn_name]'));
    //explain 不是必填项目
    //focusData($('td[data-type=explain]'));
    //初使化数据
    if(project.length >0 ){
        var html = "<li class='list-group-item project' data_id="+project[0].id+"  style='cursor:pointer' data-type='project'><span> "+project[0].content.cn_name;
        html += " </span>";
        html +="<b class='closeBtn'>X</b>";
        html +="<button class='btn btn-primary btn-xs addCategories pull-right'>添加分类</button>";
        html += "<ul class='list-group' data-type='categories' style='margin:7px 0px 0px 0px'>";
        if(categories.length >0){
            for(var i =0; i < categories.length; i++){
                html += "<li class='list-group-item categories'  category_name='"+categories[i].content.name+"'   data-type='categories' data_id='"+categories[i].id+"'  style='cursor:pointer'> <span>"+categories[i].content.cn_name;
                html += "</span>";
                html +="<button class='btn btn-primary btn-xs addHql pull-right' style='z-index:1000; position:relative'>添加Hql</button>";
                html +="<b class='closeBtn'>X</b>";
                html += "   <ul class='list-group' data-type='groups' style='margin:7px 0px 0px 0px'>";
                if(categories[i].children.length >0){
                    var children = categories[i].children;
                    for(var j=0; j< children.length; j++){
                        html += "<li class='list-group-item'  category_name='"+categories[i].content.name+"'  parent_id='"+categories[i].id +"'";
                        html += " data_id='"+children[j].id+"' groupname='"+children[j].content.name+"'  style='cursor:pointer'";
                        html += "data-type='groups'><span> "+children[j].content.cn_name;
                        html += " </span>";
                        html +="<b class='closeBtn'>X</b>";
                        html += " </li> ";
                    }
                }
                html +="</ul>";
                html +="</li>";
            }
        }
        html +="</ul>";
        html +='</li>';
        $('.listmap_ul').append(html);
    }
    $('.nav').children('li').click(function(){
        if($('.container').find('.alert-danger').length ==0){
            if(window.confirm('放弃正在编辑的项目吗？')){
                localStorage.clear();
            }else{
                return false;
            }
        }

    });
    //添加项目
    $('.addProject').on('click',function(){
        if(project.length >0 ){
            alert('项目只能添加一个');
            return;
        }
        if(projectInfo !=undefined){
            for(var i  in projectInfo){
                var id = i
                var idInfo = id.split("_");
                if(idInfo[0] =='project'){
                    alert('项目只能添加一个');
                    return;
                }
            }
        }
        $('.rightContent').html($('.tablehtml').html());
        $('.rightContent').find('.cn_name').html('项目中文名');
        $('.rightContent').find('input[name=type]').val('project');
        $('.rightContent').find('.addtitle').text('添加项目');
        $('.rightContent').find('.storetypebox').show();
        $(this).hide();
    });

    $('body').on('change','select.schedule_interval',function(){
         var  value = $(this).val();
         var  offsetVal = getOffsetVal(value);
         $('[name=schedule_interval_offset]').val(offsetVal);
    });
    //保存信息
    $('body').on('click','.saveInfo',function(){
        var project_name =  $('input[name=name]').val();
        var hql_type = $('select[name=hql_type]').val();
        var store_type = $('select[name=storetype]').val();
        var parentTag = $(this).closest('tbody');

        var type = 'project';
        if($("input[name=type]").val()=='project'){
            type = 'project';
        }else if($("input[name=type]").val()=='categories'){
            type = 'group';
        }else if($("input[name=type]").val()=='groups'){
            type = 'hql';
        }
        if(type=='project'){
            var hql_type = $('select[name=hql_type]').val();
        }else{
            var hql_type = JSON.parse(projectInfo.project_1).hql_type;
        }
        var tempflag = 0;
        //保存信息校验
        $.ajax({
            url : "/project/checkprojectdata",
            method : "get",
            data: {"item_val":project_name,"hql_type":hql_type,"store_type":store_type,"item_type":type},
            async: false,
            success : function(data) {
                var data = JSON.parse(data);
                if(data.status==0){
                    $("input[name=name]").next("b").html("*");
                    //$('.saveInfo').attr("disabled",false);
                    tempflag = 0;
                }else{
                    $("input[name=name]").next("b").html(data.msg);
                    //$('.saveInfo').attr("disabled",true);
                    tempflag = 1;
                }

            },
            error: function() {

                connectionError();
            }
        });

        console.log(tempflag+"flag");
        if(tempflag >0){
            return false;
        }



        var type = $(this).next().val();
        data_id ++;
        switch(type){
            case 'project':
                var project ={};
                project.cn_name = $('input[name=cn_name]').val();
                project.explain = $('input[name=explain]').val();
                project.name = $('input[name=name]').val();
                project.hql_type = $('select[name="hql_type"]').val();
                project.storetype = $('select[name="storetype"]').val();
                //$('.saveInfo').attr("disabled",true);

                if(!project.cn_name){
                    alert('请填写中文名');
                    return;
                }
                /* explain 不是必填项
                if(!project.explain){
                    alert('请填写explain');
                    return;
                }*/

                if(!project.name){
                    alert('请填写项目英文名');
                    return;
                }

                if(project.hql_type == '2'){
                    project.storetype = "-1";
                }
                var html = "<li class='list-group-item project' data-type='project' data_id='"+type+"_"+data_id+"' style='cursor:pointer'><span> "+project.cn_name;
                html += " </span><button class='btn btn-primary btn-xs addCategories pull-right'>添加分类</button>";
                html +="<b class='closeBtn'>X</b>";
                html += "<ul class='list-group' data-type='categories' style='margin:7px 0px 0px 0px'></ul>";
                html +='</li>';
                $('.listmap_ul').append(html);

                $('.rightContent').html('');
                $('.saveConfig').removeAttr('disabled');
                if(JSON.stringify(projectInfo) == '{}'){
                    $('.rightContent').html('');
                    $('.rightContent').html($('.tablehtml').html());
                    $('.rightContent').find('.cn_name').html('分类中文名');
                    $('.rightContent').find('input[name=type]').val('categories');
                    $('.rightContent').find('input[name=name]').removeAttr('disabled');
                    $('.rightContent').find('.addtitle').text('添加分类');
                }

                project.categories = [];
                projectInfo[type+"_"+data_id] = JSON.stringify(project);
                break;
            case 'categories':
                var categories ={};
                categories.cn_name = $('input[name=cn_name]').val();
                categories.explain = $('input[name=explain]').val();
                categories.name = $('input[name=name]').val();
                var sort = $(this).next().attr('sort');
                var html ="";
                var html = "<li class='list-group-item categories' data-type='categories' category_name='"+categories.name+"'  data_id='"+type+"_"+data_id+"'  style='cursor:pointer'> <span>"+categories.cn_name;
                html += "</span> <button class='btn btn-primary btn-xs addHql pull-right'>添加Hql</button>";
                html +="<b class='closeBtn'>X</b>";
                html += "   <ul class='list-group' data-type='groups' style='margin:7px 0px 0px 0px'></ul>";
                html +="</li>";
                var $html = $(html);
                if(!categories.cn_name){
                    alert('请填写中文名');
                    return;
                }
                // explain 不是必填项
                /*if(!categories.explain){
                    alert('请填写explain');
                    return;
                }*/
                if(!categories.name){
                    alert('请填写英文名');
                    return;
                }
                $('ul[data-type=categories]').append($html);
                projectInfo[type+"_"+data_id] = JSON.stringify(categories);
                $('.rightContent').html('');
                $('.saveConfig').removeAttr('disabled');
                break;
            case 'groups':
                var groups ={};
                //groups.hql = $('.rightContent').find('.code').val();
                groups.hql = editor1.getValue();
                if(groups.hql ==''){
                    alert('请先填写hql并解析hql');
                    return false;
                }
                 //hdfs 还是数据库
                groups.name = $('.rightContent').find('input[name=name]').val();
                groups.cn_name = $('.rightContent').find('input[name=cn_name]').val();
                groups.explain = $('.rightContent').find('input[name=explain]').val();
                groups.attach = $('.rightContent').find('textarea.attach').val();
                groups.custom_cdate = $('.rightContent').find('input.custom_cdate').is(':checked')?1:0;
                groups.custom_start = $('.rightContent').find('input.custom_start').val();
                groups.custom_end = $('.rightContent').find('input.custom_end').val();
                groups.custom_type = $('#custome_cdate_type').val();
                groups.custom_single = $('.custom_single').val();
                groups.schedule_interval = $('.rightContent').find('select[name="schedule_interval"]').val();
                groups.schedule_interval_offset = $('.rightContent').find('input[name="schedule_interval_offset"]').val();
                groups.hive_queue = $('.rightContent').find('select[name="hive_queue"]').val();
                var project = JSON.parse(projectInfo.project_1);

                var latest_end_time = $('.rightContent').find('input[name="latest_end_time"]').val();
                var radio_val = $('.rightContent').find('input[name="alarm_type"]:checked').val();
                var alarm_users = $('.rightContent').find('input[name="alarm_users"]').val();
                if(radio_val != 'undefined') {
                    groups.alarm_type = radio_val;
                }
                if(alarm_users != '') {
                    groups.alarm_users = alarm_users.toLocaleLowerCase();
                }
                if(latest_end_time !=''){
                    var end_time;
                    end_time = latest_end_time.replace(/day|hour|minute|\)|\(/gi, '');
                    if (!/^(20|21|22|23|[0-1]\d):[0-5]\d$/.test(end_time)) {
                        if (end_time < 0 || end_time > 60) {
                            alert('报警时间格式错误');
                            return ;
                        } else {
                            groups.latest_end_time = latest_end_time;
                        }
                    }
                    groups.latest_end_time = latest_end_time;
                }

                groups.hql_type = (project['hql_type'])?project.hql_type:1;
                if(groups.cn_name ==''){
                    alert('请填写中文名');
                    return;
                }
                if(groups.schedule_interval_offset ==''){
                    alert('调度不能为空');
                    return;
                }
                if(groups.name ==''){
                    alert('请填写英文名');
                    return;
                }
                if(groups.custom_cdate == 1 && groups.custom_type == 'range' && (groups.custom_start == '' || groups.custom_end == '')){
                    alert('起始终止时间不能为空');
                    return false;
                }

                if(groups.custom_cdate == 1 && groups.custom_type == 'single' && (groups.custom_single == '')){
                    alert('时间不能为空');
                    return false;
                }

                if(localConfigHql.hql && localConfigHql.hql != groups.hql){
                    alert('您的hql已经发生改变，请您重新hql解析！');
                    $(".metricsBox").html('');
                    $(this).attr('disabled',true);
                    return false;
                }

                if( groups.hql.indexOf('$DATE') <0 ){
                    if( window.confirm('亲,没有用$DATE函数哦,你这样有可能每天跑的数都是一样的哦,亲，你确定吗') ){
                    }else{
                        return false;
                    }
                }

                if(groups.hql_type == "1") {
                    groups.metrics = getTableInfo($('.rightContent').find('table.metrics'));
                    groups.dim_sets = getTableInfo($('.rightContent').find('table.dim_sets'));
                    groups.dimensions = getTableInfo($('.rightContent').find('table.dimensions'));
                    groups.tables = getTableInfo($('.rightContent').find('table.tables'));

                    if( groups.metrics.length == 0 && groups.dimensions.length == 0 && groups.custom_cdate == 0 ){
                        alert('请您先hql解析');
                        return false;
                    }

                    if(groups.custom_cdate == 1 && groups.metrics.length == 0 ){
                        alert('请您先hql解析');
                        return false;
                    }
                    if(groups.custom_cdate == 0 && (groups.metrics.length == 0 || groups.dimensions.length == 0)){
                        alert('您hql解析的纬度、指标不能为空,请您重新解析');
                        return false;
                    }

                    var status = checkData();
                    if(status){
                        alert('您填写的信息不全!');
                        return;
                    }
                    saveAnalyse();

                } else {
                    groups.metrics = [];
                    groups.dim_sets = [];
                    groups.dimensions = [];
                    $tables = $('.rightContent').find('table.tables');
                    var obj = ckgetTableData($tables);
                    if(obj.status > 0) {
                        alert('您填写信息不全');
                        return false;
                    }
                    groups.tables = obj.tablearr;

                }

                var category_name =$('.rightContent').find('input[name=name]').attr('category_name');
                var parent_id = $(this).next().attr('parent_id');
                var html ="";
                var html = "<li class='list-group-item' add_key=1 category_name='"+category_name+"' parent_id='"+parent_id+"' data_id='"+type+"_"+data_id+"@"+parent_id+"'  style='cursor:pointer' data-type='groups'><span> "+groups.cn_name;
                html += " </span>";
                html +="<b class='closeBtn'>X</b>";
                html +="</li> ";
                $('li[data_id='+parent_id+']').find('ul').append(html);
                projectInfo[type+"_"+data_id +"@"+parent_id] = JSON.stringify(groups);
                getHqllist();
                var categories = $(this).next().attr('parent_id');
                var project_conf=projectInfo['project_1'],cate_conf = projectInfo[categories];
                //保存hql信息
                $.ajax({
                    url : "/project/saveHql",
                    method : "POST",
                    data: {'project_conf':project_conf,'cate_conf':cate_conf,"project_name":project.name,"category_name":category_name,'hql_name':groups.name,"group":JSON.stringify(groups)},
                    async: false,
                    success : function(data) {
                        var data = JSON.parse(data);
                        if(data.status==0){
                            tempflag = 0;
                        }else{
                            alert(data.msg);
                            tempflag = 1;
                        }
                    },
                    error: function() {
                        connectionError();
                    }
                });
                if(tempflag >0){
                    return false;
                }
                $('.rightContent').html('');
                break;
        }

    });
    //编辑信息
    $('body').on('click','.editInfo',function(){
        if (isCoreProject == 0) {
            saveEditInfo(this);
            return;
        }
        var thiss = this;
        $.messager.confirm('提示', '该项目是核心项目确认是否要进行修改？', function(r) {
            if (r) {
                saveEditInfo(thiss);
            }
        });
        function saveEditInfo (thiss) {
            var type = $(thiss).next().val();
            var key = $(thiss).next().attr('data_id');

            switch(type){
                case 'project':
                    var project ={};
                    project.cn_name = $('input[name=cn_name]').val();
                    project.explain = $('input[name=explain]').val();
                    project.name = $('input[name=name]').val();
                    project.storetype = $('select[name="storetype"]').val();
                    project.hql_type = $('select[name="hql_type"]').val();
                    project.authuser = $('input[name="authuser"]').val();
                    if( typeof config =='undefined'){
                        project.authtype =  1;
                    }else{
                        project.authtype =  parseInt(config.run.authtype);
                    }

                    if(!project.cn_name){
                        alert('请填写中文名');
                        return;
                    }

                    // if(!project.authuser){
                    //     alert('请填写项目操作人');
                    //     return;
                    // }
                    if(!project.name){
                        alert('请填写英文名');
                        return;
                    }

                    if(project.hql_type == '2'){
                        project.storetype = "-1";
                    }

                    $('li[data_id='+key+']').children('span').html(project.cn_name);
                    projectInfo[key] = JSON.stringify(project);
                    $('.rightContent').html('');
                    break;
                case 'categories':
                    var categories ={};
                    categories.cn_name = $('input[name=cn_name]').val();
                    categories.explain = $('input[name=explain]').val();
                    categories.name = $('input[name=name]').val();
                    if(!categories.cn_name){
                        alert('请填写中文名');
                        return;
                    }

                    if(!categories.name){
                        alert('请填写英文名');
                        return;
                    }
                    $('li[data_id='+key+']').children('span').html(categories.cn_name);
                    $('li[data_id='+key+']').attr('category_name',categories.name);
                    projectInfo[key] = JSON.stringify(categories);
                    $('.rightContent').html('');
                    break;
                case 'groups':
                    var groups ={},$rightContent = $('.rightContent');
                    //groups.hql = $('.rightContent').find('.code').val();
                    groups.hql = editor1.getValue();
                    if(groups.hql ==''){
                        alert('请先填写hql并解析hql');
                        return false;
                    }

                    if(localConfigHql.hql && localConfigHql.hql != groups.hql){
                        alert('您的hql已经发生改变，请您重新hql解析！');
                        $(".metricsBox").html('');
                        $(thiss).attr('disabled',true);
                        return false;
                    }

                    //hdfs 还是数据库
                    groups.name = $rightContent.find('input[name=name]').val();
                    groups.cn_name = $rightContent.find('input[name=cn_name]').val();
                    groups.explain = $rightContent.find('input[name=explain]').val();
                    groups.attach = $rightContent.find('textarea.attach').val();//参数配置
                    groups.custom_cdate = $rightContent.find('input.custom_cdate').is(':checked')?1:0;
                    groups.custom_start = $rightContent.find('input.custom_start').val();
                    groups.custom_end = $rightContent.find('input.custom_end').val();
                    groups.custom_type = $('#custome_cdate_type').val();
                    groups.custom_single = $('.custom_single').val();
                    groups.schedule_interval = $rightContent.find('select[name="schedule_interval"]').val();
                    groups.hive_queue = $('.rightContent').find('select[name="hive_queue"]').val();
                    groups.run_times = $rightContent.find('input[name=run_times]').val();//回溯次数

                    var schedule_interval_offset =  $rightContent.find('input[name="schedule_interval_offset"]').val();
                    var latest_end_time = $rightContent.find('input[name="latest_end_time"]').val();
                    var radio_val = $rightContent.find('input[name="alarm_type"]:checked').val();
                    var alarm_users = $rightContent.find('input[name="alarm_users"]').val();
                    if(schedule_interval_offset !=''){
                        groups.schedule_interval_offset = schedule_interval_offset;
                    }
                    if(radio_val != 'undefined') {
                        groups.alarm_type = radio_val;
                    }
                    if(alarm_users != '') {
                        groups.alarm_users = alarm_users.toLocaleLowerCase();
                    }
                    if(latest_end_time !=''){
                        var end_time;
                        end_time = latest_end_time.replace(/day|hour|minute|\)|\(/gi, '');
                        if (!/^(20|21|22|23|[0-1]\d):[0-5]\d$/.test(end_time)) {
                            if (end_time < 0 || end_time > 60) {
                                alert('报警时间格式错误');
                                return ;
                            } else {
                                groups.latest_end_time = latest_end_time;
                            }
                        }
                        groups.latest_end_time = latest_end_time;
                    }

                    var project = JSON.parse(projectInfo.project_1);
                    groups.hql_type = (project['hql_type'])?project.hql_type:1;
                    if(groups.cn_name ==''){
                        alert('请填写中文名');
                        return;
                    }
                    if(groups.name ==''){
                        alert('请填写英文名');
                        return;
                    }

                    if(groups.custom_cdate == 1 && groups.custom_type == 'range' && (groups.custom_start == '' || groups.custom_end == '')){
                        alert('起始终止时间不能为空');
                        return false;
                    }

                    if(groups.custom_cdate == 1 && groups.custom_type == 'single' && (groups.custom_single == '')){
                        alert('时间不能为空');
                        return false;
                    }

                    if(groups.hql_type == "1") {
                        groups.metrics = getTableInfo($rightContent.find('table.metrics'));
                        groups.dim_sets = getTableInfo($rightContent.find('table.dim_sets'));
                        groups.dimensions = getTableInfo($rightContent.find('table.dimensions'));
                        groups.tables = getTableInfo($rightContent.find('table.tables'));

                        if( groups.metrics.length == 0 && groups.dimensions.length == 0 && groups.custom_cdate == 0 ){
                            alert('请您先hql解析');
                            return false;
                        }

                        if(groups.custom_cdate == 1 && groups.metrics.length == 0 ){
                            alert('请您先hql解析');
                            return false;
                        }
                        if(groups.custom_cdate == 0 && (groups.metrics.length == 0 || groups.dimensions.length == 0)){
                            alert('您hql解析的纬度、指标不能为空,请您重新解析');
                            return false;
                        }

                        var status = checkData();
                        if(status){
                            alert('信息不全');
                            return;
                        }

                        saveAnalyse();

                    } else {
                        groups.metrics = [];
                        groups.dim_sets = [];
                        groups.dimensions = [];
                        $tables = $rightContent.find('table.tables');
                        var obj = ckgetTableData($tables);
                        if(obj.status > 0) {
                            alert('您填写信息不全');
                            return false;
                        }
                        groups.tables = obj.tablearr;
                        //console.log(groups.tables);

                    }

                    if( groups.hql.indexOf('$DATE') <0 ){
                        if( window.confirm('亲,没有用$DATE函数哦,你这样有可能每天跑的数都是一样的哦,亲，你确定吗') ){
                        }else{
                            return false;
                        }
                    }
                    var parent_id = $(thiss).next().attr('parent_id');
                    $('li[data_id="'+key+'"]').children('span').html(groups.cn_name);
                    projectInfo[key] = JSON.stringify(groups);
                    getHqllist();
                    var category_name =$rightContent.find('input[name=name]').attr('category_name');
                    var categories = $(thiss).next().attr('parent_id');
                    var project_conf=projectInfo['project_1'],cate_conf = projectInfo[categories];

                    $.ajax({
                        url : "/project/saveHql",
                        method : "POST",
                        data: {'project_conf':project_conf,'cate_conf':cate_conf,"project_name":project.name,"category_name":category_name,'hql_name':groups.name,"group":JSON.stringify(groups)},
                        async: false,
                        success : function(data) {
                            var data = JSON.parse(data);
                            if(data.status==0){
                                tempflag = 0;
                            }else{
                                alert(data.msg);
                                tempflag = 1;
                            }
                        },
                        error: function() {
                            connectionError();
                        }
                    });
                    if(tempflag >0){
                        return false;
                    }
                    $('.rightContent').html('');
                    break;
            }
        }
    });
    //添加分类
    $('body').on('click','.addCategories',function(event){

        //$('.saveInfo').attr("disabled",true);
        $('.rightContent').html('');
        $('.rightContent').html($('.tablehtml').html());
        $('.rightContent').find('.cn_name').html('分类中文名');
        $('.rightContent').find('input[name=type]').val('categories');
        $('.rightContent').find('input[name=name]').removeAttr('disabled');
        $('.rightContent').find('.addtitle').text('添加分类');


        localConfigHql = {};
    });
    //添加sql
    $('body').on('click','.addHql',function(){
        var  _parent = $(this).parent();
        $('.rightContent').html('');
        $('.rightContent').html($('.hqlHtml').html());
        $('.rightContent').find('input[name=type]').val('groups');
        $('.rightContent').find('.metricsBox').html('');
        $('.rightContent').find('input[name=name]').removeAttr('disabled');
        $('.rightContent').find('input[name=name]').attr('category_name',_parent.attr('category_name'));
        $('.rightContent').find('input[name=name]').attr('add_key',1);
        var parent = $(this).parent();
        $('.rightContent').find('input[name=type]').attr('parent_id',parent.attr('data_id'));
        $('.rightContent').find('.addtitle').text('添加hql');
        localConfigHql = {};
        var project_1 = JSON.parse(projectInfo.project_1);
        if(project_1['hql_type'] && project_1['hql_type'] == '2'){
            $('.rightContent .hqlInfo').find('.hdfsMsg').show();
        }
        $('.rightContent').find('select[name="schedule_interval"]').val("0");
        $('.rightContent').find('input[name="schedule_interval_offset"]').val(getOffsetVal(0));
        $('.rightContent').find('select').select2();

        // hql编辑初始化
        $('.rightContent').find('.editorcode').attr('id','editorcode');

            //初始化hql编辑器
          window.editor1 = ace.edit("editorcode");
          editor1.setTheme("ace/theme/tomorrow_night_eighties");
          editor1.session.setMode("ace/mode/sql");
          editor1.setAutoScrollEditorIntoView(true);
          editor1.setOption("minLines", 20);
          editor1.setOption("maxLines", 30); 
        

    });
    //hql解析
    $('body').on('click','.hqlAnalyse',function(){
        //var hql = $('.rightContent').find('.hqlInfo').find('.code').val();
        var hql = editor1.getValue();
        var project = JSON.parse(projectInfo.project_1);
        var hql_type = (project['hql_type']) ? project.hql_type : 1;
        if(!hql){
            alert('请填写hql');
            return false;
        }
        var type ='hql';
        if($('.rightContent').find('.astbox').is(":visible")){
            hql = $('.rightContent').find('.astbox').find('textarea[name=ast]').val();
            type ='ast';
        }
        var  thisObj = $(this);
        var _parent = $(this).closest('.hqlInfo');
        //判断当前hql解析 是编辑解析 还是添加解析 
        var keyObj = _parent.find('input[name=name]');
        var add_key =  _parent.find('input[name=name]').attr('add_key');
        if( add_key !=undefined && parseInt(add_key) >0  ){
            var typeStatus = 1;
        }else{
            var typeStatus = 0;
        }
        var attach = _parent.find('textarea.attach').val(), custom_cdate = _parent.find('input.custom_cdate').is(":checked") ? 1:0;
        hql_name = keyObj.val();
        category_name = keyObj.attr('category_name');
        var sendData ={
            'hql':hql,type:type,'hql_type':hql_type,
            'app_name':project.name,'hql_name':hql_name,
            'category_name':category_name,
            'attach':attach,'custom_cdate':custom_cdate};
        $('#contain').show();
        $('.hqlInfo .metricsBox').html('');
        thisObj.attr("disabled","disabled");
        // hql_type  写入数据库DB  2写入hqfs
        $.ajax({
            type: "POST",
            url: "/project/getGroups",
            data: sendData,
            dataType: "json",
            success: function(res){

                if(res ===null){
                    $('.astbox').show();
                    $('#contain').hide();
                    thisObj.attr("disabled",false);
                } else {
                    if(res.status ==0){
                        localtempcount++;
                        localConfigHql.hql = hql; 
                        //写入数据库
                        if(hql_type == '2'){
                            var interText = doT.template($("#interpolationtmpl2").text());
                            res = (res=='') ? {} : res;
                            //table 是否校验 默认为1 
                            if(res.data.tables){
                                for(var p in res.data.tables){
                                    if(!res.data.tables[p]['time_depend']){
                                        res.data.tables[p]['time_depend'] = '';
                                    }
                                }

                                for(var p in res.data.tables){
                                    if(!res.data.tables[p]['ischecktables']){
                                        res.data.tables[p]['ischecktables'] = 1;
                                    }
                                }
                            }

                            if(commentInfo.tables !=undefined && res.data.tables !==undefined){

                                var mergObj = getMergeInfo(res.data.tables,'tables');
                                if(mergObj.length >0){
                                    res.data.tables = mergObj;
                                }
                            }
                            //console.log(JSON.stringify(res.data));
                            $(".metricsBox").html(interText(res.data));
                            $('#contain').hide();
                            thisObj.attr("disabled",false);
                            $('.operation .editInfo,.operation .saveInfo').removeAttr('disabled'); 
                            return false;
                        }
                        if(commentInfo.metrics !=undefined ){
                            var mergObj = getMergeInfo(res.data.metrics,'metrics');
                            if(mergObj.length >0){
                                res.data.metrics = mergObj;
                            }
                        }

                        if(commentInfo.dimensions !=undefined ){

                            var mergObj = getMergeInfo(res.data.dimensions,'dimensions');
                            if(mergObj.length >0){
                                res.data.dimensions = mergObj;
                            }
                        }

                        if(commentInfo.tables !=undefined ){

                            var mergObj = getMergeInfo(res.data.tables,'tables');
                            if(mergObj.length >0){
                                res.data.tables = mergObj;
                            }
                        }

                        if(commentInfo.dim_sets !=undefined ){
                            var mergObj = getMergeInfo(res.data.dim_sets,'dim_sets');

                            if(mergObj.length >0){
                                res.data.dim_sets = mergObj;
                            }
                        }
                        
                        var tableslen = res.data.tables.length;
                        if(tableslen > 0) {
                            for(var p in res.data.tables){
                                if(!res.data.tables[p]['time_depend']){
                                    res.data.tables[p]['time_depend'] = '';
                                }
                            }

                            for(var p in res.data.tables){
                                res.data.tables[p]['ischecktables'] = 1; //默认选中依赖表
                            }
                        }
                        var interText = doT.template($("#interpolationtmpl").text());
                        res.data.typeStatus = typeStatus; 
                        res.data.field_type = field_type;  

                        $(".metricsBox").html(interText(res.data));
                        $(".metricsBox").find('input[name=metricsConfig]').val(JSON.stringify(res.data));
                        $('.operation .editInfo,.operation .saveInfo').removeAttr('disabled');
                        $('#contain').hide();
                    }else{
                        // alert('解析失败：'+res.msg);
                        $.messager.alert('警告', res.msg);
                        $('#contain').hide();
                        thisObj.attr("disabled",false);
                        return false;
                    }
                    $('.rightContent').find('select').select2();
                }

                $('#contain').hide();
                thisObj.attr("disabled",false);
               
            },
            error:function(re,error){
                thisObj.attr("disabled",false);
                console.log(1);
                console.log(error);
            }
        });

    });

    $('body').on('click','.btn-his-version',function(event){
        $status = $('#collapseVersion').attr('aria-expanded');

        if($status != 'true') {
            var project = GetQueryString('project');
            var groupname = GetQueryString('groupname');
            var category_name = GetQueryString('category_name');
            $.ajax({
                url : "/project/historyAppConfLog",
                method : "get",
                data: {"project": project,"groupname": groupname, "category_name": category_name, "num": 12},
                async: false,
                success : function(data) {
                    var data = JSON.parse(data);
                    if(data.status == 0){
                        var log_data = data.data;
                        var base64class = new Base64();

                        if(log_data.length > 0) {
                            var log_html = '<div class="container" style="padding: 0;">';
                            for(var chunk_index in log_data){
                                log_html += '<div class="row" style="padding: 0; margin: 3px;">';
                                var chunk_data = log_data[chunk_index];
                                for(var log_index in chunk_data){
                                    var log = chunk_data[log_index];
                                    log_html += '<div class="col-md-3" style="padding: 0px;"><button type="button" class="btn btn-default his-version" data-id="' + log['id'] + '" data-user="' + log['editor'] +'" data-time="' + log['updated_at'] + '" data-sql="' + base64class.encode(log['hql']) + '">' + log['editor'] + '<br>' + log['updated_at'] + '</button></div>';
                                }
                                log_html += '</div>'
                            }
                            log_html += '</div>'

                            $('#collapseVersion').find('.well').html(log_html);
                        }
                    }else{
                        alert('解析数据异常')
                    }
                },
                error: function() {
                    alert('请求数据异常')
                }
            });
        }
    });

    $('body').on('click','.his-version',function(event){
        var id = $(this).data('id');
        var user = $(this).data('user');
        var time = $(this).data('time');
        var sql = $(this).data('sql');
        var current = $('#his_version');
        var base64class = new Base64();

        current.find('.version_id').html('版本 ' + id);
        current.find('.version_user').html(user);
        current.find('.version_date').html(time);

        window.editorversion = ace.edit("editorcodeversion");
        editorversion.setTheme("ace/theme/tomorrow_night_eighties");
        editorversion.session.setMode("ace/mode/sql");
        editorversion.setAutoScrollEditorIntoView(true);
        editorversion.setOption("minLines", 20);
        editorversion.setOption("maxLines", 30);
        editorversion.setValue(base64class.decode(sql));

        $("#his_version").modal('show');
    });

    //编辑
    $('body').on('click','.list-group-item',function(event){
        typeStatus = 0;
        //判断是否是编辑页面
        if(id !=undefined){
            var  add_key = $(this).attr('add_key');
            if( add_key !=undefined && parseInt(add_key) >0 ){
                typeStatus = 1;
            }
        }else{
            typeStatus = 1;
        }
        var _this = $(this);
        //$('.easyui-validatebox').validatebox({ required:true});
        var  filter = event.target.tagName.toLowerCase();
        if( filter =='button' ||  filter=='b' ){
            return false ;
        }
        //$('.saveInfo').attr("disabled",false);
        $("input[name=name]").next("b").html("*");
        $('.saveConfig').attr('disabled','disabled');
        var type = $(this).attr('data-type');
        var key = $(this).attr('data_id');
        $rightContent = $('.rightContent');
        $('li.list-group-item').removeClass('active');
        $(this).addClass('active');

        //添加页面可修改main 编辑页面不可修改 cubeeidtor
        if(window.location.href.indexOf("cubeeidtor") > 0){
            $('input[name=name]').attr('disabled','disabled');
        } 
        // 默认project 项目类型为1；
        var project_1 = JSON.parse(projectInfo.project_1);
        project_1['hql_type'] = (project_1['hql_type']) ? project_1.hql_type : 1;

        switch(type){
            case 'project':
                var project =  getData(key);
                if(project){
                    $rightContent.html($('.tablehtml').html());
                    $rightContent.find('.cn_name').html('项目中文名');
                    $rightContent.find('input[name=cn_name]').val(project.cn_name);
                    $rightContent.find('input[name=explain]').val(project.explain);
                    $rightContent.find('input[name=name]').val(project.name);
                    //获取权限类型
                    if(project.authtype  ==undefined){
                         if( typeof config =='undefined'){
                            project.authtype = 1;
                         }else{
                            project.authtype = config.run.authtype;
                         }
                    }
                    //获取权限人员
                    if(project.authuser  ==undefined){
                        if( typeof config =='undefined'){
                            project.authuser = '';
                        }else{
                            project.authuser = config.run.authuser;
                        }
                    }
                    if(project.authtype == 2){
                        $rightContent.find('.authuser').show(); 
                        $rightContent.find('input[name=authuser]').val(project.authuser);
                    }else{
                        $rightContent.find('.authuser').hide(); 
                    }
                    
                    $rightContent.find('.saveInfo').addClass('editInfo').removeClass('saveInfo');
                    $rightContent.find('input[name=type]').val('project');
                    $rightContent.find('input[name=type]').attr('data_id',$(this).attr('data_id'));
                    $rightContent.find('.storetypebox').show();
                    $rightContent.find('.addtitle').text('编辑项目');
                    //调度类
                    if(project.hql_type && project.hql_type == '2'){
                        $rightContent.find('.storebox').hide();
                    } else {
                        $rightContent.find('.storebox').show();
                    }

                    if(project.hql_type){
                         $rightContent.find('select.hql_type').val(project.hql_type);
                    }

                    if(project.storetype && project.storetype != '-1'){
                         $rightContent.find('select.storetype').val(project.storetype);
                    }
                    $rightContent.find('select.hql_type').attr('disabled','disabled') ;
                    //name禁用
                    if(window.categories.length>0){
                        $rightContent.find('input[name=name]').prop('disabled',true);
                    }
                }
                break;
            case 'categories':
                var categories =  getData(key);
                if(categories){
                    $('.rightContent').html($('.tablehtml').html());
                    $('.rightContent').find('.cn_name').html('分类中文名');
                    $('.rightContent').find('input[name=cn_name]').val(categories.cn_name);
                    $('.rightContent').find('input[name=explain]').val(categories.explain);
                    $('.rightContent').find('input[name=name]').val(categories.name);
                    $('.rightContent').find('.saveInfo').addClass('editInfo').removeClass('saveInfo');
                    $('.rightContent').find('input[name=type]').val('categories');
                    $('.rightContent').find('input[name=type]').attr('data_id',key);
                    $('.rightContent').find('.addtitle').text('编辑分类');
                    $('.rightContent').find('.authuser').hide();

                    if(window.categories.length>0){
                        for(var p in window.categories){
                            if(window.categories[p].id == key ){
                                $rightContent.find('input[name=name]').prop('disabled',true);
                                break;
                            }
                        }

                    }
                }
                break;
            case 'groups':
                var data = getData($(this).attr('data_id')),
                    $rightContent = $('.rightContent');
                var tableslen = data.tables.length;


                if(window.location.href.indexOf("cubeeidtor") > 0){
                    var groupnames = $(this).attr('groupname');
                    var category_name = $(this).attr('category_name');
                    var project = GetQueryString('project');
                    var id = GetQueryString('id');
                    history.pushState({}, "", "?project="+project+"&id="+id+"&groupname="+groupnames+"&category_name="+category_name);
                }
                if(tableslen > 0) {
                    for(var p in data.tables){
                        if(!data.tables[p]['time_depend']){
                            data.tables[p]['time_depend'] = '';
                        }
                    }
                    for(var p in data.tables){
                        if(!data.tables[p]['ischecktables']){
                            data.tables[p]['ischecktables'] = 1; 
                        } 
                    }

                }
                //赋值hql 分类名
                $rightContent.html('');
                $rightContent.html($('.hqlHtml').html());
                $rightContent.find('input[name=type]').val('groups');
                $rightContent.find('.metricsBox').html('');
                var parent = $(this).parent();
                $rightContent.find('input[name=type]').attr('parent_id',$(this).attr('parent_id'));
                $rightContent.find('input[name=type]').attr('data_id',$(this).attr('data_id'));
                $rightContent.find('.saveInfo').addClass('editInfo').removeClass('saveInfo');

                data.hql_type = project_1['hql_type'];
                var attach = data.attach?data.attach:"";
                var hive_queue = data.hive_queue ? data.hive_queue : 'bloc';
                var schedule_interval = data.schedule_interval?data.schedule_interval:"0";
                var schedule_interval_offset = data.schedule_interval_offset?data.schedule_interval_offset: getOffsetVal(schedule_interval);
                var latest_end_time = data.latest_end_time ? data.latest_end_time : '';
                var alarm_users = data.alarm_users ? data.alarm_users : '';
                var alarm_type = data.alarm_type ? data.alarm_type : '';
                data.typeStatus = typeStatus;
                data.field_type = field_type;          
                if(data.hql_type && data.hql_type == 2){
                   var interText = doT.template($("#interpolationtmpl2").text());

                   $(".metricsBox").html(interText(data));
                   $('.rightContent .hqlInfo').find('.hdfsMsg').show();
                } else {
                   var interText = doT.template($("#interpolationtmpl").text());
                   $(".metricsBox").html(interText(data));
                   $('.rightContent .hqlInfo').find('.hdfsMsg').hide();
                }

                $(".metricsBox").find('input[name=metricsConfig]').val(JSON.stringify(data));
                $rightContent.find('.hqlInfo').find('input[name=cn_name]').val(data.cn_name);
                $rightContent.find('.hqlInfo').find('input[name=name]').val(data.name).attr('disabled','disabled');

                $rightContent.find('.hqlInfo').find('input[name=name]').attr('category_name',_this.attr('category_name'));
                $rightContent.find('.hqlInfo').find('input[name=name]').attr('add_key',add_key);

                $rightContent.find('.hqlInfo').find('input[name=explain]').val(data.explain);
                $rightContent.find('.hqlInfo').find('textarea.attach').val(attach);
                if(data.run_times !=undefined){
                    $('.rightContent').find('.hqlInfo').find('input[name=run_times]').val(data.run_times);
                }
                $rightContent.find('select[name="hive_queue"]').val(hive_queue);
                $rightContent.find('select[name="schedule_interval"]').val(schedule_interval);
                $rightContent.find('input[name="schedule_interval_offset"]').val(schedule_interval_offset);

                $rightContent.find('input[name="latest_end_time"]').val(latest_end_time);
                $rightContent.find('input[name="alarm_users"]').val(alarm_users);

                if (alarm_type != '') {
                    var check_type;
                    if(alarm_type == 0) {
                        $('input[type="radio"][name="alarm_type"]').eq(1).attr("checked", true);
                    } else if (alarm_type == 1) {
                        $('input[type="radio"][name="alarm_type"]').eq(0).attr("checked", true);
                    }
                }

                if(data.custom_cdate && data.custom_cdate == '1'){
                    var custom_type = data.custom_type?data.custom_type:'range';
                    $rightContent.find('#custome_cdate_type').val(custom_type);
                    var start = data.custom_start?data.custom_start:"$DATE(0)",
                        end = data.custom_end?data.custom_end:"$DATE(0)",
                        single = data.custom_single?data.custom_single:"$DATE(0)";
                    $rightContent.find('input.custom_cdate').prop('checked',true);
                    $rightContent.find('input.custom_start').val(start);
                    $rightContent.find('input.custom_end').val(end);
                    console.log(single);
                    $rightContent.find('input.custom_single').val(single);
                    $(".custome_cdate_type").show();
                    if (custom_type == 'range') {
                        $rightContent.find('#custom_cdatecon').show();
                        $rightContent.find('#custom_cdatecon2').hide();
                    } else {
                        $rightContent.find('#custom_cdatecon').hide();
                        $rightContent.find('#custom_cdatecon2').show();
                    }
                }

                //$('.rightContent').find('.hqlInfo').find('.code').val(data.hql);
                 // hql编辑初始化
                $rightContent.find('.editorcode').attr('id','editorcode');

                  window.editor1 = ace.edit("editorcode");
                  editor1.setTheme("ace/theme/tomorrow_night_eighties");
                  editor1.session.setMode("ace/mode/sql");
                  editor1.setAutoScrollEditorIntoView(true);
                  editor1.setOption("minLines", 20);
                  editor1.setOption("maxLines", 30); 
                  editor1.setValue(data.hql);


                saveAnalyse();
                localConfigHql = JSON.parse(JSON.stringify(data)); // 本地临时group数据保存
                $rightContent.find('.addtitle').text('编辑hql');

                $rightContent.find('select').select2();
                break;
        }
        event.stopPropagation();
    });
    //关闭按钮
    $('body').on('mouseover','.list-group-item',function(event){
        event.stopPropagation();
        $(this).children('b.closeBtn').show();
    }).on('mouseout','.list-group-item',function(){
        $(this).children('b.closeBtn').hide();
    });
    //关闭按钮
    $('body').on('click','.closeBtn',function(){

        var key = $(this).parent().attr('data_id');
        var key_name = $(this).parent().attr('category_name');
        var tempname  = JSON.parse(projectInfo[key])['name'];
        if($(this).parent().attr('data-type') !='groups'){
            var obj = $(this).parent().children('ul');

            if(obj.children('li').length >0){
                alert('请先删除子数据');
                return false;
            }
             //删除执行任务的hql
            $('.hqllist').find('td[name="'+tempname+'"]').closest('tr').remove();
        } else {
           //删除执行任务的hql
            $('.hqllist').find('li[data_id="'+key+'"] >li[name="'+tempname+'"]').remove();
            $('.hqllist').find('td[name="'+key_name+'"] + td > ul >li[name="'+tempname+'"]').remove();
        }

        delete projectInfo[key];
        $('.rightContent').html('');
        if(JSON.stringify(projectInfo) == '{}'){
            $('.addProject').show();
        }

        $(this).parent().remove();
    });
    //全部保存
    $('.saveConfig').click(function(){
        var projectName = GetQueryString('project');
        $.ajax({
            url : "/project/historyConfLog",
            method : "get",
            data: {"project": projectName, "num": 1},
            async: false,
            success : function(data) {
                var data = JSON.parse(data);
                if(data.status == 0){
                     var log = data.data;
                     $.messager.confirm('提示', '最近30分钟存在保存记录<br>修改人员：' + log['creater'] + '<br>修改时间：' + log['updated_at'], function(r) {
                         if (r) {
                             saveConfig();
                         }
                     });
                }else{
                    saveConfig();
                }
            },
            error: function() {
                alert('请求数据异常')
            }
        });

        return;

        if (isCoreProject == 0) {
            saveConfig();
            return;
        }
        $.messager.confirm('提示', '该项目是核心项目确认是否要进行保存？', function(r) {
            if (r) {
                saveConfig();
            }
        });
        function saveConfig() {
            var status = checkData(status);
            if(status){
                alert('信息不全');
                return;
            }
            if( projectInfo !=undefined){
                changeData();
                var config = {};
                if(project.length == 0){
                    alert('请您添加项目');
                    return false;
                }
                config= project[0].content;
                config.categories =[];
                for(var i=0; i< categories.length; i++){
                    var categorie ={};
                    categorie.cn_name = categories[i].content.cn_name;
                    categorie.explain = categories[i].content.explain;
                    categorie.name = categories[i].content.name;
                    categorie.groups =[];
                    if(categories[i].children.length >0){
                        for(var j =0; j<categories[i].children.length; j++){
                            var  group = {};
                            group = categories[i].children[j].content;
                            categorie.groups.push(group);
                        }
                    }
                    config.categories.push(categorie);
                }
                var configObj = {};
                configObj.project = [];
                configObj.project.push(config);
                configObj.run = gerOperate();
                var sendOJb  = {};
                if( id != undefined){
                    sendOJb.config = configObj;
                    sendOJb.id = id;
                }else{
                    sendOJb.config = configObj;
                }
                $('body').mask('正在保存配置。。。。');
                $.ajax({
                    type: "POST",
                    url: "/project/saveProject",
                    data: sendOJb,
                    dataType: "json",
                    success: function(data){
                        if(data.status ==0){
                            //是否启动
                            if(localtempcount >= 0) {
                                $.messager.confirm('提示','项目配置保存成功,需要手动启动，是否现在启动？', function(r){
                                    if(r){
                                        window.location.href ='/project/runlist?project='+configObj.project[0].name+'&id='+sendOJb.id;
                                    } else {
                                        window.location.href ='/project/index';
                                    }
                                });

                            } else {
                                alert('项目配置保存成功！');
                                window.location.href ='/project/index';
                            }
                            localtempcount = -1;
                        }else{
                            alert(data.msg);
                        }
                        $('body').unmask();
                    }
                });

            }else{
                alert('不要捣乱，请填写配置');
            }
        }
    });

    //表格添加一行
    $('body').on('click','button.addtableBtn',function(){
        var str = '<tr><td contenteditable="true" data-type="name"></td><td contenteditable="true" data-type="cn_name"></td> <td contenteditable="true" data-type="par"></td><td contenteditable="true" data-type="time_depend"></td><td style="text-align:center"><input type="checkbox" class="ischecktables" name="ischecktables" checked></td><td><button class="btn btn-default btn-xs removetableBtn">删除</button></td></tr>';
        $(this).closest('tr').before(str);

    });
    //表格删除一行
    $('body').on('click','button.removetableBtn',function(){
        $(this).closest('tr').remove();
    });

    //hql 存储类型
    $('body').on('change','.rightContent select.hql_type',function(){
        var hql_type = $(this).val();
        if(hql_type == '2'){
            $(this).closest('table').find('tr.storebox').hide();
        } else {
            $(this).closest('table').find('tr.storebox').show();
        }
        
    });

    //执行sqllist
    getHqllist();
    $('.saveRun').click(function(){

        //判断用户流程 避免用户信息丢
        var html = $('.rightContent').html();
        if(html !=''){
            var type = $('.rightContent').find('input[name=type]').val();
            switch(type){
                case 'project':
                 alert('请先保存项目信息');break;
                case 'categories':
                 alert('请先保存分类信息');break;
                case 'groups':
                 alert('请先保存sql信息');break;
                default:
                 alert('请先配置项目');break;
            }
            return;
        }
        var  run = gerOperate();
        projectInfo['operate'] =  JSON.stringify(run);
        $('.saveConfig').removeAttr('disabled');
        alert('ok');
    });

    var operate = getData('operate');
    if(operate){
        $('.runConfig').find('.start').val(operate.date_s)
        $('.runConfig').find('.end').val(operate.date_e);
        var checkedArr = operate.run_instance.group;
        $('.runConfig').find('.runList').each(function(){
            var obj = $(this);
            $(this).find('td').eq(1).find('ul').find('li').each(function(){
                var  str  = obj.find('td').eq(0).attr('name');
                str += "." + $(this).attr('name');
                for(var i=0; i< checkedArr.length; i++){
                    if( str == checkedArr.length){
                        $(this).find('input').attr('checked','checked');
                    }
                }
            });
        });
    }
    if($('.alert-danger').length >0){
        $('.right_progress').hide();
    }

    $('body').on('click','.codefull',function(){
        // var top = $(document).scrollTop()-137;
        // var width = $(window).width();
        // var hqlwidth = $('.hqlInfo').width();
        // var left = width-hqlwidth-45;
        var top = $(document).scrollTop()-127;
        var width = $(window).width()+11;
        var hqlwidth = $('.hqlInfo').width();
        var left = width-hqlwidth-55;

        $codebox = $(this).closest('.codebox')
        $editorcode = $codebox.find('.editorcode');

        if($editorcode.hasClass('big')){
            editor1.setOption("minLines", 20);
            editor1.setOption("maxLines", 30);  
             var css = $editorcode.attr('style');
            var h = css.split(";")[0];
            $codebox.css('position','relative');
            $editorcode.removeClass('big').css({'height':h,'left':'0','width':'90%','top':'0'});;
            $(this).removeClass('codefullbig').css({"top":"7px","right":"15%",'position':'absolute'});
            $('body').css('overflow','scroll');
            
        } else {
            $codebox.css('position','inherit');
            // editor1.setOption("minLines", 40);
            // editor1.setOption("maxLines", 40);
            var line = $(window).height() / 16;
            editor1.setOption("minLines", line);
            editor1.setOption("maxLines", line);
            $('body').css('overflow-y','hidden');
            $editorcode.addClass('big').css({'width':width,'top':top,'left':"-"+left+'px'});
            //$(this).addClass('codefullbig').css({'top':(top+4),'right':'5%'});
            //$(this).css({'top':'10px','right':'30px','position':'fiexd' });
            $(this).addClass('codefullbig').css({'top':'10px','right':'40px','position':'fixed'});
            
        }
    });

    $('body').on('change', '.custome_cdate_type' , function() {
        var $custom_cdatecon = $("#custom_cdatecon"),
            $custom_cdatecon2 = $("#custom_cdatecon2");
        if ($("#custome_cdate_type").val() == 'range') {
            $custom_cdatecon.show();
            $custom_cdatecon2.hide();
        } else {
            $custom_cdatecon.hide();
            $custom_cdatecon2.show();
        }
    });

    //自定义数据时间展现
    $('body').on('click','input.custom_cdate',function(){

        var $custom_cdatebox = $(this).closest('.custom_cdatebox'),
            $custom_cdate_type = $custom_cdatebox.find("#s2id_custome_cdate_type"),
            $custom_cdatecon = $custom_cdatebox.find("#custom_cdatecon"),
            $custom_cdatecon2 = $custom_cdatebox.find("#custom_cdatecon2");
        var old_custom_cdate = localConfigHql.custom_cdate!= undefined ? localConfigHql.custom_cdate : -1 ;

        if($(this).is(':checked')){
            $custom_cdate_type.show();
            if ($("#custome_cdate_type").val() == 'range') {
                $custom_cdatecon.show();
                $custom_cdatecon2.hide();
            } else {
                $custom_cdatecon.hide();
                $custom_cdatecon2.show();
            }
            if(old_custom_cdate!=1){
                //alert('设置自定义数据展现时间，必须要重新解析hql');
                $(".metricsBox").html('');
            }
        } else {
            $custom_cdatecon.hide();
            $custom_cdate_type.hide();
            $custom_cdatecon.hide();
            $custom_cdatecon2.hide();
            $.messager.alert('提示','您取消了自定义数据展现时间设置,请注意修改hql中的cdate字段','info');
        }

    });

    //报表编辑链接到hql 指标的name
   var groupname = GetQueryString('groupname');
    if(groupname){
        $('.listmap_ul').find('li[groupname='+groupname+']').click();
    }

});

