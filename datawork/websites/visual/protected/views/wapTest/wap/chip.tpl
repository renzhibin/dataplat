  {/if $guider|@count neq 0 /}
  <!--面包屑效果-->
  <ol class="breadcrumb wapbreadcrumb">
    {/foreach from = $guider item= place key=key/}
    {/if $guider[0] eq $place /}
    <li><a href="{/$place.href/}">{/$place.content/}</a></li>
    {/else/}
    {/if $place.href eq '#'/}
    <li>{/$place.content/}</li>
    {/else/}
    <li><a href="{/$place.href/}">{/$place.content/}</a></li>
    {//if/}
    {//if/}
    {//foreach/}
  </ol>
  {//if/}

