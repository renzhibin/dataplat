<?php 
  class  ChartController extends Controller{
  	
  	/*图表execl下载*/
		function actionDownExcel(){
			$dataExcel = $_POST['chartExcel'];
			error_reporting (0); // 屏蔽警告和NOTICE等所有提示.包括error
			Header ( "Content-type:   application/octet-stream " );
			Header ( "Accept-Ranges:   bytes " );
			Header ( "Content-type:application/vnd.ms-excel;charset=Big5" );
			Header ( "Content-Disposition:attachment;filename=" . date ( 'Ymd' ) . ".xls " );
			header ( 'content-Type:application/vnd.ms-excel;charset=utf-8' ); 
			echo $this->chart->tableexcel(json_decode($dataExcel,true));
    }
    
    /*初使化图表加载*/
	function actionShowChart() {
		 
		//处理时间
			$setting = $this->chart->settingDecode($_POST['srcSecting']);
			$now = time();
			if($_POST['timeInterval']  !=''){

				if($_POST['timeInterval'] =='now_year'){
					$now = time();
					$setting['date_from'] = date('Y-m-d', mktime(0, 0,0, 1, 1, date('Y', $now)));
				}else if( $_POST['timeInterval'] =='alldata'){
					$setting['date_from'] = '2011-01-01';
				}else{
	                $setting['date_from'] = date("Y-m-d",strtotime('-'.$_POST['timeInterval']));
	            }
				$setting['date_to'] = date("Y-m-d",strtotime('-1 day'));
				 
			}else{
				//先获取数扰自带时间，如果获取不到，则重新赋值
				if($setting['date_from'] ==''){
					if($setting['dataConig'][0]['edate'] !=''){
						$setting['date_from'] =  $setting['dataConig'][0]['date'];
					}else{
						$setting['date_from'] = date("Y-m-d",strtotime('-8 day'));
					}
				}
			}
			if($setting['date_to'] ==''){
				if($setting['dataConig'][0]['edate'] !=''){
					$setting['date_to'] =  $setting['dataConig'][0]['edate'];
				}else{
					$setting['date_to'] = date("Y-m-d",strtotime('-1 day'));
				}
				
			}
			switch($_POST['timeType']){
				case 'day':  
	   				//$data =  $this->chart->setConfig($setting);
					if(!isset($_POST['sum'])){
						$data =  $this->chart->setConfig($setting);
						echo json_encode($data);
					}else{
						$chartArr = $this->chart->getapiData($setting['dataConig'], $setting['date_from'], $setting['date_to']);
		 				$sumData = $this->chart->sumData($chartArr,$_POST['timeType']);
						foreach($sumData as $dt=>$dtVal){
						   $sumData[$dt]['dt'] = $setting['date_from']."~".$setting['date_to'];
						}
	  					echo json_encode($sumData);
					}
	  			break;
				case  'week': 
				case  'month':
						$comparisons = array();
		        	 	$chartData = $this->chart->setConfig($setting);
		        	 	$i = 0;
		        	 	$chartArr = $this->chart->getapiData($setting['dataConig'], $setting['date_from'], $setting['date_to']);
						$num = 0;
						$xaxis = array();
	 	        	 	foreach ($chartArr as $k => $va){
		        	 		$showArr = $this->chart->dayweekmonthData($_POST['timeType'],$va['data']);
							foreach($chartData['series'] as $ck=>$cv){
								if($cv['name']==$va['name']){
//									file_put_contents('/Users/peng/Desktop/temp/phplog.txt',$ck['name'].$cv['name'].PHP_EOL,FILE_APPEND);
									$chartData['series'][$ck]['data'] = $showArr['data'];
								}
							}
//		        	 		$chartData['series'][$i]['data'] = $showArr['data'];
		        	 		$i++;
	  						if($num < count($showArr['xaxis'])){
	 							$xaxis = $showArr['xaxis'];
								$num =  count($showArr['xaxis']);
							} 
	 	        	 	}
	 	        	 	//$chartData['xAxis'] = array();
		        	 	$chartData['xAxis']['data']= $xaxis;
		        	 	// $chartData['xAxis']['axisLabel'] =array(
			          //      'rotate'=>8
			          //   );
		        	 	//$chartData['xAxis']['labels']= array( 'rotation'=>-30,'align'=>'right');
	 					if(!isset($_POST['sum'])){
							echo json_encode($chartData);
						}else{
	 						$sumData = $this->chart->sumData($chartArr,$_POST['timeType']);
	                        foreach($sumData as $dt=>$dtVal){
	                            $sumData[$dt]['dt'] = $chartData['xAxis']['data'][0]."--".end($chartData['xAxis']['data']);
	                        }
	  						echo json_encode($sumData);
	 					}
			  	break;
	 		 } 
	}

	  function getSpecialChart($specialArr,$keyNameArr = array()){
			$chartConfig = array();

			foreach ($specialArr as $key => $value) {
				//sql处理
				$endtime = $_REQUEST['endtime']? $_REQUEST['endtime']: date("Y-m-d",strtotime("-1 day"));
				$starttime = $_REQUEST['starttime']? $_REQUEST['starttime']: date("Y-m-d",strtotime("-7 day"));
				$value['sql']  = str_replace("{endtime}", "'".$endtime."'", $value['sql']);
				$value['sql']  = str_replace("{starttime}", "'".$starttime."'", $value['sql']);

				//默认值替换
				foreach($keyNameArr['filter']  as $defVal){
					$speStr = "{".$defVal['key']."}";
					$value['sql'] =str_replace($speStr,"'".$defVal['value']."'",$value['sql']);
				}
				foreach($_REQUEST as $spe=>$speVal){
					$speStr = "{".$spe."}";
					$value['sql'] =str_replace($speStr,"'".$speVal."'",$value['sql']);
				}
				if(isset($value['db_name'])){
					$data = biDataModel::getInstance()->getSpecialChart($value['sql'],$value['db_name']);
				}else{
					$data = biDataModel::getInstance()->getSpecialChart($value['sql']);
				}
				//echo $value['sql'];exit;
				$oneChart = array();
				switch ($value['charttype']){
					case 'pie':
						#算总的数据
						$total= 0;
						foreach ($data as $oneVal) {
							$total +=  $oneVal[$value['key']];						
						}
						//处理数据
						foreach ($data as $pid => $percentVal) {
							$data[$pid][$value['key']] =  round($percentVal[$value['key']]*100 / $total,2);
						}
						//生成图表配置文件
						$oneChart['chart']['renderTo'] = $this->generate_Str(6);
						$oneChart['title']['text'] = $value['title'];
						$oneChart['yAxis']['title']['text'] ='';
						$oneChart['credits']['enabled']=false;
						$oneChart['tooltip']['pointFormat'] = '{point.name}: <b>{point.percentage:.1f}%</b>';
						$oneChart['plotOptions']['pie']['allowPointSelect'] = true;
						$oneChart['plotOptions']['pie']['cursor'] = 'pointer';
						$oneChart['plotOptions']['pie']['dataLabels'] =  array(
							'enabled'=>true,
							'color'=>'#000000',
							'connectorColor'=>"#000000",
							'format'=>'<b>{point.name}</b>: {point.percentage:.1f} %'
						);
						$oneChart['series']  =  array();
						$dataCon = array();
						$dataCon['type']  = 'pie';
						$dataCon['data']  = array();				
						foreach ($data as $sid => $seriesVal) {
							$oneData = array();
							if(isset($value['special'])){
								$oneData[0] =  $value['special'][$seriesVal[$value['name_key']]];
							}else{							
								$oneData[0] = $seriesVal[$value['name_key']];
							}
							$oneData[1] = $seriesVal[$value['key']];
							$dataCon['data'][] = $oneData;
						}
						$oneChart['series'][]  = $dataCon;
						$chartOther = array();
						$chartOther['id'] = $oneChart['chart']['renderTo'];
						$chartOther['config'] = json_encode($oneChart);
						break;
					case 'column':
						if(isset($value['xAxis'])){
							$dataKey = $value['xAxis'];
						}else{
							$dataKey = $value['name_key'];
						}
						foreach ($data as $pid => $percentVal) {
								$oneChart['xAxis']['categories'][] = $percentVal[$dataKey];
						}
						//生成图表配置文件
						$oneChart['chart']['renderTo'] = $this->generate_Str(6);
						$oneChart['chart']['defaultSeriesType'] = 'column';
						$oneChart['title']['text'] = $value['title'];
						$oneChart['yAxis']['title']['text'] ='';
						$oneChart['credits']['enabled']=false;
						$oneChart['series']  =  array();
						if(is_array($value['key'])){
							foreach ($value['key'] as $keychart) {
								$dataCon = array();
								if(isset($keyNameArr)){
									foreach ($keyNameArr['table']['tbHeader']  as  $nVal) {
										if($nVal['key'] == $keychart){
											$dataCon['name'] = $nVal['name'];
										}
									}
								}else{
									$dataCon['name'] = $keychart;
								}
								$dataCon['data']  = array();	
								$dataCon['dataLabels']['enabled']  = true;			
								foreach ($data as $sid => $seriesVal) {
									$oneData = array();
									if(isset($value['xAxis'])){
										$oneData[0] = $seriesVal[$value['name_key']];
										$oneData[1] = floatval($seriesVal[$keychart]);
									}else{							
										$oneData[0] = floatval($seriesVal[$keychart]);
									}
									$dataCon['data'][] = $oneData;
								}
								$oneChart['series'][]  = $dataCon;
							}		
						}else{
							$dataCon = array();
							if(isset($keyNameArr)){
								foreach ($keyNameArr['table']['tbHeader']  as  $nVal) {
									if($nVal['key'] == $keychart){
										$dataCon['name'] = $nVal['name'];
									}
								}
							}else{
								$dataCon['name'] = $keychart;
							}
							$dataCon['data']  = array();	
							$dataCon['dataLabels']['enabled']  = true;			
							foreach ($data as $sid => $seriesVal) {
								$oneData = array();
								if(isset($value['xAxis'])){
									$oneData[0] = $seriesVal[$value['name_key']];
									$oneData[1] = floatval($seriesVal[$value['key']]);
								}else{							
									$oneData[0] = floatval($seriesVal[$value['key']]);
								}
								$dataCon['data'][] = $oneData;
							}
							$oneChart['series'][]  = $dataCon;
						}
						//print_r($oneChart);exit;
						$chartOther = array();
						$chartOther['id'] = $oneChart['chart']['renderTo'];
						$chartOther['config'] = json_encode($oneChart);
						break;
					
					case 'spline':
					case 'area':
					case 'line':
						if(isset($value['xAxis'])){
							$dataKey = $value['xAxis'];
						}else{
							$dataKey = $value['name_key'];
						}
						foreach ($data as $pid => $percentVal) {
								$oneChart['xAxis']['categories'][] = $percentVal[$dataKey];
						}
						//生成图表配置文件
						$oneChart['chart']['renderTo'] = $this->generate_Str(6);
						$oneChart['chart']['zoomType'] = 'x';
						$oneChart['chart']['defaultSeriesType'] = $value['charttype'];
						$oneChart['title']['text'] = $value['title'];
						$oneChart['yAxis']['title']['text'] ='';
						$oneChart['credits']['enabled']=false;
						$oneChart['tooltip']['shared'] = true;
						//$oneChart['tooltip']['shared'] = true;
						$oneChart['series']  =  array();
						if(is_array($value['key'])){
							foreach ($value['key'] as $keychart) {
								$dataCon = array();
								$dataCon['data']  = array();	
								if(is_array($keychart)){
									$dataCon['name'] = $keychart['name'];
									//$dataCon['dataLabels']['enabled']  = true;			
									foreach ($data as $sid => $seriesVal) {
										$oneData = array();
										if(isset($value['xAxis'])){
											$oneData[0] = $seriesVal[$value['name_key']];
											$oneData[1] = floatval($seriesVal[$keychart['key']]);
										}else{							
											$oneData[0] = floatval($seriesVal[$keychart['key']]);
										}
										$dataCon['data'][] = $oneData;
									}	
								}else{
									if(isset($keyNameArr)){
										foreach ($keyNameArr['table']['tbHeader']  as  $nVal) {
											if($nVal['key'] == $keychart){
												$dataCon['name'] = $nVal['name'];
											}
										}
									}else{
										$dataCon['name'] = $keychart;
									}
									//$dataCon['dataLabels']['enabled']  = true;			
									foreach ($data as $sid => $seriesVal) {
										$oneData = array();
										if(isset($value['xAxis'])){
											$oneData[0] = $seriesVal[$value['name_key']];
											$oneData[1] = floatval($seriesVal[$keychart]);
										}else{							
											$oneData[0] = floatval($seriesVal[$keychart]);
										}
										$dataCon['data'][] = $oneData;
									}	
								}
								$oneChart['series'][]  = $dataCon;
							}		
						}else{
							$dataCon = array();
							if(isset($keyNameArr)){
								foreach ($keyNameArr['table']['tbHeader']  as  $nVal) {
									if($nVal['key'] == $keychart){
										$dataCon['name'] = $nVal['name'];
									}
								}
							}else{
								$dataCon['name'] = $keychart;
							}
							$dataCon['data']  = array();	
							$dataCon['dataLabels']['enabled']  = true;			
							foreach ($data as $sid => $seriesVal) {
								$oneData = array();
								if(isset($value['xAxis'])){
									$oneData[0] = $seriesVal[$value['name_key']];
									$oneData[1] = floatval($seriesVal[$value['key']]);
								}else{							
									$oneData[0] = floatval($seriesVal[$value['key']]);
								}
								$dataCon['data'][] = $oneData;
							}
							$oneChart['series'][]  = $dataCon;
						}
						//print_r($oneChart);exit;
						$chartOther = array();
						$chartOther['id'] = $oneChart['chart']['renderTo'];
						$chartOther['config'] = json_encode($oneChart);
						break;
					default:
						break;
				}
				$chartConfig[] = $chartOther;
			}
			return $chartConfig;
		}  

  }