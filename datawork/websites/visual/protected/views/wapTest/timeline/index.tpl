{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">

<div>
  {/include file="layouts/menu.tpl"/}
  <div id='right'>
    <div id="content" class="content" >
        <div id="breadcrumbs-one">
            {/foreach from = $guider item= place key=key/}
            {/if $guider[0] eq $place /}
            <span><a href="{/$place.href/}">{/$place.content/}</a></span>
            {/else/}
            {/if $place.href eq '#'/}
            <span>></span><span>{/$place.content/}</span>
            {/else/}
            <span>></span><span><a href="{/$place.href/}">{/$place.content/}</a></span>
            {//if/}
            {//if/}
            {//foreach/}
        </div>
		<div style='height:10px'></div>
		<div class='container'>
		  <form action="/Addition/showtimeline" id='formproject' method="post" >
			  <div class="panel panel-info">
				  <div class="panel-heading">
						<span>项目名称：</span>  
					 	<select name='event_id' style="width:200px">
					      <option value='filter_not'>--请选择--</option>
					      {/foreach from =$project item =item key=key/}
					         <option value='{/$item.event_id/}' 
					         {/if $event_id eq $item.event_id /} selected="selected"{//if/}>{/$item.event_name/}</option>
					      {//foreach/}
					    </select>
				    <a class="btn btn-primary btn-xs" href="/addition/editorTimeline?event_id={/$event_id/}">编辑</a>
                    <a class="btn btn-default btn-xs pull-right" href="/addition/editorTimeline?event_id=0">添加</a>
				  </div>
				  <div class="panel-body" style='padding:5px'>
				  	<div id="timeline-embed"></div>
				  </div>
			  </div>
		  </form> 
		</div>

    </div>
  </div>
</div>
{/include file="layouts/menujs.tpl"/}
<script type="text/javascript">
	$(function(){
		$('select[name=event_id]').on('change',function(){
			 if($(this).val() !='filter_not'){
			 	$('#formproject').submit();
			 }else{
			 	$.messger.alert('提示','未选择项目','info');
			 }
		});
	});
	$('select').select2();
    var timeline_config = {
        width:              '100%',
        height:             '600',
        source:             '/addition/getTimelinejson?event_id={/$event_id/}',
        embed_id:           'timeline-embed',               //OPTIONAL USE A DIFFERENT DIV ID FOR EMBED
        start_at_end:       true,                          //OPTIONAL START AT LATEST DATE
        start_at_slide:     '0',                            //OPTIONAL START AT SPECIFIC SLIDE
        start_zoom_adjust:  '0',                            //OPTIONAL TWEAK THE DEFAULT ZOOM LEVEL
        hash_bookmark:      true,                           //OPTIONAL LOCATION BAR HASHES
        font:               'SansitaOne-Kameron',             //OPTIONAL FONT
        debug:              false,                           //OPTIONAL DEBUG TO CONSOLE
        lang:               'zh-cn',                        //OPTIONAL LANGUAGE
        maptype:            'watercolor',                   //OPTIONAL MAP STYLE
        css:                '../../assets/lib/timeline/css/timeline.css',     //OPTIONAL PATH TO CSS
        js:                 '../../assets/lib/timeline/js/timeline-min.js'    //OPTIONAL PATH TO JS
    }
</script>
<script type="text/javascript" src="/assets/lib/timeline/js/storyjs-embed.js"></script>