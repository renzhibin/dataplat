<script type="text/javascript">
    var first_menu= "";
    var second_menu= "";
    var third_menu= "";
    var ygData = "";
</script>
<div class="sidebar_wrapper">
    <div id="sidebar" class="max-hide d_sidebar">
        <ul>
            <!--
            <li><a href='/visual/index/4694'>优供概要数据</a></li>
            -->
            {/$ygData="优供核心数据"/}
            {/foreach from = $urlMenu item=um key=key/}
                <li class="submenus">
                    {/if $um.table_id|@count neq 0 /}
                        <a id="whiteMenu" class="two-tab" href='{/$um.table_id[0].url/}'>
                            {/$um.first_menu/}
                        </a>
                    {//if/}
                    <ul class="three-tab hide white-menu-three hidden" style="position: fixed;top: 41px;left: 4.1rem;height:100%;overflow:scroll;padding-bottom:45px" data-id="{/$menuinfo.menu_id/}">
                        {/foreach from = $urlMenu[0].table_id item=tMenu key=tkey/}
                            {/if $table.type neq 2/}
                                <!--三级正常 -->
                                <li  class=''>
                                    <a href="/visual/index/{/$tMenu.id/}" data-id="{/$tMenu.id/}" {/if $id === $tMenu.id/}class="menu-active" {//if/}>
                                        {/$tMenu.cn_name/}
                                    </a>
                                </li>
                            {/else/}
                                <!--三级外链 电脑上不显示web-three-tab-->
                                <li  class='web-three-tab'>
                                    <a  data-option="{/$table.url/}" class="openurl ygcp" data-id="{/$menuinfo.menu_id/}" data-type="{/$table.type/}"> 
                                        {/$table.name/} 
                                        <span class=" glyphicon glyphicon-globe"></span>
                                    </a>
                                </li>
                            {//if/}
                        {//foreach/}
                    </ul>
                 </li>
            {//foreach/}
            <!--收藏-->
            <li class='submenu  {/if $menu_id eq 0  && $url_tpl eq '' && $isWhiteTable !== 1  /}open{//if/}'>
                <a href="#" class="d_first_menu">
                    {/if $menu_id eq 0/}
                        {/if $url_tpl eq 'visual'/}
                            <i class="icon-angle-right show"></i>
                        {/else/}
                            <i class='icon-angle-down show'></i>
                        {//if/}
                    {/else/}
                        <i class="icon-angle-right show"></i>
                    {//if/}
                    <i class="icon-heart"></i>
                    <span>收藏</span>
                </a>
                <!--收藏子菜单-->
                {/if $collect|@count neq 0  || $isCollectCustom|@count neq 0/}
                    <ul class="d_secoed_menu">
                        {/foreach from = $collect item= coll key=key/}
                            <li 
                                {/if $coll.id eq $id /} 
                                    class='active'  
                                    {/$first_menu = "收藏"/}  
                                    {/$second_menu = $coll.name/}
                                {//if/}
                            >
                                <a href="/visual/index/{/$coll.id/}">{/$coll.name/}</a>
                            </li>
                        {//foreach/}
                        {/foreach from = $collectCustom item= coll key=key/}
                            <li 
                                {/if $coll.id eq $id /} 
                                    class='active'  
                                    {/$first_menu = "收藏"/}  
                                    {/$second_menu = $coll.name/}
                                {//if/}
                            >
                                <a href="/visual/index/{/$coll.id/}?custom=1">{/$coll.name/}</a>
                            </li>
                        {//foreach/}
                    </ul>
                {//if/}
            </li>
            <!--一级菜单menuTitle-->
            {/foreach from = $menuTitle item= item key=key/}
                <!--判断应该展开的一级菜单 $menu_id == $secondid-->
                <li class="submenu
                    {/foreach from = $item  key=secondid  item = secondinfo/}
                        {/if $key  eq '垂直业务' && $menu_id eq 0  && $collect|@count eq 0  /}
                            open
                        {//if/}
                        {/if $menu_id eq  $secondid/}
                            open
                        {//if/}
                 {//foreach/}"
                >
                    <a class="d_first_menu">
                        {/if $menu_id neq 0 /}
                          {/foreach from = $item key=secondid  item = secondinfo/}
                           {/if $menu_id eq  $secondid/}
                              <i class="icon-angle-down show"></i>
                           {/else/}
                              <i class="icon-angle-right show"></i>
                           {//if/}
                           {/break/}
                          {//foreach/}
                        {/else/}
                              <i class="icon-angle-down show"></i>
                        {//if/}
                        <i class="icon-time"></i>
                        <span>{/$key/}</span>
                    </a>
                    <!--二级菜单-->
                    <ul class="d_secoed_menu">
                        {/foreach from = $item item= menuinfo key= mid/}
                            <!--面包屑赋值-->
                            <li {/if $menu_id eq $menuinfo.menu_id/} class='active' {/$first_menu = $key/}  {/$second_menu = $menuinfo.name/} {//if/}>
                                {/if  $menuinfo.type eq  2/}
                                   <!--二级外链 -->
                                   <i class=" icon-circle-blank"></i>
                                   <a target='_blank' data-option="{/$menuinfo.url/}" data-type=2>{/$menuinfo.name/}</a>
                                {/else/}
                                    <!--二级正常1 -->
                                    {/if  $menuinfo.default_id  neq ''/}
                                      <a href="/visual/index/menu_id/{/$menuinfo.menu_id/}/id/{/$menuinfo.default_id/}" class="two-tab">
                                        <i class=" icon-circle-blank"></i>
                                        {/$menuinfo.name/}
                                      </a>
                                        <!--三级 -->
                                        <ul class="three-tab hide" style="position: fixed;top: 41px;left: 4.1rem;height:100%;overflow:scroll;padding-bottom:45px" data-id="{/$menuinfo.menu_id/}">
                                          {/foreach $menuinfo.table as $table/}
                                              {/if $table.type neq 2/}
                                                  <!--三级正常 -->
                                                  <li  class=''>
                                                      <a href="/visual/index/menu_id/{/$menuinfo.menu_id/}/id/{/$table.id/}" data-id="{/$table.id/}">{/$table.cn_name/}
                                                        {/if $table.type eq 3/}
                                                        <span class=" glyphicon glyphicon-globe"></span>
                                                        {//if/}
                                                      </a>
                                                  </li>
                                              {/else/}
                                                    <!--三级外链 电脑上不显示web-three-tab-->
                                                    <li  class='web-three-tab'>
                                                        <a  data-option="{/$table.url/}" class="openurl ygcp" data-id="{/$menuinfo.menu_id/}" data-type="{/$table.type/}"> {/$table.name/} <span class=" glyphicon glyphicon-globe"></span></a>
                                                    </li>
                                              {//if/}
                                            {//foreach/}
                                        </ul>
                                    {/else/}
                                        <!--二级正常2 -->
                                        <a href="/visual/index/menu_id/{/$menuinfo.menu_id/}" class="two-tab">
                                            <i class=" icon-circle-blank"></i>
                                            {/$menuinfo.name/}
                                        </a>
                                        <ul class="three-tab web-three-tab hide" style="position: fixed;top: 41px;left: 4.1rem;height:100%;overflow:scroll;padding-bottom:45px" data-id="{/$menuinfo.menu_id/}">
                                            {/foreach $menuinfo.table as $table/}
                                                {/if $table.type eq 3/}
                                                    <!--三级正常 -->
                                                    <li  class=''>
                                                        <a href="/visual/index/menu_id/{/$menuinfo.menu_id/}/id/{/$table.id/}" data-id="{/$table.id/}">{/$table.cn_name/}</a>
                                                    </li>
                                                {/else if $table.type eq 3/}
                                                    <li  class=''>
                                                        <a data-option="{/$table.url/}" href="/visual/index/menu_id/{/$menuinfo.menu_id/}/id/{/$table.id/}" data-id="{/$table.id/}">{/$table.cn_name/}<span class=" glyphicon glyphicon-globe"></span></a>
                                                    </li>
                                                {/else/}
                                                    <!--三级外链 -->
                                                    <li  class=''>
                                                        <a  data-option="{/$table.url/}" class="openurl ygcp" data-id="{/$menuinfo.menu_id/}" data-type="{/$table.type/}"> 
                                                            {/$table.name/} 
                                                            <span class=" glyphicon glyphicon-globe"></span>
                                                        </a>
                                                    </li>
                                                {//if/}
                                            {//foreach/}
                                        </ul>
                                    {//if/}
                                {//if/}
                            </li>
                        {//foreach/}
                    </ul>
                </li>
            {//foreach/}

            <!--管理工具菜单-->
            {/if $specialMenu|@count neq 0/}
                <li class='submenu 
                    {/if $url_tpl eq 'report' || $url_tpl eq 'project' || $url_tpl eq 'projecttpl' || $url_tpl eq 'menu' || $url_tpl eq 'timemail' || $url_tpl eq 'api' || $url_tpl eq 'privilege' /} 
                        open 
                    {//if/}  
                max-hide'>
                    <a class="d_first_menu">
                        {/if $url_tpl neq '' /}
                            <i class="icon-angle-down show"></i>
                        {/else/}
                            <i class="icon-angle-right show"></i>
                        {//if/}
                        <i class="icon-briefcase"></i>
                        <span>管理工具</span>
                    </a>
                    <ul class="d_secoed_menu">
                        {/foreach from = $specialMenu item= smenu key=key/}
                            <li {/if $url_tpl eq  $smenu.index/}  class="active" {//if/}>
                                <a href='{/$smenu.url/}'>
                                    <i class=" icon-circle-blank"></i>
                                    {/$smenu.name/}
                                </a>
                            </li>
                        {//foreach/}
                    </ul>
                </li>
            {//if/}
            <!--权限管理-->
            {/if $is_super eq  1/}
                <li class='submenu 
                    {/if $url_tpl eq 'index' || $url_tpl eq 'userroles' || $url_tpl eq 'reportroles' /} 
                        open 
                    {//if/}  
                max-hide'>
                    <a class="d_first_menu">
                        {/if $url_tpl neq '' /}
                            <i class="icon-angle-down show"></i>
                        {/else/}
                            <i class="icon-angle-right show"></i>
                        {//if/}
                        <i class="icon-lock"></i>
                        <span>权限管理</span>
                    </a>
                    <ul class="d_secoed_menu">
                        {/foreach from = $powerMenu item= smenu key=key/}
                            <li {/if $url_tpl eq  $smenu.index/}  class="active" {//if/}>
                                <a href='{/$smenu.url/}'>
                                    <i class=" icon-circle-blank"></i>
                                    {/$smenu.name/}
                                </a>
                            </li>
                        {//foreach/}
                    </ul>
                </li>
            {//if/}
            <!--常用工具菜单-->
            {/if $specialMenu|@count neq 0/}
                <li class="max-hide">
                    <a href='/visual/toolguider' class="d_first_menu">
                        <i class="icon-cog"></i>
                        常用工具
                    </a>
                </li>
            {//if/}
        </ul>
    </div>
</div>
<div class="max-show web-breadcrumbs">
    {/if $first_menu/}
        {/if $first_menu=="收藏" || $first_menu=="优供产品"/}
            {/if $isWhiteTable === 0/}
                <a class="first-bread">{/$first_menu/}</a>
                <!--二级面包屑 -->
                <!--收藏-优供 -->
                {/if $second_menu/}
                    <a class="second-bread">{/$second_menu/}</a>
                {/else /}
                    {/$ygData/}
                {//if/}
                <span class="third-bread"></span>
            {/else/}
                <a class="second-bread">{/$second_menu/}</a>
                <span class="third-bread"></span>
            {//if/}
        {/else/}
            <a class="first-bread">{/$first_menu/}</a>
            <!--正常三级面包屑 -->
            {/if $second_menu/}
                <a class="second-bread">{/$second_menu/}</a>
            {/else /}
                {/$ygData/}
            {//if/}
            <span class="third-bread"></span>
        {//if/}
    {/else/}
        <!--优供概要 -->
        <a class="first-bread">{/$ygData/}</a>
        {/if $isWhiteTable === 1 && !empty($urlMenu[0].table_id) /}
            {/foreach from = $urlMenu[0].table_id item= tMenu key=tkey/}
                {/if $tMenu.id === $id/}
                  <a class="second-bread">{/$tMenu.cn_name/}</a>
                {//if/}
            {//foreach/}
        {//if/}
    {//if/}
</div>