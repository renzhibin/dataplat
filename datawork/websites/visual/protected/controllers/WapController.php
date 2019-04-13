<?php

class WapController extends Controller
{
	public $statusName = array("1"=>"资源位申请","2"=>"时尚度申请","3"=>"banner图申请","4"=>"上线申请","5"=>"已上线","6"=>"上线并打通微信钱包","-1"=>"已下线","0"=>"紧急申请上线");
	public $replyName = array("-1"=>"已撤消","0"=>"待审核","1"=>"已通过","2"=>"审核不通过");


	function __checkAuth($id){

		if($this->admin)
			return true;

		$objAuth=new AuthManager();
		$res=$objAuth->checkReportPoint($id);

		/* $conf=$this->objReport->getReoport($id);
         $userouth= $this->objuser->selectUserGroup();

         if(array_intersect(explode(',',$conf['auth']),explode(',',$userouth))) {
              return true;
         }
             return false;*/
		return $res;

	}

	public function actionTest(){
		echo 'test';
	}
    
	public function actionSpeed(){
		$keyword  =  trim($_POST['keyword']);
		$apiUrl = 'http://api.speed.meilishuo.com';
		//$apiUrl ='http://apitest.speed.meilishuo.com';
		$token = $_POST['token'];
		$apply_id = $_POST['apply_id'];
		// $token = '5d48ae7e6ff180886639fb435d52222a';
		// $apply_id = 129;
		// $keyword ='用户';
		if($keyword ==''){
			exit;
		}
		if($apply_id ==129 && $token =='5d48ae7e6ff180886639fb435d52222a' ){
			//首焦自动化
			if($keyword[0]==='0'){
				$id_for_user=substr($keyword,1);
				$bohui=false;
				if(strstr($id_for_user,'n')){
					//被驳回了
					$bohui=substr(strstr($id_for_user,'n'),1);
					$lenth2 = strpos($id_for_user,'n');
					$id_for_user=substr($id_for_user,0,$lenth2);
					$bohui='驳回:'.$bohui;
				}
				//获取用户名,别的地方应该用不到
				$curl = yii::app()->curl;
				$url1 = 'http://api.speed.meilishuo.com/user/show?token=' . $token . '&id=' . $_POST['user_id'];
				$u_info = $curl->get($url1);
				$response = json_decode($u_info['body'], 1);
				$mail = $response['data']['mail'];
				$lenth = strpos($mail,'@');
				$user=substr($mail,0,$lenth);
				$user_name=$response['data']['name'];
				//根据$user,$id_for_user,获取source_id(id)与flow(status)
				//var_dump($user);
				//var_dump($id_for_user);
				$quick_reply = $this->AppHomefocus->get_quick_reply($user,$id_for_user);
				//var_dump($quick_reply);
				if($quick_reply){
					//var_dump(1);
					$replyinfo = $bohui ? $bohui : '';
					$reply_status = $bohui ? 2 : 1;
					$data['id'] = $quick_reply['source_id'];
					$data['status'] = $quick_reply['flow'];
					$data['reply_info'] = $replyinfo;
					$data['reply_status'] = $reply_status;
					$data['user'] = $user;
					$data['user_name'] = $user_name;
					//Yii::import('websites/visual/protected/controllers/AppHomefocusController.php');
					//var_dump($data);
					$datamsg = $this->dealReplyUpdate($data);
					//var_dump($datamsg);
					if ($datamsg['status'] == 0) {
						//去干掉quick_reply里的状态
						$this->AppHomefocus->kick_quick_reply($quick_reply['id']);
						$msg[] = '您输入了'.$keyword;
						$msg[] = $datamsg['msg'];
						$msg[] = '来自 首焦自动化 ';
					} else {
						$msg[] = '您输入了'.$keyword;
						$msg[] = $datamsg['msg'];
						$msg[] = '来自 首焦自动化 ';
					}

				$url = $apiUrl."/im/publicMsg";
				$params = array(
						'token'=>$token,
						'msg'=>implode("\n", $msg),
						'user_ids'=>$_POST['user_id'],
						'msg_type'=>0,
						'source'=>'eg:speed'
				);
				$curl = yii::app()->curl;
				$output = $curl->post($url,$params);}
				//$control=yii::app()->runController('/AppHomefocus/replyUpdate');
				//var_dump('/AppHomefocus/actionReplyUpdate?id='.$quick_reply['source_id'].'&status='.$quick_reply['flow'].'&reply_info='.$replyinfo.'&reply_status='.$reply_status);die();
				//$control=Yii::app()->runController('/AppHomefocus/actionReplyUpdate','id'=>$quick_reply['source_id'],'status'=>$quick_reply['flow'],'reply_info'=>$replyinfo,'reply_status'=>$reply_status);
				//var_dump($control);
				//$this->redirect(array('/AppHomefocus/replyUpdate','id'=>$quick_reply['source_id'],'status'=>$quick_reply['flow'],'reply_info'=>$replyinfo,'reply_status'=>$reply_status));
			}



			//非首焦自动化,匹配关键字返回报表
			//获取keywords 返回显示
			$info = $this->report->getKeyWords($keyword);

			//取出info中的值,批量确认风控信息
			$ids=array();//reportid的数组,批量查风控
			foreach($info as $k=>$v){
				$ids[]=$v['id'].'_'.$v['cn_name'];
			}
			$objAuth=new AuthManager();
			$ids=$objAuth->checkPoint($ids);

			foreach($info as $k=>$v){
				//var_dump(in_array($v['id'].'_'.$v['cn_name'],$ids));
				if(!in_array($v['id'].'_'.$v['cn_name'],$ids)){
					unset($info[$k]);
				}
			}

			if(empty($info)){
				exit;
			}
			$msg  = array();
			foreach ($info as $kid => $keyVal) {
				$msg[]= $keyVal['cn_name'].":"."http://data.lsh123.com/wap/report/".$keyVal['id'];
				//$msg[]= $keyVal['cn_name'].":"."http://172.17.74.36:8080/wap/report/".$keyVal['id'];
			}
			$url = $apiUrl."/im/publicMsg";
			$params = array(
				'token'=>$token,
				'msg'=>implode("\n", $msg),
				'user_ids'=>$_POST['user_id'],
				'msg_type'=>0,
				'source'=>'eg:speed'
			);

			$curl = yii::app()->curl;
	        $output = $curl->post($url,$params);

        }

	}

