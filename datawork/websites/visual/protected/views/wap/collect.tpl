{/include file="layouts/wap_header.tpl"/}
<link href="/assets/css/wap.css?version={/$version/}" rel="stylesheet" />
  {/include file="wap/chip.tpl"/}
	<ul class="list-nav">
		{/foreach from = $collect item= coll key=key/}
		<li {/if $coll.id eq $id /} class='active'{//if/}>
		  <a href="/wap/report/{/$coll.id/}">{/$coll.name/}</a>
		</li>
		{//foreach/}
	</ul>
	<div class="clearfix"></div>
</body>
