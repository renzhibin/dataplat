{/include file="layouts/wap_header.tpl"/}
<link href="/assets/css/wap.css?version={/$version/}" rel="stylesheet" />
	<ul class="list-nav first-nav row">
		{/foreach from = $urlMenu item=um key=key/}
		<li class="col-xs-6 col-md-4">
			<a href="/wap/report/{/$um.table_id[0].id/}">
				<span>{/$um.first_menu/}</span>
				<span class='rightnav'></span>
			</a>
		</li>
		{//foreach/}
		<li class="col-xs-6 col-md-4">
			<a href="/wap/collect">
				<span>收藏</span>
				<span class='rightnav'></span>
			</a>
		</li>
		<li class="col-xs-6 col-md-4">
			<a href="/wap/recently">
				<span>最常访问</span>
				<span class='rightnav'></span>
			</a>
		</li>
		{/foreach from = $menuTitle  item = item  key = key  /}
			<li class="col-xs-6 col-md-4">
				<a href="/wap/SecondMenu?menu_name={/$key/}">
					<span>{/$key/}</span>
					<span class='rightnav'></span>
				</a>
			</li>
		{//foreach/}
	</ul>
	<div class="clearfix"></div>
</body>
