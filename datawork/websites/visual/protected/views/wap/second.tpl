{/include file="layouts/wap_header.tpl"/}
<link href="/assets/css/wap.css?version={/$version/}" rel="stylesheet" />
	{/include file="wap/chip.tpl"/}
	<ul class="list-nav">
		{/foreach from = $menuInfo  item = item  key = key  /}
			<li>
				<i class="glyphicon glyphicon-chevron-right"></i>
				<a href="/wap/reportlist?menu_name={/$menu_name/}&menu_id={/$item.menu_id/}">
					<span>{/$item.name/}</span>
					<span class='rightnav'></span>
				</a>
			</li>
		{//foreach/}
	</ul>
	<div class="clearfix"></div>
</body>
