{/include file="layouts/wap_header.tpl"/}
<link href="/assets/css/wap.css?version={/$version/}" rel="stylesheet" />
	{/include file="wap/chip.tpl"/}
	<ul class="list-nav">
		{/foreach from =$reportList  item = item  key = key  /}
			<li>
				<i class="glyphicon glyphicon-chevron-right"></i>
				{/if $item.type  eq 1  /}
                  <a href="/wap/report/{/$item.id/}">
					<span>{/$item.cn_name/}</span>
					<span class='rightnav'></span>
				</a>
               {/else/}
                  <a href="{/$item.url/}" target="_blank" class="openurl">
                  	<span>{/$item.cn_name/}</span>
                  	<span class='rightnav'></span>
                  </a>
                {//if/}
			</li>
		{//foreach/}
	</ul>

	<div class="clearfix"></div>
</body>