	//审核的数据处理方法 供给批量审核使用； $data接收的数据 $id
	//首焦自动化
	public function dealReplyUpdate($data){
		$datamsg = array("status"=>"0","msg"=>"","data"=>array());
		if(!isset($data['id'])){
			$datamsg['status']= 1;
			$datamsg['msg']= "缺少id参数";
			return $datamsg;
		}
		if(!isset($data['status'])){
			$datamsg['status']= 1;
			$datamsg['msg']= "缺少status参数";
			return $datamsg;
		}

		$id = $data['id'];
		$reply_info = $data['reply_info'];
		$status = $data['status'];
		$reply_status = $data['reply_status'];

		$wherearr = array("id"=>$id,"status"=>$status);
		$getresult = $this->AppHomefocus->getList($wherearr);

		if(empty($getresult)){
			$datamsg['status']= 1;
			$datamsg['msg']= "您不能审核,数据库中没有相应的数据信息";
			return $datamsg;
		}

		$result = $getresult[0];
		$myresult = $getresult[0];

		$oldauditinfo = json_decode($result['auditinfo'],true);
		$creater = $result['creater_name'];
		//获取审核信息
		$auditarr = $this->getAuditinfo($status,$oldauditinfo,$data['reply_status'],array("reply_info"=>$reply_info,"reply_status"=>$reply_status),$data['user'],$data['user_name']);
		//如果是最后一个人审核的 要判断是否通过审核

		$params['auditinfo'] = json_encode($auditarr['auditinfo']);
		$params['reply_status'] = $auditarr['reply_status'];
		$result = $this->AppHomefocus->Update($id,$params);

		if(!$result){
			$datamsg['status']= 1;
			$datamsg['msg']= "数据更新失败";
			return $datamsg;
		}

		//发送邮件 根据$auditarr['auditinfo']  的todo do 以及 reply_status 判断是否通过审核。审核完毕后均要发邮件通知
		//send email

		//全部审核后发邮件通知
		$sendmails = $creater;
		$host = Yii::app()->request->hostInfo;

		if($auditarr['reply_status']>0){
			$title = $this->statusName[$status].$this->replyName[$auditarr['reply_status']];
			if($auditarr['reply_status'] == 1){
				//进入下一步的申请 紧急上线status＝0 而且只有一步
				if(intval($status)<4 && intval($status)>0 ){
					$newstatus = intval($status)+1;
					$newtitle = $this->statusName[$newstatus];
					$html = "<p>审核状态：".$this->replyName[$auditarr['reply_status']]." (".$this->statusName[$status].")</p><p>处理位置：<a href='".$host."/AppHomefocus/apply?flow=".$newstatus."&id=".$id."'>".$newtitle."</a></p>";
				} else {
					//上线申请通过
					if(intval($status) == 4 || intval($status) == 0){
						$html = "<p>审核状态：".$this->replyName[$auditarr['reply_status']]." (".$this->statusName[$status].")</p><p>查看位置：<a href='".$host."/AppHomefocus/detail?flow=".$status."&id=".$id."'>查看详细</a></p>";
					}
				}
			} else {
				//不通过编辑
				$html = "<p>审核状态：".$this->replyName[$auditarr['reply_status']]." (".$this->statusName[$status].")</p><p>查看位置：<a href='".$host."/AppHomefocus/detail?flow=".$status."&id=".$id."'>查看详细</a>后，再进行修改编辑</p>";
			}

			$this->AppHomefocus->sendMail($myresult,$sendmails,$html,$title,'',true);

		} else {
			//上线申请 串行审核
			if(intval($status) == 4 && $auditarr['reply_status']== 0){
				$title = '上线申请待审核';

				if($auditarr['auditinfo']['todo'] !=''){

					$todoarr = explode(',',$auditarr['auditinfo']['todo']) ;
					$sendmails = $todoarr[0];
					//$sendmails = $auditarr['auditinfo']['todo'];
					//下一个人去审核
					$html = "<p>审核状态：".$title."</p><p>处理位置：<a href='".$host."/AppHomefocus/reply?flow=".$status."&id=".$id."'>上线申请审核</a></p>";
					$this->AppHomefocus->sendMail($myresult,$sendmails,$html,$title,'',true);

				}

			}

			//如果某个人审核不通过 发邮件
			if( $reply_status == 2 ){
				$title = $this->statusName[$status];
				$html = "<p>审核状态：".$title."被驳回</p><p>查看位置：<a href='".$host."/AppHomefocus/detail?flow=".$status."&id=".$id."'>".$title."</a></p>";
				$this->AppHomefocus->sendMail($myresult,$creater,$html,$title,'',true);

			}

		}

		$datamsg['status']= 0;
		$datamsg['msg']= "数据更新成功";
		return $datamsg;

	}



