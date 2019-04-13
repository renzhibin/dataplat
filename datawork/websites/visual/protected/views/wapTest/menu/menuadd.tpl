{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">

<script src="/assets/js/project.js?version={/$version/}"></script> 
<style type="text/css">
  .tdwidth{ width: 30%; text-align: right;}
  .inputall{ width: 300px}
</style>
<div>
  {/include file="layouts/menu.tpl"/}
  <div id='right'>
    <div id="content" class="content" >
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
        <div class='container'>
          <div class="panel panel-info">
          <div class="panel-heading">
            {/if $id neq ''/}
                编辑二级菜单页面
            {/else/}
                添加二级菜单页面
            {//if/}
            <a href='/menu/index' class='pull-right'>返回列表</a>
          </div>
          <div class="panel-body" style='padding:5px'>
            <form id='fm' method='post'>
              <table class='table table-condensed table-bordered' style='margin-bottom:5px'>
                <tr class='firstMenu'>
                  <td class='tdwidth'>一级菜单名称<b style='color:red'>*</b></td>
                  <td>
                    <select name='first_menu' class='inputall'>
                      {/foreach from=$firstMenu item = item key=key/}
                        <option value='{/$item.first_menu/}'>{/$item.first_menu/}</option>
                      {//foreach/}
                    </select>
                  </td>
                </tr>
                <tr class='secondMenu'>
                  <td class='tdwidth'>二级菜单名称<b style='color:red'>*</b></td>
                    <td>
                      <input type='text' name='second_menu' class='inputall'/><br/>
                      <span class='tipinfoother'>(报表名必须是中英文、数字、小括号或者下划线且不超过15个字符!)
                    </td>
                </tr>
               <!--  <tr>
                  <td  class='tdwidth'>菜单Url类型</td>
                    <td>
                      <select class='menuUrlType inputall' name ='type'>
                        <option value=1 selected ='selected'>报表</option>
                        <option value=2 >外链</option>
                      </select>
                    </td>
                </tr>  -->
                <tr class='innerUrl'>
                    <td class='tdwidth'>报表<b style='color:red'>*</b></td>
                    <td>
                       <select multiple class='select inputall' style='width:350px' name='table_id[]'>
                          {/foreach  from = $visualList item = item  key=key/}
                           <option value='{/$item.id/}'>{/$item.cn_name/}</option>
                          {//foreach/}
                       </select>
                    </td>
                </tr>
                <tr class='openUrl'>
                    <td class='tdwidth'>外链</td>
                    <td>
                       <textarea  name='url'  style='width:350px;height:100px' class="inputall" 
                       placeholder="名称:url的格式，以回车分隔"></textarea><br/>
                       <span class='tipinfo'>(url必须是完整的比如：http://www.baidu.com)</span>
                    </td>
                </tr>
              </table>
              <span style='padding-left:30%'></span>
                <input type='hidden' value='' name='menu_id'/> 
              <button type='button' class='btn btn-primary btn-sm opratecheck'>保存</button>
            </form>
          </div>
          </div>
        </div>
    </div>
  </div>
</div>
{/include file="layouts/menujs.tpl"/}
<script type="text/javascript">
  var type ='{/$type/}';
  {/if $type eq 'editor'/}
    var  id = {/$id/};
    var  menuInfo = {/$menuInfo/};
  {//if/}
</script>
<script src="/assets/js/menu.js?version={/$version/}"></script> 