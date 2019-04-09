	<div class='container' style="width:99%;padding-top:15px;">
			<form class="form-horizontal toolreport">
				<div class="form-group">
					<label class="col-sm-2 text-right">离线查询工具名称: </label>
					<div class="col-sm-6">{/$confArr['cn_name']/}</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 text-right">开始时间: </label>
					<div class="col-sm-3">
						<input type="text" name="starttime" value="" class="form-control datepicker"  />
					 </div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 text-right">结束时间: </label>
					<div class="col-sm-3">
						<input type="text" name="endtime" value="" class="form-control datepicker" />
					 </div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 text-right">查询内容: </label>
					<div class="col-sm-6 textareastr">
						<textarea name="pramsreplace" id="text1" style="height:120px;" placeholder="一行一个,例如:"></textarea>
					 </div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 text-right">数据接收人: </label>
					<div class="col-sm-6 emaillistbox">
						<textarea name="emaillist" id="text2" style="height:120px;" placeholder="公司邮箱前缀以逗号隔开"></textarea><br/>
						<span style="color:#B1AEAE;">数据生成后，会自动邮件到数据接收人<br/>
							格式：公司邮箱前缀 例如:sanzhang,lili,xiaowang</span>
					 </div>

				</div>
				<div class="form-group">
					<label class="col-sm-2 text-right">注释: </label>
					<div class="col-sm-6">{/$confArr['explain']/}</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 text-right">&nbsp;</label>
					<div class="col-sm-9">
						<button class="btn btn-primary taskinfo" style="display:block;margin-top:3px" data-id="{/$confArr['id']/}">提交</button>
					</div>
				</div>
			</form>
		</div>

<style type="text/css">

	.textareastr textarea[name="pramsreplace"]::-webkit-input-placeholder:after{
		  display:block;
		  content:"aa \A bb";/*  \A 表示换行  */
		  color:#bcbcbc;
	};
	.textareastr textarea[name="pramsreplace"]::-moz-placeholder:after{
		  content:"aa \A bb";/*  \A 表示换行  */
		  color:#bcbcbc;
	};
	textarea[name="emaillist"]::-webkit-input-placeholder:after{
		  display:block;
		  content:"qiangli \A sanzhang";/*  \A 表示换行  */
		  color:#bcbcbc;
	};
	textarea[name="emaillist"]::-moz-placeholder:after{
		  content:"qiangli \A sanzhang";/*  \A 表示换行  */
		  color:#bcbcbc;
	};


</style>
<script type="text/javascript">
	var user_name = '{/Yii::app()->user->username/}';
	var webapi = '{/$WEB_API/}';
</script>
<script type="text/javascript">
	$(document).ready(function(){
		var dd = new Date();
		var m = dd.getMonth() + 1 < 10 ? "0" + (dd.getMonth() + 1) : dd.getMonth() + 1;
 		var d = dd.getDate() < 10 ? "0" + dd.getDate() : dd.getDate();
		var today = dd.getFullYear()+'-'+m+'-'+d;
		$('input[name="starttime"], input[name="endtime"]').val(today);

		$('.taskinfo').bind('click',function(){
		 	var pramsreplace = $('.toolreport textarea[name="pramsreplace"]').val(),
		 		emaillist = $('.toolreport textarea[name="emaillist"]').val(),
		 		starttime = $('.toolreport input[name="starttime"]').val(),
		 		endtime = $('.toolreport input[name="endtime"]').val(),
		 		id = $(this).attr('data-id');

		 	if(starttime == ''){
		 		$.messager.alert('提示','开始时间不能为空','info');
		 		return false;
		 	}
		 	if(endtime == ''){
		 		$.messager.alert('提示','结束时间不能为空','info');
		 		return false;
		 	}


		 	var start_timestamp = Date.parse(new Date(starttime));
		 	var end_timestamp = Date.parse(new Date(endtime));
		 	if(start_timestamp > end_timestamp){
		 		$.messager.alert('提示','开始时间不能大于结束时间','info');
		 		return false;
		 	}


		 	var days = GetDateDiff(starttime,endtime,'day');
		 	if(days >30){
		 		$.messager.alert('提示','请选择30天之内的时间段','info');
		 		return false;
		 	}

		 	if(pramsreplace == ''){
		 		$.messager.alert('提示','查询内容不能为空','info');
		 		return false;
		 	}
		 	if(emaillist == ''){
		 		$.messager.alert('提示','数据接收人不能为空','info');
		 		return false;
		 	}


		 	var data = {"id":id,"pramsreplace":pramsreplace,"emaillist":emaillist,"starttime":starttime,"endtime":endtime};
		 	$.ajax({
			   type: "POST",
			   url: '/tool/GetDataReport',
			   'data': data,
			   datatype:"JSON",
			   success: function(result){
			   		if(result == "null") { console.log('result:'+result);return false; }
			   		var results = JSON.parse(result);
			   		if(results.status==0){
			   			var url = results.WEB_API+'/get_run_detail?serial='+results.data['serial']+'&app_name='+results.data['app_name']+'&stat_date='+results.data['stat_date']+'&module_name='+results.data['module_name'];
			   			window.location.href=url;

			   			return false;
			   		} else {
			   			$.messager.alert('提示',results.msg,'info');
			   			return false;
			   		}

			   },
			   error: function(){
			   		w.close();
			   		$.messager.alert('提示','服务器连接失败','info');
			   		return false;
			   }
			});

		 	return false;
		 });
	});

	function GetDateDiff(startTime, endTime, diffType) {

            //将xxxx-xx-xx的时间格式，转换为 xxxx/xx/xx的格式

            startTime = startTime.replace(/\-/g, "/");

            endTime = endTime.replace(/\-/g, "/");

            //将计算间隔类性字符转换为小写

            diffType = diffType.toLowerCase();

            var sTime = new Date(startTime);      //开始时间

            var eTime = new Date(endTime);  //结束时间

            //作为除数的数字

            var divNum = 1;

            switch (diffType) {

                case "second":

                    divNum = 1000;

                    break;

                case "minute":

                    divNum = 1000 * 60;

                    break;

                case "hour":

                    divNum = 1000 * 3600;

                    break;

                case "day":

                    divNum = 1000 * 3600 * 24;

                    break;

                default:

                    break;

            }

            return parseInt((eTime.getTime() - sTime.getTime()) / parseInt(divNum));

        }

</script>
