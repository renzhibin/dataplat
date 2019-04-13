<script type="text/javascript">
    var first_menu= "";
    var second_menu= "";
    var third_menu= "";
    var ygData = "";
</script>


<div id="sidebar" class="max-hide">
  <ul>
      <!--
      <li><a href='/visual/index/4694'>优供概要数据</a></li>
      -->
      {/$ygData="首页"/}
      {/foreach from = $urlMenu item=um key=key/}
      <li class="submenus">
          {/if $um.table_id|@count neq 0 /}
          <a id="whiteMenu" class="two-tab" href='{/$um.table_id[0].url/}'>{/$um.first_menu/}</a>
          {//if/}
          <ul class="three-tab hide white-menu-three hidden" style="position: fixed;top: 41px;left: 4.1rem;height:100%;overflow:scroll;padding-bottom:45px" data-id="{/$menuinfo.menu_id/}">
            {/foreach from = $urlMenu[0].table_id item=tMenu key=tkey/}
                {/if $table.type neq 2/}
                    <!--三级正常 -->
                    <li  class=''>
                        <a href="/visual/index/{/$tMenu.id/}" data-id="{/$tMenu.id/}" {/if $id === $tMenu.id/}class="menu-active" {//if/}>{/$tMenu.cn_name/}</a>
                    </li>
                {/else/}
                      <!--三级外链 电脑上不显示web-three-tab-->
                      <li  class='web-three-tab'>
                          <a  data-option="{/$table.url/}" class="openurl ygcp" data-id="{/$menuinfo.menu_id/}" data-type="{/$table.type/}"> {/$table.name/} <span class=" glyphicon glyphicon-globe"></span></a>
                      </li>
                {//if/}
              {//foreach/}
          </ul>
      </li>
      {//foreach/}
      <!--收藏-->
      <li class='submenu  {/if $menu_id eq 0  && $url_tpl eq '' && $isWhiteTable !== 1  /}open{//if/}'>
          <a href="#" class="{/if $menu_id eq 0/}
                   {/if $url_tpl eq 'visual'/}
                     iconfont icon-shousuojiahao
                    {/else/}
                     iconfont icon-shousuojianhao
                    {//if/}
               {/else/}
                     iconfont icon-shousuojiahao
               {//if/}">
              <span style='font-weight:bolder'>收藏</span>
          </a>
          <!--收藏子菜单-->
          {/if $collect|@count neq 0 /}
          <ul>
              {/foreach from = $collect item= coll key=key/}
              <li {/if $coll.id eq $id /} class='active'  {/$first_menu = "收藏"/}  {/$second_menu = $coll.name/}{//if/}>

                  <a href="/visual/index/{/$coll.id/}">{/$coll.name/}</a>
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
               {/if $key  eq '垂直业务' && $menu_id eq 0  && $collect|@count eq 0  /}open{//if/}
               {/if $menu_id eq  $secondid/} open{//if/}
             {//foreach/}"
            >
                <a class="{/if $menu_id neq 0 /}
                      {/foreach from = $item key=secondid  item = secondinfo/}
                       {/if $menu_id eq  $secondid/}
                          iconfont icon-shousuojianhao
                       {/else/}
                           iconfont icon-shousuojiahao
                       {//if/}
                      {//foreach/}
                    {/else/}
                        iconfont icon-shousuojiahao
                    {//if/}">
                    <span style='font-weight:bolder'>{/$key/}</span>
                </a>
                <!--二级菜单-->
                <ul>
                  {/foreach from = $item item= menuinfo key= mid/}
                    <!--面包屑赋值-->
                      <li {/if $menu_id eq $menuinfo.menu_id/} class='active' {/$first_menu = $key/}  {/$second_menu = $menuinfo.name/} {//if/}>
                        {/if  $menuinfo.type eq  2/}
                           <!--二级外链 -->
                           <a target='_blank' data-option="{/$menuinfo.url/}" data-type=2>{/$menuinfo.name/}</a>
                        {/else/}
                            <!--二级正常1 -->
                            {/if  $menuinfo.default_id  neq ''/}
                              <a href="/visual/index/menu_id/{/$menuinfo.menu_id/}/id/{/$menuinfo.default_id/}" class="two-tab">{/$menuinfo.name/}</a>
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
                              <a href="/visual/index/menu_id/{/$menuinfo.menu_id/}" class="two-tab">{/$menuinfo.name/}</a>
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
                                              <a  data-option="{/$table.url/}" class="openurl ygcp" data-id="{/$menuinfo.menu_id/}" data-type="{/$table.type/}"> {/$table.name/} <span class=" glyphicon glyphicon-globe"></span></a>
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
      <li class='submenu {/if $url_tpl neq 'tool' && $url_tpl neq '' && $url_tpl neq 'explain'/} open {//if/}  max-hide' >

          <a class="{/if $url_tpl neq '' /}iconfont icon-shousuojianhao{/else/}iconfont icon-shousuojiahao{//if/}">
              <span>管理工具</span>
          </a>
          <ul>
              {/foreach from = $specialMenu item= smenu key=key/}
              <li {/if $url_tpl eq  $smenu.index/}  class="active" {//if/}
              ><a href='{/$smenu.url/}'>{/$smenu.name/}</a></li>
              {//foreach/}
          </ul>
      </li>
      <li class="max-hide"><a href='/visual/toolguider'>常用工具</a></li>
      {//if/}


      <!--常用工具菜单-->
      <!--<li class='submenu {/if $url_tpl neq ''/} open{//if/} ' >
        <a class="{/if $url_tpl neq '' /}iconfont icon-shousuojianhao{/else/}iconfont icon-shousuojiahao{//if/}">
          <span>常用工具</span>
        </a>
        <ul>
          {/foreach from = $commonMenu item= smenu key=key/}
            <li {/if $url_tpl eq  $smenu.index/}  class="active" {//if/}
            ><a href='{/$smenu.url/}'>{/$smenu.name/}</a></li>
          {//foreach/}
        </ul>
      </li>-->
  </ul>

</div>
<div id="menu_hidden" class="max-show web-breadcrumbs" style="display: none;">
    {/if $first_menu/}
        {/if $first_menu=="收藏" || $first_menu=="优供产品"/}
            {/if $isWhiteTable === 0/}
            <a class="first-bread" style="color:#424242;">{/$first_menu/}</a>
            <!--二级面包屑 -->
            <!--收藏-优供 -->
            {/if $second_menu/}
                > <a class="second-bread" style="color:#424242;">{/$second_menu/}</a>
            {/else /}
                {/$ygData/}
            {//if/}
            <span class="third-bread" style="color:#BDBDBD;"></span>
            {/else/}
            <a class="second-bread">{/$second_menu/}</a>
            <span class="third-bread" style="color:#BDBDBD;"></span>
            {//if/}

        {/else/}
            <a class="first-bread" style="color:#424242;">{/$first_menu/}</a>
            <!--正常三级面包屑 -->
            {/if $second_menu/}
                > <a class="second-bread" style="color:#424242;">{/$second_menu/}</a>
            {/else /}
                {/$ygData/}
            {//if/}
             <span class="third-bread" style="color:#BDBDBD;"></span>
        {//if/}
    {/else/}
        <!--优供概要 -->
        <a class="first-bread" style="color:#424242;">{/$ygData/}</a>
        {/if $isWhiteTable === 1 && !empty($urlMenu[0].table_id) /}
            {/foreach from = $urlMenu[0].table_id item= tMenu key=tkey/}
                {/if $tMenu.id === $id/}
                  ><a class="second-bread" style="color:#424242;">{/$tMenu.cn_name/}</a>
                {//if/}
            {//foreach/}
        {//if/}

    {//if/}


</div>
