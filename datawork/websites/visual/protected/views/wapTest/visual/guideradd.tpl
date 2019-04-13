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
                编辑工具页面
            {/else/}
                添加工具页面
            {//if/}
            <a href='/menu/index' class='pull-right'>返回列表</a>
          </div>
          <div class="panel-body" style='padding:5px'>
            <form id='fm' method='post'>
              <table class='table table-condensed table-bordered' style='margin-bottom:5px'>
                <tr class='firstMenu'>
                  <td class='tdwidth'>分类名称<b style='color:red'>*</b></td>
                  <td>
                    <select name='parent_id' class='inputall'>
                      {/foreach from=$firstMenu item = item key=key/}
                        <option value='{/$item.id/}'>{/$item.name/}</option>
                      {//foreach/}
                    </select>
                  </td>
                </tr>
                <tr class='secondMenu'>
                  <td class='tdwidth'>工具名称<b style='color:red'>*</b></td>
                    <td>
                      <input type='text' name='name' class='inputall'/><br/>
                    </td>
                </tr>
                  <tr class='content'>
                      <td class='tdwidth'>注释<b style='color:red'>*</b></td>
                      <td>
                          <input type='text' name='content' class='inputall'/><br/>
                      </td>
                  </tr>
                  <tr class='icon'>
                      <td class='tdwidth'>图标<b style='color:red'>*</b></td>
                      <td>
                      <input type="text" name="icon" class="inputall icon-input" value="glyphicon glyphicon-adjust">
                          <a style='padding:3px 10px' class='btn btn-default btn-sm show-icon-ul'>选择图标</a><br/>
                          <span class="glyphicon glyphicon-adjust"></span><br/>

                          <span class='tipinfo'>(点击选择图标,从下方的图标中选择一个,或直接填入将图标代号)</span>
                      </td>
                  </tr>

                  <tr class='new_window'>
                  <td class='tdwidth'>打开位置<b style='color:red'>*</b></td>
                  <td>
                      <select name="new_window">
                          <option value="1">新窗口</option>
                          <option value="2">当前页</option>
                      </select>
                  </td>
                  </tr>

                  <tr class='url'>
                      <td class='tdwidth'>链接</td>
                      <td>
                          <input type='text' name='url' class='inputall'/></span><br/>
                          <span class="tipinfo">(外网url必须是完整的比如：http://www.baidu.com,data平台内的链接可直接填入路径如: /visual/index)</span>
                      </td>
                  </tr>

              </table>
              <span style='padding-left:30%'></span>
                <input type='hidden' value='' name='id'/>
              <button type='button' class='btn btn-primary btn-sm opratecheck'>保存</button>
            </form>
          </div>
          </div>
        </div>

        <ul class="bs-glyphicons icon-ul" style="display: none;">
            <li>
                <span class="glyphicon glyphicon-adjust"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-adjust</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-align-center"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-align-center</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-align-justify"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-align-justify</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-align-left"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-align-left</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-align-right"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-align-right</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-arrow-down"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-arrow-down</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-arrow-left"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-arrow-left</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-arrow-right"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-arrow-right</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-arrow-up"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-arrow-up</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-asterisk"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-asterisk</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-backward"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-backward</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-ban-circle"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-ban-circle</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-barcode"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-barcode</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-bell"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-bell</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-bold"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-bold</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-book"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-book</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-bookmark"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-bookmark</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-briefcase"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-briefcase</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-bullhorn"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-bullhorn</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-calendar"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-calendar</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-camera"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-camera</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-certificate"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-certificate</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-check"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-check</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-chevron-down"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-chevron-down</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-chevron-left"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-chevron-left</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-chevron-right"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-chevron-right</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-chevron-up"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-chevron-up</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-circle-arrow-down"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-circle-arrow-down</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-circle-arrow-left"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-circle-arrow-left</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-circle-arrow-right"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-circle-arrow-right</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-circle-arrow-up"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-circle-arrow-up</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-cloud"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-cloud</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-cloud-download"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-cloud-download</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-cloud-upload"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-cloud-upload</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-cog"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-cog</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-collapse-down"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-collapse-down</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-collapse-up"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-collapse-up</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-comment"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-comment</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-compressed"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-compressed</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-copyright-mark"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-copyright-mark</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-credit-card"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-credit-card</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-cutlery"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-cutlery</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-dashboard"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-dashboard</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-download"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-download</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-download-alt"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-download-alt</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-earphone"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-earphone</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-edit"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-edit</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-eject"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-eject</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-envelope"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-envelope</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-euro"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-euro</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-exclamation-sign"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-exclamation-sign</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-expand"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-expand</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-export"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-export</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-eye-close"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-eye-close</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-eye-open"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-eye-open</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-facetime-video"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-facetime-video</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-fast-backward"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-fast-backward</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-fast-forward"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-fast-forward</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-file"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-file</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-film"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-film</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-filter"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-filter</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-fire"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-fire</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-flag"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-flag</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-flash"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-flash</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-floppy-disk"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-floppy-disk</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-floppy-open"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-floppy-open</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-floppy-remove"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-floppy-remove</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-floppy-save"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-floppy-save</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-floppy-saved"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-floppy-saved</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-folder-close"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-folder-close</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-folder-open"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-folder-open</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-font"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-font</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-forward"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-forward</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-fullscreen"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-fullscreen</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-gbp"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-gbp</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-gift"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-gift</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-glass"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-glass</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-globe"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-globe</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-hand-down"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-hand-down</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-hand-left"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-hand-left</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-hand-right"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-hand-right</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-hand-up"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-hand-up</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-hd-video"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-hd-video</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-hdd"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-hdd</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-header"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-header</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-headphones"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-headphones</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-heart"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-heart</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-heart-empty"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-heart-empty</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-home"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-home</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-import"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-import</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-inbox"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-inbox</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-indent-left"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-indent-left</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-indent-right"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-indent-right</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-info-sign"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-info-sign</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-italic"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-italic</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-leaf"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-leaf</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-link"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-link</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-list"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-list</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-list-alt"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-list-alt</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-lock"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-lock</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-log-in"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-log-in</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-log-out"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-log-out</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-magnet"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-magnet</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-map-marker"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-map-marker</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-minus"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-minus</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-minus-sign"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-minus-sign</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-move"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-move</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-music"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-music</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-new-window"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-new-window</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-off"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-off</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-ok"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-ok</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-ok-circle"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-ok-circle</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-ok-sign"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-ok-sign</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-open"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-open</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-paperclip"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-paperclip</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-pause"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-pause</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-pencil"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-pencil</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-phone"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-phone</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-phone-alt"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-phone-alt</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-picture"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-picture</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-plane"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-plane</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-play"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-play</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-play-circle"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-play-circle</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-plus"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-plus</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-plus-sign"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-plus-sign</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-print"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-print</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-pushpin"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-pushpin</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-qrcode"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-qrcode</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-question-sign"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-question-sign</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-random"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-random</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-record"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-record</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-refresh"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-refresh</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-registration-mark"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-registration-mark</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-remove"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-remove</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-remove-circle"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-remove-circle</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-remove-sign"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-remove-sign</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-repeat"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-repeat</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-resize-full"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-resize-full</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-resize-horizontal"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-resize-horizontal</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-resize-small"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-resize-small</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-resize-vertical"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-resize-vertical</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-retweet"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-retweet</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-road"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-road</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-save"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-save</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-saved"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-saved</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-screenshot"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-screenshot</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-sd-video"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-sd-video</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-search"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-search</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-send"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-send</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-share"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-share</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-share-alt"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-share-alt</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-shopping-cart"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-shopping-cart</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-signal"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-signal</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-sort"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-sort</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-sort-by-alphabet"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-sort-by-alphabet</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-sort-by-alphabet-alt"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-sort-by-alphabet-alt</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-sort-by-attributes"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-sort-by-attributes</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-sort-by-attributes-alt"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-sort-by-attributes-alt</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-sort-by-order"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-sort-by-order</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-sort-by-order-alt"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-sort-by-order-alt</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-sound-5-1"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-sound-5-1</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-sound-6-1"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-sound-6-1</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-sound-7-1"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-sound-7-1</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-sound-dolby"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-sound-dolby</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-sound-stereo"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-sound-stereo</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-star"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-star</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-star-empty"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-star-empty</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-stats"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-stats</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-step-backward"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-step-backward</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-step-forward"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-step-forward</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-stop"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-stop</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-subtitles"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-subtitles</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-tag"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-tag</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-tags"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-tags</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-tasks"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-tasks</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-text-height"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-text-height</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-text-width"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-text-width</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-th"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-th</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-th-large"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-th-large</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-th-list"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-th-list</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-thumbs-down"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-thumbs-down</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-thumbs-up"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-thumbs-up</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-time"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-time</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-tint"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-tint</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-tower"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-tower</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-transfer"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-transfer</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-trash"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-trash</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-tree-conifer"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-tree-conifer</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-tree-deciduous"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-tree-deciduous</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-unchecked"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-unchecked</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-upload"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-upload</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-usd"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-usd</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-user"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-user</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-volume-down"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-volume-down</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-volume-off"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-volume-off</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-volume-up"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-volume-up</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-warning-sign"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-warning-sign</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-wrench"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-wrench</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-zoom-in"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-zoom-in</span>
            </li>
            <li>
                <span class="glyphicon glyphicon-zoom-out"></span><br>
                <span class="glyphicon-class">glyphicon glyphicon-zoom-out</span>
            </li>
        </ul>
        <style>
            .bs-glyphicons li {
                float: left;
                width: 100px;
                height: 100px;
                padding: 10px;
                margin: 0 -1px -1px 0;
                font-size: 12px;
                line-height: 1.4;
                text-align: center;
                border: 1px solid #ddd;
            }
            .bs-glyphicons li:hover {
                color: #00b7ee;
            }
        </style>
        <script>
            $('.show-icon-ul').click(function(){
                $('.icon-ul').slideToggle();
            })
            $('.icon-ul li').click(function(){
            $('.icon-input').val($(this).find('.glyphicon-class').html());
                $('.icon-input').next().next().next().attr('class',$(this).find('.glyphicon-class').html());
                $('.icon-ul').slideUp();
            })
        </script>
    </div>
  </div>
</div>

<script type="text/javascript">
  var type ='{/$type/}';
  {/if $type eq 'editor'/}
    var  id = {/$id/};
    var  menuInfo = {/$menuInfo/};
  {//if/}
</script>
<script src="/assets/js/tool.js?version={/$version/}"></script>