	//获取审核流程信息 status流程状态＝流程id  $auditinfo审核信息 $apply 申请0 审核1 -1撤消  $newinfo={"reply_status":"","reply_info":""} $Cc 抄送人
	//首焦自动化
	function getAuditinfo($status=false,$auditinfo=array(),$isapply=0,$newrelyinfo=null,$user,$user_name){
		$id = isset($status) ? (int)$status : 1;
		$result =$this->AppHomefocus->getProcessinfo($id);
		$params = array("reply_status"=>0); // 包含 reply_status 和 auditinfo两个字段的信息
		//添加获取审核信息
		foreach($result as $key=>$val){

			if($val['id'] == $status){
				$auditor = $val["auditor"];
				break;
			}
		}

		if(empty($auditinfo)){
			//审核信息
			$auditinfo=array("todo"=>"","do"=>array(),"history"=>null);
		}
		//print_r($result);print_r($auditor);exit;
		//申请 1、todo重新赋值审核人 2、清空do数组 3、更改status 小状态0
		if($isapply==0) {
			$auditinfo['todo'] = $auditor;
			//清空do里的
			$auditinfo['do'] = array();
			//$auditinfo['history'] = array();
			$params['reply_status'] = 0;

		} else if($isapply == -1){
			//撤消
			$today = date('Y-m-d H:i:s',time());
			$auditinfo['todo'] = "";
			//清空do里的
			$auditinfo['do'] = array();
			$params['reply_status'] = -1;
			//$auditinfo['history'] = array();
			//清空
			$name = $user_name;
			$username = $user;

			//$do=array();
			$do = $auditinfo['history'][$status];
			$newrelyinfo['name'] = $name;
			$newrelyinfo['username'] = $username;
			$newrelyinfo['time'] = $today;
			$newrelyinfo['flag'] =$this->statusName[$status]."撤消";
			$newrelyinfo['reply_info']='';
			$do[] = $newrelyinfo;
			$auditinfo['history'][$status] = $do;

		} else {

			//审核1、todo减去一人 2、do添加一条信息 3.如果最后一个人审核 （3.1）清空todo  do  （3.2）do的内容添加到history
			$today = date('Y-m-d H:i:s',time());

			//审核todo 减 并记录审核信息
			$todo = $auditinfo['todo'];
			//$do = json_decode($auditinfo['do'],true);
			$do = $auditinfo['do'];
			//减去todo
			$name = $user_name;
			$username = $user;
			$todoarr = explode(",",$todo);
			$newtodo = array();
			if(!empty($todoarr)){
				foreach($todoarr as $key=>$val){
					//var_dump($val);var_dump($username);
					if($val != $username){
						$newtodo[]=$val;
					}
				}
			}

			//do 添加一条信息
			if($newrelyinfo){
				$newrelyinfo['name'] = $name;
				$newrelyinfo['username'] = $username;
				$newrelyinfo['time'] = $today;
				$newrelyinfo['flag'] = $newrelyinfo['reply_status'] == 1 ?"通过":"未通过";
				$do[] = $newrelyinfo;

			}

			//print_r($newtodo);print_r('----');print_r(count($do));exit;
			//最后一个人审核的时候 判断（3.1）清空todo  do  （3.2）do的内容添加到history
			if(count($newtodo)==0 && count($do)>0){
				$count = 0;
				foreach($do as $key=>$val){
					if($val['reply_status'] == 2 ){
						$count++;
					}
				}
				if(!empty($auditinfo['history']) && isset($auditinfo['history'][$status])){
					$history = array_merge($auditinfo['history'][$status],$do);
				} else {
					$history = $do;
				}
				$auditinfo['history'][$status] = $history;

				//清空do里的
				$do = array();

				//通过 不通过的
				$params['reply_status'] = ($count>0) ? 2 : 1;

			}

			$auditinfo['todo'] = implode(',',$newtodo);
			$auditinfo['do'] = $do;

		}
		$params['auditinfo'] = $auditinfo;
		$leadername = "";
		//第四步 上线申请需要leader首批 或者紧急上线时 leader的
		if($id == 4 || $id == 0){
			//获取leader
			$leader = $this->getLeader($user);
			$leadername = $leader['name'];
		}
		$params['leader']= $leadername;
		return $params;

	}

