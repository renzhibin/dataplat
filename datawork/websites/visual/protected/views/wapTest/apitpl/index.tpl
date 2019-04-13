{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">
<style type="text/css">
	.text-right{ text-align: right;}
	.appname { width: 100%; height: 26px; border:1px solid #aaa; text-indent: 12px; }
	input[type="button"] { line-height: 12px; font-size: 12px; }
	/*2015-04-21 api开发者中心自定义 */
	.bs-callout {
	  padding: 20px;
	  margin: 20px 0;
	  border: 1px solid #eee;
	  border-left-width: 5px;
	  border-radius: 3px;
	}
	.bs-callout-danger {
	  border-left-color: #d9534f;
	}
	.tab-con{ padding-top: 15px; }
	.error_msg { color: red; display: none; }
	.li-panel-box { margin:0 0 15px 0; }
	.li-panel-head { height: 28px; line-height: 28px; font-weight: bold; color: #000; background: #eee; text-indent: 24px;  border-bottom: none;  }
	.li-panel-con{ padding: 10px; border: 1px solid #eee; }
	.reportlistbox { margin-top:15px; display:none; }
	.applist button.checked, .highlight span.checked { background:#337ab7; color:#fff; border: #337ab7; }
	table th {background: #eee; }
	table th, table td {text-align: center;}
	.highlight  pre {padding: 15px 37px 15px 20px; color: #5c5c5c;}
	.highlight  pre code { font-size: 12px; color:#337ab7 ; }
	.highlight { position: relative; }
	.btn-clipboard {
	  position: absolute;
	  top: 23px;
	  right: 0px;
	  z-index: 10;
	  display: block;
	  padding: 5px 8px;
	  font-size: 12px;
	  color: #767676;
	  cursor: pointer;
	  background-color: #fff;
	  border: 1px solid #e1e1e8;
	  border-radius: 0 4px 0 4px;
	}
	h5 { font-size: 12px; }

	/* modal css 弹窗样式 */

	/*.window { min-width: 200px; background: #fff; border: 1px solid #e5e5e5; border-radius: 5px }
	.window-shadow { border-radius: 5px; box-shadow: 0 5px 15px rgba(0,0,0,.5); }
	.window .window-header {border-bottom: 1px solid #e5e5e5; padding-left: 15px; }
	.panel-header,.panel-title { height: 36px; line-height: 36px; font-size: 16px; font-weight: bold; }
	.window .window-body{ padding: 15px; background: #fff; border:none;  border-bottom: 1px solid #e5e5e5;}
	.window-mask{background: #000;}
	.dialog-button { padding: 15px; text-algin:right; border: none; background: none; }
	.l-btn { height:28px; background: none; border: 1px solid #e5e5e5; border-radius: 4px; color:#000 }
	.l-btn-left { padding: 0px 12px; }
	.l-btn-text { line-height: 28px; }
	.dialog-button .l-btn:first-child{background-color: #337ab7; color: #fff; };
	.dialog-button .l-btn .l-btn-icon-left .l-btn-text { margin: 0;}
	.contBox { font-size: 12px; text-align: left; }*/
</style>
<div>
  {/include file="layouts/menu.tpl"/}
   <div id='right'>
	   <div id="content" style='background-color:#fff;position:realtive;min-height:630px' >

		<div style='height:10px'></div>
		<div class='container' style="width:99%">
			<div class="bs-callout bs-callout-danger">
		      <h4>公告</h4>
		      <p>使用步骤：1、输入应用名称获取token   2、申请项目   3、选择报表获取url </p>
		      <p><span style="color:#716868">注：申请过的应用在应用列表 标签页显示，点击查看项目, 项目列表方可显示相应的数据<br/>为了保证信息安全，请不要将<code>应用Token</code>敏感信息泄露给其它人，谢谢合作！</span></p>
		    </div>

		    <ul class="nav nav-tabs" id="myTab" role="tablist">
		    <li role="presentation" class="active"><a href="#apply" aria-controls="apply" role="tab" data-toggle="tab">申请应用</a></li> 
		    <li role="presentation"><a href="#applistbox" aria-controls="applistbox" role="tab" data-toggle="tab">应用列表</a></li>
		    <li role="presentation"><a href="#projectlistbox" aria-controls="projectlistbox" role="tab" data-toggle="tab">项目列表</a></li>
		    
		  </ul>
		  <!-- Tab panes -->
		  <div class="tab-content">
		  	<!-- 申请应用 -->
		    <div role="tabpanel" class="tab-pane tab-con active" id="apply">
				<form class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-2 control-label">应用名称: </label>
						<div class="col-sm-4">
							<input type="text" name="appname" value="" class="appname" />
						</div>
						<div class="col-sm-4">
							<input type="button" name="appkeyBtn" class="btn btn-primary" value="获取token" />
							<label class="error_msg error_appmsg">请输入应用名称</label>
						</div>
						<input type="hidden" class="getapptoken" value=""  />
					</div>
					<div class="form-group" style="display:none">
						<label class="col-sm-2 control-label">应用token: </label>
						<div class="col-sm-4">
							<pre><code><span id="apptoken"></span></code></pre>
							<!--<input type="button" class="btn btn-primary" value="获取密钥" />-->
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">项目名称: </label>
						<div class="col-sm-4">
							<select name="project" errortag='err_projectmsg' style="width:100%">
							<!-- <option value="data">data</option> -->
							{/foreach from = $project item = item key=key/}
					             <option value='{/$item.project/}'>{/$item.cn_name/}</option>
					        {//foreach/}
							</select>
						</div>
						<div class="col-sm-4">
							<input type="button" name="projectBtn" class="btn btn-primary projectBtn" value="申请项目" />
							<label class="error_msg err_projectmsg">请输入应用名称</label>
						</div>
					</div>
					<div class="form-group ckreport" style="display:none">
						<label class="col-sm-2 control-label">报表名称:</label>
						<div class="col-sm-4">
							<select name="reportlist" class="reportlistselect" style="width:100%">
							<option value="-1">请选择</option>
							</select>
						</div>
						<div class="col-sm-4">
							<input type="button" class="btn btn-primary applygeturl" value="获取url链接" />
							<label class="error_msg err_reportmsg"></label>
						</div>
					</div>
				</form>
				<div class="row">
					<label class="col-sm-1"></label>
					<div class="reportlistbox col-sm-10 applyreportlistbox">
						<div class="li-panel-head">报表url</div>
						<div class="li-panel-con" id="applylisturl">
							<!-- <div class="highlight">
								<div class="zero-clipboard"><span class="btn-clipboard btn-clipboard-hover">复制</span></div>
								<h5>343</h5>
								<pre><code>eerrer</code></pre>
							</div> -->
						</div>
					</div>
			</div>

		    </div>
		    <!-- 应用列表 -->
		    <div role="tabpanel" class="tab-pane tab-con" id="applistbox">
		    	<!-- 应用列表 -->
		    	<div class="li-panel-box applistbox">
			    	<table class="table table-bordered applist">
			    		<tr><th>应用ID</th><th>应用名称</th><th>应用token</th><th>操作</th></tr>
					</table>
				</div>
			</div>

			<!-- 项目列表 -->
		    <div role="tabpanel" class="tab-pane tab-con" id="projectlistbox">
				<div class="li-panel-box prolistbox">
					<table class="table table-bordered projectlist">
						<tr><th>序号</th><th>项目名称</th><th>应用名称</th><th>操作</th></tr>
					</table>
				<!--  报表列表 -->
				<div class="reportlistbox reportlistcon">
					<div class="li-panel-head">报表url</div>
					<div class="li-panel-con" id="listurl">
						<!-- <div class="highlight">
							<div class="zero-clipboard"><span class="btn-clipboard btn-clipboard-hover">复制</span></div>
							<h4></h4>
							<pre><code></code></pre>
						</div> -->
					</div>
				</div>

			</div>

		    </div>

		  </div>
		</div>

	   </div>
  </div>
</div>
{/include file="layouts/menujs.tpl"/}
<!-- Modal 添加项目-->
<div id="myModalpro" style="padding: 20 10px; overflow:hidden;">
	<div class="form-group">
	<label class="col-sm-3 control-label">项目名称：</label>
	<div class="col-sm-9">
		<select name="addproject" class="addproject" style="height:24px" errortag='error_modalpromsg'>
				{/foreach from = $project item = item key=key/}
		             <option value='{/$item.project/}'>{/$item.cn_name/}</option>
		        {//foreach/}
		</select> 
	</div>
	<div class="col-sm-12 error_msg error_modalpromsg" style="padding-top:10px"></div>
	<input type="hidden" class="modalProApp" value="" />
	</div>   
	 
</div>

<!-- Modal 添加报表-->
<div id="myModalreport" style="padding: 20 10px; overflow:hidden;">
	<div class="form-group">
	<label class="col-sm-3 control-label">报表名称：</label>
	<div class="col-sm-9">
		<select name="addreport" class="addreport" style="height:24px" errortag='error_modalreportmsg'>
				
		</select> 
	</div>
	<div class="col-sm-12 error_msg error_modalreportmsg" style="padding-top:10px"></div>
	<input type="hidden" class="modalPeportval" value="" />
	</div>   
	 
</div>
<script type="text/javascript">
	var user_name = '{/Yii::app()->user->username/}';
</script>
<script type="text/javascript" src="/assets/js/api.js"></script>