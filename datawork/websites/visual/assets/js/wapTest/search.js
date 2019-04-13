function createNewElement(configData, tableObj){
 	 var html ='<div class="row web-filter-content" style="margin:0px;background-color:#F6F6F6;padding:5px 0px 2px 10px;">';
	 for(var i in  configData ){
	 	  var nameKey = configData[i].key.split(".").join("_");
 		  switch(configData[i].type){
			  case 'hidden':
				  html +="<input type='hidden'  name='"+nameKey+"'";
				  if(configData[i].value !='filter_not'){
					  html +="value='"+configData[i].value+"'";
				  } else if(configData[i].defaultsearch != undefined && configData[i].defaultsearch!=''){
  					html += "value='"+configData[i].defaultsearch+"'";
  				  }
				  html +="/>";
				  break;

			case 'input':
  				html +="<div class='divbox'>";
  				if( configData[i].is_accurate  !=undefined && configData[i].is_accurate  ==1 ){
  					
  					html +="<span>"+configData[i].title+"：</span>";
  				}else{
  					html +="<span style=''>"+configData[i].title+"：</span>";
  				}
  				
  				if(configData[i].key =='starttime' || configData[i].key =='endtime'){
  					 html +="<input type='text' name='"+nameKey+"'";
  					 html +="style='display:inline-block;width:130px'";
  					 if(configData[i].value !='filter_not'){
  					 	html +="value='"+configData[i].value+"'";
  					 } else if(configData[i].defaultsearch != undefined && configData[i].defaultsearch!=''){
  						html += "value='"+configData[i].defaultsearch+"'";
  				  	 }	
  					 html +="class='datepicker'/>";				  
 				}else{
 					 html +="<input type='text'  name='"+nameKey+"'";
  					 html +="style='display:inline-block'";	
  					 if(  configData[i].value !=undefined &&  configData[i].value !='filter_not'){
  					 	html +="value='"+configData[i].value+"'";
  					 } else if(configData[i].defaultsearch != undefined && configData[i].defaultsearch!=''){
  						html += "value='"+configData[i].defaultsearch+"'";
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
				if( configData[i].is_accurate  !=undefined && configData[i].is_accurate  ==1 ){
  					html +="<span style='text-align:right;display:inline-block;'>"+configData[i].title+"：</span>";
  				}else{
  					html +="<span style='text-align:right;display:inline-block'>"+configData[i].title+"：</span>";
  				}
				html +="<select name="+nameKey+" ";
				if(configData[i].isadd !=0){
					html +=" multiple ";
				}
				html += "style='width:130px;' class='selectChange'>";
				if( configData[i].isadd == 0  && configData[i].tableType != 2){
                    if (tableObj.grade.pubdata.reshape != '1') {
                        html +="<option value='filter_not' selected ='selected' >--请选择--</option>";
                    }
			    }
 				for( var j in  configData[i].data_source){
					if(configData[i].value !=undefined){
					    if(configData[i].data_source[j].key == configData[i].value){
							html +="<option value='"+configData[i].data_source[j].key+"' selected ='selected'>"+configData[i].data_source[j].value+"</option>";
						}else{

							html +="<option value='"+configData[i].data_source[j].key+"'>"+configData[i].data_source[j].value+"</option>";
						}
					}else{
						var selstr = "";
						if(configData[i].defaultsearch != undefined && configData[i].defaultsearch!=''){
  							selstr = "selected ='selected'";
  				  		}
						html +="<option value='"+configData[i].data_source[j].key+"' "+selstr+">"+configData[i].data_source[j].value+"</option>";
  					}
 				}
				html +='</select>';
				html +='</div>';
 				break; 
 			case 'textarea':
				html +="<div class='col-md-3' style='margin-bottom:1px'>";

				if( configData[i].is_accurate  !=undefined && configData[i].is_accurate  ==1 ){
  					html +="<span style='text-align:right;display:inline-block;color:#0c8f44'>"+configData[i].title+"：</span>";
  				}else{
  					html +="<span style='text-align:right;display:inline-block'>"+configData[i].title+"：</span>";
  				}

				//html +="<span style='text-align:right;display:inline-block'>"+configData[i].title+"：</span>";
				html +="<textarea name="+nameKey+" >";
				if(configData[i].value !='filter_not'){
  					html += configData[i].value;
  				} else if(configData[i].defaultsearch != undefined && configData[i].defaultsearch!=''){
  					html += configData[i].defaultsearch;
  				}
  				html +='</textarea>';
				html +='</div>';
 				break;
 			 
		  }
	 }
	 var  tips="";
	 for(var j in configData){
	 	if( configData[j].is_accurate  !=undefined  && configData[j].is_accurate ==1){
	 		tips =  "<span style='color:#0c8f44'>绿色为精确查找</span><br>";
	 	}
	 }
	 if(tips !=''){
	 	html +='<b style="margin-left:10px;line-height:20px" data-toggle="tooltip" title="'+tips+'" class="showinfo glyphicon glyphicon-question-sign"></b>';
	 }
	 if(configData.length >0 &&  configData[0].key !="rsv_pq"){
		 html +="<div style='padding-left:3px;display: inline;'>"
		 html +='<button type="submit" class="btn btn-def btn-sm btnSearch" style="padding:2px 6px">查询</button>';
		 html +="</div>";
		 html +='</div>';
	 }else{
	 	 html ='';
	 }
	 return html;
 }
