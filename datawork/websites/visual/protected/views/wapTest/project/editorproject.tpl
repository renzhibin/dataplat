{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">
<div>
  {/include file="layouts/menu.tpl"/}
  <div id='right'>
    <div id="content"  class="content" >
        <!--面包屑效果-->
        <div id="breadcrumbs-one">
            {/foreach from = $guider item= place key=key/}
            {/if $guider[0] eq $place /}
            <span><a href="{/$place.href/}">{/$place.content/}</a></span>
            {/else/}
            {/if $place.href eq '#'/}
            <span>></span><span>{/$place.content/}</span>
            {/else/}
            <span>></span><span><a href="{/$place.href/}">{/$place.content/}</a></span>
            {//if/}
            {//if/}
            {//foreach/}
        </div>
      <div style='height:10px'></div>
      <div class="container" style='padding:0px'>
        {/if isset($config)/}
      	<div class='row'>
          <h3 id='data'>数据配置</h3>
      		<div class='col-lg-4' style='width:30%;float:left'>
      			<ul class='list-group listmap_ul' style='padding:10px;'>
      			</ul>
      		</div>
      		<div class='col-lg-8 rightContent' style='width:70%;float:left'></div>
      	</div>
        <div class='row'>
           <h3 id='opreate'>操作配置</h3>
           <p style="padding-left:15px;color:#5bc0de;">温馨提示：<br/>
                  项目日期：只有在此时间区间内的项目才会运行。<br/>
                  执行sql列表：只有在执行sql列表中选定的sql才会执行。</p>
           <div class='col-lg-12'>
            <table class='table table-bordered table-condensed runConfig' >
              <tr>
                <td>作者</td>
                <td class='author'>{/$author/}</td>
              </tr>
              <tr>
                <td>项目日期</td>
                <td>
                  开始时间：<input type='text' class='datepicker start'/>
                  结束时间：<input type='text' class='datepicker end'/>
                </td>
              </tr>
              <tr>
                <td>执行sql列表</td>
                <td class='hqllist'></td>
              </tr>
              <tr>
                <td>操作</td>
                <td><button class='btn btn-primary saveRun'>确定</button></td>
              </tr>
            </table>
           </div>
        </div>
        {/else/}
          <div class="alert alert-danger" role="alert">{/$msg/}</div>
        {//if/}
      </div>

    </div>
  </div>
</div>
{/include file="layouts/menujs.tpl"/}

{/include file="project/public.tpl"/}
<script type="text/javascript">

  //处理数据
  {/if isset($config)/}
    var id = {/$id/};
    var config = {/$config/};
    localStorage.clear();
    var data_id =0;
    data_id ++;
    var project = {};
    project.cn_name = config.project[0].cn_name;
    project.name = config.project[0].name;
    project.explain = config.project[0].explain;
    project.hql_type = (config.project[0].hql_type) ? config.project[0].hql_type : 1;
    project.storetype = (config.project[0].storetype) ? config.project[0].storetype : 2;

    project.authtype = config.project[0].authtype
    project.authuser = config.project[0].authuser;


    projectInfo['project_'+ data_id] = JSON.stringify(project);
    if(config.run !=undefined){
       projectInfo['operate'] = JSON.stringify(config.run);
    }
    var  categoriesArr= config.project[0].categories;
    data_id ++;
    for(var i =0; i< categoriesArr.length; i++){
        var  categories ={};
        categories.cn_name = categoriesArr[i].cn_name;
        categories.name = categoriesArr[i].name;
        categories.explain = categoriesArr[i].explain;
        var cateId = 'categories_'+ data_id;
        projectInfo[cateId] = JSON.stringify(categories);
        if(categoriesArr[i].groups.length >0){
           for(var j =0; j< categoriesArr[i].groups.length; j++){
            data_id ++; 
            projectInfo['groups_'+ data_id +"@"+ cateId] = JSON.stringify(categoriesArr[i].groups[j]);
           }
        }
    }
    $('.saveConfig').removeAttr('disabled');
  {//if/}
</script>
<script src="/assets/js/projectpublic.js?version={/$version/}"></script> 
<script type="text/javascript">
    $(function(){
      if("undefined" != typeof config){
        if(commentInfo.length <1){
          commentInfo.metrics = config.project[0].categories[0].groups[0].metrics;
          commentInfo.dimensions = config.project[0].categories[0].groups[0].dimensions;
          commentInfo.tables = config.project[0].categories[0].groups[0].tables;
          commentInfo.dim_sets = config.project[0].categories[0].groups[0].dim_sets;
        }
        //处理选择项
        if(config.run !=undefined){
          var operateInfo = projectInfo['operate'];
          operateInfo = eval( "("+ operateInfo +")" );
          $(".hqllist").find('.list-group-item').find('input').each(function(){
            var opObj = operateInfo.run_instance.group;
            var name = $(this).parent().attr('name');
            var parent_name=$(this).parent().parent().parent().prev().attr('name');
            var status =0;
            for(var i=0; i< opObj.length; i++){
                str = opObj[i].name.split('.')[1];
                str_parent=opObj[i].name.split('.')[0]
                if(str == name && str_parent==parent_name){
                  status =1;
                }
            }
            if(status >0){
               $(this).attr('checked','checked');
            }else{
               $(this).attr('checked',false);
            }
          });
        }
      }else{
         setTimeout("history.back(-1)",1000);
      }
    })
</script>