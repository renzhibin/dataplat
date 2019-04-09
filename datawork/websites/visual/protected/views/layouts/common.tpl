{/include file="../../protected/views/layouts/new_header.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">
<div class='main'>
  <div class='left'>
  {/include file="../../protected/views/layouts/menu.tpl"/}
  {/include file="../../protected/views/layouts/menujs.tpl"/}
  </div>
  <div class='right' style='margin-left:215px;'>
      {/block name="main"/}{//block/}
  </div>
</div>
<!-- 添加水印的功能 -->
<script type="text/javascript" src="/assets/js/watermark.js"></script>
<script type="text/javascript">
	 window.watermark({
	    'txt': '{/Yii::app()->user->username/}', // 水印文案，默认为“机密数据，请勿外传”，推荐填写当前登录用户的邮箱
	    'selector': 'body', // 需要添加水印的选择器，默认为“.page-content” ，根据实际需要添加水印的元素选择器填写
	    'isForce': true // 是否强制添加水印，如果是，则会删除tr元素的背景等操作
    })
</script>