{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">
<link rel="stylesheet" type="text/css" href="/assets/css/searchtime.css?version={/$version/}">

<script src="/assets/js/filter.js?version={/$version/}"></script>
<style type="text/css">
  .chartlist{position: relative;}
  .chartlist .chartclose,.chartedit{
     display: none;
  }
  .shadow{
     box-shadow: 0 1px 6px rgba(0, 0, 0, 0.12), 0 1px 6px rgba(0, 0, 0, 0.12);
     margin-top: 20px;
     padding: 20px 10px;
  }
  .framebefore{
      height: 5px;
  }
  /*移动端适配+.three-mnue类名*/
  @media screen and (max-width: 768px) {
      .nav.nav-tabs.three-mnue{
         /* position: fixed;
          left: 5.1rem;
          top: 0;
          width: 180px;
          z-index: 1111;*/
          display: none;
      }
      .nav-tabs.three-mnue>li>a{
          padding: 20px 20px;
          border: none;
          font-size: 14px;
          width: 5rem;
          background-color: #F6FBFF;
          color: #6191d4;
      }
      .nav-tabs.three-mnue>li.active a{
          height: 62px;
          background-image: none;
          background-color: #ea764f;
          color: #fff;
      }
      .nav-tabs.three-mnue>li {
          display: block;
      }
      .framebefore{
          height: 0;
      }
  }

</style>
<script type="text/javascript">
  var menu_id= {/$menu_id/};
  {/if $id neq ''/}
    var id = '{/$id/}';
  {/else/}
    var id =0;
  {//if/}
  {/if $isCollect  eq 'true'/}
  var isCollect = 1;
  {/else/}
  var isCollect = 0;
  {//if/}
  {/if $general neq ''/}
    var general = {/$general/};
  {/else/}
    var general = 0; 
  {//if/}

  var isWhiteTable = {/$isWhiteTable/};
</script>
<div>

  <div id ='right'>
      {/if $allcontent neq '' /}
        <div id="all-content" class="all-content" >
            {/else/}
            {/include file='layouts/menu.tpl'/}
          <div id="content" class="content" >
              {//if/}
    {/if $reportauth eq 'true' /}
      {/if $menu_id eq 0  /}
        {/if $collect|@count eq 0  && $id eq 0  /}
              <!-- 首页移动端需要rightreport节点,填充外链页面-->
              <div  class="rightreport" style="margin-left:-10px">
          <div class='h4show'>
            <h4 > 收藏报表，让您的首页不再空白!</h4>
            <p>问：怎么收藏？&nbsp;&nbsp;答：点击报表页右上角&nbsp;<span class='glyphicon glyphicon-star-empty'></span><span>收藏</span></p>
            <p style='padding-left:30%;text-align:left'></p>
          </div>
        {/else/}
          {/if $id eq 0 /}
          {/else/}
            <div>
            <!--2016-12-23 增加概要数据的三级菜单显示-->
            {/if $isWhiteTable === 1 && !empty($urlMenu[0].table_id) /}
                 <ul class="nav nav-tabs  three-mnue">
                 {/foreach from = $urlMenu[0].table_id item= tMenu key=tkey/}
                   <li
                   style='margin-top:5px;cursor:pointer'
                    {/if $id eq $tMenu.id /}
                        class="active"
                    {//if/}
                    >
                        <a href="/visual/index/{/$tMenu.id/}">{/$tMenu.cn_name/}
                          {/if $id eq $tMenu.id /}
                            &nbsp;<span class='glyphicon glyphicon-question-sign navtab-reportexplain'></span>
                          {//if/}
                        </a>
                    </li>
                   {//foreach/}
                  </div>
              {//if/}



            <!--2016-12-23 结束-->


              {/if $isWhiteTable !== 1/}
              {/foreach from = $collect item= coll key=key/}
                {/if $coll.id eq $id /}
                <li class='active'>
                    <!-- 收藏页面和概要页面显示的tab在移动端隐藏-->
                  <a class="max-hide" href="/visual/index/{/$coll.id/}">{/$coll.name/} &nbsp;<span class='glyphicon glyphicon-question-sign navtab-reportexplain'></span></a>
                </li>
                {//if/}
              {//foreach/}
            </ul>
            {/foreach from = $collect item= coll key=key/}
              {/if $coll.id eq $id /}
              <div>
                 {/if $coll.first_menu neq '' &&  $coll.second_menu neq '' /}
                <span style='padding:3px 0px 3px 10px'>来源菜单：{/$coll.first_menu/}  >> {/$coll.second_menu/} >> {/$coll.name/} </span>
                 {//if/}
              </div>
              {//if/}
            {//foreach/}
            {//if/}
            <div class="framebefore"></div>
            <div  class="rightreport" style="margin-left:-10px">
              {/if $confArr['type'] == '4'/}
                {/include file='tooltpl/showreport.tpl'/}
              {/else/}
                {/include file='reporttpl/common.tpl'/}
              {//if/}
            </div>
          {//if/}
        {//if/}
      {/else/}
              {/if  $allcontent neq 2 /}

              {/if !empty($showTable.table) /}
                   <ul class="nav nav-tabs  three-mnue">
                   {/foreach from = $showTable.table item= tname key=tkey/}
                     <li
                     style='margin-top:5px;cursor:pointer'
                      {/if $id eq $tname.id /}
                          class="active"
                      {//if/}
                      {/if $tname.type  neq 1  /}
                        title ='外部链接' name='openlink'
                      {//if/}
                      >
                       {/if $tname.type  eq 1  /}
                          <a href="/visual/index/menu_id/{/$menu_id/}/id/{/$tname.id/}">{/$tname.cn_name/}
                            {/if $id eq $tname.id /}
                              &nbsp;<span class='glyphicon glyphicon-question-sign navtab-reportexplain'></span>
                            {//if/}
                          </a>
                       {/else if $tname.type  eq 3  /}
                           <a href="/visual/index/menu_id/{/$menu_id/}/id/{/$tname.id/}" id="{/$tname.id/}" menu-id="{/$menu_id/}">{/$tname.cn_name/}
                             &nbsp;<span class='glyphicon glyphicon-globe'></span>
                           </a>
                       {/else/}
                          <!-- <span class='glyphicon glyphicon-globe'></span> -->
                          <a data-option="{/$tname.url/}" class="openurl" menu-id="{/$menu_id/}">
                          <span>{/$tname.cn_name/}</span>&nbsp;<span class='glyphicon glyphicon-globe'></span></a>

                     {//if/}
                    </li>
                 {//foreach/}
                </ul>
         {//if/}
              {//if/}
        <div class="framebefore"></div>
        <div class="rightreport" style="margin-left:-10px">
          {/if $confArr['type'] == '4'/}
                {/include file='tooltpl/showreport.tpl'/}
          {/else if $confArr['type'] == '9'/}
                {/include file='reporttpl/openurl.tpl'/}
          {/else/}
                {/include file='reporttpl/common.tpl'/}
          {//if/}
        </div>
      {//if/}
    {/else/}
       <h4 class='h4show'>您没有权限</h4>
    {//if/}

    </div>
    <!--处理报表标题-->
    <script>
      var titleObj =$("#content").children('ul');
      if(titleObj.length >0){
          //点击tab，设置title，外链等页面需要设置，所以统一设置了
           titleObj.each(function(){
              if($(this).children('li').hasClass('active')){
                  document.title= $.trim($(this).children('.active').text())+ "-趣店数据分析平台";
              }else{
                  document.title=$.trim($(this).children().eq(0).text())+"-趣店数据分析平台";
              }
          });
      }
      $(document).ready(function(){
          $('.filter .btnSearch').click();
      })
      //$('[name=openlink]').tooltip({ 'position':'top'});
    </script>
  </div>
</div>
{/include file='layouts/menujs.tpl'/}
{/include file='visualtpl/list.tpl'/}
{/include file='visualtpl/chart.tpl'/}
{/include file="layouts/footer.tpl"/}

{/if $allcontent neq '' /}
  <script>
      var allcontent = {/$allcontent/};
  </script>
{/else/}
  <script>
      var allcontent = false;
  </script>
{//if/}
