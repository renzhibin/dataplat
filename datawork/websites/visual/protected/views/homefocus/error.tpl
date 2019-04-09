{/include file="homefocus/header.tpl"/}
<style type="text/css">
    .txt-center{ text-align: center;}
    .errorbox { margin-top: 35px;  font-size: 14px; font-weight: normal;}
    .redcolor { color: red; }
    .content-header h1{
        text-align: center;
    }
</style>
<div id="content">
    <!--<div id="content-header">
        <h1>错误提示</h1>
    </div>-->
    <div class="container-fluid errorbox txt-center" >

        <div><b class="redcolor">提示: </b>{/$msg/}, 请返回<a href="/AppHomefocus/index">首页</a>
    </div>
</div>
{/include file="homefocus/footer.tpl"/}