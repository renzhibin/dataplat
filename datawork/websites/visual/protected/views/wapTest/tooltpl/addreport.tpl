{/include file="layouts/header.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">
<div>
  {/include file="layouts/menu.tpl"/}
   <div id='right'>
   	<div id="content" style='background-color:#fff;position:realtive;min-height:630px' >
   	<div style='height:20px'></div>
   	<div id="toolreportbox">
	<form class="form-horizontal toolreport">
		<div class="form-group">
			<label class="col-sm-2 control-label">离线查询工具名称（<span class="redcolor">*</span>）: </label>
			<div class="col-sm-6">
				<input type="text" name="cn_name" value="" class="cn_name" />
			</div>
			<div class="col-sm-4">
				<label class="error_msg error_name">请输入离线查询工具名称</label>
			</div>
		</div>
		<!-- <div class="form-group">
			<label class="col-sm-2 control-label">报表英文名称（<span class="redcolor">*</span>）: </label>
			<div class="col-sm-6">
				<input type="text" name="cn_name" value="" class="cn_name" />
			</div>
			<div class="col-sm-4">
				<label class="error_msg error_cn_name">请输入报表英文名称</label>
			</div>
		</div> -->
		<div class="form-group">
			<label class="col-sm-2 control-label">注释: </label>
			<div class="col-sm-6">
				<input type="text" name="explain" value="" class="explain" />
			</div>
			<div class="col-sm-4">
				<label class="error_msg error_explain"> &nbsp; </label>
			</div>
		</div>
	</form>

	<form class="form-horizontal toolreport">
		<div class="form-group">
			<!-- <div class="col-sm-12"> -->
				<label class="col-sm-2 control-label">hql (<span class="redcolor">*</span>): </label>
				<div class="col-sm-6">
					<textarea name="hql" class="hql" style="resize:none"; placeholder="select [x as ] a,b,c,d,e,f,g from ( 子查询) x"></textarea>
				</div>
				<div class="col-sm-4">
					<button class="btn btn-primary hqlAnalyse" style="display:block;margin-top:3px">hql解析</button>
				</div>
		</div>

		<div class="form-group hqlcontent">
				<label class="col-sm-2 control-label">解析信息：</label>	
				<div class="col-sm-9">
					<table class="hqltable table table-bordered table-condensed"> 
						<thead><tr style="background:#eee;text-align:center"><td style="width:45%">name</td><td>cn_name</td></tr></thead>
						<tbody>
						
						</tbody>
					</table>
					<label class="col-sm-1">&nbsp;</label>	
			</div>
		</div>

	</form>

	<form class="form-horizontal savebox">
		<label class="col-sm-2 control-label">&nbsp;</label>	
		<div class="col-sm-9">
			<button class="btn btn-primary toolsaveinfo" style="display:block;margin-top:3px">确定</button>
		</div>
	</form>
	</div>
  	</div>
  </div>
</div>
{/include file="layouts/menujs.tpl"/}
<script type="text/javascript" src="/assets/js/toolreports.js?version={/$version/}"></script>
<script type="text/javascript">
	var user_name = '{/Yii::app()->user->username/}';
	var params=null, isEdit = "0";
	{/if ($params)/}
	    params = {/$params/};
	    isEdit = params.isEdit;
	{//if/}

	$(document).ready(function(){
		var toolreports = new ToolReport({'boxtag':'#toolreportbox','params':params,'isEdit':isEdit});
			toolreports.bindEvent();
	});

</script>

