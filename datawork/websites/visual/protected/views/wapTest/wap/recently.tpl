{/include file="layouts/wap_header.tpl"/}
<link href="/assets/css/wap.css?version={/$version/}" rel="stylesheet" />
	{/include file="wap/chip.tpl"/}
	<ul class="list-nav">
		{/foreach from = $recently item= recent key=key/}
		{/if $recent.report_name neq '' /}
		<li {/if $recent.id eq $id /} class='active'{//if/}>
			<i class="glyphicon glyphicon-chevron-right"></i><a href="/{/$recent.user_action/}">{/$recent.report_name/}</a>
		</li>
		{//if/}
		{//foreach/}
	</ul>
	<div class="clearfix"></div>
</body>
