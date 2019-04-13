{/include file="layouts/header.tpl"/}
<style type="text/css">
  .content-header h1{
    text-align: center;
  }
</style>
<div id="content">
  <div id="content-header">
    <div id="breadcrumb"></div>
    <h1>Error</h1>
  </div>
  <div class="container-fluid">
    <div class="row-fluid">
      <div class="span12">
        <div class="widget-box">
          <div class="widget-content">
            <div class="error_ex">
              {/foreach from = $msg item= p key=key/}
              <p class="h3">{/$p/}</pclass>
              {//foreach/}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
{/include file="layouts/footer.tpl"/}