	//业务方的leader
	//首焦
	function getLeader($user){
		$username = $user;
		$url="http://api.speed.meilishuo.com/user/get_superior?mail=".$username."&token=a595a9bd9909e72a792bb535379ed477";
		$result = $this->getCurlData($url);
		$mail = "";
		$name = "";

		//测试
		//$result="";

		if($result){
			$mail = $result['mail'];
			if($result['departid'] == 86){
				$mail="jiaqishen@meilishuo.com";
			}

			$namearr = explode('@',$mail);
			$name = $namearr[0];
		}
		return array("mail"=>$mail,"name"=>$name);
	}
	//首焦自动化
	//调用外部接口的
	function getCurlData($url){

		$output = $this->curl->get($url);
		$config = json_decode($output['body'], true);
		if($config['code'] == 200){
			$result = $config['data'];
		} else {
			$result = false;
		}
		return $result;
	}

    public function actionIndex(){
	  $menuInfo = $this->getMenu();
		Yii::app()->smarty->assign('menuTitle', $menuInfo['menuTitle']);
		Yii::app()->smarty->assign('urlMenu', $menuInfo['urlMenu']);
		/*$tplArr['collect']=$menuInfo['collect'];//收藏报表

      $recently = $this->report->getRecentlyReport('wap',date("Y-m-d H:i:s",strtotime("-3 day")));
      //获取所有报表信息
      $reportList   = $this->report->getReportList();
      foreach ($recently as $rekey => $reItem) {
      	 	$idArr  =  json_decode($reItem['param'],true);
      		foreach ($reportList as $report => $reportItem) {
      			if( $reportItem['id'] == $idArr['table_id']){
      				$recently[$rekey]['report_name'] = $reportItem['cn_name'];
      			}
      		}
      }
      $tplArr['recently'] = $recently;
		*/
      $this->render('wap/index.tpl');
	}
	function actionCollect(){
		$menuInfo = $this->getMenu();
        $tplArr['collect']=$menuInfo['collect'];//收藏报表
        $indexStr[] = array('href'=>"/wap/index",'content'=>'首页');
        $indexStr[] = array('href'=>"#",'content'=>'收藏');
        $tplArr['guider'] = $indexStr;
        $this->render('wap/collect.tpl',$tplArr);
	}
	function actionRecently(){
		$recently = $this->report->getRecentlyReport('all',date("Y-m-d H:i:s",strtotime("-3 month")));
		//获取所有报表信息
		$reportList   = $this->report->getReportList();

		$uni_arr=array();//防止手机端与PC端的记录重复

		foreach ($recently as $rekey => $reItem) {
			 	$idArr  =  json_decode($reItem['param'],true);
			if(!in_array($idArr['table_id'],$uni_arr)) {
				$uni_arr[]=$idArr['table_id'];
				foreach ($reportList as $report => $reportItem) {
					if ($reportItem['id'] == $idArr['table_id']) {
						$recently[$rekey]['report_name'] = $reportItem['cn_name'];
						//去掉斜线
						//$recently[$rekey]['user_action']=ltrim($recently[$rekey]['user_action'],'/');
						//$re_arr=json_decode($recently[$rekey]['param'],true);
						$recently[$rekey]['user_action'] = 'wap/report/' . $idArr['table_id'];
						//var_dump($re_arr['table_id']);
					}
				}
			}
		}


		$indexStr[] = array('href'=>"/wap/index",'content'=>'首页');
        $indexStr[] = array('href'=>"#",'content'=>'最常访问');
        $tplArr['guider'] = $indexStr;
		$tplArr['recently'] = $recently;
        $this->render('wap/recently.tpl',$tplArr);
	}
	//二级菜单显示页
	public function actionSecondMenu(){
	  $menu_name = $_GET['menu_name'];
	  $menuInfo = $this->getMenu();
	  $tplArr['menuInfo']  =  $menuInfo['menuTitle'][$menu_name];
	  $tplArr['menu_name'] = $menu_name;
	  $indexStr[] = array('href'=>"/wap/index",'content'=>'首页');
      $indexStr[] = array('href'=>"#",'content'=>$menu_name);
      $tplArr['guider'] = $indexStr;
      $this->render('wap/second.tpl',$tplArr);
	}
	//二级菜单显示页
	public function actionReportList(){
	  $menuInfo = $this->getMenu();
	  $menu_name = $_GET['menu_name'];
	  $menu_id = $_GET['menu_id'];
	  $menuInfo  =  $menuInfo['menuTitle'][$menu_name];
	  $reportList = array();
	  $secondName = '';
	  foreach ($menuInfo as $key => $value) {
			if($key == $menu_id){	
				$secondName = $value['name'];
				$reportList = $value['table'];
			}
	  }
	  $indexStr[] = array('href'=>"/wap/index",'content'=>'首页');
      $indexStr[] = array('href'=>"/wap/SecondMenu?menu_name=".$menu_name,'content'=>$menu_name);
      $indexStr[] = array('href'=>"#",'content'=>$secondName);
      $tplArr['guider'] = $indexStr;
	  $tplArr['reportList'] =  $reportList;


	  // echo "<pre>";
	  // print_r($reportList);exit;
      $this->render('wap/reportlist.tpl',$tplArr);
	}
	/*wap显示页*/
	public function  actionReport(){
		$id = $_GET['id'];
		$status = $this->common->checkDevice();
		//去掉头部
		$reportauth=$this->__checkAuth($id);
		if($reportauth){
	    $newArr = $this->report->showReport($id);
	    $newArr['params'] = json_decode($newArr['params'],true);
	    if(!empty($newArr['params']['chart'])){
	    	foreach ($newArr['params']['chart'] as $key => $value) {
	    		$newArr['params']['chart'][$key]['chartconf'][0]['header'] =1;
	    	}
	    }
	    $newArr['params'] = json_encode($newArr['params']);
	    $menuObj = new MenuManager();
	    $indexStr = array();
	    //面包屑
	    $res = $menuObj->getMenuByReoprt($id);
	    if(!empty($res) ){
	    	$indexStr[] = array('href'=>"/wap/index",'content'=>'首页');
	    	$indexStr[] = array('href'=>"/wap/SecondMenu?menu_name=".$res[0]['first_menu'],'content'=>$res[0]['first_menu']);
      		$indexStr[] = array('href'=>"/wap/reportlist?menu_name=".$res[0]['first_menu']."&menu_id=".$res[0]['id'],'content'=>$res[0]['second_menu']);
      		$indexStr[] = array('href'=>"#",'content'=>$newArr['confArr']['cn_name']);
	    }
	    $newArr['guider'] = $indexStr;
	    //记录用户访问记录
	    $this->behavior->addUserBehaviorToLog($id,0,'wap/report/'.$id,array('table_id'=>$id));
	    $this->render('wap/report.tpl',$newArr);}else{
			echo '<h1>您没有查看此报表的权限</h1>';
		}
	}
}
