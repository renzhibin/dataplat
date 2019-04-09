//生成条件html
function createElement(configData){
 	 var html ='<div class="row" style="margin:0px;background-color:#eee;padding:5px 0px 5px 10px;" >';
	 for(var i in  configData ){
 		  switch(configData[i].type){
			  case 'hidden':
				  html +="<input type='hidden'  name='"+configData[i].key+"'";
				  if(configData[i].value !='filter_not'){
					  html +="value='"+configData[i].value+"'";
				  }
				  html +="/>";
				  break;

			case 'input':
  				html +="<div class='divbox'>";
  				html +="<span style=''>"+configData[i].title+"：</span>";
  				if(configData[i].key =='starttime' || configData[i].key =='endtime'){
  					 html +="<input type='text' name='"+configData[i].key+"'";
  					 html +="style='display:inline-block;width:130px'";
  					 if(configData[i].value !='filter_not'){
  					 	html +="value='"+configData[i].value+"'";
  					 }	
  					 html +="class='datepicker'/>";				  
 				}else{
 					 html +="<input type='text'  name='"+configData[i].key+"'";
  					 html +="style='display:inline-block'";	
  					 if(configData[i].value !='filter_not'){
  					 	html +="value='"+configData[i].value+"'";
  					 } 
  					 if(configData[i].special  !=undefined){
  					 	if( configData[i].special  >1){
  					 		html +="class='secondTimebox'/>";
  					 	}else{
  					 		html +="class='datepicker'/>";
  					 	}
  					 }else{
  					 	html +="/>";
  					 }
  					 
 				}
 				html +='</div>';
				break;   
			case 'select':
				html +="<div class='divbox'>";
				html +="<span style='text-align:center;display:inline-block'>"+configData[i].title+"：</span>";
				html +="<select name="+configData[i].key+" style='width:130px;' class='selectChange'>";
				html +="<option value='filter_not'  selected ='selected' >--请选择--</option>";
 				for( var j in  configData[i].data_source){
					if(configData[i].value !=undefined){
					    if(configData[i].data_source[j]  == configData[i].value){
							html +="<option value='"+configData[i].data_source[j]+"' selected ='selected'>"+configData[i].data_source[j]+"</option>";
						}else{
							html +="<option value='"+configData[i].data_source[j]+"'>"+configData[i].data_source[j]+"</option>";
						}
					}else{
						html +="<option value='"+configData[i].data_source[j]+"'>"+configData[i].data_source[j]+"</option>";
  					}
 				}
				html +='</select>';
				html +='</div>';
 				break; 
 			case 'textarea':
				html +="<div class='col-md-3' style='margin-bottom:1px'>";
				html +="<span style='text-align:right;display:inline-block'>"+configData[i].title+"：</span>";
				html +="<textarea name="+configData[i].key+" >";
				if(configData[i].value !='filter_not'){
  					html += configData[i].value;
  				} 
  				html +='</textarea>';
				html +='</div>';
 				break;
		  }
	 }
	if(configData.length >0 &&  configData[0].key !="rsv_pq"){
		 html +="<div style='padding-left:3px;float:left'>"
		 html +='<button type="submit" class="btn btn-def btn-sm btnSearch" style="padding:2px 6px">查询</button>';
		 html +="</div>";
		 html +='</div>';
	 }else{
	 	 html ='';
	 }
	 return html;
 }

