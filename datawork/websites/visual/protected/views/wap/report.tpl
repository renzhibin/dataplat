{/include file="layouts/wap_header.tpl"/}
{/include file="layouts/wapscript.tpl"/}
<link href="/assets/css/wap.css?version={/$version/}" rel="stylesheet" />
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
  body{ background-color: #fff}
  .form-control{ width: 70%;display: inline-block;}
  .btn-group{ display: none;}
</style>
<div style='height:1px'></div>
{/include file="wap/chip.tpl"/}
{/if $confArr['type']=='4'/}
  {/include file='tooltpl/showreport.tpl'/}
{/else/}
    {/if  $confArr.type eq 3/}
    {/include file="wap/wap_derive.tpl"/}
    {/else/}
    {/include file="wap/wap_normal.tpl"/}
    {//if/}

  {/include file='visualtpl/chart.tpl'/}
{//if/}

{/include file='wap/wap_fixmenu.tpl'/}
<script type="text/javascript">
  $(function(){
     $(window).off('scroll');
     $("#scroll").hide();
  });
</script>

