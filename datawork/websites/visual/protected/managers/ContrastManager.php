<?php
Class ContrastManager extends Manager{
	#生成添加对比数据
	function getContrastData($data,$params){

		$gardConfig = $this->visual->getVisualHeader($params);
/*
		if($params['udcconf']){
			$tmp= json_decode(urldecode($params['udcconf']),true);
			foreach($tmp as $k=>$v){
				$v['name']=strtolower($v['name']);
				$tmp[$k]=$v;
			}
			$params['udcconf']=$tmp;
		}

		foreach(explode(',',$params['metric']) as $v){
			$tmp=str_replace('.','_',$v);
			$metricdot2underline[$tmp]=$v;
		}
		//echo '<pre/>';print_r($data);exit();
		$metricEn2cn=array();
		foreach($gardConfig['metric'] as $k=>$v){

			$metricEn2cn[$v['name']]=$v['cn_name'];
		}


		//print_r($dimensionsArr);print_r('<pre>');print_r($metricArr);print_r('<pre>');print_r($udcArr);exit();
		$overallConfigArr['project'] = $params['project'];
		$overallConfigArr['group'] =  implode(",", $dimensionsArr);
			//20150710 钻取功能 key ＝》 .替换_
			$otherlink=array();

*/

		$dimensionsArr =ConstManager::getConfigName($gardConfig['dim']);
		$metricArr = ConstManager::getConfigName($gardConfig['metric']);



		if(is_array($params['grade']['data'])){
				foreach ($params['grade']['data'] as $linkkey => $linkvalue) {
					//外链功能
					if( $linkvalue['otherlink'] !='-' &&  $linkvalue['otherlink'] !=''){
						$urlkey = implode("_", explode(".", $linkvalue['key']));
						$otherlink[$urlkey]=$linkvalue['otherlink'];
					}
					//图片显示功能
					if( $linkvalue['img_link'] !='-' &&  $linkvalue['img_link'] !=''){
						$urlkey = implode("_", explode(".", $linkvalue['key']));
						$imglink[$urlkey]=$linkvalue['img_link'];
					}
				}
			}
			foreach ($data as $key=>$val){

				$nameArr = array();//表头
				$filter = array();//过滤条件
				foreach ($dimensionsArr as $item) {

					$oneFilter = array();
					$oneFilter['key'] =$item;
					$oneFilter['op'] = '=';
					//拼接filter_str
					if(is_array($val[$item])){

						$oneFilter['val'] = $val[$item]['realdata'];
						$tempval=$val[$item]['commentdata'];

					}else{
						$oneFilter['val'] =array($val[$item]);
						//$tempval = $val[$item];
						$tempval= htmlentities($val[$item],ENT_COMPAT,'UTF-8');

					}

					$tempval = self::replaceStr($item,$otherlink,$val,$tempval);
					if(is_array($data[$key][$item]) ){
						if(!empty($data[$key][$item]['commentdata'])){
							$nameArr[] = $data[$key][$item]['commentdata'];
						}else{
							$nameArr[] = $data[$key][$item]['realdata'];
						}
					}else{
						$nameArr[]= $data[$key][$item];
					}


					$filter[] = $oneFilter;
					$data[$key][$item]= $tempval;
				}
				foreach ($metricArr as $keyid ){
					$vData=$val[$keyid];
					$constractName=$nameArr;

//					$constractName[]=$metricEn2cn[$keyid];

					if(is_array($vData)){
						$realdata=$vData['commentdata'];
					}else{
						$realdata=$vData;
					}

					$realdata = self::replaceStr($keyid,$otherlink,$val,$realdata);

					//$content =  $this->dataContrast($keyStr,$name,$realdata,$isproportion);
					$data[$key][$keyid]  = $realdata;
				}
				if(!empty($imglink)){
					foreach ($imglink as $img => $imgItem) {
						if(isset($val[$img])){
						 	$new_key = $img."_img";
						 	$data[$key][$new_key] = "<a class='imgShow' href='".$this->linkReplace($imgItem,$val)."'><img width='100%' height='120px' src=".$this->linkReplace($imgItem,$val)." /></a>";
						}
					}
				}
				
			}
		return $data;
	}

	function  linkReplace($url,$dataval){
		$Reg ='/\${[^}]*}/i';
		preg_match_all($Reg, $url,$match);
		if(!empty($match)){
			foreach ($match[0] as $k => $value) {
				//取出${}里的内容并 .转为_
				$matchvalue = substr($value, 2,(strlen($value)-3));
				$matchkey = implode("_", explode(".", $matchvalue));
				$realval = is_array($dataval[$matchkey]) ?$dataval[$matchkey]["realdata"] : $dataval[$matchkey];
				$url = str_replace($value, $realval, $url);
			}
		}
		return $url;
	}

	// 20150710  表格钻取功能 替换url的 参数部分
	// $params['otherlink']= {icon_name: "/report/showreport/1731?icon_name=${app_source_icon.hone_icon.pv}", app_source_icon.hone_icon.pv: "/report/showreport/1731?app_source_icon.hone_icon.pv=${app_source_icon.hone_icon.pv}"}
	// $params['dataval'] = array( [icon_name] => 清仓,  [app_source_icon_hone_icon_uv] => 15121 ,
	//					[date] => 2015-07-02, [app_source_icon_hone_icon_pv] => 16456, [my] => 0.92)
	//

	function replaceStr($key,$otherlink, $dataval,$realdata){
		if($key=='mgj_item_id'){
			$realdata=$dataval[$key]['realdata'];
		}
		//print_r($dataval);exit();
		//正则表达式
		$Reg ='/\${[^}]*}/i';
		// 钻取 url 外链
		if(!empty($otherlink)&&is_array($otherlink) &&array_key_exists($key, $otherlink)){
			//替换url的参数
			$url = $otherlink[$key];
			//匹配${app_source_icon.hone_icon.pv} 替换成＝》${app_source_icon_hone_icon_pv}
			preg_match_all($Reg, $url,$match);
			if(!empty($match)){
				foreach ($match[0] as $k => $value) {
					//取出${}里的内容并 .转为_
					$matchvalue = substr($value, 2,(strlen($value)-3));
					$matchkey = implode("_", explode(".", $matchvalue));
					//如果是数组[shop_id] => Array( [realdata] => 132899  [commentdata] => <a style="padding-left:5px" target="_blank" href="http://www.meilishuo.com/shop/132899?from=fakecube">132899</a>)
					//print_r($dataval[$matchkey]);
					$realval = is_array($dataval[$matchkey]) ?$dataval[$matchkey]["realdata"] : $dataval[$matchkey];
					$url = str_replace($value, $realval, $url);
				}
			}
			//print_r('<pre>');print_r(strpos($url,'/'));exit();
			//内部平台链接 加上 edate日期
			if((strpos($url,'data.meiliworks.com')!==false)|| (strpos($url,'/')==0)||(strpos($url,'report')!==false) || (strpos($url,'visual/index')!==false)){

				$ctag = (strpos($url,'?') >=0)?"&":"?" ;
				$dateReg='/(\d{4}-\d{2}-\d{2})-(\d{4}-\d{2}-\d{2})/i';
				preg_match($dateReg,$dataval['date'],$DateMatch);
				if($DateMatch){
					$url=$url.$ctag.'edate='.$DateMatch[2].'&date='.$DateMatch[1];
				}else{
					$url = $url.$ctag.'edate='.$dataval['date'];
				}
//				$url = $url.$ctag.'edate='.$dataval['date'];
			}

			//兼容 shop_id style_id twitter_id的问题 覆盖原有的死链接
			if(is_array($dataval[$key])&& $key!='mgj_item_id'){
				$realdata = strip_tags($dataval[$key]["commentdata"]);
			}
			if($key=='mgj_item_id'){

				$realres = "<a href='".$url."' target='_blank'>".$realdata."</a>".$dataval[$key]['commentdata'];
			}
			else{

				$realres = "<a href='".$url."' target='_blank'>".$realdata."</a>";
			}

			return $realres;
		} else {
			return $realdata;
		}
	}

	//自定义列 处理返回数据的 外链和图片url
	function definedgrade($data,$otherlink,$imglink){
		$gardData = $data['data'];
		foreach($gardData as $key=>$gardval){
			//$val Array([cdate] => 2015-09-24 [col2] => 8000856767.58 [col1] => 0);
			foreach($gardval as $item=>$val){
				if($item !='cdate' && $item !='date' && is_numeric($val) && strpos($item,'id')<0 ){
					//千分隔
					$val = preg_replace('/(?<=[0-9])(?=(?:[0-9]{3})+(?![0-9]))/', ',', $val);
				}
				$val = $this->definedreplacestr($otherlink,$item,$val,$gardval);
				//映射数据
                /*
				if(is_array($val) and array_key_exists('commentdata',$val)){
					$val=$val['commentdata'];
				}
                */
                $currentValue = $val;
                if (is_array($currentValue) and array_key_exists('commentdata', $currentValue)) {
                    $gardData[$key]["{$item}_commentdata"] = $val = $currentValue['commentdata'];
                }
                if (is_array($currentValue) and array_key_exists('realdata', $currentValue)) {
                    $gardData[$key]["{$item}_realdata"] = $currentValue['realdata'];
                }
				$gardData[$key][$item] = $val;

				//图片url
				if(!empty($imglink) && array_key_exists($item,$imglink)){
					$new_key = $item."_img";
					$gardData[$key][$new_key] = "<a class='imgShow' href='".$this->linkReplace($imglink[$item],$val)."'><img width='100%' height='120px' src=".$this->linkReplace($imglink[$item],$val)." /></a>";
				}
			}
		}
		return $gardData;
	}
	//自定义表格 替换外链字符串
	function definedreplacestr($otherlink,$key,$dataval,$dataArr){
		//正则表达式
		$Reg ='/\${[^}]*}/i';
		if(!empty($otherlink)&&is_array($otherlink) &&array_key_exists($key, $otherlink)){
			$url = $otherlink[$key];
			preg_match_all($Reg, $url,$match);
			if(!empty($match)){
				foreach ($match[0] as $k => $value) {
					$matchvalue = substr($value, 2,(strlen($value)-3));
					$matchkey = implode("_", explode(".", $matchvalue));
					$realval =  $dataArr[$matchkey];
					$url = str_replace($value, $realval, $url);
				}
			}
			if((strpos($url,'data.meiliworks.com')!==false)|| (strpos($url,'/')==0)||(strpos($url,'report')!==false) || (strpos($url,'visual/index')!==false)){

				if(array_key_exists('cdate',$dataArr) || array_key_exists('date',$dataArr)){
					$date = array_key_exists('cdate',$dataArr)?'cdate':'date';
					$ctag = (strpos($url,'?')===true)?"&":"?" ;
					$url = $url.$ctag.'edate='.$dataArr[$date];
				}

			}
			$realres = "<a href='".$url."' target='_blank'>".$dataval."</a>";
			return $realres;

		} else {
			return $dataval;
		}

	}
}