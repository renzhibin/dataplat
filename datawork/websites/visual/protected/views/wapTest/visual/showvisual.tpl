{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/searchtime.css?version={/$version/}">
<style type="text/css">
  .chartlist{position: relative;}
  .chartlist .chartclose,.chartedit{
     display: none;
  }
  /*浮框可收缩，shadow影响效果，所以去掉*/
  .window-shadow{
      display: none!important;
  }
  /*对比框收缩样式变化*/
  .panel.contrast-flod{
      width: 26px!important;
      height: 100px;
      opacity: 1;
  }

  .contrast-hide{
      opacity: 0;
  }
  .contrast-show{
      width: auto!important;
  }
  .contrast-show .panel-title{
      display: inline-block;
      margin-left: 3px;
  }
  .contrast-show .panel-body{
      height: auto!important;
  }

  .contrast-flod .panel-title{
      margin-left: 2px;
  }
  .window-header.contrast-flod{
      width: 22px!important;
      height: 100px;
      right: 2px;
      border: 0;
  }
  .window-header.contrast-flod .panel-tool{
      top: 85%;
      right: 5px;
  }
  /*适配无菜单页面*/
  @media screen and (min-width: 100px) {
      .navbar-header img{
          width: 78px;
          height: 45px;
      }
      .web-filter {
          display: none;
      }
      .web-title{
          font-size: 18px!important;
          height: auto!important;
          line-height: 20px!important;
      }
      .max-show{
          display: none;
      }
      .muneIcon {
          display: none;
      }

      .navbar-right {
          display: none;
      }

      .shadow {
          box-shadow: 0 1px 6px rgba(0, 0, 0, 0.12), 0 1px 6px rgba(0, 0, 0, 0.12);
          margin-top: 20px;
          padding: 20px 10px;
      }
      .three-mnue .big-input{
          width: 130px!important;
          height: 25px!important;
          line-height: 25px!important;
      }
      .three-mnue .timestyle{
          float: left!important;
          width: auto!important;
      }
  }
  body{ background-color: #fff}

</style>
<div style='height:10px'></div>

{/if $confArr['type']=='4'/}
  {/include file='tooltpl/showreport.tpl'/}
{/else/}
  {/include file='reporttpl/common.tpl'/}
  {/include file='visualtpl/list.tpl'/}
  {/include file='visualtpl/chart.tpl'/}
{//if/}

