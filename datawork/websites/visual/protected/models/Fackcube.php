<?php


class Fackcube
{
	function  __construct(){


		$this->curl = Yii::app()->curl;
	}
	/*
	 $path    相对路径  如  data.meiliworks.com/get_app_conf   $path = get_app_conf
	 $params  参数  格式   array(key=>value )   如 array('project_name'=>'mob_app')
	 $method  是否是Post提交   true 为 post提交 
	 $timeout 超时设置  默认30秒
	 $refer   
	 $getTotal  是否发送请求 开关
	*/
	function  get($path,$params,$method=false,$timeout=30,$refer='',$getTotal=false){
		//api
		if(isset($params['api']) && $params['api'] == 1){
			$url= WEB_API . "/$path?" . http_build_query($params);
			return $url;
		} else {
			$params['appName']='data';
			$params['appToken']='7BTUhUuzeOmB';
		}

		$url= WEB_API . "/$path";
		if(!empty($params))
			$strparams=http_build_query($params);
        $t1=microtime(true);
//var_dump($url . "?" . $strparams);
        ### 此URL可以使用get进行Debug
        $debugURL = $url . "?" . $strparams;
        ### 此URL可以使用get进行Debug
		if($method ==false) {
			$retu = $this->curl->get($url . "?" . $strparams);
		}
		else {
            // $retu=$this->curl->post($url,$params,$refer,$timeout);
            $retu=$this->curl->post($url,$strparams,$refer,$timeout);
        }
        if($retu['http_code']!=200){
			$msg[]='我们的服务器发生了故障,(╯﹏╰）,请稍等片刻或截图联系我们';
			Yii::app()->smarty->assign('msg',$msg);
			Yii::app()->smarty->display('error/error.tpl');
			die();
		}
		$t2=microtime(true);

		if(strlen($retu['body'])>200000000){
			$return['status']=1;
			$return['msg']='数据过大,无法显示,请调整配置';
			$return['relyMsg']='数据过大';
			return $return;
		}

		$t=round($t2-$t1,3);

		$config = json_decode($retu['body'], true);


		Yii::log('spend time:'.$t.' '.$url. "?" . $strparams,'info','fakecube');

		if ($getTotal==false && $config['status'] != 0 ){
			return false;
		}
		return $config;
	}

	function get_hql($parmms){
		$retu=$this->get('get_cat',$parmms);
		if($retu['status']==0){
			return $retu['data'];
		}
		return false;
	}

	function  get_profile($params,$getAll=false){
		$timeout=180;
		$retu=$this->get('get_profile',$params,true,$timeout,'',$getAll);

		if($retu==false){
			return false;
		}
		if($getAll==false)
			return $retu['data'];
		return $retu;

	}

	function  save_project($params,$getAll=false){
		$retu=$this->get('save_project',$params,true,30,'',true);


	//	if($getAll==false)
	//		return $retu['data'];
		return $retu;

	}




	function  get_app_conf($params,$getAll=false){


		$retu=$this->get('get_app_conf',$params);

		if($retu==false){
			return false;
		}
		if($getAll==false)
			return $retu['data']['project'][0];
		return $retu;

	}
	function  get_metric($params){
		$retu=$this->get('get_metric',$params);
		if($retu==false){
			return false;
		}
		return $retu;

	}
	function get_dimset($params){

		$retu=$this->get('get_dimset',$params);
		if($retu==false){
			return false;
		}
		return $retu;
	}

	function list_app(){

		$retu=$this->get('list_app','');
		if($retu==false){
			return false;
		}
		return $retu['data'];
	}


	function  query_app($params){

		//Yii::trace(CVarDumper::dumpAsString($params));
		if( !isset($params['api']) ){
        	$params['appName']='data';
			$params['appToken']='7BTUhUuzeOmB';
        }

		$retu=$this->get('query_app',$params,true,600,'',true);

		if( isset($params['api']) && $params['api']==1 ){
     	   return $retu;
        } 
		return array('data'=>$retu['data'],'total'=>$retu['total'],'status'=>$retu['status'],'msg'=>$retu['msg'],'showMsg'=>$retu['showMsg'],'relyMsg'=>$retu['relyMsg']);
	}



}
