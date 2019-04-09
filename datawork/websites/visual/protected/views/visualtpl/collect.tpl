<a class='collnet pull-right' target='_blank' href=''>
   <i class='glyphicon glyphicon-question-sign'></i><span>wiki</span>
</a>
<span class='collnet collclick pull-right'>
	{/if $menu_id eq 0 &&  $general eq ''/}
    <i class='glyphicon glyphicon-star'></i><span>收藏</span>
  {/else if $isCollect  eq  'true' /}
  	<i class='glyphicon glyphicon-star'></i><span>收藏</span>
  {/else/}
    <i class='glyphicon glyphicon-star-empty'></i><span>收藏</span>
  {//if/}
</span>
<span class='collnet downclick pull-right'>
    <form method='post' action='/visual/downData'   id='downData' >
        <input type='hidden' name='downConfig' value=''/>
         <i class='glyphicon  glyphicon-save'></i><span>下载</span>
    </form>
</span>
